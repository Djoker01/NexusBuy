<?php
include_once '../Models/Banner.php';
include_once '../Models/Historial.php';
include_once '../Util/Config/config.php';

session_start();

// Verificar que el usuario es administrador
function verificarAdmin() {
    if (empty($_SESSION['id']) || $_SESSION['rol'] !== 'admin') {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'error' => 'no_autorizado',
            'message' => 'Acceso no autorizado. Se requieren permisos de administrador.'
        ]);
        exit();
    }
    return $_SESSION['id'];
}

header('Content-Type: application/json');

$banner = new Banner();
$historial = new Historial();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Método no permitido']);
    exit();
}

if (!isset($_POST['funcion'])) {
    echo json_encode(['success' => false, 'error' => 'Función no especificada']);
    exit();
}

$id_admin = verificarAdmin();

switch ($_POST['funcion']) {
    case 'listar_banners':
        listarBanners();
        break;
        
    case 'obtener_banner':
        obtenerBanner();
        break;
        
    case 'crear_banner':
        crearBanner($id_admin);
        break;
        
    case 'actualizar_banner':
        actualizarBanner($id_admin);
        break;
        
    case 'eliminar_banner':
        eliminarBanner($id_admin);
        break;
        
    case 'cambiar_estado':
        cambiarEstado($id_admin);
        break;
        
    case 'reordenar_banners':
        reordenarBanners($id_admin);
        break;
        
    case 'obtener_estadisticas':
        obtenerEstadisticas();
        break;
        
    case 'upload_imagen':
        uploadImagen($id_admin);
        break;
        
    default:
        echo json_encode(['success' => false, 'error' => 'Función no válida']);
        break;
}

