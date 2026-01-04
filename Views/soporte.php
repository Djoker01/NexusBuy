<?php
// Soporte.php
session_start();
include_once 'Layauts/header_general.php';

// Obtener el filtro de la URL
$filtro = isset($_GET['filtro']) ? $_GET['filtro'] : 'centro_ayuda';

// Configurar título y contenido según el filtro
$titulo = "";
$contenido = "";
$icono = "";

switch($filtro) {
    case 'centro_ayuda':
        $titulo = "Centro de Ayuda";
        $icono = "fas fa-question-circle";
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

                <!-- Preguntas Frecuentes -->
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
                                <p>Para realizar un pedido sigue estos pasos:</p>
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

                    <!-- FAQ 3 -->
                    <div class="card mb-2 faq-item">
                        <div class="card-header" id="faqHeading3">
                            <h5 class="mb-0">
                                <button class="btn btn-link w-100 text-left d-flex justify-content-between align-items-center" 
                                        type="button" data-toggle="collapse" data-target="#faq3" 
                                        aria-expanded="false" aria-controls="faq3">
                                    <span>
                                        <i class="fas fa-question-circle mr-2 text-primary"></i>
                                        ¿Cómo puedo rastrear mi pedido?
                                    </span>
                                    <i class="fas fa-chevron-down"></i>
                                </button>
                            </h5>
                        </div>
                        <div id="faq3" class="collapse" aria-labelledby="faqHeading3" data-parent="#faqAccordion">
                            <div class="card-body">
                                <p>Para rastrear tu pedido:</p>
                                <ol>
                                    <li>Inicia sesión en tu cuenta</li>
                                    <li>Ve a "Mis pedidos"</li>
                                    <li>Haz clic en el pedido que deseas rastrear</li>
                                    <li>Verás el número de seguimiento y enlace a la paquetería</li>
                                </ol>
                                <p>También recibirás un email con el número de seguimiento cuando tu pedido sea despachado.</p>
                            </div>
                        </div>
                    </div>

                    <!-- FAQ 4 -->
                    <div class="card mb-2 faq-item">
                        <div class="card-header" id="faqHeading4">
                            <h5 class="mb-0">
                                <button class="btn btn-link w-100 text-left d-flex justify-content-between align-items-center" 
                                        type="button" data-toggle="collapse" data-target="#faq4" 
                                        aria-expanded="false" aria-controls="faq4">
                                    <span>
                                        <i class="fas fa-question-circle mr-2 text-primary"></i>
                                        ¿Qué métodos de pago aceptan?
                                    </span>
                                    <i class="fas fa-chevron-down"></i>
                                </button>
                            </h5>
                        </div>
                        <div id="faq4" class="collapse" aria-labelledby="faqHeading4" data-parent="#faqAccordion">
                            <div class="card-body">
                                <p>Aceptamos los siguientes métodos de pago:</p>
                                <div class="row">
                                    <div class="col-md-6">
                                        <ul>
                                            <li><i class="fab fa-cc-visa text-primary mr-2"></i> Tarjetas Visa</li>
                                            <li><i class="fab fa-cc-mastercard text-primary mr-2"></i> MasterCard</li>
                                            <li><i class="fab fa-cc-amex text-primary mr-2"></i> American Express</li>
                                        </ul>
                                    </div>
                                    <div class="col-md-6">
                                        <ul>
                                            <li><i class="fab fa-paypal text-primary mr-2"></i> PayPal</li>
                                            <li><i class="fas fa-university text-primary mr-2"></i> Transferencia bancaria</li>
                                            <li><i class="fas fa-money-bill-wave text-primary mr-2"></i> Pago contra entrega</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- FAQ 5 -->
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
                                <p class="text-muted small">Tienes 30 días desde la recepción para solicitar devoluciones.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sección de contacto rápido -->
                <div class="row mt-5">
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-body">
                                <h5><i class="fas fa-headset mr-2 text-primary"></i> ¿No encontraste tu respuesta?</h5>
                                <p class="mb-4">Nuestro equipo de soporte está listo para ayudarte personalmente.</p>
                                
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <div class="text-center p-3 border rounded">
                                            <i class="fas fa-phone fa-2x text-primary mb-2"></i>
                                            <h6>Llamarnos</h6>
                                            <p class="small mb-0">+1 234 567 890</p>
                                            <p class="small text-muted">Lun-Vie 9:00-18:00</p>
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <div class="text-center p-3 border rounded">
                                            <i class="fas fa-envelope fa-2x text-primary mb-2"></i>
                                            <h6>Email</h6>
                                            <p class="small mb-0">soporte@nexusbuy.com</p>
                                            <p class="small text-muted">Respuesta en 24h</p>
                                        </div>
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
        </div>
<!-- Estilos específicos para centro de ayuda -->
        <style>
        .card-categoria-ayuda {
            color: inherit;
            text-decoration: none;
            border: 1px solid #dee2e6;
            transition: all 0.3s ease;
        }
        
        .card-categoria-ayuda:hover {
            text-decoration: none;
            color: inherit;
        }
        
        .hover-shadow:hover {
            box-shadow: 0 6px 20px rgba(0,0,0,0.1) !important;
        }
        
        .faq-item .card-header {
            background-color: rgba(67, 97, 238, 0.05);
            border-bottom: 1px solid rgba(67, 97, 238, 0.1);
        }
        
        .faq-item .btn-link {
            color: #333;
            font-weight: 500;
            text-decoration: none;
        }
        
        .faq-item .btn-link:hover {
            color: var(--primary);
        }
        
        .faq-item .collapse.show {
            background-color: #f8f9fa;
        }
        
        #buscarAyuda:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 0.2rem rgba(67, 97, 238, 0.25);
        }
        
        @media (max-width: 768px) {
            .card-categoria-ayuda {
                margin-bottom: 15px;
            }
            
            .faq-item .btn-link {
                font-size: 0.95rem;
            }
        }
        </style>
        ';
        break;

    case 'metodos_pago':
        $titulo = "Métodos de Pago";
        $icono = "fas fa-credit-card";
        $contenido = "
        <div class='row'>
            <div class='col-md-12'>
                <h4>Formas de pago aceptadas</h4>
                <p>Aceptamos múltiples métodos de pago para tu comodidad.</p>
                
                <div class='row mt-4'>
                    <div class='col-md-3 text-center mb-4'>
                        <div class='card h-100'>
                            <div class='card-body'>
                                <i class='fab fa-cc-visa fa-3x text-primary mb-3'></i>
                                <h5>Tarjetas de Crédito</h5>
                                <p>Visa, MasterCard, American Express</p>
                            </div>
                        </div>
                    </div>
                    <div class='col-md-3 text-center mb-4'>
                        <div class='card h-100'>
                            <div class='card-body'>
                                <i class='fas fa-university fa-3x text-primary mb-3'></i>
                                <h5>Transferencia Bancaria</h5>
                                <p>Transferencia a nuestra cuenta</p>
                            </div>
                        </div>
                    </div>
                    <div class='col-md-3 text-center mb-4'>
                        <div class='card h-100'>
                            <div class='card-body'>
                                <i class='fab fa-paypal fa-3x text-primary mb-3'></i>
                                <h5>PayPal</h5>
                                <p>Pago seguro a través de PayPal</p>
                            </div>
                        </div>
                    </div>
                    <div class='col-md-3 text-center mb-4'>
                        <div class='card h-100'>
                            <div class='card-body'>
                                <i class='fas fa-money-bill-wave fa-3x text-primary mb-3'></i>
                                <h5>Pago Contra Entrega</h5>
                                <p>Paga cuando recibas tu pedido</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        ";
        break;

    case 'envios_entregas':
        $titulo = "Envíos y Entregas";
        $icono = "fas fa-shipping-fast";
        $contenido = "
        <div class='row'>
            <div class='col-md-12'>
                <h4>Política de Envíos</h4>
                <p>Entregamos en todo el país con diferentes opciones de envío.</p>
                
                <div class='mt-4'>
                    <h5><i class='fas fa-truck mr-2'></i> Tiempos de Entrega</h5>
                    <ul>
                        <li><strong>Envío Estándar:</strong> 3-5 días hábiles</li>
                        <li><strong>Envío Express:</strong> 1-2 días hábiles</li>
                        <li><strong>Recogida en Tienda:</strong> Disponible en 24 horas</li>
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
                                <p>Tienes <strong>30 días</strong> desde la recepción del producto para solicitar una devolución.</p>
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
        $contenido = "
        <div class='row'>
            <div class='col-md-12'>
                <h4>Términos de Uso</h4>
                <p>Al usar nuestro sitio web, aceptas los siguientes términos y condiciones.</p>
                
                <div class='mt-4'>
                    <h5><i class='fas fa-user-check mr-2'></i> Uso del Sitio</h5>
                    <p>El contenido de este sitio es para tu uso personal y no comercial. No puedes modificar, copiar o distribuir el contenido sin autorización.</p>
                    
                    <h5 class='mt-4'><i class='fas fa-shopping-bag mr-2'></i> Compras</h5>
                    <p>Al realizar una compra, confirmas que eres mayor de edad y que la información proporcionada es veraz.</p>
                    
                    <h5 class='mt-4'><i class='fas fa-lock mr-2'></i> Privacidad</h5>
                    <p>Protegemos tu información personal según nuestra Política de Privacidad.</p>
                    
                    <h5 class='mt-4'><i class='fas fa-balance-scale mr-2'></i> Limitación de Responsabilidad</h5>
                    <p>No nos hacemos responsables por daños indirectos derivados del uso de nuestros productos o servicios.</p>
                </div>
            </div>
        </div>
        ";
        break;

    case 'contacto':
        $titulo = "Contacto";
        $icono = "fas fa-envelope";
        $contenido = "
        <div class='row'>
            <div class='col-md-8'>
                <h4>Contáctanos</h4>
                <p>Estamos aquí para ayudarte. Escríbenos y te responderemos pronto.</p>
                
                <form id='formContacto' class='mt-4'>
                    <div class='form-group'>
                        <label for='nombre'>Nombre completo</label>
                        <input type='text' class='form-control' id='nombre' required>
                    </div>
                    <div class='form-group'>
                        <label for='email'>Correo electrónico</label>
                        <input type='email' class='form-control' id='email' required>
                    </div>
                    <div class='form-group'>
                        <label for='asunto'>Asunto</label>
                        <select class='form-control' id='asunto' required>
                            <option value=''>Selecciona un asunto</option>
                            <option value='consulta'>Consulta general</option>
                            <option value='problema'>Problema con pedido</option>
                            <option value='devolucion'>Devolución</option>
                            <option value='sugerencia'>Sugerencia</option>
                        </select>
                    </div>
                    <div class='form-group'>
                        <label for='mensaje'>Mensaje</label>
                        <textarea class='form-control' id='mensaje' rows='5' required></textarea>
                    </div>
                    <button type='submit' class='btn btn-primary'>
                        <i class='fas fa-paper-plane mr-2'></i> Enviar mensaje
                    </button>
                </form>
            </div>
            <div class='col-md-4'>
                <div class='card'>
                    <div class='card-body'>
                        <h5><i class='fas fa-map-marker-alt mr-2'></i> Dirección</h5>
                        <p>Av. Principal 123, Ciudad</p>
                        
                        <h5 class='mt-4'><i class='fas fa-phone mr-2'></i> Teléfono</h5>
                        <p>+1 234 567 890</p>
                        
                        <h5 class='mt-4'><i class='fas fa-envelope mr-2'></i> Email</h5>
                        <p>info@nexusbuy.com</p>
                        
                        <h5 class='mt-4'><i class='fas fa-clock mr-2'></i> Horario de Atención</h5>
                        <p>Lunes a Viernes: 9:00 - 18:00<br>Sábados: 10:00 - 14:00</p>
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

