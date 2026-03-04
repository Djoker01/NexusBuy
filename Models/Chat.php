<?php
include_once 'Conexion.php';

class Chat {
    var $objetos;
    var $acceso;

    public function __construct() {
        $db = new Conexion();
        $this->acceso = $db->pdo;
    }
    
    // ✅ Crear nueva conversación
    public function crearConversacion($datos) {
        try {
            // Verificar si ya existe una conversación con el mismo ID
            $sql_verificar = "SELECT id FROM chat_conversaciones WHERE id = :id";
            $query = $this->acceso->prepare($sql_verificar);
            $query->execute(array(':id' => $datos['conversacion_id']));
            
            if ($query->fetch()) {
                error_log("Ya existe una conversación con ID: " . $datos['conversacion_id']);
                return false;
            }
            
            // Insertar nueva conversación
            $sql = "INSERT INTO chat_conversaciones 
                    (id, usuario_id, nombre_usuario, email_usuario, asunto, categoria, ip_address, user_agent) 
                    VALUES (:id, :usuario_id, :nombre_usuario, :email_usuario, :asunto, :categoria, :ip_address, :user_agent)";
            
            $query = $this->acceso->prepare($sql);
            
            $params = array(
                ':id' => $datos['conversacion_id'],
                ':usuario_id' => isset($datos['usuario_id']) && !empty($datos['usuario_id']) ? $datos['usuario_id'] : null,
                ':nombre_usuario' => $datos['nombre_usuario'],
                ':email_usuario' => $datos['email_usuario'],
                ':asunto' => $datos['asunto'] ?? 'Consulta general',
                ':categoria' => $datos['categoria'] ?? 'general',
                ':ip_address' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
                ':user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Desconocido'
            );
            
            $resultado = $query->execute($params);
            
            if (!$resultado) {
                $error_info = $this->acceso->errorInfo();
                error_log("Error al crear conversación: " . print_r($error_info, true));
                error_log("SQL: " . $sql);
                error_log("Parámetros: " . print_r($params, true));
                return false;
            }
            
            return true;
            
        } catch (Exception $e) {
            error_log("Error en crearConversacion: " . $e->getMessage());
            return false;
        }
    }
    
    // ✅ Enviar mensaje
    public function enviarMensaje($datos) {
        try {
            $sql = "INSERT INTO chat_mensajes 
                    (conversacion_id, usuario_id, nombre_usuario, email_usuario, mensaje, tipo, ip_address, user_agent) 
                    VALUES (:conversacion_id, :usuario_id, :nombre_usuario, :email_usuario, :mensaje, :tipo, :ip_address, :user_agent)";
            
            $query = $this->acceso->prepare($sql);
            
            $params = array(
                ':conversacion_id' => $datos['conversacion_id'],
                ':usuario_id' => isset($datos['usuario_id']) && !empty($datos['usuario_id']) ? $datos['usuario_id'] : null,
                ':nombre_usuario' => $datos['nombre_usuario'],
                ':email_usuario' => $datos['email_usuario'],
                ':mensaje' => $datos['mensaje'],
                ':tipo' => $datos['tipo'],
                ':ip_address' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
                ':user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Desconocido'
            );
            
            $resultado = $query->execute($params);
            
            if ($resultado) {
                // Actualizar última actividad de la conversación
                $this->actualizarUltimaActividad($datos['conversacion_id']);
            }
            
            return $resultado;
            
        } catch (Exception $e) {
            error_log("Error al enviar mensaje de chat: " . $e->getMessage());
            return false;
        }
    }
    
