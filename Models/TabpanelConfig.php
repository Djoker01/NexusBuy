<?php
include_once 'Conexion.php';
class Configuracion {
    var $objetos;
    var $acceso;
    
    public function __construct() {
        $db = new Conexion();
        $this->acceso = $db->pdo;
    }

    // Guardar configuración del usuario
    public function guardarConfiguracion($id_usuario, $tipo, $datos) {
        // Verificar si ya existe configuración para este tipo
        $sql_verificar = "SELECT id FROM configuracion_usuario 
                         WHERE id_usuario = :id_usuario AND tipo = :tipo";
        $query = $this->acceso->prepare($sql_verificar);
        $query->execute([':id_usuario' => $id_usuario, ':tipo' => $tipo]);
        $existe = $query->fetch();
        
        if ($existe) {
            // Actualizar configuración existente
            $sql = "UPDATE configuracion_usuario 
                   SET datos = :datos, fecha_actualizacion = CURRENT_TIMESTAMP 
                   WHERE id_usuario = :id_usuario AND tipo = :tipo";
        } else {
            // Insertar nueva configuración
            $sql = "INSERT INTO configuracion_usuario (id_usuario, tipo, datos) 
                   VALUES (:id_usuario, :tipo, :datos)";
        }
        
        $query = $this->acceso->prepare($sql);
        return $query->execute([
            ':id_usuario' => $id_usuario,
            ':tipo' => $tipo,
            ':datos' => $datos
        ]);
    }

    // Cargar todas las configuraciones del usuario
    public function cargarConfiguraciones($id_usuario) {
        $sql = "SELECT tipo, datos FROM configuracion_usuario 
               WHERE id_usuario = :id_usuario";
        $query = $this->acceso->prepare($sql);
        $query->execute([':id_usuario' => $id_usuario]);
        $configuraciones = $query->fetchAll();
        
        $resultado = [];
        foreach ($configuraciones as $config) {
            $resultado[$config->tipo] = json_decode($config->datos, true);
        }
        
        return $resultado;
    }

    // Generar datos para exportación
    public function generarDatosExportacion($id_usuario, $formatos) {
        $datos = [];
        
        // Información del perfil
        if (in_array('perfil', $formatos)) {
            $sql_perfil = "SELECT user, nombres, apellidos, email, telefono, dni, avatar 
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
            $sql_resenas = "SELECT r.calificacion, r.descripcion, r.fecha_creacion, p.nombre as producto
                           FROM reseña r
                           JOIN producto_tienda pt ON r.id_producto_tienda = pt.id
                           JOIN producto p ON pt.id_producto = p.id
                           WHERE r.id_usuario = :id_usuario AND r.estado = 'A'
                           ORDER BY r.fecha_creacion DESC";
            $query = $this->acceso->prepare($sql_resenas);
            $query->execute([':id_usuario' => $id_usuario]);
            $datos['resenas'] = $query->fetchAll();
        }
        
        // Direcciones
        if (in_array('direcciones', $formatos)) {
            $sql_direcciones = "SELECT um.direccion, m.nombre as municipio, p.nombre as provincia
                               FROM usuario_municipio um
                               JOIN municipio m ON um.id_municipio = m.id
                               JOIN provincia p ON m.id_provincia = p.id
                               WHERE um.id_usuario = :id_usuario AND um.estado = 'A'";
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

    // Desactivar cuenta (soft delete)
    public function desactivarCuenta($id_usuario) {
        $this->acceso->beginTransaction();
        
        try {
            // Marcar usuario como inactivo
            $sql_usuario = "UPDATE usuario SET estado = 'I' WHERE id = :id_usuario";
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