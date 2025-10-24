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
        // Obtener pedidos del usuario
        $sql = "SELECT o.*, 
                       COUNT(od.id) as total_productos,
                       SUM(od.subtotal) as subtotal_productos
                FROM orden o 
                LEFT JOIN orden_detalle od ON o.id = od.id_orden 
                WHERE o.id_usuario = :id_usuario 
                GROUP BY o.id 
                ORDER BY o.fecha_creacion DESC";
        
        $query = $orden->acceso->prepare($sql);
        $query->execute([':id_usuario' => $id_usuario]);
        $pedidos = $query->fetchAll();
        
        $pedidos_formateados = [];
        
        foreach ($pedidos as $pedido) {
            // Obtener detalles del pedido
            $sql_detalles = "SELECT od.*, p.nombre as producto_nombre, p.imagen_principal as imagen
                            FROM orden_detalle od
                            JOIN producto_tienda pt ON od.id_producto_tienda = pt.id
                            JOIN producto p ON pt.id_producto = p.id
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
                case 'confirmado':
                    $estado_clase = 'estado-confirmado';
                    $estado_texto = 'Confirmado';
                    break;
                case 'enviado':
                    $estado_clase = 'estado-enviado';
                    $estado_texto = 'Enviado';
                    break;
                case 'entregado':
                    $estado_clase = 'estado-entregado';
                    $estado_texto = 'Entregado';
                    break;
                case 'cancelado':
                    $estado_clase = 'estado-cancelado';
                    $estado_texto = 'Cancelado';
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
                'estado' => $pedido->estado,
                'estado_clase' => $estado_clase,
                'estado_texto' => $estado_texto,
                'total_productos' => $pedido->total_productos,
                'subtotal_productos' => floatval($pedido->subtotal_productos),
                'direccion_envio' => $pedido->direccion_envio,
                'detalles' => $detalles
            ];
        }
        
        // Aplicar filtro si existe
        if ($filtro_estado && $filtro_estado != '') {
            $pedidos_formateados = array_filter($pedidos_formateados, function($pedido) use ($filtro_estado) {
                return $pedido['estado'] == $filtro_estado;
            });
            $pedidos_formateados = array_values($pedidos_formateados); // Reindexar
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
        $sql_verificar = "SELECT id FROM orden WHERE id = :id_pedido AND id_usuario = :id_usuario";
        $query = $orden->acceso->prepare($sql_verificar);
        $query->execute([':id_pedido' => $id_pedido, ':id_usuario' => $id_usuario]);
        
        if (!$query->fetch()) {
            echo json_encode(['error' => 'Pedido no encontrado']);
            exit();
        }
        
        // Obtener información completa del pedido
        $sql_pedido = "SELECT o.*, 
                              ed.empresa_envio, 
                              ed.tipo_envio, 
                              ed.costo_envio,
                              ed.tiempo_estimado
                       FROM orden o
                       LEFT JOIN envio_detalles ed ON o.id = ed.id_orden
                       WHERE o.id = :id_pedido";
        
        $query = $orden->acceso->prepare($sql_pedido);
        $query->execute([':id_pedido' => $id_pedido]);
        $pedido = $query->fetch();
        
        // Obtener detalles de los productos
        $sql_detalles = "SELECT od.*, 
                                p.nombre as producto_nombre,
                                p.imagen_principal as imagen,
                                m.nombre as marca_nombre,
                                t.nombre as tienda_nombre
                         FROM orden_detalle od
                         JOIN producto_tienda pt ON od.id_producto_tienda = pt.id
                         JOIN producto p ON pt.id_producto = p.id
                         JOIN marca m ON p.id_marca = m.id
                         JOIN tienda t ON pt.id_tienda = t.id
                         WHERE od.id_orden = :id_orden";
        
        $query_detalles = $orden->acceso->prepare($sql_detalles);
        $query_detalles->execute([':id_orden' => $id_pedido]);
        $detalles = $query_detalles->fetchAll();
        
        echo json_encode([
            'success' => true,
            'pedido' => $pedido,
            'detalles' => $detalles
        ]);
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'error' => 'Error al cargar los detalles del pedido'
        ]);
    }
}