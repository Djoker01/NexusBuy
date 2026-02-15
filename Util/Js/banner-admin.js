// =============================================
// ADMINISTRACIÓN DE BANNERS
// =============================================
$(document).ready(function() {
    // Inicializar según la página
    if (window.location.pathname.includes('banners.php')) {
        initBannersList();
    } else if (window.location.pathname.includes('banner_form.php')) {
        initBannerForm();
    }
});

// =============================================
// LISTADO DE BANNERS
// =============================================
function initBannersList() {
    let paginaActual = 1;
    let filtros = {};

    cargarBanners();

    // Evento filtrar
    $('#btnFiltrar').click(function() {
        paginaActual = 1;
        aplicarFiltros();
        cargarBanners();
    });

    // Enter en búsqueda
    $('#busqueda').keypress(function(e) {
        if (e.which === 13) {
            paginaActual = 1;
            aplicarFiltros();
            cargarBanners();
        }
    });

    function aplicarFiltros() {
        filtros = {
            posicion: $('#filtroPosicion').val(),
            estado: $('#filtroEstado').val(),
            busqueda: $('#busqueda').val()
        };
    }

    function cargarBanners() {
        const data = {
            funcion: 'listar_banners',
            pagina: paginaActual,
            por_pagina: 20,
            ...filtros
        };

        $.post('../../Controllers/AdminBannerController.php', data, function(response) {
            if (response.success) {
                renderizarTabla(response.data);
                renderizarPaginacion(response.data);
            } else {
                toastr.error(response.error || 'Error al cargar banners');
            }
        }).fail(function() {
            toastr.error('Error de conexión al servidor');
        });
    }

    function renderizarTabla(data) {
        const tbody = $('#tablaBanners');
        
        if (!data.banners || data.banners.length === 0) {
            tbody.html('<tr><td colspan="8" class="text-center py-4">No hay banners</td></tr>');
            return;
        }

        let html = '';
        data.banners.forEach((banner, index) => {
            const posicionTexto = {
                'slider_principal': 'Slider Principal',
                'lateral_derecho': 'Lateral Derecho',
                'superior': 'Superior',
                'inferior': 'Inferior',
                'popup': 'Popup'
            }[banner.posicion] || banner.posicion;

            const fechaInicio = new Date(banner.fecha_inicio).toLocaleDateString();
            const fechaFin = new Date(banner.fecha_fin).toLocaleDateString();
            
            const imagenSrc = banner.imagen.includes('http') 
                ? banner.imagen 
                : '../../Util/Img/Banners/' + banner.imagen;

            html += `
                <tr data-id="${banner.id}">
                    <td>${(data.pagina_actual - 1) * 20 + index + 1}</td>
                    <td>
                        <img src="${imagenSrc}" class="banner-thumb" alt="${banner.titulo}"
                             onclick="previewBanner('${imagenSrc}', '${banner.titulo}')"
                             style="cursor: pointer;">
                    </td>
                    <td>
                        <strong>${banner.titulo}</strong>
                        <small class="d-block text-muted">${banner.usuario_nombre || ''}</small>
                    </td>
                    <td>
                        <span class="badge-posicion">
                            <i class="fas fa-map-pin mr-1"></i>${posicionTexto}
                        </span>
                    </td>
                    <td>
                        <small>${fechaInicio} - ${fechaFin}</small>
                    </td>
                    <td>
                        <input type="number" class="form-control form-control-sm orden-input" 
                               value="${banner.orden}" data-id="${banner.id}"
                               onchange="cambiarOrden(this)">
                    </td>
                    <td>
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" 
                                   id="estado_${banner.id}" ${banner.estado == 1 ? 'checked' : ''}
                                   onchange="cambiarEstado(${banner.id}, this.checked)">
                            <label class="custom-control-label" for="estado_${banner.id}"></label>
                        </div>
                    </td>
                    <td>
                        <div class="btn-group">
                            <button class="btn btn-sm btn-info" onclick="editarBanner(${banner.id})">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-danger" onclick="eliminarBanner(${banner.id}, '${banner.titulo}')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        });

        tbody.html(html);
        $('#infoPaginacion').text(
            `Mostrando ${(data.pagina_actual - 1) * 20 + 1} a ${Math.min(data.pagina_actual * 20, data.total)} de ${data.total} banners`
        );
    }

    function renderizarPaginacion(data) {
        const $paginacion = $('#paginacion');
        if (data.paginas <= 1) {
            $paginacion.empty();
            return;
        }

        let html = '';
        
        // Botón anterior
        html += `<li class="page-item ${data.pagina_actual == 1 ? 'disabled' : ''}">
                    <a class="page-link" href="#" data-pagina="${data.pagina_actual - 1}">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                </li>`;

        // Páginas
        for (let i = 1; i <= data.paginas; i++) {
            if (i === 1 || i === data.paginas || (i >= data.pagina_actual - 2 && i <= data.pagina_actual + 2)) {
                html += `<li class="page-item ${i === data.pagina_actual ? 'active' : ''}">
                            <a class="page-link" href="#" data-pagina="${i}">${i}</a>
                        </li>`;
            } else if (i === data.pagina_actual - 3 || i === data.pagina_actual + 3) {
                html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
            }
        }

        // Botón siguiente
        html += `<li class="page-item ${data.pagina_actual == data.paginas ? 'disabled' : ''}">
                    <a class="page-link" href="#" data-pagina="${data.pagina_actual + 1}">
                        <i class="fas fa-chevron-right"></i>
                    </a>
                </li>`;

        $paginacion.html(html);

        // Eventos de paginación
        $paginacion.find('.page-link[data-pagina]').click(function(e) {
            e.preventDefault();
            const pagina = $(this).data('pagina');
            if (pagina && pagina !== data.pagina_actual) {
                paginaActual = pagina;
                cargarBanners();
            }
        });
    }
}

// =============================================
// ACCIONES DEL LISTADO
// =============================================
function cambiarEstado(id, estado) {
    Swal.fire({
        title: '¿Cambiar estado?',
        text: `El banner será ${estado ? 'activado' : 'desactivado'}`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, cambiar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.post('../../Controllers/AdminBannerController.php', {
                funcion: 'cambiar_estado',
                id: id,
                estado: estado ? 1 : 0
            }, function(response) {
                if (response.success) {
                    toastr.success(response.message);
                } else {
                    toastr.error(response.error || 'Error al cambiar estado');
                    $(`#estado_${id}`).prop('checked', !estado);
                }
            }).fail(function() {
                toastr.error('Error de conexión');
                $(`#estado_${id}`).prop('checked', !estado);
            });
        } else {
            $(`#estado_${id}`).prop('checked', !estado);
        }
    });
}

