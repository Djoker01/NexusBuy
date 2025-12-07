<?php
include_once '../Models/Producto.php';
include_once '../Util/Config/config.php';
include_once '../Models/Reseña.php';
include_once '../Models/Imagen.php';
include_once '../Models/Tienda.php';
$producto = new Producto();
$reseña = new Reseña();
$img = new Imagen();
$tnd = new Tienda();

session_start();


if ($_POST['funcion'] == 'llenar_productos') {
    $id_subcategoria = isset($_POST['id_subcategoria']) ? $_POST['id_subcategoria'] : null;
    $id_categoria = isset($_POST['id_categoria']) ? $_POST['id_categoria'] : null;

    if ($id_subcategoria) {
        $producto->llenar_productos(null, $id_subcategoria);
    } else if ($id_categoria) {
        $producto->llenar_productos(null, null, $id_categoria);
    } else {
        $producto->llenar_productos();
    }
    $json = array();
    foreach ($producto->objetos as $objeto) {
        $reseña->evaluar_calificaciones($objeto->id);

        $json[] = array(
            'id' => openssl_encrypt($objeto->id, CODE, KEY),
            'id_producto' => $objeto->id_producto,
            'producto' => $objeto->producto,
            'imagen' => $objeto->imagen,
            'marca' => $objeto->marca,
            'calificacion' => number_format($reseña->objetos[0]->promedio, 1),
            'envio' => $objeto->envio,
            'precio' => number_format($objeto->precio, 2),
            'descuento' => $objeto->descuento,
            'precio_descuento' => number_format($objeto->precio_descuento, 2),
            'stock' => $objeto->stock,
            'es_nuevo' => $objeto->es_nuevo,
            'destacado' => $objeto->destacado,
            'fecha_creacion' => $objeto->fecha_creacion
        );
    }
    $jsonstring = json_encode($json);
    echo $jsonstring;
}
if ($_POST['funcion'] == 'verificar_producto') {
    $formateado = str_replace(" ", "+", $_SESSION['product-verification']);
    $id_producto_tienda = openssl_decrypt($formateado, CODE, KEY);
    if (is_numeric($id_producto_tienda)) {
        $producto_obj = new Producto();
        $producto_obj->llenar_productos($id_producto_tienda);
        if (empty($producto_obj->objetos)) {
            echo 'error';
            return;
        }
        $producto_data = $producto_obj->objetos[0];
        $id_producto = $producto_data->id_producto;
        $producto = $producto_data->producto;
        $sku = $producto_data->sku;
        $stock = $producto_data->stock;
        $stock_minimo = $producto_data->stock_minimo;
        $destacado = $producto_data->destacado;
        $detalles = $producto_data->detalles;
        $imagen = $producto_data->imagen;
        $marca = $producto_data->marca;
        $envio = $producto_data->envio;
        $precio = $producto_data->precio;
        $descuento = $producto_data->descuento;
        $precio_descuento = $producto_data->precio_descuento;
        $id_tienda = $producto_data->id_tienda;
        $direccion_tienda = $producto_data->direccion;
        $tienda = $producto_data->tienda;
        $calificacion = $producto_data->promedio;
        $img->capturar_imagenes($id_producto);
        $imagenes = array();
        foreach ($img->objetos as $objeto) {
            $imagenes[] = array(
                'id' => $objeto->id,
                'nombre' => $objeto->imagen_url,
            );
        }
        $tnd->contar_reseñas($id_tienda);
        $tnd_data = $tnd->objetos[0];
        $calificacion_tienda = $tnd_data->sumatoria;
        $caracteristicas = $producto_data->caracteristicas;
        $caracteristicas = array();
        if (isset($producto_data->caracteristicas) && !empty($producto_data->caracteristicas)) {
            try {
                $caracteristicas_json = json_decode($producto_data->caracteristicas, true);
                if (is_array($caracteristicas_json) && !empty($caracteristicas_json)) {
                    $contador = 0;
                    foreach ($caracteristicas_json as $key => $value) {
                        $contador++;
                        if (is_array($value)) {
                            $titulo = isset($value['titulo']) ? $value['titulo'] : $key;
                            $descripcion = isset($value['descripcion']) ? $value['descripcion'] : (isset($value['valor']) ? $value['valor'] : (is_string($value) ? $value : json_encode($value)));
                        } else {
                            $titulo = $key;
                            $descripcion = $value;
                        }
                        $caracteristicas[] = array(
                            'id' => $contador,
                            'titulo' => $titulo,
                            'descripcion' => $descripcion,
                        );
                    }
                }
            } catch (Exception $e) {
                error_log("Error decodificando características JSON: " . $e->getMessage());
            }
        }
        $reseña->capturar_reseñas($id_producto_tienda);
        $reseñas = array();
        foreach ($reseña->objetos as $objeto) {
            $reseñas[] = array(
                'id' => $objeto->id,
                'calificacion' => $objeto->calificacion,
                'comentario' => $objeto->comentario,
                'respuesta' => $objeto->respuesta,
                'fecha_creacion' => $objeto->fecha_creacion,
                'usuario' => $objeto->user,
                'avatar' => $objeto->avatar,
            );
        }
        $json = array(
            'id' => $id_producto_tienda,
            'producto' => $producto,
            'sku' => $sku,
            'stock' => $stock,
            'stock_minimo' => $stock_minimo,
            'destacado' => $destacado,
            'detalles' => $detalles,
            'imagen' => $imagen,
            'marca' => $marca,
            'envio'=>$envio,
            'precio' => $precio,
            'descuento' => $descuento,
            'precio_descuento' => $precio_descuento,
            'calificacion'=>number_format($calificacion, 1),
            'direccion_tienda'=>$direccion_tienda,
            'promedio_calificacion_tienda' => number_format($calificacion_tienda, 1),
            'tienda' => $tienda,
            'imagenes' => $imagenes,
            'caracteristicas' => $caracteristicas,
            'reseñas' => $reseñas
        );

        $jsonstring = json_encode($json);
        echo $jsonstring;
    } else {
        echo 'error';
    }
}
