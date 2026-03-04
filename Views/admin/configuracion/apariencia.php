<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NexusBuy - Gestión Visual</title>
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

        /* Visual Navigation */
        .visual-nav {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 2rem;
            background: white;
            padding: 0.5rem;
            border-radius: 12px;
            border: 1px solid #e9ecef;
            flex-wrap: wrap;
        }

        .visual-nav-item {
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

        .visual-nav-item:hover {
            background: #f1f3f5;
            color: #4361ee;
        }

        .visual-nav-item.active {
            background: #4361ee;
            color: white;
        }

        /* Theme Grid */
        .themes-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .theme-card {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0,0,0,0.02);
            border: 2px solid transparent;
            transition: all 0.2s;
            cursor: pointer;
        }

        .theme-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(0,0,0,0.05);
        }

        .theme-card.active {
            border-color: #4361ee;
        }

        .theme-preview {
            height: 160px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            position: relative;
            overflow: hidden;
        }

        .theme-preview.light {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        }

        .theme-preview.dark {
            background: linear-gradient(135deg, #1a2639 0%, #0d1b2a 100%);
        }

        .theme-preview.modern {
            background: linear-gradient(135deg, #4361ee 0%, #7209b7 100%);
        }

        .theme-preview.classic {
            background: linear-gradient(135deg, #2b5876 0%, #4e4376 100%);
        }

        .theme-overlay {
            position: absolute;
            top: 20px;
            left: 20px;
            right: 20px;
            bottom: 20px;
            background: rgba(255,255,255,0.1);
            backdrop-filter: blur(5px);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            opacity: 0;
            transition: opacity 0.2s;
        }

        .theme-card:hover .theme-overlay {
            opacity: 1;
        }

        .theme-info {
            padding: 1.5rem;
        }

        .theme-info h3 {
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .theme-info p {
            font-size: 0.875rem;
            color: #6c757d;
        }

        .theme-badge {
            display: inline-block;
            background: #e1e8ff;
            color: #4361ee;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.7rem;
            margin-top: 0.5rem;
        }

        /* Color Customizer */
        .color-section {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.02);
        }

        .color-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 2rem;
        }

        .color-item {
            text-align: center;
        }

        .color-preview {
            width: 100%;
            height: 100px;
            border-radius: 12px;
            margin-bottom: 1rem;
            border: 2px solid #e9ecef;
            cursor: pointer;
            transition: transform 0.2s;
        }

        .color-preview:hover {
            transform: scale(1.05);
        }

        .color-preview.primary {
            background: #4361ee;
        }

        .color-preview.secondary {
            background: #7209b7;
        }

        .color-preview.accent {
            background: #4cc9f0;
        }

        .color-preview.success {
            background: #06d6a0;
        }

        .color-preview.warning {
            background: #ffb703;
        }

        .color-preview.danger {
            background: #e63946;
        }

        .color-label {
            font-weight: 500;
            margin-bottom: 0.5rem;
        }

        .color-value {
            font-family: monospace;
            color: #6c757d;
        }

        .color-input {
            margin-top: 0.5rem;
            width: 100%;
            padding: 0.5rem;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            text-align: center;
        }

        /* Typography */
        .typography-section {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.02);
        }

        .font-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 2rem;
        }

        .font-card {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 1.5rem;
            cursor: pointer;
            border: 2px solid transparent;
            transition: all 0.2s;
        }

        .font-card:hover {
            border-color: #4361ee;
        }

        .font-card.active {
            border-color: #4361ee;
            background: #e1e8ff;
        }

        .font-sample {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .font-sample.inter {
            font-family: 'Inter', sans-serif;
        }

        .font-sample.roboto {
            font-family: 'Roboto', sans-serif;
        }

        .font-sample.montserrat {
            font-family: 'Montserrat', sans-serif;
        }

        .font-sample.open-sans {
            font-family: 'Open Sans', sans-serif;
        }

        .font-name {
            font-weight: 600;
            margin-bottom: 0.25rem;
        }

        .font-description {
            font-size: 0.875rem;
            color: #6c757d;
        }

        /* Layout Customizer */
        .layout-section {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.02);
        }

        .layout-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.5rem;
        }

        .layout-option {
            text-align: center;
            cursor: pointer;
        }

        .layout-preview {
            width: 100%;
            height: 120px;
            background: #f8f9fa;
            border-radius: 12px;
            margin-bottom: 1rem;
            border: 2px solid #e9ecef;
            position: relative;
            overflow: hidden;
            transition: all 0.2s;
        }

        .layout-preview:hover {
            border-color: #4361ee;
        }

        .layout-preview.active {
            border-color: #4361ee;
            background: #e1e8ff;
        }

        .preview-header {
            height: 30px;
            background: #4361ee;
            opacity: 0.2;
        }

        .preview-sidebar {
            width: 30px;
            height: 60px;
            background: #7209b7;
            opacity: 0.2;
            position: absolute;
            left: 10px;
            top: 40px;
        }

        .preview-content {
            height: 40px;
            background: #4cc9f0;
            opacity: 0.2;
            position: absolute;
            left: 50px;
            right: 10px;
            top: 40px;
        }

        .preview-footer {
            height: 20px;
            background: #06d6a0;
            opacity: 0.2;
            position: absolute;
            left: 10px;
            right: 10px;
            bottom: 10px;
        }

        .preview-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 5px;
            padding: 10px;
            height: 100%;
        }

        .preview-grid-cell {
            background: #4361ee;
            opacity: 0.2;
        }

        .layout-label {
            font-weight: 500;
        }

        /* Sliders */
        .slider-group {
            margin-top: 2rem;
        }

        .slider-label {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
        }

        .slider {
            width: 100%;
            height: 8px;
            background: #e9ecef;
            border-radius: 4px;
            outline: none;
            -webkit-appearance: none;
        }

        .slider::-webkit-slider-thumb {
            -webkit-appearance: none;
            width: 20px;
            height: 20px;
            background: #4361ee;
            border-radius: 50%;
            cursor: pointer;
        }

        /* CSS Editor */
        .css-editor {
            background: #1e1e2f;
            color: #fff;
            border-radius: 12px;
            padding: 1rem;
            font-family: 'Monaco', 'Menlo', monospace;
            font-size: 0.875rem;
            line-height: 1.5;
            margin-top: 1rem;
        }

        .css-editor textarea {
            width: 100%;
            height: 200px;
            background: transparent;
            border: none;
            color: #fff;
            font-family: inherit;
            resize: vertical;
            outline: none;
        }

        /* Header Builder */
        .header-builder {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            margin-top: 2rem;
        }

        .header-preview {
            background: white;
            border: 1px solid #e9ecef;
            border-radius: 12px;
            overflow: hidden;
        }

        .preview-frame {
            height: 200px;
            position: relative;
            background: #f8f9fa;
        }

        .header-simulated {
            height: 60px;
            background: white;
            border-bottom: 1px solid #e9ecef;
            display: flex;
            align-items: center;
            padding: 0 1rem;
        }

        .logo-simulated {
            width: 100px;
            height: 30px;
            background: #4361ee;
            border-radius: 4px;
        }

        .nav-simulated {
            display: flex;
            gap: 1rem;
            margin-left: 2rem;
        }

        .nav-item-simulated {
            width: 60px;
            height: 10px;
            background: #e9ecef;
            border-radius: 2px;
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
            .themes-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .color-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .font-grid {
                grid-template-columns: 1fr;
            }
            
            .layout-grid {
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
            
            .themes-grid {
                grid-template-columns: 1fr;
            }
            
            .color-grid {
                grid-template-columns: 1fr;
            }
            
            .layout-grid {
                grid-template-columns: 1fr;
            }
            
            .header-builder {
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
                <a href="#" class="admin-nav-item active">
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
                    <h1>Gestión Visual</h1>
                    <p>Personaliza la apariencia de tu plataforma</p>
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

            <!-- Visual Navigation -->
            <div class="visual-nav">
                <span class="visual-nav-item active" data-section="themes">
                    <i class="fas fa-paint-brush"></i>
                    Temas
                </span>
                <span class="visual-nav-item" data-section="colors">
                    <i class="fas fa-fill-drip"></i>
                    Colores
                </span>
                <span class="visual-nav-item" data-section="typography">
                    <i class="fas fa-font"></i>
                    Tipografía
                </span>
                <span class="visual-nav-item" data-section="layout">
                    <i class="fas fa-columns"></i>
                    Layout
                </span>
                <span class="visual-nav-item" data-section="header">
                    <i class="fas fa-window-maximize"></i>
                    Cabecera
                </span>
                <span class="visual-nav-item" data-section="footer">
                    <i class="fas fa-window-minimize"></i>
                    Pie de página
                </span>
                <span class="visual-nav-item" data-section="custom">
                    <i class="fas fa-code"></i>
                    CSS personalizado
                </span>
            </div>

            <!-- Themes Section -->
            <div class="themes-grid" id="themes">
                <div class="theme-card active">
                    <div class="theme-preview light"></div>
                    <div class="theme-info">
                        <h3>Tema Claro</h3>
                        <p>Diseño limpio y profesional, ideal para tiendas de moda</p>
                        <span class="theme-badge">Activo</span>
                    </div>
                </div>
                <div class="theme-card">
                    <div class="theme-preview dark"></div>
                    <div class="theme-info">
                        <h3>Tema Oscuro</h3>
                        <p>Moderno y elegante, perfecto para tecnología</p>
                    </div>
                </div>
                <div class="theme-card">
                    <div class="theme-preview modern"></div>
                    <div class="theme-info">
                        <h3>Tema Moderno</h3>
                        <p>Colores vibrantes y diseño contemporáneo</p>
                    </div>
                </div>
                <div class="theme-card">
                    <div class="theme-preview classic"></div>
                    <div class="theme-info">
                        <h3>Tema Clásico</h3>
                        <p>Estilo tradicional y elegante</p>
                    </div>
                </div>
                <div class="theme-card">
                    <div class="theme-preview" style="background: linear-gradient(135deg, #ff6b6b 0%, #feca57 100%);"></div>
                    <div class="theme-info">
                        <h3>Tema Vibrante</h3>
                        <p>Colores cálidos y energéticos</p>
                    </div>
                </div>
                <div class="theme-card">
                    <div class="theme-preview" style="background: linear-gradient(135deg, #48c6ef 0%, #6f86d6 100%);"></div>
                    <div class="theme-info">
                        <h3>Tema Fresco</h3>
                        <p>Tonos azules y verdes relajantes</p>
                    </div>
                </div>
            </div>

            <!-- Colors Section (hidden initially) -->
            <div class="color-section" id="colors" style="display: none;">
                <h2 style="margin-bottom: 2rem;">Personalización de colores</h2>
                
                <div class="color-grid">
                    <div class="color-item">
                        <div class="color-preview primary"></div>
                        <div class="color-label">Color primario</div>
                        <div class="color-value">#4361ee</div>
                        <input type="color" class="color-input" value="#4361ee">
                    </div>
                    <div class="color-item">
                        <div class="color-preview secondary"></div>
                        <div class="color-label">Color secundario</div>
                        <div class="color-value">#7209b7</div>
                        <input type="color" class="color-input" value="#7209b7">
                    </div>
                    <div class="color-item">
                        <div class="color-preview accent"></div>
                        <div class="color-label">Color de acento</div>
                        <div class="color-value">#4cc9f0</div>
                        <input type="color" class="color-input" value="#4cc9f0">
                    </div>
                    <div class="color-item">
                        <div class="color-preview success"></div>
                        <div class="color-label">Color éxito</div>
                        <div class="color-value">#06d6a0</div>
                        <input type="color" class="color-input" value="#06d6a0">
                    </div>
                    <div class="color-item">
                        <div class="color-preview warning"></div>
                        <div class="color-label">Color advertencia</div>
                        <div class="color-value">#ffb703</div>
                        <input type="color" class="color-input" value="#ffb703">
                    </div>
                    <div class="color-item">
                        <div class="color-preview danger"></div>
                        <div class="color-label">Color peligro</div>
                        <div class="color-value">#e63946</div>
                        <input type="color" class="color-input" value="#e63946">
                    </div>
                </div>

                <div style="margin-top: 2rem;">
                    <label class="form-label">Fondo de página</label>
                    <div style="display: flex; gap: 1rem; align-items: center;">
                        <input type="color" class="color-input" value="#f8f9fa" style="width: 100px; height: 50px;">
                        <span>#f8f9fa</span>
                    </div>
                </div>

                <div style="margin-top: 2rem;">
                    <label class="form-label">Texto principal</label>
                    <div style="display: flex; gap: 1rem; align-items: center;">
                        <input type="color" class="color-input" value="#212529" style="width: 100px; height: 50px;">
                        <span>#212529</span>
                    </div>
                </div>
            </div>

            <!-- Typography Section (hidden initially) -->
            <div class="typography-section" id="typography" style="display: none;">
                <h2 style="margin-bottom: 2rem;">Tipografía</h2>

                <div class="font-grid">
                    <div class="font-card active">
                        <div class="font-sample inter">Aa</div>
                        <div class="font-name">Inter</div>
                        <div class="font-description">Sans-serif moderna, excelente legibilidad</div>
                    </div>
                    <div class="font-card">
                        <div class="font-sample roboto" style="font-family: 'Roboto';">Aa</div>
                        <div class="font-name">Roboto</div>
                        <div class="font-description">Geométrica y versátil, muy popular</div>
                    </div>
                    <div class="font-card">
                        <div class="font-sample montserrat" style="font-family: 'Montserrat';">Aa</div>
                        <div class="font-name">Montserrat</div>
                        <div class="font-description">Urbana y moderna, ideal para títulos</div>
                    </div>
                    <div class="font-card">
                        <div class="font-sample open-sans" style="font-family: 'Open Sans';">Aa</div>
                        <div class="font-name">Open Sans</div>
                        <div class="font-description">Clara y neutral, máxima legibilidad</div>
                    </div>
                </div>

                <div class="slider-group">
                    <div class="slider-label">
                        <span>Tamaño de fuente base</span>
                        <span>16px</span>
                    </div>
                    <input type="range" class="slider" min="12" max="20" value="16">
                </div>

                <div class="slider-group">
                    <div class="slider-label">
                        <span>Espaciado entre líneas</span>
                        <span>1.5</span>
                    </div>
                    <input type="range" class="slider" min="1" max="2.5" step="0.1" value="1.5">
                </div>

                <div class="slider-group">
                    <div class="slider-label">
                        <span>Peso de fuente (regular)</span>
                        <span>400</span>
                    </div>
                    <input type="range" class="slider" min="100" max="900" step="100" value="400">
                </div>
            </div>

            <!-- Layout Section (hidden initially) -->
            <div class="layout-section" id="layout" style="display: none;">
                <h2 style="margin-bottom: 2rem;">Diseño de página</h2>

                <div class="layout-grid">
                    <div class="layout-option">
                        <div class="layout-preview active">
                            <div class="preview-header"></div>
                            <div class="preview-sidebar"></div>
                            <div class="preview-content"></div>
                            <div class="preview-footer"></div>
                        </div>
                        <div class="layout-label">Layout estándar</div>
                    </div>
                    <div class="layout-option">
                        <div class="layout-preview">
                            <div class="preview-header"></div>
                            <div class="preview-content" style="left: 10px;"></div>
                            <div class="preview-footer"></div>
                        </div>
                        <div class="layout-label">Sin sidebar</div>
                    </div>
                    <div class="layout-option">
                        <div class="layout-preview">
                            <div class="preview-header"></div>
                            <div class="preview-grid">
                                <div class="preview-grid-cell"></div>
                                <div class="preview-grid-cell"></div>
                                <div class="preview-grid-cell"></div>
                                <div class="preview-grid-cell"></div>
                            </div>
                            <div class="preview-footer"></div>
                        </div>
                        <div class="layout-label">Grid layout</div>
                    </div>
                </div>

                <div style="margin-top: 2rem;">
                    <label class="form-label">Ancho máximo del contenedor</label>
                    <select class="form-control" style="width: 200px;">
                        <option>1200px (estándar)</option>
                        <option>1400px (ancho)</option>
                        <option>100% (fluido)</option>
                    </select>
                </div>

                <div style="margin-top: 1rem;">
                    <label class="form-label">Espaciado entre elementos</label>
                    <select class="form-control" style="width: 200px;">
                        <option>Compacto</option>
                        <option selected>Normal</option>
                        <option>Amplio</option>
                    </select>
                </div>
            </div>

            <!-- Header Section (hidden initially) -->
            <div class="layout-section" id="header" style="display: none;">
                <h2 style="margin-bottom: 2rem;">Personalizar cabecera</h2>

                <div class="header-builder">
                    <div>
                        <div class="form-group">
                            <label class="form-label">Estilo de cabecera</label>
                            <select class="form-control">
                                <option>Clásica (logo izquierda, menú derecha)</option>
                                <option>Centrada (logo centro, menú abajo)</option>
                                <option>Moderna (logo izquierda, menú hamburguesa)</option>
                                <option>Minimalista (solo logo)</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Altura de cabecera</label>
                            <select class="form-control">
                                <option>Pequeña (60px)</option>
                                <option selected>Normal (80px)</option>
                                <option>Grande (100px)</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Fondo de cabecera</label>
                            <select class="form-control">
                                <option>Transparente</option>
                                <option selected>Blanco</option>
                                <option>Oscuro</option>
                                <option>Color primario</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Posición</label>
                            <div class="radio-group">
                                <label class="radio-label">
                                    <input type="radio" name="headerPosition" checked> Fija
                                </label>
                                <label class="radio-label">
                                    <input type="radio" name="headerPosition"> Estática
                                </label>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="form-label">Vista previa</label>
                        <div class="header-preview">
                            <div class="header-simulated">
                                <div class="logo-simulated"></div>
                                <div class="nav-simulated">
                                    <div class="nav-item-simulated"></div>
                                    <div class="nav-item-simulated"></div>
                                    <div class="nav-item-simulated"></div>
                                </div>
                            </div>
                            <div class="preview-frame">
                                <div style="padding: 1rem; color: #6c757d;">Contenido de la página...</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer Section (hidden initially) -->
            <div class="layout-section" id="footer" style="display: none;">
                <h2 style="margin-bottom: 2rem;">Personalizar pie de página</h2>

                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Número de columnas</label>
                        <select class="form-control">
                            <option>1 columna</option>
                            <option selected>3 columnas</option>
                            <option>4 columnas</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Mostrar redes sociales</label>
                        <div class="toggle-switch">
                            <label class="switch">
                                <input type="checkbox" checked>
                                <span class="slider"></span>
                            </label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Mostrar newsletter</label>
                        <div class="toggle-switch">
                            <label class="switch">
                                <input type="checkbox">
                                <span class="slider"></span>
                            </label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Texto de copyright</label>
                        <input type="text" class="form-control" value="© 2025 NexusBuy. Todos los derechos reservados.">
                    </div>
                </div>
            </div>

            <!-- Custom CSS Section (hidden initially) -->
            <div class="layout-section" id="custom" style="display: none;">
                <h2 style="margin-bottom: 2rem;">CSS personalizado</h2>

                <div class="warning-box">
                    <i class="fas fa-exclamation-triangle"></i>
                    <p>El CSS personalizado se aplicará después de todas las configuraciones. Úsalo con cuidado.</p>
                </div>

                <div class="css-editor">
                    <textarea placeholder="/* Escribe tu CSS personalizado aquí */
:root {
  --custom-color: #4361ee;
}

.custom-class {
  background: var(--custom-color);
}"></textarea>
                </div>

                <div style="margin-top: 1rem;">
                    <button class="btn-secondary">
                        <i class="fas fa-eye"></i> Previsualizar
                    </button>
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
                <button class="btn-success">
                    <i class="fas fa-eye"></i>
                    Previsualizar
                </button>
            </div>
        </main>
    </div>

    <script>
        // Navegación entre secciones
        const navItems = document.querySelectorAll('.visual-nav-item');
        const sections = {
            themes: document.getElementById('themes'),
            colors: document.getElementById('colors'),
            typography: document.getElementById('typography'),
            layout: document.getElementById('layout'),
            header: document.getElementById('header'),
            footer: document.getElementById('footer'),
            custom: document.getElementById('custom')
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
                    sections[sectionId].style.display = 'grid';
                }
            });
        });

        // Theme selection
        document.querySelectorAll('.theme-card').forEach(card => {
            card.addEventListener('click', function() {
                document.querySelectorAll('.theme-card').forEach(c => c.classList.remove('active'));
                this.classList.add('active');
                alert(`Tema seleccionado: ${this.querySelector('h3').textContent} (demo)`);
            });
        });

        // Font selection
        document.querySelectorAll('.font-card').forEach(card => {
            card.addEventListener('click', function() {
                document.querySelectorAll('.font-card').forEach(f => f.classList.remove('active'));
                this.classList.add('active');
                alert(`Tipografía seleccionada: ${this.querySelector('.font-name').textContent} (demo)`);
            });
        });

        // Layout selection
        document.querySelectorAll('.layout-option').forEach(option => {
            option.addEventListener('click', function() {
                document.querySelectorAll('.layout-option .layout-preview').forEach(p => p.classList.remove('active'));
                this.querySelector('.layout-preview').classList.add('active');
                alert(`Layout seleccionado: ${this.querySelector('.layout-label').textContent} (demo)`);
            });
        });

        // Color pickers
        document.querySelectorAll('.color-input').forEach(input => {
            input.addEventListener('change', function() {
                const colorName = this.closest('.color-item').querySelector('.color-label').textContent;
                alert(`${colorName} cambiado a: ${this.value} (demo)`);
            });
        });

        // Sliders
        document.querySelectorAll('.slider').forEach(slider => {
            slider.addEventListener('input', function() {
                const value = this.value;
                const label = this.closest('.slider-group').querySelector('.slider-label span:last-child');
                if (label) {
                    if (this.step === '0.1') {
                        label.textContent = value;
                    } else {
                        label.textContent = value + (this.min === '12' ? 'px' : '');
                    }
                }
            });
        });

        // Save button
        document.querySelector('.btn-primary').addEventListener('click', function() {
            alert('Configuración visual guardada exitosamente (demo)');
        });

        // Preview button
        document.querySelector('.btn-success').addEventListener('click', function() {
            alert('Mostrando vista previa de los cambios (demo)');
        });

        // Cancel button
        document.querySelector('.btn-secondary').addEventListener('click', function() {
            if(confirm('¿Descartar todos los cambios visuales?')) {
                alert('Cambios descartados (demo)');
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