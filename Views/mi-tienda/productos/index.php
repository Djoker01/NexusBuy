<?php
include_once '../layouts/header.php';
?>

    <title>NexusBuy - Gestión de Productos</title>
    
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
            box-shadow: 0 2px 4px rgba(0,0,0,0.02);
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
        }

        .product-image {
            width: 48px;
            height: 48px;
            background: #e9ecef;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #6c757d;
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
        }

        .price-info small {
            font-weight: normal;
            color: #6c757d;
            font-size: 0.75rem;
            margin-left: 0.25rem;
        }

        .stock-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.5rem;
            border-radius: 999px;
            font-size: 0.75rem;
            font-weight: 500;
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
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            padding: 0.25rem 0.5rem;
            border-radius: 999px;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .status-badge.active {
            background: #d1f7ea;
            color: #06d6a0;
        }

        .status-badge.inactive {
            background: #e9ecef;
            color: #6c757d;
        }

        .action-icons {
            display: flex;
            gap: 0.5rem;
        }

        .action-icons button {
            background: none;
            border: none;
            color: #6c757d;
            cursor: pointer;
            padding: 0.25rem;
            border-radius: 4px;
            transition: all 0.2s;
        }

        .action-icons button:hover {
            background: #f1f3f5;
            color: #4361ee;
        }

        .action-icons button.delete:hover {
            color: #e63946;
        }

        /* Pagination */
        .pagination {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .pagination-info {
            color: #6c757d;
            font-size: 0.875rem;
        }

        .pagination-controls {
            display: flex;
            gap: 0.5rem;
        }

        .page-btn {
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            background: white;
            color: #6c757d;
            cursor: pointer;
            transition: all 0.2s;
        }

        .page-btn:hover {
            background: #f1f3f5;
            border-color: #ced4da;
        }

        .page-btn.active {
            background: #4361ee;
            color: white;
            border-color: #4361ee;
        }

        /* Modal (Crear/Editar Producto) */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
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
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
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
            
            .btn-primary, .btn-secondary {
                flex: 1;
                justify-content: center;
            }
        }
    </style>


            <!-- Header -->
            <div class="top-header">
                <div class="page-title">
                    <h1>Productos</h1>
                    <p>Gestiona el catálogo de tu tienda</p>
                </div>
                <div class="user-menu">
                    <div class="notifications">
                        <i class="far fa-bell"></i>
                        <span class="badge">5</span>
                    </div>
                    <div class="user-info">
                        <div class="user-avatar">MF</div>
                        <div class="user-details">
                            <div class="user-name">Miguel Fernández</div>
                            <div class="user-role">Nexus Fashion</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Bar -->
            <div class="action-bar">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Buscar producto...">
                </div>
                <div class="filters">
                    <select class="filter-select">
                        <option>Todas las categorías</option>
                        <option>Ropa</option>
                        <option>Calzado</option>
                        <option>Accesorios</option>
                    </select>
                    <select class="filter-select">
                        <option>Ordenar por</option>
                        <option>Más vendidos</option>
                        <option>Precio: menor a mayor</option>
                        <option>Precio: mayor a menor</option>
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
                    <tbody>
                        <tr>
                            <td>
                                <div class="product-cell">
                                    <div class="product-image">
                                        <i class="fas fa-tshirt"></i>
                                    </div>
                                    <div class="product-info">
                                        <h4>Camiseta Oversize Negra</h4>
                                        <span>SKU: CAM-001-NEG</span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="price-info">
                                    $1,300 CUP
                                    <br>
                                    <small>$40 USD</small>
                                </div>
                            </td>
                            <td>
                                <span class="stock-badge normal">12 unidades</span>
                            </td>
                            <td>45</td>
                            <td>
                                <span class="status-badge active">
                                    <i class="fas fa-circle" style="font-size: 0.5rem;"></i>
                                    Activo
                                </span>
                            </td>
                            <td>
                                <div class="action-icons">
                                    <button onclick="openModal(true)"><i class="fas fa-edit"></i></button>
                                    <button><i class="fas fa-copy"></i></button>
                                    <button class="delete"><i class="fas fa-trash"></i></button>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div class="product-cell">
                                    <div class="product-image">
                                        <i class="fas fa-tshirt"></i>
                                    </div>
                                    <div class="product-info">
                                        <h4>Jeans Skinny Azul</h4>
                                        <span>SKU: JNS-001-AZU</span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="price-info">
                                    $2,300 CUP
                                    <br>
                                    <small>$65 USD</small>
                                </div>
                            </td>
                            <td>
                                <span class="stock-badge low">5 unidades</span>
                            </td>
                            <td>32</td>
                            <td>
                                <span class="status-badge active">
                                    <i class="fas fa-circle" style="font-size: 0.5rem;"></i>
                                    Activo
                                </span>
                            </td>
                            <td>
                                <div class="action-icons">
                                    <button><i class="fas fa-edit"></i></button>
                                    <button><i class="fas fa-copy"></i></button>
                                    <button class="delete"><i class="fas fa-trash"></i></button>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div class="product-cell">
                                    <div class="product-image">
                                        <i class="fas fa-hat-cowboy"></i>
                                    </div>
                                    <div class="product-info">
                                        <h4>Gorra New Era Negra/Roja</h4>
                                        <span>SKU: GOR-002-NGR</span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="price-info">
                                    $1,500 CUP
                                    <br>
                                    <small>$45 USD</small>
                                </div>
                            </td>
                            <td>
                                <span class="stock-badge normal">18 unidades</span>
                            </td>
                            <td>28</td>
                            <td>
                                <span class="status-badge active">
                                    <i class="fas fa-circle" style="font-size: 0.5rem;"></i>
                                    Activo
                                </span>
                            </td>
                            <td>
                                <div class="action-icons">
                                    <button><i class="fas fa-edit"></i></button>
                                    <button><i class="fas fa-copy"></i></button>
                                    <button class="delete"><i class="fas fa-trash"></i></button>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div class="product-cell">
                                    <div class="product-image">
                                        <i class="fas fa-shoe-prints"></i>
                                    </div>
                                    <div class="product-info">
                                        <h4>Zapatillas Running</h4>
                                        <span>SKU: ZAP-003-RUN</span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="price-info">
                                    $3,900 CUP
                                    <br>
                                    <small>$110 USD</small>
                                </div>
                            </td>
                            <td>
                                <span class="stock-badge low">3 unidades</span>
                            </td>
                            <td>21</td>
                            <td>
                                <span class="status-badge active">
                                    <i class="fas fa-circle" style="font-size: 0.5rem;"></i>
                                    Activo
                                </span>
                            </td>
                            <td>
                                <div class="action-icons">
                                    <button><i class="fas fa-edit"></i></button>
                                    <button><i class="fas fa-copy"></i></button>
                                    <button class="delete"><i class="fas fa-trash"></i></button>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div class="product-cell">
                                    <div class="product-image">
                                        <i class="fas fa-tshirt"></i>
                                    </div>
                                    <div class="product-info">
                                        <h4>Sudadera Hoodie Gris</h4>
                                        <span>SKU: SUD-001-GRI</span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="price-info">
                                    $2,500 CUP
                                    <br>
                                    <small>$70 USD</small>
                                </div>
                            </td>
                            <td>
                                <span class="stock-badge critical">0 unidades</span>
                            </td>
                            <td>15</td>
                            <td>
                                <span class="status-badge inactive">
                                    <i class="fas fa-circle" style="font-size: 0.5rem;"></i>
                                    Inactivo
                                </span>
                            </td>
                            <td>
                                <div class="action-icons">
                                    <button><i class="fas fa-edit"></i></button>
                                    <button><i class="fas fa-copy"></i></button>
                                    <button class="delete"><i class="fas fa-trash"></i></button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="pagination">
                <div class="pagination-info">
                    Mostrando 5 de 24 productos
                </div>
                <div class="pagination-controls">
                    <button class="page-btn"><i class="fas fa-chevron-left"></i></button>
                    <button class="page-btn active">1</button>
                    <button class="page-btn">2</button>
                    <button class="page-btn">3</button>
                    <button class="page-btn">4</button>
                    <button class="page-btn"><i class="fas fa-chevron-right"></i></button>
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
            if(e.target === this) {
                closeModal();
            }
        });

        // Simulación de acciones
        document.querySelectorAll('.action-icons button').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                if(btn.classList.contains('delete')) {
                    if(confirm('¿Estás seguro de eliminar este producto?')) {
                        alert('Producto eliminado (demo)');
                    }
                } else if(btn.querySelector('.fa-edit')) {
                    openModal(true);
                } else if(btn.querySelector('.fa-copy')) {
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
</body>
</html>