<div class="content-wrapper">
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
                        <div class="card-header">
                            <h5 class="card-title mb-0">Secciones de Soporte</h5>
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
                                <a href="soporte.php?filtro=contacto" 
                                   class="list-group-item list-group-item-action <?php echo $filtro == 'contacto' ? 'active' : ''; ?>">
                                    <i class="fas fa-envelope mr-2"></i> Contacto
                                </a>
                            </div>
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
</div>

<?php include_once 'Layauts/footer_general.php'; ?>

<script>
        document.addEventListener('DOMContentLoaded', function() {
            const formContacto = document.getElementById('formContacto');
            if (formContacto) {
                formContacto.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    // Validación simple
                    const nombre = document.getElementById('nombre').value;
                    const email = document.getElementById('email').value;
                    const mensaje = document.getElementById('mensaje').value;
                    
                    if (!nombre || !email || !mensaje) {
                        Swal.fire('Error', 'Por favor completa todos los campos requeridos', 'error');
                        return;
                    }
                    
                    // Simular envío (aquí iría tu AJAX real)
                    Swal.fire({
                        icon: 'success',
                        title: '¡Mensaje enviado!',
                        text: 'Te contactaremos en 24-48 horas.',
                        timer: 3000,
                        showConfirmButton: false
                    });
                    
                    // Limpiar formulario
                    formContacto.reset();
                });
            }
        });

        $(document).ready(function() {
            // Búsqueda en FAQ
            $("#buscarAyuda, #btnBuscarAyuda").on("input click", function() {
                const searchTerm = $("#buscarAyuda").val().toLowerCase().trim();
                
                if (searchTerm.length > 0) {
                    $(".faq-item").each(function() {
                        const question = $(this).find(".btn-link span").text().toLowerCase();
                        const answer = $(this).find(".card-body").text().toLowerCase();
                        
                        if (question.includes(searchTerm) || answer.includes(searchTerm)) {
                            $(this).show();
                            // Expandir si está colapsado
                            if ($(this).find(".collapse").hasClass("show") === false) {
                                $(this).find("[data-toggle=\"collapse\"]").trigger("click");
                            }
                        } else {
                            $(this).hide();
                        }
                    });
                    
                    // Mostrar contador de resultados
                    const visibleCount = $(".faq-item:visible").length;
                    if (visibleCount === 0) {
                        $("#resultadosBusqueda").remove();
                        $("#faqAccordion").before(
                            `<div id="resultadosBusqueda" class="alert alert-info">
                                <i class="fas fa-info-circle mr-2"></i>
                                No encontramos resultados para "${searchTerm}". 
                                <a href="soporte.php?filtro=contacto" class="alert-link">Contáctanos</a> para ayuda personalizada.
                            </div>`
                        );
                    } else {
                        $("#resultadosBusqueda").remove();
                        $("#faqAccordion").before(
                            `<div id="resultadosBusqueda" class="alert alert-success">
                                <i class="fas fa-check-circle mr-2"></i>
                                Encontramos ${visibleCount} resultado(s) para "${searchTerm}"
                            </div>`
                        );
                    }
                } else {
                    // Mostrar todos si no hay búsqueda
                    $(".faq-item").show();
                    $("#resultadosBusqueda").remove();
                    // Colapsar todos excepto el primero
                    $(".faq-item .collapse:not(#faq1)").removeClass("show");
                }
            });

            // Efecto hover en categorías
            $(".card-categoria-ayuda").hover(
                function() {
                    $(this).css({
                        "transform": "translateY(-5px)",
                        "box-shadow": "0 8px 25px rgba(67, 97, 238, 0.15)",
                        "transition": "all 0.3s ease"
                    });
                },
                function() {
                    $(this).css({
                        "transform": "translateY(0)",
                        "box-shadow": "none"
                    });
                }
            );

            // Auto-completar búsqueda con sugerencias
            const sugerencias = [
                "devolución", "reembolso", "pago", "tarjeta", "envío", 
                "seguimiento", "pedido", "cancelar", "cuenta", "contraseña",
                "factura", "garantía", "cambio", "talla", "color"
            ];
            
            $("#buscarAyuda").on("focus", function() {
                if ($(this).val() === "") {
                    $(this).attr("placeholder", "Ejemplo: ¿cómo cambio mi contraseña?");
                }
            });

            // Animación para FAQs
            $(".faq-item .btn-link").click(function() {
                const icon = $(this).find(".fa-chevron-down, .fa-chevron-up");
                if ($(this).attr("aria-expanded") === "true") {
                    icon.removeClass("fa-chevron-down").addClass("fa-chevron-up");
                } else {
                    icon.removeClass("fa-chevron-up").addClass("fa-chevron-down");
                }
            });

            // Cargar preguntas frecuentes desde localStorage si existe
            function cargarFAQsGuardadas() {
                const faqsGuardadas = localStorage.getItem("nexusbuy_faqs");
                if (faqsGuardadas) {
                    // Aquí podrías cargar FAQs personalizadas
                    console.log("FAQs personalizadas disponibles");
                }
            }
            cargarFAQsGuardadas();
        });
        </script>
