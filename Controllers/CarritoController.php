<?php
include_once '../Models/Carrito.php';
include_once '../Util/Config/config.php';
$carrito = new Carrito();
session_start();

// Headers para JSON
header('Content-Type: application/json');

// Verificar método POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit();
}

// Verificar que la función existe
if (!isset($_POST['funcion'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Función no especificada']);
    exit();
}

if ($_POST['funcion'] == 'obtener_carrito') {
    try {
        if (!empty($_SESSION['id'])) {
            $id_usuario = $_SESSION['id'];
            $carrito->obtener_carrito($id_usuario);
            
            $json = array();
            foreach ($carrito->objetos as $objeto) {
                $json[] = array(
                    'id' => $objeto->id,
                    'cantidad_producto' => $objeto->cantidad_producto,
                    'subtotal' => $objeto->subtotal,
                    'descuento_final' => $objeto->descuento_final,
                    'cantidad' => $objeto->cantidad,
                    'precio' => $objeto->precio_final,
                    'precio_unitario' => $objeto->precio_unitario,
                    'descuento_porcentaje' => $objeto->descuento_porcentaje,
                    'nombre' => $objeto->nombre,
                    'imagen' => $objeto->imagen,
                    'detalles' => $objeto->detalles,
                    'id_producto' => $objeto->id_producto,
                    'id_producto_tienda' => $objeto->id_producto_tienda,
                    'tienda_nombre' => $objeto->tienda_nombre,
                    'marca_nombre' => $objeto->marca_nombre,
                    'envio' => $objeto->envio
                );
            }
            
            echo json_encode($json);
        } else {
            echo json_encode(['error' => 'no_sesion']);
        }
    } catch (Exception $e) {
        error_log("Error en obtener_carrito: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'Error interno del servidor']);
    }
}

if ($_POST['funcion'] == 'agregar_al_carrito') {
    // Log inicial
    error_log("=== INICIO AGREGAR AL CARRITO ===");
    error_log("SESSION ID: " . ($_SESSION['id'] ?? 'NO_SESION'));
    
    if (empty($_SESSION['id'])) {
        error_log("ERROR: Usuario no tiene sesión");
        echo json_encode(['success' => false, 'error' => 'no_sesion']);
        exit();
    }
    
    $id_usuario = $_SESSION['id'];
    $id_producto_tienda_encrypted = $_POST['id_producto_tienda'];
    $cantidad = intval($_POST['cantidad'] ?? 1);
    
    error_log("Datos recibidos - Usuario: $id_usuario, Productoenc: $id_producto_tienda_encrypted, Cantidad: $cantidad");
    
    // Desencriptar ID
    $id_producto_tienda = openssl_decrypt($id_producto_tienda_encrypted, CODE, KEY);
    error_log("Primer intento desencriptar: $id_producto_tienda");
    
    if (!is_numeric($id_producto_tienda)) {
        $id_producto_tienda_encrypted_fixed = str_replace(" ", "+", $id_producto_tienda_encrypted);
        $id_producto_tienda = openssl_decrypt($id_producto_tienda_encrypted_fixed, CODE, KEY);
        error_log("Segundo intento desencriptar: $id_producto_tienda");
    }
    
    error_log("ID final desencriptado: $id_producto_tienda, Es numérico: " . (is_numeric($id_producto_tienda) ? 'SI' : 'NO'));
    
    if (is_numeric($id_producto_tienda) && $cantidad >= 1) {
        try {
            error_log("Llamando a modelo->agregar_al_carrito...");
            $resultado = $carrito->agregar_al_carrito($id_usuario, $id_producto_tienda, $cantidad);
            error_log("Resultado del modelo: " . ($resultado ? 'TRUE' : 'FALSE'));
            
            if ($resultado) {
                error_log("ÉXITO: Producto agregado al carrito");
                echo json_encode(['success' => true, 'mensaje' => 'Producto agregado al carrito']);
            } else {
                error_log("FALLO: Modelo retornó false");
                
                // Debug adicional para ver qué hay en el carrito
                $carrito->obtener_carrito($id_usuario);
                error_log("Items en carrito: " . count($carrito->objetos));
                
                $ya_en_carrito = false;
                foreach ($carrito->objetos as $item) {
                    error_log("Item en carrito - ID: {$item->id_producto_tienda}, Nombre: {$item->nombre}");
                    if ($item->id_producto_tienda == $id_producto_tienda) {
                        $ya_en_carrito = true;
                        break;
                    }
                }
                
                if ($ya_en_carrito) {
                    error_log("Producto ya está en carrito");
                    echo json_encode(['success' => false, 'error' => 'El producto ya está en tu carrito']);
                } else {
                    error_log("Error desconocido al agregar");
                    echo json_encode(['success' => false, 'error' => 'No se pudo agregar el producto al carrito']);
                }
            }
        } catch (Exception $e) {
            error_log("EXCEPCIÓN en agregar_al_carrito: " . $e->getMessage());
            echo json_encode(['success' => false, 'error' => 'Error: ' . $e->getMessage()]);
        }
    } else {
        error_log("ERROR: Datos inválidos - ID: $id_producto_tienda, Cantidad: $cantidad");
        echo json_encode(['success' => false, 'error' => 'Datos inválidos o ID de producto incorrecto']);
    }
    error_log("=== FIN AGREGAR AL CARRITO ===");
}

if ($_POST['funcion'] == 'actualizar_cantidad') {
    try {
        if (!empty($_SESSION['id'])) {
            $id_carrito_detalle = $_POST['id_carrito_detalle'] ?? null;
            $nueva_cantidad = isset($_POST['cantidad']) ? intval($_POST['cantidad']) : null;
            
            if (!$id_carrito_detalle || $nueva_cantidad === null) {
                echo json_encode(['error' => 'Datos incompletos']);
                exit();
            }
            
            if ($nueva_cantidad <= 0) {
                echo json_encode(['error' => 'Cantidad inválida']);
                exit();
            }
            
            $resultado = $carrito->actualizar_cantidad($id_carrito_detalle, $nueva_cantidad);
            
            if ($resultado) {
                echo json_encode(['success' => 'Cantidad actualizada']);
            } else {
                echo json_encode(['error' => 'No se pudo actualizar la cantidad']);
            }
        } else {
            echo json_encode(['error' => 'no_sesion']);
        }
    } catch (Exception $e) {
        error_log("Error en actualizar_cantidad: " . $e->getMessage());
        echo json_encode(['error' => $e->getMessage()]);
    }
}

if ($_POST['funcion'] == 'eliminar_del_carrito') {
    try {
        if (!empty($_SESSION['id'])) {
            $id_usuario = $_SESSION['id'];
            $id_carrito_detalle = $_POST['id_carrito_detalle'] ?? null;
            
            if (!$id_carrito_detalle) {
                echo json_encode(['error' => 'ID de item no especificado']);
                exit();
            }
            
            $resultado = $carrito->eliminar_del_carrito($id_carrito_detalle, $id_usuario);
            
            if ($resultado) {
                echo json_encode(['success' => 'Producto eliminado del carrito']);
            } else {
                echo json_encode(['error' => 'No se pudo eliminar el producto']);
            }
        } else {
            echo json_encode(['error' => 'no_sesion']);
        }
    } catch (Exception $e) {
        error_log("Error en eliminar_del_carrito: " . $e->getMessage());
        echo json_encode(['error' => 'Error interno del servidor']);
    }
}

if ($_POST['funcion'] == 'vaciar_carrito') {
    try {
        if (!empty($_SESSION['id'])) {
            $id_usuario = $_SESSION['id'];
            
            $resultado = $carrito->vaciar_carrito($id_usuario);
            
            if ($resultado) {
                echo json_encode(['success' => 'Carrito vaciado correctamente']);
            } else {
                echo json_encode(['error' => 'No se pudo vaciar el carrito']);
            }
        } else {
            echo json_encode(['error' => 'no_sesion']);
        }
    } catch (Exception $e) {
        error_log("Error en vaciar_carrito: " . $e->getMessage());
        echo json_encode(['error' => 'Error interno del servidor']);
    }
}

if ($_POST['funcion'] == 'obtener_resumen') {
    try {
        if (!empty($_SESSION['id'])) {
            $id_usuario = $_SESSION['id'];
            
            $resumen = $carrito->obtener_resumen_carrito($id_usuario);
            $cantidad_total = $carrito->obtener_cantidad_total($id_usuario);
            
            $json = array(
                'total_items' => $resumen->total_items,
                'total_cantidad' => $cantidad_total,
                'subtotal' => floatval($resumen->subtotal),
                'descuento_total' => floatval($resumen->descuento_total),
                'total' => floatval($resumen->total)
            );
            
            echo json_encode($json);
        } else {
            echo json_encode(['error' => 'no_sesion']);
        }
    } catch (Exception $e) {
        error_log("Error en obtener_resumen: " . $e->getMessage());
        echo json_encode(['error' => 'Error interno del servidor']);
    }
}

// Función para obtener cantidad total de items en el carrito (útil para el badge)
if ($_POST['funcion'] == 'obtener_cantidad_total') {
    try {
        if (!empty($_SESSION['id'])) {
            $id_usuario = $_SESSION['id'];
            $cantidad_total = $carrito->obtener_cantidad_total($id_usuario);
            
            echo json_encode(['cantidad_total' => $cantidad_total]);
        } else {
            echo json_encode(['cantidad_total' => 0]);
        }
    } catch (Exception $e) {
        error_log("Error en obtener_cantidad_total: " . $e->getMessage());
        echo json_encode(['cantidad_total' => 0]);
    }
}