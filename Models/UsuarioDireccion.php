<?php
include_once 'Conexion.php';
class UsuarioDireccion {
    private $conexion;
    
    public function __construct($conexion) {
        $this->conexion = $conexion;
    }
    
    // ✅ Obtener direcciones de usuario
    public function obtenerPorUsuario($idUsuario) {
        $sql = "SELECT ud.*, m.nombre as municipio_nombre, p.nombre as provincia_nombre
                FROM usuario_direccion ud
                JOIN municipio m ON ud.id_municipio = m.id
                JOIN provincia p ON m.id_provincia = p.id
                WHERE ud.id_usuario = :id_usuario AND ud.estado = 'activa'
                ORDER BY ud.es_principal DESC, ud.fecha_creacion DESC";
        
        return $this->conexion->obtenerTodos($sql, [':id_usuario' => $idUsuario]);
    }
    
    // ✅ Obtener dirección por ID
    public function obtenerPorId($id) {
        $sql = "SELECT ud.*, m.nombre as municipio_nombre, p.nombre as provincia_nombre
                FROM usuario_direccion ud
                JOIN municipio m ON ud.id_municipio = m.id
                JOIN provincia p ON m.id_provincia = p.id
                WHERE ud.id = :id";
        
        return $this->conexion->obtenerUno($sql, [':id' => $id]);
    }
    
    // ✅ Crear dirección
    public function crear($datos) {
        $sql = "INSERT INTO usuario_direccion (id_usuario, alias, direccion, id_municipio,
                                             codigo_postal, telefono_contacto, instrucciones_entrega)
                VALUES (:id_usuario, :alias, :direccion, :id_municipio,
                        :codigo_postal, :telefono_contacto, :instrucciones_entrega)";
        
        return $this->conexion->ejecutar($sql, $datos);
    }
    
    // ✅ Actualizar dirección
    public function actualizar($id, $datos) {
        $sql = "UPDATE usuario_direccion SET alias = :alias, direccion = :direccion,
                                           id_municipio = :id_municipio, codigo_postal = :codigo_postal,
                                           telefono_contacto = :telefono_contacto,
                                           instrucciones_entrega = :instrucciones_entrega,
                                           fecha_actualizacion = CURRENT_TIMESTAMP
                WHERE id = :id";
        
        $datos[':id'] = $id;
        return $this->conexion->ejecutar($sql, $datos);
    }
    
    // ✅ Establecer como dirección principal
    public function establecerPrincipal($idUsuario, $idDireccion) {
        // Primero, quitar principal de todas las direcciones
        $sql1 = "UPDATE usuario_direccion SET es_principal = FALSE 
                WHERE id_usuario = :id_usuario";
        $this->conexion->ejecutar($sql1, [':id_usuario' => $idUsuario]);
        
        // Luego, establecer la nueva dirección como principal
        $sql2 = "UPDATE usuario_direccion SET es_principal = TRUE,
                                           fecha_actualizacion = CURRENT_TIMESTAMP
                WHERE id = :id AND id_usuario = :id_usuario";
        
        return $this->conexion->ejecutar($sql2, [
            ':id' => $idDireccion,
            ':id_usuario' => $idUsuario
        ]);
    }
    
    // ✅ Eliminar dirección (cambiar estado)
    public function eliminar($id) {
        $sql = "UPDATE usuario_direccion SET estado = 'inactiva',
                                           fecha_actualizacion = CURRENT_TIMESTAMP
                WHERE id = :id";
        
        return $this->conexion->ejecutar($sql, [':id' => $id]);
    }
}