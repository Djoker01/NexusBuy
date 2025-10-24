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
    .estado-pedido {
        font-weight: bold;
        padding: 5px 10px;
        border-radius: 20px;
        font-size: 12px;
    }
    .estado-pendiente { background: #fff3cd; color: #856404; }
    .estado-confirmado { background: #d1ecf1; color: #0c5460; }
    .estado-enviado { background: #d4edda; color: #155724; }
    .estado-entregado { background: #e2e3e5; color: #383d41; }
    .estado-cancelado { background: #f8d7da; color: #721c24; }
    
    .pedido-card {
        border: 1px solid #e3e6f0;
        border-radius: 8px;
        transition: all 0.3s ease;
    }
    .pedido-card:hover {
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        transform: translateY(-2px);
    }
    
    .producto-img {
        width: 60px;
        height: 60px;
        object-fit: cover;
        border-radius: 6px;
    }
    
    .timeline {
        position: relative;
        padding-left: 30px;
    }
    .timeline::before {
        content: '';
        position: absolute;
        left: 15px;
        top: 0;
        bottom: 0;
        width: 2px;
        background: #e3e6f0;
    }
    .timeline-item {
        position: relative;
        margin-bottom: 20px;
    }
    .timeline-item::before {
        content: '';
        position: absolute;
        left: -23px;
        top: 5px;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background: #6c757d;
        border: 2px solid white;
    }
    .timeline-item.completado::before {
        background: #28a745;
    }
    .timeline-item.activo::before {
        background: #007bff;
        animation: pulse 2s infinite;
    }
    
    @keyframes pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.2); }
        100% { transform: scale(1); }
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
        <!-- Filtros -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-filter mr-2"></i>Filtrar pedidos
                                </h5>
                            </div>
                            <div class="col-md-4">
                                <select class="form-control" id="filtro-estado">
                                    <option value="">Todos los estados</option>
                                    <option value="pendiente">Pendiente</option>
                                    <option value="confirmado">Confirmado</option>
                                    <option value="enviado">Enviado</option>
                                    <option value="entregado">Entregado</option>
                                    <option value="cancelado">Cancelado</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Lista de Pedidos -->
        <div class="row">
            <div class="col-12">
                <div id="lista-pedidos">
                    <!-- Loading -->
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">Cargando pedidos...</span>
                        </div>
                        <p class="text-muted mt-2">Cargando tus pedidos...</p>
                    </div>
                </div>
                
                <!-- Estado vacío -->
                <div id="estado-vacio" class="text-center py-5" style="display: none;">
                    <div class="empty-orders-icon mb-3">
                        <i class="fas fa-shopping-bag fa-4x text-muted"></i>
                    </div>
                    <h4 class="text-muted">No tienes pedidos aún</h4>
                    <p class="text-muted mb-4">¡Descubre nuestros productos y realiza tu primer pedido!</p>
                    <a href="../index.php" class="btn btn-primary">
                        <i class="fas fa-shopping-bag mr-2"></i>Comenzar a comprar
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Modal Detalles del Pedido -->
<div class="modal fade" id="modalDetallesPedido" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detalles del Pedido</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="detalles-pedido-content">
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