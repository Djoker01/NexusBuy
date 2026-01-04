<?php
include_once 'Conexion.php';

class Ofertas
{
    var $objetos;
    var $acceso;
    
    public function __construct()
    {
        $db = new Conexion();
        $this->acceso = $db->pdo;
    }

    /**
     * Obtener ofertas flash activas desde la tabla oferta_flash
     */
    function obtener_ofertas_flash($limite = 8)
{
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
                pt.precio as precio_original,
                of.precio_especial as precio,
                ROUND(((pt.precio - of.precio_especial) / pt.precio * 100), 2) as descuento,
                of.precio_especial as precio_descuento,
                COALESCE(of.stock_limitado, pt.stock) as stock,
                pt.stock_minimo as stock_minimo,
                pt.es_destacado as destacado,
                pt.total_resenas as total_resenas,
                pt.calificacion_promedio as calificacion,
                t.id as id_tienda,
                t.nombre as tienda,
                t.direccion as direccion,
                of.fecha_inicio,
                of.fecha_fin,
                CASE WHEN DATEDIFF(of.fecha_inicio, NOW()) <= 2 THEN 1 ELSE 0 END as es_flash_nuevo,
                TIMESTAMPDIFF(HOUR, NOW(), of.fecha_fin) as horas_restantes
            FROM oferta_flash of
            JOIN producto_tienda pt ON pt.id = of.producto_tienda_id
            JOIN producto p ON p.id = pt.id_producto
            LEFT JOIN producto_imagen pi ON p.id = pi.id_producto AND pi.es_principal = 1
            JOIN marca m ON m.id = p.id_marca
            JOIN tienda t ON t.id = pt.id_tienda
            WHERE of.estado = 'activa'
            -- Para desarrollo, mostrar ofertas futuras también
            AND (NOW() BETWEEN of.fecha_inicio AND of.fecha_fin 
                 OR (of.fecha_inicio > NOW() AND of.fecha_fin > NOW()))
            AND (of.stock_limitado IS NULL OR of.stock_vendido < of.stock_limitado)
            AND pt.estado = 'activo'
            AND p.estado = 'activo'
            ORDER BY horas_restantes ASC, of.precio_especial ASC
            LIMIT :limite";
    
    $query = $this->acceso->prepare($sql);
    $query->bindParam(':limite', $limite, PDO::PARAM_INT);
    $query->execute();
    $this->objetos = $query->fetchAll();
    return $this->objetos;
}

    /**
     * Obtener bundles activos desde la tabla bundle
     */
    function obtener_bundles($limite = 4)
{
    $sql = "SELECT 
                b.id,
                b.nombre,
                b.descripcion,
                b.imagen,
                b.precio_original,
                b.precio_oferta,
                b.descuento_porcentaje,
                b.stock,
                b.fecha_inicio,
                b.fecha_fin,
                b.estado,
                t.nombre as tienda,
                t.id as tienda_id,
                COUNT(bp.id) as total_productos,
                GROUP_CONCAT(DISTINCT p.nombre ORDER BY p.nombre SEPARATOR '|') as productos_nombres,
                GROUP_CONCAT(DISTINCT COALESCE(pi.imagen_url, 'producto_default.png') SEPARATOR '|') as productos_imagenes
            FROM bundle b
            JOIN tienda t ON t.id = b.tienda_id
            LEFT JOIN bundle_producto bp ON bp.bundle_id = b.id
            LEFT JOIN producto_tienda pt ON pt.id = bp.producto_tienda_id
            LEFT JOIN producto p ON p.id = pt.id_producto
            LEFT JOIN producto_imagen pi ON p.id = pi.id_producto AND pi.es_principal = 1
            WHERE b.estado = 'activo'
            AND (b.fecha_inicio IS NULL OR NOW() >= b.fecha_inicio)
            AND (b.fecha_fin IS NULL OR NOW() <= b.fecha_fin)
            AND b.stock > 0
            GROUP BY b.id
            ORDER BY b.descuento_porcentaje DESC
            LIMIT :limite";
    
    $query = $this->acceso->prepare($sql);
    $query->bindParam(':limite', $limite, PDO::PARAM_INT);
    $query->execute();
    $this->objetos = $query->fetchAll(PDO::FETCH_ASSOC); // Cambiado a FETCH_ASSOC
    
    return $this->objetos;
}

