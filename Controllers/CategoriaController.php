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
            'subcategorias' => $subcategorias
        );
    }
    
    $jsonstring = json_encode($json);
    echo $jsonstring;
}
if ($_POST['funcion'] == 'obtener_subcategoria_por_id') {
    $id_subcategoria = $_POST['id_subcategoria'];

    $sql = "SELECT sc.*, c.nombre as categoria_nombre
            FROM subcategoria sc
            JOIN categoria c ON sc.id_categoria = c.id
            WHERE sc.id = :id AND sc.estado = 'A'";
    $query = $subcategoria->acceso->prepare($sql);
    $query->execute(array(':id'=>$id_subcategoria));
    $subcategoria_data = $query->fetchAll();
    
    if (!empty($subcategoria_data)) {
        $json = array(
            'id' => $subcategoria_data[0]->id,
            'nombre' => $subcategoria_data[0]->nombre,
            'categoria_nombre' =>$subcategoria_data[0]->categoria_nombre
        );
        echo json_encode($json);
    } else {
        echo 'error';
    }
    

}
