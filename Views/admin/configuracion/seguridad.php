<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NexusBuy - Seguridad</title>
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

        /* Security Navigation */
        .security-nav {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 2rem;
            background: white;
            padding: 0.5rem;
            border-radius: 12px;
            border: 1px solid #e9ecef;
            flex-wrap: wrap;
        }

        .security-nav-item {
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

        .security-nav-item:hover {
            background: #f1f3f5;
            color: #4361ee;
        }

        .security-nav-item.active {
            background: #4361ee;
            color: white;
        }

        /* Stats Cards */
        .security-stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .security-stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 16px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.02);
            border-left: 4px solid;
        }

        .security-stat-card.blue {
            border-left-color: #4361ee;
        }

        .security-stat-card.green {
            border-left-color: #06d6a0;
        }

        .security-stat-card.yellow {
            border-left-color: #ffb703;
        }

        .security-stat-card.red {
            border-left-color: #e63946;
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

        .stat-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
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

        .stat-icon.red {
            background: #ffe5e5;
            color: #e63946;
        }

        .stat-value {
            font-size: 1.75rem;
            font-weight: 700;
            margin-bottom: 0.25rem;
        }

        .stat-label {
            color: #6c757d;
            font-size: 0.875rem;
        }

        /* Settings Sections */
        .security-section {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.02);
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #e9ecef;
        }

        .section-header h2 {
            font-size: 1.25rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .section-header h2 i {
            color: #4361ee;
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
            color: #1a2639;
        }

        .form-label i {
            color: #4361ee;
            margin-right: 0.5rem;
        }

        .form-control {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            font-size: 0.875rem;
            transition: all 0.2s;
        }

        .form-control:focus {
            outline: none;
            border-color: #4361ee;
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
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

        /* Toggle Switch */
        .toggle-switch {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-top: 0.5rem;
        }

        .switch {
            position: relative;
            display: inline-block;
            width: 52px;
            height: 28px;
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
            border-radius: 28px;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 24px;
            width: 24px;
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

        .toggle-label {
            font-size: 0.875rem;
        }

        /* Checkbox Group */
        .checkbox-group {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            margin-top: 0.5rem;
        }

        .checkbox-label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.875rem;
            cursor: pointer;
        }

        .checkbox-label input[type="checkbox"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
        }

        /* Radio Group */
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

        /* Info Box */
        .info-box {
            background: #e1e8ff;
            border-radius: 12px;
            padding: 1rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            color: #4361ee;
        }

        .info-box i {
            font-size: 1.5rem;
        }

        .info-box p {
            font-size: 0.875rem;
            color: #1a2639;
        }

        .warning-box {
            background: #fff3d1;
            border-radius: 12px;
            padding: 1rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            color: #ffb703;
        }

        .warning-box i {
            font-size: 1.5rem;
        }

        .warning-box p {
            font-size: 0.875rem;
            color: #1a2639;
        }

        .danger-box {
            background: #ffe5e5;
            border-radius: 12px;
            padding: 1rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            color: #e63946;
        }

        .danger-box i {
            font-size: 1.5rem;
        }

        .danger-box p {
            font-size: 0.875rem;
            color: #1a2639;
        }

        /* 2FA Section */
        .twofa-section {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .twofa-status {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .status-badge {
            padding: 0.25rem 0.75rem;
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

        .qr-code {
            width: 200px;
            height: 200px;
            background: white;
            border: 1px solid #e9ecef;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 1rem auto;
        }

        .qr-code img {
            width: 180px;
            height: 180px;
        }

        .backup-codes {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 0.5rem;
            margin-top: 1rem;
        }

        .backup-code {
            font-family: monospace;
            background: white;
            padding: 0.5rem;
            border-radius: 4px;
            text-align: center;
            border: 1px solid #e9ecef;
        }

        /* Session Table */
        .sessions-table {
            width: 100%;
            border-collapse: collapse;
        }

        .sessions-table th {
            text-align: left;
            padding: 1rem;
            background: #f8f9fa;
            color: #6c757d;
            font-weight: 500;
            font-size: 0.75rem;
            text-transform: uppercase;
            border-bottom: 1px solid #e9ecef;
        }

        .sessions-table td {
            padding: 1rem;
            border-bottom: 1px solid #e9ecef;
            font-size: 0.875rem;
        }

        .device-info {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .device-icon {
            width: 36px;
            height: 36px;
            background: #f1f3f5;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #4361ee;
        }

        .current-session {
            background: #e1e8ff;
            color: #4361ee;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.7rem;
        }

        /* Activity Log */
        .activity-log {
            max-height: 400px;
            overflow-y: auto;
        }

        .log-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            border-bottom: 1px solid #e9ecef;
        }

        .log-icon {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .log-icon.success {
            background: #d1f7ea;
            color: #06d6a0;
        }

        .log-icon.failed {
            background: #ffe5e5;
            color: #e63946;
        }

        .log-icon.warning {
            background: #fff3d1;
            color: #ffb703;
        }

        .log-details {
            flex: 1;
        }

        .log-details h4 {
            font-size: 0.875rem;
            font-weight: 600;
            margin-bottom: 0.25rem;
        }

        .log-details p {
            font-size: 0.75rem;
            color: #6c757d;
        }

        .log-time {
            font-size: 0.75rem;
            color: #6c757d;
        }

        /* IP Whitelist */
        .ip-list {
            margin-top: 1rem;
        }

        .ip-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 0.75rem;
            background: #f8f9fa;
            border-radius: 8px;
            margin-bottom: 0.5rem;
        }

        .ip-address {
            flex: 1;
            font-family: monospace;
        }

        .ip-description {
            color: #6c757d;
            font-size: 0.875rem;
        }

        .ip-actions {
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

        .add-ip-btn {
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
            margin-top: 1rem;
        }

        .add-ip-btn:hover {
            border-color: #4361ee;
            color: #4361ee;
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            justify-content: flex-end;
            gap: 1rem;
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid #e9ecef;
        }

        .btn-primary {
            background: #4361ee;
            color: white;
            border: none;
            padding: 0.75rem 2rem;
            border-radius: 8px;
            font-size: 0.875rem;
            font-weight: 500;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: background 0.2s;
        }

        .btn-primary:hover {
            background: #3651d4;
        }

        .btn-secondary {
            background: white;
            color: #6c757d;
            border: 1px solid #e9ecef;
            padding: 0.75rem 2rem;
            border-radius: 8px;
            font-size: 0.875rem;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.2s;
        }

        .btn-secondary:hover {
            background: #f8f9fa;
            border-color: #ced4da;
        }

        .btn-danger {
            background: #e63946;
            color: white;
            border: none;
            padding: 0.75rem 2rem;
            border-radius: 8px;
            font-size: 0.875rem;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: background 0.2s;
        }

        .btn-danger:hover {
            background: #c52b38;
        }

        .btn-success {
            background: #06d6a0;
            color: white;
            border: none;
            padding: 0.75rem 2rem;
            border-radius: 8px;
            font-size: 0.875rem;
            cursor: pointer;
        }

        /* Responsive */
        @media (max-width: 1200px) {
            .security-stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .form-grid {
                grid-template-columns: 1fr;
            }
            
            .form-group.full-width {
                grid-column: span 1;
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
            
            .security-stats-grid {
                grid-template-columns: 1fr;
            }
            
            .security-nav {
                flex-direction: column;
            }
            
            .security-nav-item {
                width: 100%;
                justify-content: center;
            }
            
            .backup-codes {
                grid-template-columns: 1fr;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .action-buttons button {
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
                <a href="#" class="admin-nav-item">
                    <i class="fas fa-percent"></i>
                    Comisiones
                </a>
                <a href="#" class="admin-nav-item">
                    <i class="fas fa-hand-holding-usd"></i>
                    Retiros
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
                <a href="#" class="admin-nav-item active">
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
                    <h1>Seguridad</h1>
                    <p>Protege tu plataforma contra accesos no autorizados</p>
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

            <!-- Security Stats -->
            <div class="security-stats-grid">
                <div class="security-stat-card blue">
                    <div class="stat-header">
                        <h3>Intentos de login hoy</h3>
                        <div class="stat-icon blue">
                            <i class="fas fa-sign-in-alt"></i>
                        </div>
                    </div>
                    <div class="stat-value">156</div>
                    <div class="stat-label">12 fallidos</div>
                </div>
                <div class="security-stat-card green">
                    <div class="stat-header">
                        <h3>Usuarios activos</h3>
                        <div class="stat-icon green">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                    <div class="stat-value">1,234</div>
                    <div class="stat-label">sesiones activas</div>
                </div>
                <div class="security-stat-card yellow">
                    <div class="stat-header">
                        <h3>IPs bloqueadas</h3>
                        <div class="stat-icon yellow">
                            <i class="fas fa-ban"></i>
                        </div>
                    </div>
                    <div class="stat-value">23</div>
                    <div class="stat-label">última hora</div>
                </div>
                <div class="security-stat-card red">
                    <div class="stat-header">
                        <h3>Amenazas detectadas</h3>
                        <div class="stat-icon red">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                    </div>
                    <div class="stat-value">3</div>
                    <div class="stat-label">en las últimas 24h</div>
                </div>
            </div>

            <!-- Security Navigation -->
            <div class="security-nav">
                <span class="security-nav-item active" data-section="general">
                    <i class="fas fa-shield-alt"></i>
                    General
                </span>
                <span class="security-nav-item" data-section="twofa">
                    <i class="fas fa-mobile-alt"></i>
                    Autenticación 2FA
                </span>
                <span class="security-nav-item" data-section="password">
                    <i class="fas fa-key"></i>
                    Políticas de contraseña
                </span>
                <span class="security-nav-item" data-section="sessions">
                    <i class="fas fa-laptop"></i>
                    Sesiones activas
                </span>
                <span class="security-nav-item" data-section="logs">
                    <i class="fas fa-history"></i>
                    Registro de actividad
                </span>
                <span class="security-nav-item" data-section="whitelist">
                    <i class="fas fa-list"></i>
                    IP Whitelist
                </span>
                <span class="security-nav-item" data-section="firewall">
                    <i class="fas fa-fire"></i>
                    Firewall
                </span>
            </div>

            <!-- General Security Section -->
            <div class="security-section" id="general">
                <div class="section-header">
                    <h2><i class="fas fa-shield-alt"></i> Configuración general de seguridad</h2>
                </div>

                <div class="form-grid">
                    <div class="form-group full-width">
                        <div class="toggle-switch">
                            <span class="toggle-label">Modo mantenimiento</span>
                            <label class="switch">
                                <input type="checkbox">
                                <span class="slider"></span>
                            </label>
                        </div>
                        <div class="help-text" style="margin-left: 4rem;">El sitio no será accesible para usuarios normales</div>
                    </div>

                    <div class="form-group full-width">
                        <div class="toggle-switch">
                            <span class="toggle-label">HTTPS forzado</span>
                            <label class="switch">
                                <input type="checkbox" checked>
                                <span class="slider"></span>
                            </label>
                        </div>
                        <div class="help-text" style="margin-left: 4rem;">Redirigir todo el tráfico a HTTPS</div>
                    </div>

                    <div class="form-group full-width">
                        <div class="toggle-switch">
                            <span class="toggle-label">Protección contra fuerza bruta</span>
                            <label class="switch">
                                <input type="checkbox" checked>
                                <span class="slider"></span>
                            </label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Máximo intentos de login</label>
                        <input type="number" class="form-control" value="5" min="1" max="10">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Tiempo de bloqueo (minutos)</label>
                        <input type="number" class="form-control" value="30" min="1" max="1440">
                    </div>

                    <div class="form-group full-width">
                        <div class="toggle-switch">
                            <span class="toggle-label">Notificaciones de inicio de sesión</span>
                            <label class="switch">
                                <input type="checkbox" checked>
                                <span class="slider"></span>
                            </label>
                        </div>
                    </div>

                    <div class="form-group full-width">
                        <div class="toggle-switch">
                            <span class="toggle-label">Bloquear después de intentos fallidos</span>
                            <label class="switch">
                                <input type="checkbox" checked>
                                <span class="slider"></span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2FA Section -->
            <div class="security-section" id="twofa" style="display: none;">
                <div class="section-header">
                    <h2><i class="fas fa-mobile-alt"></i> Autenticación de dos factores</h2>
                </div>

                <div class="twofa-section">
                    <div class="twofa-status">
                        <span>Estado de 2FA para administradores</span>
                        <span class="status-badge active">Activado</span>
                    </div>

                    <div class="warning-box">
                        <i class="fas fa-info-circle"></i>
                        <p>La autenticación de dos factores añade una capa extra de seguridad a tu cuenta. Necesitarás tu teléfono para iniciar sesión.</p>
                    </div>

                    <div class="qr-code">
                        <img src="https://via.placeholder.com/180x180/4361ee/ffffff?text=QR" alt="QR Code">
                    </div>

                    <p style="text-align: center; margin-bottom: 1rem;">Escanea este código con Google Authenticator o similar</p>

                    <div class="form-group">
                        <label class="form-label">Código de verificación</label>
                        <div class="input-group">
                            <input type="text" class="form-control" placeholder="Ingresa el código de 6 dígitos">
                            <button class="btn-primary">Verificar</button>
                        </div>
                    </div>

                    <h3 style="margin: 2rem 0 1rem;">Códigos de respaldo</h3>
                    <p style="color: #6c757d; margin-bottom: 1rem;">Guarda estos códigos en un lugar seguro. Puedes usarlos si pierdes acceso a tu teléfono.</p>

                    <div class="backup-codes">
                        <div class="backup-code">ABCD-1234-EFGH-5678</div>
                        <div class="backup-code">IJKL-9012-MNOP-3456</div>
                        <div class="backup-code">QRST-7890-UVWX-1234</div>
                        <div class="backup-code">YZAB-5678-CDEF-9012</div>
                        <div class="backup-code">GHIJ-3456-KLMN-7890</div>
                        <div class="backup-code">OPQR-1234-STUV-5678</div>
                    </div>

                    <div style="margin-top: 1rem;">
                        <button class="btn-secondary">
                            <i class="fas fa-redo"></i> Regenerar códigos
                        </button>
                        <button class="btn-secondary">
                            <i class="fas fa-download"></i> Descargar códigos
                        </button>
                    </div>
                </div>
            </div>

            <!-- Password Policies Section -->
            <div class="security-section" id="password" style="display: none;">
                <div class="section-header">
                    <h2><i class="fas fa-key"></i> Políticas de contraseña</h2>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Longitud mínima</label>
                        <input type="number" class="form-control" value="8" min="6" max="20">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Expiración de contraseña (días)</label>
                        <input type="number" class="form-control" value="90" min="0" max="365">
                        <div class="help-text">0 = no expira</div>
                    </div>

                    <div class="form-group full-width">
                        <label class="form-label">Requisitos de complejidad</label>
                        <div class="checkbox-group">
                            <label class="checkbox-label">
                                <input type="checkbox" checked> Al menos una letra mayúscula
                            </label>
                            <label class="checkbox-label">
                                <input type="checkbox" checked> Al menos una letra minúscula
                            </label>
                            <label class="checkbox-label">
                                <input type="checkbox" checked> Al menos un número
                            </label>
                            <label class="checkbox-label">
                                <input type="checkbox" checked> Al menos un carácter especial
                            </label>
                        </div>
                    </div>

                    <div class="form-group full-width">
                        <div class="toggle-switch">
                            <span class="toggle-label">Evitar contraseñas comunes</span>
                            <label class="switch">
                                <input type="checkbox" checked>
                                <span class="slider"></span>
                            </label>
                        </div>
                    </div>

                    <div class="form-group full-width">
                        <div class="toggle-switch">
                            <span class="toggle-label">No permitir reutilización de contraseñas</span>
                            <label class="switch">
                                <input type="checkbox" checked>
                                <span class="slider"></span>
                            </label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Historial de contraseñas</label>
                        <input type="number" class="form-control" value="5" min="1" max="20">
                        <div class="help-text">Número de contraseñas anteriores a recordar</div>
                    </div>
                </div>
            </div>

            <!-- Active Sessions Section -->
            <div class="security-section" id="sessions" style="display: none;">
                <div class="section-header">
                    <h2><i class="fas fa-laptop"></i> Sesiones activas</h2>
                    <button class="btn-danger">
                        <i class="fas fa-sign-out-alt"></i> Cerrar todas las sesiones
                    </button>
                </div>

                <table class="sessions-table">
                    <thead>
                        <tr>
                            <th>Dispositivo</th>
                            <th>Ubicación</th>
                            <th>IP</th>
                            <th>Última actividad</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <div class="device-info">
                                    <div class="device-icon"><i class="fas fa-laptop"></i></div>
                                    <div>
                                        <strong>Chrome en Windows</strong>
                                        <div style="font-size: 0.75rem; color: #6c757d;">Escritorio principal</div>
                                    </div>
                                </div>
                            </td>
                            <td>La Habana, Cuba</td>
                            <td>190.123.45.67</td>
                            <td>Ahora mismo <span class="current-session">actual</span></td>
                            <td>
                                <button class="btn-icon"><i class="fas fa-times"></i></button>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div class="device-info">
                                    <div class="device-icon"><i class="fas fa-mobile-alt"></i></div>
                                    <div>
                                        <strong>Safari en iPhone</strong>
                                        <div style="font-size: 0.75rem; color: #6c757d;">Móvil</div>
                                    </div>
                                </div>
                            </td>
                            <td>La Habana, Cuba</td>
                            <td>190.123.45.89</td>
                            <td>Hace 2 horas</td>
                            <td>
                                <button class="btn-icon"><i class="fas fa-times"></i></button>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div class="device-info">
                                    <div class="device-icon"><i class="fas fa-tablet-alt"></i></div>
                                    <div>
                                        <strong>Firefox en iPad</strong>
                                        <div style="font-size: 0.75rem; color: #6c757d;">Tableta</div>
                                    </div>
                                </div>
                            </td>
                            <td>Miami, USA</td>
                            <td>45.67.89.123</td>
                            <td>Hace 1 día</td>
                            <td>
                                <button class="btn-icon"><i class="fas fa-times"></i></button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Activity Log Section -->
            <div class="security-section" id="logs" style="display: none;">
                <div class="section-header">
                    <h2><i class="fas fa-history"></i> Registro de actividad</h2>
                    <button class="btn-secondary">
                        <i class="fas fa-download"></i> Exportar logs
                    </button>
                </div>

                <div class="activity-log">
                    <div class="log-item">
                        <div class="log-icon success">
                            <i class="fas fa-check"></i>
                        </div>
                        <div class="log-details">
                            <h4>Inicio de sesión exitoso</h4>
                            <p>IP: 190.123.45.67 - Chrome en Windows</p>
                        </div>
                        <div class="log-time">Hace 5 minutos</div>
                    </div>
                    <div class="log-item">
                        <div class="log-icon failed">
                            <i class="fas fa-times"></i>
                        </div>
                        <div class="log-details">
                            <h4>Intento de inicio fallido</h4>
                            <p>IP: 45.67.89.123 - Contraseña incorrecta (3 intentos)</p>
                        </div>
                        <div class="log-time">Hace 15 minutos</div>
                    </div>
                    <div class="log-item">
                        <div class="log-icon warning">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <div class="log-details">
                            <h4>Cambio de contraseña</h4>
                            <p>Usuario: admin@nexusbuy.com</p>
                        </div>
                        <div class="log-time">Hace 2 horas</div>
                    </div>
                    <div class="log-item">
                        <div class="log-icon success">
                            <i class="fas fa-check"></i>
                        </div>
                        <div class="log-details">
                            <h4>Configuración de seguridad actualizada</h4>
                            <p>Políticas de contraseña modificadas</p>
                        </div>
                        <div class="log-time">Hace 5 horas</div>
                    </div>
                    <div class="log-item">
                        <div class="log-icon failed">
                            <i class="fas fa-ban"></i>
                        </div>
                        <div class="log-details">
                            <h4>IP bloqueada automáticamente</h4>
                            <p>45.67.89.123 - Múltiples intentos fallidos</p>
                        </div>
                        <div class="log-time">Hace 1 día</div>
                    </div>
                </div>
            </div>

            <!-- IP Whitelist Section -->
            <div class="security-section" id="whitelist" style="display: none;">
                <div class="section-header">
                    <h2><i class="fas fa-list"></i> IP Whitelist</h2>
                    <button class="btn-primary">
                        <i class="fas fa-plus"></i> Añadir IP
                    </button>
                </div>

                <div class="info-box">
                    <i class="fas fa-info-circle"></i>
                    <p>Las IPs en esta lista tienen acceso privilegiado al panel de administración</p>
                </div>

                <div class="ip-list">
                    <div class="ip-item">
                        <span class="ip-address">190.123.45.67</span>
                        <span class="ip-description">Oficina principal</span>
                        <div class="ip-actions">
                            <button class="btn-icon"><i class="fas fa-edit"></i></button>
                            <button class="btn-icon delete"><i class="fas fa-trash"></i></button>
                        </div>
                    </div>
                    <div class="ip-item">
                        <span class="ip-address">192.168.1.0/24</span>
                        <span class="ip-description">Red local</span>
                        <div class="ip-actions">
                            <button class="btn-icon"><i class="fas fa-edit"></i></button>
                            <button class="btn-icon delete"><i class="fas fa-trash"></i></button>
                        </div>
                    </div>
                    <div class="ip-item">
                        <span class="ip-address">45.67.89.123</span>
                        <span class="ip-description">Casa (admin remoto)</span>
                        <div class="ip-actions">
                            <button class="btn-icon"><i class="fas fa-edit"></i></button>
                            <button class="btn-icon delete"><i class="fas fa-trash"></i></button>
                        </div>
                    </div>
                </div>

                <button class="add-ip-btn">
                    <i class="fas fa-plus"></i> Añadir nueva IP o rango
                </button>
            </div>

            <!-- Firewall Section -->
            <div class="security-section" id="firewall" style="display: none;">
                <div class="section-header">
                    <h2><i class="fas fa-fire"></i> Configuración de firewall</h2>
                </div>

                <div class="form-grid">
                    <div class="form-group full-width">
                        <div class="toggle-switch">
                            <span class="toggle-label">Firewall activado</span>
                            <label class="switch">
                                <input type="checkbox" checked>
                                <span class="slider"></span>
                            </label>
                        </div>
                    </div>

                    <div class="form-group full-width">
                        <div class="toggle-switch">
                            <span class="toggle-label">Bloquear ataques DDoS</span>
                            <label class="switch">
                                <input type="checkbox" checked>
                                <span class="slider"></span>
                            </label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Límite de peticiones por IP</label>
                        <input type="number" class="form-control" value="100" min="10" max="1000">
                        <div class="help-text">por minuto</div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Tiempo de bloqueo (minutos)</label>
                        <input type="number" class="form-control" value="60" min="5" max="1440">
                    </div>

                    <div class="form-group full-width">
                        <label class="form-label">Países bloqueados</label>
                        <select class="form-control" multiple size="4">
                            <option>Rusia</option>
                            <option>China</option>
                            <option>Corea del Norte</option>
                            <option>Irán</option>
                        </select>
                    </div>

                    <div class="form-group full-width">
                        <label class="form-label">User agents bloqueados</label>
                        <textarea class="form-control" rows="3">bot|crawler|spider|scraper</textarea>
                        <div class="help-text">Expresiones regulares separadas por |</div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="action-buttons">
                <button class="btn-secondary">
                    <i class="fas fa-times"></i>
                    Cancelar
                </button>
                <button class="btn-primary">
                    <i class="fas fa-save"></i>
                    Guardar cambios
                </button>
            </div>
        </main>
    </div>

    <script>
        // Navegación entre secciones
        const navItems = document.querySelectorAll('.security-nav-item');
        const sections = {
            general: document.getElementById('general'),
            twofa: document.getElementById('twofa'),
            password: document.getElementById('password'),
            sessions: document.getElementById('sessions'),
            logs: document.getElementById('logs'),
            whitelist: document.getElementById('whitelist'),
            firewall: document.getElementById('firewall')
        };

        navItems.forEach(item => {
            item.addEventListener('click', function() {
                navItems.forEach(nav => nav.classList.remove('active'));
                this.classList.add('active');

                Object.values(sections).forEach(section => {
                    if(section) section.style.display = 'none';
                });

                const sectionId = this.dataset.section;
                if(sections[sectionId]) {
                    sections[sectionId].style.display = 'block';
                }
            });
        });

        // Guardar cambios
        document.querySelector('.btn-primary').addEventListener('click', function() {
            alert('Configuración de seguridad guardada (demo)');
        });

        // Cancelar
        document.querySelector('.btn-secondary').addEventListener('click', function() {
            if(confirm('¿Descartar cambios?')) {
                alert('Cambios descartados (demo)');
            }
        });

        // Cerrar sesiones
        document.querySelector('.btn-danger').addEventListener('click', function() {
            if(confirm('¿Cerrar todas las sesiones excepto la actual?')) {
                alert('Sesiones cerradas (demo)');
            }
        });

        // Añadir IP
        document.querySelector('.btn-primary[title]').addEventListener('click', function() {
            if(this.querySelector('.fa-plus')) {
                alert('Formulario para añadir IP (demo)');
            }
        });

        // Eliminar IP
        document.querySelectorAll('.btn-icon.delete').forEach(btn => {
            btn.addEventListener('click', function() {
                if(confirm('¿Eliminar esta IP de la whitelist?')) {
                    this.closest('.ip-item').remove();
                }
            });
        });

        // Verificar código 2FA
        document.querySelector('.btn-primary[type="button"]').addEventListener('click', function() {
            alert('Verificando código 2FA (demo)');
        });

        // Regenerar códigos de respaldo
        document.querySelector('.btn-secondary').addEventListener('click', function() {
            if(this.querySelector('.fa-redo')) {
                if(confirm('¿Regenerar códigos de respaldo? Los anteriores dejarán de funcionar.')) {
                    alert('Códigos regenerados (demo)');
                }
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