    /**
     * Obtener productos de un bundle específico
     */
    function obtener_productos_bundle($bundle_id)
    {
        $sql = "SELECT 
                    bp.id,
                    bp.cantidad,
                    p.id as id_producto,
                    p.nombre as producto,
                    p.caracteristicas,
                    p.sku,
                    COALESCE(pi.imagen_url, 'producto_default.png') as imagen,
                    pt.precio as precio_individual,
                    pt.descuento_porcentaje as descuento_individual,
                    m.nombre as marca
                FROM bundle_producto bp
                JOIN producto_tienda pt ON pt.id = bp.producto_tienda_id
                JOIN producto p ON p.id = pt.id_producto
                LEFT JOIN producto_imagen pi ON p.id = pi.id_producto AND pi.es_principal = 1
                JOIN marca m ON m.id = p.id_marca
                WHERE bp.bundle_id = :bundle_id
                ORDER BY bp.id";
        
        $query = $this->acceso->prepare($sql);
        $query->bindParam(':bundle_id', $bundle_id, PDO::PARAM_INT);
        $query->execute();
        $this->objetos = $query->fetchAll();
        return $this->objetos;
    }

    /**
     * Obtener categorías con ofertas activas
     */
    function obtener_categorias_oferta($limite = 4)
{
    $sql = "SELECT 
                co.id,
                c.id as categoria_id,
                c.nombre as categoria,
                co.descuento_maximo,
                co.imagen_banner,
                co.color_fondo,
                co.fecha_inicio,
                co.fecha_fin,
                COALESCE(producto_count.total_productos_oferta, 0) as total_productos_oferta
            FROM categoria_oferta co
            JOIN categoria c ON c.id = co.categoria_id
            LEFT JOIN (
                -- Subconsulta para contar productos con oferta por categoría
                SELECT 
                    cat.id as categoria_id,
                    COUNT(DISTINCT pt.id) as total_productos_oferta
                FROM categoria cat
                JOIN subcategoria sub ON sub.id_categoria = cat.id
                JOIN producto p ON p.id_subcategoria = sub.id
                JOIN producto_tienda pt ON pt.id_producto = p.id
                WHERE pt.descuento_porcentaje > 0
                AND pt.estado = 'activo'
                AND p.estado = 'activo'
                GROUP BY cat.id
            ) producto_count ON producto_count.categoria_id = c.id
            WHERE co.estado = 'activa'
            AND c.estado = 'activa'
            AND (co.fecha_inicio IS NULL OR NOW() >= co.fecha_inicio)
            AND (co.fecha_fin IS NULL OR NOW() <= co.fecha_fin)
            ORDER BY co.orden ASC, co.fecha_inicio DESC
            LIMIT :limite";
    
    $query = $this->acceso->prepare($sql);
    $query->bindParam(':limite', $limite, PDO::PARAM_INT);
    $query->execute();
    $this->objetos = $query->fetchAll();
    return $this->objetos;
}

    /**
     * Obtener suscriptores para ofertas
     */
    function obtener_suscriptores_ofertas($frecuencia = 'semanal')
    {
        $sql = "SELECT 
                    id,
                    email,
                    nombre,
                    frecuencia
                FROM suscripcion_ofertas
                WHERE estado = 'activa'
                AND confirmada = 1
                AND frecuencia = :frecuencia";
        
        $query = $this->acceso->prepare($sql);
        $query->bindParam(':frecuencia', $frecuencia, PDO::PARAM_STR);
        $query->execute();
        $this->objetos = $query->fetchAll();
        return $this->objetos;
    }

    /**
     * Obtener productos con super descuentos (más del X%)
     */
    function obtener_super_descuentos($min_descuento = 50, $limite = 12)
    {
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
                    pt.calificacion_promedio as calificacion,
                    pt.descuento_porcentaje as descuento,
                    pt.precio_final as precio_descuento,
                    pt.stock as stock,
                    pt.stock_minimo as stock_minimo,
                    pt.es_destacado as destacado,
                    pt.total_resenas as total_resenas,
                    t.id as id_tienda,
                    t.nombre as tienda,
                    t.direccion as direccion,
                    pt.fecha_creacion,
                    CASE WHEN DATEDIFF(NOW(), pt.fecha_creacion) <= 7 THEN 1 ELSE 0 END as es_nuevo
                FROM producto_tienda pt
                JOIN producto p ON p.id = pt.id_producto
                LEFT JOIN producto_imagen pi ON p.id = pi.id_producto AND pi.es_principal = 1
                JOIN marca m ON m.id = p.id_marca
                JOIN tienda t ON t.id = pt.id_tienda
                WHERE pt.descuento_porcentaje >= :min_descuento
                AND pt.estado = 'activo'
                AND p.estado = 'activo'
                ORDER BY pt.descuento_porcentaje DESC
                LIMIT :limite";
        
        $query = $this->acceso->prepare($sql);
        $query->bindParam(':min_descuento', $min_descuento, PDO::PARAM_INT);
        $query->bindParam(':limite', $limite, PDO::PARAM_INT);
        $query->execute();
        $this->objetos = $query->fetchAll();
        return $this->objetos;
    }

