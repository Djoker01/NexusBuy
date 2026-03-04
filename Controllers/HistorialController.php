<?php
include_once '../Models/Historial.php';
$historial = new Historial();
session_start();

if($_POST['funcion']=='llenar_historial'){
    if(empty($_SESSION['id'])){
        echo json_encode(['error' => 'no_sesion']);
        exit();
    }
    
    $id_usuario = $_SESSION['id'];
    $historial->llenar_historial($id_usuario);
    
    $bandera='';
    $cont=0;
    $fechas = array();
    
    // DEBUG: Ver qué datos estamos obteniendo
    error_log("Objetos obtenidos: " . print_r($historial->objetos, true));
    
    foreach ($historial->objetos as $objeto) {
        $fecha_hora = date_create($objeto->fecha . ' ' . $objeto->hora);
        $hora = date_format($fecha_hora, 'H:i:s');
        $fecha = date_format($fecha_hora, 'd-m-Y');
        
        if ($fecha != $bandera) {
            $cont++;
            $bandera = $fecha;
        }
        
        if ($cont <= 4) {
            $item = array(
                'id' => $objeto->id,
                'descripcion' => $objeto->descripcion,
                'fecha' => $fecha,
                'hora' => $hora,
                'tipo_historial' => $objeto->tipo_historial,
                'th_icono' => $objeto->th_icono,
                'modulo' => $objeto->modulo,
                'm_icono' => $objeto->m_icono,
                'accion' => $objeto->accion
            );
            
            // DEBUG: Ver cada item
            error_log("Item: " . print_r($item, true));
            
            $fechas[$cont-1][] = $item;
        } else {
            break;
        }
    }
    
    // DEBUG: Ver estructura final
    error_log("Estructura final: " . print_r($fechas, true));
    
    echo json_encode($fechas);
}

// Función para crear historial desde otros controladores
if($_POST['funcion']=='crear_historial'){
    if(empty($_SESSION['id'])){
        echo json_encode(['error' => 'no_sesion']);
        exit();
    }
    
    $id_usuario = $_SESSION['id'];
    $descripcion = $_POST['descripcion'] ?? '';
    $tipo_historial = $_POST['tipo_historial'] ?? 9; // 9 = Sistema por defecto
    $modulo = $_POST['modulo'] ?? 8; // 8 = Sistema por defecto
    $accion = $_POST['accion'] ?? null;
    $datos_json = isset($_POST['datos']) ? json_encode($_POST['datos']) : null;
    
    $resultado = $historial->crear_historial(
        $descripcion, 
        $tipo_historial, 
        $modulo, 
        $id_usuario, 
        $accion, 
        $datos_json
    );
    
    echo json_encode(['success' => $resultado]);
}
?>