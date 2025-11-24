<?php
include_once 'Layauts/header_general.php';
?>

<title>Mis Favoritos | NexusBuy</title>

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

    /* Favorites Header */
    .favorites-header {
        background: white;
        border-radius: var(--border-radius);
        box-shadow: var(--shadow);
        padding: 30px;
        margin-bottom: 25px;
        border-left: 4px solid var(--primary);
    }

    .favorites-title {
        font-size: 2rem;
        font-weight: 700;
        color: var(--dark);
        margin: 0;
    }

    .favorites-subtitle {
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

    .form-control {
        border: 2px solid #e9ecef;
        border-radius: 8px;
        padding: 10px 15px;
        transition: var(--transition);
    }

    .form-control:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 0.2rem rgba(67, 97, 238, 0.25);
    }

    /* Actions Bar */
    .actions-bar {
        background: white;
        border-radius: var(--border-radius);
        box-shadow: var(--shadow);
        padding: 20px;
        margin-bottom: 25px;
    }

    .view-toggle {
        display: flex;
        gap: 10px;
    }

    .btn-view {
        width: 45px;
        height: 45px;
        border: 2px solid #e9ecef;
        background: white;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: var(--transition);
        color: #6c757d;
    }

    .btn-view.active,
    .btn-view:hover {
        border-color: var(--primary);
        color: var(--primary);
        background: rgba(67, 97, 238, 0.05);
    }

    /* Counter Card */
    .counter-card {
        background: var(--gradient-primary);
        color: white;
        border-radius: var(--border-radius);
        padding: 25px;
        margin-bottom: 25px;
        box-shadow: var(--shadow);
    }

    .counter-icon {
        font-size: 2.5rem;
        margin-bottom: 15px;
    }

    .counter-number {
        font-size: 2rem;
        font-weight: 700;
        margin-bottom: 5px;
    }

    .counter-text {
        opacity: 0.9;
        margin: 0;
    }

    /* Product Cards - Grid View */
    .favorite-card {
        background: white;
        border-radius: var(--border-radius);
        box-shadow: var(--shadow);
        overflow: hidden;
        transition: var(--transition);
        height: 100%;
        position: relative;
    }

    .favorite-card:hover {
        transform: translateY(-5px);
        box-shadow: var(--shadow-hover);
    }

    .product-image {
        height: 200px;
        background: #f8f9fa;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
        position: relative;
    }

    .product-image img {
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
    }

    .favorite-badge {
        position: absolute;
        top: 15px;
        right: 15px;
        width: 40px;
        height: 40px;
        background: rgba(255, 255, 255, 0.95);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--danger);
        font-size: 1.2rem;
        cursor: pointer;
        transition: var(--transition);
        box-shadow: var(--shadow);
    }

    .favorite-badge:hover {
        background: white;
        transform: scale(1.1);
        color: var(--danger);
    }

    .discount-badge {
        position: absolute;
        top: 15px;
        left: 15px;
        background: var(--danger);
        color: white;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 0.8rem;
        font-weight: 600;
    }

    .product-info {
        padding: 20px;
    }

    .product-category {
        color: var(--primary);
        font-size: 0.8rem;
        font-weight: 600;
        text-transform: uppercase;
        margin-bottom: 5px;
    }

    .product-title {
        font-weight: 600;
        color: var(--dark);
        margin-bottom: 10px;
        line-height: 1.4;
        height: 2.8em;
        overflow: hidden;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
    }

    .product-brand {
        color: #6c757d;
        font-size: 0.9rem;
        margin-bottom: 10px;
    }

    .rating-stars {
        color: #ffc107;
        margin-bottom: 10px;
    }

    .pricing {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 8px;
        margin-bottom: 15px;
    }

    .current-price {
        font-size: 1.3rem;
        font-weight: 700;
        color: var(--primary);
    }

    .original-price {
        text-decoration: line-through;
        color: #6c757d;
        font-size: 0.9rem;
    }

    .product-actions {
        display: flex;
        gap: 10px;
    }

    .btn-add-cart {
        flex: 1;
        background: var(--gradient-primary);
        color: white;
        border: none;
        border-radius: 6px;
        padding: 10px 15px;
        font-weight: 600;
        transition: var(--transition);
    }

    .btn-add-cart:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(67, 97, 238, 0.3);
    }

    .btn-quick-view {
        background: white;
        color: var(--dark);
        border: 2px solid #e9ecef;
        border-radius: 6px;
        padding: 10px;
        transition: var(--transition);
    }

    .btn-quick-view:hover {
        border-color: var(--primary);
        color: var(--primary);
    }

    /* List View */
    .list-view-item {
        background: white;
        border-radius: var(--border-radius);
        box-shadow: var(--shadow);
        padding: 20px;
        margin-bottom: 15px;
        transition: var(--transition);
    }

    .list-view-item:hover {
        box-shadow: var(--shadow-hover);
    }

    .list-view-content {
        display: flex;
        align-items: center;
        gap: 20px;
    }

    .list-view-image {
        width: 120px;
        height: 120px;
        background: #f8f9fa;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .list-view-image img {
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
    }

    .list-view-details {
        flex: 1;
    }

    .list-view-actions {
        display: flex;
        flex-direction: column;
        gap: 10px;
        min-width: 150px;
    }

    /* Empty State */
    .empty-favorites {
        text-align: center;
        padding: 80px 20px;
        background: white;
        border-radius: var(--border-radius);
        box-shadow: var(--shadow);
    }

    .empty-favorites-icon {
        font-size: 5rem;
        color: #e9ecef;
        margin-bottom: 20px;
    }

    .empty-favorites-title {
        color: #6c757d;
        margin-bottom: 10px;
        font-weight: 600;
    }

    .empty-favorites-text {
        color: #6c757d;
        margin-bottom: 30px;
        max-width: 400px;
        margin-left: auto;
        margin-right: auto;
    }

    /* Pagination */
    .pagination-container {
        background: white;
        border-radius: var(--border-radius);
        box-shadow: var(--shadow);
        padding: 20px;
        margin-top: 30px;
    }

    .page-link {
        border: 2px solid #e9ecef;
        color: var(--dark);
        padding: 10px 18px;
        margin: 0 5px;
        border-radius: 8px;
        transition: var(--transition);
    }

    .page-link:hover {
        border-color: var(--primary);
        color: var(--primary);
        background: white;
    }

    .page-item.active .page-link {
        background: var(--gradient-primary);
        border-color: var(--primary);
        color: white;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .filters-section .row {
            gap: 15px;
        }

        .list-view-content {
            flex-direction: column;
            align-items: flex-start;
        }

        .list-view-actions {
            flex-direction: row;
            width: 100%;
            justify-content: space-between;
        }

        .list-view-image {
            width: 100%;
            height: 200px;
        }

        .actions-bar .row {
            flex-direction: column;
            gap: 15px;
        }

        .actions-bar .col-md-6 {
            text-align: center !important;
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

    .favorite-card,
    .list-view-item {
        animation: fadeInUp 0.4s ease-out;
    }

    /* Loading States */
    .loading-favorites {
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
                    <div id="vista-lista" style="display: none;">
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