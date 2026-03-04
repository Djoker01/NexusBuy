<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NexusBuy - Reportes y Estadísticas</title>
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

        /* Report Navigation */
        .report-nav {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 2rem;
            background: white;
            padding: 0.5rem;
            border-radius: 12px;
            border: 1px solid #e9ecef;
            flex-wrap: wrap;
        }

        .report-nav-item {
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-size: 0.875rem;
            font-weight: 500;
            color: #6c757d;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .report-nav-item:hover {
            background: #f1f3f5;
            color: #4361ee;
        }

        .report-nav-item.active {
            background: #4361ee;
            color: white;
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

        .export-actions {
            display: flex;
            gap: 0.5rem;
        }

        .btn-export {
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

        .btn-export:hover {
            background: #f8f9fa;
            border-color: #4361ee;
        }

        /* KPI Cards */
        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .kpi-card {
            background: white;
            padding: 1.5rem;
            border-radius: 16px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.02);
            border-left: 4px solid;
            transition: transform 0.2s;
        }

        .kpi-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(0,0,0,0.05);
        }

        .kpi-card.blue {
            border-left-color: #4361ee;
        }

        .kpi-card.green {
            border-left-color: #06d6a0;
        }

        .kpi-card.yellow {
            border-left-color: #ffb703;
        }

        .kpi-card.purple {
            border-left-color: #7209b7;
        }

        .kpi-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .kpi-header h3 {
            font-size: 0.875rem;
            color: #6c757d;
            font-weight: 500;
        }

        .kpi-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
        }

        .kpi-icon.blue {
            background: #e1e8ff;
            color: #4361ee;
        }

        .kpi-icon.green {
            background: #d1f7ea;
            color: #06d6a0;
        }

        .kpi-icon.yellow {
            background: #fff3d1;
            color: #ffb703;
        }

        .kpi-icon.purple {
            background: #ead1ff;
            color: #7209b7;
        }

        .kpi-value {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.25rem;
        }

        .kpi-trend {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.75rem;
        }

        .trend-up {
            color: #06d6a0;
        }

        .trend-down {
            color: #e63946;
        }

        .kpi-sub {
            color: #6c757d;
            font-size: 0.875rem;
        }

        /* Charts Row */
        .charts-row {
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

        /* Tables Grid */
        .tables-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .table-card {
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.02);
        }

        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .table-header h3 {
            font-size: 1rem;
            font-weight: 600;
        }

        .table-header a {
            color: #4361ee;
            text-decoration: none;
            font-size: 0.875rem;
        }

        .ranking-table {
            width: 100%;
            border-collapse: collapse;
        }

        .ranking-table td {
            padding: 0.75rem 0;
            border-bottom: 1px solid #e9ecef;
        }

        .ranking-table tr:last-child td {
            border-bottom: none;
        }

        .rank-cell {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .rank-number {
            width: 30px;
            height: 30px;
            background: #f1f3f5;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 0.875rem;
            color: #4361ee;
        }

        .rank-info h4 {
            font-weight: 600;
            font-size: 0.875rem;
            margin-bottom: 0.25rem;
        }

        .rank-info span {
            font-size: 0.7rem;
            color: #6c757d;
        }

        .rank-value {
            font-weight: 600;
            color: #4361ee;
        }

        .rank-value small {
            font-weight: normal;
            color: #6c757d;
            font-size: 0.7rem;
        }

        /* Progress Bars */
        .progress-list {
            margin-top: 1rem;
        }

        .progress-item {
            margin-bottom: 1rem;
        }

        .progress-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
            font-size: 0.875rem;
        }

        .progress-bar {
            height: 8px;
            background: #e9ecef;
            border-radius: 4px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            background: #4361ee;
            border-radius: 4px;
        }

        /* Heat Map */
        .heatmap-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 0.5rem;
            margin-top: 1rem;
        }

        .heatmap-cell {
            aspect-ratio: 1;
            background: #f1f3f5;
            border-radius: 4px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            font-size: 0.7rem;
        }

        .heatmap-cell.high {
            background: #4361ee;
            color: white;
        }

        .heatmap-cell.medium {
            background: #6c8aff;
            color: white;
        }

        .heatmap-cell.low {
            background: #a5b8ff;
        }

        .day-label {
            font-weight: 600;
            margin-bottom: 0.5rem;
            text-align: center;
        }

        /* Responsive */
        @media (max-width: 1200px) {
            .kpi-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .charts-row {
                grid-template-columns: 1fr;
            }
            
            .tables-grid {
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
            
            .kpi-grid {
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
            
            .heatmap-grid {
                font-size: 0.6rem;
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
                <a href="#" class="admin-nav-item">
                    <i class="fas fa-percent"></i>
                    Comisiones
                </a>
                <a href="#" class="admin-nav-item">
                    <i class="fas fa-hand-holding-usd"></i>
                    Retiros
                </a>
                <a href="#" class="admin-nav-item active">
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
                    <h1>Reportes y Estadísticas</h1>
                    <p>Analiza el rendimiento de tu plataforma</p>
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

            <!-- Report Navigation -->
            <div class="report-nav">
                <span class="report-nav-item active">
                    <i class="fas fa-chart-pie"></i>
                    Vista general
                </span>
                <span class="report-nav-item">
                    <i class="fas fa-shopping-cart"></i>
                    Ventas
                </span>
                <span class="report-nav-item">
                    <i class="fas fa-users"></i>
                    Usuarios
                </span>
                <span class="report-nav-item">
                    <i class="fas fa-store"></i>
                    Tiendas
                </span>
                <span class="report-nav-item">
                    <i class="fas fa-box"></i>
                    Productos
                </span>
                <span class="report-nav-item">
                    <i class="fas fa-percent"></i>
                    Comisiones
                </span>
                <span class="report-nav-item">
                    <i class="fas fa-download"></i>
                    Exportar datos
                </span>
            </div>

            <!-- Date Range -->
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
                <div class="export-actions">
                    <button class="btn-export">
                        <i class="fas fa-file-pdf"></i>
                        PDF
                    </button>
                    <button class="btn-export">
                        <i class="fas fa-file-excel"></i>
                        Excel
                    </button>
                    <button class="btn-export">
                        <i class="fas fa-print"></i>
                        Imprimir
                    </button>
                </div>
            </div>

            <!-- KPI Cards -->
            <div class="kpi-grid">
                <div class="kpi-card blue">
                    <div class="kpi-header">
                        <h3>Ventas totales</h3>
                        <div class="kpi-icon blue">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                    </div>
                    <div class="kpi-value">$89,234</div>
                    <div class="kpi-trend trend-up">
                        <i class="fas fa-arrow-up"></i> +12.5%
                        <span style="color: #6c757d;">vs período anterior</span>
                    </div>
                    <div class="kpi-sub">$2,550 USD</div>
                </div>

                <div class="kpi-card green">
                    <div class="kpi-header">
                        <h3>Pedidos</h3>
                        <div class="kpi-icon green">
                            <i class="fas fa-shopping-bag"></i>
                        </div>
                    </div>
                    <div class="kpi-value">1,234</div>
                    <div class="kpi-trend trend-up">
                        <i class="fas fa-arrow-up"></i> +8.3%
                        <span style="color: #6c757d;">vs período anterior</span>
                    </div>
                    <div class="kpi-sub">82 pedidos/día</div>
                </div>

                <div class="kpi-card yellow">
                    <div class="kpi-header">
                        <h3>Usuarios activos</h3>
                        <div class="kpi-icon yellow">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                    <div class="kpi-value">2,345</div>
                    <div class="kpi-trend trend-up">
                        <i class="fas fa-arrow-up"></i> +15.2%
                        <span style="color: #6c757d;">vs período anterior</span>
                    </div>
                    <div class="kpi-sub">45 nuevos hoy</div>
                </div>

                <div class="kpi-card purple">
                    <div class="kpi-header">
                        <h3>Comisiones</h3>
                        <div class="kpi-icon purple">
                            <i class="fas fa-percent"></i>
                        </div>
                    </div>
                    <div class="kpi-value">$8,923</div>
                    <div class="kpi-trend trend-up">
                        <i class="fas fa-arrow-up"></i> +12.5%
                        <span style="color: #6c757d;">vs período anterior</span>
                    </div>
                    <div class="kpi-sub">10% promedio</div>
                </div>
            </div>

            <!-- Charts Row -->
            <div class="charts-row">
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

                <!-- Distribución de ventas -->
                <div class="chart-card">
                    <div class="chart-header">
                        <h3>Ventas por categoría</h3>
                    </div>
                    <div class="chart-container">
                        <canvas id="categoryChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Tables Grid -->
            <div class="tables-grid">
                <!-- Top productos -->
                <div class="table-card">
                    <div class="table-header">
                        <h3>Top productos más vendidos</h3>
                        <a href="#">Ver todos <i class="fas fa-arrow-right"></i></a>
                    </div>
                    <table class="ranking-table">
                        <tr>
                            <td>
                                <div class="rank-cell">
                                    <div class="rank-number">1</div>
                                    <div class="rank-info">
                                        <h4>Camiseta Oversize Negra</h4>
                                        <span>Nexus Fashion</span>
                                    </div>
                                </div>
                            </td>
                            <td class="rank-value">$12,450 <small>CUP</small></td>
                        </tr>
                        <tr>
                            <td>
                                <div class="rank-cell">
                                    <div class="rank-number">2</div>
                                    <div class="rank-info">
                                        <h4>Zapatillas Running</h4>
                                        <span>Deportes Cuba</span>
                                    </div>
                                </div>
                            </td>
                            <td class="rank-value">$8,190 <small>CUP</small></td>
                        </tr>
                        <tr>
                            <td>
                                <div class="rank-cell">
                                    <div class="rank-number">3</div>
                                    <div class="rank-info">
                                        <h4>Jeans Skinny Azul</h4>
                                        <span>Fashion Store</span>
                                    </div>
                                </div>
                            </td>
                            <td class="rank-value">$7,360 <small>CUP</small></td>
                        </tr>
                        <tr>
                            <td>
                                <div class="rank-cell">
                                    <div class="rank-number">4</div>
                                    <div class="rank-info">
                                        <h4>Gorra New Era</h4>
                                        <span>Fashion Store</span>
                                    </div>
                                </div>
                            </td>
                            <td class="rank-value">$4,200 <small>CUP</small></td>
                        </tr>
                        <tr>
                            <td>
                                <div class="rank-cell">
                                    <div class="rank-number">5</div>
                                    <div class="rank-info">
                                        <h4>Laptop Lenovo</h4>
                                        <span>Tech Store</span>
                                    </div>
                                </div>
                            </td>
                            <td class="rank-value">$3,450 <small>CUP</small></td>
                        </tr>
                    </table>
                </div>

                <!-- Top tiendas -->
                <div class="table-card">
                    <div class="table-header">
                        <h3>Top tiendas por ventas</h3>
                        <a href="#">Ver todas <i class="fas fa-arrow-right"></i></a>
                    </div>
                    <table class="ranking-table">
                        <tr>
                            <td>
                                <div class="rank-cell">
                                    <div class="rank-number">1</div>
                                    <div class="rank-info">
                                        <h4>Nexus Fashion</h4>
                                        <span>María Pérez</span>
                                    </div>
                                </div>
                            </td>
                            <td class="rank-value">$45,670 <small>CUP</small></td>
                        </tr>
                        <tr>
                            <td>
                                <div class="rank-cell">
                                    <div class="rank-number">2</div>
                                    <div class="rank-info">
                                        <h4>Tech Store</h4>
                                        <span>Juan López</span>
                                    </div>
                                </div>
                            </td>
                            <td class="rank-value">$38,200 <small>CUP</small></td>
                        </tr>
                        <tr>
                            <td>
                                <div class="rank-cell">
                                    <div class="rank-number">3</div>
                                    <div class="rank-info">
                                        <h4>Deportes Cuba</h4>
                                        <span>Carlos Ruiz</span>
                                    </div>
                                </div>
                            </td>
                            <td class="rank-value">$29,450 <small>CUP</small></td>
                        </tr>
                        <tr>
                            <td>
                                <div class="rank-cell">
                                    <div class="rank-number">4</div>
                                    <div class="rank-info">
                                        <h4>Fashion Store</h4>
                                        <span>Ana García</span>
                                    </div>
                                </div>
                            </td>
                            <td class="rank-value">$18,930 <small>CUP</small></td>
                        </tr>
                        <tr>
                            <td>
                                <div class="rank-cell">
                                    <div class="rank-number">5</div>
                                    <div class="rank-info">
                                        <h4>Móvil Store</h4>
                                        <span>Pedro Méndez</span>
                                    </div>
                                </div>
                            </td>
                            <td class="rank-value">$12,340 <small>CUP</small></td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Additional Charts Row -->
            <div class="charts-row">
                <!-- Ventas por hora -->
                <div class="chart-card">
                    <div class="chart-header">
                        <h3>Ventas por hora del día</h3>
                    </div>
                    <div class="chart-container">
                        <canvas id="hourlyChart"></canvas>
                    </div>
                </div>

                <!-- Métodos de pago -->
                <div class="chart-card">
                    <div class="chart-header">
                        <h3>Métodos de pago</h3>
                    </div>
                    <div class="chart-container">
                        <canvas id="paymentChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Progress Bars -->
            <div class="tables-grid">
                <div class="table-card">
                    <div class="table-header">
                        <h3>Cumplimiento de metas mensuales</h3>
                    </div>
                    <div class="progress-list">
                        <div class="progress-item">
                            <div class="progress-header">
                                <span>Ventas</span>
                                <span>$89,234 / $100,000 (89%)</span>
                            </div>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: 89%;"></div>
                            </div>
                        </div>
                        <div class="progress-item">
                            <div class="progress-header">
                                <span>Pedidos</span>
                                <span>1,234 / 1,500 (82%)</span>
                            </div>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: 82%;"></div>
                            </div>
                        </div>
                        <div class="progress-item">
                            <div class="progress-header">
                                <span>Usuarios nuevos</span>
                                <span>450 / 500 (90%)</span>
                            </div>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: 90%;"></div>
                            </div>
                        </div>
                        <div class="progress-item">
                            <div class="progress-header">
                                <span>Tiendas activas</span>
                                <span>98 / 120 (82%)</span>
                            </div>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: 82%;"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Heatmap de actividad -->
                <div class="table-card">
                    <div class="table-header">
                        <h3>Actividad por día de la semana</h3>
                    </div>
                    <div class="day-label">Ventas promedio (CUP)</div>
                    <div class="heatmap-grid">
                        <div class="heatmap-cell low">Lun<br>$5,200</div>
                        <div class="heatmap-cell medium">Mar<br>$7,800</div>
                        <div class="heatmap-cell high">Mié<br>$12,400</div>
                        <div class="heatmap-cell high">Jue<br>$11,200</div>
                        <div class="heatmap-cell high">Vie<br>$14,500</div>
                        <div class="heatmap-cell medium">Sáb<br>$8,900</div>
                        <div class="heatmap-cell low">Dom<br>$4,300</div>
                    </div>
                    <div style="margin-top: 1rem;">
                        <div class="day-label">Horas pico</div>
                        <div style="display: flex; gap: 0.5rem; flex-wrap: wrap; margin-top: 0.5rem;">
                            <span style="background: #4361ee; color: white; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.7rem;">10:00 - 12:00</span>
                            <span style="background: #4361ee; color: white; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.7rem;">16:00 - 19:00</span>
                            <span style="background: #4361ee; color: white; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.7rem;">21:00 - 23:00</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Resumen ejecutivo -->
            <div class="date-range-card" style="justify-content: center; text-align: center;">
                <div>
                    <h3 style="margin-bottom: 0.5rem;">Resumen ejecutivo del período</h3>
                    <p style="color: #6c757d;">El rendimiento general es positivo con un crecimiento del 12.5% en ventas. La categoría de <strong>Ropa</strong> lidera las ventas, seguida de <strong>Electrónica</strong>. El día de mayor actividad es el <strong>viernes</strong> y la hora pico es <strong>16:00-19:00</strong>.</p>
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
                        data: [12500, 14200, 16800, 15400, 18900, 15200, 9800],
                        backgroundColor: '#4361ee',
                        borderRadius: 6
                    },
                    {
                        label: 'USD',
                        data: [357, 406, 480, 440, 540, 434, 280],
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

        // Gráfico de categorías
        const ctxCategory = document.getElementById('categoryChart').getContext('2d');
        new Chart(ctxCategory, {
            type: 'doughnut',
            data: {
                labels: ['Ropa', 'Electrónica', 'Calzado', 'Accesorios', 'Otros'],
                datasets: [{
                    data: [45, 30, 15, 8, 2],
                    backgroundColor: ['#4361ee', '#06d6a0', '#ffb703', '#7209b7', '#e9ecef'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                },
                cutout: '70%'
            }
        });

        // Gráfico de ventas por hora
        const ctxHourly = document.getElementById('hourlyChart').getContext('2d');
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

        // Gráfico de métodos de pago
        const ctxPayment = document.getElementById('paymentChart').getContext('2d');
        new Chart(ctxPayment, {
            type: 'doughnut',
            data: {
                labels: ['Transfermóvil', 'Efectivo', 'Tarjeta', 'EnZona', 'Transferencia'],
                datasets: [{
                    data: [45, 30, 15, 8, 2],
                    backgroundColor: ['#4361ee', '#06d6a0', '#ffb703', '#7209b7', '#e9ecef'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                },
                cutout: '70%'
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

        // Export buttons
        document.querySelectorAll('.btn-export').forEach(btn => {
            btn.addEventListener('click', function() {
                const format = this.querySelector('i').classList.contains('fa-file-pdf') ? 'PDF' : 
                              this.querySelector('i').classList.contains('fa-file-excel') ? 'Excel' : 'impresión';
                alert(`Exportando reporte en formato ${format} (demo)`);
            });
        });

        // Report navigation
        document.querySelectorAll('.report-nav-item').forEach(item => {
            item.addEventListener('click', function() {
                document.querySelectorAll('.report-nav-item').forEach(nav => nav.classList.remove('active'));
                this.classList.add('active');
                alert(`Cambiando a vista: ${this.textContent.trim()} (demo)`);
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