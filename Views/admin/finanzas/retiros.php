<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NexusBuy - Sistema de Retiros</title>
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
        .withdrawal-stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .withdrawal-stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 16px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.02);
            border-left: 4px solid;
        }

        .withdrawal-stat-card.blue {
            border-left-color: #4361ee;
        }

        .withdrawal-stat-card.green {
            border-left-color: #06d6a0;
        }

        .withdrawal-stat-card.yellow {
            border-left-color: #ffb703;
        }

        .withdrawal-stat-card.purple {
            border-left-color: #7209b7;
        }

        .stat-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.5rem;
        }

        .stat-header h3 {
            font-size: 0.875rem;
            color: #6c757d;
            font-weight: 500;
        }

        .stat-icon-small {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .stat-icon-small.blue {
            background: #e1e8ff;
            color: #4361ee;
        }

        .stat-icon-small.green {
            background: #d1f7ea;
            color: #06d6a0;
        }

        .stat-icon-small.yellow {
            background: #fff3d1;
            color: #ffb703;
        }

        .stat-icon-small.purple {
            background: #ead1ff;
            color: #7209b7;
        }

        .stat-value-large {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.25rem;
        }

        .stat-label {
            color: #6c757d;
            font-size: 0.875rem;
        }

        /* Filter Bar */
        .filter-bar {
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.02);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .filter-tabs {
            display: flex;
            gap: 0.5rem;
            background: #f8f9fa;
            padding: 0.25rem;
            border-radius: 8px;
        }

        .filter-tab {
            padding: 0.5rem 1rem;
            border-radius: 6px;
            font-size: 0.875rem;
            cursor: pointer;
            transition: all 0.2s;
        }

        .filter-tab:hover {
            background: rgba(255,255,255,0.5);
        }

        .filter-tab.active {
            background: white;
            color: #4361ee;
            font-weight: 500;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        .filter-actions {
            display: flex;
            gap: 0.5rem;
            align-items: center;
        }

        .search-box {
            display: flex;
            align-items: center;
            background: #f8f9fa;
            border-radius: 8px;
            padding: 0.5rem 1rem;
            width: 250px;
        }

        .search-box i {
            color: #6c757d;
            margin-right: 0.5rem;
        }

        .search-box input {
            border: none;
            background: transparent;
            outline: none;
            width: 100%;
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

        /* Withdrawals Table */
        .withdrawals-table-container {
            background: white;
            border-radius: 16px;
            border: 1px solid #e9ecef;
            overflow: auto;
            margin-bottom: 1.5rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.02);
        }

        .withdrawals-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 1200px;
        }

        .withdrawals-table th {
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

        .withdrawals-table td {
            padding: 1rem;
            border-bottom: 1px solid #e9ecef;
            font-size: 0.875rem;
        }

        .store-cell {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .store-avatar {
            width: 40px;
            height: 40px;
            background: #e1e8ff;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #4361ee;
            font-weight: 600;
        }

        .store-info h4 {
            font-weight: 600;
            font-size: 0.875rem;
            margin-bottom: 0.25rem;
        }

        .store-info span {
            font-size: 0.7rem;
            color: #6c757d;
        }

        .amount-info {
            font-weight: 600;
            color: #4361ee;
        }

        .amount-info small {
            font-weight: normal;
            color: #6c757d;
            font-size: 0.7rem;
            margin-left: 0.25rem;
        }

        .commission-info {
            font-size: 0.7rem;
            color: #6c757d;
        }

        .method-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            padding: 0.25rem 0.5rem;
            background: #f1f3f5;
            border-radius: 4px;
            font-size: 0.7rem;
        }

        .method-badge i {
            color: #4361ee;
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

        .status-badge.pending {
            background: #fff3d1;
            color: #ffb703;
        }

        .status-badge.processing {
            background: #e1e8ff;
            color: #4361ee;
        }

        .status-badge.completed {
            background: #d1f7ea;
            color: #06d6a0;
        }

        .status-badge.rejected {
            background: #ffe5e5;
            color: #e63946;
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

        /* Withdrawal Detail */
        .withdrawal-info {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.75rem;
            padding-bottom: 0.75rem;
            border-bottom: 1px solid #e9ecef;
        }

        .info-row:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }

        .info-label {
            color: #6c757d;
            font-size: 0.875rem;
        }

        .info-value {
            font-weight: 600;
        }

        .store-summary {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 8px;
            margin-bottom: 1.5rem;
        }

        .store-summary-avatar {
            width: 48px;
            height: 48px;
            background: #4361ee;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 1.25rem;
        }

        .store-summary-info h4 {
            font-weight: 600;
            margin-bottom: 0.25rem;
        }

        .store-summary-info p {
            font-size: 0.875rem;
            color: #6c757d;
        }

        .payment-details {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 8px;
            margin: 1.5rem 0;
        }

        .payment-details h4 {
            margin-bottom: 1rem;
        }

        .rejection-input {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            margin: 1rem 0;
            resize: vertical;
        }

        .btn-success {
            background: #06d6a0;
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-size: 0.875rem;
            cursor: pointer;
        }

        .btn-danger {
            background: #e63946;
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-size: 0.875rem;
            cursor: pointer;
        }

        .btn-warning {
            background: #ffb703;
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-size: 0.875rem;
            cursor: pointer;
        }

        .btn-outline {
            background: white;
            border: 1px solid #4361ee;
            color: #4361ee;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-size: 0.875rem;
            cursor: pointer;
        }

        /* Responsive */
        @media (max-width: 1200px) {
            .withdrawal-stats-grid {
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
            
            .withdrawal-stats-grid {
                grid-template-columns: 1fr;
            }
            
            .filter-bar {
                flex-direction: column;
                align-items: stretch;
            }
            
            .filter-tabs {
                width: 100%;
                justify-content: center;
            }
            
            .search-box {
                width: 100%;
            }
            
            .filter-actions {
                flex-direction: column;
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
                <a href="#" class="admin-nav-item active">
                    <i class="fas fa-hand-holding-usd"></i>
                    Retiros
                    <span class="badge-admin">3</span>
                </a>
                <a href="#" class="admin-nav-item">
                    <i class="fas fa-dollar-sign"></i>
                    Comisiones
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
                    <h1>Sistema de Retiros</h1>
                    <p>Gestiona los pagos a vendedores</p>
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
            <div class="withdrawal-stats-grid">
                <div class="withdrawal-stat-card yellow">
                    <div class="stat-header">
                        <h3>Pendientes</h3>
                        <div class="stat-icon-small yellow">
                            <i class="fas fa-clock"></i>
                        </div>
                    </div>
                    <div class="stat-value-large">12</div>
                    <div class="stat-label">solicitudes por revisar</div>
                </div>

                <div class="withdrawal-stat-card blue">
                    <div class="stat-header">
                        <h3>En proceso</h3>
                        <div class="stat-icon-small blue">
                            <i class="fas fa-sync-alt"></i>
                        </div>
                    </div>
                    <div class="stat-value-large">8</div>
                    <div class="stat-label">siendo procesados</div>
                </div>

                <div class="withdrawal-stat-card green">
                    <div class="stat-header">
                        <h3>Completados</h3>
                        <div class="stat-icon-small green">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                    <div class="stat-value-large">156</div>
                    <div class="stat-label">este mes</div>
                </div>

                <div class="withdrawal-stat-card purple">
                    <div class="stat-header">
                        <h3>Total pagado</h3>
                        <div class="stat-icon-small purple">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                    </div>
                    <div class="stat-value-large">$45,670</div>
                    <div class="stat-label">CUP este mes</div>
                </div>
            </div>

            <!-- Filter Bar -->
            <div class="filter-bar">
                <div class="filter-tabs">
                    <span class="filter-tab active">Todas</span>
                    <span class="filter-tab">Pendientes</span>
                    <span class="filter-tab">En proceso</span>
                    <span class="filter-tab">Completadas</span>
                    <span class="filter-tab">Rechazadas</span>
                </div>
                <div class="filter-actions">
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" placeholder="Buscar por tienda...">
                    </div>
                    <button class="btn-secondary">
                        <i class="fas fa-calendar"></i>
                        Filtros
                    </button>
                    <button class="btn-secondary">
                        <i class="fas fa-download"></i>
                        Exportar
                    </button>
                </div>
            </div>

            <!-- Withdrawals Table -->
            <div class="withdrawals-table-container">
                <table class="withdrawals-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tienda</th>
                            <th>Fecha solicitud</th>
                            <th>Monto</th>
                            <th>Comisión</th>
                            <th>Neto</th>
                            <th>Método</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>#R024</td>
                            <td>
                                <div class="store-cell">
                                    <div class="store-avatar">NF</div>
                                    <div class="store-info">
                                        <h4>Nexus Fashion</h4>
                                        <span>María Pérez</span>
                                    </div>
                                </div>
                            </td>
                            <td>15/03/2025</td>
                            <td>
                                <div class="amount-info">
                                    $5,000 CUP
                                    <br>
                                    <small>$143 USD</small>
                                </div>
                            </td>
                            <td>
                                <span class="commission-info">$125 CUP (2.5%)</span>
                            </td>
                            <td>
                                <div class="amount-info">
                                    $4,875 CUP
                                </div>
                            </td>
                            <td>
                                <span class="method-badge">
                                    <i class="fas fa-mobile-alt"></i>
                                    Transfermóvil
                                </span>
                            </td>
                            <td>
                                <span class="status-badge pending">
                                    <i class="fas fa-clock"></i>
                                    Pendiente
                                </span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <button class="action-btn" onclick="viewWithdrawal('R024')"><i class="fas fa-eye"></i></button>
                                    <button class="action-btn approve" onclick="approveWithdrawal('R024')"><i class="fas fa-check"></i></button>
                                    <button class="action-btn reject" onclick="rejectWithdrawal('R024')"><i class="fas fa-times"></i></button>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>#R023</td>
                            <td>
                                <div class="store-cell">
                                    <div class="store-avatar">TS</div>
                                    <div class="store-info">
                                        <h4>Tech Store</h4>
                                        <span>Juan López</span>
                                    </div>
                                </div>
                            </td>
                            <td>14/03/2025</td>
                            <td>
                                <div class="amount-info">
                                    $3,200 CUP
                                    <br>
                                    <small>$91 USD</small>
                                </div>
                            </td>
                            <td>
                                <span class="commission-info">$80 CUP (2.5%)</span>
                            </td>
                            <td>
                                <div class="amount-info">
                                    $3,120 CUP
                                </div>
                            </td>
                            <td>
                                <span class="method-badge">
                                    <i class="fas fa-university"></i>
                                    Transferencia
                                </span>
                            </td>
                            <td>
                                <span class="status-badge processing">
                                    <i class="fas fa-sync-alt"></i>
                                    En proceso
                                </span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <button class="action-btn"><i class="fas fa-eye"></i></button>
                                    <button class="action-btn"><i class="fas fa-edit"></i></button>
                                    <button class="action-btn"><i class="fas fa-print"></i></button>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>#R022</td>
                            <td>
                                <div class="store-cell">
                                    <div class="store-avatar">DC</div>
                                    <div class="store-info">
                                        <h4>Deportes Cuba</h4>
                                        <span>Carlos Ruiz</span>
                                    </div>
                                </div>
                            </td>
                            <td>13/03/2025</td>
                            <td>
                                <div class="amount-info">
                                    $8,500 CUP
                                    <br>
                                    <small>$243 USD</small>
                                </div>
                            </td>
                            <td>
                                <span class="commission-info">$212 CUP (2.5%)</span>
                            </td>
                            <td>
                                <div class="amount-info">
                                    $8,288 CUP
                                </div>
                            </td>
                            <td>
                                <span class="method-badge">
                                    <i class="fas fa-mobile-alt"></i>
                                    EnZona
                                </span>
                            </td>
                            <td>
                                <span class="status-badge completed">
                                    <i class="fas fa-check-circle"></i>
                                    Completado
                                </span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <button class="action-btn"><i class="fas fa-eye"></i></button>
                                    <button class="action-btn"><i class="fas fa-print"></i></button>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>#R021</td>
                            <td>
                                <div class="store-cell">
                                    <div class="store-avatar">FS</div>
                                    <div class="store-info">
                                        <h4>Fashion Store</h4>
                                        <span>Ana García</span>
                                    </div>
                                </div>
                            </td>
                            <td>12/03/2025</td>
                            <td>
                                <div class="amount-info">
                                    $2,100 CUP
                                    <br>
                                    <small>$60 USD</small>
                                </div>
                            </td>
                            <td>
                                <span class="commission-info">$52 CUP (2.5%)</span>
                            </td>
                            <td>
                                <div class="amount-info">
                                    $2,048 CUP
                                </div>
                            </td>
                            <td>
                                <span class="method-badge">
                                    <i class="fas fa-money-bill"></i>
                                    Efectivo
                                </span>
                            </td>
                            <td>
                                <span class="status-badge completed">
                                    <i class="fas fa-check-circle"></i>
                                    Completado
                                </span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <button class="action-btn"><i class="fas fa-eye"></i></button>
                                    <button class="action-btn"><i class="fas fa-print"></i></button>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>#R020</td>
                            <td>
                                <div class="store-cell">
                                    <div class="store-avatar">MS</div>
                                    <div class="store-info">
                                        <h4>Móvil Store</h4>
                                        <span>Pedro Méndez</span>
                                    </div>
                                </div>
                            </td>
                            <td>11/03/2025</td>
                            <td>
                                <div class="amount-info">
                                    $1,500 CUP
                                    <br>
                                    <small>$43 USD</small>
                                </div>
                            </td>
                            <td>
                                <span class="commission-info">$37 CUP (2.5%)</span>
                            </td>
                            <td>
                                <div class="amount-info">
                                    $1,463 CUP
                                </div>
                            </td>
                            <td>
                                <span class="method-badge">
                                    <i class="fas fa-credit-card"></i>
                                    Tarjeta
                                </span>
                            </td>
                            <td>
                                <span class="status-badge rejected">
                                    <i class="fas fa-times-circle"></i>
                                    Rechazado
                                </span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <button class="action-btn"><i class="fas fa-eye"></i></button>
                                    <button class="action-btn"><i class="fas fa-redo-alt"></i></button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="pagination">
                <div class="pagination-info">
                    Mostrando 5 de 48 solicitudes
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

    <!-- Modal Ver Detalle de Retiro -->
    <div class="modal-overlay" id="withdrawalModal">
        <div class="modal">
            <div class="modal-header">
                <h2>Detalle del Retiro #R024</h2>
                <button class="modal-close" onclick="closeWithdrawalModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <!-- Estado actual -->
                <div class="withdrawal-info">
                    <div class="info-row">
                        <span class="info-label">Estado actual:</span>
                        <span class="status-badge pending">Pendiente de revisión</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Fecha solicitud:</span>
                        <span>15/03/2025 - 10:30 AM</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Tiempo en espera:</span>
                        <span>2 horas</span>
                    </div>
                </div>

                <!-- Información de la tienda -->
                <div class="store-summary">
                    <div class="store-summary-avatar">NF</div>
                    <div class="store-summary-info">
                        <h4>Nexus Fashion</h4>
                        <p>Propietario: María Pérez · maria@nexusfashion.com · +53 5 1234567</p>
                    </div>
                </div>

                <!-- Detalles financieros -->
                <div class="withdrawal-info">
                    <div class="info-row">
                        <span class="info-label">Monto solicitado:</span>
                        <span class="amount-info">$5,000 CUP</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Comisión (2.5%):</span>
                        <span>$125 CUP</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Monto a transferir:</span>
                        <span class="amount-info" style="color: #06d6a0;">$4,875 CUP</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Saldo disponible después:</span>
                        <span>$7,450 CUP</span>
                    </div>
                </div>

                <!-- Detalles de pago -->
                <div class="payment-details">
                    <h4>Detalles del pago</h4>
                    <div class="info-row">
                        <span class="info-label">Método:</span>
                        <span class="method-badge">
                            <i class="fas fa-mobile-alt"></i>
                            Transfermóvil
                        </span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Número teléfono:</span>
                        <span>+53 5 1234567</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Titular:</span>
                        <span>María Pérez González</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Banco:</span>
                        <span>Banco Metropolitano</span>
                    </div>
                </div>

                <!-- Historial de la tienda -->
                <div class="withdrawal-info">
                    <h4 style="margin-bottom: 0.5rem;">Historial de retiros</h4>
                    <div class="info-row">
                        <span class="info-label">Retiros previos:</span>
                        <span>12 retiros completados</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Monto total retirado:</span>
                        <span>$45,670 CUP</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Último retiro:</span>
                        <span>10/03/2025 - $5,000 CUP</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Confiabilidad:</span>
                        <span style="color: #06d6a0;">Alta (98% retiros sin problemas)</span>
                    </div>
                </div>

                <!-- Notas internas -->
                <div style="margin-top: 1rem;">
                    <label class="info-label">Notas internas</label>
                    <textarea class="rejection-input" rows="3" placeholder="Añadir notas sobre este retiro...">Cliente frecuente, siempre retira montos similares</textarea>
                </div>

                <!-- Campo para rechazo (visible solo si se rechaza) -->
                <div id="rejectionReason" style="display: none;">
                    <label class="info-label">Razón del rechazo</label>
                    <textarea class="rejection-input" rows="3" placeholder="Indica el motivo del rechazo..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn-secondary" onclick="closeWithdrawalModal()">Cerrar</button>
                <button class="btn-warning" onclick="markAsProcessing()">
                    <i class="fas fa-sync-alt"></i> Marcar en proceso
                </button>
                <button class="btn-success" onclick="processWithdrawal()">
                    <i class="fas fa-check"></i> Confirmar pago
                </button>
                <button class="btn-danger" onclick="showRejection()">
                    <i class="fas fa-times"></i> Rechazar
                </button>
            </div>
        </div>
    </div>

    <script>
        // Modal functions
        function viewWithdrawal(id) {
            document.getElementById('withdrawalModal').classList.add('show');
            document.querySelector('.modal-header h2').textContent = 'Detalle del Retiro ' + id;
        }

        function closeWithdrawalModal() {
            document.getElementById('withdrawalModal').classList.remove('show');
            document.getElementById('rejectionReason').style.display = 'none';
        }

        function approveWithdrawal(id) {
            if(confirm(`¿Aprobar el retiro ${id}?`)) {
                alert(`Retiro ${id} aprobado y marcado para procesar (demo)`);
            }
        }

        function rejectWithdrawal(id) {
            const reason = prompt('Indica la razón del rechazo:');
            if(reason) {
                alert(`Retiro ${id} rechazado. Motivo: ${reason} (demo)`);
            }
        }

        function processWithdrawal() {
            if(confirm('¿Confirmar que el pago ha sido realizado?')) {
                alert('Pago confirmado. El retiro se marcará como completado (demo)');
                closeWithdrawalModal();
            }
        }

        function markAsProcessing() {
            alert('Retiro marcado como "en proceso" (demo)');
        }

        function showRejection() {
            document.getElementById('rejectionReason').style.display = 'block';
        }

        // Close modal on outside click
        document.getElementById('withdrawalModal').addEventListener('click', function(e) {
            if(e.target === this) {
                closeWithdrawalModal();
            }
        });

        // Filter tabs
        document.querySelectorAll('.filter-tab').forEach(tab => {
            tab.addEventListener('click', function() {
                document.querySelectorAll('.filter-tab').forEach(t => t.classList.remove('active'));
                this.classList.add('active');
                alert(`Filtrando por: ${this.textContent} (demo)`);
            });
        });

        // Search
        document.querySelector('.search-box input').addEventListener('keyup', function(e) {
            if(e.key === 'Enter') {
                alert('Buscando: ' + this.value + ' (demo)');
            }
        });

        // Filter button
        document.querySelector('.btn-secondary').addEventListener('click', function() {
            if(this.querySelector('.fa-calendar')) {
                alert('Abriendo filtros avanzados (demo)');
            } else if(this.querySelector('.fa-download')) {
                alert('Exportando reporte de retiros (demo)');
            }
        });

        // Action buttons in table
        document.querySelectorAll('.action-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                if(btn.classList.contains('approve')) {
                    // Ya tiene su función
                } else if(btn.classList.contains('reject')) {
                    // Ya tiene su función
                } else if(btn.querySelector('.fa-edit')) {
                    alert('Editando retiro (demo)');
                } else if(btn.querySelector('.fa-print')) {
                    alert('Imprimiendo comprobante (demo)');
                } else if(btn.querySelector('.fa-redo-alt')) {
                    alert('Reintentando retiro (demo)');
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