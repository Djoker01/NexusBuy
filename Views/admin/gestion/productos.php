<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NexusBuy - Gestión de Productos</title>
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: #f0f2f5;
            color: #1a2639;
        }

        /* Layout */
        .app {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar Admin */
        .admin-sidebar {
            width: 280px;
            background: linear-gradient(180deg, #1a2639 0%, #0d1b2a 100%);
            color: white;
            padding: 1.5rem;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            box-shadow: 4px 0 10px rgba(0,0,0,0.1);
        }

        .admin-logo {
            font-size: 1.5rem;
            font-weight: 700;
            color: white;
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .admin-logo i {
            font-size: 2rem;
            color: #4cc9f0;
        }

        .admin-logo span {
            background: linear-gradient(135deg, #f8f9fa, #4cc9f0);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .admin-user {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            background: rgba(255,255,255,0.1);
            border-radius: 12px;
            margin-bottom: 2rem;
        }

        .admin-avatar {
            width: 48px;
            height: 48px;
            background: #4cc9f0;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 1.25rem;
        }

        .admin-info h4 {
            font-size: 0.875rem;
            margin-bottom: 0.25rem;
        }

        .admin-info p {
            font-size: 0.75rem;
            opacity: 0.7;
        }

        .admin-nav {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .nav-category {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: rgba(255,255,255,0.5);
            margin: 1rem 0 0.5rem 0.5rem;
        }

        .admin-nav-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1rem;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.2s;
        }

        .admin-nav-item:hover {
            background: rgba(255,255,255,0.15);
            color: white;
        }

        .admin-nav-item.active {
            background: #4cc9f0;
            color: #1a2639;
            font-weight: 500;
        }

        .admin-nav-item i {
            width: 20px;
            font-size: 1.1rem;
        }

        .badge-admin {
            background: #e63946;
            color: white;
            font-size: 0.7rem;
            padding: 0.25rem 0.5rem;
            border-radius: 999px;
            margin-left: auto;
        }

        /* Main Content Admin */
        .admin-main {
            flex: 1;
            margin-left: 280px;
            padding: 1.5rem 2rem;
        }

        /* Header Admin */
        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            background: white;
            padding: 1rem 1.5rem;
            border-radius: 16px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.02);
        }

        .page-title h1 {
            font-size: 1.5rem;
            font-weight: 600;
            color: #1a2639;
        }

        .page-title p {
            color: #6c757d;
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        .header-notifications {
            position: relative;
            cursor: pointer;
        }

        .header-notifications i {
            font-size: 1.25rem;
            color: #6c757d;
        }

        .badge-danger {
            position: absolute;
            top: -8px;
            right: -8px;
            background: #e63946;
            color: white;
            font-size: 0.7rem;
            padding: 0.25rem 0.5rem;
            border-radius: 999px;
        }

        .header-admin-user {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 8px;
            transition: background 0.2s;
        }

        .header-admin-user:hover {
            background: #f8f9fa;
        }

        .admin-mini-avatar {
            width: 36px;
            height: 36px;
            background: #4361ee;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
        }

        /* Stats Cards */
        .product-stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .product-stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 16px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.02);
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .product-stat-icon {
            width: 56px;
            height: 56px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.75rem;
        }

        .product-stat-icon.blue {
            background: #e1e8ff;
            color: #4361ee;
        }

        .product-stat-icon.green {
            background: #d1f7ea;
            color: #06d6a0;
        }

        .product-stat-icon.yellow {
            background: #fff3d1;
            color: #ffb703;
        }

        .product-stat-icon.purple {
            background: #ead1ff;
            color: #7209b7;
        }

        .product-stat-info h3 {
            font-size: 1.75rem;
            font-weight: 700;
            margin-bottom: 0.25rem;
        }

        .product-stat-info p {
            color: #6c757d;
            font-size: 0.875rem;
        }

        /* Moderation Queue */
        .moderation-queue {
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.02);
        }

        .queue-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .queue-header h2 {
            font-size: 1.1rem;
            font-weight: 600;
        }

        .queue-items {
            display: flex;
            gap: 1rem;
            overflow-x: auto;
            padding-bottom: 0.5rem;
        }

        .queue-item {
            min-width: 300px;
            background: #f8f9fa;
            border-radius: 12px;
            padding: 1rem;
            border: 1px solid #e9ecef;
            display: flex;
            gap: 1rem;
        }

        .queue-item-image {
            width: 80px;
            height: 80px;
            background: #e9ecef;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #6c757d;
            font-size: 2rem;
        }

        .queue-item-info {
            flex: 1;
        }

        .queue-item-info h4 {
            font-size: 0.875rem;
            font-weight: 600;
            margin-bottom: 0.25rem;
        }

        .queue-item-store {
            font-size: 0.7rem;
            color: #4361ee;
            margin-bottom: 0.25rem;
        }

        .queue-item-price {
            font-weight: 700;
            color: #4361ee;
            margin-bottom: 0.5rem;
        }

        .queue-item-actions {
            display: flex;
            gap: 0.5rem;
        }

        .queue-btn {
            padding: 0.25rem 0.75rem;
            border-radius: 4px;
            font-size: 0.7rem;
            border: none;
            cursor: pointer;
        }

        .queue-btn.approve {
            background: #06d6a0;
            color: white;
        }

        .queue-btn.reject {
            background: #e63946;
            color: white;
        }

        .queue-btn.view {
            background: white;
            border: 1px solid #e9ecef;
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
            width: 350px;
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

        .filter-group {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .filter-select {
            padding: 0.5rem 2rem 0.5rem 1rem;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            background: white;
            font-size: 0.875rem;
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

        /* Products Table */
        .products-table-container {
            background: white;
            border-radius: 16px;
            border: 1px solid #e9ecef;
            overflow: auto;
            margin-bottom: 1.5rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.02);
        }

        .products-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 1400px;
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
            border-radius: 8px;
            background: #f1f3f5;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #6c757d;
            overflow: hidden;
        }

        .product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .product-info h4 {
            font-weight: 600;
            font-size: 0.875rem;
            margin-bottom: 0.25rem;
        }

        .product-info span {
            font-size: 0.7rem;
            color: #6c757d;
        }

        .store-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            padding: 0.25rem 0.5rem;
            background: #e1e8ff;
            color: #4361ee;
            border-radius: 4px;
            font-size: 0.7rem;
        }

        .price-info {
            font-weight: 600;
            color: #4361ee;
        }

        .price-info small {
            font-weight: normal;
            color: #6c757d;
            font-size: 0.7rem;
            margin-left: 0.25rem;
        }

        .stock-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.5rem;
            border-radius: 999px;
            font-size: 0.7rem;
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
            font-size: 0.7rem;
            font-weight: 500;
        }

        .status-badge.active {
            background: #d1f7ea;
            color: #06d6a0;
        }

        .status-badge.pending {
            background: #fff3d1;
            color: #ffb703;
        }

        .status-badge.rejected {
            background: #ffe5e5;
            color: #e63946;
        }

        .status-badge.hidden {
            background: #e9ecef;
            color: #6c757d;
        }

        .report-badge {
            background: #e63946;
            color: white;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.7rem;
            font-weight: 500;
        }

        .action-buttons {
            display: flex;
            gap: 0.5rem;
        }

        .action-btn {
            background: none;
            border: none;
            color: #6c757d;
            cursor: pointer;
            padding: 0.25rem;
            border-radius: 4px;
            transition: all 0.2s;
        }

        .action-btn:hover {
            background: #f1f3f5;
            color: #4361ee;
        }

        .action-btn.approve:hover {
            color: #06d6a0;
        }

        .action-btn.reject:hover {
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

        /* Modal */
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
            border-radius: 16px;
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
            position: sticky;
            top: 0;
            background: white;
            z-index: 10;
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
            position: sticky;
            bottom: 0;
            background: white;
        }

        /* Product Detail */
        .product-detail-header {
            display: flex;
            gap: 2rem;
            margin-bottom: 2rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid #e9ecef;
        }

        .product-detail-gallery {
            width: 200px;
        }

        .product-detail-main-image {
            width: 200px;
            height: 200px;
            background: #f1f3f5;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            color: #6c757d;
            margin-bottom: 1rem;
        }

        .product-detail-thumbnails {
            display: flex;
            gap: 0.5rem;
        }

        .product-thumb {
            width: 50px;
            height: 50px;
            background: #f1f3f5;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            color: #6c757d;
            cursor: pointer;
        }

        .product-detail-info {
            flex: 1;
        }

        .product-detail-info h3 {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .product-detail-meta {
            display: flex;
            gap: 1.5rem;
            margin-bottom: 1rem;
            color: #6c757d;
            font-size: 0.875rem;
        }

        .product-detail-prices {
            display: flex;
            gap: 2rem;
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
        }

        .price-box {
            text-align: center;
        }

        .price-label {
            font-size: 0.7rem;
            color: #6c757d;
            margin-bottom: 0.25rem;
        }

        .price-value {
            font-size: 1.25rem;
            font-weight: 700;
            color: #4361ee;
        }

        .detail-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
        }

        .reports-section {
            background: #ffe5e5;
            padding: 1rem;
            border-radius: 8px;
            margin: 1rem 0;
        }

        .report-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.5rem;
            border-bottom: 1px solid rgba(230, 57, 70, 0.2);
        }

        .report-reason {
            font-size: 0.875rem;
        }

        .report-date {
            font-size: 0.7rem;
            color: #e63946;
        }

        .rejection-input {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            margin-top: 1rem;
        }

        .btn-success {
            background: #06d6a0;
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-size: 0.875rem;
            cursor: pointer;
        }

        .btn-danger {
            background: #e63946;
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-size: 0.875rem;
            cursor: pointer;
        }

        .btn-warning {
            background: #ffb703;
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-size: 0.875rem;
            cursor: pointer;
        }

        /* Responsive */
        @media (max-width: 1200px) {
            .product-stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .product-detail-header {
                flex-direction: column;
                align-items: center;
                text-align: center;
            }
        }

        @media (max-width: 768px) {
            .admin-sidebar {
                display: none;
            }
            
            .admin-main {
                margin-left: 0;
                padding: 1rem;
            }
            
            .product-stats-grid {
                grid-template-columns: 1fr;
            }
            
            .action-bar {
                flex-direction: column;
                align-items: stretch;
            }
            
            .search-box {
                width: 100%;
            }
            
            .filter-group {
                flex-direction: column;
            }
            
            .detail-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="app">
        <!-- Sidebar Admin -->
        <aside class="admin-sidebar">
            <div class="admin-logo">
                <i class="fas fa-crown"></i>
                <span>NexusBuy Admin</span>
            </div>

            <div class="admin-user">
                <div class="admin-avatar">AD</div>
                <div class="admin-info">
                    <h4>Admin Principal</h4>
                    <p>superadmin@nexusbuy.com</p>
                </div>
            </div>

            <nav class="admin-nav">
                <div class="nav-category">PRINCIPAL</div>
                <a href="#" class="admin-nav-item">
                    <i class="fas fa-chart-pie"></i>
                    Dashboard
                </a>
                <a href="#" class="admin-nav-item">
                    <i class="fas fa-shopping-cart"></i>
                    Ventas
                </a>

                <div class="nav-category">GESTIÓN</div>
                <a href="#" class="admin-nav-item">
                    <i class="fas fa-users"></i>
                    Usuarios
                </a>
                <a href="#" class="admin-nav-item">
                    <i class="fas fa-store"></i>
                    Tiendas
                </a>
                <a href="#" class="admin-nav-item active">
                    <i class="fas fa-box"></i>
                    Productos
                    <span class="badge-admin">12</span>
                </a>
                <a href="#" class="admin-nav-item">
                    <i class="fas fa-shopping-bag"></i>
                    Pedidos
                </a>
                <a href="#" class="admin-nav-item">
                    <i class="fas fa-tags"></i>
                    Categorías
                </a>

                <div class="nav-category">FINANZAS</div>
                <a href="#" class="admin-nav-item">
                    <i class="fas fa-dollar-sign"></i>
                    Comisiones
                </a>
                <a href="#" class="admin-nav-item">
                    <i class="fas fa-hand-holding-usd"></i>
                    Retiros
                    <span class="badge-admin">3</span>
                </a>
                <a href="#" class="admin-nav-item">
                    <i class="fas fa-chart-line"></i>
                    Reportes
                </a>

                <div class="nav-category">CONFIGURACIÓN</div>
                <a href="#" class="admin-nav-item">
                    <i class="fas fa-cog"></i>
                    General
                </a>
                <a href="#" class="admin-nav-item">
                    <i class="fas fa-palette"></i>
                    Apariencia
                </a>
                <a href="#" class="admin-nav-item">
                    <i class="fas fa-shield-alt"></i>
                    Seguridad
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="admin-main">
            <!-- Header -->
            <div class="admin-header">
                <div class="page-title">
                    <h1>Gestión de Productos</h1>
                    <p>Modera y administra todos los productos del sistema</p>
                </div>
                <div class="header-actions">
                    <div class="header-notifications">
                        <i class="far fa-bell"></i>
                        <span class="badge-danger">8</span>
                    </div>
                    <div class="header-admin-user">
                        <div class="admin-mini-avatar">AD</div>
                        <i class="fas fa-chevron-down" style="color: #6c757d;"></i>
                    </div>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="product-stats-grid">
                <div class="product-stat-card">
                    <div class="product-stat-icon blue">
                        <i class="fas fa-box"></i>
                    </div>
                    <div class="product-stat-info">
                        <h3>4,567</h3>
                        <p>Productos totales</p>
                    </div>
                </div>
                <div class="product-stat-card">
                    <div class="product-stat-icon green">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="product-stat-info">
                        <h3>3,890</h3>
                        <p>Activos</p>
                    </div>
                </div>
                <div class="product-stat-card">
                    <div class="product-stat-icon yellow">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="product-stat-info">
                        <h3>12</h3>
                        <p>Pendientes</p>
                    </div>
                </div>
                <div class="product-stat-card">
                    <div class="product-stat-icon purple">
                        <i class="fas fa-flag"></i>
                    </div>
                    <div class="product-stat-info">
                        <h3>8</h3>
                        <p>Reportados</p>
                    </div>
                </div>
            </div>

            <!-- Moderation Queue -->
            <div class="moderation-queue">
                <div class="queue-header">
                    <h2>Cola de moderación <span style="color: #ffb703; margin-left: 0.5rem;">(12 pendientes)</span></h2>
                    <a href="#" style="color: #4361ee; text-decoration: none; font-size: 0.875rem;">Ver todos</a>
                </div>
                <div class="queue-items">
                    <div class="queue-item">
                        <div class="queue-item-image">
                            <i class="fas fa-tshirt"></i>
                        </div>
                        <div class="queue-item-info">
                            <h4>Camiseta Oversize Negra</h4>
                            <div class="queue-item-store">Nexus Fashion</div>
                            <div class="queue-item-price">$1,300 CUP</div>
                            <div class="queue-item-actions">
                                <button class="queue-btn approve" onclick="approveProduct('Camiseta Oversize Negra')"><i class="fas fa-check"></i> Aprobar</button>
                                <button class="queue-btn reject" onclick="rejectProduct('Camiseta Oversize Negra')"><i class="fas fa-times"></i> Rechazar</button>
                                <button class="queue-btn view" onclick="viewProductDetails('Camiseta Oversize Negra')"><i class="fas fa-eye"></i></button>
                            </div>
                        </div>
                    </div>
                    <div class="queue-item">
                        <div class="queue-item-image">
                            <i class="fas fa-laptop"></i>
                        </div>
                        <div class="queue-item-info">
                            <h4>Laptop Lenovo ThinkPad</h4>
                            <div class="queue-item-store">Tech Store</div>
                            <div class="queue-item-price">$45,000 CUP</div>
                            <div class="queue-item-actions">
                                <button class="queue-btn approve"><i class="fas fa-check"></i> Aprobar</button>
                                <button class="queue-btn reject"><i class="fas fa-times"></i> Rechazar</button>
                                <button class="queue-btn view"><i class="fas fa-eye"></i></button>
                            </div>
                        </div>
                    </div>
                    <div class="queue-item">
                        <div class="queue-item-image">
                            <i class="fas fa-shoe-prints"></i>
                        </div>
                        <div class="queue-item-info">
                            <h4>Zapatillas Nike Air</h4>
                            <div class="queue-item-store">Deportes Cuba</div>
                            <div class="queue-item-price">$8,500 CUP</div>
                            <div class="queue-item-actions">
                                <button class="queue-btn approve"><i class="fas fa-check"></i> Aprobar</button>
                                <button class="queue-btn reject"><i class="fas fa-times"></i> Rechazar</button>
                                <button class="queue-btn view"><i class="fas fa-eye"></i></button>
                            </div>
                        </div>
                    </div>
                    <div class="queue-item">
                        <div class="queue-item-image">
                            <i class="fas fa-mobile-alt"></i>
                        </div>
                        <div class="queue-item-info">
                            <h4>iPhone 13 Pro</h4>
                            <div class="queue-item-store">Móvil Store</div>
                            <div class="queue-item-price">$95,000 CUP</div>
                            <div class="queue-item-actions">
                                <button class="queue-btn approve"><i class="fas fa-check"></i> Aprobar</button>
                                <button class="queue-btn reject"><i class="fas fa-times"></i> Rechazar</button>
                                <button class="queue-btn view"><i class="fas fa-eye"></i></button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Bar -->
            <div class="action-bar">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Buscar producto por nombre o SKU...">
                </div>
                <div class="filter-group">
                    <select class="filter-select">
                        <option>Todas las categorías</option>
                        <option>Ropa</option>
                        <option>Electrónica</option>
                        <option>Calzado</option>
                        <option>Accesorios</option>
                    </select>
                    <select class="filter-select">
                        <option>Todos los estados</option>
                        <option>Activos</option>
                        <option>Pendientes</option>
                        <option>Rechazados</option>
                        <option>Ocultos</option>
                    </select>
                    <select class="filter-select">
                        <option>Stock</option>
                        <option>Con stock</option>
                        <option>Stock bajo</option>
                        <option>Agotados</option>
                    </select>
                    <select class="filter-select">
                        <option>Ordenar por</option>
                        <option>Fecha (reciente)</option>
                        <option>Más vendidos</option>
                        <option>Precio: menor a mayor</option>
                        <option>Precio: mayor a menor</option>
                    </select>
                    <button class="btn-primary">
                        <i class="fas fa-filter"></i>
                        Filtrar
                    </button>
                    <button class="btn-secondary">
                        <i class="fas fa-download"></i>
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
                            <th>Tienda</th>
                            <th>Precio</th>
                            <th>Stock</th>
                            <th>Ventas</th>
                            <th>Estado</th>
                            <th>Reportes</th>
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
                                <span class="store-badge">
                                    <i class="fas fa-store"></i>
                                    Nexus Fashion
                                </span>
                            </td>
                            <td>
                                <div class="price-info">
                                    $1,300 CUP
                                    <br>
                                    <small>$37 USD</small>
                                </div>
                            </td>
                            <td>
                                <span class="stock-badge normal">45</span>
                            </td>
                            <td>234</td>
                            <td>
                                <span class="status-badge active">
                                    <i class="fas fa-circle"></i>
                                    Activo
                                </span>
                            </td>
                            <td>-</td>
                            <td>
                                <div class="action-buttons">
                                    <button class="action-btn" onclick="viewProductDetails('Camiseta Oversize Negra')"><i class="fas fa-eye"></i></button>
                                    <button class="action-btn"><i class="fas fa-ban"></i></button>
                                    <button class="action-btn"><i class="fas fa-edit"></i></button>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div class="product-cell">
                                    <div class="product-image">
                                        <i class="fas fa-laptop"></i>
                                    </div>
                                    <div class="product-info">
                                        <h4>Laptop Lenovo ThinkPad</h4>
                                        <span>SKU: LEN-001-X1</span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="store-badge">
                                    <i class="fas fa-store"></i>
                                    Tech Store
                                </span>
                            </td>
                            <td>
                                <div class="price-info">
                                    $45,000 CUP
                                    <br>
                                    <small>$1,285 USD</small>
                                </div>
                            </td>
                            <td>
                                <span class="stock-badge normal">12</span>
                            </td>
                            <td>45</td>
                            <td>
                                <span class="status-badge pending">
                                    <i class="fas fa-clock"></i>
                                    Pendiente
                                </span>
                            </td>
                            <td>-</td>
                            <td>
                                <div class="action-buttons">
                                    <button class="action-btn"><i class="fas fa-eye"></i></button>
                                    <button class="action-btn approve"><i class="fas fa-check" style="color: #06d6a0;"></i></button>
                                    <button class="action-btn reject"><i class="fas fa-times" style="color: #e63946;"></i></button>
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
                                        <h4>Zapatillas Nike Air</h4>
                                        <span>SKU: ZAP-002-NIK</span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="store-badge">
                                    <i class="fas fa-store"></i>
                                    Deportes Cuba
                                </span>
                            </td>
                            <td>
                                <div class="price-info">
                                    $8,500 CUP
                                    <br>
                                    <small>$243 USD</small>
                                </div>
                            </td>
                            <td>
                                <span class="stock-badge low">3</span>
                            </td>
                            <td>89</td>
                            <td>
                                <span class="status-badge active">
                                    <i class="fas fa-circle"></i>
                                    Activo
                                </span>
                            </td>
                            <td>
                                <span class="report-badge">2</span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <button class="action-btn"><i class="fas fa-eye"></i></button>
                                    <button class="action-btn"><i class="fas fa-ban"></i></button>
                                    <button class="action-btn"><i class="fas fa-flag" style="color: #e63946;"></i></button>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div class="product-cell">
                                    <div class="product-image">
                                        <i class="fas fa-mobile-alt"></i>
                                    </div>
                                    <div class="product-info">
                                        <h4>iPhone 13 Pro</h4>
                                        <span>SKU: APL-013-PRO</span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="store-badge">
                                    <i class="fas fa-store"></i>
                                    Móvil Store
                                </span>
                            </td>
                            <td>
                                <div class="price-info">
                                    $95,000 CUP
                                    <br>
                                    <small>$2,714 USD</small>
                                </div>
                            </td>
                            <td>
                                <span class="stock-badge normal">8</span>
                            </td>
                            <td>23</td>
                            <td>
                                <span class="status-badge rejected">
                                    <i class="fas fa-times-circle"></i>
                                    Rechazado
                                </span>
                            </td>
                            <td>-</td>
                            <td>
                                <div class="action-buttons">
                                    <button class="action-btn"><i class="fas fa-eye"></i></button>
                                    <button class="action-btn"><i class="fas fa-undo-alt" style="color: #ffb703;"></i></button>
                                    <button class="action-btn delete"><i class="fas fa-trash"></i></button>
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
                                        <h4>Gorra New Era</h4>
                                        <span>SKU: GOR-003-NEW</span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="store-badge">
                                    <i class="fas fa-store"></i>
                                    Fashion Store
                                </span>
                            </td>
                            <td>
                                <div class="price-info">
                                    $1,500 CUP
                                    <br>
                                    <small>$43 USD</small>
                                </div>
                            </td>
                            <td>
                                <span class="stock-badge critical">0</span>
                            </td>
                            <td>156</td>
                            <td>
                                <span class="status-badge hidden">
                                    <i class="fas fa-eye-slash"></i>
                                    Oculto
                                </span>
                            </td>
                            <td>-</td>
                            <td>
                                <div class="action-buttons">
                                    <button class="action-btn"><i class="fas fa-eye"></i></button>
                                    <button class="action-btn"><i class="fas fa-play" style="color: #06d6a0;"></i></button>
                                    <button class="action-btn"><i class="fas fa-edit"></i></button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="pagination">
                <div class="pagination-info">
                    Mostrando 5 de 4,567 productos
                </div>
                <div class="pagination-controls">
                    <button class="page-btn"><i class="fas fa-chevron-left"></i></button>
                    <button class="page-btn active">1</button>
                    <button class="page-btn">2</button>
                    <button class="page-btn">3</button>
                    <button class="page-btn">4</button>
                    <button class="page-btn">5</button>
                    <button class="page-btn"><i class="fas fa-chevron-right"></i></button>
                </div>
            </div>
        </main>
    </div>

    <!-- Modal Detalle de Producto -->
    <div class="modal-overlay" id="productDetailModal">
        <div class="modal">
            <div class="modal-header">
                <h2>Detalle del Producto</h2>
                <button class="modal-close" onclick="closeProductDetailModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <div class="product-detail-header">
                    <div class="product-detail-gallery">
                        <div class="product-detail-main-image">
                            <i class="fas fa-tshirt"></i>
                        </div>
                        <div class="product-detail-thumbnails">
                            <div class="product-thumb">
                                <i class="fas fa-tshirt"></i>
                            </div>
                            <div class="product-thumb">
                                <i class="fas fa-tshirt"></i>
                            </div>
                            <div class="product-thumb">
                                <i class="fas fa-tshirt"></i>
                            </div>
                        </div>
                    </div>
                    <div class="product-detail-info">
                        <h3 id="detailProductName">Camiseta Oversize Negra</h3>
                        <div class="product-detail-meta">
                            <span><i class="fas fa-store"></i> Nexus Fashion</span>
                            <span><i class="fas fa-tag"></i> SKU: CAM-001-NEG</span>
                            <span><i class="fas fa-calendar"></i> Publicado: 15/03/2025</span>
                        </div>
                        
                        <div class="product-detail-prices">
                            <div class="price-box">
                                <div class="price-label">Precio CUP</div>
                                <div class="price-value">$1,300</div>
                            </div>
                            <div class="price-box">
                                <div class="price-label">Precio USD</div>
                                <div class="price-value">$37</div>
                            </div>
                        </div>

                        <div class="detail-grid">
                            <div class="detail-item">
                                <span class="detail-label">Categoría</span>
                                <span class="detail-value">Ropa > Camisetas</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Stock actual</span>
                                <span class="detail-value">
                                    <span class="stock-badge normal">45 unidades</span>
                                </span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Ventas totales</span>
                                <span class="detail-value">234 unidades</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Valoración</span>
                                <span class="detail-value">
                                    <i class="fas fa-star" style="color: #ffb703;"></i> 4.8 (45 reseñas)
                                </span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Estado</span>
                                <span class="detail-value">
                                    <span class="status-badge active">Activo</span>
                                </span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Visibilidad</span>
                                <span class="detail-value">Público</span>
                            </div>
                        </div>

                        <div class="detail-section" style="margin-top: 1rem;">
                            <h4>Descripción</h4>
                            <p style="color: #6c757d; line-height: 1.5;">
                                Camiseta oversize 100% algodón, corte moderno, ideal para uso diario. 
                                Disponible en tallas S, M, L, XL. Colores: Negro, Blanco, Gris.
                            </p>
                        </div>

                        <!-- Reports Section (visible solo si hay reportes) -->
                        <div class="reports-section">
                            <h4 style="color: #e63946; margin-bottom: 0.5rem;">
                                <i class="fas fa-exclamation-triangle"></i>
                                Reportes de usuarios (2)
                            </h4>
                            <div class="report-item">
                                <div>
                                    <div class="report-reason">Producto falsificado / no original</div>
                                    <div style="font-size: 0.7rem; color: #6c757d;">Reportado por: Juan Pérez</div>
                                </div>
                                <div class="report-date">Hace 2 días</div>
                            </div>
                            <div class="report-item">
                                <div>
                                    <div class="report-reason">Precio incorrecto / engañoso</div>
                                    <div style="font-size: 0.7rem; color: #6c757d;">Reportado por: María Gómez</div>
                                </div>
                                <div class="report-date">Hace 1 día</div>
                            </div>
                        </div>

                        <!-- Rejection Reason (visible solo si está rechazado) -->
                        <div class="rejection-input" style="display: none;">
                            <label style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Razón de rechazo:</label>
                            <textarea rows="3" style="width: 100%; padding: 0.75rem; border: 1px solid #e9ecef; border-radius: 8px;">El producto no cumple con las políticas de autenticidad</textarea>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn-secondary" onclick="closeProductDetailModal()">Cerrar</button>
                <button class="btn-success" onclick="approveProduct()">
                    <i class="fas fa-check"></i> Aprobar
                </button>
                <button class="btn-warning">
                    <i class="fas fa-ban"></i> Ocultar
                </button>
                <button class="btn-danger">
                    <i class="fas fa-times"></i> Rechazar
                </button>
            </div>
        </div>
    </div>

    <script>
        // Modal functions
        function viewProductDetails(productName) {
            document.getElementById('productDetailModal').classList.add('show');
            document.getElementById('detailProductName').textContent = productName;
        }

        function closeProductDetailModal() {
            document.getElementById('productDetailModal').classList.remove('show');
        }

        function approveProduct(productName) {
            if(confirm(`¿Aprobar el producto?`)) {
                alert(`Producto aprobado (demo)`);
                closeProductDetailModal();
            }
        }

        function rejectProduct(productName) {
            const reason = prompt('Indica la razón del rechazo:');
            if(reason) {
                alert(`Producto rechazado: ${reason} (demo)`);
            }
        }

        // Close modal on outside click
        document.getElementById('productDetailModal').addEventListener('click', function(e) {
            if(e.target === this) {
                closeProductDetailModal();
            }
        });

        // Filter button
        document.querySelector('.btn-primary').addEventListener('click', function() {
            if(this.querySelector('.fa-filter')) {
                alert('Aplicando filtros (demo)');
            }
        });

        // Export button
        document.querySelector('.btn-secondary').addEventListener('click', function() {
            alert('Exportando lista de productos (demo)');
        });

        // Queue buttons
        document.querySelectorAll('.queue-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                if(btn.classList.contains('approve')) {
                    approveProduct();
                } else if(btn.classList.contains('reject')) {
                    rejectProduct();
                } else if(btn.classList.contains('view')) {
                    viewProductDetails('Producto de la cola');
                }
            });
        });

        // Action buttons in table
        document.querySelectorAll('.action-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                
                if(btn.querySelector('.fa-check')) {
                    approveProduct();
                } else if(btn.querySelector('.fa-times')) {
                    rejectProduct();
                } else if(btn.querySelector('.fa-ban')) {
                    alert('Ocultando producto (demo)');
                } else if(btn.querySelector('.fa-undo-alt')) {
                    alert('Reactivando producto (demo)');
                } else if(btn.querySelector('.fa-play')) {
                    alert('Publicando producto (demo)');
                } else if(btn.querySelector('.fa-flag')) {
                    alert('Viendo reportes (demo)');
                } else if(btn.querySelector('.fa-trash')) {
                    if(confirm('¿Eliminar este producto permanentemente?')) {
                        alert('Producto eliminado (demo)');
                    }
                }
            });
        });

        // Navigation
        document.querySelectorAll('.admin-nav-item').forEach(item => {
            item.addEventListener('click', (e) => {
                e.preventDefault();
                document.querySelectorAll('.admin-nav-item').forEach(nav => nav.classList.remove('active'));
                item.classList.add('active');
            });
        });
    </script>
</body>
</html>