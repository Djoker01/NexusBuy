<?php
include_once 'Layauts/header_general.php';
?>
<!-- Modal -->
<div class="modal fade" id="modal_contra" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Cambiar Password</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="form-contra">
          <div class="form-group">
            <label for="pass_old">Ingrese password actual</label>
            <input type="password" name="pass_old" class="form-control" id="pass_old" placeholder="Ingrese contraseña actual">
          </div>
          <div class="form-group">
            <label for="pass_new">Ingrese nuevo password</label>
            <input type="password" name="pass_new" class="form-control" id="pass_new" placeholder="Ingrese nueva contraseña">
          </div>
          <div class="form-group">
            <label for="pass_repeat">Repita su nuevo password</label>
            <input type="password" name="pass_repeat" class="form-control" id="pass_repeat" placeholder="Repita su contraseña nueva">
          </div>
        </div>
      <div class="modal-footer">
      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
      <button type="submit" class="btn btn-primary">Guardar</button>
      </form>
      </div>
    </div>
  </div>
 </div>

<div class="modal fade" id="modal_datos" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Editar datos personales</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="form-datos" enctype="multipart/form-data">
          <div class="form-group">
            <label for="nombres_mod">Nombres</label>
            <input type="text" name="nombres_mod" class="form-control" id="nombres_mod" placeholder="Ingrese Nombres">
          </div>
          <div class="form-group">
            <label for="apellidos_mod">Apellidos</label>
            <input type="text" name="apellidos_mod" class="form-control" id="apellidos_mod" placeholder="Ingrese Apellidos">
          </div>
          <div class="form-group">
            <label for="dni_mod">DNI</label>
            <input type="text" name="dni_mod" class="form-control" id="dni_mod" placeholder="Ingrese DNI">
          </div>
          <div class="form-group">
            <label for="email_mod">Email</label>
            <input type="text" name="email_mod" class="form-control" id="email_mod" placeholder="Ingrese Email">
          </div>
          <div class="form-group">
            <label for="telefono_mod">Teléfono</label>
            <input type="text" name="telefono_mod" class="form-control" id="telefono_mod" placeholder="Ingrese Teléfono">
          </div>
          <div class="form-group">
                    <label for="exampleInputFile">Avatar</label>
                    <div class="input-group">
                      <div class="custom-file">
                        <input type="file" class="custom-file-input" name="avatar_mod" id="avatar_mod">
                        <label class="custom-file-label" for="exampleInputFile">Seleccione un Avatar</label>
                      </div>
                    </div>
          </div>
        </div>
      <div class="modal-footer">
      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
      <button type="submit" class="btn btn-primary">Guardar</button>
      </form>
      </div>
    </div>
  </div>
 </div>

 <div class="modal fade" id="modal_direcciones" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Agregar direcciones</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="form-direccion">
          <div class="form-group">
            <label for="">Provincia: </label>
            <select id="provincia" class="form-control" style="width:100%" required></select>
          </div>
          <div class="form-group">
            <label for="">Municipio: </label>
            <select id="municipio" class="form-control" style="width:100%" required></select>
          </div>
          <div class="form-group">
            <label for="">Dirección: </label>
            <input id="direccion" class="form-control" placeholder="Ingrese su dirección" required></input>
          </div>
        
      </div>
      <div class="modal-footer">
      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
      <button type="submit" class="btn btn-primary">Crear</button>
      </form>
      </div>
    </div>
  </div>
 </div>

 <!-- Modal Exportar Datos -->
<div class="modal fade" id="modalExportarDatos" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Exportar Mis Datos</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Selecciona qué datos quieres exportar:</p>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" id="exportar-perfil" checked>
                    <label class="form-check-label" for="exportar-perfil">Información del perfil</label>
                </div>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" id="exportar-pedidos" checked>
                    <label class="form-check-label" for="exportar-pedidos">Historial de pedidos</label>
                </div>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" id="exportar-resenas" checked>
                    <label class="form-check-label" for="exportar-resenas">Reseñas y calificaciones</label>
                </div>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" id="exportar-direcciones">
                    <label class="form-check-label" for="exportar-direcciones">Direcciones guardadas</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="exportar-preferencias">
                    <label class="form-check-label" for="exportar-preferencias">Preferencias y configuración</label>
                </div>
                
                <div class="mt-3">
                    <label for="formato-exportacion" class="font-weight-bold">Formato de exportación:</label>
                    <select class="form-control" id="formato-exportacion">
                        <option value="json">JSON</option>
                        <option value="csv">CSV</option>
                        <option value="pdf">PDF</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="confirmarExportacion()">
                    <i class="fas fa-download mr-2"></i>Exportar
                </button>
            </div>
        </div>
    </div>
