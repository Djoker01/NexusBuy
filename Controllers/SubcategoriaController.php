<?php
include_once '../Models/Subcategoria.php';
$subcategoria = new Subcategoria();
session_start();

if ($_POST['funcion'] == 'buscar_subcategoria_por_nombre') {
    $nombre_subcategoria = $_POST['nombre_subcategoria'];
    
    $sql = "SELECT sc.*, c.nombre as categoria_nombre 
            FROM subcategoria sc
            JOIN categoria c ON sc.id_categoria = c.id
            WHERE sc.nombre = :nombre 
            AND sc.estado = 'activa'
            AND c.estado = 'activa'";
    
    $query = $subcategoria->acceso->prepare($sql);
    $query->execute(array(':nombre' => $nombre_subcategoria));
    $subcategoria_data = $query->fetch(PDO::FETCH_OBJ);
    
    if ($subcategoria_data) {
        echo json_encode([
            'success' => true,
            'id' => $subcategoria_data->id,
            'nombre' => $subcategoria_data->nombre,
            'categoria_nombre' => $subcategoria_data->categoria_nombre,
            'categoria_id' => $subcategoria_data->id_categoria
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Subcategoría no encontrada']);
    }
}

if ($_POST['funcion'] == 'obtener_subcategoria_por_id') {
    $id_subcategoria = $_POST['id_subcategoria'];
    
    $sql = "SELECT sc.*, c.nombre as categoria_nombre 
            FROM subcategoria sc
            JOIN categoria c ON sc.id_categoria = c.id
            WHERE sc.id = :id AND sc.estado = 'activa'";
    $query = $subcategoria->acceso->prepare($sql);
    $query->execute(array(':id' => $id_subcategoria));
    $subcategoria_data = $query->fetch(PDO::FETCH_OBJ);
    
    if ($subcategoria_data) {
        echo json_encode([
            'success' => true,
            'id' => $subcategoria_data->id,
            'nombre' => $subcategoria_data->nombre,
            'categoria_nombre' => $subcategoria_data->categoria_nombre,
            'categoria_id' => $subcategoria_data->id_categoria
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Subcategoría no encontrada']);
    }
}

if ($_POST['funcion'] == 'obtener_categoria_por_subcategoria') {
    $nombre_subcategoria = $_POST['nombre_subcategoria'];
    
    $sql = "SELECT c.nombre as categoria_nombre, c.id as categoria_id
            FROM categoria c
            JOIN subcategoria sc ON c.id = sc.id_categoria
            WHERE sc.nombre = :nombre_subcategoria 
            AND sc.estado = 'activa'
            AND c.estado = 'activa'";
    
    $query = $subcategoria->acceso->prepare($sql);
    $query->execute(array(':nombre_subcategoria' => $nombre_subcategoria));
    $categoria_data = $query->fetch(PDO::FETCH_OBJ);
    
    if ($categoria_data) {
        echo json_encode([
            'success' => true,
            'categoria_nombre' => $categoria_data->categoria_nombre,
            'categoria_id' => $categoria_data->categoria_id
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Categoría no encontrada']);
    }
}
?>