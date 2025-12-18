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

// Función para manejar errores consistentemente
function manejarError($mensaje, $codigo = 500) {
    error_log("Error CarritoController: " . $mensaje);
    http_response_code($codigo);
    echo json_encode(['success' => false, 'error' => $mensaje]);
    exit();
}

// Función para enviar respuestas exitosas
function enviarRespuesta($data, $mensaje = null) {
    $respuesta = ['success' => true];
    if ($mensaje) $respuesta['mensaje'] = $mensaje;
    if ($data !== null) $respuesta['data'] = $data;
    echo json_encode($respuesta);
    exit();
}

if ($_POST['funcion'] == 'obtener_carrito') {
    try {
        if (empty($_SESSION['id'])) {
            echo json_encode(['error' => 'no_sesion']);
            exit();
        }

        $id_usuario = $_SESSION['id'];
        $items = $carrito->obtener_carrito($id_usuario);
        
        // Verificar si hay error en la respuesta del modelo
        if ($items === false) {
            throw new Exception("Error al obtener el carrito");
        }

        $json = array();
        foreach ($items as $objeto) {
            $json[] = array(
                'id' => $objeto->id, // ← ID del carrito (IMPORTANTE)
                'cantidad_producto' => $objeto->cantidad_producto,
                'subtotal' => floatval($objeto->subtotal),
                'descuento_unitario' => floatval($objeto->descuento_unitario),
                'stock_disponible' => intval($objeto->stock_disponible),
                'precio_final' => floatval($objeto->precio_final),
                'precio_unitario' => floatval($objeto->precio_unitario),
                'descuento_porcentaje' => floatval($objeto->descuento_porcentaje),
                'nombre' => $objeto->nombre,
                'imagen' => $objeto->imagen ?: 'producto_default.png',
                'detalles' => $objeto->detalles ?: 'Sin descripción adicional',
                'id_producto' => $objeto->id_producto,
                'id_producto_tienda' => $objeto->id_producto_tienda, // ← ID del producto en tienda
                'tienda_nombre' => $objeto->tienda_nombre,
                'marca_nombre' => $objeto->marca_nombre ?: 'Sin marca',
                'costo_envio' => $objeto->costo_envio,
                'envio_gratis' => $objeto->envio_gratis,
                'envio' => $objeto->envio_gratis ? 'Gratis' : 'Con costo'
            );
        }
        
        echo json_encode($json);

    } catch (Exception $e) {
        manejarError('Error al obtener el carrito: ' . $e->getMessage());
    }
}