function cambiarOrden(input) {
    const id = $(input).data('id');
    const nuevoOrden = parseInt($(input).val());

    $.post('../../Controllers/AdminBannerController.php', {
        funcion: 'reordenar_banners',
        ordenes: JSON.stringify([{id: id, orden: nuevoOrden}])
    }, function(response) {
        if (response.success) {
            toastr.success('Orden actualizado');
        } else {
            toastr.error(response.error || 'Error al actualizar orden');
            $(input).val($(input).data('original') || 0);
        }
    }).fail(function() {
        toastr.error('Error de conexión');
    });
}

function editarBanner(id) {
    window.location.href = 'banner_form.php?id=' + id;
}

function eliminarBanner(id, titulo) {
    Swal.fire({
        title: '¿Eliminar banner?',
        html: `Estás a punto de eliminar: <strong>${titulo}</strong><br>Esta acción no se puede deshacer.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, eliminar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.post('../../Controllers/AdminBannerController.php', {
                funcion: 'eliminar_banner',
                id: id
            }, function(response) {
                if (response.success) {
                    toastr.success(response.message);
                    $(`tr[data-id="${id}"]`).fadeOut(400, function() {
                        $(this).remove();
                        if ($('#tablaBanners tr').length === 1) {
                            location.reload();
                        }
                    });
                } else {
                    toastr.error(response.error || 'Error al eliminar');
                }
            }).fail(function() {
                toastr.error('Error de conexión');
            });
        }
    });
}

function previewBanner(imagen, titulo) {
    $('#previewContent').html(`
        <img src="${imagen}" class="img-fluid" alt="${titulo}">
        <h5 class="mt-3">${titulo}</h5>
    `);
    $('#previewModal').modal('show');
}

// =============================================
// FORMULARIO DE BANNER
// =============================================
function initBannerForm() {
    // Cargar datos si es edición
    if (bannerId > 0) {
        cargarBanner(bannerId);
    }

    // Manejar subida de imagen
    $('#imagen').change(function(e) {
        const file = e.target.files[0];
        if (file) {
            subirImagen(file);
        }
    });

    // Manejar envío del formulario
    $('#formBanner').submit(function(e) {
        e.preventDefault();
        guardarBanner();
    });

    // Validar fechas
    $('#fecha_inicio, #fecha_fin').change(function() {
        validarFechas();
    });

    // Previsualizar icono
    $('#icono_boton').on('input', function() {
        const icono = $(this).val();
        $('#previewIcono').html(`<i class="fas ${icono} mr-1"></i>`);
    });
}

function cargarBanner(id) {
    $.post('../../Controllers/AdminBannerController.php', {
        funcion: 'obtener_banner',
        id: id
    }, function(response) {
        if (response.success && response.banner) {
            const b = response.banner;
            
            $('#bannerId').val(b.id);
            $('#titulo').val(b.titulo);
            $('#descripcion').val(b.descripcion);
            $('#posicion').val(b.posicion);
            $('#orden').val(b.orden);
            $('#fecha_inicio').val(b.fecha_inicio.split(' ')[0]);
            $('#fecha_fin').val(b.fecha_fin.split(' ')[0]);
            $('#url').val(b.url);
            $('#texto_boton').val(b.texto_boton);
            $('#icono_boton').val(b.icono_boton);
            $('#estado').prop('checked', b.estado == 1);
            
            // Actualizar imagen
            if (b.imagen && b.imagen !== 'default_banner.jpg') {
                $('#previewImagen').attr('src', '../Util/Img/Banners/' + b.imagen);
                $('#imagen_nombre').val(b.imagen);
            }
            
            validarFechas();
        } else {
            toastr.error('Error al cargar el banner');
        }
    }).fail(function() {
        toastr.error('Error de conexión');
    });
}

function subirImagen(file) {
    const formData = new FormData();
    formData.append('imagen', file);
    formData.append('funcion', 'upload_imagen');

    $('#previewImagen').attr('src', '../../Util/Img/loading.gif');

    $.ajax({
        url: '../../Controllers/AdminBannerController.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            if (response.success) {
                $('#previewImagen').attr('src', '../../Util/Img/Banners/' + response.nombre_archivo);
                $('#imagen_nombre').val(response.nombre_archivo);
                $('.custom-file-label').text(file.name);
                toastr.success('Imagen subida correctamente');
            } else {
                $('#previewImagen').attr('src', '../../Util/Img/Banners/default_banner.jpg');
                toastr.error(response.error || 'Error al subir imagen');
            }
        },
        error: function() {
            $('#previewImagen').attr('src', '../Util/Img/Banners/default_banner.jpg');
            toastr.error('Error al subir imagen');
        }
    });
}

function guardarBanner() {
    // Validar fechas
    if (!validarFechas()) return;

    const formData = {
        funcion: bannerId > 0 ? 'actualizar_banner' : 'crear_banner',
        id: bannerId,
        titulo: $('#titulo').val(),
        descripcion: $('#descripcion').val(),
        posicion: $('#posicion').val(),
        orden: $('#orden').val(),
        fecha_inicio: $('#fecha_inicio').val(),
        fecha_fin: $('#fecha_fin').val(),
        url: $('#url').val(),
        texto_boton: $('#texto_boton').val(),
        icono_boton: $('#icono_boton').val(),
        estado: $('#estado').is(':checked') ? 1 : 0,
        imagen_nombre: $('#imagen_nombre').val()
    };

    // Validar campos requeridos
    if (!formData.titulo || !formData.posicion || !formData.fecha_inicio || !formData.fecha_fin) {
        toastr.warning('Por favor completa todos los campos requeridos');
        return;
    }

    $.post('../../Controllers/AdminBannerController.php', formData, function(response) {
        if (response.success) {
            toastr.success(response.message);
            setTimeout(() => {
                window.location.href = 'banners.php';
            }, 1500);
        } else {
            toastr.error(response.error || 'Error al guardar banner');
        }
    }).fail(function() {
        toastr.error('Error de conexión al servidor');
    });
}

function validarFechas() {
    const inicio = $('#fecha_inicio').val();
    const fin = $('#fecha_fin').val();

    if (inicio && fin) {
        if (new Date(inicio) > new Date(fin)) {
            $('#fecha_fin').addClass('is-invalid');
            toastr.warning('La fecha de fin debe ser posterior a la fecha de inicio');
            return false;
        } else {
            $('#fecha_fin').removeClass('is-invalid');
        }
    }
    return true;
}