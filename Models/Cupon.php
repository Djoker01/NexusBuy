<?php
include_once 'Conexion.php';
class Cupon {
    private $conexion;
    
    public function __construct($conexion) {
        $this->conexion = $conexion;
    }
    
    // ✅ Obtener cupón por código
    public function obtenerPorCodigo($codigo) {
        $sql = "SELECT c.*, 
                       (SELECT COUNT(*) FROM cupon_usado cu WHERE cu.id_cupon = c.id) as usos_actuales
                FROM cupon c
                WHERE c.codigo = :codigo 
                AND c.estado = 'activo'
                AND c.fecha_inicio <= NOW() 
                AND c.fecha_expiracion >= NOW()";
        
        return $this->conexion->obtenerUno($sql, [':codigo' => $codigo]);
    }
    
    // ✅ Obtener cupón por ID
    public function obtenerPorId($id) {
        $sql = "SELECT c.* FROM cupon c WHERE c.id = :id";
        return $this->conexion->obtenerUno($sql, [':id' => $id]);
    }
    
    // ✅ Obtener todos los cupones
    public function obtenerTodos($pagina = 1, $limite = 10) {
        $offset = ($pagina - 1) * $limite;
        
        $sql = "SELECT c.*, 
                       (SELECT COUNT(*) FROM cupon_usado cu WHERE cu.id_cupon = c.id) as usos_actuales
                FROM cupon c
                ORDER BY c.fecha_creacion DESC
                LIMIT :limite OFFSET :offset";
        
        return $this->conexion->obtenerTodos($sql, [
            ':limite' => $limite,
            ':offset' => $offset
        ]);
    }
    
    // ✅ Crear cupón
    public function crear($datos) {
        $sql = "INSERT INTO cupon (codigo, descripcion, tipo_descuento, valor, minimo_compra,
                                 maximo_descuento, usos_maximos, usos_por_usuario,
                                 fecha_inicio, fecha_expiracion, aplicable_todo)
                VALUES (:codigo, :descripcion, :tipo_descuento, :valor, :minimo_compra,
                        :maximo_descuento, :usos_maximos, :usos_por_usuario,
                        :fecha_inicio, :fecha_expiracion, :aplicable_todo)";
        
        return $this->conexion->ejecutar($sql, $datos);
    }
    
    // ✅ Actualizar cupón
    public function actualizar($id, $datos) {
        $sql = "UPDATE cupon SET descripcion = :descripcion, tipo_descuento = :tipo_descuento,
                               valor = :valor, minimo_compra = :minimo_compra,
                               maximo_descuento = :maximo_descuento, usos_maximos = :usos_maximos,
                               usos_por_usuario = :usos_por_usuario, fecha_inicio = :fecha_inicio,
                               fecha_expiracion = :fecha_expiracion, aplicable_todo = :aplicable_todo,
                               fecha_actualizacion = CURRENT_TIMESTAMP
                WHERE id = :id";
        
        $datos[':id'] = $id;
        return $this->conexion->ejecutar($sql, $datos);
    }
    
    // ✅ Cambiar estado del cupón
    public function cambiarEstado($id, $estado) {
        $sql = "UPDATE cupon SET estado = :estado, fecha_actualizacion = CURRENT_TIMESTAMP
                WHERE id = :id";
        
        return $this->conexion->ejecutar($sql, [':estado' => $estado, ':id' => $id]);
    }
    
    // ✅ Verificar si cupón es aplicable
    public function esAplicable($idCupon, $idUsuario, $subtotal) {
        $cupon = $this->obtenerPorId($idCupon);
        
        if (!$cupon) {
            return ['valido' => false, 'mensaje' => 'Cupón no encontrado'];
        }
        
        // Verificar fechas
        if (strtotime($cupon['fecha_inicio']) > time()) {
            return ['valido' => false, 'mensaje' => 'Cupón no está vigente'];
        }
        
        if (strtotime($cupon['fecha_expiracion']) < time()) {
            return ['valido' => false, 'mensaje' => 'Cupón ha expirado'];
        }
        
        // Verificar mínimo de compra
        if ($subtotal < $cupon['minimo_compra']) {
            return ['valido' => false, 'mensaje' => 'No alcanza el mínimo de compra'];
        }
        
        // Verificar usos máximos
        if ($cupon['usos_maximos'] && $cupon['usos_actuales'] >= $cupon['usos_maximos']) {
            return ['valido' => false, 'mensaje' => 'Cupón ha alcanzado su límite de usos'];
        }
        
        // Verificar usos por usuario
        if ($idUsuario) {
            $usosUsuario = $this->contarUsosPorUsuario($idCupon, $idUsuario);
            if ($usosUsuario >= $cupon['usos_por_usuario']) {
                return ['valido' => false, 'mensaje' => 'Ya usaste este cupón el máximo de veces permitidas'];
            }
        }
        
        return ['valido' => true, 'cupon' => $cupon];
    }
    
    // ✅ Contar usos por usuario
    public function contarUsosPorUsuario($idCupon, $idUsuario) {
        $sql = "SELECT COUNT(*) as total FROM cupon_usado 
                WHERE id_cupon = :id_cupon AND id_usuario = :id_usuario";
        
        $resultado = $this->conexion->obtenerUno($sql, [
            ':id_cupon' => $idCupon,
            ':id_usuario' => $idUsuario
        ]);
        
        return $resultado ? $resultado['total'] : 0;
    }
    
    // ✅ Registrar uso de cupón
    public function registrarUso($idCupon, $idUsuario, $idOrden, $descuentoAplicado) {
        $sql = "INSERT INTO cupon_usado (id_cupon, id_usuario, id_orden, descuento_aplicado)
                VALUES (:id_cupon, :id_usuario, :id_orden, :descuento_aplicado)";
        
        return $this->conexion->ejecutar($sql, [
            ':id_cupon' => $idCupon,
            ':id_usuario' => $idUsuario,
            ':id_orden' => $idOrden,
            ':descuento_aplicado' => $descuentoAplicado
        ]);
    }
    
    // ✅ Obtener productos aplicables al cupón
    public function obtenerProductosAplicables($idCupon) {
        $sql = "SELECT cp.*, pt.precio_final, p.nombre as producto_nombre, t.nombre as tienda_nombre
                FROM cupon_producto cp
                JOIN producto_tienda pt ON cp.id_producto_tienda = pt.id
                JOIN producto p ON pt.id_producto = p.id
                JOIN tienda t ON pt.id_tienda = t.id
                WHERE cp.id_cupon = :id_cupon";
        
        return $this->conexion->obtenerTodos($sql, [':id_cupon' => $idCupon]);
    }
    
    // ✅ Agregar producto aplicable
    public function agregarProductoAplicable($idCupon, $idProductoTienda) {
        $sql = "INSERT INTO cupon_producto (id_cupon, id_producto_tienda)
                VALUES (:id_cupon, :id_producto_tienda)";
        
        return $this->conexion->ejecutar($sql, [
            ':id_cupon' => $idCupon,
            ':id_producto_tienda' => $idProductoTienda
        ]);
    }
    
    // ✅ Eliminar producto aplicable
    public function eliminarProductoAplicable($idCupon, $idProductoTienda) {
        $sql = "DELETE FROM cupon_producto 
                WHERE id_cupon = :id_cupon AND id_producto_tienda = :id_producto_tienda";
        
        return $this->conexion->ejecutar($sql, [
            ':id_cupon' => $idCupon,
            ':id_producto_tienda' => $idProductoTienda
        ]);
    }
}