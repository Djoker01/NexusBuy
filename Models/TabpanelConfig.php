<?php
// Models/TabpanelConfig.php
include_once 'Conexion.php';

class Configuracion {
    var $objetos;
    var $acceso;
    
    public function __construct() {
        $db = new Conexion();
        $this->acceso = $db->pdo;
    }

    // ========== FUNCIÓN GUARDAR CONFIGURACIÓN OPTIMIZADA ==========
    public function guardarConfiguracion($id_usuario, $tipo, $datos) {
        // Verificar que el usuario existe
        if (!$this->usuarioExiste($id_usuario)) {
            throw new Exception("El usuario no existe");
        }
        
        // Verificar si tiene configuración, si no, crear por defecto
        if (!$this->tieneConfiguracion($id_usuario)) {
            $this->crearConfiguracionPorDefecto($id_usuario);
        }
        
        $this->acceso->beginTransaction();
        
        try {
            $datos_array = json_decode($datos, true);
            $filas_afectadas = 0;
            
            switch ($tipo) {
                case 'notificaciones':
                    $filas_afectadas = $this->guardarNotificaciones($id_usuario, $datos_array);
                    break;
                case 'privacidad':
                    $filas_afectadas = $this->guardarPrivacidad($id_usuario, $datos_array);
                    break;
                case 'visualizacion':
                    $filas_afectadas = $this->guardarVisualizacion($id_usuario, $datos_array);
                    break;
                default:
                    throw new Exception("Tipo de configuración no válido");
            }
            
            // Registrar en historial (solo si hubo cambios reales)
            if ($filas_afectadas > 0) {
                // $this->registrarCambioHistorial($id_usuario, $tipo, $datos);
            }
            
            $this->acceso->commit();
            
            return [
                'success' => true,
                'filas_afectadas' => $filas_afectadas,
                'mensaje' => $filas_afectadas > 0 ? 'Configuración actualizada' : 'Sin cambios'
            ];
            
        } catch (Exception $e) {
            $this->acceso->rollBack();
            throw $e;
        }
    }

    // ========== FUNCIONES ESPECÍFICAS OPTIMIZADAS PARA TU TABLA ==========

    /**
     * Guardar configuración de notificaciones
     * Campos: notificaciones_email, notificaciones_push, newsletter, 
     *         notificaciones_productos, notificaciones_resenas
     */
    private function guardarNotificaciones($id_usuario, $datos) {
        $sql = "UPDATE usuario_configuracion SET
                notificaciones_email = :email,
                notificaciones_push = :pedidos,
                newsletter = :promociones,
                notificaciones_productos = :productos,
                notificaciones_resenas = :resenas,
                fecha_actualizacion = CURRENT_TIMESTAMP
                WHERE id_usuario = :id_usuario";
        
        $query = $this->acceso->prepare($sql);
        $query->execute([
            ':email' => $this->toBoolean($datos['email'] ?? true),
            ':pedidos' => $this->toBoolean($datos['pedidos'] ?? true),
            ':promociones' => $this->toBoolean($datos['promociones'] ?? false),
            ':productos' => $this->toBoolean($datos['productos'] ?? false),
            ':resenas' => $this->toBoolean($datos['resenas'] ?? true),
            ':id_usuario' => $id_usuario
        ]);
        
        return $query->rowCount();
    }

    /**
     * Guardar configuración de privacidad
     * Campos: privacy_profile_public, privacy_activity_public, 
     *         privacy_searchable, privacy_data_sharing
     */
    private function guardarPrivacidad($id_usuario, $datos) {
        $sql = "UPDATE usuario_configuracion SET
                privacy_profile_public = :perfil_publico,
                privacy_activity_public = :actividad_publica,
                privacy_searchable = :aparecer_busquedas,
                privacy_data_sharing = :compartir_datos,
                fecha_actualizacion = CURRENT_TIMESTAMP
                WHERE id_usuario = :id_usuario";
        
        $query = $this->acceso->prepare($sql);
        $query->execute([
            ':perfil_publico' => $this->toBoolean($datos['perfil_publico'] ?? true),
            ':actividad_publica' => $this->toBoolean($datos['actividad_publica'] ?? false),
            ':aparecer_busquedas' => $this->toBoolean($datos['aparecer_busquedas'] ?? true),
            ':compartir_datos' => $this->toBoolean($datos['compartir_datos'] ?? true),
            ':id_usuario' => $id_usuario
        ]);
        
        return $query->rowCount();
    }

