<?php
$base_path_url = ""; // Ya está en la raíz
$base_path = "../";
$pageTitle = "Todo los Productos";
$pageName = "Productos";
$breadcrumb = "desactive";
$checkout = "desactive";
$notificaciones = "desactive";
$soporte = "desactive";
// $pageDescription = "Análisis detallado de toda la plataforma";

include_once 'layouts/header.php';

// Limpiar cualquier filtro anterior cuando haya una nueva búsqueda
if (isset($_GET['busqueda'])) {
    unset($_SESSION['marca_filtro']);
    unset($_SESSION['categoria_filtro']);
    unset($_SESSION['subcategoria_filtro']);
    unset($_SESSION['filtro_nuevos']);

    // Solo guardar la búsqueda actual
    $_SESSION['busqueda_filtro'] = urldecode($_GET['busqueda']);
}

// // Obtener parámetros de la URL
// $nombre_categoria = isset($_GET['categoria']) ? urldecode($_GET['categoria']) : null;
// $nombre_subcategoria = isset($_GET['subcategoria']) ? urldecode($_GET['subcategoria']) : null;
// $nombre_marca = isset($_GET['marca']) ? urldecode($_GET['marca']) : null;
// $busqueda = isset($_GET['busqueda']) ? urldecode($_GET['busqueda']) : null;
// $filtro_nuevos = isset($_GET['filtro']) && $_GET['filtro'] === 'nuevos';


$titulo_pagina = "Todos los Productos";

// // Establecer título según parámetros
// if ($busqueda) {
//     $titulo_pagina = "Resultados para: " . htmlspecialchars($busqueda);
//     $_SESSION['busqueda_filtro'] = $busqueda;
// } elseif ($filtro_nuevos) {
//     $titulo_pagina = "Nuevos Productos";
//     $_SESSION['filtro_nuevos'] = true;
// } elseif ($nombre_marca) {
//     $titulo_pagina = "Productos de " . $nombre_marca;
//     $_SESSION['marca_filtro'] = $nombre_marca;
// } elseif ($nombre_subcategoria) {
//     $titulo_pagina = $nombre_subcategoria;
//     $_SESSION['subcategoria_filtro'] = $nombre_subcategoria;
// } elseif ($nombre_categoria) {
//     $titulo_pagina = $nombre_categoria;
//     $_SESSION['categoria_filtro'] = $nombre_categoria;
// } 
?>

<title>
    <?php
    if ($busqueda) {
        echo "Buscar: " . htmlspecialchars($busqueda) . " | NexusBuy";
    } elseif ($filtro_nuevos) {
        echo "Productos Nuevos";
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
</title>

<link rel="stylesheet" href="../Util/Css/cliente/producto.css">

<section class="products-page">
    <!-- Header -->
    <!-- <div class="products-header">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h1>
                        <?php
                        if ($busqueda) {
                            echo "Resultados para: " . htmlspecialchars($busqueda);
                        } elseif ($filtro_nuevos) {
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
                            <?php if ($busqueda): ?>
                                <li class="breadcrumb-item active" id="busquedaBreadcrumb">
                                    Busqueda: "<?php echo htmlspecialchars($busqueda); ?>"
                                </li>
                            <?php elseif ($filtro_nuevos): ?>
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
    </div> -->

    <div class="container-fluid">
        <!-- Contenido Principal -->
        <main class="products-main">
            <!-- Controles -->
            <div class="products-controls d-flex justify-content-between align-items-center flex-wrap">
                <div class="results-count" id="resultsCount">
                    <!-- Se actualizará con JavaScript -->
                </div>
                <div class="d-flex align-items-center gap-3 flex-wrap">
                    <?php if ($busqueda): ?>
                        <a href="producto.php" class="btn btn-outline-danger btn-sm">
                            <i class="fas fa-times"></i> Limpiar búsqueda
                        </a>
                    <?php elseif ($filtro_nuevos): ?>
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
        const busquedaParam = urlParams.get('busqueda');
        const marcaParam = urlParams.get('marca');
        const categoriaParam = urlParams.get('categoria');
        const subcategoriaParam = urlParams.get('subcategoria');
        const filtroParam = urlParams.get('filtro');

        if (busquedaParam) {
            window.filtroActual = {
                tipo: 'busqueda',
                valor: decodeURIComponent(busquedaParam)
            };
        } else if (filtroParam === 'nuevos') {
            window.filtroActual = {
                tipo: 'nuevos',
                valor: true
            };

            // Si hay parámetro de marca
        } else if (marcaParam) {
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
include_once 'Layouts/footer_general.php';
?>