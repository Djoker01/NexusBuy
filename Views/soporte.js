// soporte.js - Versión solo para usuarios logueados
let chatOpen = false;
let currentEmail = '';
let checkingMessages = false;
let messageCheckInterval = null;
let userName = '';
let userId = null;
let isAgentTyping = false;
let isUserLoggedIn = false;

// Inicializar chat
function initChat() {
    console.log('Inicializando chat...');
    console.log('Datos del usuario:', userData);
    
    // Verificar si el usuario está logueado
    if (userData && userData.logueado) {
        isUserLoggedIn = true;
        userId = userData.id;
        userName = userData.nombre || '';
        currentEmail = userData.email || '';
        
        console.log('Usuario logueado:', { 
            id: userId, 
            nombre: userName, 
            email: currentEmail 
        });
        
        // Verificar si el email es válido
        if (currentEmail && validateEmail(currentEmail)) {
            loadMessages();
            startMessageChecking();
        } else {
            console.warn('Email no válido para el usuario logueado');
            showChatWarning('Tu email no está configurado correctamente. Actualiza tu perfil para usar el chat.');
        }
        
        // Mostrar mensaje de bienvenida
        showWelcomeMessage();
        
    } else {
        // Usuario no logueado
        isUserLoggedIn = false;
        console.log('Usuario no logueado');
        
        // Mostrar mensaje de que necesita iniciar sesión
        showLoginRequiredMessage();
        
        // Deshabilitar el botón de chat
        disableChatButton();
    }
}

// Mostrar mensaje de bienvenida para usuarios logueados
function showWelcomeMessage() {
    const chatBody = document.getElementById('chatBody');
    if (!chatBody) return;
    
    const welcomeName = userName || 'Usuario';
    chatBody.innerHTML = `
        <div class="chat-message agent">
            <div class="message-bubble agent">
                <div class="message-sender">Agente</div>
                <div class="message-text">¡Hola ${welcomeName}! Soy tu agente de soporte. ¿En qué puedo ayudarte hoy?</div>
                <div class="message-time">${new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}</div>
            </div>
        </div>
    `;
}

// Mostrar mensaje de que necesita iniciar sesión
function showLoginRequiredMessage() {
    const chatBody = document.getElementById('chatBody');
    if (!chatBody) return;
    
    chatBody.innerHTML = `
        <div class="chat-message system">
            <div class="message-bubble system">
                <div class="message-sender">Sistema</div>
                <div class="message-text">
                    <p>💡 Para usar el chat en vivo, necesitas iniciar sesión.</p>
                    <div class="text-center mt-3">
                        <a href="login.php" class="btn btn-primary btn-sm">
                            <i class="fas fa-sign-in-alt mr-2"></i>Iniciar Sesión
                        </a>
                        <a href="register.php" class="btn btn-outline-primary btn-sm ml-2">
                            <i class="fas fa-user-plus mr-2"></i>Registrarse
                        </a>
                    </div>
                </div>
            </div>
        </div>
    `;
}

// Deshabilitar botón de chat para usuarios no logueados
function disableChatButton() {
    // Deshabilitar el botón flotante
    const chatToggle = document.querySelector('.chat-toggle');
    if (chatToggle) {
        chatToggle.style.opacity = '0.6';
        chatToggle.style.cursor = 'not-allowed';
        chatToggle.title = 'Inicia sesión para usar el chat';
        
        // Remover el evento click original
        chatToggle.onclick = function(e) {
            e.preventDefault();
            showLoginAlert();
        };
    }
    
    // Deshabilitar el botón en la tarjeta de chat
    const chatCardButton = document.querySelector('.support-card .btn-primary');
    if (chatCardButton && chatCardButton.textContent.includes('Abrir Chat')) {
        chatCardButton.disabled = true;
        chatCardButton.innerHTML = '<i class="fas fa-sign-in-alt mr-2"></i>Iniciar Sesión';
        chatCardButton.onclick = function(e) {
            e.preventDefault();
            showLoginAlert();
        };
    }
}

