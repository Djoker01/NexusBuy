<?php
$base_path = "../";
$pageTitle = "Finanzas";
$pageName = "Finanzas";
$pageDescription = "Gestiona tus ingresos y realiza retiros.";
include_once '../layouts/header.php';
?>

    
    
    
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

        /* Balance Cards */
        .balance-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .balance-card {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            border: 1px solid #e9ecef;
            position: relative;
            overflow: hidden;
        }

        .balance-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
        }

        .balance-card.available::before {
            background: #06d6a0;
        }

        .balance-card.pending::before {
            background: #ffb703;
        }

        .balance-card.total::before {
            background: #4361ee;
        }

        .balance-label {
            color: #6c757d;
            font-size: 0.875rem;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .balance-amount {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.25rem;
        }

        .balance-sub {
            color: #6c757d;
            font-size: 0.875rem;
        }

        .balance-icon {
            position: absolute;
            right: 1.5rem;
            top: 50%;
            transform: translateY(-50%);
            font-size: 3rem;
            color: rgba(0,0,0,0.05);
        }

        /* Withdraw Section */
        .withdraw-section {
            background: white;
            border-radius: 12px;
            border: 1px solid #e9ecef;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }

        .section-title {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
        }

        .withdraw-form {
            display: grid;
            grid-template-columns: 2fr 1fr 2fr auto;
            gap: 1rem;
            align-items: flex-end;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-label {
            font-size: 0.75rem;
            color: #6c757d;
            margin-bottom: 0.25rem;
            font-weight: 500;
        }

        .form-control {
            padding: 0.75rem;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            font-size: 0.875rem;
            transition: all 0.2s;
        }

        .form-control:focus {
            outline: none;
            border-color: #4361ee;
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
            display: flex;
            align-items: center;
            gap: 0.5rem;
            white-space: nowrap;
        }

        .btn-primary:hover {
            background: #3651d4;
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

        .info-text {
            font-size: 0.75rem;
            color: #6c757d;
            margin-top: 0.5rem;
        }

        /* Charts Row */
        .charts-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .chart-card {
            background: white;
            border-radius: 12px;
            border: 1px solid #e9ecef;
            padding: 1.5rem;
        }

        .chart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .chart-header h3 {
            font-size: 1rem;
            font-weight: 600;
        }

        .chart-period {
            display: flex;
            gap: 0.5rem;
        }

        .period-btn {
            background: none;
            border: none;
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
            color: #6c757d;
            cursor: pointer;
            border-radius: 4px;
        }

        .period-btn.active {
            background: #4361ee;
            color: white;
        }

        .chart-container {
            height: 250px;
            position: relative;
        }

        /* Transactions Table */
        .transactions-section {
            background: white;
            border-radius: 12px;
            border: 1px solid #e9ecef;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .section-actions {
            display: flex;
            gap: 0.5rem;
        }

        .transactions-table {
            width: 100%;
            border-collapse: collapse;
        }

        .transactions-table th {
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

        .transactions-table td {
            padding: 1rem;
            border-bottom: 1px solid #e9ecef;
            font-size: 0.875rem;
        }

        .transaction-concept {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .transaction-icon {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .transaction-icon.sale {
            background: #d1f7ea;
            color: #06d6a0;
        }

        .transaction-icon.withdraw {
            background: #ffe5e5;
            color: #e63946;
        }

        .transaction-icon.commission {
            background: #fff3d1;
            color: #ffb703;
        }

        .transaction-info h4 {
            font-weight: 500;
            font-size: 0.875rem;
            margin-bottom: 0.25rem;
        }

        .transaction-info span {
            font-size: 0.75rem;
            color: #6c757d;
        }

        .transaction-amount {
            font-weight: 600;
        }

        .transaction-amount.positive {
            color: #06d6a0;
        }

        .transaction-amount.negative {
            color: #e63946;
        }

        .commission-badge {
            font-size: 0.75rem;
            color: #6c757d;
            background: #f1f3f5;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
        }

        /* Withdrawals History */
        .withdrawals-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
            margin-top: 1rem;
        }

        .withdrawal-card {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 1rem;
            border: 1px solid #e9ecef;
        }

        .withdrawal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.5rem;
        }

        .withdrawal-date {
            font-size: 0.75rem;
            color: #6c757d;
        }

        .withdrawal-status {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
            border-radius: 999px;
        }

        .withdrawal-status.completed {
            background: #d1f7ea;
            color: #06d6a0;
        }

        .withdrawal-status.pending {
            background: #fff3d1;
            color: #ffb703;
        }

        .withdrawal-status.processing {
            background: #e1e8ff;
            color: #4361ee;
        }

        .withdrawal-amount {
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 0.25rem;
        }

        .withdrawal-method {
            font-size: 0.75rem;
            color: #6c757d;
        }

        /* Export Buttons */
        .export-buttons {
            display: flex;
            gap: 0.5rem;
            justify-content: flex-end;
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .balance-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .charts-row {
                grid-template-columns: 1fr;
            }
            
            .withdraw-form {
                grid-template-columns: 1fr;
            }
            
            .withdrawals-grid {
                grid-template-columns: repeat(2, 1fr);
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
            
            .balance-grid {
                grid-template-columns: 1fr;
            }
            
            .withdrawals-grid {
                grid-template-columns: 1fr;
            }
            
            .transactions-table {
                min-width: 800px;
            }
            
            .section-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }
        }
    </style>

            

            <!-- Balance Cards -->
            <div class="balance-grid">
                <div class="balance-card available">
                    <div class="balance-label">
                        <i class="fas fa-wallet"></i>
                        Saldo disponible
                    </div>
                    <div class="balance-amount">$12,450 CUP</div>
                    <div class="balance-sub">$356 USD</div>
                    <div class="balance-icon">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                </div>
                <div class="balance-card pending">
                    <div class="balance-label">
                        <i class="fas fa-clock"></i>
                        Saldo retenido
                    </div>
                    <div class="balance-amount">$3,200 CUP</div>
                    <div class="balance-sub">$92 USD</div>
                    <div class="balance-icon">
                        <i class="fas fa-hourglass-half"></i>
                    </div>
                </div>
                <div class="balance-card total">
                    <div class="balance-label">
                        <i class="fas fa-chart-line"></i>
                        Total ventas
                    </div>
                    <div class="balance-amount">$45,670 CUP</div>
                    <div class="balance-sub">$1,305 USD</div>
                    <div class="balance-icon">
                        <i class="fas fa-circle-up"></i>
                    </div>
                </div>
            </div>

            <!-- Withdraw Section -->
            <div class="withdraw-section">
                <h3 class="section-title">
                    <i class="fas fa-hand-holding-usd" style="margin-right: 0.5rem; color: #4361ee;"></i>
                    Solicitar retiro
                </h3>
                <div class="withdraw-form">
                    <div class="form-group">
                        <label class="form-label">Monto a retirar (CUP)</label>
                        <input type="text" class="form-control" placeholder="0.00" value="5,000">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Monto disponible</label>
                        <input type="text" class="form-control" value="$12,450 CUP" disabled style="background: #f8f9fa;">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Método de pago</label>
                        <select class="form-control">
                            <option>Transfermóvil</option>
                            <option>EnZona</option>
                            <option>Transferencia bancaria</option>
                            <option>Efectivo (recogida)</option>
                        </select>
                    </div>
                    <button class="btn-primary">
                        <i class="fas fa-paper-plane"></i>
                        Solicitar retiro
                    </button>
                </div>
                <div class="info-text">
                    <i class="fas fa-info-circle"></i>
                    Los retiros se procesan en un plazo de 24-48 horas hábiles. Comisión por retiro: 2.5%
                </div>
            </div>

            <!-- Charts Row -->
            <div class="charts-row">
                <!-- Monthly Income Chart -->
                <div class="chart-card">
                    <div class="chart-header">
                        <h3>Ingresos mensuales</h3>
                        <div class="chart-period">
                            <button class="period-btn active">2025</button>
                            <button class="period-btn">2024</button>
                        </div>
                    </div>
                    <div class="chart-container">
                        <canvas id="monthlyChart"></canvas>
                    </div>
                </div>

                <!-- Payment Methods Chart -->
                <div class="chart-card">
                    <div class="chart-header">
                        <h3>Métodos de pago</h3>
                        <div class="chart-period">
                            <button class="period-btn active">Este mes</button>
                            <button class="period-btn">Histórico</button>
                        </div>
                    </div>
                    <div class="chart-container">
                        <canvas id="paymentChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Transactions History -->
            <div class="transactions-section">
                <div class="section-header">
                    <h3 class="section-title" style="margin-bottom: 0;">Historial de transacciones</h3>
                    <div class="section-actions">
                        <select class="form-control" style="width: 150px;">
                            <option>Últimos 30 días</option>
                            <option>Este mes</option>
                            <option>Este año</option>
                            <option>Personalizado</option>
                        </select>
                        <button class="btn-outline">
                            <i class="fas fa-download"></i>
                            Exportar
                        </button>
                    </div>
                </div>
                
                <div style="overflow-x: auto;">
                    <table class="transactions-table">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Concepto</th>
                                <th>Ingreso</th>
                                <th>Comisión</th>
                                <th>Neto</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>15/03/2025</td>
                                <td>
                                    <div class="transaction-concept">
                                        <div class="transaction-icon sale">
                                            <i class="fas fa-shopping-cart"></i>
                                        </div>
                                        <div class="transaction-info">
                                            <h4>Venta #2345</h4>
                                            <span>Juan Pérez - 2 productos</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="transaction-amount positive">$4,100 CUP</td>
                                <td>
                                    <span class="commission-badge">- $410 CUP (10%)</span>
                                </td>
                                <td class="transaction-amount positive">$3,690 CUP</td>
                                <td>
                                    <span class="withdrawal-status completed">Completado</span>
                                </td>
                            </tr>
                            <tr>
                                <td>14/03/2025</td>
                                <td>
                                    <div class="transaction-concept">
                                        <div class="transaction-icon sale">
                                            <i class="fas fa-shopping-cart"></i>
                                        </div>
                                        <div class="transaction-info">
                                            <h4>Venta #2344</h4>
                                            <span>María Gómez - 1 producto</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="transaction-amount positive">$890 CUP</td>
                                <td>
                                    <span class="commission-badge">- $89 CUP (10%)</span>
                                </td>
                                <td class="transaction-amount positive">$801 CUP</td>
                                <td>
                                    <span class="withdrawal-status completed">Completado</span>
                                </td>
                            </tr>
                            <tr>
                                <td>14/03/2025</td>
                                <td>
                                    <div class="transaction-concept">
                                        <div class="transaction-icon withdraw">
                                            <i class="fas fa-hand-holding-usd"></i>
                                        </div>
                                        <div class="transaction-info">
                                            <h4>Retiro #R023</h4>
                                            <span>Transfermóvil - 5,000 CUP</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="transaction-amount negative">- $5,000 CUP</td>
                                <td>
                                    <span class="commission-badge">- $125 CUP (2.5%)</span>
                                </td>
                                <td class="transaction-amount negative">- $5,125 CUP</td>
                                <td>
                                    <span class="withdrawal-status completed">Procesado</span>
                                </td>
                            </tr>
                            <tr>
                                <td>13/03/2025</td>
                                <td>
                                    <div class="transaction-concept">
                                        <div class="transaction-icon sale">
                                            <i class="fas fa-shopping-cart"></i>
                                        </div>
                                        <div class="transaction-info">
                                            <h4>Venta #2340</h4>
                                            <span>Carlos Ruiz - 3 productos</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="transaction-amount positive">$2,100 CUP</td>
                                <td>
                                    <span class="commission-badge">- $210 CUP (10%)</span>
                                </td>
                                <td class="transaction-amount positive">$1,890 CUP</td>
                                <td>
                                    <span class="withdrawal-status completed">Completado</span>
                                </td>
                            </tr>
                            <tr>
                                <td>12/03/2025</td>
                                <td>
                                    <div class="transaction-concept">
                                        <div class="transaction-icon sale">
                                            <i class="fas fa-shopping-cart"></i>
                                        </div>
                                        <div class="transaction-info">
                                            <h4>Venta #2338</h4>
                                            <span>Ana López - 2 productos</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="transaction-amount positive">$3,400 CUP</td>
                                <td>
                                    <span class="commission-badge">- $340 CUP (10%)</span>
                                </td>
                                <td class="transaction-amount positive">$3,060 CUP</td>
                                <td>
                                    <span class="withdrawal-status pending">Retenido</span>
                                </td>
                            </tr>
                            <tr>
                                <td>11/03/2025</td>
                                <td>
                                    <div class="transaction-concept">
                                        <div class="transaction-icon commission">
                                            <i class="fas fa-percent"></i>
                                        </div>
                                        <div class="transaction-info">
                                            <h4>Ajuste de comisión</h4>
                                            <span>Promoción marzo</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="transaction-amount positive">$150 CUP</td>
                                <td>
                                    <span class="commission-badge">-</span>
                                </td>
                                <td class="transaction-amount positive">$150 CUP</td>
                                <td>
                                    <span class="withdrawal-status completed">Completado</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 1.5rem;">
                    <div class="pagination-info" style="color: #6c757d; font-size: 0.875rem;">
                        Mostrando 6 de 48 transacciones
                    </div>
                    <div class="pagination-controls" style="display: flex; gap: 0.5rem;">
                        <button class="page-btn" style="width: 36px; height: 36px; border: 1px solid #e9ecef; border-radius: 8px; background: white;"><i class="fas fa-chevron-left"></i></button>
                        <button class="page-btn active" style="width: 36px; height: 36px; border: 1px solid #4361ee; border-radius: 8px; background: #4361ee; color: white;">1</button>
                        <button class="page-btn" style="width: 36px; height: 36px; border: 1px solid #e9ecef; border-radius: 8px; background: white;">2</button>
                        <button class="page-btn" style="width: 36px; height: 36px; border: 1px solid #e9ecef; border-radius: 8px; background: white;">3</button>
                        <button class="page-btn" style="width: 36px; height: 36px; border: 1px solid #e9ecef; border-radius: 8px; background: white;">4</button>
                        <button class="page-btn" style="width: 36px; height: 36px; border: 1px solid #e9ecef; border-radius: 8px; background: white;"><i class="fas fa-chevron-right"></i></button>
                    </div>
                </div>
            </div>

            <!-- Recent Withdrawals -->
            <div class="transactions-section">
                <div class="section-header">
                    <h3 class="section-title" style="margin-bottom: 0;">Retiros recientes</h3>
                    <button class="btn-outline">
                        <i class="fas fa-history"></i>
                        Ver todos
                    </button>
                </div>

                <div class="withdrawals-grid">
                    <div class="withdrawal-card">
                        <div class="withdrawal-header">
                            <span class="withdrawal-date">10/03/2025</span>
                            <span class="withdrawal-status completed">Completado</span>
                        </div>
                        <div class="withdrawal-amount">$5,000 CUP</div>
                        <div class="withdrawal-method">
                            <i class="fas fa-mobile-alt"></i> Transfermóvil
                        </div>
                        <div style="font-size: 0.75rem; color: #6c757d; margin-top: 0.5rem;">
                            Procesado: 14/03/2025
                        </div>
                    </div>
                    <div class="withdrawal-card">
                        <div class="withdrawal-header">
                            <span class="withdrawal-date">05/03/2025</span>
                            <span class="withdrawal-status completed">Completado</span>
                        </div>
                        <div class="withdrawal-amount">$3,500 CUP</div>
                        <div class="withdrawal-method">
                            <i class="fas fa-university"></i> Transferencia
                        </div>
                        <div style="font-size: 0.75rem; color: #6c757d; margin-top: 0.5rem;">
                            Procesado: 07/03/2025
                        </div>
                    </div>
                    <div class="withdrawal-card">
                        <div class="withdrawal-header">
                            <span class="withdrawal-date">28/02/2025</span>
                            <span class="withdrawal-status completed">Completado</span>
                        </div>
                        <div class="withdrawal-amount">$2,000 CUP</div>
                        <div class="withdrawal-method">
                            <i class="fas fa-money-bill"></i> Efectivo
                        </div>
                        <div style="font-size: 0.75rem; color: #6c757d; margin-top: 0.5rem;">
                            Procesado: 02/03/2025
                        </div>
                    </div>
                </div>
            </div>

            <!-- Export Buttons -->
            <div class="export-buttons">
                <button class="btn-outline">
                    <i class="fas fa-file-pdf"></i>
                    PDF
                </button>
                <button class="btn-outline">
                    <i class="fas fa-file-excel"></i>
                    Excel
                </button>
                <button class="btn-outline">
                    <i class="fas fa-envelope"></i>
                    Enviar por email
                </button>
            </div>
        </main>
    </div>
<?php
include_once '../layouts/footer.php';
?>
    <script>
        // Inicializar gráficos cuando el documento esté listo
        document.addEventListener('DOMContentLoaded', function() {
            // Gráfico de ingresos mensuales
            const ctx1 = document.getElementById('monthlyChart').getContext('2d');
            new Chart(ctx1, {
                type: 'line',
                data: {
                    labels: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
                    datasets: [{
                        label: 'Ingresos 2025 (CUP)',
                        data: [8500, 9200, 11800, 10500, 12400, 13800, 15200, 14800, 16200, 15800, 17100, 18900],
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
            const ctx2 = document.getElementById('paymentChart').getContext('2d');
            new Chart(ctx2, {
                type: 'doughnut',
                data: {
                    labels: ['Transfermóvil', 'Efectivo', 'Tarjeta', 'EnZona'],
                    datasets: [{
                        data: [45, 30, 15, 10],
                        backgroundColor: ['#4361ee', '#06d6a0', '#ffb703', '#7209b7'],
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
        });

        
        // Simulación de solicitud de retiro
        document.querySelector('.btn-primary').addEventListener('click', function() {
            alert('Solicitud de retiro enviada (demo)');
        });

        // Cambio de período en gráficos
        document.querySelectorAll('.period-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                this.parentElement.querySelectorAll('.period-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                alert('Cambiando período del gráfico (demo)');
            });
        });

        // Botones de exportación
        document.querySelectorAll('.btn-outline').forEach(btn => {
            btn.addEventListener('click', function() {
                if(this.querySelector('.fa-file-pdf')) {
                    alert('Generando PDF (demo)');
                } else if(this.querySelector('.fa-file-excel')) {
                    alert('Generando Excel (demo)');
                } else if(this.querySelector('.fa-envelope')) {
                    alert('Enviando reporte por email (demo)');
                }
            });
        });
    </script>
