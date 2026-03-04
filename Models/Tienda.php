<?php
include_once 'Conexion.php';
class Tienda{
    var $objetos;
    var $acceso;
    public function __construct(){
        $db = new Conexion();
        $this->acceso = $db->pdo;
    }
    function contar_reseñas($id_tienda) 
    {
        $sql = "SELECT COUNT(r.id) as numero_reseñas,
                COALESCE(AVG(r.calificacion), 0) as sumatoria
                FROM tienda t
                LEFT JOIN producto_tienda pt ON t.id = pt.id_tienda AND pt.estado ='activo'
                LEFT JOIN reseña r ON pt.id = r.id_producto_tienda AND r.estado = 'aprobada'
                WHERE t.id = :id_tienda
                AND t.estado = 'activa'
                GROUP BY t.id";
        $query = $this->acceso->prepare($sql);
        $query->execute(array(':id_tienda' => $id_tienda));
        $this->objetos = $query->fetchAll();
        
    }

    function obtener_redes_sociales($id_tienda) {
        $sql = "SELECT facebook, instagram, tiktok, youtube, whatsapp, email_contacto, sitio_web 
                FROM tienda 
                WHERE id = :id_tienda AND estado = 'A'";
        $query = $this->acceso->prepare($sql);
        $query->execute(array(':id_tienda' => $id_tienda));
        $this->objetos = $query->fetchAll();
        return $this->objetos;
    }

    // ---------------------------------
    // SECCIÓN PARA VENDEDORES
    // ---------------------------------

    /**
     * Obtener tienda por ID de usuario propietario (vendedor)
     * @param int $id_usuario ID del usuario propietario
     * @return array Datos de la tienda
     */
    function obtener_tienda_por_usuario($id_usuario)
    {
        $sql = "SELECT t.*, m.nombre as municipio
                FROM tienda t
                LEFT JOIN municipio m ON t.id_municipio = m.id
                WHERE t.id_usuario_propietario = :id_usuario";
        
        $query = $this->acceso->prepare($sql);
        $query->execute(array(':id_usuario' => $id_usuario));
        $this->objetos = $query->fetchAll(PDO::FETCH_OBJ);
        return $this->objetos;
    }

    /**
     * Obtener tienda por ID de tienda
     * @param int $id_tienda ID de la tienda
     * @return array Datos de la tienda
     */
    function obtener_tienda_por_id($id_tienda)
    {
        $sql = "SELECT t.*, m.nombre as municipio
                FROM tienda t
                LEFT JOIN municipio m ON t.id_municipio = m.id
                WHERE t.id = :id_tienda
                AND t.estado IN ('activa', 'pendiente')";
        
        $query = $this->acceso->prepare($sql);
        $query->execute(array(':id_tienda' => $id_tienda));
        $this->objetos = $query->fetchAll(PDO::FETCH_OBJ);
        return $this->objetos;
    }

    /**
     * Verificar si el usuario tiene una tienda
     * @param int $id_usuario ID del usuario
     * @return bool True si tiene tienda
     */
    function usuario_tiene_tienda($id_usuario)
    {
        $sql = "SELECT COUNT(*) as total FROM tienda 
                WHERE id_usuario_propietario = :id_usuario 
                AND estado IN ('activa', 'pendiente')";
        
        $query = $this->acceso->prepare($sql);
        $query->execute(array(':id_usuario' => $id_usuario));
        $resultado = $query->fetch(PDO::FETCH_OBJ);
        
        return $resultado->total > 0;
    }

