<?php
session_start();

// Verificar que el usuario está logueado
if (empty($_SESSION['id'])) {
    header('Location: login.php');
    exit();
}

include_once 'Layauts/header_general.php';
?>

<title>Mis Pedidos | NexusBuy</title>

<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Mis Pedidos</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="../index.php">Home</a></li>
                    <li class="breadcrumb-item active">Mis Pedidos</li>
                </ol>
            </div>
        </div>
    </div>
</section>

<section class="content">
    <div class="container-fluid">
        <!-- Orders Header -->
        <div class="orders-header">
            <h1 class="orders-title">
                <i class="fas fa-shopping-bag mr-2"></i>Mis Pedidos
            </h1>
            <p class="orders-subtitle">Revisa el estado y detalles de todos tus pedidos</p>
        </div>

        <!-- Filters Section -->
        <div class="filters-section">
            <div class="row">
                <div class="col-md-8">
                    <div class="filter-group">
                        <label class="filter-label">Filtrar por Estado</label>
                        <select class="form-control-modern" id="filtro-estado">
                            <option value="">Todos los estados</option>
                            <option value="pendiente">Pendiente</option>
                            <option value="confirmado">Confirmado</option>
                            <option value="enviado">Enviado</option>
                            <option value="entregado">Entregado</option>
                            <option value="cancelado">Cancelado</option>
                            <option value="reembolsado">Reembolsado</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="filter-group">
                        <label class="filter-label">Ordenar por</label>
                        <select class="form-control-modern" id="filtro-orden">
                            <option value="recientes">Más recientes</option>
                            <option value="antiguos">Más antiguos</option>
                            <option value="total-asc">Total: Menor a Mayor</option>
                            <option value="total-desc">Total: Mayor a Menor</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Orders List -->
        <div class="row">
            <div class="col-12">
                <div id="lista-pedidos">
                    <!-- Loading State -->
                    <div class="loading-orders">
                        <div class="loading-spinner"></div>
                        <p class="text-muted">Cargando tus pedidos...</p>
                    </div>
                </div>

                <!-- Empty State -->
                <div id="estado-vacio" class="empty-orders" style="display: none;">
                    <div class="empty-orders-icon">
                        <i class="fas fa-shopping-bag"></i>
                    </div>
                    <h3 class="empty-orders-title">No tienes pedidos aún</h3>
                    <p class="empty-orders-text">
                        ¡Descubre productos increíbles y realiza tu primer pedido! En NexusBuy encontrarás 
                        todo lo que necesitas al mejor precio.
                    </p>
                    <div class="row justify-content-center">
                        <div class="col-md-8">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <a href="producto.php" class="btn btn-primary btn-block btn-lg">
                                        <i class="fas fa-shopping-bag mr-2"></i>
                                        Explorar Productos
                                    </a>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <a href="favoritos.php" class="btn btn-outline-primary btn-block btn-lg">
                                        <i class="fas fa-heart mr-2"></i>
                                        Ver Favoritos
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Modal Detalles del Pedido -->
<div class="modal fade modal-modern" id="modalDetallesPedido" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-info-circle mr-2"></i>Detalles del Pedido
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="detalles-pedido-content">
                <!-- Contenido cargado dinámicamente -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary" onclick="imprimirDetalles()">
                    <i class="fas fa-print mr-2"></i>Imprimir
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Seguimiento del Pedido -->
<div class="modal fade modal-modern" id="modalSeguimientoPedido" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-shipping-fast mr-2"></i>Seguimiento del Pedido
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="seguimiento-pedido-content">
                <!-- Contenido cargado dinámicamente -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<?php include_once 'Layauts/footer_general.php'; ?>

<script src="mis_pedidos.js"></script>

