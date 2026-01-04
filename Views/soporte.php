<?php
session_start();

// Verificar que el usuario está logueado para acceder al chat
if (empty($_SESSION['id'])) {
    // No redirigir, solo marcar que el usuario no está logueado
    $userData = array(
        'logueado' => false,
        'id' => '',
        'nombre' => '',
        'email' => '',
        'role' => ''
    );
} else {
    // Obtener datos del usuario si está logueado
    include_once '../Models/Usuario.php'; // Ajusta la ruta según tu estructura
    
    $usuario = new Usuario();
    $usuario->obtener_datos($_SESSION['id']);
    $datos_usuario = $usuario->objetos[0] ?? null;
    
    if ($datos_usuario) {
        // Asegurar que la sesión tiene el email
        if (empty($_SESSION['email']) && !empty($datos_usuario->email)) {
            $_SESSION['email'] = $datos_usuario->email;
        }
        
        $userData = array(
            'logueado' => true,
            'id' => $_SESSION['id'],
            'nombre' => $_SESSION['nombre'] ?? $datos_usuario->username ?? '',
            'email' => $_SESSION['email'] ?? $datos_usuario->email ?? '',
            'role' => $_SESSION['role'] ?? 'cliente'
        );
    } else {
        $userData = array(
            'logueado' => false,
            'id' => '',
            'nombre' => '',
            'email' => '',
            'role' => ''
        );
    }
}

include_once 'Layauts/header_general.php';
?>

<title>Soporte | NexusBuy</title>

<script>
    const userData = <?php echo json_encode($userData); ?>;
</script>

<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Centro de Soporte</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="../index.php">Home</a></li>
                    <li class="breadcrumb-item active">Soporte</li>
                </ol>
            </div>
        </div>
    </div>
</section>

<style>
    /* Estilos específicos para soporte.php que no están en nexusbuy.css */
    
    /* Ajustes específicos para la sección hero */
    .support-hero .container {
        max-width: 800px;
    }
    
    /* Ajustes específicos para las tarjetas de opciones */
    .support-card {
        border-top: 4px solid var(--primary);
    }
    
    .support-card:nth-child(2) {
        border-top-color: var(--success);
    }
    
    .support-card:nth-child(3) {
        border-top-color: var(--info);
    }
    
    .support-card:nth-child(4) {
        border-top-color: var(--warning);
    }
    
    /* Estilo específico para el botón de "En desarrollo" */
    .footer-link.en-desarrollo::after {
        top: 8px;
        right: 8px;
        padding: 3px 6px;
        font-size: 0.65rem;
        z-index: 1;
    }
    
    /* Ajustes para los iconos dentro de las tarjetas */
    .support-card .btn i, 
    .support-card .btn-outline-primary i {
        font-size: 0.9rem;
    }
    
    /* Estilo específico para la información de contacto */
    .contact-info {
        position: relative;
    }
    
    .contact-info:not(:last-child)::after {
        content: '';
        position: absolute;
        bottom: -12px;
        left: 0;
        right: 0;
        height: 1px;
        background: linear-gradient(to right, transparent, #e9ecef, transparent);
    }
    
    /* Estilo específico para los headers de FAQ */
    .faq-header[aria-expanded="true"] {
        background: var(--gradient-primary);
        color: white;
    }
    
    .faq-header[aria-expanded="true"] h5 {
        color: white;
    }
    
    .faq-header[aria-expanded="true"] .fa-chevron-down {
        color: white;
    }
    
    /* Ajustes para el chat en vivo */
    .live-chat-widget {
        transition: transform 0.3s ease;
    }
    
    .live-chat-widget:hover {
        transform: translateY(-5px);
    }
    
    .chat-toggle {
        position: relative;
        overflow: hidden;
    }
    
    .chat-toggle::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(45deg, transparent 30%, rgba(255,255,255,0.3) 50%, transparent 70%);
        animation: shine 3s infinite;
    }
    
    @keyframes shine {
        0% { transform: translateX(-100%); }
        100% { transform: translateX(100%); }
    }
    
    /* Estilo para los mensajes del chat */
    .chat-message.agent .message-bubble {
        position: relative;
    }
    
    .chat-message.agent .message-bubble::before {
        content: 'Agente';
        position: absolute;
        top: -18px;
        left: 0;
        font-size: 0.7rem;
        color: #6c757d;
        font-weight: 600;
    }
    
    .chat-message.user .message-bubble::before {
        content: 'Tú';
        position: absolute;
        top: -18px;
        right: 0;
        font-size: 0.7rem;
        color: var(--primary);
        font-weight: 600;
    }
    
    /* Estilo para el formulario específico */
    #support-form button[type="submit"] {
        min-width: 180px;
    }
    
    /* Ajustes responsivos específicos */
    @media (max-width: 768px) {
        .support-card {
            padding: 25px;
        }
        
        .support-card .btn {
            width: 100%;
        }
        
        .contact-form-section {
            padding: 20px;
        }
    }
    
    @media (max-width: 576px) {
        .support-hero {
            margin: -20px -15px 30px;
            border-radius: 0;
            padding: 30px 15px;
        }
        
        .faq-item {
            border-radius: 10px;
        }
        
        .contact-form-section .card-header {
            padding: 15px;
        }
        
        .contact-form-section .card-body {
            padding: 15px;
        }
    }
    
    /* Estilo para el botón de enviar cuando está cargando */
    .btn-support.loading {
        position: relative;
        color: transparent;
    }
    
    .btn-support.loading::after {
        content: '';
        position: absolute;
        left: 50%;
        top: 50%;
        transform: translate(-50%, -50%);
        width: 20px;
        height: 20px;
        border: 2px solid white;
        border-top: 2px solid transparent;
        border-radius: 50%;
        animation: spin 0.8s linear infinite;
    }

    /* Estilos para usuarios no logueados */
