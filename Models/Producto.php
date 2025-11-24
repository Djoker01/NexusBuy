<?php
include_once 'Conexion.php';

class Producto
{
    var $objetos;
    var $acceso;
    
    public function __construct()
    {
        $db = new Conexion();
        $this->acceso = $db->pdo;
    }
    
    function llenar_productos($id = null, $id_subcategoria = null, $id_categoria = null)
{
    if ($id) {
        $sql = "SELECT 
                pt.id as id,
                p.id as id_producto,
                p.nombre as producto,
                p.caracteristicas as caracteristicas,
                p.sku as sku,
                COALESCE(pi.imagen_url, 'producto_default.png') as imagen,
                p.descripcion_larga as detalles,
                m.nombre as marca,
                pt.envio_gratis as envio,
                pt.precio as precio,
                pt.calificacion_promedio as promedio,
                pt.descuento_porcentaje as descuento,
                pt.precio_final as precio_descuento,
                pt.stock as stock,
                pt.stock_minimo as stock_minimo,
                t.id as id_tienda,
                t.nombre as tienda,
                t.direccion as direccion,
                pt.fecha_creacion,
                CASE WHEN DATEDIFF(NOW(), pt.fecha_creacion) <= 30 THEN 1 ELSE 0 END as es_nuevo
            FROM producto_tienda pt
            JOIN producto p ON p.id = pt.id_producto
            LEFT JOIN producto_imagen pi ON p.id = pi.id_producto AND pi.es_principal = 1
            JOIN marca m ON m.id = p.id_marca
            JOIN tienda t ON t.id = pt.id_tienda
            WHERE pt.id = :id 
            AND pt.estado = 'activo' 
            AND p.estado = 'activo'";
            
        $query = $this->acceso->prepare($sql);
        $query->execute(array(':id' => $id));
        $this->objetos = $query->fetchAll();
        return $this->objetos;
        
    } elseif ($id_subcategoria) {
        $sql = "SELECT 
                pt.id as id,
                p.id as id_producto,
                p.nombre as producto,
                p.caracteristicas as caracteristicas,
                p.sku as sku,
                COALESCE(pi.imagen_url, 'producto_default.png') as imagen,
                p.descripcion_larga as detalles,
                m.nombre as marca,
                pt.envio_gratis as envio,
                pt.precio as precio,
                pt.calificacion_promedio as promedio,
                pt.descuento_porcentaje as descuento,
                pt.precio_final as precio_descuento,
                pt.stock as stock,
                pt.stock_minimo as stock_minimo,
                t.id as id_tienda,
                t.nombre as tienda,
                t.direccion as direccion,
                pt.fecha_creacion,
                CASE WHEN DATEDIFF(NOW(), pt.fecha_creacion) <= 30 THEN 1 ELSE 0 END as es_nuevo
            FROM producto_tienda pt
            JOIN producto p ON p.id = pt.id_producto
            LEFT JOIN producto_imagen pi ON p.id = pi.id_producto AND pi.es_principal = 1
            JOIN marca m ON m.id = p.id_marca
            JOIN tienda t ON t.id = pt.id_tienda
            JOIN subcategoria sc ON p.id_subcategoria = sc.id
            JOIN categoria c ON sc.id_categoria = c.id
            WHERE p.id_subcategoria = :id_subcategoria
            AND pt.estado = 'activo'
            AND p.estado = 'activo'
            ORDER BY pt.total_ventas DESC, pt.fecha_creacion DESC";
            
        $query = $this->acceso->prepare($sql);
        $query->execute(array(':id_subcategoria' => $id_subcategoria));
        $this->objetos = $query->fetchAll();
        return $this->objetos;
        
    } elseif ($id_categoria) {
        // Usar la función específica para categorías
        return $this->llenar_productos_por_categorias($id_categoria);
        
    } else {
        $sql = "SELECT 
                pt.id as id,
                p.id as id_producto,
                p.nombre as producto,
                p.caracteristicas as caracteristicas,
                p.sku as sku,
                COALESCE(pi.imagen_url, 'producto_default.png') as imagen,
                p.descripcion_larga as detalles,
                m.nombre as marca,
                pt.envio_gratis as envio,
                pt.precio as precio,
                pt.calificacion_promedio as promedio,
                pt.descuento_porcentaje as descuento,
                pt.precio_final as precio_descuento,
                pt.stock as stock,
                pt.stock_minimo as stock_minimo,
                t.id as id_tienda,
                t.nombre as tienda,
                t.direccion as direccion,
                pt.fecha_creacion,
                CASE WHEN DATEDIFF(NOW(), pt.fecha_creacion) <= 30 THEN 1 ELSE 0 END as es_nuevo
            FROM producto_tienda pt
            JOIN producto p ON p.id = pt.id_producto
            LEFT JOIN producto_imagen pi ON p.id = pi.id_producto AND pi.es_principal = 1
            JOIN marca m ON m.id = p.id_marca
            JOIN tienda t ON t.id = pt.id_tienda
            WHERE pt.estado = 'activo'
            AND p.estado = 'activo'
            ORDER BY pt.total_ventas DESC, pt.fecha_creacion DESC
            LIMIT 50";
            
        $query = $this->acceso->prepare($sql);
        $query->execute();
        $this->objetos = $query->fetchAll();
        return $this->objetos;
    }
}
    