    /**
     * Guardar configuración de visualización
     * Campos: id_moneda, idioma, tema, densidad
     */
    private function guardarVisualizacion($id_usuario, $datos) {
        // Validar valores permitidos según tu tabla
        $temas_permitidos = ['claro', 'oscuro', 'auto'];
        $densidades_permitidas = ['comoda', 'normal', 'compacta'];
        $idiomas_permitidos = ['es', 'en', 'pt']; // Ajusta según tus necesidades
        
        $tema = $datos['tema'] ?? 'claro';
        $densidad = $datos['densidad'] ?? 'normal';
        $idioma = $datos['idioma'] ?? 'es';
        $moneda = $datos['moneda'] ?? 'CUP';
        
        // Validar que los valores sean permitidos
        if (!in_array($tema, $temas_permitidos)) {
            $tema = 'claro';
        }
        
        if (!in_array($densidad, $densidades_permitidas)) {
            $densidad = 'normal';
        }
        
        if (!in_array($idioma, $idiomas_permitidos)) {
            $idioma = 'es';
        }
        
        $id_moneda = $this->obtenerIdMoneda($moneda);
        
        $sql = "UPDATE usuario_configuracion SET
                id_moneda = :id_moneda,
                idioma = :idioma,
                tema = :tema,
                densidad = :densidad,
                fecha_actualizacion = CURRENT_TIMESTAMP
                WHERE id_usuario = :id_usuario";
        
        $query = $this->acceso->prepare($sql);
        $query->execute([
            ':id_moneda' => $id_moneda,
            ':idioma' => $idioma,
            ':tema' => $tema,
            ':densidad' => $densidad,
            ':id_usuario' => $id_usuario
        ]);
        
        return $query->rowCount();
    }

    /**
     * Guardar configuración completa (todas las secciones a la vez)
     */
    public function guardarConfiguracionCompleta($id_usuario, $datos_completos) {
        if (!$this->usuarioExiste($id_usuario)) {
            throw new Exception("El usuario no existe");
        }
        
        if (!$this->tieneConfiguracion($id_usuario)) {
            $this->crearConfiguracionPorDefecto($id_usuario);
        }
        
        $this->acceso->beginTransaction();
        
        try {
            $datos = json_decode($datos_completos, true);
            
            $sql = "UPDATE usuario_configuracion SET
                    -- Notificaciones
                    notificaciones_email = :email,
                    notificaciones_push = :pedidos,
                    newsletter = :promociones,
                    notificaciones_productos = :productos,
                    notificaciones_resenas = :resenas,
                    
                    -- Privacidad
                    privacy_profile_public = :perfil_publico,
                    privacy_activity_public = :actividad_publica,
                    privacy_searchable = :aparecer_busquedas,
                    privacy_data_sharing = :compartir_datos,
                    
                    -- Visualización
                    id_moneda = :id_moneda,
                    idioma = :idioma,
                    tema = :tema,
                    densidad = :densidad,
                    
                    fecha_actualizacion = CURRENT_TIMESTAMP
                    WHERE id_usuario = :id_usuario";
            
            $query = $this->acceso->prepare($sql);
            
            // Obtener ID de moneda
            $id_moneda = $this->obtenerIdMoneda($datos['visualizacion']['moneda'] ?? 'CUP');
            
            $query->execute([
                // Notificaciones
                ':email' => $this->toBoolean($datos['notificaciones']['email'] ?? true),
                ':pedidos' => $this->toBoolean($datos['notificaciones']['pedidos'] ?? true),
                ':promociones' => $this->toBoolean($datos['notificaciones']['promociones'] ?? false),
                ':productos' => $this->toBoolean($datos['notificaciones']['productos'] ?? false),
                ':resenas' => $this->toBoolean($datos['notificaciones']['resenas'] ?? true),
                
                // Privacidad
                ':perfil_publico' => $this->toBoolean($datos['privacidad']['perfil_publico'] ?? true),
                ':actividad_publica' => $this->toBoolean($datos['privacidad']['actividad_publica'] ?? false),
                ':aparecer_busquedas' => $this->toBoolean($datos['privacidad']['aparecer_busquedas'] ?? true),
                ':compartir_datos' => $this->toBoolean($datos['privacidad']['compartir_datos'] ?? true),
                
                // Visualización
                ':id_moneda' => $id_moneda,
                ':idioma' => $datos['visualizacion']['idioma'] ?? 'es',
                ':tema' => $datos['visualizacion']['tema'] ?? 'claro',
                ':densidad' => $datos['visualizacion']['densidad'] ?? 'normal',
                
                ':id_usuario' => $id_usuario
            ]);
            
            $filas_afectadas = $query->rowCount();
            
            // Registrar en historial
            // $this->registrarCambioHistorial($id_usuario, 'completa', $datos_completos);
            
            $this->acceso->commit();
            
            return [
                'success' => true,
                'filas_afectadas' => $filas_afectadas
            ];
            
        } catch (Exception $e) {
            $this->acceso->rollBack();
            throw $e;
        }
    }

