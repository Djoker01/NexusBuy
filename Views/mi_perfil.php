<?php
session_start();

// Verificar que el usuario está logueado
if (empty($_SESSION['id'])) {
    header('Location: login.php');
    exit();
}

include_once 'Layauts/header_general.php';
?>

<title>Mi Perfil | NexusBuy</title>

<!-- Modales -->
<div class="modal fade modal-modern" id="modal_contra" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-key mr-2"></i>Cambiar Contraseña
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="form-contra">
                    <div class="form-group-modern">
                        <label class="form-label-modern" for="pass_old">Contraseña Actual</label>
                        <input type="password" name="pass_old" class="form-control-modern" id="pass_old" placeholder="Ingresa tu contraseña actual" required>
                    </div>
                    <div class="form-group-modern">
                        <label class="form-label-modern" for="pass_new">Nueva Contraseña</label>
                        <input type="password" name="pass_new" class="form-control-modern" id="pass_new" placeholder="Ingresa tu nueva contraseña" required>
                        <small class="text-muted">Mínimo 8 caracteres, con letras y números</small>
                    </div>
                    <div class="form-group-modern">
                        <label class="form-label-modern" for="pass_repeat">Confirmar Nueva Contraseña</label>
                        <input type="password" name="pass_repeat" class="form-control-modern" id="pass_repeat" placeholder="Confirma tu nueva contraseña" required>
                    </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary-modern">
                    <i class="fas fa-save mr-2"></i>Actualizar Contraseña
                </button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade modal-modern" id="modal_datos" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-user-edit mr-2"></i>Editar Perfil
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="form-datos" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group-modern">
                                <label class="form-label-modern" for="nombres_mod">Nombres</label>
                                <input type="text" name="nombres_mod" class="form-control-modern" id="nombres_mod" placeholder="Ingresa tus nombres" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group-modern">
                                <label class="form-label-modern" for="apellidos_mod">Apellidos</label>
                                <input type="text" name="apellidos_mod" class="form-control-modern" id="apellidos_mod" placeholder="Ingresa tus apellidos" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group-modern">
                                <label class="form-label-modern" for="dni_mod">DNI</label>
                                <input type="text" name="dni_mod" class="form-control-modern" id="dni_mod" placeholder="Ingresa tu DNI" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group-modern">
                                <label class="form-label-modern" for="telefono_mod">Teléfono</label>
                                <input type="text" name="telefono_mod" class="form-control-modern" id="telefono_mod" placeholder="Ingresa tu teléfono" required>
                            </div>
                        </div>
                    </div>
                    <div class="form-group-modern">
                        <label class="form-label-modern" for="email_mod">Email</label>
                        <input type="email" name="email_mod" class="form-control-modern" id="email_mod" placeholder="Ingresa tu email" required>
                    </div>
                    <div class="form-group-modern">
                        <label class="form-label-modern">Foto de Perfil</label>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" name="avatar_mod" id="avatar_mod" accept="image/*">
                            <label class="custom-file-label" for="avatar_mod">Seleccionar imagen...</label>
                        </div>
                        <small class="text-muted">Formatos: JPG, PNG, GIF. Tamaño máximo: 2MB</small>
                    </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary-modern">
                    <i class="fas fa-save mr-2"></i>Guardar Cambios
                </button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade modal-modern" id="modal_direcciones" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-map-marker-alt mr-2"></i>Agregar Dirección
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="form-direccion">
                    <div class="form-group-modern">
                        <label class="form-label-modern">Provincia</label>
                        <select id="provincia" class="form-control-modern" required>
                            <option value="">Selecciona una provincia</option>
                        </select>
                    </div>
                    <div class="form-group-modern">
                        <label class="form-label-modern">Municipio</label>
                        <select id="municipio" class="form-control-modern" required disabled>
                            <option value="">Primero selecciona una provincia</option>
                        </select>
                    </div>
                    <div class="form-group-modern">
                        <label class="form-label-modern">Dirección</label>
                        <textarea id="direccion" class="form-control-modern" rows="3" placeholder="Ingresa tu dirección completa" required></textarea>
                    </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary-modern">
                    <i class="fas fa-plus mr-2"></i>Agregar Dirección
                </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Exportar Datos -->
<div class="modal fade modal-modern" id="modalExportarDatos" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-download mr-2"></i>Exportar Mis Datos
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Selecciona qué datos quieres exportar:</p>
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" id="exportar-perfil" checked>
                    <label class="form-check-label" for="exportar-perfil">
                        <strong>Información del perfil</strong>
                        <small class="d-block text-muted">Datos personales y configuración</small>
                    </label>
                </div>
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" id="exportar-pedidos" checked>
                    <label class="form-check-label" for="exportar-pedidos">
                        <strong>Historial de pedidos</strong>
                        <small class="d-block text-muted">Todos tus pedidos y transacciones</small>
                    </label>
                </div>
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" id="exportar-resenas" checked>
                    <label class="form-check-label" for="exportar-resenas">
                        <strong>Reseñas y calificaciones</strong>
                        <small class="d-block text-muted">Tus reseñas y opiniones</small>
                    </label>
                </div>
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" id="exportar-direcciones">
                    <label class="form-check-label" for="exportar-direcciones">
                        <strong>Direcciones guardadas</strong>
                        <small class="d-block text-muted">Tus direcciones de envío</small>
                    </label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="exportar-preferencias">
                    <label class="form-check-label" for="exportar-preferencias">
                        <strong>Preferencias y configuración</strong>
                        <small class="d-block text-muted">Configuración de notificaciones y privacidad</small>
                    </label>
                </div>

                <div class="mt-4">
                    <label for="formato-exportacion" class="form-label-modern">Formato de exportación:</label>
                    <select class="form-control-modern" id="formato-exportacion">
                        <option value="json">JSON (Recomendado)</option>
                        <option value="csv">CSV</option>
                        <option value="pdf">PDF</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary-modern" onclick="confirmarExportacion()">
                    <i class="fas fa-download mr-2"></i>Exportar Datos
                </button>
            </div>
        </div>
    </div>
</div>

<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Mi Perfil</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="../index.php">Home</a></li>
                    <li class="breadcrumb-item active">Mi Perfil</li>
                </ol>
            </div>
        </div>
    </div>
</section>

<section class="content">
    <div class="container-fluid">
        <!-- Profile Header -->
        <div class="profile-header-card">
            <div class="profile-cover"></div>
            <div class="profile-avatar">
                <img id="avatar_perfil" src="" alt="Avatar del usuario">
            </div>
            <div class="profile-info">
                <h1 class="profile-name" id="username">Cargando...</h1>
                <div class="profile-role" id="tipo_usuario">Usuario</div>
                <p class="text-muted mb-0" id="user_email">cargando@ejemplo.com</p>

                <div class="profile-stats">
                    <div class="stat-item">
                        <span class="stat-number" id="stat-pedidos">0</span>
                        <span class="stat-label">Pedidos</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number" id="stat-favoritos">0</span>
                        <span class="stat-label">Favoritos</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number" id="stat-resenas">0</span>
                        <span class="stat-label">Reseñas</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number" id="stat-puntos">0</span>
                        <span class="stat-label">Puntos</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-4">
                <!-- Personal Information Card -->
                <div class="sidebar-card">
                    <div class="card-header-modern">
                        <h5>Información Personal</h5>
                        <div class="card-tools-modern">
                            <button type="button" class="btn-tool-modern editar_datos" data-bs-toggle="modal" data-bs-target="#modal_datos">
                                <i class="fas fa-pencil-alt"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <ul class="personal-info-list">
                            <li class="personal-info-item">
                                <div class="personal-info-icon">
                                    <i class="fas fa-user"></i>
                                </div>
                                <div class="personal-info-content">
                                    <div class="personal-info-label">Nombre Completo</div>
                                    <div class="personal-info-value" id="nombres_completos">Cargando...</div>
                                </div>
                            </li>
                            <li class="personal-info-item">
                                <div class="personal-info-icon">
                                    <i class="fas fa-address-card"></i>
                                </div>
                                <div class="personal-info-content">
                                    <div class="personal-info-label">DNI</div>
                                    <div class="personal-info-value" id="dni">Cargando...</div>
                                </div>
                            </li>
                            <li class="personal-info-item">
                                <div class="personal-info-icon">
                                    <i class="fas fa-envelope"></i>
                                </div>
                                <div class="personal-info-content">
                                    <div class="personal-info-label">Email</div>
                                    <div class="personal-info-value" id="email">Cargando...</div>
                                </div>
                            </li>
                            <li class="personal-info-item">
                                <div class="personal-info-icon">
                                    <i class="fas fa-phone"></i>
                                </div>
                                <div class="personal-info-content">
                                    <div class="personal-info-label">Teléfono</div>
                                    <div class="personal-info-value" id="telefono">Cargando...</div>
                                </div>
                            </li>
                        </ul>
                    </div>
                    <div class="card-footer text-center">
                        <button class="btn btn-primary-modern btn-block" data-bs-toggle="modal" data-bs-target="#modal_contra">
                            <i class="fas fa-key mr-2"></i>Cambiar Contraseña
                        </button>
                    </div>
                </div>

                <!-- Addresses Card -->
                <div class="sidebar-card">
                    <div class="card-header-modern">
                        <h5>Direcciones de Envío</h5>
                        <div class="card-tools-modern">
                            <button type="button" class="btn-tool-modern" data-bs-toggle="modal" data-bs-target="#modal_direcciones">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div id="direcciones">
                            <div class="text-center py-3">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="sr-only">Cargando...</span>
                                </div>
                                <p class="text-muted mt-2">Cargando direcciones...</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions Card -->
                <div class="sidebar-card">
                    <div class="card-header-modern">
                        <h5>Acciones Rápidas</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="mis_pedidos.php" class="btn btn-outline-primary btn-modern">
                                <i class="fas fa-shopping-bag mr-2"></i>Mis Pedidos
                            </a>
                            <a href="favoritos.php" class="btn btn-outline-success btn-modern">
                                <i class="fas fa-heart mr-2"></i>Mis Favoritos
                            </a>
                            <a href="soporte.php" class="btn btn-outline-info btn-modern">
                                <i class="fas fa-headset mr-2"></i>Soporte
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-8">
                <div class="profile-tabs">
                    <div class="nav-tabs-modern">
                        <ul class="nav nav-pills">
                            <li class="nav-item">
                                <a class="nav-link active" href="#timeline" data-toggle="tab">
                                    <i class="fas fa-history mr-2"></i>Actividad
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#configuracion" data-toggle="tab">
                                    <i class="fas fa-cogs mr-2"></i>Configuración
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#tienda" data-toggle="tab">
                                    <i class="fas fa-store mr-2"></i>Mi Tienda
                                </a>
                            </li>
                        </ul>
                    </div>

                    <div class="tab-content-modern">
                        <!-- Activity Tab -->
                        <div class="active tab-pane" id="timeline">
                            <h4 class="mb-4">Tu Actividad Reciente</h4>
                            <div id="historiales" class="timeline-modern">
                                <!-- Timeline items will be loaded here -->
                                <div class="text-center py-4">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="sr-only">Cargando...</span>
                                    </div>
                                    <p class="text-muted mt-2">Cargando tu actividad...</p>
                                </div>
                            </div>
                        </div>

                        <!-- Configuration Tab -->
                        <div class="tab-pane" id="configuracion">
                            <div class="row">
                                <div class="col-md-8">
                                    <!-- Notifications Configuration -->
                                    <div class="config-card">
                                        <div class="config-card-header">
                                            <h5 class="mb-0">
                                                <i class="fas fa-bell mr-2"></i>Preferencias de Notificaciones
                                            </h5>
                                        </div>
                                        <div class="config-card-body">
                                            <form id="form-notificaciones">
                                                <div class="switch-group">
                                                    <div class="switch-label">
                                                        <div class="switch-title">Notificaciones por Email</div>
                                                        <div class="switch-description">Recibir notificaciones sobre tus pedidos y promociones</div>
                                                    </div>
                                                    <div class="custom-control custom-switch">
                                                        <input type="checkbox" class="custom-control-input" id="notificacion-email" checked>
                                                        <label class="custom-control-label" for="notificacion-email"></label>
                                                    </div>
                                                </div>

                                                <div class="switch-group">
                                                    <div class="switch-label">
                                                        <div class="switch-title">Actualizaciones de Pedidos</div>
                                                        <div class="switch-description">Notificaciones cuando tu pedido cambie de estado</div>
                                                    </div>
                                                    <div class="custom-control custom-switch">
                                                        <input type="checkbox" class="custom-control-input" id="notificacion-pedidos" checked>
                                                        <label class="custom-control-label" for="notificacion-pedidos"></label>
                                                    </div>
                                                </div>

                                                <div class="switch-group">
                                                    <div class="switch-label">
                                                        <div class="switch-title">Promociones y Ofertas</div>
                                                        <div class="switch-description">Recibir ofertas especiales y descuentos</div>
                                                    </div>
                                                    <div class="custom-control custom-switch">
                                                        <input type="checkbox" class="custom-control-input" id="notificacion-promociones">
                                                        <label class="custom-control-label" for="notificacion-promociones"></label>
                                                    </div>
                                                </div>

                                                <div class="switch-group">
                                                    <div class="switch-label">
                                                        <div class="switch-title">Productos Nuevos</div>
                                                        <div class="switch-description">Notificaciones sobre nuevos productos en categorías de interés</div>
                                                    </div>
                                                    <div class="custom-control custom-switch">
                                                        <input type="checkbox" class="custom-control-input" id="notificacion-productos">
                                                        <label class="custom-control-label" for="notificacion-productos"></label>
                                                    </div>
                                                </div>

                                                <div class="switch-group">
                                                    <div class="switch-label">
                                                        <div class="switch-title">Recordatorios de Reseñas</div>
                                                        <div class="switch-description">Recordatorios para reseñar productos comprados</div>
                                                    </div>
                                                    <div class="custom-control custom-switch">
                                                        <input type="checkbox" class="custom-control-input" id="notificacion-resenas">
                                                        <label class="custom-control-label" for="notificacion-resenas"></label>
                                                    </div>
                                                </div>

                                                <div class="text-right mt-4">
                                                    <button type="submit" class="btn btn-primary-modern">
                                                        <i class="fas fa-save mr-2"></i>Guardar Preferencias
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>

                                    <!-- Privacy Settings -->
                                    <div class="config-card">
                                        <div class="config-card-header">
                                            <h5 class="mb-0">
                                                <i class="fas fa-shield-alt mr-2"></i>Configuración de Privacidad
                                            </h5>
                                        </div>
                                        <div class="config-card-body">
                                            <form id="form-privacidad">
                                                <div class="switch-group">
                                                    <div class="switch-label">
                                                        <div class="switch-title">Perfil Público</div>
                                                        <div class="switch-description">Permitir que otros usuarios vean tu perfil y reseñas</div>
                                                    </div>
                                                    <div class="custom-control custom-switch">
                                                        <input type="checkbox" class="custom-control-input" id="privacidad-perfil" checked>
                                                        <label class="custom-control-label" for="privacidad-perfil"></label>
                                                    </div>
                                                </div>

                                                <div class="switch-group">
                                                    <div class="switch-label">
                                                        <div class="switch-title">Actividad Pública</div>
                                                        <div class="switch-description">Mostrar tu actividad reciente a otros usuarios</div>
                                                    </div>
                                                    <div class="custom-control custom-switch">
                                                        <input type="checkbox" class="custom-control-input" id="privacidad-actividad">
                                                        <label class="custom-control-label" for="privacidad-actividad"></label>
                                                    </div>
                                                </div>

                                                <div class="switch-group">
                                                    <div class="switch-label">
                                                        <div class="switch-title">Aparecer en Búsquedas</div>
                                                        <div class="switch-description">Permitir que otros usuarios te encuentren en búsquedas</div>
                                                    </div>
                                                    <div class="custom-control custom-switch">
                                                        <input type="checkbox" class="custom-control-input" id="privacidad-busqueda">
                                                        <label class="custom-control-label" for="privacidad-busqueda"></label>
                                                    </div>
                                                </div>

                                                <div class="switch-group">
                                                    <div class="switch-label">
                                                        <div class="switch-title">Compartir Datos Anónimos</div>
                                                        <div class="switch-description">Compartir datos de uso anónimos para mejorar la plataforma</div>
                                                    </div>
                                                    <div class="custom-control custom-switch">
                                                        <input type="checkbox" class="custom-control-input" id="privacidad-datos" checked>
                                                        <label class="custom-control-label" for="privacidad-datos"></label>
                                                    </div>
                                                </div>

                                                <div class="text-right mt-4">
                                                    <button type="submit" class="btn btn-primary-modern">
                                                        <i class="fas fa-save mr-2"></i>Guardar Configuración
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <!-- Display Preferences -->
                                    <div class="config-card">
                                        <div class="config-card-header">
                                            <h5 class="mb-0">
                                                <i class="fas fa-palette mr-2"></i>Preferencias de Visualización
                                            </h5>
                                        </div>
                                        <div class="config-card-body">
                                            <form id="form-visualizacion">
                                                <div class="form-group-modern">
                                                    <label for="tema-interface" class="form-label-modern">Tema de Interfaz</label>
                                                    <select class="form-control-modern" id="tema-interface">
                                                        <option value="claro">Claro</option>
                                                        <option value="oscuro">Oscuro</option>
                                                        <option value="auto">Automático (según sistema)</option>
                                                    </select>
                                                </div>

                                                <div class="form-group-modern">
                                                    <label for="densidad-interface" class="form-label-modern">Densidad de Interfaz</label>
                                                    <select class="form-control-modern" id="densidad-interface">
                                                        <option value="comoda">Cómoda (más espacio)</option>
                                                        <option value="normal" selected>Normal</option>
                                                        <option value="compacta">Compacta (más contenido)</option>
                                                    </select>
                                                </div>

                                                <div class="form-group-modern">
                                                    <label for="idioma-interface" class="form-label-modern">Idioma</label>
                                                    <select class="form-control-modern" id="idioma-interface">
                                                        <option value="es" selected>Español</option>
                                                        <option value="en">English</option>
                                                        <option value="pt">Português</option>
                                                    </select>
                                                </div>
                                                <div class="form-group">
                                                    <label for="moneda-interface" class="font-weight-bold">Moneda Predeterminada</label>
                                                    <select class="form-control" id="moneda-interface">

                                                    </select>
                                                    <small class="text-muted">Moneda para mostrar precios</small>
                                                </div>
                                                <div class="text-right mt-4">
                                                    <button type="submit" class="btn btn-primary-modern">
                                                        <i class="fas fa-save mr-2"></i>Guardar Preferencias
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>

                                    <!-- Advanced Settings -->
                                    <div class="config-card">
                                        <div class="config-card-header bg-light">
                                            <h5 class="mb-0">
                                                <i class="fas fa-cogs mr-2"></i>Configuración Avanzada
                                            </h5>
                                        </div>
                                        <div class="config-card-body">
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

                        <!-- Store Tab -->
                        <div class="tab-pane" id="tienda">
                            <div class="row">
                                <div class="col-12">
                                    <!-- Store Status Card -->
                                    <div class="config-card">
                                        <div class="config-card-header">
                                            <h5 class="mb-0">
                                                <i class="fas fa-store mr-2"></i>Estado de Mi Tienda
                                            </h5>
                                        </div>
                                        <div class="config-card-body">
                                            <div id="estado_tienda">
                                                <div class="text-center py-4">
                                                    <div class="spinner-border text-primary" role="status">
                                                        <span class="sr-only">Cargando...</span>
                                                    </div>
                                                    <p class="text-muted mt-2">Verificando estado de tu tienda...</p>
                                                </div>
                                            </div>

                                            <!-- Store Registration Form -->
                                            <div id="formulario_tienda" style="display: none;">
                                                <div class="alert alert-info">
                                                    <h6><i class="fas fa-info-circle mr-2"></i>¿Quieres vender en NexusBuy?</h6>
                                                    <p class="mb-0">Registra tu tienda y comienza a vender tus productos a millones de clientes.</p>
                                                </div>

                                                <form id="form-registro-tienda" class="mt-4">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="form-group-modern">
                                                                <label class="form-label-modern" for="nombre_tienda">Nombre de la Tienda *</label>
                                                                <input type="text" class="form-control-modern" id="nombre_tienda" name="nombre_tienda"
                                                                    placeholder="Ej: Mi Tienda Online" required maxlength="100">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group-modern">
                                                                <label class="form-label-modern" for="razon_social">Razón Social *</label>
                                                                <input type="text" class="form-control-modern" id="razon_social" name="razon_social"
                                                                    placeholder="Ej: Mi Empresa S.A. de C.V." required maxlength="200">
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="form-group-modern">
                                                                <label class="form-label-modern" for="ruc_tienda">RUC/RFC *</label>
                                                                <input type="text" class="form-control-modern" id="ruc_tienda" name="ruc_tienda"
                                                                    placeholder="Ej: 12345678901" required maxlength="20">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group-modern">
                                                                <label class="form-label-modern" for="telefono_tienda">Teléfono de Contacto *</label>
                                                                <input type="text" class="form-control-modern" id="telefono_tienda" name="telefono_tienda"
                                                                    placeholder="Ej: +1234567890" required>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="form-group-modern">
                                                                <label class="form-label-modern" for="provincia_tienda">Provincia *</label>
                                                                <select class="form-control-modern" id="provincia_tienda" name="provincia_tienda" required>
                                                                    <option value="">Selecciona una provincia</option>
                                                                    <!-- Provincias se cargarán dinámicamente -->
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group-modern">
                                                                <label class="form-label-modern" for="municipio_tienda">Municipio *</label>
                                                                <select class="form-control-modern" id="municipio_tienda" name="municipio_tienda" required disabled>
                                                                    <option value="">Primero selecciona una provincia</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="form-group-modern">
                                                        <label class="form-label-modern" for="direccion_tienda">Dirección Completa *</label>
                                                        <input type="text" class="form-control-modern" id="direccion_tienda" name="direccion_tienda"
                                                            placeholder="Ej: Av. Principal #123, Colonia Centro" required maxlength="255">
                                                    </div>

                                                    <div class="form-group-modern">
                                                        <label class="form-label-modern" for="descripcion_tienda">Descripción de la Tienda</label>
                                                        <textarea class="form-control-modern" id="descripcion_tienda" name="descripcion_tienda"
                                                            rows="4" placeholder="Describe los productos o servicios que ofreces..."
                                                            maxlength="500"></textarea>
                                                        <small class="text-muted">
                                                            <span id="contador_descripcion">0</span>/500 caracteres
                                                        </small>
                                                    </div>

                                                    <div class="form-check mb-4">
                                                        <input type="checkbox" class="form-check-input" id="terminos_tienda" required>
                                                        <label class="form-check-label" for="terminos_tienda">
                                                            Acepto los <a href="#" target="_blank">términos y condiciones para vendedores</a>
                                                        </label>
                                                    </div>

                                                    <div class="text-center">
                                                        <button type="submit" class="btn btn-success btn-lg btn-modern">
                                                            <i class="fas fa-store mr-2"></i>
                                                            Registrar Mi Tienda
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>

                                            <!-- Store Management Panel -->
                                            <div id="panel_gestion_tienda" style="display: none;">
                                                <div class="row">
                                                    <div class="col-md-4">
                                                        <div class="store-card">
                                                            <div class="store-header">
                                                                <div class="store-logo">
                                                                    <img id="logo_tienda" src="" alt="Logo de la tienda">
                                                                </div>
                                                                <h4 id="nombre_tienda_mostrar" class="mb-2"></h4>
                                                                <span id="estado_tienda_mostrar" class="badge badge-success">Activa</span>
                                                            </div>
                                                            <div class="store-info p-4">
                                                                <div class="store-detail mb-3">
                                                                    <strong>RUC/RFC:</strong>
                                                                    <span id="ruc_tienda_mostrar" class="float-right"></span>
                                                                </div>
                                                                <div class="store-detail mb-3">
                                                                    <strong>Teléfono:</strong>
                                                                    <span id="telefono_tienda_mostrar" class="float-right"></span>
                                                                </div>
                                                                <div class="store-detail mb-3">
                                                                    <strong>Dirección:</strong>
                                                                    <span id="direccion_tienda_mostrar" class="float-right text-right"></span>
                                                                </div>
                                                                <div class="store-detail">
                                                                    <strong>Ubicación:</strong>
                                                                    <span id="ubicacion_tienda_mostrar" class="float-right text-right"></span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-8">
                                                        <!-- Store Statistics -->
                                                        <div class="config-card mb-4">
                                                            <div class="config-card-header">
                                                                <h5 class="mb-0">Estadísticas de la Tienda</h5>
                                                            </div>
                                                            <div class="config-card-body">
                                                                <div class="row text-center">
                                                                    <div class="col-4">
                                                                        <div class="stat-item">
                                                                            <span class="stat-number text-primary" id="stat-productos">0</span>
                                                                            <span class="stat-label">Productos</span>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-4">
                                                                        <div class="stat-item">
                                                                            <span class="stat-number text-success" id="stat-ventas">0</span>
                                                                            <span class="stat-label">Ventas</span>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-4">
                                                                        <div class="stat-item">
                                                                            <span class="stat-number text-info" id="stat-calificacion">0.0</span>
                                                                            <span class="stat-label">Calificación</span>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <!-- Quick Actions -->
                                                        <div class="config-card">
                                                            <div class="config-card-header">
                                                                <h5 class="mb-0">Acciones Rápidas</h5>
                                                            </div>
                                                            <div class="config-card-body">
                                                                <div class="store-actions">
                                                                    <a href="gestion_productos.php" class="btn-store-action">
                                                                        <i class="fas fa-boxes fa-2x mb-2"></i>
                                                                        <span>Gestionar Productos</span>
                                                                    </a>
                                                                    <a href="pedidos_tienda.php" class="btn-store-action">
                                                                        <i class="fas fa-shopping-cart fa-2x mb-2"></i>
                                                                        <span>Ver Pedidos</span>
                                                                    </a>
                                                                    <a href="estadisticas_tienda.php" class="btn-store-action">
                                                                        <i class="fas fa-chart-bar fa-2x mb-2"></i>
                                                                        <span>Estadísticas</span>
                                                                    </a>
                                                                    <button class="btn-store-action" id="editar_tienda">
                                                                        <i class="fas fa-edit fa-2x mb-2"></i>
                                                                        <span>Editar Tienda</span>
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
include_once 'Layauts/footer_general.php';
?>
<script src="mi_perfil.js"></script>

<script>
    // Funciones para la gestión del perfil
    function exportarDatos() {
        $('#modalExportarDatos').modal('show');
    }

    function confirmarExportacion() {
        const format = $('#formato-exportacion').val();

        // Simular exportación
        Swal.fire({
            title: 'Exportando datos...',
            text: 'Preparando tu archivo de exportación',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        setTimeout(() => {
            Swal.fire({
                icon: 'success',
                title: '¡Exportación completada!',
                text: 'Tu archivo ha sido generado y descargado',
                confirmButtonText: 'Aceptar'
            });
            $('#modalExportarDatos').modal('hide');
        }, 2000);
    }

    function limpiarHistorial() {
        Swal.fire({
            title: '¿Limpiar historial?',
            text: 'Esta acción eliminará tu historial local del navegador',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, limpiar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire(
                    '¡Historial limpiado!',
                    'Tu historial local ha sido eliminado.',
                    'success'
                );
            }
        });
    }

    function restablecerPreferencias() {
        Swal.fire({
            title: '¿Restablecer preferencias?',
            text: 'Todas tus configuraciones volverán a los valores por defecto',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, restablecer',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire(
                    '¡Preferencias restablecidas!',
                    'Tus configuraciones han sido restablecidas.',
                    'success'
                );
            }
        });
    }

    function eliminarCuenta() {
        Swal.fire({
            title: '¿Eliminar cuenta?',
            text: 'Esta acción no se puede deshacer. Se perderán todos tus datos.',
            icon: 'error',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, eliminar cuenta',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Eliminando cuenta...',
                    text: 'Esto puede tomar unos momentos',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                setTimeout(() => {
                    Swal.fire(
                        '¡Cuenta eliminada!',
                        'Tu cuenta ha sido eliminada exitosamente.',
                        'success'
                    ).then(() => {
                        window.location.href = '../index.php';
                    });
                }, 3000);
            }
        });
    }

    // Contador de caracteres para descripción de tienda
    $('#descripcion_tienda').on('input', function() {
        const count = $(this).val().length;
        $('#contador_descripcion').text(count);
    });

    // Simular carga del estado de la tienda
    $(document).ready(function() {
        setTimeout(() => {
            // Simular que el usuario no tiene tienda registrada
            $('#estado_tienda').hide();
            $('#formulario_tienda').show();
        }, 1500);
    });
</script>