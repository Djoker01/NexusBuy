<?php
include_once 'Conexion.php';
class Carrito
{
    var $objetos;
    var $acceso;
    public function __construct()
    {
        $db = new Conexion();
        $this->acceso = $db->pdo;
    }

    function obtener_carrito($id_usuario) {
        $sql = "SELECT 
                    ci.id,
                    ci.cantidad,
                    ci.precio_unitario,
                    ci.descuento_unitario,
                    ci.subtotal,
                    ci.fecha_agregado,
                    pt.id as id_producto_tienda,
                    p.nombre,
                    p.imagen_principal as imagen,
                    p.detalles,
                    t.nombre as tienda_nombre,
                    m.nombre as marca_nombre,
                    pt.estado_envio as envio,
                    (ci.precio_unitario - ci.descuento_unitario) as precio_final,
                    pt.cantidad as stock_disponible
                FROM carrrito c
                JOIN carrito_items ci ON c.id = ci.id_carrito
                JOIN producto_tienda pt ON ci.id_producto_tienda = pt.id
                JOIN producto p ON pt.id_producto = p.id
                JOIN tienda t ON pt.id_tienda = t.id
                JOIN marca m ON p.id_marca = m.id
                WHERE c.id_usuario = :id_usuario
                AND c.estado = 'activo'
                AND pt.estado = 'A'
                AND p.estado = 'A'";
        
        $query = $this->acceso->prepare($sql);
        $query->execute(array(':id_usuario' => $id_usuario));
        $this->objetos = $query->fetchAll();
        return $this->objetos;
    }

    function agregar_al_carrito($id_usuario, $id_producto_tienda, $cantidad = 1) {
        $this->acceso->beginTransaction();
        try {
            // 1. Obtener o crear carrito activo
            $id_carrito = $this->obtener_carrito_activo($id_usuario);
            
            // 2. Verificar si el producto ya está en el carrito
            $item_existente = $this->verificar_item_carrito($id_carrito, $id_producto_tienda);
            
            if ($item_existente) {
                // Actualizar cantidad
                return $this->actualizar_cantidad_item($item_existente->id, $item_existente->cantidad + $cantidad);
            } else {
                // Agregar nuevo item
                return $this->agregar_nuevo_item($id_carrito, $id_producto_tienda, $cantidad);
            }
            
        } catch (Exception $e) {
            $this->acceso->rollBack();
            error_log("Error agregar_al_carrito: " . $e->getMessage());
            return false;
        }
    }

    private function obtener_carrito_activo($id_usuario) {
        $sql = "SELECT id FROM carrrito WHERE id_usuario = :usuario AND estado = 'activo'";
        $query = $this->acceso->prepare($sql);
        $query->execute(array(':usuario' => $id_usuario));
        $carrito = $query->fetch();
        
        if ($carrito) {
            return $carrito->id;
        } else {
            // Crear nuevo carrito
            $sql = "INSERT INTO carrrito (id_usuario, estado) VALUES (:usuario, 'activo')";
            $query = $this->acceso->prepare($sql);
            $query->execute(array(':usuario' => $id_usuario));
            return $this->acceso->lastInsertId();
        }
    }

    private function verificar_item_carrito($id_carrito, $id_producto_tienda) {
        $sql = "SELECT id, cantidad FROM carrito_items 
                WHERE id_carrito = :id_carrito 
                AND id_producto_tienda = :id_producto_tienda";
        $query = $this->acceso->prepare($sql);
        $query->execute(array(
            ':id_carrito' => $id_carrito,
            ':id_producto_tienda' => $id_producto_tienda
        ));
        return $query->fetch();
    }

    private function agregar_nuevo_item($id_carrito, $id_producto_tienda, $cantidad) {
        // Obtener información del producto
        $sql = "SELECT pt.precio, pt.descuento, pt.cantidad as stock
                FROM producto_tienda pt
                WHERE pt.id = :id_producto_tienda
                AND pt.estado = 'A'";
        $query = $this->acceso->prepare($sql);
        $query->execute(array(':id_producto_tienda' => $id_producto_tienda));
        $producto = $query->fetch();
        
        if (!$producto) {
            throw new Exception("Producto no encontrado");
        }
        
        // Verificar stock
        if ($producto->stock < $cantidad) {
            throw new Exception("Stock insuficiente. Máximo disponible: {$producto->stock}");
        }
        
        // Calcular precios
        $precio_unitario = $producto->precio;
        $descuento_unitario = $producto->precio * ($producto->descuento / 100);
        $subtotal = ($precio_unitario - $descuento_unitario) * $cantidad;
        
        // Insertar nuevo item
        $sql = "INSERT INTO carrito_items 
                (id_carrito, id_producto_tienda, cantidad, precio_unitario, descuento_unitario, subtotal) 
                VALUES (:id_carrito, :id_producto_tienda, :cantidad, :precio_unitario, :descuento_unitario, :subtotal)";
        
        $query = $this->acceso->prepare($sql);
        $resultado = $query->execute(array(
            ':id_carrito' => $id_carrito,
            ':id_producto_tienda' => $id_producto_tienda,
            ':cantidad' => $cantidad,
            ':precio_unitario' => $precio_unitario,
            ':descuento_unitario' => $descuento_unitario,
            ':subtotal' => $subtotal
        ));
        
        if ($resultado) {
            $this->acceso->commit();
            return true;
        } else {
            throw new Exception("Error al insertar en carrito");
        }
    }

