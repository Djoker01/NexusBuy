<?php
$base_path = "../";
$pageTitle = "Gestión de Productos";
$pageName = "Productos";
$pageDescription = "Gestiona tu catálogo de productos.";
include_once '../layouts/header.php';
?>



<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: 'Inter', sans-serif;
        background: #f8f9fa;
        color: #212529;
    }

    /* Layout */
    .app {
        display: flex;
        min-height: 100vh;
    }

    /* Sidebar (igual que dashboard) */
    .sidebar {
        width: 260px;
        background: white;
        border-right: 1px solid #e9ecef;
        padding: 1.5rem;
        position: fixed;
        height: 100vh;
        overflow-y: auto;
    }

    .logo {
        font-size: 1.5rem;
        font-weight: 700;
        color: #4361ee;
        margin-bottom: 2rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .logo i {
        font-size: 2rem;
    }

    .nav-item {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.75rem 1rem;
        color: #6c757d;
        text-decoration: none;
        border-radius: 8px;
        margin-bottom: 0.25rem;
        transition: all 0.2s;
    }

    .nav-item:hover {
        background: #f1f3f5;
        color: #4361ee;
    }

    .nav-item.active {
        background: #4361ee;
        color: white;
    }

    .nav-item i {
        width: 20px;
    }

    /* Main Content */
    .main {
        flex: 1;
        margin-left: 260px;
        padding: 1.5rem 2rem;
    }

    /* Header */
    .top-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
        background: white;
        padding: 1rem 1.5rem;
        border-radius: 12px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02);
    }

    .page-title h1 {
        font-size: 1.5rem;
        font-weight: 600;
    }

    .page-title p {
        color: #6c757d;
        font-size: 0.875rem;
        margin-top: 0.25rem;
    }

    .user-menu {
        display: flex;
        align-items: center;
        gap: 1.5rem;
    }

    .notifications {
        position: relative;
        cursor: pointer;
    }

    .notifications i {
        font-size: 1.25rem;
        color: #6c757d;
    }

    .badge {
        position: absolute;
        top: -8px;
        right: -8px;
        background: #e63946;
        color: white;
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
        border-radius: 999px;
        min-width: 18px;
        height: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .user-info {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        cursor: pointer;
    }

    .user-avatar {
        width: 40px;
        height: 40px;
        background: #4361ee;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 600;
    }

    .user-details {
        line-height: 1.3;
    }

    .user-name {
        font-weight: 600;
        font-size: 0.875rem;
    }

    .user-role {
        color: #6c757d;
        font-size: 0.75rem;
    }

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
    width: 20px;
    height: 20px;
    background: #ffd700;
    color: #000;
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
</style>




<!-- Action Bar -->
<div class="action-bar">
    <div class="search-box">
        <i class="fas fa-search"></i>
        <input type="text" id="search-input" placeholder="Buscar producto...">
    </div>
    <div class="filters">
        <select class="filter-select" id="categoria-filter">
            <option value="">Todas las categorías</option>
            <option value="ropa">Ropa</option>
            <option value="calzado">Calzado</option>
            <option value="accesorios">Accesorios</option>
        </select>
        <select class="filter-select" id="estado-filter">
            <option value="">Todos los estados</option>
            <option value="activo">Activos</option>
            <option value="inactivo">Inactivos</option>
            <option value="agotado">Agotados</option>
        </select>
        <select class="filter-select" id="orden-filter">
            <option value="">Ordenar por</option>
            <option value="ventas">Más vendidos</option>
            <option value="precio_asc">Precio: menor a mayor</option>
            <option value="precio_desc">Precio: mayor a menor</option>
            <option value="nuevos">Más recientes</option>
        </select>
    </div>
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

<!-- Products Table -->
<div class="products-table-container">
    <table class="products-table">
        <thead>
            <tr>
                <th>Producto</th>
                <th>Precio</th>
                <th>Stock</th>
                <th>Ventas</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody id="productos-container">
            
            </tr>
        </tbody>
    </table>
</div>

<!-- Pagination -->
<div class="pagination">
    <div class="pagination-info">
        Mostrando 0 - 0 de 0 productos
    </div>
    <div class="pagination-controls">
        <!-- Se llenará con JavaScript -->
    </div>
</div>
</main>
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
<script src="productos.js"></script>
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
    document.querySelectorAll('.action-icons button').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.stopPropagation();
            if (btn.classList.contains('delete')) {
                if (confirm('¿Estás seguro de eliminar este producto?')) {
                    alert('Producto eliminado (demo)');
                }
            } else if (btn.querySelector('.fa-edit')) {
                openModal(true);
            } else if (btn.querySelector('.fa-copy')) {
                alert('Producto duplicado (demo)');
            }
        });
    });

    // Navegación
    // document.querySelectorAll('.nav-item').forEach(item => {
    //     item.addEventListener('click', (e) => {
    //         e.preventDefault();
    //         document.querySelectorAll('.nav-item').forEach(nav => nav.classList.remove('active'));
    //         item.classList.add('active');
    //     });
    // });
</script>