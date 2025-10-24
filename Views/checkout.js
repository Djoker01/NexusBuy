$(document).ready(function () {
    //console.log('Checkout.js inicializado');
    
    // Variables globales
    window.checkoutItems = [];
    window.checkoutSubtotal = 0;
    window.checkoutEnvio = 0;
    window.checkoutTotal = 0;
    window.direccionEnvioCompleta = '';
    window.metodoPagoSeleccionado = null;
    window.datosPago = {};
    
    // Inicializar checkout
    inicializarCheckout();

    async function inicializarCheckout() {
        //console.log('Inicializando checkout...');
        
        try {
            // Cargar datos del carrito desde sessionStorage
            await cargarDatosCarrito();
            
            // Cargar datos del usuario
            await cargarDatosUsuario();
            
            // Cargar provincias y municipios
            await cargarProvincias();
            
            // Cargar métodos de pago guardados
            await cargarMetodosPago();
            
            // Cargar direcciones del usuario
            await cargarDireccionesUsuario();
            
            // Actualizar resumen lateral
            actualizarResumenLateral();
            
            // Actualizar resumen de productos
            actualizarResumenProductos();
            
            console.log('Checkout inicializado correctamente');
        } catch (error) {
            console.error('Error inicializando checkout:', error);
            mostrarError('Error al cargar la página de checkout');
        }
    }

    function cargarDatosCarrito() {
        return new Promise((resolve, reject) => {
            try {
                // Obtener datos del sessionStorage
                const itemsStorage = sessionStorage.getItem('checkoutItems');
                const subtotalStorage = sessionStorage.getItem('checkoutSubtotal');
                const envioStorage = sessionStorage.getItem('checkoutEnvio');
                const totalStorage = sessionStorage.getItem('checkoutTotal');
                
                if (!itemsStorage) {
                    throw new Error('No hay productos seleccionados para checkout');
                }
                
                checkoutItems = JSON.parse(itemsStorage);
                checkoutSubtotal = parseFloat(subtotalStorage) || 0;
                checkoutEnvio = parseFloat(envioStorage) || 0;
                checkoutTotal = parseFloat(totalStorage) || 0;
                
                // console.log('Datos del carrito cargados:', {
                //     items: checkoutItems,
                //     subtotal: checkoutSubtotal,
                //     envio: checkoutEnvio,
                //     total: checkoutTotal
                // });
                
                resolve();
            } catch (error) {
                console.error('Error cargando datos del carrito:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No se pudieron cargar los productos. Por favor, regresa al carrito.',
                    confirmButtonText: 'Volver al carrito'
                }).then(() => {
                    window.location.href = 'carrito.php';
                });
                reject(error);
            }
        });
    }

    function cargarDatosUsuario() {
        return new Promise((resolve, reject) => {
            try {
                // Prellenar formulario con datos del usuario
                if (usuarioData.nombres) {
                    $('#nombres').val(usuarioData.nombres);
                }
                if (usuarioData.apellidos) {
                    $('#apellidos').val(usuarioData.apellidos);
                }
                if (usuarioData.email) {
                    $('#email').val(usuarioData.email);
                }
                if (usuarioData.telefono) {
                    $('#telefono').val(usuarioData.telefono);
                }
                
                //console.log('Datos del usuario cargados:', usuarioData);
                resolve();
            } catch (error) {
                console.error('Error cargando datos del usuario:', error);
                reject(error);
            }
        });
    }

    function cargarProvincias() {
        return new Promise((resolve, reject) => {
            $.post('../Controllers/ProvinciaController.php', { funcion: 'llenar_provincia' })
                .done(function(response) {
                    try {
                        const provincias = typeof response === 'string' ? JSON.parse(response) : response;
                        let options = '<option value="">Selecciona una provincia</option>';
                        
                        provincias.forEach(provincia => {
                            options += `<option value="${provincia.id}">${provincia.nombre}</option>`;
                        });
                        
                        $('#provincia').html(options);
                        //console.log('Provincias cargadas:', provincias.length);
                        resolve();
                    } catch (error) {
                        reject(error);
                    }
                })
                .fail(function(error) {
                    reject(error);
                });
        });
    }

    async function cargarDireccionesUsuario() {
        try {
            const response = await $.post('../Controllers/UsuarioMunicipioController.php', { 
                funcion: 'llenar_direcciones' 
            });
            
            const direcciones = typeof response === 'string' ? JSON.parse(response) : response;
            
            if (direcciones.length > 0) {
                let html = '<h6 class="mb-3">Direcciones guardadas:</h6>';
                direcciones.forEach((direccion, index) => {
                    const direccionCompleta = `${direccion.direccion}, ${direccion.municipio}, ${direccion.provincia}`;
                    html += `
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" name="direccion_guardada" 
                                   id="dir_${index}" value="${direccionCompleta}" 
                                   onchange="seleccionarDireccionGuardada('${direccionCompleta}')">
                            <label class="form-check-label" for="dir_${index}">
                                <small>${direccionCompleta}</small>
                            </label>
                        </div>
                    `;
                });
                
                $('#direccion').after(`
                    <div class="mb-3" id="direcciones-guardadas">
                        ${html}
                        <hr>
                        <h6>O ingresa una nueva dirección:</h6>
                    </div>
                `);
            }
        } catch (error) {
            console.error('Error cargando direcciones:', error);
        }
    }

    function cargarMetodosPago() {
        return new Promise((resolve, reject) => {
            $.post('../Controllers/PagoController.php', { funcion: 'obtener_metodos_pago' })
                .done(function(response) {
                    try {
                        const metodos = typeof response === 'string' ? JSON.parse(response) : response;
                        const $listaMetodos = $('#lista-metodos-pago');
                        
                        if (metodos.length === 0) {
                            $listaMetodos.html(`
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle mr-2"></i>
                                    No tienes métodos de pago guardados. Agrega uno nuevo.
                                </div>
                            `);
                        } else {
                            let html = '';
                            metodos.forEach((metodo, index) => {
                                let info = '';
                                let icono = '';
                                
                                if (metodo.tipo === 'tarjeta') {
                                    const ultimos4 = metodo.numero.slice(-4);
                                    info = `Tarjeta terminada en ${ultimos4}`;
                                    icono = 'far fa-credit-card';
                                } else if (metodo.tipo === 'paypal') {
                                    info = `PayPal: ${metodo.paypal_email}`;
                                    icono = 'fab fa-paypal';
                                } else if (metodo.tipo === 'transferencia') {
                                    info = `Transferencia: ${metodo.banco}`;
                                    icono = 'fas fa-university';
                                }
                                
                                html += `
                                    <div class="payment-method" onclick="seleccionarMetodoGuardado(${metodo.id}, '${metodo.tipo}')">
                                        <input type="radio" name="metodo_pago_guardado" 
                                               id="metodo_guardado_${metodo.id}" value="${metodo.id}">
                                        <label for="metodo_guardado_${metodo.id}">
                                            <i class="${icono} mr-2"></i>
                                            ${info}
                                            ${metodo.predeterminado ? '<span class="badge bg-success ml-2">Predeterminado</span>' : ''}
                                        </label>
                                    </div>
                                `;
                            });
                            $listaMetodos.html(html);
                        }
                        
                        //console.log('Métodos de pago cargados:', metodos.length);
                        resolve();
                    } catch (error) {
                        reject(error);
                    }
                })
                .fail(function(error) {
                    reject(error);
                });
        });
    }

    function actualizarResumenLateral() {
        // Actualizar los totales en el resumen lateral
        $('#resumen-lateral-subtotal').text(`$ ${checkoutSubtotal.toFixed(2)}`);
        $('#resumen-lateral-envio').text(`$ ${checkoutEnvio.toFixed(2)}`);
        $('#resumen-lateral-descuento').text('$ 0.00');
        $('#resumen-lateral-total').text(`$ ${checkoutTotal.toFixed(2)}`);
        
        //console.log('Resumen lateral actualizado');
    }

    async function actualizarResumenProductos() {
        const $resumenProductos = $('#resumen-productos');
        
        if (checkoutItems.length === 0) {
            $resumenProductos.html('<p class="text-muted">No hay productos en el carrito</p>');
            return;
        }
        
        try {
            // Obtener los detalles completos de los productos del carrito
            const response = await $.post('../Controllers/CarritoController.php', { 
                funcion: 'obtener_carrito' 
            });
            
            let carritoCompleto;
            if (typeof response === 'string') {
                carritoCompleto = JSON.parse(response);
            } else {
                carritoCompleto = response;
            }
            
            // Filtrar solo los productos seleccionados para checkout
            const productosSeleccionados = carritoCompleto.filter(item => 
                checkoutItems.includes(item.id.toString())
            );
            
            console.log('Productos seleccionados para checkout:', productosSeleccionados);
            
            let html = '';
            
            if (productosSeleccionados.length === 0) {
                html = `
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        No se encontraron los productos seleccionados.
                    </div>
                `;
            } else {
                productosSeleccionados.forEach(producto => {
                    const precioFinal = parseFloat(producto.precio) || 0;
                    const cantidad = parseInt(producto.cantidad_producto) || 1;
                    const subtotal = precioFinal * cantidad;
                    const tieneDescuento = parseFloat(producto.descuento_porcentaje) > 0;
                    const precioOriginal = parseFloat(producto.precio_unitario) || precioFinal;
                    
                    html += `
                        <div class="resumen-producto">
                            <div class="row align-items-center">
                                <div class="col-2">
                                    <img src="../Util/Img/Producto/${producto.imagen || 'producto_default.png'}" 
                                         alt="${producto.nombre}" 
                                         class="img-fluid rounded"
                                         style="max-height: 80px; object-fit: cover;"
                                         onerror="this.src='../Util/Img/Producto/producto_default.png'">
                                </div>
                                <div class="col-6">
                                    <strong class="d-block">${producto.nombre || 'Producto'}</strong>
                                    <small class="text-muted">${producto.marca_nombre || ''}</small>
                                    <br>
                                    <small class="text-muted">Vendido por: ${producto.tienda_nombre || 'Tienda'}</small>
                                    ${producto.detalles ? `<br><small class="text-muted">${producto.detalles}</small>` : ''}
                                </div>
                                <div class="col-2 text-center">
                                    <small class="text-muted">Cantidad: ${cantidad}</small>
                                </div>
                                <div class="col-2 text-right">
                                    <strong>$${subtotal.toFixed(2)}</strong>
                                    ${tieneDescuento ? `
                                        <br>
                                        <small class="text-muted text-decoration-line-through">$${precioOriginal.toFixed(2)}</small>
                                        <small class="text-success">-${producto.descuento_porcentaje}%</small>
                                    ` : ''}
                                    <br>
                                    <small class="text-muted">$${precioFinal.toFixed(2)} c/u</small>
                                </div>
                            </div>
                        </div>
                    `;
                });
            }
            
            $resumenProductos.html(html);
            
        } catch (error) {
            console.error('Error cargando detalles de productos:', error);
            $resumenProductos.html(`
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    Error al cargar los detalles de los productos.
                </div>
            `);
        }
    }

    // Función para obtener icono según el tipo de método de pago
    function obtenerIconoMetodo(tipo) {
        switch(tipo) {
            case 'tarjeta': return 'far fa-credit-card';
            case 'paypal': return 'fab fa-paypal';
            case 'transferencia': return 'fas fa-university';
            default: return 'fas fa-money-bill-wave';
        }
    }

    // Evento para cargar municipios cuando se selecciona una provincia
    $(document).on('change', '#provincia', function() {
        const idProvincia = $(this).val();
        const $municipio = $('#municipio');
        
        if (idProvincia) {
            $municipio.prop('disabled', false);
            
            $.post('../Controllers/MunicipioController.php', { 
                funcion: 'llenar_municipio', 
                id_provincia: idProvincia 
            }).done(function(response) {
                try {
                    const municipios = typeof response === 'string' ? JSON.parse(response) : response;
                    let options = '<option value="">Selecciona un municipio</option>';
                    
                    municipios.forEach(municipio => {
                        options += `<option value="${municipio.id}">${municipio.nombre}</option>`;
                    });
                    
                    $municipio.html(options);
                } catch (error) {
                    console.error('Error cargando municipios:', error);
                }
            });
        } else {
            $municipio.prop('disabled', true);
            $municipio.html('<option value="">Primero selecciona una provincia</option>');
        }
    });

    // Validación de tarjeta de crédito en tiempo real
    $(document).on('input', 'input[name="tarjeta_numero"]', function() {
        let valor = $(this).val().replace(/\s/g, '');
        valor = valor.replace(/(\d{4})/g, '$1 ').trim();
        $(this).val(valor);
    });

    $(document).on('input', 'input[name="tarjeta_vencimiento"]', function() {
        let valor = $(this).val().replace(/\D/g, '');
        if (valor.length >= 2) {
            valor = valor.substring(0, 2) + '/' + valor.substring(2, 4);
        }
        $(this).val(valor);
    });

    // Inicializar en el primer paso
    siguientePaso(1);
});

