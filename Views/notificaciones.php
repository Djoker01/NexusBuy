<?php
session_start();

// Verificar que el usuario está logueado
if (empty($_SESSION['id'])) {
    header('Location: login.php');
    exit();
}

$base_path_url = ""; // Ya está en Views
$base_path = "../";
$pageTitle =  "Notificaciones";
$pageName = "Notificaciones";
$breadcrumb = "desactive";
$notificaciones = "active";
$checkout = "desactive";
$soporte = "desactive";
// $pageDescription = "Análisis detallado de toda la plataforma";
include_once 'Layouts/header.php';
?>
<link rel="stylesheet" href="<?php echo $base_path; ?>util/css/cliente/notificaciones.css">

<!-- Modal para ver notificación detallada -->
<div class="modal fade modal-modern" id="modalNotificacionDetalle" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-bell mr-2"></i>Detalle de Notificación
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="notificacionDetalleContent">
                <!-- Contenido se cargará dinámicamente -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary-modern" onclick="marcarComoLeida()">
                    <i class="fas fa-check mr-2"></i>Marcar como Leída
                </button>
            </div>
        </div>
    </div>
</div>


<section class="content">
    <div class="container-fluid">
        <!-- Header de Notificaciones -->
        <div class="notifications-header">
            <h1>Mis Notificaciones</h1>
            <p>Mantente al día con tus pedidos, promociones y actividad en NexusBuy</p>
        </div>

        <!-- Filtros Rápidos -->
        <div class="notifications-filters">
            <div class="filter-tabs">
                <div class="filter-tab active" data-filter="all">
                    <i class="fas fa-inbox"></i>
                    Todas
                    <span class="badge" id="count-all">12</span>
                </div>
                <div class="filter-tab" data-filter="unread">
                    <i class="fas fa-envelope"></i>
                    No leídas
                    <span class="badge" id="count-unread">5</span>
                </div>
                <div class="filter-tab" data-filter="orders">
                    <i class="fas fa-shopping-cart"></i>
                    Pedidos
                    <span class="badge" id="count-orders">3</span>
                </div>
                <div class="filter-tab" data-filter="promotions">
                    <i class="fas fa-tag"></i>
                    Promociones
                    <span class="badge" id="count-promotions">4</span>
                </div>
                <div class="filter-tab" data-filter="system">
                    <i class="fas fa-cog"></i>
                    Sistema
                    <span class="badge" id="count-system">2</span>
                </div>
            </div>
            
            <!-- Botón Marcas todas como leídas -->
            <button class="btn btn-primary-modern btn-sm" onclick="marcarTodasLeidas()">
                <i class="fas fa-check-double mr-2"></i>Marcar todas como leídas
            </button>
        </div>

        <!-- Acciones Masivas -->
        <div class="bulk-actions" id="bulkActions" style="display: none;">
            <div class="bulk-select">
                <input type="checkbox" id="selectAllNotifications" onchange="toggleSelectAll()">
                <label for="selectAllNotifications" class="mb-0">
                    <span id="selectedCount">0</span> notificaciones seleccionadas
                </label>
            </div>
            
            <div class="bulk-buttons">
                <button class="btn-bulk read" onclick="marcarSeleccionadasLeidas()">
                    <i class="fas fa-check"></i>
                    Marcar como leídas
                </button>
                <button class="btn-bulk delete" onclick="eliminarSeleccionadas()">
                    <i class="fas fa-trash"></i>
                    Eliminar
                </button>
            </div>
        </div>

        <!-- Lista de Notificaciones -->
        <div class="notifications-list">
            <div id="notifications-container">
                <!-- Las notificaciones se cargarán aquí dinámicamente -->
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Cargando...</span>
                    </div>
                    <p class="text-muted mt-2">Cargando notificaciones...</p>
                </div>
            </div>
        </div>

        <!-- Estado Vacío -->
        <div class="notifications-empty" id="emptyState" style="display: none;">
            <div class="notifications-empty-icon">
                <i class="far fa-bell-slash"></i>
            </div>
            <h3 class="notifications-empty-title">No hay notificaciones</h3>
            <p class="notifications-empty-text">
                No tienes notificaciones en este momento. Te avisaremos cuando tengas novedades.
            </p>
            <button class="btn btn-primary-modern" onclick="recargarNotificaciones()">
                <i class="fas fa-sync-alt mr-2"></i>Recargar
            </button>
        </div>

        <!-- Paginación -->
        <div class="notifications-pagination" id="paginationContainer" style="display: none;">
            <nav aria-label="Paginación de notificaciones">
                <ul class="pagination pagination-modern">
                    <li class="page-item disabled">
                        <a class="page-link-modern" href="#" aria-label="Anterior">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    </li>
                    <li class="page-item active"><a class="page-link-modern" href="#">1</a></li>
                    <li class="page-item"><a class="page-link-modern" href="#">2</a></li>
                    <li class="page-item"><a class="page-link-modern" href="#">3</a></li>
                    <li class="page-item">
                        <a class="page-link-modern" href="#" aria-label="Siguiente">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </div>
</section>

<?php
include_once 'Layouts/footer.php';
?>
<script src="notificaciones.js"></script>