// Mostrar alerta para iniciar sesión
function showLoginAlert() {
    Swal.fire({
        title: 'Inicio de sesión requerido',
        html: `
            <div class="text-left">
                <p>Para usar el chat en vivo, necesitas iniciar sesión en tu cuenta.</p>
                <div class="mt-4 text-center">
                    <a href="login.php?redirect=soporte.php" 
                       class="btn btn-primary btn-lg btn-block mb-2">
                        <i class="fas fa-sign-in-alt mr-2"></i>Iniciar Sesión
                    </a>
                    <p class="small text-muted">¿No tienes cuenta? 
                        <a href="register.php" class="text-primary">Regístrate aquí</a>
                    </p>
                </div>
            </div>
        `,
        icon: 'info',
        showCancelButton: true,
        confirmButtonText: 'Ir a Login',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = 'login.php?redirect=soporte.php';
        }
    });
}

// Validar email
function validateEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

// Toggle chat - Solo para usuarios logueados
function toggleChat() {
    // Verificar si el usuario está logueado
    if (!isUserLoggedIn) {
        showLoginAlert();
        return;
    }
    
    // Verificar si el email es válido
    if (!currentEmail || !validateEmail(currentEmail)) {
        showChatWarning('Por favor, actualiza tu email en tu perfil para usar el chat.');
        return;
    }
    
    const chatWindow = document.getElementById('chatWindow');
    const chatToggle = document.querySelector('.chat-toggle');
    
    chatOpen = !chatOpen;
    chatWindow.style.display = chatOpen ? 'block' : 'none';
    
    if (chatOpen) {
        // Agregar clase activa al botón
        if (chatToggle) {
            chatToggle.classList.add('active');
        }
        
        // Cargar mensajes
        loadMessages();
        
        // Iniciar verificación de mensajes
        startMessageChecking();
        
        // Enfocar el input
        setTimeout(() => {
            const chatInput = document.getElementById('chatInput');
            if (chatInput) {
                chatInput.focus();
            }
            
            // Desplazar al final
            const chatBody = document.getElementById('chatBody');
            if (chatBody) {
                chatBody.scrollTop = chatBody.scrollHeight;
            }
        }, 100);
    } else {
        // Remover clase activa
        if (chatToggle) {
            chatToggle.classList.remove('active');
        }
        
        // Detener verificación
        stopMessageChecking();
    }
}

// Mostrar advertencia en el chat
function showChatWarning(message) {
    const chatBody = document.getElementById('chatBody');
    if (!chatBody) return;
    
    const warningMessage = document.createElement('div');
    warningMessage.className = 'chat-message system';
    warningMessage.innerHTML = `
        <div class="message-bubble system warning">
            <i class="fas fa-exclamation-triangle mr-2"></i>
            ${escapeHtml(message)}
            <div class="mt-2">
                <a href="mi_perfil.php" class="btn btn-warning btn-sm">
                    <i class="fas fa-user-edit mr-1"></i>Actualizar Perfil
                </a>
            </div>
        </div>
    `;
    chatBody.appendChild(warningMessage);
    chatBody.scrollTop = chatBody.scrollHeight;
}

// Enviar mensaje - Solo para usuarios logueados
function sendMessage() {
    // Verificar si el usuario está logueado
    if (!isUserLoggedIn) {
        showLoginAlert();
        return;
    }
    
    // Verificar email válido
    if (!currentEmail || !validateEmail(currentEmail)) {
        showChatWarning('Por favor, actualiza tu email en tu perfil para usar el chat.');
        return;
    }
    
    const input = document.getElementById('chatInput');
    const message = input.value.trim();
    
    if (!message) return;
    
    const chatBody = document.getElementById('chatBody');
    if (!chatBody) return;
    
    // Agregar mensaje de usuario visualmente
    const userMessage = createMessageElement('user', message);
    chatBody.appendChild(userMessage);
    
    // Limpiar input
    input.value = '';
    
    // Desplazar al final
    chatBody.scrollTop = chatBody.scrollHeight;
    
    // Mostrar indicador de que el agente está escribiendo
    showAgentTyping();
    
    // Enviar al servidor
    sendMessageToServer(message);
}