    /**
     * Obtener productos más vendidos que tienen descuento
     */
    function obtener_mas_vendidos_oferta($limite = 8)
    {
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
                    pt.calificacion_promedio as calificacion,
                    pt.descuento_porcentaje as descuento,
                    pt.precio_final as precio_descuento,
                    pt.stock as stock,
                    pt.stock_minimo as stock_minimo,
                    pt.es_destacado as destacado,
                    pt.total_resenas as total_resenas,
                    t.id as id_tienda,
                    t.nombre as tienda,
                    t.direccion as direccion,
                    pt.fecha_creacion,
                    CASE WHEN DATEDIFF(NOW(), pt.fecha_creacion) <= 7 THEN 1 ELSE 0 END as es_nuevo
                FROM producto_tienda pt
                JOIN producto p ON p.id = pt.id_producto
                LEFT JOIN producto_imagen pi ON p.id = pi.id_producto AND pi.es_principal = 1
                JOIN marca m ON m.id = p.id_marca
                JOIN tienda t ON t.id = pt.id_tienda
                WHERE pt.descuento_porcentaje > 0
                AND pt.estado = 'activo'
                AND p.estado = 'activo'
                ORDER BY pt.total_ventas DESC
                LIMIT :limite";
        
        $query = $this->acceso->prepare($sql);
        $query->bindParam(':limite', $limite, PDO::PARAM_INT);
        $query->execute();
        $this->objetos = $query->fetchAll();
        return $this->objetos;
    }

    /**
     * Obtener productos con descuento y envío gratis
     */
    function obtener_envio_gratis_oferta($limite = 8)
    {
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
                    pt.calificacion_promedio as calificacion,
                    pt.descuento_porcentaje as descuento,
                    pt.precio_final as precio_descuento,
                    pt.stock as stock,
                    pt.stock_minimo as stock_minimo,
                    pt.es_destacado as destacado,
                    pt.total_resenas as total_resenas,
                    t.id as id_tienda,
                    t.nombre as tienda,
                    t.direccion as direccion,
                    pt.fecha_creacion,
                    CASE WHEN DATEDIFF(NOW(), pt.fecha_creacion) <= 7 THEN 1 ELSE 0 END as es_nuevo
                FROM producto_tienda pt
                JOIN producto p ON p.id = pt.id_producto
                LEFT JOIN producto_imagen pi ON p.id = pi.id_producto AND pi.es_principal = 1
                JOIN marca m ON m.id = p.id_marca
                JOIN tienda t ON t.id = pt.id_tienda
                WHERE pt.descuento_porcentaje > 0
                AND pt.envio_gratis = 1
                AND pt.estado = 'activo'
                AND p.estado = 'activo'
                ORDER BY pt.descuento_porcentaje DESC
                LIMIT :limite";
        
        $query = $this->acceso->prepare($sql);
        $query->bindParam(':limite', $limite, PDO::PARAM_INT);
        $query->execute();
        $this->objetos = $query->fetchAll();
        return $this->objetos;
    }

    /**
     * Obtener ofertas por categoría específica
     */
    function obtener_ofertas_por_categoria($id_categoria, $limite = 12)
    {
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
                    pt.calificacion_promedio as calificacion,
                    pt.descuento_porcentaje as descuento,
                    pt.precio_final as precio_descuento,
                    pt.stock as stock,
                    pt.stock_minimo as stock_minimo,
                    pt.es_destacado as destacado,
                    pt.total_resenas as total_resenas,
                    t.id as id_tienda,
                    t.nombre as tienda,
                    t.direccion as direccion,
                    pt.fecha_creacion,
                    CASE WHEN DATEDIFF(NOW(), pt.fecha_creacion) <= 7 THEN 1 ELSE 0 END as es_nuevo
                FROM producto_tienda pt
                JOIN producto p ON p.id = pt.id_producto
                LEFT JOIN producto_imagen pi ON p.id = pi.id_producto AND pi.es_principal = 1
                JOIN marca m ON m.id = p.id_marca
                JOIN tienda t ON t.id = pt.id_tienda
                JOIN subcategoria sc ON p.id_subcategoria = sc.id
                WHERE pt.descuento_porcentaje > 0
                AND sc.id_categoria = :id_categoria
                AND pt.estado = 'activo'
                AND p.estado = 'activo'
                ORDER BY pt.descuento_porcentaje DESC
                LIMIT :limite";
        
        $query = $this->acceso->prepare($sql);
        $query->bindParam(':id_categoria', $id_categoria, PDO::PARAM_INT);
        $query->bindParam(':limite', $limite, PDO::PARAM_INT);
        $query->execute();
        $this->objetos = $query->fetchAll();
        return $this->objetos;
    }

