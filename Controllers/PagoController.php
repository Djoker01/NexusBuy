<?php
include_once '../Models/Orden.php';
include_once '../Models/MetodoPago.php';
include_once '../Models/Carrito.php';
include_once '../Models/Historial.php';
include_once '../Util/Config/config.php';

$orden = new Orden();
$metodo_pago = new MetodoPago();
$carrito = new Carrito();
$historial = new Historial();

// INICIO DE SESIÓN UNA SOLA VEZ AL PRINCIPIO
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

// ==============================================
// FUNCIONES AUXILIARES DE VALIDACIÓN Y SEGURIDAD
// ==============================================

/**
 * Validar y sanitizar datos del método de pago
 */
function validar_y_sanitizar_datos_pago($datos) {
    if (!is_array($datos)) {
        throw new Exception('Datos de pago inválidos');
    }
    
    $tipos_validos = ['tarjeta_credito', 'transferencia', 'paypal', 'efectivo'];
    
    // Validar tipo de pago
    if (empty($datos['tipo']) || !in_array($datos['tipo'], $tipos_validos)) {
        throw new Exception('Método de pago inválido o no especificado');
    }
    
    $sanitized = [
        'tipo' => $datos['tipo'],
        'titular' => isset($datos['titular']) ? htmlspecialchars(trim($datos['titular']), ENT_QUOTES, 'UTF-8') : ''
    ];
    
    // Validaciones específicas por tipo
    switch($datos['tipo']) {
        case 'tarjeta_credito':
            if (empty($datos['numero'])) {
                throw new Exception('Número de tarjeta requerido');
            }
            
            // Limpiar y validar número de tarjeta
            $numero = preg_replace('/\D/', '', $datos['numero']);
            if (strlen($numero) < 15 || strlen($numero) > 19 || !preg_match('/^\d+$/', $numero)) {
                throw new Exception('Número de tarjeta inválido');
            }
            
            // Validar algoritmo de Luhn para tarjetas de crédito
            if (!validar_algoritmo_luhn($numero)) {
                throw new Exception('Número de tarjeta inválido (fallo en validación)');
            }
            
            // Validar fecha de vencimiento (formato MM/YY)
            if (!empty($datos['fecha_vencimiento'])) {
                if (!preg_match('/^(0[1-9]|1[0-2])\/([0-9]{2})$/', $datos['fecha_vencimiento'])) {
                    throw new Exception('Fecha de vencimiento inválida. Use formato MM/YY');
                }
                
                list($mes, $ano) = explode('/', $datos['fecha_vencimiento']);
                $anoActual = date('y');
                $mesActual = date('m');
                
                if ($ano < $anoActual || ($ano == $anoActual && $mes < $mesActual)) {
                    throw new Exception('La tarjeta ha expirado');
                }
            }
            
            // Validar CVV (3-4 dígitos)
            if (!empty($datos['cvv'])) {
                if (!preg_match('/^\d{3,4}$/', $datos['cvv'])) {
                    throw new Exception('CVV inválido (debe tener 3 o 4 dígitos)');
                }
                $sanitized['cvv'] = $datos['cvv'];
            }
            
            $sanitized['numero'] = $numero;
            $sanitized['fecha_vencimiento'] = $datos['fecha_vencimiento'] ?? null;
            break;
            
        case 'paypal':
            if (empty($datos['paypal_email'])) {
                throw new Exception('Email de PayPal requerido');
            }
            
            if (!filter_var($datos['paypal_email'], FILTER_VALIDATE_EMAIL)) {
                throw new Exception('Email de PayPal inválido');
            }
            
            $sanitized['paypal_email'] = filter_var($datos['paypal_email'], FILTER_SANITIZE_EMAIL);
            break;
            
        case 'transferencia':
            if (empty($datos['banco'])) {
                throw new Exception('Nombre del banco requerido');
            }
            
            if (empty($datos['numero_cuenta'])) {
                throw new Exception('Número de cuenta requerido');
            }
            
            $sanitized['banco'] = htmlspecialchars(trim($datos['banco']), ENT_QUOTES, 'UTF-8');
            $sanitized['numero_cuenta'] = preg_replace('/\D/', '', $datos['numero_cuenta']);
            break;
            
        case 'efectivo':
            // No se necesitan datos adicionales para efectivo
            break;
    }
    
    return $sanitized;
}

