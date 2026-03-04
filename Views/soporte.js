// soporte.js - Archivo JavaScript completo para la página de soporte
// Versión organizada y mejorada

// =============================================
// MÓDULO PRINCIPAL - INICIALIZACIÓN
// =============================================
$(document).ready(function() {
    inicializarSoporte();
});

// =============================================
// FUNCIONES DE INICIALIZACIÓN
// =============================================
async function inicializarSoporte() {
    // console.log('Inicializando sistema de soporte...');
    
    try {
        // Cargar datos de configuración del sitio
        await cargarConfiguracionSitio();
        
        // Inicializar funcionalidades específicas según la página
        inicializarFuncionalidadesPorPagina();
        
        // Inicializar funcionalidades comunes
        inicializarFuncionalidadesComunes();
        
        // Inicializar funcionalidad de reintento
        inicializarReintentoEnvio();
        
    } catch (error) {
        console.error("Error inicializando soporte:", error);
        mostrarError("Error al cargar los datos de soporte");
    }
}

function inicializarFuncionalidadesPorPagina() {
    const url = window.location.href;
    
    // Inicializar formulario de contacto
    if (url.includes('filtro=contacto')) {
        // console.log('Inicializando página de contacto...');
        inicializarFormularioContacto();
    }
    
    // Formatear contenido legal
    if (url.includes('filtro=terminos_condiciones') || url.includes('filtro=privacidad')) {
        formatearContenidoLegal();
    }
    
    // Inicializar búsqueda en FAQ
    if (url.includes('filtro=centro_ayuda')) {
        inicializarBusquedaFAQ();
    }
}

function inicializarFuncionalidadesComunes() {
    // Inicializar efectos hover en categorías
    inicializarEfectosHover();
    
    // Inicializar animaciones para FAQs
    inicializarAnimacionesFAQ();
}

// =============================================
// CONFIGURACIÓN DEL SITIO
// =============================================
async function cargarConfiguracionSitio() {
    try {
        const response = await $.post("../Controllers/ConfiguracionSitioController.php", {
            funcion: "obtener_datos_soporte",
        });
        
        // console.log("Datos de configuración cargados:", response);
        
        if (response.status === 'success') {
            localStorage.setItem('configuracion_sitio', JSON.stringify(response.data));
            actualizarElementosDinamicos(response.data);
        }
    } catch (error) {
        console.error("Error cargando configuración:", error);
    }
}

function actualizarElementosDinamicos(datos) {
    if (datos.general && datos.general.nombre_tienda) {
        document.title = `Soporte - ${datos.general.nombre_tienda.valor}`;
    }
}

// =============================================
// FORMULARIO DE CONTACTO - FUNCIONES PRINCIPALES
// =============================================
function inicializarFormularioContacto() {
    // console.log("Inicializando formulario de contacto...");
    
    const formContacto = document.getElementById('formContacto');
    const mensajeTextarea = document.getElementById('mensaje');
    const mensajeContador = document.getElementById('mensajeContador');
    
    if (!formContacto) {
        console.warn('Formulario de contacto no encontrado');
        return;
    }
    
    // Inicializar máscara de teléfono
    inicializarMascaraTelefono();
    
    // Inicializar contador de caracteres
    if (mensajeTextarea && mensajeContador) {
        inicializarContadorCaracteres(mensajeTextarea, mensajeContador);
    }
    
    // Inicializar validación en tiempo real
    inicializarValidacionFormulario(formContacto);
    
    // Inicializar manejo de envío
    inicializarEnvioFormulario(formContacto, mensajeTextarea, mensajeContador);
    
    // Auto-completar datos del usuario si está logueado
    autocompletarDatosUsuario();
    
    // Inicializar eventos de asunto
    inicializarEventosAsunto();
    
    // console.log('Formulario de contacto inicializado correctamente');
}

