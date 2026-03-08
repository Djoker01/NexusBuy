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

    function llenar_productos(
        $id = null,
        $id_subcategoria = null,
        $id_categoria = null,
        $solo_destacados = false,
        $limite = null,
        $id_marca = null,
        $filtro_nuevos = false,
        $id_tienda = false
    ) {
        if ($id) {
            $sql = "SELECT 
                pt.id as id,
                p.id as id_producto,
                p.nombre as producto,
                p.caracteristicas as caracteristicas,
                p.sku as sku,
                COALESCE(pi.imagen_url, 'producto_default.png') as imagen,
                p.descripcion_larga as detalles,
                p.etiquetas as etiquetas,
                m.nombre as marca,
                pt.envio_gratis as envio,
                pt.total_ventas as ventas,
                pt.estado as estado,
                pt.garantia_meses as garantia,
                pt.tiempo_entrega as entrega,
                pt.precio as precio,
                pt.calificacion_promedio as promedio,
                pt.descuento_porcentaje as descuento,
                pt.precio_final as precio_descuento,
                pt.stock as stock,
                pt.stock_minimo as stock_minimo,
                pt.es_destacado as destacado,
                pt.total_resenas as resenas,
                t.id as id_tienda,
                t.calificacion_promedio as clasificacion_tienda,
                t.nombre as tienda,
                t.direccion as direccion,
                c.nombre as categoria,
                sc.nombre as subcategoria,
                pt.fecha_creacion,
                CASE WHEN DATEDIFF(NOW(), pt.fecha_creacion) <= 7 THEN 1 ELSE 0 END as es_nuevo
            FROM producto_tienda pt
            JOIN producto p ON p.id = pt.id_producto
            LEFT JOIN producto_imagen pi ON p.id = pi.id_producto AND pi.es_principal = 1
            JOIN marca m ON m.id = p.id_marca
            JOIN tienda t ON t.id = pt.id_tienda
            JOIN subcategoria sc ON p.id_subcategoria = sc.id
            JOIN categoria c ON sc.id_categoria = c.id
            WHERE pt.id = :id 
            AND pt.estado = 'activo'";

            $query = $this->acceso->prepare($sql);
            $query->execute(array(':id' => $id));
            $this->objetos = $query->fetchAll();
            return $this->objetos;
        } elseif ($id_tienda) {
            $sql = "SELECT 
                pt.id as id,
                p.id as id_producto,
                p.nombre as producto,
                p.caracteristicas as caracteristicas,
                p.sku as sku,
                COALESCE(pi.imagen_url, 'producto_default.png') as imagen,
                p.descripcion_larga as detalles,
                p.etiquetas as etiquetas,
                m.nombre as marca,
                pt.envio_gratis as envio,
                pt.total_ventas as ventas,
                pt.estado as estado,
                pt.garantia_meses as garantia,
                pt.tiempo_entrega as entrega,
                pt.precio as precio,
                pt.calificacion_promedio as promedio,
                pt.descuento_porcentaje as descuento,
                pt.precio_final as precio_descuento,
                pt.stock as stock,
                pt.stock_minimo as stock_minimo,
                pt.es_destacado as destacado,
                pt.total_resenas as resenas,
                t.id as id_tienda,
                t.calificacion_promedio as clasificacion_tienda,
                t.nombre as tienda,
                t.direccion as direccion,
                c.nombre as categoria,
                sc.nombre as subcategoria,
                pt.fecha_creacion,
                CASE WHEN DATEDIFF(NOW(), pt.fecha_creacion) <= 7 THEN 1 ELSE 0 END as es_nuevo
            FROM producto_tienda pt
            JOIN producto p ON p.id = pt.id_producto
            LEFT JOIN producto_imagen pi ON p.id = pi.id_producto AND pi.es_principal = 1
            JOIN marca m ON m.id = p.id_marca
            JOIN tienda t ON t.id = pt.id_tienda
            JOIN subcategoria sc ON p.id_subcategoria = sc.id
            JOIN categoria c ON sc.id_categoria = c.id
            WHERE t.id = :id_tienda 
            AND pt.estado = 'activo'";

            $query = $this->acceso->prepare($sql);
            $query->execute(array(':id_tienda' => $id_tienda));
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
                p.etiquetas as etiquetas,
                m.nombre as marca,
                pt.envio_gratis as envio,
                pt.total_ventas as ventas,
                pt.estado as estado,
                pt.precio as precio,
                pt.calificacion_promedio as promedio,
                pt.descuento_porcentaje as descuento,
                pt.precio_final as precio_descuento,
                pt.stock as stock,
                pt.stock_minimo as stock_minimo,
                pt.es_destacado as destacado,
                pt.total_resenas as resenas,
                t.id as id_tienda,
                t.nombre as tienda,
                t.direccion as direccion,
                c.nombre as categoria,
                sc.nombre as subcategoria,
                pt.fecha_creacion,
                CASE WHEN DATEDIFF(NOW(), pt.fecha_creacion) <= 7 THEN 1 ELSE 0 END as es_nuevo
            FROM producto_tienda pt
            JOIN producto p ON p.id = pt.id_producto
            LEFT JOIN producto_imagen pi ON p.id = pi.id_producto AND pi.es_principal = 1
            JOIN marca m ON m.id = p.id_marca
            JOIN tienda t ON t.id = pt.id_tienda
            JOIN subcategoria sc ON p.id_subcategoria = sc.id
            JOIN categoria c ON sc.id_categoria = c.id
            WHERE p.id_subcategoria = :id_subcategoria
            AND pt.estado = 'activo'";

            if ($filtro_nuevos) {
                $sql .= " AND DATEDIFF(NOW(), pt.fecha_creacion) <= 7";
            }

            if ($id_marca) {
                $sql .= " AND m.id = :id_marca";
            }

            if ($solo_destacados) {
                $sql .= " AND pt.es_destacado = 1";
            }

            $sql .= " ORDER BY pt.total_ventas DESC, pt.fecha_creacion DESC";

            $params = array(':id_subcategoria' => $id_subcategoria);
            if ($id_marca) {
                $params[':id_marca'] = $id_marca;
            }

            $query = $this->acceso->prepare($sql);
            $query->execute($params);
            $this->objetos = $query->fetchAll();
            return $this->objetos;
        } elseif ($id_categoria) {
            // Usar la función específica para categorías con filtro de marca
            return $this->llenar_productos_por_categorias($id_categoria, $solo_destacados, $id_marca, $filtro_nuevos);
        } else {
            $sql = "SELECT 
                pt.id as id,
                p.id as id_producto,
                p.nombre as producto,
                p.caracteristicas as caracteristicas,
                p.sku as sku,
                COALESCE(pi.imagen_url, 'producto_default.png') as imagen,
                p.descripcion_larga as detalles,
                p.etiquetas as etiquetas,
                m.nombre as marca,
                pt.envio_gratis as envio,
                pt.total_ventas as ventas,
                pt.estado as estado,
                pt.precio as precio,
                pt.calificacion_promedio as promedio,
                pt.descuento_porcentaje as descuento,
                pt.precio_final as precio_descuento,
                pt.stock as stock,
                pt.stock_minimo as stock_minimo,
                pt.es_destacado as destacado,
                pt.total_resenas as resenas,
                t.id as id_tienda,
                t.nombre as tienda,
                t.direccion as direccion,
                c.nombre as categoria,
                sc.nombre as subcategoria,
                pt.fecha_creacion,
                CASE WHEN DATEDIFF(NOW(), pt.fecha_creacion) <= 7 THEN 1 ELSE 0 END as es_nuevo
            FROM producto_tienda pt
            JOIN producto p ON p.id = pt.id_producto
            LEFT JOIN producto_imagen pi ON p.id = pi.id_producto AND pi.es_principal = 1
            JOIN marca m ON m.id = p.id_marca
            JOIN tienda t ON t.id = pt.id_tienda
            JOIN subcategoria sc ON p.id_subcategoria = sc.id
            JOIN categoria c ON sc.id_categoria = c.id
            WHERE pt.estado = 'activo'";

            if ($filtro_nuevos) {
                $sql .= " AND DATEDIFF(NOW(), pt.fecha_creacion) <= 7";
            }

            if ($id_marca) {
                $sql .= " AND m.id = :id_marca";
            }

            if ($solo_destacados) {
                $sql .= " AND pt.es_destacado = 1";
            }

            $sql .= " ORDER BY pt.total_ventas DESC, pt.fecha_creacion DESC";

            // LIMITE PARA PRODUCTOS DESTACADOS
            if ($limite !== null && is_numeric($limite)) {
                $sql .= " LIMIT " . intval($limite);
            }

            $query = $this->acceso->prepare($sql);

            if ($id_marca) {
                $query->execute(array(':id_marca' => $id_marca));
            } else {
                $query->execute();
            }

            $this->objetos = $query->fetchAll();
            return $this->objetos;
        }
    }

    function llenar_productos_por_categorias($id_categoria, $solo_destacados = false, $id_marca = null, $filtro_nuevos = false)
    {
        $sql = "SELECT 
                pt.id as id,
                p.id as id_producto,
                p.nombre as producto,
                p.caracteristicas as caracteristicas,
                p.sku as sku,
                COALESCE(pi.imagen_url, 'producto_default.png') as imagen,
                p.descripcion_larga as detalles,
                p.etiquetas as etiquetas,
                m.nombre as marca,
                pt.envio_gratis as envio,
                pt.precio as precio,
                pt.calificacion_promedio as promedio,
                pt.descuento_porcentaje as descuento,
                pt.precio_final as precio_descuento,
                pt.stock as stock,
                pt.stock_minimo as stock_minimo,
                pt.es_destacado as destacado,
                pt.total_resenas as resenas,
                t.id as id_tienda,
                t.nombre as tienda,
                t.direccion as direccion,
                c.nombre as categoria,
                sc.nombre as subcategoria,
                pt.fecha_creacion,
                CASE WHEN DATEDIFF(NOW(), pt.fecha_creacion) <= 7 THEN 1 ELSE 0 END as es_nuevo
            FROM producto_tienda pt
            JOIN producto p ON p.id = pt.id_producto
            LEFT JOIN producto_imagen pi ON p.id = pi.id_producto AND pi.es_principal = 1
            JOIN marca m ON m.id = p.id_marca
            JOIN tienda t ON t.id = pt.id_tienda
            JOIN subcategoria sc ON p.id_subcategoria = sc.id
            JOIN categoria c ON sc.id_categoria = c.id
            WHERE sc.id_categoria = :id_categoria
            AND pt.estado = 'activo''";

        if ($filtro_nuevos) {
            $sql .= " AND DATEDIFF(NOW(), pt.fecha_creacion) <= 7";
        }

        if ($id_marca) {
            $sql .= " AND m.id = :id_marca";
        }

        if ($solo_destacados) {
            $sql .= " AND pt.es_destacado = 1";
        }

        $sql .= " ORDER BY pt.total_ventas DESC, pt.fecha_creacion DESC";

        $params = array(':id_categoria' => $id_categoria);
        if ($id_marca) {
            $params[':id_marca'] = $id_marca;
        }

        $query = $this->acceso->prepare($sql);
        $query->execute($params);
        $this->objetos = $query->fetchAll();
        return $this->objetos;
    }

    function buscar_productos($termino, $filtros = array())
    {
        $sql = "SELECT 
                pt.id as id,
                p.id as id_producto,
                p.nombre as producto,
                p.caracteristicas as caracteristicas,
                p.sku as sku,
                COALESCE(pi.imagen_url, 'producto_default.png') as imagen,
                p.descripcion_larga as detalles,
                p.etiquetas as etiquetas,
                m.nombre as marca,
                pt.envio_gratis as envio,
                pt.precio as precio,
                pt.calificacion_promedio as promedio,
                pt.descuento_porcentaje as descuento,
                pt.precio_final as precio_descuento,
                pt.stock as stock,
                pt.stock_minimo as stock_minimo,
                pt.es_destacado as destacado,
                pt.total_resenas as resenas,
                t.id as id_tienda,
                t.nombre as tienda,
                t.direccion as direccion,
                c.nombre as categoria,
                sc.nombre as subcategoria,
                pt.fecha_creacion,
                CASE WHEN DATEDIFF(NOW(), pt.fecha_creacion) <= 7 THEN 1 ELSE 0 END as es_nuevo
            FROM producto_tienda pt
            JOIN producto p ON p.id = pt.id_producto
            LEFT JOIN producto_imagen pi ON p.id = pi.id_producto AND pi.es_principal = 1
            JOIN marca m ON m.id = p.id_marca
            JOIN tienda t ON t.id = pt.id_tienda
            JOIN subcategoria sc ON p.id_subcategoria = sc.id
            JOIN categoria c ON sc.id_categoria = c.id
            WHERE pt.estado = 'activo'";

        $params = array();

        // Búsqueda por término
        if (!empty($termino)) {
            $sql .= " AND (p.nombre LIKE :termino 
                          OR p.descripcion_larga LIKE :termino 
                          OR m.nombre LIKE :termino
                          OR p.etiquetas LIKE :termino
                          OR t.nombre LIKE :termino
                          OR c.nombre LIKE :termino
                          OR sc.nombre LIKE :termino)";
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

        // Filtro por marca
        if (!empty($filtros['id_marca'])) {
            $sql .= " AND m.id = :id_marca";
            $params[':id_marca'] = $filtros['id_marca'];
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

    // ================================
    // GESTION DE PRODUCTOS POR TIENDA
    // ================================

    // Obtener productos por tienda
    function obtener_productos_por_tienda_paginados($id_tienda, $offset, $limit)
    {
        $sql = "SELECT 
                pt.id as id,
                p.id as id_producto,
                p.nombre as producto,
                p.caracteristicas as caracteristicas,
                p.sku as sku,
                COALESCE(pi.imagen_url, 'producto_default.png') as imagen,
                p.descripcion_larga as detalles,
                p.etiquetas as etiquetas,
                m.nombre as marca,
                pt.envio_gratis as envio,
                pt.total_ventas as ventas,
                pt.estado as estado,
                pt.garantia_meses as garantia,
                pt.tiempo_entrega as entrega,
                pt.precio as precio,
                pt.calificacion_promedio as promedio,
                pt.descuento_porcentaje as descuento,
                pt.precio_final as precio_descuento,
                pt.stock as stock,
                pt.stock_minimo as stock_minimo,
                pt.es_destacado as destacado,
                pt.total_resenas as resenas,
                t.id as id_tienda,
                t.calificacion_promedio as clasificacion_tienda,
                t.nombre as tienda,
                t.direccion as direccion,
                c.nombre as categoria,
                sc.nombre as subcategoria,
                pt.fecha_creacion,
                CASE WHEN DATEDIFF(NOW(), pt.fecha_creacion) <= 7 THEN 1 ELSE 0 END as es_nuevo
            FROM producto_tienda pt
            JOIN producto p ON p.id = pt.id_producto
            LEFT JOIN producto_imagen pi ON p.id = pi.id_producto AND pi.es_principal = 1
            JOIN marca m ON m.id = p.id_marca
            JOIN tienda t ON t.id = pt.id_tienda
            JOIN subcategoria sc ON p.id_subcategoria = sc.id
            JOIN categoria c ON sc.id_categoria = c.id
            WHERE t.id = :id_tienda
            LIMIT :limit OFFSET :offset";
        $query = $this->acceso->prepare($sql);
        $query->bindParam(':id_tienda', $id_tienda);
        $query->bindParam(':limit', $limit, PDO::PARAM_INT);
        $query->bindParam(':offset', $offset, PDO::PARAM_INT);
        $query->execute();

        return $query->fetchAll(PDO::FETCH_OBJ);
    }

    function contar_productos_tienda($id_tienda) {
        $sql = "SELECT COUNT(*) as total 
                FROM producto_tienda 
                WHERE id_tienda = :id_tienda 
                AND estado = 'activo'";
        
        $query = $this->acceso->prepare($sql);
        $query->execute([':id_tienda' => $id_tienda]);
        $resultado = $query->fetch(PDO::FETCH_OBJ);
        
        return $resultado->total;
    }

     /**
     * Obtener productos por tienda con todos los filtros aplicados
     */
    function obtener_por_tienda_con_filtros($filtros) {
        $sql = "SELECT 
                pt.id as id,
                p.id as id_producto,
                p.nombre as producto,
                p.caracteristicas as caracteristicas,
                p.sku as sku,
                COALESCE(pi.imagen_url, 'producto_default.png') as imagen,
                p.descripcion_larga as detalles,
                p.etiquetas as etiquetas,
                m.nombre as marca,
                pt.envio_gratis as envio,
                pt.total_ventas as ventas,
                pt.estado as estado,
                pt.garantia_meses as garantia,
                pt.tiempo_entrega as entrega,
                pt.precio as precio,
                pt.calificacion_promedio as promedio,
                pt.descuento_porcentaje as descuento,
                pt.precio_final as precio_descuento,
                pt.stock as stock,
                pt.stock_minimo as stock_minimo,
                pt.es_destacado = 1 as destacado,
                pt.total_resenas as resenas,
                t.id as id_tienda,
                t.calificacion_promedio as clasificacion_tienda,
                t.nombre as tienda,
                t.direccion as direccion,
                c.nombre as categoria,
                sc.nombre as subcategoria,
                pt.fecha_creacion,
                CASE WHEN DATEDIFF(NOW(), pt.fecha_creacion) <= 7 THEN 1 ELSE 0 END as es_nuevo
            FROM producto_tienda pt
            JOIN producto p ON p.id = pt.id_producto
            LEFT JOIN producto_imagen pi ON p.id = pi.id_producto AND pi.es_principal = 1
            JOIN marca m ON m.id = p.id_marca
            JOIN tienda t ON t.id = pt.id_tienda
            JOIN subcategoria sc ON p.id_subcategoria = sc.id
            JOIN categoria c ON sc.id_categoria = c.id
            WHERE pt.id_tienda = :id_tienda";
        
        $params = [':id_tienda' => $filtros['id_tienda']];
        
        // Filtro por búsqueda (nombre o SKU)
        if (!empty($filtros['buscar'])) {
            $sql .= " AND (p.nombre LIKE :buscar OR p.sku LIKE :buscar)";
            $params[':buscar'] = "%" . $filtros['buscar'] . "%";
        }
        
        // Filtro por categoría
        if (!empty($filtros['categoria']) && $filtros['categoria'] > 0) {
            $sql .= " AND sc.id_categoria = :categoria";
            $params[':categoria'] = $filtros['categoria'];
        }
        
        // Filtro por subcategoría
        if (!empty($filtros['subcategoria']) && $filtros['subcategoria'] > 0) {
            $sql .= " AND p.id_subcategoria = :subcategoria";
            $params[':subcategoria'] = $filtros['subcategoria'];
        }
        
        // Filtro por estado
        if (!empty($filtros['estado'])) {
            $sql .= " AND pt.estado = :estado";
            $params[':estado'] = $filtros['estado'];
        }
        
        // Filtro por stock
        if (!empty($filtros['stock'])) {
            switch ($filtros['stock']) {
                case 'bajo':
                    $sql .= " AND pt.stock > 0 AND pt.stock <= 5";
                    break;
                case 'critico':
                    $sql .= " AND pt.stock > 0 AND pt.stock <= 2";
                    break;
                case 'agotado':
                    $sql .= " AND pt.stock <= 0";
                    break;
                case 'disponible':
                    $sql .= " AND pt.stock > 0";
                    break;
            }
        }
        
        // Filtro por destacado
        if (!empty($filtros['destacado']) && $filtros['destacado'] == 1) {
            $sql .= " AND pt.es_destacado = 1";
        }
        
        // Filtro por fechas
        if (!empty($filtros['fecha_desde'])) {
            $sql .= " AND DATE(pt.fecha_creacion) >= :fecha_desde";
            $params[':fecha_desde'] = $filtros['fecha_desde'];
        }
        
        if (!empty($filtros['fecha_hasta'])) {
            $sql .= " AND DATE(pt.fecha_creacion) <= :fecha_hasta";
            $params[':fecha_hasta'] = $filtros['fecha_hasta'];
        }
        
        // Ordenamiento
        switch ($filtros['ordenar_por']) {
            case 'fecha_asc':
                $sql .= " ORDER BY p.fecha_creacion ASC";
                break;
            case 'fecha_desc':
                $sql .= " ORDER BY pt.fecha_creacion DESC";
                break;
            case 'nombre_asc':
                $sql .= " ORDER BY p.nombre ASC";
                break;
            case 'nombre_desc':
                $sql .= " ORDER BY p.nombre DESC";
                break;
            case 'precio_asc':
                $sql .= " ORDER BY pt.precio ASC";
                break;
            case 'precio_desc':
                $sql .= " ORDER BY pt.precio DESC";
                break;
            case 'stock_asc':
                $sql .= " ORDER BY pt.stock ASC";
                break;
            case 'vendidos_desc':
                $sql .= " ORDER BY pt.total_ventas DESC";
                break;
            default: // fecha_desc
                $sql .= " ORDER BY p.id ASC";
                break;
        }
        
        // Paginación
        if (isset($filtros['limite'])) {
            $sql .= " LIMIT :limite OFFSET :offset";
            $params[':limite'] = (int)$filtros['limite'];
            $params[':offset'] = (int)$filtros['offset'];
        }
        
        $query = $this->acceso->prepare($sql);
        
        // Bind de parámetros con tipos específicos
        foreach ($params as $key => $value) {
            if ($key == ':limite' || $key == ':offset') {
                $query->bindValue($key, $value, PDO::PARAM_INT);
            } else {
                $query->bindValue($key, $value);
            }
        }
        
        $query->execute();
        $this->objetos = $query->fetchAll(PDO::FETCH_OBJ);
        return $this->objetos;
    }

    /**
     * Contar productos con los mismos filtros (para paginación)
     */
    function contar_por_tienda_con_filtros($filtros) {
        $sql = "SELECT COUNT(*) as total 
                FROM producto_tienda pt
                JOIN producto p ON p.id = pt.id_producto
                JOIN subcategoria sc ON p.id_subcategoria = sc.id
                JOIN categoria c ON sc.id_categoria = c.id
                WHERE pt.id_tienda = :id_tienda";
        
        $params = [':id_tienda' => $filtros['id_tienda']];
        
        // Aplicar los mismos filtros que en la consulta principal
        if (!empty($filtros['buscar'])) {
            $sql .= " AND (p.nombre LIKE :buscar OR p.sku LIKE :buscar)";
            $params[':buscar'] = "%" . $filtros['buscar'] . "%";
        }
        
        if (!empty($filtros['categoria']) && $filtros['categoria'] > 0) {
            $sql .= " AND sc.id_categoria = :categoria";
            $params[':categoria'] = $filtros['categoria'];
        }
        
        if (!empty($filtros['subcategoria']) && $filtros['subcategoria'] > 0) {
            $sql .= " AND p.id_subcategoria = :subcategoria";
            $params[':subcategoria'] = $filtros['subcategoria'];
        }
        
        if (!empty($filtros['estado'])) {
            $sql .= " AND pt.estado = :estado";
            $params[':estado'] = $filtros['estado'];
        }
        
        if (!empty($filtros['stock'])) {
            switch ($filtros['stock']) {
                case 'bajo':
                    $sql .= " AND pt.stock > 0 AND pt.stock <= 5";
                    break;
                case 'critico':
                    $sql .= " AND pt.stock > 0 AND pt.stock <= 2";
                    break;
                case 'agotado':
                    $sql .= " AND pt.stock <= 0";
                    break;
                case 'disponible':
                    $sql .= " AND pt.stock > 0";
                    break;
            }
        }
        
        if (!empty($filtros['destacado']) && $filtros['destacado'] == 1) {
            $sql .= " AND pt.es_destacado = 1";
        }
        
        if (!empty($filtros['fecha_desde'])) {
            $sql .= " AND DATE(pt.fecha_creacion) >= :fecha_desde";
            $params[':fecha_desde'] = $filtros['fecha_desde'];
        }
        
        if (!empty($filtros['fecha_hasta'])) {
            $sql .= " AND DATE(pt.fecha_creacion) <= :fecha_hasta";
            $params[':fecha_hasta'] = $filtros['fecha_hasta'];
        }
        
        $query = $this->acceso->prepare($sql);
        $query->execute($params);
        $resultado = $query->fetch(PDO::FETCH_OBJ);
         return $resultado ? (int)$resultado->total : 0;
    }

    /**
     * Obtener estadísticas rápidas de la tienda
     */
    function obtener_estadisticas_tienda($id_tienda) {
        $sql = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN pt.estado = 'activo' THEN 1 ELSE 0 END) as activos,
                    SUM(CASE WHEN pt.estado = 'inactivo' THEN 1 ELSE 0 END) as inactivos,
                    SUM(CASE WHEN pt.stock <= 5 AND stock > 0 THEN 1 ELSE 0 END) as stock_bajo,
                    SUM(CASE WHEN pt.stock <= 0 THEN 1 ELSE 0 END) as agotados,
                    SUM(CASE WHEN pt.es_destacado = 1 THEN 1 ELSE 0 END) as destacados
                FROM producto_tienda pt
                WHERE pt.id_tienda = :id_tienda";
        
        $query = $this->acceso->prepare($sql);
        $query->execute([':id_tienda' => $id_tienda]);
        return $query->fetch(PDO::FETCH_OBJ);

        // Asegurar valores por defecto
        if (!$resultado) {
            $resultado = new stdClass();
            $resultado->total = 0;
            $resultado->activos = 0;
            $resultado->inactivos = 0;
            $resultado->stock_bajo = 0;
            $resultado->agotados = 0;
            $resultado->destacados = 0;
        }

         return $resultado;
    }
}