/**
 * Validar número de tarjeta con algoritmo de Luhn
 */
function validar_algoritmo_luhn($numero) {
    $numero = strrev(preg_replace('/[^\d]/', '', $numero));
    $sum = 0;
    
    for ($i = 0, $j = strlen($numero); $i < $j; $i++) {
        if (($i % 2) == 0) {
            $val = $numero[$i];
        } else {
            $val = $numero[$i] * 2;
            if ($val > 9) {
                $val -= 9;
            }
        }
        $sum += $val;
    }
    
    return (($sum % 10) == 0);
}

/**
 * Función para encriptar datos sensibles usando las constantes definidas
 */
function encriptar_datos_sensibles($datos) {
    // Usar las constantes definidas en config.php
    $key = defined('KEY') ? KEY : 'nexusbuy';
    $code = defined('CODE') ? CODE : 'AES-128-ECB';
    
    if (isset($datos['numero'])) {
        $datos['numero_encriptado'] = openssl_encrypt(
            $datos['numero'], 
            $code, 
            $key, 
            OPENSSL_RAW_DATA
        );
        // Codificar en base64 para almacenamiento seguro
        $datos['numero_encriptado'] = base64_encode($datos['numero_encriptado']);
        unset($datos['numero']); // Eliminar número original
    }
    
    if (isset($datos['cvv'])) {
        // Para CVV usamos hash en lugar de encriptación reversible
        $datos['cvv_hash'] = password_hash($datos['cvv'], PASSWORD_BCRYPT);
        unset($datos['cvv']); // Eliminar CVV original
    }
    
    // Encriptar número de cuenta si existe
    if (isset($datos['numero_cuenta'])) {
        $datos['numero_cuenta_encriptado'] = base64_encode(openssl_encrypt(
            $datos['numero_cuenta'], 
            $code, 
            $key, 
            OPENSSL_RAW_DATA
        ));
        unset($datos['numero_cuenta']); // Eliminar número de cuenta original
    }
    
    return $datos;
}

/**
 * Función para desencriptar datos (solo para uso interno/backoffice)
 */
function desencriptar_datos($texto_encriptado) {
    if (empty($texto_encriptado)) {
        return '';
    }
    
    $key = defined('KEY') ? KEY : 'nexusbuy';
    $code = defined('CODE') ? CODE : 'AES-128-ECB';
    
    try {
        $texto_decodificado = base64_decode($texto_encriptado);
        return openssl_decrypt($texto_decodificado, $code, $key, OPENSSL_RAW_DATA);
    } catch (Exception $e) {
        error_log("Error desencriptando datos: " . $e->getMessage());
        return '';
    }
}

/**
 * Enmascarar datos sensibles para mostrar en frontend
 */
function enmascarar_dato_sensible($dato, $tipo = 'tarjeta') {
    if (empty($dato)) {
        return '';
    }
    
    switch($tipo) {
        case 'tarjeta':
            if (strlen($dato) >= 4) {
                return '**** **** **** ' . substr($dato, -4);
            }
            return '****';
            
        case 'cuenta_bancaria':
            if (strlen($dato) >= 4) {
                return '****' . substr($dato, -4);
            }
            return '****';
            
        case 'email':
            $parts = explode('@', $dato);
            if (count($parts) == 2) {
                $username = $parts[0];
                $domain = $parts[1];
                if (strlen($username) > 2) {
                    $masked = substr($username, 0, 1) . '***' . substr($username, -1);
                } else {
                    $masked = '***';
                }
                return $masked . '@' . $domain;
            }
            return '***@***';
            
        default:
            return '****';
    }
}

// ==============================================
// ENDPOINTS DEL CONTROLADOR
// ==============================================

