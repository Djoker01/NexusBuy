<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NexusBuy - Gestión de Tiendas</title>
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
        .store-stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .store-stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 16px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.02);
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .store-stat-icon {
            width: 56px;
            height: 56px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.75rem;
        }

        .store-stat-icon.blue {
            background: #e1e8ff;
            color: #4361ee;
        }

        .store-stat-icon.green {
            background: #d1f7ea;
            color: #06d6a0;
        }

        .store-stat-icon.yellow {
            background: #fff3d1;
            color: #ffb703;
        }

        .store-stat-icon.purple {
            background: #ead1ff;
            color: #7209b7;
        }

        .store-stat-info h3 {
            font-size: 1.75rem;
            font-weight: 700;
            margin-bottom: 0.25rem;
        }

        .store-stat-info p {
            color: #6c757d;
            font-size: 0.875rem;
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

        .btn-success {
            background: #06d6a0;
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
        }

        .btn-success:hover {
            background: #05b586;
        }

        .btn-warning {
            background: #ffb703;
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
        }

        .btn-warning:hover {
            background: #e6a500;
        }

        /* Stores Table */
        .stores-table-container {
            background: white;
            border-radius: 16px;
            border: 1px solid #e9ecef;
            overflow: auto;
            margin-bottom: 1.5rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.02);
        }

        .stores-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 1300px;
        }

        .stores-table th {
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

        .stores-table td {
            padding: 1rem;
            border-bottom: 1px solid #e9ecef;
            font-size: 0.875rem;
        }

        .store-cell {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .store-logo {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            background: #f1f3f5;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: #4361ee;
            overflow: hidden;
        }

        .store-logo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .store-info h4 {
            font-weight: 600;
            font-size: 0.875rem;
            margin-bottom: 0.25rem;
        }

        .store-info span {
            font-size: 0.75rem;
            color: #6c757d;
        }

        .owner-cell {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .owner-avatar {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            background: #e1e8ff;
            color: #4361ee;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 0.75rem;
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

        .status-badge.inactive {
            background: #e9ecef;
            color: #6c757d;
        }

        .status-badge.pending {
            background: #fff3d1;
            color: #ffb703;
        }

        .status-badge.suspended {
            background: #ffe5e5;
            color: #e63946;
        }

        .verified-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            color: #06d6a0;
            font-size: 0.75rem;
        }

        .rating {
            display: flex;
            align-items: center;
            gap: 0.25rem;
            color: #ffb703;
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
            max-width: 700px;
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

        /* Store Detail */
        .store-detail-header {
            display: flex;
            align-items: center;
            gap: 1.5rem;
            margin-bottom: 2rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid #e9ecef;
        }

        .store-detail-logo {
            width: 100px;
            height: 100px;
            border-radius: 20px;
            background: #f1f3f5;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            color: #4361ee;
            overflow: hidden;
        }

        .store-detail-logo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .store-detail-info h3 {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .store-detail-meta {
            display: flex;
            gap: 1.5rem;
            color: #6c757d;
            font-size: 0.875rem;
            flex-wrap: wrap;
        }

        .detail-section {
            margin-bottom: 2rem;
        }

        .detail-section h4 {
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 1rem;
        }

        .detail-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 8px;
        }

        .detail-item {
            display: flex;
            flex-direction: column;
        }

        .detail-label {
            font-size: 0.7rem;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.25rem;
        }

        .detail-value {
            font-weight: 500;
        }

        .products-preview {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
        }

        .product-mini {
            text-align: center;
        }

        .product-mini-image {
            width: 80px;
            height: 80px;
            background: #f1f3f5;
            border-radius: 8px;
            margin: 0 auto 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #6c757d;
        }

        .product-mini-name {
            font-size: 0.75rem;
            font-weight: 500;
            margin-bottom: 0.25rem;
        }

        .product-mini-price {
            font-size: 0.7rem;
            color: #4361ee;
        }

        .commission-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 8px;
            margin-top: 1rem;
        }

        .commission-edit {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .commission-input {
            width: 80px;
            padding: 0.5rem;
            border: 1px solid #e9ecef;
            border-radius: 4px;
        }

        .btn-outline-sm {
            background: white;
            border: 1px solid #e9ecef;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.7rem;
            cursor: pointer;
        }

        .rejection-reason {
            background: #ffe5e5;
            padding: 1rem;
            border-radius: 8px;
            margin-top: 1rem;
            color: #e63946;
        }

        /* Responsive */
        @media (max-width: 1200px) {
            .store-stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .products-preview {
                grid-template-columns: repeat(2, 1fr);
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
            
            .store-stats-grid {
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
            
            .store-detail-header {
                flex-direction: column;
                text-align: center;
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
                <a href="#" class="admin-nav-item active">
                    <i class="fas fa-store"></i>
                    Tiendas
                </a>
                <a href="#" class="admin-nav-item">
                    <i class="fas fa-box"></i>
                    Productos
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
                    <h1>Gestión de Tiendas</h1>
                    <p>Administra todas las tiendas del sistema</p>
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
            <div class="store-stats-grid">
                <div class="store-stat-card">
                    <div class="store-stat-icon blue">
                        <i class="fas fa-store"></i>
                    </div>
                    <div class="store-stat-info">
                        <h3>128</h3>
                        <p>Tiendas totales</p>
                    </div>
                </div>
                <div class="store-stat-card">
                    <div class="store-stat-icon green">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="store-stat-info">
                        <h3>98</h3>
                        <p>Activas</p>
                    </div>
                </div>
                <div class="store-stat-card">
                    <div class="store-stat-icon yellow">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="store-stat-info">
                        <h3>12</h3>
                        <p>Pendientes</p>
                    </div>
                </div>
                <div class="store-stat-card">
                    <div class="store-stat-icon purple">
                        <i class="fas fa-ban"></i>
                    </div>
                    <div class="store-stat-info">
                        <h3>18</h3>
                        <p>Suspendidas</p>
                    </div>
                </div>
            </div>

            <!-- Action Bar -->
            <div class="action-bar">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Buscar tienda por nombre o propietario...">
                </div>
                <div class="filter-group">
                    <select class="filter-select">
                        <option>Todos los estados</option>
                        <option>Activas</option>
                        <option>Pendientes</option>
                        <option>Suspendidas</option>
                        <option>Inactivas</option>
                    </select>
                    <select class="filter-select">
                        <option>Verificación</option>
                        <option>Verificadas</option>
                        <option>No verificadas</option>
                    </select>
                    <select class="filter-select">
                        <option>Ordenar por</option>
                        <option>Fecha (reciente)</option>
                        <option>Fecha (antiguo)</option>
                        <option>Más ventas</option>
                        <option>Mayor valoración</option>
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

            <!-- Stores Table -->
            <div class="stores-table-container">
                <table class="stores-table">
                    <thead>
                        <tr>
                            <th>Tienda</th>
                            <th>Propietario</th>
                            <th>Email</th>
                            <th>Productos</th>
                            <th>Ventas</th>
                            <th>Valoración</th>
                            <th>Estado</th>
                            <th>Verificada</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <div class="store-cell">
                                    <div class="store-logo">
                                        <i class="fas fa-tshirt"></i>
                                    </div>
                                    <div class="store-info">
                                        <h4>Nexus Fashion</h4>
                                        <span>Ropa y accesorios</span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="owner-cell">
                                    <div class="owner-avatar">MP</div>
                                    <span>María Pérez</span>
                                </div>
                            </td>
                            <td>maria@nexusfashion.com</td>
                            <td>45</td>
                            <td>$12,450</td>
                            <td>
                                <div class="rating">
                                    <i class="fas fa-star"></i>
                                    4.8
                                </div>
                            </td>
                            <td>
                                <span class="status-badge active">
                                    <i class="fas fa-circle"></i>
                                    Activa
                                </span>
                            </td>
                            <td>
                                <span class="verified-badge">
                                    <i class="fas fa-check-circle"></i>
                                    Verificada
                                </span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <button class="action-btn" onclick="viewStoreDetails('Nexus Fashion')"><i class="fas fa-eye"></i></button>
                                    <button class="action-btn"><i class="fas fa-edit"></i></button>
                                    <button class="action-btn"><i class="fas fa-ban"></i></button>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div class="store-cell">
                                    <div class="store-logo">
                                        <i class="fas fa-laptop"></i>
                                    </div>
                                    <div class="store-info">
                                        <h4>Tech Store</h4>
                                        <span>Electrónica</span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="owner-cell">
                                    <div class="owner-avatar">JL</div>
                                    <span>Juan López</span>
                                </div>
                            </td>
                            <td>juan@techstore.com</td>
                            <td>32</td>
                            <td>$8,900</td>
                            <td>
                                <div class="rating">
                                    <i class="fas fa-star"></i>
                                    4.5
                                </div>
                            </td>
                            <td>
                                <span class="status-badge pending">
                                    <i class="fas fa-clock"></i>
                                    Pendiente
                                </span>
                            </td>
                            <td>
                                <span class="verified-badge" style="color: #6c757d;">
                                    <i class="fas fa-times-circle"></i>
                                    No verificada
                                </span>
                            </td>
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
                                <div class="store-cell">
                                    <div class="store-logo">
                                        <i class="fas fa-futbol"></i>
                                    </div>
                                    <div class="store-info">
                                        <h4>Deportes Cuba</h4>
                                        <span>Artículos deportivos</span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="owner-cell">
                                    <div class="owner-avatar">CR</div>
                                    <span>Carlos Ruiz</span>
                                </div>
                            </td>
                            <td>carlos@deportescuba.com</td>
                            <td>56</td>
                            <td>$15,200</td>
                            <td>
                                <div class="rating">
                                    <i class="fas fa-star"></i>
                                    4.9
                                </div>
                            </td>
                            <td>
                                <span class="status-badge active">
                                    <i class="fas fa-circle"></i>
                                    Activa
                                </span>
                            </td>
                            <td>
                                <span class="verified-badge">
                                    <i class="fas fa-check-circle"></i>
                                    Verificada
                                </span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <button class="action-btn"><i class="fas fa-eye"></i></button>
                                    <button class="action-btn"><i class="fas fa-edit"></i></button>
                                    <button class="action-btn"><i class="fas fa-ban"></i></button>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div class="store-cell">
                                    <div class="store-logo">
                                        <i class="fas fa-mobile-alt"></i>
                                    </div>
                                    <div class="store-info">
                                        <h4>Móvil Store</h4>
                                        <span>Celulares y accesorios</span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="owner-cell">
                                    <div class="owner-avatar">AG</div>
                                    <span>Ana García</span>
                                </div>
                            </td>
                            <td>ana@movilstore.com</td>
                            <td>28</td>
                            <td>$6,750</td>
                            <td>
                                <div class="rating">
                                    <i class="fas fa-star"></i>
                                    4.2
                                </div>
                            </td>
                            <td>
                                <span class="status-badge suspended">
                                    <i class="fas fa-ban"></i>
                                    Suspendida
                                </span>
                            </td>
                            <td>
                                <span class="verified-badge" style="color: #6c757d;">
                                    <i class="fas fa-times-circle"></i>
                                    No verificada
                                </span>
                            </td>
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
                                <div class="store-cell">
                                    <div class="store-logo">
                                        <i class="fas fa-book"></i>
                                    </div>
                                    <div class="store-info">
                                        <h4>Librería Central</h4>
                                        <span>Libros y papelería</span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="owner-cell">
                                    <div class="owner-avatar">LM</div>
                                    <span>Laura Martínez</span>
                                </div>
                            </td>
                            <td>laura@libreriacentral.com</td>
                            <td>89</td>
                            <td>$5,230</td>
                            <td>
                                <div class="rating">
                                    <i class="fas fa-star"></i>
                                    4.6
                                </div>
                            </td>
                            <td>
                                <span class="status-badge inactive">
                                    <i class="fas fa-circle"></i>
                                    Inactiva
                                </span>
                            </td>
                            <td>
                                <span class="verified-badge" style="color: #6c757d;">
                                    <i class="fas fa-times-circle"></i>
                                    No verificada
                                </span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <button class="action-btn"><i class="fas fa-eye"></i></button>
                                    <button class="action-btn"><i class="fas fa-edit"></i></button>
                                    <button class="action-btn"><i class="fas fa-play" style="color: #06d6a0;"></i></button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="pagination">
                <div class="pagination-info">
                    Mostrando 5 de 128 tiendas
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

    <!-- Modal Detalle de Tienda -->
    <div class="modal-overlay" id="storeDetailModal">
        <div class="modal">
            <div class="modal-header">
                <h2>Detalle de la Tienda</h2>
                <button class="modal-close" onclick="closeStoreDetailModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <div class="store-detail-header">
                    <div class="store-detail-logo">
                        <i class="fas fa-tshirt"></i>
                    </div>
                    <div class="store-detail-info">
                        <h3 id="detailStoreName">Nexus Fashion</h3>
                        <div class="store-detail-meta">
                            <span><i class="fas fa-user"></i> María Pérez</span>
                            <span><i class="fas fa-envelope"></i> maria@nexusfashion.com</span>
                            <span><i class="fas fa-phone"></i> +53 5 1234567</span>
                        </div>
                    </div>
                </div>

                <!-- Información básica -->
                <div class="detail-section">
                    <h4>Información general</h4>
                    <div class="detail-grid">
                        <div class="detail-item">
                            <span class="detail-label">Nombre tienda</span>
                            <span class="detail-value">Nexus Fashion</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Categoría</span>
                            <span class="detail-value">Ropa y accesorios</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Fecha registro</span>
                            <span class="detail-value">15/01/2025</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Estado</span>
                            <span class="detail-value">
                                <span class="status-badge active">
                                    <i class="fas fa-circle"></i>
                                    Activa
                                </span>
                            </span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Verificación</span>
                            <span class="detail-value">
                                <span class="verified-badge">
                                    <i class="fas fa-check-circle"></i>
                                    Verificada
                                </span>
                            </span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Valoración</span>
                            <span class="detail-value">
                                <div class="rating">
                                    <i class="fas fa-star"></i>
                                    4.8 (45 reseñas)
                                </div>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Estadísticas -->
                <div class="detail-section">
                    <h4>Estadísticas</h4>
                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem;">
                        <div style="background: #f8f9fa; padding: 1rem; border-radius: 8px; text-align: center;">
                            <div style="font-size: 1.5rem; font-weight: 700; color: #4361ee;">45</div>
                            <div style="font-size: 0.75rem; color: #6c757d;">Productos</div>
                        </div>
                        <div style="background: #f8f9fa; padding: 1rem; border-radius: 8px; text-align: center;">
                            <div style="font-size: 1.5rem; font-weight: 700; color: #4361ee;">234</div>
                            <div style="font-size: 0.75rem; color: #6c757d;">Pedidos</div>
                        </div>
                        <div style="background: #f8f9fa; padding: 1rem; border-radius: 8px; text-align: center;">
                            <div style="font-size: 1.5rem; font-weight: 700; color: #4361ee;">$12,450</div>
                            <div style="font-size: 0.75rem; color: #6c757d;">Ventas totales</div>
                        </div>
                    </div>
                </div>

                <!-- Productos destacados -->
                <div class="detail-section">
                    <h4>Productos destacados</h4>
                    <div class="products-preview">
                        <div class="product-mini">
                            <div class="product-mini-image">
                                <i class="fas fa-tshirt"></i>
                            </div>
                            <div class="product-mini-name">Camiseta Negra</div>
                            <div class="product-mini-price">$1,300</div>
                        </div>
                        <div class="product-mini">
                            <div class="product-mini-image">
                                <i class="fas fa-tshirt"></i>
                            </div>
                            <div class="product-mini-name">Jeans Azules</div>
                            <div class="product-mini-price">$2,300</div>
                        </div>
                        <div class="product-mini">
                            <div class="product-mini-image">
                                <i class="fas fa-hat-cowboy"></i>
                            </div>
                            <div class="product-mini-name">Gorra New Era</div>
                            <div class="product-mini-price">$1,500</div>
                        </div>
                        <div class="product-mini">
                            <div class="product-mini-image">
                                <i class="fas fa-shoe-prints"></i>
                            </div>
                            <div class="product-mini-name">Zapatillas</div>
                            <div class="product-mini-price">$3,900</div>
                        </div>
                    </div>
                </div>

                <!-- Comisión -->
                <div class="detail-section">
                    <h4>Configuración de comisión</h4>
                    <div class="commission-info">
                        <div>
                            <span style="font-weight: 500;">Comisión actual:</span>
                            <span style="color: #4361ee; font-weight: 700; margin-left: 0.5rem;">10%</span>
                        </div>
                        <div class="commission-edit">
                            <input type="number" class="commission-input" value="10" min="0" max="100" step="0.5">
                            <button class="btn-outline-sm">
                                <i class="fas fa-save"></i> Actualizar
                            </button>
                        </div>
                    </div>
                    <div style="margin-top: 0.5rem; font-size: 0.75rem; color: #6c757d;">
                        <i class="fas fa-info-circle"></i>
                        La comisión se aplica a cada venta realizada en la tienda
                    </div>
                </div>

                <!-- Razón de suspensión (si aplica) -->
                <div class="rejection-reason" style="display: none;">
                    <strong><i class="fas fa-exclamation-triangle"></i> Razón de suspensión:</strong>
                    <p style="margin-top: 0.5rem;">Incumplimiento de políticas de envío - 3 quejas de clientes en la última semana</p>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn-secondary" onclick="closeStoreDetailModal()">Cerrar</button>
                <button class="btn-success" onclick="approveStore()">
                    <i class="fas fa-check"></i> Aprobar
                </button>
                <button class="btn-warning">
                    <i class="fas fa-ban"></i> Suspender
                </button>
                <button class="btn-primary">
                    <i class="fas fa-edit"></i> Editar
                </button>
            </div>
        </div>
    </div>

    <script>
        // Modal functions
        function viewStoreDetails(storeName) {
            document.getElementById('storeDetailModal').classList.add('show');
            document.getElementById('detailStoreName').textContent = storeName;
        }

        function closeStoreDetailModal() {
            document.getElementById('storeDetailModal').classList.remove('show');
        }

        function approveStore() {
            if(confirm('¿Aprobar esta tienda?')) {
                alert('Tienda aprobada (demo)');
                closeStoreDetailModal();
            }
        }

        // Close modal on outside click
        document.getElementById('storeDetailModal').addEventListener('click', function(e) {
            if(e.target === this) {
                closeStoreDetailModal();
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
            alert('Exportando lista de tiendas (demo)');
        });

        // Action buttons in table
        document.querySelectorAll('.action-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                
                if(btn.querySelector('.fa-check')) {
                    alert('Aprobando tienda (demo)');
                } else if(btn.querySelector('.fa-times')) {
                    alert('Rechazando tienda (demo)');
                } else if(btn.querySelector('.fa-ban')) {
                    alert('Suspendiendo tienda (demo)');
                } else if(btn.querySelector('.fa-undo-alt')) {
                    alert('Reactivando tienda (demo)');
                } else if(btn.querySelector('.fa-play')) {
                    alert('Activando tienda (demo)');
                } else if(btn.querySelector('.fa-trash')) {
                    if(confirm('¿Eliminar esta tienda?')) {
                        alert('Tienda eliminada (demo)');
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

        // Update commission
        document.querySelector('.btn-outline-sm').addEventListener('click', function() {
            alert('Comisión actualizada (demo)');
        });
    </script>
</body>
</html>