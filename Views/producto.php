<?php
include_once 'Layauts/header_general.php';
?>

<title>Productos | NexusBuy</title>

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

.products-page {
    background-color: #f5f7ff;
    min-height: 100vh;
}

/* Header de productos */
.products-header {
    background: var(--gradient-primary);
    color: white;
    padding: 30px 0;
    margin-bottom: 30px;
}

.products-header h1 {
    font-weight: 700;
    margin-bottom: 10px;
}

.breadcrumb {
    background: transparent;
    padding: 0;
}

.breadcrumb-item a {
    color: rgba(255, 255, 255, 0.9);
}

.breadcrumb-item.active {
    color: white;
}

/* Controles de productos */
.products-controls {
    background: white;
    border-radius: var(--border-radius);
    box-shadow: var(--shadow);
    padding: 20px;
    margin-bottom: 25px;
}

.view-options {
    display: flex;
    gap: 10px;
}

.view-btn {
    background: #f8f9fa;
    border: 2px solid #e9ecef;
    border-radius: 8px;
    padding: 10px 15px;
    cursor: pointer;
    transition: var(--transition);
}

.view-btn.active {
    background: var(--primary);
    color: white;
    border-color: var(--primary);
}

.view-btn:hover:not(.active) {
    border-color: var(--primary);
    color: var(--primary);
}

.sort-select {
    border: 2px solid #e9ecef;
    border-radius: 8px;
    padding: 10px 15px;
    background: white;
    transition: var(--transition);
}

.sort-select:focus {
    border-color: var(--primary);
    outline: none;
}

.results-count {
    color: #6c757d;
    font-weight: 500;
}

/* Layout principal */
.products-layout {
    display: grid;
    grid-template-columns: 280px 1fr;
    gap: 25px;
}

/* Filtros */
.filters-sidebar {
    background: white;
    border-radius: var(--border-radius);
    box-shadow: var(--shadow);
    padding: 25px;
    height: fit-content;
    position: sticky;
    top: 100px;
}

.filter-section {
    margin-bottom: 25px;
    padding-bottom: 20px;
    border-bottom: 1px solid #e9ecef;
}

.filter-section:last-child {
    border-bottom: none;
    margin-bottom: 0;
}

.filter-title {
    font-weight: 600;
    margin-bottom: 15px;
    color: var(--dark);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.filter-title button {
    background: none;
    border: none;
    color: var(--primary);
    font-size: 0.8rem;
    cursor: pointer;
}

.filter-options {
    max-height: 200px;
    overflow-y: auto;
}

.filter-option {
    display: flex;
    align-items: center;
    margin-bottom: 10px;
    cursor: pointer;
}

.filter-option input {
    margin-right: 10px;
}

.filter-option label {
    cursor: pointer;
    margin-bottom: 0;
    flex: 1;
}

.filter-count {
    color: #6c757d;
    font-size: 0.9rem;
}

/* Rango de precios */
.price-range {
    margin-top: 15px;
}

.range-values {
    display: flex;
    justify-content: space-between;
    margin-top: 10px;
    color: #6c757d;
    font-size: 0.9rem;
}

.range-inputs {
    display: flex;
    gap: 10px;
    margin-top: 10px;
}

.range-inputs input {
    flex: 1;
    border: 1px solid #e9ecef;
    border-radius: 6px;
    padding: 8px;
    text-align: center;
}

/* Productos Grid */
.products-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 25px;
}

.products-grid.list-view {
    grid-template-columns: 1fr;
}

/* Tarjetas de producto */
.product-card {
    background: white;
    border-radius: var(--border-radius);
    box-shadow: var(--shadow);
    overflow: hidden;
    transition: var(--transition);
    height: 100%;
    display: flex;
    flex-direction: column;
}

.product-card:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow-hover);
}

.product-card.list-layout {
    flex-direction: row;
    height: auto;
}

.product-image {
    position: relative;
    height: 200px;
    overflow: hidden;
}

.product-card.list-layout .product-image {
    height: 200px;
    width: 200px;
    flex-shrink: 0;
}

.product-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: var(--transition);
}

.product-card:hover .product-image img {
    transform: scale(1.05);
}