    /**
     * Obtener ofertas recientes (últimos 7 días)
     */
    function obtener_ofertas_recientes($limite = 10)
    {
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
                    pt.calificacion_promedio as calificacion,
                    pt.descuento_porcentaje as descuento,
                    pt.precio_final as precio_descuento,
                    pt.stock as stock,
                    pt.stock_minimo as stock_minimo,
                    pt.es_destacado as destacado,
                    pt.total_resenas as total_resenas,
                    t.id as id_tienda,
                    t.nombre as tienda,
                    t.direccion as direccion,
                    pt.fecha_creacion,
                    CASE WHEN DATEDIFF(NOW(), pt.fecha_creacion) <= 7 THEN 1 ELSE 0 END as es_nuevo
                FROM producto_tienda pt
                JOIN producto p ON p.id = pt.id_producto
                LEFT JOIN producto_imagen pi ON p.id = pi.id_producto AND pi.es_principal = 1
                JOIN marca m ON m.id = p.id_marca
                JOIN tienda t ON t.id = pt.id_tienda
                WHERE pt.descuento_porcentaje > 0
                AND pt.estado = 'activo'
                AND p.estado = 'activo'
                AND pt.fecha_creacion >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                ORDER BY pt.fecha_creacion DESC
                LIMIT :limite";
        
        $query = $this->acceso->prepare($sql);
        $query->bindParam(':limite', $limite, PDO::PARAM_INT);
        $query->execute();
        $this->objetos = $query->fetchAll();
        return $this->objetos;
    }

    /**
     * Obtener productos destacados con descuento
     */
    function obtener_destacados_oferta($limite = 8)
    {
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
                    pt.calificacion_promedio as calificacion,
                    pt.descuento_porcentaje as descuento,
                    pt.precio_final as precio_descuento,
                    pt.stock as stock,
                    pt.stock_minimo as stock_minimo,
                    pt.es_destacado as destacado,
                    pt.total_resenas as total_resenas,
                    t.id as id_tienda,
                    t.nombre as tienda,
                    t.direccion as direccion,
                    pt.fecha_creacion,
                    CASE WHEN DATEDIFF(NOW(), pt.fecha_creacion) <= 7 THEN 1 ELSE 0 END as es_nuevo
                FROM producto_tienda pt
                JOIN producto p ON p.id = pt.id_producto
                LEFT JOIN producto_imagen pi ON p.id = pi.id_producto AND pi.es_principal = 1
                JOIN marca m ON m.id = p.id_marca
                JOIN tienda t ON t.id = pt.id_tienda
                WHERE pt.descuento_porcentaje > 0
                AND pt.es_destacado = 1
                AND pt.estado = 'activo'
                AND p.estado = 'activo'
                ORDER BY pt.descuento_porcentaje DESC
                LIMIT :limite";
        
        $query = $this->acceso->prepare($sql);
        $query->bindParam(':limite', $limite, PDO::PARAM_INT);
        $query->execute();
        $this->objetos = $query->fetchAll();
        return $this->objetos;
    }

    /**
     * Obtener ofertas por rango de descuento
     */
    function obtener_ofertas_por_rango($min_descuento, $max_descuento, $limite = 12)
    {
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
                    pt.calificacion_promedio as calificacion,
                    pt.descuento_porcentaje as descuento,
                    pt.precio_final as precio_descuento,
                    pt.stock as stock,
                    pt.stock_minimo as stock_minimo,
                    pt.es_destacado as destacado,
                    pt.total_resenas as total_resenas,
                    t.id as id_tienda,
                    t.nombre as tienda,
                    t.direccion as direccion,
                    pt.fecha_creacion,
                    CASE WHEN DATEDIFF(NOW(), pt.fecha_creacion) <= 7 THEN 1 ELSE 0 END as es_nuevo
                FROM producto_tienda pt
                JOIN producto p ON p.id = pt.id_producto
                LEFT JOIN producto_imagen pi ON p.id = pi.id_producto AND pi.es_principal = 1
                JOIN marca m ON m.id = p.id_marca
                JOIN tienda t ON t.id = pt.id_tienda
                WHERE pt.descuento_porcentaje BETWEEN :min_descuento AND :max_descuento
                AND pt.estado = 'activo'
                AND p.estado = 'activo'
                ORDER BY pt.descuento_porcentaje DESC
                LIMIT :limite";
        
        $query = $this->acceso->prepare($sql);
        $query->bindParam(':min_descuento', $min_descuento, PDO::PARAM_INT);
        $query->bindParam(':max_descuento', $max_descuento, PDO::PARAM_INT);
        $query->bindParam(':limite', $limite, PDO::PARAM_INT);
        $query->execute();
        $this->objetos = $query->fetchAll();
        return $this->objetos;
    }

