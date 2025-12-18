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

<style>
    :root {
        --primary: #4361ee;
        --secondary: #3f37c9;
        --accent: #4cc9f0;
        --success: #4bb543;
        --warning: #ffc107;
        --danger: #e63946;
        --light: #f8f9fa;
        --dark: #212529;
        --gradient-primary: linear-gradient(135deg, #4361ee 0%, #3a0ca3 100%);
        --gradient-accent: linear-gradient(135deg, #4cc9f0 0%, #4895ef 100%);
        --shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        --shadow-hover: 0 8px 24px rgba(0, 0, 0, 0.12);
        --border-radius: 12px;
        --transition: all 0.3s ease;
    }

    .content-header {
        background: var(--gradient-primary);
        color: white;
        border-radius: 0 0 var(--border-radius) var(--border-radius);
        margin-bottom: 30px;
        box-shadow: var(--shadow);
    }

    .breadcrumb {
        background: transparent;
        margin: 0;
    }

    .breadcrumb-item a {
        color: rgba(255, 255, 255, 0.9);
    }

    .breadcrumb-item.active {
        color: white;
    }

    /* Profile Header */
    .profile-header-card {
        background: white;
        border-radius: var(--border-radius);
        box-shadow: var(--shadow);
        overflow: hidden;
        margin-bottom: 25px;
    }

    .profile-cover {
        background: var(--gradient-primary);
        height: 120px;
        position: relative;
    }

    .profile-avatar {
        position: absolute;
        bottom: -50px;
        left: 30px;
        width: 120px;
        height: 120px;
        border: 4px solid white;
        border-radius: 50%;
        background: white;
        box-shadow: var(--shadow);
    }

    .profile-avatar img {
        width: 100%;
        height: 100%;
        border-radius: 50%;
        object-fit: cover;
    }

    .profile-info {
        padding: 70px 30px 30px 30px;
    }

    .profile-name {
        font-size: 1.8rem;
        font-weight: 700;
        color: var(--dark);
        margin: 0;
    }

    .profile-role {
        color: var(--primary);
        font-weight: 600;
        margin: 5px 0;
    }

    .profile-stats {
        display: flex;
        gap: 30px;
        margin-top: 20px;
    }

    .stat-item {
        text-align: center;
    }

    .stat-number {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--primary);
        display: block;
    }

    .stat-label {
        font-size: 0.8rem;
        color: #6c757d;
        text-transform: uppercase;
        font-weight: 600;
    }

    /* Sidebar Cards */
    .sidebar-card {
        background: white;
        border-radius: var(--border-radius);
        box-shadow: var(--shadow);
        margin-bottom: 25px;
        overflow: hidden;
        transition: var(--transition);
    }

    .sidebar-card:hover {
        box-shadow: var(--shadow-hover);
    }

    .card-header-modern {
        background: var(--gradient-primary);
        color: white;
        padding: 20px;
        border: none;
        position: relative;
    }

    .card-header-modern h5 {
        margin: 0;
        font-weight: 600;
    }

    .card-tools-modern {
        position: absolute;
        top: 15px;
        right: 15px;
    }

    .btn-tool-modern {
        background: rgba(255, 255, 255, 0.2);
        border: none;
        color: white;
        width: 35px;
        height: 35px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: var(--transition);
    }

    .btn-tool-modern:hover {
        background: rgba(255, 255, 255, 0.3);
        transform: scale(1.1);
    }

    .personal-info-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .personal-info-item {
        display: flex;
        align-items: center;
        padding: 12px 0;
        border-bottom: 1px solid #f8f9fa;
    }

    .personal-info-item:last-child {
        border-bottom: none;
    }

    .personal-info-icon {
        width: 40px;
        height: 40px;
        background: var(--gradient-accent);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        margin-right: 15px;
        flex-shrink: 0;
    }

    .personal-info-content {
        flex: 1;
    }

    .personal-info-label {
        font-size: 0.8rem;
        color: #6c757d;
        text-transform: uppercase;
        font-weight: 600;
    }

    .personal-info-value {
        font-weight: 600;
        color: var(--dark);
    }

    /* Address Cards */
    .address-card {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 10px;
        border-left: 4px solid var(--primary);
    }

    .address-card:last-child {
        margin-bottom: 0;
    }

    .address-actions {
        display: flex;
        gap: 10px;
        margin-top: 10px;
    }

    .btn-address {
        padding: 5px 10px;
        font-size: 0.8rem;
        border-radius: 4px;
    }

    /* Main Content Tabs */
    .profile-tabs {
        background: white;
        border-radius: var(--border-radius);
        box-shadow: var(--shadow);
        overflow: hidden;
        margin-bottom: 25px;
    }

    .nav-tabs-modern {
        display: flex;
        flex-wrap: wrap;
        padding-left: 0;
        margin-bottom: 0;
        list-style: none;
        background: #f8f9fa;
        border: none;
        padding: 0 20px;
    }

    .nav-tabs-modern .nav-item {
        margin-bottom: -1px;
    }

    .nav-tabs-modern .nav-link {
        border: none;
        color: #6c757d;
        font-weight: 600;
        padding: 20px 25px;
        transition: var(--transition);
        position: relative;
        cursor: pointer;
        border-radius: 8px 8px 0 0;
        margin: 0 2px;
        background: rgba(255, 255, 255, 0.1);
    }

    .nav-tabs-modern .nav-link:hover {
        color: var(--primary);
        background: rgba(255, 255, 255, 0.2);
    }

    .nav-tabs-modern .nav-link.active {
        color: var(--primary) !important;
        background: white !important;
        border-bottom: 3px solid white;
    }

    .tab-content-modern {
        padding: 30px;
    }

    /* ========== TIMELINE ESTILOS CORREGIDOS ========== */
    .timeline-modern {
        position: relative;
        padding: 30px 0;
        margin: 0;
        list-style: none;
        width: 100%;
    }

    .timeline-modern::before {
        content: '';
        position: absolute;
        left: 35px;
        top: 0;
        bottom: 0;
        width: 4px;
        background: linear-gradient(to bottom, #4361ee, #3a0ca3);
        border-radius: 2px;
        z-index: 1;
    }

    .timeline-item-modern {
        position: relative;
        margin-bottom: 30px;
        padding-left: 85px;
        min-height: 80px;
    }

    .timeline-icon-modern {
        position: absolute;
        left: 20px;
        top: 0;
        width: 50px;
        height: 50px;
        background: #4361ee;
        border-radius: 50%;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        color: white !important;
        box-shadow: 0 4px 12px rgba(67, 97, 238, 0.3);
        border: 4px solid white;
        z-index: 2;
    }

    .timeline-icon-modern i {
        font-size: 1.2rem;
        color: white !important;
    }

    .timeline-content-modern {
        background: white;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        border-left: 4px solid #4361ee;
        margin-bottom: 10px;
        position: relative;
        z-index: 1;
    }

    .timeline-date-modern {
        color: #6c757d;
        font-size: 0.9rem;
        margin-bottom: 10px;
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .timeline-date-modern i {
        color: #4361ee;
    }

    .timeline-title-modern {
        font-weight: 600;
        color: #212529;
        margin-bottom: 12px;
        font-size: 1.1rem;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .timeline-title-modern i {
        color: #4361ee;
        font-size: 1.2rem;
    }

    .timeline-content-modern p {
        color: #495057;
        line-height: 1.6;
        margin-bottom: 15px;
        padding-left: 15px;
        border-left: 3px solid #f1f3f5;
    }

    .timeline-content-modern small {
        color: #868e96;
        font-size: 0.85rem;
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .timeline-content-modern .badge {
        font-size: 0.75rem;
        padding: 4px 10px;
        border-radius: 20px;
        font-weight: 600;
    }

    /* Estado vacío */
    .timeline-modern .text-center {
        padding: 60px 20px;
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-radius: 12px;
        border: 2px dashed #dee2e6;
        text-align: center;
    }

    .timeline-modern .text-center i {
        color: #adb5bd;
        margin-bottom: 20px;
        font-size: 3rem;
    }

    .timeline-modern .text-center h5 {
        color: #6c757d;
        font-weight: 600;
        margin-bottom: 10px;
        font-size: 1.5rem;
    }

    .timeline-modern .text-center p {
        color: #868e96;
        max-width: 300px;
        margin: 0 auto;
        font-size: 1rem;
    }

    /* Estado de carga */
    .timeline-modern .spinner-border {
        width: 3rem;
        height: 3rem;
        border-width: 0.25rem;
    }

    /* Colores para iconos */
    .timeline-icon-modern.bg-info {
        background: #17a2b8 !important;
    }
    
    .timeline-icon-modern.bg-success {
        background: #28a745 !important;
    }
    
    .timeline-icon-modern.bg-warning {
        background: #ffc107 !important;
    }
    
    .timeline-icon-modern.bg-danger {
        background: #dc3545 !important;
    }

    /* Configuration Cards */
    .config-card {
        background: white;
        border-radius: var(--border-radius);
        box-shadow: var(--shadow);
        margin-bottom: 25px;
        overflow: hidden;
    }

    .config-card-header {
        background: #f8f9fa;
        padding: 20px;
        border-bottom: 1px solid #e9ecef;
    }

    .config-card-body {
        padding: 25px;
    }

    .switch-group {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 15px 0;
        border-bottom: 1px solid #f8f9fa;
    }

    .switch-group:last-child {
        border-bottom: none;
    }

    .switch-label {
        flex: 1;
    }

    .switch-title {
        font-weight: 600;
        color: var(--dark);
        margin-bottom: 2px;
    }

    .switch-description {
        color: #6c757d;
        font-size: 0.9rem;
    }

    /* Store Cards */
    .store-card {
        background: white;
        border-radius: var(--border-radius);
        box-shadow: var(--shadow);
        overflow: hidden;
        margin-bottom: 25px;
    }

    .store-header {
        background: var(--gradient-primary);
        color: white;
        padding: 25px;
        text-align: center;
    }

    .store-logo {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        border: 4px solid white;
        margin: 0 auto 15px;
        background: white;
        overflow: hidden;
    }

    .store-logo img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .store-actions {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 15px;
        padding: 20px;
    }

    .btn-store-action {
        padding: 15px;
        border: 2px solid #e9ecef;
        border-radius: 8px;
        background: white;
        color: var(--dark);
        font-weight: 600;
        transition: var(--transition);
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
    }

    .btn-store-action:hover {
        border-color: var(--primary);
        color: var(--primary);
        transform: translateY(-2px);
    }

    /* Modal Styles */
    .modal-modern .modal-content {
        border: none;
        border-radius: var(--border-radius);
        box-shadow: var(--shadow-hover);
    }

    .modal-modern .modal-header {
        background: var(--gradient-primary);
        color: white;
        border-radius: var(--border-radius) var(--border-radius) 0 0;
        border: none;
        padding: 25px;
    }

    .modal-modern .modal-body {
        padding: 25px;
    }

    .modal-modern .modal-footer {
        border: none;
        padding: 20px 25px;
        background: #f8f9fa;
        border-radius: 0 0 var(--border-radius) var(--border-radius);
    }

    /* Form Styles */
    .form-group-modern {
        margin-bottom: 20px;
    }

    .form-label-modern {
        font-weight: 600;
        color: var(--dark);
        margin-bottom: 8px;
        display: block;
    }

    .form-control-modern {
        border: 2px solid #e9ecef;
        border-radius: 8px;
        padding: 12px 15px;
        font-size: 1rem;
        transition: var(--transition);
        width: 100%;
    }

    .form-control-modern:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 0.2rem rgba(67, 97, 238, 0.25);
    }

    .btn-modern {
        border-radius: 8px;
        padding: 12px 25px;
        font-weight: 600;
        transition: var(--transition);
    }

    .btn-primary-modern {
        background: var(--gradient-primary);
        border: none;
        color: white;
    }

    .btn-primary-modern:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(67, 97, 238, 0.4);
    }

    /* Animations */
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes pulse {
        0% {
            transform: scale(1);
        }
        50% {
            transform: scale(1.05);
        }
        100% {
            transform: scale(1);
        }
    }

    .sidebar-card,
    .config-card,
    .timeline-item-modern {
        animation: fadeInUp 0.4s ease-out;
    }

    .timeline-icon-modern.bg-info {
        animation: pulse 2s infinite;
    }

    /* Pestañas activas */
    .profile-tabs .tab-content-modern .tab-pane {
        display: none;
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .profile-tabs .tab-content-modern .tab-pane.active {
        display: block;
        opacity: 1;
        animation: fadeIn 0.5s ease;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .profile-avatar {
            left: 50%;
            transform: translateX(-50%);
            bottom: -60px;
        }

        .profile-info {
            text-align: center;
            padding-top: 80px;
        }

        .profile-stats {
            justify-content: center;
            gap: 20px;
        }

        .nav-tabs-modern .nav-link {
            padding: 15px 20px;
            font-size: 0.9rem;
        }

        .store-actions {
            grid-template-columns: 1fr;
        }
        
        .timeline-modern::before {
            left: 30px;
        }
        
        .timeline-item-modern {
            padding-left: 70px;
        }
        
        .timeline-icon-modern {
            left: 20px;
            width: 40px;
            height: 40px;
        }
        
        .timeline-content-modern {
            padding: 15px;
        }
    }

    @media (max-width: 576px) {
        .timeline-modern::before {
            left: 25px;
        }
        
        .timeline-item-modern {
            padding-left: 60px;
        }
        
        .timeline-icon-modern {
            left: 15px;
            width: 35px;
            height: 35px;
        }
        
        .timeline-title-modern {
            font-size: 1rem;
        }
        
        .timeline-content-modern {
            padding: 15px;
        }
    }

    /* FIX para timeline si aún no se ve */
#historiales.timeline-modern {
    display: block !important;
    visibility: visible !important;
}

.timeline-icon-modern {
    transition: all 0.3s ease;
}

.timeline-icon-modern:hover {
    transform: scale(1.1);
    box-shadow: 0 6px 20px rgba(67, 97, 238, 0.4);
}

.timeline-content-modern {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.timeline-content-modern:hover {
    transform: translateX(5px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}

/* Mejor contraste para badges */
.badge-primary { background: var(--primary); }
.badge-success { background: var(--success); }
.badge-warning { background: var(--warning); color: #212529; }
.badge-danger { background: var(--danger); }
.badge-info { background: var(--accent); }

/* Animación para nuevos items */
@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateX(-20px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

.timeline-item-modern {
    animation: slideIn 0.5s ease-out;
}
</style>

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
                    <i class="fas fa-save me-2"></i>Actualizar Contraseña
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
                                <label class="form-label-modern" for="nombres_mod">Nombres *</label>
                                <input type="text" name="nombres_mod" class="form-control-modern" id="nombres_mod" 
                                       placeholder="Ingresa tus nombres" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group-modern">
                                <label class="form-label-modern" for="apellidos_mod">Apellidos *</label>
                                <input type="text" name="apellidos_mod" class="form-control-modern" id="apellidos_mod" 
                                       placeholder="Ingresa tus apellidos" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group-modern">
                                <label class="form-label-modern" for="dni_mod">DNI *</label>
                                <input type="text" name="dni_mod" class="form-control-modern" id="dni_mod" 
                                       placeholder="Ingresa tu DNI" required maxlength="11">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group-modern">
                                <label class="form-label-modern" for="telefono_mod">Teléfono *</label>
                                <input type="tel" name="telefono_mod" class="form-control-modern" id="telefono_mod" 
                                       placeholder="Ingresa tu teléfono" required maxlength="20">
                            </div>
                        </div>
                    </div>
                    <div class="form-group-modern">
                        <label class="form-label-modern" for="email_mod">Email *</label>
                        <input type="email" name="email_mod" class="form-control-modern" id="email_mod" 
                               placeholder="Ingresa tu email" required>
                    </div>
                    
                    
                     
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group-modern">
                                <label class="form-label-modern" for="fecha_nacimiento_mod">Fecha de Nacimiento</label>
                                <input type="date" name="fecha_nacimiento_mod" class="form-control-modern" 
                                       id="fecha_nacimiento_mod">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group-modern">
                                <label class="form-label-modern" for="genero_mod">Género</label>
                                <select name="genero_mod" class="form-control-modern" id="genero_mod">
                                    <option value="">Seleccionar</option>
                                    <option value="M">Masculino</option>
                                    <option value="F">Femenino</option>
                                    <option value="O">Otro</option>
                                    <option value="prefiero_no_decir">Prefiero no decir</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    
                    <div class="form-group-modern">
                        <label class="form-label-modern">Foto de Perfil</label>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" name="avatar_mod" id="avatar_mod" 
                                   accept="image/*" data-browse="Seleccionar">
                            <label class="custom-file-label" for="avatar_mod">Seleccionar imagen...</label>
                        </div>
                        <small class="text-muted">Formatos: JPG, PNG, GIF. Tamaño máximo: 2MB</small>
                        <div id="avatar-preview" class="mt-2 text-center" style="display: none;">
                            <img id="avatar-preview-img" src="" alt="Vista previa" 
                                 style="max-width: 150px; max-height: 150px; border-radius: 50%;">
                        </div>
                    </div>
                    
                    <div class="alert alert-info mt-3">
                        <small>
                            <i class="fas fa-info-circle mr-1"></i>
                            Los campos marcados con * son obligatorios.
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary-modern">
                        <i class="fas fa-save mr-2"></i>Guardar Cambios
                    </button>
                </div>
                </form>
            </div>
        </div>
    </div>
 

<!-- Modal para agregar dirección -->
<div class="modal fade" id="modal_direcciones" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-map-marker-alt mr-2"></i>
                    Agregar nueva dirección
                </h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="form-direccion">
                    <!-- Alias (opcional) -->
                    <div class="form-group">
                        <label for="alias_direccion">Alias (ej: Casa, Trabajo)</label>
                        <input type="text" class="form-control" id="alias_direccion" 
                               placeholder="Nombre para identificar esta dirección">
                    </div>
                    
                    <!-- Provincia y Municipio -->
                    <div class="form-group">
                        <label for="provincia">Provincia *</label>
                        <select class="form-control select2" id="provincia" required>
                            <option value="">Selecciona una provincia</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="municipio">Municipio *</label>
                        <select class="form-control select2" id="municipio" required>
                            <option value="">Primero selecciona una provincia</option>
                        </select>
                    </div>
                    
                    <!-- Dirección -->
                    <div class="form-group">
                        <label for="direccion">Dirección completa *</label>
                        <textarea class="form-control" id="direccion" rows="3" 
                                  placeholder="Calle, número, edificio, apartamento..." 
                                  required></textarea>
                    </div>
                    
                    <!-- Instrucciones adicionales -->
                    <div class="form-group">
                        <label for="instrucciones_direccion">Instrucciones de entrega</label>
                        <textarea class="form-control" id="instrucciones_direccion" rows="2"
                                  placeholder="Ej: Timbre 3A, dejar con conserje..."></textarea>
                    </div>
                    
                    <!-- Marcar como principal -->
                    <div class="form-check mb-3">
                        <input type="checkbox" class="form-check-input" id="es_principal">
                        <label class="form-check-label" for="es_principal">
                            Marcar como dirección principal
                        </label>
                    </div>
                    
                    <div class="alert alert-info">
                        <small>
                            <i class="fas fa-info-circle mr-1"></i>
                            Los campos marcados con * son obligatorios
                        </small>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" form="form-direccion" class="btn btn-primary">
                    <i class="fas fa-save mr-2"></i>Guardar dirección
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para editar dirección -->
<div class="modal fade" id="modal_editar_direccion" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-edit mr-2"></i>
                    Editar dirección
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- El formulario se cargará aquí dinámicamente -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times mr-2"></i>Cancelar
                </button>
                <button type="submit" form="form-editar-direccion" class="btn btn-primary">
                    <i class="fas fa-save mr-2"></i>Guardar cambios
                </button>
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
                            <button type="button" class="btn-tool-modern editar_datos">
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
                            <li class="personal-info-item">
                                <div class="personal-info-icon">
                                    <i class="fas fa-calendar"></i>
                                </div>
                                <div class="personal-info-content">
                                    <div class="personal-info-label">Fecha de Nacimiento</div>
                                    <div class="personal-info-value" id="fecha">Cargando...</div>
                                </div>
                            </li>
                            <li class="personal-info-item">
                                <div class="personal-info-icon">
                                    <i class="fas fa-transgender-alt"></i>
                                </div>
                                <div class="personal-info-content">
                                    <div class="personal-info-label">Genero</div>
                                    <div class="personal-info-value" id="genero">Cargando...</div>
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
                        <div class="tab-pane fade show active" id="timeline">
    <h4 class="mb-4">Tu Actividad Reciente</h4>
    <div id="historiales" class="timeline-modern">
        <!-- Timeline se cargará dinámicamente -->
        <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status">
                <span class="sr-only">Cargando...</span>
            </div>
            <p class="text-muted mt-2">Selecciona esta pestaña para cargar tu actividad</p>
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