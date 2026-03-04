<?php
include_once '../Models/Ofertas.php';
include_once '../Util/Config/config.php';

$ofertas = new Ofertas();

// Función para encriptar IDs de manera segura
function encryptId($id, $prefix = '') {
    if (empty($id) || !is_numeric($id)) {
        return null;
    }
    try {
        return openssl_encrypt((string)$id, CODE, KEY);
    } catch (Exception $e) {
        error_log("Error encriptando ID: " . $e->getMessage());
        return null;
    }
}

// Función para desencriptar IDs
function decryptId($encryptedId) {
    if (empty($encryptedId)) {
        return null;
    }
    try {
        // Limpiar el ID encriptado
        $encryptedId = str_replace(" ", "+", $encryptedId);
        return openssl_decrypt($encryptedId, CODE, KEY);
    } catch (Exception $e) {
        error_log("Error desencriptando ID: " . $e->getMessage());
        return null;
    }
}

// Función para enviar respuestas JSON de manera segura
function sendJsonResponse($data, $statusCode = 200) {
    // Limpiar cualquier salida previa
    while (ob_get_level()) {
        ob_end_clean();
    }
    
    // Establecer headers
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    http_response_code($statusCode);
    
    // Convertir objetos a array para JSON seguro
    $data = convertObjectsToArrays($data);
    
    // Enviar respuesta JSON
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit();
}

// Función para convertir objetos a arrays (evita problemas con JSON)
function convertObjectsToArrays($data) {
    if (is_object($data)) {
        $data = (array)$data;
    }
    
    if (is_array($data)) {
        foreach ($data as $key => $value) {
            $data[$key] = convertObjectsToArrays($value);
        }
    }
    
    return $data;
}

// Función para procesar y encriptar productos
function processProducts($products) {
    if (!is_array($products) || empty($products)) {
        return [];
    }
    
    $processed = [];
    foreach ($products as $product) {
        if (is_object($product)) {
            $product = (array)$product;
        }
        
        // Encriptar IDs importantes
        if (isset($product['id'])) {
            $product['id'] = encryptId($product['id']);
        }
        
        if (isset($product['id_producto_tienda'])) {
            $product['id_producto_tienda'] = encryptId($product['id_producto_tienda']);
        }
        
        if (isset($product['id_producto'])) {
            $product['id_producto'] = encryptId($product['id_producto']);
        }
        
        if (isset($product['id_tienda'])) {
            $product['id_tienda'] = encryptId($product['id_tienda']);
        }
        
        // Asegurar que los valores sean strings/numbers válidos
        foreach ($product as $key => $value) {
            if (is_resource($value)) {
                $product[$key] = null;
            } elseif (is_object($value)) {
                $product[$key] = (array)$value;
            }
        }
        
        $processed[] = $product;
    }
    
    return $processed;
}

// Función para procesar y encriptar bundles
function processBundles($bundles) {
    if (!is_array($bundles) || empty($bundles)) {
        return [];
    }
    
    $processed = [];
    foreach ($bundles as $bundle) {
        if (is_object($bundle)) {
            $bundle = (array)$bundle;
        }
        
        // Encriptar IDs importantes
        if (isset($bundle['id'])) {
            $bundle['id'] = encryptId($bundle['id']);
        }
        
        if (isset($bundle['tienda_id'])) {
            $bundle['tienda_id'] = encryptId($bundle['tienda_id']);
        }
        
        // Asegurar que los campos sean strings válidos
        if (!isset($bundle['imagen']) || empty($bundle['imagen'])) {
            $bundle['imagen'] = 'bundle_default.png';
        } else {
            // Limpiar la ruta de la imagen
            $bundle['imagen'] = basename($bundle['imagen']);
        }
        
        // Si hay productos_imagenes, separar en array
        if (isset($bundle['productos_imagenes']) && !empty($bundle['productos_imagenes'])) {
            $bundle['productos_imagenes'] = $bundle['productos_imagenes'];
        } else {
            $bundle['productos_imagenes'] = '';
        }
        
        // Convertir nombres de productos a string si es un array
        if (isset($bundle['productos_nombres']) && is_array($bundle['productos_nombres'])) {
            $bundle['productos_nombres'] = implode('|', $bundle['productos_nombres']);
        }
        
        $processed[] = $bundle;
    }
    
    return $processed;
}

