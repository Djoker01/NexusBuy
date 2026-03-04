<?php
session_start();

// Verificar que el usuario está logueado
if (empty($_SESSION['id'])) {
    header('Location: login.php');
    exit();
}

$base_path_url = ""; // Ya está en Views
$base_path = "../";
$pageTitle =  "Mis Pedidos";
$pageName = "Mis Pedidos";
$breadcrumb = "active";
$checkout = "desactive";
$notificaciones = "desactive";
$soporte = "desactive";
// $pageDescription = "Análisis detallado de toda la plataforma";
include_once 'Layouts/header.php';
?>
<link rel="stylesheet" href="<?php echo $base_path; ?>util/css/cliente/mis-pedidos.css">


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

<?php include_once 'Layouts/footer.php'; ?>

<script src="mis_pedidos.js"></script>