    // ========== FUNCIONES AUXILIARES ==========

    // Generar datos para exportación
    public function generarDatosExportacion($id_usuario, $formatos) {
        $datos = [];
        
        // Información del perfil
        if (in_array('perfil', $formatos)) {
            $sql_perfil = "SELECT username, nombres, apellidos, email, telefono, dni, avatar 
                          FROM usuario WHERE id = :id_usuario";
            $query = $this->acceso->prepare($sql_perfil);
            $query->execute([':id_usuario' => $id_usuario]);
            $datos['perfil'] = $query->fetch();
        }
        
        // Pedidos
        if (in_array('pedidos', $formatos)) {
            $sql_pedidos = "SELECT numero_orden, fecha_creacion, total, estado 
                           FROM orden WHERE id_usuario = :id_usuario 
                           ORDER BY fecha_creacion DESC";
            $query = $this->acceso->prepare($sql_pedidos);
            $query->execute([':id_usuario' => $id_usuario]);
            $datos['pedidos'] = $query->fetchAll();
        }
        
        // Reseñas
        if (in_array('resenas', $formatos)) {
            $sql_resenas = "SELECT r.calificacion, r.comentario, r.fecha_creacion, p.nombre as producto
                           FROM reseña r
                           JOIN producto_tienda pt ON r.id_producto_tienda = pt.id
                           JOIN producto p ON pt.id_producto = p.id
                           WHERE r.id_usuario = :id_usuario
                           ORDER BY r.fecha_creacion DESC";
            $query = $this->acceso->prepare($sql_resenas);
            $query->execute([':id_usuario' => $id_usuario]);
            $datos['resenas'] = $query->fetchAll();
        }
        
        // Direcciones
        if (in_array('direcciones', $formatos)) {
            $sql_direcciones = "SELECT ud.direccion, m.nombre as municipio, p.nombre as provincia
                               FROM usuario_direccion ud
                               JOIN municipio m ON ud.id_municipio = m.id
                               JOIN provincia p ON m.id_provincia = p.id
                               WHERE ud.id_usuario = :id_usuario AND ud.estado = 'activa'";
            $query = $this->acceso->prepare($sql_direcciones);
            $query->execute([':id_usuario' => $id_usuario]);
            $datos['direcciones'] = $query->fetchAll();
        }
        
        // Preferencias
        if (in_array('preferencias', $formatos)) {
            $datos['preferencias'] = $this->cargarConfiguraciones($id_usuario);
        }
        
        return $datos;
    }


    /**
     * Crear configuración por defecto para un nuevo usuario
     */
    private function crearConfiguracionPorDefecto($id_usuario) {
        $id_moneda = $this->obtenerIdMoneda('CUP');
        
        $sql = "INSERT INTO usuario_configuracion (
                    id_usuario, id_moneda, idioma, tema, densidad,
                    notificaciones_email, notificaciones_push, newsletter,
                    notificaciones_productos, notificaciones_resenas,
                    privacy_profile_public, privacy_activity_public,
                    privacy_searchable, privacy_data_sharing,
                    exportaciones_realizadas,
                    fecha_creacion, fecha_actualizacion
                ) VALUES (
                    :id_usuario, :id_moneda, 'es', 'claro', 'normal',
                    1, 1, 0, 0, 1,
                    1, 0, 1, 1,
                    0,
                    CURRENT_TIMESTAMP, CURRENT_TIMESTAMP
                )";
        
        $query = $this->acceso->prepare($sql);
        $query->execute([
            ':id_usuario' => $id_usuario,
            ':id_moneda' => $id_moneda
        ]);
        
