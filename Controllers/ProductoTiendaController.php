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
    $nombre_categoria = isset($_POST['nombre_categoria']) ? $_POST['nombre_categoria'] : null;
    $solo_destacados = isset($_POST['solo_destacados']) ? filter_var($_POST['solo_destacados'], FILTER_VALIDATE_BOOLEAN) : false;
    $limite = isset($_POST['limite']) ? intval($_POST['limite']) : null;

    //Si se envió el nombre_categoria, buscar pripero el ID
    if ($nombre_categoria) {
    // Buscar el ID de la categoría por nombre
    $sql_cat = "SELECT id FROM categoria WHERE nombre = :nombre AND estado = 'activa' LIMIT 1";
    $query_cat = $producto->acceso->prepare($sql_cat); // Usar la conexión del modelo
    $query_cat->execute(array(':nombre' => $nombre_categoria));
    $categoria_data = $query_cat->fetch(PDO::FETCH_OBJ);
    
    if ($categoria_data) {
        $id_categoria = $categoria_data->id;
    } else {
        // Si no encuentra la categoría
        echo json_encode(array());
        return;
    }
}

    if ($id_subcategoria) {
        $producto->llenar_productos(null, $id_subcategoria, null, $solo_destacados, $limite);
    } else if ($id_categoria) {
        $producto->llenar_productos(null, null, $id_categoria, $solo_destacados, $limite);
    } else {
        $producto->llenar_productos(null, null, null, $solo_destacados, $limite);
    }
    $json = array();
    foreach ($producto->objetos as $objeto) {
        // $reseña->evaluar_calificaciones($objeto->id);
        // $reseña->capturar_reseñas($objeto->id);
        // $total_resenas = count($reseña->objetos);

        $json[] = array(
            'id' => openssl_encrypt($objeto->id, CODE, KEY),
            'id_producto' => $objeto->id_producto,
            'producto' => $objeto->producto,
            'imagen' => $objeto->imagen,
            'marca' => $objeto->marca,
            'calificacion' => $objeto->promedio,
            'envio' => $objeto->envio,
            'precio' => $objeto->precio,
            'descuento' => $objeto->descuento,
            'precio_descuento' => $objeto->precio_descuento,
            'stock' => $objeto->stock,
            'es_nuevo' => $objeto->es_nuevo,
            'destacado' => $objeto->destacado,
            'fecha_creacion' => $objeto->fecha_creacion,
            'total_resenas' => $objeto->resenas
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
        $total_resenas = $producto_data->resenas;
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
            'calificacion'=>number_format($calificacion, 2),
            'direccion_tienda'=>$direccion_tienda,
            'promedio_calificacion_tienda' => number_format($calificacion_tienda, 1),
            'tienda' => $tienda,
            'imagenes' => $imagenes,
            'caracteristicas' => $caracteristicas,
            'reseñas' => $reseñas,
            'total_resenas' => $total_resenas
        );

        $jsonstring = json_encode($json);
        echo $jsonstring;
    } else {
        echo 'error';
    }
}

// Vista Rápida

if ($_POST['funcion'] == 'obtener_producto_rapido') {
    try {
        $formateado = str_replace(" ", "+", $_POST['id_producto_tienda']);
        $id_producto_tienda = openssl_decrypt($formateado, CODE, KEY);
        
        if (!is_numeric($id_producto_tienda)) {
            echo json_encode(['success' => false, 'error' => 'ID de producto inválido']);
            return;
        }
        
        $producto_obj = new Producto();
        $producto_obj->llenar_productos($id_producto_tienda);
        
        if (empty($producto_obj->objetos)) {
            echo json_encode(['success' => false, 'error' => 'Producto no encontrado']);
            return;
        }
        
        $producto_data = $producto_obj->objetos[0];
        
        // Obtener imágenes adicionales
        $img->capturar_imagenes($producto_data->id_producto);
        $imagenes = [];
        foreach ($img->objetos as $objeto) {
            $imagenes[] = $objeto->imagen_url;
        }
        
        // Obtener reseñas
        $reseña->capturar_reseñas($id_producto_tienda);
        $total_resenas = count($reseña->objetos);
        
        // Formatear respuesta
        $producto = [
            'id' => openssl_encrypt($producto_data->id, CODE, KEY),
            'id_producto' => $producto_data->id_producto,
            'producto' => $producto_data->producto,
            'imagen' => $producto_data->imagen,
            'marca' => $producto_data->marca,
            'calificacion' => number_format($producto_data->promedio, 1),
            'total_resenas' => $total_resenas,
            'envio' => $producto_data->envio,
            'precio' => $producto_data->precio,
            'descuento' => $producto_data->descuento,
            'precio_descuento' => $producto_data->precio_descuento,
            'stock' => $producto_data->stock,
            'stock_minimo' => $producto_data->stock_minimo,
            'destacado' => $producto_data->destacado,
            'detalles' => $producto_data->detalles,
            'caracteristicas' => json_decode($producto_data->caracteristicas, true) ?: [],
            'tienda' => $producto_data->tienda,
            'fecha_creacion' => $producto_data->fecha_creacion,
            'es_nuevo' => $producto_data->es_nuevo,
            'imagenes_adicionales' => $imagenes
        ];
        
        echo json_encode(['success' => true, 'producto' => $producto]);
        
    } catch (Exception $e) {
        error_log("Error en obtener_producto_rapido: " . $e->getMessage());
        echo json_encode(['success' => false, 'error' => 'Error interno del servidor']);
    }
}
