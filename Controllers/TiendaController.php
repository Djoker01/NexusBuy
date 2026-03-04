<?php
// /Controllers/TiendaController.php
session_start();
require_once '../Models/Tienda.php';

header('Content-Type: application/json'); // Asegurar respuesta JSON

$tienda = new Tienda();

// Verificar sesión para todas las operaciones
if (!isset($_SESSION['id'])) {
    echo json_encode(['error' => 'no_sesion', 'message' => 'Debe iniciar sesión']);
    exit;
}

// Obtener el ID de la tienda de la sesión si existe
if (!isset($_SESSION['id_tienda']) && isset($_SESSION['id'])) {
    // Intentar obtener la tienda del usuario
    $tienda_data = $tienda->obtener_tienda_por_usuario($_SESSION['id']);
    if (!empty($tienda_data)) {
        $_SESSION['id_tienda'] = $tienda_data[0]->id;
    }
}

$funcion = $_POST['funcion'] ?? '';

switch ($funcion) {
    
    case 'obtener_datos_tienda':
        obtenerDatosTienda();
        break;
        
    case 'obtener_estadisticas':
        obtenerEstadisticas();
        break;
        
    case 'actualizar_tienda':
        actualizarTienda();
        break;
        
    case 'subir_logo':
        subirLogo();
        break;
        
    case 'subir_banner':
        subirBanner();
        break;
        
    case 'guardar_redes_sociales':
        guardarRedesSociales();
        break;
        
    case 'guardar_politicas':
        guardarPoliticas();
        break;
        
    case 'verificar_tienda':
        verificarTienda();
        break;
        
    default:
        echo json_encode(['error' => 'funcion_no_valida']);
        break;
}

// ==============================================
// FUNCIONES ESPECÍFICAS
// ==============================================

function obtenerDatosTienda() {
    global $tienda;
    
    $id_tienda = $_SESSION['id_tienda'] ?? 0;
    
    if (!$id_tienda) {
        // Intentar obtener por usuario
        $resultado = $tienda->obtener_tienda_por_usuario($_SESSION['id']);
        
        if (!empty($resultado)) {
            $tienda_data = $resultado[0];
            $_SESSION['id_tienda'] = $tienda_data->id;
            
            // Decodificar campos JSON
            if ($tienda_data->redes_sociales) {
                $tienda_data->redes_sociales = json_decode($tienda_data->redes_sociales);
            }
            if ($tienda_data->politicas) {
                $tienda_data->politicas = json_decode($tienda_data->politicas);
            }
            
            echo json_encode([
                'success' => true,
                'data' => $tienda_data
            ]);
        } else {
            echo json_encode([
                'error' => 'no_tienda',
                'message' => 'No tienes una tienda asociada. Contacta al administrador.'
            ]);
        }
    } else {
        $resultado = $tienda->obtener_tienda_por_id($id_tienda);
        
        if (!empty($resultado)) {
            $tienda_data = $resultado[0];
            
            // Decodificar campos JSON
            if ($tienda_data->redes_sociales) {
                $tienda_data->redes_sociales = json_decode($tienda_data->redes_sociales);
            }
            if ($tienda_data->politicas) {
                $tienda_data->politicas = json_decode($tienda_data->politicas);
            }
            
            echo json_encode([
                'success' => true,
                'data' => $tienda_data
            ]);
        } else {
            echo json_encode([
                'error' => 'no_tienda',
                'message' => 'Tienda no encontrada'
            ]);
        }
    }
}

function obtenerEstadisticas() {
    global $tienda;
    
    $id_tienda = $_POST['id_tienda'] ?? $_SESSION['id_tienda'] ?? 0;
    
    if (!$id_tienda) {
        echo json_encode(['error' => 'no_tienda']);
        exit;
    }
    
    $stats = $tienda->obtener_estadisticas($id_tienda);
    
    // Agregar información adicional útil para el dashboard
    $stats->fecha_consulta = date('Y-m-d H:i:s');
    
    echo json_encode([
        'success' => true,
        'data' => $stats
    ]);
}

