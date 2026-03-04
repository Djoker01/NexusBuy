<?php
// admin/seguridad/index.php
$base_path = "../../";
$titulo = "Dashboard de Seguridad";
$breadcrumb = '<li class="breadcrumb-item"><a href="/admin/index.php">Inicio</a></li>
               <li class="breadcrumb-item active">Seguridad</li>';
include '../layouts/header.php';
?>

<div class="row">
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>156</h3>
                <p>Intentos de login hoy</p>
            </div>
            <div class="icon">
                <i class="fas fa-sign-in-alt"></i>
            </div>
            <span class="small-box-footer">12 fallidos</span>
        </div>
    </div>
    
    <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>1,234</h3>
                <p>Usuarios activos</p>
            </div>
            <div class="icon">
                <i class="fas fa-users"></i>
            </div>
            <span class="small-box-footer">sesiones activas</span>
        </div>
    </div>
    
    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>23</h3>
                <p>IPs bloqueadas</p>
            </div>
            <div class="icon">
                <i class="fas fa-ban"></i>
            </div>
            <span class="small-box-footer">última hora</span>
        </div>
    </div>
    
    <div class="col-lg-3 col-6">
        <div class="small-box bg-danger">
            <div class="inner">
                <h3>3</h3>
                <p>Amenazas detectadas</p>
            </div>
            <div class="icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <span class="small-box-footer">últimas 24h</span>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-shield-alt mr-2"></i>
                    Estado de seguridad
                </h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 col-sm-6 col-12">
                        <div class="info-box">
                            <span class="info-box-icon bg-success"><i class="fas fa-check"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">HTTPS</span>
                                <span class="info-box-number">Activo</span>
                                <span class="info-box-text">Certificado válido</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3 col-sm-6 col-12">
                        <div class="info-box">
                            <span class="info-box-icon bg-success"><i class="fas fa-shield-alt"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Firewall</span>
                                <span class="info-box-number">Activo</span>
                                <span class="info-box-text">Modo estricto</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3 col-sm-6 col-12">
                        <div class="info-box">
                            <span class="info-box-icon bg-warning"><i class="fas fa-key"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">2FA</span>
                                <span class="info-box-number">Parcial</span>
                                <span class="info-box-text">45% de admins</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3 col-sm-6 col-12">
                        <div class="info-box">
                            <span class="info-box-icon bg-info"><i class="fas fa-user-lock"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Políticas</span>
                                <span class="info-box-number">Robustas</span>
                                <span class="info-box-text">Password +8 chars</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-history mr-2"></i>
                    Últimos eventos de seguridad
                </h3>
                <div class="card-tools">
                    <a href="logs.php" class="btn btn-sm btn-primary">
                        Ver todos <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Evento</th>
                                <th>Usuario/IP</th>
                                <th>Resultado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>15/03/2025 10:30</td>
                                <td>Inicio de sesión exitoso</td>
                                <td>admin@nexusbuy.com</td>
                                <td><span class="badge badge-success">Éxito</span></td>
                            </tr>
                            <tr>
                                <td>15/03/2025 09:15</td>
                                <td>Intento fallido x3</td>
                                <td>190.123.45.67</td>
                                <td><span class="badge badge-danger">Fallo</span></td>
                            </tr>
                            <tr>
                                <td>15/03/2025 08:45</td>
                                <td>Cambio de contraseña</td>
                                <td>maria@fashion.com</td>
                                <td><span class="badge badge-success">Éxito</span></td>
                            </tr>
                            <tr>
                                <td>14/03/2025 23:10</td>
                                <td>IP bloqueada automáticamente</td>
                                <td>45.67.89.123</td>
                                <td><span class="badge badge-warning">Bloqueo</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    Alertas activas
                </h3>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <span class="status-indicator warning"></span>
                            <strong>Múltiples intentos fallidos</strong>
                            <br>
                            <small class="text-muted">IP: 45.67.89.123</small>
                        </div>
                        <span class="badge badge-warning">3</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <span class="status-indicator danger"></span>
                            <strong>Cuenta suspendida</strong>
                            <br>
                            <small class="text-muted">Usuario: carlos@tech.com</small>
                        </div>
                        <span class="badge badge-danger">1</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <span class="status-indicator info"></span>
                            <strong>2FA no activado</strong>
                            <br>
                            <small class="text-muted">5 administradores</small>
                        </div>
                        <span class="badge badge-info">5</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-tasks mr-2"></i>
                    Acciones rápidas
                </h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 col-sm-6 col-12 mb-2">
                        <a href="general.php" class="btn btn-block btn-outline-primary">
                            <i class="fas fa-shield-alt mr-2"></i> Configuración general
                        </a>
                    </div>
                    <div class="col-md-3 col-sm-6 col-12 mb-2">
                        <a href="twofa.php" class="btn btn-block btn-outline-primary">
                            <i class="fas fa-mobile-alt mr-2"></i> Autenticación 2FA
                        </a>
                    </div>
                    <div class="col-md-3 col-sm-6 col-12 mb-2">
                        <a href="passwords.php" class="btn btn-block btn-outline-primary">
                            <i class="fas fa-key mr-2"></i> Políticas de contraseña
                        </a>
                    </div>
                    <div class="col-md-3 col-sm-6 col-12 mb-2">
                        <a href="sesiones.php" class="btn btn-block btn-outline-primary">
                            <i class="fas fa-laptop mr-2"></i> Sesiones activas
                        </a>
                    </div>
                    <div class="col-md-3 col-sm-6 col-12 mb-2">
                        <a href="whitelist.php" class="btn btn-block btn-outline-primary">
                            <i class="fas fa-list mr-2"></i> IP Whitelist
                        </a>
                    </div>
                    <div class="col-md-3 col-sm-6 col-12 mb-2">
                        <a href="firewall.php" class="btn btn-block btn-outline-primary">
                            <i class="fas fa-fire mr-2"></i> Firewall
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../layouts/footer.php'; ?>