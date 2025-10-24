<?php
include_once 'Conexion.php';
class Tienda{
    var $objetos;
    var $acceso;
    public function __construct(){
        $db = new Conexion();
        $this->acceso = $db->pdo;
    }
    function contar_reseñas($id_tienda) 
    {
        $sql = "SELECT r.id as numero_reseñas,
                AVG(r.calificacion) as sumatoria
                FROM reseña r
                JOIN producto_tienda pt ON r.id_producto_tienda = pt.id
                JOIN  tienda t ON pt.id_tienda = t.id
                WHERE t.id=:id_tienda
                AND t.estado = 'A'";
        $query = $this->acceso->prepare($sql);
        $query->execute(array(':id_tienda' => $id_tienda));
        $this->objetos = $query->fetchAll();
         
    }

    function obtener_redes_sociales($id_tienda) {
        $sql = "SELECT facebook, instagram, tiktok, youtube, whatsapp, email_contacto, sitio_web 
                FROM tienda 
                WHERE id = :id_tienda AND estado = 'A'";
        $query = $this->acceso->prepare($sql);
        $query->execute(array(':id_tienda' => $id_tienda));
        $this->objetos = $query->fetchAll();
        return $this->objetos;
    }
    
}