function actualizarTienda() {
    global $tienda;
    
    $id_tienda = $_SESSION['id_tienda'] ?? 0;
    
    if (!$id_tienda) {
        echo json_encode(['error' => 'no_tienda']);
        exit;
    }
    
    // Validar campos obligatorios
    $errores = [];
    if (empty($_POST['nombre'])) {
        $errores[] = 'El nombre de la tienda es obligatorio';
    }
    if (empty($_POST['id_municipio'])) {
        $errores[] = 'Debes seleccionar un municipio';
    }
    
    if (!empty($errores)) {
        echo json_encode([
            'success' => false,
            'errores' => $errores
        ]);
        exit;
    }
    
    $datos = [
        'nombre' => $_POST['nombre'] ?? null,
        'descripcion' => $_POST['descripcion'] ?? null,
        'telefono' => $_POST['telefono'] ?? null,
        'email' => $_POST['email'] ?? null,
        'sitio_web' => $_POST['sitio_web'] ?? null,
        'direccion' => $_POST['direccion'] ?? null,
        'id_municipio' => $_POST['id_municipio'] ?? null,
        'redes_sociales' => null, // Se maneja en función aparte
        'politicas' => null // Se maneja en función aparte
    ];
    
    $resultado = $tienda->actualizar_tienda($id_tienda, $datos);
    
    if ($resultado) {
        echo json_encode([
            'success' => true,
            'message' => 'Datos actualizados correctamente'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'No se realizaron cambios',
            'tipo' => 'sin_cambios'
        ]);
    }
}

function subirLogo() {
    global $tienda;
    
    $id_tienda = $_SESSION['id_tienda'] ?? 0;
    
    if (!$id_tienda) {
        echo json_encode(['error' => 'no_tienda']);
        exit;
    }
    
    if (!isset($_FILES['logo']) || $_FILES['logo']['error'] != 0) {
        echo json_encode([
            'error' => 'no_archivo',
            'message' => 'No se recibió ningún archivo'
        ]);
        exit;
    }
    
    $archivo = $_FILES['logo'];
    $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
    $extensiones_permitidas = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $tamano_maximo = 2 * 1024 * 1024; // 2MB
    
    // Validar extensión
    if (!in_array($extension, $extensiones_permitidas)) {
        echo json_encode([
            'error' => 'extension_no_permitida',
            'message' => 'Solo se permiten archivos: ' . implode(', ', $extensiones_permitidas)
        ]);
        exit;
    }
    
    // Validar tamaño
    if ($archivo['size'] > $tamano_maximo) {
        echo json_encode([
            'error' => 'archivo_grande',
            'message' => 'El archivo no debe superar los 2MB'
        ]);
        exit;
    }
    
    // Generar nombre único
    $nombre_archivo = 'tienda_' . $id_tienda . '_logo_' . time() . '.' . $extension;
    $ruta_destino = '../Util/Img/Stores/' . $nombre_archivo;
    
    // Crear directorio si no existe
    if (!file_exists('../Util/Img/Stores/')) {
        mkdir('../Util/Img/Stores/', 0777, true);
    }
    
    if (move_uploaded_file($archivo['tmp_name'], $ruta_destino)) {
        $resultado = $tienda->actualizar_logo($id_tienda, $nombre_archivo);
        
        if ($resultado) {
            echo json_encode([
                'success' => true,
                'logo' => $nombre_archivo,
                'message' => 'Logo actualizado correctamente'
            ]);
        } else {
            echo json_encode([
                'error' => 'bd_error',
                'message' => 'Error al guardar en la base de datos'
            ]);
        }
    } else {
        echo json_encode([
            'error' => 'upload_error',
            'message' => 'Error al subir el archivo'
        ]);
    }
}

