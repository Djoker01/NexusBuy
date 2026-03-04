<?php
// ✅ AGREGAR AL INICIO - Headers y manejo de errores
header('Content-Type: application/json; charset=utf-8');
error_reporting(0); // Desactivar errores PHP en producción

// Iniciar output buffering para capturar cualquier salida accidental
ob_start();

try {
    include_once '../Models/Moneda.php';
    $moneda = new Moneda();

    session_start();

    // Verificar que sea una petición POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método no permitido');
    }

    // Verificar que exista la función
    if (!isset($_POST['funcion'])) {
        throw new Exception('Función no especificada');
    }

    $funcion = $_POST['funcion'];

    switch ($funcion) {
        case 'obtener_monedas':
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
            break;

        case 'obtener_tasa_cambio':
            $codigo_moneda = $_POST['moneda'] ?? 'CUP';
            
            $moneda->obtener_tasa_cambio($codigo_moneda);
            
            if (!empty($moneda->objetos)) {
                $moneda_obj = $moneda->objetos; 
                
                echo json_encode([
                    'success' => true,
                    'tasa_cambio' => $moneda_obj['tasa_cambio'] ?? 1.0,
                    'moneda' => $moneda_obj 
                ]);
            } else {
                // Si no encuentra la moneda, usar CUP por defecto
                $moneda->obtener_tasa_cambio('CUP');
                $moneda_obj = $moneda->objetos;
                
                echo json_encode([
                    'success' => true,
                    'tasa_cambio' => $moneda_obj['tasa_cambio'] ?? 1.0,
                    'moneda' => $moneda_obj
                ]);
            }
            break;

        case 'convertir_precios_productos':
            $codigo_moneda = $_POST['moneda'] ?? 'CUP';
            $productos = isset($_POST['productos']) ? json_decode($_POST['productos'], true) : [];
            
            // Si no se enviaron productos específicos, devolver ejemplo
            if (empty($productos)) {
                // Datos de ejemplo para pruebas
                $productos_ejemplo = [
                    [
                        'id' => 1,
                        'producto' => 'Producto Ejemplo',
                        'precio' => 100.00,
                        'precio_descuento' => 85.00,
                        'descuento' => 15,
                        'marca' => 'Marca Ejemplo',
                        'imagen' => 'default.png'
                    ]
                ];
                
                // Simular conversión
                $productos_convertidos = [];
                foreach ($productos_ejemplo as $prod) {
                    $productos_convertidos[] = [
                        'id' => $prod['id'],
                        'producto' => $prod['producto'],
                        'precio_convertido' => $prod['precio'],
                        'precio_descuento_convertido' => $prod['precio_descuento'],
                        'descuento' => $prod['descuento'],
                        'marca' => $prod['marca'],
                        'imagen' => $prod['imagen'],
                        'simbolo_moneda' => '$'
                    ];
                }
                
                echo json_encode([
                    'success' => true,
                    'productos' => $productos_convertidos,
                    'moneda' => [
                        'codigo' => $codigo_moneda,
                        'simbolo' => '$'
                    ]
                ]);
            } else {
                // Lógica real de conversión
                $productos_convertidos = $moneda->convertir_precio_productos($productos, $codigo_moneda);
                
                echo json_encode([
                    'success' => true,
                    'productos' => $productos_convertidos,
                    'moneda' => [
                        'codigo' => $codigo_moneda,
                        'simbolo' => $productos_convertidos[0]['simbolo_moneda'] ?? '$'
                    ]
                ]);
            }
            break;

        default:
            echo json_encode([
                'success' => false,
                'error' => 'Función no reconocida: ' . $funcion
            ]);
            break;
    }
    
} catch (Exception $e) {
    // Limpiar cualquier output previo
    ob_end_clean();
    
    echo json_encode([
        'success' => false,
        'error' => 'Error en el servidor: ' . $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
} finally {
    // Asegurar que no haya output adicional
    if (ob_get_length()) {
        ob_end_flush();
    }
}
?>