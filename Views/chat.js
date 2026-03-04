// chat.js - Sistema de Chat en Vivo para NexusBuy
// Versión completa con diagnóstico y mejoras

class ChatEnVivo {
    constructor() {
        this.conversacionId = null;
        this.ultimoMensajeId = 0;
        this.pollingInterval = null;
        this.esAgente = false;
        this.nombreUsuario = '';
        this.emailUsuario = '';
        this.config = {
            pollingInterval: 3000, // 3 segundos
            maxMensajes: 100,
            urlController: '../Controllers/ChatController.php'
        };
        
        // Sonido para nuevos mensajes
        this.messageSound = new Audio('../assets/sounds/notification.mp3');
            this.messageSound.preload = 'auto';
        
        this.initialize();
    }
    
    initialize() {
        // console.log('Inicializando sistema de chat...');
        
        // Intentar crear sonido para notificaciones
        try {
            this.messageSound = new Audio('../assets/sounds/notification.mp3');
            this.messageSound.preload = 'auto';
        } catch (e) {
            // console.log('No se pudo cargar sonido de notificación:', e);
        }
        
        // Verificar si el usuario está logueado
        this.verificarSesion();
        
        // Inicializar eventos
        this.inicializarEventos();
        
        // Verificar si hay una conversación activa en localStorage
        this.cargarConversacionGuardada();
    }
    
    verificarSesion() {
        // Intentar obtener datos del usuario de localStorage o session
        const usuarioGuardado = localStorage.getItem('usuario_nexusbuy');
        
        if (usuarioGuardado) {
            try {
                const usuario = JSON.parse(usuarioGuardado);
                this.nombreUsuario = usuario.nombre || '';
                this.emailUsuario = usuario.email || '';
                
                if (usuario.rol && ['admin', 'soporte'].includes(usuario.rol)) {
                    this.esAgente = true;
                }
            } catch (e) {
                console.error('Error al parsear usuario:', e);
            }
        }
    }
    
    inicializarEventos() {
        // Evento para abrir/cerrar el chat
        document.addEventListener('click', (e) => {
            if (e.target.closest('.chat-toggle')) {
                this.toggleChat();
            }
            
            if (e.target.closest('.chat-close')) {
                this.cerrarChat();
            }
        });
        
        // Evento para enviar mensaje
        document.addEventListener('click', (e) => {
            if (e.target.closest('#chat-enviar-btn')) {
                this.enviarMensaje();
            }
        });
        
        // Enviar mensaje con Enter
        document.addEventListener('keypress', (e) => {
            if (e.target.id === 'chat-mensaje-input' && e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                this.enviarMensaje();
            }
        });
        
        // Auto-expansión del textarea
        const textarea = document.getElementById('chat-mensaje-input');
        if (textarea) {
            textarea.addEventListener('input', function() {
                this.style.height = 'auto';
                this.style.height = (this.scrollHeight) + 'px';
            });
        }
        
        // Iniciar nueva conversación
        document.addEventListener('click', (e) => {
            if (e.target.closest('#iniciar-chat-btn')) {
                this.iniciarNuevaConversacion();
            }
        });
        
        // Cerrar conversación
        document.addEventListener('click', (e) => {
            if (e.target.closest('#chat-cerrar-btn')) {
                this.cerrarConversacion();
            }
        });
    }
    
    cargarConversacionGuardada() {
        const conversacionGuardada = localStorage.getItem('chat_conversacion_activa');
        
        if (conversacionGuardada) {
            try {
                const data = JSON.parse(conversacionGuardada);
                this.conversacionId = data.id;
                this.ultimoMensajeId = data.ultimo_id || 0;
                
                // Verificar si la conversación sigue activa
                this.verificarEstadoConversacion();
            } catch (e) {
                console.error('Error al cargar conversación guardada:', e);
                localStorage.removeItem('chat_conversacion_activa');
            }
        }
    }
    
    guardarConversacion() {
        if (this.conversacionId) {
            const data = {
                id: this.conversacionId,
                ultimo_id: this.ultimoMensajeId,
                timestamp: Date.now()
            };
            localStorage.setItem('chat_conversacion_activa', JSON.stringify(data));
        }
    }
    
