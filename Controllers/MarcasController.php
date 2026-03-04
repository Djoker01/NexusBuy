<?php
include_once '../Models/Marcas.php';
$marcas = new Marcas();
session_start();

if ($_POST['funcion'] == 'obtener_marcas') {
    // Recibir parámetros de paginación
    $pagina = isset($_POST['page']) ? (int)$_POST['page'] : 1;
    $limite = isset($_POST['limit']) ? (int)$_POST['limit'] : 50;
    $offset = ($pagina - 1) * $limite;
    
    // Obtener marcas con paginación
    $marcas_resultados = $marcas->obtener_marcas_paginadas($limite, $offset);
    
    // Obtener el total de marcas
    $total = $marcas->obtener_total_marcas();
    $total_paginas = ceil($total / $limite);
    
    $json = array();
    $json['marcas'] = array();
    
    foreach ($marcas_resultados as $marca_item) {
        $json['marcas'][] = array(
            'nombre' => $marca_item->nombre,
            'logo' => $marca_item->logo,
            'descripcion' => $marca_item->descripcion
        );
    }
    
    // Agregar metadatos de paginación
    $json['paginacion'] = array(
        'pagina_actual' => $pagina,
        'total_paginas' => $total_paginas,
        'total_marcas' => $total,
        'limite_por_pagina' => $limite
    );
    
    $jsonstring = json_encode($json);
    echo $jsonstring;
}