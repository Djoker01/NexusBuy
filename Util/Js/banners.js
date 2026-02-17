/**
 * Util/Js/banners.js
 * Funcionalidades JavaScript para el módulo de banners
 * 
 * Dependencias: jQuery, SweetAlert2, DataTables, Chart.js
 */

// Usar IIFE para evitar contaminar el ámbito global
(function($) {
    'use strict';
    
    // =============================================
    // CONFIGURACIÓN GLOBAL
    // =============================================
    
    const BannersApp = {
        // Configuración
        config: {
            datatablesSpanishUrl: '../../../Util/Js/json/datatables-spanish.json',
            toastrTimeout: 3000,
            maxFileSize: 2 * 1024 * 1024, // 2MB
            allowedImageTypes: ['image/jpeg', 'image/png', 'image/gif', 'image/webp']
        },
        
        // =============================================
        // INICIALIZACIÓN
        // =============================================
        
        /**
         * Inicializa todas las funcionalidades según la página
         */
        init: function() {
            this.initDataTables();
            this.initFileInputs();
            this.initCancelButtons();
            this.initRenewButtons();
            this.initDateRangePicker();
            this.initCharts();
            this.initImagePreview();
            this.initPriceCalculator();
            this.initFormValidation();
            
            console.log('BannersApp inicializado correctamente');
        },
        
        // =============================================
        // DATATABLES
        // =============================================
        
        /**
         * Inicializa todas las tablas DataTable
         */
        initDataTables: function() {
            if (!$.fn.DataTable) return;
            
            // Configuración por defecto en español
            $.extend($.fn.dataTable.defaults, {
                language: {
                    url: this.config.datatablesSpanishUrl
                },
                pageLength: 25,
                lengthMenu: [10, 25, 50, 100]
            });
            
            // Tabla de banners principal
            if ($('#tablaBanners').length) {
                $('#tablaBanners').DataTable({
                    order: [[0, 'desc']],
                    columnDefs: [
                        { orderable: false, targets: [1, 10] } // Columnas imagen y acciones
                    ]
                });
            }
            
            // Tabla de estadísticas diarias
            if ($('#tablaEstadisticasDiarias').length) {
                $('#tablaEstadisticasDiarias').DataTable({
                    order: [[0, 'desc']],
                    pageLength: 15,
                    searching: false
                });
            }
        },
        
        // =============================================
        // FILE INPUTS (Bootstrap Custom File Input)
        // =============================================
        
        /**
         * Inicializa los inputs de archivo personalizados
         */
        initFileInputs: function() {
            if (typeof bsCustomFileInput !== 'undefined') {
                bsCustomFileInput.init();
            }
            
            // Validación de imagen en tiempo real
            $('input[type="file"][accept="image/*"]').on('change', function(e) {
                const file = this.files[0];
                if (!file) return;
                
                // Validar tipo
                if (!BannersApp.config.allowedImageTypes.includes(file.type)) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Tipo de archivo no válido',
                        text: 'Solo se permiten imágenes JPG, PNG, GIF o WebP'
                    });
                    $(this).val('');
                    return;
                }
                
                // Validar tamaño
                if (file.size > BannersApp.config.maxFileSize) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Archivo demasiado grande',
                        text: 'El tamaño máximo permitido es 2MB'
                    });
                    $(this).val('');
                    return;
                }
                
                // Vista previa
                BannersApp.previewImage(this);
            });
        },
        
        /**
         * Vista previa de imagen
         */
        previewImage: function(input) {
            const $preview = $('#vistaPrevia');
            if (!$preview.length) return;
            
            const file = input.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    $preview.html(`
                        <img src="${e.target.result}" class="img-fluid border rounded" 
                             alt="Vista previa" style="max-height: 200px;">
                    `);
                };
                reader.readAsDataURL(file);
            }
        },
        
        // =============================================
        // CANCELACIÓN DE BANNERS
        // =============================================
        
        /**
         * Inicializa botones de cancelación
         */
        initCancelButtons: function() {
            $('.btn-cancelar').off('click').on('click', function(e) {
                e.preventDefault();
                const id = $(this).data('id');
                const titulo = $(this).data('titulo') || 'este banner';
                BannersApp.confirmCancel(id, titulo);
            });
        },
        
        /**
         * Confirma cancelación de banner
         */
        confirmCancel: function(bannerId, titulo) {
            Swal.fire({
                title: '¿Cancelar banner?',
                html: `¿Estás seguro de cancelar <strong>"${titulo}"</strong>?<br>
                       Se te reembolsará el tiempo restante proporcionalmente.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, cancelar',
                cancelButtonText: 'No, mantener'
            }).then((result) => {
                if (result.isConfirmed) {
                    this.askCancelReason(bannerId);
                }
            });
        },
        
        /**
         * Pregunta motivo de cancelación
         */
        askCancelReason: function(bannerId) {
            Swal.fire({
                title: 'Motivo de cancelación',
                input: 'textarea',
                inputPlaceholder: 'Opcional: ¿Por qué cancelas el banner?',
                showCancelButton: true,
                confirmButtonText: 'Confirmar cancelación',
                cancelButtonText: 'Volver',
                inputValidator: (value) => {
                    return new Promise((resolve) => {
                        resolve();
                    });
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    this.submitCancel(bannerId, result.value);
                }
            });
        },
        
        /**
         * Envía la cancelación
         */
        submitCancel: function(bannerId, motivo) {
            const form = $('<form>', {
                method: 'POST',
                action: `/mi-tienda/banners.php?action=cancelar&id=${bannerId}`
            }).append($('<input>', {
                type: 'hidden',
                name: 'motivo',
                value: motivo || ''
            }));
            
            $('body').append(form);
            form.submit();
        },
        
        // =============================================
        // RENOVACIÓN DE BANNERS
        // =============================================
        
        /**
         * Inicializa botones de renovación
         */
        initRenewButtons: function() {
            $('.btn-renovar').off('click').on('click', function(e) {
                e.preventDefault();
                const id = $(this).data('id');
                const titulo = $(this).data('titulo');
                BannersApp.showRenewModal(id, titulo);
            });
        },
        
        /**
         * Muestra modal de renovación
         */
        showRenewModal: function(bannerId, titulo) {
            Swal.fire({
                title: 'Renovar Banner',
                html: `
                    <p>Selecciona la duración para renovar <strong>"${titulo}"</strong></p>
                    <select id="duracionRenovacion" class="form-control">
                        <option value="3_dias" data-precio="100">3 Días - $100</option>
                        <option value="1_semana" data-precio="250">1 Semana - $250</option>
                        <option value="1_mes" data-precio="750" selected>1 Mes - $750</option>
                    </select>
                    <div class="mt-3 text-center">
                        <strong>Total a pagar: $<span id="totalRenovacion">750</span></strong>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: 'Renovar ahora',
                cancelButtonText: 'Cancelar',
                didOpen: () => {
                    $('#duracionRenovacion').on('change', function() {
                        const precio = $(this).find('option:selected').data('precio');
                        $('#totalRenovacion').text(precio);
                    });
                },
                preConfirm: () => {
                    return $('#duracionRenovacion').val();
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    this.submitRenew(bannerId, result.value);
                }
            });
        },
        
        /**
         * Envía la renovación
         */
        submitRenew: function(bannerId, duracion) {
            const form = $('<form>', {
                method: 'POST',
                action: `/mi-tienda/banners.php?action=renovar&id=${bannerId}`
            }).append($('<input>', {
                type: 'hidden',
                name: 'duracion',
                value: duracion
            }));
            
            $('body').append(form);
            form.submit();
        },
        
        // =============================================
        // DATE RANGE PICKER
        // =============================================
        
        /**
         * Inicializa DateRangePicker
         */
        initDateRangePicker: function() {
            if (!$.fn.daterangepicker || !$('#daterange').length) return;
            
            const startDate = $('#fecha_inicio').val() || moment().subtract(30, 'days');
            const endDate = $('#fecha_fin').val() || moment();
            
            $('#daterange').daterangepicker({
                locale: {
                    format: 'DD/MM/YYYY',
                    applyLabel: 'Aplicar',
                    cancelLabel: 'Cancelar',
                    customRangeLabel: 'Personalizado',
                    daysOfWeek: ['Do', 'Lu', 'Ma', 'Mi', 'Ju', 'Vi', 'Sa'],
                    monthNames: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 
                                'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
                },
                startDate: moment(startDate),
                endDate: moment(endDate),
                maxDate: moment(),
                ranges: {
                    'Hoy': [moment(), moment()],
                    'Ayer': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                    'Últimos 7 días': [moment().subtract(6, 'days'), moment()],
                    'Últimos 30 días': [moment().subtract(29, 'days'), moment()],
                    'Este mes': [moment().startOf('month'), moment().endOf('month')],
                    'Mes pasado': [moment().subtract(1, 'month').startOf('month'), 
                                  moment().subtract(1, 'month').endOf('month')]
                }
            }, function(start, end) {
                $('#fecha_inicio').val(start.format('YYYY-MM-DD'));
                $('#fecha_fin').val(end.format('YYYY-MM-DD'));
            });
        },
        
        // =============================================
        // GRÁFICOS (CHART.JS)
        // =============================================
        
        /**
         * Inicializa todos los gráficos
         */
        initCharts: function() {
            this.initRendimientoChart();
            this.initEvolucionChart();
        },
        
        /**
         * Gráfico de rendimiento individual
         */
        initRendimientoChart: function() {
            const canvas = document.getElementById('graficoRendimiento');
            if (!canvas || typeof Chart === 'undefined') return;
            
            const ctx = canvas.getContext('2d');
            const fechas = $(canvas).data('fechas') || [];
            const impresiones = $(canvas).data('impresiones') || [];
            const clicks = $(canvas).data('clicks') || [];
            
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: fechas.map(f => moment(f).format('DD/MM')),
                    datasets: [{
                        label: 'Impresiones',
                        data: impresiones,
                        borderColor: 'rgb(23, 162, 184)',
                        backgroundColor: 'rgba(23, 162, 184, 0.1)',
                        tension: 0.1
                    }, {
                        label: 'Clicks',
                        data: clicks,
                        borderColor: 'rgb(40, 167, 69)',
                        backgroundColor: 'rgba(40, 167, 69, 0.1)',
                        tension: 0.1,
                        yAxisID: 'y1'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false,
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: { display: true, text: 'Impresiones' }
                        },
                        y1: {
                            beginAtZero: true,
                            position: 'right',
                            title: { display: true, text: 'Clicks' },
                            grid: { drawOnChartArea: false }
                        }
                    }
                }
            });
        },
        
        /**
         * Gráfico de evolución general
         */
        initEvolucionChart: function() {
            const canvas = document.getElementById('graficoEvolucion');
            if (!canvas || typeof Chart === 'undefined') return;
            
            const ctx = canvas.getContext('2d');
            const fechas = $(canvas).data('fechas') || [];
            const impresiones = $(canvas).data('impresiones') || [];
            const clicks = $(canvas).data('clicks') || [];
            
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: fechas.map(f => moment(f).format('DD/MM')),
                    datasets: [{
                        label: 'Impresiones',
                        data: impresiones,
                        borderColor: 'rgb(23, 162, 184)',
                        backgroundColor: 'rgba(23, 162, 184, 0.1)',
                        tension: 0.1
                    }, {
                        label: 'Clicks',
                        data: clicks,
                        borderColor: 'rgb(40, 167, 69)',
                        backgroundColor: 'rgba(40, 167, 69, 0.1)',
                        tension: 0.1,
                        yAxisID: 'y1'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false,
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: { display: true, text: 'Impresiones' }
                        },
                        y1: {
                            beginAtZero: true,
                            position: 'right',
                            title: { display: true, text: 'Clicks' },
                            grid: { drawOnChartArea: false }
                        }
                    }
                }
            });
        },
        
        // =============================================
        // CALCULADORA DE PRECIOS
        // =============================================
        
        /**
         * Inicializa calculadora de precios en formulario de creación
         */
        initPriceCalculator: function() {
            const $duracion = $('#duracion');
            if (!$duracion.length) return;
            
            $duracion.on('change', function() {
                const option = $(this).find('option:selected');
                const precio = option.data('precio');
                const dias = option.data('dias');
                
                if (precio && dias) {
                    // Calcular fecha de fin
                    const fechaFin = moment().add(dias, 'days');
                    
                    $('#resumenDuracion').text(option.text().split('-')[0].trim());
                    $('#resumenFin').text(fechaFin.format('DD/MM/YYYY'));
                    $('#resumenCosto').text('$' + new Intl.NumberFormat('es-ES', {
                        minimumFractionDigits: 2
                    }).format(precio));
                    $('#costoSpan').text('$' + new Intl.NumberFormat('es-ES', {
                        minimumFractionDigits: 2
                    }).format(precio));
                    $('#btnPublicar').prop('disabled', false);
                } else {
                    $('#resumenDuracion').text('-');
                    $('#resumenFin').text('-');
                    $('#resumenCosto').text('$0');
                    $('#costoSpan').text('$0');
                    $('#btnPublicar').prop('disabled', true);
                }
            }).trigger('change');
        },
        
        // =============================================
        // VALIDACIÓN DE FORMULARIOS
        // =============================================
        
        /**
         * Inicializa validación de formularios
         */
        initFormValidation: function() {
            const $form = $('form[data-validation]');
            if (!$form.length || !$.fn.validate) return;
            
            $form.each(function() {
                const $f = $(this);
                const saldoRequerido = $f.data('saldo') || 0;
                
                $f.validate({
                    rules: {
                        titulo: {
                            required: true,
                            maxlength: 255
                        },
                        url: {
                            required: true,
                            url: true
                        },
                        imagen: {
                            required: $f.find('#imagen').prop('required'),
                            accept: "image/*"
                        },
                        duracion: {
                            required: true
                        }
                    },
                    messages: {
                        titulo: {
                            required: "El título es obligatorio",
                            maxlength: "Máximo 255 caracteres"
                        },
                        url: {
                            required: "La URL es obligatoria",
                            url: "Ingresa una URL válida (incluye http:// o https://)"
                        },
                        imagen: {
                            required: "La imagen es obligatoria",
                            accept: "Solo se permiten imágenes"
                        },
                        duracion: "Selecciona una duración"
                    },
                    errorElement: 'span',
                    errorPlacement: function(error, element) {
                        error.addClass('invalid-feedback');
                        element.closest('.form-group').append(error);
                    },
                    highlight: function(element) {
                        $(element).addClass('is-invalid');
                    },
                    unhighlight: function(element) {
                        $(element).removeClass('is-invalid');
                    },
                    submitHandler: function(form) {
                        BannersApp.checkBalanceBeforeSubmit(form, saldoRequerido);
                    }
                });
            });
            
            // Validación especial para el formulario de crear
            if ($('#btnPublicar').length) {
                this.setupBalanceCheck();
            }
        },
        
        /**
         * Configura verificación de saldo
         */
        setupBalanceCheck: function() {
            const saldo = parseFloat($('#saldoDisponible').val() || 0);
            
            $('form').on('submit', function(e) {
                const precio = parseFloat($('#duracion').find('option:selected').data('precio') || 0);
                
                if (precio > saldo) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'error',
                        title: 'Saldo insuficiente',
                        html: `Necesitas <strong>$${new Intl.NumberFormat('es-ES', {
                            minimumFractionDigits: 2
                        }).format(precio)}</strong><br>
                        Tu saldo disponible es <strong>$${new Intl.NumberFormat('es-ES', {
                            minimumFractionDigits: 2
                        }).format(saldo)}</strong>`,
                        confirmButtonText: 'Entendido'
                    });
                    return false;
                }
            });
        },
        
        /**
         * Verifica saldo antes de enviar
         */
        checkBalanceBeforeSubmit: function(form, saldoRequerido) {
            const precio = parseFloat($('#duracion').find('option:selected').data('precio') || 0);
            const saldo = parseFloat($('#saldoDisponible').val() || 0);
            
            if (precio > saldo) {
                Swal.fire({
                    icon: 'error',
                    title: 'Saldo insuficiente',
                    text: `Necesitas $${precio.toFixed(2)} pero tienes $${saldo.toFixed(2)} disponible`
                });
                return false;
            }
            
            form.submit();
        },
        
        // =============================================
        // UTILIDADES
        // =============================================
        
        /**
         * Muestra notificación toast
         */
        showToast: function(type, message, title = '') {
            if (typeof toastr !== 'undefined') {
                toastr[type](message, title, {
                    timeOut: this.config.toastrTimeout,
                    closeButton: true,
                    progressBar: true,
                    positionClass: 'toast-top-right'
                });
            } else {
                alert(message);
            }
        },
        
        /**
         * Formatea número como moneda
         */
        formatMoney: function(amount) {
            return new Intl.NumberFormat('es-ES', {
                style: 'currency',
                currency: 'USD',
                minimumFractionDigits: 2
            }).format(amount);
        },
        
        /**
         * Formatea fecha
         */
        formatDate: function(date) {
            return moment(date).format('DD/MM/YYYY');
        }
    };
    
    // =============================================
    // INICIALIZACIÓN AUTOMÁTICA
    // =============================================
    
    $(document).ready(function() {
        BannersApp.init();
    });
    
    // Exponer globalmente si es necesario
    window.BannersApp = BannersApp;
    
})(jQuery);