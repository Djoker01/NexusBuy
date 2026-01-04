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

    // ========== FUNCIONES PRINCIPALES ==========

    // Guardar configuración completa
    public function guardarConfiguracion($id_usuario, $tipo, $datos) {
        $this->acceso->beginTransaction();
        
        try {
            $datos_array = json_decode($datos, true);
            
            switch ($tipo) {
                case 'notificaciones':
                    $this->guardarNotificaciones($id_usuario, $datos_array);
                    break;
                case 'privacidad':
                    $this->guardarPrivacidad($id_usuario, $datos_array);
                    break;
                case 'visualizacion':
                    $this->guardarVisualizacion($id_usuario, $datos_array);
                    break;
                default:
                    throw new Exception("Tipo de configuración no válido");
            }
            
            // Registrar en historial
            $this->registrarCambioHistorial($id_usuario, $tipo, $datos);
            
            $this->acceso->commit();
            return true;
            
        } catch (Exception $e) {
            $this->acceso->rollBack();
            throw $e;
        }
    }

    // Cargar configuración completa
    public function cargarConfiguraciones($id_usuario) {
        // Verificar si existe configuración
        $sql_check = "SELECT id FROM usuario_configuracion WHERE id_usuario = :id_usuario";
        $query = $this->acceso->prepare($sql_check);
        $query->execute([':id_usuario' => $id_usuario]);
        
        if ($query->rowCount() == 0) {
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
                'fecha_creacion' => $config['fecha_creacion']
            ]
        ];
    }

    // ========== FUNCIONES ESPECÍFICAS ==========

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
            ':email' => $datos['email'] ? 1 : 0,
            ':pedidos' => $datos['pedidos'] ? 1 : 0,
            ':promociones' => $datos['promociones'] ? 1 : 0,
            ':productos' => $datos['productos'] ? 1 : 0,
            ':resenas' => $datos['resenas'] ? 1 : 0,
            ':id_usuario' => $id_usuario
        ]);
    }

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
            ':perfil_publico' => $datos['perfil_publico'] ? 1 : 0,
            ':actividad_publica' => $datos['actividad_publica'] ? 1 : 0,
            ':aparecer_busquedas' => $datos['aparecer_busquedas'] ? 1 : 0,
            ':compartir_datos' => $datos['compartir_datos'] ? 1 : 0,
            ':id_usuario' => $id_usuario
        ]);
    }

    private function guardarVisualizacion($id_usuario, $datos) {
        $id_moneda = $this->obtenerIdMoneda($datos['moneda'] ?? 'CUP');
        
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
            ':idioma' => $datos['idioma'] ?? 'es',
            ':tema' => $datos['tema'] ?? 'claro',
            ':densidad' => $datos['densidad'] ?? 'normal',
            ':id_usuario' => $id_usuario
        ]);
    }

    private function crearConfiguracionPorDefecto($id_usuario) {
        $id_moneda = $this->obtenerIdMoneda('CUP');
        
        $sql = "INSERT INTO usuario_configuracion 
                (id_usuario, id_moneda, idioma, tema, densidad,
                 notificaciones_email, notificaciones_push, newsletter,
                 notificaciones_productos, notificaciones_resenas,
                 privacy_profile_public, privacy_activity_public,
                 privacy_searchable, privacy_data_sharing,
                 fecha_creacion, fecha_actualizacion)
                VALUES (:id_usuario, :id_moneda, 'es', 'claro', 'normal',
                 1, 1, 0, 0, 1,
                 1, 0, 1, 1,
                 CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)";
        
        $query = $this->acceso->prepare($sql);
        $query->execute([
            ':id_usuario' => $id_usuario,
            ':id_moneda' => $id_moneda
        ]);
    }

    // ========== FUNCIONES DE EXPORTACIÓN ==========

    public function generarDatosExportacion($id_usuario, $formatos) {
        $datos = [];
        
        // Información del perfil
        if (in_array('perfil', $formatos)) {
            $datos['perfil'] = $this->obtenerDatosPerfil($id_usuario);
        }
        
        // Configuración
        if (in_array('preferencias', $formatos)) {
            $datos['configuracion'] = $this->cargarConfiguraciones($id_usuario);
        }
        
        // Direcciones
        if (in_array('direcciones', $formatos)) {
            $datos['direcciones'] = $this->obtenerDirecciones($id_usuario);
        }
        
        // Pedidos
        if (in_array('pedidos', $formatos)) {
            $datos['pedidos'] = $this->obtenerPedidos($id_usuario);
        }
        
        // Reseñas
        if (in_array('resenas', $formatos)) {
            $datos['resenas'] = $this->obtenerResenas($id_usuario);
        }
        
        // Historial de exportaciones
        $datos['historial_exportaciones'] = $this->obtenerHistorialExportaciones($id_usuario);
        
        // Registrar exportación
        $this->registrarExportacion($id_usuario, $formatos, $datos);
        
        return $datos;
    }

    private function obtenerDatosPerfil($id_usuario) {
        $sql = "SELECT user as username, nombres, apellidos, email, 
                       telefono, dni, avatar, fecha_nacimiento, genero,
                       fecha_creacion, estado
                FROM usuario WHERE id = :id_usuario";
        $query = $this->acceso->prepare($sql);
        $query->execute([':id_usuario' => $id_usuario]);
        return $query->fetch(PDO::FETCH_ASSOC);
    }

    private function obtenerDirecciones($id_usuario) {
        $sql = "SELECT um.direccion, um.alias, um.instrucciones, um.es_principal,
                       m.nombre as municipio, p.nombre as provincia,
                       um.fecha_creacion, um.estado
                FROM usuario_municipio um
                JOIN municipio m ON um.id_municipio = m.id
                JOIN provincia p ON m.id_provincia = p.id
                WHERE um.id_usuario = :id_usuario
                ORDER BY um.es_principal DESC, um.fecha_creacion DESC";
        $query = $this->acceso->prepare($sql);
        $query->execute([':id_usuario' => $id_usuario]);
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    private function registrarExportacion($id_usuario, $formatos, $datos) {
        // Actualizar contador
        $sql_update = "UPDATE usuario_configuracion 
                      SET exportaciones_realizadas = exportaciones_realizadas + 1,
                          ultima_exportacion = CURRENT_TIMESTAMP
                      WHERE id_usuario = :id_usuario";
        $query = $this->acceso->prepare($sql_update);
        $query->execute([':id_usuario' => $id_usuario]);
        
        // Registrar en tabla de exportaciones
        $sql_insert = "INSERT INTO usuario_exportaciones 
                      (id_usuario, tipo_exportacion, formato, datos_exportados, ip_solicitud, user_agent)
                      VALUES (:id_usuario, :tipo, 'json', :datos, :ip, :user_agent)";
        
        $query = $this->acceso->prepare($sql_insert);
        $query->execute([
            ':id_usuario' => $id_usuario,
            ':tipo' => implode(',', $formatos),
            ':datos' => json_encode(array_keys($datos)),
            ':ip' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
            ':user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? ''
        ]);
    }

    // ========== FUNCIONES AUXILIARES ==========

    private function obtenerIdMoneda($codigo_moneda) {
        $sql = "SELECT id FROM moneda WHERE codigo = :codigo AND estado = 'A' LIMIT 1";
        $query = $this->acceso->prepare($sql);
        $query->execute([':codigo' => $codigo_moneda]);
        $result = $query->fetch();
        
        return $result ? $result->id : 1;
    }

    private function registrarCambioHistorial($id_usuario, $tipo, $datos) {
        $sql = "INSERT INTO configuracion_historial 
                (id_usuario, tipo_cambio, valor_nuevo, ip_cambio, user_agent)
                VALUES (:id_usuario, :tipo, :datos, :ip, :user_agent)";
        
        $query = $this->acceso->prepare($sql);
        $query->execute([
            ':id_usuario' => $id_usuario,
            ':tipo' => $tipo,
            ':datos' => $datos,
            ':ip' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
            ':user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? ''
        ]);
    }

    public function obtenerMonedas() {
        $sql = "SELECT id, codigo, nombre, simbolo, tasa_cambio 
                FROM moneda 
                WHERE estado = 'A' 
                ORDER BY codigo";
        $query = $this->acceso->prepare($sql);
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerTasaCambio($codigo_moneda) {
        $sql = "SELECT tasa_cambio, codigo, nombre, simbolo 
                FROM moneda 
                WHERE codigo = :codigo AND estado = 'A'";
        $query = $this->acceso->prepare($sql);
        $query->execute([':codigo' => $codigo_moneda]);
        return $query->fetch(PDO::FETCH_ASSOC);
    }

    public function desactivarCuenta($id_usuario) {
        $this->acceso->beginTransaction();
        
        try {
            // 1. Crear backup de configuración
            $this->crearBackupPreEliminacion($id_usuario);
            
            // 2. Desactivar usuario
            $sql_usuario = "UPDATE usuario SET estado = 'I' WHERE id = :id_usuario";
            $query = $this->acceso->prepare($sql_usuario);
            $query->execute([':id_usuario' => $id_usuario]);
            
            // 3. Anonimizar datos
            $sql_anonimizar = "UPDATE usuario 
                              SET user = CONCAT('user_deleted_', id),
                                  nombres = 'Usuario',
                                  apellidos = 'Eliminado',
                                  email = CONCAT('deleted_', id, '@nexusbuy.com'),
                                  telefono = '0000000000',
                                  dni = CONCAT('0000000', id),
                                  avatar = 'default.jpg'
                              WHERE id = :id_usuario";
            $query = $this->acceso->prepare($sql_anonimizar);
            $query->execute([':id_usuario' => $id_usuario]);
            
            // 4. Registrar en historial
            $sql_historial = "INSERT INTO configuracion_historial 
                             (id_usuario, tipo_cambio, campo_modificado, valor_nuevo)
                             VALUES (:id_usuario, 'eliminacion', 'cuenta', 'desactivada')";
            $query = $this->acceso->prepare($sql_historial);
            $query->execute([':id_usuario' => $id_usuario]);
            
            $this->acceso->commit();
            return true;
            
        } catch (Exception $e) {
            $this->acceso->rollBack();
            throw $e;
        }
    }

    private function crearBackupPreEliminacion($id_usuario) {
        $configuracion = $this->cargarConfiguraciones($id_usuario);
        $datos_json = json_encode($configuracion);
        
        $sql = "INSERT INTO configuracion_backup 
                (id_usuario, tipo_backup, datos_configuracion, hash_backup, tamano_backup)
                VALUES (:id_usuario, 'pre_eliminacion', :datos, SHA2(:datos, 256), LENGTH(:datos))";
        
        $query = $this->acceso->prepare($sql);
        $query->execute([
            ':id_usuario' => $id_usuario,
            ':datos' => $datos_json
        ]);
    }

    public function obtenerEstadisticasUsuario($id_usuario) {
        $sql = "SELECT 
                uc.exportaciones_realizadas,
                uc.ultima_exportacion,
                (SELECT COUNT(*) FROM usuario_exportaciones WHERE id_usuario = :id_usuario) as total_exportaciones,
                (SELECT COUNT(*) FROM configuracion_historial WHERE id_usuario = :id_usuario) as total_cambios,
                (SELECT fecha_cambio FROM configuracion_historial WHERE id_usuario = :id_usuario ORDER BY fecha_cambio DESC LIMIT 1) as ultimo_cambio
                FROM usuario_configuracion uc
                WHERE uc.id_usuario = :id_usuario";
        
        $query = $this->acceso->prepare($sql);
        $query->execute([':id_usuario' => $id_usuario]);
        return $query->fetch(PDO::FETCH_ASSOC);
    }
}
?>