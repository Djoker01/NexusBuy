// ========== FUNCIONES NUEVAS PARA LA BASE DE DATOS ACTUALIZADA ==========

// Cargar estadísticas del usuario
async function cargarEstadisticasUsuario() {
    try {
        const response = await $.ajax({
            url: "../Controllers/TabpanelConfigController.php",
            type: "POST",
            data: { funcion: "obtener_estadisticas" },
            dataType: "json"
        });

        if (response.success && response.estadisticas) {
            mostrarEstadisticas(response.estadisticas);
        }
    } catch (error) {
        console.error("❌ Error cargando estadísticas:", error);
    }
}

function mostrarEstadisticas(estadisticas) {
    // Actualizar la interfaz con las estadísticas
    if (estadisticas.exportaciones_realizadas > 0) {
        $("#stat-exportaciones").text(estadisticas.exportaciones_realizadas);
    }
    
    if (estadisticas.ultima_exportacion) {
        $("#ultima-exportacion").text(
            new Date(estadisticas.ultima_exportacion).toLocaleDateString()
        );
    }
}

// Cargar historial de cambios
async function cargarHistorialCambios() {
    try {
        const response = await $.ajax({
            url: "../Controllers/TabpanelConfigController.php",
            type: "POST",
            data: { funcion: "cargar_historial_cambios" },
            dataType: "json"
        });

        if (response.success && response.historial) {
            mostrarHistorialCambios(response.historial);
        }
    } catch (error) {
        console.error("❌ Error cargando historial:", error);
    }
}

function mostrarHistorialCambios(historial) {
    // Implementar la visualización del historial
    console.log("Historial de cambios:", historial);
}