if ($_POST['funcion'] == 'agregar_al_carrito') {
    error_log("=== INICIO AGREGAR AL CARRITO ===");
    
    if (empty($_SESSION['id'])) {
        error_log("ERROR: Usuario no tiene sesión");
        echo json_encode(['success' => false, 'error' => 'no_sesion']);
        exit();
    }
    
    $id_usuario = $_SESSION['id'];
    
    // Validar parámetros requeridos
    if (empty($_POST['id_producto_tienda'])) {
        echo json_encode(['success' => false, 'error' => 'ID de producto no especificado']);
        exit();
    }
    
    $id_producto_tienda_encrypted = $_POST['id_producto_tienda'];
    $cantidad = intval($_POST['cantidad'] ?? 1);
    
    error_log("Datos recibidos - Usuario: $id_usuario, ProductoEnc: $id_producto_tienda_encrypted, Cantidad: $cantidad");
    
    // Validar cantidad
    if ($cantidad < 1) {
        echo json_encode(['success' => false, 'error' => 'Cantidad inválida']);
        exit();
    }
    
    // Desencriptar ID
    try {
        $formateado = str_replace(" ", "+", $id_producto_tienda_encrypted);
        $id_producto_tienda = openssl_decrypt($formateado, CODE, KEY);
        
        error_log("ID encriptado recibido: " . $id_producto_tienda_encrypted);
        error_log("ID formateado: " . $formateado);
        error_log("ID desencriptado: " . $id_producto_tienda);
        
        if (!is_numeric($id_producto_tienda)) {
            throw new Exception("ID de producto inválido");
        }
        
        $id_producto_tienda = intval($id_producto_tienda);
        
    } catch (Exception $e) {
        error_log("Error desencriptando ID: " . $e->getMessage());
        echo json_encode(['success' => false, 'error' => 'ID de producto inválido']);
        exit();
    }
    
    try {
        error_log("Llamando a modelo->agregar_al_carrito...");
        $resultado = $carrito->agregar_al_carrito($id_usuario, $id_producto_tienda, $cantidad);
        error_log("Resultado del modelo: " . ($resultado ? 'TRUE' : 'FALSE'));
        
        if ($resultado) {
            error_log("ÉXITO: Producto agregado al carrito");
            
            // Obtener la nueva cantidad total para actualizar el badge
            $nueva_cantidad_total = $carrito->obtener_cantidad_total($id_usuario);
            
            echo json_encode([
                'success' => true, 
                'mensaje' => 'Producto agregado al carrito',
                'cantidad_total' => $nueva_cantidad_total
            ]);
        } else {
            error_log("FALLO: Modelo retornó false");
            echo json_encode(['success' => false, 'error' => 'No se pudo agregar el producto al carrito']);
        }
        
    } catch (Exception $e) {
        error_log("EXCEPCIÓN en agregar_al_carrito: " . $e->getMessage());
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    error_log("=== FIN AGREGAR AL CARRITO ===");
}

if ($_POST['funcion'] == 'actualizar_cantidad') {
    try {
        if (empty($_SESSION['id'])) {
            echo json_encode(['success' => false, 'error' => 'no_sesion']);
            exit();
        }

        $id_usuario = $_SESSION['id'];
        $id_carrito = $_POST['id_carrito_detalle'] ?? null;
        $nueva_cantidad = isset($_POST['cantidad']) ? intval($_POST['cantidad']) : null;
        
        error_log("=== ACTUALIZAR CANTIDAD ===");
        error_log("Usuario ID: $id_usuario");
        error_log("Carrito ID recibido: " . ($id_carrito ?? 'NULL'));
        error_log("Nueva cantidad: " . ($nueva_cantidad ?? 'NULL'));
        error_log("POST completo:", $_POST);
        
        // Validaciones
        if (!$id_carrito || $nueva_cantidad === null) {
            error_log("ERROR: Datos incompletos - id_carrito: " . ($id_carrito ? 'Sí' : 'No') . ", cantidad: " . ($nueva_cantidad !== null ? 'Sí' : 'No'));
            echo json_encode(['success' => false, 'error' => 'Datos incompletos']);
            exit();
        }
        
        if ($nueva_cantidad < 1) {
            error_log("ERROR: Cantidad inválida: $nueva_cantidad");
            echo json_encode(['success' => false, 'error' => 'Cantidad inválida']);
            exit();
        }
        
        // Verificar que el item pertenece al usuario
        $sql_verificar = "SELECT c.id FROM carrito c 
                         WHERE c.id = :id_carrito 
                         AND c.id_usuario = :id_usuario";
        $db = new Conexion();
        $acceso = $db->pdo;
        $query = $acceso->prepare($sql_verificar);
        $query->execute([
            ':id_carrito' => $id_carrito,
            ':id_usuario' => $id_usuario
        ]);
        
        if (!$query->fetch()) {
            error_log("ERROR: Item $id_carrito no pertenece al usuario $id_usuario");
            echo json_encode(['success' => false, 'error' => 'Item no encontrado en tu carrito']);
            exit();
        }
        
        error_log("Verificación exitosa, llamando al modelo...");
        $resultado = $carrito->actualizar_cantidad($id_carrito, $nueva_cantidad);
        
        if ($resultado) {
            error_log("ÉXITO: Cantidad actualizada");
            // Obtener cantidad total actualizada
            $cantidad_total = $carrito->obtener_cantidad_total($id_usuario);
            
            echo json_encode([
                'success' => true, 
                'mensaje' => 'Cantidad actualizada',
                'cantidad_total' => $cantidad_total
            ]);
        } else {
            error_log("FALLO: Modelo retornó false");
            echo json_encode(['success' => false, 'error' => 'No se pudo actualizar la cantidad']);
        }
        
    } catch (Exception $e) {
        error_log("EXCEPCIÓN en actualizar_cantidad: " . $e->getMessage());
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}

if ($_POST['funcion'] == 'eliminar_del_carrito') {
    try {
        if (empty($_SESSION['id'])) {
            echo json_encode(['success' => false, 'error' => 'no_sesion']);
            exit();
        }

        $id_usuario = $_SESSION['id'];
        $id_carrito = $_POST['id_carrito_detalle'] ?? null;
        
        error_log("=== ELIMINAR DEL CARRITO ===");
        error_log("Usuario ID: $id_usuario");
        error_log("Carrito ID recibido: " . ($id_carrito ?? 'NULL'));
        
        if (!$id_carrito) {
            error_log("ERROR: ID de item no especificado");
            echo json_encode(['success' => false, 'error' => 'ID de item no especificado']);
            exit();
        }
        
        // Verificar que el item pertenece al usuario
        $sql_verificar = "SELECT c.id FROM carrito c 
                         WHERE c.id = :id_carrito 
                         AND c.id_usuario = :id_usuario";
        $db = new Conexion();
        $acceso = $db->pdo;
        $query = $acceso->prepare($sql_verificar);
        $query->execute([
            ':id_carrito' => $id_carrito,
            ':id_usuario' => $id_usuario
        ]);
        
        if (!$query->fetch()) {
            error_log("ERROR: Item $id_carrito no pertenece al usuario $id_usuario");
            echo json_encode(['success' => false, 'error' => 'Item no encontrado en tu carrito']);
            exit();
        }
        
        error_log("Verificación exitosa, llamando al modelo...");
        $resultado = $carrito->eliminar_del_carrito($id_carrito, $id_usuario);
        
        if ($resultado) {
            error_log("ÉXITO: Producto eliminado del carrito");
            // Obtener cantidad total actualizada
            $cantidad_total = $carrito->obtener_cantidad_total($id_usuario);
            
            echo json_encode([
                'success' => true, 
                'mensaje' => 'Producto eliminado del carrito',
                'cantidad_total' => $cantidad_total
            ]);
        } else {
            error_log("FALLO: Modelo retornó false");
            echo json_encode(['success' => false, 'error' => 'No se pudo eliminar el producto']);
        }
        
    } catch (Exception $e) {
        error_log("EXCEPCIÓN en eliminar_del_carrito: " . $e->getMessage());
        echo json_encode(['success' => false, 'error' => 'Error interno del servidor']);
    }
}

if ($_POST['funcion'] == 'vaciar_carrito') {
    try {
        if (empty($_SESSION['id'])) {
            echo json_encode(['success' => false, 'error' => 'no_sesion']);
            exit();
        }

        $id_usuario = $_SESSION['id'];
        
        $resultado = $carrito->vaciar_carrito($id_usuario);
        
        if ($resultado) {
            echo json_encode([
                'success' => true, 
                'mensaje' => 'Carrito vaciado correctamente',
                'cantidad_total' => 0
            ]);
        } else {
            echo json_encode(['success' => false, 'error' => 'No se pudo vaciar el carrito']);
        }
        
    } catch (Exception $e) {
        error_log("Error en vaciar_carrito: " . $e->getMessage());
        echo json_encode(['success' => false, 'error' => 'Error interno del servidor']);
    }
}

if ($_POST['funcion'] == 'obtener_resumen') {
    try {
        if (empty($_SESSION['id'])) {
            echo json_encode(['error' => 'no_sesion']);
            exit();
        }

        $id_usuario = $_SESSION['id'];
        
        $resumen = $carrito->obtener_resumen_carrito($id_usuario);
        $cantidad_total = $carrito->obtener_cantidad_total($id_usuario);
        
        $json = array(
            'total_items' => intval($resumen->total_items),
            'total_cantidad' => intval($cantidad_total),
            'subtotal' => floatval($resumen->subtotal),
            'descuento_total' => floatval($resumen->descuento_total),
            'total' => floatval($resumen->total)
        );
        
        echo json_encode($json);
        
    } catch (Exception $e) {
        error_log("Error en obtener_resumen: " . $e->getMessage());
        echo json_encode(['error' => 'Error interno del servidor']);
    }
}

if ($_POST['funcion'] == 'obtener_cantidad_total') {
    try {
        if (empty($_SESSION['id'])) {
            echo json_encode(['cantidad_total' => 0]);
            exit();
        }

        $id_usuario = $_SESSION['id'];
        $cantidad_total = $carrito->obtener_cantidad_total($id_usuario);
        
        echo json_encode(['cantidad_total' => intval($cantidad_total)]);
        
    } catch (Exception $e) {
        error_log("Error en obtener_cantidad_total: " . $e->getMessage());
        echo json_encode(['cantidad_total' => 0]);
    }
    exit();
}

// Función adicional para verificar estado del carrito
if ($_POST['funcion'] == 'verificar_estado') {
    try {
        if (empty($_SESSION['id'])) {
            echo json_encode(['sesion_activa' => false]);
            exit();
        }

        $id_usuario = $_SESSION['id'];
        $cantidad_total = $carrito->obtener_cantidad_total($id_usuario);
        $items = $carrito->obtener_carrito($id_usuario);
        
        echo json_encode([
            'sesion_activa' => true,
            'cantidad_total' => intval($cantidad_total),
            'tiene_items' => !empty($items)
        ]);
        
    } catch (Exception $e) {
        error_log("Error en verificar_estado: " . $e->getMessage());
        echo json_encode(['sesion_activa' => false]);
    }
}