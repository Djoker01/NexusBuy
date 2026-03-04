$(document).ready(function () {
    var funcion;
    var notificaciones = [];
    var filtroActual = 'all';
    var notificacionesSeleccionadas = [];
    
    // Inicializar
    cargarNotificaciones();
    configurarEventos();
    
    // Función para cargar notificaciones
    function cargarNotificaciones() {
        funcion = 'read_all_notificaciones';
        $('#notifications-container').html(`
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="sr-only">Cargando...</span>
                </div>
                <p class="text-muted mt-2">Cargando notificaciones...</p>
            </div>
        `);
        
        $.post("../Controllers/NotificacionController.php", { funcion }, (response) => {
            try {
                notificaciones = JSON.parse(response);
                if (!notificaciones.error) {
                    if (notificaciones.length === 0) {
                        mostrarEstadoVacio();
                    } else {
                        mostrarNotificaciones(notificaciones);
                        aplicarFiltroActual();
                    }
                } else {
                    mostrarError('Error al cargar notificaciones');
                }
            } catch (e) {
                console.error('Error al parsear respuesta:', e);
                mostrarError('Error al cargar notificaciones');
            }
        });
    }
    
    // Función para mostrar notificaciones
    function mostrarNotificaciones(notifs) {
        let html = '';
        notifs.forEach(notif => {
            const fecha = formatearFecha(notif.fecha_creacion);
            const tipo = obtenerTipoClase(notif.tipo);
            const icono = obtenerIconoTipo(notif.tipo);
            const tipoTexto = obtenerTextoInicial(notif.tipo);
            const noLeida = notif.leida == 0;
            
            html += `
                <div class="notification-item ${noLeida ? 'unread' : ''}" 
                     data-id="${notif.id}" 
                     data-type="${tipo}">
                    <span class="notification-indicator"></span>
                    <div class="notification-icon ${tipo}">
                        <i class="${icono}"></i>
                    </div>
                    <div class="notification-content">
                        <div class="notification-title">${notif.titulo}</div>
                        <div class="notification-message">${notif.mensaje}</div>
                        <div class="notification-meta">
                            <div class="notification-time">
                                <i class="far fa-clock"></i> ${fecha}
                            </div>
                            <div class="notification-type">${tipoTexto}</div>
                        </div>
                    </div>
                    <div class="notification-actions">
                        ${noLeida ? `
                        <button class="notification-action-btn read" onclick="marcarComoLeidaIndividual(event, ${notif.id})" title="Marcar como leída">
                            <i class="fas fa-check"></i>
                        </button>
                        ` : ''}
                        <button class="notification-action-btn delete" onclick="eliminarNotificacion(event, ${notif.id})" title="Eliminar">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            `;
        });
        
        $('#notifications-container').html(html);
        $('#emptyState').hide();
        $('#notifications-container').show();
    }
    
    // Función para filtrar notificaciones
    function aplicarFiltroActual() {
        const items = $('.notification-item');
        
        items.each(function() {
            const $item = $(this);
            const tipo = $item.data('type');
            const esNoLeida = $item.hasClass('unread');
            
            let mostrar = true;
            
            switch(filtroActual) {
                case 'all':
                    mostrar = true;
                    break;
                case 'unread':
                    mostrar = esNoLeida;
                    break;
                case 'orders':
                    mostrar = tipo === 'order';
                    break;
                case 'promotions':
                    mostrar = tipo === 'promo';
                    break;
                case 'system':
                    mostrar = tipo === 'system';
                    break;
                case 'security':
                    mostrar = tipo === 'security';
                    break;
            }
            
            if (mostrar) {
                $item.show();
            } else {
                $item.hide();
            }
        });
        
        // Verificar si hay notificaciones visibles
        const visibles = $('.notification-item:visible').length;
        if (visibles === 0 && notificaciones.length > 0) {
            $('#notifications-container').html(`
                <div class="text-center py-5">
                    <i class="far fa-filter text-muted" style="font-size: 3rem;"></i>
                    <h4 class="mt-3">No hay notificaciones con este filtro</h4>
                    <p class="text-muted">Intenta con otro filtro o limpia los filtros.</p>
                    <button class="btn btn-primary-modern" onclick="limpiarFiltros()">
                        <i class="fas fa-times mr-2"></i>Limpiar Filtros
                    </button>
                </div>
            `);
        }
    }
    
    // Función para configurar eventos
    function configurarEventos() {
        // Filtros rápidos
        $('.filter-tab').click(function() {
            $('.filter-tab').removeClass('active');
            $(this).addClass('active');
            filtroActual = $(this).data('filter');
            aplicarFiltroActual();
        });
        
        // Click en notificación
        $(document).on('click', '.notification-item', function(e) {
            if (!$(e.target).closest('.notification-actions').length) {
                const id = $(this).data('id');
                mostrarDetalleNotificacion(id);
            }
        });
    }
    
    // Función para marcar como leída individual
    window.marcarComoLeidaIndividual = function(event, id) {
        event.stopPropagation();
        funcion = 'mark_as_read';
        
        $.post("../Controllers/NotificacionController.php", { funcion, id }, (response) => {
            try {
                const data = JSON.parse(response);
                if (data.success) {
                    // Actualizar UI
                    $(`.notification-item[data-id="${id}"]`).removeClass('unread');
                    $(`.notification-item[data-id="${id}"] .notification-action-btn.read`).remove();
                    
                    // Mostrar toast de éxito
                    mostrarToast('success', 'Notificación marcada como leída');
                }
            } catch (e) {
                console.error('Error:', e);
                mostrarToast('error', 'Error al marcar como leída');
            }
        });
        $(document).trigger('notificacion-actualizada');
    }
    
    // Función para eliminar notificación
    window.eliminarNotificacion = function(event, id) {
        event.stopPropagation();
        
        Swal.fire({
            title: '¿Eliminar notificación?',
            text: 'Esta acción no se puede deshacer',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#4361ee',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                funcion = 'delete';
                
                $.post("../Controllers/NotificacionController.php", { funcion, id }, (response) => {
                    try {
                        const data = JSON.parse(response);
                        if (data.success) {
                            // Remover del DOM
                            $(`.notification-item[data-id="${id}"]`).remove();
                            
                            // Actualizar notificaciones array
                            notificaciones = notificaciones.filter(n => n.id != id);
                            
                            // Verificar si quedan notificaciones
                            if (notificaciones.length === 0) {
                                mostrarEstadoVacio();
                            }
                            
                            mostrarToast('success', 'Notificación eliminada');
                        }
                    } catch (e) {
                        console.error('Error:', e);
                        mostrarToast('error', 'Error al eliminar notificación');
                    }
                });
            }
        });
        $(document).trigger('notificacion-actualizada');
    }
    
    // Función para marcar todas como leídas
    window.marcarTodasLeidas = function() {
        Swal.fire({
            title: '¿Marcar todas como leídas?',
            text: 'Se marcarán todas las notificaciones no leídas',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#4361ee',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, marcar todas',
            cancelButtonText: 'Cancelar',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                funcion = 'mark_all_as_read';
                
                $.post("../Controllers/NotificacionController.php", { funcion }, (response) => {
                    try {
                        const data = JSON.parse(response);
                        if (data.success) {
                            // Actualizar UI
                            $('.notification-item').removeClass('unread');
                            $('.notification-action-btn.read').remove();
                            
                            mostrarToast('success', 'Todas las notificaciones marcadas como leídas');
                        }
                    } catch (e) {
                        console.error('Error:', e);
                        mostrarToast('error', 'Error al marcar notificaciones');
                    }
                });
            }
        });
        $(document).trigger('notificacion-actualizada');
    }
    
    // Función para mostrar detalle de notificación
    window.mostrarDetalleNotificacion = function(id) {
        const notif = notificaciones.find(n => n.id == id);
        if (!notif) return;
        
        const fecha = formatearFechaCompleta(notif.fecha_creacion);
        const tipo = obtenerTipoClase(notif.tipo);
        const icono = obtenerIconoTipo(notif.tipo);
        const tipoTexto = obtenerTextoInicial(notif.tipo);
        
        let contenido = `
            <div class="notification-detail">
                <div class="notification-detail-header">
                    <div class="notification-detail-icon ${tipo}">
                        <i class="${icono}"></i>
                    </div>
                    <div class="notification-detail-info">
                        <h4>${notif.titulo}</h4>
                        <div class="notification-detail-time">
                            <i class="far fa-clock mr-1"></i> ${fecha}
                        </div>
                        <div class="badge badge-modern mt-2">${tipoTexto}</div>
                    </div>
                </div>
                <div class="notification-detail-body">
                    <p>${notif.mensaje.replace(/\n/g, '</p><p>')}</p>
                </div>
        `;
        
        if (notif.url) {
            contenido += `
                <div class="notification-detail-actions">
                    <a href="${notif.url}" class="btn btn-primary-modern">
                        <i class="fas fa-external-link-alt mr-2"></i>Ver más
                    </a>
            `;
        } else {
            contenido += `<div class="notification-detail-actions">`;
        }
        
        contenido += `
                    <button class="btn btn-outline-modern" onclick="marcarComoLeidaDesdeModal(${notif.id})">
                        <i class="fas fa-check mr-2"></i>Marcar como Leída
                    </button>
                </div>
            </div>
        `;
        
        $('#notificacionDetalleContent').html(contenido);
        $('#modalNotificacionDetalle').modal('show');
    }
    
    // Función para marcar como leída desde modal
    window.marcarComoLeidaDesdeModal = function(id) {
        funcion = 'mark_as_read';
        
        $.post("../Controllers/NotificacionController.php", { funcion, id }, (response) => {
            try {
                const data = JSON.parse(response);
                if (data.success) {
                    // Cerrar modal
                    $('#modalNotificacionDetalle').modal('hide');
                    
                    // Actualizar UI
                    $(`.notification-item[data-id="${id}"]`).removeClass('unread');
                    $(`.notification-item[data-id="${id}"] .notification-action-btn.read`).remove();
                    
                    mostrarToast('success', 'Notificación marcada como leída');
                }
            } catch (e) {
                console.error('Error:', e);
                mostrarToast('error', 'Error al marcar como leída');
            }
        });
    }
    
    // Función para recargar notificaciones
    window.recargarNotificaciones = function() {
        cargarNotificaciones();
        mostrarToast('info', 'Notificaciones actualizadas');
    }
    
    // Función para limpiar filtros
    window.limpiarFiltros = function() {
        // Resetear filtro rápido
        $('.filter-tab').removeClass('active');
        $('.filter-tab[data-filter="all"]').addClass('active');
        filtroActual = 'all';
        
        // Aplicar filtro
        $('.notification-item').show();
        
        mostrarToast('info', 'Filtros limpiados');
    }
    
    // Funciones auxiliares
    function formatearFecha(fechaStr) {
        const fecha = new Date(fechaStr);
        const ahora = new Date();
        const diffMs = ahora - fecha;
        const diffMins = Math.floor(diffMs / 60000);
        const diffHours = Math.floor(diffMs / 3600000);
        const diffDays = Math.floor(diffMs / 86400000);
        
        if (diffMins < 1) return 'Hace un momento';
        if (diffMins < 60) return `Hace ${diffMins} min`;
        if (diffHours < 24) return `Hace ${diffHours} h`;
        if (diffDays < 7) return `Hace ${diffDays} d`;
        
        return fecha.toLocaleDateString('es-ES', {
            day: 'numeric',
            month: 'short',
            year: 'numeric'
        });
    }
    
    function formatearFechaCompleta(fechaStr) {
        const fecha = new Date(fechaStr);
        return fecha.toLocaleDateString('es-ES', {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }
    
    function obtenerTipoClase(tipo) {
        const map = {
            'pedido': 'order',
            'promocion': 'promo',
            'sistema': 'system',
            'seguridad': 'security',
            'soporte': 'social'
        };
        return map[tipo] || 'system';
    }
    
    function obtenerIconoTipo(tipo) {
        const map = {
            'pedido': 'fas fa-shopping-cart',
            'promocion': 'fas fa-tag',
            'sistema': 'fas fa-cog',
            'seguridad': 'fas fa-shield-alt',
            'soporte': 'fas fa-headset'
        };
        return map[tipo] || 'fas fa-bell';
    }
    
    function obtenerTextoInicial(tipo) {
        const map = {
            'pedido': 'Pedido',
            'promocion': 'Promoción',
            'sistema': 'Sistema',
            'seguridad': 'Seguridad',
            'soporte': 'Soporte'
        };
        return map[tipo] || 'Sistema';
    }
    
    function mostrarEstadoVacio() {
        $('#notifications-container').hide();
        $('#emptyState').show();
        $('#paginationContainer').hide();
        $('#bulkActions').hide();
    }
    
    function mostrarError(mensaje) {
        $('#notifications-container').html(`
            <div class="text-center py-5">
                <i class="fas fa-exclamation-triangle text-danger" style="font-size: 3rem;"></i>
                <h4 class="mt-3 text-danger">Error</h4>
                <p class="text-muted">${mensaje}</p>
                <button class="btn btn-primary-modern" onclick="recargarNotificaciones()">
                    <i class="fas fa-redo mr-2"></i>Reintentar
                </button>
            </div>
        `);
    }
    
    function mostrarToast(tipo, mensaje) {
        // Crear toast si no existe
        if (!$('#toast-notificaciones').length) {
            $('body').append(`
                <div id="toast-notificaciones" class="toast" style="position: fixed; top: 20px; right: 20px; z-index: 9999;">
                    <div class="toast-header">
                        <strong class="me-auto">Notificaciones</strong>
                        <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
                    </div>
                    <div class="toast-body"></div>
                </div>
            `);
        }
        
        // Configurar toast
        const $toast = $('#toast-notificaciones');
        const $toastBody = $toast.find('.toast-body');
        
        // Configurar colores según tipo
        const bgColor = tipo === 'success' ? 'bg-success' : 
                       tipo === 'error' ? 'bg-danger' : 
                       tipo === 'info' ? 'bg-info' : 'bg-primary';
        
        $toast.find('.toast-header').removeClass('bg-success bg-danger bg-info bg-primary').addClass(bgColor + ' text-white');
        $toastBody.text(mensaje);
        
        // Mostrar toast
        $toast.toast('show');
        
        // Auto ocultar después de 3 segundos
        setTimeout(() => {
            $toast.toast('hide');
        }, 3000);
    }
    
    // Funciones para acciones masivas
    window.toggleSelectAll = function() {
        const selectAll = $('#selectAllNotifications').is(':checked');
        $('.notification-item input[type="checkbox"]').prop('checked', selectAll);
        actualizarContadorSeleccionadas();
    }
    
    window.actualizarContadorSeleccionadas = function() {
        const seleccionadas = $('.notification-item input[type="checkbox"]:checked').length;
        $('#selectedCount').text(seleccionadas);
        
        if (seleccionadas > 0) {
            $('#bulkActions').show();
        } else {
            $('#bulkActions').hide();
        }
    }
    
    window.marcarSeleccionadasLeidas = function() {
        const ids = [];
        $('.notification-item input[type="checkbox"]:checked').each(function() {
            ids.push($(this).closest('.notification-item').data('id'));
        });
        
        if (ids.length > 0) {
            Swal.fire({
                title: `¿Marcar ${ids.length} notificaciones como leídas?`,
                text: 'Las notificaciones seleccionadas se marcarán como leídas',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#4361ee',
                cancelButtonColor: '#6c757d',
                confirmButtonText: `Sí, marcar ${ids.length} notificaciones`,
                cancelButtonText: 'Cancelar',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    // Aquí iría la llamada AJAX para marcar múltiples
                    console.log('Marcar como leídas:', ids);
                    // Mientras tanto, simular acción
                    $('.notification-item input[type="checkbox"]:checked').each(function() {
                        const $item = $(this).closest('.notification-item');
                        $item.removeClass('unread');
                        $item.find('.notification-action-btn.read').remove();
                    });
                    mostrarToast('success', `${ids.length} notificaciones marcadas como leídas`);
                    $('#selectAllNotifications').prop('checked', false);
                    $('#bulkActions').hide();
                }
            });
        }
    }
    
    window.eliminarSeleccionadas = function() {
        const ids = [];
        $('.notification-item input[type="checkbox"]:checked').each(function() {
            ids.push($(this).closest('.notification-item').data('id'));
        });
        
        if (ids.length > 0) {
            Swal.fire({
                title: `¿Eliminar ${ids.length} notificaciones?`,
                text: 'Esta acción no se puede deshacer',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#4361ee',
                cancelButtonColor: '#6c757d',
                confirmButtonText: `Sí, eliminar ${ids.length} notificaciones`,
                cancelButtonText: 'Cancelar',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    // Aquí iría la llamada AJAX para eliminar múltiples
                    console.log('Eliminar:', ids);
                    // Mientras tanto, simular acción
                    $('.notification-item input[type="checkbox"]:checked').each(function() {
                        $(this).closest('.notification-item').remove();
                    });
                    
                    // Actualizar array de notificaciones
                    ids.forEach(id => {
                        notificaciones = notificaciones.filter(n => n.id != id);
                    });
                    
                    // Verificar si quedan notificaciones
                    if (notificaciones.length === 0) {
                        mostrarEstadoVacio();
                    }
                    
                    mostrarToast('success', `${ids.length} notificaciones eliminadas`);
                    $('#selectAllNotifications').prop('checked', false);
                    $('#bulkActions').hide();
                }
            });
        }
    }
});