    async iniciarNuevaConversacion() {
        try {
            // console.log('=== INICIAR NUEVA CONVERSACIÓN ===');
            
            // Obtener datos del formulario o usar datos guardados
            const nombre = document.getElementById('chat-nombre')?.value || this.nombreUsuario;
            const email = document.getElementById('chat-email')?.value || this.emailUsuario;
            const asunto = document.getElementById('chat-asunto')?.value || 'Consulta general';
            const categoria = document.getElementById('chat-categoria')?.value || 'general';
            
            // console.log('Datos del formulario:', { nombre, email, asunto, categoria });
            
            if (!nombre || !email) {
                console.error('❌ Nombre o email faltante');
                this.mostrarError('Por favor ingresa tu nombre y email para iniciar el chat');
                return;
            }
            
            // Mostrar cargando
            this.mostrarCargando('Iniciando chat...');
            
            const formData = new FormData();
            formData.append('funcion', 'iniciar_conversacion');
            formData.append('nombre', nombre);
            formData.append('email', email);
            formData.append('asunto', asunto);
            formData.append('categoria', categoria);
            
            // Agregar ID de usuario encriptado si está logueado
            const usuarioGuardado = localStorage.getItem('usuario_nexusbuy');
            if (usuarioGuardado) {
                try {
                    const usuario = JSON.parse(usuarioGuardado);
                    // console.log('Usuario encontrado en localStorage:', usuario);
                    
                    if (usuario.id_encrypted) {
                        formData.append('id_usuario_encrypted', usuario.id_encrypted);
                        // console.log('ID encriptado agregado:', usuario.id_encrypted);
                    }
                } catch (e) {
                    console.error('Error al parsear usuario:', e);
                }
            }
            
            // console.log('Enviando datos al servidor...');
            // console.log('URL:', this.config.urlController);
            
            // Mostrar FormData en consola
            for (let pair of formData.entries()) {
                // console.log(pair[0] + ': ' + pair[1]);
            }
            
            const response = await fetch(this.config.urlController, {
                method: 'POST',
                body: formData
            });
            
            // console.log('Respuesta recibida, status:', response.status);
            
            const textResponse = await response.text();
            // console.log('Respuesta en texto:', textResponse);
            
            let data;
            try {
                data = JSON.parse(textResponse);
                // console.log('Respuesta parseada:', data);
            } catch (parseError) {
                console.error('❌ Error parseando JSON:', parseError);
                console.error('Texto recibido:', textResponse);
                throw new Error('Respuesta del servidor inválida: ' + textResponse.substring(0, 200));
            }
            
            if (data.success) {
                // console.log('✅ Conversación iniciada exitosamente');
                // console.log('ID de conversación:', data.data.conversacion_id);
                
                this.conversacionId = data.data.conversacion_id;
                this.ultimoMensajeId = 0;
                
                // Guardar en localStorage
                this.guardarConversacion();
                
                // Actualizar interfaz
                this.actualizarInterfazChat();
                
                // Iniciar polling
                this.iniciarPolling();
                
                // Mostrar mensaje de éxito
                this.mostrarMensaje('¡Chat iniciado! Un agente te atenderá pronto.', 'success');
                
            } else {
                console.error('❌ Error del servidor:', data.error);
                throw new Error(data.error || 'Error al iniciar el chat');
            }
            
        } catch (error) {
            console.error('❌ Error completo en iniciarNuevaConversacion:', error);
            console.error('Stack trace:', error.stack);
            
            // Intentar hacer diagnóstico automático
            // console.log('Realizando diagnóstico automático...');
            if (this.diagnosticarSistemaChat) {
                await this.diagnosticarSistemaChat();
            }
            
            this.mostrarError(error.message);
        }
    }
    
    async enviarMensaje() {
    try {
        const input = document.getElementById('chat-mensaje-input');
        const mensaje = input.value.trim();
        
        if (!mensaje) {
            return;
        }
        
        if (!this.conversacionId) {
            this.mostrarError('Primero debes iniciar una conversación');
            return;
        }
        
        // Guardar el mensaje localmente para identificarlo después
        const mensajeLocalId = 'local_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
        const mensajeTexto = mensaje;
        
        // Deshabilitar input temporalmente
        input.disabled = true;
        
        const formData = new FormData();
        formData.append('funcion', 'enviar_mensaje');
        formData.append('conversacion_id', this.conversacionId);
        formData.append('mensaje', mensajeTexto);
        
        // Mostrar mensaje inmediatamente (pero con ID local)
        this.agregarMensajeTemporal({
            id: mensajeLocalId,
            mensaje: mensajeTexto,
            tipo: 'usuario',
            hora: new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}),
            fecha: new Date().toLocaleDateString(),
            esTemporal: true
        });
        
        // Limpiar input inmediatamente
        input.value = '';
        input.disabled = false;
        input.focus();
        input.style.height = 'auto';
        