function inicializarMascaraTelefono() {
    const telefonoInput = document.getElementById('telefono');
    if (!telefonoInput) return;
    
    telefonoInput.addEventListener('input', function(e) {
        let valor = e.target.value.replace(/\D/g, '');
        
        if (valor.length > 0) {
            // Formato cubano: +53 5 XXX XXXX
            if (valor.startsWith('53')) {
                valor = '+53 ' + valor.substring(2);
            }
            
            // Limitar a 8 dígitos después del código
            if (valor.replace(/\D/g, '').length > 8) {
                valor = valor.substring(0, valor.length - 1);
            }
        }
        
        e.target.value = valor;
    });
}

function inicializarContadorCaracteres(textarea, contador) {
    function actualizarContador() {
        const longitud = textarea.value.length;
        contador.textContent = `${longitud}/2000 caracteres`;
        
        // Cambiar color según la longitud
        contador.classList.remove('caracteres-bajos', 'caracteres-muy-bajos');
        if (longitud < 10) {
            contador.classList.add('caracteres-muy-bajos');
        } else if (longitud < 30) {
            contador.classList.add('caracteres-bajos');
        }
    }
    
    textarea.addEventListener('input', actualizarContador);
    actualizarContador(); // Inicializar
}

function inicializarValidacionFormulario(formulario) {
    const inputs = formulario.querySelectorAll('input, textarea, select');
    
    inputs.forEach(input => {
        input.addEventListener('blur', function() {
            validarCampo(this);
        });
        
        input.addEventListener('input', function() {
            if (this.classList.contains('is-invalid')) {
                this.classList.remove('is-invalid');
                const feedback = this.nextElementSibling;
                if (feedback && feedback.classList.contains('invalid-feedback')) {
                    feedback.remove();
                }
            }
        });
    });
    
    // Limpiar validaciones al resetear
    formulario.addEventListener('reset', function() {
        resetearFormulario(formulario);
    });
}

function validarCampo(campo) {
    const valor = campo.value.trim();
    let valido = true;
    let mensajeError = '';
    
    switch(campo.id) {
        case 'nombre':
            if (valor.length < 2) {
                valido = false;
                mensajeError = 'El nombre debe tener al menos 2 caracteres.';
            }
            break;
            
        case 'email':
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(valor)) {
                valido = false;
                mensajeError = 'Por favor ingresa un email válido.';
            }
            break;
            
        case 'telefono':
            if (valor && !/^[\d\s\-\+\(\)]{8,20}$/.test(valor)) {
                valido = false;
                mensajeError = 'El teléfono no tiene un formato válido.';
            }
            break;
            
        case 'asunto':
            if (!valor) {
                valido = false;
                mensajeError = 'Por favor selecciona un asunto.';
            }
            break;
            
        case 'mensaje':
            if (valor.length < 10) {
                valido = false;
                mensajeError = 'El mensaje debe tener al menos 10 caracteres.';
            }
            break;
            
        case 'privacidad':
            if (!campo.checked) {
                valido = false;
                mensajeError = 'Debes aceptar la política de privacidad.';
            }
            break;
    }
    
    if (!valido) {
        campo.classList.add('is-invalid');
        let feedback = campo.nextElementSibling;
        
        if (feedback && feedback.classList.contains('invalid-feedback')) {
            feedback.textContent = mensajeError;
        } else {
            feedback = document.createElement('div');
            feedback.className = 'invalid-feedback';
            feedback.textContent = mensajeError;
            campo.parentNode.insertBefore(feedback, campo.nextSibling);
        }
    } else {
        campo.classList.remove('is-invalid');
        campo.classList.add('is-valid');
        
        const feedback = campo.nextElementSibling;
        if (feedback && feedback.classList.contains('invalid-feedback')) {
            feedback.remove();
        }
    }
    
    return valido;
}