// Crear elemento de mensaje
function createMessageElement(type, message, time = null, senderName = null) {
    const messageDiv = document.createElement('div');
    messageDiv.className = `chat-message ${type}`;
    
    const timeStr = time || new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
    const sender = type === 'user' ? 'Tú' : (senderName || 'Agente');
    
    messageDiv.innerHTML = `
        <div class="message-bubble ${type}">
            <div class="message-sender">${sender}</div>
            <div class="message-text">${escapeHtml(message)}</div>
            <div class="message-time">${timeStr}</div>
        </div>
    `;
    
    return messageDiv;
}

// Escapar HTML
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Mostrar que el agente está escribiendo
function showAgentTyping() {
    if (isAgentTyping) return;
    
    isAgentTyping = true;
    const chatBody = document.getElementById('chatBody');
    
    const typingIndicator = document.createElement('div');
    typingIndicator.className = 'typing-indicator';
    typingIndicator.id = 'typingIndicator';
    typingIndicator.innerHTML = `
        <span></span>
        <span></span>
        <span></span>
        <div class="ml-2">El agente está escribiendo...</div>
    `;
    
    chatBody.appendChild(typingIndicator);
    chatBody.scrollTop = chatBody.scrollHeight;
}

// Ocultar indicador de escritura
function hideAgentTyping() {
    isAgentTyping = false;
    const typingIndicator = document.getElementById('typingIndicator');
    if (typingIndicator) {
        typingIndicator.remove();
    }
}

