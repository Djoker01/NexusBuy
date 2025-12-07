<?php
include_once 'Layauts/header_general.php';
?>

<title>Productos | NexusBuy</title>

<section class="products-page">
    <!-- Header -->
    <div class="products-header">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h1>Nuestros Productos</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                            <li class="breadcrumb-item active">Productos</li>
                        </ol>
                    </nav>
                </div>
                <div class="col-md-6 text-md-right">
                    <p class="mb-0">Encuentra exactamente lo que buscas</p>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        <!-- Contenido Principal -->
        <main class="products-main">
            <!-- Controles -->
            <div class="products-controls d-flex justify-content-between align-items-center flex-wrap">
                <div class="results-count">
                    Mostrando <strong>1-12</strong> de <strong>156</strong> productos
                </div>
                <div class="d-flex align-items-center gap-3 flex-wrap">
                    <div class="view-options">
                        <button class="view-btn active" data-view="grid">
                            <i class="fas fa-th"></i>
                        </button>
                        <button class="view-btn" data-view="list">
                            <i class="fas fa-list"></i>
                        </button>
                    </div>
                    <select class="sort-select">
                        <option value="popular">Más populares</option>
                        <option value="newest">Más recientes</option>
                        <option value="price-low">Precio: menor a mayor</option>
                        <option value="price-high">Precio: mayor a menor</option>
                        <option value="rating">Mejor calificados</option>
                    </select>
                </div>
            </div>
            <!-- Grid de Productos -->
            <div class="products-grid" id="productsGrid">
                <!-- Los productos se cargarán dinámicamente -->
                <div class="col-12 text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Cargando productos...</span>
                    </div>
                    <p class="text-muted mt-2">Cargando productos...</p>
                </div>
            </div>
            <!-- Paginación -->
            <div class="pagination-container">
                <nav aria-label="Page navigation">
                    <ul class="pagination">
                        <li class="page-item disabled">
                            <a class="page-link" href="#" tabindex="-1">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                        </li>
                        <li class="page-item active"><a class="page-link" href="#">1</a></li>
                        <li class="page-item"><a class="page-link" href="#">2</a></li>
                        <li class="page-item"><a class="page-link" href="#">3</a></li>
                        <li class="page-item">
                            <a class="page-link" href="#">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
        </main>
    </div>
</section>
<script src="producto.js"></script>


<?php
include_once 'Layauts/footer_general.php';
?>