function validarCheckboxPrivacidad() {
    const checkbox = document.getElementById('privacidad');
    if (!checkbox) return true; // Si no existe, no validar
    
    if (!checkbox.checked) {
        // Mostrar error específico
        checkbox.classList.add('is-invalid');
        
        let feedback = checkbox.nextElementSibling;
        if (!feedback || !feedback.classList.contains('invalid-feedback')) {
            feedback = document.createElement('div');
            feedback.className = 'invalid-feedback';
            feedback.textContent = 'Debes aceptar la política de privacidad.';
            checkbox.parentNode.insertBefore(feedback, checkbox.nextSibling);
        }
        
        // Desplazar a la vista
        checkbox.scrollIntoView({ behavior: 'smooth', block: 'center' });
        
        // Resaltar
        checkbox.style.boxShadow = '0 0 0 3px rgba(220, 53, 69, 0.25)';
        setTimeout(() => {
            checkbox.style.boxShadow = '';
        }, 2000);
        
        return false;
    }
    
    return true;
}

function validarFormularioCompleto(formulario) {
    let valido = true;
    const camposRequeridos = formulario.querySelectorAll('[required]');
    
    camposRequeridos.forEach(campo => {
        if (!validarCampo(campo)) {
            valido = false;
        }
    });
    
    // Validar honeypot
    const honeypot = document.getElementById('website');
    if (honeypot && honeypot.value.trim() !== '') {
        console.warn('Posible bot detectado');
        valido = false;
    }
    
    // Validar específicamente el checkbox de privacidad
    if (!validarCheckboxPrivacidad()) {
        valido = false;
    }
    
    return valido;
}

function inicializarEnvioFormulario(formulario, textarea, contador) {
    const btnEnviar = formulario.querySelector('[type="submit"]');
    if (!btnEnviar) return;
    
    formulario.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        if (!validarFormularioCompleto(formulario)) {
            mostrarError('Por favor corrige los errores en el formulario.');
            return;
        }
        
        // Deshabilitar botón de envío
        const btnOriginal = btnEnviar.innerHTML;
        btnEnviar.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Enviando...';
        btnEnviar.disabled = true;
        
        try {
            // Crear FormData
            const formData = new FormData(formulario);
            
            // Agregar timestamp para evitar cache
            formData.append('timestamp', Date.now());
            
            // console.log('Enviando datos del formulario...');
            
            // Enviar con timeout
            const controller = new AbortController();
            const timeoutId = setTimeout(() => controller.abort(), 30000); // 30 segundos
            
            const response = await fetch('../Controllers/ContactoController.php', {
                method: 'POST',
                body: formData,
                signal: controller.signal
            });
            
            clearTimeout(timeoutId);
            
            // Obtener la respuesta como texto primero
            const responseText = await response.text();
            // console.log('Respuesta cruda del servidor:', responseText.substring(0, 500));
            
            // Verificar si es JSON válido
            let data;
            try {
                data = JSON.parse(responseText);
            } catch (parseError) {
                console.error('Error parseando JSON:', parseError);
                
                // Si hay HTML en la respuesta, probablemente sea un error de PHP
                if (responseText.includes('<br />') || responseText.includes('<b>')) {
                    throw new Error('El servidor encontró un error. Por favor intenta más tarde.');
                } else {
                    throw new Error('Respuesta del servidor no válida.');
                }
            }
            
            // console.log('Datos parseados del servidor:', data);
            
            // Manejar la respuesta
            if (data.status === 'success') {
                manejarEnvioExitoso(data, formulario, textarea, contador);
            } else {
                throw new Error(data.message || 'Error desconocido del servidor');
            }
            
        } catch (error) {
            console.error('Error completo al enviar formulario:', error);
            
            if (error.name === 'AbortError') {
                manejarErrorEnvio(new Error('La solicitud tardó demasiado. Por favor intenta nuevamente.'));
            } else {
                manejarErrorEnvio(error);
            }
            
        } finally {
            // Restaurar botón
            btnEnviar.innerHTML = btnOriginal;
            btnEnviar.disabled = false;
        }
    });
}

