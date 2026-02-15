<?php
include_once 'Conexion.php';

class Banner {
    private $acceso;
    public $objetos;
    public $error;

    public function __construct() {
        $db = new Conexion();
        $this->acceso = $db->pdo;
    }

    // ==================== FRONTEND ====================

    /**
     * Obtiene banners activos por posición
     */
    public function obtenerBannersPorPosicion($posicion, $limite = 10) {
        try {
            $sql = "SELECT b.*, u.nombres as usuario_creador
                    FROM banners b
                    LEFT JOIN usuario u ON b.id_usuario = u.id
                    WHERE b.posicion = :posicion
                      AND b.estado = 1
                      AND b.fecha_inicio <= CURDATE()
                      AND b.fecha_fin >= CURDATE()
                    ORDER BY b.orden ASC, b.id DESC
                    LIMIT :limite";

            $query = $this->acceso->prepare($sql);
            $query->bindParam(':posicion', $posicion);
            $query->bindParam(':limite', $limite, PDO::PARAM_INT);
            $query->execute();

            $this->objetos = $query->fetchAll(PDO::FETCH_OBJ);
            return $this->objetos;
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }

    /**
     * Obtiene todos los banners activos agrupados por posición
     */
    public function obtenerTodosBannersActivos() {
        try {
            $sql = "SELECT b.*, u.nombres as usuario_creador
                    FROM banners b
                    LEFT JOIN usuario u ON b.id_usuario = u.id
                    WHERE b.estado = 1
                      AND b.fecha_inicio <= CURDATE()
                      AND b.fecha_fin >= CURDATE()
                    ORDER BY b.posicion, b.orden ASC";

            $query = $this->acceso->prepare($sql);
            $query->execute();

            $resultados = $query->fetchAll(PDO::FETCH_OBJ);
            
            // Agrupar por posición
            $agrupados = [];
            foreach ($resultados as $banner) {
                $agrupados[$banner->posicion][] = $banner;
            }
            
            return $agrupados;
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }

    // ==================== BACKEND/ADMIN ====================

    /**
     * Obtiene banners con paginación y filtros (para admin)
     */
    public function listarBanners($pagina = 1, $por_pagina = 20, $filtros = []) {
        try {
            $offset = ($pagina - 1) * $por_pagina;
            
            $sql = "SELECT b.*, u.nombres as usuario_nombre,
                           u.apellidos as usuario_apellidos
                    FROM banners b
                    LEFT JOIN usuario u ON b.id_usuario = u.id
                    WHERE 1=1";
            
            $params = [];

            // Aplicar filtros
            if (!empty($filtros['posicion'])) {
                $sql .= " AND b.posicion = :posicion";
                $params[':posicion'] = $filtros['posicion'];
            }

            if (isset($filtros['estado']) && $filtros['estado'] !== '') {
                $sql .= " AND b.estado = :estado";
                $params[':estado'] = $filtros['estado'];
            }

            if (!empty($filtros['busqueda'])) {
                $sql .= " AND (b.titulo LIKE :busqueda OR b.descripcion LIKE :busqueda)";
                $params[':busqueda'] = '%' . $filtros['busqueda'] . '%';
            }

            // Ordenamiento
            $sql .= " ORDER BY b.fecha_creacion DESC, b.orden ASC
                      LIMIT :offset, :por_pagina";

            // Consulta para contar total
            $sql_count = str_replace(
                "SELECT b.*, u.nombres as usuario_nombre, u.apellidos as usuario_apellidos",
                "SELECT COUNT(*) as total",
                $sql
            );
            $sql_count = preg_replace('/LIMIT.*/', '', $sql_count);

            // Ejecutar consulta de conteo
            $query_count = $this->acceso->prepare($sql_count);
            foreach ($params as $key => $value) {
                if ($key !== ':offset' && $key !== ':por_pagina') {
                    $query_count->bindValue($key, $value);
                }
            }
            $query_count->execute();
            $total = $query_count->fetch()->total;

            // Ejecutar consulta principal
            $query = $this->acceso->prepare($sql);
            foreach ($params as $key => $value) {
                $query->bindValue($key, $value);
            }
            $query->bindValue(':offset', $offset, PDO::PARAM_INT);
            $query->bindValue(':por_pagina', $por_pagina, PDO::PARAM_INT);
            $query->execute();

            $this->objetos = $query->fetchAll(PDO::FETCH_OBJ);
            
            return [
                'banners' => $this->objetos,
                'total' => $total,
                'paginas' => ceil($total / $por_pagina),
                'pagina_actual' => $pagina
            ];

        } catch (PDOException $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }

    /**
     * Obtiene un banner por ID
     */
    public function obtenerBannerPorId($id) {
        try {
            $sql = "SELECT b.*, u.nombres as usuario_nombre
                    FROM banners b
                    LEFT JOIN usuario u ON b.id_usuario = u.id
                    WHERE b.id = :id";

            $query = $this->acceso->prepare($sql);
            $query->bindParam(':id', $id);
            $query->execute();

            return $query->fetch(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }

    /**
     * Crea un nuevo banner
     */
    public function crearBanner($datos) {
        try {
            $sql = "INSERT INTO banners (
                        titulo, descripcion, imagen, url, posicion,
                        orden, fecha_inicio, fecha_fin, estado,
                        texto_boton, icono_boton, id_usuario
                    ) VALUES (
                        :titulo, :descripcion, :imagen, :url, :posicion,
                        :orden, :fecha_inicio, :fecha_fin, :estado,
                        :texto_boton, :icono_boton, :id_usuario
                    )";

            $query = $this->acceso->prepare($sql);
            
            $query->bindParam(':titulo', $datos['titulo']);
            $query->bindParam(':descripcion', $datos['descripcion']);
            $query->bindParam(':imagen', $datos['imagen']);
            $query->bindParam(':url', $datos['url']);
            $query->bindParam(':posicion', $datos['posicion']);
            $query->bindParam(':orden', $datos['orden']);
            $query->bindParam(':fecha_inicio', $datos['fecha_inicio']);
            $query->bindParam(':fecha_fin', $datos['fecha_fin']);
            $query->bindParam(':estado', $datos['estado']);
            $query->bindParam(':texto_boton', $datos['texto_boton']);
            $query->bindParam(':icono_boton', $datos['icono_boton']);
            $query->bindParam(':id_usuario', $datos['id_usuario']);

            $query->execute();
            return $this->acceso->lastInsertId();

        } catch (PDOException $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }

    /**
     * Actualiza un banner existente
     */
    public function actualizarBanner($id, $datos) {
        try {
            $sql = "UPDATE banners SET
                        titulo = :titulo,
                        descripcion = :descripcion,
                        url = :url,
                        posicion = :posicion,
                        orden = :orden,
                        fecha_inicio = :fecha_inicio,
                        fecha_fin = :fecha_fin,
                        estado = :estado,
                        texto_boton = :texto_boton,
                        icono_boton = :icono_boton";

            // Si hay nueva imagen, actualizarla
            if (!empty($datos['imagen'])) {
                $sql .= ", imagen = :imagen";
            }

            $sql .= " WHERE id = :id";

            $query = $this->acceso->prepare($sql);
            
            $query->bindParam(':titulo', $datos['titulo']);
            $query->bindParam(':descripcion', $datos['descripcion']);
            $query->bindParam(':url', $datos['url']);
            $query->bindParam(':posicion', $datos['posicion']);
            $query->bindParam(':orden', $datos['orden']);
            $query->bindParam(':fecha_inicio', $datos['fecha_inicio']);
            $query->bindParam(':fecha_fin', $datos['fecha_fin']);
            $query->bindParam(':estado', $datos['estado']);
            $query->bindParam(':texto_boton', $datos['texto_boton']);
            $query->bindParam(':icono_boton', $datos['icono_boton']);
            $query->bindParam(':id', $id);

            if (!empty($datos['imagen'])) {
                $query->bindParam(':imagen', $datos['imagen']);
            }

            return $query->execute();

        } catch (PDOException $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }

    /**
     * Elimina un banner
     */
    public function eliminarBanner($id) {
        try {
            $sql = "DELETE FROM banners WHERE id = :id";
            $query = $this->acceso->prepare($sql);
            $query->bindParam(':id', $id);
            return $query->execute();
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }

    /**
     * Cambia el estado de un banner (activar/desactivar)
     */
    public function cambiarEstado($id, $estado) {
        try {
            $sql = "UPDATE banners SET estado = :estado WHERE id = :id";
            $query = $this->acceso->prepare($sql);
            $query->bindParam(':estado', $estado);
            $query->bindParam(':id', $id);
            return $query->execute();
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }

    /**
     * Reordena banners (para drag & drop)
     */
    public function reordenarBanners($ordenes) {
        try {
            $this->acceso->beginTransaction();
            
            foreach ($ordenes as $item) {
                $sql = "UPDATE banners SET orden = :orden WHERE id = :id";
                $query = $this->acceso->prepare($sql);
                $query->execute([
                    ':orden' => $item['orden'],
                    ':id' => $item['id']
                ]);
            }
            
            $this->acceso->commit();
            return true;

        } catch (PDOException $e) {
            $this->acceso->rollBack();
            $this->error = $e->getMessage();
            return false;
        }
    }

    /**
     * Obtiene estadísticas de banners
     */
    public function obtenerEstadisticas() {
        try {
            $stats = [];
            
            // Total de banners
            $sql = "SELECT COUNT(*) as total FROM banners";
            $query = $this->acceso->query($sql);
            $stats['total'] = $query->fetch()->total;

            // Activos vs Inactivos
            $sql = "SELECT estado, COUNT(*) as cantidad 
                    FROM banners GROUP BY estado";
            $query = $this->acceso->query($sql);
            while ($row = $query->fetch()) {
                $stats[$row->estado == 1 ? 'activos' : 'inactivos'] = $row->cantidad;
            }

            // Por posición
            $sql = "SELECT posicion, COUNT(*) as cantidad 
                    FROM banners GROUP BY posicion";
            $query = $this->acceso->query($sql);
            $stats['por_posicion'] = [];
            while ($row = $query->fetch()) {
                $stats['por_posicion'][$row->posicion] = $row->cantidad;
            }

            // Próximos a vencer (7 días)
            $sql = "SELECT COUNT(*) as proximos 
                    FROM banners 
                    WHERE fecha_fin BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
                    AND estado = 1";
            $query = $this->acceso->query($sql);
            $stats['proximos_vencer'] = $query->fetch()->proximos;

            return $stats;

        } catch (PDOException $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }
}
?>