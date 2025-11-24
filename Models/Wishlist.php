<?php
include_once 'Conexion.php';
class Wishlist {
    private $conexion;
    
    public function __construct($conexion) {
        $this->conexion = $conexion;
    }
    
    // ✅ Obtener wishlists por usuario
    public function obtenerPorUsuario($idUsuario) {
        $sql = "SELECT w.*, 
                       (SELECT COUNT(*) FROM wishlist_producto wp WHERE wp.id_wishlist = w.id) as total_productos
                FROM wishlist w
                WHERE w.id_usuario = :id_usuario
                ORDER BY w.fecha_creacion DESC";
        
        return $this->conexion->obtenerTodos($sql, [':id_usuario' => $idUsuario]);
    }
    
    // ✅ Obtener wishlist por ID
    public function obtenerPorId($id) {
        $sql = "SELECT w.*, u.nombres, u.apellidos
                FROM wishlist w
                JOIN usuario u ON w.id_usuario = u.id
                WHERE w.id = :id";
        
        return $this->conexion->obtenerUno($sql, [':id' => $id]);
    }
    
    // ✅ Crear wishlist
    public function crear($idUsuario, $nombre, $descripcion = '', $esPublica = false) {
        $sql = "INSERT INTO wishlist (id_usuario, nombre, descripcion, es_publica)
                VALUES (:id_usuario, :nombre, :descripcion, :es_publica)";
        
        return $this->conexion->ejecutar($sql, [
            ':id_usuario' => $idUsuario,
            ':nombre' => $nombre,
            ':descripcion' => $descripcion,
            ':es_publica' => $esPublica
        ]);
    }
    
    // ✅ Actualizar wishlist
    public function actualizar($id, $nombre, $descripcion, $esPublica) {
        $sql = "UPDATE wishlist SET nombre = :nombre, descripcion = :descripcion,
                                  es_publica = :es_publica, fecha_actualizacion = CURRENT_TIMESTAMP
                WHERE id = :id";
        
        return $this->conexion->ejecutar($sql, [
            ':nombre' => $nombre,
            ':descripcion' => $descripcion,
            ':es_publica' => $esPublica,
            ':id' => $id
        ]);
    }
    
    // ✅ Eliminar wishlist
    public function eliminar($id) {
        // Primero eliminar los productos (cascada)
        $sql = "DELETE FROM wishlist WHERE id = :id";
        return $this->conexion->ejecutar($sql, [':id' => $id]);
    }
    
    // ✅ Agregar producto a wishlist
    public function agregarProducto($idWishlist, $idProductoTienda) {
        $sql = "INSERT INTO wishlist_producto (id_wishlist, id_producto_tienda)
                VALUES (:id_wishlist, :id_producto_tienda)";
        
        return $this->conexion->ejecutar($sql, [
            ':id_wishlist' => $idWishlist,
            ':id_producto_tienda' => $idProductoTienda
        ]);
    }
    
    // ✅ Eliminar producto de wishlist
    public function eliminarProducto($idWishlist, $idProductoTienda) {
        $sql = "DELETE FROM wishlist_producto 
                WHERE id_wishlist = :id_wishlist AND id_producto_tienda = :id_producto_tienda";
        
        return $this->conexion->ejecutar($sql, [
            ':id_wishlist' => $idWishlist,
            ':id_producto_tienda' => $idProductoTienda
        ]);
    }
    
    // ✅ Obtener productos de wishlist
    public function obtenerProductos($idWishlist) {
        $sql = "SELECT wp.*, pt.precio_final, p.nombre as producto_nombre, 
                       t.nombre as tienda_nombre, pt.descuento_porcentaje,
                       (SELECT imagen_url FROM producto_imagen WHERE id_producto = pt.id_producto AND es_principal = 1 LIMIT 1) as imagen
                FROM wishlist_producto wp
                JOIN producto_tienda pt ON wp.id_producto_tienda = pt.id
                JOIN producto p ON pt.id_producto = p.id
                JOIN tienda t ON pt.id_tienda = t.id
                WHERE wp.id_wishlist = :id_wishlist
                ORDER BY wp.fecha_agregado DESC";
        
        return $this->conexion->obtenerTodos($sql, [':id_wishlist' => $idWishlist]);
    }
}