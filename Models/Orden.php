<?php
include_once 'Conexion.php';
class Orden {
    var $objetos;
    var $acceso;
    
    public function __construct() {
        $db = new Conexion();
        $this->acceso = $db->pdo;
    }

    function crear_orden($id_usuario, $subtotal, $envio, $descuento, $total, $direccion_envio, $notas = null) {
        $numero_orden = 'ORD-' . date('Ymd') . '-' . strtoupper(uniqid());
        
        $this->acceso->beginTransaction();
        try {
            $sql = "INSERT INTO orden (numero_orden, id_usuario, subtotal, envio, descuento, total, direccion_envio, notas) 
                    VALUES (:numero_orden, :id_usuario, :subtotal, :envio, :descuento, :total, :direccion_envio, :notas)";
            
            $query = $this->acceso->prepare($sql);
            $query->execute(array(
                ':numero_orden' => $numero_orden,
                ':id_usuario' => $id_usuario,
                ':subtotal' => $subtotal,
                ':envio' => $envio,
                ':descuento' => $descuento,
                ':total' => $total,
                ':direccion_envio' => $direccion_envio,
                ':notas' => $notas
            ));
            
            $id_orden = $this->acceso->lastInsertId();
            $this->acceso->commit();
            
            return array('success' => true, 'id_orden' => $id_orden, 'numero_orden' => $numero_orden);
            
        } catch (Exception $e) {
            $this->acceso->rollBack();
            return array('success' => false, 'error' => $e->getMessage());
        }
    }

    function agregar_detalle_orden($id_orden, $id_producto_tienda, $cantidad, $precio_unitario, $descuento, $subtotal) {
        $sql = "INSERT INTO orden_detalle (id_orden, id_producto_tienda, cantidad, precio_unitario, descuento, subtotal) 
                VALUES (:id_orden, :id_producto_tienda, :cantidad, :precio_unitario, :descuento, :subtotal)";
        
        $query = $this->acceso->prepare($sql);
        return $query->execute(array(
            ':id_orden' => $id_orden,
            ':id_producto_tienda' => $id_producto_tienda,
            ':cantidad' => $cantidad,
            ':precio_unitario' => $precio_unitario,
            ':descuento' => $descuento,
            ':subtotal' => $subtotal
        ));
    }

    function actualizar_stock($id_producto_tienda, $cantidad_vendida) {
        $sql = "UPDATE producto_tienda 
                SET cantidad = cantidad - :cantidad_vendida 
                WHERE id = :id_producto_tienda 
                AND cantidad >= :cantidad_vendida";
        
        $query = $this->acceso->prepare($sql);
        return $query->execute(array(
            ':id_producto_tienda' => $id_producto_tienda,
            ':cantidad_vendida' => $cantidad_vendida
        ));
    }
}
