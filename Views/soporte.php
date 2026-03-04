<?php
// Soporte.php
session_start();
$base_path_url = ""; // Ya está en Views
$base_path = "../";
$pageTitle =  "Soporte";
$pageName = "" ;
$breadcrumb = "desactive";
$notificaciones = "desactive";
$checkout = "desactive";
$soporte = "active";
// $pageDescription = "Análisis detallado de toda la plataforma";
include_once 'Layouts/header.php';

// Incluir el modelo
include_once '../Models/ConfiguracionSitio.php';
include_once '../Util/Config/config.php'; // Para CODE y KEY

$configuracion = new ConfiguracionSitio();

// Obtener el filtro de la URL
$filtro = isset($_GET['filtro']) ? $_GET['filtro'] : 'centro_ayuda';

// Obtener datos de la base de datos
$datosContacto = $configuracion->obtenerPorCategoria('contacto');
$datosGeneral = $configuracion->obtenerPorCategoria('general');
$datosFinanzas = $configuracion->obtenerPorCategoria('finanzas');
$datosLegal = $configuracion->obtenerPorCategoria('legal');

// Función para formatear y mostrar fechas
function formatearFecha($fecha, $formato = 'd/m/Y')
{
    if (empty($fecha) || $fecha === '0000-00-00 00:00:00' || $fecha === '0000-00-00') {
        return 'No disponible';
    }

    try {
        // Verificar si ya es un objeto DateTime
        if ($fecha instanceof DateTime) {
            $date = $fecha;
        } else {
            $date = new DateTime($fecha);
        }
        return $date->format($formato);
    } catch (Exception $e) {
        error_log("Error formateando fecha: " . $e->getMessage());
        return 'Fecha inválida';
    }
}

// Función para generar enlace de WhatsApp
function generarLinkWhatsApp($numero, $mensaje = null, $nombreTienda = 'NexusBuy')
{
    // Limpiar número
    $numeroLimpio = preg_replace('/[^0-9+]/', '', $numero);

    // Asegurar que tenga el prefijo +
    if (strpos($numeroLimpio, '+') !== 0) {
        $numeroLimpio = '+' . $numeroLimpio;
    }

    // Codificar el mensaje
    if ($mensaje === null) {
        $mensaje = "Hola $nombreTienda, me gustaría obtener más información sobre sus productos/servicios.";
    }

    $mensajeCodificado = urlencode($mensaje);

    // Generar enlace
    return "https://wa.me/{$numeroLimpio}?text={$mensajeCodificado}";
}

// Obtener datos del usuario si está logueado
$nombreUsuario = '';
$emailUsuario = '';
if (isset($_SESSION['usuario'])) {
    $nombreUsuario = $_SESSION['usuario']['nombre'] ?? '';
    $emailUsuario = $_SESSION['usuario']['email'] ?? '';
} elseif (isset($_SESSION['id'])) {
    // Intentar obtener del usuario guardado
    $nombreUsuario = $_SESSION['nombre'] ?? '';
    $emailUsuario = $_SESSION['email'] ?? '';
}

// Configurar título y contenido según el filtro
$titulo = "";
$contenido = "";
$icono = "";

