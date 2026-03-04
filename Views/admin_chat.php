<?php
// admin_chat.php
// Verificar si es admin
if (!isset($_SESSION['id_tipo_usuario']) || $_SESSION['id_tipo_usuario'] != '2') {
    header('Location: ../index.php');
    exit();
}

include_once 'Layauts/header_general.php';


?>

<title>Panel de Chat - Admin | NexusBuy</title>

<style>
    .conversation-item {
        cursor: pointer;
        padding: 15px;
        border-bottom: 1px solid #dee2e6;
        transition: all 0.3s;
    }
    .conversation-item:hover {
        background-color: #f8f9fa;
    }
    .conversation-item.active {
        background-color: #e3f2fd;
        border-left: 4px solid #2196F3;
    }
    .badge-unread {
        background-color: #f44336;
        color: white;
        border-radius: 10px;
        padding: 2px 8px;
        font-size: 0.8rem;
    }
    .chat-messages-container {
        height: 60vh;
        overflow-y: auto;
        padding: 20px;
        background-color: #f8f9fa;
        border-radius: 5px;
    }
    .message-user {
        background-color: #e3f2fd;
        padding: 10px 15px;
        border-radius: 10px;
        margin-bottom: 10px;
        max-width: 80%;
        margin-right: auto;
    }
    .message-agent {
        background-color: #f1f3f4;
        padding: 10px 15px;
        border-radius: 10px;
        margin-bottom: 10px;
        max-width: 80%;
        margin-left: auto;
    }
    .message-time {
        font-size: 0.8rem;
        color: #6c757d;
        text-align: right;
    }
    .message-sender {
        font-weight: bold;
        margin-bottom: 5px;
    }
    #replySection {
        display: none;
    }
</style>

<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Panel de Chat - Soporte</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="index.php">Inicio</a></li>
                    <li class="breadcrumb-item"><a href="soporte.php">Soporte</a></li>
                    <li class="breadcrumb-item active">Chat Admin</li>
                </ol>
            </div>
        </div>
    </div>
</section>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <!-- Columna izquierda: Lista de conversaciones -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Conversaciones</h3>
                        <div class="card-tools">
                            <div class="input-group input-group-sm">
                                <input type="text" class="form-control" placeholder="Buscar..." id="searchConversations">
                                <div class="input-group-append">
                                    <button class="btn btn-primary" onclick="searchConversations()">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div id="conversationsList">
                            <!-- Las conversaciones se cargarán aquí -->
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Columna derecha: Mensajes de la conversación -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title" id="currentConversationTitle">
                            Selecciona una conversación
                        </h3>
                        <div class="card-tools">
                            <button class="btn btn-sm btn-danger" id="deleteConversationBtn" style="display:none;">
                                <i class="fas fa-trash"></i> Eliminar
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="chat-messages-container" id="chatMessages">
                            <!-- Los mensajes se mostrarán aquí -->
                        </div>
                        
                        <div class="mt-3" id="replySection">
                            <div class="input-group">
                                <textarea class="form-control" id="replyMessage" 
                                          placeholder="Escribe tu respuesta..." rows="2"></textarea>
                                <div class="input-group-append">
                                    <button class="btn btn-primary" onclick="sendAgentMessage()">
                                        <i class="fas fa-paper-plane"></i> Enviar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
let currentConversationEmail = '';
let refreshInterval = null;

// Cargar conversaciones
function loadConversations() {
    const formData = new FormData();
    formData.append('funcion', 'obtener_conversaciones');
    
    fetch('../Controllers/ChatController.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.estado === 'success') {
            displayConversations(data.conversaciones);
        } else {
            showError(data.mensaje);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showError('Error al cargar conversaciones');
    });
}

// Mostrar conversaciones en la lista
function displayConversations(conversations) {
    const container = document.getElementById('conversationsList');
    
    if (conversations.length === 0) {
        container.innerHTML = `
            <div class="text-center p-3">
                <p class="text-muted">No hay conversaciones activas</p>
            </div>
        `;
        return;
    }
    
    let html = '';
    conversations.forEach(conv => {
        const unreadBadge = conv.mensajes_no_leidos > 0 ? 
            `<span class="badge-unread float-right">${conv.mensajes_no_leidos}</span>` : '';
        
        html += `
            <div class="conversation-item" data-email="${conv.email}" 
                 onclick="selectConversation('${conv.email}', '${conv.nombre.replace(/'/g, "\\'")}')">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <strong>${conv.nombre}</strong><br>
                        <small class="text-muted">${conv.email}</small>
                    </div>
                    ${unreadBadge}
                </div>
                <div class="mt-2">
                    <small class="text-muted">
                        <i class="far fa-clock"></i> ${conv.hace}
                    </small>
                </div>
            </div>
        `;
    });
    
    container.innerHTML = html;
}

// Seleccionar conversación
function selectConversation(email, nombre) {
    currentConversationEmail = email;
    
    // Actualizar UI
    document.getElementById('currentConversationTitle').innerHTML = `
        Conversación con <strong>${nombre}</strong><br>
        <small class="text-muted">${email}</small>
    `;
    
    // Mostrar botones
    document.getElementById('replySection').style.display = 'block';
    document.getElementById('deleteConversationBtn').style.display = 'inline-block';
    
    // Resaltar conversación seleccionada
    document.querySelectorAll('.conversation-item').forEach(item => {
        item.classList.remove('active');
        if (item.dataset.email === email) {
            item.classList.add('active');
        }
    });
    
    // Cargar mensajes
    loadConversationMessages(email);
}

