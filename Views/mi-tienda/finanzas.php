<?php
include_once '../Layauts/header_tienda.php';

// Obtener ID de tienda del vendedor logueado
$id_tienda = $_SESSION['id_tienda'] ?? 0;
?>
<title>Mis Finanzas | NexusBuy Tienda</title>

<style>
    .card-resumen {
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 20px;
        color: white;
    }
    .card-disponible {
        background: linear-gradient(135deg, #28a745, #20c997);
    }
    .card-retenido {
        background: linear-gradient(135deg, #ffc107, #fd7e14);
    }
    .card-total {
        background: linear-gradient(135deg, #17a2b8, #007bff);
    }
    .monto {
        font-size: 32px;
        font-weight: bold;
        margin: 10px 0;
    }
    .movimiento-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 10px;
        border-bottom: 1px solid #eee;
    }
    .movimiento-item:hover {
        background-color: #f8f9fa;
    }
    .monto-positivo {
        color: #28a745;
        font-weight: bold;
    }
    .monto-negativo {
        color: #dc3545;
        font-weight: bold;
    }
</style>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Panel Financiero</h1>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <!-- Resumen de saldos -->
        <div class="row" id="resumenContainer">
            <div class="col-md-4">
                <div class="card-resumen card-disponible">
                    <h5>Saldo Disponible</h5>
                    <div class="monto" id="saldoDisponible">$0.00</div>
                    <small>Listo para retirar</small>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card-resumen card-retenido">
                    <h5>Saldo Retenido</h5>
                    <div class="monto" id="saldoRetenido">$0.00</div>
                    <small>Liberación en 7 días</small>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card-resumen card-total">
                    <h5>Total Ganado</h5>
                    <div class="monto" id="totalGanado">$0.00</div>
                    <small>Histórico de ventas</small>
                </div>
            </div>
        </div>

        <!-- Próximas liberaciones -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Próximas Liberaciones</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <strong>Ventas pendientes:</strong> <span id="pendientesCount">0</span>
                    </div>
                    <div class="col-md-6">
                        <strong>Monto a liberar:</strong> <span id="pendientesMonto">$0.00</span>
                    </div>
                </div>
                <div class="progress mt-3" style="height: 20px;">
                    <div class="progress-bar bg-success" role="progressbar" id="progressLiberacion" style="width: 0%"></div>
                </div>
            </div>
        </div>

        <!-- Historial de movimientos -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Últimos Movimientos</h3>
            </div>
            <div class="card-body">
                <div id="movimientosContainer">
                    <div class="text-center py-4">
                        <i class="fas fa-spinner fa-spin fa-2x text-primary"></i>
                        <p class="mt-2">Cargando movimientos...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script src="../Util/Js/tienda-finanzas.js"></script>