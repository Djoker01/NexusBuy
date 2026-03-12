<?php
$base_path = "../";
// Titulo de la página
$pageTitle = "Mis Productos";
// Titulo de la vista
$pageName = "Mis Productos";
// Descripción de la vista
$pageDescription = "Gestiona tu catálogo de productos.";

// Incluir Header
include_once '../layouts/header.php';

// Incluir modelos necesarios
require_once '../../../Models/Producto.php';
require_once '../../../Models/Categoria.php';
require_once '../../../Models/Subcategoria.php';
require_once '../../../Models/Tienda.php';
require_once '../../../Models/Imagen.php';

$tienda = new Tienda();
$id_tienda = ($GLOBALS['tienda_actual']->id ?? 'SIN TIENDA');

// =============================================
// FILTROS - Recibir y sanitizar parámetros GET
// =============================================
$buscar = isset($_GET['buscar']) ? trim($_GET['buscar']) : '';
$categoria = isset($_GET['categoria']) ? (int)$_GET['categoria'] : 0;
$subcategoria = isset($_GET['subcategoria']) ? (int)$_GET['subcategoria'] : 0;
$estado = isset($_GET['estado']) ? $_GET['estado'] : '';
$stock = isset($_GET['stock']) ? $_GET['stock'] : '';
$destacado = isset($_GET['destacado']) ? (int)$_GET['destacado'] : 0;
$fecha_desde = isset($_GET['fecha_desde']) ? $_GET['fecha_desde'] : '';
$fecha_hasta = isset($_GET['fecha_hasta']) ? $_GET['fecha_hasta'] : '';
$ordenar_por = isset($_GET['ordenar_por']) ? $_GET['ordenar_por'] : 'fecha_asc';
$pagina = isset($_GET['pagina']) ? max(1, (int)$_GET['pagina']) : 1;
$por_pagina = 10;
$offset = ($pagina - 1) * $por_pagina;

// =============================================
// CONSTRUIR FILTROS PARA LA CONSULTA
// =============================================
$filtros = [
    'id_tienda' => $id_tienda,
    'buscar' => $buscar,
    'categoria' => $categoria,
    'subcategoria' => $subcategoria,
    'estado' => $estado,
    'stock' => $stock,
    'destacado' => $destacado,
    'fecha_desde' => $fecha_desde,
    'fecha_hasta' => $fecha_hasta,
    'ordenar_por' => $ordenar_por,
    'limite' => $por_pagina,
    'offset' => $offset
];


// =============================================
// OBTENER DATOS CON FILTROS
// =============================================
$producto = new Producto();
$productos = $producto->obtener_por_tienda_con_filtros($filtros);

// Verificar que $productos sea un array antes de usar count()
if (!is_array($productos)) {
    $productos = [];
    error_log("Productos no es un array, se inicializa como vacío");
}

if (!is_array($productos) || empty($productos)) {
    // Si no hay productos, probemos una consulta sin filtros
    $filtros_simples = ['id_tienda' => $id_tienda, 'limite' => 10, 'offset' => 0];
    $productos_simples = $producto->obtener_por_tienda_con_filtros($filtros_simples);
}


$total_productos = $producto->contar_por_tienda_con_filtros($filtros);
// Asegurar que $total_productos sea un número
$total_productos = is_numeric($total_productos) ? (int)$total_productos : 0;

$total_paginas = $total_productos > 0 ? ceil($total_productos / $por_pagina) : 0;

// Obtener categorías para los filtros
$categoria_model = new Categoria();
$categorias = $categoria_model->obtener_categorias_activas();

// Obtener subcategorías si hay categoría seleccionada
$subcategorias = [];
if ($categorias > 0) {
    $subcategoria_model = new Subcategoria();
    $subcategorias = $subcategoria_model->obtener_subcategorias_por_categoria($categoria);
}

// Estadísticas para los contadores
$stats = $producto->obtener_estadisticas_tienda($id_tienda);

?>