function manejarEnvioExitoso(data, formulario, textarea, contador) {
    // console.log('Mensaje enviado exitosamente:', data);
    
    // Mostrar SweetAlert personalizado
    Swal.fire({
        icon: 'success',
        title: '¡Éxito!',
        html: `
            <div class="text-center py-3">
                <div class="mb-4">
                    <div class="rounded-circle bg-success d-inline-flex align-items-center justify-content-center" 
                         style="width: 80px; height: 80px;">
                        <i class="fas fa-check fa-2x text-white"></i>
                    </div>
                </div>
                <h5 class="text-success mb-3">${data.message}</h5>
                <div class="bg-light p-3 rounded mb-3">
                    <p class="mb-1"><strong>ID de referencia:</strong> #${data.data.id}</p>
                    <p class="mb-0"><strong>Fecha:</strong> ${new Date(data.data.timestamp).toLocaleString()}</p>
                </div>
                <p class="text-muted small">
                    <i class="fas fa-info-circle mr-1"></i>
                    Hemos guardado tu mensaje y te contactaremos pronto.
                </p>
            </div>
        `,
        showConfirmButton: true,
        confirmButtonText: 'Aceptar',
        confirmButtonColor: '#4361ee',
        allowOutsideClick: false,
        allowEscapeKey: false,
        backdrop: 'rgba(0,0,0,0.4)'
    }).then((result) => {
        if (result.isConfirmed) {
            // Resetear formulario completamente
            resetearFormulario(formulario);
            
            // Resetear contador específico
            if (contador) {
                contador.textContent = '0/2000 caracteres';
                contador.classList.remove('caracteres-bajos', 'caracteres-muy-bajos');
            }
            
            // Resetear textarea
            if (textarea) {
                textarea.value = '';
            }
            
            // console.log('Formulario reseteado después de envío exitoso');
            
            // Opcional: Redirigir o mostrar otra acción
            // window.location.href = 'soporte.php?filtro=centro_ayuda';
        }
    });
}

function manejarErrorEnvio(error) {
    console.error('Error detallado:', error);
    
    let mensajeUsuario = 'Error al enviar el mensaje. Por favor intenta nuevamente.';
    
    // Mensajes más específicos según el tipo de error
    if (error.message.includes('429')) {
        mensajeUsuario = 'Has enviado muchos mensajes recientemente. Por favor espera unos minutos.';
    } else if (error.message.includes('conectar') || error.message.includes('conexión')) {
        mensajeUsuario = 'Error de conexión. Verifica tu internet e intenta nuevamente.';
    } else if (error.message.includes('validación')) {
        mensajeUsuario = error.message;
    } else if (error.message.includes('token') || error.message.includes('seguridad')) {
        mensajeUsuario = 'Error de seguridad. Por favor recarga la página e intenta nuevamente.';
    }
    
    Swal.fire({
        icon: 'error',
        title: 'Error',
        html: `
            <div class="text-center">
                <div class="mb-3">
                    <i class="fas fa-exclamation-triangle fa-3x text-danger"></i>
                </div>
                <h5 class="text-danger">${mensajeUsuario}</h5>
                <p class="text-muted small mt-3">
                    <i class="fas fa-lightbulb mr-1"></i>
                    Si el problema persiste, contacta a soporte técnico.
                </p>
            </div>
        `,
        confirmButtonText: 'Reintentar',
        confirmButtonColor: '#dc3545',
        showCancelButton: true,
        cancelButtonText: 'Cancelar',
        cancelButtonColor: '#6c757d',
        backdrop: 'rgba(0,0,0,0.4)'
    }).then((result) => {
        if (result.isConfirmed) {
            // El usuario quiere reintentar
            // console.log('Usuario quiere reintentar el envío');
            
            // Desplazarse al formulario
            const formContacto = document.getElementById('formContacto');
            if (formContacto) {
                formContacto.scrollIntoView({ 
                    behavior: 'smooth',
                    block: 'center'
                });
                
                // Resaltar el formulario temporalmente
                formContacto.style.transition = 'all 0.3s ease';
                formContacto.style.boxShadow = '0 0 0 3px rgba(67, 97, 238, 0.3)';
                setTimeout(() => {
                    formContacto.style.boxShadow = '';
                }, 2000);
            }
        }
    });
}

