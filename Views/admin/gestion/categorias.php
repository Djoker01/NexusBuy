<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NexusBuy - Gestión de Categorías</title>
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
        .category-stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .category-stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 16px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.02);
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .category-stat-icon {
            width: 56px;
            height: 56px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.75rem;
        }

        .category-stat-icon.blue {
            background: #e1e8ff;
            color: #4361ee;
        }

        .category-stat-icon.green {
            background: #d1f7ea;
            color: #06d6a0;
        }

        .category-stat-icon.purple {
            background: #ead1ff;
            color: #7209b7;
        }

        .category-stat-icon.orange {
            background: #ffead1;
            color: #fb8b24;
        }

        .category-stat-info h3 {
            font-size: 1.75rem;
            font-weight: 700;
            margin-bottom: 0.25rem;
        }

        .category-stat-info p {
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

        /* Categories Tree */
        .categories-container {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .categories-tree {
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.02);
        }

        .tree-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .tree-header h2 {
            font-size: 1.1rem;
            font-weight: 600;
        }

        .tree-actions {
            display: flex;
            gap: 0.5rem;
        }

        .category-list {
            list-style: none;
        }

        .category-item {
            margin-bottom: 0.5rem;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            background: white;
        }

        .category-item.main {
            border-left: 4px solid #4361ee;
        }

        .category-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1rem;
            cursor: pointer;
            transition: background 0.2s;
        }

        .category-header:hover {
            background: #f8f9fa;
        }

        .category-info {
            display: flex;
            align-items: center;
            gap: 1rem;
            flex: 1;
        }

        .category-icon {
            width: 40px;
            height: 40px;
            background: #e1e8ff;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #4361ee;
            font-size: 1.25rem;
        }

        .category-details h4 {
            font-weight: 600;
            font-size: 0.875rem;
            margin-bottom: 0.25rem;
        }

        .category-details p {
            font-size: 0.7rem;
            color: #6c757d;
        }

        .category-stats {
            display: flex;
            align-items: center;
            gap: 1.5rem;
            margin-right: 1rem;
        }

        .stat-tag {
            display: flex;
            align-items: center;
            gap: 0.25rem;
            font-size: 0.7rem;
            color: #6c757d;
        }

        .stat-tag i {
            color: #4361ee;
        }

        .category-actions {
            display: flex;
            gap: 0.5rem;
            opacity: 0;
            transition: opacity 0.2s;
        }

        .category-header:hover .category-actions {
            opacity: 1;
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

        .subcategory-list {
            list-style: none;
            padding-left: 2rem;
            border-left: 2px dashed #e9ecef;
            margin-left: 2rem;
            margin-bottom: 0.5rem;
        }

        .subcategory-item {
            padding: 0.75rem;
            border-bottom: 1px solid #e9ecef;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .subcategory-item:last-child {
            border-bottom: none;
        }

        .subcategory-info {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .subcategory-badge {
            width: 24px;
            height: 24px;
            background: #f1f3f5;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #6c757d;
            font-size: 0.7rem;
        }

        /* Category Form */
        .category-form {
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.02);
        }

        .form-header {
            margin-bottom: 1.5rem;
        }

        .form-header h2 {
            font-size: 1.1rem;
            font-weight: 600;
        }

        .form-group {
            margin-bottom: 1rem;
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

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        .icon-selector {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 0.5rem;
            margin-top: 0.5rem;
        }

        .icon-option {
            aspect-ratio: 1;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            color: #6c757d;
            cursor: pointer;
            transition: all 0.2s;
        }

        .icon-option:hover {
            border-color: #4361ee;
            color: #4361ee;
        }

        .icon-option.selected {
            background: #4361ee;
            border-color: #4361ee;
            color: white;
        }

        .image-upload {
            border: 2px dashed #e9ecef;
            border-radius: 8px;
            padding: 1.5rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s;
        }

        .image-upload:hover {
            border-color: #4361ee;
        }

        .image-upload i {
            font-size: 2rem;
            color: #6c757d;
            margin-bottom: 0.5rem;
        }

        .image-preview {
            width: 100%;
            height: 120px;
            background: #f1f3f5;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 0.5rem;
            overflow: hidden;
        }

        .image-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
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

        .form-actions {
            display: flex;
            gap: 0.5rem;
            margin-top: 1.5rem;
        }

        .btn-save {
            flex: 1;
            background: #4361ee;
            color: white;
            border: none;
            padding: 0.75rem;
            border-radius: 8px;
            font-size: 0.875rem;
            font-weight: 500;
            cursor: pointer;
        }

        .btn-cancel {
            flex: 1;
            background: white;
            border: 1px solid #e9ecef;
            padding: 0.75rem;
            border-radius: 8px;
            font-size: 0.875rem;
            cursor: pointer;
        }

        .btn-delete {
            background: white;
            color: #e63946;
            border: 1px solid #e63946;
            padding: 0.75rem;
            border-radius: 8px;
            font-size: 0.875rem;
            cursor: pointer;
            width: 100%;
        }

        /* Category Detail */
        .category-detail {
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            margin-top: 1.5rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.02);
        }

        .detail-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .detail-header h2 {
            font-size: 1.1rem;
            font-weight: 600;
        }

        .seo-preview {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 8px;
            margin-top: 1rem;
        }

        .seo-url {
            color: #06d6a0;
            font-size: 0.7rem;
            margin-bottom: 0.25rem;
        }

        .seo-title {
            font-weight: 600;
            margin-bottom: 0.25rem;
        }

        .seo-description {
            font-size: 0.7rem;
            color: #6c757d;
        }

        /* Responsive */
        @media (max-width: 1200px) {
            .categories-container {
                grid-template-columns: 1fr;
            }
            
            .category-stats-grid {
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
            
            .category-stats-grid {
                grid-template-columns: 1fr;
            }
            
            .action-bar {
                flex-direction: column;
                align-items: stretch;
            }
            
            .search-box {
                width: 100%;
            }
            
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .subcategory-list {
                padding-left: 1rem;
                margin-left: 1rem;
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
                <a href="#" class="admin-nav-item active">
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
                    <h1>Gestión de Categorías</h1>
                    <p>Organiza el catálogo de productos</p>
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
            <div class="category-stats-grid">
                <div class="category-stat-card">
                    <div class="category-stat-icon blue">
                        <i class="fas fa-tags"></i>
                    </div>
                    <div class="category-stat-info">
                        <h3>24</h3>
                        <p>Categorías principales</p>
                    </div>
                </div>
                <div class="category-stat-card">
                    <div class="category-stat-icon green">
                        <i class="fas fa-sitemap"></i>
                    </div>
                    <div class="category-stat-info">
                        <h3>156</h3>
                        <p>Subcategorías</p>
                    </div>
                </div>
                <div class="category-stat-card">
                    <div class="category-stat-icon purple">
                        <i class="fas fa-box"></i>
                    </div>
                    <div class="category-stat-info">
                        <h3>4,567</h3>
                        <p>Productos categorizados</p>
                    </div>
                </div>
                <div class="category-stat-card">
                    <div class="category-stat-icon orange">
                        <i class="fas fa-eye"></i>
                    </div>
                    <div class="category-stat-info">
                        <h3>18</h3>
                        <p>Activas en menú</p>
                    </div>
                </div>
            </div>

            <!-- Action Bar -->
            <div class="action-bar">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Buscar categoría...">
                </div>
                <button class="btn-primary" onclick="openCategoryModal()">
                    <i class="fas fa-plus"></i>
                    Nueva categoría
                </button>
            </div>

            <!-- Categories Container -->
            <div class="categories-container">
                <!-- Categories Tree -->
                <div class="categories-tree">
                    <div class="tree-header">
                        <h2>Estructura de categorías</h2>
                        <div class="tree-actions">
                            <button class="btn-secondary" style="padding: 0.25rem 0.5rem;">
                                <i class="fas fa-arrow-up"></i>
                            </button>
                            <button class="btn-secondary" style="padding: 0.25rem 0.5rem;">
                                <i class="fas fa-arrow-down"></i>
                            </button>
                            <button class="btn-secondary" style="padding: 0.25rem 0.5rem;">
                                <i class="fas fa-sync-alt"></i>
                            </button>
                        </div>
                    </div>

                    <ul class="category-list">
                        <!-- Ropa -->
                        <li class="category-item main">
                            <div class="category-header" onclick="toggleSubcategories(this)">
                                <div class="category-info">
                                    <div class="category-icon">
                                        <i class="fas fa-tshirt"></i>
                                    </div>
                                    <div class="category-details">
                                        <h4>Ropa</h4>
                                        <p>Creado: 15/01/2025</p>
                                    </div>
                                </div>
                                <div class="category-stats">
                                    <span class="stat-tag">
                                        <i class="fas fa-box"></i> 1,234
                                    </span>
                                    <span class="stat-tag">
                                        <i class="fas fa-store"></i> 45
                                    </span>
                                </div>
                                <div class="category-actions">
                                    <button class="action-btn" onclick="editCategory('Ropa')"><i class="fas fa-edit"></i></button>
                                    <button class="action-btn" onclick="addSubcategory('Ropa')"><i class="fas fa-plus"></i></button>
                                    <button class="action-btn delete" onclick="deleteCategory('Ropa')"><i class="fas fa-trash"></i></button>
                                </div>
                            </div>
                            <ul class="subcategory-list" style="display: none;">
                                <li class="subcategory-item">
                                    <div class="subcategory-info">
                                        <span class="subcategory-badge"><i class="fas fa-tshirt"></i></span>
                                        <span>Camisetas</span>
                                    </div>
                                    <div class="category-actions">
                                        <button class="action-btn"><i class="fas fa-edit"></i></button>
                                        <button class="action-btn delete"><i class="fas fa-trash"></i></button>
                                    </div>
                                </li>
                                <li class="subcategory-item">
                                    <div class="subcategory-info">
                                        <span class="subcategory-badge"><i class="fas fa-tshirt"></i></span>
                                        <span>Pantalones</span>
                                    </div>
                                    <div class="category-actions">
                                        <button class="action-btn"><i class="fas fa-edit"></i></button>
                                        <button class="action-btn delete"><i class="fas fa-trash"></i></button>
                                    </div>
                                </li>
                                <li class="subcategory-item">
                                    <div class="subcategory-info">
                                        <span class="subcategory-badge"><i class="fas fa-tshirt"></i></span>
                                        <span>Sudaderas</span>
                                    </div>
                                    <div class="category-actions">
                                        <button class="action-btn"><i class="fas fa-edit"></i></button>
                                        <button class="action-btn delete"><i class="fas fa-trash"></i></button>
                                    </div>
                                </li>
                                <li class="subcategory-item">
                                    <div class="subcategory-info">
                                        <span class="subcategory-badge"><i class="fas fa-tshirt"></i></span>
                                        <span>Abrigos</span>
                                    </div>
                                    <div class="category-actions">
                                        <button class="action-btn"><i class="fas fa-edit"></i></button>
                                        <button class="action-btn delete"><i class="fas fa-trash"></i></button>
                                    </div>
                                </li>
                            </ul>
                        </li>

                        <!-- Electrónica -->
                        <li class="category-item main">
                            <div class="category-header" onclick="toggleSubcategories(this)">
                                <div class="category-info">
                                    <div class="category-icon">
                                        <i class="fas fa-laptop"></i>
                                    </div>
                                    <div class="category-details">
                                        <h4>Electrónica</h4>
                                        <p>Creado: 20/01/2025</p>
                                    </div>
                                </div>
                                <div class="category-stats">
                                    <span class="stat-tag">
                                        <i class="fas fa-box"></i> 892
                                    </span>
                                    <span class="stat-tag">
                                        <i class="fas fa-store"></i> 32
                                    </span>
                                </div>
                                <div class="category-actions">
                                    <button class="action-btn"><i class="fas fa-edit"></i></button>
                                    <button class="action-btn"><i class="fas fa-plus"></i></button>
                                    <button class="action-btn delete"><i class="fas fa-trash"></i></button>
                                </div>
                            </div>
                            <ul class="subcategory-list" style="display: none;">
                                <li class="subcategory-item">
                                    <div class="subcategory-info">
                                        <span class="subcategory-badge"><i class="fas fa-mobile-alt"></i></span>
                                        <span>Teléfonos</span>
                                    </div>
                                    <div class="category-actions">
                                        <button class="action-btn"><i class="fas fa-edit"></i></button>
                                        <button class="action-btn delete"><i class="fas fa-trash"></i></button>
                                    </div>
                                </li>
                                <li class="subcategory-item">
                                    <div class="subcategory-info">
                                        <span class="subcategory-badge"><i class="fas fa-laptop"></i></span>
                                        <span>Computadoras</span>
                                    </div>
                                    <div class="category-actions">
                                        <button class="action-btn"><i class="fas fa-edit"></i></button>
                                        <button class="action-btn delete"><i class="fas fa-trash"></i></button>
                                    </div>
                                </li>
                                <li class="subcategory-item">
                                    <div class="subcategory-info">
                                        <span class="subcategory-badge"><i class="fas fa-headphones"></i></span>
                                        <span>Auriculares</span>
                                    </div>
                                    <div class="category-actions">
                                        <button class="action-btn"><i class="fas fa-edit"></i></button>
                                        <button class="action-btn delete"><i class="fas fa-trash"></i></button>
                                    </div>
                                </li>
                            </ul>
                        </li>

                        <!-- Calzado -->
                        <li class="category-item main">
                            <div class="category-header" onclick="toggleSubcategories(this)">
                                <div class="category-info">
                                    <div class="category-icon">
                                        <i class="fas fa-shoe-prints"></i>
                                    </div>
                                    <div class="category-details">
                                        <h4>Calzado</h4>
                                        <p>Creado: 25/01/2025</p>
                                    </div>
                                </div>
                                <div class="category-stats">
                                    <span class="stat-tag">
                                        <i class="fas fa-box"></i> 567
                                    </span>
                                    <span class="stat-tag">
                                        <i class="fas fa-store"></i> 28
                                    </span>
                                </div>
                                <div class="category-actions">
                                    <button class="action-btn"><i class="fas fa-edit"></i></button>
                                    <button class="action-btn"><i class="fas fa-plus"></i></button>
                                    <button class="action-btn delete"><i class="fas fa-trash"></i></button>
                                </div>
                            </div>
                            <ul class="subcategory-list" style="display: none;">
                                <li class="subcategory-item">
                                    <div class="subcategory-info">
                                        <span class="subcategory-badge"><i class="fas fa-shoe-prints"></i></span>
                                        <span>Zapatillas</span>
                                    </div>
                                    <div class="category-actions">
                                        <button class="action-btn"><i class="fas fa-edit"></i></button>
                                        <button class="action-btn delete"><i class="fas fa-trash"></i></button>
                                    </div>
                                </li>
                                <li class="subcategory-item">
                                    <div class="subcategory-info">
                                        <span class="subcategory-badge"><i class="fas fa-shoe-prints"></i></span>
                                        <span>Botas</span>
                                    </div>
                                    <div class="category-actions">
                                        <button class="action-btn"><i class="fas fa-edit"></i></button>
                                        <button class="action-btn delete"><i class="fas fa-trash"></i></button>
                                    </div>
                                </li>
                                <li class="subcategory-item">
                                    <div class="subcategory-info">
                                        <span class="subcategory-badge"><i class="fas fa-shoe-prints"></i></span>
                                        <span>Zapatos formales</span>
                                    </div>
                                    <div class="category-actions">
                                        <button class="action-btn"><i class="fas fa-edit"></i></button>
                                        <button class="action-btn delete"><i class="fas fa-trash"></i></button>
                                    </div>
                                </li>
                            </ul>
                        </li>

                        <!-- Accesorios -->
                        <li class="category-item main">
                            <div class="category-header" onclick="toggleSubcategories(this)">
                                <div class="category-info">
                                    <div class="category-icon">
                                        <i class="fas fa-clock"></i>
                                    </div>
                                    <div class="category-details">
                                        <h4>Accesorios</h4>
                                        <p>Creado: 30/01/2025</p>
                                    </div>
                                </div>
                                <div class="category-stats">
                                    <span class="stat-tag">
                                        <i class="fas fa-box"></i> 890
                                    </span>
                                    <span class="stat-tag">
                                        <i class="fas fa-store"></i> 34
                                    </span>
                                </div>
                                <div class="category-actions">
                                    <button class="action-btn"><i class="fas fa-edit"></i></button>
                                    <button class="action-btn"><i class="fas fa-plus"></i></button>
                                    <button class="action-btn delete"><i class="fas fa-trash"></i></button>
                                </div>
                            </div>
                            <ul class="subcategory-list" style="display: none;">
                                <li class="subcategory-item">
                                    <div class="subcategory-info">
                                        <span class="subcategory-badge"><i class="fas fa-hat-cowboy"></i></span>
                                        <span>Gorras</span>
                                    </div>
                                    <div class="category-actions">
                                        <button class="action-btn"><i class="fas fa-edit"></i></button>
                                        <button class="action-btn delete"><i class="fas fa-trash"></i></button>
                                    </div>
                                </li>
                                <li class="subcategory-item">
                                    <div class="subcategory-info">
                                        <span class="subcategory-badge"><i class="fas fa-glasses"></i></span>
                                        <span>Gafas</span>
                                    </div>
                                    <div class="category-actions">
                                        <button class="action-btn"><i class="fas fa-edit"></i></button>
                                        <button class="action-btn delete"><i class="fas fa-trash"></i></button>
                                    </div>
                                </li>
                                <li class="subcategory-item">
                                    <div class="subcategory-info">
                                        <span class="subcategory-badge"><i class="fas fa-clock"></i></span>
                                        <span>Relojes</span>
                                    </div>
                                    <div class="category-actions">
                                        <button class="action-btn"><i class="fas fa-edit"></i></button>
                                        <button class="action-btn delete"><i class="fas fa-trash"></i></button>
                                    </div>
                                </li>
                                <li class="subcategory-item">
                                    <div class="subcategory-info">
                                        <span class="subcategory-badge"><i class="fas fa-gem"></i></span>
                                        <span>Joyería</span>
                                    </div>
                                    <div class="category-actions">
                                        <button class="action-btn"><i class="fas fa-edit"></i></button>
                                        <button class="action-btn delete"><i class="fas fa-trash"></i></button>
                                    </div>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </div>

                <!-- Category Form -->
                <div class="category-form" id="categoryForm">
                    <div class="form-header">
                        <h2 id="formTitle">Nueva categoría</h2>
                    </div>

                    <form>
                        <div class="form-group">
                            <label class="form-label">Nombre de la categoría *</label>
                            <input type="text" class="form-control" placeholder="Ej: Ropa, Electrónica...">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Categoría padre</label>
                            <select class="form-control">
                                <option value="">-- Ninguna (categoría principal) --</option>
                                <option>Ropa</option>
                                <option>Electrónica</option>
                                <option>Calzado</option>
                                <option>Accesorios</option>
                            </select>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Icono</label>
                                <div class="icon-selector">
                                    <div class="icon-option selected"><i class="fas fa-tshirt"></i></div>
                                    <div class="icon-option"><i class="fas fa-laptop"></i></div>
                                    <div class="icon-option"><i class="fas fa-shoe-prints"></i></div>
                                    <div class="icon-option"><i class="fas fa-clock"></i></div>
                                    <div class="icon-option"><i class="fas fa-mobile-alt"></i></div>
                                    <div class="icon-option"><i class="fas fa-headphones"></i></div>
                                    <div class="icon-option"><i class="fas fa-camera"></i></div>
                                    <div class="icon-option"><i class="fas fa-book"></i></div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Imagen (opcional)</label>
                                <div class="image-upload">
                                    <div class="image-preview">
                                        <i class="fas fa-cloud-upload-alt"></i>
                                    </div>
                                    <p style="font-size: 0.7rem; color: #6c757d;">Click para subir imagen</p>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Descripción</label>
                            <textarea class="form-control" rows="3" placeholder="Breve descripción de la categoría..."></textarea>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Orden</label>
                                <input type="number" class="form-control" value="1" min="0">
                            </div>

                            <div class="form-group">
                                <label class="form-label">Estado</label>
                                <div class="toggle-switch">
                                    <span>Activa</span>
                                    <label class="switch">
                                        <input type="checkbox" checked>
                                        <span class="slider"></span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Mostrar en menú principal</label>
                            <div class="toggle-switch">
                                <span>Visible</span>
                                <label class="switch">
                                    <input type="checkbox" checked>
                                    <span class="slider"></span>
                                </label>
                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="button" class="btn-save" onclick="saveCategory()">Guardar categoría</button>
                            <button type="button" class="btn-cancel" onclick="resetForm()">Cancelar</button>
                        </div>
                    </form>

                    <!-- SEO Preview -->
                    <div class="seo-preview">
                        <h4 style="margin-bottom: 0.5rem;">Vista previa SEO</h4>
                        <div class="seo-url">nexusbuy.com/categoria/ropa</div>
                        <div class="seo-title">Ropa - NexusBuy</div>
                        <div class="seo-description">Encuentra la mejor ropa en NexusBuy. Camisetas, pantalones, sudaderas y más de las mejores tiendas.</div>
                    </div>

                    <!-- Delete Button (solo en edición) -->
                    <button type="button" class="btn-delete" style="margin-top: 1rem; display: none;" id="deleteBtn">
                        <i class="fas fa-trash"></i> Eliminar categoría
                    </button>
                </div>
            </div>

            <!-- Category Detail (visible al hacer clic en una categoría) -->
            <div class="category-detail" id="categoryDetail" style="display: none;">
                <div class="detail-header">
                    <h2>Detalle de categoría: Ropa</h2>
                    <button class="btn-secondary" onclick="hideDetail()">
                        <i class="fas fa-times"></i> Cerrar
                    </button>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Total productos</label>
                        <div style="font-size: 2rem; font-weight: 700; color: #4361ee;">1,234</div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Tiendas activas</label>
                        <div style="font-size: 2rem; font-weight: 700; color: #4361ee;">45</div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Ventas del mes</label>
                        <div style="font-size: 2rem; font-weight: 700; color: #4361ee;">$45,670</div>
                    </div>
                </div>

                <div style="margin-top: 1rem;">
                    <h4>Subcategorías</h4>
                    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 0.5rem; margin-top: 1rem;">
                        <div style="background: #f8f9fa; padding: 0.5rem; border-radius: 4px;">Camisetas (456 productos)</div>
                        <div style="background: #f8f9fa; padding: 0.5rem; border-radius: 4px;">Pantalones (345 productos)</div>
                        <div style="background: #f8f9fa; padding: 0.5rem; border-radius: 4px;">Sudaderas (234 productos)</div>
                        <div style="background: #f8f9fa; padding: 0.5rem; border-radius: 4px;">Abrigos (199 productos)</div>
                    </div>
                </div>

                <div style="margin-top: 1.5rem;">
                    <button class="btn-primary" style="margin-right: 0.5rem;">
                        <i class="fas fa-edit"></i> Editar
                    </button>
                    <button class="btn-danger">
                        <i class="fas fa-trash"></i> Eliminar
                    </button>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Toggle subcategories visibility
        function toggleSubcategories(element) {
            const sublist = element.nextElementSibling;
            if (sublist && sublist.classList.contains('subcategory-list')) {
                if (sublist.style.display === 'none') {
                    sublist.style.display = 'block';
                } else {
                    sublist.style.display = 'none';
                }
            }
        }

        // Category form functions
        function openCategoryModal() {
            document.getElementById('formTitle').textContent = 'Nueva categoría';
            document.getElementById('deleteBtn').style.display = 'none';
            document.getElementById('categoryDetail').style.display = 'none';
            // Reset form fields
            document.querySelector('.category-form form').reset();
        }

        function editCategory(categoryName) {
            document.getElementById('formTitle').textContent = 'Editar categoría: ' + categoryName;
            document.getElementById('deleteBtn').style.display = 'block';
            document.getElementById('categoryDetail').style.display = 'none';
            // Simular carga de datos
            alert('Cargando datos de ' + categoryName + ' (demo)');
        }

        function addSubcategory(parentCategory) {
            document.getElementById('formTitle').textContent = 'Nueva subcategoría en ' + parentCategory;
            document.getElementById('deleteBtn').style.display = 'none';
            document.getElementById('categoryDetail').style.display = 'none';
            // Preseleccionar categoría padre
            alert('Formulario listo para crear subcategoría en ' + parentCategory + ' (demo)');
        }

        function deleteCategory(categoryName) {
            if(confirm(`¿Estás seguro de eliminar la categoría "${categoryName}"?`)) {
                alert('Categoría eliminada (demo)');
            }
        }

        function saveCategory() {
            alert('Categoría guardada (demo)');
        }

        function resetForm() {
            if(confirm('¿Descartar cambios?')) {
                document.querySelector('.category-form form').reset();
                document.getElementById('formTitle').textContent = 'Nueva categoría';
                document.getElementById('deleteBtn').style.display = 'none';
            }
        }

        // Category detail
        function showDetail(categoryName) {
            document.getElementById('categoryDetail').style.display = 'block';
            document.getElementById('categoryDetail').querySelector('h2').textContent = 'Detalle de categoría: ' + categoryName;
        }

        function hideDetail() {
            document.getElementById('categoryDetail').style.display = 'none';
        }

        // Icon selector
        document.querySelectorAll('.icon-option').forEach(icon => {
            icon.addEventListener('click', function() {
                document.querySelectorAll('.icon-option').forEach(i => i.classList.remove('selected'));
                this.classList.add('selected');
            });
        });

        // Image upload
        document.querySelector('.image-upload').addEventListener('click', function() {
            alert('Selector de imágenes (demo)');
        });

        // Search
        document.querySelector('.search-box input').addEventListener('keyup', function(e) {
            if(e.key === 'Enter') {
                alert('Buscando: ' + this.value + ' (demo)');
            }
        });

        // Action buttons in tree
        document.querySelectorAll('.action-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                // Las acciones específicas ya están en los onclick
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