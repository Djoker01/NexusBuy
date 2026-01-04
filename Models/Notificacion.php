<?php
include_once 'Conexion.php';
class Notificacion{
    var $objetos;
    var $acceso;
    public function __construct(){
        $db = new Conexion();
        $this->acceso = $db->pdo;
    }
    
    function read($id_usuario){
        $sql="SELECT * FROM notificacion
        WHERE id_usuario=:id_usuario
        AND leida = 0 ORDER BY fecha_creacion DESC";
        $query = $this->acceso->prepare($sql);
        $query->execute(array(':id_usuario'=>$id_usuario));
        $this->objetos = $query->fetchAll();
        return $this->objetos;
    }
    
    function read_all($id_usuario){
        $sql="SELECT * FROM notificacion
        WHERE id_usuario=:id_usuario
        ORDER BY fecha_creacion DESC";
        $query = $this->acceso->prepare($sql);
        $query->execute(array(':id_usuario'=>$id_usuario));
        $this->objetos = $query->fetchAll();
        return $this->objetos;
    }
    
    function read_by_type($id_usuario, $tipo){
        $sql="SELECT * FROM notificacion
        WHERE id_usuario=:id_usuario
        AND tipo = :tipo
        ORDER BY fecha_creacion DESC";
        $query = $this->acceso->prepare($sql);
        $query->execute(array(':id_usuario'=>$id_usuario, ':tipo'=>$tipo));
        $this->objetos = $query->fetchAll();
        return $this->objetos;
    }
    
    function mark_as_read($id){
        $sql="UPDATE notificacion SET leida = 1, fecha_leida = NOW() WHERE id = :id";
        $query = $this->acceso->prepare($sql);
        $query->execute(array(':id'=>$id));
    }
    
    function mark_all_as_read($id_usuario){
        $sql="UPDATE notificacion SET leida = 1, fecha_leida = NOW() 
              WHERE id_usuario = :id_usuario AND leida = 0";
        $query = $this->acceso->prepare($sql);
        $query->execute(array(':id_usuario'=>$id_usuario));
    }
    
    function delete($id){
        $sql="DELETE FROM notificacion WHERE id = :id";
        $query = $this->acceso->prepare($sql);
        $query->execute(array(':id'=>$id));
    }
    
    function delete_multiple($ids){
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $sql="DELETE FROM notificacion WHERE id IN ($placeholders)";
        $query = $this->acceso->prepare($sql);
        $query->execute($ids);
    }
    
    function get_counts($id_usuario){
        $sql="SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN leida = 0 THEN 1 ELSE 0 END) as no_leidas,
                SUM(CASE WHEN tipo = 'pedido' THEN 1 ELSE 0 END) as pedidos,
                SUM(CASE WHEN tipo = 'promocion' THEN 1 ELSE 0 END) as promociones,
                SUM(CASE WHEN tipo = 'sistema' THEN 1 ELSE 0 END) as sistema,
                SUM(CASE WHEN tipo = 'seguridad' THEN 1 ELSE 0 END) as seguridad,
                SUM(CASE WHEN tipo = 'soporte' THEN 1 ELSE 0 END) as soporte
              FROM notificacion 
              WHERE id_usuario = :id_usuario";
        $query = $this->acceso->prepare($sql);
        $query->execute(array(':id_usuario'=>$id_usuario));
        return $query->fetch(PDO::FETCH_ASSOC);
    }
    
    function create($id_usuario, $titulo, $mensaje, $tipo = 'sistema', $url = null, $icono = null){
        $sql="INSERT INTO notificacion (id_usuario, titulo, mensaje, tipo, url, icono) 
              VALUES (:id_usuario, :titulo, :mensaje, :tipo, :url, :icono)";
        $query = $this->acceso->prepare($sql);
        $query->execute(array(
            ':id_usuario'=>$id_usuario,
            ':titulo'=>$titulo,
            ':mensaje'=>$mensaje,
            ':tipo'=>$tipo,
            ':url'=>$url,
            ':icono'=>$icono
        ));
    }
}
?>