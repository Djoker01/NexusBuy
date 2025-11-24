<?php
include_once 'Conexion.php';
class Municipio{
    var $objetos;
    var $acceso;
    public function __construct(){
        $db = new Conexion();
        $this->acceso = $db->pdo;
    }
    function llenar_municipio($id_provincia){
        $sql="SELECT * FROM municipio
        WHERE id_provincia=:id";
        $query = $this->acceso->prepare($sql);
        $query->execute(array(':id'=>$id_provincia));
        $this->objetos = $query->fetchAll();
        return $this->objetos;
    }
    
} 