// Función para generar reporte de configuración
async function generarReporteConfiguracion() {
    Swal.fire({
        title: 'Generando reporte...',
        text: 'Preparando tu reporte de configuración',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    try {
        const response = await $.ajax({
            url: "../Controllers/TabpanelConfigController.php",
            type: "POST",
            data: {
                funcion: "exportar_datos",
                formatos: ["preferencias"],
                formato: "json"
            },
            dataType: "json"
        });

        if (response.success) {
            Swal.close();
            
            // Crear un reporte bonito
            const reporte = crearReporteBonito(response.datos);
            
            Swal.fire({
                title: '✅ Reporte Generado',
                html: reporte,
                width: 800,
                showCloseButton: true,
                showConfirmButton: true,
                confirmButtonText: 'Descargar JSON',
                showCancelButton: true,
                cancelButtonText: 'Cerrar'
            }).then((result) => {
                if (result.isConfirmed) {
                    descargarArchivoExportacion(response.datos, 'json');
                }
            });
        }
    } catch (error) {
        Swal.fire('Error', 'No se pudo generar el reporte: ' + error.message, 'error');
    }
}

function crearReporteBonito(datos) {
    let html = `
        <div class="text-left">
            <h5>📊 Reporte de Configuración</h5>
            <hr>
            <div class="row">
                <div class="col-md-6">
                    <h6><i class="fas fa-bell mr-2"></i>Notificaciones</h6>
                    <ul class="list-unstyled">
    `;
    
    if (datos.configuracion?.notificaciones) {
        Object.entries(datos.configuracion.notificaciones).forEach(([key, value]) => {
            html += `<li>${key}: <strong>${value ? '✅ Activado' : '❌ Desactivado'}</strong></li>`;
        });
    }
    
    html += `
                    </ul>
                </div>
                <div class="col-md-6">
                    <h6><i class="fas fa-eye mr-2"></i>Visualización</h6>
                    <ul class="list-unstyled">
    `;
    
    if (datos.configuracion?.visualizacion) {
        html += `<li>Tema: <strong>${datos.configuracion.visualizacion.tema}</strong></li>`;
        html += `<li>Idioma: <strong>${datos.configuracion.visualizacion.idioma}</strong></li>`;
        html += `<li>Moneda: <strong>${datos.configuracion.visualizacion.moneda}</strong></li>`;
        html += `<li>Densidad: <strong>${datos.configuracion.visualizacion.densidad}</strong></li>`;
    }
    
    html += `
                    </ul>
                </div>
            </div>
            <div class="mt-3">
                <small class="text-muted">
                    <i class="fas fa-info-circle mr-1"></i>
                    Generado el ${new Date().toLocaleDateString()} a las ${new Date().toLocaleTimeString()}
                </small>
            </div>
        </div>
    `;
    
    return html;
}

// Función para restablecer configuración específica
async function restablecerConfiguracionEspecifica(tipo) {
    Swal.fire({
        title: `¿Restablecer configuración de ${tipo}?`,
        text: 'Esta acción volverá a los valores por defecto',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, restablecer',
        cancelButtonText: 'Cancelar'
    }).then(async (result) => {
        if (result.isConfirmed) {
            let datosPorDefecto = {};
            
            switch (tipo) {
                case 'notificaciones':
                    datosPorDefecto = {
                        email: true,
                        pedidos: true,
                        promociones: false,
                        productos: false,
                        resenas: true
                    };
                    break;
                case 'privacidad':
                    datosPorDefecto = {
                        perfil_publico: true,
                        actividad_publica: false,
                        aparecer_busquedas: true,
                        compartir_datos: true
                    };
                    break;
                case 'visualizacion':
                    datosPorDefecto = {
                        tema: 'claro',
                        densidad: 'normal',
                        idioma: 'es',
                        moneda: 'CUP'
                    };
                    break;
            }
            
            try {
                await guardarConfiguracion(tipo, datosPorDefecto);
                Swal.fire('✅ Listo', `Configuración de ${tipo} restablecida`, 'success');
                location.reload();
            } catch (error) {
                Swal.fire('Error', 'No se pudo restablecer la configuración', 'error');
            }
        }
    });
}

// Agregar botones de restablecimiento específico en $(document).ready()
$(document).ready(function() {
    // Botón para restablecer notificaciones
    $('#btn-restablecer-notificaciones').click(function() {
        restablecerConfiguracionEspecifica('notificaciones');
    });
    
    // Botón para restablecer privacidad
    $('#btn-restablecer-privacidad').click(function() {
        restablecerConfiguracionEspecifica('privacidad');
    });
    
    // Botón para restablecer visualización
    $('#btn-restablecer-visualizacion').click(function() {
        restablecerConfiguracionEspecifica('visualizacion');
    });
    
    // Botón para generar reporte
    $('#btn-generar-reporte').click(function() {
        generarReporteConfiguracion();
    });
});

// ============================================================
// FUNCIONES GLOBALES (deben estar ANTES de $(document).ready())
// ============================================================

// ========== FUNCIONES PRINCIPALES DEL PERFIL ==========

// Función PRINCIPAL para cargar el historial
function llenar_historial() {
    // console.log('🔄 EJECUTANDO llenar_historial...');
    
    // Asegurar que el contenedor tenga la clase correcta
    $('#historiales').addClass('timeline-modern');
    
    $('#historiales').html(`
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="sr-only">Cargando...</span>
            </div>
            <p class="text-muted mt-2">Cargando tu actividad...</p>
        </div>
    `);
    
    $.ajax({
        url: '../Controllers/HistorialController.php',
        type: 'POST',
        data: { funcion: 'llenar_historial' },
        dataType: 'json',
        success: function(historiales) {
            // console.log('✅ Datos recibidos:', historiales);
            
            let template = '';
            
            if (!historiales || historiales.length === 0) {
                template = `
                    <div class="text-center py-5">
                        <i class="fas fa-history fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No hay actividad reciente</h5>
                        <p class="text-muted">Tu actividad aparecerá aquí</p>
                    </div>
                `;
            } else {
                historiales.forEach(grupo => {
                    if (grupo && grupo.length > 0) {
                        // Agregar marcador de fecha
                        const primeraFecha = grupo[0].fecha;
                        template += `
                            <div class="timeline-item-modern">
                                <div class="timeline-icon-modern bg-info">
                                    <i class="far fa-calendar"></i>
                                </div>
                                <div class="timeline-content-modern">
                                    <div class="timeline-date-modern">
                                        <i class="fas fa-calendar-day"></i>
                                        <strong>${primeraFecha}</strong>
                                    </div>
                                </div>
                            </div>
                        `;
                        
                        // Agregar actividades
                        grupo.forEach(item => {
                            // Determinar color según tipo de actividad
                            let iconColor = '#4361ee'; // Azul por defecto
                            let badgeClass = 'badge-primary';
                            
                            if (item.tipo_historial) {
                                const tipo = item.tipo_historial.toLowerCase();
                                if (tipo.includes('compra') || tipo.includes('pedido')) {
                                    iconColor = '#28a745';
                                    badgeClass = 'badge-success';
                                } else if (tipo.includes('edicion') || tipo.includes('actualizacion')) {
                                    iconColor = '#ffc107';
                                    badgeClass = 'badge-warning';
                                } else if (tipo.includes('eliminacion') || tipo.includes('borrado')) {
                                    iconColor = '#dc3545';
                                    badgeClass = 'badge-danger';
                                } else if (tipo.includes('registro') || tipo.includes('creacion')) {
                                    iconColor = '#17a2b8';
                                    badgeClass = 'badge-info';
                                }
                            }
                            
                            // Usar iconos de la base de datos o por defecto
                            const iconoTipo = item.th_icono || '<i class="fas fa-circle"></i>';
                            const iconoModulo = item.m_icono || '<i class="fas fa-folder"></i>';
                            
                            template += `
                                <div class="timeline-item-modern">
                                    <div class="timeline-icon-modern" style="background: ${iconColor};">
                                        ${iconoTipo}
                                    </div>
                                    <div class="timeline-content-modern">
                                        <div class="timeline-date-modern">
                                            <i class="far fa-clock"></i> ${item.hora}
                                            <span class="badge ${badgeClass} ml-2">${item.tipo_historial}</span>
                                        </div>
                                        <h6 class="timeline-title-modern">
                                            ${iconoModulo}
                                            <strong>${item.modulo}</strong>
                                        </h6>
                                        <p>${item.descripcion}</p>
                                        <small class="text-muted">
                                            <i class="fas fa-code mr-1"></i>${item.accion}
                                        </small>
                                    </div>
                                </div>
                            `;
                        });
                    }
                });
            }
            
            $('#historiales').html(template);
            // console.log('✅ Historial renderizado');
            
        },
        error: function(xhr, status, error) {
            console.error('❌ Error:', status, error);
            $('#historiales').html(`
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    Error cargando el historial. Intenta recargar la página.
                </div>
            `);
        }
    });
}

// Función para obtener datos del usuario
function obtener_datos() {
    let funcion = "obtener_datos";
    $.post("../Controllers/UsuarioController.php", { funcion }, (response) => {
    //   console.log(response);
        try {
            let usuario = JSON.parse(response);
            $("#username").text(usuario.username);
            $("#tipo_usuario").text(usuario.tipo_usuario);
            $("#nombres_completos").text(usuario.nombres + " " + usuario.apellidos);
            $("#avatar_perfil").attr("src", "../Util/Img/Users/" + usuario.avatar);
            $("#dni").text(usuario.dni);
            $("#email").text(usuario.email);
            $("#telefono").text(usuario.telefono);
            $("#fecha").text(usuario.fecha_nacimiento);
            $("#genero").text(usuario.genero);
        } catch (e) {
            console.error("Error parseando datos:", e);
        }
    });
}

// Función para llenar provincias
function llenar_provincia() {
    let funcion = "llenar_provincia";
    // console.log("Iniciando Obtener Provincias");
    $.post("../Controllers/ProvinciaController.php", { funcion }, (response) => {
        // console.log("obteniendo las provincias: ", response);
        let provincias = JSON.parse(response);

        let template = "";
        provincias.forEach((provincia) => {
            template += `<option value="${provincia.id}">${provincia.nombre}</option>`;
        });
        $("#provincia").html(template);
        $("#provincia").val("").trigger("change");
    });
}

// Función para llenar direcciones
function llenar_direcciones() {
    // console.log('🔄 Llenando direcciones...');
    
    let funcion = "llenar_direcciones";
    
    // Mostrar estado de carga
    $("#direcciones").html(`
        <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Cargando...</span>
            </div>
            <p class="text-muted mt-2">Cargando direcciones...</p>
        </div>
    `);
    
    $.ajax({
        url: "../Controllers/UsuarioMunicipioController.php",
        type: "POST",
        data: { funcion },
        dataType: "json",
        success: function(direcciones) {
            // console.log('✅ Direcciones recibidas:', direcciones);
            
            let contador = 0;
            let template = "";
            
            if (!direcciones || direcciones.length === 0) {
                template = `
                    <div class="text-center py-4">
                        <i class="fas fa-map-marker-alt fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted mb-2">No hay direcciones guardadas</h5>
                        <p class="text-muted mb-4">Agrega direcciones para envíos más rápidos</p>
                        <button class="btn btn-primary-modern" data-bs-toggle="modal" data-bs-target="#modal_direcciones">
                            <i class="fas fa-plus mr-2"></i>Agregar primera dirección
                        </button>
                    </div>
                `;
            } else {
                direcciones.forEach((direccion) => {
                    contador++;
                    
                    // Determinar si es dirección principal
                    const esPrincipal = direccion.es_principal === '1' || direccion.es_principal === 1;
                    
                    template += `
                        <div class="address-card ${esPrincipal ? 'address-card-primary' : ''}">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    ${esPrincipal ? `
                                        <span class="badge badge-primary mb-2">
                                            <i class="fas fa-star mr-1"></i>Principal
                                        </span>
                                    ` : ''}
                                    
                                    <h6 class="mb-1">
                                        <strong>Dirección ${contador}</strong>
                                        ${direccion.alias ? ` - ${direccion.alias}` : ''}
                                    </h6>
                                    
                                    <p class="mb-1">
                                        <i class="fas fa-map-marker-alt text-primary mr-2"></i>
                                        ${direccion.direccion}
                                    </p>
                                    
                                    <p class="text-muted mb-0 small">
                                        <b><i class="fas fa-city mr-1"></i> Municipio:</b> ${direccion.municipio || 'No especificado'}
                                    </p>
                                    
                                    <p class="text-muted mb-0 small">
                                        <b><i class="fas fa-globe-americas mr-1"></i> Provincia:</b> ${direccion.provincia || 'No especificada'}
                                    </p>
                                    
                                    ${direccion.instrucciones ? `
                                        <p class="text-muted mb-0 small mt-1">
                                            <b><i class="fas fa-info-circle mr-1"></i> Instrucciones:</b> ${direccion.instrucciones}
                                        </p>
                                    ` : ''}
                                    
                                    <div class="mt-2">
                                        ${esPrincipal ? '' : `
                                            <button data-dir-id="${direccion.id}" 
                                                    type="button" 
                                                    class="btn btn-sm btn-outline-primary set-principal-btn">
                                                <i class="fas fa-star mr-1"></i>Marcar como principal
                                            </button>
                                        `}
                                    </div>
                                </div>
                                
                                <div class="btn-group-vertical">
                                    ${!esPrincipal ? `
                                        <button data-dir-id="${direccion.id}" 
                                                type="button" 
                                                class="btn btn-outline-warning btn-sm editar_direccion mb-1">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    ` : ''}
                                    
                                    <button data-dir-id="${direccion.id}" 
                                            type="button" 
                                            class="btn btn-outline-danger btn-sm eliminar_direccion">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    `;
                });
            }
            
            $("#direcciones").html(template);
            // console.log('✅ Direcciones renderizadas');
        },
        error: function(xhr, status, error) {
            console.error('❌ Error cargando direcciones:', error);
            $("#direcciones").html(`
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    Error cargando las direcciones. Intenta recargar la página.
                </div>
            `);
        }
    });
}

// Función para marcar dirección como principal
function marcarDireccionPrincipal(idDireccion) {
    console.log(`Marcando dirección ${idDireccion} como principal...`);
    
    Swal.fire({
        title: '¿Marcar como dirección principal?',
        text: 'Esta dirección será usada por defecto para envíos',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#4361ee',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, marcar como principal',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.post("../Controllers/UsuarioMunicipioController.php", {
                funcion: "marcar_direccion_principal",
                id: idDireccion
            }, (response) => {
                if (response == "success") {
                    Swal.fire({
                        icon: 'success',
                        title: 'Dirección principal actualizada',
                        text: 'La dirección ha sido marcada como principal',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        llenar_direcciones();
                        llenar_historial();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'No se pudo marcar como dirección principal'
                    });
                }
            });
        }
    });
}

