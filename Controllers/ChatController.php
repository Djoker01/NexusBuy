<?php
// Controllers/ChatController.php
include_once '../Models/Chat.php';
include_once '../Models/Usuario.php';

session_start();

// ==================== FUNCIONES AUXILIARES ====================

// Verificar si el usuario está autenticado
function verificar_autenticacion() {
    if (empty($_SESSION['id'])) {
        return array(
            'autenticado' => false,
            'mensaje' => 'Debes iniciar sesión para usar el chat'
        );
    }
    return array('autenticado' => true);
}

// Obtener datos del usuario actual desde la sesión
function obtener_usuario_actual() {
    if (isset($_SESSION['id'])) {
        return array(
            'id' => $_SESSION['id'],
            'nombre' => $_SESSION['nombre'] ?? '',
            'email' => $_SESSION['email'] ?? '',
            'role' => $_SESSION['role'] ?? 'cliente'
        );
    }
    return null;
}

// Validar que el usuario tenga acceso a un email específico
function validar_acceso_email($email_solicitado) {
    $usuario_actual = obtener_usuario_actual();
    
    if (!$usuario_actual) {
        return array('acceso' => false, 'mensaje' => 'No autenticado');
    }
    
    // Administradores pueden acceder a cualquier email
    if ($usuario_actual['role'] == 'admin') {
        return array('acceso' => true);
    }
    
    // Usuarios normales solo pueden acceder a su propio email
    if ($usuario_actual['email'] == $email_solicitado) {
        return array('acceso' => true);
    }
    
    return array('acceso' => false, 'mensaje' => 'No tienes permiso para acceder a estos mensajes');
}

// Función para calcular tiempo transcurrido
function calcular_tiempo_transcurrido($fecha) {
    $fecha_dt = new DateTime($fecha);
    $ahora = new DateTime();
    $diferencia = $ahora->diff($fecha_dt);
    
    if ($diferencia->days > 0) {
        return $diferencia->days . ' día' . ($diferencia->days > 1 ? 's' : '');
    } elseif ($diferencia->h > 0) {
        return $diferencia->h . ' hora' . ($diferencia->h > 1 ? 's' : '');
    } elseif ($diferencia->i > 0) {
        return $diferencia->i . ' minuto' . ($diferencia->i > 1 ? 's' : '');
    } else {
        return 'Hace unos segundos';
    }
}

// Sanitizar entrada de texto
function sanitizar_texto($texto) {
    return htmlspecialchars(strip_tags(trim($texto)), ENT_QUOTES, 'UTF-8');
}

// ==================== FUNCIONES PRINCIPALES ====================

$chat = new Chat();
$usuario = new Usuario();

// Verificar que haya una función solicitada
if (!isset($_POST['funcion'])) {
    echo json_encode(array(
        'estado' => 'error',
        'mensaje' => 'No se especificó la función'
    ));
    exit();
}

