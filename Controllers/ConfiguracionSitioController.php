<?php
header('Content-Type: application/json');

include_once '../Models/ConfiguracionSitio.php';

$configuracion = new ConfiguracionSitio();

if ($_POST['funcion'] == 'obtener_datos_soporte') {
    try {
        $datos = $configuracion->obtenerDatosSoporte();
        
        $json = array(
            'status' => 'success',
            'data' => $datos
        );
        
        echo json_encode($json);
    } catch (Exception $e) {
        $json = array(
            'status' => 'error',
            'message' => 'Error al obtener datos de soporte: ' . $e->getMessage()
        );
        echo json_encode($json);
    }
    exit();
}

// Nueva función para obtener datos de contacto
if ($_POST['funcion'] == 'obtener_contacto') {
    try {
        $contacto = $configuracion->obtenerPorCategoria('contacto');
        $general = $configuracion->obtenerPorCategoria('general');
        
        $json = array(
            'status' => 'success',
            'data' => array_merge($contacto, $general)
        );
        
        echo json_encode($json);
    } catch (Exception $e) {
        $json = array(
            'status' => 'error',
            'message' => 'Error al obtener datos de contacto: ' . $e->getMessage()
        );
        echo json_encode($json);
    }
    exit();
}

// Nueva función para obtener términos y condiciones
if ($_POST['funcion'] == 'obtener_terminos') {
    try {
        $terminos = $configuracion->obtener('terminos_condiciones');
        $privacidad = $configuracion->obtener('politica_privacidad');
        
        $json = array(
            'status' => 'success',
            'data' => [
                'terminos_condiciones' => $terminos,
                'politica_privacidad' => $privacidad
            ]
        );
        
        echo json_encode($json);
    } catch (Exception $e) {
        $json = array(
            'status' => 'error',
            'message' => 'Error al obtener términos: ' . $e->getMessage()
        );
        echo json_encode($json);
    }
    exit();
}

if ($_POST['funcion'] == 'obtenerRedesSociales') {
    $configuracion->obtenerRedesSociales();
    $json = array();
    
    foreach ($configuracion->objetos as $redes_item) {
        $json[] = array(
            'id' => $redes_item->id,
            'nombre' => $redes_item->nombre,
            'icono' => $redes_item->icono,
            'url' => $redes_item->url
        );
    }
    
    $jsonstring = json_encode($json);
    echo $jsonstring;
}