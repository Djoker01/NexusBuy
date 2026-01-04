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

    function obtener_carrito($id_usuario)
    {
        $sql = "SELECT c.id,
        c.cantidad as cantidad_producto,
        pt.precio as precio_unitario,
        (pt.precio * pt.descuento_porcentaje / 100) as descuento_unitario,
        (pt.precio - (pt.precio * pt.descuento_porcentaje / 100)) as precio_final,
        c.fecha_agregado,
        pt.id as id_producto_tienda,
        p.nombre,
        pi.imagen_url as imagen,
        p.descripcion_corta as detalles,
        t.nombre as tienda_nombre,
        m.nombre as marca_nombre,
        pt.stock as stock_disponible,
        pt.descuento_porcentaje,
        pt.costo_envio as costo_envio,
        pt.envio_gratis = 1 as envio_gratis,
        p.id as id_producto
        FROM carrito c
        JOIN producto_tienda pt ON c.id_producto_tienda = pt.id
        JOIN producto p ON pt.id_producto = p.id
        LEFT JOIN producto_imagen pi ON p.id = pi.id_producto AND pi.es_principal = 1
        JOIN tienda t ON pt.id_tienda = t.id
        LEFT JOIN marca m ON p.id_marca = m.id
        WHERE c.id_usuario = :id_usuario
        AND pt.estado = 'activo'
        AND p.estado = 'activo'";

        $query = $this->acceso->prepare($sql);
        $query->execute(array(':id_usuario' => $id_usuario));
        $this->objetos = $query->fetchAll();
        return $this->objetos;
    }

    function agregar_al_carrito($id_usuario, $id_producto_tienda, $cantidad = 1)
{
    $this->acceso->beginTransaction();
    try {
        // Verificar si el producto ya está en el carrito
        $sql_verificar = "SELECT id, cantidad FROM carrito
                          WHERE id_usuario = :id_usuario
                          AND id_producto_tienda = :id_producto_tienda";
        $query = $this->acceso->prepare($sql_verificar);
        $query->execute(array(
            ':id_usuario' => $id_usuario,
            ':id_producto_tienda' => $id_producto_tienda
        ));
        $existente = $query->fetch();

        if ($existente) {
            // Actualizar cantidad existente
            $nuevaCantidad = $existente->cantidad + $cantidad;
            $resultado = $this->actualizar_cantidad($existente->id, $nuevaCantidad);
        } else {
            // Verificar stock y obtener precio
            $sql_producto = "SELECT precio, descuento_porcentaje, stock
                            FROM producto_tienda
                            WHERE id = :id_producto_tienda
                            AND estado = 'activo'";
            $query = $this->acceso->prepare($sql_producto);
            $query->execute(array(':id_producto_tienda' => $id_producto_tienda));
            $producto = $query->fetch();

            if (!$producto) {
                throw new Exception("Producto no disponible");
            }
            if ($producto->stock < $cantidad) {
                throw new Exception("Stock insuficiente. Disponible: {$producto->stock}");
            }

            // Insertar nuevo item
            $sql_insert = "INSERT INTO carrito (id_usuario, id_producto_tienda, cantidad, fecha_agregado)
                           VALUES (:id_usuario, :id_producto_tienda, :cantidad, NOW())";
            
            $query = $this->acceso->prepare($sql_insert);
            $resultado = $query->execute(array(
                ':id_usuario' => $id_usuario,
                ':id_producto_tienda' => $id_producto_tienda,
                ':cantidad' => $cantidad
            ));
        }

        if ($resultado) {
            $this->acceso->commit();
            return true;
        } else {
            $this->acceso->rollBack();
            return false;
        }
        
    } catch (Exception $e) {
        $this->acceso->rollBack();
        error_log("Error agregar_al_carrito: " . $e->getMessage());
        throw $e;
    }
}

    function actualizar_cantidad($id_carrito, $nueva_cantidad)
    {
        try {
        $sql_stock = "SELECT pt.stock
                    FROM carrito c 
                    JOIN producto_tienda pt ON c.id_producto_tienda = pt.id
                    WHERE c.id = :id_carrito";
        $query = $this->acceso->prepare($sql_stock);
        $query->execute(array(':id_carrito' => $id_carrito));
        $stock = $query->fetch();

        if (!$stock) {
            throw new Exception("Item no encontrado");
        }

        if ($stock->stock < $nueva_cantidad) {
            throw new Exception("Stock insuficiente. Máximo disponible: {$stock->stock}");
        }

        if ($nueva_cantidad < 1) {
            throw new Exception("La cantidad debe ser al menos 1");
        }

        $sql_update = "UPDATE carrito
                    SET cantidad = :cantidad, 
                        fecha_actualizacion = NOW() 
                    WHERE id = :id_carrito";
        $query = $this->acceso->prepare($sql_update);
        $resultado = $query->execute(array(
            ':cantidad' => $nueva_cantidad,
            ':id_carrito' => $id_carrito
        ));
        
        return $resultado;
    } catch (Exception $e) {
        error_log("Error actualizar_cantidad: " . $e->getMessage());
        throw $e;
    }
    }

    function eliminar_del_carrito($id_carrito, $id_usuario)
    {
        try {
            // Verificar que el item pertenece al usuario
            $sql_verificar = "SELECT id 
                             FROM carrito
                             WHERE id = :id_carrito
                             AND id_usuario = :id_usuario";
            $query = $this->acceso->prepare($sql_verificar);
            $query->execute(array(
                ':id_carrito' => $id_carrito,
                ':id_usuario' => $id_usuario
            ));

            if (!$query->fetch()) {
                throw new Exception("Item no encontrado en el carrito del usuario");
            }

            // Eliminar item
            $sql_eliminar = "DELETE FROM carrito WHERE id = :id_carrito";
            $query = $this->acceso->prepare($sql_eliminar);
            $resultado = $query->execute(array(':id_carrito' => $id_carrito));

            return $resultado;
        } catch (Exception $e) {
            error_log("Error eliminando del carrito: " . $e->getMessage());
            return false;
        }
    }

    function vaciar_carrito($id_usuario)
    {
        try {
            // Obtener el carrito activo del usuario
            $sql_eliminar = "DELETE FROM carrito WHERE id_usuario = :id_usuario";
            $query = $this->acceso->prepare($sql_eliminar);
            $resultado = $query->execute(array(':id_usuario' => $id_usuario));
            return $resultado;
        } catch (Exception $e) {
            error_log("Error vaciando carrito: " . $e->getMessage());
            return false;
        }
    }

    function obtener_resumen_carrito($id_usuario)
    {
        $sql = "SELECT 
                    COUNT(c.id) as total_items,
                    SUM(c.cantidad) as total_cantidad,
                    SUM((pt.precio - (pt.precio * pt.descuento_porcentaje / 100)) * c.cantidad) as subtotal,
                    SUM((pt.precio * pt.descuento_porcentaje / 100) * c.cantidad) as descuento_total,
                    SUM((pt.precio - (pt.precio * pt.descuento_porcentaje / 100)) * c.cantidad) as total
                FROM carrito c
                JOIN producto_tienda pt ON c.id_producto_tienda = pt.id
                WHERE c.id_usuario = :id_usuario";

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

    function obtener_cantidad_total($id_usuario)
    {
        $sql = "SELECT SUM(cantidad) as total_items
                FROM carrito 
                WHERE id_usuario = :id_usuario";

        $query = $this->acceso->prepare($sql);
        $query->execute(array(':id_usuario' => $id_usuario));
        $resultado = $query->fetch();

        return $resultado ? $resultado->total_items : 0;
    }
}
