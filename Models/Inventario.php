<?php
include_once 'Conexion.php';
class Inventario {
    private $conexion;
    
    public function __construct($conexion) {
        $this->conexion = $conexion;
    }
    
    // ✅ Obtener movimientos de inventario
    public function obtenerMovimientos($idProductoTienda, $pagina = 1, $limite = 10) {
        $offset = ($pagina - 1) * $limite;
        
        $sql = "SELECT im.*, u.nombres, u.apellidos
                FROM inventario_movimiento im
                JOIN usuario u ON im.id_usuario = u.id
                WHERE im.id_producto_tienda = :id_producto_tienda
                ORDER BY im.fecha_movimiento DESC
                LIMIT :limite OFFSET :offset";
        
        return $this->conexion->obtenerTodos($sql, [
            ':id_producto_tienda' => $idProductoTienda,
            ':limite' => $limite,
            ':offset' => $offset
        ]);
    }
    
    // ✅ Registrar movimiento de inventario
    public function registrarMovimiento($datos) {
        $sql = "INSERT INTO inventario_movimiento (id_producto_tienda, tipo_movimiento, cantidad,
                                                 stock_anterior, stock_nuevo, motivo, referencia,
                                                 id_usuario, notas)
                VALUES (:id_producto_tienda, :tipo_movimiento, :cantidad,
                        :stock_anterior, :stock_nuevo, :motivo, :referencia,
                        :id_usuario, :notas)";
        
        return $this->conexion->ejecutar($sql, $datos);
    }
    
    // ✅ Ajustar stock
    public function ajustarStock($idProductoTienda, $nuevoStock, $motivo, $idUsuario, $notas = '') {
        // Obtener stock actual
        $sqlStock = "SELECT stock FROM producto_tienda WHERE id = :id";
        $producto = $this->conexion->obtenerUno($sqlStock, [':id' => $idProductoTienda]);
        
        if (!$producto) {
            return false;
        }
        
        $stockAnterior = $producto['stock'];
        $diferencia = $nuevoStock - $stockAnterior;
        $tipoMovimiento = $diferencia > 0 ? 'entrada' : 'salida';
        
        // Actualizar stock
        $sqlUpdate = "UPDATE producto_tienda SET stock = :stock, fecha_actualizacion = CURRENT_TIMESTAMP
                      WHERE id = :id";
        $this->conexion->ejecutar($sqlUpdate, [
            ':stock' => $nuevoStock,
            ':id' => $idProductoTienda
        ]);
        
        // Registrar movimiento
        return $this->registrarMovimiento([
            ':id_producto_tienda' => $idProductoTienda,
            ':tipo_movimiento' => $tipoMovimiento,
            ':cantidad' => abs($diferencia),
            ':stock_anterior' => $stockAnterior,
            ':stock_nuevo' => $nuevoStock,
            ':motivo' => $motivo,
            ':referencia' => 'AJUSTE',
            ':id_usuario' => $idUsuario,
            ':notas' => $notas
        ]);
    }
    
    // ✅ Registrar entrada de stock
    public function registrarEntrada($idProductoTienda, $cantidad, $motivo, $idUsuario, $referencia = '', $notas = '') {
        $sqlStock = "SELECT stock FROM producto_tienda WHERE id = :id";
        $producto = $this->conexion->obtenerUno($sqlStock, [':id' => $idProductoTienda]);
        
        if (!$producto) {
            return false;
        }
        
        $stockAnterior = $producto['stock'];
        $stockNuevo = $stockAnterior + $cantidad;
        
        // Actualizar stock
        $sqlUpdate = "UPDATE producto_tienda SET stock = :stock, fecha_actualizacion = CURRENT_TIMESTAMP
                      WHERE id = :id";
        $this->conexion->ejecutar($sqlUpdate, [
            ':stock' => $stockNuevo,
            ':id' => $idProductoTienda
        ]);
        
        // Registrar movimiento
        return $this->registrarMovimiento([
            ':id_producto_tienda' => $idProductoTienda,
            ':tipo_movimiento' => 'entrada',
            ':cantidad' => $cantidad,
            ':stock_anterior' => $stockAnterior,
            ':stock_nuevo' => $stockNuevo,
            ':motivo' => $motivo,
            ':referencia' => $referencia,
            ':id_usuario' => $idUsuario,
            ':notas' => $notas
        ]);
    }
    
    // ✅ Registrar salida de stock
    public function registrarSalida($idProductoTienda, $cantidad, $motivo, $idUsuario, $referencia = '', $notas = '') {
        $sqlStock = "SELECT stock FROM producto_tienda WHERE id = :id";
        $producto = $this->conexion->obtenerUno($sqlStock, [':id' => $idProductoTienda]);
        
        if (!$producto || $producto['stock'] < $cantidad) {
            return false;
        }
        
        $stockAnterior = $producto['stock'];
        $stockNuevo = $stockAnterior - $cantidad;
        
        // Actualizar stock
        $sqlUpdate = "UPDATE producto_tienda SET stock = :stock, fecha_actualizacion = CURRENT_TIMESTAMP
                      WHERE id = :id";
        $this->conexion->ejecutar($sqlUpdate, [
            ':stock' => $stockNuevo,
            ':id' => $idProductoTienda
        ]);
        
        // Registrar movimiento
        return $this->registrarMovimiento([
            ':id_producto_tienda' => $idProductoTienda,
            ':tipo_movimiento' => 'salida',
            ':cantidad' => $cantidad,
            ':stock_anterior' => $stockAnterior,
            ':stock_nuevo' => $stockNuevo,
            ':motivo' => $motivo,
            ':referencia' => $referencia,
            ':id_usuario' => $idUsuario,
            ':notas' => $notas
        ]);
    }
    
    // ✅ Obtener productos con stock bajo
    public function obtenerStockBajo($idTienda = null, $limite = 50) {
        $where = "WHERE pt.stock <= pt.stock_minimo AND pt.estado = 'activo'";
        $parametros = [':limite' => $limite];
        
        if ($idTienda) {
            $where .= " AND pt.id_tienda = :id_tienda";
            $parametros[':id_tienda'] = $idTienda;
        }
        
        $sql = "SELECT pt.*, p.nombre as producto_nombre, t.nombre as tienda_nombre,
                       (pt.stock_minimo - pt.stock) as faltante
                FROM producto_tienda pt
                JOIN producto p ON pt.id_producto = p.id
                JOIN tienda t ON pt.id_tienda = t.id
                $where
                ORDER BY faltante DESC
                LIMIT :limite";
        
        return $this->conexion->obtenerTodos($sql, $parametros);
    }
    
    // ✅ Obtener historial de movimientos por referencia
    public function obtenerPorReferencia($referencia) {
        $sql = "SELECT im.*, p.nombre as producto_nombre, t.nombre as tienda_nombre,
                       u.nombres, u.apellidos
                FROM inventario_movimiento im
                JOIN producto_tienda pt ON im.id_producto_tienda = pt.id
                JOIN producto p ON pt.id_producto = p.id
                JOIN tienda t ON pt.id_tienda = t.id
                JOIN usuario u ON im.id_usuario = u.id
                WHERE im.referencia = :referencia
                ORDER BY im.fecha_movimiento DESC";
        
        return $this->conexion->obtenerTodos($sql, [':referencia' => $referencia]);
    }
}