function resetearFormulario(formulario) {
    if (!formulario) return;
    
    // Resetear valores
    formulario.reset();
    
    // Remover clases de validación
    formulario.querySelectorAll('.is-valid, .is-invalid').forEach(el => {
        el.classList.remove('is-valid', 'is-invalid');
    });
    
    // Remover mensajes de error
    formulario.querySelectorAll('.invalid-feedback').forEach(el => {
        el.remove();
    });
    
    // Resetear específicamente el checkbox
    const checkbox = document.getElementById('privacidad');
    if (checkbox) {
        checkbox.checked = false;
        checkbox.classList.remove('is-invalid');
    }
    
    // Remover información extra de asunto
    const infoExtra = document.getElementById('infoExtraAsunto');
    if (infoExtra) {
        infoExtra.remove();
    }
    
    // Resetear select de asunto
    const asuntoSelect = document.getElementById('asunto');
    if (asuntoSelect) {
        asuntoSelect.selectedIndex = 0;
    }
    
    // console.log('Formulario reseteado completamente');
}

function autocompletarDatosUsuario() {
    // Esta función depende de tu implementación de sesiones
    // Modifícala según tus necesidades
    
    const nombreInput = document.getElementById('nombre');
    const emailInput = document.getElementById('email');
    
    // Ejemplo con localStorage (puedes cambiar por tu sistema)
    const usuarioGuardado = localStorage.getItem('usuario_nexusbuy');
    
    if (usuarioGuardado) {
        try {
            const usuario = JSON.parse(usuarioGuardado);
            if (nombreInput && usuario.nombre) {
                nombreInput.value = usuario.nombre;
            }
            if (emailInput && usuario.email) {
                emailInput.value = usuario.email;
            }
        } catch (e) {
            console.error('Error al parsear usuario guardado:', e);
        }
    }
    
    // Si usas PHP sessions, podrías tener algo como:
    // <?php if (isset($_SESSION['usuario_nombre'])): ?>
    // document.getElementById('nombre').value = '<?php echo $_SESSION['usuario_nombre']; ?>';
    // <?php endif; ?>
}

function inicializarEventosAsunto() {
    const asuntoSelect = document.getElementById('asunto');
    if (!asuntoSelect) return;
    
    asuntoSelect.addEventListener('change', function() {
        const asunto = this.value.toLowerCase();
        let infoExtra = '';
        
        switch(asunto) {
            case 'problema con pedido':
                infoExtra = '<div class="alert alert-info mt-2"><small><i class="fas fa-info-circle mr-1"></i> Por favor incluye el número de pedido para una atención más rápida.</small></div>';
                break;
            case 'devolución o reembolso':
            case 'devolucion':
                const diasDevolucion = window.diasDevolucion || 30;
                infoExtra = `<div class="alert alert-warning mt-2"><small><i class="fas fa-exclamation-triangle mr-1"></i> Recuerda que tienes ${diasDevolucion} días para solicitar devoluciones.</small></div>`;
                break;
            case 'problemas técnicos':
                infoExtra = '<div class="alert alert-info mt-2"><small><i class="fas fa-desktop mr-1"></i> Si es posible, incluye capturas de pantalla del problema.</small></div>';
                break;
        }
        
        let infoContainer = document.getElementById('infoExtraAsunto');
        if (!infoContainer) {
            infoContainer = document.createElement('div');
            infoContainer.id = 'infoExtraAsunto';
            asuntoSelect.parentNode.insertBefore(infoContainer, asuntoSelect.nextSibling);
        }
        infoContainer.innerHTML = infoExtra;
    });
}

