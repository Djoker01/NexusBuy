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
            background: #f8f9fa;
            color: #212529;
        }

        /* Layout */
        .app {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            width: 260px;
            background: white;
            border-right: 1px solid #e9ecef;
            padding: 1.5rem;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
        }

        .logo {
            font-size: 1.5rem;
            font-weight: 700;
            color: #4361ee;
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .logo i {
            font-size: 2rem;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1rem;
            color: #6c757d;
            text-decoration: none;
            border-radius: 8px;
            margin-bottom: 0.25rem;
            transition: all 0.2s;
        }

        .nav-item:hover {
            background: #f1f3f5;
            color: #4361ee;
        }

        .nav-item.active {
            background: #4361ee;
            color: white;
        }

        .nav-item i {
            width: 20px;
        }

        /* Main Content */
        .main {
            flex: 1;
            margin-left: 260px;
            padding: 1.5rem 2rem;
        }

        /* Header */
        .top-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            background: white;
            padding: 1rem 1.5rem;
            border-radius: 12px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.02);
        }

        .page-title h1 {
            font-size: 1.5rem;
            font-weight: 600;
        }

        .page-title p {
            color: #6c757d;
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }

        .user-menu {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        .notifications {
            position: relative;
            cursor: pointer;
        }

        .notifications i {
            font-size: 1.25rem;
            color: #6c757d;
        }

        .badge {
            position: absolute;
            top: -8px;
            right: -8px;
            background: #e63946;
            color: white;
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
            border-radius: 999px;
            min-width: 18px;
            height: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            cursor: pointer;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            background: #4361ee;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
        }

        .user-details {
            line-height: 1.3;
        }

        .user-name {
            font-weight: 600;
            font-size: 0.875rem;
        }

        .user-role {
            color: #6c757d;
            font-size: 0.75rem;
        }

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            border: 1px solid #e9ecef;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .stat-icon.pending {
            background: #fff3d1;
            color: #ffb703;
        }

        .stat-icon.confirmed {
            background: #d1f7ea;
            color: #06d6a0;
        }

        .stat-icon.shipped {
            background: #e1e8ff;
            color: #4361ee;
        }

        .stat-icon.delivered {
            background: #e9ecef;
            color: #6c757d;
        }

        .stat-info h3 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.25rem;
        }

        .stat-info p {
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
            width: 300px;
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

        .filters {
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
            color: #212529;
            cursor: pointer;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%236c757d' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 0.5rem center;
        }

        .date-range {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            background: white;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 0.25rem 0.5rem;
        }

        .date-range input {
            border: none;
            outline: none;
            padding: 0.25rem;
            width: 100px;
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
        }

        /* Orders Table */
        .orders-table-container {
            background: white;
            border-radius: 12px;
            border: 1px solid #e9ecef;
            overflow: auto;
            margin-bottom: 1.5rem;
        }

        .orders-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 1000px;
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

        .customer-info h4 {
            font-weight: 500;
            font-size: 0.875rem;
            margin-bottom: 0.25rem;
        }

        .customer-info span {
            font-size: 0.75rem;
            color: #6c757d;
        }

        .order-total {
            font-weight: 600;
        }

        .order-total small {
            font-weight: normal;
            color: #6c757d;
            font-size: 0.75rem;
            margin-left: 0.25rem;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            padding: 0.25rem 0.75rem;
            border-radius: 999px;
            font-size: 0.75rem;
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

        .action-icons {
            display: flex;
            gap: 0.5rem;
        }

        .action-icons button {
            background: none;
            border: none;
            color: #6c757d;
            cursor: pointer;
            padding: 0.25rem;
            border-radius: 4px;
            transition: all 0.2s;
        }

        .action-icons button:hover {
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

        /* Modal Detalle Pedido */
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
            border-radius: 12px;
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

        /* Order Detail Sections */
        .detail-section {
            margin-bottom: 2rem;
        }

        .section-title {
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: #212529;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.5rem;
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 8px;
        }

        .info-item {
            display: flex;
            flex-direction: column;
        }

        .info-label {
            font-size: 0.75rem;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.25rem;
        }

        .info-value {
            font-weight: 500;
        }

        .address-box {
            background: white;
            padding: 1rem;
            border-radius: 8px;
            border: 1px solid #e9ecef;
        }

        .products-table-detail {
            width: 100%;
            border-collapse: collapse;
            background: #f8f9fa;
            border-radius: 8px;
            overflow: hidden;
        }

        .products-table-detail th {
            background: #e9ecef;
            padding: 0.75rem;
            text-align: left;
            font-size: 0.75rem;
            font-weight: 600;
            color: #495057;
        }

        .products-table-detail td {
            padding: 0.75rem;
            border-bottom: 1px solid #dee2e6;
        }

        .product-detail-cell {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .product-detail-image {
            width: 40px;
            height: 40px;
            background: #dee2e6;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #6c757d;
        }

        .summary-box {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 8px;
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
            border-top: 1px solid #dee2e6;
            padding-top: 0.75rem;
            margin-top: 0.75rem;
        }

        .status-timeline {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin: 1.5rem 0;
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
            width: 40px;
            height: 40px;
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
            font-size: 0.75rem;
            color: #6c757d;
        }

        .status-progress {
            position: absolute;
            top: 20px;
            left: 0;
            right: 0;
            height: 2px;
            background: #e9ecef;
            z-index: 0;
        }

        .status-progress-fill {
            height: 100%;
            background: #4361ee;
            width: 75%;
        }

        .tracking-input {
            display: flex;
            gap: 1rem;
            align-items: center;
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 8px;
        }

        .tracking-input input {
            flex: 1;
            padding: 0.75rem;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            font-size: 0.875rem;
        }

        .btn-save {
            background: #4361ee;
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-size: 0.875rem;
            cursor: pointer;
        }

        .btn-cancel {
            background: white;
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
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .info-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .sidebar {
                display: none;
            }
            
            .main {
                margin-left: 0;
                padding: 1rem;
            }
            
            .action-bar {
                flex-direction: column;
                align-items: stretch;
            }
            
            .search-box {
                width: 100%;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .timeline-step .timeline-label {
                font-size: 0.7rem;
            }
        }
    </style>
</head>
<body>
    <div class="app">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="logo">
                <i class="fas fa-store"></i>
                <span>NexusBuy</span>
            </div>
            
            <nav>
                <a href="#" class="nav-item">
                    <i class="fas fa-chart-pie"></i>
                    <span>Dashboard</span>
                </a>
                <a href="#" class="nav-item">
                    <i class="fas fa-box"></i>
                    <span>Productos</span>
                </a>
                <a href="#" class="nav-item active">
                    <i class="fas fa-shopping-cart"></i>
                    <span>Pedidos</span>
                </a>
                <a href="#" class="nav-item">
                    <i class="fas fa-dollar-sign"></i>
                    <span>Finanzas</span>
                </a>
                <a href="#" class="nav-item">
                    <i class="fas fa-comments"></i>
                    <span>Mensajes</span>
                    <span class="badge" style="position: static; margin-left: auto;">3</span>
                </a>
                <a href="#" class="nav-item">
                    <i class="fas fa-cog"></i>
                    <span>Configuración</span>
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main">
            <!-- Header -->
            <div class="top-header">
                <div class="page-title">
                    <h1>Pedidos</h1>
                    <p>Gestiona los pedidos de tu tienda</p>
                </div>
                <div class="user-menu">
                    <div class="notifications">
                        <i class="far fa-bell"></i>
                        <span class="badge">5</span>
                    </div>
                    <div class="user-info">
                        <div class="user-avatar">MF</div>
                        <div class="user-details">
                            <div class="user-name">Miguel Fernández</div>
                            <div class="user-role">Nexus Fashion</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon pending">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-info">
                        <h3>12</h3>
                        <p>Pendientes</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon confirmed">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-info">
                        <h3>18</h3>
                        <p>Confirmados</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon shipped">
                        <i class="fas fa-truck"></i>
                    </div>
                    <div class="stat-info">
                        <h3>9</h3>
                        <p>Enviados</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon delivered">
                        <i class="fas fa-check-double"></i>
                    </div>
                    <div class="stat-info">
                        <h3>45</h3>
                        <p>Entregados</p>
                    </div>
                </div>
            </div>

            <!-- Action Bar -->
            <div class="action-bar">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Buscar pedido por ID o cliente...">
                </div>
                <div class="filters">
                    <select class="filter-select">
                        <option>Todos los estados</option>
                        <option>Pendientes</option>
                        <option>Confirmados</option>
                        <option>Enviados</option>
                        <option>Entregados</option>
                    </select>
                    <div class="date-range">
                        <i class="fas fa-calendar"></i>
                        <input type="date" value="2025-03-01">
                        <span>-</span>
                        <input type="date" value="2025-03-15">
                    </div>
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
                            <th>Cliente</th>
                            <th>Fecha</th>
                            <th>Total</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <span class="order-id">#2345</span>
                            </td>
                            <td>
                                <div class="customer-info">
                                    <h4>Juan Pérez</h4>
                                    <span>juan@email.com</span>
                                </div>
                            </td>
                            <td>15/03/2025 10:30</td>
                            <td>
                                <div class="order-total">
                                    $450 CUP
                                    <br>
                                    <small>$13 USD</small>
                                </div>
                            </td>
                            <td>
                                <span class="status-badge pending">
                                    <i class="fas fa-clock"></i>
                                    Pendiente
                                </span>
                            </td>
                            <td>
                                <div class="action-icons">
                                    <button onclick="openOrderModal(2345)"><i class="fas fa-eye"></i></button>
                                    <button><i class="fas fa-print"></i></button>
                                    <button><i class="fas fa-envelope"></i></button>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <span class="order-id">#2344</span>
                            </td>
                            <td>
                                <div class="customer-info">
                                    <h4>María Gómez</h4>
                                    <span>maria@email.com</span>
                                </div>
                            </td>
                            <td>15/03/2025 09:15</td>
                            <td>
                                <div class="order-total">
                                    $890 CUP
                                    <br>
                                    <small>$25 USD</small>
                                </div>
                            </td>
                            <td>
                                <span class="status-badge confirmed">
                                    <i class="fas fa-check-circle"></i>
                                    Confirmado
                                </span>
                            </td>
                            <td>
                                <div class="action-icons">
                                    <button onclick="openOrderModal(2344)"><i class="fas fa-eye"></i></button>
                                    <button><i class="fas fa-print"></i></button>
                                    <button><i class="fas fa-envelope"></i></button>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <span class="order-id">#2343</span>
                            </td>
                            <td>
                                <div class="customer-info">
                                    <h4>Carlos Ruiz</h4>
                                    <span>carlos@email.com</span>
                                </div>
                            </td>
                            <td>14/03/2025 18:40</td>
                            <td>
                                <div class="order-total">
                                    $1,200 CUP
                                    <br>
                                    <small>$34 USD</small>
                                </div>
                            </td>
                            <td>
                                <span class="status-badge shipped">
                                    <i class="fas fa-truck"></i>
                                    Enviado
                                </span>
                            </td>
                            <td>
                                <div class="action-icons">
                                    <button onclick="openOrderModal(2343)"><i class="fas fa-eye"></i></button>
                                    <button><i class="fas fa-print"></i></button>
                                    <button><i class="fas fa-envelope"></i></button>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <span class="order-id">#2342</span>
                            </td>
                            <td>
                                <div class="customer-info">
                                    <h4>Ana López</h4>
                                    <span>ana@email.com</span>
                                </div>
                            </td>
                            <td>14/03/2025 15:20</td>
                            <td>
                                <div class="order-total">
                                    $340 CUP
                                    <br>
                                    <small>$10 USD</small>
                                </div>
                            </td>
                            <td>
                                <span class="status-badge confirmed">
                                    <i class="fas fa-check-circle"></i>
                                    Confirmado
                                </span>
                            </td>
                            <td>
                                <div class="action-icons">
                                    <button onclick="openOrderModal(2342)"><i class="fas fa-eye"></i></button>
                                    <button><i class="fas fa-print"></i></button>
                                    <button><i class="fas fa-envelope"></i></button>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <span class="order-id">#2341</span>
                            </td>
                            <td>
                                <div class="customer-info">
                                    <h4>Pedro Méndez</h4>
                                    <span>pedro@email.com</span>
                                </div>
                            </td>
                            <td>14/03/2025 11:05</td>
                            <td>
                                <div class="order-total">
                                    $2,100 CUP
                                    <br>
                                    <small>$60 USD</small>
                                </div>
                            </td>
                            <td>
                                <span class="status-badge delivered">
                                    <i class="fas fa-check-double"></i>
                                    Entregado
                                </span>
                            </td>
                            <td>
                                <div class="action-icons">
                                    <button onclick="openOrderModal(2341)"><i class="fas fa-eye"></i></button>
                                    <button><i class="fas fa-print"></i></button>
                                    <button><i class="fas fa-envelope"></i></button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="pagination">
                <div class="pagination-info">
                    Mostrando 5 de 84 pedidos
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

    <!-- Modal Detalle del Pedido -->
    <div class="modal-overlay" id="orderModal">
        <div class="modal">
            <div class="modal-header">
                <h2>Pedido #2345 - Juan Pérez</h2>
                <button class="modal-close" onclick="closeOrderModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <!-- Estado del pedido con timeline -->
                <div class="detail-section">
                    <div class="section-title">Estado del pedido</div>
                    <div class="status-timeline">
                        <div class="status-progress">
                            <div class="status-progress-fill" style="width: 75%;"></div>
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
                    
                    <div style="display: flex; gap: 0.5rem; margin-top: 1rem;">
                        <button class="btn-primary" style="padding: 0.5rem 1rem;">
                            <i class="fas fa-check"></i> Confirmar pedido
                        </button>
                        <button class="btn-secondary" style="padding: 0.5rem 1rem;">
                            <i class="fas fa-times"></i> Cancelar pedido
                        </button>
                    </div>
                </div>

                <!-- Información del cliente -->
                <div class="detail-section">
                    <div class="section-title">Información del cliente</div>
                    <div class="info-grid">
                        <div class="info-item">
                            <span class="info-label">Nombre completo</span>
                            <span class="info-value">Juan Pérez Rodríguez</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Email</span>
                            <span class="info-value">juan.perez@email.com</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Teléfono</span>
                            <span class="info-value">+53 5 1234567</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Tipo de entrega</span>
                            <span class="info-value">Envío a domicilio</span>
                        </div>
                    </div>
                </div>

                <!-- Dirección de envío -->
                <div class="detail-section">
                    <div class="section-title">Dirección de envío</div>
                    <div class="address-box">
                        <p style="margin-bottom: 0.25rem;">Calle 123 #456 e/ 7ma y 8va, Plaza de la Revolución, La Habana</p>
                        <p style="color: #6c757d; font-size: 0.875rem;">
                            <i class="fas fa-info-circle"></i>
                            Instrucciones: Tocar timbre, edificio azul, apartamento 3B
                        </p>
                    </div>
                </div>

                <!-- Productos -->
                <div class="detail-section">
                    <div class="section-title">Productos</div>
                    <table class="products-table-detail">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th>Cantidad</th>
                                <th>Precio unit.</th>
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
                </div>

                <!-- Resumen de costos -->
                <div class="detail-section">
                    <div class="section-title">Resumen</div>
                    <div class="summary-box">
                        <div class="summary-row">
                            <span>Subtotal (CUP):</span>
                            <span>$4,100 CUP</span>
                        </div>
                        <div class="summary-row">
                            <span>Subtotal (USD):</span>
                            <span>$125 USD</span>
                        </div>
                        <div class="summary-row">
                            <span>Envío:</span>
                            <span>$300 CUP / $9 USD</span>
                        </div>
                        <div class="summary-row">
                            <span>Comisión (10%):</span>
                            <span>$440 CUP / $13.40 USD</span>
                        </div>
                        <div class="summary-row total">
                            <span>Total a recibir:</span>
                            <span>$3,960 CUP / $120.60 USD</span>
                        </div>
                    </div>
                </div>

                <!-- Número de seguimiento -->
                <div class="detail-section">
                    <div class="section-title">Número de seguimiento</div>
                    <div class="tracking-input">
                        <i class="fas fa-box-open" style="color: #6c757d;"></i>
                        <input type="text" placeholder="Ingresa el número de guía" value="CUBA-123456789">
                        <button class="btn-primary">Guardar</button>
                    </div>
                </div>

                <!-- Notas internas -->
                <div class="detail-section">
                    <div class="section-title">Notas internas</div>
                    <div style="background: #f8f9fa; padding: 1rem; border-radius: 8px;">
                        <p style="color: #6c757d; font-style: italic; margin-bottom: 0.5rem;">No hay notas para este pedido</p>
                        <textarea class="form-control" rows="2" placeholder="Añadir una nota interna..." style="width: 100%; padding: 0.75rem; border: 1px solid #e9ecef; border-radius: 8px;"></textarea>
                        <button class="btn-outline" style="margin-top: 0.5rem;">
                            <i class="fas fa-plus"></i> Añadir nota
                        </button>
                    </div>
                </div>

                <!-- Línea de tiempo completa -->
                <div class="detail-section">
                    <div class="section-title">Historial del pedido</div>
                    <div style="background: #f8f9fa; padding: 1rem; border-radius: 8px;">
                        <div style="display: flex; gap: 1rem; margin-bottom: 1rem; padding-bottom: 1rem; border-bottom: 1px solid #e9ecef;">
                            <div style="min-width: 100px;">
                                <span style="font-weight: 500;">15/03 10:30</span>
                            </div>
                            <div>
                                <p style="font-weight: 500;">Pedido realizado</p>
                                <p style="color: #6c757d; font-size: 0.875rem;">El cliente realizó el pedido</p>
                            </div>
                        </div>
                        <div style="display: flex; gap: 1rem; margin-bottom: 1rem; padding-bottom: 1rem; border-bottom: 1px solid #e9ecef;">
                            <div style="min-width: 100px;">
                                <span style="font-weight: 500;">15/03 10:35</span>
                            </div>
                            <div>
                                <p style="font-weight: 500;">Pago confirmado</p>
                                <p style="color: #6c757d; font-size: 0.875rem;">El pago fue procesado exitosamente</p>
                            </div>
                        </div>
                        <div style="display: flex; gap: 1rem;">
                            <div style="min-width: 100px;">
                                <span style="font-weight: 500;">15/03 11:20</span>
                            </div>
                            <div>
                                <p style="font-weight: 500;">Pendiente de confirmación</p>
                                <p style="color: #6c757d; font-size: 0.875rem;">Esperando confirmación de la tienda</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn-outline" onclick="printOrder()">
                    <i class="fas fa-print"></i> Imprimir factura
                </button>
                <button class="btn-outline">
                    <i class="fas fa-envelope"></i> Contactar cliente
                </button>
                <button class="btn-cancel" onclick="closeOrderModal()">Cerrar</button>
            </div>
        </div>
    </div>

    <script>
        function openOrderModal(orderId) {
            document.getElementById('orderModal').classList.add('show');
            // Aquí se podría actualizar el título dinámicamente según el pedido
        }

        function closeOrderModal() {
            document.getElementById('orderModal').classList.remove('show');
        }

        function printOrder() {
            alert('Generando factura para impresión (demo)');
        }

        // Cerrar modal al hacer click fuera
        document.getElementById('orderModal').addEventListener('click', function(e) {
            if(e.target === this) {
                closeOrderModal();
            }
        });

        // Simulación de acciones en la tabla
        document.querySelectorAll('.action-icons button').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                if(btn.querySelector('.fa-eye')) {
                    // Ya abre el modal por el onclick
                } else if(btn.querySelector('.fa-print')) {
                    alert('Generando factura (demo)');
                } else if(btn.querySelector('.fa-envelope')) {
                    alert('Contactando al cliente (demo)');
                }
            });
        });

        // Navegación
        document.querySelectorAll('.nav-item').forEach(item => {
            item.addEventListener('click', (e) => {
                e.preventDefault();
                document.querySelectorAll('.nav-item').forEach(nav => nav.classList.remove('active'));
                item.classList.add('active');
            });
        });

        // Filtros demo
        document.querySelector('.btn-primary').addEventListener('click', function() {
            alert('Aplicando filtros (demo)');
        });
    </script>
</body>
</html>