if ($_POST['funcion'] == 'procesar_pago') {
    try {
        // 1. VERIFICAR SESIÓN
        if (empty($_SESSION['id'])) {
            echo json_encode([
                'success' => false, 
                'error' => 'no_sesion', 
                'message' => 'Debes iniciar sesión para realizar el pago'
            ]);
            exit();
        }

        $id_usuario = $_SESSION['id'];
        
        // 2. VALIDAR DATOS
        if (empty($_POST['direccion_envio']) || empty($_POST['items_seleccionados'])) {
            throw new Exception('Datos incompletos');
        }
        
        $direccion_envio = filter_var($_POST['direccion_envio'], FILTER_SANITIZE_STRING);
        $items_seleccionados = json_decode($_POST['items_seleccionados'], true);
        
        if (!is_array($items_seleccionados) || count($items_seleccionados) === 0) {
            throw new Exception('No hay productos seleccionados');
        }

        // 3. OBTENER TOTALES
        $subtotal = isset($_POST['subtotal']) ? floatval($_POST['subtotal']) : 0;
        $envio = isset($_POST['envio']) ? floatval($_POST['envio']) : 0;
        $total = isset($_POST['total']) ? floatval($_POST['total']) : 0;
        
        if ($total <= 0) {
            throw new Exception('Monto total inválido');
        }
        
        // 4. VERIFICAR STOCK ANTES DE PROCESAR
        $stock_disponible = true;
        $productos_sin_stock = [];
        
        foreach ($items_seleccionados as $item_id) {
            // Obtener información del producto en carrito
            $sql_carrito = "SELECT c.cantidad, pt.id as id_producto_tienda, pt.stock, p.nombre
                           FROM carrito c
                           JOIN producto_tienda pt ON c.id_producto_tienda = pt.id
                           JOIN producto p ON pt.id_producto = p.id
                           WHERE c.id = :item_id AND c.id_usuario = :id_usuario";
            
            $query = $orden->acceso->prepare($sql_carrito);
            $query->execute([':item_id' => $item_id, ':id_usuario' => $id_usuario]);
            $producto = $query->fetch();
            
            if (!$producto) {
                throw new Exception("Producto no encontrado en el carrito");
            }
            
            if ($producto->stock < $producto->cantidad) {
                $stock_disponible = false;
                $productos_sin_stock[] = [
                    'nombre' => $producto->nombre,
                    'solicitado' => $producto->cantidad,
                    'disponible' => $producto->stock
                ];
            }
        }
        
        if (!$stock_disponible) {
            $error_msg = "Stock insuficiente para: ";
            foreach ($productos_sin_stock as $prod) {
                $error_msg .= "{$prod['nombre']} (solicitaste: {$prod['solicitado']}, disponible: {$prod['disponible']}); ";
            }
            throw new Exception($error_msg);
        }
        
        // 5. CREAR ORDEN
        $resultado_orden = $orden->crear_orden($id_usuario, $subtotal, $envio, 0, $total, $direccion_envio);
        
        if (!$resultado_orden['success']) {
            throw new Exception("Error al crear orden: " . $resultado_orden['error']);
        }
        
        $id_orden = $resultado_orden['id_orden'];
        $numero_orden = $resultado_orden['numero_orden'];
        
        // 6. PROCESAR ITEMS
        $items_procesados = 0;
        
        foreach ($items_seleccionados as $item_id) {
            // Obtener información completa del producto
            $sql_item = "SELECT c.*, pt.precio, pt.descuento_porcentaje, pt.id as id_producto_tienda, 
                        (pt.precio - (pt.precio * pt.descuento_porcentaje / 100)) as precio_final,
                        p.nombre
                        FROM carrito c
                        JOIN producto_tienda pt ON c.id_producto_tienda = pt.id
                        JOIN producto p ON pt.id_producto = p.id
                        WHERE c.id = :item_id AND c.id_usuario = :id_usuario";
            
            $query = $orden->acceso->prepare($sql_item);
            $query->execute([':item_id' => $item_id, ':id_usuario' => $id_usuario]);
            $item = $query->fetch();
            
            if (!$item) {
                continue; // Saltar si no se encuentra
            }
            
            // Calcular precios
            $precio_unitario = $item->precio;
            $descuento_porcentaje = $item->descuento_porcentaje;
            $precio_final = $item->precio_final;
            $cantidad = $item->cantidad;
            $subtotal_item = $precio_final * $cantidad;
            
            // Descuento unitario
            $descuento_unitario = ($descuento_porcentaje > 0) ? 
                ($precio_unitario * $descuento_porcentaje / 100) : 0;
            
            // Agregar a orden_detalle
            $detalle_ok = $orden->agregar_detalle_orden(
                $id_orden,
                $item->id_producto_tienda,
                $cantidad,
                $precio_unitario,
                $descuento_unitario,
                $subtotal_item
            );
            
            if ($detalle_ok) {
                // Actualizar stock
                $sql_update = "UPDATE producto_tienda 
                              SET stock = stock - :cantidad,
                                  total_ventas = total_ventas + :cantidad,
                                  fecha_actualizacion = NOW()
                              WHERE id = :id";
                
                $query_update = $orden->acceso->prepare($sql_update);
                $query_update->execute([
                    ':cantidad' => $cantidad,
                    ':id' => $item->id_producto_tienda
                ]);
                
                // Eliminar del carrito
                $sql_delete = "DELETE FROM carrito WHERE id = :id AND id_usuario = :usuario";
                $query_delete = $orden->acceso->prepare($sql_delete);
                $query_delete->execute([
                    ':id' => $item_id,
                    ':usuario' => $id_usuario
                ]);
                
                $items_procesados++;
            }
        }
        
        if ($items_procesados == 0) {
            throw new Exception('No se pudieron procesar los productos');
        }
        
        // 7. REGISTRAR TRANSACCIÓN DE PAGO (opcional)
        try {
            $sql_trans = "INSERT INTO transaccion_pago 
                         (id_orden, id_metodo_pago, referencia_externa, monto, moneda, estado)
                         VALUES (:id_orden, NULL, :referencia, :monto, 'CUP', 'completada')";
            
            $query_trans = $orden->acceso->prepare($sql_trans);
            $query_trans->execute([
                ':id_orden' => $id_orden,
                ':referencia' => $numero_orden,
                ':monto' => $total
            ]);
        } catch (Exception $e) {
            // No es crítico, solo registrar error
            error_log("Advertencia: No se pudo registrar transacción: " . $e->getMessage());
        }
        
        // 8. REGISTRAR EN HISTORIAL
        try {
            $descripcion = "Realizó una compra por CUP {$total}. Orden: {$numero_orden}";
            $historial->crear_historial($descripcion, 2, 2, $id_usuario);
        } catch (Exception $e) {
            // No es crítico
        }
        
        // 9. RESPUESTA EXITOSA
        echo json_encode([
            'success' => true,
            'numero_orden' => $numero_orden,
            'id_orden' => $id_orden,
            'total' => $total,
            'items_procesados' => $items_procesados,
            'fecha' => date('Y-m-d H:i:s'),
            'message' => '¡Compra realizada con éxito! Tu orden #' . $numero_orden . ' ha sido procesada.'
        ]);
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false, 
            'error' => $e->getMessage(),
            'codigo' => 'ERROR_PROCESAMIENTO'
        ]);
        error_log("PagoController Error: " . $e->getMessage());
    }
    exit();
}