// ================= FUNCIONES GLOBALES =================

function siguientePaso(paso) {
    //console.log('Navegando al paso:', paso);
    
    // Validar paso actual antes de avanzar
    if (paso === 2 && !validarPaso1()) {
        return;
    }
    if (paso === 3 && !validarPaso2()) {
        return;
    }
    
    // Ocultar todos los pasos
    $('.checkout-step').hide();
    
    // Mostrar paso seleccionado
    $(`#step-content-${paso}`).show();
    
    // Actualizar indicadores de pasos
    $('.step').removeClass('active completed');
    
    for (let i = 1; i <= 3; i++) {
        if (i < paso) {
            $(`#step-${i}`).addClass('completed');
        } else if (i === paso) {
            $(`#step-${i}`).addClass('active');
        }
    }
    
    // Si vamos al paso 3, actualizar los resúmenes
    if (paso === 3) {
        actualizarResumenFinal();
    }
}

function validarPaso1() {
    const required = ['#nombres', '#apellidos', '#direccion', '#provincia', '#municipio', '#telefono'];
    let valido = true;
    
    required.forEach(selector => {
        const $element = $(selector);
        if (!$element.val().trim()) {
            $element.addClass('is-invalid');
            valido = false;
        } else {
            $element.removeClass('is-invalid');
        }
    });
    
    // Validar email si se proporcionó
    const email = $('#email').val();
    if (email && !isValidEmail(email)) {
        $('#email').addClass('is-invalid');
        valido = false;
    }
    
    if (!valido) {
        mostrarError('Por favor completa todos los campos requeridos');
    } else {
        // Guardar dirección de envío
        guardarDireccionEnvio();
    }
    
    return valido;
}

