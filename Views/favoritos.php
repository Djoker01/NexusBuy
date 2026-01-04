<?php
include_once 'Layauts/header_general.php';
?>

<title>Mis Favoritos | NexusBuy</title>

<style>
    /* ESTILOS ESPECÍFICOS DE MIS PEDIDOS - No están en nexusbuy.css */
    
    /* Header de pedidos - diseño específico */
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

    /* Filtros específicos */
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

    /* Tarjetas de pedido específicas */
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

    /* Estados de pedido específicos */
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

    /* Imágenes de productos en cards específicas */
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

    /* Ajustes específicos para el layout generado por JavaScript */
    .pedido-card .d-flex.align-items-center {
        display: flex !important;
        align-items: center !important;
        gap: 10px;
    }

    .pedido-card .producto-img.mr-2 {
        margin-right: 0.5rem !important;
    }

    /* Botones en las cards específicas */
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

    /* Estado vacío específico */
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

    /* Ajustes para los precios específicos */
    .pedido-card .text-danger {
        color: var(--danger) !important;
        font-size: 1.3rem;
        font-weight: 700;
    }

    /* Ajuste para el resumen de precios */
    .pedido-card .bg-light {
        background: #f8f9fa !important;
        border-radius: 8px;
        padding: 15px;
    }

    /* Badges específicos */
    .badge.badge-light {
        background: #f8f9fa;
        color: #6c757d;
        padding: 8px 12px;
        border-radius: 20px;
        font-size: 0.8rem;
    }

    /* Responsive específico para mis pedidos */
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

    /* Animaciones específicas para mis pedidos */
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

    /* Loading específico */
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

    /* Ajustes de texto específicos */
    .pedido-card small.d-block {
        display: block;
        line-height: 1.3;
    }

    .pedido-card .font-weight-bold {
        font-weight: 600;
    }

    .pedido-card h6 {
        color: var(--dark);
        font-weight: 600;
        margin-bottom: 15px;
        font-size: 1rem;
    }
</style>

<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Mis Favoritos</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="../index.php">Home</a></li>
                    <li class="breadcrumb-item active">Mis Favoritos</li>
                </ol>
            </div>
        </div>
    </div>
</section>

