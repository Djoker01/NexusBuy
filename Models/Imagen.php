<?php
include_once 'Conexion.php';
class Imagen{
    var $objetos;
    var $acceso;
    public function __construct(){
        $db = new Conexion();
        $this->acceso = $db->pdo;
    }
    function capturar_imagenes($id_producto)
    {
        $sql = "SELECT *
                FROM producto_imagen
                WHERE producto_imagen.id_producto=:id_producto
                AND estado='activa' ";
        $query = $this->acceso->prepare($sql);
        $query->execute(array(':id_producto' => $id_producto));
        $this->objetos = $query->fetchAll();
        return $this->objetos;
    }
    
}