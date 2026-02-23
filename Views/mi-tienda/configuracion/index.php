<?php
$base_path = "../";
$pageTitle = "Configuración";
$pageName = "Configuración de la Tienda";
$pageDescription = "Personaliza todas los aspectos de tu negocio.";
include_once '../layouts/header.php';
?>

    
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: #f8f9fa;
            color: #212529;
        }

        /* Layout */
        .app {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            width: 260px;
            background: white;
            border-right: 1px solid #e9ecef;
            padding: 1.5rem;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
        }

        .logo {
            font-size: 1.5rem;
            font-weight: 700;
            color: #4361ee;
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .logo i {
            font-size: 2rem;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1rem;
            color: #6c757d;
            text-decoration: none;
            border-radius: 8px;
            margin-bottom: 0.25rem;
            transition: all 0.2s;
        }

        .nav-item:hover {
            background: #f1f3f5;
            color: #4361ee;
        }

        .nav-item.active {
            background: #4361ee;
            color: white;
        }

        .nav-item i {
            width: 20px;
        }

        /* Main Content */
        .main {
            flex: 1;
            margin-left: 260px;
            padding: 1.5rem 2rem;
        }

        /* Header */
        .top-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            background: white;
            padding: 1rem 1.5rem;
            border-radius: 12px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.02);
        }

        .page-title h1 {
            font-size: 1.5rem;
            font-weight: 600;
        }

        .page-title p {
            color: #6c757d;
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }

        .user-menu {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        .notifications {
            position: relative;
            cursor: pointer;
        }

        .notifications i {
            font-size: 1.25rem;
            color: #6c757d;
        }

        .badge {
            position: absolute;
            top: -8px;
            right: -8px;
            background: #e63946;
            color: white;
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
            border-radius: 999px;
            min-width: 18px;
            height: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            cursor: pointer;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            background: #4361ee;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
        }

        .user-details {
            line-height: 1.3;
        }

        .user-name {
            font-weight: 600;
            font-size: 0.875rem;
        }

        .user-role {
            color: #6c757d;
            font-size: 0.75rem;
        }

        /* Settings Navigation */
        .settings-nav {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 2rem;
            flex-wrap: wrap;
            background: white;
            padding: 0.5rem;
            border-radius: 12px;
            border: 1px solid #e9ecef;
        }

        .settings-nav-item {
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-size: 0.875rem;
            font-weight: 500;
            color: #6c757d;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .settings-nav-item:hover {
            background: #f1f3f5;
            color: #4361ee;
        }

        .settings-nav-item.active {
            background: #4361ee;
            color: white;
        }

        /* Settings Sections */
        .settings-section {
            background: white;
            border-radius: 12px;
            border: 1px solid #e9ecef;
            padding: 2rem;
            margin-bottom: 2rem;
        }

        .section-title {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #212529;
        }

        .section-title i {
            color: #4361ee;
        }

        .section-divider {
            height: 1px;
            background: #e9ecef;
            margin: 2rem 0;
        }

        /* Forms */
        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.5rem;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-group.full-width {
            grid-column: span 2;
        }

        .form-label {
            display: block;
            font-size: 0.875rem;
            font-weight: 500;
            margin-bottom: 0.5rem;
            color: #212529;
        }

        .form-label i {
            color: #4361ee;
            margin-right: 0.25rem;
        }

        .form-control {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            font-size: 0.875rem;
            transition: all 0.2s;
        }

        .form-control:focus {
            outline: none;
            border-color: #4361ee;
        }

        .form-control:disabled {
            background: #f8f9fa;
            color: #6c757d;
        }

        textarea.form-control {
            resize: vertical;
            min-height: 100px;
        }

        /* Image Upload */
        .image-upload-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .image-upload-card {
            text-align: center;
        }

        .image-upload-label {
            font-weight: 500;
            margin-bottom: 1rem;
            display: block;
        }

        .image-preview {
            width: 100%;
            aspect-ratio: 16/9;
            background: #f8f9fa;
            border: 2px dashed #e9ecef;
            border-radius: 12px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: #6c757d;
            cursor: pointer;
            transition: all 0.2s;
            margin-bottom: 1rem;
            position: relative;
            overflow: hidden;
        }

        .image-preview:hover {
            border-color: #4361ee;
            color: #4361ee;
        }

        .image-preview i {
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }

        .image-preview.has-image {
            border: 2px solid #4361ee;
        }

        .image-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            position: absolute;
            top: 0;
            left: 0;
        }

        .image-preview.logo-preview {
            aspect-ratio: 1/1;
            width: 200px;
            margin: 0 auto;
            border-radius: 50%;
        }

        .image-actions {
            display: flex;
            gap: 0.5rem;
            justify-content: center;
        }

        .btn-outline-sm {
            background: white;
            border: 1px solid #e9ecef;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-size: 0.75rem;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-outline-sm:hover {
            background: #f1f3f5;
        }

        /* Location Grid */
        .location-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 2fr;
            gap: 1rem;
        }

        /* Schedule Grid */
        .schedule-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
            align-items: center;
        }

        .day-schedule {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .day-label {
            min-width: 100px;
            font-weight: 500;
        }

        /* Checkbox Group */
        .checkbox-group {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .checkbox-label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.875rem;
            cursor: pointer;
        }

        .checkbox-label input[type="checkbox"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
        }

        /* Radio Group */
        .radio-group {
            display: flex;
            gap: 2rem;
        }

        .radio-label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.875rem;
            cursor: pointer;
        }

        /* Tags/Attributes */
        .tags-container {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-top: 0.5rem;
        }

        .tag {
            background: #f1f3f5;
            padding: 0.25rem 0.75rem;
            border-radius: 999px;
            font-size: 0.75rem;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .tag i {
            cursor: pointer;
            color: #6c757d;
        }

        .tag i:hover {
            color: #e63946;
        }

        .add-tag-btn {
            background: none;
            border: 1px dashed #ced4da;
            padding: 0.25rem 0.75rem;
            border-radius: 999px;
            font-size: 0.75rem;
            color: #6c757d;
            cursor: pointer;
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            justify-content: flex-end;
            gap: 1rem;
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid #e9ecef;
        }

        .btn-primary {
            background: #4361ee;
            color: white;
            border: none;
            padding: 0.75rem 2rem;
            border-radius: 8px;
            font-size: 0.875rem;
            font-weight: 500;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-primary:hover {
            background: #3651d4;
        }

        .btn-secondary {
            background: white;
            color: #6c757d;
            border: 1px solid #e9ecef;
            padding: 0.75rem 2rem;
            border-radius: 8px;
            font-size: 0.875rem;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-secondary:hover {
            background: #f8f9fa;
            border-color: #ced4da;
        }

        .btn-danger {
            background: white;
            color: #e63946;
            border: 1px solid #e63946;
            padding: 0.75rem 2rem;
            border-radius: 8px;
            font-size: 0.875rem;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-danger:hover {
            background: #e63946;
            color: white;
        }

        /* Info Box */
        .info-box {
            background: #e1e8ff;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            color: #4361ee;
        }

        .info-box i {
            font-size: 1.5rem;
        }

        .info-box p {
            font-size: 0.875rem;
        }

        /* Security Section */
        .security-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 8px;
            margin-bottom: 0.5rem;
        }

        .security-info h4 {
            font-weight: 500;
            font-size: 0.875rem;
            margin-bottom: 0.25rem;
        }

        .security-info p {
            font-size: 0.75rem;
            color: #6c757d;
        }

        .toggle-switch {
            width: 48px;
            height: 24px;
            background: #e9ecef;
            border-radius: 999px;
            position: relative;
            cursor: pointer;
            transition: all 0.2s;
        }

        .toggle-switch.active {
            background: #4361ee;
        }

        .toggle-switch::after {
            content: '';
            width: 20px;
            height: 20px;
            background: white;
            border-radius: 50%;
            position: absolute;
            top: 2px;
            left: 2px;
            transition: all 0.2s;
        }

        .toggle-switch.active::after {
            left: 26px;
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
            
            .form-group.full-width {
                grid-column: span 1;
            }
            
            .location-grid {
                grid-template-columns: 1fr;
            }
            
            .schedule-grid {
                grid-template-columns: 1fr;
            }
            
            .image-upload-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .sidebar {
                display: none;
            }
            
            .main {
                margin-left: 0;
                padding: 1rem;
            }
            
            .settings-nav {
                flex-direction: column;
            }
            
            .settings-nav-item {
                width: 100%;
                justify-content: center;
            }
            
            .radio-group {
                flex-direction: column;
                gap: 0.5rem;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .action-buttons button {
                width: 100%;
                justify-content: center;
            }
        }
    </style>

            

            <!-- Settings Navigation -->
            <div class="settings-nav">
                <div class="settings-nav-item active" data-section="general">
                    <i class="fas fa-store"></i>
                    General
                </div>
                <div class="settings-nav-item" data-section="images">
                    <i class="fas fa-image"></i>
                    Imágenes
                </div>
                <div class="settings-nav-item" data-section="contact">
                    <i class="fas fa-address-card"></i>
                    Contacto
                </div>
                <div class="settings-nav-item" data-section="location">
                    <i class="fas fa-map-marker-alt"></i>
                    Ubicación
                </div>
                <div class="settings-nav-item" data-section="schedule">
                    <i class="fas fa-clock"></i>
                    Horario
                </div>
                <div class="settings-nav-item" data-section="policies">
                    <i class="fas fa-file-contract"></i>
                    Políticas
                </div>
                <div class="settings-nav-item" data-section="payment">
                    <i class="fas fa-credit-card"></i>
                    Pago y envío
                </div>
                <div class="settings-nav-item" data-section="notifications">
                    <i class="fas fa-bell"></i>
                    Notificaciones
                </div>
                <div class="settings-nav-item" data-section="security">
                    <i class="fas fa-shield-alt"></i>
                    Seguridad
                </div>
            </div>

            <!-- General Section -->
            <div class="settings-section" id="general">
                <h3 class="section-title">
                    <i class="fas fa-store"></i>
                    Información general
                </h3>

                <div class="form-grid">
                    <div class="form-group full-width">
                        <label class="form-label">
                            <i class="fas fa-tag"></i>
                            Nombre de la tienda *
                        </label>
                        <input type="text" class="form-control" value="Nexus Fashion Store">
                    </div>

                    <div class="form-group full-width">
                        <label class="form-label">
                            <i class="fas fa-link"></i>
                            URL amigable
                        </label>
                        <input type="text" class="form-control" value="nexusbuy.com/tienda/nexus-fashion">
                        <div style="font-size: 0.75rem; color: #6c757d; margin-top: 0.25rem;">
                            Esta será la dirección web de tu tienda
                        </div>
                    </div>

                    <div class="form-group full-width">
                        <label class="form-label">
                            <i class="fas fa-align-left"></i>
                            Descripción de la tienda
                        </label>
                        <textarea class="form-control">Tienda de ropa urbana y accesorios. Especializados en moda oversize y streetwear. Productos 100% originales con envíos a toda Cuba.</textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-globe"></i>
                            Idioma principal
                        </label>
                        <select class="form-control">
                            <option>Español</option>
                            <option>Inglés</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-flag"></i>
                            País
                        </label>
                        <input type="text" class="form-control" value="Cuba" disabled>
                    </div>

                    <div class="form-group full-width">
                        <label class="form-label">
                            <i class="fas fa-tags"></i>
                            Categorías principales
                        </label>
                        <div class="tags-container">
                            <span class="tag">Ropa <i class="fas fa-times"></i></span>
                            <span class="tag">Calzado <i class="fas fa-times"></i></span>
                            <span class="tag">Accesorios <i class="fas fa-times"></i></span>
                            <span class="tag">Ofertas <i class="fas fa-times"></i></span>
                            <button class="add-tag-btn">
                                <i class="fas fa-plus"></i> Añadir
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Images Section (oculta por defecto) -->
            <div class="settings-section" id="images" style="display: none;">
                <h3 class="section-title">
                    <i class="fas fa-image"></i>
                    Imágenes de la tienda
                </h3>

                <div class="image-upload-grid">
                    <!-- Logo -->
                    <div class="image-upload-card">
                        <label class="image-upload-label">Logo de la tienda</label>
                        <div class="image-preview logo-preview has-image">
                            <img src="https://via.placeholder.com/200x200/4361ee/ffffff?text=NFS" alt="Logo">
                        </div>
                        <div class="image-actions">
                            <button class="btn-outline-sm">
                                <i class="fas fa-upload"></i> Cambiar
                            </button>
                            <button class="btn-outline-sm">
                                <i class="fas fa-trash"></i> Eliminar
                            </button>
                        </div>
                        <div style="font-size: 0.75rem; color: #6c757d; margin-top: 0.5rem;">
                            Recomendado: 200x200px, PNG o JPG
                        </div>
                    </div>

                    <!-- Banner -->
                    <div class="image-upload-card">
                        <label class="image-upload-label">Banner de la tienda</label>
                        <div class="image-preview has-image">
                            <img src="https://via.placeholder.com/1200x400/4361ee/ffffff?text=Nexus+Fashion+Banner" alt="Banner">
                        </div>
                        <div class="image-actions">
                            <button class="btn-outline-sm">
                                <i class="fas fa-upload"></i> Cambiar
                            </button>
                            <button class="btn-outline-sm">
                                <i class="fas fa-trash"></i> Eliminar
                            </button>
                        </div>
                        <div style="font-size: 0.75rem; color: #6c757d; margin-top: 0.5rem;">
                            Recomendado: 1200x400px, JPG o PNG
                        </div>
                    </div>
                </div>

                <div class="info-box">
                    <i class="fas fa-info-circle"></i>
                    <p>Las imágenes se mostrarán en la página pública de tu tienda. El logo aparece en la esquina superior y el banner en la cabecera.</p>
                </div>
            </div>

            <!-- Contact Section -->
            <div class="settings-section" id="contact" style="display: none;">
                <h3 class="section-title">
                    <i class="fas fa-address-card"></i>
                    Información de contacto
                </h3>

                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-envelope"></i>
                            Email de contacto *
                        </label>
                        <input type="email" class="form-control" value="tienda@nexusfashion.com">
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-phone"></i>
                            Teléfono *
                        </label>
                        <input type="text" class="form-control" value="+53 5 1234567">
                    </div>

                    <div class="form-group full-width">
                        <label class="form-label">
                            <i class="fas fa-hashtag"></i>
                            Redes sociales
                        </label>
                        <div style="display: grid; gap: 0.5rem;">
                            <div style="display: flex; gap: 0.5rem;">
                                <i class="fab fa-instagram" style="color: #e4405f; width: 30px;"></i>
                                <input type="text" class="form-control" value="@nexusfashion.cuba">
                            </div>
                            <div style="display: flex; gap: 0.5rem;">
                                <i class="fab fa-facebook" style="color: #1877f2; width: 30px;"></i>
                                <input type="text" class="form-control" value="/nexusfashioncuba">
                            </div>
                            <div style="display: flex; gap: 0.5rem;">
                                <i class="fab fa-whatsapp" style="color: #25d366; width: 30px;"></i>
                                <input type="text" class="form-control" value="+53 5 1234567">
                            </div>
                            <div style="display: flex; gap: 0.5rem;">
                                <i class="fab fa-telegram" style="color: #0088cc; width: 30px;"></i>
                                <input type="text" class="form-control" value="@nexusfashion_bot">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Location Section -->
            <div class="settings-section" id="location" style="display: none;">
                <h3 class="section-title">
                    <i class="fas fa-map-marker-alt"></i>
                    Ubicación y dirección
                </h3>

                <div class="location-grid">
                    <div class="form-group">
                        <label class="form-label">Provincia *</label>
                        <select class="form-control">
                            <option>La Habana</option>
                            <option>Artemisa</option>
                            <option>Mayabeque</option>
                            <option>Matanzas</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Municipio *</label>
                        <select class="form-control">
                            <option>Plaza de la Revolución</option>
                            <option>Playa</option>
                            <option>Centro Habana</option>
                            <option>La Habana Vieja</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Dirección física</label>
                        <input type="text" class="form-control" value="Calle 123 #456 e/ 7ma y 8va, Vedado">
                    </div>
                </div>

                <div style="margin-top: 1rem;">
                    <label class="form-label">
                        <i class="fas fa-map-pin"></i>
                        Ubicación en el mapa
                    </label>
                    <div style="background: #f1f3f5; height: 200px; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #6c757d;">
                        <i class="fas fa-map" style="font-size: 2rem; margin-right: 0.5rem;"></i>
                        Vista previa del mapa (integración con Google Maps)
                    </div>
                </div>
            </div>

            <!-- Schedule Section -->
            <div class="settings-section" id="schedule" style="display: none;">
                <h3 class="section-title">
                    <i class="fas fa-clock"></i>
                    Horario de atención
                </h3>

                <div class="schedule-grid">
                    <div class="day-schedule">
                        <span class="day-label">Lunes a Viernes:</span>
                        <select class="form-control" style="width: 100px;">
                            <option>09:00</option>
                            <option>08:00</option>
                            <option>10:00</option>
                        </select>
                        <span>a</span>
                        <select class="form-control" style="width: 100px;">
                            <option>18:00</option>
                            <option>17:00</option>
                            <option>19:00</option>
                        </select>
                    </div>

                    <div class="day-schedule">
                        <span class="day-label">Sábados:</span>
                        <select class="form-control" style="width: 100px;">
                            <option>10:00</option>
                            <option>09:00</option>
                            <option>11:00</option>
                        </select>
                        <span>a</span>
                        <select class="form-control" style="width: 100px;">
                            <option>14:00</option>
                            <option>13:00</option>
                            <option>15:00</option>
                        </select>
                    </div>

                    <div class="day-schedule">
                        <span class="day-label">Domingos:</span>
                        <select class="form-control" style="width: 150px;">
                            <option>Cerrado</option>
                            <option>10:00 a 14:00</option>
                            <option>11:00 a 15:00</option>
                        </select>
                    </div>
                </div>

                <div style="margin-top: 1rem;">
                    <label class="form-label">
                        <i class="fas fa-calendar-alt"></i>
                        Días festivos cerrado
                    </label>
                    <div class="tags-container">
                        <span class="tag">1 Enero <i class="fas fa-times"></i></span>
                        <span class="tag">1 Mayo <i class="fas fa-times"></i></span>
                        <span class="tag">26 Julio <i class="fas fa-times"></i></span>
                        <span class="tag">10 Octubre <i class="fas fa-times"></i></span>
                        <span class="tag">25 Diciembre <i class="fas fa-times"></i></span>
                        <button class="add-tag-btn">
                            <i class="fas fa-plus"></i> Añadir fecha
                        </button>
                    </div>
                </div>
            </div>

            <!-- Policies Section -->
            <div class="settings-section" id="policies" style="display: none;">
                <h3 class="section-title">
                    <i class="fas fa-file-contract"></i>
                    Políticas de la tienda
                </h3>

                <div class="form-grid">
                    <div class="form-group full-width">
                        <label class="form-label">
                            <i class="fas fa-truck"></i>
                            Políticas de envío
                        </label>
                        <textarea class="form-control">Realizamos envíos a toda La Habana. El costo de envío es de $150 CUP (zona urbana) y $200 CUP (zona periférica). Los pedidos se entregan en un plazo de 24-48 horas hábiles después de la confirmación del pago.</textarea>
                    </div>

                    <div class="form-group full-width">
                        <label class="form-label">
                            <i class="fas fa-undo-alt"></i>
                            Políticas de devolución
                        </label>
                        <textarea class="form-control">Aceptamos devoluciones dentro de los 7 días posteriores a la compra. El producto debe estar en su estado original, sin usar y con todas las etiquetas. Los gastos de envío de la devolución corren por cuenta del cliente, excepto en casos de productos defectuosos.</textarea>
                    </div>

                    <div class="form-group full-width">
                        <label class="form-label">
                            <i class="fas fa-gavel"></i>
                            Términos y condiciones
                        </label>
                        <textarea class="form-control">Al realizar una compra en Nexus Fashion, el cliente acepta los siguientes términos: ...</textarea>
                    </div>
                </div>
            </div>

            <!-- Payment Section -->
            <div class="settings-section" id="payment" style="display: none;">
                <h3 class="section-title">
                    <i class="fas fa-credit-card"></i>
                    Métodos de pago y envío
                </h3>

                <div class="form-grid">
                    <div class="form-group full-width">
                        <label class="form-label">
                            <i class="fas fa-money-bill"></i>
                            Moneda principal
                        </label>
                        <select class="form-control" style="width: 200px;">
                            <option>CUP - Peso Cubano</option>
                            <option>USD - Dólar</option>
                            <option>EUR - Euro</option>
                        </select>
                    </div>

                    <div class="form-group full-width">
                        <label class="form-label">
                            <i class="fas fa-credit-card"></i>
                            Métodos de pago aceptados
                        </label>
                        <div class="checkbox-group">
                            <label class="checkbox-label">
                                <input type="checkbox" checked> Efectivo (entrega)
                            </label>
                            <label class="checkbox-label">
                                <input type="checkbox" checked> Transfermóvil
                            </label>
                            <label class="checkbox-label">
                                <input type="checkbox" checked> EnZona
                            </label>
                            <label class="checkbox-label">
                                <input type="checkbox"> Tarjeta bancaria
                            </label>
                            <label class="checkbox-label">
                                <input type="checkbox"> PayPal
                            </label>
                        </div>
                    </div>

                    <div class="form-group full-width">
                        <label class="form-label">
                            <i class="fas fa-truck"></i>
                            Métodos de envío
                        </label>
                        <div class="radio-group">
                            <label class="radio-label">
                                <input type="radio" name="shipping" checked> Envío a domicilio
                            </label>
                            <label class="radio-label">
                                <input type="radio" name="shipping"> Punto de entrega
                            </label>
                            <label class="radio-label">
                                <input type="radio" name="shipping"> Ambos
                            </label>
                        </div>
                    </div>

                    <div class="form-group full-width">
                        <label class="form-label">
                            <i class="fas fa-calculator"></i>
                            Costo de envío
                        </label>
                        <select class="form-control" style="width: 200px;">
                            <option>Precio fijo</option>
                            <option>Por distancia</option>
                            <option>Por peso</option>
                            <option>Grátis</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Tarifa base</label>
                        <input type="text" class="form-control" value="$150 CUP">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Tarifa por km</label>
                        <input type="text" class="form-control" value="$10 CUP">
                    </div>
                </div>
            </div>

            <!-- Notifications Section -->
            <div class="settings-section" id="notifications" style="display: none;">
                <h3 class="section-title">
                    <i class="fas fa-bell"></i>
                    Notificaciones
                </h3>

                <div class="form-grid">
                    <div class="form-group full-width">
                        <label class="form-label">
                            <i class="fas fa-envelope"></i>
                            Recibir notificaciones por:
                        </label>
                        <div class="checkbox-group">
                            <label class="checkbox-label">
                                <input type="checkbox" checked> Email
                            </label>
                            <label class="checkbox-label">
                                <input type="checkbox" checked> SMS
                            </label>
                            <label class="checkbox-label">
                                <input type="checkbox"> WhatsApp
                            </label>
                            <label class="checkbox-label">
                                <input type="checkbox"> Telegram
                            </label>
                        </div>
                    </div>

                    <div class="form-group full-width">
                        <label class="form-label">
                            <i class="fas fa-bell"></i>
                            Notificarme cuando:
                        </label>
                        <div class="checkbox-group">
                            <label class="checkbox-label">
                                <input type="checkbox" checked> Nuevo pedido
                            </label>
                            <label class="checkbox-label">
                                <input type="checkbox" checked> Pago confirmado
                            </label>
                            <label class="checkbox-label">
                                <input type="checkbox" checked> Stock bajo
                            </label>
                            <label class="checkbox-label">
                                <input type="checkbox" checked> Nueva reseña
                            </label>
                            <label class="checkbox-label">
                                <input type="checkbox" checked> Mensaje de cliente
                            </label>
                            <label class="checkbox-label">
                                <input type="checkbox" checked> Retiro procesado
                            </label>
                            <label class="checkbox-label">
                                <input type="checkbox"> Promociones y novedades
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Security Section -->
            <div class="settings-section" id="security" style="display: none;">
                <h3 class="section-title">
                    <i class="fas fa-shield-alt"></i>
                    Seguridad
                </h3>

                <div class="security-item">
                    <div class="security-info">
                        <h4>Verificación en dos pasos</h4>
                        <p>Añade una capa extra de seguridad a tu cuenta</p>
                    </div>
                    <div class="toggle-switch"></div>
                </div>

                <div class="security-item">
                    <div class="security-info">
                        <h4>Notificaciones de inicio de sesión</h4>
                        <p>Recibe un email cuando alguien acceda a tu cuenta</p>
                    </div>
                    <div class="toggle-switch active"></div>
                </div>

                <div style="margin-top: 2rem;">
                    <h4 style="margin-bottom: 1rem; font-size: 1rem;">Cambiar contraseña</h4>
                    <div class="form-grid" style="max-width: 400px;">
                        <div class="form-group">
                            <label class="form-label">Contraseña actual</label>
                            <input type="password" class="form-control" placeholder="********">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Nueva contraseña</label>
                            <input type="password" class="form-control" placeholder="********">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Confirmar contraseña</label>
                            <input type="password" class="form-control" placeholder="********">
                        </div>
                        <div>
                            <button class="btn-primary" style="padding: 0.75rem 1.5rem;">
                                <i class="fas fa-key"></i>
                                Cambiar contraseña
                            </button>
                        </div>
                    </div>
                </div>

                <div class="section-divider"></div>

                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <h4 style="color: #e63946; margin-bottom: 0.5rem;">Zona de peligro</h4>
                        <p style="color: #6c757d; font-size: 0.875rem;">Una vez que elimines tu tienda, no hay vuelta atrás.</p>
                    </div>
                    <button class="btn-danger">
                        <i class="fas fa-trash"></i>
                        Eliminar tienda
                    </button>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="action-buttons">
                <button class="btn-secondary">
                    <i class="fas fa-times"></i>
                    Cancelar
                </button>
                <button class="btn-primary">
                    <i class="fas fa-save"></i>
                    Guardar cambios
                </button>
            </div>
        </main>
    </div>
<?php
include_once '../layouts/footer.php';
?>
    <script>
        // Navegación entre secciones
        const navItems = document.querySelectorAll('.settings-nav-item');
        const sections = {
            general: document.getElementById('general'),
            images: document.getElementById('images'),
            contact: document.getElementById('contact'),
            location: document.getElementById('location'),
            schedule: document.getElementById('schedule'),
            policies: document.getElementById('policies'),
            payment: document.getElementById('payment'),
            notifications: document.getElementById('notifications'),
            security: document.getElementById('security')
        };

        navItems.forEach(item => {
            item.addEventListener('click', function() {
                // Remover active de todos
                navItems.forEach(nav => nav.classList.remove('active'));
                this.classList.add('active');

                // Ocultar todas las secciones
                Object.values(sections).forEach(section => {
                    if(section) section.style.display = 'none';
                });

                // Mostrar la sección seleccionada
                const sectionId = this.dataset.section;
                if(sections[sectionId]) {
                    sections[sectionId].style.display = 'block';
                }
            });
        });

        // Toggle switches
        document.querySelectorAll('.toggle-switch').forEach(toggle => {
            toggle.addEventListener('click', function() {
                this.classList.toggle('active');
            });
        });

        // Botones de guardar
        document.querySelector('.btn-primary').addEventListener('click', function() {
            alert('Cambios guardados (demo)');
        });

        document.querySelector('.btn-secondary').addEventListener('click', function() {
            alert('Cambios cancelados (demo)');
        });

        // Botón eliminar tienda
        document.querySelector('.btn-danger').addEventListener('click', function() {
            if(confirm('¿Estás seguro de que quieres eliminar tu tienda? Esta acción no se puede deshacer.')) {
                alert('Tienda eliminada (demo)');
            }
        });

        // Subir imágenes
        document.querySelectorAll('.btn-outline-sm').forEach(btn => {
            btn.addEventListener('click', function() {
                if(this.querySelector('.fa-upload')) {
                    alert('Selector de imágenes (demo)');
                } else if(this.querySelector('.fa-trash')) {
                    alert('Imagen eliminada (demo)');
                }
            });
        });

        // Añadir tags
        document.querySelectorAll('.add-tag-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const tag = prompt('Añadir nuevo elemento:');
                if(tag) {
                    alert(`Añadiendo: ${tag} (demo)`);
                }
            });
        });

        // Eliminar tags
        document.querySelectorAll('.tag i').forEach(closeBtn => {
            closeBtn.addEventListener('click', function() {
                this.closest('.tag').remove();
            });
        });
    </script>
