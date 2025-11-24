<?php
include_once '../Models/Moneda.php';

$moneda = new Moneda();

session_start();

if ($_POST['funcion'] == 'obtener_monedas') {
    try {
        $moneda->obtener_monedas();
        $monedas = [];
        
        foreach ($moneda->objetos as $moneda_obj) {
            $monedas[] = [
                'id' => $moneda_obj->id,
                'codigo' => $moneda_obj->codigo,
                'nombre' => $moneda_obj->nombre,
                'simbolo' => $moneda_obj->simbolo,
                'tasa_cambio' => $moneda_obj->tasa_cambio,
                'predeterminada' => $moneda_obj->predeterminada ?? 0
            ];
        }
        
        echo json_encode([
            'success' => true,
            'monedas' => $monedas
        ]);
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'error' => 'Error al cargar monedas: ' . $e->getMessage()
        ]);
    }
}

if ($_POST['funcion'] == 'obtener_tasa_cambio') {
    $codigo_moneda = $_POST['moneda'];
    
    try {
        $moneda->obtener_tasa_cambio($codigo_moneda);
        
        if (!empty($moneda->objetos)) {
            $moneda_obj = $moneda->objetos; // Esto es un array asociativo
            
            echo json_encode([
                'success' => true,
                'tasa_cambio' => $moneda_obj['tasa_cambio'],
                'moneda' => [ // Cambiado a array asociativo explícito
                    'codigo' => $moneda_obj['codigo'],
                    'nombre' => $moneda_obj['nombre'],
                    'simbolo' => $moneda_obj['simbolo']
                ]
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'error' => 'Moneda no encontrada'
            ]);
        }
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'error' => 'Error al obtener tasa de cambio: ' . $e->getMessage()
        ]);
    }
}

if ($_POST['funcion'] == 'convertir_precios_productos') {
    $codigo_moneda = $_POST['moneda'];
    $productos = isset($_POST['productos']) ? json_decode($_POST['productos'], true) : [];
    
    try {
        // Si no se enviaron productos específicos, obtener todos los productos visibles
        if (empty($productos)) {
            // Aquí deberías incluir tu modelo de productos y obtenerlos
            include_once '../Models/ProductoTienda.php';
            $producto_model = new ProductoTienda();
            $productos_data = $producto_model->llenar_productos(); // Ajusta según tu método
            
            // Convertir a array si es necesario
            $productos = [];
            foreach ($productos_data as $prod) {
                $productos[] = [
                    'id' => $prod->id,
                    'producto' => $prod->producto,
                    'precio' => $prod->precio,
                    'precio_descuento' => $prod->precio_descuento,
                    'descuento' => $prod->descuento,
                    'marca' => $prod->marca,
                    'imagen' => $prod->imagen
                ];
            }
        }
        
        // Convertir precios
        $productos_convertidos = $moneda->convertir_precio_productos($productos, $codigo_moneda);
        
        echo json_encode([
            'success' => true,
            'productos' => $productos_convertidos,
            'moneda' => [
                'codigo' => $codigo_moneda,
                'simbolo' => $productos_convertidos[0]['simbolo_moneda'] ?? '$'
            ]
        ]);
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'error' => 'Error al convertir precios: ' . $e->getMessage()
        ]);
    }
}