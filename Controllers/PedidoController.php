<?php
include_once '../Models/Orden.php';
include_once '../Models/OrdenDetalle.php';
include_once '../Util/Config/config.php';

$orden = new Orden();
$ordenDetalle = new OrdenDetalle();

session_start();

if ($_POST['funcion'] == 'obtener_pedidos_usuario') {
    if (empty($_SESSION['id'])) {
        echo json_encode(['error' => 'no_sesion']);
        exit();
    }
    
    $id_usuario = $_SESSION['id'];
    $filtro_estado = $_POST['filtro_estado'] ?? '';
    
    try {
        // Obtener pedidos del usuario con dirección y conteo de productos
        $sql = "SELECT 
                    o.*,
                    ud.direccion,
                    ud.telefono_contacto,
                    COUNT(od.id) as total_productos,
                    SUM(od.cantidad) as total_cantidad,
                    SUM(od.subtotal) as subtotal_productos
                FROM orden o 
                JOIN usuario_direccion ud ON o.id_direccion_envio = ud.id
                LEFT JOIN orden_detalle od ON o.id = od.id_orden 
                WHERE o.id_usuario = :id_usuario 
                GROUP BY o.id
                ORDER BY o.fecha_creacion DESC";
        
        $query = $orden->acceso->prepare($sql);
        $query->execute([':id_usuario' => $id_usuario]);
        $pedidos = $query->fetchAll();
        
        $pedidos_formateados = [];
        
        foreach ($pedidos as $pedido) {
            // Obtener detalles del pedido con todas las uniones necesarias
            $sql_detalles = "SELECT 
                                od.id,
                                od.id_orden,
                                od.id_producto_tienda,
                                od.cantidad,
                                od.precio_unitario,
                                od.descuento_unitario,
                                od.subtotal,
                                p.id as id_producto,
                                p.nombre as producto_nombre,
                                p.descripcion_corta as producto_descripcion,
                                COALESCE(pi.imagen_url, 'default_product.png') as imagen,
                                m.nombre as marca_nombre,
                                t.nombre as tienda_nombre,
                                pt.sku_tienda,
                                pt.descuento_porcentaje as descuento_producto,
                                pt.precio as precio_original,
                                pt.precio_final as precio_final_producto
                            FROM orden_detalle od
                            JOIN producto_tienda pt ON od.id_producto_tienda = pt.id
                            JOIN producto p ON pt.id_producto = p.id
                            LEFT JOIN producto_imagen pi ON p.id = pi.id_producto AND pi.es_principal = 1
                            LEFT JOIN marca m ON p.id_marca = m.id
                            LEFT JOIN tienda t ON pt.id_tienda = t.id
                            WHERE od.id_orden = :id_orden";
            
            $query_detalles = $orden->acceso->prepare($sql_detalles);
            $query_detalles->execute([':id_orden' => $pedido->id]);
            $detalles = $query_detalles->fetchAll();
            
            // Formatear estado
            $estado_clase = '';
            $estado_texto = '';
            
            switch ($pedido->estado) {
                case 'pendiente':
                    $estado_clase = 'estado-pendiente';
                    $estado_texto = 'Pendiente';
                    break;
                case 'confirmada':
                    $estado_clase = 'estado-confirmado';
                    $estado_texto = 'Confirmada';
                    break;
                case 'procesando':
                    $estado_clase = 'estado-enviado';
                    $estado_texto = 'Procesando';
                    break;
                case 'enviada':
                    $estado_clase = 'estado-enviado';
                    $estado_texto = 'Enviada';
                    break;
                case 'entregada':
                    $estado_clase = 'estado-entregado';
                    $estado_texto = 'Entregada';
                    break;
                case 'cancelada':
                    $estado_clase = 'estado-cancelado';
                    $estado_texto = 'Cancelada';
                    break;
                case 'reembolsada':
                    $estado_clase = 'estado-reembolsado';
                    $estado_texto = 'Reembolsada';
                    break;
                default:
                    $estado_clase = 'estado-pendiente';
                    $estado_texto = 'Pendiente';
            }
            
            $pedidos_formateados[] = [
                'id' => $pedido->id,
                'numero_orden' => $pedido->numero_orden,
                'fecha_creacion' => $pedido->fecha_creacion,
                'total' => floatval($pedido->total),
                'subtotal' => floatval($pedido->subtotal),
                'descuento' => floatval($pedido->descuento),
                'costo_envio' => floatval($pedido->costo_envio),
                'impuestos' => floatval($pedido->impuestos),
                'estado' => $pedido->estado,
                'estado_clase' => $estado_clase,
                'estado_texto' => $estado_texto,
                'total_productos' => intval($pedido->total_productos),
                'total_cantidad' => intval($pedido->total_cantidad),
                'subtotal_productos' => floatval($pedido->subtotal_productos),
                'direccion_envio' => $pedido->direccion,
                'telefono_contacto' => $pedido->telefono_contacto,
                'codigo_seguimiento' => $pedido->codigo_seguimiento,
                'fecha_entrega_estimada' => $pedido->fecha_entrega_estimada,
                'fecha_entrega_real' => $pedido->fecha_entrega_real,
                'detalles' => $detalles
            ];
        }
        
        // Aplicar filtro si existe
        if ($filtro_estado && $filtro_estado != '') {
            $pedidos_formateados = array_filter($pedidos_formateados, function($pedido) use ($filtro_estado) {
                return $pedido['estado'] == $filtro_estado;
            });
            $pedidos_formateados = array_values($pedidos_formateados);
        }
        
        echo json_encode([
            'success' => true,
            'pedidos' => $pedidos_formateados
        ]);
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'error' => 'Error al cargar los pedidos: ' . $e->getMessage()
        ]);
    }
}

