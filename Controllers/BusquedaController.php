<?php
// BusquedaController.php - VERSIÓN CORREGIDA

// Agregar esto al inicio del archivo
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json; charset=utf-8');

// Incluir archivos necesarios
include_once '../Models/Producto.php';
include_once '../Models/Reseña.php';
include_once '../Util/Config/config.php';
include_once '../Models/Categoria.php';
include_once '../Models/Subcategoria.php';
include_once '../Models/Conexion.php';

// Iniciar sesión
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Crear instancias
$categoria = new Categoria();
$subcategoria = new Subcategoria();
$producto_tienda = new Producto();
$reseña = new Reseña();

// Verificar que se haya enviado la función
if (!isset($_POST['funcion'])) {
    echo json_encode([
        'success' => false,
        'error' => 'Función no especificada',
        'debug' => 'No se recibió el parámetro "funcion"'
    ]);
    exit();
}

// Función para buscar sugerencias
if ($_POST['funcion'] == 'buscar_sugerencias') {
    $termino = $_POST['termino'] ?? '';
    
    if (strlen($termino) < 2) {
        echo json_encode(['sugerencias' => []]);
        exit();
    }
    
    try {
        // Verificar conexión a base de datos
        if (!isset($producto_tienda->acceso) || !$producto_tienda->acceso) {
            throw new Exception('Error de conexión a la base de datos');
        }
        
        // Consulta para productos
        $sql = "SELECT DISTINCT 
                       p.nombre as producto, 
                       m.nombre as marca,
                       c.nombre as categoria,
                       'producto' as tipo
                FROM producto p
                JOIN marca m ON p.id_marca = m.id
                JOIN subcategoria sc ON p.id_subcategoria = sc.id
                JOIN categoria c ON sc.id_categoria = c.id
                WHERE p.estado = 'activo'
                AND (p.nombre LIKE :termino
                     OR p.etiquetas LIKE :termino2)
                LIMIT 5";
        
        $query = $producto_tienda->acceso->prepare($sql);
        $termino_like = '%' . $termino . '%';
        $query->bindParam(':termino', $termino_like);
        $query->bindParam(':termino2', $termino_like);
        $query->execute();
        $productos = $query->fetchAll(PDO::FETCH_OBJ);
        
        // Buscar marcas
        $sql_marcas = "SELECT nombre as producto, 
                              '' as marca,
                              'Marca' as categoria,
                              'marca' as tipo
                       FROM marca
                       WHERE nombre LIKE :termino
                       LIMIT 2";
        
        $query_marcas = $producto_tienda->acceso->prepare($sql_marcas);
        $query_marcas->bindParam(':termino', $termino_like);
        $query_marcas->execute();
        $marcas = $query_marcas->fetchAll(PDO::FETCH_OBJ);
        
        // Buscar categorías
        $sql_categorias = "SELECT nombre as producto, 
                                  '' as marca,
                                  'Categoría' as categoria,
                                  'categoria' as tipo
                           FROM categoria
                           WHERE nombre LIKE :termino
                           LIMIT 2";
        
        $query_categorias = $producto_tienda->acceso->prepare($sql_categorias);
        $query_categorias->bindParam(':termino', $termino_like);
        $query_categorias->execute();
        $categorias = $query_categorias->fetchAll(PDO::FETCH_OBJ);
        
        // Combinar resultados
        $sugerencias = array_merge($productos, $marcas, $categorias);
        
        // Limitar a 8 resultados totales
        $sugerencias = array_slice($sugerencias, 0, 8);
        
        echo json_encode([
            'success' => true,
            'sugerencias' => $sugerencias
        ]);
        
    } catch (Exception $e) {
        error_log("Error en buscar_sugerencias: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'error' => 'Error en el servidor: ' . $e->getMessage(),
            'sugerencias' => []
        ]);
    }
    exit();
}