function validarPaso2() {
    const metodoSeleccionado = $('input[name="metodo_pago"]:checked').val() || 
                              $('input[name="metodo_pago_guardado"]:checked').val();
    
    if (!metodoSeleccionado) {
        mostrarError('Por favor selecciona un método de pago');
        return false;
    }
    
    // Validar formularios específicos según el método de pago
    if ($('#metodo_tarjeta').is(':checked') && !validarFormularioTarjeta()) {
        return false;
    }
    if ($('#metodo_paypal').is(':checked') && !validarFormularioPayPal()) {
        return false;
    }
    if ($('#metodo_transferencia').is(':checked') && !validarFormularioTransferencia()) {
        return false;
    }
    
    // Guardar datos del método de pago
    guardarDatosPago();
    
    return true;
}

function validarFormularioTarjeta() {
    const required = ['tarjeta_titular', 'tarjeta_numero', 'tarjeta_vencimiento', 'tarjeta_cvv'];
    let valido = true;
    
    required.forEach(name => {
        const valor = $(`input[name="${name}"]`).val();
        if (!valor || !valor.trim()) {
            $(`input[name="${name}"]`).addClass('is-invalid');
            valido = false;
        } else {
            $(`input[name="${name}"]`).removeClass('is-invalid');
        }
    });
    
    // Validar formato de número de tarjeta (solo básico)
    const numeroTarjeta = $('input[name="tarjeta_numero"]').val().replace(/\s/g, '');
    if (numeroTarjeta.length < 13) {
        $('input[name="tarjeta_numero"]').addClass('is-invalid');
        valido = false;
    }
    
    if (!valido) {
        mostrarError('Por favor completa todos los campos de la tarjeta');
    }
    
    return valido;
}