function subirBanner() {
    global $tienda;
    
    $id_tienda = $_SESSION['id_tienda'] ?? 0;
    
    if (!$id_tienda) {
        echo json_encode(['error' => 'no_tienda']);
        exit;
    }
    
    if (!isset($_FILES['banner']) || $_FILES['banner']['error'] != 0) {
        echo json_encode([
            'error' => 'no_archivo',
            'message' => 'No se recibió ningún archivo'
        ]);
        exit;
    }
    
    $archivo = $_FILES['banner'];
    $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
    $extensiones_permitidas = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $tamano_maximo = 5 * 1024 * 1024; // 5MB para banners
    
    // Validar extensión
    if (!in_array($extension, $extensiones_permitidas)) {
        echo json_encode([
            'error' => 'extension_no_permitida',
            'message' => 'Solo se permiten archivos: ' . implode(', ', $extensiones_permitidas)
        ]);
        exit;
    }
    
    // Validar tamaño
    if ($archivo['size'] > $tamano_maximo) {
        echo json_encode([
            'error' => 'archivo_grande',
            'message' => 'El archivo no debe superar los 5MB'
        ]);
        exit;
    }
    
    // Generar nombre único
    $nombre_archivo = 'tienda_' . $id_tienda . '_banner_' . time() . '.' . $extension;
    $ruta_destino = '../Util/Img/Stores/' . $nombre_archivo;
    
    if (move_uploaded_file($archivo['tmp_name'], $ruta_destino)) {
        $resultado = $tienda->actualizar_banner($id_tienda, $nombre_archivo);
        
        if ($resultado) {
            echo json_encode([
                'success' => true,
                'banner' => $nombre_archivo,
                'message' => 'Banner actualizado correctamente'
            ]);
        } else {
            echo json_encode([
                'error' => 'bd_error',
                'message' => 'Error al guardar en la base de datos'
            ]);
        }
    } else {
        echo json_encode([
            'error' => 'upload_error',
            'message' => 'Error al subir el archivo'
        ]);
    }
}

function guardarRedesSociales() {
    global $tienda;
    
    $id_tienda = $_SESSION['id_tienda'] ?? 0;
    
    if (!$id_tienda) {
        echo json_encode(['error' => 'no_tienda']);
        exit;
    }
    
    // Construir array de redes sociales
    $redes = [
        'facebook' => $_POST['facebook'] ?? '',
        'instagram' => $_POST['instagram'] ?? '',
        'tiktok' => $_POST['tiktok'] ?? '',
        'youtube' => $_POST['youtube'] ?? '',
        'whatsapp' => $_POST['whatsapp'] ?? '',
        'telegram' => $_POST['telegram'] ?? '',
        'twitter' => $_POST['twitter'] ?? ''
    ];
    
    // Filtrar redes vacías
    $redes = array_filter($redes, function($valor) {
        return !empty($valor);
    });
    
    $resultado = $tienda->guardar_redes_sociales($id_tienda, $redes);
    
    if ($resultado) {
        echo json_encode([
            'success' => true,
            'message' => 'Redes sociales actualizadas correctamente'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'No se realizaron cambios'
        ]);
    }
}

function guardarPoliticas() {
    global $tienda;
    
    $id_tienda = $_SESSION['id_tienda'] ?? 0;
    
    if (!$id_tienda) {
        echo json_encode(['error' => 'no_tienda']);
        exit;
    }
    
    // Construir array de políticas
    $politicas = [
        'cambios' => $_POST['politica_cambios'] ?? '',
        'devoluciones' => $_POST['politica_devoluciones'] ?? '',
        'envios' => $_POST['politica_envios'] ?? '',
        'pagos' => $_POST['politica_pagos'] ?? '',
        'privacidad' => $_POST['politica_privacidad'] ?? '',
        'terminos' => $_POST['politica_terminos'] ?? ''
    ];
    
    // Filtrar políticas vacías
    $politicas = array_filter($politicas, function($valor) {
        return !empty($valor);
    });
    
    $resultado = $tienda->guardar_politicas($id_tienda, $politicas);
    
    if ($resultado) {
        echo json_encode([
            'success' => true,
            'message' => 'Políticas actualizadas correctamente'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'No se realizaron cambios'
        ]);
    }
}

function verificarTienda() {
    global $tienda;
    
    $usuario_tiene_tienda = $tienda->usuario_tiene_tienda($_SESSION['id']);
    
    if ($usuario_tiene_tienda) {
        // Obtener datos de la tienda para la sesión
        $tienda_data = $tienda->obtener_tienda_por_usuario($_SESSION['id']);
        if (!empty($tienda_data)) {
            $_SESSION['id_tienda'] = $tienda_data[0]->id;
        }
    }
    
    echo json_encode([
        'success' => true,
        'tiene_tienda' => $usuario_tiene_tienda,
        'id_tienda' => $_SESSION['id_tienda'] ?? null
    ]);
}