// Función para procesar y encriptar categorías
function processCategorias($categorias) {
    if (!is_array($categorias) || empty($categorias)) {
        return [];
    }
    
    $processed = [];
    foreach ($categorias as $categoria) {
        if (is_object($categoria)) {
            $categoria = (array)$categoria;
        }
        
        // Encriptar ID
        if (isset($categoria['id'])) {
            $categoria['id'] = encryptId($categoria['id']);
        }
        
        $processed[] = $categoria;
    }
    
    return $processed;
}

// Verificar que exista la función en POST
if (!isset($_POST['funcion'])) {
    sendJsonResponse(['success' => false, 'message' => 'Función no especificada'], 400);
}

// OBTENER TODAS LAS OFERTAS (Función principal corregida)
if ($_POST['funcion'] == 'obtener_todas_ofertas') {
    try {
        $resultados = [];
        
        // 1. Ofertas Flash
        $flash = $ofertas->obtener_ofertas_flash(4);
        $resultados['flash'] = processProducts($flash);
        
        // 2. Bundles - IMPORTANTE: Asegurar que el modelo devuelva las imágenes
        $bundles = $ofertas->obtener_bundles(2);
        
        // Depuración: Verificar qué datos vienen del modelo
        error_log("Bundles recibidos del modelo: " . json_encode($bundles));
        
        $resultados['bundles'] = processBundles($bundles);
        
        // 3. Categorías con oferta
        $categorias_oferta = $ofertas->obtener_categorias_oferta(4);
        $resultados['categorias_oferta'] = processCategorias($categorias_oferta);
        
        // 4. Super descuentos
        $super = $ofertas->obtener_super_descuentos(50, 6);
        $resultados['super'] = processProducts($super);
        
        // 5. Más vendidos
        $vendidos = $ofertas->obtener_mas_vendidos_oferta(4);
        $resultados['vendidos'] = processProducts($vendidos);
        
        // 6. Envío gratis
        $envio_gratis = $ofertas->obtener_envio_gratis_oferta(4);
        $resultados['envio_gratis'] = processProducts($envio_gratis);
        
        // 7. Destacados
        $destacados = $ofertas->obtener_destacados_oferta(4);
        $resultados['destacados'] = processProducts($destacados);
        
        // Depuración: Verificar qué datos se envían al frontend
        error_log("Resultados enviados al frontend: " . json_encode(array_keys($resultados)));
        
        sendJsonResponse($resultados);
        
    } catch (Exception $e) {
        error_log("Error en obtener_todas_ofertas: " . $e->getMessage());
        sendJsonResponse(['success' => false, 'message' => 'Error al cargar ofertas'], 500);
    }
}

// OBTENER OFERTAS FLASH
if ($_POST['funcion'] == 'obtener_ofertas_flash') {
    try {
        $limite = isset($_POST['limite']) ? (int)$_POST['limite'] : 8;
        
        $productos = $ofertas->obtener_ofertas_flash($limite);
        $productos_procesados = processProducts($productos);
        
        sendJsonResponse($productos_procesados);
        
    } catch (Exception $e) {
        error_log("Error en obtener_ofertas_flash: " . $e->getMessage());
        sendJsonResponse([], 500);
    }
}

// OBTENER BUNDLES
if ($_POST['funcion'] == 'obtener_bundles') {
    try {
        $limite = isset($_POST['limite']) ? (int)$_POST['limite'] : 4;
        
        $bundles = $ofertas->obtener_bundles($limite);
        $bundles_procesados = processBundles($bundles);
        
        sendJsonResponse($bundles_procesados);
        
    } catch (Exception $e) {
        error_log("Error en obtener_bundles: " . $e->getMessage());
        sendJsonResponse([], 500);
    }
}