// Función para buscar productos (VERSIÓN CORREGIDA)
if ($_POST['funcion'] == 'buscar_productos') {
    $termino_busqueda = trim($_POST['termino'] ?? '');
    $pagina = isset($_POST['pagina']) ? (int)$_POST['pagina'] : 1;
    $limite = isset($_POST['limite']) ? (int)$_POST['limite'] : 12;
    
    if (strlen($termino_busqueda) < 2) {
        echo json_encode([
            'success' => false,
            'error' => 'Término de búsqueda muy corto',
            'productos' => [],
            'total_resultados' => 0
        ]);
        exit();
    }
    
    try {
        // CONSULTA SIMPLIFICADA Y CORREGIDA
        $sql = "SELECT DISTINCT 
                       pt.id as id_producto_tienda,
                       p.id as id_producto,
                       p.nombre as producto,
                       COALESCE(pi.imagen_url, 'producto_default.png') as imagen,
                       p.descripcion_larga as detalles,
                       p.etiquetas as etiquetas,
                       m.nombre as marca,
                       pt.envio_gratis as envio_gratis,
                       pt.precio as precio,
                       pt.descuento_porcentaje as descuento,
                       (pt.precio - (pt.precio * (pt.descuento_porcentaje * 0.01))) as precio_descuento,
                       t.nombre as tienda,
                       c.nombre as categoria,
                       sc.nombre as subcategoria,
                       pt.stock as stock,
                       pt.fecha_creacion as fecha_creacion,
                       CASE WHEN DATEDIFF(NOW(), pt.fecha_creacion) <= 7 THEN 1 ELSE 0 END as es_nuevo
                FROM producto_tienda pt
                INNER JOIN producto p ON pt.id_producto = p.id
                INNER JOIN marca m ON p.id_marca = m.id
                INNER JOIN tienda t ON pt.id_tienda = t.id
                INNER JOIN subcategoria sc ON p.id_subcategoria = sc.id
                INNER JOIN categoria c ON sc.id_categoria = c.id
                LEFT JOIN producto_imagen pi ON p.id = pi.id_producto AND pi.es_principal = 1
                WHERE pt.estado = 'activo' 
                AND p.estado = 'activo'
                AND (p.nombre LIKE :termino 
                     OR p.descripcion_larga LIKE :termino
                     OR m.nombre LIKE :termino
                     OR t.nombre LIKE :termino
                     OR c.nombre LIKE :termino
                     OR sc.nombre LIKE :termino)";
        
        // Agregar búsqueda en etiquetas si existe la columna
        $sql .= " OR p.etiquetas LIKE :termino";
        
        $sql .= " ORDER BY 
                    CASE 
                        WHEN p.nombre LIKE :termino_exacto THEN 1
                        WHEN p.nombre LIKE :termino_medio THEN 2
                        ELSE 3
                    END,
                    pt.precio ASC
                LIMIT :limite OFFSET :offset";
        
        $query = $producto_tienda->acceso->prepare($sql);
        
        $termino_like = '%' . $termino_busqueda . '%';
        $termino_exacto = $termino_busqueda . '%';
        $termino_medio = '%' . $termino_busqueda . '%';
        
        $offset = ($pagina - 1) * $limite;
        
        // Bind parameters
        $query->bindValue(':termino', $termino_like, PDO::PARAM_STR);
        $query->bindValue(':termino_exacto', $termino_exacto, PDO::PARAM_STR);
        $query->bindValue(':termino_medio', $termino_medio, PDO::PARAM_STR);
        $query->bindValue(':limite', $limite, PDO::PARAM_INT);
        $query->bindValue(':offset', $offset, PDO::PARAM_INT);
        
        $query->execute();
        $productos = $query->fetchAll(PDO::FETCH_OBJ);
        
        // Debug: ver qué productos se encontraron
        error_log("Término buscado: " . $termino_busqueda);
        error_log("Productos encontrados: " . count($productos));
        foreach($productos as $index => $prod) {
            error_log("Producto " . ($index+1) . ": " . $prod->producto);
        }
        
        // Si NO HAY resultados, mostrar array vacío inmediatamente
        if (empty($productos)) {
            echo json_encode([
                'success' => true,
                'productos' => [],
                'total_resultados' => 0,
                'pagina_actual' => $pagina,
                'total_paginas' => 0,
                'termino_busqueda' => $termino_busqueda
            ]);
            exit();
        }
        
        // Continuar con el procesamiento normal...
        $json = array();
        foreach ($productos as $objeto) {
            // Obtener calificación
            try {
                $reseña->evaluar_calificaciones($objeto->id_producto_tienda);
                $calificacion = !empty($reseña->objetos) ? number_format($reseña->objetos[0]->promedio, 1) : 0;
                $total_resenas = !empty($reseña->objetos) ? $reseña->objetos[0]->total : 0;
            } catch (Exception $e) {
                $calificacion = 0;
                $total_resenas = 0;
            }
            
            // Encriptar ID
            $id_encriptado = openssl_encrypt($objeto->id_producto_tienda, CODE, KEY);
            
            $json[] = array(
                'id' => $id_encriptado,
                'producto' => $objeto->producto,
                'imagen' => $objeto->imagen,
                'marca' => $objeto->marca,
                'calificacion' => $calificacion,
                'total_resenas' => $total_resenas,
                'envio' => ($objeto->envio_gratis == 1) ? 'Envío gratis' : 'Envío con costo',
                'precio' => $objeto->precio,
                'descuento' => $objeto->descuento,
                'precio_descuento' => $objeto->precio_descuento,
                'tienda' => $objeto->tienda,
                'categoria' => $objeto->categoria,
                'subcategoria' => $objeto->subcategoria,
                'detalles' => $objeto->detalles,
                'etiquetas' => $objeto->etiquetas,
                'stock' => $objeto->stock,
                'es_nuevo' => $objeto->es_nuevo,
                'fecha_creacion' => $objeto->fecha_creacion
            );
        }
        
        // Contar total de resultados (usando la misma consulta sin LIMIT)
        $sql_count = "SELECT COUNT(DISTINCT pt.id) as total
                     FROM producto_tienda pt
                     INNER JOIN producto p ON pt.id_producto = p.id
                     INNER JOIN marca m ON p.id_marca = m.id
                     INNER JOIN tienda t ON pt.id_tienda = t.id
                     INNER JOIN subcategoria sc ON p.id_subcategoria = sc.id
                     INNER JOIN categoria c ON sc.id_categoria = c.id
                     WHERE pt.estado = 'activo' 
                     AND p.estado = 'activo'
                     AND (p.nombre LIKE :termino 
                          OR p.descripcion_larga LIKE :termino
                          OR m.nombre LIKE :termino
                          OR t.nombre LIKE :termino
                          OR c.nombre LIKE :termino
                          OR sc.nombre LIKE :termino
                          OR p.etiquetas LIKE :termino)";
        
        $query_count = $producto_tienda->acceso->prepare($sql_count);
        $query_count->bindValue(':termino', $termino_like, PDO::PARAM_STR);
        $query_count->execute();
        $total_resultados = $query_count->fetch(PDO::FETCH_OBJ)->total;
        
        echo json_encode([
            'success' => true,
            'productos' => $json,
            'total_resultados' => $total_resultados,
            'pagina_actual' => $pagina,
            'total_paginas' => ceil($total_resultados / $limite),
            'termino_busqueda' => $termino_busqueda
        ]);
        
    } catch (Exception $e) {
        error_log("ERROR en buscar_productos: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'error' => 'Error en la búsqueda: ' . $e->getMessage(),
            'productos' => [],
            'total_resultados' => 0
        ]);
    }
    exit();
}

