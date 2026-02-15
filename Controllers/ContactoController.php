<?php
// ContactoController.php - Versión con desencriptación de ID

// =============================================
// CONFIGURACIÓN INICIAL
// =============================================
ob_start();
ini_set('display_errors', 0);
ini_set('log_errors', 1);
date_default_timezone_set('America/Havana');

// Incluir configuración y modelo
require_once __DIR__ . '/../Util/Config/config.php';
require_once __DIR__ . '/../Models/Contacto.php';

// =============================================
// FUNCIÓN PARA ENVIAR RESPUESTA JSON
// =============================================
function enviarJson($data, $statusCode = 200) {
    // Limpiar cualquier output anterior
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=UTF-8');
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

// =============================================
// FUNCIÓN PARA DESENCRIPTAR ID
// =============================================
function desencriptarID($id_encrypted) {
    try {
        if (empty($id_encrypted)) {
            return null;
        }
        
        $formateado = str_replace(" ", "+", $id_encrypted);
        $id = openssl_decrypt($formateado, CODE, KEY);
        
        if (!is_numeric($id)) {
            return null;
        }
        
        return intval($id);
    } catch (Exception $e) {
        error_log("Error desencriptando ID: " . $e->getMessage());
        return null;
    }
}

// =============================================
// MANEJO DE ERRORES NO CAPTURADOS
// =============================================
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        error_log("Error fatal en ContactoController: " . print_r($error, true));
        enviarJson([
            'status' => 'error',
            'message' => 'Error interno del servidor.'
        ], 500);
    }
});

// =============================================
// VALIDACIÓN DE MÉTODO HTTP
// =============================================
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    enviarJson([
        'status' => 'error',
        'message' => 'Método no permitido. Use POST.'
    ], 405);
}