    private function actualizar_cantidad_item($id_item, $nueva_cantidad) {
        // Obtener información actual del item
        $sql = "SELECT ci.precio_unitario, ci.descuento_unitario, pt.cantidad as stock
                FROM carrito_items ci
                JOIN producto_tienda pt ON ci.id_producto_tienda = pt.id
                WHERE ci.id = :id_item";
        $query = $this->acceso->prepare($sql);
        $query->execute(array(':id_item' => $id_item));
        $item = $query->fetch();
        
        if (!$item) {
            throw new Exception("Item no encontrado");
        }
        
        // Verificar stock
        if ($item->stock < $nueva_cantidad) {
            throw new Exception("Stock insuficiente. Máximo disponible: {$item->stock}");
        }
        
        // Recalcular subtotal
        $subtotal = ($item->precio_unitario - $item->descuento_unitario) * $nueva_cantidad;
        
        // Actualizar item
        $sql = "UPDATE carrito_items 
                SET cantidad = :cantidad, subtotal = :subtotal, fecha_actualizacion = CURRENT_TIMESTAMP
                WHERE id = :id_item";
        
        $query = $this->acceso->prepare($sql);
        $resultado = $query->execute(array(
            ':cantidad' => $nueva_cantidad,
            ':subtotal' => $subtotal,
            ':id_item' => $id_item
        ));
        
        if ($resultado) {
            $this->acceso->commit();
            return true;
        } else {
            throw new Exception("Error al actualizar cantidad");
        }
    }

    function actualizar_cantidad($id_carrito_detalle, $nueva_cantidad) {
        return $this->actualizar_cantidad_item($id_carrito_detalle, $nueva_cantidad);
    }

    function eliminar_del_carrito($id_carrito_detalle, $id_usuario) {
        try {
            // Verificar que el item pertenece al usuario
            $sql_verificar = "SELECT ci.id 
                             FROM carrito_items ci
                             JOIN carrrito c ON ci.id_carrito = c.id
                             WHERE ci.id = :id_item
                             AND c.id_usuario = :id_usuario";
            $query = $this->acceso->prepare($sql_verificar);
            $query->execute(array(
                ':id_item' => $id_carrito_detalle,
                ':id_usuario' => $id_usuario
            ));
            
            if (!$query->fetch()) {
                throw new Exception("Item no encontrado en el carrito del usuario");
            }

            // Eliminar item
            $sql_eliminar = "DELETE FROM carrito_items WHERE id = :id_item";
            $query = $this->acceso->prepare($sql_eliminar);
            $resultado = $query->execute(array(':id_item' => $id_carrito_detalle));

            return $resultado;

        } catch (Exception $e) {
            error_log("Error eliminando del carrito: " . $e->getMessage());
            return false;
        }
    }

    function vaciar_carrito($id_usuario) {
        try {
            // Obtener el carrito activo del usuario
            $sql_carrito = "SELECT id FROM carrrito WHERE id_usuario = :id_usuario AND estado = 'activo'";
            $query = $this->acceso->prepare($sql_carrito);
            $query->execute(array(':id_usuario' => $id_usuario));
            $carrito = $query->fetch();
            
            if ($carrito) {
                // Eliminar todos los items del carrito
                $sql_eliminar = "DELETE FROM carrito_items WHERE id_carrito = :id_carrito";
                $query = $this->acceso->prepare($sql_eliminar);
                $resultado = $query->execute(array(':id_carrito' => $carrito->id));
                return $resultado;
            }
            
            return true;

        } catch (Exception $e) {
            error_log("Error vaciando carrito: " . $e->getMessage());
            return false;
        }
    }

    function obtener_resumen_carrito($id_usuario) {
        $sql = "SELECT 
                    COUNT(ci.id) as total_items,
                    SUM(ci.cantidad) as total_cantidad,
                    SUM(ci.subtotal) as subtotal,
                    SUM(ci.descuento_unitario * ci.cantidad) as descuento_total,
                    SUM(ci.subtotal) as total
                FROM carrrito c
                JOIN carrito_items ci ON c.id = ci.id_carrito
                WHERE c.id_usuario = :id_usuario
                AND c.estado = 'activo'";
                
        $query = $this->acceso->prepare($sql);
        $query->execute(array(':id_usuario' => $id_usuario));
        $resumen = $query->fetch();
        
        return $resumen ?: (object)[
            'total_items' => 0,
            'total_cantidad' => 0,
            'subtotal' => 0,
            'descuento_total' => 0,
            'total' => 0
        ];
    }

    function obtener_cantidad_total($id_usuario) {
        $sql = "SELECT SUM(ci.cantidad) as total_items
                FROM carrrito c
                JOIN carrito_items ci ON c.id = ci.id_carrito
                WHERE c.id_usuario = :id_usuario
                AND c.estado = 'activo'";
                
        $query = $this->acceso->prepare($sql);
        $query->execute(array(':id_usuario' => $id_usuario));
        $resultado = $query->fetch();
        
        return $resultado ? $resultado->total_items : 0;
    }
}