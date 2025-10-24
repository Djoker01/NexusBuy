<?php
include_once '../Models/Notificacion.php';
include_once '../Util/Config/config.php';
$notificacion = new Notificacion();
session_start();

if ($_POST['funcion']=='read_notificaciones'){
    if (!empty($_SESSION['id'])) {
        $id_usuario = $_POST['id_usuario'];
        $notificacion->read($id_usuario);
        var_dump($notificacion);
        //$jsonstring = json_encode($json);
        //echo $jsonstring;
    } else {
        echo 'error, el usuario no esta en session';
    }
    
}