<section class="content">
    <div class="container-fluid">
        <!-- Header -->
        <div class="favorites-header">
            <h1 class="favorites-title">
                <i class="fas fa-heart mr-2"></i>Mis Productos Favoritos
            </h1>
            <p class="favorites-subtitle">Guarda tus productos favoritos y accede a ellos fácilmente</p>
        </div>

        <div class="row">
            <!-- Main Content -->
            <div class="col-lg-9">
                <!-- Filters -->
                <div class="filters-section">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="filter-group">
                                <label class="filter-label">Categoría</label>
                                <select class="form-control" id="filtro-categoria">
                                    <option value="">Todas las categorías</option>
                                    <!-- Se llenará dinámicamente -->
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="filter-group">
                                <label class="filter-label">Rango de Precio</label>
                                <select class="form-control" id="filtro-precio">
                                    <option value="">Todos los precios</option>
                                    <option value="0-50">$0 - $50</option>
                                    <option value="50-100">$50 - $100</option>
                                    <option value="100-200">$100 - $200</option>
                                    <option value="200-500">$200 - $500</option>
                                    <option value="500-1000">$500 - $1000</option>
                                    <option value="1000-999999">Más de $1000</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="filter-group">
                                <label class="filter-label">Ordenar por</label>
                                <select class="form-control" id="filtro-orden">
                                    <option value="recientes">Más recientes</option>
                                    <option value="antiguos">Más antiguos</option>
                                    <option value="precio-asc">Precio: Menor a Mayor</option>
                                    <option value="precio-desc">Precio: Mayor a Menor</option>
                                    <option value="nombre-asc">Nombre: A-Z</option>
                                    <option value="nombre-desc">Nombre: Z-A</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Actions Bar -->
                <div class="actions-bar">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <div class="view-toggle">
                                <button class="btn-view active" id="btn-vista-grid" title="Vista de cuadrícula">
                                    <i class="fas fa-th"></i>
                                </button>
                                <button class="btn-view" id="btn-vista-lista" title="Vista de lista">
                                    <i class="fas fa-list"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-6 text-md-right">
                            <button type="button" class="btn btn-outline-danger" id="btn-limpiar-favoritos">
                                <i class="fas fa-trash mr-2"></i>
                                Limpiar Todos los Favoritos
                            </button>
                            <button type="button" class="btn btn-outline-primary ml-2" id="btn-compartir-favoritos">
                                <i class="fas fa-share-alt mr-2"></i>
                                Compartir Lista
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Counter -->
                <div class="counter-card">
                    <div class="text-center">
                        <div class="counter-icon">
                            <i class="fas fa-heart"></i>
                        </div>
                        <div class="counter-number" id="contador-favoritos">0</div>
                        <p class="counter-text">productos en tus favoritos</p>
                    </div>
                </div>

                <!-- Favorites Content -->
                <div id="favorites-content">
                    <!-- Grid View -->
                    <div id="vista-grid">
                        <div class="row" id="lista-favoritos">
                            <!-- Loading State -->
                            <div class="col-12">
                                <div class="loading-favorites">
                                    <div class="loading-spinner"></div>
                                    <p class="text-muted">Cargando tus productos favoritos...</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- List View -->
                    <div id="vista-lista" style="display: none">
                        <div id="lista-favoritos-tabla">
                            <!-- Se llenará dinámicamente -->
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div id="favoritos-vacios" class="empty-favorites" style="display: none;">
                        <div class="empty-favorites-icon">
                            <i class="fas fa-heart"></i>
                        </div>
                        <h3 class="empty-favorites-title">Tu lista de favoritos está vacía</h3>
                        <p class="empty-favorites-text">
                            Descubre productos increíbles y agrégalos a tus favoritos para guardarlos para después.
                        </p>
                        <div class="row justify-content-center">
                            <div class="col-md-8">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <a href="../index.php" class="btn btn-primary btn-block btn-lg">
                                            <i class="fas fa-shopping-bag mr-2"></i>
                                            Explorar Productos
                                        </a>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <a href="mis_pedidos.php" class="btn btn-outline-primary btn-block btn-lg">
                                            <i class="fas fa-history mr-2"></i>
                                            Ver Mis Pedidos
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                <div class="pagination-container">
                    <nav aria-label="Paginación de favoritos">
                        <ul class="pagination justify-content-center" id="paginacion-favoritos">
                            <!-- Se llenará dinámicamente -->
                        </ul>
                    </nav>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-3">
                <!-- Quick Actions -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-bolt mr-2"></i>
                            Acciones Rápidas
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button class="btn btn-outline-primary" onclick="window.location.href='producto.php'">
                                <i class="fas fa-plus mr-2"></i>
                                Agregar Más Productos
                            </button>
                            <button class="btn btn-outline-success" id="btn-comprar-todo">
                                <i class="fas fa-shopping-cart mr-2"></i>
                                Comprar Todos
                            </button>
                            <button class="btn btn-outline-info" id="btn-exportar-favoritos">
                                <i class="fas fa-download mr-2"></i>
                                Exportar Lista
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Recently Viewed -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-eye mr-2"></i>
                            Vistos Recientemente
                        </h5>
                    </div>
                    <div class="card-body">
                        <div id="recientes-vistos">
                            <p class="text-muted text-center small">No hay productos vistos recientemente</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Modal Compartir Favoritos -->
<div class="modal fade" id="modalCompartirFavoritos" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-share-alt mr-2"></i>
                    Compartir Lista de Favoritos
                </h5>
                <button type="button" class="close text-white" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Comparte tu lista de productos favoritos con amigos y familiares:</p>
                
                <div class="form-group">
                    <label for="enlace-compartir" class="font-weight-bold">Enlace para compartir:</label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="enlace-compartir" readonly>
                        <div class="input-group-append">
                            <button class="btn btn-outline-primary" type="button" onclick="copiarEnlace()">
                                <i class="fas fa-copy mr-2"></i>Copiar
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="mt-4">
                    <label class="font-weight-bold mb-3">Compartir en redes sociales:</label>
                    <div class="row text-center">
                        <div class="col-4 mb-3">
                            <button type="button" class="btn btn-primary btn-block" onclick="compartirFacebook()">
                                <i class="fab fa-facebook-f mr-2"></i> Facebook
                            </button>
                        </div>
                        <div class="col-4 mb-3">
                            <button type="button" class="btn btn-info btn-block" onclick="compartirTwitter()">
                                <i class="fab fa-twitter mr-2"></i> Twitter
                            </button>
                        </div>
                        <div class="col-4 mb-3">
                            <button type="button" class="btn btn-danger btn-block" onclick="compartirPinterest()">
                                <i class="fab fa-pinterest-p mr-2"></i> Pinterest
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<?php include_once 'Layauts/footer_general.php'; ?>
<script src="favoritos.js"></script>