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
    
    public function obtener_provincia_municipio($id_municipio) {
    $sql = "SELECT id_provincia FROM municipio WHERE id = :id";
    $query = $this->acceso->prepare($sql);
    $query->execute(array(':id' => $id_municipio));
    $resultado = $query->fetch(PDO::FETCH_ASSOC);
    
    return $resultado ? $resultado['id_provincia'] : null;
}

// Método para obtener todos los municipios
public function obtener_todos_municipios() {
    $sql = "SELECT * FROM municipio ORDER BY nombre ASC";
    $query = $this->acceso->prepare($sql);
    $query->execute();
    $this->objetos = $query->fetchAll();
    return $this->objetos;
}
} 