<?php
include_once 'Conexion.php';
class Historial{
    var $objetos;
    var $acceso;
    
    public function __construct(){
        $db = new Conexion();
        $this->acceso = $db->pdo;
    }
    
    function llenar_historial($user, $limite = 50){
        $sql = "SELECT 
                    h.id as id,
                    h.descripcion as descripcion,
                    DATE(h.fecha_creacion) as fecha,
                    TIME(h.fecha_creacion) as hora,
                    th.nombre as tipo_historial,
                    th.icono as th_icono,
                    m.nombre as modulo,
                    m.icono as m_icono,
                    h.accion,
                    h.datos
                FROM historial h
                LEFT JOIN tipo_historial th ON h.id_tipo_historial = th.id
                LEFT JOIN modulo m ON h.id_modulo = m.id 
                WHERE h.id_usuario = :user 
                ORDER BY h.fecha_creacion DESC 
                LIMIT :limite";
        
        $query = $this->acceso->prepare($sql);
        $query->bindParam(':user', $user, PDO::PARAM_INT);
        $query->bindParam(':limite', $limite, PDO::PARAM_INT);
        $query->execute();
        $this->objetos = $query->fetchAll();
        return $this->objetos;
    }
    
    function crear_historial($descripcion, $tipo_historial, $modulo, $id_usuario, $accion = null, $datos_json = null){
        $sql = "INSERT INTO historial(
                    descripcion, 
                    id_tipo_historial, 
                    id_modulo, 
                    id_usuario,
                    accion,
                    datos,
                    fecha_creacion
                ) VALUES(
                    :descripcion,
                    :id_tipo_historial,
                    :id_modulo,
                    :id_usuario,
                    :accion,
                    :datos,
                    NOW()
                )";
        
        $query = $this->acceso->prepare($sql);
        $variables = array(
            ':descripcion' => $descripcion,
            ':id_tipo_historial' => $tipo_historial,
            ':id_modulo' => $modulo,
            ':id_usuario' => $id_usuario,
            ':accion' => $accion,
            ':datos' => $datos_json
        );
        
        return $query->execute($variables);
    }
    
    // Función para obtener estadísticas del historial
    function obtener_estadisticas_usuario($id_usuario, $dias = 30){
        $sql = "SELECT 
                    COUNT(*) as total,
                    DATE(fecha_creacion) as fecha,
                    th.nombre as tipo,
                    m.nombre as modulo
                FROM historial h
                LEFT JOIN tipo_historial th ON h.id_tipo_historial = th.id
                LEFT JOIN modulo m ON h.id_modulo = m.id
                WHERE h.id_usuario = :id_usuario 
                AND h.fecha_creacion >= DATE_SUB(NOW(), INTERVAL :dias DAY)
                GROUP BY DATE(fecha_creacion), th.nombre, m.nombre
                ORDER BY fecha DESC";
        
        $query = $this->acceso->prepare($sql);
        $query->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
        $query->bindParam(':dias', $dias, PDO::PARAM_INT);
        $query->execute();
        return $query->fetchAll();
    }
    
    // Función para obtener últimos movimientos por tipo
    function obtener_ultimos_movimientos($id_usuario, $tipo = null, $limit = 10){
        $sql = "SELECT 
                    h.*,
                    th.nombre as tipo_nombre,
                    th.icono as tipo_icono,
                    m.nombre as modulo_nombre,
                    m.icono as modulo_icono
                FROM historial h
                LEFT JOIN tipo_historial th ON h.id_tipo_historial = th.id
                LEFT JOIN modulo m ON h.id_modulo = m.id
                WHERE h.id_usuario = :id_usuario";
        
        if($tipo){
            $sql .= " AND th.nombre = :tipo";
        }
        
        $sql .= " ORDER BY h.fecha_creacion DESC LIMIT :limit";
        
        $query = $this->acceso->prepare($sql);
        $query->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
        
        if($tipo){
            $query->bindParam(':tipo', $tipo);
        }
        
        $query->bindParam(':limit', $limit, PDO::PARAM_INT);
        $query->execute();
        return $query->fetchAll();
    }
}
?>