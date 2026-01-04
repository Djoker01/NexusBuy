<?php
include_once '../Models/UsuarioDireccion.php';
include_once '../Models/Historial.php';
include_once '../Util/Config/config.php';

$direccion_model = new UsuarioDireccion();
$historial = new Historial();
session_start();

// ✅ Crear dirección (actualizado)
if ($_POST['funcion']=='crear_direccion'){
    $id_usuario = $_SESSION['id'];
    $id_municipio = $_POST['id_municipio'];
    $direccion_texto = $_POST['direccion'];
    $alias = $_POST['alias'] ?? null;
    $instrucciones = $_POST['instrucciones'] ?? null;
    
    // Si no hay direcciones principales, esta será principal
    $es_principal = 0;
    if (isset($_POST['es_principal']) && $_POST['es_principal'] == '1') {
        $es_principal = 1;
    } else {
        // Verificar si el usuario ya tiene dirección principal
        $tiene_principal = $direccion_model->verificar_principal_usuario($id_usuario);
        if ($tiene_principal == 0) {
            $es_principal = 1; // Primera dirección = principal por defecto
        }
    }
    
    $direccion_model->crear_direccion(
        $id_usuario, 
        $id_municipio, 
        $direccion_texto, 
        $alias, 
        $instrucciones, 
        $es_principal
    );
    
    $descripcion = 'Ha creado una nueva dirección: ' . $direccion_texto;
    $historial->crear_historial($descripcion, 1, 3, $id_usuario, 'crear_direccion');
    echo 'success';
}

// ✅ Obtener direcciones (actualizado)
if ($_POST['funcion']=='llenar_direcciones'){
    $id_usuario = $_SESSION['id'];
    $direccion_model->llenar_direcciones($id_usuario);
    
    $json=array();
    foreach ($direccion_model->objetos as $objeto) {
        $json[]=array(
            'id'=>openssl_encrypt($objeto->id, CODE, KEY),
            'direccion'=>$objeto->direccion,
            'municipio'=>$objeto->municipio,
            'provincia'=>$objeto->provincia,
            'alias'=>$objeto->alias,
            'instrucciones'=>$objeto->instrucciones,
            'es_principal'=>$objeto->es_principal,
            'fecha_creacion'=>$objeto->fecha_creacion
        );
    }
    $jsonstring = json_encode($json);
    echo $jsonstring;
}

// ✅ Eliminar dirección
if ($_POST['funcion']=='eliminar_direccion'){
    $id_direccion = openssl_decrypt($_POST['id'], CODE, KEY);
    
    if (is_numeric($id_direccion)) {
        $direccion_model->recuperar_direccion($id_direccion);
        
        if (!empty($direccion_model->objetos)) {
            $direccion_borrada = $direccion_model->objetos[0]->direccion.', Municipio: '.$direccion_model->objetos[0]->municipio.', Provincia: '.$direccion_model->objetos[0]->provincia;
            
            $direccion_model->eliminar_direccion($id_direccion);
            
            $descripcion = 'Ha eliminado la Dirección: '.$direccion_borrada;
            $historial->crear_historial($descripcion, 3, 3, $_SESSION['id'], 'eliminar_direccion');
            
            echo 'success';
        } else {
            echo 'error';
        }
    } else {
        echo 'error';
    }
}

// ✅ Marcar dirección como principal (NUEVA)
if ($_POST['funcion']=='marcar_direccion_principal'){
    $id_direccion = openssl_decrypt($_POST['id'], CODE, KEY);
    $id_usuario = $_SESSION['id'];
    
    if (is_numeric($id_direccion)) {
        $resultado = $direccion_model->marcar_direccion_principal($id_usuario, $id_direccion);
        
        if ($resultado) {
            $direccion_model->recuperar_direccion($id_direccion);
            if (!empty($direccion_model->objetos)) {
                $direccion_principal = $direccion_model->objetos[0]->direccion;
                $descripcion = 'Marcó como dirección principal: ' . $direccion_principal;
                $historial->crear_historial($descripcion, 2, 3, $id_usuario, 'marcar_principal');
            }
            
            echo 'success';
        } else {
            echo 'error';
        }
    } else {
        echo 'error';
    }
}

// ✅ Obtener dirección por ID (NUEVA - para editar)
if ($_POST['funcion']=='obtener_direccion'){
    $id_direccion = openssl_decrypt($_POST['id'], CODE, KEY);
    
    if (is_numeric($id_direccion)) {
        $direccion_model->recuperar_direccion($id_direccion);
        
        if (!empty($direccion_model->objetos)) {
            $objeto = $direccion_model->objetos[0];
            
            // OBTENER LA PROVINCIA DEL MUNICIPIO
            $sql_provincia = "SELECT m.id_provincia 
                            FROM municipio m 
                            WHERE m.id = :id_municipio";
            $query = $direccion_model->acceso->prepare($sql_provincia);
            $query->execute(array(':id_municipio' => $objeto->id_municipio));
            $provincia_data = $query->fetch(PDO::FETCH_ASSOC);
            
            $json = array(
                'id'=>openssl_encrypt($objeto->id, CODE, KEY),
                'direccion'=>$objeto->direccion,
                'id_municipio'=>$objeto->id_municipio,
                'alias'=>$objeto->alias,
                'instrucciones'=>$objeto->instrucciones_entrega,
                'es_principal'=>$objeto->es_principal,
                // AGREGAR ESTO ↓
                'id_provincia'=>$provincia_data['id_provincia'] ?? 1
            );
            
            echo json_encode($json);
        }
    }
}

// ✅ Actualizar dirección existente
if ($_POST['funcion'] == 'actualizar_direccion') {
    $id_direccion = openssl_decrypt($_POST['id'], CODE, KEY);
    $id_usuario = $_SESSION['id'];
    
    if (!is_numeric($id_direccion)) {
        echo 'error_id';
        exit();
    }
    
    // Obtener datos del formulario
    $id_municipio = $_POST['id_municipio'] ?? '';
    $direccion_texto = $_POST['direccion'] ?? '';
    $alias = $_POST['alias'] ?? null;
    $instrucciones = $_POST['instrucciones'] ?? null;
    $es_principal = isset($_POST['es_principal']) && $_POST['es_principal'] == '1' ? 1 : 0;
    
    // Validar campos obligatorios
    if (empty($id_municipio) || empty($direccion_texto)) {
        echo 'error_campos';
        exit();
    }
    
    // Verificar que la dirección pertenezca al usuario
    $direccion_model->recuperar_direccion($id_direccion);
    if (empty($direccion_model->objetos) || $direccion_model->objetos[0]->id_usuario != $id_usuario) {
        echo 'error_permiso';
        exit();
    }
    
    // Si se marca como principal, actualizar las demás
    if ($es_principal == 1) {
        $direccion_model->marcar_direccion_principal($id_usuario, $id_direccion);
    } else {
        // Actualizar solo esta dirección
        $resultado = $direccion_model->actualizar_direccion(
            $id_direccion,
            $id_municipio,
            $direccion_texto,
            $alias,
            $instrucciones,
            $es_principal
        );
    }
    
    if ($resultado || $es_principal == 1) {
        // Registrar en historial
        $descripcion = 'Actualizó la dirección: ' . $direccion_texto;
        $historial->crear_historial($descripcion, 2, 3, $id_usuario, 'actualizar_direccion');
        echo 'success';
    } else {
        echo 'error_actualizar';
    }
}