    /**
     * Obtener ofertas por marca específica
     */
    function obtener_ofertas_por_marca($id_marca, $limite = 8)
    {
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
                    pt.calificacion_promedio as calificacion,
                    pt.descuento_porcentaje as descuento,
                    pt.precio_final as precio_descuento,
                    pt.stock as stock,
                    pt.stock_minimo as stock_minimo,
                    pt.es_destacado as destacado,
                    pt.total_resenas as total_resenas,
                    t.id as id_tienda,
                    t.nombre as tienda,
                    t.direccion as direccion,
                    pt.fecha_creacion,
                    CASE WHEN DATEDIFF(NOW(), pt.fecha_creacion) <= 7 THEN 1 ELSE 0 END as es_nuevo
                FROM producto_tienda pt
                JOIN producto p ON p.id = pt.id_producto
                LEFT JOIN producto_imagen pi ON p.id = pi.id_producto AND pi.es_principal = 1
                JOIN marca m ON m.id = p.id_marca
                JOIN tienda t ON t.id = pt.id_tienda
                WHERE pt.descuento_porcentaje > 0
                AND m.id = :id_marca
                AND pt.estado = 'activo'
                AND p.estado = 'activo'
                ORDER BY pt.descuento_porcentaje DESC
                LIMIT :limite";
        
        $query = $this->acceso->prepare($sql);
        $query->bindParam(':id_marca', $id_marca, PDO::PARAM_INT);
        $query->bindParam(':limite', $limite, PDO::PARAM_INT);
        $query->execute();
        $this->objetos = $query->fetchAll();
        return $this->objetos;
    }

    /**
     * Verificar si un producto está en oferta
     */
    function verificar_producto_oferta($id_producto_tienda)
    {
        $sql = "SELECT 
                    pt.id,
                    pt.descuento_porcentaje,
                    pt.precio,
                    pt.precio_final,
                    pt.envio_gratis,
                    pt.stock,
                    'descuento_regular' as tipo_oferta,
                    NULL as fecha_fin_oferta
                FROM producto_tienda pt
                WHERE pt.id = :id_producto_tienda
                AND pt.descuento_porcentaje > 0
                AND pt.estado = 'activo'
                
                UNION ALL
                
                SELECT 
                    pt.id,
                    ROUND(((pt.precio - of.precio_especial) / pt.precio * 100), 2) as descuento_porcentaje,
                    pt.precio,
                    of.precio_especial as precio_final,
                    pt.envio_gratis,
                    COALESCE(of.stock_limitado, pt.stock) as stock,
                    'oferta_flash' as tipo_oferta,
                    of.fecha_fin as fecha_fin_oferta
                FROM producto_tienda pt
                LEFT JOIN oferta_flash of ON pt.id = of.producto_tienda_id
                WHERE pt.id = :id_producto_tienda
                AND of.estado = 'activa'
                AND NOW() BETWEEN of.fecha_inicio AND of.fecha_fin
                LIMIT 1";
        
        $query = $this->acceso->prepare($sql);
        $query->execute(array(':id_producto_tienda' => $id_producto_tienda));
        $this->objetos = $query->fetchAll();
        return $this->objetos;
    }

