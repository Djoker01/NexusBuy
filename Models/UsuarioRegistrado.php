<?php
include_once 'Conexion.php';


class UsuarioRegistrado{
    var $objetos;
    var $acceso;
    public function __construct(){
        $db = new Conexion();
        $this->acceso = $db->pdo;
    }


    function total_usuarios(){
        $sql = "SELECT  COUNT(u.id) as total            
                FROM usuario u";
        $query = $this->acceso->prepare($sql);
        $query->execute();
        $resultado= $query->fetch(PDO::FETCH_ASSOC);
        return $resultado['total'];
    }
    function total_tienda(){
        $sql = "SELECT   COUNT(u.id) as total
                FROM usuario u
                WHERE u.id_tipo = 3";
        $query = $this->acceso->prepare($sql);
        $query->execute();
        $resultado= $query->fetch(PDO::FETCH_ASSOC);
        return $resultado['total'];
    }
}
