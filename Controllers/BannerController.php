<?php
include_once '../Models/Banner.php';
include_once '../Util/Config/config.php';

header('Content-Type: application/json');

$banner = new Banner();

// VERIFICAR QUE LA FUNCIÓN EXISTE EN EL POST
if (!isset($_POST['funcion'])) {
    echo json_encode(['success' => false, 'error' => 'Función no especificada']);
    exit();
}

$funcion = $_POST['funcion'];

// ==================== FUNCIÓN PRINCIPAL ====================
if ($funcion == 'obtener_banners') {
    try {
        $posicion = $_POST['posicion'] ?? 'slider_principal';
        $limite = isset($_POST['limite']) ? intval($_POST['limite']) : 10;
        
        // Llamar al modelo
        $banners = $banner->obtenerBannersPorPosicion($posicion, $limite);
        
        if ($banners === false) {
            throw new Exception($banner->error ?? 'Error al obtener banners');
        }
        
        $json = [];
        foreach ($banners as $b) {
            // Asegurar que imagen nunca sea null o undefined
            $imagen = !empty($b->imagen) ? $b->imagen : 'default_banner.jpg';
            
            // Construir ruta completa
            if (strpos($imagen, 'http') !== 0 && strpos($imagen, '/') !== 0) {
                $imagen = 'Util/Img/Banners/' . $imagen;
            }
            
            $json[] = [
                'id' => $b->id,
                'titulo' => $b->titulo,
                'descripcion' => $b->descripcion,
                'imagen' => $imagen,  // ← Ahora nunca será undefined
                'url' => $b->url ?: '#',
                'texto_boton' => $b->texto_boton ?: 'Ver más',
                'icono_boton' => $b->icono_boton ?: 'fa-shopping-cart',
                'orden' => $b->orden
            ];
        }
        
        echo json_encode([
            'success' => true,
            'banners' => $json,
            'posicion' => $posicion,
            'total' => count($json)
        ]);

    } catch (Exception $e) {
        error_log("Error en BannerController: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'error' => 'Error al cargar banners: ' . $e->getMessage()
        ]);
    }
    exit();
}

// ==================== SI LLEGA AQUÍ, LA FUNCIÓN NO EXISTE ====================
echo json_encode(['success' => false, 'error' => 'Función no válida']);
exit();
?>