// Función para editar dirección
// Función para editar dirección (COMPLETA)
function editarDireccion(idDireccion) {
    console.log(`✏️ Editando dirección ${idDireccion}...`);
    
    // Mostrar modal de edición
    $('#modal_editar_direccion').modal('show');
    
    // Mostrar loading
    $('#modal_editar_direccion .modal-body').html(`
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Cargando...</span>
            </div>
            <p class="text-muted mt-2">Cargando información de la dirección...</p>
        </div>
    `);
    
    // Obtener datos de la dirección
    $.ajax({
        url: "../Controllers/UsuarioMunicipioController.php",
        type: "POST",
        data: { 
            funcion: "obtener_direccion",
            id: idDireccion
        },
        dataType: "json",
        success: function(response) {
            console.log('✅ Datos de dirección:', response);
            
            if (response && response.id) {
                // Cargar formulario de edición
                cargarFormularioEdicion(response);
            } else {
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: "No se pudieron cargar los datos de la dirección",
                });
                $('#modal_editar_direccion').modal('hide');
            }
        },
        error: function(xhr, status, error) {
            console.error('❌ Error:', error);
            Swal.fire({
                icon: "error",
                title: "Error",
                text: "Error al cargar la dirección",
            });
            $('#modal_editar_direccion').modal('hide');
        }
    });
}

// Función para cargar formulario de edición
function cargarFormularioEdicion(direccion) {
    const formulario = `
        <form id="form-editar-direccion">
            <input type="hidden" name="id" value="${direccion.id}">
            
            <!-- Alias -->
            <div class="form-group-modern">
                <label class="form-label-modern" for="alias_editar">Alias (ej: Casa, Trabajo)</label>
                <input type="text" name="alias" class="form-control-modern" 
                       id="alias_editar" value="${direccion.alias || ''}"
                       placeholder="Nombre para identificar esta dirección">
            </div>
            
            <!-- Provincia y Municipio -->
            <div class="form-group-modern">
                <label class="form-label-modern" for="provincia_editar">Provincia *</label>
                <select class="form-control-modern select2" id="provincia_editar" required>
                    <option value="">Selecciona una provincia</option>
                </select>
            </div>
            
            <div class="form-group-modern">
                <label class="form-label-modern" for="municipio_editar">Municipio *</label>
                <select class="form-control-modern select2" name="id_municipio" 
                        id="municipio_editar" required>
                    <option value="">Cargando municipios...</option>
                </select>
            </div>
            
            <!-- Dirección -->
            <div class="form-group-modern">
                <label class="form-label-modern" for="direccion_editar">Dirección completa *</label>
                <textarea name="direccion" class="form-control-modern" 
                          id="direccion_editar" rows="3" required
                          placeholder="Calle, número, edificio, apartamento...">${direccion.direccion || ''}</textarea>
            </div>
            
            <!-- Instrucciones adicionales -->
            <div class="form-group-modern">
                <label class="form-label-modern" for="instrucciones_editar">Instrucciones de entrega</label>
                <textarea name="instrucciones" class="form-control-modern" 
                          id="instrucciones_editar" rows="2"
                          placeholder="Ej: Timbre 3A, dejar con conserje...">${direccion.instrucciones || ''}</textarea>
            </div>
            
            <!-- Marcar como principal -->
            <div class="form-check mb-3">
                <input type="checkbox" class="form-check-input" 
                       id="es_principal_editar" name="es_principal" value="1"
                       ${direccion.es_principal == '1' ? 'checked' : ''}>
                <label class="form-check-label" for="es_principal_editar">
                    Marcar como dirección principal
                </label>
            </div>
            
            <div class="alert alert-info">
                <small>
                    <i class="fas fa-info-circle mr-1"></i>
                    Los campos marcados con * son obligatorios
                </small>
            </div>
        </form>
    `;
    
    $('#modal_editar_direccion .modal-body').html(formulario);
    
    // Inicializar Select2
    $('#provincia_editar, #municipio_editar').select2({
        placeholder: 'Seleccionar...',
        language: {
            noResults: function() { return "No hay resultado"; },
            searching: function() { return "Buscando..."; }
        }
    });
    
    // Cargar provincias
    cargarProvinciasParaEdicion(direccion);
}

// Función para cargar provincias en el formulario de edición
function cargarProvinciasParaEdicion(direccion) {
    $.post("../Controllers/ProvinciaController.php", { 
        funcion: "llenar_provincia" 
    }, (response) => {
        const provincias = JSON.parse(response);
        let template = '<option value="">Selecciona una provincia</option>';
        
        provincias.forEach((provincia) => {
            template += `<option value="${provincia.id}">${provincia.nombre}</option>`;
        });
        
        $('#provincia_editar').html(template);
        
        // Cargar municipios de la provincia actual
        cargarMunicipiosParaEdicion(direccion);
    });
}

// Función para cargar municipios en el formulario de edición
function cargarMunicipiosParaEdicion(direccion) {
    console.log('🔄 Cargando municipios para edición');
    
    $.ajax({
        url: "../Controllers/UsuarioMunicipioController.php",
        type: "POST",
        data: { 
            funcion: "obtener_direccion",
            id: direccion.id
        },
        dataType: "json",
        success: function(dirCompleta) {
            if (dirCompleta && dirCompleta.id_provincia && dirCompleta.id_municipio) {
                // ¡Ahora tenemos la provincia directamente!
                
                // 1. Cargar municipios de esa provincia
                $.post("../Controllers/MunicipioController.php", { 
                    funcion: "llenar_municipio",
                    id_provincia: dirCompleta.id_provincia
                }, (response) => {
                    const municipios = JSON.parse(response);
                    let template = '<option value="">Selecciona un municipio</option>';
                    
                    municipios.forEach((municipio) => {
                        const selected = municipio.id == dirCompleta.id_municipio ? 'selected' : '';
                        template += `<option value="${municipio.id}" ${selected}>${municipio.nombre}</option>`;
                    });
                    
                    $('#municipio_editar').html(template);
                    
                    // 2. Seleccionar la provincia
                    $('#provincia_editar').val(dirCompleta.id_provincia).trigger('change');
                });
            }
        }
    });
}

// Función auxiliar para obtener provincia de un municipio (necesitarías crear este endpoint)
function obtenerProvinciaDeMunicipio(idMunicipio) {
    // Esta función debería hacer una consulta para obtener la provincia del municipio
    // Por ahora, retornamos null - necesitarás implementar esto
    return null;
}

// ========== EVENTOS PARA DIRECCIONES ==========

// Evento para formulario de dirección (MEJORADO)
$("#form-direccion").submit((e) => {
    e.preventDefault();
    
    // Validar campos
    const id_municipio = $("#municipio").val();
    const direccion = $("#direccion").val();
    
    if (!id_municipio || !direccion.trim()) {
        Swal.fire({
            icon: "warning",
            title: "Campos incompletos",
            text: "Completa todos los campos obligatorios",
        });
        return;
    }
    
    let funcion = "crear_direccion";
    
    // Mostrar loading
    const submitBtn = $("#form-direccion button[type='submit']");
    const originalText = submitBtn.html();
    submitBtn.prop('disabled', true).html(`
        <i class="fas fa-spinner fa-spin mr-2"></i>
        Guardando...
    `);
    
    // Obtener datos adicionales si existen
    const datos = {
        funcion: funcion,
        id_municipio: id_municipio,
        direccion: direccion,
        alias: $("#alias_direccion").val() || null,
        instrucciones: $("#instrucciones_direccion").val() || null,
        es_principal: $("#es_principal").is(":checked") ? 1 : 0
    };
    
    $.ajax({
        url: "../Controllers/UsuarioMunicipioController.php",
        type: "POST",
        data: datos,
        success: function(response) {
            submitBtn.prop('disabled', false).html(originalText);
            
            if (response == "success") {
                Swal.fire({
                    position: "center",
                    icon: "success",
                    title: "Dirección guardada",
                    text: "Se ha registrado tu dirección correctamente",
                    showConfirmButton: false,
                    timer: 1500,
                }).then(function () {
                    // Limpiar formulario
                    $("#form-direccion").trigger("reset");
                    $("#provincia, #municipio").val("").trigger("change");
                    
                    // Cerrar modal
                    $("#modal_direcciones").modal('hide');
                    
                    // Actualizar interfaz
                    llenar_direcciones();
                    llenar_historial();
                });
            } else {
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: "No se pudo guardar la dirección: " + response,
                });
            }
        },
        error: function() {
            submitBtn.prop('disabled', false).html(originalText);
            Swal.fire({
                icon: "error",
                title: "Error de conexión",
                text: "No se pudo conectar con el servidor",
            });
        }
    });
});