    /**
     * Actualizar datos de la tienda
     * @param int $id_tienda ID de la tienda
     * @param array $datos Datos a actualizar
     * @return bool True si se actualizó
     */
    function actualizar_tienda($id_tienda, $datos)
    {
        $sql = "UPDATE tienda SET 
                nombre = :nombre,
                descripcion = :descripcion,
                telefono = :telefono,
                email = :email,
                sitio_web = :sitio_web,
                direccion = :direccion,
                id_municipio = :id_municipio,
                redes_sociales = :redes_sociales,
                politicas = :politicas,
                fecha_actualizacion = CURRENT_TIMESTAMP
                WHERE id = :id_tienda";
        
        $query = $this->acceso->prepare($sql);
        $query->execute(array(
            ':id_tienda' => $id_tienda,
            ':nombre' => $datos['nombre'] ?? null,
            ':descripcion' => $datos['descripcion'] ?? null,
            ':telefono' => $datos['telefono'] ?? null,
            ':email' => $datos['email'] ?? null,
            ':sitio_web' => $datos['sitio_web'] ?? null,
            ':direccion' => $datos['direccion'] ?? null,
            ':id_municipio' => $datos['id_municipio'] ?? null,
            ':redes_sociales' => $datos['redes_sociales'] ?? null,
            ':politicas' => $datos['politicas'] ?? null
        ));
        
        return $query->rowCount() > 0;
    }

    /**
     * Actualizar logo de la tienda
     * @param int $id_tienda ID de la tienda
     * @param string $logo Nombre del archivo de logo
     * @return bool True si se actualizó
     */
    function actualizar_logo($id_tienda, $logo)
    {
        // Obtener logo actual para eliminar archivo anterior
        $sql_select = "SELECT logo FROM tienda WHERE id = :id_tienda";
        $query_select = $this->acceso->prepare($sql_select);
        $query_select->execute(array(':id_tienda' => $id_tienda));
        $resultado = $query_select->fetch(PDO::FETCH_OBJ);
        
        if ($resultado && $resultado->logo != 'default_store_logo.png') {
            $ruta_anterior = '../Util/Img/Stores/' . $resultado->logo;
            if (file_exists($ruta_anterior)) {
                unlink($ruta_anterior);
            }
        }

        $sql = "UPDATE tienda SET logo = :logo WHERE id = :id_tienda";
        $query = $this->acceso->prepare($sql);
        $query->execute(array(
            ':id_tienda' => $id_tienda,
            ':logo' => $logo
        ));
        
        return $query->rowCount() > 0;
    }

    /**
     * Actualizar banner de la tienda
     * @param int $id_tienda ID de la tienda
     * @param string $banner Nombre del archivo de banner
     * @return bool True si se actualizó
     */
    function actualizar_banner($id_tienda, $banner)
    {
        // Obtener banner actual para eliminar archivo anterior
        $sql_select = "SELECT banner FROM tienda WHERE id = :id_tienda";
        $query_select = $this->acceso->prepare($sql_select);
        $query_select->execute(array(':id_tienda' => $id_tienda));
        $resultado = $query_select->fetch(PDO::FETCH_OBJ);
        
        if ($resultado && $resultado->banner && $resultado->banner != '') {
            $ruta_anterior = '../Util/Img/Stores/' . $resultado->banner;
            if (file_exists($ruta_anterior)) {
                unlink($ruta_anterior);
            }
        }

        $sql = "UPDATE tienda SET banner = :banner WHERE id = :id_tienda";
        $query = $this->acceso->prepare($sql);
        $query->execute(array(
            ':id_tienda' => $id_tienda,
            ':banner' => $banner
        ));
        
        return $query->rowCount() > 0;
    }

