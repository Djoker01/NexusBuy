<?php
include_once 'Conexion.php';
class Reseña{
    var $objetos;
    var $acceso;
    public function __construct(){
        $db = new Conexion();
        $this->acceso = $db->pdo;
    }
 function evaluar_calificaciones($id_producto_tienda)
    {
        $sql = "SELECT AVG(calificacion) as promedio
                FROM reseña r
                WHERE r.id_producto_tienda= :id_producto_tienda
                AND r.estado='aprobada' ";
        $query = $this->acceso->prepare($sql);
        $query->execute(array(':id_producto_tienda' => $id_producto_tienda));
        $this->objetos = $query->fetchAll();
        return $this->objetos;
    }
    function capturar_reseñas($id_producto_tienda)
    {
        $sql = "SELECT r.id as id,
                    r.calificacion as calificacion,
                    r.comentario as comentario,
                    r.respuesta_tienda as respuesta,
                    r.fecha_creacion as fecha_creacion,
                    u.username as user,
                    u.avatar as avatar
                FROM reseña r
                JOIN usuario u ON u.id=r.id_usuario
                WHERE r.id_producto_tienda=:id_producto_tienda
                AND r.estado='aprobada' ORDER BY r.fecha_creacion DESC";
        $query = $this->acceso->prepare($sql);
        $query->execute(array(':id_producto_tienda' => $id_producto_tienda));
        $this->objetos = $query->fetchAll();
        return $this->objetos;
    }
    function verificar_reseña_usuario($id_producto_tienda, $id_usuario)
    {
        $sql = "SELECT *
                FROM reseña 
                WHERE id_producto_tienda = :id_producto_tienda
                AND id_usuario = :id_usuario
                AND estado = 'A'";
        $query = $this->acceso->prepare($sql);
        $query->execute(array(':id_producto_tienda' => $id_producto_tienda,':id_usuario' => $id_usuario));
        $this->objetos = $query->fetchAll();
        return $this->objetos;
    }
    function crear_reseña($id_producto_tienda, $id_usuario, $calificacion, $comentario)
    {
        $sql = "INSERT INTO reseña (calificacion, descripcion, id_producto_tienda, id_usuario)
                VALUES (:calificacion, :descripcion, :id_producto_tienda, :id_usuario)";
        $query = $this->acceso->prepare($sql);
        $resultado = $query->execute(array(':calificacion' => $calificacion,':descripcion' => $comentario, ':id_producto_tienda' => $id_producto_tienda,':id_usuario' => $id_usuario));
        
        return $resultado ? 'success' : 'error';
    }
    
    
    
}