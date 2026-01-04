<?php
session_start();

// Verificar que el usuario está logueado
if (empty($_SESSION['id'])) {
    header('Location: login.php');
    exit();
}

include_once 'Layauts/header_general.php';
?>

<title>Notificaciones | NexusBuy</title>

<style>
    /* ESTILOS ESPECÍFICOS DE NOTIFICACIONES */
    
    /* Header de notificaciones */
    .notifications-header {
        background: var(--gradient-primary);
        color: white;
        border-radius: var(--border-radius);
        padding: 25px;
        margin-bottom: 25px;
        box-shadow: var(--shadow);
    }

    .notifications-header h1 {
        font-weight: 700;
        margin-bottom: 10px;
        color: white;
    }

    .notifications-header p {
        opacity: 0.9;
        margin-bottom: 0;
    }

    /* Filtros de notificaciones */
    .notifications-filters {
        background: white;
        border-radius: var(--border-radius);
        box-shadow: var(--shadow);
        padding: 20px;
        margin-bottom: 25px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .filter-tabs {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-bottom: 0;
    }

    .filter-tab {
        padding: 10px 20px;
        border: 2px solid #e9ecef;
        border-radius: 50px;
        background: white;
        color: #6c757d;
        font-weight: 600;
        cursor: pointer;
        transition: var(--transition);
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .filter-tab:hover {
        border-color: var(--primary);
        color: var(--primary);
    }

    .filter-tab.active {
        background: var(--gradient-primary);
        border-color: var(--primary);
        color: white;
    }

    .filter-tab .badge {
        background: rgba(255, 255, 255, 0.2);
        color: white;
        font-size: 0.8rem;
        padding: 3px 8px;
        border-radius: 10px;
        min-width: 24px;
    }

    .filter-tab:not(.active) .badge {
        background: var(--primary);
        color: white;
    }

    /* Lista de notificaciones */
    .notifications-list {
        background: white;
        border-radius: var(--border-radius);
        box-shadow: var(--shadow);
        overflow: hidden;
    }

    .notification-item {
        padding: 20px;
        border-bottom: 1px solid #f8f9fa;
        display: flex;
        align-items: flex-start;
        gap: 15px;
        transition: var(--transition);
        cursor: pointer;
        position: relative;
    }

    .notification-item:hover {
        background: #f8f9fa;
        transform: translateX(5px);
    }

    .notification-item.unread {
        background: rgba(67, 97, 238, 0.05);
        border-left: 4px solid var(--primary);
    }

    .notification-item.unread:hover {
        background: rgba(67, 97, 238, 0.1);
    }

    .notification-item:last-child {
        border-bottom: none;
    }

    .notification-icon {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        font-size: 1.2rem;
        color: white;
        box-shadow: var(--shadow);
        transition: var(--transition);
    }

    .notification-item:hover .notification-icon {
        transform: scale(1.1);
    }

    .notification-icon.order {
        background: var(--gradient-primary);
    }

    .notification-icon.promo {
        background: var(--gradient-success);
    }

    .notification-icon.system {
        background: var(--gradient-accent);
    }

    .notification-icon.security {
        background: var(--gradient-warning);
    }

    .notification-icon.social {
        background: var(--gradient-danger);
    }

    .notification-content {
        flex: 1;
        min-width: 0;
    }

    .notification-title {
        font-weight: 600;
        color: var(--dark);
        margin-bottom: 5px;
        line-height: 1.4;
        font-size: 1.05rem;
    }

    .notification-message {
        color: #6c757d;
        margin-bottom: 8px;
        line-height: 1.5;
    }

    .notification-meta {
        display: flex;
        align-items: center;
        gap: 15px;
        font-size: 0.85rem;
        color: #adb5bd;
    }

    .notification-time {
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .notification-type {
        background: #f8f9fa;
        padding: 3px 10px;
        border-radius: 12px;
        font-weight: 500;
        font-size: 0.8rem;
        color: #6c757d;
    }

    .notification-actions {
        display: flex;
        gap: 10px;
        opacity: 0;
        transition: var(--transition);
    }

    .notification-item:hover .notification-actions {
        opacity: 1;
    }

    .notification-action-btn {
        width: 35px;
        height: 35px;
        border-radius: 50%;
        border: none;
        background: white;
        color: #6c757d;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: var(--transition);
        box-shadow: var(--shadow);
    }

    .notification-action-btn:hover {
        transform: scale(1.1);
    }

    .notification-action-btn.read:hover {
        background: var(--success);
        color: white;
    }

    .notification-action-btn.delete:hover {
        background: var(--danger);
        color: white;
    }

    .notification-indicator {
        position: absolute;
        top: 25px;
        left: 15px;
        width: 8px;
        height: 8px;
        background: var(--primary);
        border-radius: 50%;
        opacity: 0;
        transition: var(--transition);
    }

    .notification-item.unread .notification-indicator {
        opacity: 1;
        animation: pulse-notification 2s infinite;
    }

    @keyframes pulse-notification {
        0%, 100% {
            transform: scale(1);
            box-shadow: 0 0 0 0 rgba(67, 97, 238, 0.7);
        }
        50% {
            transform: scale(1.2);
            box-shadow: 0 0 0 4px rgba(67, 97, 238, 0);
        }
    }

    /* Estado vacío */
    .notifications-empty {
        text-align: center;
        padding: 60px 20px;
    }

    .notifications-empty-icon {
        font-size: 4rem;
        color: #e9ecef;
        margin-bottom: 20px;
    }

    .notifications-empty-title {
        color: #6c757d;
        font-weight: 600;
        margin-bottom: 10px;
        font-size: 1.5rem;
    }

    .notifications-empty-text {
        color: #6c757d;
        margin-bottom: 30px;
        max-width: 400px;
        margin-left: auto;
        margin-right: auto;
    }

    /* Acciones masivas */
    .bulk-actions {
        background: white;
        border-radius: var(--border-radius);
        box-shadow: var(--shadow);
        padding: 15px 20px;
        margin-bottom: 25px;
        display: flex;
        align-items: center;
        gap: 15px;
        flex-wrap: wrap;
    }

    .bulk-select {
        display: flex;
        align-items: center;
        gap: 10px;
        font-weight: 500;
    }

    .bulk-select input {
        width: 18px;
        height: 18px;
        cursor: pointer;
    }

    .bulk-buttons {
        display: flex;
        gap: 10px;
        margin-left: auto;
    }

    .btn-bulk {
        padding: 8px 16px;
        border-radius: 6px;
        border: none;
        font-weight: 500;
        cursor: pointer;
        transition: var(--transition);
        display: flex;
        align-items: center;
        gap: 8px;
        background: #f8f9fa;
        color: #6c757d;
    }

    .btn-bulk:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow);
    }

    .btn-bulk.read:hover {
        background: var(--success);
        color: white;
    }

    .btn-bulk.delete:hover {
        background: var(--danger);
        color: white;
    }

    /* Paginación específica */
    .notifications-pagination {
        display: flex;
        justify-content: center;
        margin-top: 30px;
    }

    /* Modal de vista de notificación */
    .notification-modal .modal-body {
        padding: 30px;
    }

    .notification-detail {
        background: white;
        border-radius: var(--border-radius);
        padding: 30px;
        box-shadow: var(--shadow-light);
        border-left: 4px solid var(--primary);
    }

    .notification-detail-header {
        display: flex;
        align-items: center;
        gap: 15px;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 1px solid #f8f9fa;
    }

    .notification-detail-icon {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        color: white;
        flex-shrink: 0;
    }

    .notification-detail-info h4 {
        margin: 0;
        font-weight: 600;
        color: var(--dark);
    }

    .notification-detail-time {
        color: #adb5bd;
        font-size: 0.9rem;
        margin-top: 5px;
    }

    .notification-detail-body {
        line-height: 1.6;
        color: #495057;
    }

    .notification-detail-body p {
        margin-bottom: 15px;
    }

    .notification-detail-actions {
        display: flex;
        gap: 10px;
        margin-top: 25px;
        padding-top: 20px;
        border-top: 1px solid #f8f9fa;
    }

    /* Responsive específico */
    @media (max-width: 768px) {
        .notifications-header {
            padding: 20px;
            text-align: center;
        }
        
        .notifications-filters {
            flex-direction: column;
            gap: 15px;
        }
        
        .filter-tabs {
            justify-content: center;
            width: 100%;
        }
        
        .notification-item {
            padding: 15px;
            flex-direction: column;
            gap: 12px;
        }
        
        .notification-icon {
            width: 40px;
            height: 40px;
            font-size: 1rem;
        }
        
        .notification-meta {
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .notification-actions {
            opacity: 1;
            margin-top: 10px;
        }
        
        .bulk-actions {
            flex-direction: column;
            align-items: stretch;
            gap: 15px;
        }
        
        .bulk-buttons {
            margin-left: 0;
            justify-content: center;
        }
        
        .notification-detail {
            padding: 20px;
        }
        
        .notification-detail-header {
            flex-direction: column;
            text-align: center;
        }
        
        .notification-detail-actions {
            flex-direction: column;
        }
    }

    @media (max-width: 576px) {
        .filter-tabs {
            justify-content: stretch;
        }
        
        .filter-tab {
            flex: 1;
            justify-content: center;
            padding: 10px;
        }
        
        .notification-detail-icon {
            width: 50px;
            height: 50px;
            font-size: 1.2rem;
        }
    }
</style>

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

<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Notificaciones</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="../index.php">Home</a></li>
                    <li class="breadcrumb-item"><a href="mi_perfil.php">Mi Perfil</a></li>
                    <li class="breadcrumb-item active">Notificaciones</li>
                </ol>
            </div>
        </div>
    </div>
</section>

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
include_once 'Layauts/footer_general.php';
?>
<script src="notificaciones.js"></script>
