<?php
include_once 'Conexion.php';
class Favorito
{
    var $objetos;
    var $acceso;

    public function __construct()
    {
        $db = new Conexion();
        $this->acceso = $db->pdo;
    }

    // Agregar producto a favoritos
    public function agregarFavorito($id_usuario, $id_producto_tienda)
    {
        // Verificar si ya existe
        $sql_verificar = "SELECT id FROM favorito
                         WHERE id_usuario = :id_usuario 
                         AND id_producto_tienda = :id_producto_tienda";
        $query = $this->acceso->prepare($sql_verificar);
        $query->execute([
            ':id_usuario' => $id_usuario,
            ':id_producto_tienda' => $id_producto_tienda
        ]);

        if ($query->fetch()) {
            return false; // Ya existe en favoritos
        }

        // Insertar nuevo favorito
        $sql = "INSERT INTO favorito (id_usuario, id_producto_tienda) 
               VALUES (:id_usuario, :id_producto_tienda)";
        $query = $this->acceso->prepare($sql);
        return $query->execute([
            ':id_usuario' => $id_usuario,
            ':id_producto_tienda' => $id_producto_tienda
        ]);
    }

    // Eliminar producto de favoritos
    public function eliminarFavorito($id_usuario, $id_producto_tienda)
    {
        $sql = "DELETE FROM favorito
                WHERE id_usuario = :id_usuario
                AND id_producto_tienda = :id_producto_tienda";
        $query = $this->acceso->prepare($sql);
        return $query->execute([
            ':id_usuario' => $id_usuario,
            ':id_producto_tienda' => $id_producto_tienda
        ]);
    }

    // Obtener favoritos del usuario con paginación y filtros
    public function obtenerFavoritosUsuario($id_usuario, $pagina = 1, $limite = 12, $filtro_categoria = '', $filtro_precio = '', $orden = 'recientes')
    {
        $offset = ($pagina - 1) * $limite;

        $sql = "SELECT 
                    f.id as favorito_id,
                    f.fecha_agregado,
                    pt.id as id_producto_tienda,
                    pt.precio,
                    pt.descuento_porcentaje as descuento,
                    pt.precio_final as precio_descuento,
                    p.nombre as producto,
                    p.descripcion_corta as detalles,
                    m.nombre as marca,
                    c.nombre as categoria,
                    sc.nombre as subcategoria,
                    COALESCE(pi.imagen_url, 'producto_default.png') as imagen,
                    COALESCE(r.calificacion_promedio, 0) as calificacion
                FROM favorito f
                JOIN producto_tienda pt ON f.id_producto_tienda = pt.id
                JOIN producto p ON pt.id_producto = p.id
                JOIN marca m ON p.id_marca = m.id
                JOIN subcategoria sc ON p.id_subcategoria = sc.id
                JOIN categoria c ON sc.id_categoria = c.id
                LEFT JOIN producto_imagen pi ON p.id =pi.id_producto AND pi.es_principal = 1
                LEFT JOIN (
                    SELECT id_producto_tienda, AVG(calificacion) as calificacion_promedio
                    FROM reseña 
                    WHERE estado = 'aprobada'
                    GROUP BY id_producto_tienda
                ) r ON pt.id = r.id_producto_tienda
                WHERE f.id_usuario = :id_usuario 
                AND pt.estado = 'activo'
                AND p.estado = 'activo'";

        // Aplicar filtros
        $parametros = [':id_usuario' => $id_usuario];

        if ($filtro_categoria && $filtro_categoria != '') {
            $sql .= " AND sc.id = :categoria";
            $parametros[':categoria'] = $filtro_categoria;
        }

        if ($filtro_precio && $filtro_precio != '') {
            list($min, $max) = explode('-', $filtro_precio);
            $sql .= " AND pt.precio_final BETWEEN :precio_min AND :precio_max";
            $parametros[':precio_min'] = $min;
            $parametros[':precio_max'] = $max;
        }

        // Aplicar orden
        switch ($orden) {
            case 'antiguos':
                $sql .= " ORDER BY f.fecha_agregado ASC";
                break;
            case 'precio-asc':
                $sql .= " ORDER BY pt.precio_final ASC";
                break;
            case 'precio-desc':
                $sql .= " ORDER BY pt.precio_final DESC";
                break;
            case 'nombre-asc':
                $sql .= " ORDER BY p.nombre ASC";
                break;
            case 'nombre-desc':
                $sql .= " ORDER BY p.nombre DESC";
                break;
            case 'recientes':
            default:
                $sql .= " ORDER BY f.fecha_agregado DESC";
        }

        // Aplicar paginación
        $sql .= " LIMIT :limite OFFSET :offset";
        $parametros[':limite'] = $limite;
        $parametros[':offset'] = $offset;

        $query = $this->acceso->prepare($sql);

        // Bind parameters
        foreach ($parametros as $key => $value) {
            if ($key === ':limite' || $key === ':offset') {
                $query->bindValue($key, (int)$value, PDO::PARAM_INT);
            } else {
                $query->bindValue($key, $value);
            }
        }

        $query->execute();
        $favoritos = $query->fetchAll(PDO::FETCH_OBJ);

        // Formatear resultados y encriptar IDs
        foreach ($favoritos as $favorito) {
            $favorito->id_encrypted = openssl_encrypt($favorito->id_producto_tienda, CODE, KEY);
            $favorito->calificacion = $favorito->calificacion ? round($favorito->calificacion) : 0;
        }

        return $favoritos;
    }

