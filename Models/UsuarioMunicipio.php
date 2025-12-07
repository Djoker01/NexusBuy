<?php
include_once 'Conexion.php';
class UsuarioMunicipio{
    var $objetos;
    var $acceso;
    public function __construct(){
        $db = new Conexion();
        $this->acceso = $db->pdo;
    }
    function crear_direccion($id_usuario,$id_municipio, $direccion){
        $sql = "INSERT INTO usuario_municipio(direccion,id_municipio,id_usuario) VALUES(:direccion,:id_municipio,:id_usuario)";
        $query = $this->acceso->prepare($sql);
        $query->execute(array(':direccion'=>$direccion,':id_municipio'=>$id_municipio,'id_usuario'=>$id_usuario));
        
    }
    function llenar_direcciones($id_usuario){
        $sql="SELECT um.id as id,direccion,m.nombre as municipio, p.nombre as provincia FROM usuario_municipio um
        JOIN municipio m ON m.id=um.id_municipio
        JOIN provincia p ON p.id=m.id_provincia
        WHERE id_usuario=:id and estado='A'";
        $query = $this->acceso->prepare($sql);
        $query->execute(array(':id'=>$id_usuario));
        $this->objetos = $query->fetchAll();
        return $this->objetos;
    }
    function eliminar_direccion($id_direccion){
        $sql = "UPDATE usuario_municipio SET estado='I' WHERE id=:id_direccion";
        $query = $this->acceso->prepare($sql);
        $query->execute(array(':id_direccion'=>$id_direccion));
        
    }
    function recuperar_direccion($id_direccion){
        $sql="SELECT um.id as id,direccion,m.nombre as municipio, p.nombre as provincia FROM usuario_municipio um
        JOIN municipio m ON m.id=um.id_municipio
        JOIN provincia p ON p.id=m.id_provincia
        WHERE um.id=:id and estado='A'";
        $query = $this->acceso->prepare($sql);
        $query->execute(array(':id'=>$id_direccion));
        $this->objetos = $query->fetchAll();
        return $this->objetos;
    }
}
 