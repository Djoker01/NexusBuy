<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NexusBuy - Gestión de Pedidos</title>
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
        .order-stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .order-stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 16px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.02);
            display: flex;
            align-items: center;
            gap: 1rem;
            border-left: 4px solid;
        }

        .order-stat-card.blue {
            border-left-color: #4361ee;
        }

        .order-stat-card.green {
            border-left-color: #06d6a0;
        }

        .order-stat-card.yellow {
            border-left-color: #ffb703;
        }

        .order-stat-card.purple {
            border-left-color: #7209b7;
        }

        .order-stat-icon {
            width: 56px;
            height: 56px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.75rem;
        }

        .order-stat-icon.blue {
            background: #e1e8ff;
            color: #4361ee;
        }

        .order-stat-icon.green {
            background: #d1f7ea;
            color: #06d6a0;
        }

        .order-stat-icon.yellow {
            background: #fff3d1;
            color: #ffb703;
        }

        .order-stat-icon.purple {
            background: #ead1ff;
            color: #7209b7;
        }

        .order-stat-info h3 {
            font-size: 1.75rem;
            font-weight: 700;
            margin-bottom: 0.25rem;
        }

        .order-stat-info p {
            color: #6c757d;
            font-size: 0.875rem;
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

        /* Orders Table */
        .orders-table-container {
            background: white;
            border-radius: 16px;
            border: 1px solid #e9ecef;
            overflow: auto;
            margin-bottom: 1.5rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.02);
        }

        .orders-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 1400px;
        }

        .orders-table th {
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

        .orders-table td {
            padding: 1rem;
            border-bottom: 1px solid #e9ecef;
            font-size: 0.875rem;
        }

        .order-id {
            font-weight: 600;
            color: #4361ee;
        }

        .store-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            padding: 0.25rem 0.5rem;
            background: #e1e8ff;
            color: #4361ee;
            border-radius: 4px;
            font-size: 0.7rem;
        }

        .customer-cell {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .customer-avatar {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            background: #e1e8ff;
            color: #4361ee;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 0.75rem;
        }

        .customer-info h4 {
            font-weight: 600;
            font-size: 0.875rem;
            margin-bottom: 0.25rem;
        }

        .customer-info span {
            font-size: 0.7rem;
            color: #6c757d;
        }

        .products-count {
            text-align: center;
            font-weight: 500;
        }

        .order-total {
            font-weight: 600;
            color: #4361ee;
        }

        .order-total small {
            font-weight: normal;
            color: #6c757d;
            font-size: 0.7rem;
            margin-left: 0.25rem;
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
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            padding: 0.25rem 0.75rem;
            border-radius: 999px;
            font-size: 0.7rem;
            font-weight: 500;
        }

        .status-badge.pending {
            background: #fff3d1;
            color: #ffb703;
        }

        .status-badge.confirmed {
            background: #d1f7ea;
            color: #06d6a0;
        }

        .status-badge.shipped {
            background: #e1e8ff;
            color: #4361ee;
        }

        .status-badge.delivered {
            background: #e9ecef;
            color: #6c757d;
        }

        .status-badge.cancelled {
            background: #ffe5e5;
            color: #e63946;
        }

        .payment-method {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.7rem;
            color: #6c757d;
        }

        .payment-method i {
            width: 16px;
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
            max-width: 900px;
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

        /* Order Detail */
        .order-detail-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid #e9ecef;
        }

        .order-detail-id {
            font-size: 1.5rem;
            font-weight: 700;
            color: #4361ee;
        }

        .order-detail-date {
            color: #6c757d;
            font-size: 0.875rem;
        }

        .order-timeline {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin: 2rem 0;
            position: relative;
        }

        .timeline-step {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            position: relative;
            z-index: 1;
            flex: 1;
        }

        .timeline-icon {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: white;
            border: 2px solid #e9ecef;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 0.5rem;
            color: #6c757d;
        }

        .timeline-step.completed .timeline-icon {
            background: #06d6a0;
            border-color: #06d6a0;
            color: white;
        }

        .timeline-step.active .timeline-icon {
            background: #4361ee;
            border-color: #4361ee;
            color: white;
        }

        .timeline-label {
            font-size: 0.75rem;
            font-weight: 500;
        }

        .timeline-date {
            font-size: 0.7rem;
            color: #6c757d;
        }

        .timeline-progress {
            position: absolute;
            top: 24px;
            left: 0;
            right: 0;
            height: 2px;
            background: #e9ecef;
            z-index: 0;
        }

        .timeline-progress-fill {
            height: 100%;
            background: #4361ee;
            width: 75%;
        }

        .detail-grid-2 {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .info-card {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 12px;
        }

        .info-card h4 {
            font-size: 0.875rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: #1a2639;
        }

        .info-row {
            display: flex;
            margin-bottom: 0.75rem;
            font-size: 0.875rem;
        }

        .info-label {
            width: 100px;
            color: #6c757d;
        }

        .info-value {
            flex: 1;
            font-weight: 500;
        }

        .products-table-detail {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 1.5rem;
        }

        .products-table-detail th {
            text-align: left;
            padding: 0.75rem;
            background: #f8f9fa;
            color: #6c757d;
            font-weight: 500;
            font-size: 0.7rem;
            text-transform: uppercase;
            border-bottom: 1px solid #e9ecef;
        }

        .products-table-detail td {
            padding: 0.75rem;
            border-bottom: 1px solid #e9ecef;
            font-size: 0.875rem;
        }

        .product-detail-cell {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .product-detail-image {
            width: 40px;
            height: 40px;
            background: #f1f3f5;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #6c757d;
        }

        .summary-box {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 12px;
            margin-top: 1rem;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.75rem;
            font-size: 0.875rem;
        }

        .summary-row.total {
            font-weight: 700;
            font-size: 1.125rem;
            border-top: 1px solid #e9ecef;
            padding-top: 0.75rem;
            margin-top: 0.75rem;
        }

        .tracking-info {
            display: flex;
            gap: 1rem;
            align-items: center;
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 8px;
        }

        .tracking-input {
            flex: 1;
            padding: 0.75rem;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            font-size: 0.875rem;
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
            .order-stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .detail-grid-2 {
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
            
            .order-stats-grid {
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
            
            .date-range-selector {
                flex-direction: column;
                width: 100%;
            }
            
            .date-preset {
                width: 100%;
                justify-content: space-between;
            }
            
            .timeline-step .timeline-label {
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
                <a href="#" class="admin-nav-item active">
                    <i class="fas fa-shopping-bag"></i>
                    Pedidos
                    <span class="badge-admin">12</span>
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
                    <h1>Gestión de Pedidos</h1>
                    <p>Administra todos los pedidos del sistema</p>
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
            <div class="order-stats-grid">
                <div class="order-stat-card blue">
                    <div class="order-stat-icon blue">
                        <i class="fas fa-shopping-bag"></i>
                    </div>
                    <div class="order-stat-info">
                        <h3>1,234</h3>
                        <p>Pedidos totales</p>
                    </div>
                </div>
                <div class="order-stat-card green">
                    <div class="order-stat-icon green">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="order-stat-info">
                        <h3>89</h3>
                        <p>Completados hoy</p>
                    </div>
                </div>
                <div class="order-stat-card yellow">
                    <div class="order-stat-icon yellow">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="order-stat-info">
                        <h3>23</h3>
                        <p>Pendientes</p>
                    </div>
                </div>
                <div class="order-stat-card purple">
                    <div class="order-stat-icon purple">
                        <i class="fas fa-truck"></i>
                    </div>
                    <div class="order-stat-info">
                        <h3>45</h3>
                        <p>En envio</p>
                    </div>
                </div>
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
                <div class="export-btn">
                    <i class="fas fa-download"></i>
                    Exportar reporte
                </div>
            </div>

            <!-- Action Bar -->
            <div class="action-bar">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Buscar por ID, cliente o tienda...">
                </div>
                <div class="filter-group">
                    <select class="filter-select">
                        <option>Todos los estados</option>
                        <option>Pendientes</option>
                        <option>Confirmados</option>
                        <option>Enviados</option>
                        <option>Entregados</option>
                        <option>Cancelados</option>
                    </select>
                    <select class="filter-select">
                        <option>Todas las tiendas</option>
                        <option>Nexus Fashion</option>
                        <option>Tech Store</option>
                        <option>Deportes Cuba</option>
                    </select>
                    <select class="filter-select">
                        <option>Método de pago</option>
                        <option>Transfermóvil</option>
                        <option>Efectivo</option>
                        <option>Tarjeta</option>
                    </select>
                    <button class="btn-primary">
                        <i class="fas fa-filter"></i>
                        Filtrar
                    </button>
                </div>
            </div>

            <!-- Orders Table -->
            <div class="orders-table-container">
                <table class="orders-table">
                    <thead>
                        <tr>
                            <th>ID Pedido</th>
                            <th>Tienda</th>
                            <th>Cliente</th>
                            <th>Fecha</th>
                            <th>Productos</th>
                            <th>Total</th>
                            <th>Comisión</th>
                            <th>Estado</th>
                            <th>Pago</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <span class="order-id">#2345</span>
                            </td>
                            <td>
                                <span class="store-badge">
                                    <i class="fas fa-store"></i>
                                    Nexus Fashion
                                </span>
                            </td>
                            <td>
                                <div class="customer-cell">
                                    <div class="customer-avatar">JP</div>
                                    <div class="customer-info">
                                        <h4>Juan Pérez</h4>
                                        <span>juan@email.com</span>
                                    </div>
                                </div>
                            </td>
                            <td>15/03/2025</td>
                            <td class="products-count">2</td>
                            <td>
                                <div class="order-total">
                                    $4,100 CUP
                                    <br>
                                    <small>$117 USD</small>
                                </div>
                            </td>
                            <td>
                                <span class="commission-badge">$410 CUP</span>
                            </td>
                            <td>
                                <span class="status-badge pending">
                                    <i class="fas fa-clock"></i>
                                    Pendiente
                                </span>
                            </td>
                            <td>
                                <div class="payment-method">
                                    <i class="fas fa-mobile-alt"></i>
                                    Transfermóvil
                                </div>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <button class="action-btn" onclick="viewOrderDetails('2345')"><i class="fas fa-eye"></i></button>
                                    <button class="action-btn"><i class="fas fa-edit"></i></button>
                                    <button class="action-btn"><i class="fas fa-print"></i></button>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <span class="order-id">#2344</span>
                            </td>
                            <td>
                                <span class="store-badge">
                                    <i class="fas fa-store"></i>
                                    Tech Store
                                </span>
                            </td>
                            <td>
                                <div class="customer-cell">
                                    <div class="customer-avatar">MG</div>
                                    <div class="customer-info">
                                        <h4>María Gómez</h4>
                                        <span>maria@email.com</span>
                                    </div>
                                </div>
                            </td>
                            <td>15/03/2025</td>
                            <td class="products-count">3</td>
                            <td>
                                <div class="order-total">
                                    $3,450 CUP
                                    <br>
                                    <small>$99 USD</small>
                                </div>
                            </td>
                            <td>
                                <span class="commission-badge">$345 CUP</span>
                            </td>
                            <td>
                                <span class="status-badge confirmed">
                                    <i class="fas fa-check-circle"></i>
                                    Confirmado
                                </span>
                            </td>
                            <td>
                                <div class="payment-method">
                                    <i class="fas fa-credit-card"></i>
                                    Tarjeta
                                </div>
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
                            <td>
                                <span class="order-id">#2343</span>
                            </td>
                            <td>
                                <span class="store-badge">
                                    <i class="fas fa-store"></i>
                                    Deportes Cuba
                                </span>
                            </td>
                            <td>
                                <div class="customer-cell">
                                    <div class="customer-avatar">CR</div>
                                    <div class="customer-info">
                                        <h4>Carlos Ruiz</h4>
                                        <span>carlos@email.com</span>
                                    </div>
                                </div>
                            </td>
                            <td>14/03/2025</td>
                            <td class="products-count">1</td>
                            <td>
                                <div class="order-total">
                                    $5,200 CUP
                                    <br>
                                    <small>$149 USD</small>
                                </div>
                            </td>
                            <td>
                                <span class="commission-badge">$520 CUP</span>
                            </td>
                            <td>
                                <span class="status-badge shipped">
                                    <i class="fas fa-truck"></i>
                                    Enviado
                                </span>
                            </td>
                            <td>
                                <div class="payment-method">
                                    <i class="fas fa-university"></i>
                                    Transferencia
                                </div>
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
                            <td>
                                <span class="order-id">#2342</span>
                            </td>
                            <td>
                                <span class="store-badge">
                                    <i class="fas fa-store"></i>
                                    Fashion Store
                                </span>
                            </td>
                            <td>
                                <div class="customer-cell">
                                    <div class="customer-avatar">AL</div>
                                    <div class="customer-info">
                                        <h4>Ana López</h4>
                                        <span>ana@email.com</span>
                                    </div>
                                </div>
                            </td>
                            <td>14/03/2025</td>
                            <td class="products-count">2</td>
                            <td>
                                <div class="order-total">
                                    $890 CUP
                                    <br>
                                    <small>$25 USD</small>
                                </div>
                            </td>
                            <td>
                                <span class="commission-badge">$89 CUP</span>
                            </td>
                            <td>
                                <span class="status-badge delivered">
                                    <i class="fas fa-check-double"></i>
                                    Entregado
                                </span>
                            </td>
                            <td>
                                <div class="payment-method">
                                    <i class="fas fa-money-bill"></i>
                                    Efectivo
                                </div>
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
                            <td>
                                <span class="order-id">#2341</span>
                            </td>
                            <td>
                                <span class="store-badge">
                                    <i class="fas fa-store"></i>
                                    Móvil Store
                                </span>
                            </td>
                            <td>
                                <div class="customer-cell">
                                    <div class="customer-avatar">PM</div>
                                    <div class="customer-info">
                                        <h4>Pedro Méndez</h4>
                                        <span>pedro@email.com</span>
                                    </div>
                                </div>
                            </td>
                            <td>13/03/2025</td>
                            <td class="products-count">1</td>
                            <td>
                                <div class="order-total">
                                    $1,500 CUP
                                    <br>
                                    <small>$43 USD</small>
                                </div>
                            </td>
                            <td>
                                <span class="commission-badge">$150 CUP</span>
                            </td>
                            <td>
                                <span class="status-badge cancelled">
                                    <i class="fas fa-times-circle"></i>
                                    Cancelado
                                </span>
                            </td>
                            <td>
                                <div class="payment-method">
                                    <i class="fas fa-mobile-alt"></i>
                                    EnZona
                                </div>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <button class="action-btn"><i class="fas fa-eye"></i></button>
                                    <button class="action-btn"><i class="fas fa-edit"></i></button>
                                    <button class="action-btn"><i class="fas fa-print"></i></button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="pagination">
                <div class="pagination-info">
                    Mostrando 5 de 1,234 pedidos
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

    <!-- Modal Detalle de Pedido -->
    <div class="modal-overlay" id="orderDetailModal">
        <div class="modal">
            <div class="modal-header">
                <h2>Detalle del Pedido</h2>
                <button class="modal-close" onclick="closeOrderDetailModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <div class="order-detail-header">
                    <div>
                        <div class="order-detail-id">#2345</div>
                        <div class="order-detail-date">Realizado el 15/03/2025 a las 10:30</div>
                    </div>
                    <span class="status-badge pending">Pendiente</span>
                </div>

                <!-- Timeline -->
                <div class="order-timeline">
                    <div class="timeline-progress">
                        <div class="timeline-progress-fill" style="width: 25%;"></div>
                    </div>
                    <div class="timeline-step completed">
                        <div class="timeline-icon">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <span class="timeline-label">Pedido</span>
                        <span class="timeline-date">15/03 10:30</span>
                    </div>
                    <div class="timeline-step completed">
                        <div class="timeline-icon">
                            <i class="fas fa-credit-card"></i>
                        </div>
                        <span class="timeline-label">Pago</span>
                        <span class="timeline-date">15/03 10:35</span>
                    </div>
                    <div class="timeline-step active">
                        <div class="timeline-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <span class="timeline-label">Confirmar</span>
                        <span class="timeline-date">Pendiente</span>
                    </div>
                    <div class="timeline-step">
                        <div class="timeline-icon">
                            <i class="fas fa-truck"></i>
                        </div>
                        <span class="timeline-label">Enviar</span>
                        <span class="timeline-date">-</span>
                    </div>
                    <div class="timeline-step">
                        <div class="timeline-icon">
                            <i class="fas fa-check-double"></i>
                        </div>
                        <span class="timeline-label">Entregar</span>
                        <span class="timeline-date">-</span>
                    </div>
                </div>

                <!-- Información de cliente y tienda -->
                <div class="detail-grid-2">
                    <div class="info-card">
                        <h4>Información del cliente</h4>
                        <div class="info-row">
                            <span class="info-label">Nombre:</span>
                            <span class="info-value">Juan Pérez Rodríguez</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Email:</span>
                            <span class="info-value">juan.perez@email.com</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Teléfono:</span>
                            <span class="info-value">+53 5 1234567</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Dirección:</span>
                            <span class="info-value">Calle 123 #456, Vedado, La Habana</span>
                        </div>
                    </div>

                    <div class="info-card">
                        <h4>Información de la tienda</h4>
                        <div class="info-row">
                            <span class="info-label">Tienda:</span>
                            <span class="info-value">Nexus Fashion</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Vendedor:</span>
                            <span class="info-value">María Pérez</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Email tienda:</span>
                            <span class="info-value">maria@nexusfashion.com</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Teléfono:</span>
                            <span class="info-value">+53 5 7654321</span>
                        </div>
                    </div>
                </div>

                <!-- Productos -->
                <h4 style="margin-bottom: 1rem;">Productos</h4>
                <table class="products-table-detail">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Cantidad</th>
                            <th>Precio</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <div class="product-detail-cell">
                                    <div class="product-detail-image">
                                        <i class="fas fa-tshirt"></i>
                                    </div>
                                    <div>
                                        <strong>Camiseta Oversize Negra</strong>
                                        <br>
                                        <span style="color: #6c757d; font-size: 0.75rem;">Talla L</span>
                                    </div>
                                </div>
                            </td>
                            <td>2</td>
                            <td>$1,300 CUP</td>
                            <td><strong>$2,600 CUP</strong></td>
                        </tr>
                        <tr>
                            <td>
                                <div class="product-detail-cell">
                                    <div class="product-detail-image">
                                        <i class="fas fa-hat-cowboy"></i>
                                    </div>
                                    <div>
                                        <strong>Gorra New Era Negra</strong>
                                        <br>
                                        <span style="color: #6c757d; font-size: 0.75rem;">Talla única</span>
                                    </div>
                                </div>
                            </td>
                            <td>1</td>
                            <td>$1,500 CUP</td>
                            <td><strong>$1,500 CUP</strong></td>
                        </tr>
                    </tbody>
                </table>

                <!-- Resumen financiero -->
                <div class="summary-box">
                    <div class="summary-row">
                        <span>Subtotal:</span>
                        <span>$4,100 CUP</span>
                    </div>
                    <div class="summary-row">
                        <span>Envío:</span>
                        <span>$300 CUP</span>
                    </div>
                    <div class="summary-row">
                        <span>Comisión (10%):</span>
                        <span>$440 CUP</span>
                    </div>
                    <div class="summary-row total">
                        <span>Total a pagar:</span>
                        <span>$4,400 CUP</span>
                    </div>
                </div>

                <!-- Seguimiento -->
                <div style="margin-top: 1.5rem;">
                    <h4 style="margin-bottom: 1rem;">Información de seguimiento</h4>
                    <div class="tracking-info">
                        <i class="fas fa-box-open" style="color: #4361ee;"></i>
                        <input type="text" class="tracking-input" placeholder="Número de guía" value="CUBA-123456789">
                        <button class="btn-outline">Actualizar</button>
                    </div>
                </div>

                <!-- Notas internas -->
                <div style="margin-top: 1.5rem;">
                    <h4 style="margin-bottom: 1rem;">Notas internas</h4>
                    <textarea class="tracking-input" rows="3" placeholder="Añadir nota interna..." style="width: 100%;">Cliente solicitó entregar después de las 5pm</textarea>
                    <button class="btn-outline" style="margin-top: 0.5rem;">Guardar nota</button>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn-secondary" onclick="closeOrderDetailModal()">Cerrar</button>
                <button class="btn-primary">
                    <i class="fas fa-check"></i> Confirmar
                </button>
                <button class="btn-primary" style="background: #ffb703;">
                    <i class="fas fa-truck"></i> Marcar enviado
                </button>
                <button class="btn-danger">
                    <i class="fas fa-times"></i> Cancelar
                </button>
            </div>
        </div>
    </div>

    <script>
        // Modal functions
        function viewOrderDetails(orderId) {
            document.getElementById('orderDetailModal').classList.add('show');
            document.querySelector('.order-detail-id').textContent = '#' + orderId;
        }

        function closeOrderDetailModal() {
            document.getElementById('orderDetailModal').classList.remove('show');
        }

        // Close modal on outside click
        document.getElementById('orderDetailModal').addEventListener('click', function(e) {
            if(e.target === this) {
                closeOrderDetailModal();
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
            alert('Generando reporte de pedidos (demo)');
        });

        // Filter button
        document.querySelector('.btn-primary').addEventListener('click', function() {
            if(this.querySelector('.fa-filter')) {
                alert('Aplicando filtros (demo)');
            }
        });

        // Action buttons in modal
        document.querySelectorAll('.modal-footer .btn-primary, .modal-footer .btn-danger').forEach(btn => {
            btn.addEventListener('click', function() {
                if(this.classList.contains('btn-danger')) {
                    if(confirm('¿Cancelar este pedido?')) {
                        alert('Pedido cancelado (demo)');
                        closeOrderDetailModal();
                    }
                } else {
                    alert(`Acción: ${this.textContent.trim()} (demo)`);
                }
            });
        });

        // Update tracking
        document.querySelector('.btn-outline').addEventListener('click', function() {
            alert('Número de seguimiento actualizado (demo)');
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