// OBTENER PRODUCTOS DE BUNDLE
if ($_POST['funcion'] == 'obtener_productos_bundle') {
    try {
        $bundle_id = isset($_POST['bundle_id']) ? $_POST['bundle_id'] : null;
        
        if (!$bundle_id) {
            sendJsonResponse([]);
        }
        
        // Desencriptar ID del bundle
        $bundle_id_decrypted = decryptId($bundle_id);
        
        if (!$bundle_id_decrypted) {
            sendJsonResponse([]);
        }
        
        $productos = $ofertas->obtener_productos_bundle($bundle_id_decrypted);
        $productos_procesados = processProducts($productos);
        
        sendJsonResponse($productos_procesados);
        
    } catch (Exception $e) {
        error_log("Error en obtener_productos_bundle: " . $e->getMessage());
        sendJsonResponse([], 500);
    }
}

// OBTENER CATEGORÍAS CON OFERTA
if ($_POST['funcion'] == 'obtener_categorias_oferta') {
    try {
        $limite = isset($_POST['limite']) ? (int)$_POST['limite'] : 4;
        
        $categorias = $ofertas->obtener_categorias_oferta($limite);
        $categorias_procesadas = processCategorias($categorias);
        
        sendJsonResponse($categorias_procesadas);
        
    } catch (Exception $e) {
        error_log("Error en obtener_categorias_oferta: " . $e->getMessage());
        sendJsonResponse([], 500);
    }
}

// OBTENER SUPER DESCUENTOS
if ($_POST['funcion'] == 'obtener_super_descuentos') {
    try {
        $min_descuento = isset($_POST['min_descuento']) ? (int)$_POST['min_descuento'] : 50;
        $limite = isset($_POST['limite']) ? (int)$_POST['limite'] : 12;
        
        $productos = $ofertas->obtener_super_descuentos($min_descuento, $limite);
        $productos_procesados = processProducts($productos);
        
        sendJsonResponse($productos_procesados);
        
    } catch (Exception $e) {
        error_log("Error en obtener_super_descuentos: " . $e->getMessage());
        sendJsonResponse([], 500);
    }
}

// OBTENER MÁS VENDIDOS
if ($_POST['funcion'] == 'obtener_mas_vendidos_oferta') {
    try {
        $limite = isset($_POST['limite']) ? (int)$_POST['limite'] : 8;
        
        $productos = $ofertas->obtener_mas_vendidos_oferta($limite);
        $productos_procesados = processProducts($productos);
        
        sendJsonResponse($productos_procesados);
        
    } catch (Exception $e) {
        error_log("Error en obtener_mas_vendidos_oferta: " . $e->getMessage());
        sendJsonResponse([], 500);
    }
}

// OBTENER ENVÍO GRATIS
if ($_POST['funcion'] == 'obtener_envio_gratis_oferta') {
    try {
        $limite = isset($_POST['limite']) ? (int)$_POST['limite'] : 8;
        
        $productos = $ofertas->obtener_envio_gratis_oferta($limite);
        $productos_procesados = processProducts($productos);
        
        sendJsonResponse($productos_procesados);
        
    } catch (Exception $e) {
        error_log("Error en obtener_envio_gratis_oferta: " . $e->getMessage());
        sendJsonResponse([], 500);
    }
}

// OBTENER OFERTAS POR CATEGORÍA
if ($_POST['funcion'] == 'obtener_ofertas_por_categoria') {
    try {
        $id_categoria = isset($_POST['id_categoria']) ? (int)$_POST['id_categoria'] : null;
        $limite = isset($_POST['limite']) ? (int)$_POST['limite'] : 12;
        
        if (!$id_categoria) {
            sendJsonResponse([]);
        }
        
        $productos = $ofertas->obtener_ofertas_por_categoria($id_categoria, $limite);
        $productos_procesados = processProducts($productos);
        
        sendJsonResponse($productos_procesados);
        
    } catch (Exception $e) {
        error_log("Error en obtener_ofertas_por_categoria: " . $e->getMessage());
        sendJsonResponse([], 500);
    }
}

// OBTENER OFERTAS RECIENTES
if ($_POST['funcion'] == 'obtener_ofertas_recientes') {
    try {
        $limite = isset($_POST['limite']) ? (int)$_POST['limite'] : 10;
        
        $productos = $ofertas->obtener_ofertas_recientes($limite);
        $productos_procesados = processProducts($productos);
        
        sendJsonResponse($productos_procesados);
        
    } catch (Exception $e) {
        error_log("Error en obtener_ofertas_recientes: " . $e->getMessage());
        sendJsonResponse([], 500);
    }
}

