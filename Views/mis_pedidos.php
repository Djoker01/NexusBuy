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

<style>
    :root {
        --primary: #4361ee;
        --secondary: #3f37c9;
        --accent: #4cc9f0;
        --success: #4bb543;
        --warning: #ffc107;
        --danger: #e63946;
        --light: #f8f9fa;
        --dark: #212529;
        --gradient-primary: linear-gradient(135deg, #4361ee 0%, #3a0ca3 100%);
        --gradient-accent: linear-gradient(135deg, #4cc9f0 0%, #4895ef 100%);
        --shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        --shadow-hover: 0 8px 24px rgba(0, 0, 0, 0.12);
        --border-radius: 12px;
        --transition: all 0.3s ease;
    }

    .content-header {
        background: var(--gradient-primary);
        color: white;
        border-radius: 0 0 var(--border-radius) var(--border-radius);
        margin-bottom: 30px;
        box-shadow: var(--shadow);
    }

    .breadcrumb {
        background: transparent;
        margin: 0;
    }

    .breadcrumb-item a {
        color: rgba(255, 255, 255, 0.9);
    }

    .breadcrumb-item.active {
        color: white;
    }

    /* Orders Header */
    .orders-header {
        background: white;
        border-radius: var(--border-radius);
        box-shadow: var(--shadow);
        padding: 30px;
        margin-bottom: 25px;
        border-left: 4px solid var(--primary);
    }

    .orders-title {
        font-size: 2rem;
        font-weight: 700;
        color: var(--dark);
        margin: 0;
    }

    .orders-subtitle {
        color: #6c757d;
        margin: 5px 0 0 0;
    }

    /* Filters Section */
    .filters-section {
        background: white;
        border-radius: var(--border-radius);
        box-shadow: var(--shadow);
        padding: 25px;
        margin-bottom: 25px;
    }

    .filter-group {
        margin-bottom: 15px;
    }

    .filter-label {
        font-weight: 600;
        color: var(--dark);
        margin-bottom: 8px;
        display: block;
    }

    .form-control-modern {
        border: 2px solid #e9ecef;
        border-radius: 8px;
        padding: 12px 15px;
        font-size: 1rem;
        transition: var(--transition);
    }

    .form-control-modern:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 0.2rem rgba(67, 97, 238, 0.25);
    }

    /* Order Cards */
    .order-card {
        background: white;
        border-radius: var(--border-radius);
        box-shadow: var(--shadow);
        margin-bottom: 20px;
        overflow: hidden;
        transition: var(--transition);
    }

    .order-card:hover {
        transform: translateY(-5px);
        box-shadow: var(--shadow-hover);
    }

    .order-header {
        background: #f8f9fa;
        padding: 20px;
        border-bottom: 1px solid #e9ecef;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 15px;
    }

    .order-info {
        display: flex;
        flex-direction: column;
        gap: 5px;
    }

    .order-number {
        font-weight: 700;
        color: var(--dark);
        font-size: 1.1rem;
    }

    .order-date {
        color: #6c757d;
        font-size: 0.9rem;
    }

    .order-status {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .status-badge {
        padding: 8px 16px;
        border-radius: 20px;
        font-weight: 600;
        font-size: 0.8rem;
        text-transform: uppercase;
    }

    .status-pendiente { background: #fff3cd; color: #856404; }
    .status-confirmado { background: #d1ecf1; color: #0c5460; }
    .status-enviado { background: #d4edda; color: #155724; }
    .status-entregado { background: #c3e6cb; color: #155724; }
    .status-cancelado { background: #f8d7da; color: #721c24; }
    .status-reembolsado { background: #e2e3e5; color: #383d41; }

    .order-body {
        padding: 25px;
    }

    .order-products {
        display: flex;
        flex-direction: column;
        gap: 15px;
    }

    .product-item {
        display: flex;
        align-items: center;
        gap: 15px;
        padding: 15px;
        background: #f8f9fa;
        border-radius: 8px;
    }

    .product-image {
        width: 80px;
        height: 80px;
        border-radius: 8px;
        overflow: hidden;
        background: white;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .product-image img {
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
    }

    .product-details {
        flex: 1;
    }

    .product-name {
        font-weight: 600;
        color: var(--dark);
        margin-bottom: 5px;
    }

    .product-brand {
        color: #6c757d;
        font-size: 0.9rem;
        margin-bottom: 5px;
    }

    .product-quantity {
        color: #6c757d;
        font-size: 0.9rem;
    }

    .product-price {
        text-align: right;
        min-width: 100px;
    }

    .current-price {
        font-weight: 700;
        color: var(--primary);
        font-size: 1.1rem;
    }

    .original-price {
        text-decoration: line-through;
        color: #6c757d;
        font-size: 0.9rem;
    }

    .order-footer {
        background: #f8f9fa;
        padding: 20px 25px;
        border-top: 1px solid #e9ecef;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 15px;
    }

    .order-total {
        font-size: 1.3rem;
        font-weight: 700;
        color: var(--primary);
    }

    .order-actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .btn-order-action {
        padding: 10px 20px;
        border-radius: 8px;
        font-weight: 600;
        transition: var(--transition);
        border: 2px solid;
    }

    .btn-view-details {
        background: var(--gradient-primary);
        color: white;
        border-color: var(--primary);
    }

    .btn-view-details:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(67, 97, 238, 0.3);
    }

    .btn-track-order {
        background: white;
        color: var(--success);
        border-color: var(--success);
    }

    .btn-track-order:hover {
        background: var(--success);
        color: white;
    }

    .btn-reorder {
        background: white;
        color: var(--primary);
        border-color: var(--primary);
    }

    .btn-reorder:hover {
        background: var(--primary);
        color: white;
    }

    /* Empty State */
    .empty-orders {
        text-align: center;
        padding: 80px 20px;
        background: white;
        border-radius: var(--border-radius);
        box-shadow: var(--shadow);
    }

    .empty-orders-icon {
        font-size: 5rem;
        color: #e9ecef;
        margin-bottom: 20px;
    }

    .empty-orders-title {
        color: #6c757d;
        margin-bottom: 10px;
        font-weight: 600;
    }

    .empty-orders-text {
        color: #6c757d;
        margin-bottom: 30px;
        max-width: 400px;
        margin-left: auto;
        margin-right: auto;
    }

    /* Order Timeline */
    .order-timeline {
        position: relative;
        padding: 20px 0;
    }

    .order-timeline::before {
        content: '';
        position: absolute;
        left: 25px;
        top: 0;
        bottom: 0;
        width: 2px;
        background: #e9ecef;
    }

    .timeline-step {
        position: relative;
        margin-bottom: 30px;
        padding-left: 70px;
    }

    .timeline-step:last-child {
        margin-bottom: 0;
    }

    .timeline-icon {
        position: absolute;
        left: 15px;
        top: 0;
        width: 40px;
        height: 40px;
        background: #e9ecef;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #6c757d;
        z-index: 2;
    }

    .timeline-step.completed .timeline-icon {
        background: var(--success);
        color: white;
    }

    .timeline-step.active .timeline-icon {
        background: var(--primary);
        color: white;
        animation: pulse 2s infinite;
    }

    .timeline-content {
        background: white;
        border-radius: var(--border-radius);
        padding: 20px;
        box-shadow: var(--shadow);
    }

    .timeline-date {
        color: #6c757d;
        font-size: 0.9rem;
        margin-bottom: 5px;
    }

    .timeline-title {
        font-weight: 600;
        color: var(--dark);
        margin-bottom: 5px;
    }

    .timeline-description {
        color: #6c757d;
        font-size: 0.9rem;
    }

    /* Modal Styles */
    .modal-modern .modal-content {
        border: none;
        border-radius: var(--border-radius);
        box-shadow: var(--shadow-hover);
    }

    .modal-modern .modal-header {
        background: var(--gradient-primary);
        color: white;
        border-radius: var(--border-radius) var(--border-radius) 0 0;
        border: none;
        padding: 25px;
    }

    .modal-modern .modal-body {
        padding: 25px;
        max-height: 70vh;
        overflow-y: auto;
    }

    .modal-modern .modal-footer {
        border: none;
        padding: 20px 25px;
        background: #f8f9fa;
        border-radius: 0 0 var(--border-radius) var(--border-radius);
    }

    /* Responsive */
    @media (max-width: 768px) {
        .order-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .order-status {
            align-self: flex-start;
        }

        .product-item {
            flex-direction: column;
            text-align: center;
        }

        .product-price {
            text-align: center;
        }

        .order-footer {
            flex-direction: column;
            align-items: flex-start;
        }

        .order-actions {
            width: 100%;
            justify-content: space-between;
        }

        .btn-order-action {
            flex: 1;
            text-align: center;
        }
    }

    /* Animations */
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .order-card {
        animation: fadeInUp 0.4s ease-out;
    }

    @keyframes pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.1); }
        100% { transform: scale(1); }
    }

    /* Loading States */
    .loading-orders {
        text-align: center;
        padding: 60px 20px;
    }

    .loading-spinner {
        width: 50px;
        height: 50px;
        border: 4px solid #f3f3f3;
        border-top: 4px solid var(--primary);
        border-radius: 50%;
        animation: spin 1s linear infinite;
        margin: 0 auto 20px;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
</style>

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