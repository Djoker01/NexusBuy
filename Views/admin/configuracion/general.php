<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NexusBuy - Configuración General</title>
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

        /* Settings Navigation */
        .settings-nav {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 2rem;
            background: white;
            padding: 0.5rem;
            border-radius: 12px;
            border: 1px solid #e9ecef;
            flex-wrap: wrap;
        }

        .settings-nav-item {
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

        .settings-nav-item:hover {
            background: #f1f3f5;
            color: #4361ee;
        }

        .settings-nav-item.active {
            background: #4361ee;
            color: white;
        }

        /* Settings Sections */
        .settings-section {
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

        .form-control:disabled {
            background: #f8f9fa;
            color: #6c757d;
        }

        textarea.form-control {
            resize: vertical;
            min-height: 100px;
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
            white-space: nowrap;
        }

        /* Image Upload */
        .image-upload-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .image-upload-card {
            text-align: center;
        }

        .image-upload-label {
            font-weight: 500;
            margin-bottom: 1rem;
            display: block;
        }

        .image-preview {
            width: 100%;
            aspect-ratio: 16/9;
            background: #f8f9fa;
            border: 2px dashed #e9ecef;
            border-radius: 12px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: #6c757d;
            cursor: pointer;
            transition: all 0.2s;
            margin-bottom: 1rem;
            position: relative;
            overflow: hidden;
        }

        .image-preview:hover {
            border-color: #4361ee;
            color: #4361ee;
        }

        .image-preview.has-image {
            border: 2px solid #4361ee;
        }

        .image-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            position: absolute;
            top: 0;
            left: 0;
        }

        .image-preview.logo-preview {
            aspect-ratio: 1/1;
            width: 150px;
            margin: 0 auto;
            border-radius: 50%;
        }

        .image-preview.favicon-preview {
            aspect-ratio: 1/1;
            width: 64px;
            margin: 0 auto;
            border-radius: 12px;
        }

        .image-actions {
            display: flex;
            gap: 0.5rem;
            justify-content: center;
        }

        .btn-outline-sm {
            background: white;
            border: 1px solid #e9ecef;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-size: 0.75rem;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-outline-sm:hover {
            background: #f1f3f5;
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

        /* Social Media */
        .social-input {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 0.5rem;
        }

        .social-icon {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
        }

        .social-icon.facebook {
            background: #1877f2;
        }

        .social-icon.instagram {
            background: #e4405f;
        }

        .social-icon.whatsapp {
            background: #25d366;
        }

        .social-icon.telegram {
            background: #0088cc;
        }

        .social-icon.twitter {
            background: #1da1f2;
        }

        /* API Keys */
        .api-key-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 8px;
            margin-bottom: 0.5rem;
        }

        .api-key-info {
            flex: 1;
        }

        .api-key-info h4 {
            font-size: 0.875rem;
            font-weight: 600;
            margin-bottom: 0.25rem;
        }

        .api-key-info p {
            font-size: 0.7rem;
            color: #6c757d;
        }

        .api-key-value {
            font-family: monospace;
            background: white;
            padding: 0.5rem;
            border-radius: 4px;
            font-size: 0.875rem;
        }

        .api-key-actions {
            display: flex;
            gap: 0.5rem;
        }

        /* Maintenance Mode */
        .maintenance-card {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 1.5rem;
            margin-top: 1rem;
        }

        .maintenance-message {
            margin-top: 1rem;
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
            background: white;
            color: #e63946;
            border: 1px solid #e63946;
            padding: 0.75rem 2rem;
            border-radius: 8px;
            font-size: 0.875rem;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.2s;
        }

        .btn-danger:hover {
            background: #e63946;
            color: white;
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
            .form-grid {
                grid-template-columns: 1fr;
            }
            
            .form-group.full-width {
                grid-column: span 1;
            }
            
            .image-upload-grid {
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
            
            .settings-nav {
                flex-direction: column;
            }
            
            .settings-nav-item {
                width: 100%;
                justify-content: center;
            }
            
            .radio-group {
                flex-direction: column;
                gap: 0.5rem;
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
                <a href="#" class="admin-nav-item active">
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
                    <h1>Configuración General</h1>
                    <p>Personaliza todos los aspectos de tu plataforma</p>
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

            <!-- Settings Navigation -->
            <div class="settings-nav">
                <span class="settings-nav-item active" data-section="general">
                    <i class="fas fa-globe"></i>
                    General
                </span>
                <span class="settings-nav-item" data-section="branding">
                    <i class="fas fa-paint-brush"></i>
                    Branding
                </span>
                <span class="settings-nav-item" data-section="contact">
                    <i class="fas fa-address-book"></i>
                    Contacto
                </span>
                <span class="settings-nav-item" data-section="payment">
                    <i class="fas fa-credit-card"></i>
                    Pagos
                </span>
                <span class="settings-nav-item" data-section="email">
                    <i class="fas fa-envelope"></i>
                    Email
                </span>
                <span class="settings-nav-item" data-section="security">
                    <i class="fas fa-shield-alt"></i>
                    Seguridad
                </span>
                <span class="settings-nav-item" data-section="api">
                    <i class="fas fa-key"></i>
                    APIs
                </span>
                <span class="settings-nav-item" data-section="maintenance">
                    <i class="fas fa-tools"></i>
                    Mantenimiento
                </span>
            </div>

            <!-- General Settings -->
            <div class="settings-section" id="general">
                <div class="section-header">
                    <h2><i class="fas fa-globe"></i> Información general</h2>
                </div>

                <div class="form-grid">
                    <div class="form-group full-width">
                        <label class="form-label">
                            <i class="fas fa-tag"></i>
                            Nombre del sitio
                        </label>
                        <input type="text" class="form-control" value="NexusBuy">
                    </div>

                    <div class="form-group full-width">
                        <label class="form-label">
                            <i class="fas fa-align-left"></i>
                            Descripción del sitio
                        </label>
                        <textarea class="form-control">Plataforma de comercio electrónico para conectar vendedores y compradores en Cuba. Productos de calidad, envíos seguros y múltiples métodos de pago.</textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-language"></i>
                            Idioma principal
                        </label>
                        <select class="form-control">
                            <option>Español</option>
                            <option>Inglés</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-clock"></i>
                            Zona horaria
                        </label>
                        <select class="form-control">
                            <option>America/Havana (GMT-5)</option>
                            <option>America/New_York (GMT-5)</option>
                            <option>Europe/Madrid (GMT+1)</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-calendar"></i>
                            Formato de fecha
                        </label>
                        <select class="form-control">
                            <option>DD/MM/YYYY</option>
                            <option>MM/DD/YYYY</option>
                            <option>YYYY-MM-DD</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-money-bill"></i>
                            Moneda principal
                        </label>
                        <select class="form-control">
                            <option>CUP - Peso Cubano</option>
                            <option>USD - Dólar Americano</option>
                            <option>EUR - Euro</option>
                        </select>
                    </div>

                    <div class="form-group full-width">
                        <label class="form-label">
                            <i class="fas fa-coins"></i>
                            Monedas soportadas
                        </label>
                        <div class="checkbox-group">
                            <label class="checkbox-label">
                                <input type="checkbox" checked> CUP - Peso Cubano
                            </label>
                            <label class="checkbox-label">
                                <input type="checkbox" checked> USD - Dólar Americano
                            </label>
                            <label class="checkbox-label">
                                <input type="checkbox"> EUR - Euro
                            </label>
                            <label class="checkbox-label">
                                <input type="checkbox"> MLC - Moneda Libremente Convertible
                            </label>
                        </div>
                    </div>

                    <div class="form-group full-width">
                        <label class="form-label">
                            <i class="fas fa-sync-alt"></i>
                            Tipo de cambio (CUP a USD)
                        </div>
                        <div class="input-group">
                            <span class="input-group-text">1 USD =</span>
                            <input type="number" class="form-control" value="35" step="0.01">
                            <span class="input-group-text">CUP</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Branding Settings -->
            <div class="settings-section" id="branding" style="display: none;">
                <div class="section-header">
                    <h2><i class="fas fa-paint-brush"></i> Branding</h2>
                </div>

                <div class="image-upload-grid">
                    <!-- Logo -->
                    <div class="image-upload-card">
                        <label class="image-upload-label">Logo principal</label>
                        <div class="image-preview logo-preview has-image">
                            <img src="https://via.placeholder.com/150x150/4361ee/ffffff?text=NexusBuy" alt="Logo">
                        </div>
                        <div class="image-actions">
                            <button class="btn-outline-sm">
                                <i class="fas fa-upload"></i> Cambiar
                            </button>
                            <button class="btn-outline-sm">
                                <i class="fas fa-trash"></i> Eliminar
                            </button>
                        </div>
                    </div>

                    <!-- Favicon -->
                    <div class="image-upload-card">
                        <label class="image-upload-label">Favicon</label>
                        <div class="image-preview favicon-preview has-image">
                            <img src="https://via.placeholder.com/64x64/4361ee/ffffff?text=N" alt="Favicon">
                        </div>
                        <div class="image-actions">
                            <button class="btn-outline-sm">
                                <i class="fas fa-upload"></i> Cambiar
                            </button>
                            <button class="btn-outline-sm">
                                <i class="fas fa-trash"></i> Eliminar
                            </button>
                        </div>
                    </div>

                    <!-- Banner -->
                    <div class="image-upload-card">
                        <label class="image-upload-label">Banner principal</label>
                        <div class="image-preview has-image">
                            <img src="https://via.placeholder.com/1200x400/4361ee/ffffff?text=NexusBuy+Banner" alt="Banner">
                        </div>
                        <div class="image-actions">
                            <button class="btn-outline-sm">
                                <i class="fas fa-upload"></i> Cambiar
                            </button>
                            <button class="btn-outline-sm">
                                <i class="fas fa-trash"></i> Eliminar
                            </button>
                        </div>
                    </div>
                </div>

                <div class="form-grid">
                    <div class="form-group full-width">
                        <label class="form-label">
                            <i class="fas fa-palette"></i>
                            Colores del tema
                        </label>
                        <div style="display: flex; gap: 1rem; align-items: center;">
                            <div>
                                <label>Color primario</label>
                                <input type="color" class="form-control" style="width: 100px; height: 50px;" value="#4361ee">
                            </div>
                            <div>
                                <label>Color secundario</label>
                                <input type="color" class="form-control" style="width: 100px; height: 50px;" value="#7209b7">
                            </div>
                            <div>
                                <label>Color de acento</label>
                                <input type="color" class="form-control" style="width: 100px; height: 50px;" value="#4cc9f0">
                            </div>
                        </div>
                    </div>

                    <div class="form-group full-width">
                        <label class="form-label">
                            <i class="fas fa-font"></i>
                            Tipografía
                        </label>
                        <select class="form-control" style="width: 300px;">
                            <option>Inter (sistema)</option>
                            <option>Roboto</option>
                            <option>Open Sans</option>
                            <option>Montserrat</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Contact Settings -->
            <div class="settings-section" id="contact" style="display: none;">
                <div class="section-header">
                    <h2><i class="fas fa-address-book"></i> Información de contacto</h2>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-envelope"></i>
                            Email de contacto
                        </label>
                        <input type="email" class="form-control" value="contacto@nexusbuy.com">
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-phone"></i>
                            Teléfono de soporte
                        </label>
                        <input type="text" class="form-control" value="+53 7 1234567">
                    </div>

                    <div class="form-group full-width">
                        <label class="form-label">
                            <i class="fas fa-map-marker-alt"></i>
                            Dirección física
                        </label>
                        <input type="text" class="form-control" value="Calle 123 #456, Vedado, La Habana, Cuba">
                    </div>

                    <div class="form-group full-width">
                        <label class="form-label">
                            <i class="fas fa-hashtag"></i>
                            Redes sociales
                        </label>
                        <div class="social-input">
                            <div class="social-icon facebook"><i class="fab fa-facebook-f"></i></div>
                            <input type="text" class="form-control" value="nexusbuy.cuba">
                        </div>
                        <div class="social-input">
                            <div class="social-icon instagram"><i class="fab fa-instagram"></i></div>
                            <input type="text" class="form-control" value="@nexusbuy.cuba">
                        </div>
                        <div class="social-input">
                            <div class="social-icon whatsapp"><i class="fab fa-whatsapp"></i></div>
                            <input type="text" class="form-control" value="+53 5 1234567">
                        </div>
                        <div class="social-input">
                            <div class="social-icon telegram"><i class="fab fa-telegram-plane"></i></div>
                            <input type="text" class="form-control" value="@nexusbuy_bot">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment Settings -->
            <div class="settings-section" id="payment" style="display: none;">
                <div class="section-header">
                    <h2><i class="fas fa-credit-card"></i> Configuración de pagos</h2>
                </div>

                <div class="form-grid">
                    <div class="form-group full-width">
                        <label class="form-label">
                            <i class="fas fa-money-bill"></i>
                            Métodos de pago disponibles
                        </label>
                        <div class="checkbox-group">
                            <label class="checkbox-label">
                                <input type="checkbox" checked> Transfermóvil
                            </label>
                            <label class="checkbox-label">
                                <input type="checkbox" checked> EnZona
                            </label>
                            <label class="checkbox-label">
                                <input type="checkbox" checked> Efectivo (entrega)
                            </label>
                            <label class="checkbox-label">
                                <input type="checkbox"> Tarjeta bancaria (pasarela)
                            </label>
                            <label class="checkbox-label">
                                <input type="checkbox"> PayPal
                            </label>
                        </div>
                    </div>

                    <div class="form-group full-width">
                        <label class="form-label">
                            <i class="fas fa-percent"></i>
                            Configuración de comisiones
                        </label>
                        <div class="input-group">
                            <span class="input-group-text">Comisión por defecto</span>
                            <input type="number" class="form-control" value="10" min="0" max="100" step="0.1">
                            <span class="input-group-text">%</span>
                        </div>
                        <div class="input-group" style="margin-top: 0.5rem;">
                            <span class="input-group-text">Comisión mínima</span>
                            <input type="number" class="form-control" value="50" min="0">
                            <span class="input-group-text">CUP</span>
                        </div>
                        <div class="input-group" style="margin-top: 0.5rem;">
                            <span class="input-group-text">Comisión máxima</span>
                            <input type="number" class="form-control" value="5000" min="0">
                            <span class="input-group-text">CUP</span>
                        </div>
                    </div>

                    <div class="form-group full-width">
                        <label class="form-label">
                            <i class="fas fa-truck"></i>
                            Configuración de envíos
                        </label>
                        <div class="radio-group">
                            <label class="radio-label">
                                <input type="radio" name="shipping" checked> Por distancia
                            </label>
                            <label class="radio-label">
                                <input type="radio" name="shipping"> Por peso
                            </label>
                            <label class="radio-label">
                                <input type="radio" name="shipping"> Tarifa fija
                            </label>
                        </div>
                        <div class="input-group" style="margin-top: 0.5rem;">
                            <span class="input-group-text">Tarifa base</span>
                            <input type="number" class="form-control" value="150">
                            <span class="input-group-text">CUP</span>
                        </div>
                        <div class="input-group" style="margin-top: 0.5rem;">
                            <span class="input-group-text">Tarifa por km</span>
                            <input type="number" class="form-control" value="10">
                            <span class="input-group-text">CUP</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Email Settings -->
            <div class="settings-section" id="email" style="display: none;">
                <div class="section-header">
                    <h2><i class="fas fa-envelope"></i> Configuración de email</h2>
                </div>

                <div class="info-box">
                    <i class="fas fa-info-circle"></i>
                    <p>Todos los emails transaccionales se envían usando esta configuración SMTP</p>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Servidor SMTP</label>
                        <input type="text" class="form-control" value="smtp.gmail.com">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Puerto</label>
                        <input type="text" class="form-control" value="587">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Usuario</label>
                        <input type="text" class="form-control" value="notificaciones@nexusbuy.com">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Contraseña</label>
                        <input type="password" class="form-control" value="********">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Email remitente</label>
                        <input type="email" class="form-control" value="noreply@nexusbuy.com">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Nombre remitente</label>
                        <input type="text" class="form-control" value="NexusBuy">
                    </div>

                    <div class="form-group full-width">
                        <label class="form-label">Encriptación</label>
                        <select class="form-control" style="width: 200px;">
                            <option>TLS</option>
                            <option>SSL</option>
                            <option>Ninguna</option>
                        </select>
                    </div>

                    <div class="form-group full-width">
                        <div class="toggle-switch">
                            <span class="toggle-label">Enviar emails de prueba</span>
                            <label class="switch">
                                <input type="checkbox" checked>
                                <span class="slider"></span>
                            </label>
                        </div>
                    </div>
                </div>

                <div style="margin-top: 1rem;">
                    <button class="btn-outline-sm">
                        <i class="fas fa-paper-plane"></i> Enviar email de prueba
                    </button>
                </div>
            </div>

            <!-- Security Settings -->
            <div class="settings-section" id="security" style="display: none;">
                <div class="section-header">
                    <h2><i class="fas fa-shield-alt"></i> Configuración de seguridad</h2>
                </div>

                <div class="form-grid">
                    <div class="form-group full-width">
                        <div class="toggle-switch">
                            <span class="toggle-label">Verificación en dos pasos para administradores</span>
                            <label class="switch">
                                <input type="checkbox" checked>
                                <span class="slider"></span>
                            </label>
                        </div>
                        <div class="toggle-switch">
                            <span class="toggle-label">Notificaciones de inicio de sesión</span>
                            <label class="switch">
                                <input type="checkbox" checked>
                                <span class="slider"></span>
                            </label>
                        </div>
                        <div class="toggle-switch">
                            <span class="toggle-label">Bloqueo automático después de intentos fallidos</span>
                            <label class="switch">
                                <input type="checkbox" checked>
                                <span class="slider"></span>
                            </label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Máximo de intentos fallidos</label>
                        <input type="number" class="form-control" value="5">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Tiempo de bloqueo (minutos)</label>
                        <input type="number" class="form-control" value="30">
                    </div>

                    <div class="form-group full-width">
                        <label class="form-label">Política de contraseñas</label>
                        <div class="checkbox-group">
                            <label class="checkbox-label">
                                <input type="checkbox" checked> Mínimo 8 caracteres
                            </label>
                            <label class="checkbox-label">
                                <input type="checkbox" checked> Al menos una mayúscula
                            </label>
                            <label class="checkbox-label">
                                <input type="checkbox" checked> Al menos un número
                            </label>
                            <label class="checkbox-label">
                                <input type="checkbox" checked> Al menos un carácter especial
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- API Settings -->
            <div class="settings-section" id="api" style="display: none;">
                <div class="section-header">
                    <h2><i class="fas fa-key"></i> Claves de API</h2>
                </div>

                <div class="api-key-item">
                    <div class="api-key-info">
                        <h4>Google Maps API</h4>
                        <p>Para mostrar mapas en direcciones de entrega</p>
                    </div>
                    <div class="api-key-value">••••••••••••••••••••</div>
                    <div class="api-key-actions">
                        <button class="btn-outline-sm"><i class="fas fa-edit"></i></button>
                        <button class="btn-outline-sm"><i class="fas fa-eye"></i></button>
                    </div>
                </div>

                <div class="api-key-item">
                    <div class="api-key-info">
                        <h4>PayPal API</h4>
                        <p>Para procesar pagos internacionales</p>
                    </div>
                    <div class="api-key-value">••••••••••••••••••••</div>
                    <div class="api-key-actions">
                        <button class="btn-outline-sm"><i class="fas fa-edit"></i></button>
                        <button class="btn-outline-sm"><i class="fas fa-eye"></i></button>
                    </div>
                </div>

                <div class="api-key-item">
                    <div class="api-key-info">
                        <h4>SendGrid API</h4>
                        <p>Para envío de emails transaccionales</p>
                    </div>
                    <div class="api-key-value">••••••••••••••••••••</div>
                    <div class="api-key-actions">
                        <button class="btn-outline-sm"><i class="fas fa-edit"></i></button>
                        <button class="btn-outline-sm"><i class="fas fa-eye"></i></button>
                    </div>
                </div>

                <div class="api-key-item">
                    <div class="api-key-info">
                        <h4>Twilio API</h4>
                        <p>Para notificaciones SMS</p>
                    </div>
                    <div class="api-key-value">••••••••••••••••••••</div>
                    <div class="api-key-actions">
                        <button class="btn-outline-sm"><i class="fas fa-edit"></i></button>
                        <button class="btn-outline-sm"><i class="fas fa-eye"></i></button>
                    </div>
                </div>
            </div>

            <!-- Maintenance Settings -->
            <div class="settings-section" id="maintenance" style="display: none;">
                <div class="section-header">
                    <h2><i class="fas fa-tools"></i> Modo mantenimiento</h2>
                </div>

                <div class="warning-box">
                    <i class="fas fa-exclamation-triangle"></i>
                    <p>Activar el modo mantenimiento hará que el sitio sea inaccesible para los usuarios normales</p>
                </div>

                <div class="maintenance-card">
                    <div class="toggle-switch">
                        <span class="toggle-label" style="font-weight: 600;">Activar modo mantenimiento</span>
                        <label class="switch">
                            <input type="checkbox" id="maintenanceToggle">
                            <span class="slider"></span>
                        </label>
                    </div>

                    <div class="maintenance-message">
                        <label class="form-label">Mensaje para los usuarios</label>
                        <textarea class="form-control" rows="4">Estamos realizando mejoras en nuestra plataforma. Disculpe las molestias, estaremos de vuelta en breve.</textarea>
                    </div>

                    <div style="margin-top: 1rem;">
                        <label class="form-label">IPs permitidas (separadas por coma)</label>
                        <input type="text" class="form-control" value="192.168.1.1, 10.0.0.1">
                    </div>
                </div>

                <div style="margin-top: 2rem;">
                    <h3 style="margin-bottom: 1rem;">Respaldo de base de datos</h3>
                    <div style="display: flex; gap: 1rem;">
                        <button class="btn-secondary">
                            <i class="fas fa-database"></i> Crear respaldo ahora
                        </button>
                        <button class="btn-secondary">
                            <i class="fas fa-history"></i> Restaurar respaldo
                        </button>
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
        const navItems = document.querySelectorAll('.settings-nav-item');
        const sections = {
            general: document.getElementById('general'),
            branding: document.getElementById('branding'),
            contact: document.getElementById('contact'),
            payment: document.getElementById('payment'),
            email: document.getElementById('email'),
            security: document.getElementById('security'),
            api: document.getElementById('api'),
            maintenance: document.getElementById('maintenance')
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

        // Toggle maintenance mode warning
        document.getElementById('maintenanceToggle').addEventListener('change', function(e) {
            if(this.checked) {
                alert('Modo mantenimiento activado. El sitio no será accesible para usuarios normales (demo)');
            } else {
                alert('Modo mantenimiento desactivado. El sitio vuelve a estar accesible (demo)');
            }
        });

        // Guardar cambios
        document.querySelector('.btn-primary').addEventListener('click', function() {
            alert('Configuración guardada exitosamente (demo)');
        });

        // Cancelar
        document.querySelector('.btn-secondary').addEventListener('click', function() {
            if(confirm('¿Descartar todos los cambios?')) {
                alert('Cambios descartados (demo)');
            }
        });

        // Subir imágenes
        document.querySelectorAll('.btn-outline-sm').forEach(btn => {
            btn.addEventListener('click', function() {
                if(this.querySelector('.fa-upload')) {
                    alert('Selector de imágenes (demo)');
                } else if(this.querySelector('.fa-trash')) {
                    alert('Imagen eliminada (demo)');
                }
            });
        });

        // Email de prueba
        document.querySelector('.btn-outline-sm[title]').addEventListener('click', function() {
            alert('Enviando email de prueba... (demo)');
        });

        // Ver API keys
        document.querySelectorAll('.api-key-actions .btn-outline-sm').forEach(btn => {
            btn.addEventListener('click', function() {
                if(this.querySelector('.fa-eye')) {
                    alert('Mostrando API key (demo)');
                } else if(this.querySelector('.fa-edit')) {
                    alert('Editando API key (demo)');
                }
            });
        });

        // Backup buttons
        document.querySelectorAll('.btn-secondary').forEach(btn => {
            if(btn.querySelector('.fa-database')) {
                btn.addEventListener('click', () => alert('Creando respaldo de base de datos... (demo)'));
            } else if(btn.querySelector('.fa-history')) {
                btn.addEventListener('click', () => alert('Restaurando respaldo... (demo)'));
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