function validarFormularioPayPal() {
    const email = $('input[name="paypal_email"]').val();
    if (!email || !isValidEmail(email)) {
        $('input[name="paypal_email"]').addClass('is-invalid');
        mostrarError('Por favor ingresa un email válido de PayPal');
        return false;
    }
    return true;
}

function validarFormularioTransferencia() {
    const required = ['transferencia_banco', 'transferencia_cuenta'];
    let valido = true;
    
    required.forEach(name => {
        const valor = $(`input[name="${name}"]`).val();
        if (!valor || !valor.trim()) {
            $(`input[name="${name}"]`).addClass('is-invalid');
            valido = false;
        }
    });
    
    if (!valido) {
        mostrarError('Por favor completa todos los campos de transferencia');
    }
    
    return valido;
}

function seleccionarMetodoPago(tipo) {
    //console.log('Seleccionando método de pago:', tipo);
    
    // Deseleccionar todos los métodos
    $('.payment-method').removeClass('selected');
    $('input[name="metodo_pago"]').prop('checked', false);
    $('input[name="metodo_pago_guardado"]').prop('checked', false);
    $('.payment-method > div').hide();
    
    // Seleccionar el método clickeado
    $(`#metodo_${tipo}`).prop('checked', true).closest('.payment-method').addClass('selected');
    $(`#form-${tipo}`).show();
}