// =============================================
// BÚSQUEDA EN FAQ
// =============================================
function inicializarBusquedaFAQ() {
    const buscarAyuda = document.getElementById('buscarAyuda');
    const btnBuscarAyuda = document.getElementById('btnBuscarAyuda');
    
    if (!buscarAyuda) return;
    
    buscarAyuda.addEventListener('input', realizarBusquedaFAQ);
    
    if (btnBuscarAyuda) {
        btnBuscarAyuda.addEventListener('click', realizarBusquedaFAQ);
    }
    
    // También buscar al presionar Enter
    buscarAyuda.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            realizarBusquedaFAQ();
        }
    });
}

function realizarBusquedaFAQ() {
    const buscarInput = document.getElementById('buscarAyuda');
    if (!buscarInput) return;
    
    const searchTerm = buscarInput.value.toLowerCase().trim();
    const faqItems = document.querySelectorAll('.faq-item');
    let visibleCount = 0;
    
    faqItems.forEach(item => {
        const question = item.querySelector('.btn-link span')?.textContent.toLowerCase() || '';
        const answer = item.querySelector('.card-body')?.textContent.toLowerCase() || '';
        
        if (searchTerm.length === 0 || question.includes(searchTerm) || answer.includes(searchTerm)) {
            item.style.display = 'block';
            visibleCount++;
            
            // Si hay término de búsqueda, expandir el FAQ encontrado
            if (searchTerm.length > 0) {
                const collapse = item.querySelector('.collapse');
                if (collapse && !collapse.classList.contains('show')) {
                    const toggleButton = item.querySelector('[data-toggle="collapse"]');
                    if (toggleButton) {
                        toggleButton.click();
                    }
                }
            }
        } else {
            item.style.display = 'none';
            // Colapsar FAQ no relevante
            const collapse = item.querySelector('.collapse');
            if (collapse && collapse.classList.contains('show')) {
                const toggleButton = item.querySelector('[data-toggle="collapse"]');
                if (toggleButton) {
                    toggleButton.click();
                }
            }
        }
    });
    
    mostrarResultadosBusqueda(searchTerm, visibleCount);
}

function mostrarResultadosBusqueda(termino, contador) {
    let resultadosDiv = document.getElementById('resultadosBusqueda');
    const faqAccordion = document.getElementById('faqAccordion');
    
    if (!faqAccordion) return;
    
    if (termino.length > 0) {
        if (!resultadosDiv) {
            resultadosDiv = document.createElement('div');
            resultadosDiv.id = 'resultadosBusqueda';
            resultadosDiv.className = 'alert';
            faqAccordion.parentNode.insertBefore(resultadosDiv, faqAccordion);
        }
        
        if (contador === 0) {
            resultadosDiv.className = 'alert alert-info';
            resultadosDiv.innerHTML = `
                <i class="fas fa-info-circle mr-2"></i>
                No encontramos resultados para "<strong>${termino}</strong>". 
                <a href="soporte.php?filtro=contacto" class="alert-link">Contáctanos</a> para ayuda personalizada.
            `;
        } else {
            resultadosDiv.className = 'alert alert-success';
            resultadosDiv.innerHTML = `
                <i class="fas fa-check-circle mr-2"></i>
                Encontramos <strong>${contador}</strong> resultado(s) para "<strong>${termino}</strong>"
            `;
        }
    } else if (resultadosDiv) {
        resultadosDiv.remove();
        
        // Colapsar todos los FAQs excepto el primero
        const faqItems = document.querySelectorAll('.faq-item');
        faqItems.forEach((item, index) => {
            if (index > 0) {
                const collapse = item.querySelector('.collapse');
                if (collapse && collapse.classList.contains('show')) {
                    const toggleButton = item.querySelector('[data-toggle="collapse"]');
                    if (toggleButton) {
                        toggleButton.click();
                    }
                }
            }
        });
    }
}

