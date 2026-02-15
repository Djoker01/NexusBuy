<?php
include_once '../Layauts/header_admin.php';
?>
<title>Gestión de Banners | NexusBuy Admin</title>

<!-- Estilos adicionales -->
<style>
    .banner-thumb {
        width: 80px;
        height: 60px;
        object-fit: cover;
        border-radius: 4px;
    }
    .badge-posicion {
        background-color: #e9ecef;
        color: #495057;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 0.85rem;
    }
    .orden-input {
        width: 60px;
        text-align: center;
    }
</style>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Gestión de Banners</h1>
            </div>
            <div class="col-sm-6">
                <button class="btn btn-primary float-right" onclick="location.href='banner_form.php'">
                    <i class="fas fa-plus mr-2"></i>Nuevo Banner
                </button>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <!-- Filtros -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Filtros</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Posición</label>
                            <select class="form-control" id="filtroPosicion">
                                <option value="">Todas</option>
                                <option value="slider_principal">Slider Principal</option>
                                <option value="lateral_derecho">Lateral Derecho</option>
                                <option value="superior">Superior</option>
                                <option value="inferior">Inferior</option>
                                <option value="popup">Popup</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Estado</label>
                            <select class="form-control" id="filtroEstado">
                                <option value="">Todos</option>
                                <option value="1">Activos</option>
                                <option value="0">Inactivos</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Buscar</label>
                            <input type="text" class="form-control" id="busqueda" 
                                   placeholder="Título o descripción...">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <button class="btn btn-primary btn-block" id="btnFiltrar">
                                <i class="fas fa-search mr-2"></i>Filtrar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabla de banners -->
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th style="width: 30px">#</th>
                                <th>Imagen</th>
                                <th>Título</th>
                                <th>Posición</th>
                                <th>Fechas</th>
                                <th>Orden</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="tablaBanners">
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <i class="fas fa-spinner fa-spin fa-2x text-primary"></i>
                                    <p class="mt-2">Cargando banners...</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
                <div class="row mt-3">
                    <div class="col-sm-6">
                        <div class="dataTables_info" id="infoPaginacion"></div>
                    </div>
                    <div class="col-sm-6">
                        <nav aria-label="Page navigation">
                            <ul class="pagination justify-content-end" id="paginacion"></ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Modal previsualización -->
<div class="modal fade" id="previewModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Previsualizar Banner</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body text-center" id="previewContent"></div>
        </div>
    </div>
</div>

<?php
include_once '../Layauts/footer_admin.php';
?>
<script src="../../Util/Js/banner-admin.js"></script>