    /**
     * Obtener estadísticas de la tienda
     * @param int $id_tienda ID de la tienda
     * @return object Estadísticas
     */
    function obtener_estadisticas($id_tienda)
    {
        $stats = new stdClass();

        // Total de productos en la tienda
        $sql_productos = "SELECT COUNT(*) as total FROM producto_tienda 
                          WHERE id_tienda = :id_tienda AND estado = 'activo'";
        $query = $this->acceso->prepare($sql_productos);
        $query->execute(array(':id_tienda' => $id_tienda));
        $result = $query->fetch(PDO::FETCH_OBJ);
        $stats->total_productos = $result->total ?? 0;

        // Total de pedidos de la tienda
        $sql_pedidos = "SELECT COUNT(*) as total FROM orden 
                        WHERE id_tienda = :id_tienda";
        $query = $this->acceso->prepare($sql_pedidos);
        $query->execute(array(':id_tienda' => $id_tienda));
        $result = $query->fetch(PDO::FETCH_OBJ);
        $stats->total_pedidos = $result->total ?? 0;

        // Total de reseñas de productos de la tienda
        $sql_resenas = "SELECT COUNT(*) as total, COALESCE(AVG(r.calificacion), 0) as promedio 
                        FROM resena r
                        JOIN producto_tienda pt ON r.id_producto_tienda = pt.id
                        WHERE pt.id_tienda = :id_tienda 
                        AND r.estado = 'aprobada'";
        $query = $this->acceso->prepare($sql_resenas);
        $query->execute(array(':id_tienda' => $id_tienda));
        $resenas = $query->fetch(PDO::FETCH_OBJ);
        $stats->total_resenas = $resenas->total ?? 0;
        $stats->promedio_resenas = round($resenas->promedio ?? 0, 1);

        // Ventas totales (suma de subtotales de detalles de órdenes completadas)
        $sql_ventas = "SELECT SUM(od.subtotal) as total 
                       FROM orden_detalle od
                       JOIN orden o ON od.id_orden = o.id
                       WHERE o.id_tienda = :id_tienda 
                       AND o.estado_pago = 'completado'";
        $query = $this->acceso->prepare($sql_ventas);
        $query->execute(array(':id_tienda' => $id_tienda));
        $result = $query->fetch(PDO::FETCH_OBJ);
        $stats->ventas_totales = $result->total ?? 0;

        // Pedidos pendientes
        $sql_pendientes = "SELECT COUNT(*) as total FROM orden 
                           WHERE id_tienda = :id_tienda 
                           AND estado_entrega = 'pendiente'";
        $query = $this->acceso->prepare($sql_pendientes);
        $query->execute(array(':id_tienda' => $id_tienda));
        $result = $query->fetch(PDO::FETCH_OBJ);
        $stats->pedidos_pendientes = $result->total ?? 0;

        // Productos con stock bajo (menor al mínimo)
        $sql_stock_bajo = "SELECT COUNT(*) as total FROM producto_tienda 
                           WHERE id_tienda = :id_tienda 
                           AND estado = 'activo'
                           AND stock <= stock_minimo";
        $query = $this->acceso->prepare($sql_stock_bajo);
        $query->execute(array(':id_tienda' => $id_tienda));
        $result = $query->fetch(PDO::FETCH_OBJ);
        $stats->productos_stock_bajo = $result->total ?? 0;

        return $stats;
    }

    /**
     * Guardar/actualizar redes sociales como JSON
     * @param int $id_tienda ID de la tienda
     * @param array $redes Array con las redes sociales
     * @return bool True si se actualizó
     */
    function guardar_redes_sociales($id_tienda, $redes)
    {
        $redes_json = json_encode($redes);
        
        $sql = "UPDATE tienda SET redes_sociales = :redes_sociales 
                WHERE id = :id_tienda";
        $query = $this->acceso->prepare($sql);
        $query->execute(array(
            ':id_tienda' => $id_tienda,
            ':redes_sociales' => $redes_json
        ));
        
        return $query->rowCount() > 0;
    }

    /**
     * Guardar/actualizar políticas como JSON
     * @param int $id_tienda ID de la tienda
     * @param array $politicas Array con las políticas
     * @return bool True si se actualizó
     */
    function guardar_politicas($id_tienda, $politicas)
    {
        $politicas_json = json_encode($politicas);
        
        $sql = "UPDATE tienda SET politicas = :politicas 
                WHERE id = :id_tienda";
        $query = $this->acceso->prepare($sql);
        $query->execute(array(
            ':id_tienda' => $id_tienda,
            ':politicas' => $politicas_json
        ));
        
        return $query->rowCount() > 0;
    }
    
}