if ($_POST['funcion'] == 'obtener_metodos_pago') {
    try {
        if (empty($_SESSION['id'])) {
            echo json_encode([
                'success' => false, 
                'error' => 'no_sesion',
                'message' => 'Sesión no válida'
            ]);
            exit();
        }
        
        $id_usuario = $_SESSION['id'];
        
        // Asegurar que obtenemos un array
        $metodos = $metodo_pago->obtener_metodos_usuario($id_usuario);
        
        // DEBUG: Log para ver qué devuelve realmente
        error_log("Métodos obtenidos para usuario {$id_usuario}: " . print_r($metodos, true));
        
        // Verificar que $metodos sea un array
        if (!is_array($metodos)) {
            // Si no es array, crear array vacío
            $metodos = [];
        }
        
        // Preparar respuesta segura
        $metodos_seguros = [];
        
        // Asegurarnos de iterar correctamente
        if (is_array($metodos) && count($metodos) > 0) {
            foreach ($metodos as $metodo) {
                // Convertir a array si es objeto
                $metodo_array = (array)$metodo;
                
                $metodo_seguro = [
                    'id' => $metodo_array['id'] ?? '',
                    'tipo' => $metodo_array['tipo'] ?? '',
                    'titular' => $metodo_array['titular'] ?? '',
                    'predeterminado' => isset($metodo_array['predeterminado']) ? (int)$metodo_array['predeterminado'] : 0,
                    'fecha_creacion' => $metodo_array['fecha_creacion'] ?? '',
                    'estado' => $metodo_array['estado'] ?? 'A'
                ];
                
                // Enmascarar datos según tipo
                if ($metodo_array['tipo'] == 'tarjeta_credito') {
                    if (!empty($metodo_array['numero'])) {
                        // Intentar desencriptar para enmascarar
                        try {
                            $numero_desencriptado = desencriptar_datos($metodo_array['numero']);
                            $metodo_seguro['numero_enmascarado'] = enmascarar_dato_sensible($numero_desencriptado, 'tarjeta');
                        } catch (Exception $e) {
                            $metodo_seguro['numero_enmascarado'] = '**** **** **** ****';
                        }
                    }
                    if (!empty($metodo_array['fecha_vencimiento'])) {
                        $metodo_seguro['fecha_vencimiento'] = $metodo_array['fecha_vencimiento'];
                    }
                } 
                elseif ($metodo_array['tipo'] == 'paypal') {
                    if (!empty($metodo_array['paypal_email'])) {
                        $metodo_seguro['email_enmascarado'] = enmascarar_dato_sensible($metodo_array['paypal_email'], 'email');
                    }
                } 
                elseif ($metodo_array['tipo'] == 'transferencia') {
                    if (!empty($metodo_array['banco'])) {
                        $metodo_seguro['banco'] = $metodo_array['banco'];
                    }
                    if (!empty($metodo_array['numero_cuenta'])) {
                        try {
                            $cuenta_desencriptada = desencriptar_datos($metodo_array['numero_cuenta']);
                            $metodo_seguro['cuenta_enmascarada'] = enmascarar_dato_sensible($cuenta_desencriptada, 'cuenta_bancaria');
                        } catch (Exception $e) {
                            $metodo_seguro['cuenta_enmascarada'] = '****';
                        }
                    }
                }
                
                $metodos_seguros[] = $metodo_seguro;
            }
        }
        
        echo json_encode([
            'success' => true, 
            'metodos' => $metodos_seguros,
            'total' => count($metodos_seguros)
        ]);
        
    } catch (Exception $e) {
        error_log("Error en obtener_metodos_pago: " . $e->getMessage());
        echo json_encode([
            'success' => false, 
            'error' => $e->getMessage(),
            'message' => 'No se pudieron cargar los métodos de pago',
            'metodos' => [] // Asegurar que siempre devuelve un array
        ]);
    }
}