</div>
 <!-- /Modal -->
<title>Mi perfil | NexusBuy</title>
<section class="content">
      <div class="container-fluid">
        <div class="row">
          <div class="col-md-3">

            <div class="card card-widget widget-user">
                <!-- Add the bg color to the header using any of the bg-* classes -->
                <div class="widget-user-header bg-info">
                    <h3 id="username" class="widget-user-username"></h3>
                    <h5 id="tipo_usuario" class="widget-user-desc"></h5>
                </div>
                <div class="widget-user-image">
                    <img id="avatar_perfil" class="img-circle elevation-2" src="" alt="User Avatar">
                </div>
                <div class="card-footer" id="vendedor_datos">
                    <div class="row">
                    <div class="col-sm-4 border-right">
                        <div class="description-block">
                        <h5 class="description-header">3,200</h5>
                        <span class="description-text">SALES</span>
                        </div>
                        <!-- /.description-block -->
                    </div>
                    <!-- /.col -->
                    <div class="col-sm-4 border-right">
                        <div class="description-block">
                        <h5 class="description-header">13,000</h5>
                        <span class="description-text">FOLLOWERS</span>
                        </div>
                        <!-- /.description-block -->
                    </div>
                    <!-- /.col -->
                    <div class="col-sm-4">
                        <div class="description-block">
                        <h5 class="description-header">35</h5>
                        <span class="description-text">PRODUCTS</span>
                        </div>
                        <!-- /.description-block -->
                    </div>
                    <!-- /.col -->
                    </div>
                    <!-- /.row -->
                </div>
                </div>
            

            <!-- About Me Box -->
            <div class="card card-light">
                <div class="card-header border-bottom-0">
                  <strong>Mis datos personales</strong>
                  <div class="card-tools">
                    <button type="button" class="editar_datos btn btn-tool" data-bs-toggle="modal" data-bs-target="#modal_datos">
                        <i class="fas fa-pencil-alt"></i>
                    </button>
                  </div>
                </div>
                <div class="card-body pt-0 mt-3">
                  <div class="row">
                    <div class="col-8">
                      <h2 id="nombres" class="lead"><b></b></h2>
                      <ul class="ml-4 mb-0 fa-ul text-muted">
                        <li class="small"><span class="fa-li"><i class="fas fa-address-card"></i></span>DNI: <span id="dni"></span></li>
                        <li class="small"><span class="fa-li"><i class="fas fa-at"></i></span>Email: <span id="email"></span></li>
                        <li class="small"><span class="fa-li"><i class="fas fa-lg fa-phone"></i></span>Teléfono: <span id="telefono"></span></li>
                      </ul>
                    </div>
                    <div class="col-4 text-center">
                      <img src="../Util/img/datos.png" alt="user-avatar" class="img-circle img-fluid">
                    </div>
                  </div>
                </div>
                <div class="card-footer">
                  <button class="btn btn-warning btn-block" data-bs-toggle="modal" data-bs-target="#modal_contra">Cambiar Password</button>
                </div>
              </div>
              <div class="card">
                <div class="card-header border-bottom-0">
                  <h5 class="card-title">Mis direcciones de envio</h5>
                  <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-bs-toggle="modal" data-bs-target="#modal_direcciones">
                        <i class="fas fa-plus"></i>
                    </button>
                  </div>
                </div>
                <div id="direcciones" class="card-body pt-0 mt-3">
                  
                </div>
              </div>
              <div class="card card-light">
                <div class="card-header border-bottom-0">
                  <strong>Mis tarjetas de pago</strong>
                  <div class="card-tools">
                    <button type="button" class="btn btn-tool">
                        <i class="fas fa-plus"></i>
                    </button>
                  </div>
                </div>
                <div class="card-body pt-0 mt-3">
                  <div class="row">
                    <div class="col-8">
                      <h2 class="lead"><b>Nicole Pearson</b></h2>
                      <p class="text-muted text-sm"><b>About: </b> Web Designer / UX / Graphic Artist / Coffee Lover </p>
                      <ul class="ml-4 mb-0 fa-ul text-muted">
                        <li class="small"><span class="fa-li"><i class="fas fa-lg fa-building"></i></span> Address: Demo Street 123, Demo City 04312, NJ</li>
                        <li class="small"><span class="fa-li"><i class="fas fa-lg fa-phone"></i></span> Phone #: + 800 - 12 12 23 52</li>
                      </ul>
                    </div>
                    <div class="col-4 text-center">
                      <img src="../Util/img/credito.png" alt="user-avatar" class="img-circle img-fluid">
                    </div>
                  </div>
                </div>
              </div>
            <!-- /.card -->
          </div>
          <!-- /.col -->
          <div class="col-md-9">
            <div class="card">
              <div class="card-header p-2">
              <ul class="nav nav-pills">
                <li class="nav-item"><a class="nav-link active" href="#activity" data-toggle="tab">Actividad</a></li>
                <li class="nav-item"><a class="nav-link" href="#timeline" data-toggle="tab">Historial</a></li>
                <li class="nav-item"><a class="nav-link" href="#tienda" data-toggle="tab">Mi Tienda</a></li>
                <li class="nav-item"><a class="nav-link" href="#configuracion" data-toggle="tab">Configuración</a></li>
            </ul>
              </div><!-- /.card-header -->
              <div class="card-body">
                <div class="tab-content">
                  <div class="active tab-pane" id="activity">
                    <div class="card">
                      <div class="card-header">
                          <h5 class="card-title mb-0">
                              <i class="fas fa-history mr-2"></i>Mi Actividad Reciente
                          </h5>
                      </div>
                      <div class="card-body">
                          <!-- Filtros de actividad -->
                          <div class="row mb-4">
                              <div class="col-md-6">
                                  <select class="form-control" id="filtro-actividad">
                                      <option value="">Todas las actividades</option>
                                      <option value="compra">Compras</option>
                                      <option value="reseña">Reseñas</option>
                                      <option value="perfil">Cambios de perfil</option>
                                      <option value="direccion">Direcciones</option>
                                  </select>
                              </div>
                              <div class="col-md-6">
                                  <select class="form-control" id="filtro-periodo">
                                      <option value="7">Última semana</option>
                                      <option value="30" selected>Último mes</option>
                                      <option value="90">Últimos 3 meses</option>
                                      <option value="365">Último año</option>
                                      <option value="0">Todo el tiempo</option>
                                  </select>
                              </div>
                          </div>

                          <!-- Lista de actividad -->
                          <div id="lista-actividad">
                              <div class="text-center py-5">
                                  <div class="spinner-border text-primary" role="status">
                                      <span class="sr-only">Cargando actividad...</span>
                                  </div>
                                  <p class="text-muted mt-2">Cargando tu actividad...</p>
                              </div>
                          </div>

                          <!-- Estado vacío -->
                          <div id="actividad-vacia" class="text-center py-5" style="display: none;">
                              <div class="empty-activity-icon mb-3">
                                  <i class="fas fa-history fa-4x text-muted"></i>
                              </div>
                              <h4 class="text-muted">No hay actividad reciente</h4>
                              <p class="text-muted mb-4">Tu actividad aparecerá aquí cuando realices acciones en la plataforma.</p>
                              <a href="../index.php" class="btn btn-primary">
                                  <i class="fas fa-shopping-bag mr-2"></i>Explorar productos
                              </a>
                          </div>
                      </div>
                  </div>

                  <!-- Estadísticas rápidas -->
                  <div class="row mt-4">
                      <div class="col-md-3">
                          <div class="info-box bg-gradient-info">
                              <span class="info-box-icon"><i class="fas fa-shopping-cart"></i></span>
                              <div class="info-box-content">
                                  <span class="info-box-text">Total Pedidos</span>
                                  <span class="info-box-number" id="total-pedidos">0</span>
                              </div>
                          </div>
                      </div>
                      <div class="col-md-3">
                          <div class="info-box bg-gradient-success">
                              <span class="info-box-icon"><i class="fas fa-star"></i></span>
                              <div class="info-box-content">
                                  <span class="info-box-text">Reseñas</span>
                                  <span class="info-box-number" id="total-resenas">0</span>
                              </div>
                          </div>
                      </div>
                      <div class="col-md-3">
                          <div class="info-box bg-gradient-warning">
                              <span class="info-box-icon"><i class="fas fa-user-edit"></i></span>
                              <div class="info-box-content">
                                  <span class="info-box-text">Actualizaciones</span>
                                  <span class="info-box-number" id="total-actualizaciones">0</span>
                              </div>
                          </div>
                      </div>
                      <div class="col-md-3">
                          <div class="info-box bg-gradient-danger">
                              <span class="info-box-icon"><i class="fas fa-map-marker-alt"></i></span>
                              <div class="info-box-content">
                                  <span class="info-box-text">Direcciones</span>
                                  <span class="info-box-number" id="total-direcciones">0</span>
                              </div>
                          </div>
                      </div>
                  </div>
              </div>
                  <!-- /.tab-pane -->
                  <div class="tab-pane" id="timeline">
                    <!-- The timeline -->
                    <div id="historiales" class="timeline timeline-inverse">
                      <!-- timeline time label -->
                      
                    </div>
                  </div>
                  
                  <!-- /.tab-pane -->
                  <div class="tab-pane" id="configuracion">
                    <div class="row">
                      <div class="col-md-8">
                          <!-- Configuración de Notificaciones -->
                          <div class="card mb-4">
                              <div class="card-header">
                                  <h5 class="card-title mb-0">
                                      <i class="fas fa-bell mr-2"></i>Preferencias de Notificaciones
                                  </h5>
                              </div>
                              <div class="card-body">
                                  <form id="form-notificaciones">
                                      <div class="form-group">
                                          <div class="custom-control custom-switch mb-3">
                                              <input type="checkbox" class="custom-control-input" id="notificacion-email" checked>
                                              <label class="custom-control-label" for="notificacion-email">
                                                  <strong>Notificaciones por Email</strong>
                                                  <small class="d-block text-muted">Recibir notificaciones sobre tus pedidos y promociones</small>
                                              </label>
                                          </div>
                                          
                                          <div class="custom-control custom-switch mb-3">
                                              <input type="checkbox" class="custom-control-input" id="notificacion-pedidos" checked>
                                              <label class="custom-control-label" for="notificacion-pedidos">
                                                  <strong>Actualizaciones de Pedidos</strong>
                                                  <small class="d-block text-muted">Notificaciones cuando tu pedido cambie de estado</small>
                                              </label>
                                          </div>
                                          
                                          <div class="custom-control custom-switch mb-3">
                                              <input type="checkbox" class="custom-control-input" id="notificacion-promociones">
                                              <label class="custom-control-label" for="notificacion-promociones">
                                                  <strong>Promociones y Ofertas</strong>
                                                  <small class="d-block text-muted">Recibir ofertas especiales y descuentos</small>
                                              </label>
                                          </div>
                                          
                                          <div class="custom-control custom-switch mb-3">
                                              <input type="checkbox" class="custom-control-input" id="notificacion-productos">
                                              <label class="custom-control-label" for="notificacion-productos">
                                                  <strong>Productos Nuevos</strong>
                                                  <small class="d-block text-muted">Notificaciones sobre nuevos productos en categorías de interés</small>
                                              </label>
                                          </div>
                                          
                                          <div class="custom-control custom-switch">
                                              <input type="checkbox" class="custom-control-input" id="notificacion-resenas">
                                              <label class="custom-control-label" for="notificacion-resenas">
                                                  <strong>Recordatorios de Reseñas</strong>
                                                  <small class="d-block text-muted">Recordatorios para reseñar productos comprados</small>
                                              </label>
                                          </div>
                                      </div>
                                      
                                      <div class="text-right mt-4">
                                          <button type="submit" class="btn btn-primary">
                                              <i class="fas fa-save mr-2"></i>Guardar Preferencias
                                          </button>
                                      </div>
                                  </form>
                              </div>
                          </div>

                          <!-- Preferencias de Privacidad -->
                          <div class="card mb-4">
                              <div class="card-header">
                                  <h5 class="card-title mb-0">
                                      <i class="fas fa-shield-alt mr-2"></i>Configuración de Privacidad
                                  </h5>
                              </div>
                              <div class="card-body">
                                  <form id="form-privacidad">
                                      <div class="form-group">
                                          <div class="custom-control custom-switch mb-3">
                                              <input type="checkbox" class="custom-control-input" id="privacidad-perfil" checked>
                                              <label class="custom-control-label" for="privacidad-perfil">
                                                  <strong>Perfil Público</strong>
                                                  <small class="d-block text-muted">Permitir que otros usuarios vean tu perfil y reseñas</small>
                                              </label>
                                          </div>
                                          
                                          <div class="custom-control custom-switch mb-3">
                                              <input type="checkbox" class="custom-control-input" id="privacidad-actividad">
                                              <label class="custom-control-label" for="privacidad-actividad">
                                                  <strong>Actividad Pública</strong>
                                                  <small class="d-block text-muted">Mostrar tu actividad reciente a otros usuarios</small>
                                              </label>
                                          </div>
                                          
                                          <div class="custom-control custom-switch mb-3">
                                              <input type="checkbox" class="custom-control-input" id="privacidad-busqueda">
                                              <label class="custom-control-label" for="privacidad-busqueda">
                                                  <strong>Aparecer en Búsquedas</strong>
                                                  <small class="d-block text-muted">Permitir que otros usuarios te encuentren en búsquedas</small>
                                              </label>
                                          </div>
                                          
                                          <div class="custom-control custom-switch">
                                              <input type="checkbox" class="custom-control-input" id="privacidad-datos" checked>
                                              <label class="custom-control-label" for="privacidad-datos">
                                                  <strong>Compartir Datos Anónimos</strong>
                                                  <small class="d-block text-muted">Compartir datos de uso anónimos para mejorar la plataforma</small>
                                              </label>
                                          </div>
                                      </div>
                                      
                                      <div class="text-right mt-4">
                                          <button type="submit" class="btn btn-primary">
                                              <i class="fas fa-save mr-2"></i>Guardar Configuración
                                          </button>
                                      </div>
                                  </form>
                              </div>
                          </div>
                      </div>

                      <div class="col-md-4">
                          <!-- Preferencias de Visualización -->
                          <div class="card mb-4">
                              <div class="card-header">
                                  <h5 class="card-title mb-0">
                                      <i class="fas fa-palette mr-2"></i>Preferencias de Visualización
                                  </h5>
                              </div>
                              <div class="card-body">
                                  <form id="form-visualizacion">
                                      <div class="form-group">
                                          <label for="tema-interface" class="font-weight-bold">Tema de Interfaz</label>
                                          <select class="form-control" id="tema-interface">
                                              <option value="claro">Claro</option>
                                              <option value="oscuro">Oscuro</option>
                                              <option value="auto">Automático (según sistema)</option>
                                          </select>
                                          <small class="text-muted">Elige cómo quieres ver la plataforma</small>
                                      </div>
                                      
                                      <div class="form-group">
                                          <label for="densidad-interface" class="font-weight-bold">Densidad de Interfaz</label>
                                          <select class="form-control" id="densidad-interface">
                                              <option value="comoda">Cómoda (más espacio)</option>
                                              <option value="normal" selected>Normal</option>
                                              <option value="compacta">Compacta (más contenido)</option>
                                          </select>
                                          <small class="text-muted">Controla el espaciado entre elementos</small>
                                      </div>
                                      
                                      <div class="form-group">
                                          <label for="idioma-interface" class="font-weight-bold">Idioma</label>
                                          <select class="form-control" id="idioma-interface">
                                              <option value="es" selected>Español</option>
                                              <option value="en">English</option>
                                              <option value="pt">Português</option>
                                              <option value="fr">Français</option>
                                          </select>
                                          <small class="text-muted">Idioma de la interfaz</small>
                                      </div>
                                      
                                      <div class="form-group">
                                          <label for="moneda-interface" class="font-weight-bold">Moneda Predeterminada</label>
                                          <select class="form-control" id="moneda-interface">
                                            <option value="CUP">CUP - Peso Cubano</option>
                                              <option value="USD">USD - Dólar Americano</option>
                                              <option value="EUR" selected>EUR - Euro</option>
                                              <option value="MXN">MXN - Peso Mexicano</option>
                                              <option value="COP">COP - Peso Colombiano</option>
                                              <option value="ARS">ARS - Peso Argentino</option>
                                          </select>
                                          <small class="text-muted">Moneda para mostrar precios</small>
                                      </div>
                                      
                                      <div class="text-right mt-4">
                                          <button type="submit" class="btn btn-primary">
                                              <i class="fas fa-save mr-2"></i>Guardar Preferencias
                                          </button>
                                      </div>
                                  </form>
                              </div>
                          </div>

                          <!-- Configuración Avanzada -->
                          <div class="card">
                              <div class="card-header bg-light">
                                  <h5 class="card-title mb-0">
                                      <i class="fas fa-cogs mr-2"></i>Configuración Avanzada
                                  </h5>
                              </div>
                              <div class="card-body">
                                  <div class="mb-3">
                                      <button type="button" class="btn btn-outline-secondary btn-block mb-2" onclick="exportarDatos()">
                                          <i class="fas fa-download mr-2"></i>Exportar Mis Datos
                                      </button>
                                      <small class="text-muted">Descarga una copia de toda tu información</small>
                                  </div>
                                  
                                  <div class="mb-3">
                                      <button type="button" class="btn btn-outline-warning btn-block mb-2" onclick="limpiarHistorial()">
                                          <i class="fas fa-broom mr-2"></i>Limpiar Historial Local
                                      </button>
                                      <small class="text-muted">Elimina datos temporales del navegador</small>
                                  </div>
                                  
                                  <div class="mb-3">
                                      <button type="button" class="btn btn-outline-info btn-block mb-2" onclick="restablecerPreferencias()">
                                          <i class="fas fa-undo mr-2"></i>Restablecer Preferencias
                                      </button>
                                      <small class="text-muted">Vuelve a la configuración por defecto</small>
                                  </div>
                                  
                                  <hr>
                                  
                                  <div class="text-center">
                                      <button type="button" class="btn btn-outline-danger" onclick="eliminarCuenta()">
                                          <i class="fas fa-trash mr-2"></i>Eliminar Mi Cuenta
                                      </button>
                                  </div>
                              </div>
                          </div>
                      </div>
                  </div>
                </div>
                <div class="tab-pane" id="tienda">
    <div id="estado_tienda">
        <!-- Se cargará dinámicamente el estado de la tienda -->
        <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status">
                <span class="sr-only">Cargando...</span>
            </div>
            <p class="text-muted mt-2">Verificando estado de tu tienda...</p>
        </div>
    </div>

    <!-- Formulario para registrar tienda (se muestra solo si no tiene tienda) -->
    <div id="formulario_tienda" style="display: none;">
        <div class="card card-success">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-store mr-2"></i>Registrar Tu Tienda</h3>
            </div>
            <div class="card-body">
                <form id="form-registro-tienda">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="nombre_tienda">Nombre de la Tienda *</label>
                                <input type="text" class="form-control" id="nombre_tienda" name="nombre_tienda" 
                                       placeholder="Ej: Mi Tienda Online" required maxlength="100">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="razon_social">Razón Social *</label>
                                <input type="text" class="form-control" id="razon_social" name="razon_social" 
                                       placeholder="Ej: Mi Empresa S.A. de C.V." required maxlength="200">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="ruc_tienda">RUC/RFC *</label>
                                <input type="text" class="form-control" id="ruc_tienda" name="ruc_tienda" 
                                       placeholder="Ej: 12345678901" required maxlength="20">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="provincia_tienda">Provincia *</label>
                                <select class="form-control" id="provincia_tienda" name="provincia_tienda" required>
                                    <option value="">Selecciona una provincia</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="municipio_tienda">Municipio *</label>
                                <select class="form-control" id="municipio_tienda" name="municipio_tienda" required disabled>
                                    <option value="">Primero selecciona una provincia</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="direccion_tienda">Dirección *</label>
                                <input type="text" class="form-control" id="direccion_tienda" name="direccion_tienda" 
                                       placeholder="Ej: Av. Principal #123" required maxlength="255">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="descripcion_tienda">Descripción de la Tienda</label>
                        <textarea class="form-control" id="descripcion_tienda" name="descripcion_tienda" 
                                  rows="3" placeholder="Describe los productos o servicios que ofreces..." 
                                  maxlength="500"></textarea>
                        <small class="form-text text-muted">
                            <span id="contador_descripcion">0</span>/500 caracteres
                        </small>
                    </div>

                    <div class="form-group">
                        <label for="imagen_tienda">Logo de la Tienda (Opcional)</label>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" id="imagen_tienda" name="imagen_tienda" 
                                   accept="image/*">
                            <label class="custom-file-label" for="imagen_tienda">Seleccionar imagen...</label>
                        </div>
                        <small class="form-text text-muted">Formatos: JPG, PNG, GIF. Máx: 2MB</small>
                    </div>

                    <div class="form-check mb-3">
                        <input type="checkbox" class="form-check-input" id="terminos_tienda" required>
                        <label class="form-check-label" for="terminos_tienda">
                            Acepto los <a href="#" target="_blank">términos y condiciones</a> para vendedores
                        </label>
                    </div>

                    <div class="text-center">
                        <button type="submit" class="btn btn-success btn-lg">
                            <i class="fas fa-store mr-2"></i>
                            Registrar Mi Tienda
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Panel de gestión de tienda (se muestra si ya tiene tienda) -->
    <div id="panel_gestion_tienda" style="display: none;">
        <div class="row">
            <div class="col-md-4">
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title">Resumen de Tienda</h3>
                    </div>
                    <div class="card-body">
                        <div class="text-center">
                            <img id="logo_tienda" src="" class="img-fluid rounded-circle mb-3" 
                                 style="width: 100px; height: 100px; object-fit: cover;" alt="Logo Tienda">
                            <h4 id="nombre_tienda_mostrar"></h4>
                            <p class="text-muted" id="estado_tienda_mostrar"></p>
                        </div>
                        <div class="mt-3">
                            <p><strong>RUC:</strong> <span id="ruc_tienda_mostrar"></span></p>
                            <p><strong>Dirección:</strong> <span id="direccion_tienda_mostrar"></span></p>
                            <p><strong>Ubicación:</strong> <span id="ubicacion_tienda_mostrar"></span></p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-8">
                <div class="card card-warning">
                    <div class="card-header">
                        <h3 class="card-title">Acciones Rápidas</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <a href="gestion_productos.php" class="btn btn-outline-primary btn-block">
                                    <i class="fas fa-boxes mr-2"></i>
                                    Gestionar Productos
                                </a>
                            </div>
                            <div class="col-md-6 mb-3">
                                <a href="pedidos_tienda.php" class="btn btn-outline-success btn-block">
                                    <i class="fas fa-shopping-cart mr-2"></i>
                                    Ver Pedidos
                                </a>
                            </div>
                            <div class="col-md-6 mb-3">
                                <a href="estadisticas_tienda.php" class="btn btn-outline-info btn-block">
                                    <i class="fas fa-chart-bar mr-2"></i>
                                    Estadísticas
                                </a>
                            </div>
                            <div class="col-md-6 mb-3">
                                <button class="btn btn-outline-secondary btn-block" id="editar_tienda">
                                    <i class="fas fa-edit mr-2"></i>
                                    Editar Tienda
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
                  <!-- /.tab-pane -->
                </div>
                <!-- /.tab-content -->
              </div><!-- /.card-body -->
            </div>
            <!-- /.card -->
          </div>
          <!-- /.col -->
        </div>
        <!-- /.row -->
      </div><!-- /.container-fluid -->
    </section>
<?php
include_once 'Layauts/footer_general.php';
?>
<script src="mi_perfil.js"></script>
