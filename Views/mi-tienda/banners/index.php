<?php
include '../../Layauts/sidebar_tienda.php';
?>

<title>Mis Banners Publicitarios - Mi Tienda</title>

            <!-- Content Header -->
            <section class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <h1>Mis Banners Publicitarios</h1>
                        </div>
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item"><a href="/mi-tienda/dashboard.php">Dashboard</a></li>
                                <li class="breadcrumb-item active">Banners</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Main content -->
            <section class="content">
                <div class="container-fluid">
                    
                    <!-- Alertas -->
                    <?php if (isset($_SESSION['success'])): ?>
                        <div class="alert alert-success alert-dismissible">
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                            <?= $_SESSION['success']; unset($_SESSION['success']); ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger alert-dismissible">
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                            <?= $_SESSION['error']; unset($_SESSION['error']); ?>
                        </div>
                    <?php endif; ?>

                    <!-- Tarjetas de Resumen -->
                    <div class="row">
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-info">
                                <div class="inner">
                                    <h3><?= $stats->total_banners ?? 0 ?></h3>
                                    <p>Total Banners</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-images"></i>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-success">
                                <div class="inner">
                                    <h3><?= $stats->banners_activos ?? 0 ?></h3>
                                    <p>Activos</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-warning">
                                <div class="inner">
                                    <h3>$<?= number_format($saldo ?? 0, 2) ?></h3>
                                    <p>Saldo Disponible</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-wallet"></i>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-danger">
                                <div class="inner">
                                    <h3><?= $stats->banners_vencidos ?? 0 ?></h3>
                                    <p>Vencidos</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-clock"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Botón Crear Banner -->
                    <div class="row mb-3">
                        <div class="col-12">
                            <a href="banners.php?action=crear" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Crear Nuevo Banner
                            </a>
                        </div>
                    </div>

                    <!-- Tabla de Banners -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Mis Banners Publicitarios</h3>
                        </div>
                        <div class="card-body">
                            <table id="tablaBanners" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Imagen</th>
                                        <th>Título</th>
                                        <th>Posición</th>
                                        <th>Duración</th>
                                        <th>Inicio</th>
                                        <th>Fin</th>
                                        <th>Días Rest.</th>
                                        <th>Estado</th>
                                        <th>Inversión</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($banners): foreach ($banners as $b): ?>
                                    <tr>
                                        <td><?= $b->id ?></td>
                                        <td>
                                            <?php if ($b->imagen): ?>
                                                <img src="/<?= $b->imagen ?>" alt="Banner" style="max-width: 50px; max-height: 50px;">
                                            <?php else: ?>
                                                <span class="badge badge-secondary">Sin imagen</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= htmlspecialchars($b->titulo) ?></td>
                                        <td>
                                            <?php
                                            $posiciones = [
                                                'home' => 'Principal',
                                                'categorias' => 'Categorías',
                                                'producto' => 'Producto',
                                                'lateral' => 'Lateral',
                                                'footer' => 'Pie'
                                            ];
                                            echo $posiciones[$b->posicion] ?? $b->posicion;
                                            ?>
                                        </td>
                                        <td>
                                            <?php
                                            $duraciones = [
                                                '3_dias' => '3 Días',
                                                '1_semana' => '1 Semana',
                                                '1_mes' => '1 Mes'
                                            ];
                                            echo $duraciones[$b->tipo_duracion] ?? $b->tipo_duracion;
                                            ?>
                                        </td>
                                        <td><?= date('d/m/Y', strtotime($b->fecha_inicio)) ?></td>
                                        <td><?= date('d/m/Y', strtotime($b->fecha_fin)) ?></td>
                                        <td>
                                            <?php if ($b->estado_actual == 'activo'): ?>
                                                <span class="badge badge-success"><?= $b->dias_restantes ?> días</span>
                                            <?php elseif ($b->estado_actual == 'programado'): ?>
                                                <span class="badge badge-info">Programado</span>
                                            <?php elseif ($b->estado_actual == 'vencido'): ?>
                                                <span class="badge badge-secondary">Vencido</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php
                                            $estados = [
                                                'activo' => '<span class="badge badge-success">Activo</span>',
                                                'programado' => '<span class="badge badge-info">Programado</span>',
                                                'vencido' => '<span class="badge badge-secondary">Vencido</span>',
                                                'inactivo' => '<span class="badge badge-warning">Inactivo</span>'
                                            ];
                                            echo $estados[$b->estado_actual] ?? '<span class="badge badge-secondary">Desconocido</span>';
                                            ?>
                                        </td>
                                        <td>$<?= number_format($b->monto_pagado ?? 0, 2) ?></td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="banners.php?action=ver&id=<?= $b->id ?>" class="btn btn-sm btn-info" title="Ver">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                
                                                <?php if ($b->estado_actual == 'programado'): ?>
                                                    <a href="banners.php?action=editar&id=<?= $b->id ?>" class="btn btn-sm btn-warning" title="Editar">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                <?php endif; ?>
                                                
                                                <?php if ($b->estado_actual == 'activo'): ?>
                                                    <button class="btn btn-sm btn-danger btn-cancelar" 
                                                            data-id="<?= $b->id ?>" 
                                                            data-titulo="<?= htmlspecialchars($b->titulo) ?>"
                                                            title="Cancelar">
                                                        <i class="fas fa-ban"></i>
                                                    </button>
                                                <?php endif; ?>
                                                
                                                <?php if ($b->estado_actual == 'vencido'): ?>
                                                    <button class="btn btn-sm btn-success btn-renovar" 
                                                            data-id="<?= $b->id ?>" 
                                                            data-titulo="<?= htmlspecialchars($b->titulo) ?>"
                                                            title="Renovar">
                                                        <i class="fas fa-sync-alt"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </section>
        

        <!-- Footer -->
        <?php include '../../Layauts/footer_tienda-01.php'; ?>
 

 
    
    
    