// Función para buscar subcategorías
if ($_POST['funcion'] == 'buscar_subcategorias') {
    $termino = $_POST['termino'] ?? '';
    
    if (strlen($termino) < 2) {
        echo json_encode([
            'success' => false,
            'error' => 'Término muy corto',
            'subcategorias' => []
        ]);
        exit();
    }
    
    try {
        // Verificar conexión
        if (!isset($subcategoria->acceso) || !$subcategoria->acceso) {
            throw new Exception('Error de conexión a la base de datos');
        }
        
        // Consulta simplificada
        $sql = "SELECT sc.id as id_subcategoria, 
                       sc.nombre as nombre_subcategoria,
                       c.nombre as nombre_categoria
                FROM subcategoria sc
                JOIN categoria c ON sc.id_categoria = c.id
                WHERE sc.nombre LIKE :termino
                AND sc.estado = 'activa'
                LIMIT 10";
        
        $query = $subcategoria->acceso->prepare($sql);
        $termino_like = '%' . $termino . '%';
        $query->bindParam(':termino', $termino_like);
        $query->execute();
        $resultados = $query->fetchAll(PDO::FETCH_OBJ);
        
        $subcategorias = array();
        foreach ($resultados as $item) {
            $subcategorias[] = array(
                'id' => $item->id_subcategoria,
                'nombre' => $item->nombre_subcategoria,
                'categoria' => $item->nombre_categoria
            );
        }
        
        echo json_encode(array(
            'success' => true,
            'subcategorias' => $subcategorias,
            'total' => count($subcategorias)
        ));
        
    } catch (Exception $e) {
        error_log("Error en buscar_subcategorias: " . $e->getMessage());
        echo json_encode(array(
            'success' => false,
            'error' => 'Error en la búsqueda: ' . $e->getMessage(),
            'subcategorias' => []
        ));
    }
    exit();
}

// Si no se reconoce la función
echo json_encode([
    'success' => false,
    'error' => 'Función no reconocida: ' . $_POST['funcion']
]);
?>