    public function totalFavoritos($id_usuario){
    $sql = "SELECT COUNT(id) as total
            FROM favorito 
            WHERE id_usuario = :id_usuario";

    $query = $this->acceso->prepare($sql);
    $query->execute(array(':id_usuario' => $id_usuario));
    $resultado = $query->fetch(PDO::FETCH_OBJ);
    
    return $resultado ? $resultado->total : 0;
}

    // Contar total de favoritos para paginación
    public function contarFavoritosUsuario($id_usuario, $filtro_categoria = '', $filtro_precio = '')
    {
        $sql = "SELECT COUNT(*) as total
                FROM favorito f
                JOIN producto_tienda pt ON f.id_producto_tienda = pt.id
                JOIN producto p ON pt.id_producto = p.id
                JOIN subcategoria sc ON p.id_subcategoria = sc.id
                WHERE f.id_usuario = :id_usuario 
                AND pt.estado = 'activo'
                AND p.estado = 'activo'";

        $parametros = [':id_usuario' => $id_usuario];

        if ($filtro_categoria && $filtro_categoria != '') {
            $sql .= " AND sc.id = :categoria";
            $parametros[':categoria'] = $filtro_categoria;
        }

        if ($filtro_precio && $filtro_precio != '') {
            list($min, $max) = explode('-', $filtro_precio);
            $sql .= " AND pt.precio_final BETWEEN :precio_min AND :precio_max";
            $parametros[':precio_min'] = $min;
            $parametros[':precio_max'] = $max;
        }

        $query = $this->acceso->prepare($sql);
        $query->execute($parametros);
        $resultado = $query->fetch(PDO::FETCH_OBJ);

        return $resultado ? $resultado->total : 0;
    }

    // Verificar si un producto está en favoritos
    public function verificarFavorito($id_usuario, $id_producto_tienda)
    {
        $sql = "SELECT id FROM favorito 
               WHERE id_usuario = :id_usuario 
               AND id_producto_tienda = :id_producto_tienda";
        $query = $this->acceso->prepare($sql);
        $query->execute([
            ':id_usuario' => $id_usuario,
            ':id_producto_tienda' => $id_producto_tienda
        ]);

        return $query->fetch() !== false;
    }

    // Limpiar todos los favoritos del usuario
    public function limpiarFavoritos($id_usuario)
    {
        $sql = "DELETE FROM favorito 
               WHERE id_usuario = :id_usuario";
        $query = $this->acceso->prepare($sql);
        return $query->execute([':id_usuario' => $id_usuario]);
    }

    // Obtener categorías de los productos en favoritos
    public function obtenerCategoriasFavoritos($id_usuario)
    {
        $sql = "SELECT DISTINCT sc.id, sc.nombre
                FROM favorito f
                JOIN producto_tienda pt ON f.id_producto_tienda = pt.id
                JOIN producto p ON pt.id_producto = p.id
                JOIN subcategoria sc ON p.id_subcategoria = sc.id
                WHERE f.id_usuario = :id_usuario 
                AND pt.estado = 'activo'
                AND p.estado = 'activo'
                ORDER BY sc.nombre ASC";

        $query = $this->acceso->prepare($sql);
        $query->execute([':id_usuario' => $id_usuario]);
        return $query->fetchAll(PDO::FETCH_OBJ);
    }
}
