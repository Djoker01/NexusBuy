<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NexusBuy - Gestión de Usuarios</title>
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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

        /* Sidebar Admin (mantenemos el mismo estilo) */
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
        .user-stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .user-stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 16px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.02);
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .user-stat-icon {
            width: 56px;
            height: 56px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.75rem;
        }

        .user-stat-icon.blue {
            background: #e1e8ff;
            color: #4361ee;
        }

        .user-stat-icon.green {
            background: #d1f7ea;
            color: #06d6a0;
        }

        .user-stat-icon.purple {
            background: #ead1ff;
            color: #7209b7;
        }

        .user-stat-icon.orange {
            background: #ffead1;
            color: #fb8b24;
        }

        .user-stat-info h3 {
            font-size: 1.75rem;
            font-weight: 700;
            margin-bottom: 0.25rem;
        }

        .user-stat-info p {
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

        /* Users Table */
        .users-table-container {
            background: white;
            border-radius: 16px;
            border: 1px solid #e9ecef;
            overflow: auto;
            margin-bottom: 1.5rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.02);
        }

        .users-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 1200px;
        }

        .users-table th {
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

        .users-table td {
            padding: 1rem;
            border-bottom: 1px solid #e9ecef;
            font-size: 0.875rem;
        }

        .user-cell {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .user-avatar-table {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            color: white;
        }

        .user-avatar-table.blue {
            background: #4361ee;
        }

        .user-avatar-table.green {
            background: #06d6a0;
        }

        .user-avatar-table.purple {
            background: #7209b7;
        }

        .user-avatar-table.orange {
            background: #fb8b24;
        }

        .user-info h4 {
            font-weight: 600;
            font-size: 0.875rem;
            margin-bottom: 0.25rem;
        }

        .user-info span {
            font-size: 0.75rem;
            color: #6c757d;
        }

        .role-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.5rem;
            border-radius: 999px;
            font-size: 0.7rem;
            font-weight: 500;
        }

        .role-badge.admin {
            background: #e1e8ff;
            color: #4361ee;
        }

        .role-badge.vendor {
            background: #d1f7ea;
            color: #06d6a0;
        }

        .role-badge.seller {
            background: #fff3d1;
            color: #ffb703;
        }

        .role-badge.user {
            background: #e9ecef;
            color: #6c757d;
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

        .status-badge.suspended {
            background: #ffe5e5;
            color: #e63946;
        }

        .status-badge.pending {
            background: #fff3d1;
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

        .action-btn.delete:hover {
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
            max-width: 600px;
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

        /* Formularios */
        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
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

        /* User Detail Modal */
        .user-detail-header {
            display: flex;
            align-items: center;
            gap: 1.5rem;
            margin-bottom: 2rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid #e9ecef;
        }

        .user-detail-avatar {
            width: 80px;
            height: 80px;
            border-radius: 20px;
            background: #4361ee;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            font-weight: 600;
            color: white;
        }

        .user-detail-info h3 {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .user-detail-meta {
            display: flex;
            gap: 1rem;
            color: #6c757d;
            font-size: 0.875rem;
        }

        .detail-section {
            margin-bottom: 1.5rem;
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

        .stats-mini-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
            margin-top: 1rem;
        }

        .stat-mini-card {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 8px;
            text-align: center;
        }

        .stat-mini-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: #4361ee;
            margin-bottom: 0.25rem;
        }

        .stat-mini-label {
            font-size: 0.7rem;
            color: #6c757d;
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

        .btn-danger {
            background: white;
            color: #e63946;
            border: 1px solid #e63946;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-size: 0.875rem;
            cursor: pointer;
        }

        /* Responsive */
        @media (max-width: 1200px) {
            .user-stats-grid {
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
            
            .user-stats-grid {
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
            
            .form-grid {
                grid-template-columns: 1fr;
            }
            
            .form-group.full-width {
                grid-column: span 1;
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
                <a href="#" class="admin-nav-item active">
                    <i class="fas fa-users"></i>
                    Usuarios
                </a>
                <a href="#" class="admin-nav-item">
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
                    <h1>Gestión de Usuarios</h1>
                    <p>Administra todos los usuarios del sistema</p>
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
            <div class="user-stats-grid">
                <div class="user-stat-card">
                    <div class="user-stat-icon blue">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="user-stat-info">
                        <h3>2,345</h3>
                        <p>Usuarios totales</p>
                    </div>
                </div>
                <div class="user-stat-card">
                    <div class="user-stat-icon green">
                        <i class="fas fa-user-check"></i>
                    </div>
                    <div class="user-stat-info">
                        <h3>1,890</h3>
                        <p>Activos</p>
                    </div>
                </div>
                <div class="user-stat-card">
                    <div class="user-stat-icon purple">
                        <i class="fas fa-store"></i>
                    </div>
                    <div class="user-stat-info">
                        <h3>128</h3>
                        <p>Vendedores</p>
                    </div>
                </div>
                <div class="user-stat-card">
                    <div class="user-stat-icon orange">
                        <i class="fas fa-user-plus"></i>
                    </div>
                    <div class="user-stat-info">
                        <h3>45</h3>
                        <p>Nuevos hoy</p>
                    </div>
                </div>
            </div>

            <!-- Action Bar -->
            <div class="action-bar">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Buscar por nombre, email o ID...">
                </div>
                <div class="filter-group">
                    <select class="filter-select">
                        <option>Todos los roles</option>
                        <option>Administradores</option>
                        <option>Vendedores</option>
                        <option>Compradores</option>
                    </select>
                    <select class="filter-select">
                        <option>Todos los estados</option>
                        <option>Activos</option>
                        <option>Inactivos</option>
                        <option>Suspendidos</option>
                        <option>Pendientes</option>
                    </select>
                    <select class="filter-select">
                        <option>Ordenar por</option>
                        <option>Fecha registro (reciente)</option>
                        <option>Fecha registro (antiguo)</option>
                        <option>Nombre A-Z</option>
                        <option>Nombre Z-A</option>
                    </select>
                    <button class="btn-primary">
                        <i class="fas fa-filter"></i>
                        Filtrar
                    </button>
                    <button class="btn-secondary">
                        <i class="fas fa-download"></i>
                        Exportar
                    </button>
                    <button class="btn-primary" onclick="openUserModal()">
                        <i class="fas fa-plus"></i>
                        Nuevo usuario
                    </button>
                </div>
            </div>

            <!-- Users Table -->
            <div class="users-table-container">
                <table class="users-table">
                    <thead>
                        <tr>
                            <th>Usuario</th>
                            <th>Email</th>
                            <th>Rol</th>
                            <th>Estado</th>
                            <th>Tienda</th>
                            <th>Registro</th>
                            <th>Último acceso</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <div class="user-cell">
                                    <div class="user-avatar-table blue">MP</div>
                                    <div class="user-info">
                                        <h4>María Pérez</h4>
                                        <span>@mariaperez</span>
                                    </div>
                                </div>
                            </td>
                            <td>maria.perez@email.com</td>
                            <td>
                                <span class="role-badge vendor">Vendedor</span>
                            </td>
                            <td>
                                <span class="status-badge active">
                                    <i class="fas fa-circle" style="font-size: 0.5rem;"></i>
                                    Activo
                                </span>
                            </td>
                            <td>Fashion Store</td>
                            <td>15/03/2025</td>
                            <td>Hoy 10:30</td>
                            <td>
                                <div class="action-buttons">
                                    <button class="action-btn" onclick="viewUserDetails('María Pérez')"><i class="fas fa-eye"></i></button>
                                    <button class="action-btn" onclick="editUser('María Pérez')"><i class="fas fa-edit"></i></button>
                                    <button class="action-btn" onclick="suspendUser('María Pérez')"><i class="fas fa-ban"></i></button>
                                    <button class="action-btn delete" onclick="deleteUser('María Pérez')"><i class="fas fa-trash"></i></button>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div class="user-cell">
                                    <div class="user-avatar-table green">JL</div>
                                    <div class="user-info">
                                        <h4>Juan López</h4>
                                        <span>@juanlopez</span>
                                    </div>
                                </div>
                            </td>
                            <td>juan.lopez@email.com</td>
                            <td>
                                <span class="role-badge vendor">Vendedor</span>
                            </td>
                            <td>
                                <span class="status-badge pending">
                                    <i class="fas fa-clock"></i>
                                    Pendiente
                                </span>
                            </td>
                            <td>Tech Store</td>
                            <td>14/03/2025</td>
                            <td>-</td>
                            <td>
                                <div class="action-buttons">
                                    <button class="action-btn"><i class="fas fa-eye"></i></button>
                                    <button class="action-btn"><i class="fas fa-check" style="color: #06d6a0;"></i></button>
                                    <button class="action-btn"><i class="fas fa-times" style="color: #e63946;"></i></button>
                                    <button class="action-btn"><i class="fas fa-edit"></i></button>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div class="user-cell">
                                    <div class="user-avatar-table purple">AG</div>
                                    <div class="user-info">
                                        <h4>Ana García</h4>
                                        <span>@anagarcia</span>
                                    </div>
                                </div>
                            </td>
                            <td>ana.garcia@email.com</td>
                            <td>
                                <span class="role-badge user">Comprador</span>
                            </td>
                            <td>
                                <span class="status-badge active">
                                    <i class="fas fa-circle" style="font-size: 0.5rem;"></i>
                                    Activo
                                </span>
                            </td>
                            <td>-</td>
                            <td>13/03/2025</td>
                            <td>Ayer 18:45</td>
                            <td>
                                <div class="action-buttons">
                                    <button class="action-btn"><i class="fas fa-eye"></i></button>
                                    <button class="action-btn"><i class="fas fa-edit"></i></button>
                                    <button class="action-btn"><i class="fas fa-ban"></i></button>
                                    <button class="action-btn delete"><i class="fas fa-trash"></i></button>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div class="user-cell">
                                    <div class="user-avatar-table orange">CR</div>
                                    <div class="user-info">
                                        <h4>Carlos Ruiz</h4>
                                        <span>@carlosruiz</span>
                                    </div>
                                </div>
                            </td>
                            <td>carlos.ruiz@email.com</td>
                            <td>
                                <span class="role-badge admin">Administrador</span>
                            </td>
                            <td>
                                <span class="status-badge active">
                                    <i class="fas fa-circle" style="font-size: 0.5rem;"></i>
                                    Activo
                                </span>
                            </td>
                            <td>-</td>
                            <td>12/03/2025</td>
                            <td>Hoy 09:15</td>
                            <td>
                                <div class="action-buttons">
                                    <button class="action-btn"><i class="fas fa-eye"></i></button>
                                    <button class="action-btn"><i class="fas fa-edit"></i></button>
                                    <button class="action-btn"><i class="fas fa-ban"></i></button>
                                    <button class="action-btn delete"><i class="fas fa-trash"></i></button>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div class="user-cell">
                                    <div class="user-avatar-table green">LM</div>
                                    <div class="user-info">
                                        <h4>Laura Martínez</h4>
                                        <span>@lauramartinez</span>
                                    </div>
                                </div>
                            </td>
                            <td>laura.martinez@email.com</td>
                            <td>
                                <span class="role-badge vendor">Vendedor</span>
                            </td>
                            <td>
                                <span class="status-badge inactive">
                                    <i class="fas fa-circle" style="font-size: 0.5rem;"></i>
                                    Inactivo
                                </span>
                            </td>
                            <td>Deportes Cuba</td>
                            <td>11/03/2025</td>
                            <td>Hace 5 días</td>
                            <td>
                                <div class="action-buttons">
                                    <button class="action-btn"><i class="fas fa-eye"></i></button>
                                    <button class="action-btn"><i class="fas fa-edit"></i></button>
                                    <button class="action-btn"><i class="fas fa-check" style="color: #06d6a0;"></i></button>
                                    <button class="action-btn delete"><i class="fas fa-trash"></i></button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="pagination">
                <div class="pagination-info">
                    Mostrando 5 de 2,345 usuarios
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

    <!-- Modal Crear/Editar Usuario -->
    <div class="modal-overlay" id="userModal">
        <div class="modal">
            <div class="modal-header">
                <h2 id="userModalTitle">Nuevo Usuario</h2>
                <button class="modal-close" onclick="closeUserModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <form>
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Nombre *</label>
                            <input type="text" class="form-control" placeholder="Ej: María">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Apellidos *</label>
                            <input type="text" class="form-control" placeholder="Ej: Pérez González">
                        </div>
                        <div class="form-group full-width">
                            <label class="form-label">Email *</label>
                            <input type="email" class="form-control" placeholder="usuario@email.com">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Teléfono</label>
                            <input type="text" class="form-control" placeholder="+53 5 1234567">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Fecha nacimiento</label>
                            <input type="date" class="form-control">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Rol *</label>
                            <select class="form-control">
                                <option>Comprador</option>
                                <option>Vendedor</option>
                                <option>Administrador</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Estado</label>
                            <select class="form-control">
                                <option>Activo</option>
                                <option>Inactivo</option>
                                <option>Suspendido</option>
                                <option>Pendiente</option>
                            </select>
                        </div>
                        <div class="form-group full-width">
                            <label class="form-label">Contraseña *</label>
                            <input type="password" class="form-control" placeholder="********">
                        </div>
                        <div class="form-group full-width">
                            <label class="form-label">Confirmar contraseña *</label>
                            <input type="password" class="form-control" placeholder="********">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn-cancel" onclick="closeUserModal()">Cancelar</button>
                <button class="btn-save">Guardar usuario</button>
            </div>
        </div>
    </div>

    <!-- Modal Ver Detalle de Usuario -->
    <div class="modal-overlay" id="userDetailModal">
        <div class="modal">
            <div class="modal-header">
                <h2>Detalle del Usuario</h2>
                <button class="modal-close" onclick="closeUserDetailModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <div class="user-detail-header">
                    <div class="user-detail-avatar">MP</div>
                    <div class="user-detail-info">
                        <h3 id="detailUserName">María Pérez</h3>
                        <div class="user-detail-meta">
                            <span><i class="fas fa-envelope"></i> maria.perez@email.com</span>
                            <span><i class="fas fa-phone"></i> +53 5 1234567</span>
                        </div>
                    </div>
                </div>

                <div class="detail-section">
                    <h4>Información personal</h4>
                    <div class="detail-grid">
                        <div class="detail-item">
                            <span class="detail-label">Nombre completo</span>
                            <span class="detail-value">María Pérez González</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Fecha nacimiento</span>
                            <span class="detail-value">15/05/1990 (34 años)</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Rol</span>
                            <span class="detail-value"><span class="role-badge vendor">Vendedor</span></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Estado</span>
                            <span class="detail-value"><span class="status-badge active">Activo</span></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Fecha registro</span>
                            <span class="detail-value">15/03/2025</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Último acceso</span>
                            <span class="detail-value">Hoy 10:30</span>
                        </div>
                    </div>
                </div>

                <div class="detail-section">
                    <h4>Información de la tienda</h4>
                    <div class="detail-grid">
                        <div class="detail-item">
                            <span class="detail-label">Tienda</span>
                            <span class="detail-value">Fashion Store</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Estado tienda</span>
                            <span class="detail-value"><span class="status-badge active">Activa</span></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Productos</span>
                            <span class="detail-value">24 productos</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Ventas totales</span>
                            <span class="detail-value">$12,450 CUP</span>
                        </div>
                    </div>
                </div>

                <div class="detail-section">
                    <h4>Estadísticas</h4>
                    <div class="stats-mini-grid">
                        <div class="stat-mini-card">
                            <div class="stat-mini-value">45</div>
                            <div class="stat-mini-label">Pedidos</div>
                        </div>
                        <div class="stat-mini-card">
                            <div class="stat-mini-value">4.8</div>
                            <div class="stat-mini-label">Valoración</div>
                        </div>
                        <div class="stat-mini-card">
                            <div class="stat-mini-value">12</div>
                            <div class="stat-mini-label">Seguidores</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn-secondary" onclick="closeUserDetailModal()">Cerrar</button>
                <button class="btn-primary" onclick="editUserFromDetail()">Editar usuario</button>
                <button class="btn-danger">Suspender</button>
            </div>
        </div>
    </div>

    <script>
        // Modal functions
        function openUserModal() {
            document.getElementById('userModal').classList.add('show');
            document.getElementById('userModalTitle').textContent = 'Nuevo Usuario';
        }

        function closeUserModal() {
            document.getElementById('userModal').classList.remove('show');
        }

        function viewUserDetails(userName) {
            document.getElementById('userDetailModal').classList.add('show');
            document.getElementById('detailUserName').textContent = userName;
        }

        function closeUserDetailModal() {
            document.getElementById('userDetailModal').classList.remove('show');
        }

        function editUser(userName) {
            document.getElementById('userModal').classList.add('show');
            document.getElementById('userModalTitle').textContent = 'Editar Usuario - ' + userName;
        }

        function editUserFromDetail() {
            closeUserDetailModal();
            editUser('María Pérez');
        }

        function suspendUser(userName) {
            if(confirm(`¿Estás seguro de suspender a ${userName}?`)) {
                alert(`Usuario ${userName} suspendido (demo)`);
            }
        }

        function deleteUser(userName) {
            if(confirm(`¿Estás seguro de eliminar a ${userName}? Esta acción no se puede deshacer.`)) {
                alert(`Usuario ${userName} eliminado (demo)`);
            }
        }

        // Close modals on outside click
        document.getElementById('userModal').addEventListener('click', function(e) {
            if(e.target === this) {
                closeUserModal();
            }
        });

        document.getElementById('userDetailModal').addEventListener('click', function(e) {
            if(e.target === this) {
                closeUserDetailModal();
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
            alert('Exportando lista de usuarios (demo)');
        });

        // Navigation
        document.querySelectorAll('.admin-nav-item').forEach(item => {
            item.addEventListener('click', (e) => {
                e.preventDefault();
                document.querySelectorAll('.admin-nav-item').forEach(nav => nav.classList.remove('active'));
                item.classList.add('active');
            });
        });

        // Action buttons in table
        document.querySelectorAll('.action-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                // Las acciones específicas ya tienen sus funciones
            });
        });
    </script>
</body>
</html>