try {
    // =============================================
    // 1. SANITIZAR Y VALIDAR DATOS
    // =============================================
    
    // Función helper para sanitizar
    function sanitizar($input) {
        if ($input === null || $input === '') {
            return '';
        }
        return trim(htmlspecialchars($input, ENT_QUOTES, 'UTF-8'));
    }
    
    // Obtener y sanitizar datos
    $datos = [
        'nombre' => sanitizar($_POST['nombre'] ?? ''),
        'email' => isset($_POST['email']) ? trim(filter_var($_POST['email'], FILTER_SANITIZE_EMAIL)) : '',
        'telefono' => sanitizar($_POST['telefono'] ?? ''),
        'asunto' => sanitizar($_POST['asunto'] ?? ''),
        'mensaje' => sanitizar($_POST['mensaje'] ?? '')
    ];
    
    // Para checkbox: manejar correctamente si no está presente
    $aceptaPrivacidad = isset($_POST['privacidad']) && $_POST['privacidad'] === 'on';
    
    // =============================================
    // 2. VALIDACIONES COMPLETAS
    // =============================================
    $errores = [];
    
    // Validar nombre (2-100 caracteres)
    if (empty($datos['nombre']) || strlen($datos['nombre']) < 2) {
        $errores[] = 'El nombre debe tener al menos 2 caracteres.';
    } elseif (strlen($datos['nombre']) > 100) {
        $errores[] = 'El nombre no puede exceder los 100 caracteres.';
    }
    
    // Validar email
    if (empty($datos['email'])) {
        $errores[] = 'El email es obligatorio.';
    } elseif (!filter_var($datos['email'], FILTER_VALIDATE_EMAIL)) {
        $errores[] = 'Por favor ingresa un email válido.';
    } elseif (strlen($datos['email']) > 100) {
        $errores[] = 'El email no puede exceder los 100 caracteres.';
    }
    
    // Validar asunto (1-100 caracteres)
    if (empty($datos['asunto'])) {
        $errores[] = 'Por favor selecciona un asunto.';
    } elseif (strlen($datos['asunto']) > 100) {
        $errores[] = 'El asunto no puede exceder los 100 caracteres.';
    }
    
    // Validar mensaje (10-2000 caracteres)
    if (empty($datos['mensaje'])) {
        $errores[] = 'El mensaje es obligatorio.';
    } elseif (strlen($datos['mensaje']) < 10) {
        $errores[] = 'El mensaje debe tener al menos 10 caracteres.';
    } elseif (strlen($datos['mensaje']) > 2000) {
        $errores[] = 'El mensaje no puede exceder los 2000 caracteres.';
    }
    
    // Validar teléfono (opcional, 8-20 caracteres si se proporciona)
    if (!empty($datos['telefono']) && !preg_match('/^[\d\s\-\+\(\)]{8,20}$/', $datos['telefono'])) {
        $errores[] = 'El teléfono no tiene un formato válido (8-20 dígitos).';
    }
    
    // Validar checkbox de privacidad
    if (!$aceptaPrivacidad) {
        $errores[] = 'Debes aceptar la política de privacidad y los términos y condiciones.';
    }
    
    // Validar honeypot (protección contra bots)
    if (!empty($_POST['website'])) {
        error_log("Bot detectado - honeypot lleno: " . $_POST['website']);
        enviarJson([
            'status' => 'success',
            'message' => '¡Mensaje enviado correctamente!'
        ]);
    }
    
    // Validar token CSRF
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $errores[] = 'Token de seguridad inválido o expirado. Por favor recarga la página.';
    }
    
    // Si hay errores, retornarlos
    if (!empty($errores)) {
        enviarJson([
            'status' => 'error',
            'message' => implode(' ', $errores),
            'errors' => $errores
        ], 400);
    }
    
    // =============================================
    // 3. OBTENER ID DE USUARIO SI EXISTE
    // =============================================
    $id_usuario = null;
    
    // Intentar obtener ID del usuario de varias formas:
    
    // 1. Desde sesión (como en CarritoController)
    if (isset($_SESSION['id'])) {
        $id_usuario = intval($_SESSION['id']);
    }
    // 2. Desde variable encriptada (si se envía)
    elseif (isset($_POST['id_usuario_encrypted'])) {
        $id_usuario = desencriptarID($_POST['id_usuario_encrypted']);
    }
    // 3. Desde parámetro directo (para testing)
    elseif (isset($_POST['id_usuario'])) {
        $id_usuario = intval($_POST['id_usuario']);
    }
    
    error_log("ID Usuario obtenido: " . ($id_usuario ?: 'NULL (visitante)'));
    
    // =============================================
    // 4. CREAR INSTANCIA DEL MODELO
    // =============================================
    $contactoModel = new Contacto();
    
    // =============================================
    // 5. VERIFICAR SPAM (OPCIONAL - descomentar si quieres)
    // =============================================
    /*
    if ($contactoModel->verificarMensajeReciente($datos['email'], 15)) {
        enviarJson([
            'status' => 'error',
            'message' => 'Ya has enviado un mensaje recientemente. Por favor espera unos minutos antes de enviar otro.'
        ], 429);
    }
    */
    
    // =============================================
    // 6. PREPARAR DATOS PARA GUARDAR
    // =============================================
    $datosGuardar = [
        'nombre' => $datos['nombre'],
        'email' => $datos['email'],
        'telefono' => !empty($datos['telefono']) ? $datos['telefono'] : null,
        'asunto' => $datos['asunto'],
        'mensaje' => $datos['mensaje'],
        'id_usuario' => $id_usuario
    ];
    
    // =============================================
    // 7. GUARDAR EN BASE DE DATOS
    // =============================================
    $guardado = $contactoModel->guardarMensaje($datosGuardar);
    
    if (!$guardado) {
        throw new Exception('Error al guardar el mensaje en la base de datos.');
    }
    
    // =============================================
    // 8. RESPUESTA DE ÉXITO
    // =============================================
    
    // Generar un ID de referencia (puedes usar un hash único)
    $referencia_id = 'MSG' . date('Ymd') . rand(1000, 9999);
    
    error_log("Mensaje de contacto guardado - Email: {$datos['email']}, Asunto: {$datos['asunto']}, Referencia: $referencia_id");
    
    enviarJson([
        'status' => 'success',
        'message' => '¡Mensaje enviado correctamente! Te responderemos en 24-48 horas hábiles.',
        'data' => [
            'id' => $referencia_id, // Usamos referencia en lugar de ID de BD
            'nombre' => $datos['nombre'],
            'email' => $datos['email'],
            'asunto' => $datos['asunto'],
            'timestamp' => date('Y-m-d H:i:s'),
            'referencia' => $referencia_id
        ]
    ]);
    
} catch (Exception $e) {
    error_log("Error en ContactoController: " . $e->getMessage());
    enviarJson([
        'status' => 'error',
        'message' => 'Ocurrió un error al procesar tu mensaje: ' . $e->getMessage()
    ], 500);
}

// Limpiar buffer final
ob_end_flush();
?>