// Enviar mensaje al servidor
async function sendMessageToServer(message) {
    try {
        const formData = new FormData();
        formData.append('funcion', 'enviar_mensaje');
        formData.append('mensaje', message);
        formData.append('email', currentEmail);
        formData.append('nombre', userName);
        formData.append('usuario_id', userId);
        
        const response = await fetch('../Controllers/ChatController.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        // Ocultar indicador de escritura
        hideAgentTyping();
        
        if (result.estado === 'success') {
            // Actualizar mensaje con la hora del servidor
            const lastMessage = document.querySelector('.chat-message.user:last-child .message-time');
            if (lastMessage && result.fecha) {
                lastMessage.textContent = result.fecha;
            }
            
            // Simular respuesta del agente después de 1-3 segundos
            setTimeout(() => {
                simulateAgentResponse();
            }, 1000 + Math.random() * 2000);
            
        } else {
            showChatError(result.mensaje || 'Error al enviar mensaje');
            // Revertir mensaje si falló
            const lastMessage = document.querySelector('.chat-message.user:last-child');
            if (lastMessage) {
                lastMessage.remove();
            }
        }
        
    } catch (error) {
        console.error('Error:', error);
        hideAgentTyping();
        showChatError('Error de conexión. Intenta de nuevo.');
    }
}

// Simular respuesta del agente (temporal)
function simulateAgentResponse() {
    const responses = [
        "He recibido tu mensaje. Déjame revisar la información...",
        "Entiendo tu consulta. Voy a buscar la mejor solución para ti.",
        "Gracias por tu mensaje. Estoy verificando los detalles...",
        "Perfecto, tengo tu pregunta. Dame un momento para responderte.",
        "Estoy procesando tu solicitud. Te responderé en breve."
    ];
    
    const randomResponse = responses[Math.floor(Math.random() * responses.length)];
    const chatBody = document.getElementById('chatBody');
    
    if (chatBody) {
        const agentMessage = createMessageElement('agent', randomResponse);
        chatBody.appendChild(agentMessage);
        chatBody.scrollTop = chatBody.scrollHeight;
    }
}

// Cargar mensajes
async function loadMessages() {
    if (!isUserLoggedIn) return;
    
    try {
        const formData = new FormData();
        formData.append('funcion', 'obtener_mensajes');
        // NO enviar email, el controlador lo obtendrá de la sesión
        
        const response = await fetch('../Controllers/ChatController.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        console.log('Respuesta del servidor:', result); // Para depurar
        
        if (result.estado === 'success') {
            displayMessages(result.mensajes);
            
            // Actualizar email si viene en la respuesta
            if (result.email_usuario && result.email_usuario !== currentEmail) {
                console.log('Email actualizado desde servidor:', result.email_usuario);
                currentEmail = result.email_usuario;
            }
        } else if (result.estado === 'error') {
            console.log('Error al cargar mensajes:', result.mensaje);
            
            // Manejar errores específicos
            if (result.mensaje.includes('perfil no tiene email')) {
                showChatWarning('Tu perfil no tiene email configurado. Actualiza tu información en la configuración de tu cuenta.');
            } else if (result.mensaje.includes('No autenticado')) {
                // Sesión expirada
                showSessionExpired();
            } else {
                showWelcomeMessage();
            }
        }
    } catch (error) {
        console.error('Error al cargar mensajes:', error);
        showChatError('Error de conexión al cargar mensajes');
    }
}

// Función para manejar sesión expirada
function showSessionExpired() {
    Swal.fire({
        title: 'Sesión expirada',
        text: 'Tu sesión ha expirado. Por favor, inicia sesión nuevamente.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Iniciar Sesión',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = 'login.php?redirect=' + encodeURIComponent(window.location.pathname);
        }
    });
}

// Mostrar mensajes
function displayMessages(messages) {
    const chatBody = document.getElementById('chatBody');
    if (!chatBody) return;
    
    if (messages && messages.length > 0) {
        chatBody.innerHTML = '';
        
        messages.forEach(msg => {
            const messageElement = createMessageElement(msg.tipo, msg.mensaje, msg.fecha, msg.nombre);
            chatBody.appendChild(messageElement);
        });
    } else {
        // Mostrar mensaje de bienvenida si no hay conversación previa
        showWelcomeMessage();
    }
    
    // Desplazar al final
    chatBody.scrollTop = chatBody.scrollHeight;
}

// Verificar nuevos mensajes
async function checkForNewMessages() {
    if (!isUserLoggedIn || checkingMessages) return;
    
    checkingMessages = true;
    
    try {
        const formData = new FormData();
        formData.append('funcion', 'verificar_no_leidos');
        // NO enviar email aquí tampoco
        
        const response = await fetch('../Controllers/ChatController.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.estado === 'success' && result.total > 0) {
            // Cargar mensajes nuevos
            await loadMessages();
            
            // Mostrar notificación si el chat no está abierto
            if (!chatOpen) {
                showNotification(`Tienes ${result.total} mensaje(s) nuevo(s) en el chat`);
            }
        }
    } catch (error) {
        console.error('Error verificando mensajes:', error);
    } finally {
        checkingMessages = false;
    }
}

// Iniciar verificación periódica
function startMessageChecking() {
    if (!isUserLoggedIn) return;
    
    // Limpiar intervalo anterior si existe
    stopMessageChecking();
    
    // Verificar cada 10 segundos cuando el chat está abierto
    messageCheckInterval = setInterval(checkForNewMessages, 10000);
    
    // También verificar inmediatamente
    checkForNewMessages();
}

// Detener verificación
function stopMessageChecking() {
    if (messageCheckInterval) {
        clearInterval(messageCheckInterval);
        messageCheckInterval = null;
    }
}

// Mostrar notificación
function showNotification(message) {
    // Solo para usuarios logueados
    if (!isUserLoggedIn) return;
    
    // Verificar si las notificaciones están permitidas
    if (!("Notification" in window)) {
        return;
    }
    
    // Mostrar notificación en la interfaz
    const chatToggle = document.querySelector('.chat-toggle');
    if (chatToggle) {
        // Remover badge anterior si existe
        const oldBadge = chatToggle.querySelector('.chat-notification-badge');
        if (oldBadge) {
            oldBadge.remove();
        }
        
        // Crear nuevo badge
        const badge = document.createElement('span');
        badge.className = 'chat-notification-badge';
        badge.innerHTML = '<i class="fas fa-bell"></i>';
        chatToggle.appendChild(badge);
        
        // Hacer parpadear el botón
        chatToggle.classList.add('notifying');
        
        // Remover después de 30 segundos
        setTimeout(() => {
            if (badge.parentNode) {
                badge.remove();
            }
            chatToggle.classList.remove('notifying');
        }, 30000);
    }
    
    // Mostrar notificación del navegador si está permitido
    if (Notification.permission === "granted") {
        try {
            new Notification("NexusBuy - Nuevo mensaje", {
                body: message,
                icon: '../assets/img/favicon.png',
                tag: 'chat-notification'
            });
        } catch (error) {
            console.log('Error mostrando notificación:', error);
        }
    }
}

// Mostrar error en el chat
function showChatError(message) {
    const chatBody = document.getElementById('chatBody');
    if (!chatBody) return;
    
    const errorMessage = document.createElement('div');
    errorMessage.className = 'chat-message system';
    errorMessage.innerHTML = `
        <div class="message-bubble system error">
            <i class="fas fa-exclamation-circle mr-2"></i>
            ${escapeHtml(message)}
        </div>
    `;
    chatBody.appendChild(errorMessage);
    chatBody.scrollTop = chatBody.scrollHeight;
}

// Enviar formulario de contacto
document.getElementById('support-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Enviando...';
    submitBtn.disabled = true;
    
    try {
        // Recolectar datos del formulario
        const formData = new FormData(this);
        const formDataObj = {};
        
        // Convertir FormData a objeto
        for (let [key, value] of formData.entries()) {
            formDataObj[key] = value;
        }
        
        // Validar campos requeridos
        const requiredFields = ['name', 'email', 'subject', 'department', 'message'];
        const missingFields = [];
        
        requiredFields.forEach(field => {
            if (!formDataObj[field] || formDataObj[field].trim() === '') {
                missingFields.push(field);
            }
        });
        
        if (missingFields.length > 0) {
            throw new Error(`Faltan campos requeridos: ${missingFields.join(', ')}`);
        }
        
        // Validar email
        if (!validateEmail(formDataObj.email)) {
            throw new Error('El email no es válido');
        }
        
        // Simular envío
        await new Promise(resolve => setTimeout(resolve, 1500));
        
        // Mostrar éxito
        Swal.fire({
            icon: 'success',
            title: '¡Mensaje enviado!',
            html: `
                <div class="text-left">
                    <p>Hemos recibido tu consulta correctamente.</p>
                    <p class="mb-2"><strong>Detalles:</strong></p>
                    <ul class="text-left pl-3">
                        <li><strong>Asunto:</strong> ${escapeHtml(formDataObj.subject)}</li>
                        <li><strong>Departamento:</strong> ${escapeHtml(formDataObj.department)}</li>
                        <li><strong>Email:</strong> ${escapeHtml(formDataObj.email)}</li>
                    </ul>
                    <p class="mt-2">Te contactaremos dentro de las próximas 24 horas.</p>
                </div>
            `,
            showConfirmButton: true,
            confirmButtonText: 'Entendido',
            confirmButtonColor: '#3085d6'
        });
        
        // Resetear formulario
        this.reset();
        
    } catch (error) {
        console.error('Error enviando formulario:', error);
        
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: error.message || 'No se pudo enviar el formulario. Intenta de nuevo.',
            confirmButtonText: 'Entendido',
            confirmButtonColor: '#d33'
        });
        
    } finally {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    }
});