// Evento para marcar como principal
$(document).on("click", ".set-principal-btn", function (e) {
    e.preventDefault();
    let id = $(this).data("dir-id");
    marcarDireccionPrincipal(id);
});

// Evento para editar dirección
$(document).on("click", ".editar_direccion", function (e) {
    e.preventDefault();
    let id = $(this).data("dir-id");
    
    // Usar la función simplificada o completa
    if (typeof editarDireccion === 'function') {
        editarDireccion(id);
    } else {
        editarDireccionSimplificada(id);
    }
});

// Evento para eliminar dirección (MEJORADO)
$(document).on("click", ".eliminar_direccion", function (e) {
    e.preventDefault();
    let id = $(this).data("dir-id");

    Swal.fire({
        title: "¿Eliminar esta dirección?",
        text: "Esta acción eliminará permanentemente esta dirección",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#dc3545",
        cancelButtonColor: "#6c757d",
        confirmButtonText: "Sí, eliminar",
        cancelButtonText: "Cancelar",
        reverseButtons: true,
    }).then((result) => {
        if (result.isConfirmed) {
            let funcion = "eliminar_direccion";
            
            $.post("../Controllers/UsuarioMunicipioController.php", { 
                funcion: funcion, 
                id: id 
            }, (response) => {
                if (response == "success") {
                    Swal.fire({
                        icon: "success",
                        title: "Dirección eliminada",
                        text: "La dirección fue eliminada correctamente",
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        llenar_direcciones();
                        llenar_historial();
                    });
                } else if (response == "error") {
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: "No se pudo eliminar la dirección",
                    });
                } else {
                    Swal.fire({
                        icon: "error",
                        title: "Error del sistema",
                        text: "Tenemos problemas en el sistema",
                    });
                }
            });
        }
    });
});

// ========== FUNCIONES DE CONFIGURACIÓN ==========

// Función para cargar configuración
async function cargarConfiguracion() {
    console.log("Cargando configuración del usuario...");

    try {
        const response = await $.post("../Controllers/TabpanelConfigController.php", {
            funcion: "cargar_configuracion",
        });

        const data = typeof response === "string" ? JSON.parse(response) : response;

        if (data.error === "no_sesion") {
            window.location.href = "login.php";
            return;
        }

        if (data.success && data.configuraciones) {
            aplicarConfiguracion(data.configuraciones);
        } else {
            cargarConfiguracionPorDefecto();
        }
    } catch (error) {
        console.error("Error cargando configuración:", error);
        cargarConfiguracionPorDefecto();
    }
}

function aplicarConfiguracion(configuraciones) {
    console.log("Aplicando configuración:", configuraciones);

    // Aplicar configuración de notificaciones
    if (configuraciones.notificaciones) {
        $("#notificacion-email").prop("checked", configuraciones.notificaciones.email || true);
        $("#notificacion-pedidos").prop("checked", configuraciones.notificaciones.pedidos || true);
        $("#notificacion-promociones").prop("checked", configuraciones.notificaciones.promociones || false);
        $("#notificacion-productos").prop("checked", configuraciones.notificaciones.productos || false);
        $("#notificacion-resenas").prop("checked", configuraciones.notificaciones.resenas || true);
    }

    // Aplicar configuración de privacidad
    if (configuraciones.privacidad) {
        $("#privacidad-perfil").prop("checked", configuraciones.privacidad.perfil_publico || true);
        $("#privacidad-actividad").prop("checked", configuraciones.privacidad.actividad_publica || false);
        $("#privacidad-busqueda").prop("checked", configuraciones.privacidad.aparecer_busquedas || true);
        $("#privacidad-datos").prop("checked", configuraciones.privacidad.compartir_datos || true);
    }

    // Aplicar configuración de visualización
    if (configuraciones.visualizacion) {
        $("#tema-interface").val(configuraciones.visualizacion.tema || "claro");
        $("#densidad-interface").val(configuraciones.visualizacion.densidad || "normal");
        $("#idioma-interface").val(configuraciones.visualizacion.idioma || "es");
        $("#moneda-interface").val(configuraciones.visualizacion.moneda || "CUP");
    }

    aplicarTema(configuraciones.visualizacion?.tema || "claro");
}

function cargarConfiguracionPorDefecto() {
    console.log("Cargando configuración por defecto");

    $("#notificacion-email").prop("checked", true);
    $("#notificacion-pedidos").prop("checked", true);
    $("#notificacion-promociones").prop("checked", false);
    $("#notificacion-productos").prop("checked", false);
    $("#notificacion-resenas").prop("checked", true);

    $("#privacidad-perfil").prop("checked", true);
    $("#privacidad-actividad").prop("checked", false);
    $("#privacidad-busqueda").prop("checked", true);
    $("#privacidad-datos").prop("checked", true);

    $("#tema-interface").val("claro");
    $("#densidad-interface").val("normal");
    $("#idioma-interface").val("es");
    $("#moneda-interface").val("CUP");
}

function obtenerDatosNotificaciones() {
    return {
        email: $("#notificacion-email").is(":checked"),
        pedidos: $("#notificacion-pedidos").is(":checked"),
        promociones: $("#notificacion-promociones").is(":checked"),
        productos: $("#notificacion-productos").is(":checked"),
        resenas: $("#notificacion-resenas").is(":checked"),
    };
}

function obtenerDatosPrivacidad() {
    return {
        perfil_publico: $("#privacidad-perfil").is(":checked"),
        actividad_publica: $("#privacidad-actividad").is(":checked"),
        aparecer_busquedas: $("#privacidad-busqueda").is(":checked"),
        compartir_datos: $("#privacidad-datos").is(":checked"),
    };
}

function obtenerDatosVisualizacion() {
    return {
        tema: $("#tema-interface").val(),
        densidad: $("#densidad-interface").val(),
        idioma: $("#idioma-interface").val(),
        moneda: $("#moneda-interface").val(),
    };
}

async function guardarConfiguracion(tipo, datos) {
    console.log(`Guardando configuración ${tipo}:`, datos);

    try {
        const response = await $.post("../Controllers/TabpanelConfigController.php", {
            funcion: "guardar_configuracion",
            tipo_configuracion: tipo,
            datos_configuracion: JSON.stringify(datos),
        });

        const data = typeof response === "string" ? JSON.parse(response) : response;

        if (data.success) {
            mostrarExito("Configuración guardada correctamente");
            if (tipo === "visualizacion") {
                aplicarTema(datos.tema);
                aplicarDensidad(datos.densidad);
            }
        } else {
            throw new Error(data.error || "Error al guardar");
        }
    } catch (error) {
        console.error("Error guardando configuración:", error);
        mostrarError("Error al guardar la configuración");
    }
}

function aplicarTema(tema) {
    $("body").removeClass("tema-claro tema-oscuro");
    if (tema === "oscuro") {
        $("body").addClass("tema-oscuro");
    } else {
        $("body").addClass("tema-claro");
    }
    localStorage.setItem("tema-interface", tema);
}

function aplicarDensidad(densidad) {
    $("body").removeClass("densidad-comoda densidad-normal densidad-compacta");
    $("body").addClass(`densidad-${densidad}`);
    localStorage.setItem("densidad-interface", densidad);
}

// ========== FUNCIONES DE MONEDAS ==========

async function obtener_monedas() {
    console.log("Iniciando obtener_monedas");
    try {
        const response = await $.post("../Controllers/MonedaController.php", {
            funcion: "obtener_monedas",
        });

        const data = typeof response === "string" ? JSON.parse(response) : response;

        if (data.success) {
            llenarSelectMonedas(data.monedas);
        } else {
            console.error("Error cargando monedas:", data.error);
        }
    } catch (error) {
        console.error("Error:", error);
    }
}

