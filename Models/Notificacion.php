<?php
include_once 'Conexion.php';
class Notificacion{
    var $objetos;
    var $acceso;
    public function __construct(){
        $db = new Conexion();
        $this->acceso = $db->pdo;
    }
    function read($id_usuario){
        $sql="SELECT * FROM notificacion n
        WHERE n.id_usuario=:id_usuario
        AND n.estado='A'
        AND n.estado_abierto=0 ORDER BY n.fecha_creacion DESC";
        $query = $this->acceso->prepare($sql);
        $query->execute(array(':id_usuario'=>$id_usuario));
        $this->objetos = $query->fetchAll();
        return $this->objetos;
    }
}