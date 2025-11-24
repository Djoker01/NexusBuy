<?php
include_once '../Models/Provincia.php';
$provincia = new Provincia();
session_start();

if ($_POST['funcion']=='llenar_provincia'){
    $provincia->llenar_provincia();
    foreach ($provincia->objetos as $objeto) {
        $json[]=array(
            'id'=>$objeto->id,
            'nombre'=>$objeto->nombre,
            
        );
    }
    $jsonstring = json_encode($json);
    echo $jsonstring;
}