// NUEVO ENDPOINT: Verificar estado de pago
if ($_POST['funcion'] == 'verificar_estado_pago') {
    try {
        if (empty($_SESSION['id'])) {
            echo json_encode(['success' => false, 'error' => 'no_sesion']);
            exit();
        }
        
        $id_usuario = $_SESSION['id'];
        $id_orden = isset($_POST['id_orden']) ? intval($_POST['id_orden']) : 0;
        
        if ($id_orden <= 0) {
            throw new Exception('ID de orden inválido');
        }
        
        $sql = "SELECT o.*, tp.estado as estado_pago 
                FROM orden o
                LEFT JOIN transaccion_pago tp ON o.id = tp.id_orden
                WHERE o.id = :id_orden AND o.id_usuario = :id_usuario";
        
        $query = $orden->acceso->prepare($sql);
        $query->execute([
            ':id_orden' => $id_orden,
            ':id_usuario' => $id_usuario
        ]);
        
        $orden_info = $query->fetch();
        
        if ($orden_info) {
            echo json_encode([
                'success' => true,
                'orden' => $orden_info,
                'estado' => $orden_info->estado_pago ?? 'pendiente'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'error' => 'Orden no encontrada'
            ]);
        }
        
    } catch (Exception $e) {
        error_log("Error verificar_estado_pago: " . $e->getMessage());
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}