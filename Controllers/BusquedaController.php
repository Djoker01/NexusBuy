<?php
include_once '../Models/ProductoTienda.php';
include_once '../Models/Reseña.php';
include_once '../Util/Config/config.php';
include_once '../Models/Categoria.php';
        include_once '../Models/Subcategoria.php';
        
        $categoria = new Categoria();
        $subcategoria = new Subcategoria();

$producto_tienda = new ProductoTienda();
$reseña = new Reseña();

session_start();

if ($_POST['funcion'] == 'buscar_productos') {
    $termino_busqueda = $_POST['termino'] ?? '';
    $pagina = $_POST['pagina'] ?? 1;
    $limite = $_POST['limite'] ?? 12;
    
    try {
        // Buscar productos que coincidan con el término
        $sql = "SELECT DISTINCT pt.id as id,
                       p.id as id_producto,
                       p.nombre as producto,
                       p.imagen_principal as imagen,
                       p.detalles as detalles,
                       m.nombre as marca,
                       pt.estado_envio as envio,
                       pt.precio as precio,
                       pt.descuento as descuento,
                       (pt.precio - (pt.precio * (pt.descuento * 0.01))) as precio_descuento,
                       t.nombre as tienda,
                       c.nombre as categoria,
                       sc.nombre as subcategoria
                FROM producto_tienda pt
                JOIN producto p ON pt.id_producto = p.id
                JOIN marca m ON p.id_marca = m.id
                JOIN tienda t ON pt.id_tienda = t.id
                JOIN subcategoria sc ON p.id_subcategoria = sc.id
                JOIN categoria c ON sc.id_categoria = c.id
                WHERE pt.estado = 'A' 
                AND p.estado = 'A'
                AND (p.nombre LIKE :termino 
                     OR p.detalles LIKE :termino 
                     OR m.nombre LIKE :termino
                     OR t.nombre LIKE :termino
                     OR c.nombre LIKE :termino
                     OR sc.nombre LIKE :termino)
                ORDER BY 
                    CASE 
                        WHEN p.nombre LIKE :termino_exacto THEN 1
                        WHEN p.nombre LIKE :termino_inicio THEN 2
                        ELSE 3
                    END,
                    pt.precio ASC
                LIMIT :limite OFFSET :offset";

        $query = $producto_tienda->acceso->prepare($sql);
        
        $termino_like = '%' . $termino_busqueda . '%';
        $termino_exacto = $termino_busqueda . '%';
        $offset = ($pagina - 1) * $limite;
        
        $query->bindValue(':termino', $termino_like);
        $query->bindValue(':termino_exacto', $termino_exacto);
        $query->bindValue(':termino_inicio', $termino_exacto);
        $query->bindValue(':limite', (int)$limite, PDO::PARAM_INT);
        $query->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        
        $query->execute();
        $productos = $query->fetchAll();
        
        // Obtener total de resultados para paginación
        $sql_count = "SELECT COUNT(DISTINCT pt.id) as total
                     FROM producto_tienda pt
                     JOIN producto p ON pt.id_producto = p.id
                     JOIN marca m ON p.id_marca = m.id
                     JOIN tienda t ON pt.id_tienda = t.id
                     JOIN subcategoria sc ON p.id_subcategoria = sc.id
                     JOIN categoria c ON sc.id_categoria = c.id
                     WHERE pt.estado = 'A' 
                     AND p.estado = 'A'
                     AND (p.nombre LIKE :termino 
                          OR p.detalles LIKE :termino 
                          OR m.nombre LIKE :termino
                          OR t.nombre LIKE :termino
                          OR c.nombre LIKE :termino
                          OR sc.nombre LIKE :termino)";
        
        $query_count = $producto_tienda->acceso->prepare($sql_count);
        $query_count->bindValue(':termino', $termino_like);
        $query_count->execute();
        $total_resultados = $query_count->fetch()->total;
        
        $json = array();
        foreach ($productos as $objeto) {
            // Obtener calificación del producto
            $reseña->evaluar_calificaciones($objeto->id);
            $calificacion = !empty($reseña->objetos) ? number_format($reseña->objetos[0]->promedio) : 0;
            
            $json[] = array(
                'id' => openssl_encrypt($objeto->id, CODE, KEY),
                'producto' => $objeto->producto,
                'imagen' => $objeto->imagen,
                'marca' => $objeto->marca,
                'calificacion' => $calificacion,
                'envio' => $objeto->envio,
                'precio' => $objeto->precio,
                'descuento' => $objeto->descuento,
                'precio_descuento' => $objeto->precio_descuento,
                'tienda' => $objeto->tienda,
                'categoria' => $objeto->categoria,
                'subcategoria' => $objeto->subcategoria,
                'detalles' => $objeto->detalles
            );
        }
        
        echo json_encode([
            'success' => true,
            'productos' => $json,
            'total_resultados' => $total_resultados,
            'pagina_actual' => $pagina,
            'total_paginas' => ceil($total_resultados / $limite),
            'termino_busqueda' => $termino_busqueda
        ]);
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'error' => 'Error en la búsqueda: ' . $e->getMessage()
        ]);
    }
}

if ($_POST['funcion'] == 'buscar_sugerencias') {
    $termino = $_POST['termino'] ?? '';
    
    if (strlen($termino) < 2) {
        echo json_encode(['sugerencias' => []]);
        exit();
    }
    
    try {
        $sql = "SELECT p.nombre as producto, 
                       m.nombre as marca,
                       c.nombre as categoria,
                       'producto' as tipo
                FROM producto p
                JOIN marca m ON p.id_marca = m.id
                JOIN subcategoria sc ON p.id_subcategoria = sc.id
                JOIN categoria c ON sc.id_categoria = c.id
                WHERE p.estado = 'A'
                AND p.nombre LIKE :termino
                UNION
                SELECT m.nombre as producto,
                       '' as marca,
                       'Marca' as categoria,
                       'marca' as tipo
                FROM marca m
                WHERE m.nombre LIKE :termino
                UNION
                SELECT c.nombre as producto,
                       '' as marca,
                       'Categoría' as categoria,
                       'categoria' as tipo
                FROM categoria c
                WHERE c.nombre LIKE :termino
                LIMIT 8";
        
        $query = $producto_tienda->acceso->prepare($sql);
        $query->bindValue(':termino', $termino . '%');
        $query->execute();
        $sugerencias = $query->fetchAll();
        
        echo json_encode([
            'sugerencias' => $sugerencias
        ]);
        
    } catch (Exception $e) {
        echo json_encode(['sugerencias' => []]);
    }
}

if ($_POST['funcion'] == 'buscar_subcategorias') {
    $termino = $_POST['termino'] ?? '';
    
    try {
        
        
        // Buscar subcategorías que coincidan con el término
        $subcategoria->buscarSubcategorias($termino);
        $resultados = $subcategoria->objetos;
        
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
        echo json_encode(array(
            'success' => false,
            'error' => 'Error en la búsqueda: ' . $e->getMessage()
        ));
    }
}