    /**
     * Obtener todas las ofertas activas (actualizado para usar nuevas tablas)
     */
    function obtener_todas_ofertas()
    {
        $resultados = [];
        
        // Ofertas flash desde nueva tabla
        $resultados['flash'] = $this->obtener_ofertas_flash(4);
        
        // Bundles desde nueva tabla
        $resultados['bundles'] = $this->obtener_bundles(2);
        
        // Categorías con oferta desde nueva tabla
        $resultados['categorias_oferta'] = $this->obtener_categorias_oferta(4);
        
        // Ofertas regulares (descuento_porcentaje > 0)
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
                    pt.calificacion_promedio as calificacion,
                    pt.descuento_porcentaje as descuento,
                    pt.precio_final as precio_descuento,
                    pt.stock as stock,
                    pt.stock_minimo as stock_minimo,
                    pt.es_destacado as destacado,
                    pt.total_resenas as total_resenas,
                    t.id as id_tienda,
                    t.nombre as tienda,
                    t.direccion as direccion,
                    pt.fecha_creacion,
                    CASE WHEN DATEDIFF(NOW(), pt.fecha_creacion) <= 7 THEN 1 ELSE 0 END as es_nuevo
                FROM producto_tienda pt
                JOIN producto p ON p.id = pt.id_producto
                LEFT JOIN producto_imagen pi ON p.id = pi.id_producto AND pi.es_principal = 1
                JOIN marca m ON m.id = p.id_marca
                JOIN tienda t ON t.id = pt.id_tienda
                WHERE pt.descuento_porcentaje >= 50
                AND pt.estado = 'activo'
                AND p.estado = 'activo'
                ORDER BY pt.descuento_porcentaje DESC
                LIMIT 6";
        
        $query = $this->acceso->prepare($sql);
        $query->execute();
        $resultados['super'] = $query->fetchAll();
        
        // Más vendidos con descuento
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
                    pt.calificacion_promedio as calificacion,
                    pt.descuento_porcentaje as descuento,
                    pt.precio_final as precio_descuento,
                    pt.stock as stock,
                    pt.stock_minimo as stock_minimo,
                    pt.es_destacado as destacado,
                    pt.total_resenas as total_resenas,
                    t.id as id_tienda,
                    t.nombre as tienda,
                    t.direccion as direccion,
                    pt.fecha_creacion,
                    CASE WHEN DATEDIFF(NOW(), pt.fecha_creacion) <= 7 THEN 1 ELSE 0 END as es_nuevo
                FROM producto_tienda pt
                JOIN producto p ON p.id = pt.id_producto
                LEFT JOIN producto_imagen pi ON p.id = pi.id_producto AND pi.es_principal = 1
                JOIN marca m ON m.id = p.id_marca
                JOIN tienda t ON t.id = pt.id_tienda
                WHERE pt.descuento_porcentaje > 0
                AND pt.estado = 'activo'
                AND p.estado = 'activo'
                ORDER BY pt.total_ventas DESC
                LIMIT 4";
        
        $query = $this->acceso->prepare($sql);
        $query->execute();
        $resultados['vendidos'] = $query->fetchAll();
        
        // Envío gratis con descuento
        $resultados['envio_gratis'] = $this->obtener_envio_gratis_oferta(4);
        
        // Destacados con descuento
        $resultados['destacados'] = $this->obtener_destacados_oferta(4);
        
        return $resultados;
    }

    /**
     * Obtener estadísticas actualizadas con nuevas tablas
     */
    function obtener_estadisticas_ofertas()
    {
        $estadisticas = [];
        
        // Contar productos en oferta regular
        $sql = "SELECT COUNT(*) as total FROM producto_tienda WHERE descuento_porcentaje > 0 AND estado = 'activo'";
        $query = $this->acceso->prepare($sql);
        $query->execute();
        $estadisticas['total_ofertas'] = $query->fetch(PDO::FETCH_OBJ)->total;
        
        // Contar ofertas flash activas
        $sql = "SELECT COUNT(*) as total FROM oferta_flash WHERE estado = 'activa' AND NOW() BETWEEN fecha_inicio AND fecha_fin";
        $query = $this->acceso->prepare($sql);
        $query->execute();
        $estadisticas['total_ofertas_flash'] = $query->fetch(PDO::FETCH_OBJ)->total;
        
        // Contar bundles activos
        $sql = "SELECT COUNT(*) as total FROM bundle WHERE estado = 'activo' AND stock > 0";
        $query = $this->acceso->prepare($sql);
        $query->execute();
        $estadisticas['total_bundles'] = $query->fetch(PDO::FETCH_OBJ)->total;
        
        // Contar categorías con oferta activa
        $sql = "SELECT COUNT(*) as total FROM categoria_oferta WHERE estado = 'activa' AND (fecha_inicio IS NULL OR NOW() >= fecha_inicio) AND (fecha_fin IS NULL OR NOW() <= fecha_fin)";
        $query = $this->acceso->prepare($sql);
        $query->execute();
        $estadisticas['total_categorias_oferta'] = $query->fetch(PDO::FETCH_OBJ)->total;
        
        // Promedio de descuento
        $sql = "SELECT AVG(descuento_porcentaje) as promedio FROM producto_tienda WHERE descuento_porcentaje > 0 AND estado = 'activo'";
        $query = $this->acceso->prepare($sql);
        $query->execute();
        $estadisticas['promedio_descuento'] = round($query->fetch(PDO::FETCH_OBJ)->promedio, 2);
        
        // Producto con mayor descuento (incluyendo ofertas flash)
        $sql = "SELECT 
                    MAX(
                        CASE 
                            WHEN of.id IS NOT NULL THEN ROUND(((pt.precio - of.precio_especial) / pt.precio * 100), 2)
                            ELSE pt.descuento_porcentaje 
                        END
                    ) as max_descuento
                FROM producto_tienda pt
                LEFT JOIN oferta_flash of ON pt.id = of.producto_tienda_id 
                    AND of.estado = 'activa' 
                    AND NOW() BETWEEN of.fecha_inicio AND of.fecha_fin
                WHERE pt.estado = 'activo'";
        $query = $this->acceso->prepare($sql);
        $query->execute();
        $estadisticas['max_descuento'] = $query->fetch(PDO::FETCH_OBJ)->max_descuento;
        
        // Ofertas con envío gratis
        $sql = "SELECT COUNT(*) as envio_gratis FROM producto_tienda WHERE descuento_porcentaje > 0 AND envio_gratis = 1 AND estado = 'activo'";
        $query = $this->acceso->prepare($sql);
        $query->execute();
        $estadisticas['envio_gratis'] = $query->fetch(PDO::FETCH_OBJ)->envio_gratis;
        
        // Total suscriptores activos
        $sql = "SELECT COUNT(*) as total_suscriptores FROM suscripcion_ofertas WHERE estado = 'activa' AND confirmada = 1";
        $query = $this->acceso->prepare($sql);
        $query->execute();
        $estadisticas['total_suscriptores'] = $query->fetch(PDO::FETCH_OBJ)->total_suscriptores;
        
        return $estadisticas;
    }

