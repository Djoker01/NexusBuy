<?php
include_once '../Models/Municipio.php';
$municipio = new Municipio();
session_start();

if ($_POST['funcion']=='llenar_municipio'){
    $id_provincia=$_POST['id_provincia'];
    $municipio->llenar_municipio($id_provincia);
    $json=array();
    foreach ($municipio->objetos as $objeto) {
        $json[]=array(
            'id'=>$objeto->id,
            'nombre'=>$objeto->nombre,
            // AGREGAR ESTA LÍNEA ↓
            'id_provincia'=>$objeto->id_provincia ?? $id_provincia
        );
    }
    $jsonstring = json_encode($json);
    echo $jsonstring;
}

// AGREGAR ESTA NUEVA FUNCIÓN ↓
if ($_POST['funcion']=='obtener_provincia_municipio'){
    $id_municipio = $_POST['id_municipio'];
    
    // Crear una instancia temporal para obtener la provincia
    $municipio_temp = new Municipio();
    $provincia = $municipio_temp->obtener_provincia_municipio($id_municipio);
    
    if ($provincia) {
        echo json_encode(['id_provincia' => $provincia]);
    } else {
        echo json_encode(['id_provincia' => 1]); // Valor por defecto
    }
}