if ($_POST['funcion'] == 'obtener_detalles_pedido') {
    if (empty($_SESSION['id'])) {
        echo json_encode(['error' => 'no_sesion']);
        exit();
    }
    
    $id_pedido = $_POST['id_pedido'];
    $id_usuario = $_SESSION['id'];
    
    try {
        // Verificar que el pedido pertenece al usuario
        $sql_verificar = "SELECT o.id 
                         FROM orden o 
                         WHERE o.id = :id_pedido 
                         AND o.id_usuario = :id_usuario";
        
        $query = $orden->acceso->prepare($sql_verificar);
        $query->execute([':id_pedido' => $id_pedido, ':id_usuario' => $id_usuario]);
        
        if (!$query->fetch()) {
            echo json_encode(['error' => 'Pedido no encontrado']);
            exit();
        }
        
        // Obtener información completa del pedido
        $sql_pedido = "SELECT 
                        o.*,
                        ud.alias as direccion_alias,
                        ud.direccion,
                        ud.telefono_contacto,
                        ud.codigo_postal,
                        mu.nombre as municipio_nombre,
                        pr.nombre as provincia_nombre,
                        mp.nombre as metodo_pago_nombre,
                        mp.icono as metodo_pago_icono
                    FROM orden o
                    JOIN usuario_direccion ud ON o.id_direccion_envio = ud.id
                    LEFT JOIN municipio mu ON ud.id_municipio = mu.id
                    LEFT JOIN provincia pr ON mu.id_provincia = pr.id
                    LEFT JOIN usuario_metodo_pago ump ON o.id_metodo_pago = ump.id
                    LEFT JOIN metodo_pago mp ON ump.id_metodo_pago = mp.id
                    WHERE o.id = :id_pedido";
        
        $query = $orden->acceso->prepare($sql_pedido);
        $query->execute([':id_pedido' => $id_pedido]);
        $pedido = $query->fetch();
        
        if (!$pedido) {
            echo json_encode(['error' => 'Pedido no encontrado']);
            exit();
        }
        
        // Obtener detalles de los productos con todas las tablas necesarias
        $sql_detalles = "SELECT 
                            od.id,
                            od.id_producto_tienda,
                            od.cantidad,
                            od.precio_unitario,
                            od.descuento_unitario,
                            od.subtotal,
                            p.id as id_producto,
                            p.sku,
                            p.nombre as producto_nombre,
                            p.descripcion_corta as producto_descripcion,
                            p.descripcion_larga as producto_descripcion_larga,
                            COALESCE(pi.imagen_url, 'default_product.png') as imagen,
                            m.nombre as marca_nombre,
                            m.logo as marca_logo,
                            t.nombre as tienda_nombre,
                            t.logo as tienda_logo,
                            pt.precio as precio_original,
                            pt.precio_final as precio_final_producto,
                            pt.descuento_porcentaje as descuento_porcentaje,
                            pt.sku_tienda,
                            pt.garantia_meses,
                            pt.tiempo_entrega,
                            pt.envio_gratis,
                            pt.stock,
                            c.nombre as categoria_nombre,
                            sc.nombre as subcategoria_nombre
                        FROM orden_detalle od
                        JOIN producto_tienda pt ON od.id_producto_tienda = pt.id
                        JOIN producto p ON pt.id_producto = p.id
                        LEFT JOIN producto_imagen pi ON p.id = pi.id_producto AND pi.es_principal = 1
                        LEFT JOIN marca m ON p.id_marca = m.id
                        LEFT JOIN tienda t ON pt.id_tienda = t.id
                        LEFT JOIN subcategoria sc ON p.id_subcategoria = sc.id
                        LEFT JOIN categoria c ON sc.id_categoria = c.id
                        WHERE od.id_orden = :id_orden
                        ORDER BY od.id";
        
        $query_detalles = $orden->acceso->prepare($sql_detalles);
        $query_detalles->execute([':id_orden' => $id_pedido]);
        $detalles = $query_detalles->fetchAll();
        
        // Obtener información de transacciones si existen
        $sql_transacciones = "SELECT 
                                tp.*,
                                mp.nombre as metodo_pago_nombre
                             FROM transaccion_pago tp
                             LEFT JOIN metodo_pago mp ON tp.id_metodo_pago = mp.id
                             WHERE tp.id_orden = :id_orden
                             ORDER BY tp.fecha_transaccion DESC";
        
        $query_transacciones = $orden->acceso->prepare($sql_transacciones);
        $query_transacciones->execute([':id_orden' => $id_pedido]);
        $transacciones = $query_transacciones->fetchAll();
        
        echo json_encode([
            'success' => true,
            'pedido' => $pedido,
            'detalles' => $detalles,
            'transacciones' => $transacciones
        ]);
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'error' => 'Error al cargar los detalles del pedido: ' . $e->getMessage()
        ]);
    }
}
?>