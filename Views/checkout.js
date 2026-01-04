$(document).ready(function () {
    // ================= DEBUGGING INICIAL =================
    // console.log('🛠️ Iniciando checkout.js con debugging EXTENDIDO');
    // console.log('=== VERIFICACIÓN DE IDs CRÍTICOS ===');
    
    // Verificar TODOS los elementos importantes
    const elementosCriticos = [
        'resumen-lateral-subtotal',
        'resumen-lateral-envio', 
        'resumen-lateral-descuento',
        'resumen-lateral-total',
        'resumen-productos',
        'step-content-1',
        'step-content-2',
        'step-content-3',
        'form-checkout'
    ];
    
    elementosCriticos.forEach(id => {
        const elemento = $(`#${id}`);
        // console.log(`🔍 #${id}:`, elemento.length > 0 ? '✅ ENCONTRADO' : '❌ NO ENCONTRADO');
    });
    
    // console.log('=== FIN VERIFICACIÓN ===');
    
    // ================= VARIABLES GLOBALES =================
     window.checkoutItems = [];
    window.checkoutSubtotal = 0;
    window.checkoutEnvio = 0;
    window.checkoutTotal = 0;
    window.checkoutSubtotalOriginal = 0;
    window.checkoutEnvioOriginal = 0;
    window.checkoutTotalOriginal = 0;
    window.direccionEnvioCompleta = '';
    window.metodoPagoSeleccionado = null;
    window.datosPago = {};
    window.monedaCheckout = 'CUP';
    window.simboloMonedaCheckout = '$';
    window.tasaCambioCheckout = 1;
    
    // Verificar que usuarioData existe
    if (typeof usuarioData === 'undefined') {
        console.error('ERROR: usuarioData no está definido. Revisa el orden de los scripts en checkout.php');
        window.usuarioData = {};
    } else {
        // console.log('✅ usuarioData cargado correctamente:', usuarioData);
    }
    
    // ================= INICIALIZACIÓN =================
    inicializarCheckout();

    async function inicializarCheckout() {
        // console.log('Inicializando checkout...');
        
         try {
            // 1. Cargar datos del carrito
            await cargarDatosCarrito();
            
            // 2. Cargar datos del usuario
            await cargarDatosUsuario();
            
            // 3. Actualizar moneda (esto también actualiza precios)
            await actualizarMonedaCheckout();
            
            // 5. Cargar métodos de pago
            await cargarMetodosPago();
            
            // 6. Cargar direcciones
            await cargarDireccionesUsuario();
            
            // 7. Actualizar resumen de productos
            await actualizarResumenProductos();
            
            // 8. Inicializar en paso 1
            siguientePaso(1);
            
            // console.log('✅ Checkout inicializado correctamente');
            
            // Forzar actualización después de 1 segundo (por si hay carga asíncrona)
            setTimeout(() => {
                // console.log('🔄 Actualización forzada después de 1s');
                actualizarResumenLateral();
                // actualizarResumenLateralSimple();
            }, 1000);
            
        } catch (error) {
            console.error('Error inicializando checkout:', error);
            mostrarError('Error al cargar la página de checkout: ' + error.message);
        }
    }


    // ================= FUNCIONES PRINCIPALES =================

    function cargarDatosCarrito() {
        return new Promise((resolve, reject) => {
            try {
                // console.log('🔍 Verificando datos del carrito en sessionStorage...');
                
                const itemsStorage = sessionStorage.getItem('checkoutItems');
                const subtotalStorage = sessionStorage.getItem('checkoutSubtotal');
                const envioStorage = sessionStorage.getItem('checkoutEnvio');
                const totalStorage = sessionStorage.getItem('checkoutTotal');
                
                // console.log('📦 Datos encontrados:', {
                //     itemsStorage: itemsStorage ? 'SÍ' : 'NO',
                //     itemsLength: itemsStorage ? JSON.parse(itemsStorage).length : 0,
                //     subtotalStorage: subtotalStorage,
                //     envioStorage: envioStorage,
                //     totalStorage: totalStorage
                // });
                
                if (!itemsStorage || itemsStorage === '[]' || itemsStorage === 'null') {
                    const error = new Error('No hay productos seleccionados para checkout');
                    console.error('❌ Error:', error.message);
                    
                    // Mostrar error específico
                    Swal.fire({
                        icon: 'error',
                        title: 'Carrito vacío',
                        text: 'No se encontraron productos en el carrito. Por favor, regresa al carrito y selecciona productos.',
                        confirmButtonText: 'Volver al carrito'
                    }).then(() => {
                        window.location.href = 'carrito.php';
                    });
                    
                    reject(error);
                    return;
                }
                
                try {
                    window.checkoutItems = JSON.parse(itemsStorage);
                    window.checkoutSubtotalOriginal = parseFloat(subtotalStorage) || 0;
                    window.checkoutEnvioOriginal = parseFloat(envioStorage) || 0;
                    window.checkoutTotalOriginal = parseFloat(totalStorage) || 0;
                    
                    // console.log('✅ Datos del carrito cargados correctamente:', {
                    //     items: window.checkoutItems,
                    //     cantidadItems: window.checkoutItems.length,
                    //     subtotalOriginal: window.checkoutSubtotalOriginal,
                    //     envioOriginal: window.checkoutEnvioOriginal,
                    //     totalOriginal: window.checkoutTotalOriginal
                    // });
                    
                    resolve();
                } catch (parseError) {
                    console.error('❌ Error parseando datos del carrito:', parseError);
                    
                    Swal.fire({
                        icon: 'error',
                        title: 'Error en los datos',
                        text: 'Los datos del carrito están corruptos. Por favor, vacía el carrito y vuelve a intentarlo.',
                        confirmButtonText: 'Volver al carrito'
                    }).then(() => {
                        window.location.href = 'carrito.php';
                    });
                    
                    reject(parseError);
                }
                
            } catch (error) {
                console.error('❌ Error general cargando datos del carrito:', error);
                
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error al cargar los datos del carrito: ' + error.message,
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
                if (window.usuarioData && window.usuarioData.nombres) {
                    $('#nombres').val(window.usuarioData.nombres);
                }
                if (window.usuarioData && window.usuarioData.apellidos) {
                    $('#apellidos').val(window.usuarioData.apellidos);
                }
                if (window.usuarioData && window.usuarioData.email) {
                    $('#email').val(window.usuarioData.email);
                }
                if (window.usuarioData && window.usuarioData.telefono) {
                    $('#telefono').val(window.usuarioData.telefono);
                }
                
                // console.log('✅ Datos del usuario cargados');
                resolve();
            } catch (error) {
                console.error('Error cargando datos del usuario:', error);
                resolve(); // Continuamos aunque falle
            }
        });
    }

    async function actualizarMonedaCheckout() {
        try {
            const monedaSeleccionada = localStorage.getItem('moneda-seleccionada') || 'CUP';
            // console.log('💰 Moneda seleccionada:', monedaSeleccionada);
            
            // Obtener tasa de cambio
            const response = await $.post('../Controllers/MonedaController.php', {
                funcion: 'obtener_tasa_cambio',
                moneda: monedaSeleccionada
            });
            
            const data = typeof response === 'string' ? JSON.parse(response) : response;
            
            if (data.success) {
                window.tasaCambioCheckout = parseFloat(data.tasa_cambio) || 1;
                
                // Determinar símbolo
                if (data.moneda && typeof data.moneda === 'object') {
                    window.simboloMonedaCheckout = data.moneda.simbolo || '$';
                    window.monedaCheckout = data.moneda.codigo || monedaSeleccionada;
                } else {
                    window.simboloMonedaCheckout = '$';
                    window.monedaCheckout = monedaSeleccionada;
                }
                
                // console.log('💱 Configuración moneda:', {
                //     moneda: window.monedaCheckout,
                //     simbolo: window.simboloMonedaCheckout,
                //     tasa: window.tasaCambioCheckout
                // });
                
                // IMPORTANTE: Si cambiamos de moneda, necesitamos recalcular
                await actualizarPreciosCheckout();
                
            } else {
                throw new Error(data.error || 'Error obteniendo tasa');
            }
        } catch (error) {
            console.error('❌ Error actualizando moneda:', error);
            // Valores por defecto
            window.monedaCheckout = 'CUP';
            window.simboloMonedaCheckout = '$';
            window.tasaCambioCheckout = 1;
            await actualizarPreciosCheckout();
        }
    }

    async function actualizarPreciosCheckout() {
        // console.log('🔄 actualizarPreciosCheckout() ejecutándose');
        // console.log('📊 Valores originales:', {
        //     subtotalOriginal: window.checkoutSubtotalOriginal,
        //     envioOriginal: window.checkoutEnvioOriginal,
        //     totalOriginal: window.checkoutTotalOriginal,
        //     tasa: window.tasaCambioCheckout,
        //     moneda: window.monedaCheckout
        // });
        
        // DECISIÓN CRÍTICA: ¿Los valores ya están convertidos o no?
        // Si monedaCheckout es diferente de CUP, los valores YA están convertidos
        if (window.monedaCheckout !== 'CUP' && window.tasaCambioCheckout > 1) {
            // Los valores YA están en la moneda de destino
            // console.log('✅ Valores YA convertidos, usar directamente');
            window.checkoutSubtotal = window.checkoutSubtotalOriginal;
            window.checkoutEnvio = window.checkoutEnvioOriginal;
            window.checkoutTotal = window.checkoutTotalOriginal;
        } else {
            // Los valores están en CUP, necesitan conversión
            // console.log('🔄 Valores en CUP, convertir');
            window.checkoutSubtotal = window.checkoutSubtotalOriginal / window.tasaCambioCheckout;
            window.checkoutEnvio = window.checkoutEnvioOriginal / window.tasaCambioCheckout;
            window.checkoutTotal = window.checkoutTotalOriginal / window.tasaCambioCheckout;
        }
        
        // Redondear a 2 decimales
        window.checkoutSubtotal = parseFloat(window.checkoutSubtotal.toFixed(2));
        window.checkoutEnvio = parseFloat(window.checkoutEnvio.toFixed(2));
        window.checkoutTotal = parseFloat(window.checkoutTotal.toFixed(2));
        
        // console.log('💰 Valores finales:', {
        //     subtotal: window.checkoutSubtotal,
        //     envio: window.checkoutEnvio,
        //     total: window.checkoutTotal,
        //     simbolo: window.simboloMonedaCheckout
        // });
        
        // Actualizar resumen lateral
        actualizarResumenLateral();
    }

    window.actualizarResumenLateral = function() {
        // console.log('🎯 actualizarResumenLateral() ejecutándose');
        // console.log('💰 Valores a mostrar:', {
        //     subtotal: window.checkoutSubtotal,
        //     envio: window.checkoutEnvio,
        //     total: window.checkoutTotal,
        //     simbolo: window.simboloMonedaCheckout
        // });
        
        // Asegurar que los valores sean números válidos
        const subtotal = isNaN(window.checkoutSubtotal) ? 0 : window.checkoutSubtotal;
        const envio = isNaN(window.checkoutEnvio) ? 0 : window.checkoutEnvio;
        const total = isNaN(window.checkoutTotal) ? 0 : window.checkoutTotal;
        
        const subtotalTexto = `${window.simboloMonedaCheckout} ${subtotal.toFixed(2)}`;
        const envioTexto = `${window.simboloMonedaCheckout} ${envio.toFixed(2)}`;
        const totalTexto = `${window.simboloMonedaCheckout} ${total.toFixed(2)}`;
        
        // console.log('📝 Textos a insertar:', { subtotalTexto, envioTexto, totalTexto });
        
        // Actualizar elementos específicos
        $('#resumen-lateral-subtotal').text(subtotalTexto);
        $('#resumen-lateral-envio').text(envioTexto);
        $('#resumen-lateral-descuento').text(`${window.simboloMonedaCheckout} 0.00`);
        $('#resumen-lateral-total').text(totalTexto);
        
        // console.log('✅ Resumen lateral actualizado');
        
        // Verificar que se actualizó
        // console.log('🔍 Verificación después de actualizar:', {
        //     subtotal: $('#resumen-lateral-subtotal').text(),
        //     envio: $('#resumen-lateral-envio').text(),
        //     total: $('#resumen-lateral-total').text()
        // });
    };
    
    function crearResumenLateralSiNoExiste() {
        // console.log('🛠️ Creando resumen lateral dinámicamente...');
        
        // Buscar el contenedor del resumen lateral
        let resumenContainer = $('.resumen-lateral, .checkout-summary, .summary, .cart-summary');
        
        if (resumenContainer.length === 0) {
            // console.log('⚠️ No se encontró contenedor de resumen, creando uno');
            // Crear un contenedor básico
            $('body').append(`
                <div id="resumen-lateral-dinamico" style="position: fixed; top: 100px; right: 20px; width: 300px; background: white; border: 1px solid #ddd; padding: 20px; z-index: 1000; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                    <h5>Resumen del Pedido</h5>
                    <div class="mb-2">
                        <span>Subtotal:</span>
                        <span id="resumen-lateral-subtotal-dinamico" style="float: right;">${window.simboloMonedaCheckout} ${window.checkoutSubtotal.toFixed(2)}</span>
                    </div>
                    <div class="mb-2">
                        <span>Envío:</span>
                        <span id="resumen-lateral-envio-dinamico" style="float: right;">${window.simboloMonedaCheckout} ${window.checkoutEnvio.toFixed(2)}</span>
                    </div>
                    <div class="mb-2">
                        <span>Descuento:</span>
                        <span id="resumen-lateral-descuento-dinamico" style="float: right;">${window.simboloMonedaCheckout} 0.00</span>
                    </div>
                    <hr>
                    <div class="mb-2">
                        <strong>Total:</strong>
                        <strong id="resumen-lateral-total-dinamico" style="float: right;">${window.simboloMonedaCheckout} ${window.checkoutTotal.toFixed(2)}</strong>
                    </div>
                </div>
            `);
            resumenContainer = $('#resumen-lateral-dinamico');
        }
        
        // Guardar referencias a los nuevos elementos
        window.resumenLateralElements = {
            subtotal: $('#resumen-lateral-subtotal-dinamico'),
            envio: $('#resumen-lateral-envio-dinamico'),
            descuento: $('#resumen-lateral-descuento-dinamico'),
            total: $('#resumen-lateral-total-dinamico')
        };
    }

    async function actualizarResumenProductos() {
        const $resumenProductos = $('#resumen-productos');
        // console.log('🔄 Actualizando resumen de productos...');
        // console.log('📦 checkoutItems:', window.checkoutItems);
        
        if (!window.checkoutItems || window.checkoutItems.length === 0) {
            console.error('❌ No hay productos en checkoutItems');
            $resumenProductos.html(`
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    No hay productos seleccionados. Por favor, regresa al carrito.
                    <br>
                    <button class="btn btn-sm btn-primary mt-2" onclick="window.location.href='carrito.php'">
                        <i class="fas fa-shopping-cart mr-2"></i>Volver al carrito
                    </button>
                </div>
            `);
            return;
        }
        
        try {
            // console.log('📡 Solicitando datos del carrito al servidor...');
            
            const response = await $.post('../Controllers/CarritoController.php', { 
                funcion: 'obtener_carrito' 
            });
            
            // console.log('📥 Respuesta del servidor:', response);
            
            let carritoCompleto;
            if (typeof response === 'string') {
                try {
                    carritoCompleto = JSON.parse(response);
                    // console.log('✅ Datos parseados correctamente');
                } catch (e) {
                    console.error('❌ Error parseando respuesta JSON:', e);
                    console.error('Respuesta original:', response);
                    
                    if (response.includes('no_sesion')) {
                        console.error('Sesión expirada, recargando página...');
                        window.location.reload();
                        return;
                    }
                    
                    throw new Error('Respuesta del servidor no válida');
                }
            } else {
                carritoCompleto = response;
            }
            
            // Si el servidor retorna un error
            if (carritoCompleto.error === 'no_sesion') {
                console.error('❌ Sesión no válida');
                window.location.reload();
                return;
            }
            
            if (!Array.isArray(carritoCompleto)) {
                console.error('❌ La respuesta no es un array:', carritoCompleto);
                throw new Error('Formato de respuesta inválido');
            }
            
            // console.log('✅ Carrito completo recibido:', carritoCompleto);
            // console.log('🔍 Cantidad de productos en carrito:', carritoCompleto.length);
            
            // Filtrar productos seleccionados
            const productosSeleccionados = carritoCompleto.filter(item => {
                const encontrado = window.checkoutItems.includes(item.id.toString());
                // console.log(`   - Producto ${item.id}: ${encontrado ? 'SELECCIONADO' : 'NO SELECCIONADO'}`);
                return encontrado;
            });
            
            // console.log('🎯 Productos seleccionados para checkout:', productosSeleccionados);
            // console.log('📊 Total de productos seleccionados:', productosSeleccionados.length);
            
            if (productosSeleccionados.length === 0) {
                console.warn('⚠️ No se encontraron los productos seleccionados en el carrito completo');
                // console.log('🔄 Los IDs buscados eran:', window.checkoutItems);
                // console.log('🔄 Los IDs disponibles son:', carritoCompleto.map(item => item.id.toString()));
                
                $resumenProductos.html(`
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        No se encontraron los productos seleccionados en tu carrito.
                        <br>
                        <small class="text-muted">Esto puede ocurrir si los productos fueron eliminados o si la sesión cambió.</small>
                        <br>
                        <button class="btn btn-sm btn-primary mt-2" onclick="window.location.href='carrito.php'">
                            <i class="fas fa-shopping-cart mr-2"></i>Regresar al carrito
                        </button>
                    </div>
                `);
                return;
            }
            
            let html = '';
            productosSeleccionados.forEach((producto, index) => {
                const precioFinalOriginal = parseFloat(producto.precio_final) || parseFloat(producto.precio) || 0;
                const precioUnitarioOriginal = parseFloat(producto.precio_unitario) || precioFinalOriginal;
                const precioFinalConvertido = precioFinalOriginal / window.tasaCambioCheckout;
                const precioUnitarioConvertido = precioUnitarioOriginal / window.tasaCambioCheckout;
                const cantidad = parseInt(producto.cantidad_producto) || 1;
                const subtotalConvertido = precioFinalConvertido * cantidad;
                const tieneDescuento = parseFloat(producto.descuento_porcentaje) > 0;
                
                // console.log(`📦 Producto ${index + 1}:`, {
                //     id: producto.id,
                //     nombre: producto.nombre,
                //     precioFinalOriginal,
                //     precioFinalConvertido,
                //     cantidad,
                //     subtotalConvertido
                // });
                
                html += `
                    <div class="resumen-producto mb-3 p-3 border rounded">
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
                                <strong>${window.simboloMonedaCheckout} ${subtotalConvertido.toFixed(2)}</strong>
                                ${tieneDescuento ? `
                                    <br>
                                    <small class="text-muted text-decoration-line-through">${window.simboloMonedaCheckout} ${precioUnitarioConvertido.toFixed(2)}</small>
                                    <small class="text-success">-${producto.descuento_porcentaje}%</small>
                                ` : ''}
                                <br>
                                <small class="text-muted">${window.simboloMonedaCheckout} ${precioFinalConvertido.toFixed(2)} c/u</small>
                            </div>
                        </div>
                    </div>
                `;
            });
            
            $resumenProductos.html(html);
            // console.log('✅ Resumen de productos actualizado correctamente');
            
        } catch (error) {
            console.error('❌ Error cargando detalles de productos:', error);
            $resumenProductos.html(`
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    Error al cargar los detalles de los productos: ${error.message}
                    <br>
                    <small class="text-muted">Por favor, recarga la página o regresa al carrito.</small>
                    <br>
                    <button class="btn btn-sm btn-primary mt-2" onclick="window.location.reload()">
                        <i class="fas fa-sync-alt mr-2"></i>Recargar página
                    </button>
                    <button class="btn btn-sm btn-secondary mt-2" onclick="window.location.href='carrito.php'">
                        <i class="fas fa-shopping-cart mr-2"></i>Volver al carrito
                    </button>
                </div>
            `);
        }
    }

    // ================= FUNCIONES AUXILIARES =================

    async function cargarDireccionesUsuario() {
        try {
            const response = await $.post('../Controllers/UsuarioMunicipioController.php', { 
                funcion: 'llenar_direcciones' 
            });
            
            const direcciones = typeof response === 'string' ? JSON.parse(response) : response;
            // console.log(direcciones);
            if (direcciones && direcciones.length > 0) {
                let html = `
                    <div class="mb-3">
                        <h6 class="mb-3">Direcciones guardadas:</h6>
                `;
                
                direcciones.forEach((direccion, index) => {
                    // Formatear dirección completa (dirección, municipio, provincia)
                    const direccionCompleta = `${direccion.direccion}, ${direccion.municipio}, ${direccion.provincia}`.trim();
                    
                    if (direccionCompleta && direccionCompleta !== ', ,') {
                        html += `
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" 
                                       name="direccion_guardada" 
                                       id="dir_${index}" 
                                       value="${direccionCompleta.replace(/"/g, '&quot;')}"
                                       onchange="seleccionarDireccionGuardada('${direccionCompleta.replace(/'/g, "\\'")}')">
                                <label class="form-check-label" for="dir_${index}">
                                    <small>${direccionCompleta}</small>
                                </label>
                            </div>
                        `;
                    }
                });
                
                
                
                // Insertar después del campo de dirección
                $('#direccion').closest('.mb-3').after(html);
            }
        } catch (error) {
            console.error('Error cargando direcciones:', error);
            // No mostrar error al usuario, simplemente no mostrar direcciones guardadas
        }
    }

    async function cargarMetodosPago() {
        try {
            const response = await fetch('../Controllers/PagoController.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'funcion=obtener_metodos_pago'
            });
            
            const data = await response.json();
            
            // DEBUG: Ver qué devuelve el servidor
            // console.log('Respuesta métodos de pago:', data);
            
            if (data.success && Array.isArray(data.metodos)) {
                // Limpiar dropdown
                $('#metodo-pago-select').empty();
                
                // Agregar opción por defecto
                $('#metodo-pago-select').append(
                    '<option value="" selected disabled>Selecciona un método de pago</option>'
                );
                
                // Agregar métodos
                data.metodos.forEach((metodo, index) => {
                    let texto = '';
                    
                    switch(metodo.tipo) {
                        case 'tarjeta_credito':
                            texto = `💳 Tarjeta ${metodo.numero_enmascarado || '**** **** **** ****'}`;
                            if (metodo.fecha_vencimiento) {
                                texto += ` (Vence: ${metodo.fecha_vencimiento})`;
                            }
                            break;
                        case 'paypal':
                            texto = `📧 PayPal ${metodo.email_enmascarado || '***@***'}`;
                            break;
                        case 'transferencia':
                            texto = `🏦 ${metodo.banco || 'Transferencia'} ${metodo.cuenta_enmascarada || '****'}`;
                            break;
                        case 'efectivo':
                            texto = `💵 Pago en efectivo`;
                            break;
                        default:
                            texto = `📋 ${metodo.tipo || 'Método de pago'}`;
                    }
                    
                    // Marcar como predeterminado
                    if (metodo.predeterminado) {
                        texto += ' ⭐';
                    }
                    
                    $('#metodo-pago-select').append(
                        `<option value="${metodo.id}" data-tipo="${metodo.tipo}" 
                         ${metodo.predeterminado ? 'selected' : ''}>
                            ${texto} - ${metodo.titular || ''}
                        </option>`
                    );
                });
                
                // Agregar opción para nuevo método
                $('#metodo-pago-select').append(
                    '<option value="nuevo">➕ Agregar nuevo método de pago</option>'
                );
                
                return data.metodos;
            } else {
                console.warn('No se encontraron métodos de pago o formato inválido:', data);
                
                // Limpiar y mostrar mensaje
                $('#metodo-pago-select').empty();
                $('#metodo-pago-select').append(
                    '<option value="" selected>No tienes métodos de pago guardados</option>'
                );
                $('#metodo-pago-select').append(
                    '<option value="nuevo">➕ Agregar nuevo método de pago</option>'
                );
                
                return [];
            }
        } catch (error) {
            console.error('Error cargando métodos de pago:', error);
            
            // Manejo de error en UI
            $('#metodo-pago-select').empty();
            $('#metodo-pago-select').append(
                '<option value="" selected>Error cargando métodos de pago</option>'
            );
            $('#metodo-pago-select').append(
                '<option value="nuevo">➕ Agregar nuevo método de pago</option>'
            );
            
            return [];
        }
    }

    // ================= EVENT LISTENERS =================
    
    // Evento para cambio de moneda
    $(document).on('monedaCambiada', function() {
        // console.log('🎯 Evento monedaCambiada recibido en checkout');
        actualizarMonedaCheckout().then(() => {
            // Si estamos en el paso 3, actualizar también el resumen final
            if ($('#step-content-3').is(':visible')) {
                actualizarResumenFinal();
            }
        });
    });

    // Validación de tarjeta de crédito en tiempo real
    $(document).on('input', 'input[name="tarjeta_numero"]', function() {
        let valor = $(this).val().replace(/\s/g, '');
        valor = valor.replace(/(\d{4})/g, '$1 ').trim();
        $(this).val(valor);
        
        // Validar longitud mínima
        const numerosLimpios = valor.replace(/\s/g, '');
        if (numerosLimpios.length > 16) {
            $(this).val(valor.substring(0, 19)); // 16 dígitos + 3 espacios
        }
    });

    $(document).on('input', 'input[name="tarjeta_vencimiento"]', function() {
        let valor = $(this).val().replace(/\D/g, '');
        if (valor.length >= 2) {
            valor = valor.substring(0, 2) + '/' + valor.substring(2, 4);
        }
        if (valor.length > 5) {
            valor = valor.substring(0, 5);
        }
        $(this).val(valor);
    });

    $(document).on('input', 'input[name="tarjeta_cvv"]', function() {
        let valor = $(this).val().replace(/\D/g, '');
        if (valor.length > 4) {
            valor = valor.substring(0, 4);
        }
        $(this).val(valor);
    });

    // ================= FUNCIONES GLOBALES DENTRO DE $(DOCUMENT).READY =================

    window.siguientePaso = function(paso) {
        // console.log('🔄 Navegando al paso:', paso);
        
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
        
        // Si vamos al paso 3, actualizar TODOS los resúmenes
        if (paso === 3) {
            // console.log('🎯 Actualizando resúmenes para paso 3...');
            actualizarResumenFinal();
        }
    };

    function validarPaso1() {
        // console.log('🔍 Validando Paso 1 (Información de envío)...');
        
        // Obtener valores
        const nombres = $('#nombres').val()?.trim() || '';
        const apellidos = $('#apellidos').val()?.trim() || '';
        const direccion = $('#direccion').val()?.trim() || '';
        const telefono = $('#telefono').val()?.trim() || '';
        const email = $('#email').val()?.trim() || '';
        
        // console.log('📋 Valores obtenidos:', {
        //     nombres, apellidos, direccion, telefono, email
        // });
        
        // Limpiar todos los errores previos
        $('.is-invalid').removeClass('is-invalid');
        $('.campo-error').remove();
        
        let valido = true;
        let errores = [];
        
        // 1. Validar campos obligatorios SIEMPRE
        const camposObligatorios = [
            { id: '#nombres', valor: nombres, mensaje: 'El nombre es obligatorio' },
            { id: '#apellidos', valor: apellidos, mensaje: 'El apellido es obligatorio' },
            { id: '#direccion', valor: direccion, mensaje: 'La dirección es obligatoria' },
            { id: '#telefono', valor: telefono, mensaje: 'El teléfono es obligatorio' }
        ];
        
        camposObligatorios.forEach(campo => {
            if (!campo.valor) {
                $(campo.id).addClass('is-invalid');
                mostrarErrorCampo(campo.id, campo.mensaje);
                errores.push(campo.mensaje);
                valido = false;
            }
        });
        
        // 2. Validar email si se proporcionó
        if (email && !isValidEmail(email)) {
            $('#email').addClass('is-invalid');
            mostrarErrorCampo('#email', 'Email inválido');
            errores.push('Email inválido');
            valido = false;
        }
        
        // 3. Validar longitud mínima de dirección
        if (direccion && direccion.length < 10) {
            $('#direccion').addClass('is-invalid');
            mostrarErrorCampo('#direccion', 'La dirección es muy corta. Por favor, proporciona una dirección completa.');
            errores.push('Dirección incompleta');
            valido = false;
        }
        
        // Mostrar resultado de validación
        if (!valido) {
            // console.log('❌ Errores de validación:', errores);
            
            // Mostrar alerta general con los errores
            let mensajeError = 'Por favor corrige los siguientes errores:<br><ul>';
            errores.forEach(error => {
                mensajeError += `<li>${error}</li>`;
            });
            mensajeError += '</ul>';
            
            Swal.fire({
                icon: 'error',
                title: 'Error en el formulario',
                html: mensajeError,
                confirmButtonText: 'Entendido'
            });
            
            // Desplazar al primer error
            const primerError = $('.is-invalid').first();
            if (primerError.length) {
                $('html, body').animate({
                    scrollTop: primerError.offset().top - 100
                }, 500);
            }
            
        } else {
            // Guardar dirección de envío compuesta
            guardarDireccionEnvio();
            // console.log('✅ Paso 1 validado correctamente');
        }
        
        return valido;
    }

    function mostrarErrorCampo(selector, mensaje) {
        const $campo = $(selector);
        const $error = $(`<small class="text-danger campo-error mt-1 d-block" data-campo="${selector.replace('#', '')}">${mensaje}</small>`);
        
        // Remover error previo si existe
        $(`.campo-error[data-campo="${selector.replace('#', '')}"]`).remove();
        
        // Insertar después del campo
        $campo.after($error);
        
        // Si es select, agregar al contenedor padre
        if ($campo.is('select')) {
            $campo.closest('.form-group, .mb-3').append($error);
        }
    }

    function validarPaso2() {
        const metodoSeleccionado = $('input[name="metodo_pago"]:checked').val() || 
                                  $('input[name="metodo_pago_guardado"]:checked').val();
        
        if (!metodoSeleccionado) {
            mostrarError('Por favor selecciona un método de pago');
            return false;
        }
        
        // Validar formularios específicos según el método de pago
        let valido = true;
        if ($('#metodo_tarjeta').is(':checked') && !validarFormularioTarjeta()) {
            valido = false;
        }
        if ($('#metodo_paypal').is(':checked') && !validarFormularioPayPal()) {
            valido = false;
        }
        if ($('#metodo_transferencia').is(':checked') && !validarFormularioTransferencia()) {
            valido = false;
        }
        
        if (valido) {
            // Guardar datos del método de pago
            guardarDatosPago();
        }
        
        return valido;
    }

    function validarFormularioTarjeta() {
        const required = ['tarjeta_titular', 'tarjeta_numero', 'tarjeta_vencimiento', 'tarjeta_cvv'];
        let valido = true;
        
        // Limpiar errores
        required.forEach(name => {
            $(`input[name="${name}"]`).removeClass('is-invalid');
        });
        
        // Validar campos requeridos
        required.forEach(name => {
            const $input = $(`input[name="${name}"]`);
            const valor = $input.val();
            if (!valor || !valor.toString().trim()) {
                $input.addClass('is-invalid');
                valido = false;
            }
        });
        
        // Validar formato de número de tarjeta
        const numeroTarjeta = $('input[name="tarjeta_numero"]').val().replace(/\s/g, '');
        if (numeroTarjeta.length < 13 || numeroTarjeta.length > 19) {
            $('input[name="tarjeta_numero"]').addClass('is-invalid');
            valido = false;
        }
        
        // Validar fecha de vencimiento (MM/YY)
        const vencimiento = $('input[name="tarjeta_vencimiento"]').val();
        if (vencimiento) {
            const [mes, año] = vencimiento.split('/');
            if (!mes || !año || mes.length !== 2 || año.length !== 2) {
                $('input[name="tarjeta_vencimiento"]').addClass('is-invalid');
                valido = false;
            }
        }
        
        // Validar CVV (3-4 dígitos)
        const cvv = $('input[name="tarjeta_cvv"]').val();
        if (cvv && (cvv.length < 3 || cvv.length > 4)) {
            $('input[name="tarjeta_cvv"]').addClass('is-invalid');
            valido = false;
        }
        
        if (!valido) {
            mostrarError('Por favor completa correctamente todos los campos de la tarjeta');
        }
        
        return valido;
    }

    function validarFormularioPayPal() {
        const $emailInput = $('input[name="paypal_email"]');
        $emailInput.removeClass('is-invalid');
        
        const email = $emailInput.val();
        if (!email || !email.trim()) {
            $emailInput.addClass('is-invalid');
            mostrarError('Por favor ingresa tu email de PayPal');
            return false;
        }
        
        if (!isValidEmail(email)) {
            $emailInput.addClass('is-invalid');
            mostrarError('Por favor ingresa un email válido de PayPal');
            return false;
        }
        
        return true;
    }

    function validarFormularioTransferencia() {
        const required = ['transferencia_banco', 'transferencia_cuenta'];
        let valido = true;
        
        // Limpiar errores
        required.forEach(name => {
            $(`input[name="${name}"]`).removeClass('is-invalid');
        });
        
        // Validar campos requeridos
        required.forEach(name => {
            const $input = $(`input[name="${name}"]`);
            const valor = $input.val();
            if (!valor || !valor.toString().trim()) {
                $input.addClass('is-invalid');
                valido = false;
            }
        });
        
        if (!valido) {
            mostrarError('Por favor completa todos los campos de transferencia');
        }
        
        return valido;
    }

    window.seleccionarMetodoPago = function(tipo) {
        // console.log('Seleccionando método de pago:', tipo);
        
        // Deseleccionar todos los métodos
        $('.payment-method').removeClass('selected');
        $('input[name="metodo_pago"]').prop('checked', false);
        $('input[name="metodo_pago_guardado"]').prop('checked', false);
        $('.payment-form').hide();
        
        // Seleccionar el método clickeado
        $(`#metodo_${tipo}`).prop('checked', true).closest('.payment-method').addClass('selected');
        $(`#form-${tipo}`).show();
    };

    window.seleccionarMetodoGuardado = function(id, tipo) {
        // console.log('Seleccionando método guardado:', id, tipo);
        
        // Deseleccionar todos los métodos nuevos
        $('.payment-method').removeClass('selected');
        $('input[name="metodo_pago"]').prop('checked', false);
        $('input[name="metodo_pago_guardado"]').prop('checked', false);
        $('.payment-form').hide();
        
        // Seleccionar el método guardado
        $(`#metodo_guardado_${id}`).prop('checked', true).closest('.payment-method').addClass('selected');
        
        // Guardar datos del método seleccionado
        window.metodoPagoSeleccionado = { id, tipo };
    };

    window.seleccionarDireccionGuardada = function(direccionCompleta) {
        // console.log('Dirección seleccionada:', direccionCompleta);
        window.direccionEnvioCompleta = direccionCompleta;
        
        // Poner la dirección completa en el textarea
        $('#direccion').val(direccionCompleta);
        
        // También actualizar automáticamente otros campos si están en la dirección
        // (opcional, dependiendo de tu estructura de datos)
        const partes = direccionCompleta.split(', ');
        if (partes.length >= 1) {
            // Opcional: Si quieres extraer componentes específicos
            // Ejemplo: Si la dirección tiene formato "Calle 123, Municipio, Provincia"
            // $('#direccion').val(partes[0]); // Esto ya no es necesario
            
            // Si tienes campos separados para municipio y provincia (aunque no los uses en checkout.php)
            // y quieres llenarlos automáticamente:
            // if (partes.length >= 2) {
            //     $('#municipio').val(partes[1]);
            // }
            // if (partes.length >= 3) {
            //     $('#provincia').val(partes[2]);
            // }
        }
    };

     function guardarDireccionEnvio() {
        const nombres = $('#nombres').val()?.trim() || '';
        const apellidos = $('#apellidos').val()?.trim() || '';
        const direccion = $('#direccion').val()?.trim() || '';
        const telefono = $('#telefono').val()?.trim() || '';
        const email = $('#email').val()?.trim() || '';
        const instrucciones = $('#instrucciones').val()?.trim() || '';
        
        // Construir dirección compuesta
        let direccionCompleta = `${nombres} ${apellidos}`;
        
        if (direccion) {
            direccionCompleta += `, ${direccion}`;
        }
        
        // Agregar contacto
        direccionCompleta += `. Tel: ${telefono}`;
        if (email) {
            direccionCompleta += `, Email: ${email}`;
        }
        if (instrucciones) {
            direccionCompleta += `. Instrucciones: ${instrucciones}`;
        }
        
        window.direccionEnvioCompleta = direccionCompleta;
        // console.log('📍 Dirección de envío guardada:', window.direccionEnvioCompleta);
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
        } else if (metodoSeleccionado && !isNaN(parseInt(metodoSeleccionado))) {
            // Es un método guardado (tiene ID numérico)
            window.datosPago = {
                tipo: 'guardado',
                id: metodoSeleccionado
            };
        }
        
        // console.log('💳 Datos de pago guardados:', window.datosPago);
    }

    function actualizarResumenFinal() {
        // console.log('📋 Actualizando resumen final...');
        
        // Actualizar resumen de envío
        $('#resumen-envio').html(`
            <p class="mb-1"><strong>${$('#nombres').val()} ${$('#apellidos').val()}</strong></p>
            <p class="mb-1">${$('#direccion').val()}</p>
            <p class="mb-1">Tel: ${$('#telefono').val()}</p>
            ${$('#email').val() ? `<p class="mb-1">Email: ${$('#email').val()}</p>` : ''}
            ${$('#instrucciones').val() ? `<p class="mb-1"><em>Instrucciones: ${$('#instrucciones').val()}</em></p>` : ''}
        `);
        
        // Actualizar resumen de pago
        let metodoPagoHtml = '';
        if (window.datosPago.tipo === 'tarjeta') {
            const ultimos4 = window.datosPago.numero ? window.datosPago.numero.slice(-4) : '****';
            metodoPagoHtml = `<p class="mb-0"><i class="far fa-credit-card mr-2"></i>Tarjeta terminada en ${ultimos4}</p>`;
        } else if (window.datosPago.tipo === 'paypal') {
            metodoPagoHtml = `<p class="mb-0"><i class="fab fa-paypal mr-2"></i>PayPal: ${window.datosPago.paypal_email || 'No especificado'}</p>`;
        } else if (window.datosPago.tipo === 'transferencia') {
            metodoPagoHtml = `<p class="mb-0"><i class="fas fa-university mr-2"></i>Transferencia: ${window.datosPago.banco || 'No especificado'}</p>`;
        } else if (window.datosPago.tipo === 'guardado') {
            metodoPagoHtml = `<p class="mb-0"><i class="fas fa-credit-card mr-2"></i>Método de pago guardado (ID: ${window.datosPago.id})</p>`;
        } else {
            metodoPagoHtml = `<p class="mb-0 text-danger"><i class="fas fa-exclamation-triangle mr-2"></i>Método no especificado</p>`;
        }
        
        $('#resumen-pago').html(metodoPagoHtml);
        
        // Actualizar totales en el resumen final
        $('#resumen-subtotal').text(`${window.simboloMonedaCheckout} ${window.checkoutSubtotal.toFixed(2)}`);
        $('#resumen-envio-costo').text(`${window.simboloMonedaCheckout} ${window.checkoutEnvio.toFixed(2)}`);
        $('#resumen-descuento').text(`${window.simboloMonedaCheckout} 0.00`);
        $('#resumen-total').text(`${window.simboloMonedaCheckout} ${window.checkoutTotal.toFixed(2)}`);
        
        // console.log('✅ Resumen final actualizado:', {
        //     subtotal: window.checkoutSubtotal,
        //     envio: window.checkoutEnvio,
        //     total: window.checkoutTotal,
        //     simbolo: window.simboloMonedaCheckout
        // });
    }

    // Función para limpiar transacciones antes de intentar el pago