<script>
    // Funciones auxiliares para los modales
    function imprimirDetalles() {
        const contenido = document.getElementById('detalles-pedido-content').innerHTML;
        const ventana = window.open('', '_blank');
        ventana.document.write(`
            <!DOCTYPE html>
            <html>
            <head>
                <title>Detalles del Pedido - NexusBuy</title>
                <style>
                    body { font-family: Arial, sans-serif; margin: 20px; }
                    .print-header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #333; padding-bottom: 20px; }
                    .print-section { margin-bottom: 20px; }
                    .print-table { width: 100%; border-collapse: collapse; margin: 20px 0; }
                    .print-table th, .print-table td { border: 1px solid #ddd; padding: 12px; text-align: left; }
                    .print-table th { background-color: #f8f9fa; }
                    .text-right { text-align: right; }
                    .text-center { text-align: center; }
                </style>
            </head>
            <body>
                ${contenido}
            </body>
            </html>
        `);
        ventana.document.close();
        ventana.print();
    }

    // Simular carga de pedidos
    $(document).ready(function() {
        setTimeout(() => {
            // Simular que hay pedidos
            cargarPedidosEjemplo();
        }, 1500);
    });

    function cargarPedidosEjemplo() {
        const pedidos = [
            {
                id: 'NX-2024-001',
                fecha: '2024-01-15',
                estado: 'entregado',
                total: 189.99,
                productos: [
                    {
                        nombre: 'Samsung Galaxy S24 Ultra',
                        marca: 'Samsung',
                        imagen: '../Util/Img/Producto/samsung_s24.jpg',
                        precio: 169.99,
                        cantidad: 1
                    },
                    {
                        nombre: 'Funda Protectora',
                        marca: 'CaseMate',
                        imagen: '../Util/Img/Producto/funda.jpg',
                        precio: 19.99,
                        cantidad: 1
                    }
                ]
            },
            {
                id: 'NX-2024-002',
                fecha: '2024-01-10',
                estado: 'enviado',
                total: 89.50,
                productos: [
                    {
                        nombre: 'Nike Air Jordan 1 Retro',
                        marca: 'Nike',
                        imagen: '../Util/Img/Producto/nike_jordan.jpg',
                        precio: 89.50,
                        cantidad: 1
                    }
                ]
            }
        ];

        mostrarPedidos(pedidos);
    }

    function mostrarPedidos(pedidos) {
        const container = $('#lista-pedidos');
        
        if (pedidos.length === 0) {
            container.hide();
            $('#estado-vacio').show();
            return;
        }

        let html = '';
        
        pedidos.forEach(pedido => {
            html += `
                <div class="order-card">
                    <div class="order-header">
                        <div class="order-info">
                            <div class="order-number">Pedido #${pedido.id}</div>
                            <div class="order-date">Realizado el ${formatearFecha(pedido.fecha)}</div>
                        </div>
                        <div class="order-status">
                            <span class="status-badge status-${pedido.estado}">
                                ${obtenerTextoEstado(pedido.estado)}
                            </span>
                        </div>
                    </div>
                    
                    <div class="order-body">
                        <div class="order-products">
            `;

            pedido.productos.forEach(producto => {
                html += `
                    <div class="product-item">
                        <div class="product-image">
                            <img src="${producto.imagen}" alt="${producto.nombre}">
                        </div>
                        <div class="product-details">
                            <div class="product-name">${producto.nombre}</div>
                            <div class="product-brand">${producto.marca}</div>
                            <div class="product-quantity">Cantidad: ${producto.cantidad}</div>
                        </div>
                        <div class="product-price">
                            <div class="current-price">$${producto.precio.toFixed(2)}</div>
                        </div>
                    </div>
                `;
            });

            html += `
                        </div>
                    </div>
                    
                    <div class="order-footer">
                        <div class="order-total">Total: $${pedido.total.toFixed(2)}</div>
                        <div class="order-actions">
                            <button class="btn-order-action btn-view-details" onclick="verDetallesPedido('${pedido.id}')">
                                <i class="fas fa-eye mr-2"></i>Ver Detalles
                            </button>
                            <button class="btn-order-action btn-track-order" onclick="verSeguimiento('${pedido.id}')">
                                <i class="fas fa-shipping-fast mr-2"></i>Seguimiento
                            </button>
                            <button class="btn-order-action btn-reorder" onclick="reordenarPedido('${pedido.id}')">
                                <i class="fas fa-redo mr-2"></i>Volver a Pedir
                            </button>
                        </div>
                    </div>
                </div>
            `;
        });

        container.html(html);
    }

    function formatearFecha(fecha) {
        return new Date(fecha).toLocaleDateString('es-ES', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
    }

    function obtenerTextoEstado(estado) {
        const estados = {
            'pendiente': 'Pendiente',
            'confirmado': 'Confirmado',
            'enviado': 'Enviado',
            'entregado': 'Entregado',
            'cancelado': 'Cancelado',
            'reembolsado': 'Reembolsado'
        };
        return estados[estado] || estado;
    }

    function verDetallesPedido(idPedido) {
        // Simular carga de detalles
        $('#detalles-pedido-content').html(`
            <div class="text-center py-4">
                <div class="spinner-border text-primary" role="status">
                    <span class="sr-only">Cargando detalles...</span>
                </div>
                <p class="text-muted mt-2">Cargando detalles del pedido...</p>
            </div>
        `);
        
        $('#modalDetallesPedido').modal('show');
        
        setTimeout(() => {
            $('#detalles-pedido-content').html(`
                <div class="print-section">
                    <h6 class="mb-3">Información del Pedido</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <strong>Número de Pedido:</strong> ${idPedido}<br>
                            <strong>Fecha:</strong> ${formatearFecha('2024-01-15')}<br>
                            <strong>Estado:</strong> <span class="status-badge status-entregado">Entregado</span>
                        </div>
                        <div class="col-md-6">
                            <strong>Método de Pago:</strong> Tarjeta de Crédito<br>
                            <strong>Total:</strong> $189.99<br>
                            <strong>Dirección de Envío:</strong> Av. Principal #123
                        </div>
                    </div>
                </div>

                <div class="print-section">
                    <h6 class="mb-3">Productos</h6>
                    <table class="print-table">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th>Cantidad</th>
                                <th>Precio Unitario</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Samsung Galaxy S24 Ultra</td>
                                <td>1</td>
                                <td>$169.99</td>
                                <td>$169.99</td>
                            </tr>
                            <tr>
                                <td>Funda Protectora</td>
                                <td>1</td>
                                <td>$19.99</td>
                                <td>$19.99</td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3" class="text-right"><strong>Total:</strong></td>
                                <td><strong>$189.99</strong></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            `);
        }, 1000);
    }

    function verSeguimiento(idPedido) {
        $('#seguimiento-pedido-content').html(`
            <div class="order-timeline">
                <div class="timeline-step completed">
                    <div class="timeline-icon">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div class="timeline-content">
                        <div class="timeline-date">15 Ene 2024, 10:30 AM</div>
                        <div class="timeline-title">Pedido Confirmado</div>
                        <div class="timeline-description">Tu pedido ha sido confirmado y está siendo preparado.</div>
                    </div>
                </div>
                
                <div class="timeline-step completed">
                    <div class="timeline-icon">
                        <i class="fas fa-box"></i>
                    </div>
                    <div class="timeline-content">
                        <div class="timeline-date">16 Ene 2024, 09:15 AM</div>
                        <div class="timeline-title">Pedido Empacado</div>
                        <div class="timeline-description">Tu pedido ha sido empacado y está listo para envío.</div>
                    </div>
                </div>
                
                <div class="timeline-step completed">
                    <div class="timeline-icon">
                        <i class="fas fa-shipping-fast"></i>
                    </div>
                    <div class="timeline-content">
                        <div class="timeline-date">17 Ene 2024, 02:45 PM</div>
                        <div class="timeline-title">Pedido Enviado</div>
                        <div class="timeline-description">Tu pedido ha sido enviado. Número de seguimiento: TRK-${idPedido}</div>
                    </div>
                </div>
                
                <div class="timeline-step completed">
                    <div class="timeline-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="timeline-content">
                        <div class="timeline-date">18 Ene 2024, 11:20 AM</div>
                        <div class="timeline-title">Pedido Entregado</div>
                        <div class="timeline-description">Tu pedido ha sido entregado exitosamente.</div>
                    </div>
                </div>
            </div>
        `);
        
        $('#modalSeguimientoPedido').modal('show');
    }

    function reordenarPedido(idPedido) {
        Swal.fire({
            title: '¿Volver a pedir?',
            text: '¿Deseas agregar los productos de este pedido a tu carrito?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, agregar al carrito',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                // Simular agregado al carrito
                Swal.fire({
                    title: '¡Productos agregados!',
                    text: 'Los productos han sido agregados a tu carrito de compras.',
                    icon: 'success',
                    timer: 2000,
                    showConfirmButton: false
                }).then(() => {
                    window.location.href = 'carrito.php';
                });
            }
        });
    }
</script>