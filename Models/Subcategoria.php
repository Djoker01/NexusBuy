<?php
include_once 'Conexion.php';
class Subcategoria {
    var $objetos;
    var $acceso;
    public function __construct() {
        $db = new Conexion();
        $this->acceso = $db->pdo;
    }

    function obtener_subcategorias_por_categoria($id_categoria) {
        $sql = "SELECT * FROM subcategoria 
                WHERE id_categoria = :id_categoria 
                AND estado = 'A' 
                ORDER BY nombre ASC";
        $query = $this->acceso->prepare($sql);
        $query->execute(array(':id_categoria' => $id_categoria));
        $this->objetos = $query->fetchAll();
        return $this->objetos;
    }

    function obtener_todas_subcategorias_activas() {
        $sql = "SELECT sc.*, c.nombre as categoria_nombre 
                FROM subcategoria sc
                JOIN categoria c ON sc.id_categoria = c.id
                WHERE sc.estado = 'A' 
                ORDER BY c.nombre, sc.nombre ASC";
        $query = $this->acceso->prepare($sql);
        $query->execute();
        $this->objetos = $query->fetchAll();
        return $this->objetos;
    }

    function buscarSubcategorias($termino) {
        $sql = "SELECT 
                    sc.id as id_subcategoria,
                    sc.nombre as nombre_subcategoria,
                    c.nombre as nombre_categoria
                FROM subcategoria sc
                JOIN categoria c ON sc.id_categoria = c.id
                WHERE (sc.nombre LIKE :termino OR c.nombre LIKE :termino)
                AND sc.estado = 'A' 
                AND c.estado = 'A'
                ORDER BY c.nombre, sc.nombre";
        
        $query = $this->acceso->prepare($sql);
        $query->execute(array(':termino' => '%' . $termino . '%'));
        $this->objetos = $query->fetchAll();
    }
}
?>