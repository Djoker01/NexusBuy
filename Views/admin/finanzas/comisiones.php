<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NexusBuy - Gestión de Comisiones</title>
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
        .commission-stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .commission-stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 16px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.02);
            display: flex;
            align-items: center;
            gap: 1rem;
            border-left: 4px solid;
        }

        .commission-stat-card.blue {
            border-left-color: #4361ee;
        }

        .commission-stat-card.green {
            border-left-color: #06d6a0;
        }

        .commission-stat-card.yellow {
            border-left-color: #ffb703;
        }

        .commission-stat-card.purple {
            border-left-color: #7209b7;
        }

        .stat-icon {
            width: 56px;
            height: 56px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.75rem;
        }

        .stat-icon.blue {
            background: #e1e8ff;
            color: #4361ee;
        }

        .stat-icon.green {
            background: #d1f7ea;
            color: #06d6a0;
        }

        .stat-icon.yellow {
            background: #fff3d1;
            color: #ffb703;
        }

        .stat-icon.purple {
            background: #ead1ff;
            color: #7209b7;
        }

        .stat-info h3 {
            font-size: 1.75rem;
            font-weight: 700;
            margin-bottom: 0.25rem;
        }

        .stat-info p {
            color: #6c757d;
            font-size: 0.875rem;
        }

        /* Settings Grid */
        .settings-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        /* Global Settings Card */
        .global-settings {
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.02);
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .section-header h2 {
            font-size: 1.1rem;
            font-weight: 600;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            font-size: 0.875rem;
            font-weight: 500;
            margin-bottom: 0.5rem;
            color: #1a2639;
        }

        .form-label i {
            color: #4361ee;
            margin-right: 0.25rem;
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

        .input-group {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .input-group .form-control {
            flex: 1;
        }

        .input-group-text {
            background: #f8f9fa;
            padding: 0.75rem 1rem;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            color: #6c757d;
        }

        .radio-group {
            display: flex;
            gap: 2rem;
            margin-top: 0.5rem;
        }

        .radio-label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.875rem;
            cursor: pointer;
        }

        .help-text {
            font-size: 0.75rem;
            color: #6c757d;
            margin-top: 0.25rem;
        }

        .toggle-switch {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-top: 0.5rem;
        }

        .switch {
            position: relative;
            display: inline-block;
            width: 48px;
            height: 24px;
        }

        .switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #e9ecef;
            transition: .4s;
            border-radius: 24px;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 20px;
            width: 20px;
            left: 2px;
            bottom: 2px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }

        input:checked + .slider {
            background-color: #4361ee;
        }

        input:checked + .slider:before {
            transform: translateX(24px);
        }

        /* Tiered Commissions */
        .tiers-list {
            margin-top: 1rem;
        }

        .tier-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 8px;
            margin-bottom: 0.5rem;
        }

        .tier-range {
            flex: 2;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .tier-range input {
            width: 100px;
            padding: 0.5rem;
            border: 1px solid #e9ecef;
            border-radius: 4px;
        }

        .tier-percentage {
            flex: 1;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .tier-percentage input {
            width: 80px;
            padding: 0.5rem;
            border: 1px solid #e9ecef;
            border-radius: 4px;
        }

        .tier-actions {
            display: flex;
            gap: 0.5rem;
        }

        .btn-icon {
            background: none;
            border: none;
            color: #6c757d;
            cursor: pointer;
            padding: 0.25rem;
            border-radius: 4px;
        }

        .btn-icon:hover {
            background: #f1f3f5;
            color: #4361ee;
        }

        .btn-icon.delete:hover {
            color: #e63946;
        }

        .add-tier-btn {
            width: 100%;
            padding: 0.75rem;
            background: none;
            border: 2px dashed #e9ecef;
            border-radius: 8px;
            color: #6c757d;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            margin-top: 0.5rem;
        }

        .add-tier-btn:hover {
            border-color: #4361ee;
            color: #4361ee;
        }

        /* Category Commissions */
        .category-commission-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 0.75rem;
            border-bottom: 1px solid #e9ecef;
        }

        .category-commission-item:last-child {
            border-bottom: none;
        }

        .category-info {
            flex: 2;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .category-icon {
            width: 32px;
            height: 32px;
            background: #e1e8ff;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #4361ee;
        }

        .category-percentage {
            flex: 1;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .category-percentage input {
            width: 80px;
            padding: 0.5rem;
            border: 1px solid #e9ecef;
            border-radius: 4px;
        }

        /* Store Overrides */
        .store-overrides {
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.02);
        }

        .search-mini {
            display: flex;
            align-items: center;
            background: #f8f9fa;
            border-radius: 8px;
            padding: 0.5rem 1rem;
            margin-bottom: 1.5rem;
        }

        .search-mini i {
            color: #6c757d;
            margin-right: 0.5rem;
        }

        .search-mini input {
            border: none;
            background: transparent;
            outline: none;
            width: 100%;
        }

        .store-list {
            max-height: 400px;
            overflow-y: auto;
        }

        .store-override-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem;
            border-bottom: 1px solid #e9ecef;
        }

        .store-override-item:last-child {
            border-bottom: none;
        }

        .store-avatar {
            width: 36px;
            height: 36px;
            background: #e1e8ff;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #4361ee;
            font-weight: 600;
        }

        .store-details {
            flex: 2;
        }

        .store-details h4 {
            font-size: 0.875rem;
            font-weight: 600;
            margin-bottom: 0.25rem;
        }

        .store-details span {
            font-size: 0.7rem;
            color: #6c757d;
        }

        .commission-input {
            width: 70px;
            padding: 0.5rem;
            border: 1px solid #e9ecef;
            border-radius: 4px;
            text-align: center;
        }

        .commission-badge {
            background: #4361ee;
            color: white;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.7rem;
        }

        /* Reports Section */
        .reports-section {
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            margin-top: 2rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.02);
        }

        .reports-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
            margin-top: 1.5rem;
        }

        .report-card {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 1rem;
            cursor: pointer;
            transition: all 0.2s;
        }

        .report-card:hover {
            background: white;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }

        .report-icon {
            width: 48px;
            height: 48px;
            background: white;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: #4361ee;
        }

        .report-info h4 {
            font-size: 0.875rem;
            font-weight: 600;
            margin-bottom: 0.25rem;
        }

        .report-info p {
            font-size: 0.7rem;
            color: #6c757d;
        }

        .btn-primary {
            background: #4361ee;
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-size: 0.875rem;
            font-weight: 500;
            cursor: pointer;
        }

        .btn-secondary {
            background: white;
            color: #6c757d;
            border: 1px solid #e9ecef;
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
            .commission-stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .settings-grid {
                grid-template-columns: 1fr;
            }
            
            .reports-grid {
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
            
            .commission-stats-grid {
                grid-template-columns: 1fr;
            }
            
            .reports-grid {
                grid-template-columns: 1fr;
            }
            
            .radio-group {
                flex-direction: column;
                gap: 0.5rem;
            }
            
            .tier-item {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .tier-range, .tier-percentage {
                width: 100%;
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
                    <i class="fas fa-percent"></i>
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
                    <h1>Gestión de Comisiones</h1>
                    <p>Configura los porcentajes y reglas de comisión</p>
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
            <div class="commission-stats-grid">
                <div class="commission-stat-card blue">
                    <div class="stat-icon blue">
                        <i class="fas fa-percent"></i>
                    </div>
                    <div class="stat-info">
                        <h3>10%</h3>
                        <p>Comisión global</p>
                    </div>
                </div>
                <div class="commission-stat-card green">
                    <div class="stat-icon green">
                        <i class="fas fa-store"></i>
                    </div>
                    <div class="stat-info">
                        <h3>8</h3>
                        <p>Tiendas con comisión personalizada</p>
                    </div>
                </div>
                <div class="commission-stat-card yellow">
                    <div class="stat-icon yellow">
                        <i class="fas fa-tags"></i>
                    </div>
                    <div class="stat-info">
                        <h3>4</h3>
                        <p>Categorías especiales</p>
                    </div>
                </div>
                <div class="commission-stat-card purple">
                    <div class="stat-icon purple">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <div class="stat-info">
                        <h3>$8,923</h3>
                        <p>Comisiones este mes</p>
                    </div>
                </div>
            </div>

            <!-- Settings Grid -->
            <div class="settings-grid">
                <!-- Global Settings -->
                <div class="global-settings">
                    <div class="section-header">
                        <h2><i class="fas fa-globe" style="color: #4361ee; margin-right: 0.5rem;"></i>Configuración global</h2>
                        <button class="btn-outline">Guardar cambios</button>
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-percent"></i>
                            Comisión global por defecto
                        </label>
                        <div class="input-group">
                            <input type="number" class="form-control" value="10" min="0" max="100" step="0.1">
                            <span class="input-group-text">%</span>
                        </div>
                        <div class="help-text">Este porcentaje se aplica a todas las tiendas que no tengan una comisión personalizada</div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-calculator"></i>
                            Tipo de comisión
                        </label>
                        <div class="radio-group">
                            <label class="radio-label">
                                <input type="radio" name="commissionType" checked> Porcentaje fijo
                            </label>
                            <label class="radio-label">
                                <input type="radio" name="commissionType"> Por rangos
                            </label>
                            <label class="radio-label">
                                <input type="radio" name="commissionType"> Mixta (fijo + variable)
                            </label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-coins"></i>
                            Comisión mínima por venta
                        </label>
                        <div class="input-group">
                            <input type="number" class="form-control" value="50" min="0">
                            <span class="input-group-text">CUP</span>
                        </div>
                        <div class="help-text">Si el porcentaje da un monto menor, se cobra este mínimo</div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-money-bill-wave"></i>
                            Comisión máxima por venta
                        </label>
                        <div class="input-group">
                            <input type="number" class="form-control" value="5000" min="0">
                            <span class="input-group-text">CUP</span>
                        </div>
                        <div class="help-text">Límite máximo a cobrar por transacción</div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-toggle-on"></i>
                            Opciones adicionales
                        </label>
                        <div class="toggle-switch">
                            <span>Aplicar comisión también al costo de envío</span>
                            <label class="switch">
                                <input type="checkbox">
                                <span class="slider"></span>
                            </label>
                        </div>
                        <div class="toggle-switch" style="margin-top: 0.5rem;">
                            <span>Redondear comisión al entero superior</span>
                            <label class="switch">
                                <input type="checkbox" checked>
                                <span class="slider"></span>
                            </label>
                        </div>
                        <div class="toggle-switch" style="margin-top: 0.5rem;">
                            <span>Incluir impuestos en el cálculo</span>
                            <label class="switch">
                                <input type="checkbox">
                                <span class="slider"></span>
                            </label>
                        </div>
                    </div>

                    <div class="section-header" style="margin-top: 2rem;">
                        <h2><i class="fas fa-layer-group" style="color: #4361ee; margin-right: 0.5rem;"></i>Comisiones por rangos</h2>
                    </div>

                    <div class="tiers-list">
                        <div class="tier-item">
                            <div class="tier-range">
                                <span>De</span>
                                <input type="number" value="0" min="0">
                                <span>a</span>
                                <input type="number" value="1000" min="0">
                                <span>CUP</span>
                            </div>
                            <div class="tier-percentage">
                                <span>Comisión:</span>
                                <input type="number" value="5" min="0" max="100" step="0.1">
                                <span>%</span>
                            </div>
                            <div class="tier-actions">
                                <button class="btn-icon"><i class="fas fa-edit"></i></button>
                                <button class="btn-icon delete"><i class="fas fa-trash"></i></button>
                            </div>
                        </div>
                        <div class="tier-item">
                            <div class="tier-range">
                                <span>De</span>
                                <input type="number" value="1001" min="0">
                                <span>a</span>
                                <input type="number" value="5000" min="0">
                                <span>CUP</span>
                            </div>
                            <div class="tier-percentage">
                                <span>Comisión:</span>
                                <input type="number" value="7.5" min="0" max="100" step="0.1">
                                <span>%</span>
                            </div>
                            <div class="tier-actions">
                                <button class="btn-icon"><i class="fas fa-edit"></i></button>
                                <button class="btn-icon delete"><i class="fas fa-trash"></i></button>
                            </div>
                        </div>
                        <div class="tier-item">
                            <div class="tier-range">
                                <span>De</span>
                                <input type="number" value="5001" min="0">
                                <span>a</span>
                                <input type="number" value="10000" min="0">
                                <span>CUP</span>
                            </div>
                            <div class="tier-percentage">
                                <span>Comisión:</span>
                                <input type="number" value="10" min="0" max="100" step="0.1">
                                <span>%</span>
                            </div>
                            <div class="tier-actions">
                                <button class="btn-icon"><i class="fas fa-edit"></i></button>
                                <button class="btn-icon delete"><i class="fas fa-trash"></i></button>
                            </div>
                        </div>
                        <div class="tier-item">
                            <div class="tier-range">
                                <span>Más de</span>
                                <input type="number" value="10000" min="0">
                                <span>CUP</span>
                            </div>
                            <div class="tier-percentage">
                                <span>Comisión:</span>
                                <input type="number" value="12" min="0" max="100" step="0.1">
                                <span>%</span>
                            </div>
                            <div class="tier-actions">
                                <button class="btn-icon"><i class="fas fa-edit"></i></button>
                                <button class="btn-icon delete"><i class="fas fa-trash"></i></button>
                            </div>
                        </div>
                        <button class="add-tier-btn">
                            <i class="fas fa-plus"></i> Añadir rango
                        </button>
                    </div>
                </div>

                <!-- Store Overrides -->
                <div class="store-overrides">
                    <div class="section-header">
                        <h2><i class="fas fa-store" style="color: #4361ee; margin-right: 0.5rem;"></i>Comisiones por tienda</h2>
                    </div>

                    <div class="search-mini">
                        <i class="fas fa-search"></i>
                        <input type="text" placeholder="Buscar tienda...">
                    </div>

                    <div class="store-list">
                        <div class="store-override-item">
                            <div class="store-avatar">NF</div>
                            <div class="store-details">
                                <h4>Nexus Fashion</h4>
                                <span>María Pérez · Ventas: $12,450</span>
                            </div>
                            <div class="input-group" style="width: 100px;">
                                <input type="number" class="commission-input" value="8" min="0" max="100" step="0.1">
                                <span>%</span>
                            </div>
                        </div>
                        <div class="store-override-item">
                            <div class="store-avatar">TS</div>
                            <div class="store-details">
                                <h4>Tech Store</h4>
                                <span>Juan López · Ventas: $8,900</span>
                            </div>
                            <div class="input-group" style="width: 100px;">
                                <input type="number" class="commission-input" value="10" min="0" max="100" step="0.1">
                                <span>%</span>
                            </div>
                        </div>
                        <div class="store-override-item">
                            <div class="store-avatar">DC</div>
                            <div class="store-details">
                                <h4>Deportes Cuba</h4>
                                <span>Carlos Ruiz · Ventas: $15,200</span>
                            </div>
                            <div class="input-group" style="width: 100px;">
                                <input type="number" class="commission-input" value="7" min="0" max="100" step="0.1">
                                <span>%</span>
                            </div>
                        </div>
                        <div class="store-override-item">
                            <div class="store-avatar">MS</div>
                            <div class="store-details">
                                <h4>Móvil Store</h4>
                                <span>Pedro Méndez · Ventas: $6,750</span>
                            </div>
                            <div class="input-group" style="width: 100px;">
                                <input type="number" class="commission-input" value="12" min="0" max="100" step="0.1">
                                <span>%</span>
                            </div>
                        </div>
                        <div class="store-override-item">
                            <div class="store-avatar">FS</div>
                            <div class="store-details">
                                <h4>Fashion Store</h4>
                                <span>Ana García · Ventas: $5,230</span>
                            </div>
                            <div class="input-group" style="width: 100px;">
                                <input type="number" class="commission-input" value="10" min="0" max="100" step="0.1">
                                <span>%</span>
                            </div>
                        </div>
                    </div>

                    <div style="margin-top: 1rem;">
                        <button class="btn-outline" style="width: 100%;">
                            <i class="fas fa-save"></i> Guardar cambios por tienda
                        </button>
                    </div>
                </div>
            </div>

            <!-- Category Commissions -->
            <div class="global-settings" style="margin-top: 1rem;">
                <div class="section-header">
                    <h2><i class="fas fa-tags" style="color: #4361ee; margin-right: 0.5rem;"></i>Comisiones por categoría</h2>
                    <span class="commission-badge">Sobrescribe la comisión global</span>
                </div>

                <div style="max-height: 300px; overflow-y: auto;">
                    <div class="category-commission-item">
                        <div class="category-info">
                            <div class="category-icon"><i class="fas fa-tshirt"></i></div>
                            <span>Ropa</span>
                        </div>
                        <div class="category-percentage">
                            <input type="number" value="8" min="0" max="100" step="0.1">
                            <span>%</span>
                        </div>
                    </div>
                    <div class="category-commission-item">
                        <div class="category-info">
                            <div class="category-icon"><i class="fas fa-laptop"></i></div>
                            <span>Electrónica</span>
                        </div>
                        <div class="category-percentage">
                            <input type="number" value="12" min="0" max="100" step="0.1">
                            <span>%</span>
                        </div>
                    </div>
                    <div class="category-commission-item">
                        <div class="category-info">
                            <div class="category-icon"><i class="fas fa-shoe-prints"></i></div>
                            <span>Calzado</span>
                        </div>
                        <div class="category-percentage">
                            <input type="number" value="9" min="0" max="100" step="0.1">
                            <span>%</span>
                        </div>
                    </div>
                    <div class="category-commission-item">
                        <div class="category-info">
                            <div class="category-icon"><i class="fas fa-clock"></i></div>
                            <span>Accesorios</span>
                        </div>
                        <div class="category-percentage">
                            <input type="number" value="10" min="0" max="100" step="0.1">
                            <span>%</span>
                        </div>
                    </div>
                    <div class="category-commission-item">
                        <div class="category-info">
                            <div class="category-icon"><i class="fas fa-mobile-alt"></i></div>
                            <span>Celulares</span>
                        </div>
                        <div class="category-percentage">
                            <input type="number" value="11" min="0" max="100" step="0.1">
                            <span>%</span>
                        </div>
                    </div>
                    <div class="category-commission-item">
                        <div class="category-info">
                            <div class="category-icon"><i class="fas fa-headphones"></i></div>
                            <span>Auriculares</span>
                        </div>
                        <div class="category-percentage">
                            <input type="number" value="8" min="0" max="100" step="0.1">
                            <span>%</span>
                        </div>
                    </div>
                </div>

                <div style="margin-top: 1rem;">
                    <button class="btn-primary">Guardar comisiones por categoría</button>
                </div>
            </div>

            <!-- Reports Section -->
            <div class="reports-section">
                <div class="section-header">
                    <h2><i class="fas fa-chart-bar" style="color: #4361ee; margin-right: 0.5rem;"></i>Reportes de comisiones</h2>
                </div>

                <div class="reports-grid">
                    <div class="report-card">
                        <div class="report-icon">
                            <i class="fas fa-file-invoice"></i>
                        </div>
                        <div class="report-info">
                            <h4>Reporte mensual</h4>
                            <p>Marzo 2025 · $8,923</p>
                        </div>
                    </div>
                    <div class="report-card">
                        <div class="report-icon">
                            <i class="fas fa-store"></i>
                        </div>
                        <div class="report-info">
                            <h4>Por tienda</h4>
                            <p>Desglose por vendedor</p>
                        </div>
                    </div>
                    <div class="report-card">
                        <div class="report-icon">
                            <i class="fas fa-tags"></i>
                        </div>
                        <div class="report-info">
                            <h4>Por categoría</h4>
                            <p>Rendimiento por sección</p>
                        </div>
                    </div>
                    <div class="report-card">
                        <div class="report-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <div class="report-info">
                            <h4>Tendencia anual</h4>
                            <p>Comparativa 2024-2025</p>
                        </div>
                    </div>
                    <div class="report-card">
                        <div class="report-icon">
                            <i class="fas fa-file-pdf"></i>
                        </div>
                        <div class="report-info">
                            <h4>Exportar todo</h4>
                            <p>PDF · Excel · CSV</p>
                        </div>
                    </div>
                    <div class="report-card">
                        <div class="report-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div class="report-info">
                            <h4>Enviar reporte</h4>
                            <p>Programar envío automático</p>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Add tier
        document.querySelector('.add-tier-btn').addEventListener('click', function() {
            alert('Añadir nuevo rango de comisión (demo)');
        });

        // Save global settings
        document.querySelectorAll('.btn-outline').forEach(btn => {
            btn.addEventListener('click', function() {
                if(this.textContent.includes('Guardar cambios')) {
                    alert('Configuración global guardada (demo)');
                }
            });
        });

        // Save store overrides
        document.querySelector('.btn-outline').addEventListener('click', function() {
            if(this.textContent.includes('Guardar cambios por tienda')) {
                alert('Comisiones por tienda actualizadas (demo)');
            }
        });

        // Save category commissions
        document.querySelector('.btn-primary').addEventListener('click', function() {
            alert('Comisiones por categoría guardadas (demo)');
        });

        // Report cards
        document.querySelectorAll('.report-card').forEach(card => {
            card.addEventListener('click', function() {
                const title = this.querySelector('h4').textContent;
                alert(`Generando reporte: ${title} (demo)`);
            });
        });

        // Delete tier
        document.querySelectorAll('.btn-icon.delete').forEach(btn => {
            btn.addEventListener('click', function() {
                if(confirm('¿Eliminar este rango de comisión?')) {
                    this.closest('.tier-item').remove();
                }
            });
        });

        // Search store
        document.querySelector('.search-mini input').addEventListener('keyup', function(e) {
            if(e.key === 'Enter') {
                alert('Buscando: ' + this.value + ' (demo)');
            }
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