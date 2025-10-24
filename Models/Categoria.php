<?php
include_once 'Conexion.php';
class Categoria {
    var $objetos;
    var $acceso;
    public function __construct() {
        $db = new Conexion();
        $this->acceso = $db->pdo;
    }

    function obtener_categorias_activas() {
        $sql = "SELECT * FROM categoria 
                WHERE estado = 'A' 
                ORDER BY nombre ASC";
        $query = $this->acceso->prepare($sql);
        $query->execute();
        $this->objetos = $query->fetchAll();
        return $this->objetos;
    }

    function obtener_categoria_por_id($id) {
        $sql = "SELECT * FROM categoria 
                WHERE id = :id AND estado = 'A'";
        $query = $this->acceso->prepare($sql);
        $query->execute(array(':id' => $id));
        $this->objetos = $query->fetchAll();
        return $this->objetos;
    }
}
?>