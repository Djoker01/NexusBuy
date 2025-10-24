<?php

include_once 'Layauts/header_general.php';
?>

<title>Mis Favoritos | NexusBuy</title>
<style>
    .favorito-card {
        border: 1px solid #e3e6f0;
        border-radius: 8px;
        transition: all 0.3s ease;
        height: 100%;
    }
    .favorito-card:hover {
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        transform: translateY(-2px);
    }
    .producto-img {
        height: 200px;
        object-fit: cover;
        border-radius: 6px 6px 0 0;
    }
    .btn-favorito {
        position: absolute;
        top: 10px;
        right: 10px;
        background: rgba(255,255,255,0.9);
        border: none;
        border-radius: 50%;
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
    }
    .btn-favorito:hover {
        background: white;
        transform: scale(1.1);
    }
    .btn-favorito.activo {
        color: #dc3545;
    }
    .precio-original {
        text-decoration: line-through;
        color: #6c757d;
    }
    .badge-descuento {
        position: absolute;
        top: 10px;
        left: 10px;
        font-size: 12px;
    }
    .empty-favorites {
        text-align: center;
        padding: 60px 20px;
    }
    .empty-favorites-icon {
        font-size: 80px;
        color: #e9ecef;
        margin-bottom: 20px;
    }
    .filtros-favoritos {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 20px;
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
        <!-- Filtros y Acciones -->
        <div class="row mb-4">
            <div class="col-md-8">
                <div class="filtros-favoritos">
                    <div class="row align-items-center">
                        <div class="col-md-4">
                            <select class="form-control" id="filtro-categoria">
                                <option value="">Todas las categorías</option>
                                <!-- Se llenará dinámicamente -->
                            </select>
                        </div>
                        <div class="col-md-4">
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
                        <div class="col-md-4">
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
            <div class="col-md-4">
                <div class="d-flex justify-content-end">
                    <div class="btn-group">
                        <button type="button" class="btn btn-outline-secondary" id="btn-vista-lista">
                            <i class="fas fa-list"></i>
                        </button>
                        <button type="button" class="btn btn-outline-primary active" id="btn-vista-grid">
                            <i class="fas fa-th"></i>
                        </button>
                    </div>
                    <button type="button" class="btn btn-outline-danger ml-3" id="btn-limpiar-favoritos">
                        <i class="fas fa-trash mr-2"></i>Limpiar Todo
                    </button>
                </div>
            </div>
        </div>

        <!-- Contador de Favoritos -->
        <div class="row mb-3">
            <div class="col-12">
                <div class="alert alert-info d-flex justify-content-between align-items-center">
                    <div>
                        <i class="fas fa-heart mr-2"></i>
                        <strong id="contador-favoritos">0 productos</strong> en tus favoritos
                    </div>
                    <div>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="btn-compartir-favoritos">
                            <i class="fas fa-share-alt mr-1"></i> Compartir Lista
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Lista de Favoritos -->
        <div class="row">
            <div class="col-12">
                <!-- Vista Grid -->
                <div id="vista-grid">
                    <div class="row" id="lista-favoritos">
                        <!-- Loading -->
                        <div class="col-12 text-center py-5">
                            <div class="spinner-border text-primary" role="status">
                                <span class="sr-only">Cargando favoritos...</span>
                            </div>
                            <p class="text-muted mt-2">Cargando tus productos favoritos...</p>
                        </div>
                    </div>
                </div>

                <!-- Vista Lista -->
                <div id="vista-lista" style="display: none;">
                    <div class="card">
                        <div class="card-body">
                            <div id="lista-favoritos-tabla">
                                <!-- Se llenará dinámicamente -->
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Estado vacío -->
                <div id="favoritos-vacios" class="empty-favorites" style="display: none;">
                    <div class="empty-favorites-icon">
                        <i class="fas fa-heart"></i>
                    </div>
                    <h3 class="text-muted">Tu lista de favoritos está vacía</h3>
                    <p class="text-muted mb-4">Descubre productos increíbles y agrégalos a tus favoritos para guardarlos para después.</p>
                    <div class="row justify-content-center">
                        <div class="col-md-6">
                            <div class="row">
                                <div class="col-6">
                                    <a href="../index.php" class="btn btn-primary btn-block">
                                        <i class="fas fa-shopping-bag mr-2"></i>Explorar Productos
                                    </a>
                                </div>
                                <div class="col-6">
                                    <a href="mis_pedidos.php" class="btn btn-outline-primary btn-block">
                                        <i class="fas fa-history mr-2"></i>Ver Mis Pedidos
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Paginación -->
        <div class="row mt-4">
            <div class="col-12">
                <nav aria-label="Paginación de favoritos">
                    <ul class="pagination justify-content-center" id="paginacion-favoritos">
                        <!-- Se llenará dinámicamente -->
                    </ul>
                </nav>
            </div>
        </div>
    </div>
</section>

<!-- Modal Compartir Favoritos -->
<div class="modal fade" id="modalCompartirFavoritos" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Compartir Lista de Favoritos</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Comparte tu lista de productos favoritos con otros:</p>
                
                <div class="form-group">
                    <label for="enlace-compartir" class="font-weight-bold">Enlace para compartir:</label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="enlace-compartir" readonly>
                        <div class="input-group-append">
                            <button class="btn btn-outline-secondary" type="button" onclick="copiarEnlace()">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="mt-3">
                    <label class="font-weight-bold">Compartir en redes sociales:</label>
                    <div class="d-flex justify-content-around mt-2">
                        <button type="button" class="btn btn-primary btn-sm" onclick="compartirFacebook()">
                            <i class="fab fa-facebook-f mr-1"></i> Facebook
                        </button>
                        <button type="button" class="btn btn-info btn-sm" onclick="compartirTwitter()">
                            <i class="fab fa-twitter mr-1"></i> Twitter
                        </button>
                        <button type="button" class="btn btn-danger btn-sm" onclick="compartirPinterest()">
                            <i class="fab fa-pinterest-p mr-1"></i> Pinterest
                        </button>
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