// Función para desplazarse al formulario
function scrollToForm() {
    const formSection = document.getElementById('contact-form');
    if (formSection) {
        formSection.scrollIntoView({
            behavior: 'smooth',
            block: 'start'
        });
        
        // Resaltar la sección
        formSection.classList.add('highlight');
        setTimeout(() => {
            formSection.classList.remove('highlight');
        }, 2000);
    }
}

// Event listeners
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM cargado, inicializando chat...');
    
    // Inicializar chat
    initChat();
    
    // Configurar input del chat (solo si el usuario está logueado)
    if (isUserLoggedIn) {
        const chatInput = document.getElementById('chatInput');
        if (chatInput) {
            // Enviar con Enter
            chatInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    sendMessage();
                }
            });
            
            // Permitir nueva línea con Shift+Enter
            chatInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' && e.shiftKey) {
                    return true;
                }
            });
        }
    }
    
    // Configurar FAQ accordion
    document.querySelectorAll('.faq-header').forEach(header => {
        header.addEventListener('click', function() {
            const icon = this.querySelector('.fa-chevron-down');
            if (icon) {
                icon.style.transition = 'transform 0.3s ease';
                icon.style.transform = this.getAttribute('aria-expanded') === 'true' ? 'rotate(0deg)' : 'rotate(180deg)';
            }
        });
    });
    
    // Configurar botones de "En desarrollo"
    document.querySelectorAll('.footer-link.en-desarrollo').forEach(element => {
        element.addEventListener('click', function(e) {
            if (this.tagName === 'A' || this.querySelector('a')) {
                e.preventDefault();
            }
            
            const isChatButton = this.querySelector('.btn-primary') && 
                                this.querySelector('.btn-primary').textContent.includes('Chat');
            
            if (!isChatButton) {
                Swal.fire({
                    icon: 'info',
                    title: 'Función en desarrollo',
                    text: 'Esta funcionalidad estará disponible próximamente.',
                    timer: 2000,
                    showConfirmButton: false
                });
            }
        });
    });
    
    // Configurar botón de chat
    const chatToggleBtn = document.querySelector('.chat-toggle');
    if (chatToggleBtn) {
        chatToggleBtn.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    }
    
    // Verificar si hay parámetros en la URL para abrir el chat
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('openChat') === 'true' && isUserLoggedIn) {
        setTimeout(() => {
            toggleChat();
        }, 1000);
    }
});