async function limpiarTransaccionesAntesDePago() {
    try {
        const response = await $.post('../Controllers/PagoController.php', {
            funcion: 'limpiar_transacciones'
        });
        return response.success;
    } catch (error) {
        // console.log('⚠️ No se pudo limpiar transacciones:', error);
        return false;
    }
}

   window.procesarPago = async function() {
    // console.log('🚀 Iniciando procesamiento de pago...');
    
    // PREVENIR DOBLE CLIC DEFINITIVAMENTE
    const btn = $('#btn-procesar-pago');
    if (btn.prop('disabled')) {
        console.log('⚠️ Pago ya en proceso, ignorando clic adicional');
        return;
    }
    
    // Bloquear completamente el botón
    const originalText = btn.html();
    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i>Procesando...');
    
    // También bloquear navegación
    $('.btn-anterior, .btn-siguiente').prop('disabled', true);
    
    // Deshabilitar clic derecho y F5
    $(document).on('contextmenu keydown', function(e) {
        if (e.keyCode === 116 || (e.ctrlKey && e.keyCode === 82)) { // F5 o Ctrl+R
            e.preventDefault();
            return false;
        }
    });
    
    try {
        // Intentar limpiar transacciones antes de comenzar
        await limpiarTransaccionesAntesDePago();
        
        // Validar términos y condiciones
        if (!$('#terminos').is(':checked')) {
            throw new Error('Debes aceptar los términos y condiciones para continuar');
        }
        
        // Validar que hay productos
        if (!window.checkoutItems || window.checkoutItems.length === 0) {
            throw new Error('No hay productos en el carrito');
        }
        
        // Validar dirección
        if (!window.direccionEnvioCompleta) {
            throw new Error('Dirección de envío no especificada');
        }
        
        // Validar método de pago
        if (!window.datosPago || !window.datosPago.tipo) {
            throw new Error('Método de pago no seleccionado');
        }
        
        // Preparar datos para el pago
        const datosPago = {
            funcion: 'procesar_pago',
            datos_pago: JSON.stringify(window.datosPago),
            direccion_envio: window.direccionEnvioCompleta,
            items_seleccionados: JSON.stringify(window.checkoutItems),
            subtotal: window.checkoutSubtotal,
            envio: window.checkoutEnvio,
            total: window.checkoutTotal,
            moneda: window.monedaCheckout
        };
        
        // console.log('📤 Enviando datos de pago al servidor');
        
        // Agregar timeout para evitar esperas infinitas
        const timeoutPromise = new Promise((_, reject) => {
            setTimeout(() => reject(new Error('Timeout: El servidor tardó demasiado en responder')), 30000);
        });
        
        // Hacer la petición con timeout
        const response = await Promise.race([
            $.post('../Controllers/PagoController.php', datosPago),
            timeoutPromise
        ]);
        
        // console.log('📥 Respuesta del servidor recibida');
        
        const resultado = typeof response === 'string' ? JSON.parse(response) : response;
        
        if (resultado.success) {
            await mostrarExitoPago(resultado);
        } else {
            // NO REINTENTAR AUTOMÁTICAMENTE - mostrar error y permitir reintento manual
            throw new Error(resultado.error || 'Error desconocido al procesar el pago');
        }
        
    } catch (error) {
        console.error('❌ Error procesando pago:', error);
        
        // Restaurar controles
        $(document).off('contextmenu keydown');
        $('.btn-anterior, .btn-siguiente').prop('disabled', false);
        
        // Mostrar error con opción de reintentar
        const { value: reintentar } = await Swal.fire({
            icon: 'error',
            title: 'Error en el pago',
            html: `
                <div class="text-left">
                    <p><strong>Error:</strong> ${error.message}</p>
                    <p class="text-muted small">Si el problema persiste, contacta con soporte.</p>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: 'Reintentar',
            cancelButtonText: 'Cancelar',
            allowOutsideClick: false
        });
        
        if (reintentar) {
            // Limpiar completamente el botón y reintentar
            btn.prop('disabled', false).html(originalText);
            setTimeout(() => window.procesarPago(), 1000);
        } else {
            btn.prop('disabled', false).html(originalText);
            // Volver al paso anterior
            siguientePaso(2);
        }
    }
};

    async function mostrarExitoPago(resultado) {
    // console.log('✅ Pago exitoso, limpiando datos...');
    
    // Limpiar sessionStorage
    sessionStorage.removeItem('checkoutItems');
    sessionStorage.removeItem('checkoutSubtotal');
    sessionStorage.removeItem('checkoutEnvio');
    sessionStorage.removeItem('checkoutTotal');
    
    // También limpiar localStorage de carrito si existe
    if (typeof actualizarContadorCarrito === 'function') {
        localStorage.removeItem('carrito_cantidad');
    }
    
    // Habilitar botones nuevamente (por si acaso)
    $('#btn-procesar-pago').prop('disabled', false);
    $('.btn-anterior').prop('disabled', false);
    
    // Mostrar mensaje de éxito
    await Swal.fire({
        icon: 'success',
        title: '¡Pago Exitoso!',
        html: `
            <div class="text-center">
                <i class="fas fa-check-circle text-success fa-4x mb-3"></i>
                <h4>¡Gracias por tu compra!</h4>
                <p class="mb-2">Tu pedido ha sido procesado exitosamente.</p>
                <p><strong>Número de orden:</strong> ${resultado.numero_orden}</p>
                <p><strong>Total:</strong> ${window.simboloMonedaCheckout} ${resultado.total.toFixed(2)}</p>
                <p><strong>Fecha:</strong> ${new Date(resultado.fecha).toLocaleString()}</p>
                <div class="alert alert-info mt-3">
                    <small>
                        <i class="fas fa-info-circle mr-2"></i>
                        Recibirás un email de confirmación con los detalles de tu pedido.
                        ${resultado.transaction_id ? `<br>ID de transacción: ${resultado.transaction_id}` : ''}
                    </small>
                </div>
            </div>
        `,
        confirmButtonText: 'Ver Mis Pedidos',
        showCancelButton: true,
        cancelButtonText: 'Seguir Comprando',
        allowOutsideClick: false,
        allowEscapeKey: false,
        allowEnterKey: false
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = 'mis_pedidos.php';
        } else {
            window.location.href = 'producto.php';
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

}); // FIN DE $(document).ready