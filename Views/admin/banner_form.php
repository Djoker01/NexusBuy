<?php
include_once '../Layauts/header_admin.php';
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
?>
<title><?php echo $id ? 'Editar' : 'Nuevo'; ?> Banner | NexusBuy Admin</title>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1><?php echo $id ? 'Editar Banner' : 'Nuevo Banner'; ?></h1>
            </div>
            <div class="col-sm-6">
                <a href="banners.php" class="btn btn-secondary float-right">
                    <i class="fas fa-arrow-left mr-2"></i>Volver
                </a>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <form id="formBanner">
                            <input type="hidden" name="id" id="bannerId" value="<?php echo $id; ?>">
                            
                            <div class="form-group">
                                <label for="titulo">Título *</label>
                                <input type="text" class="form-control" id="titulo" name="titulo" required>
                            </div>

                            <div class="form-group">
                                <label for="descripcion">Descripción</label>
                                <textarea class="form-control" id="descripcion" name="descripcion" rows="3"></textarea>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="posicion">Posición *</label>
                                        <select class="form-control" id="posicion" name="posicion" required>
                                            <option value="">Seleccione...</option>
                                            <option value="slider_principal">Slider Principal</option>
                                            <option value="lateral_derecho">Lateral Derecho</option>
                                            <option value="superior">Superior</option>
                                            <option value="inferior">Inferior</option>
                                            <option value="popup">Popup</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="orden">Orden</label>
                                        <input type="number" class="form-control" id="orden" name="orden" value="0" min="0">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="fecha_inicio">Fecha Inicio *</label>
                                        <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="fecha_fin">Fecha Fin *</label>
                                        <input type="date" class="form-control" id="fecha_fin" name="fecha_fin" required>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="url">URL de destino</label>
                                <input type="text" class="form-control" id="url" name="url" placeholder="Ej: Views/producto.php?categoria=electronica">
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="texto_boton">Texto del botón</label>
                                        <input type="text" class="form-control" id="texto_boton" name="texto_boton" value="Ver más">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="icono_boton">Icono del botón</label>
                                        <input type="text" class="form-control" id="icono_boton" name="icono_boton" value="fa-shopping-cart">
                                        <small class="text-muted">Clase de FontAwesome (ej: fa-tag, fa-star)</small>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="estado" name="estado" value="1" checked>
                                    <label class="custom-control-label" for="estado">Activo</label>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save mr-2"></i>Guardar Banner
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Imagen del Banner</h3>
                    </div>
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <img id="previewImagen" src="../../Util/Img/Banners/default_banner.jpg" 
                                 class="img-fluid border" style="max-height: 200px;">
                        </div>
                        
                        <div class="custom-file mb-3">
                            <input type="file" class="custom-file-input" id="imagen" accept="image/*">
                            <label class="custom-file-label" for="imagen">Seleccionar imagen</label>
                        </div>
                        
                        <small class="text-muted d-block">
                            <i class="fas fa-info-circle mr-1"></i>
                            Formatos: JPG, PNG, GIF, WEBP (max 2MB)
                        </small>
                        <input type="hidden" id="imagen_nombre" name="imagen_nombre">
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Información</h3>
                    </div>
                    <div class="card-body">
                        <p><i class="fas fa-info-circle text-primary mr-2"></i> Los banners se mostrarán automáticamente según las fechas configuradas.</p>
                        <p><i class="fas fa-sort text-success mr-2"></i> El orden determina la prioridad de visualización.</p>
                        <p><i class="fas fa-calendar text-warning mr-2"></i> Las fechas definen el período de publicación.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
include_once '../Layauts/footer_admin.php';
?>

<script>
    var bannerId = <?php echo $id; ?>;
</script>
<script src="../../Util/Js/banner-admin.js"></script>