<?php
include_once 'Conexion.php';
class UsuarioDireccion {
    var $objetos;
    var $acceso;
    
    public function __construct() {
        $db = new Conexion();
        $this->acceso = $db->pdo;
    }

    // ✅ Actualizar dirección existente
public function actualizar_direccion($id_direccion, $id_municipio, $direccion, $alias = null, $instrucciones = null, $es_principal = 0) {
    // Primero obtener los datos actuales para comparar
    $this->recuperar_direccion($id_direccion);
    $direccion_actual = !empty($this->objetos) ? $this->objetos[0] : null;
    
    $sql = "UPDATE usuario_direccion SET 
                id_municipio = :id_municipio,
                direccion = :direccion,
                alias = :alias,
                instrucciones_entrega = :instrucciones,
                es_principal = :es_principal,
                fecha_actualizacion = NOW()
            WHERE id = :id";
    
    $query = $this->acceso->prepare($sql);
    $variables = array(
        ':id' => $id_direccion,
        ':id_municipio' => $id_municipio,
        ':direccion' => $direccion,
        ':alias' => $alias,
        ':instrucciones' => $instrucciones,
        ':es_principal' => $es_principal
    );
    
    return $query->execute($variables);
}
    
    // ✅ Crear dirección (actualizado con campos nuevos)
    public function crear_direccion($id_usuario, $id_municipio, $direccion, $alias = null, $instrucciones = null, $es_principal = 0) {
        $sql = "INSERT INTO usuario_direccion(
                    id_usuario, 
                    id_municipio, 
                    direccion,
                    alias,
                    instrucciones_entrega,
                    es_principal,
                    fecha_creacion
                ) VALUES(
                    :id_usuario,
                    :id_municipio,
                    :direccion,
                    :alias,
                    :instrucciones,
                    :es_principal,
                    NOW()
                )";
        
        $query = $this->acceso->prepare($sql);
        $variables = array(
            ':id_usuario' => $id_usuario,
            ':id_municipio' => $id_municipio,
            ':direccion' => $direccion,
            ':alias' => $alias,
            ':instrucciones' => $instrucciones,
            ':es_principal' => $es_principal
        );
        
        $query->execute($variables);
    }
    
    // ✅ Obtener direcciones del usuario
    public function llenar_direcciones($id_usuario) {
        $sql = "SELECT 
                    ud.id,
                    ud.direccion,
                    ud.alias,
                    ud.instrucciones_entrega as instrucciones,
                    ud.es_principal,
                    m.nombre as municipio,
                    p.nombre as provincia,
                    DATE(ud.fecha_creacion) as fecha_creacion
                FROM usuario_direccion ud
                JOIN municipio m ON ud.id_municipio = m.id
                JOIN provincia p ON m.id_provincia = p.id
                WHERE ud.id_usuario = :id_usuario 
                AND ud.estado = 'activa'
                ORDER BY ud.es_principal DESC, ud.fecha_creacion DESC";
        
        $query = $this->acceso->prepare($sql);
        $query->execute(array(':id_usuario' => $id_usuario));
        $this->objetos = $query->fetchAll();
        return $this->objetos;
    }
    
    // ✅ Obtener dirección específica
    public function recuperar_direccion($id_direccion) {
        $sql = "SELECT 
                    ud.*,
                    m.nombre as municipio,
                    p.nombre as provincia
                FROM usuario_direccion ud
                JOIN municipio m ON ud.id_municipio = m.id
                JOIN provincia p ON m.id_provincia = p.id
                WHERE ud.id = :id";
        
        $query = $this->acceso->prepare($sql);
        $query->execute(array(':id' => $id_direccion));
        $this->objetos = $query->fetchAll();
        return $this->objetos;
    }
    
    // ✅ Eliminar dirección (marcar como inactiva)
    public function eliminar_direccion($id_direccion) {
        $sql = "UPDATE usuario_direccion SET estado = 'inactiva' WHERE id = :id";
        $query = $this->acceso->prepare($sql);
        $query->execute(array(':id' => $id_direccion));
    }
    
    // ✅ Marcar dirección como principal
    public function marcar_direccion_principal($id_usuario, $id_direccion) {
        // 1. Quitar principal de todas las direcciones del usuario
        $sql1 = "UPDATE usuario_direccion 
                SET es_principal = 0 
                WHERE id_usuario = :id_usuario";
        
        $query1 = $this->acceso->prepare($sql1);
        $query1->execute(array(':id_usuario' => $id_usuario));
        
        // 2. Marcar la nueva dirección como principal
        $sql2 = "UPDATE usuario_direccion 
                SET es_principal = 1 
                WHERE id = :id AND id_usuario = :id_usuario";
        
        $query2 = $this->acceso->prepare($sql2);
        return $query2->execute(array(
            ':id' => $id_direccion,
            ':id_usuario' => $id_usuario
        ));
    }
    
    // ✅ Verificar si el usuario tiene direcciones principales
    public function verificar_principal_usuario($id_usuario) {
        $sql = "SELECT COUNT(*) as total FROM usuario_direccion 
                WHERE id_usuario = :id_usuario AND es_principal = 1 AND estado = 'activa'";
        
        $query = $this->acceso->prepare($sql);
        $query->execute(array(':id_usuario' => $id_usuario));
        $resultado = $query->fetch(PDO::FETCH_ASSOC);
        return $resultado['total'];
    }
}