// OBTENER DESTACADOS
if ($_POST['funcion'] == 'obtener_destacados_oferta') {
    try {
        $limite = isset($_POST['limite']) ? (int)$_POST['limite'] : 8;
        
        $productos = $ofertas->obtener_destacados_oferta($limite);
        $productos_procesados = processProducts($productos);
        
        sendJsonResponse($productos_procesados);
        
    } catch (Exception $e) {
        error_log("Error en obtener_destacados_oferta: " . $e->getMessage());
        sendJsonResponse([], 500);
    }
}

// OBTENER OFERTAS POR RANGO
if ($_POST['funcion'] == 'obtener_ofertas_por_rango') {
    try {
        $min_descuento = isset($_POST['min_descuento']) ? (int)$_POST['min_descuento'] : 10;
        $max_descuento = isset($_POST['max_descuento']) ? (int)$_POST['max_descuento'] : 49;
        $limite = isset($_POST['limite']) ? (int)$_POST['limite'] : 12;
        
        $productos = $ofertas->obtener_ofertas_por_rango($min_descuento, $max_descuento, $limite);
        $productos_procesados = processProducts($productos);
        
        sendJsonResponse($productos_procesados);
        
    } catch (Exception $e) {
        error_log("Error en obtener_ofertas_por_rango: " . $e->getMessage());
        sendJsonResponse([], 500);
    }
}

// OBTENER OFERTAS POR MARCA
if ($_POST['funcion'] == 'obtener_ofertas_por_marca') {
    try {
        $id_marca = isset($_POST['id_marca']) ? (int)$_POST['id_marca'] : null;
        $limite = isset($_POST['limite']) ? (int)$_POST['limite'] : 8;
        
        if (!$id_marca) {
            sendJsonResponse([]);
        }
        
        $productos = $ofertas->obtener_ofertas_por_marca($id_marca, $limite);
        $productos_procesados = processProducts($productos);
        
        sendJsonResponse($productos_procesados);
        
    } catch (Exception $e) {
        error_log("Error en obtener_ofertas_por_marca: " . $e->getMessage());
        sendJsonResponse([], 500);
    }
}

// VERIFICAR PRODUCTO EN OFERTA
if ($_POST['funcion'] == 'verificar_producto_oferta') {
    try {
        $id_producto_tienda = isset($_POST['id_producto_tienda']) ? $_POST['id_producto_tienda'] : null;
        
        if (!$id_producto_tienda) {
            sendJsonResponse([]);
        }
        
        // Desencriptar si viene encriptado
        $id_producto_tienda_decrypted = decryptId($id_producto_tienda);
        
        if (!$id_producto_tienda_decrypted) {
            sendJsonResponse([]);
        }
        
        $producto = $ofertas->verificar_producto_oferta($id_producto_tienda_decrypted);
        
        if (!empty($producto)) {
            $producto_procesado = processProducts($producto);
            sendJsonResponse($producto_procesado);
        } else {
            sendJsonResponse([]);
        }
        
    } catch (Exception $e) {
        error_log("Error en verificar_producto_oferta: " . $e->getMessage());
        sendJsonResponse([], 500);
    }
}

// OBTENER ESTADÍSTICAS DE OFERTAS
if ($_POST['funcion'] == 'obtener_estadisticas_ofertas') {
    try {
        $estadisticas = $ofertas->obtener_estadisticas_ofertas();
        
        // Convertir a array
        if (is_object($estadisticas)) {
            $estadisticas = (array)$estadisticas;
        }
        
        sendJsonResponse($estadisticas);
        
    } catch (Exception $e) {
        error_log("Error en obtener_estadisticas_ofertas: " . $e->getMessage());
        sendJsonResponse([], 500);
    }
}