    /**
     * Suscribir email a ofertas
     */
    function suscribir_email_ofertas($email, $nombre = null, $frecuencia = 'semanal')
    {
        try {
            // Verificar si ya está suscrito y confirmado
            $sql = "SELECT id, confirmada FROM suscripcion_ofertas WHERE email = :email";
            $query = $this->acceso->prepare($sql);
            $query->execute([':email' => $email]);
            
            if ($query->rowCount() > 0) {
                $existing = $query->fetch(PDO::FETCH_ASSOC);
                if ($existing['confirmada'] == 1) {
                    return [
                        'success' => false, 
                        'message' => 'Este email ya está suscrito y confirmado',
                        'confirmed' => true
                    ];
                } else {
                    // Ya existe pero no está confirmado
                    $suscripcion = $this->obtener_suscripcion_por_email($email);
                    return [
                        'success' => false, 
                        'message' => 'Ya hay una suscripción pendiente. Revisa tu email para confirmar.',
                        'pending' => true,
                        'token' => $suscripcion['token_confirmacion'] ?? null
                    ];
                }
            }
            
            // Generar token de confirmación más seguro
            $token = bin2hex(openssl_random_pseudo_bytes(32));
            
            // Insertar suscripción (sin confirmar inicialmente)
            $sql = "INSERT INTO suscripcion_ofertas 
                    (email, nombre, token_confirmacion, frecuencia, estado, confirmada) 
                    VALUES (:email, :nombre, :token, :frecuencia, 'activa', 0)";
            
            $query = $this->acceso->prepare($sql);
            $success = $query->execute([
                ':email' => $email,
                ':nombre' => $nombre,
                ':token' => $token,
                ':frecuencia' => $frecuencia
            ]);
            
            if ($success) {
                $id = $this->acceso->lastInsertId();
                return [
                    'success' => true, 
                    'id' => $id,
                    'token' => $token, 
                    'email' => $email,
                    'nombre' => $nombre,
                    'frecuencia' => $frecuencia,
                    'requires_confirmation' => true,
                    'message' => 'Suscripción creada exitosamente. Por favor, revisa tu email para confirmar.'
                ];
            } else {
                return [
                    'success' => false, 
                    'message' => 'Error al crear la suscripción'
                ];
            }
            
        } catch (Exception $e) {
            error_log("Error en suscribir_email_ofertas: " . $e->getMessage());
            return [
                'success' => false, 
                'message' => 'Error en el servidor'
            ];
        }
    }