    // ✅ Obtener mensajes de una conversación
    public function obtenerMensajes($conversacion_id, $limite = 50, $offset = 0) {
        try {
            $sql = "SELECT cm.*, 
                    DATE_FORMAT(cm.fecha_envio, '%H:%i') as hora,
                    DATE_FORMAT(cm.fecha_envio, '%d/%m/%Y') as fecha
                    FROM chat_mensajes cm 
                    WHERE cm.conversacion_id = :conversacion_id 
                    ORDER BY cm.fecha_envio ASC
                    LIMIT :offset, :limite";
            
            $query = $this->acceso->prepare($sql);
            $query->bindValue(':conversacion_id', $conversacion_id);
            $query->bindValue(':limite', (int)$limite, PDO::PARAM_INT);
            $query->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
            $query->execute();
            
            return $query->fetchAll();
            
        } catch (Exception $e) {
            error_log("Error al obtener mensajes de chat: " . $e->getMessage());
            return array();
        }
    }
    
    // ✅ Obtener conversaciones del usuario
    public function obtenerConversacionesUsuario($usuario_id = null, $email = null) {
        try {
            $sql = "SELECT cc.*, 
                    (SELECT COUNT(*) FROM chat_mensajes cm 
                     WHERE cm.conversacion_id = cc.id 
                     AND cm.tipo = 'agente' 
                     AND cm.leido = 0) as mensajes_sin_leer,
                    (SELECT mensaje FROM chat_mensajes cm 
                     WHERE cm.conversacion_id = cc.id 
                     ORDER BY cm.fecha_envio DESC LIMIT 1) as ultimo_mensaje,
                    (SELECT tipo FROM chat_mensajes cm 
                     WHERE cm.conversacion_id = cc.id 
                     ORDER BY cm.fecha_envio DESC LIMIT 1) as ultimo_tipo,
                    ca.nombre_agente
                    FROM chat_conversaciones cc
                    LEFT JOIN chat_agentes ca ON cc.agente_asignado = ca.id
                    WHERE 1=1";
            
            $params = array();
            
            if ($usuario_id) {
                $sql .= " AND cc.usuario_id = :usuario_id";
                $params[':usuario_id'] = $usuario_id;
            } elseif ($email) {
                $sql .= " AND cc.email_usuario = :email";
                $params[':email'] = $email;
            }
            
            $sql .= " ORDER BY cc.ultimo_mensaje DESC, cc.fecha_inicio DESC";
            
            $query = $this->acceso->prepare($sql);
            $query->execute($params);
            
            return $query->fetchAll();
            
        } catch (Exception $e) {
            error_log("Error al obtener conversaciones: " . $e->getMessage());
            return array();
        }
    }
    
    // ✅ Verificar si hay agentes disponibles
    public function hayAgentesDisponibles() {
        try {
            $sql = "SELECT COUNT(*) as total 
                    FROM chat_agentes 
                    WHERE estado = 'disponible' 
                    AND conversaciones_activas < max_conversaciones";
            
            $query = $this->acceso->query($sql);
            $result = $query->fetch();
            
            return $result->total > 0;
            
        } catch (Exception $e) {
            error_log("Error al verificar agentes disponibles: " . $e->getMessage());
            return false;
        }
    }
    
    // ✅ Asignar agente a conversación
    public function asignarAgente($conversacion_id) {
        try {
            // Buscar agente disponible
            $sqlAgente = "SELECT id 
                         FROM chat_agentes 
                         WHERE estado = 'disponible' 
                         AND conversaciones_activas < max_conversaciones
                         ORDER BY conversaciones_activas ASC, RAND()
                         LIMIT 1";
            
            $queryAgente = $this->acceso->query($sqlAgente);
            $agente = $queryAgente->fetch();
            
            if (!$agente) {
                return false; // No hay agentes disponibles
            }
            
            // Asignar agente
            $sqlAsignar = "UPDATE chat_conversaciones 
                          SET agente_asignado = :agente_id, 
                          estado = 'activa'
                          WHERE id = :conversacion_id";
            
            $queryAsignar = $this->acceso->prepare($sqlAsignar);
            $queryAsignar->execute(array(
                ':agente_id' => $agente->id,
                ':conversacion_id' => $conversacion_id
            ));
            
            // Actualizar contador del agente
            $sqlContador = "UPDATE chat_agentes 
                           SET conversaciones_activas = conversaciones_activas + 1
                           WHERE id = :agente_id";
            
            $queryContador = $this->acceso->prepare($sqlContador);
            $queryContador->execute(array(':agente_id' => $agente->id));
            
            return $agente->id;
            
        } catch (Exception $e) {
            error_log("Error al asignar agente: " . $e->getMessage());
            return false;
        }
    }
    
