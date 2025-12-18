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
                WHERE estado = 'activa' 
                ORDER BY orden ASC";
        $query = $this->acceso->prepare($sql);
        $query->execute();
        $this->objetos = $query->fetchAll();
        return $this->objetos;
    }
    function explorar_categorias(){
        $sql = "SELECT c.id as id,
                        c.nombre as nombre,
                        COALESCE(c.imagen, 'default_category.png') as imagen,
                        COUNT(DISTINCT p.id) as total_productos
                FROM categoria c
                LEFT JOIN subcategoria s ON c.id = s.id_categoria AND s.estado = 'activa'
                LEFT JOIN producto p ON s.id = p.id_subcategoria AND p.estado = 'activo'
                WHERE c.estado = 'activa' 
                GROUP BY c.id, c.nombre
                ORDER BY c.orden";
        $query = $this->acceso->prepare($sql);
        $query->execute();
        $this->objetos = $query->fetchAll();
        return $this->objetos;
    }
    
}
?>