function enviar_email_confirmacion($email, $nombre, $token)
{
    try {
        // URL de confirmación
        $base_url = "http://" . $_SERVER['HTTP_HOST'];
        $confirmationUrl = $base_url . "/Views/confirmar_suscripcion.php?token=" . urlencode($token);
        
        // Asunto del email
        $subject = "Confirma tu suscripción a las ofertas de NexusBuy";
        
        // Cuerpo HTML del email
        $html_body = "
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #4361ee; color: white; padding: 20px; text-align: center; }
                .content { background: #f8f9fa; padding: 30px; }
                .btn-confirm { 
                    display: inline-block; 
                    background: #28a745; 
                    color: white; 
                    padding: 12px 30px; 
                    text-decoration: none; 
                    border-radius: 5px; 
                    font-weight: bold; 
                }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>¡Confirma tu suscripción!</h1>
                </div>
                <div class='content'>
                    <h2>Hola " . htmlspecialchars($nombre ?: 'usuario') . ",</h2>
                    <p>Para completar tu suscripción a las ofertas de <strong>NexusBuy</strong>, haz clic en el botón:</p>
                    <p style='text-align: center;'>
                        <a href='$confirmationUrl' class='btn-confirm'>Confirmar Suscripción</a>
                    </p>
                    <p>O copia este enlace en tu navegador:<br>
                    <code>$confirmationUrl</code></p>
                </div>
            </div>
        </body>
        </html>
        ";
        
        // Cuerpo en texto plano
        $text_body = "Confirma tu suscripción a NexusBuy\n\n";
        $text_body .= "Hola " . ($nombre ?: 'usuario') . ",\n\n";
        $text_body .= "Para completar tu suscripción, visita:\n";
        $text_body .= "$confirmationUrl\n\n";
        $text_body .= "Gracias,\nEl equipo de NexusBuy";
        
        // Enviar email usando la función mail() de PHP
        $headers = "From: ofertas@nexusbuy.com\r\n";
        $headers .= "Reply-To: no-reply@nexusbuy.com\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        
        // Intentar enviar el email
        $sent = mail($email, $subject, $html_body, $headers);
        
        if ($sent) {
            error_log("Email de confirmación enviado a: $email");
            return true;
        } else {
            error_log("Error enviando email a: $email");
            return false;
        }
        
    } catch (Exception $e) {
        error_log("Error en enviar_email_confirmacion: " . $e->getMessage());
        return false;
    }
}


// SUSCRIBIR EMAIL A OFERTAS
if ($_POST['funcion'] == 'suscribir_email_ofertas') {
    try {
        $email = isset($_POST['email']) ? trim($_POST['email']) : null;
        $nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : null;
        $frecuencia = isset($_POST['frecuencia']) ? $_POST['frecuencia'] : 'semanal';
        
        if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            sendJsonResponse(['success' => false, 'message' => 'Email inválido']);
        }
        
        // Usar el modelo para crear la suscripción
        $resultado = $ofertas->suscribir_email_ofertas($email, $nombre, $frecuencia);
        
        if ($resultado['success']) {
            // Si requiere confirmación, enviar email con PHPMailer
            if (isset($resultado['requires_confirmation']) && $resultado['requires_confirmation']) {
                // Incluir y usar PHPMailer
                require_once '../Util/Mail/Mailer.php';
                $mailer = new \Util\Mail\Mailer();
                
                if ($mailer->isConfigured()) {
                    $email_sent = $mailer->sendConfirmationEmail(
                        $email, 
                        $nombre, 
                        $resultado['token']
                    );
                    
                    if ($email_sent) {
                        sendJsonResponse([
                            'success' => true,
                            'requires_confirmation' => true,
                            'message' => '¡Suscripción iniciada! Por favor, revisa tu email para confirmar.'
                        ]);
                    } else {
                        sendJsonResponse([
                            'success' => false,
                            'message' => 'Suscripción guardada, pero hubo un error enviando el email de confirmación. Error: ' . $mailer->getError()
                        ]);
                    }
                } else {
                    sendJsonResponse([
                        'success' => false,
                        'message' => 'Suscripción guardada, pero el sistema de email no está configurado.'
                    ]);
                }
            } else {
                sendJsonResponse($resultado);
            }
        } else {
            sendJsonResponse($resultado);
        }
        
    } catch (Exception $e) {
        error_log("Error en suscribir_email_ofertas: " . $e->getMessage());
        sendJsonResponse(['success' => false, 'message' => 'Error en el servidor'], 500);
    }
}