// Cargar mensajes de una conversación
function loadConversationMessages(email) {
    const formData = new FormData();
    formData.append('funcion', 'obtener_mensajes');
    formData.append('email', email);
    
    fetch('../Controllers/ChatController.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.estado === 'success') {
            displayMessages(data.mensajes);
        } else {
            showError(data.mensaje);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showError('Error al cargar mensajes');
    });
}

// Mostrar mensajes
function displayMessages(messages) {
    const container = document.getElementById('chatMessages');
    
    if (messages.length === 0) {
        container.innerHTML = `
            <div class="text-center p-3">
                <p class="text-muted">No hay mensajes en esta conversación</p>
            </div>
        `;
        return;
    }
    
    let html = '';
    messages.forEach(msg => {
        const messageClass = msg.tipo === 'agente' ? 'message-agent' : 'message-user';
        const sender = msg.tipo === 'agente' ? 'Agente' : msg.nombre;
        
        html += `
            <div class="${messageClass}">
                <div class="message-sender">${sender}</div>
                <div class="message-text">${msg.mensaje}</div>
                <div class="message-time">${msg.fecha}</div>
            </div>
        `;
    });
    
    container.innerHTML = html;
    
    // Desplazar al final
    container.scrollTop = container.scrollHeight;
}

// Enviar mensaje como agente
function sendAgentMessage() {
    const messageInput = document.getElementById('replyMessage');
    const message = messageInput.value.trim();
    
    if (!message) return;
    
    if (!currentConversationEmail) {
        showError('Selecciona una conversación primero');
        return;
    }
    
    const formData = new FormData();
    formData.append('funcion', 'enviar_mensaje_agente');
    formData.append('email_destino', currentConversationEmail);
    formData.append('mensaje', message);
    
    fetch('../Controllers/ChatController.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.estado === 'success') {
            messageInput.value = '';
            loadConversationMessages(currentConversationEmail);
            
            // Actualizar lista de conversaciones
            loadConversations();
        } else {
            showError(data.mensaje);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showError('Error al enviar mensaje');
    });
}

// Buscar conversaciones
function searchConversations() {
    const searchInput = document.getElementById('searchConversations');
    const searchTerm = searchInput.value.trim();
    
    if (!searchTerm) {
        loadConversations();
        return;
    }
    
    const formData = new FormData();
    formData.append('funcion', 'buscar_conversaciones');
    formData.append('busqueda', searchTerm);
    
    fetch('../Controllers/ChatController.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.estado === 'success') {
            // Formatear resultados para displayConversations
            const conversations = data.resultados.map(conv => ({
                email: conv.email,
                nombre: conv.nombre,
                ultimo_mensaje: conv.ultimo_mensaje,
                mensajes_no_leidos: 0,
                hace: calcularTiempoTranscurrido(conv.ultimo_mensaje)
            }));
            
            displayConversations(conversations);
        } else {
            showError(data.mensaje);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showError('Error al buscar conversaciones');
    });
}

// Calcular tiempo transcurrido
function calcularTiempoTranscurrido(fechaStr) {
    const fecha = new Date(fechaStr);
    const ahora = new Date();
    const diferencia = Math.floor((ahora - fecha) / 1000); // diferencia en segundos
    
    if (diferencia < 60) {
        return 'Hace unos segundos';
    } else if (diferencia < 3600) {
        const minutos = Math.floor(diferencia / 60);
        return `Hace ${minutos} minuto${minutos > 1 ? 's' : ''}`;
    } else if (diferencia < 86400) {
        const horas = Math.floor(diferencia / 3600);
        return `Hace ${horas} hora${horas > 1 ? 's' : ''}`;
    } else {
        const dias = Math.floor(diferencia / 86400);
        return `Hace ${dias} día${dias > 1 ? 's' : ''}`;
    }
}

// Eliminar conversación
function deleteConversation() {
    if (!currentConversationEmail) return;
    
    Swal.fire({
        title: '¿Eliminar conversación?',
        text: 'Esta acción no se puede deshacer',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            // Aquí deberías implementar la función de eliminar en el controlador
            showError('Función de eliminar no implementada');
            // Para implementar: crear función 'eliminar_conversacion' en ChatController.php
        }
    });
}

// Mostrar error
function showError(message) {
    Swal.fire({
        icon: 'error',
        title: 'Error',
        text: message,
        timer: 3000
    });
}

// Inicializar
document.addEventListener('DOMContentLoaded', function() {
    loadConversations();
    
    // Configurar búsqueda por Enter
    document.getElementById('searchConversations').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            searchConversations();
        }
    });
    
    // Configurar botón de eliminar
    document.getElementById('deleteConversationBtn').addEventListener('click', deleteConversation);
    
    // Configurar envío por Enter (con Ctrl+Enter para nueva línea)
    document.getElementById('replyMessage').addEventListener('keydown', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendAgentMessage();
        }
    });
    
    // Refrescar conversaciones cada 30 segundos
    refreshInterval = setInterval(loadConversations, 30000);
});

// Limpiar intervalo al salir
window.addEventListener('beforeunload', function() {
    if (refreshInterval) {
        clearInterval(refreshInterval);
    }
});
</script>

<?php
include_once 'Layauts/footer_general.php';
?>