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

function desencriptarID($id_encrypted) {
    try {
        if (empty($id_encrypted)) {
            return null;
        }
        
        $formateado = str_replace(" ", "+", $id_encrypted);
        $id = openssl_decrypt($formateado, CODE, KEY);
        
        if (!is_numeric($id)) {
            return null;
        }
        
        return intval($id);
    } catch (Exception $e) {
        error_log("Error desencriptando ID: " . $e->getMessage());
        return null;
    }
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

// CarritoController.php - Agregar después de la última función existente

if ($_POST['funcion'] == 'agregar_bundle_carrito') {
    try {
        error_log("=== INICIO AGREGAR BUNDLE AL CARRITO ===");
        
        if (empty($_SESSION['id'])) {
            error_log("ERROR: Usuario no tiene sesión");
            echo json_encode(['success' => false, 'error' => 'no_sesion']);
            exit();
        }
        
        $id_usuario = $_SESSION['id'];
        
        // DESENCRIPTAR ID DEL BUNDLE
        $bundle_id = null;
        
        if (isset($_POST['id_bundle_encrypted'])) {
            // Desencriptar ID
            $id_bundle_encrypted = $_POST['id_bundle_encrypted'];
            error_log("ID encriptado recibido: " . $id_bundle_encrypted);
            
            $formateado = str_replace(" ", "+", $id_bundle_encrypted);
            $bundle_id = openssl_decrypt($formateado, CODE, KEY);
            
            error_log("ID desencriptado: " . $bundle_id);
            
        } elseif (isset($_POST['bundle_id'])) {
            // ID ya desencriptado
            $bundle_id = $_POST['bundle_id'];
            error_log("ID desencriptado recibido: " . $bundle_id);
        }
        
        if (!$bundle_id || !is_numeric($bundle_id)) {
            error_log("ERROR: Bundle ID inválido: " . ($bundle_id ?? 'NULL'));
            echo json_encode(['success' => false, 'error' => 'Bundle ID inválido']);
            exit();
        }
        
        $bundle_id = intval($bundle_id);
        error_log("Bundle ID final (int): " . $bundle_id);
        
        // Obtener productos del bundle
        $productos_bundle = obtenerProductosBundle($bundle_id);
        
        if (empty($productos_bundle)) {
            error_log("ERROR: Bundle no encontrado o sin productos");
            echo json_encode(['success' => false, 'error' => 'Bundle no encontrado o sin productos']);
            exit();
        }
        
        error_log("Bundle encontrado con " . count($productos_bundle) . " productos");
        
        $productos_agregados = 0;
        $errores = [];
        
        // Agregar cada producto del bundle al carrito
        foreach ($productos_bundle as $producto) {
            try {
                $resultado = $carrito->agregar_al_carrito($id_usuario, $producto['id_producto_tienda'], 1);
                
                if ($resultado) {
                    $productos_agregados++;
                    error_log("Producto {$producto['id_producto_tienda']} agregado exitosamente");
                } else {
                    $errores[] = "Producto {$producto['nombre']} no disponible";
                    error_log("Producto {$producto['id_producto_tienda']} no pudo ser agregado");
                }
            } catch (Exception $e) {
                $errores[] = "Error con {$producto['nombre']}: " . $e->getMessage();
                error_log("Error agregando producto {$producto['id_producto_tienda']}: " . $e->getMessage());
            }
        }
        
        if ($productos_agregados > 0) {
            $cantidad_total = $carrito->obtener_cantidad_total($id_usuario);
            
            $mensaje = "¡Bundle agregado al carrito! ($productos_agregados productos)";
            if (!empty($errores)) {
                $mensaje .= ". Algunos productos no pudieron ser agregados: " . implode(', ', $errores);
            }
            
            error_log("ÉXITO: Bundle agregado - $productos_agregados productos");
            echo json_encode([
                'success' => true,
                'mensaje' => $mensaje,
                'cantidad_total' => $cantidad_total,
                'productos_agregados' => $productos_agregados,
                'total_productos' => count($productos_bundle),
                'errores' => $errores
            ]);
        } else {
            error_log("FALLO: Ningún producto del bundle pudo ser agregado");
            echo json_encode([
                'success' => false,
                'error' => 'No se pudo agregar ningún producto del bundle al carrito. ' . implode(', ', $errores)
            ]);
        }
        
    } catch (Exception $e) {
        error_log("EXCEPCIÓN en agregar_bundle_carrito: " . $e->getMessage());
        echo json_encode(['success' => false, 'error' => 'Error interno del servidor: ' . $e->getMessage()]);
    }
    error_log("=== FIN AGREGAR BUNDLE AL CARRITO ===");
}

// Función para obtener productos de un bundle
if ($_POST['funcion'] == 'obtener_productos_bundle') {
    try {
        $bundle_id = $_POST['bundle_id'] ?? null;
        
        if (!$bundle_id) {
            echo json_encode(['success' => false, 'error' => 'Bundle ID no especificado']);
            exit();
        }
        
        $productos_bundle = obtenerProductosBundle($bundle_id);
        
        if (empty($productos_bundle)) {
            echo json_encode(['success' => false, 'error' => 'Bundle no encontrado o sin productos']);
            exit();
        }
        
        echo json_encode([
            'success' => true,
            'productos' => $productos_bundle,
            'total_productos' => count($productos_bundle)
        ]);
        
    } catch (Exception $e) {
        error_log("Error obteniendo productos bundle: " . $e->getMessage());
        echo json_encode(['success' => false, 'error' => 'Error interno del servidor']);
    }
}

// Función para obtener información detallada del bundle
if ($_POST['funcion'] == 'obtener_info_bundle') {
     if ($_POST['funcion'] == 'obtener_info_bundle') {
    try {
        // Intentar obtener ID encriptado primero
        if (isset($_POST['id_bundle_encrypted'])) {
            $bundle_id = desencriptarID($_POST['id_bundle_encrypted']);
        } elseif (isset($_POST['bundle_id'])) {
            // O intentar ID desencriptado
            $bundle_id = intval($_POST['bundle_id']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Bundle ID no especificado']);
            exit();
        }
        
        if (!$bundle_id) {
            echo json_encode(['success' => false, 'error' => 'Bundle ID inválido']);
            exit();
        }
        
        $bundle_id = intval($bundle_id);
        error_log("Bundle ID convertido a int: " . $bundle_id);
        
        // Obtener información básica del bundle
        $db = new Conexion();
        $acceso = $db->pdo;
        
        $sql = "SELECT 
                    b.id,
                    b.nombre,
                    b.descripcion,
                    b.precio_original,
                    b.precio_oferta,
                    b.descuento_porcentaje,
                    b.stock,
                    b.estado,
                    b.fecha_inicio,
                    b.fecha_fin,
                    b.imagen,
                    t.nombre as tienda_nombre,
                    t.id as tienda_id
                FROM bundle b
                LEFT JOIN tienda t ON b.tienda_id = t.id
                WHERE b.id = :bundle_id
                AND b.estado = 'activo'";
        
        $query = $acceso->prepare($sql);
        $query->execute([':bundle_id' => $bundle_id]);
        $bundle = $query->fetch(PDO::FETCH_ASSOC);
        
        error_log("Resultado de la consulta: " . ($bundle ? 'ENCONTRADO' : 'NO ENCONTRADO'));
        if ($bundle) {
            error_log("Bundle encontrado: " . $bundle['nombre']);
            error_log("Estado: " . $bundle['estado']);
        }
        
        if (!$bundle) {
            // Verificar si existe pero está inactivo
            $sql_inactivo = "SELECT id, nombre, estado FROM bundle WHERE id = :bundle_id";
            $query_inactivo = $acceso->prepare($sql_inactivo);
            $query_inactivo->execute([':bundle_id' => $bundle_id]);
            $bundle_inactivo = $query_inactivo->fetch(PDO::FETCH_ASSOC);
            
            if ($bundle_inactivo) {
                error_log("Bundle existe pero está inactivo. Estado: " . $bundle_inactivo['estado']);
                echo json_encode(['success' => false, 'error' => 'Bundle existe pero está inactivo. Estado: ' . $bundle_inactivo['estado']]);
            } else {
                error_log("Bundle no existe en absoluto");
                echo json_encode(['success' => false, 'error' => 'Bundle no encontrado']);
            }
            exit();
        }
        
        // Calcular precio final
        $precio_final = $bundle['precio_oferta'] > 0 ? $bundle['precio_oferta'] : $bundle['precio_original'];
        $bundle['precio_final'] = floatval($precio_final);
        
        // Calcular ahorro
        $bundle['precio_original'] = floatval($bundle['precio_original']);
        $bundle['precio_oferta'] = floatval($bundle['precio_oferta']);
        $bundle['descuento_porcentaje'] = floatval($bundle['descuento_porcentaje']);
        
        if ($bundle['precio_original'] > 0 && $precio_final < $bundle['precio_original']) {
            $bundle['ahorro'] = $bundle['precio_original'] - $precio_final;
            $bundle['porcentaje_ahorro'] = round(($bundle['ahorro'] / $bundle['precio_original']) * 100, 0);
        } else {
            $bundle['ahorro'] = 0;
            $bundle['porcentaje_ahorro'] = 0;
        }
        
        // Obtener productos del bundle
        $productos = obtenerProductosBundle($bundle_id);
        $bundle['productos'] = $productos;
        $bundle['total_productos'] = count($productos);
        
        // Verificar si el bundle está disponible
        $hoy = date('Y-m-d H:i:s');
        $bundle['disponible'] = true;
        $bundle['mensaje_disponibilidad'] = '';
        
        if ($bundle['fecha_fin'] && $hoy > $bundle['fecha_fin']) {
            $bundle['disponible'] = false;
            $bundle['mensaje_disponibilidad'] = 'Bundle expirado';
        } elseif ($bundle['stock'] <= 0) {
            $bundle['disponible'] = false;
            $bundle['mensaje_disponibilidad'] = 'Bundle agotado';
        } elseif ($bundle['estado'] != 'activo') {
            $bundle['disponible'] = false;
            $bundle['mensaje_disponibilidad'] = 'Bundle no disponible';
        }
        
        echo json_encode([
            'success' => true,
            'bundle' => $bundle
        ]);
        
    } catch (Exception $e) {
        error_log("Error obteniendo info bundle: " . $e->getMessage());
        echo json_encode(['success' => false, 'error' => 'Error interno del servidor: ' . $e->getMessage()]);
    }
}
}


// Función auxiliar para obtener productos de un bundle
function obtenerProductosBundle($bundle_id) {
    $db = new Conexion();
    $acceso = $db->pdo;
    
    $sql = "SELECT 
                bp.producto_tienda_id,
                pt.id as id_producto_tienda,
                p.nombre,
                p.descripcion_corta,
                pt.precio,
                pt.descuento_porcentaje,
                pt.stock,
                pt.estado,
                pi.imagen_url as imagen,
                t.nombre as tienda_nombre,
                m.nombre as marca_nombre,
                (pt.precio - (pt.precio * pt.descuento_porcentaje / 100)) as precio_final
            FROM bundle_producto bp
            JOIN producto_tienda pt ON bp.producto_tienda_id = pt.id
            JOIN producto p ON pt.id_producto = p.id
            LEFT JOIN producto_imagen pi ON p.id = pi.id_producto AND pi.es_principal = 1
            LEFT JOIN tienda t ON pt.id_tienda = t.id
            LEFT JOIN marca m ON p.id_marca = m.id
            WHERE bp.bundle_id = :bundle_id
            AND pt.estado = 'activo'
            AND p.estado = 'activo'";
    
    $query = $acceso->prepare($sql);
    $query->execute([':bundle_id' => $bundle_id]);
    $productos = $query->fetchAll(PDO::FETCH_ASSOC);
    
    // Formatear datos
    foreach ($productos as &$producto) {
        $producto['precio'] = floatval($producto['precio']);
        $producto['descuento_porcentaje'] = floatval($producto['descuento_porcentaje']);
        $producto['precio_final'] = floatval($producto['precio_final']);
        $producto['stock'] = intval($producto['stock']);
        $producto['disponible'] = $producto['stock'] > 0;
        
        // Si no hay imagen, usar default
        if (!$producto['imagen'] || $producto['imagen'] == '') {
            $producto['imagen'] = 'producto_default.png';
        }
    }
    
    return $productos;
}

// Función auxiliar para obtener información del bundle
function obtenerInfoBundle($bundle_id) {
    $db = new Conexion();
    $acceso = $db->pdo;
    
    $sql = "SELECT 
                b.id,
                b.nombre,
                b.descripcion,
                b.precio_original,
                b.precio_oferta,
                b.descuento_porcentaje,
                b.stock,
                b.estado,
                b.fecha_inicio,
                b.fecha_fin,
                b.imagen,
                t.nombre as tienda_nombre
            FROM bundle b
            LEFT JOIN tienda t ON b.id_tienda = t.id
            WHERE b.id = :bundle_id
            AND b.estado = 'activo'";
    
    $query = $acceso->prepare($sql);
    $query->execute([':bundle_id' => $bundle_id]);
    $bundle = $query->fetch(PDO::FETCH_ASSOC);
    
    if ($bundle) {
        // Calcular precio final si hay descuento
        $precio_final = $bundle['precio_oferta'] > 0 ? $bundle['precio_oferta'] : $bundle['precio_original'];
        $bundle['precio_final'] = $precio_final;
        
        // Calcular ahorro
        if ($bundle['precio_original'] > 0 && $bundle['precio_final'] < $bundle['precio_original']) {
            $bundle['ahorro'] = $bundle['precio_original'] - $bundle['precio_final'];
            $bundle['porcentaje_ahorro'] = round(($bundle['ahorro'] / $bundle['precio_original']) * 100, 0);
        } else {
            $bundle['ahorro'] = 0;
            $bundle['porcentaje_ahorro'] = 0;
        }
        
        // Contar productos en el bundle
        $sql_count = "SELECT COUNT(*) as total FROM bundle_producto WHERE id_bundle = :bundle_id";
        $query_count = $acceso->prepare($sql_count);
        $query_count->execute([':bundle_id' => $bundle_id]);
        $count = $query_count->fetch();
        $bundle['total_productos_bundle'] = $count['total'];
    }
    
    return $bundle;
}