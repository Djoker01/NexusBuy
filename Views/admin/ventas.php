<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NexusBuy - Administración de Ventas</title>
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

        /* Date Range */
        .date-range-card {
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

        .date-range-selector {
            display: flex;
            gap: 0.5rem;
            align-items: center;
            flex-wrap: wrap;
        }

        .date-preset {
            display: flex;
            gap: 0.5rem;
        }

        .preset-btn {
            background: none;
            border: 1px solid #e9ecef;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-size: 0.875rem;
            color: #6c757d;
            cursor: pointer;
            transition: all 0.2s;
        }

        .preset-btn:hover {
            background: #f8f9fa;
            border-color: #4361ee;
        }

        .preset-btn.active {
            background: #4361ee;
            color: white;
            border-color: #4361ee;
        }

        .date-picker {
            display: flex;
            gap: 0.5rem;
            align-items: center;
            background: #f8f9fa;
            padding: 0.5rem 1rem;
            border-radius: 8px;
        }

        .date-picker input {
            border: none;
            background: transparent;
            padding: 0.25rem;
            width: 120px;
            outline: none;
        }

        .export-btn {
            background: white;
            border: 1px solid #e9ecef;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            cursor: pointer;
            transition: all 0.2s;
        }

        .export-btn:hover {
            background: #f8f9fa;
            border-color: #4361ee;
        }

        /* Stats Grid */
        .sales-stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .sales-stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 16px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.02);
            border-left: 4px solid;
        }

        .sales-stat-card.primary {
            border-left-color: #4361ee;
        }

        .sales-stat-card.success {
            border-left-color: #06d6a0;
        }

        .sales-stat-card.warning {
            border-left-color: #ffb703;
        }

        .sales-stat-card.info {
            border-left-color: #4cc9f0;
        }

        .stat-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .stat-header h3 {
            font-size: 0.875rem;
            color: #6c757d;
            font-weight: 500;
        }

        .stat-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
        }

        .stat-icon.primary {
            background: #e1e8ff;
            color: #4361ee;
        }

        .stat-icon.success {
            background: #d1f7ea;
            color: #06d6a0;
        }

        .stat-icon.warning {
            background: #fff3d1;
            color: #ffb703;
        }

        .stat-icon.info {
            background: #d1eaff;
            color: #4cc9f0;
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.25rem;
        }

        .stat-trend {
            font-size: 0.75rem;
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }

        .trend-up {
            color: #06d6a0;
        }

        .trend-down {
            color: #e63946;
        }

        .stat-sub {
            color: #6c757d;
            font-size: 0.875rem;
        }

        /* Charts Row */
        .sales-charts-row {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .chart-card {
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.02);
        }

        .chart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .chart-header h3 {
            font-size: 1rem;
            font-weight: 600;
        }

        .chart-legend {
            display: flex;
            gap: 1rem;
        }

        .legend-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.75rem;
        }

        .legend-color {
            width: 12px;
            height: 12px;
            border-radius: 4px;
        }

        .chart-container {
            height: 300px;
            position: relative;
        }

        /* Top Products */
        .top-products {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .top-product-card {
            background: white;
            border-radius: 16px;
            padding: 1rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.02);
            border: 1px solid #e9ecef;
        }

        .product-rank {
            width: 36px;
            height: 36px;
            background: #f1f3f5;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            color: #4361ee;
        }

        .product-info {
            flex: 1;
        }

        .product-info h4 {
            font-size: 0.875rem;
            font-weight: 600;
            margin-bottom: 0.25rem;
        }

        .product-info p {
            font-size: 0.75rem;
            color: #6c757d;
        }

        .product-stats {
            text-align: right;
        }

        .product-stats .amount {
            font-weight: 700;
            color: #4361ee;
        }

        .product-stats .units {
            font-size: 0.7rem;
            color: #6c757d;
        }

        /* Sales by Category */
        .category-section {
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.02);
        }

        .category-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.5rem;
        }

        .category-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 0.5rem;
            border-radius: 8px;
            transition: background 0.2s;
        }

        .category-item:hover {
            background: #f8f9fa;
        }

        .category-icon {
            width: 48px;
            height: 48px;
            background: #f1f3f5;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .category-details {
            flex: 1;
        }

        .category-name {
            font-weight: 600;
            margin-bottom: 0.25rem;
        }

        .progress-bar {
            height: 8px;
            background: #e9ecef;
            border-radius: 4px;
            overflow: hidden;
            margin: 0.5rem 0;
        }

        .progress-fill {
            height: 100%;
            background: #4361ee;
            border-radius: 4px;
        }

        .category-stats {
            display: flex;
            justify-content: space-between;
            font-size: 0.75rem;
            color: #6c757d;
        }

        /* Transactions Table */
        .transactions-section {
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.02);
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .section-header h2 {
            font-size: 1.1rem;
            font-weight: 600;
        }

        .table-filters {
            display: flex;
            gap: 0.5rem;
        }

        .filter-select {
            padding: 0.5rem 2rem 0.5rem 1rem;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            background: white;
            font-size: 0.875rem;
            cursor: pointer;
        }

        .sales-table {
            width: 100%;
            border-collapse: collapse;
        }

        .sales-table th {
            text-align: left;
            padding: 1rem;
            background: #f8f9fa;
            color: #6c757d;
            font-weight: 500;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .sales-table td {
            padding: 1rem;
            border-bottom: 1px solid #e9ecef;
            font-size: 0.875rem;
        }

        .store-cell {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .store-icon {
            width: 36px;
            height: 36px;
            background: #e1e8ff;
            color: #4361ee;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .payment-method {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .payment-method i {
            width: 20px;
            color: #6c757d;
        }

        .commission-badge {
            background: #fff3d1;
            color: #ffb703;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.7rem;
            font-weight: 500;
        }

        .status-badge {
            padding: 0.25rem 0.5rem;
            border-radius: 999px;
            font-size: 0.7rem;
            font-weight: 500;
        }

        .status-badge.completed {
            background: #d1f7ea;
            color: #06d6a0;
        }

        .status-badge.pending {
            background: #fff3d1;
            color: #ffb703;
        }

        .status-badge.cancelled {
            background: #ffe5e5;
            color: #e63946;
        }

        .pagination {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 1.5rem;
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

        /* Responsive */
        @media (max-width: 1200px) {
            .sales-stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .sales-charts-row {
                grid-template-columns: 1fr;
            }
            
            .top-products {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .category-grid {
                grid-template-columns: 1fr;
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
            
            .sales-stats-grid {
                grid-template-columns: 1fr;
            }
            
            .top-products {
                grid-template-columns: 1fr;
            }
            
            .date-range-selector {
                flex-direction: column;
                width: 100%;
            }
            
            .date-preset {
                width: 100%;
                justify-content: space-between;
            }
            
            .sales-table {
                min-width: 1000px;
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
                <a href="#" class="admin-nav-item active">
                    <i class="fas fa-shopping-cart"></i>
                    Ventas
                    <span class="badge-admin">12</span>
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
                    <h1>Ventas</h1>
                    <p>Análisis detallado de todas las transacciones</p>
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

            <!-- Date Range Selector -->
            <div class="date-range-card">
                <div class="date-range-selector">
                    <div class="date-preset">
                        <button class="preset-btn active">Hoy</button>
                        <button class="preset-btn">Ayer</button>
                        <button class="preset-btn">Esta semana</button>
                        <button class="preset-btn">Este mes</button>
                        <button class="preset-btn">Este año</button>
                    </div>
                    <div class="date-picker">
                        <i class="fas fa-calendar"></i>
                        <input type="date" value="2025-03-01">
                        <span>-</span>
                        <input type="date" value="2025-03-15">
                    </div>
                </div>
                <div class="export-btn">
                    <i class="fas fa-download"></i>
                    Exportar reporte
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="sales-stats-grid">
                <div class="sales-stat-card primary">
                    <div class="stat-header">
                        <h3>Ventas totales</h3>
                        <div class="stat-icon primary">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                    </div>
                    <div class="stat-value">$89,234 CUP</div>
                    <div class="stat-trend trend-up">
                        <i class="fas fa-arrow-up"></i> +12.5% vs período anterior
                    </div>
                    <div class="stat-sub">$2,550 USD</div>
                </div>

                <div class="sales-stat-card success">
                    <div class="stat-header">
                        <h3>Número de pedidos</h3>
                        <div class="stat-icon success">
                            <i class="fas fa-shopping-bag"></i>
                        </div>
                    </div>
                    <div class="stat-value">1,234</div>
                    <div class="stat-trend trend-up">
                        <i class="fas fa-arrow-up"></i> +8.3% vs período anterior
                    </div>
                    <div class="stat-sub">Promedio 82 pedidos/día</div>
                </div>

                <div class="sales-stat-card warning">
                    <div class="stat-header">
                        <h3>Ticket promedio</h3>
                        <div class="stat-icon warning">
                            <i class="fas fa-receipt"></i>
                        </div>
                    </div>
                    <div class="stat-value">$72.30 CUP</div>
                    <div class="stat-trend trend-up">
                        <i class="fas fa-arrow-up"></i> +5.2% vs período anterior
                    </div>
                    <div class="stat-sub">$2.07 USD</div>
                </div>

                <div class="sales-stat-card info">
                    <div class="stat-header">
                        <h3>Comisiones totales</h3>
                        <div class="stat-icon info">
                            <i class="fas fa-percent"></i>
                        </div>
                    </div>
                    <div class="stat-value">$8,923 CUP</div>
                    <div class="stat-trend trend-up">
                        <i class="fas fa-arrow-up"></i> +12.5% vs período anterior
                    </div>
                    <div class="stat-sub">10% promedio</div>
                </div>
            </div>

            <!-- Charts Row -->
            <div class="sales-charts-row">
                <!-- Ventas por día -->
                <div class="chart-card">
                    <div class="chart-header">
                        <h3>Ventas por día</h3>
                        <div class="chart-legend">
                            <div class="legend-item">
                                <span class="legend-color" style="background: #4361ee;"></span>
                                <span>CUP</span>
                            </div>
                            <div class="legend-item">
                                <span class="legend-color" style="background: #06d6a0;"></span>
                                <span>USD</span>
                            </div>
                        </div>
                    </div>
                    <div class="chart-container">
                        <canvas id="dailySalesChart"></canvas>
                    </div>
                </div>

                <!-- Ventas por hora -->
                <div class="chart-card">
                    <div class="chart-header">
                        <h3>Ventas por hora</h3>
                    </div>
                    <div class="chart-container">
                        <canvas id="hourlySalesChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Top Products -->
            <div class="top-products">
                <div class="top-product-card">
                    <div class="product-rank">#1</div>
                    <div class="product-info">
                        <h4>Camiseta Oversize Negra</h4>
                        <p>Nexus Fashion</p>
                    </div>
                    <div class="product-stats">
                        <div class="amount">$12,450</div>
                        <div class="units">45 unidades</div>
                    </div>
                </div>
                <div class="top-product-card">
                    <div class="product-rank">#2</div>
                    <div class="product-info">
                        <h4>Zapatillas Running</h4>
                        <p>Deportes Cuba</p>
                    </div>
                    <div class="product-stats">
                        <div class="amount">$8,190</div>
                        <div class="units">21 unidades</div>
                    </div>
                </div>
                <div class="top-product-card">
                    <div class="product-rank">#3</div>
                    <div class="product-info">
                        <h4>Jeans Skinny Azul</h4>
                        <p>Fashion Store</p>
                    </div>
                    <div class="product-stats">
                        <div class="amount">$7,360</div>
                        <div class="units">32 unidades</div>
                    </div>
                </div>
            </div>

            <!-- Sales by Category -->
            <div class="category-section">
                <div class="section-header">
                    <h2>Ventas por categoría</h2>
                    <select class="filter-select">
                        <option>Este mes</option>
                        <option>Este año</option>
                        <option>Todo el tiempo</option>
                    </select>
                </div>
                <div class="category-grid">
                    <div class="category-item">
                        <div class="category-icon">
                            <i class="fas fa-tshirt"></i>
                        </div>
                        <div class="category-details">
                            <div class="category-name">Ropa</div>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: 45%;"></div>
                            </div>
                            <div class="category-stats">
                                <span>$45,230 CUP</span>
                                <span>45%</span>
                            </div>
                        </div>
                    </div>
                    <div class="category-item">
                        <div class="category-icon">
                            <i class="fas fa-laptop"></i>
                        </div>
                        <div class="category-details">
                            <div class="category-name">Electrónica</div>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: 30%;"></div>
                            </div>
                            <div class="category-stats">
                                <span>$30,150 CUP</span>
                                <span>30%</span>
                            </div>
                        </div>
                    </div>
                    <div class="category-item">
                        <div class="category-icon">
                            <i class="fas fa-shoe-prints"></i>
                        </div>
                        <div class="category-details">
                            <div class="category-name">Calzado</div>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: 15%;"></div>
                            </div>
                            <div class="category-stats">
                                <span>$15,080 CUP</span>
                                <span>15%</span>
                            </div>
                        </div>
                    </div>
                    <div class="category-item">
                        <div class="category-icon">
                            <i class="fas fa-mobile-alt"></i>
                        </div>
                        <div class="category-details">
                            <div class="category-name">Accesorios</div>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: 10%;"></div>
                            </div>
                            <div class="category-stats">
                                <span>$10,054 CUP</span>
                                <span>10%</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Transactions Table -->
            <div class="transactions-section">
                <div class="section-header">
                    <h2>Últimas transacciones</h2>
                    <div class="table-filters">
                        <select class="filter-select">
                            <option>Todas las tiendas</option>
                            <option>Nexus Fashion</option>
                            <option>Deportes Cuba</option>
                            <option>Tech Store</option>
                        </select>
                        <select class="filter-select">
                            <option>Todos los estados</option>
                            <option>Completadas</option>
                            <option>Pendientes</option>
                            <option>Canceladas</option>
                        </select>
                    </div>
                </div>

                <div style="overflow-x: auto;">
                    <table class="sales-table">
                        <thead>
                            <tr>
                                <th>ID Pedido</th>
                                <th>Tienda</th>
                                <th>Fecha</th>
                                <th>Cliente</th>
                                <th>Productos</th>
                                <th>Total</th>
                                <th>Comisión</th>
                                <th>Método pago</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>#2345</td>
                                <td>
                                    <div class="store-cell">
                                        <div class="store-icon">
                                            <i class="fas fa-store"></i>
                                        </div>
                                        <span>Nexus Fashion</span>
                                    </div>
                                </td>
                                <td>15/03/2025</td>
                                <td>Juan Pérez</td>
                                <td>2</td>
                                <td>
                                    <strong>$4,100 CUP</strong>
                                    <br>
                                    <small>$117 USD</small>
                                </td>
                                <td>
                                    <span class="commission-badge">$410 CUP</span>
                                </td>
                                <td>
                                    <div class="payment-method">
                                        <i class="fas fa-mobile-alt"></i>
                                        Transfermóvil
                                    </div>
                                </td>
                                <td>
                                    <span class="status-badge completed">Completada</span>
                                </td>
                                <td>
                                    <button class="action-btn"><i class="fas fa-eye"></i></button>
                                </td>
                            </tr>
                            <tr>
                                <td>#2344</td>
                                <td>
                                    <div class="store-cell">
                                        <div class="store-icon">
                                            <i class="fas fa-store"></i>
                                        </div>
                                        <span>Deportes Cuba</span>
                                    </div>
                                </td>
                                <td>15/03/2025</td>
                                <td>María Gómez</td>
                                <td>3</td>
                                <td>
                                    <strong>$3,450 CUP</strong>
                                    <br>
                                    <small>$99 USD</small>
                                </td>
                                <td>
                                    <span class="commission-badge">$345 CUP</span>
                                </td>
                                <td>
                                    <div class="payment-method">
                                        <i class="fas fa-credit-card"></i>
                                        Tarjeta
                                    </div>
                                </td>
                                <td>
                                    <span class="status-badge completed">Completada</span>
                                </td>
                                <td>
                                    <button class="action-btn"><i class="fas fa-eye"></i></button>
                                </td>
                            </tr>
                            <tr>
                                <td>#2343</td>
                                <td>
                                    <div class="store-cell">
                                        <div class="store-icon">
                                            <i class="fas fa-store"></i>
                                        </div>
                                        <span>Tech Store</span>
                                    </div>
                                </td>
                                <td>14/03/2025</td>
                                <td>Carlos Ruiz</td>
                                <td>1</td>
                                <td>
                                    <strong>$5,200 CUP</strong>
                                    <br>
                                    <small>$149 USD</small>
                                </td>
                                <td>
                                    <span class="commission-badge">$520 CUP</span>
                                </td>
                                <td>
                                    <div class="payment-method">
                                        <i class="fas fa-university"></i>
                                        Transferencia
                                    </div>
                                </td>
                                <td>
                                    <span class="status-badge pending">Pendiente</span>
                                </td>
                                <td>
                                    <button class="action-btn"><i class="fas fa-eye"></i></button>
                                </td>
                            </tr>
                            <tr>
                                <td>#2342</td>
                                <td>
                                    <div class="store-cell">
                                        <div class="store-icon">
                                            <i class="fas fa-store"></i>
                                        </div>
                                        <span>Fashion Store</span>
                                    </div>
                                </td>
                                <td>14/03/2025</td>
                                <td>Ana López</td>
                                <td>2</td>
                                <td>
                                    <strong>$890 CUP</strong>
                                    <br>
                                    <small>$25 USD</small>
                                </td>
                                <td>
                                    <span class="commission-badge">$89 CUP</span>
                                </td>
                                <td>
                                    <div class="payment-method">
                                        <i class="fas fa-money-bill"></i>
                                        Efectivo
                                    </div>
                                </td>
                                <td>
                                    <span class="status-badge completed">Completada</span>
                                </td>
                                <td>
                                    <button class="action-btn"><i class="fas fa-eye"></i></button>
                                </td>
                            </tr>
                            <tr>
                                <td>#2341</td>
                                <td>
                                    <div class="store-cell">
                                        <div class="store-icon">
                                            <i class="fas fa-store"></i>
                                        </div>
                                        <span>Nexus Fashion</span>
                                    </div>
                                </td>
                                <td>13/03/2025</td>
                                <td>Pedro Méndez</td>
                                <td>1</td>
                                <td>
                                    <strong>$1,500 CUP</strong>
                                    <br>
                                    <small>$43 USD</small>
                                </td>
                                <td>
                                    <span class="commission-badge">$150 CUP</span>
                                </td>
                                <td>
                                    <div class="payment-method">
                                        <i class="fas fa-mobile-alt"></i>
                                        EnZona
                                    </div>
                                </td>
                                <td>
                                    <span class="status-badge cancelled">Cancelada</span>
                                </td>
                                <td>
                                    <button class="action-btn"><i class="fas fa-eye"></i></button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="pagination">
                    <div class="pagination-info">
                        Mostrando 5 de 124 transacciones
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
            </div>
        </main>
    </div>

    <script>
        // Gráfico de ventas diarias
        const ctxDaily = document.getElementById('dailySalesChart').getContext('2d');
        new Chart(ctxDaily, {
            type: 'bar',
            data: {
                labels: ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'],
                datasets: [
                    {
                        label: 'CUP',
                        data: [12500, 14200, 13800, 15400, 16800, 18900, 15200],
                        backgroundColor: '#4361ee',
                        borderRadius: 6
                    },
                    {
                        label: 'USD',
                        data: [357, 406, 394, 440, 480, 540, 434],
                        backgroundColor: '#06d6a0',
                        borderRadius: 6
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: '#e9ecef'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });

        // Gráfico de ventas por hora
        const ctxHourly = document.getElementById('hourlySalesChart').getContext('2d');
        new Chart(ctxHourly, {
            type: 'line',
            data: {
                labels: ['0-4', '4-8', '8-12', '12-16', '16-20', '20-24'],
                datasets: [{
                    label: 'Ventas',
                    data: [1200, 3400, 8900, 12300, 15600, 8900],
                    borderColor: '#4361ee',
                    backgroundColor: 'rgba(67, 97, 238, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: '#e9ecef'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });

        // Preset buttons
        document.querySelectorAll('.preset-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.preset-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                alert(`Filtrando por: ${this.textContent} (demo)`);
            });
        });

        // Export button
        document.querySelector('.export-btn').addEventListener('click', () => {
            alert('Generando reporte de ventas (demo)');
        });

        // View details buttons
        document.querySelectorAll('.action-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                alert('Ver detalles de la transacción (demo)');
            });
        });

        // Filter selects
        document.querySelectorAll('.filter-select').forEach(select => {
            select.addEventListener('change', function() {
                alert(`Filtro aplicado: ${this.value} (demo)`);
            });
        });
    </script>
</body>
</html>