function seleccionarMetodoGuardado(id, tipo) {
    //console.log('Seleccionando método guardado:', id, tipo);
    
    // Deseleccionar todos los métodos nuevos
    $('.payment-method').removeClass('selected');
    $('input[name="metodo_pago"]').prop('checked', false);
    $('.payment-method > div').hide();
    
    // Seleccionar el método guardado
    $(`#metodo_guardado_${id}`).prop('checked', true).closest('.payment-method').addClass('selected');
    
    // Guardar datos del método seleccionado
    window.metodoPagoSeleccionado = { id, tipo };
}

function seleccionarDireccionGuardada(direccionCompleta) {
    //console.log('Dirección seleccionada:', direccionCompleta);
    window.direccionEnvioCompleta = direccionCompleta;
    
    // Separar la dirección en sus componentes si es necesario
    const partes = direccionCompleta.split(', ');
    if (partes.length >= 3) {
        $('#direccion').val(partes[0]);
        // Aquí podrías intentar cargar provincia y municipio automáticamente
    }
}

function guardarDireccionEnvio() {
    const nombres = $('#nombres').val();
    const apellidos = $('#apellidos').val();
    const direccion = $('#direccion').val();
    const provincia = $('#provincia option:selected').text();
    const municipio = $('#municipio option:selected').text();
    const telefono = $('#telefono').val();
    const email = $('#email').val();
    const instrucciones = $('#instrucciones').val();
    
    window.direccionEnvioCompleta = `${nombres} ${apellidos}, ${direccion}, ${municipio}, ${provincia}. Tel: ${telefono}${email ? ', Email: ' + email : ''}${instrucciones ? '. Instrucciones: ' + instrucciones : ''}`;
    
    //console.log('Dirección de envío guardada:', window.direccionEnvioCompleta);
}

function guardarDatosPago() {
    const metodoSeleccionado = $('input[name="metodo_pago"]:checked').val() || 
                              $('input[name="metodo_pago_guardado"]:checked').val();
    
    window.metodoPagoSeleccionado = metodoSeleccionado;
    window.datosPago = {};
    
    if (metodoSeleccionado === 'tarjeta') {
        window.datosPago = {
            tipo: 'tarjeta',
            titular: $('input[name="tarjeta_titular"]').val(),
            numero: $('input[name="tarjeta_numero"]').val().replace(/\s/g, ''),
            fecha_vencimiento: $('input[name="tarjeta_vencimiento"]').val(),
            cvv: $('input[name="tarjeta_cvv"]').val()
        };
    } else if (metodoSeleccionado === 'paypal') {
        window.datosPago = {
            tipo: 'paypal',
            paypal_email: $('input[name="paypal_email"]').val()
        };
    } else if (metodoSeleccionado === 'transferencia') {
        window.datosPago = {
            tipo: 'transferencia',
            banco: $('input[name="transferencia_banco"]').val(),
            numero_cuenta: $('input[name="transferencia_cuenta"]').val()
        };
    } else {
        // Es un método guardado
        window.datosPago = {
            tipo: 'guardado',
            id: metodoSeleccionado
        };
    }
    
    //console.log('Datos de pago guardados:', window.datosPago);
}