// Configurar botones de las tarjetas de soporte
document.addEventListener('click', function(e) {
    // Botón de abrir chat en la tarjeta
    if (e.target.closest('.support-card .btn-primary') && 
        e.target.closest('.btn-primary').textContent.includes('Abrir Chat')) {
        e.preventDefault();
        toggleChat();
    }
    
    // Botón de crear ticket
    if (e.target.closest('.support-card .btn-outline-primary') && 
        e.target.closest('.btn-outline-primary').textContent.includes('Crear Ticket')) {
        e.preventDefault();
        scrollToForm();
    }
});

// Manejar clics fuera del chat para cerrarlo
document.addEventListener('click', function(e) {
    const chatWindow = document.getElementById('chatWindow');
    const chatToggle = document.querySelector('.chat-toggle');
    
    if (chatOpen && chatWindow && chatToggle) {
        const isClickInsideChat = chatWindow.contains(e.target) || chatToggle.contains(e.target);
        
        if (!isClickInsideChat && !e.target.closest('.support-card .btn-primary')) {
            chatOpen = false;
            chatWindow.style.display = 'none';
            if (chatToggle) {
                chatToggle.classList.remove('active');
            }
            stopMessageChecking();
        }
    }
});

// Manejar tecla Escape para cerrar el chat
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && chatOpen) {
        toggleChat();
    }
});

// Exportar funciones para uso global
window.toggleChat = toggleChat;
window.sendMessage = sendMessage;
window.scrollToForm = scrollToForm;