        const response = await fetch(this.config.urlController, {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (!data.success) {
            throw new Error(data.error || 'Error al enviar mensaje');
        }
        
        // Marcar el mensaje temporal como enviado (se reemplazará con el real en el polling)
        this.marcarMensajeComoEnviado(mensajeLocalId);
        
    } catch (error) {
        console.error('Error enviando mensaje:', error);
        this.mostrarError(error.message);
        
        // Rehabilitar input
        const input = document.getElementById('chat-mensaje-input');
        if (input) {
            input.disabled = false;
        }
        
        // Mostrar error en el mensaje temporal
        this.mostrarErrorEnMensajeTemporal(mensajeLocalId, error.message);
    }
}

agregarMensajeTemporal(mensaje) {
    const contenedor = document.getElementById('chat-mensajes-container');
    if (!contenedor) return;
    
    const mensajeElement = document.createElement('div');
    mensajeElement.className = `chat-mensaje ${mensaje.tipo}-mensaje mensaje-temporal`;
    mensajeElement.setAttribute('data-mensaje-id', mensaje.id);
    mensajeElement.setAttribute('data-timestamp', Date.now());
    
    const esUsuario = mensaje.tipo === 'usuario';
    const alineacion = esUsuario ? 'right' : 'left';
    const bgColor = esUsuario ? 'primary' : 'light';
    
    const contenido = `
        <div class="d-flex justify-content-${alineacion} mb-2">
            <div class="chat-mensaje-burbuja bg-${bgColor} ${esUsuario ? 'text-white' : ''}">
                <div class="chat-mensaje-contenido">
                    <p class="mb-1">${this.escapeHtml(mensaje.mensaje)}</p>
                    <div class="chat-mensaje-estado">
                        <small class="text-${esUsuario ? 'white-50' : 'muted'}">
                            <i class="fas fa-clock mr-1"></i> Enviando...
                        </small>
                    </div>
                </div>
                <div class="chat-mensaje-hora text-${esUsuario ? 'white-50' : 'muted'}">
                    <small>${mensaje.hora}</small>
                </div>
            </div>
        </div>
    `;
    
    mensajeElement.innerHTML = contenido;
    contenedor.appendChild(mensajeElement);
    
    this.scrollAlFinal();
}

marcarMensajeComoEnviado(mensajeLocalId) {
    const mensajeElement = document.querySelector(`[data-mensaje-id="${mensajeLocalId}"]`);
    if (mensajeElement) {
        const estadoElement = mensajeElement.querySelector('.chat-mensaje-estado');
        if (estadoElement) {
            estadoElement.innerHTML = `
                <small class="text-success">
                    <i class="fas fa-check mr-1"></i> Enviado
                </small>
            `;
        }
        
        // Programar eliminación después de 5 segundos (para cuando llegue el real)
        setTimeout(() => {
            if (mensajeElement.parentNode) {
                mensajeElement.remove();
            }
        }, 5000);
    }
}

mostrarErrorEnMensajeTemporal(mensajeLocalId, error) {
    const mensajeElement = document.querySelector(`[data-mensaje-id="${mensajeLocalId}"]`);
    if (mensajeElement) {
        const estadoElement = mensajeElement.querySelector('.chat-mensaje-estado');
        if (estadoElement) {
            estadoElement.innerHTML = `
                <small class="text-danger">
                    <i class="fas fa-exclamation-triangle mr-1"></i> Error: ${this.escapeHtml(error.substring(0, 50))}
                </small>
            `;
        }
        
        // Cambiar estilo a error
        const burbuja = mensajeElement.querySelector('.chat-mensaje-burbuja');
        if (burbuja) {
            burbuja.classList.remove('bg-primary');
            burbuja.classList.add('bg-danger');
        }
    }
}
    
