<?php
include_once 'Layauts/header_general.php';
?>

<title>Mis Favoritos | NexusBuy</title>

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