    // ✅ Marcar mensajes como leídos
    public function marcarComoLeidos($conversacion_id, $tipo = 'usuario') {
        try {
            $sql = "UPDATE chat_mensajes 
                    SET leido = 1, estado = 'leido', fecha_lectura = CURRENT_TIMESTAMP()
                    WHERE conversacion_id = :conversacion_id 
                    AND tipo = :tipo 
                    AND leido = 0";
            
            $query = $this->acceso->prepare($sql);
            return $query->execute(array(
                ':conversacion_id' => $conversacion_id,
                ':tipo' => $tipo
            ));
            
        } catch (Exception $e) {
            error_log("Error al marcar mensajes como leídos: " . $e->getMessage());
            return false;
        }
    }
    
    // ✅ Cerrar conversación
    public function cerrarConversacion($conversacion_id, $valoracion = null, $comentario = null) {
        try {
            $sql = "UPDATE chat_conversaciones 
                    SET estado = 'cerrada', 
                    fecha_cierre = CURRENT_TIMESTAMP(),
                    valoracion = :valoracion,
                    comentario_cierre = :comentario
                    WHERE id = :conversacion_id";
            
            $query = $this->acceso->prepare($sql);
            
            $params = array(
                ':conversacion_id' => $conversacion_id,
                ':valoracion' => $valoracion,
                ':comentario' => $comentario
            );
            
            $resultado = $query->execute($params);
            
            if ($resultado) {
                // Liberar agente si está asignado
                $this->liberarAgente($conversacion_id);
            }
            
            return $resultado;
            
        } catch (Exception $e) {
            error_log("Error al cerrar conversación: " . $e->getMessage());
            return false;
        }
    }
    
    // ✅ Obtener información de conversación
    public function obtenerConversacion($conversacion_id) {
        try {
            $sql = "SELECT cc.*, ca.nombre_agente, ca.estado as estado_agente
                    FROM chat_conversaciones cc
                    LEFT JOIN chat_agentes ca ON cc.agente_asignado = ca.id
                    WHERE cc.id = :conversacion_id";
            
            $query = $this->acceso->prepare($sql);
            $query->execute(array(':conversacion_id' => $conversacion_id));
            
            return $query->fetch();
            
        } catch (Exception $e) {
            error_log("Error al obtener conversación: " . $e->getMessage());
            return null;
        }
    }
    
    // ✅ Método para verificar mensajes nuevos
    public function verificarMensajesNuevos($conversacion_id, $ultimo_id = 0) {
        try {
            $sql = "SELECT COUNT(*) as total_nuevos
                    FROM chat_mensajes 
                    WHERE conversacion_id = :conversacion_id 
                    AND id > :ultimo_id 
                    AND tipo = 'agente'";
            
            $query = $this->acceso->prepare($sql);
            $query->execute(array(
                ':conversacion_id' => $conversacion_id,
                ':ultimo_id' => $ultimo_id
            ));
            
            $result = $query->fetch();
            return $result->total_nuevos;
            
        } catch (Exception $e) {
            error_log("Error al verificar mensajes nuevos: " . $e->getMessage());
            return 0;
        }
    }
    
    // ✅ Obtener estadísticas del chat
    public function obtenerEstadisticas() {
        try {
            $sql = "SELECT 
                    (SELECT COUNT(*) FROM chat_conversaciones WHERE estado = 'activa') as conversaciones_activas,
                    (SELECT COUNT(*) FROM chat_conversaciones WHERE estado = 'en_espera') as conversaciones_espera,
                    (SELECT COUNT(*) FROM chat_agentes WHERE estado = 'disponible') as agentes_disponibles,
                    (SELECT COUNT(*) FROM chat_mensajes WHERE DATE(fecha_envio) = CURDATE()) as mensajes_hoy";
            
            $query = $this->acceso->query($sql);
            return $query->fetch();
            
        } catch (Exception $e) {
            error_log("Error al obtener estadísticas: " . $e->getMessage());
            return null;
        }
    }
    
