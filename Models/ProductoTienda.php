<?php
include_once 'Conexion.php';
class ProductoTienda
{
    var $objetos;
    var $acceso;
    public function __construct()
    {
        $db = new Conexion();
        $this->acceso = $db->pdo;
    }
    function llenar_productos($id = null, $id_subcategoria = null)
    {
        if ($id) {
            $sql = "SELECT pt.id as id,
                p.id as id_producto,
                p.nombre  as producto,
                p.sku as sku,
                p.imagen_principal as imagen,
                p.detalles as detalles,
                m.nombre as marca,
                pt.estado_envio as envio,
                pt.precio as precio,
                pt.descuento as descuento,
                pt.precio - (pt.precio*(pt.descuento*0.01)) as precio_descuento,
                t.id as id_tienda,
                t.nombre as tienda,
                t.direccion as direccion
                FROM producto_tienda pt
                JOIN producto p ON p.id=pt.id_producto
                JOIN marca m ON m.id=p.id_marca
                JOIN tienda t ON t.id=pt.id_tienda
                AND pt.estado='A' AND pt.id=:id";
            $query = $this->acceso->prepare($sql);
            $query->execute(array(':id' => $id));
            $this->objetos = $query->fetchAll();
            return $this->objetos;
        } elseif ($id_subcategoria){
            $sql = "SELECT pt.id as id,
                    p.id as id_producto,
                    p.nombre  as producto,
                    p.sku as sku,
                    p.imagen_principal as imagen,
                    p.detalles as detalles,
                    m.nombre as marca,
                    pt.estado_envio as envio,
                    pt.precio as precio,
                    pt.descuento as descuento,
                    pt.precio - (pt.precio*(pt.descuento*0.01)) as precio_descuento,
                    t.id as id_tienda,
                    t.nombre as tienda,
                    t.direccion as direccion,
                    sc.nombre as subcategoria,
                    c.nombre as categoria
                FROM producto_tienda pt
                JOIN producto p ON p.id=pt.id_producto
                JOIN marca m ON m.id=p.id_marca
                JOIN tienda t ON t.id=pt.id_tienda
                JOIN subcategoria sc ON p.id_subcategoria = sc.id
                JOIN categoria c ON sc.id_categoria = c.id
                WHERE p.id_subcategoria = :id_subcategoria
                AND pt.estado='A'
                AND p.estado='A'";
            $query = $this->acceso->prepare($sql);
            $query->execute(array(':id_subcategoria'=>$id_subcategoria));
            $this->objetos = $query->fetchAll();
            return $this->objetos;
        }else {
            $sql = "SELECT pt.id as id,
                    p.id as id_producto,
                    p.nombre  as producto,
                    p.sku as sku,
                    p.imagen_principal as imagen,
                    p.detalles as detalles,
                    m.nombre as marca,
                    pt.estado_envio as envio,
                    pt.precio as precio,
                    pt.descuento as descuento,
                    pt.precio - (pt.precio*(pt.descuento*0.01)) as precio_descuento,
                    t.id as id_tienda,
                    t.nombre as tienda,
                    t.direccion as direccion
                FROM producto_tienda pt
                JOIN producto p ON p.id=pt.id_producto
                JOIN marca m ON m.id=p.id_marca
                JOIN tienda t ON t.id=pt.id_tienda
                AND pt.estado='A'";
            $query = $this->acceso->prepare($sql);
            $query->execute();
            $this->objetos = $query->fetchAll();
            return $this->objetos;
        }
    }
    function llenar_productos_por_categorias($id_categoria){
        $sql = "SELECT pt.id as id,
                    p.id as id_producto,
                    p.nombre  as producto,
                    p.sku as sku,
                    p.imagen_principal as imagen,
                    p.detalles as detalles,
                    m.nombre as marca,
                    pt.estado_envio as envio,
                    pt.precio as precio,
                    pt.descuento as descuento,
                    pt.precio - (pt.precio*(pt.descuento*0.01)) as precio_descuento,
                    t.id as id_tienda,
                    t.nombre as tienda,
                    t.direccion as direccion,
                    sc.nombre as subcategoria
                FROM producto_tienda pt
                JOIN producto p ON p.id=pt.id_producto
                JOIN marca m ON m.id=p.id_marca
                JOIN tienda t ON t.id=pt.id_tienda
                JOIN subcategoria sc ON p.id_subcategoria = sc.id
                WHERE sc.id_bcategoria = :id_categoria
                AND pt.estado='A'
                AND p.estado='A'";
            $query = $this->acceso->prepare($sql);
            $query->execute(array(':id_categoria'=>$id_categoria));
            $this->objetos = $query->fetchAll();
            return $this->objetos;
    }
    
    
    
    
}
