<?php
include_once '../Models/Favorito.php';
include_once '../Models/Producto.php';
// include_once '../Models/Historial.php';
include_once '../Util/Config/config.php';

$favorito = new Favorito();
$producto = new Producto();
// $historial = new Historial();

session_start();

if ($_POST['funcion'] == 'agregar_favorito') {
    if (empty($_SESSION['id'])) {
        echo json_encode(['error' => 'no_sesion']);
        exit();
    }
    
    $id_usuario = $_SESSION['id'];
    $id_producto_tienda_encrypted = $_POST['id_producto_tienda'];
    
    // Desencriptar ID del producto
    $id_producto_tienda = openssl_decrypt($id_producto_tienda_encrypted, CODE, KEY);
    
    if (is_numeric($id_producto_tienda)) {
        try {
            $resultado = $favorito->agregarFavorito($id_usuario, $id_producto_tienda);
            
            if ($resultado) {
                // Registrar en historial
                $descripcion = 'Agregó un producto a sus favoritos';
                $historial->crear_historial($descripcion, 1, 2, $id_usuario);
                
                echo json_encode(['success' => true, 'mensaje' => 'Producto agregado a favoritos']);
            } else {
                echo json_encode(['success' => false, 'error' => 'El producto ya está en favoritos']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => 'Error al agregar a favoritos: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'ID de producto inválido']);
    }
}

if ($_POST['funcion'] == 'eliminar_favorito') {
    if (empty($_SESSION['id'])) {
        echo json_encode(['error' => 'no_sesion']);
        exit();
    }
    
    $id_usuario = $_SESSION['id'];
    $id_producto_tienda_encrypted = $_POST['id_producto_tienda'];
    
    $id_producto_tienda = openssl_decrypt($id_producto_tienda_encrypted, CODE, KEY);
    
    if (is_numeric($id_producto_tienda)) {
        try {
            $resultado = $favorito->eliminarFavorito($id_usuario, $id_producto_tienda);
            
            if ($resultado) {
                // Registrar en historial
                $descripcion = 'Eliminó un producto de sus favoritos';
                $historial->crear_historial($descripcion, 3, 2, $id_usuario);
                
                echo json_encode(['success' => true, 'mensaje' => 'Producto eliminado de favoritos']);
            } else {
                echo json_encode(['success' => false, 'error' => 'Error al eliminar de favoritos']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => 'Error al eliminar de favoritos: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'ID de producto inválido']);
    }
}

if ($_POST['funcion'] == 'obtener_favoritos') {
    if (empty($_SESSION['id'])) {
        echo json_encode(['error' => 'no_sesion']);
        exit();
    }
    
    $id_usuario = $_SESSION['id'];
    $pagina = intval($_POST['pagina'] ?? 1);
    $limite = intval($_POST['limite'] ?? 12);
    $filtro_categoria = $_POST['filtro_categoria'] ?? '';
    $filtro_precio = $_POST['filtro_precio'] ?? '';
    $orden = $_POST['orden'] ?? 'recientes';
    
    try {
        $favoritos = $favorito->obtenerFavoritosUsuario(
            $id_usuario, 
            $pagina, 
            $limite, 
            $filtro_categoria, 
            $filtro_precio, 
            $orden
        );
        
        $total_favoritos = $favorito->contarFavoritosUsuario(
            $id_usuario, 
            $filtro_categoria, 
            $filtro_precio
        );
        
        $total_paginas = ceil($total_favoritos / $limite);
        
        echo json_encode([
            'success' => true,
            'favoritos' => $favoritos,
            'paginacion' => [
                'pagina_actual' => $pagina,
                'total_paginas' => $total_paginas,
                'total_productos' => $total_favoritos,
                'limite' => $limite
            ]
        ]);
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'error' => 'Error al cargar favoritos: ' . $e->getMessage()
        ]);
    }
}

if ($_POST['funcion'] == 'verificar_favorito') {
    if (empty($_SESSION['id'])) {
        echo json_encode(['es_favorito' => false]);
        exit();
    }
    
    $id_usuario = $_SESSION['id'];
    $id_producto_tienda_encrypted = $_POST['id_producto_tienda'];
    
    $id_producto_tienda = openssl_decrypt($id_producto_tienda_encrypted, CODE, KEY);
    
    if (is_numeric($id_producto_tienda)) {
        $es_favorito = $favorito->verificarFavorito($id_usuario, $id_producto_tienda);
        echo json_encode(['es_favorito' => $es_favorito]);
    } else {
        echo json_encode(['es_favorito' => false]);
    }
}

if ($_POST['funcion'] == 'limpiar_favoritos') {
    if (empty($_SESSION['id'])) {
        echo json_encode(['error' => 'no_sesion']);
        exit();
    }
    
    $id_usuario = $_SESSION['id'];
    
    try {
        $resultado = $favorito->limpiarFavoritos($id_usuario);
        
        if ($resultado) {
            // Registrar en historial
            $descripcion = 'Limpió todos sus productos favoritos';
            $historial->crear_historial($descripcion, 3, 2, $id_usuario);
            
            echo json_encode(['success' => true, 'mensaje' => 'Todos los favoritos han sido eliminados']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Error al limpiar favoritos']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => 'Error al limpiar favoritos: ' . $e->getMessage()]);
    }
}

if ($_POST['funcion'] == 'obtener_categorias_favoritos') {
    if (empty($_SESSION['id'])) {
        echo json_encode(['error' => 'no_sesion']);
        exit();
    }
    
    $id_usuario = $_SESSION['id'];
    
    try {
        $categorias = $favorito->obtenerCategoriasFavoritos($id_usuario);
        echo json_encode(['success' => true, 'categorias' => $categorias]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => 'Error al cargar categorías: ' . $e->getMessage()]);
    }
}