// =============================================
// EFECTOS VISUALES
// =============================================
function inicializarEfectosHover() {
    document.querySelectorAll('.card-categoria-ayuda').forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px)';
            this.style.boxShadow = '0 8px 25px rgba(67, 97, 238, 0.15)';
            this.style.transition = 'all 0.3s ease';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = 'none';
        });
    });
    
    // Efecto hover para botones de categoría
    document.querySelectorAll('.list-group-item-action').forEach(item => {
        item.addEventListener('mouseenter', function() {
            this.style.transform = 'translateX(5px)';
            this.style.transition = 'transform 0.2s ease';
        });
        
        item.addEventListener('mouseleave', function() {
            this.style.transform = 'translateX(0)';
        });
    });
}

function inicializarAnimacionesFAQ() {
    document.querySelectorAll('.faq-item .btn-link').forEach(btn => {
        btn.addEventListener('click', function() {
            const icon = this.querySelector('.fa-chevron-down, .fa-chevron-up');
            if (this.getAttribute('aria-expanded') === 'true') {
                icon.classList.remove('fa-chevron-down');
                icon.classList.add('fa-chevron-up');
            } else {
                icon.classList.remove('fa-chevron-up');
                icon.classList.add('fa-chevron-down');
            }
        });
    });
}

// =============================================
// CONTENIDO LEGAL
// =============================================
function formatearContenidoLegal() {
    const cardBody = document.querySelector('.card-body');
    if (!cardBody) return;
    
    // Formatear encabezados
    cardBody.querySelectorAll('h1, h2, h3, h4, h5, h6').forEach(el => {
        el.classList.add('mt-4', 'mb-3', 'text-primary', 'border-bottom', 'pb-2');
    });
    
    // Formatear párrafos
    cardBody.querySelectorAll('p').forEach(el => {
        el.classList.add('mb-3', 'text-justify');
    });
    
    // Formatear listas
    cardBody.querySelectorAll('ul, ol').forEach(el => {
        el.classList.add('ml-4', 'mb-3');
    });
    
    // Formatear elementos de lista
    cardBody.querySelectorAll('li').forEach(el => {
        el.classList.add('mb-2');
    });
    
    // Formatear tablas si existen
    cardBody.querySelectorAll('table').forEach(table => {
        table.classList.add('table', 'table-bordered', 'table-striped', 'mt-3', 'mb-3');
    });
    
    // Formatear bloques de código
    cardBody.querySelectorAll('pre, code').forEach(el => {
        el.classList.add('bg-light', 'p-2', 'rounded', 'd-block', 'mb-3');
    });
}

// =============================================
// FUNCIONES DE UTILIDAD
// =============================================
function mostrarError(mensaje) {
    Swal.fire({
        icon: "error",
        title: "Error",
        text: mensaje,
        confirmButtonText: "Entendido",
        confirmButtonColor: '#4361ee'
    });
}

function mostrarExito(mensaje) {
    Swal.fire({
        icon: "success",
        title: "Éxito",
        text: mensaje,
        confirmButtonText: "Entendido",
        confirmButtonColor: '#4361ee',
        timer: 3000,
        timerProgressBar: true
    });
}

function mostrarInfo(mensaje) {
    Swal.fire({
        icon: "info",
        title: "Información",
        text: mensaje,
        confirmButtonText: "Entendido",
        confirmButtonColor: '#4361ee'
    });
}