.product-badges {
    position: absolute;
    top: 10px;
    left: 10px;
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.product-badge {
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 0.75rem;
    font-weight: 600;
    color: white;
}

.badge-new {
    background: var(--accent);
}

.badge-discount {
    background: var(--danger);
}

.badge-free-shipping {
    background: var(--success);
}

.product-actions {
    position: absolute;
    top: 10px;
    right: 10px;
    display: flex;
    flex-direction: column;
    gap: 5px;
    opacity: 0;
    transition: var(--transition);
}

.product-card:hover .product-actions {
    opacity: 1;
}

.action-btn {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: white;
    border: none;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: var(--transition);
    box-shadow: var(--shadow);
}

.action-btn:hover {
    background: var(--primary);
    color: white;
    transform: scale(1.1);
}

.product-content {
    padding: 20px;
    flex: 1;
    display: flex;
    flex-direction: column;
}

.product-card.list-layout .product-content {
    padding: 25px;
    flex: 1;
}

.product-brand {
    font-size: 0.85rem;
    color: #6c757d;
    margin-bottom: 5px;
}

.product-title {
    font-weight: 600;
    color: var(--dark);
    text-decoration: none;
    margin-bottom: 10px;
    line-height: 1.4;
    display: block;
    transition: var(--transition);
}

.product-title:hover {
    color: var(--primary);
}

.product-rating {
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    gap: 5px;
}

.rating-stars {
    color: var(--warning);
}

.rating-count {
    font-size: 0.85rem;
    color: #6c757d;
}

.product-description {
    color: #6c757d;
    font-size: 0.9rem;
    margin-bottom: 15px;
    display: none;
}

.product-card.list-layout .product-description {
    display: block;
}

.product-price {
    margin-top: auto;
}

.original-price {
    text-decoration: line-through;
    color: #6c757d;
    font-size: 0.9rem;
    margin-right: 5px;
}

.discount-percent {
    color: var(--danger);
    font-size: 0.9rem;
    font-weight: 500;
}

.current-price {
    font-size: 1.3rem;
    font-weight: 700;
    color: var(--primary);
    margin-top: 5px;
}

.product-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 15px;
    padding-top: 15px;
    border-top: 1px solid #e9ecef;
}

.stock-status {
    font-size: 0.85rem;
    padding: 4px 8px;
    border-radius: 4px;
}

.in-stock {
    background: #d4edda;
    color: #155724;
}

.low-stock {
    background: #fff3cd;
    color: #856404;
}

.add-to-cart-btn {
    background: var(--gradient-primary);
    color: white;
    border: none;
    border-radius: 6px;
    padding: 10px 20px;
    font-weight: 600;
    cursor: pointer;
    transition: var(--transition);
    display: flex;
    align-items: center;
    gap: 8px;
}

.add-to-cart-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(67, 97, 238, 0.3);
}

/* Paginación */
.pagination-container {
    margin-top: 40px;
    display: flex;
    justify-content: center;
}

.pagination {
    display: flex;
    gap: 5px;
}

.page-item {
    margin: 0 2px;
}

.page-link {
    border: 2px solid #e9ecef;
    border-radius: 8px;
    padding: 10px 15px;
    color: var(--dark);
    text-decoration: none;
    transition: var(--transition);
    font-weight: 500;
}

.page-link:hover {
    border-color: var(--primary);
    color: var(--primary);
}

.page-item.active .page-link {
    background: var(--primary);
    border-color: var(--primary);
    color: white;
}

/* Filtros móviles */
.mobile-filters {
    display: none;
    margin-bottom: 20px;
}

.filter-toggle-btn {
    background: var(--primary);
    color: white;
    border: none;
    border-radius: 8px;
    padding: 12px 20px;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: var(--transition);
}

.filter-toggle-btn:hover {
    background: var(--secondary);
}

/* Responsive */
@media (max-width: 1024px) {
    .products-layout {
        grid-template-columns: 1fr;
    }
    
    .filters-sidebar {
        position: fixed;
        top: 0;
        left: -100%;
        width: 320px;
        height: 100vh;
        z-index: 1050;
        overflow-y: auto;
        transition: var(--transition);
    }
    
    .filters-sidebar.active {
        left: 0;
    }
    
    .mobile-filters {
        display: block;
    }
    
    .products-grid {
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    }
}

@media (max-width: 768px) {
    .products-controls {
        flex-direction: column;
        gap: 15px;
    }
    
    .view-options {
        justify-content: center;
    }
    
    .products-grid {
        grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
        gap: 15px;
    }
    
    .product-card.list-layout .product-image {
        width: 150px;
        height: 150px;
    }
}

@media (max-width: 576px) {
    .products-header {
        padding: 20px 0;
    }
    
    .products-header h1 {
        font-size: 1.8rem;
    }
    
    .products-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .product-card.list-layout {
        flex-direction: column;
    }
    
    .product-card.list-layout .product-image {
        width: 100%;
        height: 200px;
    }
    
    .filters-sidebar {
        width: 100%;
    }
}