switch ($filtro) {
    case 'centro_ayuda':
        $titulo = "Centro de Ayuda";
        $icono = "fas fa-question-circle";

        // Generar enlace de WhatsApp para la sección de contacto rápido
        $telefonoWhatsapp = $datosContacto['telefono_contacto']['valor'] ?? '+5351004754';
        $nombreTienda = $datosGeneral['nombre_tienda']['valor'] ?? 'NexusBuy';
        $whatsappLinkCentro = generarLinkWhatsApp($telefonoWhatsapp, null, $nombreTienda);

        $contenido = '
        <div class="row">
            <div class="col-md-12">
                <!-- Barra de búsqueda -->
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-primary text-white">
                                    <i class="fas fa-search"></i>
                                </span>
                            </div>
                            <input type="text" id="buscarAyuda" class="form-control form-control-lg" 
                                   placeholder="¿En qué podemos ayudarte? Escribe tu pregunta...">
                            <div class="input-group-append">
                                <button class="btn btn-primary" type="button" id="btnBuscarAyuda">
                                    <i class="fas fa-search mr-2"></i> Buscar
                                </button>
                            </div>
                        </div>
                        <div class="mt-2 text-muted small">
                            <i class="fas fa-lightbulb mr-1"></i>
                            Sugerencias: "devolución", "pago", "envío", "cuenta", "pedido"
                        </div>
                    </div>
                </div>

                <!-- Categorías principales -->
                <h4 class="mb-4">Categorías de Ayuda</h4>
                <div class="row mb-5">
                    <div class="col-md-3 mb-3">
                        <a href="soporte.php?filtro=metodos_pago" class="card text-center h-100 card-categoria-ayuda">
                            <div class="card-body">
                                <i class="fas fa-credit-card fa-3x text-primary mb-3"></i>
                                <h5>Pagos</h5>
                                <p class="small text-muted">Métodos, problemas, facturas</p>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="soporte.php?filtro=envios_entregas" class="card text-center h-100 card-categoria-ayuda">
                            <div class="card-body">
                                <i class="fas fa-shipping-fast fa-3x text-primary mb-3"></i>
                                <h5>Envíos</h5>
                                <p class="small text-muted">Seguimiento, tiempos, costos</p>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="soporte.php?filtro=devoluciones" class="card text-center h-100 card-categoria-ayuda">
                            <div class="card-body">
                                <i class="fas fa-undo fa-3x text-primary mb-3"></i>
                                <h5>Devoluciones</h5>
                                <p class="small text-muted">Políticas, procesos, reembolsos</p>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="soporte.php?filtro=terminos_condiciones" class="card text-center h-100 card-categoria-ayuda">
                            <div class="card-body">
                                <i class="fas fa-file-contract fa-3x text-primary mb-3"></i>
                                <h5>Términos</h5>
                                <p class="small text-muted">Políticas, condiciones, privacidad</p>
                            </div>
                        </a>
                    </div>
                </div>

                <!-- Preguntas Frecuentes (con datos dinámicos) -->
                <h4 class="mb-4">Preguntas Frecuentes</h4>
                <div class="accordion" id="faqAccordion">
                    <!-- FAQ 1 -->
                    <div class="card mb-2 faq-item">
                        <div class="card-header" id="faqHeading1">
                            <h5 class="mb-0">
                                <button class="btn btn-link w-100 text-left d-flex justify-content-between align-items-center" 
                                        type="button" data-toggle="collapse" data-target="#faq1" 
                                        aria-expanded="true" aria-controls="faq1">
                                    <span>
                                        <i class="fas fa-question-circle mr-2 text-primary"></i>
                                        ¿Cómo puedo realizar un pedido?
                                    </span>
                                    <i class="fas fa-chevron-down"></i>
                                </button>
                            </h5>
                        </div>
                        <div id="faq1" class="collapse show" aria-labelledby="faqHeading1" data-parent="#faqAccordion">
                            <div class="card-body">
                                <p>Para realizar un pedido en ' . ($datosGeneral['nombre_tienda']['valor'] ?? 'NexusBuy') . ' sigue estos pasos:</p>
                                <ol>
                                    <li>Busca el producto que deseas en nuestra tienda</li>
                                    <li>Haz clic en "Agregar al carrito"</li>
                                    <li>Ve a tu carrito de compras</li>
                                    <li>Selecciona "Proceder al pago"</li>
                                    <li>Completa tus datos y selecciona método de pago</li>
                                    <li>Confirma tu pedido</li>
                                </ol>
                                <p>Recibirás un email de confirmación con los detalles.</p>
                            </div>
                        </div>
                    </div>

                    <!-- FAQ 2 -->
                    <div class="card mb-2 faq-item">
                        <div class="card-header" id="faqHeading2">
                            <h5 class="mb-0">
                                <button class="btn btn-link w-100 text-left d-flex justify-content-between align-items-center" 
                                        type="button" data-toggle="collapse" data-target="#faq2" 
                                        aria-expanded="false" aria-controls="faq2">
                                    <span>
                                        <i class="fas fa-question-circle mr-2 text-primary"></i>
                                        ¿Cuánto tiempo tardan los envíos?
                                    </span>
                                    <i class="fas fa-chevron-down"></i>
                                </button>
                            </h5>
                        </div>
                        <div id="faq2" class="collapse" aria-labelledby="faqHeading2" data-parent="#faqAccordion">
                            <div class="card-body">
                                <p>Los tiempos de envío varían según tu ubicación:</p>
                                <ul>
                                    <li><strong>Envío estándar:</strong> 3-5 días hábiles</li>
                                    <li><strong>Envío express:</strong> 1-2 días hábiles</li>
                                    <li><strong>Recogida en tienda:</strong> 24 horas (disponible en algunas ciudades)</li>
                                </ul>
                                <p>Puedes ver el tiempo estimado durante el proceso de compra.</p>
                            </div>
                        </div>
                    </div>

                    <!-- FAQ 5 (Modificada con datos dinámicos) -->
                    <div class="card mb-2 faq-item">
                        <div class="card-header" id="faqHeading5">
                            <h5 class="mb-0">
                                <button class="btn btn-link w-100 text-left d-flex justify-content-between align-items-center" 
                                        type="button" data-toggle="collapse" data-target="#faq5" 
                                        aria-expanded="false" aria-controls="faq5">
                                    <span>
                                        <i class="fas fa-question-circle mr-2 text-primary"></i>
                                        ¿Cómo solicito una devolución?
                                    </span>
                                    <i class="fas fa-chevron-down"></i>
                                </button>
                            </h5>
                        </div>
                        <div id="faq5" class="collapse" aria-labelledby="faqHeading5" data-parent="#faqAccordion">
                            <div class="card-body">
                                <p>Para solicitar una devolución:</p>
                                <ol>
                                    <li>Inicia sesión en tu cuenta</li>
                                    <li>Ve a "Mis pedidos"</li>
                                    <li>Selecciona el pedido con el producto a devolver</li>
                                    <li>Haz clic en "Solicitar devolución"</li>
                                    <li>Completa el formulario con el motivo</li>
                                    <li>Espera nuestra confirmación por email</li>
                                </ol>
                                <p class="text-muted small">Tienes ' . ($datosFinanzas['dias_para_devolucion']['valor'] ?? '30') . ' días desde la recepción para solicitar devoluciones.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sección de contacto rápido (con datos dinámicos y WhatsApp) -->
                <div class="row mt-5">
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-body">
                                <h5><i class="fas fa-headset mr-2 text-primary"></i> ¿No encontraste tu respuesta?</h5>
                                <p class="mb-4">Nuestro equipo de soporte está listo para ayudarte personalmente.</p>
                                
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <a href="tel:' . ($datosContacto['telefono_contacto']['valor'] ?? '+5351004754') . '" class="text-decoration-none">
                                            <div class="text-center p-3 border rounded hover-shadow">
                                                <i class="fas fa-phone fa-2x text-primary mb-2"></i>
                                                <h6>Llamarnos</h6>
                                                <p class="small mb-0">' . ($datosContacto['telefono_contacto']['valor'] ?? '+5351004754') . '</p>
                                                <p class="small text-muted">Lun-Vie 9:00-18:00</p>
                                            </div>
                                        </a>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <a href="mailto:' . ($datosContacto['email_contacto']['valor'] ?? 'ventas@nexusbuy.com') . '" class="text-decoration-none">
                                            <div class="text-center p-3 border rounded hover-shadow">
                                                <i class="fas fa-envelope fa-2x text-primary mb-2"></i>
                                                <h6>Email</h6>
                                                <p class="small mb-0">' . ($datosContacto['email_contacto']['valor'] ?? 'ventas@nexusbuy.com') . '</p>
                                                <p class="small text-muted">Respuesta en 24h</p>
                                            </div>
                                        </a>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <a href="soporte.php?filtro=contacto" class="text-decoration-none">
                                            <div class="text-center p-3 border rounded hover-shadow">
                                                <i class="fas fa-comments fa-2x text-primary mb-2"></i>
                                                <h6>Formulario</h6>
                                                <p class="small mb-0">Envíanos un mensaje</p>
                                                <p class="small text-muted">Respuesta personalizada</p>
                                            </div>
                                        </a>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <a href="' . $whatsappLinkCentro . '" target="_blank" class="text-decoration-none">
                                            <div class="text-center p-3 border rounded hover-shadow" style="background-color: #25D366; color: white;">
                                                <i class="fab fa-whatsapp fa-2x mb-2"></i>
                                                <h6>WhatsApp</h6>
                                                <p class="small mb-0">Chat directo</p>
                                                <p class="small">Respuesta inmediata</p>
                                            </div>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="card bg-light">
                            <div class="card-body text-center">
                                <i class="fas fa-lightbulb fa-3x text-warning mb-3"></i>
                                <h5>Consejo útil</h5>
                                <p class="small">Antes de contactarnos, revisa el estado de tu pedido en "Mi cuenta". Muchas preguntas se resuelven allí.</p>
                                <a href="../index.php" class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-home mr-1"></i> Volver al inicio
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>';
        break;

    case 'metodos_pago':
        $titulo = "Métodos de Pago";
        $icono = "fas fa-credit-card";
        $moneda = $datosFinanzas['moneda_principal']['valor'] ?? 'CUP';
        $contenido = "
        <div class='row'>
            <div class='col-md-12'>
                <h4>Formas de pago aceptadas</h4>
                <p>Aceptamos múltiples métodos de pago para tu comodidad. Todas las transacciones son seguras.</p>
                
                <div class='row mt-4'>
                    <div class='col-md-3 text-center mb-4'>
                        <div class='card h-100'>
                            <div class='card-body'>
                                <i class='fas fa-money-bill-wave fa-3x text-primary mb-3'></i>
                                <h5>Efectivo</h5>
                                <p>Pago en " . $moneda . "</p>
                            </div>
                        </div>
                    </div>
                    <div class='col-md-3 text-center mb-4'>
                        <div class='card h-100'>
                            <div class='card-body'>
                                <i class='fas fa-university fa-3x text-primary mb-3'></i>
                                <h5>Transferencia</h5>
                                <p>Transferencia bancaria nacional</p>
                            </div>
                        </div>
                    </div>
                    <div class='col-md-3 text-center mb-4'>
                        <div class='card h-100'>
                            <div class='card-body'>
                                <i class='fas fa-credit-card fa-3x text-primary mb-3'></i>
                                <h5>Tarjetas</h5>
                                <p>Tarjetas nacionales (si aplica)</p>
                            </div>
                        </div>
                    </div>
                    <div class='col-md-3 text-center mb-4'>
                        <div class='card h-100'>
                            <div class='card-body'>
                                <i class='fas fa-handshake fa-3x text-primary mb-3'></i>
                                <h5>Contra Entrega</h5>
                                <p>Paga cuando recibas tu pedido</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class='alert alert-info mt-4'>
                    <h5><i class='fas fa-info-circle mr-2'></i> Información importante:</h5>
                    <p>Moneda principal: <strong>" . $moneda . "</strong></p>
                    <p>Impuesto sobre ventas: <strong>" . ($datosFinanzas['impuesto_venta']['valor'] ?? '0') . "%</strong></p>
                </div>
            </div>
        </div>
        ";
        break;

    case 'envios_entregas':
        $titulo = "Envíos y Entregas";
        $icono = "fas fa-shipping-fast";
        $costoEnvioGratis = $datosFinanzas['costo_envio_gratis']['valor'] ?? 50.00;
        $moneda = $datosFinanzas['moneda_principal']['valor'] ?? 'CUP';

        $contenido = "
        <div class='row'>
            <div class='col-md-12'>
                <h4>Política de Envíos</h4>
                <p>Entregamos en todo el país con diferentes opciones de envío.</p>
                
                <div class='mt-4'>
                    <h5><i class='fas fa-truck mr-2'></i> Tiempos de Entrega</h5>
                    <ul>
                        <li><strong>Envío Estándar:</strong> 3-5 días hábiles</li>
                        <li><strong>Envío Express:</strong> 1-2 días hábiles (costo adicional)</li>
                        <li><strong>Recogida en Tienda:</strong> Disponible en 24 horas</li>
                    </ul>
                    
                    <h5 class='mt-4'><i class='fas fa-shipping-fast mr-2'></i> Costos de Envío</h5>
                    <ul>
                        <li><strong>Envío estándar:</strong> A partir de 5.00 " . $moneda . "</li>
                        <li><strong>Envío gratis:</strong> En pedidos superiores a " . $costoEnvioGratis . " " . $moneda . "</li>
                        <li><strong>Recogida en tienda:</strong> Gratis</li>
                    </ul>
                    
                    <h5 class='mt-4'><i class='fas fa-map-marker-alt mr-2'></i> Zonas de Cobertura</h5>
                    <p>Entregamos a todas las ciudades principales del país. Para zonas rurales, consulta disponibilidad.</p>
                    
                    <h5 class='mt-4'><i class='fas fa-box mr-2'></i> Seguimiento de Pedidos</h5>
                    <p>Una vez despachado tu pedido, recibirás un número de seguimiento por email.</p>
                </div>
            </div>
        </div>
        ";
        break;

    case 'devoluciones':
        $titulo = "Devoluciones";
        $icono = "fas fa-undo";
        $diasDevolucion = $datosFinanzas['dias_para_devolucion']['valor'] ?? 30;

        $contenido = "
        <div class='row'>
            <div class='col-md-12'>
                <h4>Política de Devoluciones</h4>
                <p>Tu satisfacción es nuestra prioridad. Aceptamos devoluciones bajo los siguientes términos.</p>
                
                <div class='row mt-4'>
                    <div class='col-md-6'>
                        <div class='card'>
                            <div class='card-header bg-primary text-white'>
                                <h5 class='mb-0'><i class='fas fa-calendar-check mr-2'></i> Plazos</h5>
                            </div>
                            <div class='card-body'>
                                <p>Tienes <strong>" . $diasDevolucion . " días</strong> desde la recepción del producto para solicitar una devolución.</p>
                            </div>
                        </div>
                    </div>
                    <div class='col-md-6'>
                        <div class='card'>
                            <div class='card-header bg-primary text-white'>
                                <h5 class='mb-0'><i class='fas fa-box-open mr-2'></i> Condiciones</h5>
                            </div>
                            <div class='card-body'>
                                <p>El producto debe estar en su estado original, con todos los accesorios y empaque.</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class='mt-4'>
                    <h5><i class='fas fa-sync-alt mr-2'></i> Proceso de Devolución</h5>
                    <ol>
                        <li>Solicita la devolución desde tu cuenta</li>
                        <li>Empaqueta el producto correctamente</li>
                        <li>Usa la etiqueta de envío prepagada que te enviaremos</li>
                        <li>Una vez recibido, procesaremos tu reembolso en 5-7 días hábiles</li>
                    </ol>
                </div>
            </div>
        </div>
        ";
        break;

    case 'terminos_condiciones':
        $titulo = "Términos y Condiciones";
        $icono = "fas fa-file-contract";
        $terminos = $configuracion->obtener('terminos_condiciones');

        // Obtener fecha de actualización específica para términos
        $fechaActualizacion = null;
        if (isset($datosLegal['terminos_condiciones']['fecha_actualizacion'])) {
            $fechaActualizacion = $datosLegal['terminos_condiciones']['fecha_actualizacion'];
        }

        $contenido = "
        <div class='row'>
            <div class='col-md-12'>
                <div class='card'>
                    <div class='card-header bg-primary text-white'>
                        <h4 class='mb-0'><i class='fas fa-file-contract mr-2'></i> Términos y Condiciones de Uso</h4>
                    </div>
                    
                    <div class='card-body' style='max-height: 600px; overflow-y: auto;'>
                    ";

        if ($fechaActualizacion) {
            $contenido .= "<h5>Última actualización: " . formatearFecha($fechaActualizacion) . "</h5>";
        } else {
            $contenido .= "<h5>Fecha de actualización no disponible</h5>";
        }

        $contenido .= "</br>
                        " . nl2br(htmlspecialchars($terminos ?? 'Términos y condiciones no disponibles.')) . "
                    </div>
                 </div>
                
                <div class='mt-4'>
                    <h5><i class='fas fa-info-circle mr-2'></i> Información importante:</h5>
                    <p>Al usar nuestro sitio web, aceptas los términos y condiciones establecidos. Te recomendamos leerlos detenidamente.</p>
                    
                    <div class='alert alert-warning mt-3'>
                        <h6><i class='fas fa-exclamation-triangle mr-2'></i> Atención:</h6>
                        <p>Estos términos están sujetos a cambios. Te recomendamos revisarlos periódicamente.</p>
                    </div>
                </div>
            </div>
        </div>
        ";
        break;

    case 'privacidad':
        $titulo = "Política de Privacidad";
        $icono = "fas fa-shield-halved";
        $privacidad = $configuracion->obtener('politica_privacidad');

        // Obtener fecha de actualización específica para privacidad
        $fechaActualizacion = null;
        if (isset($datosLegal['politica_privacidad']['fecha_actualizacion'])) {
            $fechaActualizacion = $datosLegal['politica_privacidad']['fecha_actualizacion'];
        }

        $contenido = "
    <div class='row'>
        <div class='col-md-12'>
            <div class='card'>
                <div class='card-header bg-primary text-white'>
                    <h4 class='mb-0'><i class='fas fa-shield-halved mr-2'></i> Política de Privacidad</h4>
                </div>
                
                <div class='card-body' style='max-height: 600px; overflow-y: auto;'>
                ";
        if ($fechaActualizacion) {
            $contenido .= "<h5>Última actualización: " . formatearFecha($fechaActualizacion) . "</h5>";
        } else {
            $contenido .= "<h5>Fecha de actualización no disponible</h5>";
        }
        $contenido .= "</br>
                    " . nl2br(htmlspecialchars($privacidad ?? 'Política de privacidad no disponible.')) . "
                </div>
            </div>
        <div class='mt-4'>
                    <h5><i class='fas fa-info-circle mr-2'></i> Información importante:</h5>
                    <p>Al usar nuestro sitio web, aceptas todas nuestras políticas. Te recomendamos leerlas detenidamente.</p>
                    
                    <div class='alert alert-warning mt-3'>
                        <h6><i class='fas fa-exclamation-triangle mr-2'></i> Atención:</h6>
                        <p>Estos políticas están sujetas a cambios. Te recomendamos revisarlas periódicamente.</p>
                    </div>
                </div>
            </div>
        </div>
    ";
        break;

    case 'contacto':
        $titulo = "Contacto";
        $icono = "fas fa-envelope";
        $nombreTienda = $datosGeneral['nombre_tienda']['valor'] ?? 'NexusBuy';
        $slogan = $datosGeneral['slogan']['valor'] ?? 'Tu tienda online de confianza';

        // Generar token CSRF para seguridad
        $csrf_token = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $csrf_token;

        // Encriptar ID de usuario si está logueado
        $id_usuario_encriptado = '';
        if (isset($_SESSION['id'])) {
            // Usar las mismas constantes CODE y KEY que en CarritoController
            if (defined('CODE') && defined('KEY')) {
                $id_usuario_encriptado = openssl_encrypt($_SESSION['id'], CODE, KEY);
            } else {
                // Fallback si no están definidas
                $id_usuario_encriptado = base64_encode($_SESSION['id']);
            }
        }

        // Obtener asuntos comunes
        $asuntosComunes = [
            'Consulta general',
            'Problema con pedido',
            'Información de producto',
            'Devolución o reembolso',
            'Sugerencia o mejora',
            'Información para vendedores',
            'Problemas técnicos',
            'Publicidad y colaboraciones'
        ];

        // Generar enlace de WhatsApp
        $whatsappLink = generarLinkWhatsApp(
            $datosContacto['telefono_contacto']['valor'] ?? '+5351004754',
            null,
            $nombreTienda
        );

        $contenido = "
    <style>
        .contacto-form label {
            font-weight: 500;
            color: #495057;
        }
        .contacto-form .form-control:focus {
            border-color: #4361ee;
            box-shadow: 0 0 0 0.2rem rgba(67, 97, 238, 0.25);
        }
        .contacto-info-card {
            border-left: 4px solid #4361ee;
        }
        .required-field::after {
            content: ' *';
            color: #dc3545;
        }
        #mensajeContador {
            font-size: 0.85rem;
            color: #6c757d;
        }
        #mensajeContador.caracteres-bajos {
            color: #ffc107;
        }
        #mensajeContador.caracteres-muy-bajos {
            color: #dc3545;
        }
        .whatsapp-link:hover {
            transform: scale(1.05);
            transition: transform 0.3s ease;
        }
        .social-channel:hover {
            opacity: 0.9;
            transition: opacity 0.3s ease;
        }
    </style>
    
    <div class='row'>
        <div class='col-md-8'>
            <div class='card'>
                <div class='card-header bg-primary text-white'>
                    <h4 class='mb-0'><i class='fas fa-paper-plane mr-2'></i> Envíanos un mensaje</h4>
                </div>
                <div class='card-body'>
                    <p class='mb-4'>Completa el formulario y nuestro equipo se pondrá en contacto contigo lo antes posible.</p>
                    
                    <form id='formContacto' class='contacto-form'>
                        <input type='hidden' name='csrf_token' value='{$csrf_token}'>
                        
                        <!-- Campo oculto para ID de usuario encriptado -->
                        " . ($id_usuario_encriptado ? "<input type='hidden' name='id_usuario_encrypted' value='{$id_usuario_encriptado}'>" : "") . "
                        
                        <div class='form-group'>
                            <label for='nombre' class='required-field'>Nombre completo</label>
                            <input type='text' class='form-control' id='nombre' name='nombre' 
                                   placeholder='Ej: Juan Pérez' required
                                   minlength='2' maxlength='100'>
                            <small class='form-text text-muted'>Mínimo 2 caracteres</small>
                        </div>
                        
                        <div class='row'>
                            <div class='col-md-6'>
                                <div class='form-group'>
                                    <label for='email' class='required-field'>Correo electrónico</label>
                                    <input type='email' class='form-control' id='email' name='email'
                                           placeholder='ejemplo@email.com' required>
                                    <small class='form-text text-muted'>Te enviaremos la respuesta a este email</small>
                                </div>
                            </div>
                            <div class='col-md-6'>
                                <div class='form-group'>
                                    <label for='telefono'>Teléfono (opcional)</label>
                                    <input type='tel' class='form-control' id='telefono' name='telefono'
                                           placeholder='+53 5 1234567' pattern='[\\d\\s\\-\\+\\(\\)]{8,20}'>
                                    <small class='form-text text-muted'>Para contactarte más rápido</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class='form-group'>
                            <label for='asunto' class='required-field'>Asunto</label>
                            <select class='form-control' id='asunto' name='asunto' required>
                                <option value=''>Selecciona un asunto</option>";

        // Agregar opciones de asunto
        foreach ($asuntosComunes as $asunto) {
            $contenido .= "<option value='{$asunto}'>{$asunto}</option>";
        }

        $contenido .= "</select>
                        </div>
                        
                        <div class='form-group'>
                            <label for='mensaje' class='required-field'>Mensaje</label>
                            <textarea class='form-control' id='mensaje' name='mensaje' rows='6' 
                                      placeholder='Describe tu consulta o problema en detalle...' 
                                      required minlength='10' maxlength='2000'></textarea>
                            <div class='d-flex justify-content-between mt-1'>
                                <small class='form-text text-muted'>Mínimo 10 caracteres</small>
                                <small id='mensajeContador'>0/2000 caracteres</small>
                            </div>
                        </div>
                        
                        <!-- Protección contra bots (honeypot) -->
                        <div class='d-none'>
                            <label for='website'>Website</label>
                            <input type='text' id='website' name='website' tabindex='-1' autocomplete='off'>
                        </div>
                        
                        <div class='form-group form-check'>
                            <input type='checkbox' class='form-check-input' id='privacidad' name='privacidad' required>
                            <label class='form-check-label' for='privacidad'>
                                Acepto la <a href='soporte.php?filtro=privacidad' target='_blank'>Política de Privacidad</a> y 
                                los <a href='soporte.php?filtro=terminos_condiciones' target='_blank'>Términos y Condiciones</a>
                            </label>
                        </div>
                        
                        <div class='mt-4'>
                            <button type='submit' class='btn btn-primary btn-lg px-5' id='btnEnviar'>
                                <i class='fas fa-paper-plane mr-2'></i> Enviar mensaje
                            </button>
                            <button type='reset' class='btn btn-outline-secondary btn-lg px-5 ml-2'>
                                <i class='fas fa-redo mr-2'></i> Limpiar
                            </button>
                        </div>
                    </form>
                    
                    <div id='respuestaContacto' class='mt-4'></div>
                </div>
            </div>
            
            <!-- Información adicional -->
            <div class='card mt-4'>
                <div class='card-body'>
                    <h5><i class='fas fa-info-circle mr-2 text-primary'></i> ¿Qué información incluir?</h5>
                    <p>Para una respuesta más rápida y precisa, te recomendamos incluir:</p>
                    <ul>
                        <li>Número de pedido (si aplica)</li>
                        <li>Nombre del producto</li>
                        <li>Fecha de compra o incidente</li>
                        <li>Capturas de pantalla si hay errores</li>
                    </ul>
                </div>
            </div>
        </div>
        
        <div class='col-md-4'>
            <div class='card contacto-info-card mb-4'>
                <div class='card-body'>
                    <h4 class='text-primary mb-3'><i class='fas fa-store mr-2'></i> $nombreTienda</h4>
                    <p class='text-muted'>$slogan</p>
                    
                    <hr class='my-3'>
                    
                    <h5><i class='fas fa-map-marker-alt mr-2 text-primary'></i> Dirección</h5>
                    <p class='mb-3'>" . ($datosContacto['direccion_principal']['valor'] ?? 'Av. Principal #123') . "</p>
                    
                    <h5 class='mt-3'><i class='fas fa-phone mr-2 text-primary'></i> Teléfono</h5>
                    <p class='mb-3'>
                        " . ($datosContacto['telefono_contacto']['valor'] ?? '+5351004754') . "
                    </p>
                    
                    <h5 class='mt-3'><i class='fas fa-envelope mr-2 text-primary'></i> Email</h5>
                    <p class='mb-3'>
                        " . ($datosContacto['email_contacto']['valor'] ?? 'ventas@nexusbuy.com') . "
                    </p>
                    
                    <h5 class='mt-3'><i class='fas fa-clock mr-2 text-primary'></i> Horario de Atención</h5>
                    <p>Lunes a Viernes: 9:00 - 18:00<br>Sábados: 10:00 - 14:00</p>
                    
                    <div class='alert alert-info mt-3'>
                        <small>
                            <i class='fas fa-info-circle mr-1'></i>
                            <strong>Tiempo de respuesta:</strong> 24-48 horas hábiles
                        </small>
                    </div>
                </div>
            </div>
            
            <!-- Canales alternativos (con WhatsApp funcional) -->
            <div class='card'>
                <div class='card-header'>
                    <h5 class='mb-0'><i class='fas fa-comments mr-2'></i> Otros canales</h5>
                </div>
                <div class='card-body'>
                    <div class='row'>
                        <div class='col-6 mb-3'>
                            <div class='text-center social-channel'>
                                <div class='rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center' 
                                     style='width: 60px; height: 60px;'>
                                    <i class='fas fa-comment-dots fa-2x'></i>
                                </div>
                                <p class='mt-2 mb-0 small'>Chat en vivo</p>
                                <small class='text-muted'>Disponible</small>
                            </div>
                        </div>
                        <div class='col-6 mb-3'>
                            <a href='{$whatsappLink}' target='_blank' class='text-decoration-none whatsapp-link'>
                                <div class='text-center social-channel'>
                                    <div class='rounded-circle bg-success text-white d-inline-flex align-items-center justify-content-center' 
                                         style='width: 60px; height: 60px;'>
                                        <i class='fab fa-whatsapp fa-2x'></i>
                                    </div>
                                    <p class='mt-2 mb-0 small'>WhatsApp</p>
                                    <small class='text-muted'>" . ($datosContacto['telefono_contacto']['valor'] ?? '+5351004754') . "</small>
                                </div>
                            </a>
                        </div>
                        <div class='col-6'>
                            <a href='https://m.me/nexusbuy' target='_blank' class='text-decoration-none'>
                                <div class='text-center social-channel'>
                                    <div class='rounded-circle bg-info text-white d-inline-flex align-items-center justify-content-center' 
                                         style='width: 60px; height: 60px;'>
                                        <i class='fab fa-facebook-messenger fa-2x'></i>
                                    </div>
                                    <p class='mt-2 mb-0 small'>Messenger</p>
                                    <small class='text-muted'>@nexusbuy</small>
                                </div>
                            </a>
                        </div>
                        <div class='col-6'>
                            <a href='https://t.me/nexusbuy_bot' target='_blank' class='text-decoration-none'>
                                <div class='text-center social-channel'>
                                    <div class='rounded-circle bg-warning text-white d-inline-flex align-items-center justify-content-center' 
                                         style='width: 60px; height: 60px;'>
                                        <i class='fab fa-telegram fa-2x'></i>
                                    </div>
                                    <p class='mt-2 mb-0 small'>Telegram</p>
                                    <small class='text-muted'>@nexusbuy_bot</small>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- FAQ rápida -->
            <div class='card mt-4'>
                <div class='card-header'>
                    <h5 class='mb-0'><i class='fas fa-question-circle mr-2'></i> Preguntas frecuentes</h5>
                </div>
                <div class='card-body'>
                    <div class='list-group list-group-flush'>
                        <a href='soporte.php?filtro=devoluciones' class='list-group-item list-group-item-action border-0 py-2'>
                            <small><i class='fas fa-undo mr-2 text-primary'></i> ¿Cómo devolver un producto?</small>
                        </a>
                        <a href='soporte.php?filtro=envios_entregas' class='list-group-item list-group-item-action border-0 py-2'>
                            <small><i class='fas fa-truck mr-2 text-primary'></i> ¿Cuánto tarda el envío?</small>
                        </a>
                        <a href='soporte.php?filtro=metodos_pago' class='list-group-item list-group-item-action border-0 py-2'>
                            <small><i class='fas fa-credit-card mr-2 text-primary'></i> ¿Qué métodos de pago aceptan?</small>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Widget de Chat en Vivo -->
    <div class='chat-toggle' id='chat-toggle-btn'>
        <i class='fas fa-comments'></i>
        <span class='chat-notification' id='chat-notification' style='display: none;'></span>
    </div>
    
    <div class='chat-container' id='chat-container'>
        <div class='chat-header'>
            <h6 class='mb-0'><i class='fas fa-headset mr-2'></i> Chat en Vivo</h6>
            <button class='chat-close btn btn-sm btn-light'>
                <i class='fas fa-times'></i>
            </button>
        </div>
        
        <div class='chat-body'>
            <!-- Pantalla de inicio -->
            <div id='chat-inicio' style='display: block;'>
                <div class='p-3 text-center'>
                    <i class='fas fa-comments fa-3x text-primary mb-3'></i>
                <h5>Chat en Vivo</h5>
                <p class='small text-muted'>Chatea directamente con nuestro equipo de soporte</p>
                
                <div class='alert alert-info mb-3'>
                    <i class='fas fa-info-circle mr-2'></i>
                    <span id='chat-agentes-estado'>Verificando disponibilidad...</span>
                </div>
                
                <form id='chat-inicio-form'>
                    <div class='form-group'>
                        <input type='text' class='form-control' id='chat-nombre' 
                               placeholder='Tu nombre' value='{$nombreUsuario}' required>
                        </div>
                        <div class='form-group'>
                        <input type='email' class='form-control' id='chat-email' 
                               placeholder='Tu email' value='{$emailUsuario}' required>
                        </div>
                        <div class='form-group'>
                        <select class='form-control' id='chat-categoria'>
                            <option value='general'>Consulta General</option>
                            <option value='tecnico'>Problema Técnico</option>
                            <option value='ventas'>Información de Venta</option>
                            <option value='devoluciones'>Devolución o Reembolso</option>
                        </select>
                    </div>
                    <div class='form-group'>
                        <textarea class='form-control' id='chat-asunto' rows='2' 
                                  placeholder='Asunto de tu consulta'></textarea>
                    </div>
                    <button type='button' id='iniciar-chat-btn' class='btn btn-primary btn-block'>
                        <i class='fas fa-comment-dots mr-2'></i> Iniciar Chat
                    </button>
                </form>
            </div>
        </div>
        
        <!-- Pantalla de conversación activa -->
        <div id='chat-conversacion' style='display: none;'>
            <div class='chat-header-secondary bg-light p-2 border-bottom'>
                <small class='text-muted' id='chat-header-info'>
                    <span id='chat-estado'>Conectando...</span>
                </small>
                <button id='chat-cerrar-btn' class='btn btn-sm btn-outline-danger'>
                    <i class='fas fa-times mr-1'></i> Cerrar
                </button>
            </div>
            
            <div class='chat-mensajes-container' id='chat-mensajes-container'>
                <!-- Los mensajes se cargan aquí -->
            </div>
            
            <div class='chat-input-container'>
                <div class='input-group'>
                    <textarea class='form-control' id='chat-mensaje-input' 
                              placeholder='Escribe tu mensaje...' rows='1'></textarea>
                    <div class='input-group-append'>
                        <button class='btn btn-primary' id='chat-enviar-btn'>
                            <i class='fas fa-paper-plane'></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
    
    <style>
    /* Estilos del chat */
    .chat-toggle {
        position: fixed;
        bottom: 20px;
        right: 20px;
        background: #25D366;
        color: white;
        width: 60px;
        height: 60px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        box-shadow: 0 4px 15px rgba(37, 211, 102, 0.3);
        z-index: 1040;
        transition: all 0.3s ease;
    }
    
    .chat-toggle:hover {
        background: #128C7E;
        transform: scale(1.05);
    }
    
    .chat-notification {
        position: absolute;
        top: -5px;
        right: -5px;
        background: #dc3545;
        color: white;
        border-radius: 50%;
        width: 20px;
        height: 20px;
        font-size: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .chat-container {
        position: fixed;
        bottom: 90px;
        right: 20px;
        width: 350px;
        max-width: 90vw;
        background: white;
        border-radius: 10px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.2);
        z-index: 1050;
        display: none;
    }
    
    .chat-header {
        background: #4361ee;
        color: white;
        padding: 15px;
        border-radius: 10px 10px 0 0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .chat-body {
        height: 400px;
        display: flex;
        flex-direction: column;
    }
    
    .chat-mensajes-container {
        flex: 1;
        overflow-y: auto;
        padding: 15px;
        background: #f8f9fa;
    }
    
    .chat-input-container {
        border-top: 1px solid #dee2e6;
        padding: 10px;
        background: white;
    }
    
    .chat-mensaje {
        margin-bottom: 10px;
    }
    
    .chat-mensaje-burbuja {
        max-width: 80%;
        padding: 10px 15px;
        border-radius: 18px;
        position: relative;
    }
    
    .chat-mensaje-contenido {
        word-wrap: break-word;
    }
    
    .chat-mensaje-hora {
        font-size: 0.75em;
        text-align: right;
        margin-top: 5px;
        opacity: 0.8;
    }
    
    .usuario-mensaje .chat-mensaje-burbuja {
        background: #4361ee;
        color: white;
        margin-left: auto;
    }
    
    .agente-mensaje .chat-mensaje-burbuja {
        background: #e9ecef;
        color: #495057;
        margin-right: auto;
    }
    
    .sistema-mensaje {
        text-align: center;
        margin: 10px 0;
        color: #6c757d;
        font-size: 0.9em;
    }
    
    .chat-mensaje-sistema {
        background: #f8f9fa;
        padding: 8px 12px;
        border-radius: 8px;
        border: 1px solid #dee2e6;
    }
    
    .chat-header-secondary {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    </style>
    
    
    ";
        break;

    case 'descargo_responsabilidad':
        $titulo = "Descargo de Responsabilidad";
        $icono = "fas fa-scale-balanced";
        $contenido = "
        <div class='row'>
            <div class='col-md-12'>
                <div class='card'>
                    <div class='card-header bg-warning text-dark'>
                        <h4 class='mb-0'><i class='fas fa-exclamation-triangle mr-2'></i> Descargo de Responsabilidad</h4>
                    </div>
                    <div class='card-body'>
                        <h5>Limitación de Responsabilidad</h5>
                        <p>" . ($datosGeneral['nombre_tienda']['valor'] ?? 'NexusBuy') . " actúa como intermediario entre vendedores y compradores. No somos propietarios de los productos listados en nuestra plataforma.</p>
                        
                        <h5 class='mt-4'>Responsabilidad del Vendedor</h5>
                        <p>Cada vendedor es responsable de:</p>
                        <ul>
                            <li>La calidad y autenticidad de sus productos</li>
                            <li>El cumplimiento de los plazos de envío</li>
                            <li>La gestión de devoluciones y reclamaciones</li>
                            <li>El cumplimiento de todas las leyes aplicables</li>
                        </ul>
                        
                        <h5 class='mt-4'>Responsabilidad del Comprador</h5>
                        <p>Como comprador, eres responsable de:</p>
                        <ul>
                            <li>Verificar la información del producto antes de comprar</li>
                            <li>Proporcionar información correcta para el envío</li>
                            <li>Revisar el producto al recibirlo</li>
                            <li>Solicitar devoluciones dentro del plazo establecido</li>
                        </ul>
                        
                        <div class='alert alert-danger mt-4'>
                            <h6><i class='fas fa-exclamation-circle mr-2'></i> Importante:</h6>
                            <p class='mb-0'>No nos hacemos responsables por daños indirectos, pérdida de beneficios o cualquier otro daño derivado del uso de nuestros servicios. Para más detalles, consulta nuestros Términos y Condiciones completos.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        ";
        break;

    case 'cookies':
        $titulo = "Política de Cookies";
        $icono = "fas fa-cookie-bite";
        $nombreTienda = $datosGeneral['nombre_tienda']['valor'] ?? 'NexusBuy';

        $contenido = "
        <div class='row'>
            <div class='col-md-12'>
                <div class='card'>
                    <div class='card-header bg-primary text-white'>
                        <h4 class='mb-0'><i class='fas fa-cookie-bite mr-2'></i> Política de Cookies</h4>
                    </div>
                    <div class='card-body'>
                        <h5>¿Qué son las cookies?</h5>
                        <p>Las cookies son pequeños archivos de texto que se almacenan en tu dispositivo cuando visitas nuestro sitio web. Nos ayudan a mejorar tu experiencia de navegación.</p>
                        
                        <h5 class='mt-4'>Tipos de cookies que utilizamos</h5>
                        <ul>
                            <li><strong>Cookies esenciales:</strong> Necesarias para el funcionamiento básico del sitio</li>
                            <li><strong>Cookies de rendimiento:</strong> Nos ayudan a entender cómo usas nuestro sitio</li>
                            <li><strong>Cookies de funcionalidad:</strong> Recuerdan tus preferencias</li>
                            <li><strong>Cookies de publicidad:</strong> Personalizan los anuncios que ves</li>
                        </ul>
                        
                        <h5 class='mt-4'>Cómo gestionar las cookies</h5>
                        <p>Puedes controlar y/o eliminar las cookies según desees. Puedes eliminar todas las cookies que ya están en tu ordenador y configurar la mayoría de los navegadores para que no las acepten.</p>
                        
                        <div class='alert alert-info mt-4'>
                            <h6><i class='fas fa-info-circle mr-2'></i> Nota:</h6>
                            <p class='mb-0'>Si desactivas las cookies, algunas funciones de " . $nombreTienda . " podrían no estar disponibles o no funcionar correctamente.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        ";
        break;

    default:
        $titulo = "Centro de Ayuda";
        $icono = "fas fa-question-circle";
        $contenido = "<p>Selecciona una sección del menú para ver información detallada.</p>";
        break;
}
?>
<style>
    /* Sobrescribir cualquier estilo que pueda estar afectando el header */
    .card-header.bg-primary h4,
    .card-header.bg-primary .mb-0,
    .card-header.bg-primary * {
        color: white !important;
    }

    /* Asegurar que los títulos dentro del contenido sean legibles */
    .card-body h1,
    .card-body h2,
    .card-body h3,
    .card-body h4,
    .card-body h5,
    .card-body h6 {
        color: #4361ee !important;
        margin-top: 1rem;
        margin-bottom: 0.5rem;
    }

    /* Estilos para el contenido de términos específicamente */
    .terminos-content {
        font-size: 14px;
        line-height: 1.6;
    }

    .terminos-content h1 {
        font-size: 1.8rem;
    }

    .terminos-content h2 {
        font-size: 1.6rem;
    }

    .terminos-content h3 {
        font-size: 1.4rem;
    }

    .terminos-content h4 {
        font-size: 1.2rem;
    }

    .terminos-content h5 {
        font-size: 1.1rem;
    }

    .terminos-content h6 {
        font-size: 1rem;
    }

    .terminos-content p {
        margin-bottom: 1rem;
    }

    .terminos-content ul,
    .terminos-content ol {
        margin-bottom: 1rem;
        padding-left: 2rem;
    }

    .terminos-content li {
        margin-bottom: 0.5rem;
    }

    /* Estilos para canales de contacto */
    .social-channel {
        transition: all 0.3s ease;
    }

    .social-channel:hover {
        transform: translateY(-5px);
    }

    .whatsapp-card {
        background-color: #25D366 !important;
        color: white !important;
        border: none;
    }

    .whatsapp-card:hover {
        background-color: #128C7E !important;
    }
</style>

<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1><i class="<?php echo $icono; ?> mr-2"></i> <?php echo $titulo; ?></h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="../index.php">Inicio</a></li>
                    <li class="breadcrumb-item active"><?php echo $titulo; ?></li>
                </ol>
            </div>
        </div>
    </div>
</section>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar con menú de soporte -->
            <div class="col-md-3">
                <div class="card">
                    <div class="card-header bg-primary">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-life-ring mr-2"></i>Secciones de Soporte
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            <a href="soporte.php?filtro=centro_ayuda"
                                class="list-group-item list-group-item-action <?php echo $filtro == 'centro_ayuda' ? 'active' : ''; ?>">
                                <i class="fas fa-question-circle mr-2"></i> Centro de Ayuda
                            </a>
                            <a href="soporte.php?filtro=metodos_pago"
                                class="list-group-item list-group-item-action <?php echo $filtro == 'metodos_pago' ? 'active' : ''; ?>">
                                <i class="fas fa-credit-card mr-2"></i> Métodos de Pago
                            </a>
                            <a href="soporte.php?filtro=envios_entregas"
                                class="list-group-item list-group-item-action <?php echo $filtro == 'envios_entregas' ? 'active' : ''; ?>">
                                <i class="fas fa-shipping-fast mr-2"></i> Envíos y Entregas
                            </a>
                            <a href="soporte.php?filtro=devoluciones"
                                class="list-group-item list-group-item-action <?php echo $filtro == 'devoluciones' ? 'active' : ''; ?>">
                                <i class="fas fa-undo mr-2"></i> Devoluciones
                            </a>
                            <a href="soporte.php?filtro=terminos_condiciones"
                                class="list-group-item list-group-item-action <?php echo $filtro == 'terminos_condiciones' ? 'active' : ''; ?>">
                                <i class="fas fa-file-contract mr-2"></i> Términos y Condiciones
                            </a>
                            <a href="soporte.php?filtro=privacidad"
                                class="list-group-item list-group-item-action <?php echo $filtro == 'privacidad' ? 'active' : ''; ?>">
                                <i class="fas fa-shield-halved mr-2"></i> Privacidad
                            </a>
                            <a href="soporte.php?filtro=descargo_responsabilidad"
                                class="list-group-item list-group-item-action <?php echo $filtro == 'descargo_responsabilidad' ? 'active' : ''; ?>">
                                <i class="fas fa-scale-balanced mr-2"></i> Descargo de Responsabilidad
                            </a>
                            <a href="soporte.php?filtro=cookies"
                                class="list-group-item list-group-item-action <?php echo $filtro == 'cookies' ? 'active' : ''; ?>">
                                <i class="fas fa-cookie-bite mr-2"></i> Cookies
                            </a>
                            <a href="soporte.php?filtro=contacto"
                                class="list-group-item list-group-item-action <?php echo $filtro == 'contacto' ? 'active' : ''; ?>">
                                <i class="fas fa-envelope mr-2"></i> Contacto
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Información de contacto rápida -->
                <div class="card mt-3">
                    <div class="card-body">
                        <h6><i class="fas fa-info-circle mr-2 text-primary"></i> Información Rápida</h6>
                        <hr class="my-2">
                        <p class="small mb-1"><strong>Tienda:</strong> <?php echo $datosGeneral['nombre_tienda']['valor'] ?? 'NexusBuy'; ?></p>
                        <p class="small mb-1"><strong>Email:</strong> <?php echo $datosContacto['email_contacto']['valor'] ?? 'ventas@nexusbuy.com'; ?></p>
                        <p class="small mb-0"><strong>Teléfono:</strong> <?php echo $datosContacto['telefono_contacto']['valor'] ?? '+5351004754'; ?></p>
                        <?php
                        // Mostrar fecha de última actualización legal si existe
                        if (isset($datosLegal['terminos_condiciones']['fecha_actualizacion'])) {
                            echo "<p class='small mt-2 mb-0'><strong>Última actualización:</strong> " .
                                formatearFecha($datosLegal['terminos_condiciones']['fecha_actualizacion']) . "</p>";
                        }
                        ?>
                    </div>
                </div>
            </div>

            <!-- Contenido principal -->
            <div class="col-md-9">
                <div class="card">
                    <div class="card-body">
                        <?php echo $contenido; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include_once 'Layouts/footer.php'; ?>

<script src="soporte.js"></script>
<script src="chat.js"></script>