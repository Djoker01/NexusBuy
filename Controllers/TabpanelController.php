<?php
include_once '../Models/Historial.php';
include_once '../Models/Orden.php';
include_once '../Models/Reseña.php';
include_once '../Models/UsuarioMunicipio.php';
include_once '../Util/Config/config.php';

$historial = new Historial();
$orden = new Orden();
$reseña = new Reseña();
$usuario_municipio = new UsuarioMunicipio();

session_start();

if ($_POST['funcion'] == 'obtener_actividad_usuario') {
    if (empty($_SESSION['id'])) {
        echo json_encode(['error' => 'no_sesion']);
        exit();
    }
    
    $id_usuario = $_SESSION['id'];
    $filtro_tipo = $_POST['filtro_tipo'] ?? '';
    $filtro_periodo = intval($_POST['filtro_periodo'] ?? 30);
    
    try {
        $actividades = [];
        
        // 1. Obtener historial del sistema
        $historial->llenar_historial($id_usuario);
        foreach ($historial->objetos as $item) {
            $actividades[] = [
                'tipo' => 'sistema',
                'subtipo' => $item->tipo_historial,
                'fecha' => $item->fecha,
                'descripcion' => $item->descripcion,
                'icono' => $item->th_icono,
                'modulo' => $item->modulo,
                'modulo_icono' => $item->m_icono
            ];
        }
        
        // 2. Obtener pedidos del usuario
        $sql_pedidos = "SELECT * FROM orden 
                       WHERE id_usuario = :id_usuario 
                       ORDER BY fecha_creacion DESC 
                       LIMIT 50";
        $query = $orden->acceso->prepare($sql_pedidos);
        $query->execute([':id_usuario' => $id_usuario]);
        $pedidos = $query->fetchAll();
        
        foreach ($pedidos as $pedido) {
            $actividades[] = [
                'tipo' => 'compra',
                'subtipo' => 'pedido',
                'fecha' => $pedido->fecha_creacion,
                'descripcion' => "Realizó un pedido #{$pedido->numero_orden} por $ {$pedido->total}",
                'icono' => 'fas fa-shopping-cart',
                'detalles' => [
                    'numero_orden' => $pedido->numero_orden,
                    'total' => $pedido->total,
                    'estado' => $pedido->estado
                ]
            ];
        }
        
        // 3. Obtener reseñas del usuario
        $sql_resenas = "SELECT r.*, p.nombre as producto_nombre 
                       FROM reseña r
                       JOIN producto_tienda pt ON r.id_producto_tienda = pt.id
                       JOIN producto p ON pt.id_producto = p.id
                       WHERE r.id_usuario = :id_usuario 
                       AND r.estado = 'A'
                       ORDER BY r.fecha_creacion DESC 
                       LIMIT 50";
        $query = $reseña->acceso->prepare($sql_resenas);
        $query->execute([':id_usuario' => $id_usuario]);
        $resenas = $query->fetchAll();
        
        foreach ($resenas as $resena) {
            $actividades[] = [
                'tipo' => 'reseña',
                'subtipo' => 'reseña_producto',
                'fecha' => $resena->fecha_creacion,
                'descripcion' => "Reseñó el producto '{$resena->producto_nombre}' con {$resena->calificacion} estrellas",
                'icono' => 'fas fa-star',
                'detalles' => [
                    'producto' => $resena->producto_nombre,
                    'calificacion' => $resena->calificacion,
                    'comentario' => $resena->descripcion
                ]
            ];
        }
        
        // 4. Obtener direcciones del usuario
        $usuario_municipio->llenar_direcciones($id_usuario);
        foreach ($usuario_municipio->objetos as $direccion) {
            $actividades[] = [
                'tipo' => 'direccion',
                'subtipo' => 'direccion_agregada',
                'fecha' => date('Y-m-d H:i:s'), // En un sistema real, esto vendría de la BD
                'descripcion' => "Agregó la dirección: {$direccion->direccion}, {$direccion->municipio}",
                'icono' => 'fas fa-map-marker-alt',
                'detalles' => [
                    'direccion' => $direccion->direccion,
                    'municipio' => $direccion->municipio,
                    'provincia' => $direccion->provincia
                ]
            ];
        }
        
        // Ordenar actividades por fecha (más reciente primero)
        usort($actividades, function($a, $b) {
            return strtotime($b['fecha']) - strtotime($a['fecha']);
        });
        
        // Aplicar filtros
        if ($filtro_tipo && $filtro_tipo != '') {
            $actividades = array_filter($actividades, function($actividad) use ($filtro_tipo) {
                return $actividad['tipo'] == $filtro_tipo;
            });
        }
        
        if ($filtro_periodo > 0) {
            $fecha_limite = date('Y-m-d H:i:s', strtotime("-$filtro_periodo days"));
            $actividades = array_filter($actividades, function($actividad) use ($fecha_limite) {
                return $actividad['fecha'] >= $fecha_limite;
            });
        }
        
        // Limitar a 50 actividades
        $actividades = array_slice($actividades, 0, 50);
        
        echo json_encode([
            'success' => true,
            'actividades' => $actividades,
            'estadisticas' => [
                'total_pedidos' => count($pedidos),
                'total_resenas' => count($resenas),
                'total_actualizaciones' => count(array_filter($actividades, function($a) {
                    return $a['tipo'] == 'sistema' && strpos($a['descripcion'], 'editó') !== false;
                })),
                'total_direcciones' => count($usuario_municipio->objetos)
            ]
        ]);
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'error' => 'Error al cargar la actividad: ' . $e->getMessage()
        ]);
    }
}