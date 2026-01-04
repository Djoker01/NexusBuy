<?php
include_once 'Layauts/header_general.php';

// Obtener parámetros de la URL
$nombre_categoria = isset($_GET['categoria']) ? urldecode($_GET['categoria']) : null;
$nombre_subcategoria = isset($_GET['subcategoria']) ? urldecode($_GET['subcategoria']) : null;
$nombre_marca = isset($_GET['marca']) ? urldecode($_GET['marca']) : null;
$filtro_nuevos = isset($_GET['filtro']) && $_GET['filtro'] === 'nuevos';

$titulo_pagina = "Todos los Productos";

// Establecer título según parámetros
if ($filtro_nuevos) {
    $titulo_pagina = "Nuevos Productos";
    $_SESSION['filtro_nuevos'] = true;
} elseif ($nombre_marca) {
    $titulo_pagina = "Productos de " . $nombre_marca;
    $_SESSION['marca_filtro'] = $nombre_marca;
} elseif ($nombre_subcategoria) {
    $titulo_pagina = $nombre_subcategoria;
    $_SESSION['subcategoria_filtro'] = $nombre_subcategoria;
} elseif ($nombre_categoria) {
    $titulo_pagina = $nombre_categoria;
    $_SESSION['categoria_filtro'] = $nombre_categoria;
}
?>

<title>
    <?php
    if ($filtro_nuevos) {
        echo "Nuevos Productos | NexusBuy";
    } elseif ($nombre_marca) {
        echo "Productos de " . htmlspecialchars($nombre_marca) . " | NexusBuy";
    } elseif ($nombre_subcategoria) {
        echo htmlspecialchars($nombre_subcategoria) . " | NexusBuy";
    } elseif ($nombre_categoria) {
        echo htmlspecialchars($nombre_categoria) . " | NexusBuy";
    } else {
        echo "Productos | NexusBuy";
    }
    ?>
</title>

