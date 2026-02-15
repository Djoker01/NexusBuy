<?php
include_once 'Conexion.php';

class Contacto {
    private $pdo;
    
    public function __construct() {
        $db = new Conexion();
        $this->pdo = $db->pdo;
    }
    
    // ✅ Guardar nuevo mensaje de contacto
    public function guardarMensaje($datos) {
        try {
            $sql = "INSERT INTO contacto_mensajes (nombre, email, telefono, asunto, mensaje, estado, id_usuario, ip_address, user_agent) 
                    VALUES (:nombre, :email, :telefono, :asunto, :mensaje, :estado, :id_usuario, :ip_address, :user_agent)";
            
            $stmt = $this->pdo->prepare($sql);
            
            $params = [
                ':nombre' => $datos['nombre'],
                ':email' => $datos['email'],
                ':telefono' => $datos['telefono'] ?? null,
                ':asunto' => $datos['asunto'],
                ':mensaje' => $datos['mensaje'],
                ':estado' => 'pendiente',
                ':id_usuario' => $datos['id_usuario'] ?? null,
                ':ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
                ':user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
            ];
            
            return $stmt->execute($params);
            
        } catch (PDOException $e) {
            error_log("Error al guardar mensaje de contacto: " . $e->getMessage());
            return false;
        }
    }
    
    // ✅ Obtener mensajes (para panel de administración)
    public function obtenerMensajes($filtro = null, $limite = null) {
        try {
            $sql = "SELECT cm.*, u.nombre as nombre_usuario, u.apellidos 
                    FROM contacto_mensajes cm 
                    LEFT JOIN usuarios u ON cm.id_usuario = u.id 
                    WHERE 1=1";
            
            $params = [];
            
            if ($filtro) {
                if (isset($filtro['estado']) && $filtro['estado'] !== '') {
                    $sql .= " AND cm.estado = :estado";
                    $params[':estado'] = $filtro['estado'];
                }
                
                if (isset($filtro['asunto']) && $filtro['asunto'] !== '') {
                    $sql .= " AND cm.asunto = :asunto";
                    $params[':asunto'] = $filtro['asunto'];
                }
                
                if (isset($filtro['fecha_desde']) && $filtro['fecha_desde']) {
                    $sql .= " AND DATE(cm.created_at) >= :fecha_desde";
                    $params[':fecha_desde'] = $filtro['fecha_desde'];
                }
                
                if (isset($filtro['fecha_hasta']) && $filtro['fecha_hasta']) {
                    $sql .= " AND DATE(cm.created_at) <= :fecha_hasta";
                    $params[':fecha_hasta'] = $filtro['fecha_hasta'];
                }
            }
            
            $sql .= " ORDER BY cm.created_at DESC";
            
            if ($limite) {
                $sql .= " LIMIT :limite";
                $params[':limite'] = (int)$limite;
            }
            
            $stmt = $this->pdo->prepare($sql);
            
            // Vincular parámetros
            foreach ($params as $key => $value) {
                if ($key === ':limite') {
                    $stmt->bindValue($key, $value, PDO::PARAM_INT);
                } else {
                    $stmt->bindValue($key, $value);
                }
            }
            
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error al obtener mensajes de contacto: " . $e->getMessage());
            return [];
        }
    }
    
    // ✅ Obtener mensaje por ID
    public function obtenerMensajePorId($id) {
        try {
            $sql = "SELECT cm.*, u.nombre as nombre_usuario, u.apellidos 
                    FROM contacto_mensajes cm 
                    LEFT JOIN usuarios u ON cm.id_usuario = u.id 
                    WHERE cm.id = :id";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':id' => $id]);
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error al obtener mensaje por ID: " . $e->getMessage());
            return null;
        }
    }
    
    // ✅ Actualizar estado de mensaje
    public function actualizarEstado($id, $estado) {
        try {
            $sql = "UPDATE contacto_mensajes SET estado = :estado, updated_at = CURRENT_TIMESTAMP WHERE id = :id";
            
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([':estado' => $estado, ':id' => $id]);
            
        } catch (PDOException $e) {
            error_log("Error al actualizar estado de mensaje: " . $e->getMessage());
            return false;
        }
    }
    
    // ✅ Eliminar mensaje
    public function eliminarMensaje($id) {
        try {
            $sql = "DELETE FROM contacto_mensajes WHERE id = :id";
            
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([':id' => $id]);
            
        } catch (PDOException $e) {
            error_log("Error al eliminar mensaje: " . $e->getMessage());
            return false;
        }
    }
    
    // ✅ Obtener estadísticas
    public function obtenerEstadisticas() {
        try {
            $sql = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN estado = 'pendiente' THEN 1 ELSE 0 END) as pendientes,
                    SUM(CASE WHEN estado = 'leido' THEN 1 ELSE 0 END) as leidos,
                    SUM(CASE WHEN estado = 'respondido' THEN 1 ELSE 0 END) as respondidos,
                    SUM(CASE WHEN estado = 'archivado' THEN 1 ELSE 0 END) as archivados
                    FROM contacto_mensajes";
            
            $stmt = $this->pdo->query($sql);
            return $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error al obtener estadísticas: " . $e->getMessage());
            return null;
        }
    }
    
    // ✅ Obtener asuntos disponibles
    public function obtenerAsuntos() {
        try {
            $sql = "SELECT DISTINCT asunto FROM contacto_mensajes ORDER BY asunto";
            $stmt = $this->pdo->query($sql);
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (PDOException $e) {
            error_log("Error al obtener asuntos: " . $e->getMessage());
            return [];
        }
    }
    
    // ✅ Verificar si el usuario ya envió un mensaje recientemente (para evitar spam)
    public function verificarMensajeReciente($email, $minutos = 15) {
        try {
            $sql = "SELECT COUNT(*) as count 
                    FROM contacto_mensajes 
                    WHERE email = :email 
                    AND created_at >= DATE_SUB(NOW(), INTERVAL :minutos MINUTE)";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':email' => $email, ':minutos' => $minutos]);
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['count'] > 0;
            
        } catch (PDOException $e) {
            error_log("Error al verificar mensaje reciente: " . $e->getMessage());
            return false;
        }
    }
}
