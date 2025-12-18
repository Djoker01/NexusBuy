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
            // 1. CREAR LA ORDEN USANDO LA ESTRUCTURA REAL DE TU BD
            // Primero obtenemos o creamos una dirección para el usuario
            $sql_usuario = "SELECT telefono FROM usuario WHERE id = :id_usuario";
            $query_usuario = $this->acceso->prepare($sql_usuario);
            $query_usuario->execute([':id_usuario' => $id_usuario]);
            $usuario = $query_usuario->fetch();
            
            if (!$usuario) {
                throw new Exception("Usuario no encontrado");
            }
            
            // 2. Verificar si el usuario ya tiene una dirección
            $sql_direccion_existente = "SELECT id FROM usuario_direccion 
                                       WHERE id_usuario = :id_usuario 
                                       LIMIT 1";
            $query_dir = $this->acceso->prepare($sql_direccion_existente);
            $query_dir->execute([':id_usuario' => $id_usuario]);
            $direccion_existente = $query_dir->fetch();
            
            if ($direccion_existente) {
                // Usar dirección existente
                $id_direccion_envio = $direccion_existente->id;
                
                // Actualizar la dirección existente con la nueva información
                $sql_update_dir = "UPDATE usuario_direccion 
                                  SET direccion = :direccion, 
                                      telefono_contacto = :telefono 
                                  WHERE id = :id_direccion";
                $query_update = $this->acceso->prepare($sql_update_dir);
                $query_update->execute([
                    ':direccion' => $direccion_envio,
                    ':telefono' => $usuario->telefono,
                    ':id_direccion' => $id_direccion_envio
                ]);
            } else {
                // Crear nueva dirección
                $sql_insert_dir = "INSERT INTO usuario_direccion 
                                  (id_usuario, alias, direccion, id_municipio, 
                                   telefono_contacto, es_principal, estado)
                                  VALUES (:id_usuario, 'Dirección Principal', :direccion, 
                                  (SELECT id FROM municipio LIMIT 1), :telefono, 1, 'activa')";
                
                $query_insert = $this->acceso->prepare($sql_insert_dir);
                $query_insert->execute([
                    ':id_usuario' => $id_usuario,
                    ':direccion' => $direccion_envio,
                    ':telefono' => $usuario->telefono
                ]);
                
                $id_direccion_envio = $this->acceso->lastInsertId();
            }
            
            // 3. CREAR LA ORDEN
            $sql = "INSERT INTO orden (numero_orden, id_usuario, id_direccion_envio, 
                    subtotal, costo_envio, descuento, total, notas_cliente, estado, fecha_creacion) 
                    VALUES (:numero_orden, :id_usuario, :id_direccion_envio, 
                    :subtotal, :costo_envio, :descuento, :total, :notas_cliente, 'pendiente', NOW())";
            
            $query = $this->acceso->prepare($sql);
            $query->execute(array(
                ':numero_orden' => $numero_orden,
                ':id_usuario' => $id_usuario,
                ':id_direccion_envio' => $id_direccion_envio,
                ':subtotal' => $subtotal,
                ':costo_envio' => $envio,
                ':descuento' => $descuento,
                ':total' => $total,
                ':notas_cliente' => $notas
            ));
            
            $id_orden = $this->acceso->lastInsertId();
            $this->acceso->commit();
            
            return array('success' => true, 'id_orden' => $id_orden, 'numero_orden' => $numero_orden);
            
        } catch (Exception $e) {
            $this->acceso->rollBack();
            return array('success' => false, 'error' => $e->getMessage());
        }
    }

    function agregar_detalle_orden($id_orden, $id_producto_tienda, $cantidad, $precio_unitario, $descuento_unitario, $subtotal) {
        $sql = "INSERT INTO orden_detalle (id_orden, id_producto_tienda, cantidad, precio_unitario, descuento_unitario, subtotal) 
                VALUES (:id_orden, :id_producto_tienda, :cantidad, :precio_unitario, :descuento_unitario, :subtotal)";
        
        $query = $this->acceso->prepare($sql);
        return $query->execute(array(
            ':id_orden' => $id_orden,
            ':id_producto_tienda' => $id_producto_tienda,
            ':cantidad' => $cantidad,
            ':precio_unitario' => $precio_unitario,
            ':descuento_unitario' => $descuento_unitario,
            ':subtotal' => $subtotal
        ));
    }

    function actualizar_stock($id_producto_tienda, $cantidad_vendida) {
        $sql = "UPDATE producto_tienda 
                SET stock = stock - :cantidad_vendida,
                    total_ventas = total_ventas + :cantidad_vendida,
                    fecha_actualizacion = NOW()
                WHERE id = :id_producto_tienda 
                AND stock >= :cantidad_vendida";
        
        $query = $this->acceso->prepare($sql);
        return $query->execute(array(
            ':id_producto_tienda' => $id_producto_tienda,
            ':cantidad_vendida' => $cantidad_vendida
        ));
    }
}