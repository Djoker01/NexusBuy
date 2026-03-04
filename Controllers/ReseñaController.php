<?php
include_once '../Models/Reseña.php';
include_once '../Models/Historial.php';
include_once '../Util/Config/config.php';
$reseña = new Reseña();
$historial = new Historial();
session_start();

if ($_POST['funcion'] == 'crear_reseña') {
    if (empty($_SESSION['id'])) {
        echo 'no_sesion';
        exit();
    }
    $id_usuario = $_SESSION['id'];
    $id_producto_tienda_encrypted = $_POST['id_producto_tienda'];
    $calificacion = intval($_POST['calificacion']);
    $comentario = $_POST['comentario'];

    if (empty($calificacion) || $calificacion < 1 || $calificacion > 5) {
        echo 'error_calificacion';
        exit();
    }
    if (empty($comentario) || strlen($comentario) < 10) {
        echo 'error_comentario';
        exit();
    }
    $id_producto_tienda = openssl_decrypt($id_producto_tienda_encrypted, CODE, KEY);
    if (!is_numeric($id_producto_tienda)) {
        $id_producto_tienda = openssl_decrypt(str_replace(" ", "+", $id_producto_tienda_encrypted), CODE, KEY);
    }
    if (!is_numeric($id_producto_tienda)) {
        echo 'error_producto';
        exit();
    }
    $reseña->verificar_reseña_usuario($id_producto_tienda,$id_usuario);
    if (!empty($reseña->objetos)) {
        echo 'ya_reseñado';
        exit();
    }
    $resultado = $reseña->crear_reseña($id_producto_tienda, $id_usuario, $calificacion, $comentario);

    if ($resultado == 'success') {
        $descripcion = 'Agregó una reseña de '.$calificacion.' estrellas a un producto';
        $historial->crear_historial($descripcion, 2, 2, $id_usuario);
        echo 'success';
    }else {
        echo 'error';
    }
}
if ($_POST['funcion'] == 'verificar_reseña_usuario') {
    // Verificar que el usuario esté logueado
    if (empty($_SESSION['id'])) {
        echo json_encode(array('error' => 'no_sesion'));
        exit();
    }

    $id_usuario = $_SESSION['id'];
    $id_producto_tienda_encrypted = $_POST['id_producto_tienda'];

    // Desencriptar ID del producto
    $id_producto_tienda = openssl_decrypt($id_producto_tienda_encrypted, CODE, KEY);
    if (!is_numeric($id_producto_tienda)) {
        $id_producto_tienda = openssl_decrypt(str_replace(" ", "+", $id_producto_tienda_encrypted), CODE, KEY);
    }

    if (is_numeric($id_producto_tienda)) {
        $reseña->verificar_reseña_usuario($id_producto_tienda, $id_usuario);
        
        if (!empty($reseña->objetos)) {
            // Usuario ya hizo una reseña
            echo json_encode(array(
                'ya_reseñado' => true,
                'calificacion' => $reseña->objetos[0]->calificacion,
                'comentario' => $reseña->objetos[0]->descripcion,
                'fecha' => $reseña->objetos[0]->fecha_creacion
            ));
        } else {
            // Usuario no ha hecho reseña
            echo json_encode(array(
                'ya_reseñado' => false
            ));
        }
    } else {
        echo json_encode(array('error' => 'id_invalido'));
    }
}