function llenarSelectMonedas(monedas) {
    const select = $("#moneda-interface");
    select.empty();

    monedas.forEach((moneda) => {
        select.append(new Option(`${moneda.nombre} (${moneda.codigo})`, moneda.codigo));
    });

    const monedaGuardada = localStorage.getItem("moneda-seleccionada") || "CUP";
    select.val(monedaGuardada);
}

async function actualizarPreciosMoneda(codigoMoneda) {
    try {
        $(".precio-producto").html('<i class="fas fa-spinner fa-spin"></i>');
        const response = await $.post("../Controllers/MonedaController.php", {
            funcion: "obtener_tasa_cambio",
            moneda: codigoMoneda,
        });

        const data = typeof response === "string" ? JSON.parse(response) : response;

        if (data.success) {
            actualizarTodosLosPrecios(data.tasa_cambio, data.moneda);
        } else {
            console.error("Error obteniendo tasa de cambio:", data.error);
        }
    } catch (error) {
        console.error("Error actualizando precios:", error);
    }
}

function actualizarTodosLosPrecios(tasaCambio, monedaInfo) {
    console.log("Actualizando precios con tasa:", tasaCambio, "moneda:", monedaInfo);
}

// ========== FUNCIONES DE ACCIONES AVANZADAS ==========

async function exportarDatos() {
    console.log("Iniciando exportación de datos...");
    $("#modalExportarDatos").modal("show");
}

async function confirmarExportacion() {
    const formatos = [];
    if ($("#exportar-perfil").is(":checked")) formatos.push("perfil");
    if ($("#exportar-pedidos").is(":checked")) formatos.push("pedidos");
    if ($("#exportar-resenas").is(":checked")) formatos.push("resenas");
    if ($("#exportar-direcciones").is(":checked")) formatos.push("direcciones");
    if ($("#exportar-preferencias").is(":checked")) formatos.push("preferencias");

    const formato = $("#formato-exportacion").val();

    if (formatos.length === 0) {
        mostrarError("Selecciona al menos un tipo de dato para exportar");
        return;
    }

    try {
        const response = await $.post("../Controllers/TabpanelConfigController.php", {
            funcion: "exportar_datos",
            formatos: formatos,
            formato: formato,
        });

        const data = typeof response === "string" ? JSON.parse(response) : response;

        if (data.success) {
            descargarArchivoExportacion(data.datos, data.formato);
            $("#modalExportarDatos").modal("hide");
            mostrarExito("Datos exportados correctamente");
        } else {
            throw new Error(data.error || "Error en la exportación");
        }
    } catch (error) {
        console.error("Error exportando datos:", error);
        mostrarError("Error al exportar los datos");
    }
}

function descargarArchivoExportacion(datos, formato) {
    let contenido, tipoMime, extension;

    switch (formato) {
        case "json":
            contenido = JSON.stringify(datos, null, 2);
            tipoMime = "application/json";
            extension = "json";
            break;
        case "csv":
            contenido = convertirJSONaCSV(datos);
            tipoMime = "text/csv";
            extension = "csv";
            break;
        case "pdf":
            contenido = JSON.stringify(datos);
            tipoMime = "application/pdf";
            extension = "pdf";
            break;
        default:
            contenido = JSON.stringify(datos);
            tipoMime = "application/octet-stream";
            extension = "txt";
    }

    const blob = new Blob([contenido], { type: tipoMime });
    const url = URL.createObjectURL(blob);
    const a = document.createElement("a");
    a.href = url;
    a.download = `mis_datos_nexusbuy_${new Date().toISOString().split("T")[0]}.${extension}`;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
}

function convertirJSONaCSV(datos) {
    let csv = "";

    if (datos.perfil) {
        csv += "PERFIL\n";
        csv += "Campo,Valor\n";
        Object.entries(datos.perfil).forEach(([key, value]) => {
            csv += `"${key}","${value}"\n`;
        });
        csv += "\n";
    }

    if (datos.pedidos) {
        csv += "PEDIDOS\n";
        csv += "Número,Fecha,Total,Estado\n";
        datos.pedidos.forEach((pedido) => {
            csv += `"${pedido.numero_orden}","${pedido.fecha_creacion}","${pedido.total}","${pedido.estado}"\n`;
        });
        csv += "\n";
    }

    return csv;
}

function limpiarHistorial() {
    Swal.fire({
        title: "¿Limpiar historial local?",
        text: "Esto eliminará datos temporales del navegador, pero no afectará tu cuenta.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#ffc107",
        cancelButtonColor: "#6c757d",
        confirmButtonText: "Sí, limpiar",
        cancelButtonText: "Cancelar",
    }).then((result) => {
        if (result.isConfirmed) {
            localStorage.clear();
            sessionStorage.clear();
            mostrarExito("Historial local limpiado correctamente");
        }
    });
}

function restablecerPreferencias() {
    Swal.fire({
        title: "¿Restablecer preferencias?",
        text: "Todas tus configuraciones volverán a los valores por defecto.",
        icon: "question",
        showCancelButton: true,
        confirmButtonColor: "#17a2b8",
        cancelButtonColor: "#6c757d",
        confirmButtonText: "Sí, restablecer",
        cancelButtonText: "Cancelar",
    }).then((result) => {
        if (result.isConfirmed) {
            localStorage.removeItem("tema-interface");
            localStorage.removeItem("densidad-interface");
            location.reload();
        }
    });
}

function eliminarCuenta() {
    Swal.fire({
        title: "¿Eliminar cuenta permanentemente?",
        html: `
            <div class="text-left">
                <p class="text-danger"><strong>Esta acción no se puede deshacer</strong></p>
                <p>Se eliminarán todos tus datos:</p>
                <ul class="text-left">
                    <li>Información de perfil</li>
                    <li>Historial de pedidos</li>
                    <li>Reseñas y calificaciones</li>
                    <li>Direcciones guardadas</li>
                    <li>Preferencias y configuración</li>
                </ul>
                <p>Para confirmar, escribe <strong>ELIMINAR</strong> en el campo below:</p>
                <input type="text" id="confirmacion-eliminar" class="form-control" placeholder="ELIMINAR">
            </div>
        `,
        icon: "error",
        showCancelButton: true,
        confirmButtonColor: "#dc3545",
        cancelButtonColor: "#6c757d",
        confirmButtonText: "Eliminar cuenta permanentemente",
        cancelButtonText: "Cancelar",
        preConfirm: () => {
            const confirmacion = $("#confirmacion-eliminar").val();
            if (confirmacion !== "ELIMINAR") {
                Swal.showValidationMessage("Debes escribir ELIMINAR para confirmar");
            }
            return { confirmacion: confirmacion };
        },
    }).then(async (result) => {
        if (result.isConfirmed) {
            try {
                const response = await $.post("../Controllers/TabpanelConfigController.php", {
                    funcion: "eliminar_cuenta",
                    confirmacion: result.value.confirmacion,
                });

                const data = typeof response === "string" ? JSON.parse(response) : response;

                if (data.success) {
                    Swal.fire({
                        icon: "success",
                        title: "Cuenta eliminada",
                        text: "Tu cuenta ha sido eliminada correctamente",
                        confirmButtonText: "Entendido",
                    }).then(() => {
                        window.location.href = "../index.php";
                    });
                } else {
                    throw new Error(data.error || "Error al eliminar la cuenta");
                }
            } catch (error) {
                console.error("Error eliminando cuenta:", error);
                mostrarError("Error al eliminar la cuenta");
            }
        }
    });
}

function mostrarExito(mensaje) {
    Swal.fire({
        icon: "success",
        title: "Éxito",
        text: mensaje,
        timer: 3000,
        showConfirmButton: false,
    });
}

function mostrarError(mensaje) {
    Swal.fire({
        icon: "error",
        title: "Error",
        text: mensaje,
        confirmButtonText: "Entendido",
    });
}

// ============================================================
// $(document).ready() - SOLO EVENTOS Y LLAMADAS INICIALES
// ============================================================