function listarBanners() {
    global $banner;
    
    try {
        $pagina = isset($_POST['pagina']) ? intval($_POST['pagina']) : 1;
        $por_pagina = isset($_POST['por_pagina']) ? intval($_POST['por_pagina']) : 20;
        
        $filtros = [];
        if (isset($_POST['posicion']) && $_POST['posicion'] !== '') {
            $filtros['posicion'] = $_POST['posicion'];
        }
        if (isset($_POST['estado']) && $_POST['estado'] !== '') {
            $filtros['estado'] = intval($_POST['estado']);
        }
        if (!empty($_POST['busqueda'])) {
            $filtros['busqueda'] = $_POST['busqueda'];
        }
        
        $resultado = $banner->listarBanners($pagina, $por_pagina, $filtros);
        
        if ($resultado === false) {
            throw new Exception($banner->error ?? 'Error al listar banners');
        }
        
        echo json_encode([
            'success' => true,
            'data' => $resultado
        ]);

    } catch (Exception $e) {
        error_log("Error listando banners: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
}

function obtenerBanner() {
    global $banner;
    
    try {
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        
        if ($id <= 0) {
            throw new Exception('ID de banner no válido');
        }
        
        $datos = $banner->obtenerBannerPorId($id);
        
        if ($datos === false) {
            throw new Exception($banner->error ?? 'Banner no encontrado');
        }
        
        echo json_encode([
            'success' => true,
            'banner' => $datos
        ]);

    } catch (Exception $e) {
        error_log("Error obteniendo banner: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
}

function crearBanner($id_admin) {
    global $banner, $historial;
    
    try {
        // Validar campos requeridos
        $campos_requeridos = ['titulo', 'posicion', 'fecha_inicio', 'fecha_fin'];
        foreach ($campos_requeridos as $campo) {
            if (empty($_POST[$campo])) {
                throw new Exception("El campo {$campo} es requerido");
            }
        }
        
        // Procesar imagen
        $imagen = 'default_banner.jpg';
        if (isset($_POST['imagen_nombre']) && !empty($_POST['imagen_nombre'])) {
            $imagen = $_POST['imagen_nombre'];
        }
        
        $datos = [
            'titulo' => $_POST['titulo'],
            'descripcion' => $_POST['descripcion'] ?? '',
            'imagen' => $imagen,
            'url' => $_POST['url'] ?? '#',
            'posicion' => $_POST['posicion'],
            'orden' => intval($_POST['orden'] ?? 0),
            'fecha_inicio' => $_POST['fecha_inicio'],
            'fecha_fin' => $_POST['fecha_fin'],
            'estado' => intval($_POST['estado'] ?? 1),
            'texto_boton' => $_POST['texto_boton'] ?? 'Ver más',
            'icono_boton' => $_POST['icono_boton'] ?? 'fa-shopping-cart',
            'id_usuario' => $id_admin
        ];
        
        $id_banner = $banner->crearBanner($datos);
        
        if ($id_banner === false) {
            throw new Exception($banner->error ?? 'Error al crear banner');
        }
        
        // Registrar en historial
        $descripcion = "Creó un nuevo banner: {$datos['titulo']} (Posición: {$datos['posicion']})";
        $historial->crear_historial($descripcion, 1, 9, $id_admin, 'crear_banner', json_encode($datos));
        
        echo json_encode([
            'success' => true,
            'message' => 'Banner creado correctamente',
            'id' => $id_banner
        ]);

    } catch (Exception $e) {
        error_log("Error creando banner: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
}

function actualizarBanner($id_admin) {
    global $banner, $historial;
    
    try {
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        
        if ($id <= 0) {
            throw new Exception('ID de banner no válido');
        }
        
        // Obtener banner original para comparar
        $banner_original = $banner->obtenerBannerPorId($id);
        
        if (!$banner_original) {
            throw new Exception('Banner no encontrado');
        }
        
        $datos = [
            'titulo' => $_POST['titulo'],
            'descripcion' => $_POST['descripcion'] ?? '',
            'url' => $_POST['url'] ?? '#',
            'posicion' => $_POST['posicion'],
            'orden' => intval($_POST['orden'] ?? 0),
            'fecha_inicio' => $_POST['fecha_inicio'],
            'fecha_fin' => $_POST['fecha_fin'],
            'estado' => intval($_POST['estado'] ?? 1),
            'texto_boton' => $_POST['texto_boton'] ?? 'Ver más',
            'icono_boton' => $_POST['icono_boton'] ?? 'fa-shopping-cart'
        ];
        
        // Si hay nueva imagen
        if (isset($_POST['imagen_nombre']) && !empty($_POST['imagen_nombre'])) {
            $datos['imagen'] = $_POST['imagen_nombre'];
            
            // Eliminar imagen anterior si no es la default
            if ($banner_original->imagen != 'default_banner.jpg') {
                $ruta_imagen = "../Util/Img/Banners/" . $banner_original->imagen;
                if (file_exists($ruta_imagen)) {
                    unlink($ruta_imagen);
                }
            }
        }
        
        $resultado = $banner->actualizarBanner($id, $datos);
        
        if ($resultado === false) {
            throw new Exception($banner->error ?? 'Error al actualizar banner');
        }
        
        // Registrar en historial
        $descripcion = "Actualizó el banner: {$datos['titulo']} (ID: {$id})";
        $historial->crear_historial(
            $descripcion, 
            2, 
            9, 
            $id_admin, 
            'actualizar_banner', 
            json_encode(['original' => $banner_original, 'nuevo' => $datos])
        );
        
        echo json_encode([
            'success' => true,
            'message' => 'Banner actualizado correctamente'
        ]);

    } catch (Exception $e) {
        error_log("Error actualizando banner: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
}

function eliminarBanner($id_admin) {
    global $banner, $historial;
    
    try {
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        
        if ($id <= 0) {
            throw new Exception('ID de banner no válido');
        }
        
        // Obtener banner para eliminar imagen
        $banner_data = $banner->obtenerBannerPorId($id);
        
        if (!$banner_data) {
            throw new Exception('Banner no encontrado');
        }
        
        // Eliminar imagen si no es la default
        if ($banner_data->imagen != 'default_banner.jpg') {
            $ruta_imagen = "../Util/Img/Banners/" . $banner_data->imagen;
            if (file_exists($ruta_imagen)) {
                unlink($ruta_imagen);
            }
        }
        
        $resultado = $banner->eliminarBanner($id);
        
        if ($resultado === false) {
            throw new Exception($banner->error ?? 'Error al eliminar banner');
        }
        
        // Registrar en historial
        $descripcion = "Eliminó el banner: {$banner_data->titulo} (ID: {$id})";
        $historial->crear_historial($descripcion, 3, 9, $id_admin, 'eliminar_banner');
        
        echo json_encode([
            'success' => true,
            'message' => 'Banner eliminado correctamente'
        ]);

    } catch (Exception $e) {
        error_log("Error eliminando banner: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
}

function cambiarEstado($id_admin) {
    global $banner, $historial;
    
    try {
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        $estado = isset($_POST['estado']) ? intval($_POST['estado']) : 0;
        
        if ($id <= 0) {
            throw new Exception('ID de banner no válido');
        }
        
        $resultado = $banner->cambiarEstado($id, $estado);
        
        if ($resultado === false) {
            throw new Exception($banner->error ?? 'Error al cambiar estado');
        }
        
        $estado_texto = $estado ? 'activado' : 'desactivado';
        
        // Obtener banner para historial
        $banner_data = $banner->obtenerBannerPorId($id);
        
        // Registrar en historial
        $descripcion = "{$estado_texto} el banner: {$banner_data->titulo}";
        $historial->crear_historial($descripcion, 2, 9, $id_admin, 'cambiar_estado_banner');
        
        echo json_encode([
            'success' => true,
            'message' => "Banner {$estado_texto} correctamente"
        ]);

    } catch (Exception $e) {
        error_log("Error cambiando estado: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
}

function reordenarBanners($id_admin) {
    global $banner, $historial;
    
    try {
        $ordenes_json = $_POST['ordenes'] ?? '';
        
        if (empty($ordenes_json)) {
            throw new Exception('No se recibieron órdenes');
        }
        
        $ordenes = json_decode($ordenes_json, true);
        
        if (!is_array($ordenes)) {
            throw new Exception('Formato de órdenes inválido');
        }
        
        $resultado = $banner->reordenarBanners($ordenes);
        
        if ($resultado === false) {
            throw new Exception($banner->error ?? 'Error al reordenar banners');
        }
        
        // Registrar en historial
        $descripcion = "Reordenó " . count($ordenes) . " banners";
        $historial->crear_historial($descripcion, 2, 9, $id_admin, 'reordenar_banners');
        
        echo json_encode([
            'success' => true,
            'message' => 'Banners reordenados correctamente'
        ]);

    } catch (Exception $e) {
        error_log("Error reordenando banners: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
}

function obtenerEstadisticas() {
    global $banner;
    
    try {
        $stats = $banner->obtenerEstadisticas();
        
        if ($stats === false) {
            throw new Exception($banner->error ?? 'Error al obtener estadísticas');
        }
        
        echo json_encode([
            'success' => true,
            'estadisticas' => $stats
        ]);

    } catch (Exception $e) {
        error_log("Error obteniendo estadísticas: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
}

function uploadImagen($id_admin) {
    try {
        if (!isset($_FILES['imagen']) || $_FILES['imagen']['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('Error al subir la imagen');
        }
        
        $archivo = $_FILES['imagen'];
        $nombre_original = $archivo['name'];
        $extension = strtolower(pathinfo($nombre_original, PATHINFO_EXTENSION));
        
        // Validar extensión
        $extensiones_permitidas = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (!in_array($extension, $extensiones_permitidas)) {
            throw new Exception('Tipo de archivo no permitido. Use: ' . implode(', ', $extensiones_permitidas));
        }
        
        // Validar tamaño (máximo 2MB)
        if ($archivo['size'] > 2 * 1024 * 1024) {
            throw new Exception('La imagen no puede superar los 2MB');
        }
        
        // Generar nombre único
        $nombre_archivo = 'banner_' . time() . '_' . uniqid() . '.' . $extension;
        $ruta_destino = '../Util/Img/Banners/' . $nombre_archivo;
        
        // Crear directorio si no existe
        if (!file_exists('../Util/Img/Banners/')) {
            mkdir('../Util/Img/Banners/', 0777, true);
        }
        
        // Mover archivo
        if (!move_uploaded_file($archivo['tmp_name'], $ruta_destino)) {
            throw new Exception('Error al guardar la imagen');
        }
        
        echo json_encode([
            'success' => true,
            'nombre_archivo' => $nombre_archivo,
            'message' => 'Imagen subida correctamente'
        ]);

    } catch (Exception $e) {
        error_log("Error subiendo imagen: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
}
?>