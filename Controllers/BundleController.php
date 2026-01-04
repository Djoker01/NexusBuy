<?php
// BundleController.php
include_once '../Models/Conexion.php';
session_start();

// Headers para JSON
header('Content-Type: application/json');

// Verificar método POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit();
}

// Verificar que la función existe
if (!isset($_POST['funcion'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Función no especificada']);
    exit();
}

// Función auxiliar para obtener productos de un bundle
function obtenerProductosBundle($bundle_id) {
    $db = new Conexion();
    $acceso = $db->pdo;
    
    $sql = "SELECT 
                bp.id_producto_tienda,
                pt.id as id_producto_tienda,
                p.nombre,
                p.descripcion_corta,
                pt.precio,
                pt.descuento_porcentaje,
                pt.stock,
                pt.estado,
                pi.imagen_url as imagen,
                t.nombre as tienda_nombre,
                m.nombre as marca_nombre,
                (pt.precio - (pt.precio * pt.descuento_porcentaje / 100)) as precio_final
            FROM bundle_producto bp
            JOIN producto_tienda pt ON bp.id_producto_tienda = pt.id
            JOIN producto p ON pt.id_producto = p.id
            LEFT JOIN producto_imagen pi ON p.id = pi.id_producto AND pi.es_principal = 1
            LEFT JOIN tienda t ON pt.id_tienda = t.id
            LEFT JOIN marca m ON p.id_marca = m.id
            WHERE bp.id_bundle = :bundle_id
            AND pt.estado = 'activo'
            AND p.estado = 'activo'
            ORDER BY bp.orden";
    
    $query = $acceso->prepare($sql);
    $query->execute([':bundle_id' => $bundle_id]);
    return $query->fetchAll(PDO::FETCH_ASSOC);
}

// Obtener información del bundle
if ($_POST['funcion'] == 'obtener_info_bundle') {
    try {
        $bundle_id = $_POST['bundle_id'] ?? null;
        
        if (!$bundle_id) {
            echo json_encode(['success' => false, 'error' => 'Bundle ID no especificado']);
            exit();
        }
        
        $db = new Conexion();
        $acceso = $db->pdo;
        
        $sql = "SELECT 
                    b.id,
                    b.nombre,
                    b.descripcion,
                    b.precio_original,
                    b.precio_oferta,
                    b.descuento_porcentaje,
                    b.stock,
                    b.estado,
                    b.fecha_inicio,
                    b.fecha_fin,
                    b.imagen,
                    t.nombre as tienda_nombre
                FROM bundle b
                LEFT JOIN tienda t ON b.id_tienda = t.id
                WHERE b.id = :bundle_id
                AND b.estado = 'activo'";
        
        $query = $acceso->prepare($sql);
        $query->execute([':bundle_id' => $bundle_id]);
        $bundle = $query->fetch(PDO::FETCH_ASSOC);
        
        if (!$bundle) {
            echo json_encode(['success' => false, 'error' => 'Bundle no encontrado']);
            exit();
        }
        
        // Calcular precios y ahorro
        $precio_final = $bundle['precio_oferta'] > 0 ? $bundle['precio_oferta'] : $bundle['precio_original'];
        $bundle['precio_final'] = $precio_final;
        
        if ($bundle['precio_original'] > 0 && $precio_final < $bundle['precio_original']) {
            $bundle['ahorro'] = $bundle['precio_original'] - $precio_final;
            $bundle['porcentaje_ahorro'] = round(($bundle['ahorro'] / $bundle['precio_original']) * 100, 0);
        }
        
        // Obtener productos del bundle
        $productos = obtenerProductosBundle($bundle_id);
        $bundle['productos'] = $productos;
        $bundle['total_productos'] = count($productos);
        
        echo json_encode([
            'success' => true,
            'bundle' => $bundle
        ]);
        
    } catch (Exception $e) {
        error_log("Error obteniendo info bundle: " . $e->getMessage());
        echo json_encode(['success' => false, 'error' => 'Error interno del servidor']);
    }
}

// Obtener solo los productos del bundle
if ($_POST['funcion'] == 'obtener_productos_bundle') {
    try {
        $bundle_id = $_POST['bundle_id'] ?? null;
        
        if (!$bundle_id) {
            echo json_encode(['success' => false, 'error' => 'Bundle ID no especificado']);
            exit();
        }
        
        $productos = obtenerProductosBundle($bundle_id);
        
        if (empty($productos)) {
            echo json_encode(['success' => false, 'error' => 'Bundle no encontrado o sin productos']);
            exit();
        }
        
        echo json_encode([
            'success' => true,
            'productos' => $productos,
            'total_productos' => count($productos)
        ]);
        
    } catch (Exception $e) {
        error_log("Error obteniendo productos bundle: " . $e->getMessage());
        echo json_encode(['success' => false, 'error' => 'Error interno del servidor']);
    }
}