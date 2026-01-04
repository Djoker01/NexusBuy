<?php
include_once '../Models/Notificacion.php';
include_once '../Util/Config/config.php';
$notificacion = new Notificacion();
session_start();

if(isset($_POST['funcion'])){
    $funcion = $_POST['funcion'];
    
    switch($funcion){
        case 'read_notificaciones':
            if (!empty($_SESSION['id'])) {
                $id_usuario = $_SESSION['id'];
                $notificaciones = $notificacion->read($id_usuario);
                $json = array();
                foreach($notificaciones as $notif){
                    $json[] = array(
                        'id' => $notif->id,
                        'titulo' => $notif->titulo,
                        'mensaje' => $notif->mensaje,
                        'tipo' => $notif->tipo,
                        'url' => $notif->url,
                        'icono' => $notif->icono,
                        'leida' => $notif->leida,
                        'fecha_creacion' => $notif->fecha_creacion,
                        'fecha_leida' => $notif->fecha_leida
                    );
                }
                $jsonstring = json_encode($json);
                echo $jsonstring;
            } else {
                echo json_encode(array('error' => 'Usuario no autenticado'));
            }
            break;
            
        case 'read_all_notificaciones':
            if (!empty($_SESSION['id'])) {
                $id_usuario = $_SESSION['id'];
                $notificaciones = $notificacion->read_all($id_usuario);
                $json = array();
                foreach($notificaciones as $notif){
                    $json[] = array(
                        'id' => $notif->id,
                        'titulo' => $notif->titulo,
                        'mensaje' => $notif->mensaje,
                        'tipo' => $notif->tipo,
                        'url' => $notif->url,
                        'icono' => $notif->icono,
                        'leida' => $notif->leida,
                        'fecha_creacion' => $notif->fecha_creacion,
                        'fecha_leida' => $notif->fecha_leida
                    );
                }
                $jsonstring = json_encode($json);
                echo $jsonstring;
            } else {
                echo json_encode(array('error' => 'Usuario no autenticado'));
            }
            break;
            
        case 'get_counts':
            if (!empty($_SESSION['id'])) {
                $id_usuario = $_SESSION['id'];
                $counts = $notificacion->get_counts($id_usuario);
                echo json_encode($counts);
            } else {
                echo json_encode(array('error' => 'Usuario no autenticado'));
            }
            break;
            
        case 'mark_as_read':
            if (!empty($_SESSION['id'])) {
                $id = $_POST['id'];
                $notificacion->mark_as_read($id);
                echo json_encode(array('success' => true));
            } else {
                echo json_encode(array('error' => 'Usuario no autenticado'));
            }
            break;
            
        case 'mark_all_as_read':
            if (!empty($_SESSION['id'])) {
                $id_usuario = $_SESSION['id'];
                $notificacion->mark_all_as_read($id_usuario);
                echo json_encode(array('success' => true));
            } else {
                echo json_encode(array('error' => 'Usuario no autenticado'));
            }
            break;
            
        case 'delete':
            if (!empty($_SESSION['id'])) {
                $id = $_POST['id'];
                $notificacion->delete($id);
                echo json_encode(array('success' => true));
            } else {
                echo json_encode(array('error' => 'Usuario no autenticado'));
            }
            break;
            
        case 'delete_multiple':
            if (!empty($_SESSION['id'])) {
                $ids = json_decode($_POST['ids']);
                $notificacion->delete_multiple($ids);
                echo json_encode(array('success' => true));
            } else {
                echo json_encode(array('error' => 'Usuario no autenticado'));
            }
            break;
            
        default:
            echo json_encode(array('error' => 'Función no válida'));
            break;
    }
}
?>