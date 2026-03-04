<?php
// Controllers/TabpanelConfigController.php
session_start();

// Incluir modelos
include_once '../Models/Usuario.php';
include_once '../Models/TabpanelConfig.php';

// Verificar sesión
if (empty($_SESSION['id'])) {
    echo json_encode(['error' => 'no_sesion']);
    exit();
}

$id_usuario = $_SESSION['id'];
$configuracion = new Configuracion();

header('Content-Type: application/json');

try {
    if (!isset($_POST['funcion'])) {
        throw new Exception('Función no especificada');
    }
    
    $funcion = $_POST['funcion'];
    
    switch ($funcion) {
        case 'guardar_configuracion':
            guardarConfiguracion($configuracion, $id_usuario);
            break;
            
        case 'cargar_configuracion':
            cargarConfiguracion($configuracion, $id_usuario);
            break;
            
        case 'exportar_datos':
            exportarDatos($configuracion, $id_usuario);
            break;
            
        case 'eliminar_cuenta':
            eliminarCuenta($configuracion, $id_usuario);
            break;
            
        case 'obtener_monedas':
            obtenerMonedas($configuracion);
            break;
            
        case 'obtener_tasa_cambio':
            obtenerTasaCambio($configuracion);
            break;
            
        case 'aplicar_tema_instantaneo':
            aplicarTemaInstantaneo($configuracion, $id_usuario);
            break;
            
        case 'obtener_estadisticas':
            obtenerEstadisticas($configuracion, $id_usuario);
            break;
            
        case 'cargar_historial_cambios':
            cargarHistorialCambios($configuracion, $id_usuario);
            break;
            
        default:
            throw new Exception('Función no válida: ' . $funcion);
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

// ========== FUNCIONES DEL CONTROLADOR ==========

function guardarConfiguracion($configuracion, $id_usuario) {
    // Validar tipo de configuración
    $tipo = $_POST['tipo_configuracion'] ?? '';
    if (empty($tipo)) {
        throw new Exception('Tipo de configuración no especificado');
    }
    
    $tipos_permitidos = ['notificaciones', 'privacidad', 'visualizacion'];
    if (!in_array($tipo, $tipos_permitidos)) {
        throw new Exception('Tipo de configuración no permitido');
    }
    
    // Validar y decodificar datos
    $datos_json = $_POST['datos_configuracion'] ?? '{}';
    $datos_array = json_decode($datos_json, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Formato JSON inválido: ' . json_last_error_msg());
    }
    
    if (!is_array($datos_array)) {
        throw new Exception('Los datos de configuración deben ser un objeto JSON');
    }
    
    // Validar estructura según tipo
    switch ($tipo) {
        case 'notificaciones':
            validarDatosNotificaciones($datos_array);
            break;
        case 'privacidad':
            validarDatosPrivacidad($datos_array);
            break;
        case 'visualizacion':
            validarDatosVisualizacion($datos_array);
            break;
    }
    
    // Guardar configuración
    $resultado = $configuracion->guardarConfiguracion($id_usuario, $tipo, $datos_json);
    
    if (!$resultado) {
        throw new Exception('No se pudo guardar la configuración en la base de datos');
    }
    
    echo json_encode([
        'success' => true,
        'mensaje' => 'Configuración guardada correctamente',
        'tipo' => $tipo,
        'datos_recibidos' => $datos_array // Opcional, para depuración
    ]);
}

// Funciones de validación
function validarDatosNotificaciones($datos) {
    $campos_requeridos = ['email', 'pedidos', 'promociones', 'productos', 'resenas'];
    
    foreach ($campos_requeridos as $campo) {
        if (!isset($datos[$campo])) {
            throw new Exception("Campo '$campo' requerido en configuración de notificaciones");
        }
        if (!is_bool($datos[$campo])) {
            throw new Exception("El campo '$campo' debe ser booleano (true/false)");
        }
    }
}

function validarDatosPrivacidad($datos) {
    $campos_requeridos = ['perfil_publico', 'actividad_publica', 'aparecer_busquedas', 'compartir_datos'];
    
    foreach ($campos_requeridos as $campo) {
        if (!isset($datos[$campo])) {
            throw new Exception("Campo '$campo' requerido en configuración de privacidad");
        }
        if (!is_bool($datos[$campo])) {
            throw new Exception("El campo '$campo' debe ser booleano (true/false)");
        }
    }
}

function validarDatosVisualizacion($datos) {
    $campos_requeridos = ['tema', 'densidad', 'idioma', 'moneda'];
    $temas_permitidos = ['claro', 'oscuro', 'auto'];
    $densidades_permitidas = ['comoda', 'normal', 'compacta'];
    $idiomas_permitidos = ['es', 'en', 'pt'];
    $monedas_permitidas = ['USD', 'EUR', 'PEN', 'MXN', 'COP', 'CLP', 'CUP'];
    
    foreach ($campos_requeridos as $campo) {
        if (!isset($datos[$campo])) {
            throw new Exception("Campo '$campo' requerido en configuración de visualización");
        }
    }
    
    if (!in_array($datos['tema'], $temas_permitidos)) {
        throw new Exception("Tema no válido. Permitidos: " . implode(', ', $temas_permitidos));
    }
    
    if (!in_array($datos['densidad'], $densidades_permitidas)) {
        throw new Exception("Densidad no válida. Permitidas: " . implode(', ', $densidades_permitidas));
    }
    
    if (!in_array($datos['idioma'], $idiomas_permitidos)) {
        throw new Exception("Idioma no válido. Permitidos: " . implode(', ', $idiomas_permitidos));
    }
    
    if (!in_array($datos['moneda'], $monedas_permitidas)) {
        throw new Exception("Moneda no válida. Permitidas: " . implode(', ', $monedas_permitidas));
    }
}

function cargarConfiguracion($configuracion, $id_usuario) {
    $configuraciones = $configuracion->cargarConfiguraciones($id_usuario);
    
    echo json_encode([
        'success' => true,
        'configuraciones' => $configuraciones
    ]);
}

function exportarDatos($configuracion, $id_usuario) {
    $formatos = $_POST['formatos'] ?? [];
    $formato = $_POST['formato'] ?? 'json';
    
    if (!is_array($formatos) || empty($formatos)) {
        throw new Exception('No se especificaron formatos para exportar');
    }
    
    $formatos_permitidos = ['perfil', 'pedidos', 'resenas', 'direcciones', 'preferencias'];
    foreach ($formatos as $formato_item) {
        if (!in_array($formato_item, $formatos_permitidos)) {
            throw new Exception('Formato no permitido: ' . $formato_item);
        }
    }
    
    $datos = $configuracion->generarDatosExportacion($id_usuario, $formatos);
    
    echo json_encode([
        'success' => true,
        'datos' => $datos,
        'formato' => $formato,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}

function eliminarCuenta($configuracion, $id_usuario) {
    $confirmacion = $_POST['confirmacion'] ?? '';
    
    if ($confirmacion !== 'ELIMINAR') {
        throw new Exception('Confirmación requerida. Escribe ELIMINAR para confirmar.');
    }
    
    $resultado = $configuracion->desactivarCuenta($id_usuario);
    
    if ($resultado) {
        // Destruir sesión
        session_destroy();
        
        echo json_encode([
            'success' => true,
            'mensaje' => 'Cuenta eliminada correctamente',
            'redirect' => '../index.php'
        ]);
    } else {
        throw new Exception('No se pudo eliminar la cuenta');
    }
}

function obtenerMonedas($configuracion) {
    $monedas = $configuracion->obtenerMonedas();
    
    echo json_encode([
        'success' => true,
        'monedas' => $monedas
    ]);
}

function obtenerTasaCambio($configuracion) {
    $moneda = $_POST['moneda'] ?? 'CUP';
    
    $tasa = $configuracion->obtenerTasaCambio($moneda);
    
    if ($tasa) {
        echo json_encode([
            'success' => true,
            'tasa_cambio' => (float)$tasa['tasa_cambio'],
            'moneda' => [
                'codigo' => $tasa['codigo'],
                'nombre' => $tasa['nombre'],
                'simbolo' => $tasa['simbolo']
            ]
        ]);
    } else {
        throw new Exception('Moneda no encontrada: ' . $moneda);
    }
}

function aplicarTemaInstantaneo($configuracion, $id_usuario) {
    $tema = $_POST['tema'] ?? 'claro';
    
    $temas_permitidos = ['claro', 'oscuro', 'auto'];
    if (!in_array($tema, $temas_permitidos)) {
        throw new Exception('Tema no válido');
    }
    
    $datos = json_encode(['tema' => $tema]);
    $resultado = $configuracion->guardarConfiguracion($id_usuario, 'visualizacion', $datos);
    
    echo json_encode([
        'success' => $resultado,
        'tema' => $tema,
        'mensaje' => 'Tema aplicado correctamente'
    ]);
}

function obtenerEstadisticas($configuracion, $id_usuario) {
    $estadisticas = $configuracion->obtenerEstadisticasUsuario($id_usuario);
    
    echo json_encode([
        'success' => true,
        'estadisticas' => $estadisticas
    ]);
}

function cargarHistorialCambios($configuracion, $id_usuario) {
    // Esta función cargaría el historial de cambios desde la base de datos
    // Por ahora, retornamos un array vacío ya que no tenemos el método en el modelo
    
    echo json_encode([
        'success' => true,
        'historial' => [],
        'mensaje' => 'Función en desarrollo'
    ]);
}
?>