<?php
// /Controllers/TiendaController.php
session_start();
require_once '../Models/Tienda.php';

$tienda = new Tienda();

// Verificar sesión para todas las operaciones
if (!isset($_SESSION['id'])) {
    echo json_encode(['error' => 'no_sesion']);
    exit;
}

if ($_POST['funcion'] == 'obtener_datos_tienda') {
    // Obtener datos de la tienda del vendedor actual
    $resultado = $tienda->obtener_tienda_por_usuario($_SESSION['id']);
    
    if (!empty($resultado)) {
        echo json_encode($resultado[0]);
    } else {
        echo json_encode(['error' => 'no_tienda']);
    }
}

if ($_POST['funcion'] == 'obtener_estadisticas') {
    // Obtener estadísticas de la tienda
    $id_tienda = $_POST['id_tienda'] ?? $_SESSION['id_tienda'] ?? 0;
    
    if ($id_tienda) {
        $stats = $tienda->obtener_estadisticas($id_tienda);
        echo json_encode($stats);
    } else {
        echo json_encode(['error' => 'no_tienda']);
    }
}

if ($_POST['funcion'] == 'actualizar_tienda') {
    // Actualizar datos básicos de la tienda
    $id_tienda = $_SESSION['id_tienda'] ?? 0;
    
    if (!$id_tienda) {
        echo json_encode(['error' => 'no_tienda']);
        exit;
    }
    
    $datos = [
        'nombre' => $_POST['nombre'] ?? null,
        'descripcion' => $_POST['descripcion'] ?? null,
        'email_contacto' => $_POST['email_contacto'] ?? null,
        'telefono' => $_POST['telefono'] ?? null,
        'whatsapp' => $_POST['whatsapp'] ?? null,
        'horario_atencion' => $_POST['horario_atencion'] ?? null,
        'direccion' => $_POST['direccion'] ?? null,
        'id_municipio' => $_POST['id_municipio'] ?? null
    ];
    
    $resultado = $tienda->actualizar_tienda($id_tienda, $datos);
    
    if ($resultado) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'no_cambios']);
    }
}

if ($_POST['funcion'] == 'subir_logo') {
    // Subir nuevo logo
    $id_tienda = $_SESSION['id_tienda'] ?? 0;
    
    if (!$id_tienda) {
        echo json_encode(['error' => 'no_tienda']);
        exit;
    }
    
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] == 0) {
        $archivo = $_FILES['logo'];
        $extension = pathinfo($archivo['name'], PATHINFO_EXTENSION);
        $extensiones_permitidas = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        if (in_array(strtolower($extension), $extensiones_permitidas)) {
            if ($archivo['size'] <= 2097152) { // 2MB
                $nombre_archivo = 'tienda_' . $id_tienda . '_' . time() . '.' . $extension;
                $ruta_destino = '../Util/Img/Stores/' . $nombre_archivo;
                
                if (move_uploaded_file($archivo['tmp_name'], $ruta_destino)) {
                    $resultado = $tienda->actualizar_logo($id_tienda, $nombre_archivo);
                    
                    if ($resultado) {
                        echo json_encode([
                            'success' => true,
                            'logo' => $nombre_archivo
                        ]);
                    } else {
                        echo json_encode(['error' => 'bd_error']);
                    }
                } else {
                    echo json_encode(['error' => 'upload_error']);
                }
            } else {
                echo json_encode(['error' => 'archivo_grande']);
            }
        } else {
            echo json_encode(['error' => 'extension_no_permitida']);
        }
    } else {
        echo json_encode(['error' => 'no_archivo']);
    }
}