.chat-toggle.disabled {
    opacity: 0.6;
    cursor: not-allowed;
    pointer-events: none;
}

.chat-toggle.disabled::after {
    content: 'Inicia sesión';
    position: absolute;
    top: -25px;
    left: 50%;
    transform: translateX(-50%);
    background: #ffc107;
    color: #000;
    padding: 3px 10px;
    border-radius: 3px;
    font-size: 0.7rem;
    white-space: nowrap;
    display: none;
}

.chat-toggle.disabled:hover::after {
    display: block;
}

.message-bubble.system.warning {
    background: linear-gradient(135deg, #fff3cd, #ffc107);
    color: #856404;
    border: 1px solid #ffeaa7;
}

.message-bubble.system.error {
    background: linear-gradient(135deg, #f8d7da, #dc3545);
    color: #721c24;
    border: 1px solid #f5c6cb;
}

.btn-disabled-chat {
    position: relative;
    overflow: hidden;
}

.btn-disabled-chat::after {
    content: 'Inicia sesión';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.7);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.3s;
}

.btn-disabled-chat:hover::after {
    opacity: 1;
}

/* Notificación en el botón de chat */
.chat-toggle.notifying {
    animation: pulse-chat 1.5s infinite;
}

@keyframes pulse-chat {
    0% { box-shadow: 0 0 0 0 rgba(52, 152, 219, 0.7); }
    70% { box-shadow: 0 0 0 10px rgba(52, 152, 219, 0); }
    100% { box-shadow: 0 0 0 0 rgba(52, 152, 219, 0); }
}

/* Estilo para el formulario resaltado */
#contact-form.highlight {
    animation: highlight-form 2s ease;
}

@keyframes highlight-form {
    0% { background-color: transparent; }
    50% { background-color: rgba(52, 152, 219, 0.1); }
    100% { background-color: transparent; }
}
</style>