    // ✅ Métodos privados auxiliares
    private function actualizarUltimaActividad($conversacion_id) {
        try {
            $sql = "UPDATE chat_conversaciones 
                    SET ultimo_mensaje = CURRENT_TIMESTAMP()
                    WHERE id = :conversacion_id";
            
            $query = $this->acceso->prepare($sql);
            return $query->execute(array(':conversacion_id' => $conversacion_id));
            
        } catch (Exception $e) {
            error_log("Error al actualizar última actividad: " . $e->getMessage());
            return false;
        }
    }
    
    private function liberarAgente($conversacion_id) {
        try {
            $sql = "UPDATE chat_agentes ca
                    JOIN chat_conversaciones cc ON ca.id = cc.agente_asignado
                    SET ca.conversaciones_activas = GREATEST(0, ca.conversaciones_activas - 1)
                    WHERE cc.id = :conversacion_id";
            
            $query = $this->acceso->prepare($sql);
            return $query->execute(array(':conversacion_id' => $conversacion_id));
            
        } catch (Exception $e) {
            error_log("Error al liberar agente: " . $e->getMessage());
            return false;
        }
    }
    
    // ✅ Método para diagnosticar problemas
    public function diagnostico() {
    $resultado = array(
        'conexion_ok' => false,
        'tabla_conversaciones_existe' => false,
        'tabla_mensajes_existe' => false,
        'tabla_agentes_existe' => false,
        'base_datos' => '',
        'tablas_encontradas' => array(),
        'error' => null
    );
    
    try {
        // Verificar conexión
        $query = $this->acceso->query("SELECT 1");
        $resultado['conexion_ok'] = ($query !== false);
        
        // Obtener nombre de la base de datos
        $query = $this->acceso->query("SELECT DATABASE()");
        $resultado['base_datos'] = $query->fetchColumn();
        
        // Obtener TODAS las tablas en la base de datos
        $query = $this->acceso->query("SHOW TABLES");
        $todas_tablas = $query->fetchAll(PDO::FETCH_COLUMN);
        
        $resultado['todas_las_tablas'] = $todas_tablas;
        
        // Buscar tablas específicas (case insensitive)
        $tablas_buscadas = array('chat_conversaciones', 'chat_mensajes', 'chat_agentes');
        
        foreach ($todas_tablas as $tabla_real) {
            $tabla_lower = strtolower($tabla_real);
            
            // Agregar a tablas encontradas
            if (strpos($tabla_lower, 'chat') !== false) {
                $resultado['tablas_encontradas'][] = $tabla_real;
            }
            
            // Verificar cada tabla específica
            foreach ($tablas_buscadas as $tabla_buscada) {
                if ($tabla_lower === $tabla_buscada) {
                    $resultado[$tabla_buscada . '_existe'] = true;
                }
            }
        }
        
        // Si no encontramos con minúsculas, buscar exactamente como están
        if (!$resultado['tabla_conversaciones_existe']) {
            $query = $this->acceso->query("SHOW TABLES LIKE 'chat_conversaciones'");
            $resultado['tabla_conversaciones_existe'] = ($query->fetch() !== false);
        }
        
        if (!$resultado['tabla_mensajes_existe']) {
            $query = $this->acceso->query("SHOW TABLES LIKE 'chat_mensajes'");
            $resultado['tabla_mensajes_existe'] = ($query->fetch() !== false);
        }
        
        if (!$resultado['tabla_agentes_existe']) {
            $query = $this->acceso->query("SHOW TABLES LIKE 'chat_agentes'");
            $resultado['tabla_agentes_existe'] = ($query->fetch() !== false);
        }
        
    } catch (Exception $e) {
        $resultado['error'] = $e->getMessage();
        error_log("Error en diagnóstico Chat: " . $e->getMessage());
    }
    
    return (object)$resultado;
}
}
?>