// CONFIRMAR SUSCRIPCIÓN
if ($_POST['funcion'] == 'confirmar_suscripcion_ajax') {
    try {
        $token = isset($_POST['token']) ? trim($_POST['token']) : null;
        
        if (!$token) {
            sendJsonResponse(['success' => false, 'message' => 'Token no proporcionado']);
        }
        
        $resultado = $ofertas->confirmar_suscripcion($token);
        sendJsonResponse($resultado);
        
    } catch (Exception $e) {
        error_log("Error en confirmar_suscripcion_ajax: " . $e->getMessage());
        sendJsonResponse(['success' => false, 'message' => 'Error en el servidor'], 500);
    }
}

// REENVIAR CONFIRMACIÓN (nuevo endpoint)
if ($_POST['funcion'] == 'reenviar_confirmacion') {
    try {
        $email = isset($_POST['email']) ? trim($_POST['email']) : null;
        
        if (!$email) {
            sendJsonResponse(['success' => false, 'message' => 'Email requerido']);
        }
        
        $resultado = $ofertas->reenviar_confirmacion($email);
        
        if ($resultado['success']) {
            // Enviar el email con PHPMailer
            require_once '../Util/Mail/Mailer.php';
            $mailer = new \Util\Mail\Mailer();
            
            if ($mailer->isConfigured()) {
                $email_sent = $mailer->sendConfirmationEmail(
                    $resultado['email'],
                    $resultado['nombre'],
                    $resultado['token']
                );
                
                if ($email_sent) {
                    sendJsonResponse([
                        'success' => true,
                        'message' => 'Email de confirmación reenviado exitosamente'
                    ]);
                } else {
                    sendJsonResponse([
                        'success' => false,
                        'message' => 'Error al reenviar el email. Error: ' . $mailer->getError()
                    ]);
                }
            } else {
                sendJsonResponse([
                    'success' => false,
                    'message' => 'El sistema de email no está configurado.'
                ]);
            }
        } else {
            sendJsonResponse($resultado);
        }
        
    } catch (Exception $e) {
        error_log("Error en reenviar_confirmacion: " . $e->getMessage());
        sendJsonResponse(['success' => false, 'message' => 'Error en el servidor'], 500);
    }
}

// CANCELAR SUSCRIPCIÓN (nuevo endpoint)
if ($_POST['funcion'] == 'cancelar_suscripcion') {
    try {
        $email = isset($_POST['email']) ? trim($_POST['email']) : null;
        
        if (!$email) {
            sendJsonResponse(['success' => false, 'message' => 'Email requerido']);
        }
        
        $resultado = $ofertas->cancelar_suscripcion($email);
        sendJsonResponse($resultado);
        
    } catch (Exception $e) {
        error_log("Error en cancelar_suscripcion: " . $e->getMessage());
        sendJsonResponse(['success' => false, 'message' => 'Error en el servidor'], 500);
    }
}

// VERIFICAR ESTADO DE SUSCRIPCIÓN (nuevo endpoint)
if ($_POST['funcion'] == 'verificar_suscripcion') {
    try {
        $email = isset($_POST['email']) ? trim($_POST['email']) : null;
        
        if (!$email) {
            sendJsonResponse(['success' => false, 'message' => 'Email requerido']);
        }
        
        $suscripcion = $ofertas->obtener_suscripcion_por_email($email);
        
        if ($suscripcion) {
            sendJsonResponse([
                'success' => true,
                'exists' => true,
                'confirmed' => ($suscripcion['confirmada'] == 1),
                'active' => ($suscripcion['estado'] == 'activa'),
                'data' => $suscripcion
            ]);
        } else {
            sendJsonResponse([
                'success' => true,
                'exists' => false,
                'message' => 'No hay suscripción para este email'
            ]);
        }
        
    } catch (Exception $e) {
        error_log("Error en verificar_suscripcion: " . $e->getMessage());
        sendJsonResponse(['success' => false, 'message' => 'Error en el servidor'], 500);
    }
}

