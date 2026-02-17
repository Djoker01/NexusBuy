<?php
include '../../Layauts/sidebar_tienda.php';
?>

<title>Crear Banner Publicitario - Mi Tienda</title>
    
     

        
            <section class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <h1>Crear Nuevo Banner</h1>
                        </div>
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item"><a href="banners.php?action=index">Banners</a></li>
                                <li class="breadcrumb-item active">Crear</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </section>

            <section class="content">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-md-8">
                            <!-- Formulario Principal -->
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
                                
                                <form method="POST" action="banners.php?action=guardar" enctype="multipart/form-data" data-validation="true">
                                    <input type="hidden" id="saldoDisponible" value="<?= $saldo ?? 0 ?>">
                                    
                                    <div class="card-body">
                                        <div class="form-group">
                                            <label for="titulo">Título del Banner *</label>
                                            <input type="text" class="form-control" id="titulo" name="titulo" 
                                                   value="<?= $_SESSION['old_data']['titulo'] ?? '' ?>" required>
                                            <small class="text-muted">Máximo 255 caracteres</small>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="descripcion">Descripción</label>
                                            <textarea class="form-control" id="descripcion" name="descripcion" rows="3"><?= $_SESSION['old_data']['descripcion'] ?? '' ?></textarea>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="imagen">Imagen del Banner *</label>
                                            <div class="custom-file">
                                                <input type="file" class="custom-file-input" id="imagen" name="imagen" accept="image/*" required>
                                                <label class="custom-file-label" for="imagen">Seleccionar imagen</label>
                                            </div>
                                            <small class="text-muted">Formatos: JPG, PNG, GIF, WebP. Máximo 2MB. Tamaño recomendado: 1200x400px</small>
                                        </div>
                                        
                                        <!-- Vista previa -->
                                        <div id="vistaPrevia" class="text-center mb-3"></div>
                                        
                                        <div class="form-group">
                                            <label for="url">URL de Destino *</label>
                                            <input type="url" class="form-control" id="url" name="url" 
                                                   value="<?= $_SESSION['old_data']['url'] ?? '' ?>" 
                                                   placeholder="https://ejemplo.com" required>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="posicion">Posición</label>
                                                    <select class="form-control select2" id="posicion" name="posicion">
                                                        <?php foreach ($posiciones as $key => $value): ?>
                                                            <option value="<?= $key ?>" <?= ($_SESSION['old_data']['posicion'] ?? '') == $key ? 'selected' : '' ?>>
                                                                <?= $value ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            </div>
                                            
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="duracion">Duración *</label>
                                                    <select class="form-control" id="duracion" name="duracion" required>
                                                        <option value="">Seleccionar duración</option>
                                                        <?php if ($configuraciones): foreach ($configuraciones as $conf): ?>
                                                            <option value="<?= $conf->duracion_tipo ?>" 
                                                                    data-precio="<?= $conf->precio ?>"
                                                                    data-dias="<?= $conf->duracion_dias ?>"
                                                                    <?= ($_SESSION['old_data']['duracion'] ?? '') == $conf->duracion_tipo ? 'selected' : '' ?>>
                                                                <?= $conf->descripcion ?> - $<?= number_format($conf->precio, 2) ?>
                                                            </option>
                                                        <?php endforeach; endif; ?>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="texto_boton">Texto del Botón</label>
                                                    <input type="text" class="form-control" id="texto_boton" name="texto_boton" 
                                                           value="<?= $_SESSION['old_data']['texto_boton'] ?? 'Ver más' ?>">
                                                </div>
                                            </div>
                                            
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="icono_boton">Icono del Botón (FontAwesome)</label>
                                                    <input type="text" class="form-control" id="icono_boton" name="icono_boton" 
                                                           value="<?= $_SESSION['old_data']['icono_boton'] ?? '' ?>" 
                                                           placeholder="fas fa-arrow-right">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="card-footer">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="alert alert-info mb-0">
                                                    <strong>Saldo disponible:</strong> $<?= number_format($saldo ?? 0, 2) ?>
                                                </div>
                                            </div>
                                            <div class="col-md-6 text-right">
                                                <button type="submit" class="btn btn-primary" id="btnPublicar">
                                                    <i class="fas fa-credit-card"></i> Publicar Banner (<span id="costoSpan">$0</span>)
                                                </button>
                                                <a href="banners.php?action=index" class="btn btn-secondary">Cancelar</a>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                        
                        <!-- Panel de Resumen -->
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Resumen</h3>
                                </div>
                                <div class="card-body">
                                    <h5>Detalles de la publicación:</h5>
                                    <table class="table table-sm">
                                        <tr>
                                            <td>Duración:</td>
                                            <td id="resumenDuracion">-</td>
                                        </tr>
                                        <tr>
                                            <td>Fecha de inicio:</td>
                                            <td id="resumenInicio"><?= date('d/m/Y') ?></td>
                                        </tr>
                                        <tr>
                                            <td>Fecha de fin:</td>
                                            <td id="resumenFin">-</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Costo total:</strong></td>
                                            <td><strong id="resumenCosto">$0</strong></td>
                                        </tr>
                                    </table>
                                    
                                    <hr>
                                    
                                    <div class="alert alert-warning">
                                        <i class="fas fa-info-circle"></i>
                                        <small>El banner se activará automáticamente en la fecha de inicio. Recibirás notificaciones de su rendimiento.</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
  

<?php include '../../Layauts/footer_tienda-01.php'; ?>


    
    
