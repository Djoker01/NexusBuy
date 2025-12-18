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

    /* Order Cards - Ajustado para coincidir con JavaScript */
    .pedido-card {
        background: white;
        border-radius: var(--border-radius);
        box-shadow: var(--shadow);
        margin-bottom: 20px;
        overflow: hidden;
        transition: var(--transition);
        border: 1px solid #e9ecef;
    }

    .pedido-card:hover {
        transform: translateY(-5px);
        box-shadow: var(--shadow-hover);
    }

    .pedido-card .card-header {
        background: #f8f9fa;
        padding: 20px;
        border-bottom: 1px solid #e9ecef;
    }

    .pedido-card .card-body {
        padding: 20px;
    }

    /* Estados de pedido - Coincidiendo con JavaScript */
    .estado-pedido {
        padding: 8px 16px;
        border-radius: 20px;
        font-weight: 600;
        font-size: 0.8rem;
        text-transform: uppercase;
        display: inline-block;
    }

    .estado-pendiente { 
        background: #fff3cd; 
        color: #856404; 
    }
    .estado-confirmado { 
        background: #d1ecf1; 
        color: #0c5460; 
    }
    .estado-enviado { 
        background: #d4edda; 
        color: #155724; 
    }
    .estado-entregado { 
        background: #c3e6cb; 
        color: #155724; 
    }
    .estado-cancelado { 
        background: #f8d7da; 
        color: #721c24; 
    }
    .estado-reembolsado { 
        background: #e2e3e5; 
        color: #383d41; 
    }

    /* Imágenes de productos en cards - CORREGIDO */
    .producto-img {
        width: 60px;
        height: 60px;
        object-fit: contain;
        border-radius: 8px;
        background: white;
        padding: 3px;
        border: 1px solid #e9ecef;
        flex-shrink: 0;
    }

    /* Ajustes para el layout generado por JavaScript */
    .pedido-card .d-flex.align-items-center {
        display: flex !important;
        align-items: center !important;
        gap: 10px;
    }

    .pedido-card .producto-img.mr-2 {
        margin-right: 0.5rem !important;
    }

    /* Estilos para los precios */
    .pedido-card .text-danger {
        color: var(--danger) !important;
        font-size: 1.3rem;
        font-weight: 700;
    }

    .pedido-card .small .d-flex {
        margin-bottom: 8px;
    }

    .pedido-card .border-left {
        border-left: 1px solid #e9ecef !important;
    }

    /* Botones en las cards */
    .pedido-card .btn-outline-primary {
        background: white;
        color: var(--primary);
        border: 2px solid var(--primary);
        border-radius: 8px;
        padding: 10px 20px;
        font-weight: 600;
        transition: var(--transition);
    }

    .pedido-card .btn-outline-primary:hover {
        background: var(--primary);
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(67, 97, 238, 0.3);
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

    /* Estilos para la tabla en el modal */
    .modal-modern .table {
        background: white;
    }

    .modal-modern .table thead th {
        background: #f8f9fa;
        border-top: none;
        font-weight: 600;
        color: var(--dark);
    }

    .modal-modern .table tbody tr:hover {
        background: #f8f9fa;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .pedido-card .card-header .row {
            flex-direction: column;
            align-items: flex-start !important;
            gap: 15px;
        }

        .pedido-card .card-header .text-center,
        .pedido-card .card-header .text-right {
            text-align: left !important;
            width: 100%;
        }

        .pedido-card .card-body .border-left {
            border-left: none !important;
            border-top: 1px solid #e9ecef;
            margin-top: 20px;
            padding-top: 20px;
        }

        .producto-img {
            width: 50px;
            height: 50px;
        }

        .pedido-card .row > .col-md-8,
        .pedido-card .row > .col-md-4 {
            width: 100%;
        }

        .pedido-card .col-4 {
            width: 100%;
            margin-bottom: 15px;
        }

        .pedido-card .d-flex.align-items-center {
            flex-direction: column;
            text-align: center;
            gap: 8px;
        }

        .pedido-card .producto-img.mr-2 {
            margin-right: 0 !important;
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

    .pedido-card {
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

    /* Estilos adicionales para elementos generados por JavaScript */
    .badge.badge-light {
        background: #f8f9fa;
        color: #6c757d;
        padding: 8px 12px;
        border-radius: 20px;
        font-size: 0.8rem;
    }

    /* Ajuste para el texto en las cards */
    .pedido-card small.d-block {
        display: block;
        line-height: 1.3;
    }

    .pedido-card .font-weight-bold {
        font-weight: 600;
    }

    /* Espaciado mejorado */
    .pedido-card .mb-2 {
        margin-bottom: 0.5rem !important;
    }

    .pedido-card .mb-4 {
        margin-bottom: 1.5rem !important;
    }

    /* Estilos para los títulos en cards */
    .pedido-card h6 {
        color: var(--dark);
        font-weight: 600;
        margin-bottom: 15px;
        font-size: 1rem;
    }

    /* Ajustes para el resumen de precios */
    .pedido-card .bg-light {
        background: #f8f9fa !important;
        border-radius: 8px;
        padding: 15px;
    }

    .pedido-card hr {
        margin: 15px 0;
        border-top: 1px solid #e9ecef;
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
    <option value="confirmada">Confirmada</option>
    <option value="procesando">Procesando</option>
    <option value="enviada">Enviada</option>
    <option value="entregada">Entregada</option>
    <option value="cancelada">Cancelada</option>
    <option value="reembolsada">Reembolsada</option>
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

<!-- Modal Detalles del Pedido actualizado -->
<div class="modal fade modal-modern" id="modalDetallesPedido" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-file-invoice mr-2"></i>Factura del Pedido
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="detalles-pedido-content">
                <!-- Contenido cargado dinámicamente -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times mr-2"></i>Cerrar
                </button>
                <button type="button" class="btn btn-success" onclick="descargarFacturaPDF()">
                    <i class="fas fa-file-pdf mr-2"></i>Descargar PDF
                </button>
                <button type="button" class="btn btn-primary" onclick="imprimirFactura()">
                    <i class="fas fa-print mr-2"></i>Imprimir Factura
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