function actualizarResumenFinal() {
    // Actualizar resumen de envío
    $('#resumen-envio').html(`
        <p class="mb-1"><strong>${$('#nombres').val()} ${$('#apellidos').val()}</strong></p>
        <p class="mb-1">${$('#direccion').val()}</p>
        <p class="mb-1">${$('#municipio option:selected').text()}, ${$('#provincia option:selected').text()}</p>
        <p class="mb-1">Tel: ${$('#telefono').val()}</p>
        ${$('#email').val() ? `<p class="mb-1">Email: ${$('#email').val()}</p>` : ''}
        ${$('#instrucciones').val() ? `<p class="mb-1"><em>Instrucciones: ${$('#instrucciones').val()}</em></p>` : ''}
    `);
    
    // Actualizar resumen de pago
    let metodoPagoHtml = '';
    if (window.datosPago.tipo === 'tarjeta') {
        const ultimos4 = window.datosPago.numero.slice(-4);
        metodoPagoHtml = `<p class="mb-0"><i class="far fa-credit-card mr-2"></i>Tarjeta terminada en ${ultimos4}</p>`;
    } else if (window.datosPago.tipo === 'paypal') {
        metodoPagoHtml = `<p class="mb-0"><i class="fab fa-paypal mr-2"></i>PayPal: ${window.datosPago.paypal_email}</p>`;
    } else if (window.datosPago.tipo === 'transferencia') {
        metodoPagoHtml = `<p class="mb-0"><i class="fas fa-university mr-2"></i>Transferencia: ${window.datosPago.banco}</p>`;
    } else {
        metodoPagoHtml = `<p class="mb-0"><i class="fas fa-credit-card mr-2"></i>Método de pago guardado</p>`;
    }
    
    $('#resumen-pago').html(metodoPagoHtml);
    
    // Actualizar totales en el resumen final
    $('#resumen-subtotal').text(`$ ${checkoutSubtotal.toFixed(2)}`);
    $('#resumen-envio-costo').text(`$ ${checkoutEnvio.toFixed(2)}`);
    $('#resumen-descuento').text('$ 0.00');
    $('#resumen-total').text(`$ ${checkoutTotal.toFixed(2)}`);
}

async function procesarPago() {
    //console.log('Iniciando procesamiento de pago...');
    
    // Validar términos y condiciones
    if (!$('#terminos').is(':checked')) {
        mostrarError('Debes aceptar los términos y condiciones para continuar');
        return;
    }
    
    // Mostrar loading
    const btn = $('#btn-procesar-pago');
    const originalText = btn.html();
    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i>Procesando...');
    
    try {
        // Preparar datos para el pago
        const datosPago = {
            datos_pago: window.datosPago,
            direccion_envio: window.direccionEnvioCompleta,
            items_seleccionados: checkoutItems
        };
        
        //console.log('Enviando datos de pago:', datosPago);
        
        // Realizar la petición al servidor
        const response = await $.post('../Controllers/PagoController.php', {
            funcion: 'procesar_pago',
            ...datosPago
        });
        
        const resultado = typeof response === 'string' ? JSON.parse(response) : response;
        
        if (resultado.success) {
            // Pago exitoso
            await mostrarExitoPago(resultado);
        } else {
            throw new Error(resultado.error || 'Error desconocido al procesar el pago');
        }
        
    } catch (error) {
        console.error('Error procesando pago:', error);
        mostrarError('Error al procesar el pago: ' + error.message);
        btn.prop('disabled', false).html(originalText);
    }
}

async function mostrarExitoPago(resultado) {
    // Limpiar sessionStorage
    sessionStorage.removeItem('checkoutItems');
    sessionStorage.removeItem('checkoutSubtotal');
    sessionStorage.removeItem('checkoutEnvio');
    sessionStorage.removeItem('checkoutTotal');
    
    await Swal.fire({
        icon: 'success',
        title: '¡Pago Exitoso!',
        html: `
            <div class="text-center">
                <i class="fas fa-check-circle text-success fa-4x mb-3"></i>
                <h4>¡Gracias por tu compra!</h4>
                <p class="mb-2">Tu pedido ha sido procesado exitosamente.</p>
                <p><strong>Número de orden:</strong> ${resultado.numero_orden}</p>
                <p><strong>Total:</strong> $ ${resultado.total.toFixed(2)}</p>
                <div class="alert alert-info mt-3">
                    <small>
                        <i class="fas fa-info-circle mr-2"></i>
                        Recibirás un email de confirmación con los detalles de tu pedido.
                    </small>
                </div>
            </div>
        `,
        confirmButtonText: 'Ver Mis Pedidos',
        showCancelButton: true,
        cancelButtonText: 'Seguir Comprando'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = 'mis_pedidos.php';
        } else {
            window.location.href = '../index.php';
        }
    });
}

// ================= FUNCIONES UTILITARIAS =================

function isValidEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

function mostrarError(mensaje) {
    Swal.fire({
        icon: 'error',
        title: 'Error',
        text: mensaje,
        confirmButtonText: 'Entendido'
    });
}

function mostrarExito(mensaje) {
    Swal.fire({
        icon: 'success',
        title: 'Éxito',
        text: mensaje,
        confirmButtonText: 'Continuar'
    });
}