// OBTENER DETALLE DE OFERTA FLASH
if ($_POST['funcion'] == 'obtener_detalle_oferta_flash') {
    try {
        $oferta_id = isset($_POST['oferta_id']) ? $_POST['oferta_id'] : null;
        
        if (!$oferta_id) {
            sendJsonResponse([]);
        }
        
        // Desencriptar ID
        $oferta_id_decrypted = decryptId($oferta_id);
        
        if (!$oferta_id_decrypted) {
            sendJsonResponse([]);
        }
        
        // Esta función necesita ser implementada en el modelo Ofertas.php
        // Por ahora, usaremos una consulta directa
        $sql = "SELECT 
                    of.*,
                    pt.id as producto_tienda_id,
                    p.nombre as producto_nombre,
                    p.descripcion_larga,
                    m.nombre as marca,
                    t.nombre as tienda
                FROM oferta_flash of
                JOIN producto_tienda pt ON pt.id = of.producto_tienda_id
                JOIN producto p ON p.id = pt.id_producto
                JOIN marca m ON m.id = p.id_marca
                JOIN tienda t ON t.id = pt.id_tienda
                WHERE of.id = :id";
        
        $query = $ofertas->acceso->prepare($sql);
        $query->execute([':id' => $oferta_id_decrypted]);
        $oferta = $query->fetch(PDO::FETCH_ASSOC);
        
        if ($oferta) {
            // Encriptar IDs
            if (isset($oferta['id'])) {
                $oferta['id'] = encryptId($oferta['id']);
            }
            if (isset($oferta['producto_tienda_id'])) {
                $oferta['producto_tienda_id'] = encryptId($oferta['producto_tienda_id']);
            }
            
            sendJsonResponse([$oferta]);
        } else {
            sendJsonResponse([]);
        }
        
    } catch (Exception $e) {
        error_log("Error en obtener_detalle_oferta_flash: " . $e->getMessage());
        sendJsonResponse([], 500);
    }
}

// OBTENER DETALLE DE BUNDLE
if ($_POST['funcion'] == 'obtener_detalle_bundle') {
    try {
        $bundle_id = isset($_POST['bundle_id']) ? $_POST['bundle_id'] : null;
        
        if (!$bundle_id) {
            sendJsonResponse([]);
        }
        
        // Desencriptar ID
        $bundle_id_decrypted = decryptId($bundle_id);
        
        if (!$bundle_id_decrypted) {
            sendJsonResponse([]);
        }
        
        $sql = "SELECT 
                    b.*,
                    t.nombre as tienda,
                    t.direccion as tienda_direccion,
                    t.telefono as tienda_telefono
                FROM bundle b
                JOIN tienda t ON t.id = b.tienda_id
                WHERE b.id = :id";
        
        $query = $ofertas->acceso->prepare($sql);
        $query->execute([':id' => $bundle_id_decrypted]);
        $bundle = $query->fetch(PDO::FETCH_ASSOC);
        
        if ($bundle) {
            // Encriptar IDs
            if (isset($bundle['id'])) {
                $bundle['id'] = encryptId($bundle['id']);
            }
            if (isset($bundle['tienda_id'])) {
                $bundle['tienda_id'] = encryptId($bundle['tienda_id']);
            }
            
            // Obtener productos del bundle
            $productos = $ofertas->obtener_productos_bundle($bundle_id_decrypted);
            $productos_procesados = processProducts($productos);
            $bundle['productos'] = $productos_procesados;
            
            sendJsonResponse([$bundle]);
        } else {
            sendJsonResponse([]);
        }
        
    } catch (Exception $e) {
        error_log("Error en obtener_detalle_bundle: " . $e->getMessage());
        sendJsonResponse([], 500);
    }
}

// FUNCIONES DE ADMINISTRACIÓN (requieren validación de sesión)
function checkAdminSession() {
    session_start();
    if (!isset($_SESSION['usuario_admin']) || !$_SESSION['usuario_admin']) {
        sendJsonResponse(['success' => false, 'message' => 'No autorizado'], 403);
    }
}