<style>
    /* Estilos específicos para producto.php que no están en nexusbuy.css */

    /* Ajustes específicos para el layout de productos */
    .products-main {
        position: relative;
    }

    /* Estilo para el botón de volver específico */
    .btn-outline-secondary.btn-sm i {
        margin-right: 5px;
    }

    .btn-outline-danger.btn-sm i {
        margin-right: 5px;
    }

    /* Estilo específico para el breadcrumb de productos */
    .products-header nav.breadcrumb {
        margin-top: 10px;
    }

    /* Ajuste específico para el contenedor de resultados */
    #resultsCount {
        font-size: 1.1rem;
        font-weight: 600;
    }

    /* Estilo para el estado de carga inicial */
    .products-grid .col-12.text-center {
        grid-column: 1 / -1;
        background: white;
        border-radius: var(--border-radius);
        padding: 60px 20px;
        box-shadow: var(--shadow);
    }

    /* Estilo específico para los badges de stock out */
    .out-of-stock {
        background: #ecd6d6ff;
        color: #471111ff;
    }

    /* Estilo específico para los badges de vista rápida */
    .quick-view-badge.badge-discount {
        background: var(--danger);
    }

    .quick-view-badge.badge-new {
        background: var(--accent);
    }

    /* Estilo específico para el botón de agregar al carrito */
    .add-to-cart-btn.adding {
        position: relative;
        color: transparent;
    }

    .add-to-cart-btn.adding::after {
        content: '';
        position: absolute;
        width: 20px;
        height: 20px;
        border: 2px solid white;
        border-top: 2px solid transparent;
        border-radius: 50%;
        animation: spin 0.8s linear infinite;
    }

    /* Estilo para productos agotados */
    .product-card.out-of-stock-card {
        opacity: 0.8;
        position: relative;
    }

    .product-card.out-of-stock-card::before {
        content: 'AGOTADO';
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: rgba(0, 0, 0, 0.8);
        color: white;
        padding: 10px 20px;
        border-radius: var(--border-radius);
        font-weight: 600;
        z-index: 3;
        text-align: center;
        width: 80%;
    }

    .product-card.out-of-stock-card .product-image img {
        filter: grayscale(1);
    }

    /* Estilo para el texto del header derecho */
    .products-header .text-md-right p {
        font-size: 1.1rem;
        opacity: 0.9;
    }

    /* Ajustes específicos para responsive */
    @media (max-width: 768px) {
        .products-header .text-md-right {
            text-align: left !important;
            margin-top: 15px;
        }

        .products-controls .gap-3 {
            justify-content: center;
        }

        .products-controls .d-flex {
            width: 100%;
            justify-content: space-between;
        }
    }

    @media (max-width: 576px) {
        .pagination {
            gap: 3px;
        }

        .page-link {
            min-width: 40px;
            min-height: 40px;
            padding: 8px 12px;
            font-size: 0.9rem;
        }

        .view-btn {
            min-width: 40px;
            min-height: 40px;
        }
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
                        if ($filtro_nuevos) {
                            echo "Nuevos Productos";
                        } elseif ($nombre_marca) {
                            echo "Productos de " . htmlspecialchars($nombre_marca);
                        } elseif ($nombre_subcategoria) {
                            echo htmlspecialchars($nombre_subcategoria);
                        } elseif ($nombre_categoria) {
                            echo htmlspecialchars($nombre_categoria);
                        } else {
                            echo "Todos los Productos";
                        }
                        ?>
                    </h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                            <li class="breadcrumb-item"><a href="producto.php">Productos</a></li>
                            <?php if ($filtro_nuevos): ?>
                                <li class="breadcrumb-item active" id="filtroNuevosBreadcrumb">
                                    Nuevos Productos
                                </li>
                            <?php elseif ($nombre_marca): ?>
                                <li class="breadcrumb-item active" id="marcaBreadcrumb">
                                    <?php echo htmlspecialchars($nombre_marca); ?>
                                </li>
                            <?php elseif ($nombre_categoria && !$nombre_subcategoria && !$nombre_marca): ?>
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
                    <?php if ($filtro_nuevos): ?>
                        <a href="producto.php" class="btn btn-outline-danger btn-sm">
                            <i class="fas fa-times"></i> Limpiar filtro de nuevos
                        </a>
                    <?php elseif ($nombre_marca): ?>
                        <a href="producto.php" class="btn btn-outline-danger btn-sm">
                            <i class="fas fa-times"></i> Limpiar filtro de marca
                        </a>
                    <?php elseif ($nombre_subcategoria && $nombre_categoria): ?>
                        <a href="producto.php?categoria=<?php echo urlencode($nombre_categoria); ?>"
                            class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Volver a <?php echo htmlspecialchars($nombre_categoria); ?>
                        </a>
                        <a href="producto.php" class="btn btn-outline-danger btn-sm">
                            <i class="fas fa-times"></i> Limpiar filtros
                        </a>
                    <?php elseif ($nombre_categoria && !$nombre_subcategoria): ?>
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
    // Función para cargar el nombre de la categoría/marca si existe
    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        const marcaParam = urlParams.get('marca');
        const categoriaParam = urlParams.get('categoria');

        // Si hay parámetro de marca
        if (marcaParam) {
            const marcaNombre = decodeURIComponent(marcaParam);

            // Actualizar título
            const h1Element = document.querySelector('h1');
            if (h1Element) h1Element.textContent = "Productos de " + marcaNombre;

            // Actualizar breadcrumb
            const breadcrumbActive = document.querySelector('.breadcrumb .active');
            if (breadcrumbActive) {
                breadcrumbActive.textContent = marcaNombre;
            }

            // Actualizar meta título
            document.title = "Productos de " + marcaNombre + ' | NexusBuy';

            // Configurar filtro para el JavaScript
            window.filtroActual = {
                tipo: 'marca',
                valor: marcaNombre
            };

        } else if (categoriaParam) {
            // Código existente para categorías
            if (!isNaN(categoriaParam) && categoriaParam.trim() !== '') {
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
                            const h1Element = document.querySelector('h1');
                            if (h1Element) h1Element.textContent = data.nombre;

                            const breadcrumb = document.querySelector('.breadcrumb .active');
                            if (breadcrumb) breadcrumb.textContent = data.nombre;

                            document.title = data.nombre + ' | NexusBuy';

                            // Configurar filtro
                            window.filtroActual = {
                                tipo: 'categoria',
                                valor: data.nombre
                            };
                        }
                    })
                    .catch(error => console.error('Error:', error));
            } else {
                const categoriaNombre = decodeURIComponent(categoriaParam);
                const h1Element = document.querySelector('h1');
                if (h1Element) h1Element.textContent = categoriaNombre;

                const breadcrumb = document.querySelector('.breadcrumb .active');
                if (breadcrumb) breadcrumb.textContent = categoriaNombre;

                document.title = categoriaNombre + ' | NexusBuy';

                // Configurar filtro
                window.filtroActual = {
                    tipo: 'categoria',
                    valor: categoriaNombre
                };
            }
        } else if (urlParams.get('subcategoria')) {
            const subcategoriaNombre = decodeURIComponent(urlParams.get('subcategoria'));
            window.filtroActual = {
                tipo: 'subcategoria',
                valor: subcategoriaNombre
            };
        } else {
            window.filtroActual = null;
        }
    });
</script>
<script src="producto.js"></script>
<?php
include_once 'Layauts/footer_general.php';
?>