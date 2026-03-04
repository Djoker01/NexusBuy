<?php
include_once 'Conexion.php';

class Orden {
    var $objetos;
    var $acceso;
    
    public function __construct() {
        $db = new Conexion();
        $this->acceso = $db->pdo;
    }

    // FUNCIÓN ACTUALIZADA: Ahora acepta $metodo_pago_codigo
    function crear_orden($id_usuario, $subtotal, $envio, $descuento, $total, $direccion_envio, $metodo_pago_codigo = 'efectivo', $notas = null) {
        $numero_orden = 'ORD-' . date('Ymd') . '-' . strtoupper(uniqid());
        
        $this->acceso->beginTransaction();
        try {
            // ======= CÓDIGO ORIGINAL (OBTENER USUARIO) =======
            $sql_usuario = "SELECT telefono FROM usuario WHERE id = :id_usuario";
            $query_usuario = $this->acceso->prepare($sql_usuario);
            $query_usuario->execute([':id_usuario' => $id_usuario]);
            $usuario = $query_usuario->fetch();
            
            if (!$usuario) {
                throw new Exception("Usuario no encontrado");
            }
            
            // ======= CÓDIGO ORIGINAL (MANEJAR DIRECCIÓN) =======
            $sql_direccion_existente = "SELECT id FROM usuario_direccion 
                                       WHERE id_usuario = :id_usuario 
                                       LIMIT 1";
            $query_dir = $this->acceso->prepare($sql_direccion_existente);
            $query_dir->execute([':id_usuario' => $id_usuario]);
            $direccion_existente = $query_dir->fetch();
            
            if ($direccion_existente) {
                $id_direccion_envio = $direccion_existente->id;
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
            
            // ======= MODIFICACIÓN CLAVE: GENERAR REFERENCIA SI ES TRANSFERMÓVIL =======
            $referencia_pago = null;
            if ($metodo_pago_codigo === 'transfermovil') {
                $referencia_pago = $this->generarReferenciaPago();
            }
            
            // ======= MODIFICACIÓN CLAVE: INSERT CON CAMPOS DE PAGO =======
            $sql = "INSERT INTO orden (
                        numero_orden, 
                        id_usuario, 
                        id_direccion_envio, 
                        subtotal, 
                        costo_envio,
                        impuestos,
                        descuento, 
                        total, 
                        metodo_pago_codigo,  -- NUEVO CAMPO
                        referencia_pago,     -- NUEVO CAMPO
                        estado_pago,         -- NUEVO CAMPO
                        notas_cliente, 
                        estado, 
                        fecha_creacion
                    ) VALUES (
                        :numero_orden, 
                        :id_usuario, 
                        :id_direccion_envio, 
                        :subtotal, 
                        :costo_envio,
                        10.00,
                        :descuento, 
                        :total,
                        :metodo_pago_codigo, -- NUEVO PARÁMETRO
                        :referencia_pago,    -- NUEVO PARÁMETRO
                        'pendiente',         -- VALOR FIJO PARA PAGOS MANUALES
                        :notas_cliente, 
                        'pendiente', 
                        NOW()
                    )";
            
            $query = $this->acceso->prepare($sql);
            $query->execute(array(
                ':numero_orden' => $numero_orden,
                ':id_usuario' => $id_usuario,
                ':id_direccion_envio' => $id_direccion_envio,
                ':subtotal' => $subtotal,
                ':costo_envio' => $envio,
                ':total' => $total,
                ':descuento' => $descuento,
                ':metodo_pago_codigo' => $metodo_pago_codigo, // <-- NUEVO
                ':referencia_pago' => $referencia_pago,       // <-- NUEVO
                ':notas_cliente' => $notas
            ));
            
            $id_orden = $this->acceso->lastInsertId();
            $this->acceso->commit();
            
            return array(
                'success' => true, 
                'id_orden' => $id_orden, 
                'numero_orden' => $numero_orden,
                'referencia_pago' => $referencia_pago  // <-- DEVOLVEMOS LA REFERENCIA
            );
            
        } catch (Exception $e) {
            $this->acceso->rollBack();
            return array('success' => false, 'error' => $e->getMessage());
        }
    }

    // ======= NUEVA FUNCIÓN: GENERAR REFERENCIA ÚNICA =======
    private function generarReferenciaPago() {
        $prefijo = 'NX'; // Puedes obtener esto de configuracion_sitio
        $timestamp = time();
        $random = substr(strtoupper(md5(uniqid())), 0, 6);
        return $prefijo . '-' . date('Ymd') . '-' . $random;
    }

    // ======= FUNCIONES ORIGINALES (SIN CAMBIOS) =======
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
?>