<section class="content">
    <div class="container-fluid">
        <!-- Hero Section -->
        <div class="support-hero">
            <div class="container">
                <h1>¿En qué podemos ayudarte?</h1>
                <p>Estamos aquí para resolver cualquier duda o problema que tengas. Elige la opción que mejor se adapte a tus necesidades.</p>
            </div>
        </div>

        <!-- Support Options -->
        <div class="support-options">
            <div class="row">
                <div class="col-lg-3 col-md-6">
                    <div class="support-card footer-link en-desarrollo">
                        <div class="support-icon">
                            <i class="fas fa-comments"></i>
                        </div>
                        <h3>Chat en Vivo</h3>
                        <p>Conversación instantánea con nuestro equipo de soporte</p>
                        <button class="btn btn-primary" onclick="toggleChat()">
                            <i class="fas fa-comment-dots mr-2"></i>Abrir Chat
                        </button>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="support-card footer-link en-desarrollo">
                        <div class="support-icon">
                            <i class="fas fa-phone"></i>
                        </div>
                        <h3>Llamada Telefónica</h3>
                        <p>Habla directamente con un agente especializado</p>
                        <a href="tel:+1234567890" class="btn btn-outline-primary">
                            <i class="fas fa-phone mr-2"></i>+1 234 567 890
                        </a>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="support-card footer-link en-desarrollo">
                        <div class="support-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <h3>Correo Electrónico</h3>
                        <p>Envíanos un email y te responderemos en menos de 24h</p>
                        <a href="mailto:soporte@nexusbuy.com" class="btn btn-outline-primary">
                            <i class="fas fa-envelope mr-2"></i>soporte@nexusbuy.com
                        </a>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="support-card footer-link en-desarrollo">
                        <div class="support-icon">
                            <i class="fas fa-file-alt"></i>
                        </div>
                        <h3>Ticket de Soporte</h3>
                        <p>Crea un ticket para problemas específicos y haz seguimiento</p>
                        <button class="btn btn-outline-primary" onclick="scrollToForm()">
                            <i class="fas fa-ticket-alt mr-2"></i>Crear Ticket
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- FAQ Section -->
        <div class="faq-section">
            <div class="card">
                <div class="card-header footer-link en-desarrollo">
                    <h3 class="card-title">
                        <i class="fas fa-question-circle mr-2"></i>
                        Preguntas Frecuentes
                    </h3>
                </div>
                <div class="card-body">
                    <div class="accordion" id="faqAccordion">
                        <!-- FAQ Item 1 -->
                        <div class="faq-item">
                            <div class="faq-header" id="headingOne" data-toggle="collapse" data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                <h5>
                                    ¿Cómo realizo un pedido?
                                    <i class="fas fa-chevron-down"></i>
                                </h5>
                            </div>
                            <div id="collapseOne" class="collapse show" aria-labelledby="headingOne" data-parent="#faqAccordion">
                                <div class="faq-body">
                                    <p>Para realizar un pedido, sigue estos pasos:</p>
                                    <ol>
                                        <li>Navega por nuestro catálogo y añade los productos deseados al carrito</li>
                                        <li>Ve a tu carrito de compras y revisa los productos</li>
                                        <li>Selecciona el método de envío y pago</li>
                                        <li>Confirma tu pedido y recibe tu confirmación por email</li>
                                    </ol>
                                </div>
                            </div>
                        </div>

                        <!-- FAQ Item 2 -->
                        <div class="faq-item">
                            <div class="faq-header" id="headingTwo" data-toggle="collapse" data-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                <h5>
                                    ¿Qué métodos de pago aceptan?
                                    <i class="fas fa-chevron-down"></i>
                                </h5>
                            </div>
                            <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#faqAccordion">
                                <div class="faq-body">
                                    <p>Aceptamos los siguientes métodos de pago:</p>
                                    <ul>
                                        <li>Tarjetas de crédito (Visa, MasterCard, American Express)</li>
                                        <li>Tarjetas de débito</li>
                                        <li>PayPal</li>
                                        <li>Transferencias bancarias</li>
                                        <li>Pago contra entrega (solo en áreas seleccionadas)</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- FAQ Item 3 -->
                        <div class="faq-item">
                            <div class="faq-header" id="headingThree" data-toggle="collapse" data-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                                <h5>
                                    ¿Cuánto tarda el envío?
                                    <i class="fas fa-chevron-down"></i>
                                </h5>
                            </div>
                            <div id="collapseThree" class="collapse" aria-labelledby="headingThree" data-parent="#faqAccordion">
                                <div class="faq-body">
                                    <p>Los tiempos de envío varían según tu ubicación:</p>
                                    <ul>
                                        <li><strong>Envío estándar:</strong> 3-5 días hábiles</li>
                                        <li><strong>Envío express:</strong> 1-2 días hábiles</li>
                                        <li><strong>Envío internacional:</strong> 7-14 días hábiles</li>
                                    </ul>
                                    <p>Recibirás un email con el número de seguimiento una vez que tu pedido sea enviado.</p>
                                </div>
                            </div>
                        </div>

                        <!-- FAQ Item 4 -->
                        <div class="faq-item">
                            <div class="faq-header" id="headingFour" data-toggle="collapse" data-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
                                <h5>
                                    ¿Puedo devolver un producto?
                                    <i class="fas fa-chevron-down"></i>
                                </h5>
                            </div>
                            <div id="collapseFour" class="collapse" aria-labelledby="headingFour" data-parent="#faqAccordion">
                                <div class="faq-body">
                                    <p>Sí, aceptamos devoluciones dentro de los 30 días posteriores a la recepción del producto. Para que la devolución sea aceptada:</p>
                                    <ul>
                                        <li>El producto debe estar en su estado original</li>
                                        <li>Debe incluir todos los accesorios y empaques</li>
                                        <li>Debe tener la etiqueta original</li>
                                    </ul>
                                    <p>Para iniciar una devolución, contacta a nuestro equipo de soporte.</p>
                                </div>
                            </div>
                        </div>

                        <!-- FAQ Item 5 -->
                        <div class="faq-item">
                            <div class="faq-header" id="headingFive" data-toggle="collapse" data-target="#collapseFive" aria-expanded="false" aria-controls="collapseFive">
                                <h5>
                                    ¿Cómo puedo rastrear mi pedido?
                                    <i class="fas fa-chevron-down"></i>
                                </h5>
                            </div>
                            <div id="collapseFive" class="collapse" aria-labelledby="headingFive" data-parent="#faqAccordion">
                                <div class="faq-body">
                                    <p>Puedes rastrear tu pedido de las siguientes maneras:</p>
                                    <ul>
                                        <li>Accede a "Mis Pedidos" en tu cuenta</li>
                                        <li>Haz clic en el enlace de seguimiento que recibiste por email</li>
                                        <li>Contacta a nuestro servicio al cliente con tu número de pedido</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contact Form Section -->
        <div class="contact-form-section" id="contact-form">
            <div class="row">
                <div class="col-lg-8 footer-link en-desarrollo">
                    <h3 class="mb-4">
                        <i class="fas fa-headset mr-2"></i>
                        Formulario de Contacto
                    </h3>
                    <form id="support-form">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">Nombre completo *</label>
                                    <input type="text" class="form-control" id="name" name="name" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="email">Correo electrónico *</label>
                                    <input type="email" class="form-control" id="email" name="email" required>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="subject">Asunto *</label>
                            <input type="text" class="form-control" id="subject" name="subject" required>
                        </div>
                        <div class="form-group">
                            <label for="department">Departamento *</label>
                            <select class="form-control" id="department" name="department" required>
                                <option value="">Selecciona un departamento</option>
                                <option value="sales">Ventas</option>
                                <option value="technical">Soporte Técnico</option>
                                <option value="billing">Facturación</option>
                                <option value="shipping">Envíos</option>
                                <option value="returns">Devoluciones</option>
                                <option value="other">Otro</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="message">Mensaje *</label>
                            <textarea class="form-control" id="message" name="message" rows="5" required></textarea>
                        </div>
                        <div class="form-group">
                            <label for="attachment">Archivo adjunto (opcional)</label>
                            <input type="file" class="form-control-file" id="attachment" name="attachment">
                            <small class="form-text text-muted">Puedes adjuntar imágenes o documentos (máx. 5MB)</small>
                        </div>
                        <button type="submit" class="btn btn-support">
                            <i class="fas fa-paper-plane mr-2"></i>Enviar Mensaje
                        </button>
                    </form>
                </div>
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header footer-link en-desarrollo">
                            <h5 class="card-title mb-0" style="color:black">
                                <i class="fas fa-info-circle mr-2"></i>
                                Información de Contacto
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="contact-info mb-4">
                                <h6 style="color:black"><i class="fas fa-clock mr-2"></i>Horario de Atención</h6>
                                <p class="mb-2">Lunes a Viernes: 9:00 - 18:00</p>
                                <p>Sábados: 9:00 - 14:00</p>
                            </div>
                            <div class="contact-info mb-4">
                                <h6><i class="fas fa-phone mr-2"></i>Teléfono</h6>
                                <p>+1 (234) 567-890</p>
                            </div>
                            <div class="contact-info mb-4">
                                <h6><i class="fas fa-envelope mr-2"></i>Email</h6>
                                <p>soporte@nexusbuy.com</p>
                            </div>
                            <div class="contact-info">
                                <h6><i class="fas fa-map-marker-alt mr-2"></i>Dirección</h6>
                                <p>Av. Principal 123<br>Ciudad, País 12345</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Live Chat Widget -->
