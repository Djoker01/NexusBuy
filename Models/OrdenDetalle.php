<?php
include_once 'Conexion.php';
class OrdenDetalle {
    var $objetos;
    var $acceso;
    
    public function __construct() {
        $db = new Conexion();
        $this->acceso = $db->pdo;
    }

    // Obtener detalles de una orden
    public function obtenerDetallesOrden($id_orden) {
        $sql = "SELECT od.*, p.nombre as producto_nombre, p.imagen_principal as imagen
                FROM orden_detalle od
                JOIN producto_tienda pt ON od.id_producto_tienda = pt.id
                JOIN producto p ON pt.id_producto = p.id
                WHERE od.id_orden = :id_orden";
        
        $query = $this->acceso->prepare($sql);
        $query->execute([':id_orden' => $id_orden]);
        $this->objetos = $query->fetchAll();
        return $this->objetos;
    }
}