     /**
     * OBTENER SUSCRIPCIÓN POR EMAIL (Nueva función)
     */
    function obtener_suscripcion_por_email($email)
    {
        $sql = "SELECT * FROM suscripcion_ofertas WHERE email = :email";
        $query = $this->acceso->prepare($sql);
        $query->execute([':email' => $email]);
        return $query->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * OBTENER SUSCRIPCIÓN POR TOKEN (Nueva función)
     */
    function obtener_suscripcion_por_token($token)
    {
        $sql = "SELECT * FROM suscripcion_ofertas WHERE token_confirmacion = :token";
        $query = $this->acceso->prepare($sql);
        $query->execute([':token' => $token]);
        return $query->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Confirmar suscripción por token
     */
    function confirmar_suscripcion($token)
    {
        try {
            // Buscar suscripción con este token
            $suscripcion = $this->obtener_suscripcion_por_token($token);
            
            if (!$suscripcion) {
                return [
                    'success' => false, 
                    'invalid_token' => true,
                    'message' => 'Token inválido o expirado'
                ];
            }
            
            if ($suscripcion['confirmada'] == 1) {
                return [
                    'success' => false, 
                    'already_confirmed' => true,
                    'message' => 'Esta suscripción ya estaba confirmada'
                ];
            }
            
            // Actualizar para confirmar
            $sql = "UPDATE suscripcion_ofertas 
                    SET confirmada = 1, 
                        fecha_confirmacion = NOW(),
                        token_confirmacion = NULL
                    WHERE id = :id";
            
            $query = $this->acceso->prepare($sql);
            $success = $query->execute([':id' => $suscripcion['id']]);
            
            if ($success) {
                return [
                    'success' => true, 
                    'email' => $suscripcion['email'],
                    'nombre' => $suscripcion['nombre'],
                    'message' => 'Suscripción confirmada exitosamente'
                ];
            } else {
                return [
                    'success' => false, 
                    'message' => 'Error al confirmar la suscripción'
                ];
            }
            
        } catch (Exception $e) {
            error_log("Error en confirmar_suscripcion: " . $e->getMessage());
            return [
                'success' => false, 
                'message' => 'Error en el servidor'
            ];
        }
    }

    /**
     * REENVIAR EMAIL DE CONFIRMACIÓN (Nueva función)
     */
    function reenviar_confirmacion($email)
    {
        try {
            $suscripcion = $this->obtener_suscripcion_por_email($email);
            
            if (!$suscripcion) {
                return [
                    'success' => false, 
                    'message' => 'No hay suscripción para este email'
                ];
            }
            
            if ($suscripcion['confirmada'] == 1) {
                return [
                    'success' => false, 
                    'message' => 'Esta suscripción ya está confirmada'
                ];
            }
            
            // Generar nuevo token si es necesario
            if (empty($suscripcion['token_confirmacion'])) {
                $new_token = bin2hex(openssl_random_pseudo_bytes(32));
                $sql = "UPDATE suscripcion_ofertas SET token_confirmacion = :token WHERE id = :id";
                $query = $this->acceso->prepare($sql);
                $query->execute([
                    ':token' => $new_token,
                    ':id' => $suscripcion['id']
                ]);
                $token = $new_token;
            } else {
                $token = $suscripcion['token_confirmacion'];
            }
            
            return [
                'success' => true, 
                'token' => $token,
                'email' => $suscripcion['email'],
                'nombre' => $suscripcion['nombre'],
                'message' => 'Token disponible para reenvío de confirmación'
            ];
            
        } catch (Exception $e) {
            error_log("Error en reenviar_confirmacion: " . $e->getMessage());
            return [
                'success' => false, 
                'message' => 'Error en el servidor'
            ];
        }
    }

    /**
     * CANCELAR SUSCRIPCIÓN (Nueva función)
     */
    function cancelar_suscripcion($email)
    {
        try {
            $sql = "UPDATE suscripcion_ofertas 
                    SET estado = 'cancelada', 
                        fecha_cancelacion = NOW() 
                    WHERE email = :email 
                    AND estado = 'activa'";
            
            $query = $this->acceso->prepare($sql);
            $success = $query->execute([':email' => $email]);
            
            if ($success && $query->rowCount() > 0) {
                return [
                    'success' => true, 
                    'message' => 'Suscripción cancelada exitosamente'
                ];
            } else {
                return [
                    'success' => false, 
                    'message' => 'No se encontró una suscripción activa para cancelar'
                ];
            }
            
        } catch (Exception $e) {
            error_log("Error en cancelar_suscripcion: " . $e->getMessage());
            return [
                'success' => false, 
                'message' => 'Error en el servidor'
            ];
        }
    }

    /**
     * OBTENER ESTADÍSTICAS DE SUSCRIPCIONES (Nueva función)
     */
    function obtener_estadisticas_suscripciones()
    {
        $sql = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN confirmada = 1 THEN 1 ELSE 0 END) as confirmadas,
                    SUM(CASE WHEN confirmada = 0 THEN 1 ELSE 0 END) as pendientes,
                    SUM(CASE WHEN estado = 'cancelada' THEN 1 ELSE 0 END) as canceladas,
                    frecuencia,
                    DATE(fecha_creacion) as fecha
                FROM suscripcion_ofertas 
                GROUP BY frecuencia, DATE(fecha_creacion)";
        
        $query = $this->acceso->prepare($sql);
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }
}