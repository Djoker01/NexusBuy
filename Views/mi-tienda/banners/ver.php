<?php
include '../../Layauts/sidebar_tienda.php';
?>

<title>Detalles del Banner - Mi Tienda</title>
    
    
            <section class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <h1>Detalles del Banner</h1>
                        </div>
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item"><a href="banners.php?action=index">Banners</a></li>
                                <li class="breadcrumb-item active">Ver Banner #<?= $banner->id ?></li>
                            </ol>
                        </div>
                    </div>
                </div>
            </section>

            <section class="content">
                <div class="container-fluid">
                    
                    <!-- Alertas -->
                    <?php if (isset($_SESSION['success'])): ?>
                        <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
                    <?php endif; ?>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <!-- Información del Banner -->
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Información General</h3>
                                </div>
                                <div class="card-body">
                                    <div class="text-center mb-3">
                                        <img src="/<?= $banner->imagen ?>" class="img-fluid border rounded" alt="<?= $banner->titulo ?>">
                                    </div>
                                    
                                    <table class="table table-striped">
                                        <tr>
                                            <th>Título:</th>
                                            <td><?= htmlspecialchars($banner->titulo) ?></td>
                                        </tr>
                                        <?php if ($banner->descripcion): ?>
                                        <tr>
                                            <th>Descripción:</th>
                                            <td><?= nl2br(htmlspecialchars($banner->descripcion)) ?></td>
                                        </tr>
                                        <?php endif; ?>
                                        <tr>
                                            <th>URL Destino:</th>
                                            <td><a href="<?= $banner->url ?>" target="_blank"><?= $banner->url ?></a></td>
                                        </tr>
                                        <tr>
                                            <th>Posición:</th>
                                            <td>
                                                <?php
                                                $posiciones = [
                                                    'home' => 'Principal',
                                                    'categorias' => 'Categorías',
                                                    'producto' => 'Producto',
                                                    'lateral' => 'Lateral',
                                                    'footer' => 'Pie'
                                                ];
                                                echo $posiciones[$banner->posicion] ?? $banner->posicion;
                                                ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Duración:</th>
                                            <td>
                                                <?php
                                                $duraciones = [
                                                    '3_dias' => '3 Días',
                                                    '1_semana' => '1 Semana',
                                                    '1_mes' => '1 Mes'
                                                ];
                                                echo $duraciones[$banner->tipo_duracion] ?? $banner->tipo_duracion;
                                                ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Fecha Inicio:</th>
                                            <td><?= date('d/m/Y H:i', strtotime($banner->fecha_inicio)) ?></td>
                                        </tr>
                                        <tr>
                                            <th>Fecha Fin:</th>
                                            <td><?= date('d/m/Y H:i', strtotime($banner->fecha_fin)) ?></td>
                                        </tr>
                                        <tr>
                                            <th>Estado:</th>
                                            <td>
                                                <?php
                                                $estados = [
                                                    'activo' => '<span class="badge badge-success">Activo</span>',
                                                    'programado' => '<span class="badge badge-info">Programado</span>',
                                                    'vencido' => '<span class="badge badge-secondary">Vencido</span>',
                                                    'cancelado' => '<span class="badge badge-danger">Cancelado</span>'
                                                ];
                                                echo $estados[$banner->estado_actual] ?? '<span class="badge badge-warning">Inactivo</span>';
                                                ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Monto Pagado:</th>
                                            <td><strong>$<?= number_format($banner->monto_pagado ?? 0, 2) ?></strong></td>
                                        </tr>
                                    </table>
                                    
                                    <!-- Botones de acción -->
                                    <div class="mt-3">
                                        <?php if ($banner->estado_actual == 'programado'): ?>
                                            <a href="banners.php?action=editar&id=<?= $banner->id ?>" class="btn btn-warning btn-block">
                                                <i class="fas fa-edit"></i> Editar Banner
                                            </a>
                                        <?php endif; ?>
                                        
                                        <?php if ($banner->estado_actual == 'activo'): ?>
                                            <button class="btn btn-danger btn-block btn-cancelar" data-id="<?= $banner->id ?>" data-titulo="<?= htmlspecialchars($banner->titulo) ?>">
                                                <i class="fas fa-ban"></i> Cancelar Banner
                                            </button>
                                        <?php endif; ?>
                                        
                                        <?php if ($banner->estado_actual == 'vencido'): ?>
                                            <button class="btn btn-success btn-block btn-renovar" data-id="<?= $banner->id ?>" data-titulo="<?= htmlspecialchars($banner->titulo) ?>">
                                                <i class="fas fa-sync-alt"></i> Renovar Banner
                                            </button>
                                        <?php endif; ?>
                                        
                                        <a href="banners.php?action=index" class="btn btn-secondary btn-block">
                                            <i class="fas fa-arrow-left"></i> Volver al Listado
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-8">
                            <!-- Estadísticas -->
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Estadísticas de Rendimiento</h3>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4 col-6">
                                            <div class="info-box">
                                                <span class="info-box-icon bg-info"><i class="fas fa-eye"></i></span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Impresiones</span>
                                                    <span class="info-box-number"><?= number_format($estadisticas->total_impresiones ?? 0) ?></span>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-4 col-6">
                                            <div class="info-box">
                                                <span class="info-box-icon bg-success"><i class="fas fa-mouse-pointer"></i></span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Clicks</span>
                                                    <span class="info-box-number"><?= number_format($estadisticas->total_clicks ?? 0) ?></span>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-4 col-6">
                                            <div class="info-box">
                                                <span class="info-box-icon bg-warning"><i class="fas fa-chart-line"></i></span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">CTR</span>
                                                    <span class="info-box-number"><?= number_format($estadisticas->ctr_promedio ?? 0, 2) ?>%</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Gráfico de rendimiento diario -->
                                    <div class="mt-4">
                                        <canvas id="graficoRendimiento" 
                                                data-fechas='<?= json_encode(array_column($estadisticas_diarias ?? [], 'fecha')) ?>'
                                                data-impresiones='<?= json_encode(array_column($estadisticas_diarias ?? [], 'impresiones')) ?>'
                                                data-clicks='<?= json_encode(array_column($estadisticas_diarias ?? [], 'clicks')) ?>'
                                                style="height: 300px;">
                                        </canvas>
                                    </div>
                                    
                                    <!-- Tabla de estadísticas diarias -->
                                    <div class="mt-4">
                                        <h5>Estadísticas por Día (Últimos 30 días)</h5>
                                        <table id="tablaEstadisticasDiarias" class="table table-striped table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Fecha</th>
                                                    <th>Impresiones</th>
                                                    <th>Clicks</th>
                                                    <th>CTR</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if ($estadisticas_diarias): foreach ($estadisticas_diarias as $dia): ?>
                                                <tr>
                                                    <td><?= date('d/m/Y', strtotime($dia->fecha)) ?></td>
                                                    <td><?= number_format($dia->impresiones) ?></td>
                                                    <td><?= number_format($dia->clicks) ?></td>
                                                    <td><?= number_format($dia->ctr, 2) ?>%</td>
                                                </tr>
                                                <?php endforeach; else: ?>
                                                <tr>
                                                    <td colspan="4" class="text-center">No hay estadísticas disponibles</td>
                                                </tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        

        <?php include '../../Layauts/footer_tienda-01.php'; ?>
    
    
