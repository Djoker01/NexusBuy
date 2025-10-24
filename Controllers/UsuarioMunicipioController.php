<?php
include_once '../Models/UsuarioMunicipio.php';
include_once '../Models/Historial.php';
include_once '../Util/Config/config.php';
$usuario_municipio = new UsuarioMunicipio();
$historial = new Historial();
session_start();

if ($_POST['funcion']=='crear_direccion'){
    $id_usuario = $_SESSION['id'];
    $id_municipio=$_POST['id_municipio'];
    $direccion=$_POST['direccion'];
    $usuario_municipio->crear_direccion($id_usuario,$id_municipio, $direccion);
    $descripcion = 'Ha creado una nueva dirección: '.$direccion;
    $historial->crear_historial($descripcion,2,1,$id_usuario);
    echo 'success';
}

if ($_POST['funcion']=='llenar_direcciones'){
    $id_usuario = $_SESSION['id'];
    $usuario_municipio->llenar_direcciones($id_usuario);
    $json=array();
    foreach ($usuario_municipio->objetos as $objeto) {
        $json[]=array(
            'id'=>openssl_encrypt($objeto->id,CODE,KEY),
            'direccion'=>$objeto->direccion,
            'municipio'=>$objeto->municipio,
            'provincia'=>$objeto->provincia,
        );
    }
    $jsonstring = json_encode($json);
    echo $jsonstring;
}
if ($_POST['funcion']=='eliminar_direccion'){
    $id_direccion = openssl_decrypt($_POST['id'],CODE,KEY);
    if (is_numeric($id_direccion)) {
        $usuario_municipio->recuperar_direccion($id_direccion);
        $direccion_borrada = $usuario_municipio->objetos[0]->direccion.', Municipio: '.$usuario_municipio->objetos[0]->municipio.', Provincia: '.$usuario_municipio->objetos[0]->provincia;
        $usuario_municipio->eliminar_direccion($id_direccion);
        $descripcion = 'Ha eliminado la Dirección: '.$direccion_borrada;
        $historial->crear_historial($descripcion,3,1,$_SESSION['id']);
    echo 'success';
    } else {
        echo 'error';
    }
    
    
}