<style>
    /* Action Bar */
    .action-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .search-box {
        display: flex;
        align-items: center;
        background: white;
        border: 1px solid #e9ecef;
        border-radius: 8px;
        padding: 0.5rem 1rem;
        width: 300px;
    }

    .search-box i {
        color: #6c757d;
        margin-right: 0.5rem;
    }

    .search-box input {
        border: none;
        outline: none;
        width: 100%;
        font-size: 0.875rem;
    }

    .filters {
        display: flex;
        gap: 0.5rem;
    }

    .filter-select {
        padding: 0.5rem 2rem 0.5rem 1rem;
        border: 1px solid #e9ecef;
        border-radius: 8px;
        background: white;
        font-size: 0.875rem;
        color: #212529;
        cursor: pointer;
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%236c757d' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 0.5rem center;
    }

    .btn-primary {
        background: #4361ee;
        color: white;
        border: none;
        padding: 0.5rem 1rem;
        border-radius: 8px;
        font-size: 0.875rem;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        cursor: pointer;
        transition: background 0.2s;
    }

    .btn-primary:hover {
        background: #3651d4;
    }

    .btn-secondary {
        background: white;
        color: #6c757d;
        border: 1px solid #e9ecef;
        padding: 0.5rem 1rem;
        border-radius: 8px;
        font-size: 0.875rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-secondary:hover {
        background: #f8f9fa;
        border-color: #ced4da;
    }

    .action-buttons {
        display: flex;
        gap: 0.5rem;
    }

    /* Products Table */
    .products-table-container {
        background: white;
        border-radius: 12px;
        border: 1px solid #e9ecef;
        overflow: auto;
        margin-bottom: 1.5rem;
    }

    .products-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 1000px;
    }

    .products-table th {
        text-align: left;
        padding: 1rem;
        background: #f8f9fa;
        color: #6c757d;
        font-weight: 500;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-bottom: 1px solid #e9ecef;
    }

    .products-table td {
        padding: 1rem;
        border-bottom: 1px solid #e9ecef;
        font-size: 0.875rem;
    }

    .product-cell {
        display: flex;
        align-items: center;
        gap: 1rem;
        min-width: 300px;
    }

    .product-image {
        width: 60px;
        height: 60px;
        border-radius: 8px;
        object-fit: cover;
        border: 1px solid #e9ecef;
    }

    .product-info {
        flex: 1;
    }

    .product-info h4 {
        font-weight: 500;
        font-size: 0.875rem;
        margin-bottom: 0.25rem;
    }

    .product-info span {
        font-size: 0.75rem;
        color: #6c757d;
    }

    .price-info {
        font-weight: 600;
        color: #4361ee;
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
    }

    .price-info small {
        font-weight: normal;
        color: #6c757d;
        font-size: 0.75rem;
        margin-left: 0.25rem;
    }

    .current-price {
        font-size: 1rem;
        font-weight: 700;
        color: #4361ee;
    }

    .currency {
        font-size: 0.75rem;
        color: #6c757d;
        margin-left: 0.25rem;
    }

    .product-name-wrapper {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-bottom: 0.25rem;
    }

    .product-name-wrapper h4 {
        font-size: 0.95rem;
        font-weight: 600;
        color: #212529;
        margin: 0;
    }

    .product-sku {
        font-size: 0.75rem;
        color: #6c757d;
    }

    /* Stock */
    .stock-info {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
    }

    .stock-badge {
        display: inline-flex;
        align-items: center;
        padding: 0.35rem 0.75rem;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 500;
        width: fit-content;
    }

    .stock-badge.normal {
        background: #d1f7ea;
        color: #06d6a0;
    }

    .stock-badge.low {
        background: #fff3d1;
        color: #ffb703;
    }

    .stock-badge.critical {
        background: #ffe5e5;
        color: #e63946;
        font-weight: 600;
        text-transform: uppercase;
    }

    .stock-warning {
        display: flex;
        align-items: center;
        gap: 0.25rem;
        color: #ffb703;
        font-size: 0.7rem;
        font-weight: 500;
        background: #fff3d1;
        padding: 0.25rem 0.5rem;
        border-radius: 4px;
        width: fit-content;
    }

    .stock-warning i {
        font-size: 0.6rem;
        color: #e63946;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.35rem 0.75rem;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 500;
        white-space: nowrap;
    }

    .status-badge i {
        font-size: 0.5rem;
    }

    .status-badge.active {
        background: #d1f7ea;
        color: #06d6a0;
    }

    .status-badge.inactive {
        background: #e9ecef;
        color: #6c757d;
    }

    .status-badge.exhausted {
        background: #ffe5e5;
        color: #e63946;
    }

    .status-badge.discontinued {
        background: #fff3d1;
        color: #ffb703;
    }

    .status-badge.paused {
        background: #e1e8ff;
        color: #4361ee;
    }

    /* Acciones */

    .action-icons {
        display: flex;
        gap: 0.5rem;
        justify-content: center;
    }

    .action-btn {
        width: 32px;
        height: 32px;
        border: none;
        border-radius: 6px;
        background: white;
        color: #6c757d;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s;
        border: 1px solid #e9ecef;
    }

    .action-btn:hover {
        background: #f8f9fa;
        transform: translateY(-2px);
    }

    .action-btn.edit:hover {
        color: #4361ee;
        border-color: #4361ee;
    }

    .action-btn.copy:hover {
        color: #06d6a0;
        border-color: #06d6a0;
    }

    .action-btn.status:hover {
        color: #ffc107;
        border-color: #ffc107;
    }

    .action-btn.delete:hover {
        color: #e63946;
        border-color: #e63946;
    }

    /* Pagination */
    .pagination {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 2rem;
        padding: 1rem 0;
    }

    .pagination-info {
        color: #6c757d;
        font-size: 0.9rem;
    }

    .pagination-info strong {
        color: #4361ee;
        font-weight: 600;
    }

    .pagination-controls {
        display: flex;
        gap: 0.25rem;
        align-items: center;
    }

    .page-btn {
        min-width: 36px;
        height: 36px;
        border: 1px solid #e9ecef;
        background: white;
        color: #495057;
        font-size: 0.9rem;
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.2s;
    }

    .page-btn:hover:not(:disabled) {
        background: #f1f3f5;
        border-color: #4361ee;
        color: #4361ee;
    }

    .page-btn.active {
        background: #4361ee;
        color: white;
        border-color: #4361ee;
    }

    .page-btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    .page-dots {
        padding: 0 0.5rem;
        color: #6c757d;
        font-weight: 500;
    }

    /* Badge para productos destacados */
    .featured-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 75px;
        height: 20px;
        background: #ffd700;
        color: #181717cc;
        border-radius: 4px;
        font-size: 0.7rem;
    }

    /* Badge para descuentos */
    .discount-badge {
        background: #e63946;
        color: white;
        font-size: 0.7rem;
        font-weight: 600;
        padding: 0.2rem 0.5rem;
        border-radius: 4px;
        display: inline-block;
    }

    .discount-info {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .original-price {
        font-size: 0.8rem;
        color: #adb5bd;
        text-decoration: line-through;
    }

    /* Info de ventas */
    .sales-info {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        gap: 0.25rem;
    }

    .sales-count {
        font-size: 1rem;
        font-weight: 700;
        color: #212529;
    }

    .sales-label {
        font-size: 0.7rem;
        color: #6c757d;
    }

    .rating {
        display: flex;
        align-items: center;
        gap: 0.25rem;
        background: #f8f9fa;
        padding: 0.2rem 0.5rem;
        border-radius: 4px;
    }

    .rating i {
        color: #ffb703;
        font-size: 0.7rem;
    }

    .rating span {
        font-size: 0.75rem;
        font-weight: 600;
        color: #495057;
    }

    /* Modal (Crear/Editar Producto) */
    .modal-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5);
        align-items: center;
        justify-content: center;
        z-index: 1000;
    }

    .modal-overlay.show {
        display: flex;
    }

    .modal {
        background: white;
        border-radius: 12px;
        width: 90%;
        max-width: 800px;
        max-height: 90vh;
        overflow-y: auto;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
    }

    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1.5rem;
        border-bottom: 1px solid #e9ecef;
    }

    .modal-header h2 {
        font-size: 1.25rem;
        font-weight: 600;
    }

    .modal-close {
        background: none;
        border: none;
        font-size: 1.25rem;
        cursor: pointer;
        color: #6c757d;
    }

    .modal-body {
        padding: 1.5rem;
    }

    .modal-footer {
        padding: 1.5rem;
        border-top: 1px solid #e9ecef;
        display: flex;
        justify-content: flex-end;
        gap: 1rem;
    }

    .form-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1.5rem;
    }

    .form-group {
        margin-bottom: 1rem;
    }

    .form-group.full-width {
        grid-column: span 2;
    }

    .form-label {
        display: block;
        font-size: 0.875rem;
        font-weight: 500;
        margin-bottom: 0.5rem;
        color: #212529;
    }

    .form-control {
        width: 100%;
        padding: 0.75rem;
        border: 1px solid #e9ecef;
        border-radius: 8px;
        font-size: 0.875rem;
        transition: border-color 0.2s;
    }

    .form-control:focus {
        outline: none;
        border-color: #4361ee;
    }

    .image-upload-area {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1rem;
        margin-bottom: 1rem;
    }

    .image-upload-box {
        aspect-ratio: 1;
        border: 2px dashed #e9ecef;
        border-radius: 8px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        color: #6c757d;
        cursor: pointer;
        transition: all 0.2s;
    }

    .image-upload-box:hover {
        border-color: #4361ee;
        color: #4361ee;
    }

    .image-upload-box i {
        font-size: 1.5rem;
        margin-bottom: 0.5rem;
    }

    .image-upload-box span {
        font-size: 0.75rem;
    }

    .variants-section {
        border: 1px solid #e9ecef;
        border-radius: 8px;
        padding: 1rem;
        margin-top: 1rem;
    }

    .variants-title {
        font-weight: 500;
        margin-bottom: 1rem;
    }

    .variant-row {
        display: flex;
        gap: 0.5rem;
        margin-bottom: 0.5rem;
        align-items: center;
    }

    .variant-tag {
        background: #f1f3f5;
        padding: 0.25rem 0.75rem;
        border-radius: 999px;
        font-size: 0.75rem;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .variant-tag i {
        cursor: pointer;
        color: #6c757d;
    }

    .variant-tag i:hover {
        color: #e63946;
    }

    .add-variant-btn {
        background: none;
        border: 1px dashed #ced4da;
        padding: 0.5rem;
        border-radius: 8px;
        color: #6c757d;
        cursor: pointer;
        width: 100%;
        margin-top: 0.5rem;
    }

    .checkbox-group {
        display: flex;
        gap: 1.5rem;
        margin-top: 0.5rem;
    }

    .checkbox-label {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.875rem;
    }

    .btn-cancel {
        background: white;
        border: 1px solid #e9ecef;
        padding: 0.75rem 1.5rem;
        border-radius: 8px;
        font-size: 0.875rem;
        cursor: pointer;
    }

    .btn-save {
        background: #4361ee;
        color: white;
        border: none;
        padding: 0.75rem 1.5rem;
        border-radius: 8px;
        font-size: 0.875rem;
        cursor: pointer;
    }

    /* Empty table mejorado */
    .empty-table {
        text-align: center;
        padding: 3rem 1rem;
        background: #f8f9fa;
        border-radius: 8px;
        color: #6c757d;
    }

    .empty-table i {
        color: #dee2e6;
        margin-bottom: 1rem;
    }

    .empty-table h3 {
        font-size: 1.25rem;
        margin-bottom: 0.5rem;
        color: #495057;
    }

    .empty-table p {
        margin-bottom: 1.5rem;
    }

    .badge-warning {
        background: #ffc107;
        color: #212529;
        border-radius: 25px;
    }

    .badge-danger {
        background: #e63946;
        color: white;
        border-radius: 25px;
    }

    .badge-success {
        background: #4bb543;
        color: white;
        border-radius: 25px;
    }

    /* Responsive */
    @media (max-width: 1024px) {
        .form-grid {
            grid-template-columns: 1fr;
        }

        .form-group.full-width {
            grid-column: span 1;
        }
    }

    @media (max-width: 768px) {
        .sidebar {
            display: none;
        }

        .main {
            margin-left: 0;
            padding: 1rem;
        }

        .action-bar {
            flex-direction: column;
            align-items: stretch;
        }

        .search-box {
            width: 100%;
        }

        .filters {
            flex-wrap: wrap;
        }

        .action-buttons {
            flex-wrap: wrap;
        }

        .btn-primary,
        .btn-secondary {
            flex: 1;
            justify-content: center;
        }
    }

    @media (max-width: 1200px) {
        .product-cell {
            min-width: 250px;
        }

        .product-image {
            width: 50px;
            height: 50px;
        }
    }

    @media (max-width: 992px) {
        .products-table {
            min-width: 900px;
        }
    }

    /* ============================================= */
    /* ESTILOS PARA FILTROS AVANZADOS */
    /* ============================================= */

    /* Contenedor de filtros avanzados */
    .advanced-filters {
        margin-top: 1.5rem;
        padding-top: 1.5rem;
        border-top: 2px dashed #e9ecef;
        background: linear-gradient(to bottom, #f8fafc, transparent);
        border-radius: 12px;
        display: flex;
        width: 100%;
    }

    /* Grupo de filtros */
    .filter-group {
        margin-bottom: 0.5rem;
        transition: all 0.2s ease;
    }

    .filter-group:hover {
        transform: translateY(-2px);
    }

    /* Etiquetas de filtros */
    .filter-label {
        display: flex;
        align-items: center;
        font-size: 0.85rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        color: #495057;
        margin-bottom: 0.75rem;
    }

    .filter-label i {
        font-size: 1rem;
    }

    /* Select moderno */
    .modern-select {
        height: 45px;
        border: 2px solid #e9ecef;
        border-radius: 10px;
        padding: 0 1rem;
        font-size: 0.95rem;
        color: #212529;
        background-color: white;
        cursor: pointer;
        transition: all 0.2s ease;
        appearance: none;
        -webkit-appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%234361ee' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 1rem center;
        background-size: 16px;
    }

    .modern-select:hover {
        border-color: #4361ee;
        box-shadow: 0 4px 8px rgba(67, 97, 238, 0.1);
    }

    .modern-select:focus {
        border-color: #4361ee;
        outline: none;
        box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.2);
    }

    /* Wrapper para inputs de fecha */
    .date-input-wrapper {
        position: relative;
        display: flex;
        align-items: center;
    }

    .filter-date {
        height: 45px;
        border: 2px solid #e9ecef;
        border-radius: 10px;
        padding: 0 1rem;
        font-size: 0.95rem;
        color: #212529;
        width: 100%;
        transition: all 0.2s ease;
        background-color: white;
        cursor: pointer;
    }

    .filter-date:hover {
        border-color: #4361ee;
        box-shadow: 0 4px 8px rgba(67, 97, 238, 0.1);
    }

    .filter-date:focus {
        border-color: #4361ee;
        outline: none;
        box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.2);
    }

    /* Estilo para el icono de fecha (pseudo-elemento para navegadores que no soportan el picker nativo) */
    .date-icon {
        position: absolute;
        right: 1rem;
        color: #4361ee;
        pointer-events: none;
        font-size: 1rem;
        opacity: 0.7;
    }

    /* Wrapper para botones de acción */
    .filter-actions-wrapper {
        display: flex;
        gap: 0.75rem;
        margin-top: 0.25rem;
    }

    /* Botón de filtrar */
    .btn-filter-apply {
        flex: 1;
        height: 45px;
        background: linear-gradient(135deg, #4361ee, #3a56d4);
        color: white;
        border: none;
        border-radius: 10px;
        font-size: 0.95rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        overflow: hidden;
        box-shadow: 0 4px 12px rgba(67, 97, 238, 0.2);
    }

    .btn-filter-apply:before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
        transition: left 0.5s ease;
    }

    .btn-filter-apply:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 16px rgba(67, 97, 238, 0.3);
        background: linear-gradient(135deg, #3a56d4, #2f48b5);
    }

    .btn-filter-apply:hover:before {
        left: 100%;
    }

    .btn-filter-apply i {
        font-size: 1rem;
        transition: transform 0.2s ease;
    }

    .btn-filter-apply:hover i {
        transform: scale(1.1);
    }

    /* Botón de limpiar */
    .btn-filter-clear {
        flex: 1;
        height: 45px;
        background: white;
        color: #6c757d;
        border: 2px solid #e9ecef;
        border-radius: 10px;
        font-size: 0.95rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.02);
    }

    .btn-filter-clear:hover {
        border-color: #e63946;
        color: #e63946;
        transform: translateY(-2px);
        box-shadow: 0 8px 16px rgba(230, 57, 70, 0.1);
        background-color: #fff5f5;
        text-decoration: none;
    }

    .btn-filter-clear i {
        transition: transform 0.2s ease;
    }

    .btn-filter-clear:hover i {
        transform: rotate(-15deg);
    }

    /* Tooltip personalizado para fechas */
    .filter-date::-webkit-calendar-picker-indicator {
        opacity: 0;
        width: 100%;
        height: 100%;
        position: absolute;
        top: 0;
        left: 0;
        cursor: pointer;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .advanced-filters {
            margin-top: 1rem;
            padding-top: 1rem;
        }

        .filter-actions-wrapper {
            flex-direction: column;
        }

        .btn-filter-apply,
        .btn-filter-clear {
            width: 100%;
        }

        .filter-label {
            font-size: 0.8rem;
            margin-bottom: 0.5rem;
        }

        .modern-select,
        .filter-date,
        .btn-filter-apply,
        .btn-filter-clear {
            height: 40px;
            font-size: 0.9rem;
        }
    }

    /* Animación de entrada para los filtros */
    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-20px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .advanced-filters {
        animation: slideDown 0.3s ease-out;
    }

    /* Estilos mejorados para estadísticas */
    .product-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .small-box {
        border-radius: 12px;
        margin-bottom: 20px;
        position: relative;
        display: block;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
    }

    .small-box:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
    }

    .small-box .inner {
        padding: 20px;
        z-index: 1;
    }

    .small-box .inner h3 {
        font-size: 2.5rem;
        font-weight: 700;
        margin: 0 0 10px 0;
        white-space: nowrap;
        color: white;
    }

    .small-box .inner p {
        font-size: 1rem;
        margin: 0;
        color: rgba(255, 255, 255, 0.9);
    }

    .small-box .icon {
        position: absolute;
        top: 15px;
        right: 15px;
        font-size: 4rem;
        color: rgba(255, 255, 255, 0.3);
        transition: all 0.3s ease;
    }

    .small-box:hover .icon {
        font-size: 4.5rem;
        color: rgba(255, 255, 255, 0.4);
    }

    .small-box .small-box-footer {
        background: rgba(0, 0, 0, 0.1);
        color: rgba(255, 255, 255, 0.8);
        padding: 10px 15px;
        text-align: center;
        font-size: 0.85rem;
        font-weight: 500;
        border-bottom-left-radius: 12px;
        border-bottom-right-radius: 12px;
    }

    /* Info boxes mejorados */
    .info-box {
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        margin-bottom: 20px;
        transition: all 0.3s ease;
        min-height: 100px;
        background: white;
        border: none;
    }

    .info-box:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
    }

    .info-box .info-box-icon {
        border-radius: 12px 0 0 12px;
        width: 90px;
        font-size: 2.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .info-box .info-box-content {
        padding: 15px 20px;
    }

    .info-box .info-box-text {
        font-size: 0.9rem;
        color: #6c757d;
        margin-bottom: 5px;
        font-weight: 500;
    }

    .info-box .info-box-number {
        font-size: 1.6rem;
        font-weight: 700;
        color: #212529;
        margin-bottom: 10px;
    }

    .info-box .progress {
        background: #e9ecef;
        height: 6px;
        margin: 10px 0 5px 0;
        border-radius: 3px;
        overflow: hidden;
    }

    .info-box .progress-bar {
        background: #4361ee;
        border-radius: 3px;
    }

    .info-box .progress-description {
        font-size: 0.8rem;
        color: #6c757d;
        display: block;
    }

    /* Gradientes personalizados */
    .bg-gradient-info {
        background: linear-gradient(135deg, #4361ee, #4cc9f0);
    }

    .bg-gradient-success {
        background: linear-gradient(135deg, #06d6a0, #0cb892);
    }

    .bg-gradient-warning {
        background: linear-gradient(135deg, #ffb703, #fd9e02);
    }

    .bg-gradient-danger {
        background: linear-gradient(135deg, #e63946, #d62828);
    }

    .bg-gradient-secondary {
        background: linear-gradient(135deg, #6c757d, #5a6268);
    }

    .bg-gradient-primary {
        background: linear-gradient(135deg, #4361ee, #3a56d4);
    }

    /* Responsive */
    @media (max-width: 768px) {
        .small-box .inner h3 {
            font-size: 1.8rem;
        }

        .small-box .icon {
            font-size: 3rem;
        }

        .info-box .info-box-icon {
            width: 70px;
            font-size: 1.8rem;
        }

        .info-box .info-box-number {
            font-size: 1.2rem;
        }
    }
</style>

<!-- ============================================= -->
<!-- ESTADÍSTICAS RÁPIDAS MEJORADAS -->
<!-- ============================================= -->
<div class="product-grid">
    <div class="col-lg-3 col-6">
        <div class="small-box bg-gradient-info">
            <div class="inner">
                <h3><?php echo number_format($stats->total ?? 0); ?></h3>
                <p>Total Productos</p>
            </div>
            <div class="icon">
                <i class="fas fa-boxes"></i>
            </div>
            <div class="small-box-footer">
                <i class="fas fa-chart-bar mr-1"></i>
                Catálogo completo
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-6">
        <div class="small-box bg-gradient-success">
            <div class="inner">
                <h3><?php echo number_format($stats->activos ?? 0); ?></h3>
                <p>Productos Activos</p>
            </div>
            <div class="icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="small-box-footer">
                <i class="fas fa-eye mr-1"></i>
                Visibles en tienda
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-6">
        <div class="small-box bg-gradient-warning">
            <div class="inner">
                <h3><?php echo number_format($stats->stock_bajo ?? 0); ?></h3>
                <p>Stock Bajo</p>
            </div>
            <div class="icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div class="small-box-footer">
                <i class="fas fa-arrow-up mr-1"></i>
                Requieren atención
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-6">
        <div class="small-box bg-gradient-danger">
            <div class="inner">
                <h3><?php echo number_format($stats->agotados ?? 0); ?></h3>
                <p>Productos Agotados</p>
            </div>
            <div class="icon">
                <i class="fas fa-times-circle"></i>
            </div>
            <div class="small-box-footer">
                <i class="fas fa-exclamation-circle mr-1"></i>
                Sin stock disponible
            </div>
        </div>
    </div>
</div>


<!-- Action Bar -->
<form method="GET" action="index.php" id="form-filtros">
    <div class="action-bar">
        <div class="search-box">
            <i class="fas fa-search"></i>
            <input type="text"
                id="buscar"
                name="buscar"
                placeholder="Buscar Producto..."
                value="<?php echo htmlspecialchars($buscar); ?>">
        </div>

        <div class="filters">
            <select class="filter-select select2" id="categoria" name="categoria">
                <option value="0">Todas las categorías</option>
                <?php foreach ($categorias as $cat): ?>
                    <option value="<?php echo $cat->id; ?>"
                        <?php echo $categoria == $cat->id ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($cat->nombre); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <select class="filter-select select2" id="subcategoria" name="subcategoria">
                <option value="0">Todas las subcategorías</option>
                <?php foreach ($subcategorias as $sub): ?>
                    <option value="<?php echo $sub->id; ?>"
                        <?php echo $subcategoria == $sub->id_categoria ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($sub->nombre); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <select class="filter-select" id="estado" name="estado">
                <option value="">Todos los estados</option>
                <option value="activo" <?php echo $estado == 'activo' ? 'selected' : ''; ?>>Activos</option>
                <option value="inactivo" <?php echo $estado == 'inactivo' ? 'selected' : ''; ?>>Inactivos</option>
                <option value="agotado" <?php echo $estado == 'agotado' ? 'selected' : ''; ?>>Agotados</option>
                <option value="descontinuado" <?php echo $estado == 'descontinuado' ? 'selected' : ''; ?>>Descontinuado</option>
            </select>

            <select class="filter-select" id="stock" name="stock">
                <option value="">Cualquier stock</option>
                <option value="bajo" <?php echo $stock == 'bajo' ? 'selected' : ''; ?>>Stock bajo (≤5)</option>
                <option value="critico" <?php echo $stock == 'critico' ? 'selected' : ''; ?>>Stock crítico (≤2)</option>
                <option value="agotado" <?php echo $stock == 'agotado' ? 'selected' : ''; ?>>Agotado (0)</option>
                <option value="disponible" <?php echo $stock == 'disponible' ? 'selected' : ''; ?>>Con stock (>0)</option>
            </select>

            <select class="filter-select" id="ordenar_por" name="ordenar_por">
                <option value="fecha_desc" <?php echo $ordenar_por == 'fecha_desc' ? 'selected' : ''; ?>>Más recientes</option>
                <option value="fecha_asc" <?php echo $ordenar_por == 'fecha_asc' ? 'selected' : ''; ?>>Más antiguos</option>
                <option value="nombre_asc" <?php echo $ordenar_por == 'nombre_asc' ? 'selected' : ''; ?>>Nombre A-Z</option>
                <option value="nombre_desc" <?php echo $ordenar_por == 'nombre_desc' ? 'selected' : ''; ?>>Nombre Z-A</option>
                <option value="precio_asc" <?php echo $ordenar_por == 'precio_asc' ? 'selected' : ''; ?>>Precio menor</option>
                <option value="precio_desc" <?php echo $ordenar_por == 'precio_desc' ? 'selected' : ''; ?>>Precio mayor</option>
                <option value="stock_asc" <?php echo $ordenar_por == 'stock_asc' ? 'selected' : ''; ?>>Stock menor</option>
                <option value="vendidos_desc" <?php echo $ordenar_por == 'vendidos_desc' ? 'selected' : ''; ?>>Más vendidos</option>
            </select>
        </div>

        <!-- Fila 2: Filtros avanzados con nuevo diseño -->
        <div class="row advanced-filters">
            <div class="col-md-2">
                <div class="form-group filter-group">
                    <label for="destacado" class="filter-label">
                        <i class="fas fa-star text-warning mr-1"></i>
                        Destacados
                    </label>
                    <select class="form-control filter-select modern-select" id="destacado" name="destacado">
                        <option value="0">Todos los productos</option>
                        <option value="1" <?php echo $destacado == 1 ? 'selected' : ''; ?>>Solo destacados</option>
                    </select>
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group filter-group">
                    <label for="fecha_desde" class="filter-label">
                        <i class="fas fa-calendar-alt text-primary mr-1"></i>
                        Fecha desde
                    </label>
                    <div class="date-input-wrapper">
                        <input type="date" class="form-control filter-date" id="fecha_desde" name="fecha_desde"
                            value="<?php echo $fecha_desde; ?>">
                        <i class="fas fa-calendar-day date-icon"></i>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group filter-group">
                    <label for="fecha_hasta" class="filter-label">
                        <i class="fas fa-calendar-alt text-primary mr-1"></i>
                        Fecha hasta
                    </label>
                    <div class="date-input-wrapper">
                        <input type="date" class="form-control filter-date" id="fecha_hasta" name="fecha_hasta"
                            value="<?php echo $fecha_hasta; ?>">
                        <i class="fas fa-calendar-check date-icon"></i>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="form-group filter-group">
                    <label class="filter-label d-block">
                        <i class="fas fa-sliders-h text-info mr-1"></i>
                        Acciones
                    </label>
                    <div class="filter-actions-wrapper">
                        <button type="submit" class="btn btn-filter-apply">
                            <i class="fas fa-filter mr-1"></i>
                            <span>Filtrar</span>
                        </button>
                        <a href="index.php" class="btn btn-filter-clear">
                            <i class="fas fa-undo-alt mr-1"></i>
                            <span>Limpiar</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>


<!-- ============================================= -->
<!-- TABLA DE PRODUCTOS -->
<!-- ============================================= -->
<div class="col-12">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-list mr-2"></i>
                Listado de Productos
                <?php if ($total_productos > 0): ?>
                    <span class="badge-info ml-2"><?php echo $total_productos; ?> encontrados</span>
                <?php endif; ?>
            </h3>
            <div class="action-buttons">
                <button class="btn-primary" onclick="openModal()">
                    <i class="fas fa-plus"></i>
                    Nuevo Producto
                </button>
                <button class="btn-secondary">
                    <i class="fas fa-download"></i>
                    Importar
                </button>
                <button class="btn-secondary">
                    <i class="fas fa-upload"></i>
                    Exportar
                </button>
            </div>
        </div>
        <div class="card-body p-0">
            <?php if (empty($productos)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-box-open fa-4x text-muted mb-3"></i>
                    <h5 class="text-muted">No se encontraron productos</h5>
                    <?php if (!empty($buscar) || $categoria > 0 || $subcategoria > 0 || !empty($estado) || !empty($stock)): ?>
                        <p class="text-muted">Intenta con otros filtros de búsqueda</p>
                        <a href="index.php" class="btn btn-primary">
                            <i class="fas fa-undo mr-1"></i> Limpiar filtros
                        </a>
                    <?php else: ?>
                        <p class="text-muted">Comienza agregando tu primer producto</p>
                        <a href="crear.php" class="btn btn-primary">
                            <i class="fas fa-plus mr-1"></i> Crear producto
                        </a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover table-striped table-bordered">
                        <thead class="thead-light">
                            <tr>
                                <th width="80">ID</th>
                                <th width="60">Imagen</th>
                                <th>Producto / SKU</th>
                                <th>Categoría</th>
                                <th width="100">Precio</th>
                                <th width="80">Stock</th>
                                <th width="80">Vendidos</th>
                                <th width="100">Estado</th>
                                <th width="120">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (is_array($productos) && count($productos) > 0): ?>
                                <?php foreach ($productos as $prod): ?>

                                    <tr>
                                        <?php if ($prod->id_producto <= 9): ?>
                                            <td># 00<?php echo $prod->id_producto; ?></td>
                                        <?php elseif ($prod->id_producto >= 10 && $prod->id_producto < 100): ?>
                                            <td># 0<?php echo $prod->id_producto; ?></td>
                                        <?php else: ?>
                                            <td># <?php echo $prod->id_producto; ?></td>
                                        <?php endif; ?>
                                        <td>
                                            <img src="../../../Util/Img/Producto/<?php echo $prod->imagen ?? 'producto_default.png'; ?>"
                                                alt="Producto"
                                                class="product-image">
                                        </td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($prod->producto); ?></strong>
                                            <?php if (!empty($prod->sku)): ?>
                                                <br>
                                                <small class="text-muted">SKU: <?php echo htmlspecialchars($prod->sku); ?></small>
                                            <?php endif; ?>
                                            <?php if ($prod->destacado): ?>
                                                <br>
                                                <span class="featured-badge"><i class="fas fa-star"></i>Destacado</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars($prod->categoria ?? 'Sin categoría'); ?>
                                            <?php if (!empty($prod->subcategoria)): ?>
                                                <br>
                                                <small class="text-muted"><?php echo htmlspecialchars($prod->subcategoria); ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <strong>$<?php echo number_format($prod->precio, 2); ?></strong>
                                            <br>
                                            <small class="text-muted">CUP</small>
                                        </td>
                                        <td>
                                            <?php if ($prod->stock <= 0): ?>
                                                <span class="stock-badge critical">Agotado</span>
                                            <?php elseif ($prod->stock <= 10): ?>
                                                <span class="stock-badge critical"><?php echo $prod->stock; ?> uds</span>
                                            <?php elseif ($prod->stock <= $prod->stock_minimo): ?>
                                                <span class="stock-badge low"><?php echo $prod->stock; ?> uds</span>
                                            <?php else: ?>
                                                <span class="stock-badge normal"><?php echo $prod->stock; ?> uds</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge-info"><?php echo $prod->vendidos ?? 0; ?></span>
                                        </td>
                                        <td>
                                            <?php if ($prod->estado == 'activo'): ?>
                                                <span class="status-badge active">
                                                    <i class="fas fa-circle"></i>Activo</span>
                                            <?php elseif ($prod->estado == 'inactivo'): ?>
                                                <span class="status-badge inactive">
                                                    <i class="fas fa-circle"></i>Inactivo</span>
                                            <?php elseif ($prod->estado == 'agotado'): ?>
                                                <span class="status-badge exhausted">
                                                    <i class="fas fa-circle"></i>Agotado</span>
                                            <?php else: ?>
                                                <span class="status-badge"><?php echo $prod->estado; ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="action-icons">
                                                <a href="editar.php?id=<?php echo $prod->id_producto; ?>"
                                                    class="action-btn edit"
                                                    title="Editar"
                                                    data-toggle="tooltip">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button class="action-btn copy"
                                                    onclick="duplicarProducto(<?php echo $prod->id_producto; ?>)"
                                                    title="Duplicar"
                                                    data-toggle="tooltip">
                                                    <i class="fas fa-copy"></i>
                                                </button>
                                                <button class="action-btn status"
                                                    onclick="cambiarEstado(<?php echo $prod->id_producto; ?>)"
                                                    title="Cambiar estado"
                                                    data-toggle="tooltip">
                                                    <i class="fas fa-toggle-on"></i>
                                                </button>
                                                <button class="action-btn delete"
                                                    onclick="confirmarEliminar(<?php echo $prod->id_producto; ?>)"
                                                    title="Eliminar"
                                                    data-toggle="tooltip">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="9" class="text-center py-4">
                                        <p class="text-muted">No se encontraron productos con los filtros seleccionados</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <!-- ============================================= -->
        <!-- PAGINACIÓN -->
        <!-- ============================================= -->
        <?php if ($total_paginas > 1): ?>
            <div class="pagination">
                <div class="pagination-info">
                    <?php
                    // Calcular el rango de productos mostrados en la página actual
                    $inicio = ($pagina - 1) * $por_pagina + 1;
                    $fin = min($pagina * $por_pagina, $total_productos);

                    if ($total_productos > 0) {
                        echo "Mostrando <strong>{$inicio}-{$fin}</strong> de <strong>{$total_productos}</strong> productos";
                    } else {
                        echo "Mostrando <strong>0</strong> de <strong>0</strong> productos";
                    }
                    ?>
                </div>
                <div class="pagination-controls">
                    <!-- Aquí van los botones de paginación -->
                    <?php if ($pagina > 1): ?>
                        <a href="?<?php echo http_build_query(array_merge($_GET, ['pagina' => $pagina - 1])); ?>" class="page-btn">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    <?php endif; ?>

                    <!-- Generar números de página -->
                    <?php
                    $rango = 5; // Número de páginas a mostrar
                    $inicio_paginas = max(1, $pagina - floor($rango / 2));
                    $fin_paginas = min($total_paginas, $inicio_paginas + $rango - 1);
                    $inicio_paginas = max(1, $fin_paginas - $rango + 1);

                    for ($i = $inicio_paginas; $i <= $fin_paginas; $i++):
                    ?>
                        <a href="?<?php echo http_build_query(array_merge($_GET, ['pagina' => $i])); ?>"
                            class="page-btn <?php echo $i == $pagina ? 'active' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>

                    <?php if ($pagina < $total_paginas): ?>
                        <a href="?<?php echo http_build_query(array_merge($_GET, ['pagina' => $pagina + 1])); ?>" class="page-btn">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>


<!-- Modal Crear/Editar Producto -->
<div class="modal-overlay" id="productModal">
    <div class="modal">
        <div class="modal-header">
            <h2 id="modalTitle">Nuevo Producto</h2>
            <button class="modal-close" onclick="closeModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <form>
                <!-- Imágenes -->
                <div class="form-group full-width">
                    <label class="form-label">Imágenes del producto</label>
                    <div class="image-upload-area">
                        <div class="image-upload-box">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <span>Principal</span>
                        </div>
                        <div class="image-upload-box">
                            <i class="fas fa-plus"></i>
                            <span>Agregar</span>
                        </div>
                        <div class="image-upload-box">
                            <i class="fas fa-plus"></i>
                            <span>Agregar</span>
                        </div>
                        <div class="image-upload-box">
                            <i class="fas fa-plus"></i>
                            <span>Agregar</span>
                        </div>
                    </div>
                </div>

                <!-- Información básica -->
                <div class="form-grid">
                    <div class="form-group full-width">
                        <label class="form-label">Nombre del producto *</label>
                        <input type="text" class="form-control" value="Camiseta Oversize Negra">
                    </div>

                    <div class="form-group full-width">
                        <label class="form-label">Descripción corta *</label>
                        <input type="text" class="form-control" value="Camiseta oversize 100% algodón">
                    </div>

                    <div class="form-group full-width">
                        <label class="form-label">Descripción detallada</label>
                        <textarea class="form-control" rows="4">Camiseta de algodón premium, corte oversize, ideal para uso diario. Disponible en varias tallas.</textarea>
                    </div>

                    <!-- Precios -->
                    <div class="form-group">
                        <label class="form-label">Precio CUP *</label>
                        <input type="text" class="form-control" value="1300">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Precio USD</label>
                        <input type="text" class="form-control" value="40">
                    </div>

                    <!-- Inventario -->
                    <div class="form-group">
                        <label class="form-label">Stock actual *</label>
                        <input type="text" class="form-control" value="12">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Stock mínimo</label>
                        <input type="text" class="form-control" value="5">
                    </div>

                    <!-- Categorías -->
                    <div class="form-group">
                        <label class="form-label">Categoría principal *</label>
                        <select class="form-control">
                            <option>Ropa</option>
                            <option>Calzado</option>
                            <option>Accesorios</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Subcategoría *</label>
                        <select class="form-control">
                            <option>Camisetas</option>
                            <option>Pantalones</option>
                            <option>Sudaderas</option>
                        </select>
                    </div>
                </div>

                <!-- Atributos / Variantes -->
                <div class="variants-section">
                    <div class="variants-title">Atributos (Talla, Color, etc)</div>
                    <div class="variant-row">
                        <span class="variant-tag">
                            Talla S <i class="fas fa-times"></i>
                        </span>
                        <span class="variant-tag">
                            Talla M <i class="fas fa-times"></i>
                        </span>
                        <span class="variant-tag">
                            Talla L <i class="fas fa-times"></i>
                        </span>
                        <span class="variant-tag">
                            Talla XL <i class="fas fa-times"></i>
                        </span>
                    </div>
                    <div class="variant-row">
                        <span class="variant-tag">
                            Color Negro <i class="fas fa-times"></i>
                        </span>
                        <span class="variant-tag">
                            Color Blanco <i class="fas fa-times"></i>
                        </span>
                    </div>
                    <button class="add-variant-btn">
                        <i class="fas fa-plus"></i> Añadir variante
                    </button>
                </div>

                <!-- Configuración adicional -->
                <div class="form-group full-width" style="margin-top: 1rem;">
                    <div class="checkbox-group">
                        <label class="checkbox-label">
                            <input type="checkbox" checked> Producto destacado (+$50/mes)
                        </label>
                        <label class="checkbox-label">
                            <input type="checkbox"> Envío gratis
                        </label>
                    </div>
                </div>

                <!-- Oferta -->
                <div class="form-group full-width">
                    <label class="form-label">Oferta</label>
                    <div style="display: flex; gap: 1rem;">
                        <select class="form-control" style="width: 150px;">
                            <option>20%</option>
                            <option>30%</option>
                            <option>40%</option>
                            <option>50%</option>
                        </select>
                        <span style="align-self: center;">hasta</span>
                        <input type="date" class="form-control" style="width: 150px;" value="2025-04-15">
                    </div>
                </div>

                <!-- SEO -->
                <div class="form-group full-width">
                    <label class="form-label">URL amigable</label>
                    <input type="text" class="form-control" value="/tienda/camiseta-oversize-negra">
                </div>

                <div class="form-group full-width">
                    <label class="form-label">Meta descripción</label>
                    <input type="text" class="form-control" value="Camiseta oversize negra 100% algodón">
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn-cancel" onclick="closeModal()">Cancelar</button>
            <button class="btn-save">Guardar producto</button>
        </div>
    </div>
</div>
<?php
include_once '../layouts/footer.php';
?>
<!-- <script src="productos.js"></script> -->
<script>
    function openModal(isEdit = false) {
        document.getElementById('productModal').classList.add('show');
        document.getElementById('modalTitle').textContent = isEdit ? 'Editar Producto' : 'Nuevo Producto';
    }

    function closeModal() {
        document.getElementById('productModal').classList.remove('show');
    }

    // Cerrar modal al hacer click fuera
    document.getElementById('productModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeModal();
        }
    });

    // Simulación de acciones
    // document.querySelectorAll('.action-icons button').forEach(btn => {
    //     btn.addEventListener('click', (e) => {
    //         e.stopPropagation();
    //         if (btn.classList.contains('delete')) {
    //             if (confirm('¿Estás seguro de eliminar este producto?')) {
    //                 alert('Producto eliminado (demo)');
    //             }
    //         } else if (btn.querySelector('.fa-edit')) {
    //             openModal(true);
    //         } else if (btn.querySelector('.fa-copy')) {
    //             alert('Producto duplicado (demo)');
    //         }
    //     });
    // });


    // ==========================
    // Scripts Para las Funciones
    // ==========================
    // Variables globales para JS
    var id_tienda = <?php echo $id_tienda; ?>;

    $(document).ready(function() {
        // Inicializar tooltips
        $('[data-toggle="tooltip"]').tooltip();

        // Inicializar Select2 para mejor experiencia
        $('.select2').select2({
            theme: 'bootstrap4',
            placeholder: 'Seleccionar...'
        });

        // Validar fechas (hasta no puede ser menor que desde)
        $('#fecha_desde, #fecha_hasta').change(function() {
            var desde = $('#fecha_desde').val();
            var hasta = $('#fecha_hasta').val();

            if (desde && hasta && hasta < desde) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Fechas inválidas',
                    text: 'La fecha "hasta" debe ser mayor o igual a la fecha "desde"'
                });
                $('#fecha_hasta').val('');
            }
        });

        // Preservar filtros al cambiar página
        $(document).on('click', '.pagination a', function(e) {
            e.preventDefault();
            var url = new URL(window.location.href);
            var params = new URLSearchParams(url.search);

            // Actualizar página
            var href = new URL($(this).attr('href'), window.location.origin);
            params.set('pagina', href.searchParams.get('pagina'));

            window.location.href = url.pathname + '?' + params.toString();
        });
    });
</script>