        return $query->rowCount() > 0;
    }

    /**
     * Verificar si el usuario existe
     */
    private function usuarioExiste($id_usuario) {
        $sql = "SELECT id FROM usuario WHERE id = :id_usuario AND estado = 'activo'";
        $query = $this->acceso->prepare($sql);
        $query->execute([':id_usuario' => $id_usuario]);
        return $query->rowCount() > 0;
    }

    /**
     * Verificar si el usuario tiene configuración
     */
    private function tieneConfiguracion($id_usuario) {
        $sql = "SELECT id FROM usuario_configuracion WHERE id_usuario = :id_usuario";
        $query = $this->acceso->prepare($sql);
        $query->execute([':id_usuario' => $id_usuario]);
        return $query->rowCount() > 0;
    }

    /**
     * Obtener ID de moneda por código
     */
    private function obtenerIdMoneda($codigo_moneda) {
        $sql = "SELECT id FROM moneda WHERE codigo = :codigo AND estado = 'activo' LIMIT 1";
        $query = $this->acceso->prepare($sql);
        $query->execute([':codigo' => $codigo_moneda]);
        $result = $query->fetch(PDO::FETCH_OBJ);
        
        return $result ? $result->id : 1; // 1 como fallback (CUP)
    }

    /**
     * Registrar cambio en historial
     */
    // private function registrarCambioHistorial($id_usuario, $tipo, $datos) {
    //     $sql = "INSERT INTO configuracion_historial 
    //             (id_usuario, tipo_cambio, valor_nuevo, ip_cambio, user_agent)
    //             VALUES (:id_usuario, :tipo, :datos, :ip, :user_agent)";
        
    //     $query = $this->acceso->prepare($sql);
    //     $query->execute([
    //         ':id_usuario' => $id_usuario,
    //         ':tipo' => $tipo,
    //         ':datos' => $datos,
    //         ':ip' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
    //         ':user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? ''
    //     ]);
    // }

    /**
     * Convertir valor a booleano (0/1) para la BD
     */
    private function toBoolean($value) {
        return $value ? 1 : 0;
    }

    /**
     * Obtener todas las monedas activas
     */
    public function obtenerMonedas() {
        $sql = "SELECT id, codigo, nombre, simbolo, tasa_cambio 
                FROM moneda 
                WHERE estado = 'activo' 
                ORDER BY codigo";
        $query = $this->acceso->prepare($sql);
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Cargar configuración del usuario
     */
    public function cargarConfiguraciones($id_usuario) {
        // Verificar si existe configuración
        if (!$this->tieneConfiguracion($id_usuario)) {
            $this->crearConfiguracionPorDefecto($id_usuario);
        }
        
        // Obtener configuración
        $sql = "SELECT uc.*, m.codigo as moneda_codigo, m.nombre as moneda_nombre, m.simbolo
                FROM usuario_configuracion uc
                LEFT JOIN moneda m ON uc.id_moneda = m.id
                WHERE uc.id_usuario = :id_usuario";
        $query = $this->acceso->prepare($sql);
        $query->execute([':id_usuario' => $id_usuario]);
        $config = $query->fetch(PDO::FETCH_ASSOC);
        
        return [
            'notificaciones' => [
                'email' => (bool)$config['notificaciones_email'],
                'pedidos' => (bool)$config['notificaciones_push'],
                'promociones' => (bool)$config['newsletter'],
                'productos' => (bool)$config['notificaciones_productos'],
                'resenas' => (bool)$config['notificaciones_resenas']
            ],
            'privacidad' => [
                'perfil_publico' => (bool)$config['privacy_profile_public'],
                'actividad_publica' => (bool)$config['privacy_activity_public'],
                'aparecer_busquedas' => (bool)$config['privacy_searchable'],
                'compartir_datos' => (bool)$config['privacy_data_sharing']
            ],
            'visualizacion' => [
                'tema' => $config['tema'] ?? 'claro',
                'densidad' => $config['densidad'] ?? 'normal',
                'idioma' => $config['idioma'] ?? 'es',
                'moneda' => $config['moneda_codigo'] ?? 'CUP'
            ],
            'estadisticas' => [
                'exportaciones_realizadas' => (int)$config['exportaciones_realizadas'],
                'ultima_exportacion' => $config['ultima_exportacion'],
                'fecha_creacion' => $config['fecha_creacion'],
                'fecha_actualizacion' => $config['fecha_actualizacion']
            ]
        ];
    }

    public function desactivarCuenta($id_usuario) {
        $this->acceso->beginTransaction();
        
        try {
            // Marcar usuario como inactivo
            $sql_usuario = "DELETE FROM usuario WHERE id = :id_usuario";
            $query = $this->acceso->prepare($sql_usuario);
            $query->execute([':id_usuario' => $id_usuario]);
            
            // Aquí podrías agregar más lógica para anonimizar datos, etc.
            
            $this->acceso->commit();
            return true;
            
        } catch (Exception $e) {
            $this->acceso->rollBack();
            throw $e;
        }
    }
}
?>