<?php
include_once '../Models/Chat.php';
include_once '../Util/Config/config.php';

$chat = new Chat();
session_start();

// Headers para JSON
header('Content-Type: application/json; charset=UTF-8');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Verificar método
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Método no permitido']);
    exit();
}

// Verificar función
if (!isset($_POST['funcion'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Función no especificada']);
    exit();
}

// Función para desencriptar ID
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

// Función para convertir objeto a array
function objetoAArray($objeto) {
    if (is_object($objeto)) {
        return get_object_vars($objeto);
    }
    return $objeto;
}

// Función para convertir array de objetos a array de arrays
function objetosAArrays($objetos) {
    if (!is_array($objetos)) {
        return [];
    }
    
    $arrays = [];
    foreach ($objetos as $objeto) {
        $arrays[] = objetoAArray($objeto);
    }
    return $arrays;
}

// Función para enviar respuestas
function enviarRespuesta($success, $data = null, $error = null) {
    $respuesta = ['success' => $success];
    
    if ($success && $data !== null) {
        $respuesta['data'] = $data;
    }
    
    if (!$success && $error !== null) {
        $respuesta['error'] = $error;
    }
    
    echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);
    exit();
}

// =============================================
// FUNCIONES DEL CHAT
// =============================================

// Iniciar nueva conversación
if ($_POST['funcion'] == 'iniciar_conversacion') {
    try {
        error_log("=== INICIAR CONVERSACIÓN ===");
        
        // Validar datos básicos
        $nombre = trim($_POST['nombre'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $asunto = trim($_POST['asunto'] ?? 'Consulta general');
        $categoria = $_POST['categoria'] ?? 'general';
        
        if (empty($nombre) || strlen($nombre) < 2) {
            enviarRespuesta(false, null, 'Nombre inválido');
        }
        
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            enviarRespuesta(false, null, 'Email inválido');
        }
        
        // Obtener ID de usuario si está logueado
        $usuario_id = null;
        if (isset($_SESSION['id'])) {
            $usuario_id = $_SESSION['id'];
        } elseif (isset($_POST['id_usuario_encrypted'])) {
            $usuario_id = desencriptarID($_POST['id_usuario_encrypted']);
        }
        
        // Generar ID único para la conversación
        $conversacion_id = md5(uniqid() . time() . $email);
        
        // Crear datos para la conversación
        $datosConversacion = [
            'conversacion_id' => $conversacion_id,
            'usuario_id' => $usuario_id,
            'nombre_usuario' => htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8'),
            'email_usuario' => $email,
            'asunto' => htmlspecialchars($asunto, ENT_QUOTES, 'UTF-8'),
            'categoria' => $categoria
        ];
        
        error_log("Creando conversación con datos: " . print_r($datosConversacion, true));
        
        // Crear conversación
        $creada = $chat->crearConversacion($datosConversacion);
        
        if (!$creada) {
            error_log("La función crearConversacion devolvió false");
            
            // Diagnosticar el problema
            $diagnostico = $chat->diagnostico();
            error_log("Diagnóstico del sistema: " . print_r($diagnostico, true));
            
            throw new Exception('Error al crear la conversación. Verifica que las tablas existan en la base de datos.');
        }
        
        // Verificar si hay agentes disponibles
        $hayAgentes = $chat->hayAgentesDisponibles();
        $estado_inicial = $hayAgentes ? 'activa' : 'en_espera';
        
        if ($hayAgentes) {
            // Asignar agente automáticamente
            $agente_id = $chat->asignarAgente($conversacion_id);
            error_log("Agente asignado: " . ($agente_id ? $agente_id : 'ninguno'));
        }
        
        // Enviar mensaje inicial del sistema
        $mensajeBienvenida = "¡Hola $nombre! Has iniciado una conversación de chat. ";
        $mensajeBienvenida .= $hayAgentes ? 
            "Un agente de soporte te atenderá en breve. Por favor, describe tu consulta." :
            "Actualmente todos nuestros agentes están ocupados. Te atenderemos tan pronto como uno esté disponible.";
        
        $datosMensaje = [
            'conversacion_id' => $conversacion_id,
            'usuario_id' => null,
            'nombre_usuario' => 'Sistema',
            'email_usuario' => 'sistema@nexusbuy.com',
            'mensaje' => $mensajeBienvenida,
            'tipo' => 'sistema'
        ];
        
        $mensajeEnviado = $chat->enviarMensaje($datosMensaje);
        error_log("Mensaje de bienvenida enviado: " . ($mensajeEnviado ? 'sí' : 'no'));
        
        enviarRespuesta(true, [
            'conversacion_id' => $conversacion_id,
            'estado' => $estado_inicial,
            'hay_agentes' => $hayAgentes,
            'mensaje' => 'Conversación iniciada correctamente'
        ]);
        
    } catch (Exception $e) {
        error_log("Error iniciando conversación: " . $e->getMessage());
        enviarRespuesta(false, null, 'Error al iniciar la conversación: ' . $e->getMessage());
    }
}

// Enviar mensaje
if ($_POST['funcion'] == 'enviar_mensaje') {
    try {
        $conversacion_id = $_POST['conversacion_id'] ?? null;
        $mensaje = trim($_POST['mensaje'] ?? '');
        
        if (empty($conversacion_id)) {
            enviarRespuesta(false, null, 'ID de conversación requerido');
        }
        
        if (empty($mensaje) || strlen($mensaje) < 1) {
            enviarRespuesta(false, null, 'Mensaje no puede estar vacío');
        }
        
        // Obtener datos del usuario
        $nombre = trim($_POST['nombre'] ?? '');
        $email = trim($_POST['email'] ?? '');
        
        // Si no vienen en POST, obtener de la conversación
        if (empty($nombre) || empty($email)) {
            $conversacion = $chat->obtenerConversacion($conversacion_id);
            if (!$conversacion) {
                enviarRespuesta(false, null, 'Conversación no encontrada');
            }
            $nombre = $conversacion->nombre_usuario ?? '';
            $email = $conversacion->email_usuario ?? '';
        }
        
        if (empty($nombre) || empty($email)) {
            enviarRespuesta(false, null, 'No se pudo obtener información del usuario');
        }
        
        // Determinar tipo de mensaje
        $tipo = 'usuario';
        if (isset($_SESSION['rol']) && in_array($_SESSION['rol'], ['admin', 'soporte'])) {
            $tipo = 'agente';
        }
        
        // Obtener ID de usuario
        $usuario_id = null;
        if (isset($_SESSION['id'])) {
            $usuario_id = $_SESSION['id'];
        }
        
        $datosMensaje = [
            'conversacion_id' => $conversacion_id,
            'usuario_id' => $usuario_id,
            'nombre_usuario' => htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8'),
            'email_usuario' => $email,
            'mensaje' => htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8'),
            'tipo' => $tipo
        ];
        
        $enviado = $chat->enviarMensaje($datosMensaje);
        
        if (!$enviado) {
            throw new Exception('Error al enviar el mensaje');
        }
        
        // Si es el primer mensaje del usuario y hay agentes, asignar uno
        if ($tipo === 'usuario') {
            $conversacion = $chat->obtenerConversacion($conversacion_id);
            if ($conversacion && empty($conversacion->agente_asignado)) {
                $chat->asignarAgente($conversacion_id);
            }
        }
        
        enviarRespuesta(true, [
            'mensaje' => 'Mensaje enviado',
            'tipo' => $tipo,
            'timestamp' => date('H:i')
        ]);
        
    } catch (Exception $e) {
        error_log("Error enviando mensaje: " . $e->getMessage());
        enviarRespuesta(false, null, 'Error al enviar mensaje');
    }
}

// Obtener mensajes
if ($_POST['funcion'] == 'obtener_mensajes') {
    try {
        $conversacion_id = $_POST['conversacion_id'] ?? null;
        $ultimo_id = isset($_POST['ultimo_id']) ? intval($_POST['ultimo_id']) : 0;
        
        if (empty($conversacion_id)) {
            enviarRespuesta(false, null, 'ID de conversación requerido');
        }
        
        // Obtener mensajes
        $mensajes = $chat->obtenerMensajes($conversacion_id, 100);
        
        // Convertir objetos a arrays para JSON
        $mensajes_array = objetosAArrays($mensajes);
        
        // Filtrar solo mensajes nuevos si se especificó ultimo_id
        if ($ultimo_id > 0) {
            $mensajes_array = array_filter($mensajes_array, function($msg) use ($ultimo_id) {
                return $msg['id'] > $ultimo_id;
            });
            $mensajes_array = array_values($mensajes_array); // Reindexar
        }
        
        // Obtener información de la conversación
        $conversacion = $chat->obtenerConversacion($conversacion_id);
        $conversacion_array = $conversacion ? objetoAArray($conversacion) : null;
        
        // Marcar mensajes del agente como leídos si el usuario está viendo
        if ($conversacion_array && isset($_SESSION['id']) && $_SESSION['id'] != $conversacion_array['usuario_id']) {
            $chat->marcarComoLeidos($conversacion_id, 'agente');
        }
        
        // Obtener último ID para futuras consultas
        $nuevo_ultimo_id = $ultimo_id;
        if (!empty($mensajes_array)) {
            $ultimo = end($mensajes_array);
            $nuevo_ultimo_id = $ultimo['id'];
        }
        
        enviarRespuesta(true, [
            'mensajes' => $mensajes_array,
            'conversacion' => $conversacion_array,
            'ultimo_id' => $nuevo_ultimo_id,
            'total' => count($mensajes_array)
        ]);
        
    } catch (Exception $e) {
        error_log("Error obteniendo mensajes: " . $e->getMessage());
        enviarRespuesta(false, null, 'Error al obtener mensajes');
    }
}

// Obtener conversaciones
if ($_POST['funcion'] == 'obtener_conversaciones') {
    try {
        $usuario_id = null;
        $email = null;
        
        // Si está logueado, obtener por ID
        if (isset($_SESSION['id'])) {
            $usuario_id = $_SESSION['id'];
        } 
        // Si no está logueado pero tiene email en POST
        elseif (isset($_POST['email'])) {
            $email = $_POST['email'];
        }
        // Si no, verificar si hay email en sesión
        elseif (isset($_SESSION['email'])) {
            $email = $_SESSION['email'];
        }
        
        if (!$usuario_id && !$email) {
            enviarRespuesta(true, ['conversaciones' => []]); // No hay conversaciones
        }
        
        $conversaciones = $chat->obtenerConversacionesUsuario($usuario_id, $email);
        $conversaciones_array = objetosAArrays($conversaciones);
        
        enviarRespuesta(true, [
            'conversaciones' => $conversaciones_array,
            'total' => count($conversaciones_array)
        ]);
        
    } catch (Exception $e) {
        error_log("Error obteniendo conversaciones: " . $e->getMessage());
        enviarRespuesta(false, null, 'Error al obtener conversaciones');
    }
}

// Verificar agentes disponibles
if ($_POST['funcion'] == 'verificar_agentes') {
    try {
        $disponibles = $chat->hayAgentesDisponibles();
        $estadisticas = $chat->obtenerEstadisticas();
        $estadisticas_array = $estadisticas ? objetoAArray($estadisticas) : null;
        
        enviarRespuesta(true, [
            'disponibles' => $disponibles,
            'estadisticas' => $estadisticas_array
        ]);
        
    } catch (Exception $e) {
        error_log("Error verificando agentes: " . $e->getMessage());
        enviarRespuesta(false, null, 'Error al verificar disponibilidad');
    }
}

// Cerrar conversación
if ($_POST['funcion'] == 'cerrar_conversacion') {
    try {
        $conversacion_id = $_POST['conversacion_id'] ?? null;
        $valoracion = isset($_POST['valoracion']) ? intval($_POST['valoracion']) : null;
        $comentario = $_POST['comentario'] ?? null;
        
        if (empty($conversacion_id)) {
            enviarRespuesta(false, null, 'ID de conversación requerido');
        }
        
        // Verificar que el usuario sea dueño de la conversación o sea agente
        $conversacion = $chat->obtenerConversacion($conversacion_id);
        if (!$conversacion) {
            enviarRespuesta(false, null, 'Conversación no encontrada');
        }
        
        $conversacion_array = objetoAArray($conversacion);
        
        $es_dueño = isset($_SESSION['id']) && $_SESSION['id'] == $conversacion_array['usuario_id'];
        $es_agente = isset($_SESSION['rol']) && in_array($_SESSION['rol'], ['admin', 'soporte']);
        
        if (!$es_dueño && !$es_agente) {
            enviarRespuesta(false, null, 'No tienes permiso para cerrar esta conversación');
        }
        
        $cerrada = $chat->cerrarConversacion($conversacion_id, $valoracion, $comentario);
        
        if (!$cerrada) {
            throw new Exception('Error al cerrar la conversación');
        }
        
        // Enviar mensaje de cierre
        $mensajeCierre = $es_agente ? 
            "El agente ha cerrado esta conversación. ¡Gracias por contactarnos!" :
            "Has cerrado esta conversación. ¡Gracias por contactarnos!";
        
        $datosMensaje = [
            'conversacion_id' => $conversacion_id,
            'usuario_id' => null,
            'nombre_usuario' => 'Sistema',
            'email_usuario' => 'sistema@nexusbuy.com',
            'mensaje' => $mensajeCierre,
            'tipo' => 'sistema'
        ];
        
        $chat->enviarMensaje($datosMensaje);
        
        enviarRespuesta(true, [
            'mensaje' => 'Conversación cerrada correctamente'
        ]);
        
    } catch (Exception $e) {
        error_log("Error cerrando conversación: " . $e->getMessage());
        enviarRespuesta(false, null, 'Error al cerrar conversación');
    }
}

// Verificar mensajes nuevos
if ($_POST['funcion'] == 'verificar_nuevos') {
    try {
        $conversacion_id = $_POST['conversacion_id'] ?? null;
        $ultimo_id = isset($_POST['ultimo_id']) ? intval($_POST['ultimo_id']) : 0;
        
        if (empty($conversacion_id)) {
            enviarRespuesta(false, null, 'ID de conversación requerido');
        }
        
        $nuevos = $chat->verificarMensajesNuevos($conversacion_id, $ultimo_id);
        
        enviarRespuesta(true, [
            'nuevos' => $nuevos,
            'tiene_nuevos' => $nuevos > 0
        ]);
        
    } catch (Exception $e) {
        error_log("Error verificando nuevos mensajes: " . $e->getMessage());
        enviarRespuesta(false, null, 'Error al verificar nuevos mensajes');
    }
}

// Función para diagnóstico
if ($_POST['funcion'] == 'diagnostico') {
    try {
        $diagnostico = $chat->diagnostico();
        $diagnostico_array = objetoAArray($diagnostico);
        
        // También obtener información del servidor
        $info_servidor = [
            'php_version' => PHP_VERSION,
            'server_time' => date('Y-m-d H:i:s'),
            'session_id' => session_id(),
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Desconocido',
            'mysql_version' => null
        ];
        
        // Intentar obtener versión de MySQL
        try {
            $query = $chat->acceso->query("SELECT VERSION()");
            $info_servidor['mysql_version'] = $query->fetchColumn();
        } catch (Exception $e) {
            $info_servidor['mysql_version_error'] = $e->getMessage();
        }
        
        enviarRespuesta(true, [
            'diagnostico' => $diagnostico_array,
            'servidor' => $info_servidor,
            'timestamp' => time()
        ]);
        
    } catch (Exception $e) {
        error_log("Error en diagnóstico: " . $e->getMessage());
        enviarRespuesta(false, null, 'Error en diagnóstico: ' . $e->getMessage());
    }
}

// Función no reconocida
enviarRespuesta(false, null, 'Función no reconocida');
?>