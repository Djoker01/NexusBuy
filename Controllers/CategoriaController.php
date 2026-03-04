<?php
include_once '../Models/Categoria.php';
include_once '../Models/Subcategoria.php';
$categoria = new Categoria();
$subcategoria = new Subcategoria();
session_start();

if ($_POST['funcion'] == 'obtener_categorias_menu') {
    $categoria->obtener_categorias_activas();
    $json = array();
    
    foreach ($categoria->objetos as $categoria_item) {
        // Obtener subcategorías para esta categoría
        $subcategoria->obtener_subcategorias_por_categoria($categoria_item->id);
        $subcategorias = array();
        
        foreach ($subcategoria->objetos as $subcat) {
            $subcategorias[] = array(
                'id' => $subcat->id,
                'nombre' => $subcat->nombre
            );
        }
        
        $json[] = array(
            'id' => $categoria_item->id,
            'nombre' => $categoria_item->nombre,
            'icono' => $categoria_item->icono,
            'subcategorias' => $subcategorias
        );
    }
    
    $jsonstring = json_encode($json);
    echo $jsonstring;
}
if ($_POST['funcion'] == 'obtener_categoria_por_id') {
    $id_categoria = $_POST['id_categoria'];
    
    $sql = "SELECT * FROM categoria WHERE id = :id AND estado = 'activa'";
    $query = $categoria->acceso->prepare($sql);
    $query->execute(array(':id' => $id_categoria));
    $categoria_data = $query->fetchAll();
    
    if (!empty($categoria_data)) {
        $json = array(
            'id' => $categoria_data[0]->id,
            'nombre' => $categoria_data[0]->nombre,
            'descripcion' => $categoria_data[0]->descripcion,
            'icono' => $categoria_data[0]->icono
        );
        echo json_encode($json);
    } else {
        echo json_encode(array('error' => 'Categoría no encontrada'));
    }
}

if ($_POST['funcion'] == 'explorar_categorias') {
    $categoria->explorar_categorias();
    //  var_dump($categoria);
     $json = array();
    
     foreach ($categoria->objetos as $objeto) {
        $json[] = array(
            'id' => $objeto->id,
             'nombre' => $objeto->nombre,
             'imagen' => $objeto->imagen,
             'total_productos' => $objeto->total_productos
        );
     }
    
     $jsonstring = json_encode($json);
     echo $jsonstring;
}