$(document).ready(function () {
    // console.log('📄 Documento listo, iniciando carga...');
    
    // Inicializar componentes
    bsCustomFileInput.init();
    
    // ========== INICIALIZAR PESTAÑAS CORRECTAMENTE ==========
    // Activar la primera pestaña
    $('.nav-tabs-modern a[data-toggle="tab"]:first').tab('show');
    
    // Manejar clic en las pestañas
    $('.nav-tabs-modern a[data-toggle="tab"]').click(function (e) {
        e.preventDefault();
        $(this).tab('show');
        
        // Recargar contenido específico
        switch ($(this).attr('href')) {
            case '#timeline':
                // Solo llenar si está vacío
                if ($('#historiales .timeline-item-modern').length === 0) {
                    llenar_historial();
                }
                break;
            case '#configuracion':
                // Cargar configuración si no se ha hecho
                if (!$('#notificacion-email').is(':checked')) {
                    cargarConfiguracion();
                }
                break;
            case '#tienda':
                // Cargar datos de la tienda
                cargarDatosTienda();
                break;
        }
    });
    
    // Cargar datos iniciales
    llenar_historial();
    obtener_datos();
    llenar_provincia();
    llenar_direcciones();
    cargarConfiguracion();
    obtener_monedas();
    
    // Configurar Select2
    $('#provincia').select2({
        placeholder: 'Seleccione una provincia',
        language: {
            noResults: function () {
                return "No hay resultado";
            },
            searching: function () {
                return "Buscando...";
            }
        }
    });
    
    $('#municipio').select2({
        placeholder: 'Seleccione un municipio',
        language: {
            noResults: function () {
                return "No hay resultado";
            },
            searching: function () {
                return "Buscando...";
            }
        }
    });
    
    // ========== EVENTOS ==========
    
    // Evento para cambio de provincia
    $("#provincia").change(function () {
        let id_provincia = $("#provincia").val();
        let funcion = "llenar_municipio";
        $.post("../Controllers/MunicipioController.php", { funcion, id_provincia }, (response) => {
            let municipios = JSON.parse(response);
            // console.log(response);
            let template = "";
            municipios.forEach((municipio) => {
                template += `<option value="${municipio.id}">${municipio.nombre}</option>`;
            });
            $("#municipio").html(template);
            $("#municipio").val("").trigger("change");
        });
    });
    
    // Evento para formulario de dirección
    $("#form-direccion").submit((e) => {
        e.preventDefault();
        let funcion = "crear_direccion";
        let id_municipio = $("#municipio").val();
        let direccion = $("#direccion").val();
        $.post("../Controllers/UsuarioMunicipioController.php", { id_municipio, direccion, funcion }, (response) => {
            if (response == "success") {
                Swal.fire({
                    position: "center",
                    icon: "success",
                    title: "Se ha registrado su dirección",
                    showConfirmButton: false,
                    timer: 500,
                }).then(function () {
                    $("#form-direccion").trigger("reset");
                    $("#provincia").val("").trigger("change");
                    llenar_historial();
                    llenar_direcciones();
                });
            } else {
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: "Hubo conflicto al crear su dirección, comuniquese con el área de sistemas",
                });
            }
        });
    });
    
    // Evento para eliminar dirección
    $(document).on("click", ".eliminar_direccion", function (e) {
        e.preventDefault();
        let id = $(this).data("dir-id");

        const swalWithBootstrapButtons = Swal.mixin({
            customClass: {
                confirmButton: "btn btn-success m-3",
                cancelButton: "btn btn-danger",
            },
            buttonsStyling: false,
        });

        swalWithBootstrapButtons.fire({
            title: "¿Desea borrar esta dirección?",
            text: "Esta acción no se puede deshacer",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Sí, borrar",
            cancelButtonText: "Cancelar",
            reverseButtons: true,
        }).then((result) => {
            if (result.isConfirmed) {
                let funcion = "eliminar_direccion";
                $.post("../Controllers/UsuarioMunicipioController.php", { funcion, id }, (response) => {
                    if (response == "success") {
                        swalWithBootstrapButtons.fire("¡Borrado!", "La dirección fue eliminada", "success");
                        llenar_direcciones();
                        llenar_historial();
                    } else if (response == "error") {
                        swalWithBootstrapButtons.fire("Error", "No se pudo borrar la dirección", "error");
                    } else {
                        swalWithBootstrapButtons.fire("Error del sistema", "Tenemos problemas en el sistema", "error");
                    }
                });
            }
        });
    });
    
    // ========== EVENTO PARA EDITAR DATOS (VERSIÓN MEJORADA) ==========
    $(document).on("click", ".editar_datos", function(e) {
        e.preventDefault();
        console.log("🟡 INICIANDO CARGA DE DATOS PARA EDICIÓN");
        
        // Mostrar modal inmediatamente
        $('#modal_datos').modal('show');
        
        // Mostrar spinner mientras carga
        $('#modal_datos .modal-body').html(`
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                    <span class="visually-hidden">Cargando...</span>
                </div>
                <p class="text-muted mt-3">Cargando información del usuario...</p>
            </div>
        `);
        
        // Llamar al controlador para obtener datos
        $.ajax({
            url: "../Controllers/UsuarioController.php",
            type: "POST",
            data: { 
                funcion: "obtener_datos"
            },
            dataType: "json",
            success: function(response) {
                // console.log("✅ RESPUESTA DEL SERVIDOR:", response);
                
                // Verificar errores
                if (response && response.error === 'no_sesion') {
                    window.location.href = "login.php";
                    return;
                }
                
                if (!response || typeof response !== 'object') {
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: "Respuesta inválida del servidor",
                    });
                    $('#modal_datos').modal('hide');
                    return;
                }
                
                // Restaurar formulario con datos
                const formularioCompleto = `
                    <form id="form-datos" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group-modern">
                                    <label class="form-label-modern" for="nombres_mod">Nombres *</label>
                                    <input type="text" name="nombres_mod" class="form-control-modern" id="nombres_mod" placeholder="Ingresa tus nombres" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group-modern">
                                    <label class="form-label-modern" for="apellidos_mod">Apellidos *</label>
                                    <input type="text" name="apellidos_mod" class="form-control-modern" id="apellidos_mod" placeholder="Ingresa tus apellidos" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group-modern">
                                    <label class="form-label-modern" for="dni_mod">DNI *</label>
                                    <input type="text" name="dni_mod" class="form-control-modern" id="dni_mod" placeholder="Ingresa tu DNI" required maxlength="11">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group-modern">
                                    <label class="form-label-modern" for="telefono_mod">Teléfono *</label>
                                    <input type="tel" name="telefono_mod" class="form-control-modern" id="telefono_mod" placeholder="Ingresa tu teléfono" required maxlength="20">
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group-modern">
                            <label class="form-label-modern" for="email_mod">Email *</label>
                            <input type="email" name="email_mod" class="form-control-modern" id="email_mod" placeholder="Ingresa tu email" required>
                        </div>
                        
                        <!-- CAMPOS NUEVOS: FECHA DE NACIMIENTO Y GÉNERO -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group-modern">
                                    <label class="form-label-modern" for="fecha_nacimiento_mod">Fecha de Nacimiento</label>
                                    <input type="date" name="fecha_nacimiento_mod" class="form-control-modern" id="fecha_nacimiento_mod" max="${new Date().toISOString().split('T')[0]}">
                                    <small class="text-muted">Formato: AAAA-MM-DD</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group-modern">
                                    <label class="form-label-modern" for="genero_mod">Género</label>
                                    <select name="genero_mod" class="form-control-modern" id="genero_mod">
                                        <option value="">Seleccionar</option>
                                        <option value="Masculino">Masculino</option>
                                        <option value="Femenino">Femenino</option>
                                        <option value="Otro">Otro</option>
                                        <option value="Prefiero no decir">Prefiero no decir</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group-modern">
                            <label class="form-label-modern">Foto de Perfil</label>
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" name="avatar_mod" id="avatar_mod" accept="image/*">
                                <label class="custom-file-label" for="avatar_mod">Seleccionar imagen...</label>
                            </div>
                            <small class="text-muted">Formatos: JPG, PNG, GIF. Tamaño máximo: 2MB</small>
                            <div id="avatar-preview" class="mt-2 text-center" style="display: none;">
                                <img id="avatar-preview-img" src="" alt="Vista previa" style="max-width: 150px; max-height: 150px; border-radius: 50%;">
                            </div>
                        </div>
                        
                        <div class="alert alert-info mt-3">
                            <small>
                                <i class="fas fa-info-circle mr-1"></i>
                                Los campos marcados con * son obligatorios. Los demás son opcionales.
                            </small>
                        </div>
                    </form>
                `;
                
                // Insertar formulario
                $('#modal_datos .modal-body').html(formularioCompleto);
                
                // Llenar campos con datos
                $("#nombres_mod").val(response.nombres || '');
                $("#apellidos_mod").val(response.apellidos || '');
                $("#dni_mod").val(response.dni || '');
                $("#email_mod").val(response.email || '');
                $("#telefono_mod").val(response.telefono || '');
                
                // Llenar campos nuevos
                if (response.fecha_nacimiento && response.fecha_nacimiento !== '0000-00-00') {
                    $("#fecha_nacimiento_mod").val(response.fecha_nacimiento);
                }
                
                if (response.genero) {
                    $("#genero_mod").val(response.genero);
                }
                
                // Inicializar custom file input
                if (typeof bsCustomFileInput !== 'undefined') {
                    bsCustomFileInput.init();
                }
                
                // Vista previa del avatar
                $('#avatar_mod').on('change', function() {
                    const file = this.files[0];
                    const preview = $('#avatar-preview');
                    const previewImg = $('#avatar-preview-img');
                    
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            previewImg.attr('src', e.target.result);
                            preview.show();
                        }
                        reader.readAsDataURL(file);
                    } else {
                        preview.hide();
                    }
                });
                
            },
            error: function(xhr, status, error) {
                console.error("❌ ERROR AJAX:", error);
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: "No se pudieron cargar los datos del usuario",
                });
                $('#modal_datos').modal('hide');
            }
        });
    });
    
    // Eventos para formularios de configuración
    $("#form-notificaciones").on("submit", function (e) {
        e.preventDefault();
        guardarConfiguracion("notificaciones", obtenerDatosNotificaciones());
    });

    $("#form-privacidad").on("submit", function (e) {
        e.preventDefault();
        guardarConfiguracion("privacidad", obtenerDatosPrivacidad());
    });

    $("#form-visualizacion").on("submit", function (e) {
        e.preventDefault();
        guardarConfiguracion("visualizacion", obtenerDatosVisualizacion());
    });
    
    // Evento para cambio de moneda
    $("#moneda-interface").change(function () {
        const monedaSeleccionada = $(this).val();
        localStorage.setItem("moneda-seleccionada", monedaSeleccionada);
        actualizarPreciosMoneda(monedaSeleccionada);
    });
    
    // ========== VALIDACIÓN DE FORMULARIOS ==========
    
    // Validación personalizada para campos
    jQuery.validator.addMethod("soloLetras", function (value, element) {
        if (value.trim() === '') return true;
        return /^[A-Za-záéíóúÁÉÍÓÚñÑ\s]+$/.test(value);
    }, "*Este campo solo permite letras y espacios");

    jQuery.validator.addMethod("fechaValida", function(value, element) {
        if (!value) return true; // Opcional
        return /^\d{4}-\d{2}-\d{2}$/.test(value);
    }, "*Formato de fecha inválido (AAAA-MM-DD)");
    
    jQuery.validator.addMethod('filesize', function(value, element, param) {
        if (element.files.length === 0) return true;
        return element.files[0].size <= param;
    }, 'El archivo es demasiado grande');

    // ========== MANEJAR ENVÍO DEL FORMULARIO DE DATOS ==========
