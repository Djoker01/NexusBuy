<?php
include_once 'Conexion.php';

class UsuarioMunicipio {
    var $objetos;
    var $acceso;
    
    public function __construct() {
        $db = new Conexion();
        $this->acceso = $db->pdo;
    }
    
    // Mantener nombre original pero usar tabla correcta
    function crear_direccion($id_usuario, $id_municipio, $direccion) {
        // Crear alias por defecto
        $alias = 'Dirección ' . date('Y-m-d H:i:s');
        
        $sql = "INSERT INTO usuario_direccion 
                (id_usuario, alias, direccion, id_municipio, estado) 
                VALUES (:id_usuario, :alias, :direccion, :id_municipio, 'activa')";
        
        $query = $this->acceso->prepare($sql);
        $query->execute(array(
            ':id_usuario' => $id_usuario,
            ':alias' => $alias,
            ':direccion' => $direccion,
            ':id_municipio' => $id_municipio
        ));
    }
    
    // Mantener compatibilidad con llenar_direcciones
    function llenar_direcciones($id_usuario) {
        $sql = "SELECT ud.id as id, 
                       ud.direccion as direccion, 
                       m.nombre as municipio, 
                       p.nombre as provincia,
                       ud.alias,
                       ud.codigo_postal,
                       ud.telefono_contacto,
                       ud.instrucciones_entrega,
                       ud.es_principal,
                       ud.estado
                FROM usuario_direccion ud
                JOIN municipio m ON m.id = ud.id_municipio
                JOIN provincia p ON p.id = m.id_provincia
                WHERE ud.id_usuario = :id 
                AND ud.estado = 'activa'
                ORDER BY ud.es_principal DESC, ud.id DESC";
        
        $query = $this->acceso->prepare($sql);
        $query->execute(array(':id' => $id_usuario));
        $this->objetos = $query->fetchAll();
        return $this->objetos;
    }
    
    // Actualizar para usar estado 'inactiva' en lugar de 'I'
    function eliminar_direccion($id_direccion) {
        $sql = "UPDATE usuario_direccion 
                SET estado = 'inactiva' 
                WHERE id = :id_direccion";
        
        $query = $this->acceso->prepare($sql);
        $query->execute(array(':id_direccion' => $id_direccion));
    }
    
    // Actualizar consulta
    function recuperar_direccion($id_direccion) {
        $sql = "SELECT ud.id as id, 
                       ud.direccion as direccion, 
                       m.nombre as municipio, 
                       p.nombre as provincia,
                       ud.alias,
                       ud.codigo_postal,
                       ud.telefono_contacto,
                       ud.instrucciones_entrega,
                       ud.es_principal,
                       ud.estado
                FROM usuario_direccion ud
                JOIN municipio m ON m.id = ud.id_municipio
                JOIN provincia p ON p.id = m.id_provincia
                WHERE ud.id = :id 
                AND ud.estado = 'activa'";
        
        $query = $this->acceso->prepare($sql);
        $query->execute(array(':id' => $id_direccion));
        $this->objetos = $query->fetchAll();
        return $this->objetos;
    }
}