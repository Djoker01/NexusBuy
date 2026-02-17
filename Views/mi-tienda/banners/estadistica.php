<?php
include '../../Layauts/sidebar_tienda.php';
?>

    <title>Estadísticas de Banners - Mi Tienda</title>
    
    
            <section class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <h1>Estadísticas de Banners</h1>
                        </div>
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item"><a href="banners.php?action=index">Banners</a></li>
                                <li class="breadcrumb-item active">Estadísticas</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </section>

            <section class="content">
                <div class="container-fluid">
                    
                    <!-- Filtros -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Filtros</h3>
                        </div>
                        <div class="card-body">
                            <form method="GET" action="banners.php" class="form-inline">
                                <input type="hidden" name="action" value="estadisticas">
                                
                                <div class="form-group mb-2 mr-2">
                                    <label class="mr-2">Período:</label>
                                    <input type="text" class="form-control" id="daterange" name="daterange" 
                                           value="<?= date('d/m/Y', strtotime($fecha_inicio)) ?> - <?= date('d/m/Y', strtotime($fecha_fin)) ?>">
                                    <input type="hidden" name="fecha_inicio" id="fecha_inicio" value="<?= $fecha_inicio ?>">
                                    <input type="hidden" name="fecha_fin" id="fecha_fin" value="<?= $fecha_fin ?>">
                                </div>
                                
                                <div class="form-group mb-2 mr-2">
                                    <label class="mr-2">Banner:</label>
                                    <select name="banner_id" class="form-control">
                                        <option value="">Todos los banners</option>
                                        <?php foreach ($banners as $b): ?>
                                            <option value="<?= $b->id ?>" <?= ($_GET['banner_id'] ?? '') == $b->id ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($b->titulo) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <button type="submit" class="btn btn-primary mb-2">
                                    <i class="fas fa-filter"></i> Filtrar
                                </button>
                                
                                <a href="banners.php?action=exportar&<?= http_build_query($_GET) ?>" class="btn btn-success mb-2 ml-2">
                                    <i class="fas fa-download"></i> Exportar CSV
                                </a>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Resumen -->
                    <div class="row">
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-info">
                                <div class="inner">
                                    <?php
                                    $total_imp = array_sum(array_column($estadisticas_generales ?? [], 'impresiones'));
                                    ?>
                                    <h3><?= number_format($total_imp) ?></h3>
                                    <p>Total Impresiones</p>
                                </div>
                                <div class="icon"><i class="fas fa-eye"></i></div>
                            </div>
                        </div>
                        
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-success">
                                <div class="inner">
                                    <?php
                                    $total_clicks = array_sum(array_column($estadisticas_generales ?? [], 'clicks'));
                                    ?>
                                    <h3><?= number_format($total_clicks) ?></h3>
                                    <p>Total Clicks</p>
                                </div>
                                <div class="icon"><i class="fas fa-mouse-pointer"></i></div>
                            </div>
                        </div>
                        
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-warning">
                                <div class="inner">
                                    <?php
                                    $ctr_promedio = $total_imp > 0 ? round(($total_clicks / $total_imp) * 100, 2) : 0;
                                    ?>
                                    <h3><?= $ctr_promedio ?>%</h3>
                                    <p>CTR Promedio</p>
                                </div>
                                <div class="icon"><i class="fas fa-chart-line"></i></div>
                            </div>
                        </div>
                        
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-danger">
                                <div class="inner">
                                    <h3><?= count($estadisticas_generales ?? []) ?></h3>
                                    <p>Días con datos</p>
                                </div>
                                <div class="icon"><i class="fas fa-calendar"></i></div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Gráfico de evolución -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Evolución Diaria</h3>
                        </div>
                        <div class="card-body">
                            <canvas id="graficoEvolucion" 
                                    data-fechas='<?= json_encode(array_column($estadisticas_generales ?? [], 'fecha')) ?>'
                                    data-impresiones='<?= json_encode(array_column($estadisticas_generales ?? [], 'impresiones')) ?>'
                                    data-clicks='<?= json_encode(array_column($estadisticas_generales ?? [], 'clicks')) ?>'
                                    style="height: 300px;">
                            </canvas>
                        </div>
                    </div>
                    
                    <!-- Top Banners -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Top Banners por Rendimiento</h3>
                        </div>
                        <div class="card-body">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Banner</th>
                                        <th>Impresiones</th>
                                        <th>Clicks</th>
                                        <th>CTR</th>
                                        <th>Costo por Click</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($top_banners): foreach ($top_banners as $top): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($top->titulo) ?></td>
                                        <td><?= number_format($top->total_impresiones) ?></td>
                                        <td><?= number_format($top->total_clicks) ?></td>
                                        <td><?= number_format($top->ctr, 2) ?>%</td>
                                        <td>
                                            <?php if ($top->costo_por_click): ?>
                                                $<?= number_format($top->costo_por_click, 2) ?>
                                            <?php else: ?>
                                                -
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; else: ?>
                                    <tr><td colspan="5" class="text-center">No hay datos suficientes</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                </div>
            </section>
      

        <?php include '../../Layauts/footer_tienda-01.php'; ?>
   
    
    
