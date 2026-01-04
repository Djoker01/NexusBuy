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
    
    // Método para obtener marcas paginadas
    public function obtener_marcas_paginadas($limite, $offset) {
        $sql = "SELECT nombre, logo, descripcion 
                FROM marca 
                ORDER BY nombre 
                LIMIT :limite OFFSET :offset";
        $query = $this->acceso->prepare($sql);
        $query->bindParam(':limite', $limite, PDO::PARAM_INT);
        $query->bindParam(':offset', $offset, PDO::PARAM_INT);
        $query->execute();
        $this->objetos = $query->fetchAll(PDO::FETCH_OBJ);
        return $this->objetos;
    }

    // Método para obtener el total de marcas
    public function obtener_total_marcas() {
        $sql = "SELECT COUNT(*) as total FROM marca";
        $query = $this->acceso->prepare($sql);
        $query->execute();
        $resultado = $query->fetch(PDO::FETCH_OBJ);
        return $resultado->total;
    }
}