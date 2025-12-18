$(document).ready(function () {
    //console.log("Mis Pedidos inicializado");
    let pedidosOriginales = [];
    let pedidosFiltrados = [];
    let monedaActual = localStorage.getItem("moneda-seleccionada") || "CUP";
    let simboloMonedaActual = "$";
    let tasaCambioActual = 1;
    
    // Cargar pedidos al iniciar
    cargarPedidos();

    // Evento para filtro de estado
    $("#filtro-estado").change(function () {
        cargarPedidos($(this).val());
    });

    // Evento para filtro de orden
    $("#filtro-orden").change(function () {
        aplicarOrdenamiento($(this).val());
    });

    async function cargarPedidos(filtroEstado = "") {
        //console.log("Cargando pedidos con filtro:", filtroEstado);

        try {
            // Mostrar loading
            mostrarLoading();
            $("#estado-vacio").hide();

            const response = await $.post("../Controllers/PedidoController.php", {
                funcion: "obtener_pedidos_usuario",
                filtro_estado: filtroEstado,
            });

            const data = typeof response === "string" ? JSON.parse(response) : response;

            if (data.error === "no_sesion") {
                window.location.href = "login.php";
                return;
            }

            if (data.success && data.pedidos.length > 0) {
                pedidosOriginales = data.pedidos;
                pedidosFiltrados = [...pedidosOriginales];
                
                // Aplicar ordenamiento actual si existe
                const ordenSeleccionado = $("#filtro-orden").val();
                if (ordenSeleccionado) {
                    aplicarOrdenamiento(ordenSeleccionado);
                } else {
                    renderizarPedidos(pedidosFiltrados);
                }
                
                await actualizarPreciosMisPedidos();
                $("#estado-vacio").hide();
                $("#lista-pedidos").show();
            } else {
                mostrarEstadoVacio();
            }
        } catch (error) {
            console.error("Error cargando pedidos:", error);
            mostrarError("Error al cargar los pedidos");
            mostrarEstadoVacio();
        }
    }

    function aplicarOrdenamiento(tipoOrden) {
        if (!pedidosFiltrados || pedidosFiltrados.length === 0) return;

        // Crear copia para no modificar el original
        let pedidosOrdenados = [...pedidosFiltrados];

        switch (tipoOrden) {
            case "recientes":
                // Más recientes primero (fecha descendente)
                pedidosOrdenados.sort((a, b) => 
                    new Date(b.fecha_creacion) - new Date(a.fecha_creacion)
                );
                break;

            case "antiguos":
                // Más antiguos primero (fecha ascendente)
                pedidosOrdenados.sort((a, b) => 
                    new Date(a.fecha_creacion) - new Date(b.fecha_creacion)
                );
                break;

            case "total-asc":
                // Total: Menor a Mayor
                pedidosOrdenados.sort((a, b) => 
                    (a.total || 0) - (b.total || 0)
                );
                break;

            case "total-desc":
                // Total: Mayor a Menor
                pedidosOrdenados.sort((a, b) => 
                    (b.total || 0) - (a.total || 0)
                );
                break;

            case "estado":
                // Orden por estado (personalizado)
                const ordenEstados = {
                    'pendiente': 1,
                    'confirmada': 2,
                    'procesando': 3,
                    'enviada': 4,
                    'entregada': 5,
                    'cancelada': 6,
                    'reembolsada': 7
                };
                
                pedidosOrdenados.sort((a, b) => 
                    (ordenEstados[a.estado] || 99) - (ordenEstados[b.estado] || 99)
                );
                break;

            case "productos-asc":
                // Productos: Menor a Mayor cantidad
                pedidosOrdenados.sort((a, b) => 
                    (a.total_cantidad || 0) - (b.total_cantidad || 0)
                );
                break;

            case "productos-desc":
                // Productos: Mayor a Menor cantidad
                pedidosOrdenados.sort((a, b) => 
                    (b.total_cantidad || 0) - (a.total_cantidad || 0)
                );
                break;

            default:
                // Orden por defecto (recientes)
                pedidosOrdenados.sort((a, b) => 
                    new Date(b.fecha_creacion) - new Date(a.fecha_creacion)
                );
        }

        // Actualizar la lista filtrada
        pedidosFiltrados = pedidosOrdenados;
        
        // Renderizar pedidos ordenados
        renderizarPedidos(pedidosFiltrados);
        actualizarPreciosMisPedidos();
        
        // Mostrar notificación de ordenamiento
        mostrarNotificacionOrden(tipoOrden);
    }

    function mostrarNotificacionOrden(tipoOrden) {
        const mensajes = {
            'recientes': 'Ordenado: Más recientes primero',
            'antiguos': 'Ordenado: Más antiguos primero',
            'total-asc': 'Ordenado: Total menor a mayor',
            'total-desc': 'Ordenado: Total mayor a menor',
            'estado': 'Ordenado por estado',
            'productos-asc': 'Ordenado: Menos productos primero',
            'productos-desc': 'Ordenado: Más productos primero'
        };

        const mensaje = mensajes[tipoOrden] || 'Orden aplicado';
        
        // Mostrar notificación sutil
        const $notificacion = $(`
            <div class="alert alert-info alert-dismissible fade show position-fixed" 
                style="top: 80px; right: 20px; z-index: 1050; max-width: 300px;">
                <i class="fas fa-sort-amount-down mr-2"></i>
                ${mensaje}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `);
        
        $('body').append($notificacion);
        
        // Auto-eliminar después de 3 segundos
        setTimeout(() => {
            $notificacion.alert('close');
        }, 3000);
    }

    function mostrarLoading() {
        $("#lista-pedidos").html(`
            <div class="loading-orders">
                <div class="loading-spinner"></div>
                <p class="text-muted">Cargando tus pedidos...</p>
            </div>
        `);
    }

    function renderizarPedidos(pedidos) {
        let html = "";

        pedidos.forEach((pedido) => {
            const fecha = new Date(pedido.fecha_creacion).toLocaleDateString(
                "es-ES",
                {
                    year: "numeric",
                    month: "long",
                    day: "numeric",
                    hour: "2-digit",
                    minute: "2-digit",
                }
            );

            // Usar los valores del controlador
            const subtotal = pedido.subtotal || 0;
            const descuento = pedido.descuento || 0;
            const envio = pedido.costo_envio || 0;
            const impuestos = pedido.impuestos || 0;
            const total = pedido.total || 0;
            const subtotalProductos = pedido.subtotal_productos || subtotal;

            // Usar símbolo actual
            html += `
                <div class="card pedido-card mb-4">
                    <div class="card-header">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <h6 class="mb-0">
                                    <i class="fas fa-receipt mr-2"></i>
                                    Pedido #${pedido.numero_orden}
                                </h6>
                                <small class="text-muted">Realizado el ${fecha}</small>
                            </div>
                            <div class="col-md-3 text-center">
                                <span class="${pedido.estado_clase} estado-pedido">
                                    ${pedido.estado_texto}
                                </span>
                            </div>
                            <div class="col-md-3 text-right">
                                <strong class="h5 text-danger" data-precio-original="${total}">
                                    ${simboloMonedaActual} ${(total / tasaCambioActual).toFixed(2)}
                                </strong>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8">
                                <h6>Productos (${pedido.total_cantidad || 0})</h6>
                                <div class="row">
                `;
            
            // Verificar si hay detalles
            const detalles = pedido.detalles || [];
            const detallesParaMostrar = detalles.slice(0, 3);
            
            if (detallesParaMostrar.length > 0) {
                detallesParaMostrar.forEach((detalle) => {
                    const imagen = detalle.imagen || "default_product.png";
                    const productoNombre = detalle.producto_nombre || "Producto";
                    
                    html += `
                        <div class="col-4 mb-2">
                            <div class="d-flex align-items-center">
                                <img src="../Util/Img/Producto/${imagen}" 
                                    alt="${productoNombre}" 
                                    class="producto-img mr-2"
                                    onerror="this.src='../Util/Img/Producto/default_product.png'">
                                <div>
                                    <small class="d-block font-weight-bold">${productoNombre}</small>
                                    <small class="text-muted">Cantidad: ${detalle.cantidad || 1}</small>
                                </div>
                            </div>
                        </div>
                    `;
                });
            } else {
                html += `
                        <div class="col-12">
                            <p class="text-muted">No hay productos disponibles</p>
                        </div>
                    `;
            }

            // Si hay más de 3 productos, mostrar indicador
            if (detalles.length > 3) {
                html += `
                        <div class="col-4 mb-2">
                            <div class="d-flex align-items-center justify-content-center h-100">
                                <span class="badge badge-light">+${detalles.length - 3} más</span>
                            </div>
                        </div>
                    `;
            }

            html += `
                                </div>
                            </div>
                            <div class="col-md-4 border-left">
                                <h6>Resumen</h6>
                                <div class="small">
                                    <div class="d-flex justify-content-between">
                                        <span>Subtotal productos:</span>
                                        <span data-precio-original="${subtotalProductos}">
                                            ${simboloMonedaActual} ${(subtotalProductos / tasaCambioActual).toFixed(2)}
                                        </span>
                                    </div>
                                    ${descuento > 0 ? `
                                    <div class="d-flex justify-content-between">
                                        <span>Descuento:</span>
                                        <span data-precio-original="${descuento}">
                                            -${simboloMonedaActual} ${(descuento / tasaCambioActual).toFixed(2)}
                                        </span>
                                    </div>
                                    ` : ''}
                                    <div class="d-flex justify-content-between">
                                        <span>Envío:</span>
                                        <span data-precio-original="${envio}">
                                            ${simboloMonedaActual} ${(envio / tasaCambioActual).toFixed(2)}
                                        </span>
                                    </div>
                                    ${impuestos > 0 ? `
                                    <div class="d-flex justify-content-between">
                                        <span>Impuestos:</span>
                                        <span data-precio-original="${impuestos}">
                                            ${simboloMonedaActual} ${(impuestos / tasaCambioActual).toFixed(2)}
                                        </span>
                                    </div>
                                    ` : ''}
                                    <hr>
                                    <div class="d-flex justify-content-between font-weight-bold">
                                        <span>Total:</span>
                                        <span data-precio-original="${total}">
                                            ${simboloMonedaActual} ${(total / tasaCambioActual).toFixed(2)}
                                        </span>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <button class="btn btn-outline-primary btn-sm btn-block" 
                                            onclick="verDetallesPedido(${pedido.id})">
                                        <i class="fas fa-eye mr-1"></i> Ver Detalles
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });

        $("#lista-pedidos").html(html);
    }

    function mostrarEstadoVacio() {
        $("#lista-pedidos").hide();
        $("#estado-vacio").show();
    }

    function mostrarError(mensaje) {
        Swal.fire({
            icon: "error",
            title: "Error",
            text: mensaje,
            confirmButtonText: "Entendido",
        });
    }

    async function actualizarPreciosMisPedidos() {
        try {
            //console.log('🔄 Actualizando precios en Mis Pedidos...');
            
            const monedaSeleccionada = localStorage.getItem('moneda-seleccionada') || 'CUP';
            const response = await $.post('../Controllers/MonedaController.php', {
                funcion: 'obtener_tasa_cambio',
                moneda: monedaSeleccionada
            });
            
            const data = typeof response === 'string' ? JSON.parse(response) : response;
            
            if (data.success) {
                tasaCambioActual = parseFloat(data.tasa_cambio) || 1;
                
                if (data.moneda && typeof data.moneda === 'object') {
                    simboloMonedaActual = data.moneda.simbolo || '$';
                }
                
                // Actualizar todos los precios en las tarjetas
                $('[data-precio-original]').each(function() {
                    const $element = $(this);
                    const precioOriginal = parseFloat($element.data('precio-original')) || 0;
                    const precioConvertido = (precioOriginal / tasaCambioActual).toFixed(2);
                    $element.text(`${simboloMonedaActual} ${precioConvertido}`);
                });
                
                //console.log('✅ Precios actualizados en Mis Pedidos');
            } else {
                console.warn('Usando moneda por defecto (CUP)');
                simboloMonedaActual = '$';
                tasaCambioActual = 1;
            }
        } catch (error) {
            console.error('❌ Error actualizando precios:', error);
            simboloMonedaActual = '$';
            tasaCambioActual = 1;
        }
    }

    // Función para buscar pedidos por número o producto
    function buscarPedidos(termino) {
        if (!termino.trim()) {
            pedidosFiltrados = [...pedidosOriginales];
            renderizarPedidos(pedidosFiltrados);
            return;
        }

        const terminoLower = termino.toLowerCase();
        
        pedidosFiltrados = pedidosOriginales.filter(pedido => {
            // Buscar por número de pedido
            if (pedido.numero_orden && pedido.numero_orden.toLowerCase().includes(terminoLower)) {
                return true;
            }
            
            // Buscar en detalles de productos
            if (pedido.detalles && pedido.detalles.length > 0) {
                return pedido.detalles.some(detalle => 
                    detalle.producto_nombre && detalle.producto_nombre.toLowerCase().includes(terminoLower)
                );
            }
            
            return false;
        });

        renderizarPedidos(pedidosFiltrados);
        
        // Mostrar mensaje si no hay resultados
        if (pedidosFiltrados.length === 0) {
            $("#lista-pedidos").html(`
                <div class="empty-orders">
                    <div class="empty-orders-icon">
                        <i class="fas fa-search"></i>
                    </div>
                    <h3 class="empty-orders-title">No se encontraron pedidos</h3>
                    <p class="empty-orders-text">
                        No hay pedidos que coincidan con "${termino}"
                    </p>
                </div>
            `);
        }
    }

    // Agregar campo de búsqueda dinámicamente
    function agregarCampoBusqueda() {
        // Verificar si ya existe el campo de búsqueda
        if ($('#buscar-pedido').length > 0) return;
        
        const $searchContainer = $(`
            <div class="col-md-4">
                <div class="filter-group">
                    <label class="filter-label">Buscar pedido</label>
                    <div class="input-group">
                        <input type="text" 
                            class="form-control-modern" 
                            id="buscar-pedido" 
                            placeholder="Número o producto...">
                        <div class="input-group-append">
                            <button class="btn btn-outline-secondary" type="button" id="btn-buscar">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `);
        
        // Asegurar que se agregue al contenedor correcto
        const $row = $(".filters-section .row:first");
        if ($row.length > 0) {
            $row.append($searchContainer);
            
            // Evento para buscar
            $("#btn-buscar").click(function() {
                const termino = $("#buscar-pedido").val();
                buscarPedidos(termino);
            });
            
            // Buscar al presionar Enter
            $("#buscar-pedido").keypress(function(e) {
                if (e.which === 13) {
                    buscarPedidos($(this).val());
                }
            });
        }
    }

    // Escuchar cambios de moneda
    $(document).on('monedaCambiada', function() {
        actualizarPreciosMisPedidos();
    });

    // Inicializar
    $(window).on('load', function() {
        setTimeout(() => {
            actualizarPreciosMisPedidos();
            agregarCampoBusqueda();
        }, 100);
    });

});

// FUNCIONES GLOBALES

// Función global para ver detalles del pedido
async function verDetallesPedido(idPedido) {
    //console.log("Viendo detalles del pedido:", idPedido);

    try {
        const response = await $.post("../Controllers/PedidoController.php", {
            funcion: "obtener_detalles_pedido",
            id_pedido: idPedido,
        });

        const data = typeof response === "string" ? JSON.parse(response) : response;

        if (data.success) {
            await mostrarModalDetalles(data.pedido, data.detalles || [], data.transacciones || []);
        } else {
            throw new Error(data.error || "Error al cargar detalles");
        }
    } catch (error) {
        console.error("Error cargando detalles:", error);
        Swal.fire(
            "Error",
            "No se pudieron cargar los detalles del pedido",
            "error"
        );
    }
}

// Variable global para almacenar datos de la factura
window.facturaData = {
    pedido: null,
    detalles: [],
    simbolo: '$',
    tasa: 1,
    fechaFormateada: '',
    numeroFactura: ''
};

async function mostrarModalDetalles(pedido, detalles, transacciones) {
    const fecha = new Date(pedido.fecha_creacion).toLocaleDateString("es-ES", {
        year: "numeric",
        month: "long",
        day: "numeric",
        hour: "2-digit",
        minute: "2-digit",
    });

    try {
        // Obtener moneda actual y tasa de cambio
        const monedaActual = localStorage.getItem('moneda-seleccionada') || 'CUP';
        
        const response = await $.post("../Controllers/MonedaController.php", {
            funcion: "obtener_tasa_cambio",
            moneda: monedaActual
        });

        const data = typeof response === 'string' ? JSON.parse(response) : response;
        
        let simbolo = '$';
        let tasa = 1;
        let codigoMoneda = 'CUP';

        if (data.success) {
            simbolo = data.moneda?.simbolo || '$';
            tasa = parseFloat(data.tasa_cambio) || 1;
            codigoMoneda = data.moneda?.codigo || 'CUP';
        }

        // Guardar datos en variable global para PDF
        window.facturaData = {
            pedido: pedido,
            detalles: detalles,
            simbolo: simbolo,
            tasa: tasa,
            fechaFormateada: fecha,
            numeroFactura: `FAC-${pedido.numero_orden}-${Date.now().toString().slice(-6)}`,
            codigoMoneda: codigoMoneda,
            transacciones: transacciones || []
        };

        // Calcular subtotal de productos
        let subtotalDetalles = 0;
        detalles.forEach(detalle => {
            subtotalDetalles += parseFloat(detalle.subtotal) || 0;
        });

        const envioOriginal = parseFloat(pedido.costo_envio) || 0;
        const descuentoOriginal = parseFloat(pedido.descuento) || 0;
        const impuestosOriginal = parseFloat(pedido.impuestos) || 0;
        const totalOriginal = parseFloat(pedido.total) || 0;
        
        // Precios convertidos
        const envioConvertido = envioOriginal / tasa;
        const descuentoConvertido = descuentoOriginal / tasa;
        const impuestosConvertido = impuestosOriginal / tasa;
        const subtotalConvertido = subtotalDetalles / tasa;
        const totalConvertido = totalOriginal / tasa;

        // Estructura de factura profesional
        let html = `
        <div class="factura-container" id="factura-content">
            <!-- Encabezado de la factura -->
            <div class="factura-header mb-4">
                <div class="row">
                    <div class="col-md-6">
                        <div class="logo-factura mb-3">
                            <h2 class="text-primary mb-1">NexusBuy</h2>
                            <p class="text-muted mb-0">Tu tienda online de confianza</p>
                        </div>
                        <div class="info-empresa">
                            <p class="mb-1"><strong>Dirección:</strong> Avenida Principal #123</p>
                            <p class="mb-1"><strong>Teléfono:</strong> +53555123456</p>
                            <p class="mb-1"><strong>Email:</strong> ventas@nexusbuy.com</p>
                            <p class="mb-0"><strong>NIT:</strong> 123-456789-0</p>
                        </div>
                    </div>
                    <div class="col-md-6 text-right">
                        <div class="factura-info bg-light p-3 rounded">
                            <h4 class="text-primary mb-2">FACTURA</h4>
                            <p class="mb-1"><strong>No. Factura:</strong> ${window.facturaData.numeroFactura}</p>
                            <p class="mb-1"><strong>Pedido:</strong> ${pedido.numero_orden || 'N/A'}</p>
                            <p class="mb-1"><strong>Fecha:</strong> ${fecha}</p>
                            <p class="mb-0"><strong>Estado:</strong> <span class="estado-pedido ${getEstadoClass(pedido.estado)}">${getEstadoTexto(pedido.estado)}</span></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Información del cliente -->
            <div class="factura-cliente mb-4">
                <div class="row">
                    <div class="col-md-6">
                        <h5 class="border-bottom pb-2 mb-3">INFORMACIÓN DEL CLIENTE</h5>
                        <p class="mb-1"><strong>Nombre:</strong> ${pedido.nombres || ''} ${pedido.apellidos || ''}</p>
                        <p class="mb-1"><strong>Dirección:</strong> ${pedido.direccion || "No especificada"}</p>
                        <p class="mb-1"><strong>Municipio:</strong> ${pedido.municipio_nombre || "N/A"}</p>
                        <p class="mb-1"><strong>Provincia:</strong> ${pedido.provincia_nombre || "N/A"}</p>
                        <p class="mb-1"><strong>Teléfono:</strong> ${pedido.telefono_contacto || "N/A"}</p>
                        ${pedido.codigo_postal ? `<p class="mb-0"><strong>Código Postal:</strong> ${pedido.codigo_postal}</p>` : ''}
                    </div>
                    <div class="col-md-6">
                        <h5 class="border-bottom pb-2 mb-3">INFORMACIÓN DE ENVÍO</h5>
                        <p class="mb-1"><strong>Dirección de envío:</strong> ${pedido.direccion || "No especificada"}</p>
                        <p class="mb-1"><strong>Alias:</strong> ${pedido.direccion_alias || "Principal"}</p>
                        ${pedido.fecha_entrega_estimada ? `<p class="mb-1"><strong>Fecha estimada:</strong> ${new Date(pedido.fecha_entrega_estimada).toLocaleDateString()}</p>` : ''}
                        ${pedido.fecha_entrega_real ? `<p class="mb-1"><strong>Fecha de entrega:</strong> ${new Date(pedido.fecha_entrega_real).toLocaleDateString()}</p>` : ''}
                        ${pedido.codigo_seguimiento ? `<p class="mb-0"><strong>Código seguimiento:</strong> ${pedido.codigo_seguimiento}</p>` : ''}
                    </div>
                </div>
            </div>

            <!-- Detalles de productos -->
            <div class="factura-productos mb-4">
                <h5 class="border-bottom pb-2 mb-3">DETALLES DE PRODUCTOS</h5>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="bg-light">
                            <tr>
                                <th width="5%">#</th>
                                <th width="45%">DESCRIPCIÓN</th>
                                <th width="10%" class="text-center">CANT.</th>
                                <th width="20%" class="text-right">PRECIO UNIT.</th>
                                <th width="20%" class="text-right">SUBTOTAL</th>
                            </tr>
                        </thead>
                        <tbody>
        `;

        // Productos
        detalles.forEach((detalle, index) => {
            const precioUnitarioOriginal = parseFloat(detalle.precio_unitario) || 0;
            const subtotalProductoOriginal = parseFloat(detalle.subtotal) || 0;
            const cantidad = parseInt(detalle.cantidad) || 1;
            
            const precioUnitarioConvertido = precioUnitarioOriginal / tasa;
            const subtotalProductoConvertido = subtotalProductoOriginal / tasa;

            html += `
                <tr>
                    <td>${index + 1}</td>
                    <td>
                        <div class="d-flex align-items-center">
                            <div>
                                <strong>${detalle.producto_nombre || 'Producto'}</strong>
                                <div class="text-muted small">
                                    ${detalle.marca_nombre || 'Sin marca'}
                                    ${detalle.categoria_nombre ? ` • ${detalle.categoria_nombre}` : ''}
                                    ${detalle.tienda_nombre ? `<br>Tienda: ${detalle.tienda_nombre}` : ''}
                                </div>
                            </div>
                        </div>
                    </td>
                    <td class="text-center">${cantidad}</td>
                    <td class="text-right">${simbolo} ${precioUnitarioConvertido.toFixed(2)}</td>
                    <td class="text-right">${simbolo} ${subtotalProductoConvertido.toFixed(2)}</td>
                </tr>
            `;
        });

        html += `
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Resumen financiero -->
            <div class="factura-resumen">
                <div class="row">
                    <div class="col-md-6 offset-md-6">
                        <div class="bg-light p-4 rounded">
                            <h5 class="border-bottom pb-2 mb-3">RESUMEN FINANCIERO</h5>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Subtotal productos:</span>
                                <span>${simbolo} ${subtotalConvertido.toFixed(2)}</span>
                            </div>
                            ${descuentoConvertido > 0 ? `
                            <div class="d-flex justify-content-between mb-2 text-success">
                                <span>Descuento:</span>
                                <span>-${simbolo} ${descuentoConvertido.toFixed(2)}</span>
                            </div>
                            ` : ''}
                            <div class="d-flex justify-content-between mb-2">
                                <span>Costo de envío:</span>
                                <span>${simbolo} ${envioConvertido.toFixed(2)}</span>
                            </div>
                            ${impuestosConvertido > 0 ? `
                            <div class="d-flex justify-content-between mb-2">
                                <span>Impuestos:</span>
                                <span>${simbolo} ${impuestosConvertido.toFixed(2)}</span>
                            </div>
                            ` : ''}
                            <hr>
                            <div class="d-flex justify-content-between font-weight-bold h4">
                                <span>TOTAL ${codigoMoneda}:</span>
                                <span class="text-primary">${simbolo} ${totalConvertido.toFixed(2)}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Método de pago -->
            ${pedido.metodo_pago_nombre ? `
            <div class="factura-pago mt-4">
                <div class="row">
                    <div class="col-md-6">
                        <h5 class="border-bottom pb-2 mb-3">INFORMACIÓN DE PAGO</h5>
                        <p class="mb-1"><strong>Método de pago:</strong> ${pedido.metodo_pago_nombre}</p>
                        <p class="mb-0"><strong>Estado del pago:</strong> 
                            <span class="badge ${getEstadoPagoClass(pedido.estado_pago)}">
                                ${pedido.estado_pago || 'Pendiente'}
                            </span>
                        </p>
                    </div>
                    <div class="col-md-6 text-right">
                        <div class="mt-3">
                            <p class="text-muted mb-0">Gracias por su compra</p>
                            <p class="text-muted mb-0">Esta factura es válida como comprobante de pago</p>
                        </div>
                    </div>
                </div>
            </div>
            ` : ''}

            <!-- Notas y términos -->
            <div class="factura-notas mt-4 pt-3 border-top">
                <div class="row">
                    <div class="col-md-12">
                        <h6 class="text-muted mb-2">NOTAS Y TÉRMINOS:</h6>
                        <ul class="small text-muted mb-0 pl-3">
                            <li>Los productos pueden estar sujetos a cambios sin previo aviso</li>
                            <li>Plazo para devoluciones: 30 días a partir de la fecha de compra</li>
                            <li>Para consultas sobre su pedido, contacte a: soporte@nexusbuy.com</li>
                            <li>Esta factura fue generada automáticamente por el sistema NexusBuy</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Pie de página -->
            <div class="factura-footer mt-4 pt-3 border-top text-center">
                <p class="small text-muted mb-0">
                    NexusBuy &copy; ${new Date().getFullYear()} | Todos los derechos reservados
                </p>
            </div>
        </div>
        `;

        $("#detalles-pedido-content").html(html);
        $("#modalDetallesPedido").modal("show");
        
    } catch (error) {
        console.error('❌ Error cargando detalles del pedido:', error);
        Swal.fire("Error", "No se pudieron cargar los detalles del pedido", "error");
    }
}

// Función para descargar la factura en PDF
async function descargarFacturaPDF() {
    try {
        // Verificar que jsPDF esté disponible
        if (typeof window.jspdf === 'undefined') {
            throw new Error('La librería jsPDF no está cargada');
        }

        // Mostrar loading
        Swal.fire({
            title: 'Generando PDF...',
            text: 'Por favor espere un momento',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        const { jsPDF } = window.jspdf;
        const doc = new jsPDF('p', 'mm', 'a4');
        
        // Configuración del documento
        const pageWidth = doc.internal.pageSize.getWidth();
        const margin = 15;
        
        // Datos de la factura
        const factura = window.facturaData;
        if (!factura || !factura.pedido) {
            throw new Error('No hay datos de factura disponibles');
        }

        let yPos = margin;
        
        // Logo y encabezado
        doc.setFontSize(24);
        doc.setTextColor(67, 97, 238); // Color primario #4361ee
        doc.text('NEXUSBUY', margin, yPos);
        
        doc.setFontSize(10);
        doc.setTextColor(100, 100, 100);
        doc.text('Tu tienda online de confianza', margin, yPos + 6);
        
        yPos += 15;
        
        // Información de la factura
        doc.setFontSize(16);
        doc.setTextColor(0, 0, 0);
        doc.text('FACTURA', pageWidth - margin - 20, margin);
        
        doc.setFontSize(9);
        doc.text(`No. Factura: ${factura.numeroFactura}`, pageWidth - margin - 60, margin + 8);
        doc.text(`Pedido: ${factura.pedido.numero_orden || 'N/A'}`, pageWidth - margin - 60, margin + 13);
        doc.text(`Fecha: ${factura.fechaFormateada.split(',')[0]}`, pageWidth - margin - 60, margin + 18);
        
        // Información de la empresa
        doc.setFontSize(9);
        doc.setTextColor(0, 0, 0);
        doc.text('NexusBuy S.A.', margin, yPos);
        doc.text('Avenida Principal #123', margin, yPos + 5);
        doc.text('Tel: +53555123456 | Email: ventas@nexusbuy.com', margin, yPos + 10);
        doc.text('NIT: 123-456789-0', margin, yPos + 15);
        
        yPos += 25;
        
        // Línea separadora
        doc.setDrawColor(67, 97, 238);
        doc.setLineWidth(0.5);
        doc.line(margin, yPos, pageWidth - margin, yPos);
        yPos += 5;
        
        // Información del cliente
        doc.setFontSize(11);
        doc.setTextColor(67, 97, 238);
        doc.text('INFORMACIÓN DEL CLIENTE', margin, yPos);
        
        doc.setFontSize(9);
        doc.setTextColor(0, 0, 0);
        yPos += 7;
        doc.text(`Nombre: ${factura.pedido.nombres || ''} ${factura.pedido.apellidos || ''}`, margin, yPos);
        yPos += 5;
        doc.text(`Dirección: ${factura.pedido.direccion || 'No especificada'}`, margin, yPos);
        yPos += 5;
        doc.text(`Teléfono: ${factura.pedido.telefono_contacto || 'N/A'}`, margin, yPos);
        yPos += 5;
        doc.text(`Municipio: ${factura.pedido.municipio_nombre || 'N/A'}`, margin, yPos);
        yPos += 5;
        doc.text(`Provincia: ${factura.pedido.provincia_nombre || 'N/A'}`, margin, yPos);
        
        yPos += 10;
        
        // Tabla de productos
        doc.setFontSize(11);
        doc.setTextColor(67, 97, 238);
        doc.text('DETALLES DE PRODUCTOS', margin, yPos);
        
        yPos += 7;
        
        // Encabezado de la tabla
        doc.setFillColor(248, 249, 250);
        doc.rect(margin, yPos, pageWidth - (margin * 2), 8, 'F');
        doc.setTextColor(0, 0, 0);
        doc.setFontSize(9);
        doc.setFont(undefined, 'bold');
        
        doc.text('#', margin + 2, yPos + 6);
        doc.text('DESCRIPCIÓN', margin + 10, yPos + 6);
        doc.text('CANT.', margin + 100, yPos + 6);
        doc.text('PRECIO UNIT.', margin + 120, yPos + 6);
        doc.text('SUBTOTAL', margin + 150, yPos + 6);
        
        yPos += 8;
        doc.setFont(undefined, 'normal');
        
        // Productos
        let subtotalDetalles = 0;
        factura.detalles.forEach((detalle, index) => {
            if (yPos > 250) {
                doc.addPage();
                yPos = margin;
            }
            
            const precioUnitario = (parseFloat(detalle.precio_unitario) || 0) / factura.tasa;
            const subtotal = (parseFloat(detalle.subtotal) || 0) / factura.tasa;
            const cantidad = parseInt(detalle.cantidad) || 1;
            subtotalDetalles += parseFloat(detalle.subtotal) || 0;
            
            doc.text(`${index + 1}`, margin + 2, yPos + 5);
            
            // Descripción con ajuste de texto
            const descripcion = `${detalle.producto_nombre || 'Producto'} - ${detalle.marca_nombre || 'Sin marca'}`;
            const descripcionLines = doc.splitTextToSize(descripcion, 70);
            doc.text(descripcionLines, margin + 10, yPos + 5);
            
            // Si la descripción ocupa múltiples líneas, ajustar Y
            if (descripcionLines.length > 1) {
                yPos += (descripcionLines.length - 1) * 5;
            }
            
            doc.text(`${cantidad}`, margin + 100, yPos + 5, { align: 'center' });
            doc.text(`${factura.simbolo} ${precioUnitario.toFixed(2)}`, margin + 120, yPos + 5, { align: 'right' });
            doc.text(`${factura.simbolo} ${subtotal.toFixed(2)}`, margin + 150, yPos + 5, { align: 'right' });
            
            yPos += 8;
        });
        
        yPos += 5;
        
        // Resumen financiero
        doc.setFontSize(11);
        doc.setTextColor(67, 97, 238);
        doc.text('RESUMEN FINANCIERO', margin, yPos);
        
        yPos += 7;
        doc.setFontSize(9);
        doc.setTextColor(0, 0, 0);
        
        // Cálculos
        const envio = (parseFloat(factura.pedido.costo_envio) || 0) / factura.tasa;
        const descuento = (parseFloat(factura.pedido.descuento) || 0) / factura.tasa;
        const impuestos = (parseFloat(factura.pedido.impuestos) || 0) / factura.tasa;
        const subtotal = subtotalDetalles / factura.tasa;
        const total = (parseFloat(factura.pedido.total) || 0) / factura.tasa;
        
        // Detalles del resumen
        const col1 = pageWidth - margin - 100;
        const col2 = pageWidth - margin - 10;
        
        doc.text('Subtotal productos:', col1, yPos, { align: 'right' });
        doc.text(`${factura.simbolo} ${subtotal.toFixed(2)}`, col2, yPos, { align: 'right' });
        yPos += 5;
        
        if (descuento > 0) {
            doc.setTextColor(0, 128, 0);
            doc.text('Descuento:', col1, yPos, { align: 'right' });
            doc.text(`-${factura.simbolo} ${descuento.toFixed(2)}`, col2, yPos, { align: 'right' });
            yPos += 5;
            doc.setTextColor(0, 0, 0);
        }
        
        doc.text('Costo de envío:', col1, yPos, { align: 'right' });
        doc.text(`${factura.simbolo} ${envio.toFixed(2)}`, col2, yPos, { align: 'right' });
        yPos += 5;
        
        if (impuestos > 0) {
            doc.text('Impuestos:', col1, yPos, { align: 'right' });
            doc.text(`${factura.simbolo} ${impuestos.toFixed(2)}`, col2, yPos, { align: 'right' });
            yPos += 5;
        }
        
        // Línea separadora
        doc.setDrawColor(200, 200, 200);
        doc.line(col1 - 10, yPos, col2, yPos);
        yPos += 5;
        
        // Total
        doc.setFontSize(12);
        doc.setFont(undefined, 'bold');
        doc.setTextColor(67, 97, 238);
        doc.text('TOTAL:', col1, yPos, { align: 'right' });
        doc.text(`${factura.simbolo} ${total.toFixed(2)}`, col2, yPos, { align: 'right' });
        
        // Notas
        yPos += 15;
        doc.setFontSize(8);
        doc.setTextColor(100, 100, 100);
        doc.setFont(undefined, 'normal');
        doc.text('NOTAS Y TÉRMINOS:', margin, yPos);
        doc.text('• Los productos pueden estar sujetos a cambios sin previo aviso', margin, yPos + 5);
        doc.text('• Plazo para devoluciones: 30 días a partir de la fecha de compra', margin, yPos + 10);
        doc.text('• Para consultas sobre su pedido, contacte a: soporte@nexusbuy.com', margin, yPos + 15);
        doc.text('• Esta factura fue generada automáticamente por el sistema NexusBuy', margin, yPos + 20);
        
        // Pie de página
        doc.text(`NexusBuy © ${new Date().getFullYear()} | Factura generada el ${new Date().toLocaleDateString()}`, pageWidth/2, 290, { align: 'center' });
        
        // Guardar PDF
        doc.save(`Factura_${factura.numeroFactura}.pdf`);
        
        // Cerrar loading
        Swal.close();
        
        // Mostrar confirmación
        Swal.fire({
            icon: 'success',
            title: 'PDF Generado',
            text: `La factura ${factura.numeroFactura} ha sido descargada`,
            confirmButtonText: 'Aceptar',
            timer: 2000
        });
        
    } catch (error) {
        console.error('❌ Error generando PDF:', error);
        Swal.close();
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'No se pudo generar el PDF. Por favor intente nuevamente.',
            confirmButtonText: 'Aceptar'
        });
    }
}

// Función para imprimir solo la factura
function imprimirFactura() {
    try {
        // Obtener el contenido de la factura
        const facturaContent = document.getElementById('factura-content');
        if (!facturaContent) {
            throw new Error('No se encontró el contenido de la factura');
        }

        // Crear una nueva ventana para imprimir
        const printWindow = window.open('', '_blank');
        if (!printWindow) {
            throw new Error('No se pudo abrir la ventana de impresión. Por favor desbloquee los popups.');
        }

        // Escribir el contenido en la nueva ventana
        printWindow.document.write(`
            <!DOCTYPE html>
            <html lang="es">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Factura - NexusBuy</title>
                <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
                <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
                <style>
                    @media print {
                        @page {
                            size: A4;
                            margin: 15mm;
                        }
                        body {
                            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                            font-size: 12pt;
                            line-height: 1.5;
                            color: #000;
                            background: white !important;
                        }
                        .no-print, .modal-footer, .modal-header, .btn {
                            display: none !important;
                        }
                        .table {
                            width: 100%;
                            border-collapse: collapse;
                        }
                        .table-bordered th,
                        .table-bordered td {
                            border: 1px solid #dee2e6 !important;
                            padding: 8px;
                        }
                        .table-bordered th {
                            background-color: #f8f9fa !important;
                            font-weight: 600;
                        }
                        .text-primary {
                            color: #4361ee !important;
                        }
                        .text-danger {
                            color: #e63946 !important;
                        }
                        .text-success {
                            color: #4bb543 !important;
                        }
                        .border-bottom {
                            border-bottom: 2px solid #dee2e6 !important;
                        }
                        .bg-light {
                            background-color: #f8f9fa !important;
                        }
                        .estado-pedido {
                            padding: 4px 8px;
                            border-radius: 12px;
                            font-size: 0.8em;
                            font-weight: 600;
                        }
                        .estado-pendiente { background: #fff3cd; color: #856404; }
                        .estado-confirmado { background: #d1ecf1; color: #0c5460; }
                        .estado-enviado { background: #d4edda; color: #155724; }
                        .estado-entregado { background: #c3e6cb; color: #155724; }
                        .estado-cancelado { background: #f8d7da; color: #721c24; }
                        .estado-reembolsado { background: #e2e3e5; color: #383d41; }
                    }
                    body {
                        padding: 20px;
                        max-width: 210mm;
                        margin: 0 auto;
                        background: white;
                    }
                    .logo-factura h2 {
                        font-size: 2.2rem;
                        font-weight: 800;
                        color: #4361ee;
                        margin-bottom: 5px;
                    }
                    .factura-info {
                        border-left: 4px solid #4361ee;
                        padding-left: 15px;
                        background: #f8f9fa;
                        border-radius: 8px;
                    }
                    .factura-header, .factura-cliente, .factura-productos, .factura-resumen {
                        margin-bottom: 25px;
                    }
                    .table-responsive {
                        overflow-x: visible !important;
                    }
                    .print-controls {
                        margin-top: 20px;
                        text-align: center;
                        padding: 15px;
                        border-top: 1px solid #dee2e6;
                    }
                </style>
            </head>
            <body>
                ${facturaContent.innerHTML}
                <div class="print-controls no-print">
                    <button class="btn btn-secondary" onclick="window.close()">
                        <i class="fas fa-times mr-2"></i>Cerrar
                    </button>
                    <button class="btn btn-primary ml-2" onclick="window.print()">
                        <i class="fas fa-print mr-2"></i>Imprimir
                    </button>
                </div>
                <script>
                    // Auto-imprimir al cargar
                    window.onload = function() {
                        setTimeout(() => {
                            window.print();
                        }, 500);
                    };
                    
                    // Cerrar ventana después de imprimir
                    window.onafterprint = function() {
                        setTimeout(() => {
                            window.close();
                        }, 1000);
                    };
                <\/script>
            </body>
            </html>
        `);

        printWindow.document.close();

    } catch (error) {
        console.error('❌ Error al imprimir:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error de impresión',
            text: error.message || 'No se pudo abrir la ventana de impresión',
            confirmButtonText: 'Aceptar'
        });
    }
}

// Funciones auxiliares
function getEstadoClass(estado) {
    const clases = {
        'pendiente': "estado-pendiente",
        'confirmada': "estado-confirmado",
        'procesando': "estado-enviado",
        'enviada': "estado-enviado",
        'entregada': "estado-entregado",
        'cancelada': "estado-cancelado",
        'reembolsada': "estado-reembolsado"
    };
    return clases[estado] || "estado-pendiente";
}

function getEstadoTexto(estado) {
    const textos = {
        'pendiente': "Pendiente",
        'confirmada': "Confirmada",
        'procesando': "Procesando",
        'enviada': "Enviada",
        'entregada': "Entregada",
        'cancelada': "Cancelada",
        'reembolsada': "Reembolsada"
    };
    return textos[estado] || "Pendiente";
}

function getEstadoPagoClass(estadoPago) {
    const clases = {
        'completada': "badge-success",
        'pendiente': "badge-warning",
        'fallida': "badge-danger",
        'reembolsada': "badge-info"
    };
    return clases[estadoPago] || "badge-secondary";
}