    function llenar_productos_por_categorias($id_categoria)
    {
        $sql = "SELECT 
                pt.id as id,
                p.id as id_producto,
                p.nombre as producto,
                p.sku as sku,
                p.imagen_principal as imagen,
                p.descripcion_corta as detalles,
                m.nombre as marca,
                pt.envio_gratis as envio,
                pt.precio as precio,
                pt.descuento_porcentaje as descuento,
                pt.precio_final as precio_descuento,
                pt.stock as stock,
                t.id as id_tienda,
                t.nombre as tienda,
                t.direccion as direccion,
                sc.nombre as subcategoria,
                pt.fecha_creacion,
                CASE WHEN DATEDIFF(NOW(), pt.fecha_creacion) <= 30 THEN 1 ELSE 0 END as es_nuevo
            FROM producto_tienda pt
            JOIN producto p ON p.id = pt.id_producto
            JOIN marca m ON m.id = p.id_marca
            JOIN tienda t ON t.id = pt.id_tienda
            JOIN subcategoria sc ON p.id_subcategoria = sc.id
            WHERE sc.id_categoria = :id_categoria
            AND pt.estado = 'activo'
            AND p.estado = 'activo'
            ORDER BY pt.total_ventas DESC, pt.fecha_creacion DESC";
            
        $query = $this->acceso->prepare($sql);
        $query->execute(array(':id_categoria' => $id_categoria));
        $this->objetos = $query->fetchAll();
        return $this->objetos;
    }
    
    function buscar_productos($termino, $filtros = array())
    {
        $sql = "SELECT 
                pt.id as id,
                p.id as id_producto,
                p.nombre as producto,
                p.sku as sku,
                p.imagen_principal as imagen,
                p.descripcion_corta as detalles,
                m.nombre as marca,
                pt.envio_gratis as envio,
                pt.precio as precio,
                pt.descuento_porcentaje as descuento,
                pt.precio_final as precio_descuento,
                pt.stock as stock,
                t.id as id_tienda,
                t.nombre as tienda,
                sc.nombre as subcategoria,
                c.nombre as categoria,
                pt.fecha_creacion,
                CASE WHEN DATEDIFF(NOW(), pt.fecha_creacion) <= 30 THEN 1 ELSE 0 END as es_nuevo
            FROM producto_tienda pt
            JOIN producto p ON p.id = pt.id_producto
            JOIN marca m ON m.id = p.id_marca
            JOIN tienda t ON t.id = pt.id_tienda
            JOIN subcategoria sc ON p.id_subcategoria = sc.id
            JOIN categoria c ON sc.id_categoria = c.id
            WHERE pt.estado = 'activo'
            AND p.estado = 'activo'";
        
        $params = array();
        
        // Búsqueda por término
        if (!empty($termino)) {
            $sql .= " AND (p.nombre LIKE :termino OR p.descripcion_corta LIKE :termino OR m.nombre LIKE :termino)";
            $params[':termino'] = "%$termino%";
        }
        
        // Filtros adicionales
        if (!empty($filtros['id_categoria'])) {
            $sql .= " AND c.id = :id_categoria";
            $params[':id_categoria'] = $filtros['id_categoria'];
        }
        
        if (!empty($filtros['id_subcategoria'])) {
            $sql .= " AND sc.id = :id_subcategoria";
            $params[':id_subcategoria'] = $filtros['id_subcategoria'];
        }
        
        if (!empty($filtros['precio_min'])) {
            $sql .= " AND pt.precio_final >= :precio_min";
            $params[':precio_min'] = $filtros['precio_min'];
        }
        
        if (!empty($filtros['precio_max'])) {
            $sql .= " AND pt.precio_final <= :precio_max";
            $params[':precio_max'] = $filtros['precio_max'];
        }
        
        $sql .= " ORDER BY pt.total_ventas DESC, pt.fecha_creacion DESC";
        
        $query = $this->acceso->prepare($sql);
        $query->execute($params);
        $this->objetos = $query->fetchAll();
        return $this->objetos;
    }
}

