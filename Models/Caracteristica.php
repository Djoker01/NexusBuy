<?php
include_once 'Conexion.php';
class Caracteristica{
    var $objetos;
    var $acceso;
    public function __construct(){
        $db = new Conexion();
        $this->acceso = $db->pdo;
    }
    function ()
    {
        $sql = "";
        $query = $this->acceso->prepare($sql);
        $query->execute();
        $this->objetos = $query->fetchAll();
        return $this->objetos;
    }
    
}