    async obtenerMensajes() {
        try {
            if (!this.conversacionId) {
                return;
            }
            
            const formData = new FormData();
            formData.append('funcion', 'obtener_mensajes');
            formData.append('conversacion_id', this.conversacionId);
            formData.append('ultimo_id', this.ultimoMensajeId);
            
            const response = await fetch(this.config.urlController, {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                // Actualizar último ID
                if (data.data.ultimo_id > this.ultimoMensajeId) {
                    this.ultimoMensajeId = data.data.ultimo_id;
                    this.guardarConversacion();
                }
                
                // Agregar nuevos mensajes a la vista
                if (data.data.mensajes && data.data.mensajes.length > 0) {
                    const mensajesNuevos = this.actualizarMensajesEnVista(data.data.mensajes);
                    
                    // Reproducir sonido si hay mensajes nuevos del agente
                    if (mensajesNuevos > 0 && this.messageSound) {
                        const chatContainer = document.getElementById('chat-container');
                        if (chatContainer && (chatContainer.style.display === 'none' || !chatContainer.style.display)) {
                            this.mostrarNotificacionNuevoMensaje();
                            try {
                                await this.messageSound.play();
                            } catch (e) {
                                // console.log('No se pudo reproducir sonido:', e);
                            }
                        }
                    }
                }
                
                // Actualizar estado de la conversación
                this.actualizarEstadoConversacion(data.data.conversacion);
                
            } else {
                throw new Error(data.error || 'Error al obtener mensajes');
            }
            
        } catch (error) {
            console.error('Error obteniendo mensajes:', error);
        }
    }
    
    async verificarAgentesDisponibles() {
        try {
            const formData = new FormData();
            formData.append('funcion', 'verificar_agentes');
            
            const response = await fetch(this.config.urlController, {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                return data.data.disponibles;
            }
            
            return false;
            
        } catch (error) {
            console.error('Error verificando agentes:', error);
            return false;
        }
    }
    
    iniciarPolling() {
        // Limpiar intervalo anterior si existe
        if (this.pollingInterval) {
            clearInterval(this.pollingInterval);
        }
        
        // Iniciar nuevo intervalo
        this.pollingInterval = setInterval(() => {
            this.obtenerMensajes();
        }, this.config.pollingInterval);
    }
    
    detenerPolling() {
        if (this.pollingInterval) {
            clearInterval(this.pollingInterval);
            this.pollingInterval = null;
        }
    }
    
    actualizarMensajesEnVista(mensajes) {
    const contenedor = document.getElementById('chat-mensajes-container');
    if (!contenedor) return 0;
    
    let nuevosMensajes = 0;
    
    mensajes.forEach(mensaje => {
        // Verificar si el mensaje ya existe por ID real
        const existePorIdReal = document.querySelector(`[data-mensaje-id="${mensaje.id}"]`);
        
        // Verificar si es un mensaje temporal nuestro (buscar por contenido y timestamp)
        const existeComoTemporal = this.buscarMensajeTemporal(mensaje);
        
        if (!existePorIdReal && !existeComoTemporal) {
            this.agregarMensajeALaVista(mensaje);
            nuevosMensajes++;
        } else if (existeComoTemporal) {
            // Reemplazar el mensaje temporal con el real
            this.reemplazarMensajeTemporal(existeComoTemporal, mensaje);
        }
    });
    
    // Scroll al final
    this.scrollAlFinal();
    
    return nuevosMensajes;
}

buscarMensajeTemporal(mensaje) {
    // Buscar mensajes temporales que coincidan en contenido y tiempo
    const mensajesTemporales = document.querySelectorAll('.mensaje-temporal');
    
    for (const tempMsg of mensajesTemporales) {
        const contenido = tempMsg.querySelector('.chat-mensaje-contenido p')?.textContent;
        const timestamp = parseInt(tempMsg.getAttribute('data-timestamp'));
        const ahora = Date.now();
        
        // Si el contenido coincide y fue enviado en los últimos 10 segundos
        if (contenido === mensaje.mensaje && (ahora - timestamp) < 10000) {
            return tempMsg;
        }
    }
    
    return null;
}

reemplazarMensajeTemporal(elementoTemporal, mensajeReal) {
    // Crear nuevo elemento con el ID real
    const nuevoElemento = document.createElement('div');
    nuevoElemento.className = `chat-mensaje ${mensajeReal.tipo}-mensaje`;
    nuevoElemento.setAttribute('data-mensaje-id', mensajeReal.id);
    
    const esUsuario = mensajeReal.tipo === 'usuario';
    const alineacion = esUsuario ? 'right' : 'left';
    const bgColor = esUsuario ? 'primary' : 'light';
    
    const contenido = `
        <div class="d-flex justify-content-${alineacion} mb-2">
            <div class="chat-mensaje-burbuja bg-${bgColor} ${esUsuario ? 'text-white' : ''}">
                <div class="chat-mensaje-contenido">
                    <p class="mb-1">${this.escapeHtml(mensajeReal.mensaje)}</p>
                </div>
                <div class="chat-mensaje-hora text-${esUsuario ? 'white-50' : 'muted'}">
                    <small>${mensajeReal.hora}</small>
                </div>
            </div>
        </div>
    `;
    
    nuevoElemento.innerHTML = contenido;
    
    // Reemplazar el temporal con el real
    elementoTemporal.parentNode.replaceChild(nuevoElemento, elementoTemporal);
}
    
    agregarMensajeALaVista(mensaje) {
        const contenedor = document.getElementById('chat-mensajes-container');
        if (!contenedor) return;
        
        const mensajeElement = document.createElement('div');
        mensajeElement.className = `chat-mensaje ${mensaje.tipo}-mensaje`;
        mensajeElement.setAttribute('data-mensaje-id', mensaje.id);
        
        let contenido = '';
        
        if (mensaje.tipo === 'sistema') {
            contenido = `
                <div class="chat-mensaje-sistema">
                    <div class="chat-mensaje-contenido">
                        <small>${mensaje.mensaje}</small>
                    </div>
                    <div class="chat-mensaje-hora">
                        <small>${mensaje.hora}</small>
                    </div>
                </div>
            `;
        } else {
            const esUsuario = mensaje.tipo === 'usuario';
            const alineacion = esUsuario ? 'right' : 'left';
            const bgColor = esUsuario ? 'primary' : 'light';
            
            contenido = `
                <div class="d-flex justify-content-${alineacion} mb-2">
                    <div class="chat-mensaje-burbuja bg-${bgColor} ${esUsuario ? 'text-white' : ''}">
                        <div class="chat-mensaje-contenido">
                            <p class="mb-1">${this.escapeHtml(mensaje.mensaje)}</p>
                        </div>
                        <div class="chat-mensaje-hora text-${esUsuario ? 'white-50' : 'muted'}">
                            <small>${mensaje.hora}</small>
                        </div>
                    </div>
                </div>
            `;
        }
        
        mensajeElement.innerHTML = contenido;
        contenedor.appendChild(mensajeElement);
        
        // Scroll automático si el usuario está cerca del final
        this.scrollAlFinal();
    }
    
    actualizarEstadoConversacion(conversacion) {
        if (!conversacion) return;
        
        // Actualizar header del chat
        const header = document.getElementById('chat-header');
        if (header) {
            let estadoText = '';
            let estadoClass = '';
            
            switch(conversacion.estado) {
                case 'activa':
                    estadoText = conversacion.agente_asignado ? 
                        `Conectado con ${conversacion.nombre_agente || 'agente'}` : 
                        'Esperando agente...';
                    estadoClass = 'text-success';
                    break;
                case 'en_espera':
                    estadoText = 'En espera de agente';
                    estadoClass = 'text-warning';
                    break;
                case 'cerrada':
                    estadoText = 'Conversación cerrada';
                    estadoClass = 'text-danger';
                    break;
                default:
                    estadoText = 'Desconectado';
                    estadoClass = 'text-muted';
            }
            
            const estadoElement = header.querySelector('.chat-estado');
            if (estadoElement) {
                estadoElement.textContent = estadoText;
                estadoElement.className = `chat-estado ${estadoClass}`;
            }
        }
        
        // Mostrar/ocultar botones según estado
        this.actualizarBotones(conversacion.estado);
    }
    
    actualizarBotones(estado) {
        const cerrarBtn = document.getElementById('chat-cerrar-btn');
        const enviarBtn = document.getElementById('chat-enviar-btn');
        const input = document.getElementById('chat-mensaje-input');
        
        if (estado === 'cerrada' || estado === 'resuelta') {
            if (cerrarBtn) cerrarBtn.style.display = 'none';
            if (enviarBtn) enviarBtn.disabled = true;
            if (input) {
                input.disabled = true;
                input.placeholder = 'Esta conversación ha sido cerrada';
            }
            this.detenerPolling();
        } else {
            if (cerrarBtn) cerrarBtn.style.display = 'block';
            if (enviarBtn) enviarBtn.disabled = false;
            if (input) {
                input.disabled = false;
                input.placeholder = 'Escribe tu mensaje...';
            }
        }
    }
    
    async cerrarConversacion() {
        try {
            if (!this.conversacionId) return;
            
            const confirmado = await Swal.fire({
                title: '¿Cerrar conversación?',
                text: '¿Estás seguro de que quieres cerrar esta conversación?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Sí, cerrar',
                cancelButtonText: 'Cancelar'
            });
            
            if (!confirmado.isConfirmed) return;
            
            // Mostrar formulario de valoración
            const { value: formValues } = await Swal.fire({
                title: 'Valorar conversación',
                html: `
                    <div class="text-center mb-3">
                        <div class="rating-stars mb-3">
                            <i class="fas fa-star star-rating" data-rating="1"></i>
                            <i class="fas fa-star star-rating" data-rating="2"></i>
                            <i class="fas fa-star star-rating" data-rating="3"></i>
                            <i class="fas fa-star star-rating" data-rating="4"></i>
                            <i class="fas fa-star star-rating" data-rating="5"></i>
                        </div>
                        <input type="hidden" id="valoracion" value="0">
                        <textarea id="comentario" class="form-control mt-3" placeholder="Comentario (opcional)" rows="3"></textarea>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: 'Enviar y cerrar',
                cancelButtonText: 'Cancelar',
                didOpen: () => {
                    // Configurar estrellas de valoración
                    const stars = document.querySelectorAll('.star-rating');
                    stars.forEach(star => {
                        star.addEventListener('click', function() {
                            const rating = this.getAttribute('data-rating');
                            document.getElementById('valoracion').value = rating;
                            
                            // Actualizar visualización de estrellas
                            stars.forEach(s => {
                                if (s.getAttribute('data-rating') <= rating) {
                                    s.classList.add('text-warning');
                                } else {
                                    s.classList.remove('text-warning');
                                }
                            });
                        });
                    });
                }
            });
            
            if (!formValues) return;
            
            const valoracion = document.getElementById('valoracion')?.value || 0;
            const comentario = document.getElementById('comentario')?.value || '';
            
            const formData = new FormData();
            formData.append('funcion', 'cerrar_conversacion');
            formData.append('conversacion_id', this.conversacionId);
            formData.append('valoracion', valoracion);
            formData.append('comentario', comentario);
            
            const response = await fetch(this.config.urlController, {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.mostrarMensaje('Conversación cerrada correctamente', 'success');
                
                // Limpiar datos locales
                localStorage.removeItem('chat_conversacion_activa');
                this.conversacionId = null;
                this.ultimoMensajeId = 0;
                
                // Actualizar interfaz
                this.actualizarInterfazChat();
                
            } else {
                throw new Error(data.error || 'Error al cerrar conversación');
            }
            
        } catch (error) {
            console.error('Error cerrando conversación:', error);
            this.mostrarError(error.message);
        }
    }
    
    actualizarInterfazChat() {
        const inicioChat = document.getElementById('chat-inicio');
        const conversacionChat = document.getElementById('chat-conversacion');
        
        if (this.conversacionId) {
            // Mostrar conversación activa
            if (inicioChat) inicioChat.style.display = 'none';
            if (conversacionChat) conversacionChat.style.display = 'block';
            
            // Cargar mensajes
            this.obtenerMensajes();
            
            // Ocultar notificación si está visible
            this.ocultarNotificacion();
            
        } else {
            // Mostrar formulario de inicio
            if (inicioChat) inicioChat.style.display = 'block';
            if (conversacionChat) conversacionChat.style.display = 'none';
            
            // Verificar disponibilidad de agentes
            this.verificarAgentesDisponibles().then(disponibles => {
                this.actualizarDisponibilidadAgentes(disponibles);
            });
        }
    }
    
    async verificarEstadoConversacion() {
        try {
            if (!this.conversacionId) return;
            
            const formData = new FormData();
            formData.append('funcion', 'obtener_mensajes');
            formData.append('conversacion_id', this.conversacionId);
            
            const response = await fetch(this.config.urlController, {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                if (data.data.conversacion) {
                    // La conversación existe y está activa
                    this.iniciarPolling();
                    this.actualizarInterfazChat();
                } else {
                    // La conversación ya no existe
                    localStorage.removeItem('chat_conversacion_activa');
                    this.conversacionId = null;
                    this.actualizarInterfazChat();
                }
            }
            
        } catch (error) {
            console.error('Error verificando estado:', error);
        }
    }
    
    toggleChat() {
        const chatContainer = document.getElementById('chat-container');
        if (!chatContainer) return;
        
        if (chatContainer.style.display === 'none' || !chatContainer.style.display) {
            chatContainer.style.display = 'block';
            this.verificarAgentesDisponibles().then(disponibles => {
                this.actualizarDisponibilidadAgentes(disponibles);
            });
            
            // Ocultar notificación al abrir el chat
            this.ocultarNotificacion();
            
        } else {
            chatContainer.style.display = 'none';
        }
    }
    
    cerrarChat() {
        const chatContainer = document.getElementById('chat-container');
        if (chatContainer) {
            chatContainer.style.display = 'none';
        }
    }
    
    actualizarDisponibilidadAgentes(disponibles) {
        const estadoElement = document.getElementById('chat-agentes-estado');
        if (estadoElement) {
            if (disponibles) {
                estadoElement.innerHTML = '<i class="fas fa-circle text-success mr-1"></i> Agentes disponibles';
                estadoElement.className = 'text-success';
                
                // Habilitar botón de iniciar chat
                const btnIniciar = document.getElementById('iniciar-chat-btn');
                if (btnIniciar) {
                    btnIniciar.disabled = false;
                    btnIniciar.innerHTML = '<i class="fas fa-comment-dots mr-2"></i> Iniciar Chat';
                }
            } else {
                estadoElement.innerHTML = '<i class="fas fa-circle text-warning mr-1"></i> Agentes ocupados';
                estadoElement.className = 'text-warning';
                
                // Mostrar mensaje informativo pero permitir iniciar chat
                const btnIniciar = document.getElementById('iniciar-chat-btn');
                if (btnIniciar) {
                    btnIniciar.disabled = false;
                    btnIniciar.innerHTML = '<i class="fas fa-clock mr-2"></i> Iniciar Chat (en espera)';
                }
            }
        }
    }
    
    mostrarNotificacionNuevoMensaje() {
        const notification = document.getElementById('chat-notification');
        const chatToggle = document.getElementById('chat-toggle-btn');
        
        if (notification) {
            notification.textContent = '1';
            notification.style.display = 'flex';
        }
        
        if (chatToggle) {
            // Agregar animación de parpadeo
            chatToggle.style.animation = 'pulse 1s infinite';
            
            // Quitar animación después de 10 segundos
            setTimeout(() => {
                chatToggle.style.animation = '';
            }, 10000);
        }
    }
    
    ocultarNotificacion() {
        const notification = document.getElementById('chat-notification');
        const chatToggle = document.getElementById('chat-toggle-btn');
        
        if (notification) {
            notification.style.display = 'none';
        }
        
        if (chatToggle) {
            chatToggle.style.animation = '';
        }
    }
    
    scrollAlFinal() {
        const contenedor = document.getElementById('chat-mensajes-container');
        if (contenedor) {
            // Scroll suave al final
            contenedor.scrollTo({
                top: contenedor.scrollHeight,
                behavior: 'smooth'
            });
        }
    }
    
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    mostrarCargando(mensaje) {
        // Implementar según tu sistema de notificaciones
        // console.log('Cargando:', mensaje);
        
        // Si usas SweetAlert2
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: mensaje,
                allowOutsideClick: false,
                showConfirmButton: false,
                willOpen: () => {
                    Swal.showLoading();
                }
            });
        }
    }
    
    mostrarMensaje(mensaje, tipo = 'info') {
        // Usar SweetAlert o tu sistema de notificaciones
        if (typeof Swal !== 'undefined') {
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true
            });
            
            Toast.fire({
                icon: tipo,
                title: mensaje
            });
        } else {
            // Fallback a alert básico
            alert(mensaje);
        }
    }
    
    mostrarError(mensaje) {
        this.mostrarMensaje(mensaje, 'error');
    }
    
    // ============================================
    // MÉTODO DE DIAGNÓSTICO MEJORADO
    // ============================================
    
    async diagnosticarSistemaChat() {
        try {
            console.clear();
            console.log('%c=== DIAGNÓSTICO COMPLETO DEL SISTEMA DE CHAT ===', 'font-size: 16px; font-weight: bold; color: #4361ee;');
            
            const formData = new FormData();
            formData.append('funcion', 'diagnostico');
            
            console.log('📡 Enviando solicitud de diagnóstico...');
            console.log('URL:', this.config.urlController);
            
            const inicio = Date.now();
            const response = await fetch(this.config.urlController, {
                method: 'POST',
                body: formData
            });
            const tiempo = Date.now() - inicio;
            
            console.log('⏱️ Tiempo de respuesta:', tiempo + 'ms');
            console.log('📊 Status:', response.status, response.statusText);
            
            if (!response.ok) {
                console.error('❌ ERROR: No se pudo conectar al controlador');
                console.error('   Status:', response.status);
                console.error('   URL:', this.config.urlController);
                console.error('   Verifica que ChatController.php exista en esa ubicación');
                return false;
            }
            
            const text = await response.text();
            console.log('📄 Respuesta en crudo:', text.substring(0, 500) + '...');
            
            let data;
            try {
                data = JSON.parse(text);
            } catch (parseError) {
                console.error('❌ ERROR parseando JSON:', parseError.message);
                console.log('🔍 Posible problema:');
                console.log('   1. El controlador tiene errores PHP (revisa los logs)');
                console.log('   2. Hay salida HTML antes del JSON');
                console.log('   3. El encoding no es UTF-8');
                return false;
            }
            
            console.log('✅ JSON parseado correctamente');
            console.log('📦 Datos recibidos:', data);
            
            if (!data.success) {
                console.error('❌ Error del servidor:', data.error);
                return false;
            }
            
            const diag = data.data.diagnostico || {};
            const servidor = data.data.servidor || {};
            
            // Mostrar información del servidor
            console.log('%c=== INFORMACIÓN DEL SERVIDOR ===', 'font-size: 14px; font-weight: bold; color: #7209b7;');
            console.log('🖥️  PHP:', servidor.php_version);
            console.log('🗄️  MySQL:', servidor.mysql_version || 'No disponible');
            console.log('🕐 Hora servidor:', servidor.server_time);
            console.log('🔧 Software:', servidor.server_software);
            
            // Mostrar diagnóstico
            console.log('%c=== DIAGNÓSTICO DE LA BASE DE DATOS ===', 'font-size: 14px; font-weight: bold; color: #f72585;');
            console.log('📁 Base de datos:', diag.base_datos || 'No especificada');
            console.log('🔗 Conexión:', diag.conexion_ok ? '✅ OK' : '❌ FALLIDA');
            
            if (diag.error) {
                console.error('💥 Error en diagnóstico:', diag.error);
            }
            
            // Mostrar tablas encontradas
            console.log('%c=== TABLAS ENCONTRADAS ===', 'font-size: 14px; font-weight: bold; color: #4cc9f0;');
            
            if (diag.todas_las_tablas && diag.todas_las_tablas.length > 0) {
                console.log('📋 Todas las tablas (' + diag.todas_las_tablas.length + '):');
                diag.todas_las_tablas.forEach((tabla, i) => {
                    console.log('   ' + (i + 1) + '. ' + tabla);
                });
            }
            
            if (diag.tablas_encontradas && diag.tablas_encontradas.length > 0) {
                console.log('🔍 Tablas con "chat" (' + diag.tablas_encontradas.length + '):');
                diag.tablas_encontradas.forEach(tabla => {
                    console.log('   📄 ' + tabla);
                });
            }
            
            // Mostrar estado de las tablas requeridas
            console.log('%c=== TABLAS REQUERIDAS ===', 'font-size: 14px; font-weight: bold; color: #ff9e00;');
            
            const tablas_requeridas = [
                { nombre: 'chat_conversaciones', existe: diag.tabla_conversaciones_existe },
                { nombre: 'chat_mensajes', existe: diag.tabla_mensajes_existe },
                { nombre: 'chat_agentes', existe: diag.tabla_agentes_existe }
            ];
            
            let todas_existen = true;
            tablas_requeridas.forEach(tabla => {
                const icono = tabla.existe ? '✅' : '❌';
                const color = tabla.existe ? 'green' : 'red';
                console.log(`   %c${icono} ${tabla.nombre}`, `color: ${color}; font-weight: bold;`);
                if (!tabla.existe) todas_existen = false;
            });
            
            if (!todas_existen) {
                console.log('%c=== ACCIÓN REQUERIDA ===', 'font-size: 14px; font-weight: bold; color: red; background: yellow; padding: 5px;');
                console.log('⚠️  FALTAN TABLAS. Ejecuta este SQL en tu base de datos:');
                console.log(`
-----------------------------------------
-- CREAR TABLAS DEL CHAT
-----------------------------------------
DROP TABLE IF EXISTS chat_mensajes;
DROP TABLE IF EXISTS chat_conversaciones;
DROP TABLE IF EXISTS chat_agentes;

CREATE TABLE chat_conversaciones (
    id VARCHAR(32) PRIMARY KEY,
    usuario_id INT DEFAULT NULL,
    nombre_usuario VARCHAR(100) NOT NULL,
    email_usuario VARCHAR(100) NOT NULL,
    asunto VARCHAR(200) DEFAULT 'Consulta general',
    categoria VARCHAR(50) DEFAULT 'general',
    agente_asignado INT DEFAULT NULL,
    estado VARCHAR(20) DEFAULT 'en_espera',
    ip_address VARCHAR(45),
    user_agent TEXT,
    fecha_inicio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ultimo_mensaje TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    fecha_cierre TIMESTAMP NULL,
    valoracion TINYINT DEFAULT NULL,
    comentario_cierre TEXT
);

CREATE TABLE chat_mensajes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    conversacion_id VARCHAR(32) NOT NULL,
    usuario_id INT DEFAULT NULL,
    nombre_usuario VARCHAR(100) NOT NULL,
    email_usuario VARCHAR(100) NOT NULL,
    mensaje TEXT NOT NULL,
    tipo VARCHAR(20) DEFAULT 'usuario',
    leido BOOLEAN DEFAULT FALSE,
    estado VARCHAR(20) DEFAULT 'enviado',
    ip_address VARCHAR(45),
    user_agent TEXT,
    fecha_envio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_lectura TIMESTAMP NULL
);

CREATE TABLE chat_agentes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    nombre_agente VARCHAR(100) NOT NULL,
    estado VARCHAR(20) DEFAULT 'offline',
    conversaciones_activas INT DEFAULT 0,
    max_conversaciones INT DEFAULT 5,
    ultima_actividad TIMESTAMP NULL
);

INSERT INTO chat_agentes (usuario_id, nombre_agente, estado, max_conversaciones) 
VALUES (1, 'Soporte NexusBuy', 'disponible', 5);
-----------------------------------------
                `);
            } else {
                console.log('%c=== ✅ TODAS LAS TABLAS EXISTEN ===', 'font-size: 14px; font-weight: bold; color: green;');
                console.log('Ahora puedes probar iniciar un chat.');
            }
            
            return todas_existen;
            
        } catch (error) {
            console.error('❌ ERROR en diagnóstico:', error);
            console.error('Stack:', error.stack);
            return false;
        }
    }
}

// ============================================
// CÓDIGO DE INICIALIZACIÓN Y BOTÓN DE DIAGNÓSTICO
// ============================================

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    window.chatSistema = new ChatEnVivo();
    
    // Agregar botón toggle al DOM si no existe
    if (!document.querySelector('.chat-toggle')) {
        const toggleBtn = document.createElement('div');
        toggleBtn.className = 'chat-toggle';
        toggleBtn.id = 'chat-toggle-btn';
        toggleBtn.innerHTML = '<i class="fas fa-comments fa-2x"></i>';
        document.body.appendChild(toggleBtn);
    }
    
    // Agregar botón de diagnóstico al DOM
    // if (!document.getElementById('btn-diagnostico-chat')) {
    //     const btn = document.createElement('button');
    //     btn.id = 'btn-diagnostico-chat';
    //     btn.innerHTML = '🩺 Diagnóstico Chat';
    //     btn.style.cssText = `
    //         position: fixed;
    //         bottom: 100px;
    //         right: 20px;
    //         z-index: 1060;
    //         background: #ff6b6b;
    //         color: white;
    //         border: none;
    //         padding: 10px 15px;
    //         border-radius: 5px;
    //         cursor: pointer;
    //         font-size: 12px;
    //         box-shadow: 0 2px 10px rgba(0,0,0,0.2);
    //         transition: all 0.3s ease;
    //     `;
        
    //     btn.onmouseover = function() {
    //         this.style.transform = 'scale(1.05)';
    //         this.style.boxShadow = '0 4px 15px rgba(0,0,0,0.3)';
    //     };
        
    //     btn.onmouseout = function() {
    //         this.style.transform = 'scale(1)';
    //         this.style.boxShadow = '0 2px 10px rgba(0,0,0,0.2)';
    //     };
        
    //     btn.onclick = async function() {
    //         if (window.chatSistema) {
    //             btn.innerHTML = '🩺 Diagnóstico...';
    //             btn.disabled = true;
    //             await window.chatSistema.diagnosticarSistemaChat();
    //             btn.innerHTML = '🩺 Diagnóstico Chat';
    //             btn.disabled = false;
    //         } else {
    //             console.error('Chat sistema no inicializado');
    //         }
    //     };
        
    //     document.body.appendChild(btn);
    // }
    
    // Agregar animación CSS para el parpadeo
    if (!document.getElementById('chat-animation-style')) {
        const style = document.createElement('style');
        style.id = 'chat-animation-style';
        style.textContent = `
            @keyframes pulse {
                0% { transform: scale(1); box-shadow: 0 4px 15px rgba(37, 211, 102, 0.3); }
                50% { transform: scale(1.1); box-shadow: 0 4px 20px rgba(37, 211, 102, 0.6); }
                100% { transform: scale(1); box-shadow: 0 4px 15px rgba(37, 211, 102, 0.3); }
            }
            
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
                display: none;
            }
        `;
        document.head.appendChild(style);
    }
});

// Exportar para uso global
if (typeof window !== 'undefined') {
    window.ChatEnVivo = ChatEnVivo;
}