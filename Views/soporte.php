<?php
include_once 'Layauts/header_general.php';
?>

<title>Soporte | NexusBuy</title>

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
    .support-hero {
        background: var(--gradient-primary);
        color: white;
        padding: 60px 0;
        border-radius: var(--border-radius);
        margin-bottom: 40px;
        text-align: center;
    }

    .support-hero h1 {
        font-size: 2.5rem;
        font-weight: 700;
        margin-bottom: 1rem;
    }

    .support-hero p {
        font-size: 1.2rem;
        opacity: 0.9;
        max-width: 600px;
        margin: 0 auto;
    }

    .support-options {
        margin-bottom: 40px;
    }

    .support-card {
        border: none;
        border-radius: var(--border-radius);
        box-shadow: var(--shadow);
        transition: var(--transition);
        height: 100%;
        text-align: center;
        padding: 30px 20px;
        margin-bottom: 20px;
    }

    .support-card:hover {
        transform: translateY(-5px);
        box-shadow: var(--shadow-hover);
    }

    .support-icon {
        width: 80px;
        height: 80px;
        background: var(--gradient-primary);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 20px;
        color: white;
        font-size: 2rem;
    }

    .support-card h3 {
        font-weight: 600;
        margin-bottom: 15px;
        color: var(--dark);
    }

    .support-card p {
        color: #666;
        margin-bottom: 20px;
    }

    .support-card .btn {
        border-radius: 25px;
        padding: 8px 25px;
        font-weight: 500;
    }

    .faq-section {
        margin-bottom: 40px;
    }

    .faq-item {
        border: none;
        border-radius: var(--border-radius);
        box-shadow: var(--shadow);
        margin-bottom: 15px;
        overflow: hidden;
    }

    .faq-header {
        background: white;
        border: none;
        padding: 20px;
        cursor: pointer;
        transition: var(--transition);
    }

    .faq-header:hover {
        background: #f8f9fa;
    }

    .faq-header h5 {
        margin: 0;
        font-weight: 600;
        color: var(--dark);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .faq-header .fa-chevron-down {
        transition: transform 0.3s ease;
    }

    .faq-header[aria-expanded="true"] .fa-chevron-down {
        transform: rotate(180deg);
    }

    .faq-body {
        padding: 20px;
        background: #f8f9fa;
        border-top: 1px solid #e9ecef;
    }

    .contact-form-section {
        background: white;
        border-radius: var(--border-radius);
        box-shadow: var(--shadow);
        padding: 30px;
        margin-bottom: 40px;
    }

    .form-group {
        margin-bottom: 1.5rem;
    }

    .form-group label {
        font-weight: 600;
        color: var(--dark);
        margin-bottom: 0.5rem;
    }

    .form-control {
        border: 2px solid #e9ecef;
        border-radius: 8px;
        padding: 12px 15px;
        font-size: 1rem;
        transition: var(--transition);
    }

    .form-control:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 0.2rem rgba(67, 97, 238, 0.25);
    }

    .btn-support {
        background: var(--gradient-primary);
        border: none;
        border-radius: 8px;
        padding: 12px 30px;
        font-size: 1rem;
        font-weight: 600;
        transition: var(--transition);
    }

    .btn-support:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(67, 97, 238, 0.4);
    }

    .status-badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
        margin-left: 10px;
    }

    .status-open {
        background: #d4edda;
        color: #155724;
    }

    .status-pending {
        background: #fff3cd;
        color: #856404;
    }

    .status-resolved {
        background: #d1ecf1;
        color: #0c5460;
    }

    .ticket-card {
        border: none;
        border-radius: var(--border-radius);
        box-shadow: var(--shadow);
        margin-bottom: 15px;
        transition: var(--transition);
    }

    .ticket-card:hover {
        box-shadow: var(--shadow-hover);
    }

    .ticket-header {
        background: #f8f9fa;
        padding: 15px 20px;
        border-bottom: 1px solid #e9ecef;
        display: flex;
        justify-content: between;
        align-items: center;
    }

    .ticket-body {
        padding: 20px;
    }

    .live-chat-widget {
        position: fixed;
        bottom: 20px;
        right: 20px;
        z-index: 1000;
    }

    .chat-toggle {
        width: 60px;
        height: 60px;
        background: var(--gradient-primary);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.5rem;
        box-shadow: var(--shadow-hover);
        cursor: pointer;
        transition: var(--transition);
    }

    .chat-toggle:hover {
        transform: scale(1.1);
    }

    .chat-window {
        position: absolute;
        bottom: 70px;
        right: 0;
        width: 350px;
        background: white;
        border-radius: var(--border-radius);
        box-shadow: var(--shadow-hover);
        display: none;
    }

    .chat-header {
        background: var(--gradient-primary);
        color: white;
        padding: 15px 20px;
        border-radius: var(--border-radius) var(--border-radius) 0 0;
        display: flex;
        justify-content: between;
        align-items: center;
    }

    .chat-body {
        padding: 20px;
        max-height: 400px;
        overflow-y: auto;
    }

    .chat-message {
        margin-bottom: 15px;
        display: flex;
        align-items: flex-start;
    }

    .chat-message.user {
        justify-content: flex-end;
    }

    .message-bubble {
        max-width: 80%;
        padding: 12px 15px;
        border-radius: 18px;
        font-size: 0.9rem;
    }

    .message-bubble.agent {
        background: #f1f3f5;
        color: var(--dark);
        border-bottom-left-radius: 5px;
    }

    .message-bubble.user {
        background: var(--gradient-primary);
        color: white;
        border-bottom-right-radius: 5px;
    }

    .chat-input {
        padding: 15px;
        border-top: 1px solid #e9ecef;
    }

    @media (max-width: 768px) {
        .support-hero {
            padding: 40px 0;
        }

        .support-hero h1 {
            font-size: 2rem;
        }

        .chat-window {
            width: 300px;
            right: -50px;
        }
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
                            <h5 class="card-title mb-0">
                                <i class="fas fa-info-circle mr-2"></i>
                                Información de Contacto
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="contact-info mb-4">
                                <h6><i class="fas fa-clock mr-2"></i>Horario de Atención</h6>
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

<script>
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
</script>

<?php
include_once 'Layauts/footer_general.php';
?>