/* Overlay para filtros móviles */
.filters-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    z-index: 1049;
    display: none;
}

.filters-overlay.active {
    display: block;
}
</style>

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
        <!-- Controles móviles -->
        <div class="mobile-filters">
            <button class="filter-toggle-btn" id="filterToggle">
                <i class="fas fa-filter"></i>
                Filtros
                <span class="badge bg-light text-primary ml-2" id="activeFiltersCount">0</span>
            </button>
        </div>

        <!-- Overlay para filtros móviles -->
        <div class="filters-overlay" id="filtersOverlay"></div>

        <div class="products-layout">
            <!-- Sidebar de Filtros -->
            <aside class="filters-sidebar" id="filtersSidebar">
                <div class="filter-header d-flex justify-content-between align-items-center mb-4">
                    <h5 class="mb-0">Filtros</h5>
                    <button class="close-filters" id="closeFilters">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <!-- Filtro de Categorías -->
                <div class="filter-section">
                    <div class="filter-title">
                        <span>Categorías</span>
                    </div>
                    <div class="filter-options" id="categoriesFilter">
                        <!-- Las categorías se cargarán dinámicamente -->
                        <div class="text-center py-3">
                            <div class="spinner-border text-primary" role="status">
                                <span class="sr-only">Cargando categorías...</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filtro de Marcas -->
                <div class="filter-section">
                    <div class="filter-title">
                        <span>Marcas</span>
                        <button type="button" class="btn-clear">Limpiar</button>
                    </div>
                    <div class="filter-options" id="brandsFilter">
                        <div class="filter-option">
                            <input type="checkbox" id="brand-samsung" name="brand" value="samsung">
                            <label for="brand-samsung">Samsung <span class="filter-count">(24)</span></label>
                        </div>
                        <div class="filter-option">
                            <input type="checkbox" id="brand-apple" name="brand" value="apple">
                            <label for="brand-apple">Apple <span class="filter-count">(18)</span></label>
                        </div>
                        <div class="filter-option">
                            <input type="checkbox" id="brand-sony" name="brand" value="sony">
                            <label for="brand-sony">Sony <span class="filter-count">(12)</span></label>
                        </div>
                        <div class="filter-option">
                            <input type="checkbox" id="brand-lg" name="brand" value="lg">
                            <label for="brand-lg">LG <span class="filter-count">(8)</span></label>
                        </div>
                    </div>
                </div>

                <!-- Filtro de Precio -->
                <div class="filter-section">
                    <div class="filter-title">
                        <span>Rango de Precio</span>
                    </div>
                    <div class="price-filter">
                        <input type="range" class="form-range" id="priceRange" min="0" max="1000" step="10">
                        <div class="range-values">
                            <span>$0</span>
                            <span>$1000</span>
                        </div>
                        <div class="range-inputs">
                            <input type="number" id="minPrice" placeholder="Mín" value="0">
                            <input type="number" id="maxPrice" placeholder="Máx" value="1000">
                        </div>
                    </div>
                </div>

                <!-- Filtro de Calificación -->
                <div class="filter-section">
                    <div class="filter-title">
                        <span>Calificación</span>
                    </div>
                    <div class="filter-options">
                        <div class="filter-option">
                            <input type="checkbox" id="rating-5" name="rating" value="5">
                            <label for="rating-5">
                                <span class="rating-stars">
                                    <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                                </span>
                                <span class="filter-count">(128)</span>
                            </label>
                        </div>
                        <div class="filter-option">
                            <input type="checkbox" id="rating-4" name="rating" value="4">
                            <label for="rating-4">
                                <span class="rating-stars">
                                    <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="far fa-star"></i>
                                </span>
                                <span class="filter-count">(256)</span>
                            </label>
                        </div>
                        <div class="filter-option">
                            <input type="checkbox" id="rating-3" name="rating" value="3">
                            <label for="rating-3">
                                <span class="rating-stars">
                                    <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="far fa-star"></i><i class="far fa-star"></i>
                                </span>
                                <span class="filter-count">(189)</span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Filtro de Disponibilidad -->
                <div class="filter-section">
                    <div class="filter-title">
                        <span>Disponibilidad</span>
                    </div>
                    <div class="filter-options">
                        <div class="filter-option">
                            <input type="checkbox" id="stock-in" name="stock" value="in">
                            <label for="stock-in">En stock <span class="filter-count">(542)</span></label>
                        </div>
                        <div class="filter-option">
                            <input type="checkbox" id="stock-out" name="stock" value="out">
                            <label for="stock-out">Agotado <span class="filter-count">(23)</span></label>
                        </div>
                    </div>
                </div>

                <div class="filter-actions mt-4">
                    <button class="btn btn-primary btn-block" id="applyFilters">Aplicar Filtros</button>
                    <button class="btn btn-outline-secondary btn-block mt-2" id="clearFilters">Limpiar Todo</button>
                </div>
            </aside>

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
    </div>
