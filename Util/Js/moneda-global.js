

// Inicializar moneda al cargar la página
$(document).ready(function() {
    // Establecer moneda inicial
    const monedaGuardada = localStorage.getItem('moneda-seleccionada') || 'CUP';
    $('#moneda-interface').val(monedaGuardada);
    
    // Actualizar precios con la moneda guardada después de un delay
    setTimeout(() => {
        actualizarPreciosMoneda(monedaGuardada);
    }, 1000);


    // Variables globales para la moneda
let monedaActual = localStorage.getItem('moneda-seleccionada') || 'CUP';
let simboloMonedaActual = '$';

// Función principal para actualizar precios en toda la aplicación
async function actualizarPreciosMoneda(codigoMoneda) {
    try {
        // Mostrar loading en los precios
        mostrarLoadingPrecios();

        const response = await $.post('Controllers/MonedaController.php', {
            funcion: 'convertir_precios_productos',
            moneda: codigoMoneda
        });
        
        const data = typeof response === 'string' ? JSON.parse(response) : response;
        
        if (data.success) {
            monedaActual = codigoMoneda;
            simboloMonedaActual = data.moneda.simbolo;
            
            // Actualizar precios en todas las vistas
            await actualizarPreciosIndex(data.productos);
            await actualizarPreciosCarrito();
            await actualizarPreciosCheckout();
            await actualizarPreciosFavoritos();
            await actualizarPreciosDescripcion();
            await actualizarPreciosPedidos();
            
            // Actualizar símbolos en toda la página
            actualizarSimbolosMoneda();
            
            //console.log('Precios actualizados a moneda:', codigoMoneda);
        } else {
            console.error('Error actualizando precios:', data.error);
            restaurarPreciosOriginales();
        }
    } catch (error) {
        console.error('Error actualizando precios:', error);
        restaurarPreciosOriginales();
    }
}

// Función para mostrar loading en los precios
function mostrarLoadingPrecios() {
    $('.precio-producto, .text-danger h4, .precio-original, .resumen-lateral-total').each(function() {
        const $element = $(this);
        if (!$element.hasClass('no-loading')) {
            $element.data('original-html', $element.html());
            $element.html('<i class="fas fa-spinner fa-spin"></i>');
        }
    });
}

// Función para restaurar precios originales en caso de error
function restaurarPreciosOriginales() {
    $('.precio-producto, .text-danger h4, .precio-original').each(function() {
        const $element = $(this);
        const originalHtml = $element.data('original-html');
        if (originalHtml) {
            $element.html(originalHtml);
        }
    });
}

// Función para actualizar símbolos de moneda en toda la página
function actualizarSimbolosMoneda() {
    // Actualizar símbolos en elementos de precio
    $('h4.text-danger, .text-danger.font-weight-bold, .precio-producto strong').each(function() {
        const $element = $(this);
        const texto = $element.text();
        
        // Solo actualizar si no tiene el símbolo correcto
        if (!texto.includes(simboloMonedaActual)) {
            const soloNumero = texto.replace(/[^\d.,]/g, '').trim();
            if (soloNumero) {
                $element.text(`${simboloMonedaActual} ${soloNumero}`);
            }
        }
    });
}

// ================= FUNCIONES ESPECÍFICAS POR VISTA =================

// Función para actualizar precios en el index/tienda
async function actualizarPreciosIndex(productos) {
    if ($("#productos").length > 0) {
        productos.forEach(producto => {
            const precioFinal = producto.precio_descuento_convertido || producto.precio_convertido;
            const precioOriginal = producto.precio_convertido;
            
            // Actualizar en cards de productos
            $(`.product-card:contains("${producto.producto}")`).each(function() {
                const $card = $(this);
                
                // Actualizar precio con descuento
                $card.find('h4.text-danger').text(`${simboloMonedaActual} ${precioFinal.toFixed(2)}`);
                
                // Actualizar precio tachado si hay descuento
                if (producto.descuento > 0) {
                    $card.find('span[style*="line-through"]').text(`${simboloMonedaActual} ${precioOriginal.toFixed(2)}`);
                }
            });
        });
    }
}

// Función para actualizar precios en el carrito
async function actualizarPreciosCarrito() {
    if ($('#articulos').length > 0 && typeof window.carritoItems !== 'undefined') {
        try {
            const response = await $.post('Controllers/MonedaController.php', {
                funcion: 'convertir_precios_productos',
                moneda: monedaActual,
                productos: JSON.stringify(window.carritoItems)
            });
            
            const data = typeof response === 'string' ? JSON.parse(response) : response;
            
            if (data.success) {
                data.productos.forEach(producto => {
                    const precioUnitario = producto.precio_unitario_convertido || producto.precio_convertido;
                    const subtotal = precioUnitario * (producto.cantidad_producto || 1);
                    
                    // Actualizar en el carrito
                    $(`.articulo-item[data-id="${producto.id}"]`).each(function() {
                        const $item = $(this);
                        
                        // Actualizar precio unitario
                        $item.find('.precio-producto strong').text(`${simboloMonedaActual} ${subtotal.toFixed(2)}`);
                        $item.find('.precio-producto small').text(`${simboloMonedaActual} ${precioUnitario.toFixed(2)} c/u`);
                        
                        // Actualizar checkbox data
                        $item.find('.seleccionar-item').data('precio', precioUnitario);
                    });
                });
                
                // Recalcular resumen
                if (typeof actualizarResumen === 'function') {
                    await actualizarResumen();
                }
            }
        } catch (error) {
            console.error('Error actualizando precios del carrito:', error);
        }
    }
}

// Función para actualizar precios en checkout
async function actualizarPreciosCheckout() {
    if ($('#resumen-lateral-total').length > 0) {
        try {
            const tasaResponse = await $.post('Controllers/MonedaController.php', {
                funcion: 'obtener_tasa_cambio',
                moneda: monedaActual
            });
            
            const tasaData = typeof tasaResponse === 'string' ? JSON.parse(tasaResponse) : tasaResponse;
            
            if (tasaData.success) {
                const tasa = parseFloat(tasaData.tasa_cambio);
                
                // Convertir totales
                if (window.checkoutSubtotal) {
                    const subtotalConvertido = window.checkoutSubtotal * tasa;
                    const envioConvertido = window.checkoutEnvio * tasa;
                    const totalConvertido = window.checkoutTotal * tasa;
                    
                    $('#resumen-lateral-subtotal').text(`${simboloMonedaActual} ${subtotalConvertido.toFixed(2)}`);
                    $('#resumen-lateral-envio').text(`${simboloMonedaActual} ${envioConvertido.toFixed(2)}`);
                    $('#resumen-lateral-total').text(`${simboloMonedaActual} ${totalConvertido.toFixed(2)}`);
                    
                    // Actualizar en el paso 3 si está visible
                    $('#resumen-subtotal').text(`${simboloMonedaActual} ${subtotalConvertido.toFixed(2)}`);
                    $('#resumen-envio-costo').text(`${simboloMonedaActual} ${envioConvertido.toFixed(2)}`);
                    $('#resumen-total').text(`${simboloMonedaActual} ${totalConvertido.toFixed(2)}`);
                }
            }
        } catch (error) {
            console.error('Error actualizando precios checkout:', error);
        }
    }
}

// Función para actualizar precios en favoritos
function actualizarPreciosFavoritos() {
    if (typeof window.favoritos !== 'undefined' && $('#lista-favoritos').length > 0) {
        window.favoritos.forEach(producto => {
            const precioFinal = producto.precio_descuento_convertido || producto.precio_convertido;
            const precioOriginal = producto.precio_convertido;
            
            // Actualizar en vista grid
            $(`.favorito-card:contains("${producto.producto}")`).each(function() {
                const $card = $(this);
                
                // Actualizar precios
                $card.find('.text-danger.font-weight-bold').text(`${simboloMonedaActual} ${precioFinal.toFixed(2)}`);
                
                if (producto.descuento > 0) {
                    $card.find('.precio-original').text(`${simboloMonedaActual} ${precioOriginal.toFixed(2)}`);
                }
            });
        });
    }
}

// Función para actualizar precios en descripción de producto
async function actualizarPreciosDescripcion() {
    if ($('#informacion_precios').length > 0) {
        try {
            const tasaResponse = await $.post('Controllers/MonedaController.php', {
                funcion: 'obtener_tasa_cambio',
                moneda: monedaActual
            });
            
            const tasaData = typeof tasaResponse === 'string' ? JSON.parse(tasaResponse) : tasaResponse;
            
            if (tasaData.success) {
                const tasa = parseFloat(tasaData.tasa_cambio);
                
                // Obtener precios originales del producto
                const $precioElement = $('#informacion_precios');
                const precioOriginal = parseFloat($('#precio-original-hidden').val()) || 0;
                const precioDescuentoOriginal = parseFloat($('#precio-descuento-hidden').val()) || precioOriginal;
                const descuento = parseInt($('#descuento-hidden').val()) || 0;
                
                const precioConvertido = precioOriginal * tasa;
                const precioDescuentoConvertido = precioDescuentoOriginal * tasa;
                
                // Reconstruir el template con nuevos precios
                let nuevoTemplate = '';
                
                // Aquí reconstruyes el mismo formato que tienes en tu descripcion.js
                if (descuento != 0) {
                    nuevoTemplate += `
                        <span class="text-muted" style="text-decoration: line-through">${simboloMonedaActual} ${precioConvertido.toFixed(2)}</span>
                        <span class="text-muted">-${descuento}%</span></br>
                    `;
                }
                
                nuevoTemplate += `           
                    <h4 class="text-danger">${simboloMonedaActual} ${precioDescuentoConvertido.toFixed(2)}</h4>`;
                
                $precioElement.html(nuevoTemplate);
            }
        } catch (error) {
            console.error('Error actualizando precios descripción:', error);
        }
    }
}

// Función para actualizar precios en pedidos
function actualizarPreciosPedidos() {
    if ($('.pedido-card').length > 0) {
        // Actualizar símbolos en los pedidos
        $('.pedido-card .text-danger, .resumen .text-danger').each(function() {
            const $element = $(this);
            const textoOriginal = $element.text();
            const soloNumero = textoOriginal.replace('$', '').trim();
            $element.text(`${simboloMonedaActual} ${soloNumero}`);
        });
    }
}

});