function mostrarCargando(mensaje = "Procesando...") {
    Swal.fire({
        title: mensaje,
        allowOutsideClick: false,
        allowEscapeKey: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    return Swal; // Para poder cerrarlo después
}

// =============================================
// FUNCIONALIDAD DE REINTENTO
// =============================================
function inicializarReintentoEnvio() {
    // Esta función se llama desde eventos de error
    // No necesita inicialización específica
    // console.log('Sistema de reintento de envío disponible');
}

// =============================================
// FUNCIONES DE DEPURACIÓN
// =============================================
function debugFormulario() {
    const form = document.getElementById('formContacto');
    if (!form) {
        // console.log('Formulario no encontrado');
        return;
    }
    
    // console.log('=== DEBUG FORMULARIO ===');
    // console.log('Elementos del formulario:');
    
    const elements = form.elements;
    for (let i = 0; i < elements.length; i++) {
        const element = elements[i];
        // console.log(`${element.name || element.id}:`, {
        //     type: element.type,
        //     value: element.value,
        //     required: element.required,
        //     valid: element.checkValidity()
        // });
    }
    
    // console.log('Formulario válido:', form.checkValidity());
    // console.log('=== FIN DEBUG ===');
}

// =============================================
// FUNCIONES GLOBALES (para compatibilidad)
// =============================================

// Función para enviar formulario de contacto vía AJAX (mantenida por compatibilidad)
window.enviarContactoAJAX = function(formData) {
    return new Promise((resolve, reject) => {
        $.ajax({
            url: '../Controllers/ContactoController.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                resolve(response);
            },
            error: function(xhr, status, error) {
                reject(error);
            }
        });
    });
}

// Variable global para días de devolución (puede ser sobrescrita por PHP)
window.diasDevolucion = window.diasDevolucion || 30;

// =============================================
// EVENTOS GLOBALES
// =============================================
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar tooltips de Bootstrap si existen
    if (typeof $ !== 'undefined' && $.fn.tooltip) {
        $('[data-toggle="tooltip"]').tooltip();
    }
    
    // Inicializar popovers de Bootstrap si existen
    if (typeof $ !== 'undefined' && $.fn.popover) {
        $('[data-toggle="popover"]').popover();
    }
    
    // Manejar enlaces suaves
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            const href = this.getAttribute('href');
            if (href === '#') return;
            
            e.preventDefault();
            const target = document.querySelector(href);
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
    
    // Manejar tecla Escape en modales
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            // Cerrar todos los SweetAlerts abiertos
            if (Swal.isVisible()) {
                Swal.close();
            }
        }
    });
    
    // console.log('Sistema de soporte completamente inicializado');
});

// =============================================
// POLYFILLS Y COMPATIBILIDAD
// =============================================
// Polyfill para String.includes (si es necesario)
if (!String.prototype.includes) {
    String.prototype.includes = function(search, start) {
        if (typeof start !== 'number') {
            start = 0;
        }
        if (start + search.length > this.length) {
            return false;
        } else {
            return this.indexOf(search, start) !== -1;
        }
    };
}

// Polyfill para NodeList.forEach (si es necesario)
if (window.NodeList && !NodeList.prototype.forEach) {
    NodeList.prototype.forEach = Array.prototype.forEach;
}

// =============================================
// EXPORTACIÓN DE FUNCIONES PARA DEBUG
// =============================================
// Para facilitar la depuración desde la consola
if (typeof window !== 'undefined') {
    window.debugSoporte = {
        validarFormulario: function() {
            const form = document.getElementById('formContacto');
            if (form) {
                return validarFormularioCompleto(form);
            }
            return false;
        },
        resetearFormulario: function() {
            const form = document.getElementById('formContacto');
            if (form) {
                resetearFormulario(form);
                return true;
            }
            return false;
        },
        testEnvio: function() {
            // Función para probar el envío desde la consola
            // console.log('Probando envío de formulario...');
            const form = document.getElementById('formContacto');
            if (form) {
                form.dispatchEvent(new Event('submit'));
                return true;
            }
            return false;
        },
        verConfiguracion: function() {
            const config = localStorage.getItem('configuracion_sitio');
            if (config) {
                return JSON.parse(config);
            }
            return null;
        }
    };
    
    // console.log('Funciones de debug disponibles en window.debugSoporte');
}