$(document).on('submit', '#form-datos', function(e) {
    e.preventDefault();
    console.log("🟢 EVENTO SUBMIT DISPARADO");
    enviarFormularioDatos();
    return false;
});

// También capturar clic directo en el botón (por si el submit no funciona)
$(document).on('click', '#form-datos button[type="submit"]', function(e) {
    e.preventDefault();
    console.log("🔴 CLIC DIRECTO EN BOTÓN");
    enviarFormularioDatos();
    return false;
});

// Función para enviar el formulario
function enviarFormularioDatos() {
    console.log("🚀 EJECUTANDO enviarFormularioDatos()");
    
    const form = $('#form-datos');
    
    // Verificar que el formulario exista
    if (form.length === 0) {
        console.error("❌ Formulario no encontrado");
        return;
    }
    
    // Validar
    if (!form.valid()) {
        console.log("❌ Validación falló");
        return;
    }
    
    console.log("✅ Validación aprobada");
    
    // Mostrar loading
    const submitBtn = form.find('button[type="submit"]');
    const originalText = submitBtn.html();
    submitBtn.prop('disabled', true).html(`
        <i class="fas fa-spinner fa-spin mr-2"></i>
        Guardando...
    `);
    
    // Crear FormData
    let formData = new FormData(form[0]);
    formData.append("funcion", "editar_datos");
    
    // Mostrar datos para depuración
    console.log("📦 Datos a enviar:");
    for (let pair of formData.entries()) {
        console.log(pair[0] + ': ' + pair[1]);
    }
    
    // Enviar
    $.ajax({
        type: "POST",
        url: "../Controllers/UsuarioController.php",
        data: formData,
        cache: false,
        processData: false,
        contentType: false,
        success: function (response) {
            console.log("📨 Respuesta del servidor:", response);
            submitBtn.prop('disabled', false).html(originalText);
            
            if (response == "success") {
                Swal.fire({
                    icon: "success",
                    title: "¡Éxito!",
                    text: "Datos actualizados correctamente",
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => {
                    $('#modal_datos').modal('hide');
                    obtener_datos();
                    llenar_historial();
                });
            } else if (response == "sin_cambios") {
                Swal.fire({
                    icon: "info",
                    title: "Sin cambios",
                    text: "No realizaste ningún cambio",
                });
            } else if (response == "error_campos_vacios") {
                Swal.fire({
                    icon: "warning",
                    title: "Campos incompletos",
                    text: "Completa todos los campos obligatorios",
                });
            } else {
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: "Error: " + response,
                });
            }
        },
        error: function(xhr, status, error) {
            console.error("❌ Error AJAX:", {
                status: status,
                error: error,
                responseText: xhr.responseText
            });
            submitBtn.prop('disabled', false).html(originalText);
            Swal.fire({
                icon: "error",
                title: "Error del sistema",
                text: "No se pudo conectar: " + error,
            });
        }
    });
}

// Inicializar validación cuando se interactúa con el formulario
$(document).on('focus', '#form-datos input, #form-datos select', function() {
    if (!$('#form-datos').data('validator')) {
        $('#form-datos').validate({
            rules: {
                nombres_mod: { 
                    required: true, 
                    minlength: 2,
                    maxlength: 100,
                    soloLetras: true
                },
                apellidos_mod: { 
                    required: true, 
                    minlength: 2,
                    maxlength: 100,
                    soloLetras: true
                },
                dni_mod: { 
                    required: true, 
                    digits: true, 
                    minlength: 11, 
                    maxlength: 11 
                },
                email_mod: { 
                    required: true, 
                    email: true,
                    maxlength: 255
                },
                telefono_mod: { 
                    required: true, 
                    digits: true, 
                    minlength: 8, 
                    maxlength: 20 
                },
                fecha_nacimiento_mod: {
                    fechaValida: true
                },
                avatar_mod: {
                    accept: "image/*",
                    filesize: 2097152
                }
            },
            messages: {
                nombres_mod: { 
                    required: "*Este campo es obligatorio",
                    minlength: "*Mínimo 2 caracteres",
                    maxlength: "*Máximo 100 caracteres",
                    soloLetras: "*Solo se permiten letras"
                },
                apellidos_mod: { 
                    required: "*Este campo es obligatorio",
                    minlength: "*Mínimo 2 caracteres",
                    maxlength: "*Máximo 100 caracteres",
                    soloLetras: "*Solo se permiten letras"
                },
                dni_mod: { 
                    required: "*Este campo es obligatorio",
                    minlength: "*El DNI debe tener 11 dígitos",
                    maxlength: "*El DNI debe tener 11 dígitos",
                    digits: "*Solo se permiten números"
                },
                email_mod: { 
                    required: "*Este campo es obligatorio",
                    email: "*Ingresa un email válido",
                    maxlength: "*Máximo 255 caracteres"
                },
                telefono_mod: { 
                    required: "*Este campo es obligatorio",
                    minlength: "*Mínimo 8 dígitos",
                    maxlength: "*Máximo 20 dígitos",
                    digits: "*Solo se permiten números"
                },
                fecha_nacimiento_mod: {
                    fechaValida: "*Formato de fecha inválido (AAAA-MM-DD)"
                },
                avatar_mod: {
                    accept: "*Solo se permiten imágenes (JPG, PNG, GIF)",
                    filesize: "*La imagen no debe superar 2MB"
                }
            },
            errorElement: "span",
            errorPlacement: function (error, element) {
                error.addClass("invalid-feedback d-block");
                element.closest(".form-group-modern").append(error);
            },
            highlight: function (element, errorClass, validClass) {
                $(element).addClass("is-invalid");
                $(element).removeClass("is-valid");
            },
            unhighlight: function (element, errorClass, validClass) {
                $(element).removeClass("is-invalid");
                $(element).addClass("is-valid");
            }
        });
        console.log("✅ jQuery Validate inicializado para #form-datos");
    }
});

