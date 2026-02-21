<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NexusBuy - Panel de Administración</title>
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

        .header-search {
            display: flex;
            align-items: center;
            background: #f8f9fa;
            border-radius: 8px;
            padding: 0.5rem 1rem;
            width: 300px;
        }

        .header-search i {
            color: #6c757d;
            margin-right: 0.5rem;
        }

        .header-search input {
            border: none;
            background: transparent;
            outline: none;
            width: 100%;
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

        /* Stats Cards Admin */
        .admin-stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .admin-stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 16px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.02);
            display: flex;
            align-items: center;
            gap: 1rem;
            transition: transform 0.2s;
        }

        .admin-stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(0,0,0,0.05);
        }

        .stat-icon-admin {
            width: 56px;
            height: 56px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.75rem;
        }

        .stat-icon-admin.blue {
            background: #e1e8ff;
            color: #4361ee;
        }

        .stat-icon-admin.green {
            background: #d1f7ea;
            color: #06d6a0;
        }

        .stat-icon-admin.purple {
            background: #ead1ff;
            color: #7209b7;
        }

        .stat-icon-admin.orange {
            background: #ffead1;
            color: #fb8b24;
        }

        .stat-info-admin h3 {
            font-size: 1.75rem;
            font-weight: 700;
            margin-bottom: 0.25rem;
        }

        .stat-info-admin p {
            color: #6c757d;
            font-size: 0.875rem;
        }

        /* Charts Row Admin */
        .admin-charts-row {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .admin-chart-card {
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.02);
        }

        .admin-chart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .admin-chart-header h3 {
            font-size: 1rem;
            font-weight: 600;
        }

        .chart-period {
            display: flex;
            gap: 0.5rem;
        }

        .period-btn-admin {
            background: none;
            border: none;
            padding: 0.25rem 0.75rem;
            font-size: 0.75rem;
            color: #6c757d;
            cursor: pointer;
            border-radius: 4px;
        }

        .period-btn-admin.active {
            background: #4361ee;
            color: white;
        }

        .chart-container-admin {
            height: 300px;
            position: relative;
        }

        /* Tables */
        .admin-section {
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.02);
        }

        .admin-section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .admin-section-header h2 {
            font-size: 1.1rem;
            font-weight: 600;
        }

        .admin-section-header a {
            color: #4361ee;
            text-decoration: none;
            font-size: 0.875rem;
        }

        .admin-table {
            width: 100%;
            border-collapse: collapse;
        }

        .admin-table th {
            text-align: left;
            padding: 1rem;
            background: #f8f9fa;
            color: #6c757d;
            font-weight: 500;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-radius: 8px 8px 0 0;
        }

        .admin-table td {
            padding: 1rem;
            border-bottom: 1px solid #e9ecef;
            font-size: 0.875rem;
        }

        .user-cell {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .user-avatar-mini {
            width: 36px;
            height: 36px;
            background: #4361ee;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 0.875rem;
        }

        .user-avatar-mini.green {
            background: #06d6a0;
        }

        .user-avatar-mini.orange {
            background: #fb8b24;
        }

        .badge-admin-table {
            padding: 0.25rem 0.5rem;
            border-radius: 999px;
            font-size: 0.7rem;
            font-weight: 500;
        }

        .badge-admin-table.vendor {
            background: #d1f7ea;
            color: #06d6a0;
        }

        .badge-admin-table.seller {
            background: #fff3d1;
            color: #ffb703;
        }

        .badge-admin-table.user {
            background: #e9ecef;
            color: #6c757d;
        }

        .badge-admin-table.admin {
            background: #e1e8ff;
            color: #4361ee;
        }

        .badge-status {
            padding: 0.25rem 0.5rem;
            border-radius: 999px;
            font-size: 0.7rem;
            font-weight: 500;
        }

        .badge-status.active {
            background: #d1f7ea;
            color: #06d6a0;
        }

        .badge-status.inactive {
            background: #ffe5e5;
            color: #e63946;
        }

        .badge-status.pending {
            background: #fff3d1;
            color: #ffb703;
        }

        .action-btns {
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

        /* Quick Actions */
        .quick-actions-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
        }

        .quick-action-card {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 12px;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s;
            border: 1px solid #e9ecef;
        }

        .quick-action-card:hover {
            background: white;
            border-color: #4361ee;
            transform: translateY(-2px);
        }

        .quick-action-card i {
            font-size: 2rem;
            color: #4361ee;
            margin-bottom: 0.5rem;
        }

        .quick-action-card h4 {
            font-size: 0.875rem;
            font-weight: 600;
            margin-bottom: 0.25rem;
        }

        .quick-action-card p {
            font-size: 0.75rem;
            color: #6c757d;
        }

        /* Responsive */
        @media (max-width: 1200px) {
            .admin-stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .admin-charts-row {
                grid-template-columns: 1fr;
            }
            
            .quick-actions-grid {
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
            
            .admin-stats-grid {
                grid-template-columns: 1fr;
            }
            
            .quick-actions-grid {
                grid-template-columns: 1fr;
            }
            
            .admin-table {
                min-width: 800px;
            }
            
            .header-search {
                display: none;
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
                <a href="#" class="admin-nav-item active">
                    <i class="fas fa-chart-pie"></i>
                    Dashboard
                </a>
                <a href="#" class="admin-nav-item">
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

        <!-- Main Content Admin -->
        <main class="admin-main">
            <!-- Header -->
            <div class="admin-header">
                <div class="header-search">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Buscar en el panel...">
                </div>
                <div class="header-actions">
                    <div class="header-notifications">
                        <i class="far fa-bell"></i>
                        <span class="badge-danger">8</span>
                    </div>
                    <div class="header-admin-user">
                        <div class="admin-mini-avatar">AD</div>
                        <i class="fas fa-chevron-down" style="color: #6c757d; font-size: 0.875rem;"></i>
                    </div>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="admin-stats-grid">
                <div class="admin-stat-card">
                    <div class="stat-icon-admin blue">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-info-admin">
                        <h3>2,345</h3>
                        <p>Usuarios totales</p>
                    </div>
                </div>
                <div class="admin-stat-card">
                    <div class="stat-icon-admin green">
                        <i class="fas fa-store"></i>
                    </div>
                    <div class="stat-info-admin">
                        <h3>128</h3>
                        <p>Tiendas activas</p>
                    </div>
                </div>
                <div class="admin-stat-card">
                    <div class="stat-icon-admin purple">
                        <i class="fas fa-box"></i>
                    </div>
                    <div class="stat-info-admin">
                        <h3>4,567</h3>
                        <p>Productos</p>
                    </div>
                </div>
                <div class="admin-stat-card">
                    <div class="stat-icon-admin orange">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <div class="stat-info-admin">
                        <h3>$89,234</h3>
                        <p>Ventas totales</p>
                    </div>
                </div>
            </div>

            <!-- Charts -->
            <div class="admin-charts-row">
                <div class="admin-chart-card">
                    <div class="admin-chart-header">
                        <h3>Ventas por mes</h3>
                        <div class="chart-period">
                            <button class="period-btn-admin active">2025</button>
                            <button class="period-btn-admin">2024</button>
                        </div>
                    </div>
                    <div class="chart-container-admin">
                        <canvas id="salesChart"></canvas>
                    </div>
                </div>

                <div class="admin-chart-card">
                    <div class="admin-chart-header">
                        <h3>Distribución de usuarios</h3>
                        <div class="chart-period">
                            <button class="period-btn-admin active">Por rol</button>
                        </div>
                    </div>
                    <div class="chart-container-admin">
                        <canvas id="usersChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="admin-section">
                <div class="admin-section-header">
                    <h2>Acciones rápidas</h2>
                </div>
                <div class="quick-actions-grid">
                    <div class="quick-action-card">
                        <i class="fas fa-user-plus"></i>
                        <h4>Nuevo usuario</h4>
                        <p>Crear cuenta</p>
                    </div>
                    <div class="quick-action-card">
                        <i class="fas fa-store-alt"></i>
                        <h4>Nueva tienda</h4>
                        <p>Aprobar solicitud</p>
                    </div>
                    <div class="quick-action-card">
                        <i class="fas fa-tags"></i>
                        <h4>Nueva categoría</h4>
                        <p>Gestionar catálogo</p>
                    </div>
                    <div class="quick-action-card">
                        <i class="fas fa-money-bill"></i>
                        <h4>Procesar retiros</h4>
                        <p>3 pendientes</p>
                    </div>
                </div>
            </div>

            <!-- Recent Users -->
            <div class="admin-section">
                <div class="admin-section-header">
                    <h2>Últimos usuarios registrados</h2>
                    <a href="#">Ver todos <i class="fas fa-arrow-right"></i></a>
                </div>
                <div style="overflow-x: auto;">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Usuario</th>
                                <th>Email</th>
                                <th>Rol</th>
                                <th>Tienda</th>
                                <th>Fecha</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <div class="user-cell">
                                        <div class="user-avatar-mini">MP</div>
                                        <span>María Pérez</span>
                                    </div>
                                </td>
                                <td>maria@email.com</td>
                                <td><span class="badge-admin-table vendor">Vendedor</span></td>
                                <td>Fashion Store</td>
                                <td>15/03/2025</td>
                                <td><span class="badge-status active">Activo</span></td>
                                <td>
                                    <div class="action-btns">
                                        <button class="action-btn"><i class="fas fa-edit"></i></button>
                                        <button class="action-btn"><i class="fas fa-ban"></i></button>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="user-cell">
                                        <div class="user-avatar-mini green">JL</div>
                                        <span>Juan López</span>
                                    </div>
                                </td>
                                <td>juan@email.com</td>
                                <td><span class="badge-admin-table seller">Vendedor</span></td>
                                <td>Tech Store</td>
                                <td>14/03/2025</td>
                                <td><span class="badge-status pending">Pendiente</span></td>
                                <td>
                                    <div class="action-btns">
                                        <button class="action-btn"><i class="fas fa-check"></i></button>
                                        <button class="action-btn"><i class="fas fa-times"></i></button>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="user-cell">
                                        <div class="user-avatar-mini">AG</div>
                                        <span>Ana García</span>
                                    </div>
                                </td>
                                <td>ana@email.com</td>
                                <td><span class="badge-admin-table user">Comprador</span></td>
                                <td>-</td>
                                <td>13/03/2025</td>
                                <td><span class="badge-status active">Activo</span></td>
                                <td>
                                    <div class="action-btns">
                                        <button class="action-btn"><i class="fas fa-edit"></i></button>
                                        <button class="action-btn"><i class="fas fa-ban"></i></button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Recent Stores -->
            <div class="admin-section">
                <div class="admin-section-header">
                    <h2>Tiendas pendientes de aprobación</h2>
                    <a href="#">Ver todas <i class="fas fa-arrow-right"></i></a>
                </div>
                <div style="overflow-x: auto;">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Tienda</th>
                                <th>Propietario</th>
                                <th>Email</th>
                                <th>Productos</th>
                                <th>Solicitud</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <div class="user-cell">
                                        <i class="fas fa-store" style="color: #4361ee;"></i>
                                        <span>Deportes Cuba</span>
                                    </div>
                                </td>
                                <td>Carlos Ruiz</td>
                                <td>carlos@email.com</td>
                                <td>24</td>
                                <td>Hace 2 horas</td>
                                <td>
                                    <div class="action-btns">
                                        <button class="action-btn" style="color: #06d6a0;"><i class="fas fa-check"></i></button>
                                        <button class="action-btn" style="color: #e63946;"><i class="fas fa-times"></i></button>
                                        <button class="action-btn"><i class="fas fa-eye"></i></button>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="user-cell">
                                        <i class="fas fa-store" style="color: #4361ee;"></i>
                                        <span>Electrónica Habana</span>
                                    </div>
                                </td>
                                <td>Laura Martínez</td>
                                <td>laura@email.com</td>
                                <td>56</td>
                                <td>Ayer</td>
                                <td>
                                    <div class="action-btns">
                                        <button class="action-btn" style="color: #06d6a0;"><i class="fas fa-check"></i></button>
                                        <button class="action-btn" style="color: #e63946;"><i class="fas fa-times"></i></button>
                                        <button class="action-btn"><i class="fas fa-eye"></i></button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Withdrawals -->
            <div class="admin-section">
                <div class="admin-section-header">
                    <h2>Solicitudes de retiro pendientes</h2>
                    <a href="#">Gestionar <i class="fas fa-arrow-right"></i></a>
                </div>
                <div style="overflow-x: auto;">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Tienda</th>
                                <th>Monto</th>
                                <th>Método</th>
                                <th>Solicitud</th>
                                <th>Comisión</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Nexus Fashion</td>
                                <td><strong>$5,000 CUP</strong><br><small>$143 USD</small></td>
                                <td>Transfermóvil</td>
                                <td>Hoy 10:30</td>
                                <td>$125 CUP</td>
                                <td>
                                    <div class="action-btns">
                                        <button class="action-btn" style="color: #06d6a0;"><i class="fas fa-check"></i></button>
                                        <button class="action-btn" style="color: #e63946;"><i class="fas fa-times"></i></button>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>Tech Store</td>
                                <td><strong>$3,200 CUP</strong><br><small>$91 USD</small></td>
                                <td>EnZona</td>
                                <td>Ayer</td>
                                <td>$80 CUP</td>
                                <td>
                                    <div class="action-btns">
                                        <button class="action-btn" style="color: #06d6a0;"><i class="fas fa-check"></i></button>
                                        <button class="action-btn" style="color: #e63946;"><i class="fas fa-times"></i></button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Gráfico de ventas
        const ctxSales = document.getElementById('salesChart').getContext('2d');
        new Chart(ctxSales, {
            type: 'line',
            data: {
                labels: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
                datasets: [{
                    label: 'Ventas 2025 (CUP)',
                    data: [45000, 52000, 58000, 62000, 68000, 72000, 78000, 82000, 89000, 92000, 98000, 105000],
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

        // Gráfico de usuarios por rol
        const ctxUsers = document.getElementById('usersChart').getContext('2d');
        new Chart(ctxUsers, {
            type: 'doughnut',
            data: {
                labels: ['Compradores', 'Vendedores', 'Administradores'],
                datasets: [{
                    data: [1800, 500, 45],
                    backgroundColor: ['#06d6a0', '#4361ee', '#7209b7'],
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

        // Navegación
        document.querySelectorAll('.admin-nav-item').forEach(item => {
            item.addEventListener('click', (e) => {
                e.preventDefault();
                document.querySelectorAll('.admin-nav-item').forEach(nav => nav.classList.remove('active'));
                item.classList.add('active');
            });
        });

        // Notificaciones
        document.querySelector('.header-notifications').addEventListener('click', () => {
            alert('Panel de notificaciones (demo)');
        });

        // Acciones rápidas
        document.querySelectorAll('.quick-action-card').forEach(card => {
            card.addEventListener('click', () => {
                alert('Acción rápida (demo)');
            });
        });

        // Botones de acción en tablas
        document.querySelectorAll('.action-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                if(btn.querySelector('.fa-check')) {
                    alert('Aprobado (demo)');
                } else if(btn.querySelector('.fa-times')) {
                    alert('Rechazado (demo)');
                } else if(btn.querySelector('.fa-edit')) {
                    alert('Editar (demo)');
                } else if(btn.querySelector('.fa-ban')) {
                    alert('Bloquear (demo)');
                } else if(btn.querySelector('.fa-eye')) {
                    alert('Ver detalles (demo)');
                }
            });
        });

        // Cambio de período en gráficos
        document.querySelectorAll('.period-btn-admin').forEach(btn => {
            btn.addEventListener('click', function() {
                this.parentElement.querySelectorAll('.period-btn-admin').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                alert('Cambiando período del gráfico (demo)');
            });
        });
    </script>
</body>
</html>