// ==================== ENVIAR MENSAJE ====================
if ($_POST['funcion'] == 'enviar_mensaje') {
    // Verificar autenticación
    $auth = verificar_autenticacion();
    if (!$auth['autenticado']) {
        echo json_encode(array(
            'estado' => 'error',
            'mensaje' => $auth['mensaje']
        ));
        exit();
    }
    
    // Obtener datos del usuario actual
    $usuario_actual = obtener_usuario_actual();
    
    // Obtener y validar datos del mensaje
    $mensaje = isset($_POST['mensaje']) ? sanitizar_texto($_POST['mensaje']) : '';
    $email_usuario = isset($_POST['email']) ? filter_var($_POST['email'], FILTER_SANITIZE_EMAIL) : '';
    $nombre_usuario = isset($_POST['nombre']) ? sanitizar_texto($_POST['nombre']) : '';
    
    // Validar campos requeridos
    if (empty($mensaje)) {
        echo json_encode(array(
            'estado' => 'error',
            'mensaje' => 'El mensaje no puede estar vacío'
        ));
        exit();
    }
    
    if (empty($email_usuario) || !filter_var($email_usuario, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(array(
            'estado' => 'error',
            'mensaje' => 'Email no válido'
        ));
        exit();
    }
    
    // Verificar que el email del mensaje coincida con el email del usuario logueado
    if ($email_usuario != $usuario_actual['email']) {
        echo json_encode(array(
            'estado' => 'error',
            'mensaje' => 'Error de autenticación: el email no coincide'
        ));
        exit();
    }
    
    // Usar nombre de la sesión si no se proporciona otro
    if (empty($nombre_usuario)) {
        $nombre_usuario = $usuario_actual['nombre'];
    }
    
    // Validar longitud del mensaje
    if (strlen($mensaje) > 1000) {
        echo json_encode(array(
            'estado' => 'error',
            'mensaje' => 'El mensaje es demasiado largo (máximo 1000 caracteres)'
        ));
        exit();
    }
    
    try {
        // Guardar mensaje en la base de datos
        $id_mensaje = $chat->guardar_mensaje(
            $usuario_actual['id'],
            $nombre_usuario,
            $email_usuario,
            $mensaje,
            'usuario'
        );
        
        // Registrar actividad del chat
        error_log("Chat - Mensaje enviado: Usuario ID {$usuario_actual['id']}, Email: {$email_usuario}");
        
        echo json_encode(array(
            'estado' => 'success',
            'mensaje' => 'Mensaje enviado correctamente',
            'id_mensaje' => $id_mensaje,
            'fecha' => date('H:i')
        ));
        
    } catch (Exception $e) {
        error_log("Error en ChatController - enviar_mensaje: " . $e->getMessage());
        echo json_encode(array(
            'estado' => 'error',
            'mensaje' => 'Error interno al guardar el mensaje'
        ));
    }
}

// ==================== OBTENER MENSAJES ====================
elseif ($_POST['funcion'] == 'obtener_mensajes') {
    // Verificar autenticación
    $auth = verificar_autenticacion();
    if (!$auth['autenticado']) {
        echo json_encode(array(
            'estado' => 'error',
            'mensaje' => $auth['mensaje']
        ));
        exit();
    }
    
    $usuario_actual = obtener_usuario_actual();
    
    // Obtener email del usuario DESDE LA SESIÓN, no del POST
    // Esto es importante para evitar que usuarios envíen emails de otros
    $email_usuario = $usuario_actual['email'];
    
    if (empty($email_usuario)) {
        // Si no hay email en la sesión, usar el ID para buscar
        echo json_encode(array(
            'estado' => 'error',
            'mensaje' => 'Tu perfil no tiene email configurado. Actualiza tu información.'
        ));
        exit();
    }
    
    try {
        // Obtener mensajes usando el email de la sesión
        $chat->obtener_mensajes_por_email($email_usuario);
        $mensajes = array();
        
        foreach ($chat->objetos as $msg) {
            $mensajes[] = array(
                'id' => $msg->id,
                'nombre' => htmlspecialchars($msg->nombre_usuario, ENT_QUOTES, 'UTF-8'),
                'mensaje' => htmlspecialchars($msg->mensaje, ENT_QUOTES, 'UTF-8'),
                'tipo' => $msg->tipo,
                'fecha' => date('H:i', strtotime($msg->fecha_envio)),
                'leido' => (bool)$msg->leido,
                'fecha_completa' => date('d/m/Y H:i', strtotime($msg->fecha_envio))
            );
        }
        
        // Marcar como leídos
        $chat->marcar_mensajes_leidos($email_usuario);
        
        // Registrar actividad
        error_log("Chat - Mensajes obtenidos para usuario ID {$usuario_actual['id']}, Email: {$email_usuario}");
        
        echo json_encode(array(
            'estado' => 'success',
            'mensajes' => $mensajes,
            'total' => count($mensajes),
            'email_usuario' => $email_usuario // Para depuración
        ));
        
    } catch (Exception $e) {
        error_log("Error en ChatController - obtener_mensajes: " . $e->getMessage());
        echo json_encode(array(
            'estado' => 'error',
            'mensaje' => 'Error interno al obtener mensajes'
        ));
    }
}

// ==================== ENVIAR MENSAJE COMO AGENTE (ADMIN) ====================
elseif ($_POST['funcion'] == 'enviar_mensaje_agente') {
    // Verificar que sea admin
    $usuario_actual = obtener_usuario_actual();
    if (!$usuario_actual || $usuario_actual['role'] != 'admin') {
        echo json_encode(array(
            'estado' => 'error',
            'mensaje' => 'No tienes permisos de administrador'
        ));
        exit();
    }
    
    // Obtener y validar datos
    $email_destino = isset($_POST['email_destino']) ? filter_var($_POST['email_destino'], FILTER_SANITIZE_EMAIL) : '';
    $mensaje = isset($_POST['mensaje']) ? sanitizar_texto($_POST['mensaje']) : '';
    
    if (empty($email_destino) || !filter_var($email_destino, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(array(
            'estado' => 'error',
            'mensaje' => 'Email de destino no válido'
        ));
        exit();
    }
    
    if (empty($mensaje)) {
        echo json_encode(array(
            'estado' => 'error',
            'mensaje' => 'El mensaje no puede estar vacío'
        ));
        exit();
    }
    
    // Validar longitud del mensaje
    if (strlen($mensaje) > 1000) {
        echo json_encode(array(
            'estado' => 'error',
            'mensaje' => 'El mensaje es demasiado largo (máximo 1000 caracteres)'
        ));
        exit();
    }
    
    try {
        // Guardar mensaje como agente
        $nombre_agente = $usuario_actual['nombre'] . ' (Soporte)';
        $id_mensaje = $chat->guardar_mensaje(
            $usuario_actual['id'],
            $nombre_agente,
            $email_destino,
            $mensaje,
            'agente'
        );
        
        // Registrar actividad del admin
        error_log("Chat Admin - Mensaje enviado por admin ID {$usuario_actual['id']} a {$email_destino}");
        
        echo json_encode(array(
            'estado' => 'success',
            'mensaje' => 'Respuesta enviada correctamente',
            'id_mensaje' => $id_mensaje
        ));
        
    } catch (Exception $e) {
        error_log("Error en ChatController - enviar_mensaje_agente: " . $e->getMessage());
        echo json_encode(array(
            'estado' => 'error',
            'mensaje' => 'Error interno al enviar respuesta'
        ));
    }
}

// ==================== OBTENER CONVERSACIONES (ADMIN) ====================
elseif ($_POST['funcion'] == 'obtener_conversaciones') {
    // Verificar que sea admin
    $usuario_actual = obtener_usuario_actual();
    if (!$usuario_actual || $usuario_actual['role'] != 'admin') {
        echo json_encode(array(
            'estado' => 'error',
            'mensaje' => 'No tienes permisos de administrador'
        ));
        exit();
    }
    
    // Obtener límite (opcional)
    $limit = isset($_POST['limit']) ? intval($_POST['limit']) : 20;
    $limit = min($limit, 100); // Limitar máximo a 100
    
    try {
        // Obtener conversaciones
        $chat->obtener_conversaciones($limit);
        
        $conversaciones = array();
        foreach ($chat->objetos as $conv) {
            $conversaciones[] = array(
                'email' => $conv->email_usuario,
                'nombre' => htmlspecialchars($conv->nombre_usuario, ENT_QUOTES, 'UTF-8'),
                'ultimo_mensaje' => $conv->ultimo_mensaje,
                'total_mensajes' => (int)$conv->total_mensajes,
                'mensajes_no_leidos' => (int)$conv->mensajes_no_leidos,
                'hace' => calcular_tiempo_transcurrido($conv->ultimo_mensaje),
                'fecha_formateada' => date('d/m/Y H:i', strtotime($conv->ultimo_mensaje))
            );
        }
        
        echo json_encode(array(
            'estado' => 'success',
            'conversaciones' => $conversaciones,
            'total' => count($conversaciones)
        ));
        
    } catch (Exception $e) {
        error_log("Error en ChatController - obtener_conversaciones: " . $e->getMessage());
        echo json_encode(array(
            'estado' => 'error',
            'mensaje' => 'Error interno al obtener conversaciones'
        ));
    }
}

// ==================== BUSCAR CONVERSACIONES (ADMIN) ====================
elseif ($_POST['funcion'] == 'buscar_conversaciones') {
    // Verificar que sea admin
    $usuario_actual = obtener_usuario_actual();
    if (!$usuario_actual || $usuario_actual['role'] != 'admin') {
        echo json_encode(array(
            'estado' => 'error',
            'mensaje' => 'No tienes permisos de administrador'
        ));
        exit();
    }
    
    $busqueda = isset($_POST['busqueda']) ? sanitizar_texto($_POST['busqueda']) : '';
    
    if (empty($busqueda) || strlen($busqueda) < 2) {
        echo json_encode(array(
            'estado' => 'error',
            'mensaje' => 'La búsqueda debe tener al menos 2 caracteres'
        ));
        exit();
    }
    
    try {
        $chat->buscar_conversaciones($busqueda);
        $resultados = array();
        
        foreach ($chat->objetos as $conv) {
            $resultados[] = array(
                'email' => $conv->email_usuario,
                'nombre' => htmlspecialchars($conv->nombre_usuario, ENT_QUOTES, 'UTF-8'),
                'ultimo_mensaje' => $conv->ultimo_mensaje,
                'hace' => calcular_tiempo_transcurrido($conv->ultimo_mensaje)
            );
        }
        
        echo json_encode(array(
            'estado' => 'success',
            'resultados' => $resultados,
            'total' => count($resultados)
        ));
        
    } catch (Exception $e) {
        error_log("Error en ChatController - buscar_conversaciones: " . $e->getMessage());
        echo json_encode(array(
            'estado' => 'error',
            'mensaje' => 'Error interno al buscar conversaciones'
        ));
    }
}

// ==================== VERIFICAR MENSAJES NO LEÍDOS ====================
elseif ($_POST['funcion'] == 'verificar_no_leidos') {
    // Verificar autenticación
    $auth = verificar_autenticacion();
    if (!$auth['autenticado']) {
        echo json_encode(array(
            'estado' => 'error',
            'mensaje' => $auth['mensaje'],
            'total' => 0
        ));
        exit();
    }
    
    $usuario_actual = obtener_usuario_actual();
    $email_usuario = $usuario_actual['email'];
    
    if (empty($email_usuario)) {
        echo json_encode(array(
            'estado' => 'error',
            'mensaje' => 'Email no configurado en el perfil',
            'total' => 0
        ));
        exit();
    }
    
    try {
        $total = $chat->verificar_mensajes_no_leidos($email_usuario);
        
        echo json_encode(array(
            'estado' => 'success',
            'total' => (int)$total
        ));
        
    } catch (Exception $e) {
        error_log("Error en ChatController - verificar_no_leidos: " . $e->getMessage());
        echo json_encode(array(
            'estado' => 'error',
            'mensaje' => 'Error al verificar mensajes',
            'total' => 0
        ));
    }
}

// ==================== OBTENER CONVERSACIÓN COMPLETA (ADMIN) ====================
elseif ($_POST['funcion'] == 'obtener_conversacion_completa') {
    // Verificar que sea admin
    $usuario_actual = obtener_usuario_actual();
    if (!$usuario_actual || $usuario_actual['role'] != 'admin') {
        echo json_encode(array(
            'estado' => 'error',
            'mensaje' => 'No tienes permisos de administrador'
        ));
        exit();
    }
    
    $email_usuario = isset($_POST['email']) ? filter_var($_POST['email'], FILTER_SANITIZE_EMAIL) : '';
    
    if (empty($email_usuario)) {
        echo json_encode(array(
            'estado' => 'error',
            'mensaje' => 'Email no proporcionado'
        ));
        exit();
    }
    
    try {
        $chat->obtener_conversacion_por_email($email_usuario);
        $mensajes = array();
        
        foreach ($chat->objetos as $msg) {
            $mensajes[] = array(
                'id' => $msg->id,
                'usuario_id' => $msg->usuario_id,
                'nombre' => htmlspecialchars($msg->nombre_usuario, ENT_QUOTES, 'UTF-8'),
                'mensaje' => htmlspecialchars($msg->mensaje, ENT_QUOTES, 'UTF-8'),
                'tipo' => $msg->tipo,
                'leido' => (bool)$msg->leido,
                'fecha_envio' => $msg->fecha_envio,
                'fecha' => date('H:i', strtotime($msg->fecha_envio)),
                'fecha_completa' => date('d/m/Y H:i', strtotime($msg->fecha_envio)),
                'imagen_usuario' => $msg->imagen_usuario,
                'rol_usuario' => $msg->rol_usuario
            );
        }
        
        // Obtener información adicional del usuario
        $usuario_info = array();
        if (!empty($chat->objetos[0]->usuario_id)) {
            // Aquí podrías agregar una función para obtener más datos del usuario
            $usuario_info = array(
                'id' => $chat->objetos[0]->usuario_id,
                'tiene_cuenta' => true
            );
        } else {
            $usuario_info = array(
                'tiene_cuenta' => false
            );
        }
        
        echo json_encode(array(
            'estado' => 'success',
            'mensajes' => $mensajes,
            'usuario_info' => $usuario_info,
            'total' => count($mensajes)
        ));
        
    } catch (Exception $e) {
        error_log("Error en ChatController - obtener_conversacion_completa: " . $e->getMessage());
        echo json_encode(array(
            'estado' => 'error',
            'mensaje' => 'Error interno al obtener la conversación'
        ));
    }
}

// ==================== ELIMINAR CONVERSACIÓN (ADMIN) ====================
elseif ($_POST['funcion'] == 'eliminar_conversacion') {
    // Verificar que sea admin
    $usuario_actual = obtener_usuario_actual();
    if (!$usuario_actual || $usuario_actual['role'] != 'admin') {
        echo json_encode(array(
            'estado' => 'error',
            'mensaje' => 'No tienes permisos de administrador'
        ));
        exit();
    }
    
    $email_usuario = isset($_POST['email']) ? filter_var($_POST['email'], FILTER_SANITIZE_EMAIL) : '';
    
    if (empty($email_usuario)) {
        echo json_encode(array(
            'estado' => 'error',
            'mensaje' => 'Email no proporcionado'
        ));
        exit();
    }
    
    // Confirmar token de seguridad (opcional)
    $token = isset($_POST['token']) ? $_POST['token'] : '';
    if (empty($token) || $token !== md5('delete_chat_' . $email_usuario . '_' . date('Y-m-d'))) {
        echo json_encode(array(
            'estado' => 'error',
            'mensaje' => 'Token de seguridad inválido'
        ));
        exit();
    }
    
    try {
        $eliminados = $chat->eliminar_conversacion($email_usuario);
        
        // Registrar acción del admin
        error_log("Chat Admin - Conversación eliminada: Email {$email_usuario} por admin ID {$usuario_actual['id']}");
        
        echo json_encode(array(
            'estado' => 'success',
            'mensaje' => 'Conversación eliminada correctamente',
            'eliminados' => $eliminados
        ));
        
    } catch (Exception $e) {
        error_log("Error en ChatController - eliminar_conversacion: " . $e->getMessage());
        echo json_encode(array(
            'estado' => 'error',
            'mensaje' => 'Error interno al eliminar la conversación'
        ));
    }
}

// ==================== OBTENER ESTADÍSTICAS DEL CHAT (ADMIN) ====================
elseif ($_POST['funcion'] == 'obtener_estadisticas') {
    // Verificar que sea admin
    $usuario_actual = obtener_usuario_actual();
    if (!$usuario_actual || $usuario_actual['role'] != 'admin') {
        echo json_encode(array(
            'estado' => 'error',
            'mensaje' => 'No tienes permisos de administrador'
        ));
        exit();
    }
    
    try {
        // Obtener estadísticas del día
        $hoy = date('Y-m-d');
        $sql_hoy = "SELECT COUNT(*) as total_hoy FROM chat_mensajes WHERE DATE(fecha_envio) = :hoy";
        $query_hoy = $chat->acceso->prepare($sql_hoy);
        $query_hoy->execute(array(':hoy' => $hoy));
        $total_hoy = $query_hoy->fetch()->total_hoy;
        
        // Obtener conversaciones activas (últimas 24 horas)
        $sql_activas = "SELECT COUNT(DISTINCT email_usuario) as conversaciones_activas 
                       FROM chat_mensajes 
                       WHERE fecha_envio >= DATE_SUB(NOW(), INTERVAL 24 HOUR)";
        $query_activas = $chat->acceso->prepare($sql_activas);
        $query_activas->execute();
        $conversaciones_activas = $query_activas->fetch()->conversaciones_activas;
        
        // Obtener mensajes no leídos
        $sql_no_leidos = "SELECT COUNT(*) as total_no_leidos 
                         FROM chat_mensajes 
                         WHERE tipo = 'usuario' AND leido = FALSE";
        $query_no_leidos = $chat->acceso->prepare($sql_no_leidos);
        $query_no_leidos->execute();
        $total_no_leidos = $query_no_leidos->fetch()->total_no_leidos;
        
        echo json_encode(array(
            'estado' => 'success',
            'estadisticas' => array(
                'mensajes_hoy' => (int)$total_hoy,
                'conversaciones_activas' => (int)$conversaciones_activas,
                'mensajes_no_leidos' => (int)$total_no_leidos,
                'fecha' => date('d/m/Y')
            )
        ));
        
    } catch (Exception $e) {
        error_log("Error en ChatController - obtener_estadisticas: " . $e->getMessage());
        echo json_encode(array(
            'estado' => 'error',
            'mensaje' => 'Error interno al obtener estadísticas'
        ));
    }
}

// ==================== FUNCIÓN NO RECONOCIDA ====================
else {
    echo json_encode(array(
        'estado' => 'error',
        'mensaje' => 'Función no reconocida: ' . $_POST['funcion']
    ));
}
?>