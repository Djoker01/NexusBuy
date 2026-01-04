<?php
include_once 'Conexion.php';
class Chat {
    var $objetos;
    var $acceso;
    
    public function __construct() {
        $db = new Conexion();
        $this->acceso = $db->pdo;
    }

    // Guardar un mensaje
    function guardar_mensaje($id_usuario, $nombre_usuario, $email_usuario, $mensaje, $tipo) {
        $sql = "INSERT INTO chat_mensajes (usuario_id, nombre_usuario, email_usuario, mensaje, tipo) 
                VALUES (:usuario_id, :nombre_usuario, :email_usuario, :mensaje, :tipo)";
        $query = $this->acceso->prepare($sql);
        $params = array(
            ':usuario_id' => $id_usuario,
            ':nombre_usuario' => $nombre_usuario,
            ':email_usuario' => $email_usuario,
            ':mensaje' => $mensaje,
            ':tipo' => $tipo
        );
        $query->execute($params);
        return $this->acceso->lastInsertId();
    }

    // Obtener mensajes por email
    function obtener_mensajes_por_email($email_usuario, $limit = 50) {
        $sql = "SELECT * FROM chat_mensajes 
                WHERE email_usuario = :email_usuario 
                ORDER BY fecha_envio ASC 
                LIMIT :limit";
        $query = $this->acceso->prepare($sql);
        $query->bindParam(':email_usuario', $email_usuario, PDO::PARAM_STR);
        $query->bindParam(':limit', $limit, PDO::PARAM_INT);
        $query->execute();
        $this->objetos = $query->fetchAll();
        return $this->objetos;
    }

    // Obtener todas las conversaciones (para admin)
    function obtener_conversaciones($limit = 20) {
        $sql = "SELECT 
                    email_usuario,
                    nombre_usuario,
                    MAX(fecha_envio) as ultimo_mensaje,
                    COUNT(*) as total_mensajes,
                    SUM(CASE WHEN tipo = 'usuario' AND leido = FALSE THEN 1 ELSE 0 END) as mensajes_no_leidos
                FROM chat_mensajes
                GROUP BY email_usuario, nombre_usuario
                ORDER BY ultimo_mensaje DESC
                LIMIT :limit";
        $query = $this->acceso->prepare($sql);
        $query->bindParam(':limit', $limit, PDO::PARAM_INT);
        $query->execute();
        $this->objetos = $query->fetchAll();
        return $this->objetos;
    }

    // Marcar mensajes como leídos
    function marcar_mensajes_leidos($email_usuario) {
        $sql = "UPDATE chat_mensajes 
                SET leido = TRUE 
                WHERE email_usuario = :email_usuario AND tipo = 'usuario'";
        $query = $this->acceso->prepare($sql);
        $query->execute(array(':email_usuario' => $email_usuario));
        return $query->rowCount();
    }

    // Verificar si hay mensajes no leídos
    function verificar_mensajes_no_leidos($email_usuario) {
        $sql = "SELECT COUNT(*) as total_no_leidos 
                FROM chat_mensajes 
                WHERE email_usuario = :email_usuario 
                AND tipo = 'usuario' 
                AND leido = FALSE";
        $query = $this->acceso->prepare($sql);
        $query->execute(array(':email_usuario' => $email_usuario));
        $result = $query->fetch();
        return $result->total_no_leidos;
    }

    // Obtener último mensaje por email
    function obtener_ultimo_mensaje($email_usuario) {
        $sql = "SELECT * FROM chat_mensajes 
                WHERE email_usuario = :email_usuario 
                ORDER BY fecha_envio DESC 
                LIMIT 1";
        $query = $this->acceso->prepare($sql);
        $query->execute(array(':email_usuario' => $email_usuario));
        $this->objetos = $query->fetchAll();
        return !empty($this->objetos) ? $this->objetos[0] : null;
    }

    // Obtener conversación específica para admin
    function obtener_conversacion_por_email($email_usuario) {
        $sql = "SELECT cm.*, 
                       COALESCE(u.imagen, 'default.png') as imagen_usuario,
                       u.role as rol_usuario
                FROM chat_mensajes cm
                LEFT JOIN users u ON cm.usuario_id = u.id
                WHERE cm.email_usuario = :email_usuario 
                ORDER BY cm.fecha_envio ASC";
        $query = $this->acceso->prepare($sql);
        $query->execute(array(':email_usuario' => $email_usuario));
        $this->objetos = $query->fetchAll();
        return $this->objetos;
    }

    // Buscar conversaciones por nombre o email
    function buscar_conversaciones($busqueda) {
        $sql = "SELECT DISTINCT 
                    email_usuario,
                    nombre_usuario,
                    MAX(fecha_envio) as ultimo_mensaje
                FROM chat_mensajes 
                WHERE nombre_usuario LIKE :busqueda 
                   OR email_usuario LIKE :busqueda
                GROUP BY email_usuario, nombre_usuario
                ORDER BY ultimo_mensaje DESC
                LIMIT 20";
        $query = $this->acceso->prepare($sql);
        $busqueda_param = "%" . $busqueda . "%";
        $query->execute(array(':busqueda' => $busqueda_param));
        $this->objetos = $query->fetchAll();
        return $this->objetos;
    }

    // Eliminar conversación (solo admin)
    function eliminar_conversacion($email_usuario) {
        $sql = "DELETE FROM chat_mensajes WHERE email_usuario = :email_usuario";
        $query = $this->acceso->prepare($sql);
        $query->execute(array(':email_usuario' => $email_usuario));
        return $query->rowCount();
    }
}
?>