<div class="live-chat-widget">
    <div class="chat-toggle" onclick="toggleChat()">
        <i class="fas fa-comments"></i>
    </div>
    <div class="chat-window" id="chatWindow">
        <div class="chat-header">
            <div>
                <strong>Soporte en Vivo</strong>
                <div class="small">Estamos en línea</div>
            </div>
            <button class="btn btn-sm btn-light" onclick="toggleChat()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="chat-body" id="chatBody">
            <div class="chat-message agent">
                <div class="message-bubble agent">
                    ¡Hola! Soy Ana, tu agente de soporte. ¿En qué puedo ayudarte hoy?
                </div>
            </div>
        </div>
        <div class="chat-input">
            <div class="input-group">
                <input type="text" class="form-control" id="chatInput" placeholder="Escribe tu mensaje...">
                <div class="input-group-append">
                    <button class="btn btn-primary" onclick="sendMessage()">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- <script>
    // Chat functionality
    let chatOpen = false;

    function toggleChat() {
        const chatWindow = document.getElementById('chatWindow');
        chatOpen = !chatOpen;
        chatWindow.style.display = chatOpen ? 'block' : 'none';
    }

    function sendMessage() {
        const input = document.getElementById('chatInput');
        const message = input.value.trim();
        
        if (message) {
            const chatBody = document.getElementById('chatBody');
            
            // Add user message
            const userMessage = document.createElement('div');
            userMessage.className = 'chat-message user';
            userMessage.innerHTML = `
                <div class="message-bubble user">
                    ${message}
                </div>
            `;
            chatBody.appendChild(userMessage);
            
            // Clear input
            input.value = '';
            
            // Scroll to bottom
            chatBody.scrollTop = chatBody.scrollHeight;
            
            // Simulate agent response
            setTimeout(() => {
                const agentMessage = document.createElement('div');
                agentMessage.className = 'chat-message agent';
                agentMessage.innerHTML = `
                    <div class="message-bubble agent">
                        He recibido tu mensaje. Un momento mientras reviso la información...
                    </div>
                `;
                chatBody.appendChild(agentMessage);
                chatBody.scrollTop = chatBody.scrollHeight;
            }, 1000);
        }
    }

    // Form submission
    document.getElementById('support-form').addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Show loading state
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Enviando...';
        submitBtn.disabled = true;
        
        // Simulate form submission
        setTimeout(() => {
            Swal.fire({
                icon: 'success',
                title: '¡Mensaje enviado!',
                text: 'Hemos recibido tu consulta. Te contactaremos dentro de las próximas 24 horas.',
                timer: 3000,
                showConfirmButton: false
            });
            
            // Reset form and button
            this.reset();
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        }, 2000);
    });

    // Scroll to form function
    function scrollToForm() {
        document.getElementById('contact-form').scrollIntoView({
            behavior: 'smooth'
        });
    }

    // Enter key for chat
    document.getElementById('chatInput').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            sendMessage();
        }
    });

    // FAQ accordion enhancement
    document.querySelectorAll('.faq-header').forEach(header => {
        header.addEventListener('click', function() {
            const icon = this.querySelector('.fa-chevron-down');
            icon.style.transform = this.getAttribute('aria-expanded') === 'true' ? 'rotate(0deg)' : 'rotate(180deg)';
        });
    });
</script> -->

<?php
include_once 'Layauts/footer_general.php';
?>

<script src="soporte.js"></script>