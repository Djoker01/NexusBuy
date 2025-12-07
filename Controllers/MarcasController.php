<?php
include_once '../Models/Marcas.php';
$marcas = new Marcas();
session_start();

if ($_POST['funcion'] == 'obtener_marcas') {
    $marcas->obtener_marcas();
    
    $json = array();
    
    foreach ($marcas->objetos as $marca_item) {
        $json[] = array(
            'nombre' => $marca_item->nombre,
            'logo' => $marca_item->logo,
        );
    }
    $jsonstring = json_encode($json);
    echo $jsonstring;
}