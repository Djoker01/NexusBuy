<?php
include_once 'Conexion.php';
class Marcas
{
    var $objetos;
    var $acceso;
    public function __construct()
    {
        $db = new Conexion();
        $this->acceso = $db->pdo;
    }
    function obtener_marcas(){
        $sql = "SELECT nombre as nombre,
        logo as logo,
        descripcion as descripcion
        FROM marca";
        $query = $this->acceso->prepare($sql);
        $query->execute();
        $this->objetos = $query->fetchAll();
        return $this->objetos;
    }
    
    
    
    
}