// Validación para cambiar contraseña
$("#form-contra").validate({
    rules: {
        pass_old: { required: true, minlength: 8, maxlength: 20 },
        pass_new: { required: true, minlength: 8, maxlength: 20 },
        pass_repeat: { required: true, equalTo: "#pass_new" },
    },
    messages: {
        pass_old: { 
            required: "*Este campo es obligatorio",
            minlength: "*El password debe ser mínimo 8 carácteres",
            maxlength: "*El password debe ser máximo 20 carácteres"
        },
        pass_new: { 
            required: "*Este campo es obligatorio",
            minlength: "*El password debe ser mínimo 8 carácteres",
            maxlength: "*El password debe ser máximo 20 carácteres"
        },
        pass_repeat: { 
            required: "*Este campo es obligatorio",
            equalTo: "*No concide con el password ingresado"
        },
    },
    errorElement: "span",
    errorPlacement: function (error, element) {
        error.addClass("invalid-feedback");
        element.closest(".form-group").append(error);
    },
    highlight: function (element, errorClass, validClass) {
        $(element).addClass("is-invalid");
        $(element).removeClass("is-valid");
    },
    unhighlight: function (element, errorClass, validClass) {
        $(element).removeClass("is-invalid");
        $(element).addClass("is-valid");
    },
    submitHandler: function () {
        let funcion = "cambiar_contra";
        let pass_old = $("#pass_old").val();
        let pass_new = $("#pass_new").val();
        $.post("../Controllers/UsuarioController.php", { funcion, pass_old, pass_new }, (response) => {
            if (response == "success") {
                Swal.fire({
                    position: "center",
                    icon: "success",
                    title: "Se ha cambiado su password",
                    showConfirmButton: false,
                    timer: 1000,
                }).then(function () {
                    $("#form-contra").trigger("reset");
                    $("#modal_contra").modal('hide');
                    llenar_historial();
                });
            } else if (response == "error") {
                Swal.fire({
                    icon: "warning",
                    title: "Password incorrecto",
                    text: "Ingrese su contraseña actual para poder cambiarla!",
                });
            } else {
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: "Hubo conflicto al cambiar su password, comuniquese con el área de sistemas",
                });
            }
        });
    }
});

// Función para probar manualmente
function probarEnvioFormulario() {
    console.log("🧪 EJECUTANDO probarEnvioFormulario()");
    
    if ($('#form-datos').length === 0) {
        console.error("❌ El formulario #form-datos no existe");
        Swal.fire({
            icon: "warning",
            title: "Formulario no encontrado",
            text: "Primero abre el modal de edición",
        });
        return;
    }
    
    console.log("✅ Formulario encontrado, enviando...");
    enviarFormularioDatos();
}
    
    // Validación para cambiar contraseña
    $("#form-contra").validate({
        rules: {
            pass_old: { required: true, minlength: 8, maxlength: 20 },
            pass_new: { required: true, minlength: 8, maxlength: 20 },
            pass_repeat: { required: true, equalTo: "#pass_new" },
        },
        messages: {
            pass_old: { 
                required: "*Este campo es obligatorio",
                minlength: "*El password debe ser mínimo 8 carácteres",
                maxlength: "*El password debe ser máximo 20 carácteres"
            },
            pass_new: { 
                required: "*Este campo es obligatorio",
                minlength: "*El password debe ser mínimo 8 carácteres",
                maxlength: "*El password debe ser máximo 20 carácteres"
            },
            pass_repeat: { 
                required: "*Este campo es obligatorio",
                equalTo: "*No concide con el password ingresado"
            },
        },
        errorElement: "span",
        errorPlacement: function (error, element) {
            error.addClass("invalid-feedback");
            element.closest(".form-group").append(error);
        },
        highlight: function (element, errorClass, validClass) {
            $(element).addClass("is-invalid");
            $(element).removeClass("is-valid");
        },
        unhighlight: function (element, errorClass, validClass) {
            $(element).removeClass("is-invalid");
            $(element).addClass("is-valid");
        },
        submitHandler: function () {
            let funcion = "cambiar_contra";
            let pass_old = $("#pass_old").val();
            let pass_new = $("#pass_new").val();
            $.post("../Controllers/UsuarioController.php", { funcion, pass_old, pass_new }, (response) => {
                if (response == "success") {
                    Swal.fire({
                        position: "center",
                        icon: "success",
                        title: "Se ha cambiado su password",
                        showConfirmButton: false,
                        timer: 1000,
                    }).then(function () {
                        $("#form-contra").trigger("reset");
                        llenar_historial();
                    });
                } else if (response == "error") {
                    Swal.fire({
                        icon: "warning",
                        title: "Password incorrecto",
                        text: "Ingrese su contraseña actual para poder cambiarla!",
                    });
                } else {
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: "Hubo conflicto al cambiar su password, comuniquese con el área de sistemas",
                    });
                }
            });
        }
    });
});

// Función para probar manualmente el envío del formulario
function probarEnvioFormulario() {
    console.log("🧪 PROBANDO ENVÍO MANUAL DEL FORMULARIO");
    
    if ($('#form-datos').length === 0) {
        console.error("❌ El formulario no existe");
        return;
    }
    
    // Simular clic en el botón de envío
    $('#form-datos button[type="submit"]').click();
}
$(document).on('click', '#modal_datos .btn-primary-modern', function(e) {
    e.preventDefault();
    console.log("🔥 BOTÓN GUARDAR CLICKEADO - ENVIANDO");
    
    // Validar campos básicos
    const campos = ['#nombres_mod', '#apellidos_mod', '#dni_mod', '#email_mod', '#telefono_mod'];
    let valido = true;
    
    campos.forEach(campo => {
        if (!$(campo).val()) {
            valido = false;
            $(campo).addClass('is-invalid');
        }
    });
    
    if (!valido) {
        alert('Completa todos los campos obligatorios');
        return;
    }
    
    // Enviar datos
    let formData = new FormData();
    formData.append('funcion', 'editar_datos');
    formData.append('nombres_mod', $('#nombres_mod').val());
    formData.append('apellidos_mod', $('#apellidos_mod').val());
    formData.append('dni_mod', $('#dni_mod').val());
    formData.append('email_mod', $('#email_mod').val());
    formData.append('telefono_mod', $('#telefono_mod').val());
    formData.append('fecha_nacimiento_mod', $('#fecha_nacimiento_mod').val());
    formData.append('genero_mod', $('#genero_mod').val());
    
    // Si hay archivo de avatar
    if ($('#avatar_mod')[0].files[0]) {
        formData.append('avatar_mod', $('#avatar_mod')[0].files[0]);
    }
    
    $.ajax({
        type: "POST",
        url: "../Controllers/UsuarioController.php",
        data: formData,
        cache: false,
        processData: false,
        contentType: false,
        success: function (response) {
            console.log("Respuesta:", response);
            if (response == "success") {
                
                $('#modal_datos').modal('hide');
                // location.reload();
            } else {
                alert('Error: ' + response);
            }
        },
        error: function() {
            alert('Error de conexión');
        }
    });
});