<?php
include_once '../Models/Usuario.php';
include_once '../Models/TabpanelConfig.php';
include_once '../Util/Config/config.php';

$usuario = new Usuario();
$configuracion = new Configuracion();

session_start();

if ($_POST['funcion'] == 'guardar_configuracion') {
    if (empty($_SESSION['id'])) {
        echo json_encode(['error' => 'no_sesion']);
        exit();
    }
    
    $id_usuario = $_SESSION['id'];
    $tipo_configuracion = $_POST['tipo_configuracion'];
    $datos_configuracion = $_POST['datos_configuracion'];
    
    try {
        $resultado = $configuracion->guardarConfiguracion($id_usuario, $tipo_configuracion, $datos_configuracion);
        
        echo json_encode([
            'success' => $resultado,
            'mensaje' => 'Configuración guardada correctamente'
        ]);
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'error' => 'Error al guardar la configuración: ' . $e->getMessage()
        ]);
    }
}

if ($_POST['funcion'] == 'cargar_configuracion') {
    if (empty($_SESSION['id'])) {
        echo json_encode(['error' => 'no_sesion']);
        exit();
    }
    
    $id_usuario = $_SESSION['id'];
    
    try {
        $configuraciones = $configuracion->cargarConfiguraciones($id_usuario);
        
        echo json_encode([
            'success' => true,
            'configuraciones' => $configuraciones
        ]);
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'error' => 'Error al cargar la configuración'
        ]);
    }
}

if ($_POST['funcion'] == 'exportar_datos') {
    if (empty($_SESSION['id'])) {
        echo json_encode(['error' => 'no_sesion']);
        exit();
    }
    
    $id_usuario = $_SESSION['id'];
    $formatos_permitidos = $_POST['formatos'] ?? ['perfil', 'pedidos'];
    
    try {
        $datos_exportacion = $configuracion->generarDatosExportacion($id_usuario, $formatos_permitidos);
        
        echo json_encode([
            'success' => true,
            'datos' => $datos_exportacion,
            'formato' => $_POST['formato'] ?? 'json'
        ]);
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'error' => 'Error al exportar datos: ' . $e->getMessage()
        ]);
    }
}

if ($_POST['funcion'] == 'eliminar_cuenta') {
    if (empty($_SESSION['id'])) {
        echo json_encode(['error' => 'no_sesion']);
        exit();
    }
    
    $id_usuario = $_SESSION['id'];
    $confirmacion = $_POST['confirmacion'] ?? '';
    
    if ($confirmacion !== 'ELIMINAR') {
        echo json_encode([
            'success' => false,
            'error' => 'Confirmación requerida'
        ]);
        exit();
    }
    
    try {
        // En un sistema real, aquí harías un soft delete o marcarías como inactivo
        $resultado = $configuracion->desactivarCuenta($id_usuario);
        
        if ($resultado) {
            session_destroy();
            echo json_encode([
                'success' => true,
                'mensaje' => 'Cuenta eliminada correctamente'
            ]);
        } else {
            throw new Exception('No se pudo eliminar la cuenta');
        }
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'error' => 'Error al eliminar la cuenta: ' . $e->getMessage()
        ]);
    }
    if ($_POST['funcion'] == 'aplicar_tema_instantaneo') {
        if (empty($_SESSION['id'])) {
            echo json_encode(['error' => 'no_sesion']);
            exit();
        }
        
        $id_usuario = $_SESSION['id'];
        $tema = $_POST['tema'];
        
        // Validar tema
        $temas_permitidos = ['claro', 'oscuro', 'auto'];
        if (!in_array($tema, $temas_permitidos)) {
            echo json_encode(['success' => false, 'error' => 'Tema no válido']);
            exit();
        }
        
        // Guardar en sesión para aplicación inmediata
        $_SESSION['tema_usuario'] = $tema;
        
        // También guardar en la base de datos
        $configuracion_actual = $configuracion->cargarConfiguraciones($id_usuario);
        $config_visualizacion = $configuracion_actual['visualizacion'] ?? [];
        $config_visualizacion['tema'] = $tema;
        
        $resultado = $configuracion->guardarConfiguracion(
            $id_usuario, 
            'visualizacion', 
            json_encode($config_visualizacion)
        );
        
        echo json_encode([
            'success' => $resultado,
            'tema_aplicado' => $tema
        ]);
    }
}