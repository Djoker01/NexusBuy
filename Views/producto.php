<?php
include_once 'Layauts/header_general.php';


// Obtener parámetros de la URL
$nombre_categoria = isset($_GET['categoria']) ? urldecode($_GET['categoria']) : null;
$nombre_subcategoria = isset($_GET['subcategoria']) ? urldecode($_GET['subcategoria']) : null;

$titulo_pagina = "Todos los Productos";

// Establecer título según parámetros
if ($nombre_subcategoria) {
    $titulo_pagina = $nombre_subcategoria;
    $_SESSION['subcategoria_filtro'] = $nombre_subcategoria;
} elseif ($nombre_categoria) {
    $titulo_pagina = $nombre_categoria;
    $_SESSION['categoria_filtro'] = $nombre_categoria;
}
?>

<title>
    <?php
    if ($nombre_subcategoria) {
        echo htmlspecialchars($nombre_subcategoria) . " | NexusBuy";
    } elseif ($nombre_categoria) {
        echo htmlspecialchars($nombre_categoria) . " | NexusBuy";
    } else {
        echo "Productos | NexusBuy";
    }
    ?>
</title>

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

    .featured-badge {
        background: var(--gradient-accent);
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

    .out-of-stock{
        background: #ecd6d6ff;
        color: #471111ff;
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

   /* Estilos para Vista Rápida */
.quick-view-modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 1060;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
}

.quick-view-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.7);
    backdrop-filter: blur(5px);
}

.quick-view-content {
    position: relative;
    background: white;
    border-radius: var(--border-radius);
    box-shadow: var(--shadow-hover);
    max-width: 1000px;
    width: 100%;
    max-height: 90vh;
    overflow-y: auto;
    z-index: 1061;
    animation: slideIn 0.3s ease;
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.quick-view-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 25px;
    border-bottom: 1px solid #e9ecef;
    background: var(--gradient-primary);
    color: white;
    border-radius: var(--border-radius) var(--border-radius) 0 0;
    position: sticky;
    top: 0;
    z-index: 1;
}

.quick-view-header h3 {
    margin: 0;
    font-weight: 600;
    font-size: 1.5rem;
}

.quick-view-close {
    background: none;
    border: none;
    color: white;
    font-size: 1.5rem;
    cursor: pointer;
    padding: 5px;
    transition: var(--transition);
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.quick-view-close:hover {
    background: rgba(255, 255, 255, 0.1);
    transform: scale(1.1);
}

.quick-view-body {
    padding: 30px;
}

.quick-view-image {
    position: relative;
    border-radius: var(--border-radius);
    overflow: hidden;
    margin-bottom: 20px;
    height: 400px;
}

.quick-view-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.quick-view-image:hover img {
    transform: scale(1.05);
}

.quick-view-badges {
    position: absolute;
    top: 15px;
    left: 15px;
    display: flex;
    gap: 5px;
}

.quick-view-badge{
    color: white;
    border-radius: 6px;
    z-index: 2;
    font-weight: 600;
    font-size: 0.9rem;
    padding: 8px 12px;
}

.quick-view-badge.badge-discount {
    background: var(--danger);
}

.quick-view-badge.badge-new {
    background: var(--accent);
}

.quick-view-details .product-brand {
    font-size: 0.9rem;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 5px;
    display: block;
}

.quick-view-details .product-title {
    font-size: 1.8rem;
    font-weight: 700;
    color: var(--dark);
    margin-bottom: 15px;
    line-height: 1.3;
}

.quick-view-details .current-price {
    font-size: 2rem;
    font-weight: 700;
    color: var(--primary);
}

.quick-view-details .original-price {
    font-size: 1.3rem;
    color: #6c757d;
}

.quantity-selector {
    display: flex;
    align-items: center;
    margin-bottom: 20px;
}

.quantity-selector label {
    font-weight: 600;
    margin-right: 15px;
    color: var(--dark);
}

.quantity-controls {
    display: flex;
    align-items: center;
    border: 2px solid #e9ecef;
    border-radius: 8px;
    overflow: hidden;
}

.quantity-btn {
    background: #f8f9fa;
    border: none;
    padding: 12px 18px;
    cursor: pointer;
    transition: var(--transition);
    font-weight: 600;
    font-size: 1.2rem;
    color: var(--dark);
    min-width: 45px;
}

.quantity-btn:hover {
    background: var(--primary);
    color: white;
}

.quantity-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

#quickViewQuantity {
    width: 70px;
    border: none;
    text-align: center;
    padding: 12px;
    background: white;
    font-weight: 600;
    font-size: 1.1rem;
}

#quickViewQuantity:focus {
    outline: none;
}

.action-buttons {
    display: flex;
    gap: 15px;
    margin-top: 25px;
}

.action-buttons .btn {
    flex: 1;
    padding: 15px;
    font-size: 1.1rem;
    font-weight: 600;
}

.product-meta {
    border-top: 1px solid #e9ecef;
    padding-top: 20px;
    margin-top: 25px;
}

