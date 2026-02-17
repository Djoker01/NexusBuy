<?php
include '../../Layauts/sidebar_tienda.php';
?>

    <title>Editar Banner - Mi Tienda</title>
    
    
            <section class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <h1>Editar Banner</h1>
                        </div>
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item"><a href="banners.php?action=index">Banners</a></li>
                                <li class="breadcrumb-item active">Editar #<?= $banner->id ?></li>
                            </ol>
                        </div>
                    </div>
                </div>
            </section>

            <section class="content">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Información del Banner</h3>
                                </div>
                                
                                <?php if (isset($_SESSION['errores'])): ?>
                                    <div class="alert alert-danger m-3">
                                        <ul class="mb-0">
                                            <?php foreach ($_SESSION['errores'] as $error): ?>
                                                <li><?= $error ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                    <?php unset($_SESSION['errores']); ?>
                                <?php endif; ?>
                                
                                <form method="POST" action="banners.php?action=actualizar&id=<?= $banner->id ?>" enctype="multipart/form-data">
                                    <div class="card-body">
                                        <div class="form-group">
                                            <label for="titulo">Título del Banner *</label>
                                            <input type="text" class="form-control" id="titulo" name="titulo" 
                                                   value="<?= $_SESSION['old_data']['titulo'] ?? $banner->titulo ?>" required>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="descripcion">Descripción</label>
                                            <textarea class="form-control" id="descripcion" name="descripcion" rows="3"><?= $_SESSION['old_data']['descripcion'] ?? $banner->descripcion ?></textarea>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label>Imagen Actual</label>
                                            <div class="mb-2">
                                                <img src="/<?= $banner->imagen ?>" class="img-fluid border rounded" style="max-height: 100px;" alt="Banner actual">
                                            </div>
                                            
                                            <label for="imagen">Cambiar Imagen (opcional)</label>
                                            <div class="custom-file">
                                                <input type="file" class="custom-file-input" id="imagen" name="imagen" accept="image/*">
                                                <label class="custom-file-label" for="imagen">Seleccionar nueva imagen</label>
                                            </div>
                                            <small class="text-muted">Dejar vacío para mantener la imagen actual</small>
                                        </div>
                                        
                                        <!-- Vista previa -->
                                        <div id="vistaPrevia" class="text-center mb-3"></div>
                                        
                                        <div class="form-group">
                                            <label for="url">URL de Destino *</label>
                                            <input type="url" class="form-control" id="url" name="url" 
                                                   value="<?= $_SESSION['old_data']['url'] ?? $banner->url ?>" required>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="posicion">Posición</label>
                                            <select class="form-control select2" id="posicion" name="posicion">
                                                <?php foreach ($posiciones as $key => $value): ?>
                                                    <option value="<?= $key ?>" <?= (($_SESSION['old_data']['posicion'] ?? $banner->posicion) == $key) ? 'selected' : '' ?>>
                                                        <?= $value ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="texto_boton">Texto del Botón</label>
                                                    <input type="text" class="form-control" id="texto_boton" name="texto_boton" 
                                                           value="<?= $_SESSION['old_data']['texto_boton'] ?? $banner->texto_boton ?>">
                                                </div>
                                            </div>
                                            
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="icono_boton">Icono del Botón (FontAwesome)</label>
                                                    <input type="text" class="form-control" id="icono_boton" name="icono_boton" 
                                                           value="<?= $_SESSION['old_data']['icono_boton'] ?? $banner->icono_boton ?>"
                                                           placeholder="fas fa-arrow-right">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="card-footer">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save"></i> Guardar Cambios
                                        </button>
                                        <a href="banners.php?action=ver&id=<?= $banner->id ?>" class="btn btn-secondary">Cancelar</a>
                                    </div>
                                </form>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Información</h3>
                                </div>
                                <div class="card-body">
                                    <p><strong>Estado:</strong> 
                                        <?php if ($banner->estado_actual == 'programado'): ?>
                                            <span class="badge badge-info">Programado</span>
                                        <?php else: ?>
                                            <span class="badge badge-warning">Inactivo</span>
                                        <?php endif; ?>
                                    </p>
                                    
                                    <p><strong>Duración contratada:</strong><br>
                                        <?php
                                        $duraciones = [
                                            '3_dias' => '3 Días',
                                            '1_semana' => '1 Semana',
                                            '1_mes' => '1 Mes'
                                        ];
                                        echo $duraciones[$banner->tipo_duracion] ?? $banner->tipo_duracion;
                                        ?>
                                    </p>
                                    
                                    <p><strong>Período:</strong><br>
                                        <?= date('d/m/Y', strtotime($banner->fecha_inicio)) ?> - 
                                        <?= date('d/m/Y', strtotime($banner->fecha_fin)) ?>
                                    </p>
                                    
                                    <hr>
                                    
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle"></i>
                                        Solo puedes editar banners que aún no han sido activados.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
       

        <?php include '../../Layauts/footer_tienda-01.php'; ?>
 
    
    