if ($_POST['funcion'] == 'crear_oferta_flash') {
    try {
        checkAdminSession();
        
        $producto_tienda_id = isset($_POST['producto_tienda_id']) ? (int)$_POST['producto_tienda_id'] : null;
        $precio_especial = isset($_POST['precio_especial']) ? (float)$_POST['precio_especial'] : null;
        $fecha_inicio = isset($_POST['fecha_inicio']) ? $_POST['fecha_inicio'] : null;
        $fecha_fin = isset($_POST['fecha_fin']) ? $_POST['fecha_fin'] : null;
        $stock_limitado = isset($_POST['stock_limitado']) ? (int)$_POST['stock_limitado'] : null;
        
        if (!$producto_tienda_id || !$precio_especial || !$fecha_inicio || !$fecha_fin) {
            sendJsonResponse(['success' => false, 'message' => 'Datos incompletos']);
        }
        
        $sql = "INSERT INTO oferta_flash 
                (producto_tienda_id, precio_especial, fecha_inicio, fecha_fin, stock_limitado, estado) 
                VALUES (:producto_id, :precio, :fecha_inicio, :fecha_fin, :stock_limitado, 'activa')";
        
        $query = $ofertas->acceso->prepare($sql);
        $success = $query->execute([
            ':producto_id' => $producto_tienda_id,
            ':precio' => $precio_especial,
            ':fecha_inicio' => $fecha_inicio,
            ':fecha_fin' => $fecha_fin,
            ':stock_limitado' => $stock_limitado
        ]);
        
        if ($success) {
            sendJsonResponse(['success' => true, 'message' => 'Oferta flash creada exitosamente']);
        } else {
            sendJsonResponse(['success' => false, 'message' => 'Error al crear oferta flash']);
        }
        
    } catch (Exception $e) {
        error_log("Error en crear_oferta_flash: " . $e->getMessage());
        sendJsonResponse(['success' => false, 'message' => 'Error en el servidor'], 500);
    }
}

if ($_POST['funcion'] == 'crear_bundle') {
    try {
        checkAdminSession();
        
        $nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : null;
        $descripcion = isset($_POST['descripcion']) ? trim($_POST['descripcion']) : null;
        $tienda_id = isset($_POST['tienda_id']) ? (int)$_POST['tienda_id'] : null;
        $precio_original = isset($_POST['precio_original']) ? (float)$_POST['precio_original'] : null;
        $precio_oferta = isset($_POST['precio_oferta']) ? (float)$_POST['precio_oferta'] : null;
        $stock = isset($_POST['stock']) ? (int)$_POST['stock'] : 0;
        
        if (!$nombre || !$tienda_id || !$precio_original || !$precio_oferta) {
            sendJsonResponse(['success' => false, 'message' => 'Datos incompletos']);
        }
        
        $descuento_porcentaje = round((($precio_original - $precio_oferta) / $precio_original * 100), 2);
        
        $sql = "INSERT INTO bundle 
                (nombre, descripcion, tienda_id, precio_original, precio_oferta, descuento_porcentaje, stock, estado) 
                VALUES (:nombre, :descripcion, :tienda_id, :precio_original, :precio_oferta, :descuento, :stock, 'activo')";
        
        $query = $ofertas->acceso->prepare($sql);
        $query->execute([
            ':nombre' => $nombre,
            ':descripcion' => $descripcion,
            ':tienda_id' => $tienda_id,
            ':precio_original' => $precio_original,
            ':precio_oferta' => $precio_oferta,
            ':descuento' => $descuento_porcentaje,
            ':stock' => $stock
        ]);
        
        $bundle_id = $ofertas->acceso->lastInsertId();
        
        if ($bundle_id) {
            sendJsonResponse([
                'success' => true, 
                'message' => 'Bundle creado exitosamente', 
                'bundle_id' => encryptId($bundle_id)
            ]);
        } else {
            sendJsonResponse(['success' => false, 'message' => 'Error al crear bundle']);
        }
        
    } catch (Exception $e) {
        error_log("Error en crear_bundle: " . $e->getMessage());
        sendJsonResponse(['success' => false, 'message' => 'Error en el servidor'], 500);
    }
}

// Si ninguna función coincide
sendJsonResponse(['success' => false, 'message' => 'Función no encontrada'], 404);
?>