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
        $sql = "SELECT COUNT(r.id) as numero_reseñas,
                COALESCE(AVG(r.calificacion), 0) as sumatoria
                FROM tienda t
                LEFT JOIN producto_tienda pt ON t.id = pt.id_tienda AND pt.estado ='activo'
                LEFT JOIN reseña r ON pt.id = r.id_producto_tienda AND r.estado = 'aprobada'
                WHERE t.id = :id_tienda
                AND t.estado = 'activa'
                GROUP BY t.id";
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