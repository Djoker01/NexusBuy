<?php
include_once '../Models/ProductoTienda.php';
include_once '../Util/Config/config.php';
include_once '../Models/Reseña.php';
include_once '../Models/Imagen.php';
include_once '../Models/Tienda.php';
include_once '../Models/Caracteristica.php';
$producto_tienda = new ProductoTienda();
$reseña = new Reseña();
$img = new Imagen();
$tnd = new Tienda(); 
$caracteristica = new Caracteristica();
session_start();


if ($_POST['funcion']=='llenar_productos'){
    $id_subcategoria = isset($_POST['id_subcategoria']) ? $_POST['id_subcategoria'] : null;
    $id_categoria = isset($_POST['id_categoria']) ? $_POST['id_categoria'] : null;

    if ($id_subcategoria) {
        $producto_tienda->llenar_productos(null, $id_subcategoria);
    } else if ($id_categoria) {
        $producto_tienda->llenar_productos(null, $id_categoria);
    } else {
        $producto_tienda->llenar_productos();
    }
     
    
    
    //var_dump($producto_tienda);
    $json=array();
    foreach ($producto_tienda->objetos as $objeto) {
        $reseña->evaluar_calificaciones($objeto->id);
        
        $json[]=array(
            'id'=>openssl_encrypt($objeto->id,CODE,KEY),
            'producto'=>$objeto->producto,
            'imagen'=>$objeto->imagen,
            'marca'=>$objeto->marca,
            'calificacion'=>number_format($reseña->objetos[0]->promedio),
            'envio'=>$objeto->envio,
            'precio'=>$objeto->precio,
            'descuento'=>$objeto->descuento,
            'precio_descuento'=>$objeto->precio_descuento,
        );
    }
    $jsonstring = json_encode($json);
    echo $jsonstring;
}
if ($_POST['funcion']=='verificar_producto'){
    $formateado = str_replace(" ","+",$_SESSION['product-verification']);
    $id_producto_tienda = openssl_decrypt($formateado,CODE,KEY);
    if (is_numeric($id_producto_tienda)) {
        $producto_tienda->llenar_productos($id_producto_tienda);
        $id_producto = $producto_tienda->objetos[0]->id_producto;
        $producto = $producto_tienda->objetos[0]->producto;
        $sku = $producto_tienda->objetos[0]->sku;
        $detalles = $producto_tienda->objetos[0]->detalles;
        $imagen = $producto_tienda->objetos[0]->imagen;
        $marca = $producto_tienda->objetos[0]->marca;
        $envio = $producto_tienda->objetos[0]->envio;
        $precio = $producto_tienda->objetos[0]->precio;
        $descuento = $producto_tienda->objetos[0]->descuento;
        $precio_descuento = $producto_tienda->objetos[0]->precio_descuento;
        $id_tienda = $producto_tienda->objetos[0]->id_tienda;
        $direccion_tienda = $producto_tienda->objetos[0]->direccion;
        $tienda = $producto_tienda->objetos[0]->tienda;
        $reseña->evaluar_calificaciones($id_producto_tienda); 
        $calificacion = $reseña->objetos[0]->promedio;
        $img->capturar_imagenes($id_producto);
        $imagenes = array();
        foreach ($img->objetos as $objeto) {
            $imagenes[]=array(
                'id'=>$objeto->id,
                'nombre'=>$objeto->nombre,
            );
        }
        $tnd->contar_reseñas($id_tienda);
        // $numero_reseñas = $tnd->objetos[0]->numero_reseñas;
        $promedio_calificacion_tienda = $tnd->objetos[0]->sumatoria;
        $caracteristica->capturar_caracteristicas($id_producto);
        $caracteristicas = array();
        foreach ($caracteristica -> objetos as $objeto) {
            $caracteristicas[]=array(
                'id'=>$objeto->id,
                'titulo'=>$objeto->titulo,
                'descripcion'=>$objeto->descripcion,
            );
        }
        $reseña->capturar_reseñas($id_producto_tienda);
        $reseñas = array();
        foreach ($reseña -> objetos as $objeto) {
            $reseñas[]=array(
                'id'=>$objeto->id,
                'calificacion'=>$objeto->calificacion,
                'descripcion'=>$objeto->descripcion,
                'fecha_creacion'=>$objeto->fecha_creacion,
                'usuario'=>$objeto->user,
                'avatar'=>$objeto->avatar,
            );
        }

        $tnd->obtener_redes_sociales($id_tienda);
        $redes_sociales = array(
            'facebook' => $tnd->objetos[0]->facebook ?? null,
            'instagram' => $tnd->objetos[0]->instagram ?? null,
            'tiktok' => $tnd->objetos[0]->tiktok ?? null,
            'youtube' => $tnd->objetos[0]->youtube ?? null,
            'whatsapp' => $tnd->objetos[0]->whatsapp ?? null,
            'email' => $tnd->objetos[0]->email_contacto ?? null,
            'sitio_web' => $tnd->objetos[0]->sitio_web ?? null
        );
        //var_dump($producto_tienda);
    

        $json=array(
            'id'=>$id_producto_tienda,
            'producto'=>$producto,
            'sku'=>$sku,
            'detalles'=>$detalles,
            'imagen'=>$imagen,
            'marca'=>$marca,
            'envio'=>$envio,
            'precio'=>$precio,
            'descuento'=>$descuento,
            'precio_descuento'=>$precio_descuento,
            'calificacion'=>number_format($calificacion),
            'direccion_tienda'=>$direccion_tienda,
            // 'numero_reseñas'=>$numero_reseñas,
            'promedio_calificacion_tienda'=>number_format($promedio_calificacion_tienda),
            'tienda'=>$tienda,
            'imagenes'=>$imagenes,
            'caracteristicas'=>$caracteristicas,
            'reseñas'=>$reseñas,
            'redes_sociales'=>$redes_sociales
        );
    
        $jsonstring = json_encode($json);
        echo $jsonstring;
    } else {
        echo 'error';
    }
    
    
}