</section>
<script src="producto.js"></script>
<!-- <script>
document.addEventListener('DOMContentLoaded', function() {
    // Variables globales
    let currentView = 'grid';
    let activeFilters = 0;

    // Toggle de filtros móviles
    const filterToggle = document.getElementById('filterToggle');
    const filtersSidebar = document.getElementById('filtersSidebar');
    const filtersOverlay = document.getElementById('filtersOverlay');
    const closeFilters = document.getElementById('closeFilters');

    if (filterToggle && filtersSidebar) {
        filterToggle.addEventListener('click', function() {
            filtersSidebar.classList.add('active');
            filtersOverlay.classList.add('active');
            document.body.style.overflow = 'hidden';
        });

        closeFilters.addEventListener('click', closeFiltersSidebar);
        filtersOverlay.addEventListener('click', closeFiltersSidebar);

        function closeFiltersSidebar() {
            filtersSidebar.classList.remove('active');
            filtersOverlay.classList.remove('active');
            document.body.style.overflow = '';
        }
    }

    // Cambio de vista (grid/list)
    const viewButtons = document.querySelectorAll('.view-btn');
    const productsGrid = document.getElementById('productsGrid');

    viewButtons.forEach(button => {
        button.addEventListener('click', function() {
            const viewType = this.getAttribute('data-view');
            
            // Actualizar botones activos
            viewButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            
            // Cambiar vista
            currentView = viewType;
            if (viewType === 'list') {
                productsGrid.classList.add('list-view');
            } else {
                productsGrid.classList.remove('list-view');
            }
            
            // Recargar productos con nueva vista
            loadProducts();
        });
    });

    // Contador de filtros activos
    function updateActiveFiltersCount() {
        const checkboxes = document.querySelectorAll('input[type="checkbox"]:checked');
        const priceRange = document.getElementById('priceRange');
        const minPrice = document.getElementById('minPrice').value;
        const maxPrice = document.getElementById('maxPrice').value;
        
        let count = checkboxes.length;
        
        // Contar filtro de precio si no está en valores por defecto
        if (parseInt(minPrice) > 0 || parseInt(maxPrice) < 1000) {
            count++;
        }
        
        activeFilters = count;
        document.getElementById('activeFiltersCount').textContent = count;
    }

    // Event listeners para filtros
    const filterCheckboxes = document.querySelectorAll('input[type="checkbox"]');
    filterCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateActiveFiltersCount);
    });

    const priceInputs = document.querySelectorAll('#minPrice, #maxPrice');
    priceInputs.forEach(input => {
        input.addEventListener('input', updateActiveFiltersCount);
    });

    // Rango de precios sincronizado
    const priceRange = document.getElementById('priceRange');
    const minPriceInput = document.getElementById('minPrice');
    const maxPriceInput = document.getElementById('maxPrice');

    if (priceRange && minPriceInput && maxPriceInput) {
        priceRange.addEventListener('input', function() {
            minPriceInput.value = this.value;
            updateActiveFiltersCount();
        });

        minPriceInput.addEventListener('input', function() {
            priceRange.value = this.value;
            updateActiveFiltersCount();
        });

        maxPriceInput.addEventListener('input', function() {
            updateActiveFiltersCount();
        });
    }

    // Aplicar filtros
    const applyFiltersBtn = document.getElementById('applyFilters');
    const clearFiltersBtn = document.getElementById('clearFilters');

    if (applyFiltersBtn) {
        applyFiltersBtn.addEventListener('click', function() {
            loadProducts();
            closeFiltersSidebar();
            
            // Mostrar mensaje de aplicación de filtros
            Swal.fire({
                icon: 'success',
                title: 'Filtros aplicados',
                text: `Mostrando productos con ${activeFilters} filtros activos`,
                timer: 2000,
                showConfirmButton: false
            });
        });
    }

    if (clearFiltersBtn) {
        clearFiltersBtn.addEventListener('click', function() {
            // Limpiar todos los checkboxes
            filterCheckboxes.forEach(checkbox => {
                checkbox.checked = false;
            });
            
            // Restablecer precios
            minPriceInput.value = 0;
            maxPriceInput.value = 1000;
            priceRange.value = 1000;
            
            // Actualizar contador
            updateActiveFiltersCount();
            
            // Recargar productos
            loadProducts();
            
            // Cerrar sidebar en móviles
            closeFiltersSidebar();
        });
    }

    // Cargar productos
    function loadProducts() {
        // Simular carga de productos
        productsGrid.innerHTML = `
            <div class="col-12 text-center py-4">
                <div class="spinner-border text-primary" role="status">
                    <span class="sr-only">Aplicando filtros...</span>
                </div>
                <p class="text-muted mt-2">Aplicando filtros...</p>
            </div>
        `;

        setTimeout(() => {
            // Aquí iría la llamada AJAX real a tu backend
            const productsHTML = generateSampleProducts();
            productsGrid.innerHTML = productsHTML;
        }, 1000);
    }

    // Generar productos de ejemplo (reemplazar con datos reales)
    function generateSampleProducts() {
        const products = [
            {
                id: 1,
                name: "Smartphone Galaxy S24 Ultra",
                brand: "Samsung",
                price: 999,
                originalPrice: 1199,
                discount: 17,
                rating: 4.5,
                reviewCount: 128,
                image: "../Util/Img/Producto/a21s-3.jpg",
                description: "El último flagship de Samsung con cámara de 200MP y S-Pen integrado.",
                stock: "in",
                badges: ["new", "free-shipping"]
            },
            {
                id: 2,
                name: "Auriculares Inalámbricos Noise Cancelling",
                brand: "Sony",
                price: 299,
                originalPrice: 399,
                discount: 25,
                rating: 4.8,
                reviewCount: 89,
                image: "../Util/Img/Producto/producto_default.png",
                description: "Cancelación de ruido líder en la industria con 30h de batería.",
                stock: "in",
                badges: ["discount"]
            },
            // Agregar más productos según sea necesario...
        ];

        let html = '';
        
        products.forEach(product => {
            const isListView = currentView === 'list';
            const listClass = isListView ? 'list-layout' : '';
            
            html += `
                <div class="product-card ${listClass}">
                    <div class="product-image">
                        <img src="${product.image}" alt="${product.name}">
                        <div class="product-badges">
                            ${product.badges.includes('new') ? '<span class="product-badge badge-new">Nuevo</span>' : ''}
                            ${product.badges.includes('discount') ? `<span class="product-badge badge-discount">-${product.discount}%</span>` : ''}
                            ${product.badges.includes('free-shipping') ? '<span class="product-badge badge-free-shipping">Envío Gratis</span>' : ''}
                        </div>
                        <div class="product-actions">
                            <button class="action-btn" title="Agregar a favoritos">
                                <i class="far fa-heart"></i>
                            </button>
                            <button class="action-btn" title="Vista rápida">
                                <i class="far fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    <div class="product-content">
                        <span class="product-brand">${product.brand}</span>
                        <a href="descripcion.php?name=${encodeURIComponent(product.name)}&id=${product.id}" class="product-title">
                            ${product.name}
                        </a>
                        <div class="product-rating">
                            <div class="rating-stars">
                                ${generateStarRating(product.rating)}
                            </div>
                            <span class="rating-count">(${product.reviewCount})</span>
                        </div>
                        ${isListView ? `<p class="product-description">${product.description}</p>` : ''}
                        <div class="product-price">
                            ${product.originalPrice ? `
                                <span class="original-price">$${product.originalPrice}</span>
                                <span class="discount-percent">-${product.discount}%</span>
                            ` : ''}
                            <div class="current-price">$${product.price}</div>
                        </div>
                        <div class="product-meta">
                            <span class="stock-status ${product.stock === 'in' ? 'in-stock' : 'low-stock'}">
                                ${product.stock === 'in' ? 'En Stock' : 'Últimas unidades'}
                            </span>
                            <button class="add-to-cart-btn">
                                <i class="fas fa-shopping-cart"></i>
                                Agregar
                            </button>
                        </div>
                    </div>
                </div>
            `;
        });
        
        return html;
    }

    // Generar estrellas de rating
    function generateStarRating(rating) {
        let stars = '';
        const fullStars = Math.floor(rating);
        const hasHalfStar = rating % 1 !== 0;
        
        for (let i = 0; i < fullStars; i++) {
            stars += '<i class="fas fa-star"></i>';
        }
        
        if (hasHalfStar) {
            stars += '<i class="fas fa-star-half-alt"></i>';
        }
        
        const emptyStars = 5 - Math.ceil(rating);
        for (let i = 0; i < emptyStars; i++) {
            stars += '<i class="far fa-star"></i>';
        }
        
        return stars;
    }

    // Inicializar
    updateActiveFiltersCount();
    loadProducts();
});
</script> -->

<?php
include_once 'Layauts/footer_general.php';
?>