.meta-item {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 10px;
    color: #6c757d;
    font-size: 0.95rem;
}

.meta-item i {
    color: var(--primary);
    width: 20px;
    text-align: center;
}

/* Responsive */
@media (max-width: 992px) {
    .quick-view-content {
        margin: 10px;
        max-height: 95vh;
    }
    
    .quick-view-body {
        padding: 20px;
    }
    
    .quick-view-image {
        height: 300px;
    }
    
    .quick-view-details .product-title {
        font-size: 1.5rem;
    }
    
    .action-buttons {
        flex-direction: column;
    }
}

@media (max-width: 768px) {
    .quick-view-content {
        max-width: 95%;
    }
    
    .quick-view-image {
        height: 250px;
    }
    
    .quick-view-details .product-title {
        font-size: 1.3rem;
    }
    
    .action-buttons .btn {
        font-size: 1rem;
        padding: 12px;
    }
}

/* CSS para animación del boton de agregar al carrito */
.cart-animation {
  transform: scale(1.1);
  transition: transform 0.3s ease;
}
</style>

<section class="products-page">
    <!-- Header -->
    <div class="products-header">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h1>
    <?php
    if ($nombre_subcategoria) {
        echo htmlspecialchars($nombre_subcategoria);
    } elseif ($nombre_categoria) {
        echo htmlspecialchars($nombre_categoria);
    } else {
        echo "Nuestros Productos";
    }
    ?>
</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                            <li class="breadcrumb-item"><a href="producto.php">Productos</a></li>
                            <?php if ($nombre_categoria && !$nombre_subcategoria): ?>
                                <li class="breadcrumb-item active" id="categoriaBreadcrumb">
                <?php echo htmlspecialchars($nombre_categoria); ?>
            </li>
        <?php elseif ($nombre_subcategoria): ?>
            <?php if ($nombre_categoria): ?>
                <li class="breadcrumb-item">
                    <a href="producto.php?categoria=<?php echo urlencode($nombre_categoria); ?>" id="categoriaBreadcrumb">
                        <?php echo htmlspecialchars($nombre_categoria); ?>
                    </a>
                </li>
            <?php endif; ?>
            <li class="breadcrumb-item active" id="subcategoriaBreadcrumb">
                <?php echo htmlspecialchars($nombre_subcategoria); ?>
            </li>
        <?php else: ?>
            <li class="breadcrumb-item active">Todos los productos</li>
        <?php endif; ?>
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
            <div class="results-count" id="resultsCount">
                <!-- Se actualizará con JavaScript -->
            </div>
            <div class="d-flex align-items-center gap-3 flex-wrap">
    <?php if($nombre_subcategoria && $nombre_categoria): ?>
        <a href="producto.php?categoria=<?php echo urlencode($nombre_categoria); ?>" 
           class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Volver a <?php echo htmlspecialchars($nombre_categoria); ?>
        </a>
        <a href="producto.php" class="btn btn-outline-danger btn-sm">
            <i class="fas fa-times"></i> Limpiar filtros
        </a>
    <?php elseif($nombre_categoria && !$nombre_subcategoria): ?>
        <a href="producto.php" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-times"></i> Limpiar filtro
        </a>
    <?php endif; ?>
                
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

<script>
// Función para cargar el nombre de la categoría si existe
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const categoriaParam = urlParams.get('categoria');
    
    if (categoriaParam) {
        // Verificar si el parámetro es un ID numérico
        if (!isNaN(categoriaParam) && categoriaParam.trim() !== '') {
            // Es un ID, obtener nombre de la categoría
            const formData = new FormData();
            formData.append('funcion', 'obtener_categoria_por_id');
            formData.append('id_categoria', categoriaParam);
            
            fetch('../Controllers/CategoriaController.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.nombre) {
                    // Actualizar título
                    const h1Element = document.querySelector('h1');
                    if (h1Element) h1Element.textContent = data.nombre;
                    
                    // Actualizar breadcrumb
                    const breadcrumb = document.querySelector('.breadcrumb .active');
                    if (breadcrumb) {
                        breadcrumb.textContent = data.nombre;
                    }
                    
                    // Actualizar meta título
                    document.title = data.nombre + ' | NexusBuy';
                }
            })
            .catch(error => console.error('Error:', error));
        } else {
            // Es un nombre, usar directamente
            const categoriaNombre = decodeURIComponent(categoriaParam);
            
            // Actualizar título
            const h1Element = document.querySelector('h1');
            if (h1Element) h1Element.textContent = categoriaNombre;
            
            // Actualizar breadcrumb
            const breadcrumb = document.querySelector('.breadcrumb .active');
            if (breadcrumb) {
                breadcrumb.textContent = categoriaNombre;
            }
            
            // Actualizar meta título
            document.title = categoriaNombre + ' | NexusBuy';
        }
    }
});
</script>
<script src="producto.js"></script>
<?php
include_once 'Layauts/footer_general.php';
?>