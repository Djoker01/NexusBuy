<?php
$base_path = "../";
// Titulo de la página
$pageTitle = "Crear Producto";
// Titulo de la vista
$pageName = "Nuevo Producto";
// Descripción de la vista
$pageDescription = "Agrega un nuevo producto a tu catálogo.";

// Incluir Header
include_once '../layouts/header.php';

// Incluir modelos necesarios
require_once '../../../Models/Producto.php';
require_once '../../../Models/Categoria.php';
require_once '../../../Models/Subcategoria.php';
require_once '../../../Models/Marcas.php';
require_once '../../../Models/Tienda.php';

// Verificar tienda
$tienda = new Tienda();
$id_tienda = ($GLOBALS['tienda_actual']->id ?? 'SIN TIENDA');
if (empty($id_tienda) || $id_tienda === 'SIN TIENDA') {
    header('Location: /mi-tienda/configuracion/');
    exit;
}

// Obtener datos para selects
$categoria_model = new Categoria();
$categorias = $categoria_model->obtener_categorias_activas();

$marca_model = new Marcas();
$marcas = $marca_model->obtener_marcas();
?>

<style>
    /* ============================================= */
    /* ESTILOS PARA EL FORMULARIO DE CREACIÓN */
    /* ============================================= */

    .form-container {
        background: white;
        border-radius: 16px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        padding: 2rem;
        margin-bottom: 2rem;
    }

    .form-header {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-bottom: 2rem;
        padding-bottom: 1rem;
        border-bottom: 2px solid #e9ecef;
    }

    .form-header i {
        font-size: 2rem;
        color: #4361ee;
        background: #e1e8ff;
        padding: 1rem;
        border-radius: 12px;
    }

    .form-header h2 {
        font-size: 1.5rem;
        font-weight: 600;
        color: #212529;
        margin: 0;
    }

    .form-header p {
        color: #6c757d;
        margin: 0.25rem 0 0 0;
    }

    .form-section {
        background: #f8f9fa;
        border-radius: 12px;
        padding: 1.5rem;
        margin-bottom: 2rem;
    }

    .section-title {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-bottom: 1.5rem;
    }

    .section-title i {
        font-size: 1.25rem;
        color: #4361ee;
    }

    .section-title h3 {
        font-size: 1.1rem;
        font-weight: 600;
        color: #212529;
        margin: 0;
    }

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
        margin-right: 0.5rem;
    }

    .form-control {
        width: 100%;
        padding: 0.75rem;
        border: 1px solid #e9ecef;
        border-radius: 8px;
        font-size: 0.875rem;
        transition: border-color 0.2s;
    }

    .form-control:focus {
        outline: none;
        border-color: #4361ee;
        box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
    }

    textarea.form-control {
        resize: vertical;
        min-height: 100px;
    }

    /* Image Upload Area */
    .image-upload-grid {
        display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 1rem;
    margin-bottom: 1rem;
    transition: all 0.3s ease;
    }

    .image-upload-card {
        text-align: center;
    position: relative;
    animation: fadeIn 0.3s ease;
    }

    @keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}


    .image-upload-box {
         aspect-ratio: 1;
    border: 2px dashed #e9ecef;
    border-radius: 12px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    color: #6c757d;
    cursor: pointer;
    transition: all 0.2s;
    margin-bottom: 0.5rem;
    position: relative;
    overflow: hidden;
    background: #f8f9fa;
    }

    .image-upload-box:hover {
         border-color: #4361ee;
    color: #4361ee;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(67, 97, 238, 0.15);
    }

    .image-upload-box.has-image {
        border: 2px solid #4361ee;
    background: white;
    }

    .image-upload-box i {
        font-size: 2rem;
        margin-bottom: 0.5rem;
    }

    .image-upload-box span {
        font-size: 0.75rem;
    font-weight: 500;
    }

    .image-preview {
         width: 100%;
    height: 100%;
    object-fit: cover;
    position: absolute;
    top: 0;
    left: 0;
    }

    .image-remove {
        position: absolute;
    top: 5px;
    right: 5px;
    background: rgba(230, 57, 70, 0.9);
    color: white;
    border: none;
    border-radius: 50%;
    width: 28px;
    height: 28px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    font-size: 0.875rem;
    opacity: 0;
    transition: opacity 0.2s, transform 0.2s;
    z-index: 10;
    }

    .image-order-badge {
    position: absolute;
    top: 5px;
    left: 5px;
    background: rgba(67, 97, 238, 0.9);
    color: white;
    border-radius: 50%;
    width: 28px;
    height: 28px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.75rem;
    font-weight: 600;
    z-index: 10;
}

.image-actions {
    display: flex;
    align-items: center;
    gap: 1rem;
    flex-wrap: wrap;
}

.btn-outline-primary {
    background: white;
    border: 1px solid #4361ee;
    color: #4361ee;
    padding: 0.5rem 1rem;
    border-radius: 8px;
    font-size: 0.875rem;
    cursor: pointer;
    transition: all 0.2s;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.btn-outline-primary:hover {
    background: #4361ee;
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(67, 97, 238, 0.2);
}

.btn-outline-primary i {
    font-size: 1rem;
}

.btn-outline-primary:disabled {
    opacity: 0.5;
    cursor: not-allowed;
    pointer-events: none;
    background: #e9ecef;
    color: #6c757d;
    border-color: #ced4da;
}

.btn-outline-primary:disabled:hover {
    transform: none;
    box-shadow: none;
}

/* Responsive */
@media (max-width: 992px) {
    .image-upload-grid {
        grid-template-columns: repeat(3, 1fr);
    }
}

@media (max-width: 768px) {
    .image-upload-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 480px) {
    .image-upload-grid {
        grid-template-columns: 1fr;
    }
}

.principal-badge {
    position: absolute;
    bottom: 5px;
    left: 5px;
    background: linear-gradient(135deg, #ffd700, #ffb700);
    color: #1a2639;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-size: 0.7rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.25rem;
    z-index: 10;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.principal-badge i {
    color: #1a2639;
    font-size: 0.6rem;
}

/* Ajustar posición del badge de orden cuando hay badge de principal */
.image-upload-card .image-order-badge {
    z-index: 11;
}

    /* Variants Section */
    .variants-section {
        border: 1px solid #e9ecef;
        border-radius: 12px;
        padding: 1.5rem;
        margin-top: 1rem;
        background: white;
    }

    .variants-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
    }

    .variants-header h4 {
        font-size: 1rem;
        font-weight: 600;
        margin: 0;
    }

    .variant-type-selector {
        display: flex;
        gap: 1rem;
        margin-bottom: 1rem;
    }

    .variant-type-btn {
        flex: 1;
        padding: 0.75rem;
        border: 1px solid #e9ecef;
        background: white;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.2s;
        font-weight: 500;
    }

    .variant-type-btn.active {
        background: #4361ee;
        color: white;
        border-color: #4361ee;
    }

    .variant-input-group {
        display: flex;
        gap: 0.5rem;
        margin-bottom: 1rem;
    }

    .variant-input {
        flex: 1;
        padding: 0.75rem;
        border: 1px solid #e9ecef;
        border-radius: 8px;
    }

    .variant-add-btn {
        background: #4361ee;
        color: white;
        border: none;
        padding: 0.75rem 1.5rem;
        border-radius: 8px;
        cursor: pointer;
        font-weight: 500;
        white-space: nowrap;
    }

    .variants-list {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        margin-top: 1rem;
    }

    .variant-tag {
        background: #f1f3f5;
        padding: 0.5rem 1rem;
        border-radius: 999px;
        font-size: 0.875rem;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .variant-tag i {
        cursor: pointer;
        color: #6c757d;
        transition: color 0.2s;
    }

    .variant-tag i:hover {
        color: #e63946;
    }

    .variant-tag .price {
        color: #4361ee;
        font-weight: 600;
        margin-left: 0.5rem;
    }

    /* Checkbox and Switch */
    .checkbox-group {
        display: flex;
        flex-direction: column;
        gap: 1rem;
        margin-top: 0.5rem;
    }

    .checkbox-label {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        font-size: 0.875rem;
        cursor: pointer;
    }

    .checkbox-label input[type="checkbox"] {
        width: 18px;
        height: 18px;
    }

    .toggle-switch {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .switch {
        position: relative;
        display: inline-block;
        width: 52px;
        height: 28px;
    }

    .switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    .slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #e9ecef;
        transition: .4s;
        border-radius: 28px;
    }

    .slider:before {
        position: absolute;
        content: "";
        height: 24px;
        width: 24px;
        left: 2px;
        bottom: 2px;
        background-color: white;
        transition: .4s;
        border-radius: 50%;
    }

    input:checked+.slider {
        background-color: #4361ee;
    }

    input:checked+.slider:before {
        transform: translateX(24px);
    }

    /* Oferta */
    .offer-section {
        background: #f8f9fa;
        border-radius: 12px;
        padding: 1rem;
        margin-top: 1rem;
    }

    .offer-row {
        display: flex;
        gap: 1rem;
        align-items: center;
        flex-wrap: wrap;
    }

    .offer-input {
        width: 120px;
    }

    /* SEO Section */
    .seo-preview {
        background: #f8f9fa;
        border-radius: 12px;
        padding: 1rem;
        margin-top: 1rem;
    }

    .seo-url {
        color: #06d6a0;
        font-size: 0.875rem;
        margin-bottom: 0.25rem;
    }

    .seo-title {
        font-weight: 600;
        font-size: 1.1rem;
        color: #1a73e8;
        margin-bottom: 0.25rem;
    }

    .seo-description {
        font-size: 0.875rem;
        color: #4d5156;
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
        transition: background 0.2s;
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
        transition: all 0.2s;
    }

    .btn-secondary:hover {
        background: #f8f9fa;
        border-color: #ced4da;
    }

    .btn-success {
        background: #06d6a0;
        color: white;
        border: none;
        padding: 0.5rem 1rem;
        border-radius: 8px;
        font-size: 0.875rem;
        cursor: pointer;
    }

    .required::after {
        content: "*";
        color: #e63946;
        margin-left: 0.25rem;
    }

    .error-feedback {
        color: #e63946;
        font-size: 0.75rem;
        margin-top: 0.25rem;
        display: none;
    }

    .form-control.error {
        border-color: #e63946;
    }

    .form-control.error+.error-feedback {
        display: block;
    }

    /* Responsive */
    @media (max-width: 992px) {
        .form-grid {
            grid-template-columns: 1fr;
        }

        .form-group.full-width {
            grid-column: span 1;
        }

        .image-upload-grid {
            grid-template-columns: repeat(3, 1fr);
        }
    }

    @media (max-width: 768px) {
        .form-container {
            padding: 1rem;
        }

        .image-upload-grid {
            grid-template-columns: repeat(2, 1fr);
        }

        .action-buttons {
            flex-direction: column;
        }

        .action-buttons button {
            width: 100%;
        }

        .offer-row {
            flex-direction: column;
            align-items: stretch;
        }

        .offer-input {
            width: 100%;
        }
    }

    .autocomplete-wrapper {
        position: relative;
        width: 100%;
    }

    .autocomplete-results {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        max-height: 200px;
        overflow-y: auto;
        background: white;
        border: 1px solid #e9ecef;
        border-radius: 0 0 8px 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        z-index: 1000;
        display: none;
    }

    .autocomplete-results.show {
        display: block;
    }

    .autocomplete-item {
        padding: 0.75rem 1rem;
        cursor: pointer;
        transition: all 0.2s;
        border-bottom: 1px solid #f1f3f5;
    }

    .autocomplete-item:last-child {
        border-bottom: none;
    }

    .autocomplete-item:hover {
        background: #f1f3f5;
        color: #4361ee;
    }

    .autocomplete-item .marca-nombre {
        font-weight: 500;
    }

    .autocomplete-item .marca-id {
        font-size: 0.7rem;
        color: #6c757d;
        margin-left: 0.5rem;
    }

    .autocomplete-item.create-new {
        background: #e1e8ff;
        color: #4361ee;
        font-weight: 500;
    }

    .autocomplete-item.create-new:hover {
        background: #4361ee;
        color: white;
    }

    .autocomplete-item.create-new i {
        margin-right: 0.5rem;
    }

    /* Loading indicator */
    .autocomplete-loading {
        padding: 0.75rem 1rem;
        text-align: center;
        color: #6c757d;
        font-size: 0.875rem;
    }

    .autocomplete-loading i {
        animation: spin 1s linear infinite;
        margin-right: 0.5rem;
    }

    @keyframes spin {
        from {
            transform: rotate(0deg);
        }

        to {
            transform: rotate(360deg);
        }
    }
</style>

<!-- ============================================= -->
<!-- FORMULARIO DE CREACIÓN DE PRODUCTO -->
<!-- ============================================= -->
<!-- <div class="row"> -->
<div class="col-12">
    <div class="form-container">
        <div class="form-header">
            <i class="fas fa-box"></i>
            <div>
                <h2>Crear Nuevo Producto</h2>
                <p>Completa la información para agregar un producto a tu catálogo</p>
            </div>
        </div>

        <form id="form-crear-producto" enctype="multipart/form-data">
            <!-- SECCIÓN 1: INFORMACIÓN BÁSICA -->
            <div class="form-section">
                <div class="section-title">
                    <i class="fas fa-info-circle"></i>
                    <h3>Información Básica</h3>
                </div>

                <div class="form-grid">
                    <!-- Nombre del producto -->
                    <div class="form-group full-width">
                        <label class="form-label required">
                            <i class="fas fa-tag"></i>Nombre del producto
                        </label>
                        <input type="text" class="form-control" id="nombre" name="nombre"
                            placeholder="Ej: Camiseta Oversize Negra" required>
                        <div class="error-feedback">El nombre es obligatorio</div>
                    </div>

                    <!-- SKU -->
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-barcode"></i>SKU (Código único)
                        </label>
                        <input type="text" class="form-control" id="sku" name="sku"
                            placeholder="Ej: CAM-001-NEG">
                        <small class="text-muted">Dejar en blanco para generar automáticamente</small>
                    </div>

                    <!-- Marca -->
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-trademark"></i>Marca
                        </label>
                        <div class="autocomplete-wrapper">
                            <input type="text"
                                class="form-control"
                                id="marca_input"
                                name="marca_nombre"
                                placeholder="Escribe para buscar o crear una marca..."
                                autocomplete="off">
                            <input type="hidden" id="marca_id" name="marca_id">
                            <div class="autocomplete-results" id="marca-results"></div>
                        </div>
                        <small class="text-muted">Si la marca no existe, se creará automáticamente</small>
                    </div>

                    <!-- Descripción corta -->
                    <div class="form-group full-width">
                        <label class="form-label">
                            <i class="fas fa-align-left"></i>Descripción corta
                        </label>
                        <input type="text" class="form-control" id="descripcion_corta" name="descripcion_corta"
                            placeholder="Breve descripción del producto (máx. 200 caracteres)">
                    </div>

                    <!-- Descripción detallada -->
                    <div class="form-group full-width">
                        <label class="form-label">
                            <i class="fas fa-file-alt"></i>Descripción detallada
                        </label>
                        <textarea class="form-control" id="descripcion_larga" name="descripcion_larga"
                            rows="5" placeholder="Descripción completa del producto..."></textarea>
                    </div>
                </div>
            </div>

            <!-- SECCIÓN 2: CATEGORIZACIÓN -->
            <div class="form-section">
                <div class="section-title">
                    <i class="fas fa-sitemap"></i>
                    <h3>Categorización</h3>
                </div>

                <div class="form-grid">
                    <!-- Categoría -->
                    <div class="form-group">
                        <label class="form-label required">
                            <i class="fas fa-folder"></i>Categoría
                        </label>
                        <select class="form-control select2" id="categoria" name="categoria" required>
                            <option value="">Seleccionar categoría</option>
                            <?php foreach ($categorias as $cat): ?>
                                <option value="<?php echo $cat->id; ?>">
                                    <?php echo htmlspecialchars($cat->nombre); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="error-feedback">Debes seleccionar una categoría</div>
                    </div>

                    <!-- Subcategoría -->
                    <div class="form-group">
                        <label class="form-label required">
                            <i class="fas fa-folder-open"></i>Subcategoría
                        </label>
                        <select class="form-control select2" id="subcategoria" name="subcategoria" required>
                            <option value="">Primero selecciona una categoría</option>
                        </select>
                        <div class="error-feedback">Debes seleccionar una subcategoría</div>
                    </div>

                    <!-- Etiquetas -->
                    <div class="form-group full-width">
                        <label class="form-label">
                            <i class="fas fa-tags"></i>Etiquetas (separadas por comas)
                        </label>
                        <input type="text" class="form-control" id="etiquetas" name="etiquetas"
                            placeholder="Ej: moda, verano, oferta">
                    </div>
                </div>
            </div>

            <!-- SECCIÓN 3: PRECIOS Y STOCK -->
            <div class="form-section">
    <div class="section-title">
        <i class="fas fa-dollar-sign"></i>
        <h3>Precios y Stock</h3>
    </div>

    <div class="form-grid">
        <!-- Precio CUP -->
        <div class="form-group">
            <label class="form-label required">
                <i class="fas fa-money-bill"></i>Precio CUP
            </label>
            <div class="input-group">
                <span class="input-group-text">$</span>
                <input type="number" class="form-control" id="precio_cup" name="precio_cup" 
                    step="0.01" min="0" required>
            </div>
            <div class="error-feedback">El precio es obligatorio</div>
        </div>

        <!-- Precio USD (calculado automáticamente) -->
        <div class="form-group">
            <label class="form-label">
                <i class="fas fa-dollar-sign"></i>Precio USD (automático)
            </label>
            <div class="input-group">
                <span class="input-group-text">$</span>
                <input type="number" class="form-control" id="precio_usd" name="precio_usd" 
                    step="0.01" min="0" readonly style="background-color: #f8f9fa;">
                <span class="input-group-text" id="tasa_cambio_info" title="Tasa de cambio actual">1 USD = ? CUP</span>
            </div>
            <small class="text-muted">Se calcula automáticamente según la tasa de cambio</small>
        </div>

        <!-- Stock actual -->
        <div class="form-group">
            <label class="form-label required">
                <i class="fas fa-boxes"></i>Stock actual
            </label>
            <input type="number" class="form-control" id="stock" name="stock" 
                value="0" min="0" required>
        </div>

        <!-- Stock mínimo -->
        <div class="form-group">
            <label class="form-label">
                <i class="fas fa-exclamation-triangle"></i>Stock mínimo
            </label>
            <input type="number" class="form-control" id="stock_minimo" name="stock_minimo" 
                value="5" min="0">
            <small class="text-muted">Alerta cuando el stock baje de esta cantidad</small>
        </div>
    </div>
</div>

            <!-- SECCIÓN 4: IMÁGENES -->
            <div class="form-section">
    <div class="section-title">
        <i class="fas fa-images"></i>
        <h3>Imágenes del Producto</h3>
        <span class="badge-info ml-2">Máximo 10 imágenes</span>
    </div>

    <div class="image-upload-grid" id="image-grid">
        <!-- Las imágenes se agregarán dinámicamente aquí -->
    </div>
    
    <div class="image-actions mt-3">
        <button type="button" class="btn btn-outline-primary" id="add-image-btn" onclick="agregarCampoImagen()">
            <i class="fas fa-plus-circle mr-1"></i> Agregar otra imagen
        </button>
        <small class="text-muted ml-2">Formatos permitidos: JPG, PNG, GIF. Máximo 2MB por imagen.</small>
    </div>
    
    <!-- Mensaje de límite (oculto por defecto) -->
    <div class="alert alert-warning mt-2" id="image-limit-warning" style="display: none;">
        <i class="fas fa-exclamation-triangle mr-1"></i> Has alcanzado el límite máximo de 10 imágenes.
    </div>
</div>

            <!-- SECCIÓN 5: VARIANTES (TALLAS, COLORES) -->
            <div class="form-section">
                <div class="section-title">
                    <i class="fas fa-palette"></i>
                    <h3>Variantes</h3>
                </div>

                <div class="variants-section">
                    <div class="variants-header">
                        <h4>Agregar variantes al producto</h4>
                        <span class="badge-info">Opcional</span>
                    </div>

                    <div class="variant-type-selector">
                        <button type="button" class="variant-type-btn active" onclick="selectVariantType('talla')">
                            <i class="fas fa-ruler"></i> Tallas
                        </button>
                        <button type="button" class="variant-type-btn" onclick="selectVariantType('color')">
                            <i class="fas fa-palette"></i> Colores
                        </button>
                        <button type="button" class="variant-type-btn" onclick="selectVariantType('otro')">
                            <i class="fas fa-plus"></i> Otro
                        </button>
                    </div>

                    <div id="variant-input-container">
                        <div class="variant-input-group">
                            <input type="text" class="variant-input" id="variant-value"
                                placeholder="Ej: S, M, L, XL">
                            <input type="number" class="variant-input" id="variant-price"
                                placeholder="Precio extra" value="0" style="width: 120px;">
                            <button type="button" class="variant-add-btn" onclick="addVariant()">
                                <i class="fas fa-plus"></i> Agregar
                            </button>
                        </div>
                    </div>

                    <div class="variants-list" id="variants-list">
                        <!-- Aquí se agregarán las variantes dinámicamente -->
                    </div>
                </div>
            </div>

            <!-- SECCIÓN 6: OPCIONES ADICIONALES -->
            <div class="form-section">
                <div class="section-title">
                    <i class="fas fa-cog"></i>
                    <h3>Opciones adicionales</h3>
                </div>

                <div class="form-grid">
                    <!-- Destacado -->
                    <div class="form-group">
                        <div class="checkbox-label">
                            <input type="checkbox" id="destacado" name="destacado" value="1">
                            <label for="destacado">Marcar como producto destacado</label>
                        </div>
                        <small class="text-muted">Los productos destacados aparecen en la página principal</small>
                    </div>

                    <!-- Envío gratis -->
                    <div class="form-group">
                        <div class="checkbox-label">
                            <input type="checkbox" id="envio_gratis" name="envio_gratis" value="1">
                            <label for="envio_gratis">Envío gratis</label>
                        </div>
                    </div>

                    <!-- Garantía -->
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-shield-alt"></i>Garantía (meses)
                        </label>
                        <input type="number" class="form-control" id="garantia" name="garantia"
                            value="0" min="0">
                    </div>

                    <!-- Tiempo de entrega -->
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-truck"></i>Tiempo de entrega
                        </label>
                        <select class="form-control" id="tiempo_entrega" name="tiempo_entrega">
                            <option value="inmediato">Inmediato</option>
                            <option value="1-3 dias">1-3 días hábiles</option>
                            <option value="3-5 dias">3-5 días hábiles</option>
                            <option value="5-7 dias">5-7 días hábiles</option>
                            <option value="consultar">Consultar disponibilidad</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- SECCIÓN 7: OFERTA -->
            <div class="form-section">
                <div class="section-title">
                    <i class="fas fa-percent"></i>
                    <h3>Oferta (opcional)</h3>
                </div>

                <div class="offer-section">
                    <div class="form-group">
                        <div class="checkbox-label">
                            <input type="checkbox" id="aplicar_oferta" name="aplicar_oferta" value="1">
                            <label for="aplicar_oferta">Aplicar descuento al producto</label>
                        </div>
                    </div>

                    <div id="oferta-fields" style="display: none;">
                        <div class="offer-row">
                            <div class="offer-input">
                                <label class="form-label">Tipo</label>
                                <select class="form-control" id="tipo_descuento" name="tipo_descuento">
                                    <option value="porcentaje">Porcentaje (%)</option>
                                    <option value="fijo">Monto fijo (CUP)</option>
                                </select>
                            </div>
                            <div class="offer-input">
                                <label class="form-label">Valor</label>
                                <input type="number" class="form-control" id="valor_descuento" name="valor_descuento"
                                    value="0" min="0">
                            </div>
                            <div class="offer-input">
                                <label class="form-label">Fecha inicio</label>
                                <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio">
                            </div>
                            <div class="offer-input">
                                <label class="form-label">Fecha fin</label>
                                <input type="date" class="form-control" id="fecha_fin" name="fecha_fin">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SECCIÓN 8: SEO -->
            <div class="form-section">
                <div class="section-title">
                    <i class="fas fa-search"></i>
                    <h3>Optimización SEO</h3>
                </div>

                <div class="form-grid">
                    <!-- URL amigable -->
                    <div class="form-group full-width">
                        <label class="form-label">
                            <i class="fas fa-link"></i>URL amigable
                        </label>
                        <input type="text" class="form-control" id="url_amigable" name="url_amigable"
                            placeholder="ej: camiseta-oversize-negra">
                        <small class="text-muted">Se generará automáticamente si se deja en blanco</small>
                    </div>

                    <!-- Meta título -->
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-heading"></i>Meta título
                        </label>
                        <input type="text" class="form-control" id="meta_titulo" name="meta_titulo"
                            placeholder="Título para buscadores">
                    </div>

                    <!-- Meta descripción -->
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-align-left"></i>Meta descripción
                        </label>
                        <textarea class="form-control" id="meta_descripcion" name="meta_descripcion"
                            rows="2" placeholder="Descripción para buscadores (máx. 160 caracteres)"></textarea>
                    </div>
                </div>

                <!-- Vista previa SEO -->
                <div class="seo-preview" id="seo-preview">
                    <div class="seo-url">nexusbuy.com/producto/</div>
                    <div class="seo-title" id="seo-title-preview">Título del producto</div>
                    <div class="seo-description" id="seo-description-preview">Descripción del producto para buscadores</div>
                </div>
            </div>

            <!-- Botones de acción -->
            <div class="action-buttons">
                <a href="index.php" class="btn-secondary">
                    <i class="fas fa-times"></i> Cancelar
                </a>
                <button type="submit" class="btn-primary">
                    <i class="fas fa-save"></i> Guardar Producto
                </button>
            </div>
        </form>
    </div>
</div>
<!-- </div> -->

<!-- <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> -->
<script>
    $(document).ready(function() {
        // Inicializar Select2
        $('.select2').select2({
            theme: 'bootstrap4',
            placeholder: 'Seleccionar...'
        });

        // Cargar subcategorías al seleccionar categoría
        $('#categoria').on('change', function() {
            var id_categoria = $(this).val();
            if (id_categoria) {
                $.ajax({
                    url: '../../../Controllers/SubcategoriaController.php',
                    method: 'POST',
                    data: {
                        funcion: 'obtener_subcategorias_por_categoria',
                        id_categoria: id_categoria
                    },
                    success: function(response) {
                        console.log('Respuesta:', response); // Para depurar
                        try {
                            // Si la respuesta es JSON string, parsearla
                            var subcategorias = typeof response === 'string' ? JSON.parse(response) : response;

                            // Verificar si es un array y tiene elementos
                            if (Array.isArray(subcategorias) && subcategorias.length > 0) {
                                var options = '<option value="">Seleccionar subcategoría</option>';
                                subcategorias.forEach(function(sub) {
                                    options += '<option value="' + sub.id + '">' + sub.nombre + '</option>';
                                });
                                $('#subcategoria').html(options).prop('disabled', false);
                            } else {
                                $('#subcategoria').html('<option value="">No hay subcategorías disponibles</option>').prop('disabled', true);
                            }
                        } catch (e) {
                            console.error('Error parsing response:', e);
                            $('#subcategoria').html('<option value="">Error al cargar subcategorías</option>').prop('disabled', true);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX Error:', error);
                        $('#subcategoria').html('<option value="">Error de conexión</option>').prop('disabled', true);
                    }
                });
            } else {
                $('#subcategoria').html('<option value="">Primero selecciona una categoría</option>').prop('disabled', true);
            }
        });

        // Generar URL amigable desde el nombre
        $('#nombre').on('keyup', function() {
            var nombre = $(this).val();
            if (nombre && !$('#url_amigable').val()) {
                var url = nombre.toLowerCase()
                    .replace(/[^a-z0-9\s]/g, '')
                    .replace(/\s+/g, '-')
                    .replace(/-+/g, '-');
                $('#url_amigable').val(url);
            }
        });

        // Actualizar vista previa SEO
        $('#nombre, #meta_titulo, #meta_descripcion').on('keyup', function() {
            var titulo = $('#meta_titulo').val() || $('#nombre').val() || 'Título del producto';
            var descripcion = $('#meta_descripcion').val() || 'Descripción del producto para buscadores';
            var url = $('#url_amigable').val() || 'url-del-producto';

            $('#seo-title-preview').text(titulo);
            $('#seo-description-preview').text(descripcion);
            $('.seo-url').text('nexusbuy.com/producto/' + url);
        });

        // Mostrar/ocultar campos de oferta
        $('#aplicar_oferta').on('change', function() {
            if ($(this).is(':checked')) {
                $('#oferta-fields').slideDown();
            } else {
                $('#oferta-fields').slideUp();
            }
        });

        // Validar fechas de oferta
        $('#fecha_fin').on('change', function() {
            var inicio = $('#fecha_inicio').val();
            var fin = $(this).val();
            if (inicio && fin && fin < inicio) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Fechas inválidas',
                    text: 'La fecha de fin debe ser posterior a la fecha de inicio'
                });
                $(this).val('');
            }
        });

        // Envío del formulario
        $('#form-crear-producto').on('submit', function(e) {
            e.preventDefault();

            // Validar campos obligatorios
            var nombre = $('#nombre').val();
            var categoria = $('#categoria').val();
            var subcategoria = $('#subcategoria').val();
            var precio = $('#precio_cup').val();

            if (!nombre) {
                Swal.fire('Error', 'El nombre del producto es obligatorio', 'error');
                return;
            }
            if (!categoria) {
                Swal.fire('Error', 'Debes seleccionar una categoría', 'error');
                return;
            }
            if (!subcategoria) {
                Swal.fire('Error', 'Debes seleccionar una subcategoría', 'error');
                return;
            }
            if (!precio || precio <= 0) {
                Swal.fire('Error', 'El precio debe ser mayor a 0', 'error');
                return;
            }

            // Mostrar loading
            Swal.fire({
                title: 'Guardando producto...',
                text: 'Por favor espera',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Enviar datos por AJAX
            var formData = new FormData(this);
            formData.append('funcion', 'crear_producto');
            formData.append('id_tienda', <?php echo $id_tienda; ?>);

            $.ajax({
                url: '../../../Controllers/ProductoTiendaController.php',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    try {
                        var res = typeof response === 'string' ? JSON.parse(response) : response;
                        if (res.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Producto creado',
                                text: 'El producto se ha guardado correctamente',
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => {
                                window.location.href = 'index.php';
                            });
                        } else {
                            Swal.fire('Error', res.message || 'Error al guardar el producto', 'error');
                        }
                    } catch (e) {
                        console.error('Error:', e);
                        console.log('Respuesta:', response);
                        Swal.fire('Error', 'Error en la respuesta del servidor', 'error');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', error);
                    Swal.fire('Error', 'Error de conexión', 'error');
                }
            });
        });
    });

    // =============================================
// FUNCIONES PARA IMÁGENES DINÁMICAS
// =============================================
let imageCount = 0;
const MAX_IMAGES = 10;

// Array para almacenar qué imagen es principal
let imagenesData = [];

$(document).ready(function() {
    for (let i = 1; i <= 5; i++) {
        agregarCampoImagen(true);
    }
    actualizarBotones();
});

function agregarCampoImagen(isInitial = false) {
    if (imageCount >= MAX_IMAGES) {
        mostrarAdvertenciaLimite();
        return;
    }
    
    if (!isInitial) {
        imageCount++;
    }
    
    const imageNumber = imageCount + 1;
    const isPrincipal = imageCount === 0; // La primera imagen es principal
    
    // Registrar en el array
    imagenesData.push({
        id: imageNumber,
        esPrincipal: isPrincipal,
        archivo: null
    });
    
    const imageCard = `
        <div class="image-upload-card" id="image-card-${imageNumber}" data-index="${imageCount}" data-principal="${isPrincipal ? 1 : 0}">
            <div class="image-upload-box" id="image-box-${imageNumber}" 
                 onclick="document.getElementById('image-${imageNumber}').click()">
                <i class="fas fa-cloud-upload-alt"></i>
                <span>${isPrincipal ? 'Principal' : 'Agregar'}</span>
                <input type="file" 
                       id="image-${imageNumber}" 
                       name="imagenes[]" 
                       accept="image/*" 
                       style="display: none;" 
                       onchange="previewImage(this, ${imageNumber})"
                       ${isPrincipal ? 'required' : ''}>
            </div>
            <div class="image-order-badge" id="order-badge-${imageNumber}">${imageNumber}</div>
            ${isPrincipal ? '<div class="principal-badge"><i class="fas fa-crown"></i> Principal</div>' : ''}
        </div>
    `;
    
    $('#image-grid').append(imageCard);
    
    if (!isInitial) {
        actualizarOrdenes();
        actualizarBotones();
    }
}

function previewImage(input, num) {
    const box = document.getElementById('image-box-' + num);
    const card = document.getElementById('image-card-' + num);
    
    if (input.files && input.files[0]) {
        // Validar tamaño (2MB)
        if (input.files[0].size > 2 * 1024 * 1024) {
            Swal.fire({
                icon: 'error',
                title: 'Imagen muy grande',
                text: 'La imagen no debe superar los 2MB'
            });
            input.value = '';
            return;
        }
        
        // Validar tipo
        const tipo = input.files[0].type;
        if (!['image/jpeg', 'image/png', 'image/gif'].includes(tipo)) {
            Swal.fire({
                icon: 'error',
                title: 'Formato no válido',
                text: 'Solo se permiten imágenes JPG, PNG o GIF'
            });
            input.value = '';
            return;
        }
        
        // Actualizar array de datos
        const index = imagenesData.findIndex(item => item.id === num);
        if (index !== -1) {
            imagenesData[index].archivo = input.files[0];
        }
        
        const reader = new FileReader();
        
        reader.onload = function(e) {
            box.innerHTML = '';
            
            const img = document.createElement('img');
            img.src = e.target.result;
            img.className = 'image-preview';
            box.appendChild(img);
            
            const removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.className = 'image-remove';
            removeBtn.innerHTML = '<i class="fas fa-times"></i>';
            removeBtn.onclick = function(e) {
                e.stopPropagation();
                eliminarImagen(num);
            };
            box.appendChild(removeBtn);
            
            box.classList.add('has-image');
            
            // Actualizar texto de la caja
            const span = box.querySelector('span');
            if (span) span.remove();
        }
        
        reader.readAsDataURL(input.files[0]);
    }
}

function eliminarImagen(num) {
    Swal.fire({
        title: '¿Eliminar imagen?',
        text: 'Esta acción no se puede deshacer',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e63946',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            // Eliminar del array
            const index = imagenesData.findIndex(item => item.id === num);
            if (index !== -1) {
                imagenesData.splice(index, 1);
            }
            
            $(`#image-card-${num}`).remove();
            
            if (imageCount > 0) imageCount--;
            
            actualizarOrdenes();
            actualizarBotones();
        }
    });
}

function actualizarOrdenes() {
    // Reordenar los números de las imágenes
    $('.image-upload-card').each(function(index) {
        const newNumber = index + 1;
        const oldId = $(this).data('index');
        const wasPrincipal = $(this).data('principal') === 1;
        
        $(this).attr('id', `image-card-${newNumber}`);
        $(this).data('index', index);
        
        // La primera imagen siempre es principal
        const isPrincipal = index === 0;
        $(this).data('principal', isPrincipal ? 1 : 0);
        
        // Actualizar badge
        const badge = $(this).find('.image-order-badge');
        badge.text(newNumber);
        badge.attr('id', `order-badge-${newNumber}`);
        
        // Actualizar input
        const input = $(this).find('input[type="file"]');
        input.attr('id', `image-${newNumber}`);
        input.attr('onchange', `previewImage(this, ${newNumber})`);
        
        // Actualizar required (solo la primera)
        if (isPrincipal) {
            input.prop('required', true);
        } else {
            input.prop('required', false);
        }
        
        // Actualizar texto del span
        const span = $(this).find('.image-upload-box span');
        if (span.length) {
            span.text(isPrincipal ? 'Principal' : 'Agregar');
        }
        
        // Actualizar box
        const box = $(this).find('.image-upload-box');
        box.attr('id', `image-box-${newNumber}`);
        box.attr('onclick', `document.getElementById('image-${newNumber}').click()`);
        
        // Actualizar badge de principal
        let principalBadge = $(this).find('.principal-badge');
        if (isPrincipal) {
            if (!principalBadge.length) {
                $(this).append('<div class="principal-badge"><i class="fas fa-crown"></i> Principal</div>');
            }
        } else {
            principalBadge.remove();
        }
        
        // Actualizar botón de eliminar si existe
        const removeBtn = $(this).find('.image-remove');
        if (removeBtn.length) {
            removeBtn.attr('onclick', `eliminarImagen(${newNumber})`);
        }
    });
    
    // Actualizar array de datos
    imagenesData = [];
    $('.image-upload-card').each(function(index) {
        const id = index + 1;
        const input = $(this).find('input[type="file"]')[0];
        imagenesData.push({
            id: id,
            esPrincipal: index === 0,
            archivo: input?.files[0] || null
        });
    });
}

// Validación antes de enviar el formulario
$('#form-crear-producto').on('submit', function(e) {
    // Verificar que hay al menos una imagen
    const imagenes = $('input[name="imagenes[]"]').filter(function() {
        return this.files.length > 0;
    }).length;
    
    if (imagenes === 0) {
        e.preventDefault();
        Swal.fire({
            icon: 'error',
            title: 'Imagen requerida',
            text: 'Debes agregar al menos una imagen principal'
        });
        return false;
    }
    
    // Verificar que la primera imagen (principal) existe
    const primeraImagen = $('#image-1')[0];
    if (!primeraImagen || !primeraImagen.files || primeraImagen.files.length === 0) {
        e.preventDefault();
        Swal.fire({
            icon: 'error',
            title: 'Imagen principal requerida',
            text: 'La primera imagen debe ser la principal'
        });
        return false;
    }
    
    // Agregar datos de qué imagen es principal al FormData
    var formData = new FormData(this);
    
    // Marcar la primera imagen como principal (1) y las demás como secundarias (0)
    $('input[name="imagenes[]"]').each(function(index) {
        if (this.files.length > 0) {
            formData.append('es_principal_' + index, index === 0 ? 1 : 0);
        }
    });
    
    // Enviar por AJAX
    $.ajax({
        url: '../../../Controllers/ProductoTiendaController.php',
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            try {
                var res = typeof response === 'string' ? JSON.parse(response) : response;
                if (res.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Producto creado',
                        text: 'El producto se ha guardado correctamente',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.href = 'index.php';
                    });
                } else {
                    Swal.fire('Error', res.message || 'Error al guardar el producto', 'error');
                }
            } catch (e) {
                console.error('Error:', e);
                Swal.fire('Error', 'Error en la respuesta del servidor', 'error');
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error:', error);
            Swal.fire('Error', 'Error de conexión', 'error');
        }
    });
    
    return false; // Prevenir envío normal del formulario
});

    // =============================================
    // FUNCIONES PARA VARIANTES
    // =============================================
    var variantType = 'talla';
    var variants = [];

    function selectVariantType(type) {
        variantType = type;

        // Actualizar botones activos
        document.querySelectorAll('.variant-type-btn').forEach(btn => {
            btn.classList.remove('active');
        });
        event.target.classList.add('active');

        // Actualizar placeholder
        var placeholder = '';
        switch (type) {
            case 'talla':
                placeholder = 'Ej: S, M, L, XL';
                break;
            case 'color':
                placeholder = 'Ej: Rojo, Azul, Negro';
                break;
            default:
                placeholder = 'Ej: Material, Tamaño, etc.';
        }
        document.getElementById('variant-value').placeholder = placeholder;
    }

    function addVariant() {
        var value = document.getElementById('variant-value').value.trim();
        var price = document.getElementById('variant-price').value;

        if (!value) return;

        variants.push({
            type: variantType,
            value: value,
            price: parseFloat(price) || 0
        });

        renderVariants();

        document.getElementById('variant-value').value = '';
        document.getElementById('variant-price').value = '0';
    }

    function renderVariants() {
        var container = document.getElementById('variants-list');
        var html = '';

        variants.forEach((v, index) => {
            var typeIcon = v.type === 'talla' ? 'fa-ruler' : (v.type === 'color' ? 'fa-palette' : 'fa-tag');
            html += '<span class="variant-tag">' +
                '<i class="fas ' + typeIcon + '"></i> ' + v.value +
                (v.price > 0 ? ' <span class="price">+$' + v.price + '</span>' : '') +
                ' <i class="fas fa-times" onclick="removeVariant(' + index + ')"></i>' +
                '</span>';
        });

        container.innerHTML = html;
    }

    function removeVariant(index) {
        variants.splice(index, 1);
        renderVariants();
    }

    // =============================================
    // AUTOCOMPLETADO PARA MARCAS
    // =============================================
    let timeoutIdMarca;
    const marcaInput = document.getElementById('marca_input');
    const marcaResults = document.getElementById('marca-results');
    const marcaIdHidden = document.getElementById('marca_id');

    // Buscar marcas mientras el usuario escribe
    marcaInput.addEventListener('input', function() {
        const termino = this.value.trim();

        // Limpiar el ID oculto si el texto cambia
        if (termino === '') {
            marcaIdHidden.value = '';
        }

        clearTimeout(timeoutIdMarca);

        if (termino.length < 2) {
            marcaResults.classList.remove('show');
            return;
        }

        timeoutIdMarca = setTimeout(() => {
            buscarMarcas(termino);
        }, 300);
    });

    // Función para buscar marcas
    function buscarMarcas(termino) {
        // Mostrar loading
        marcaResults.innerHTML = '<div class="autocomplete-loading"><i class="fas fa-spinner"></i> Buscando marcas...</div>';
        marcaResults.classList.add('show');

        $.ajax({
            url: '../../../Controllers/MarcasController.php',
            method: 'POST',
            data: {
                funcion: 'buscar_marcas',
                termino: termino
            },
            dataType: 'json',
            success: function(response) {
                mostrarResultadosMarcas(response, termino);
            },
            error: function(xhr, status, error) {
                console.error('Error buscando marcas:', error);
                marcaResults.innerHTML = '<div class="autocomplete-item">Error al buscar marcas</div>';
            }
        });
    }

    // Mostrar resultados de marcas
    function mostrarResultadosMarcas(marcas, termino) {
        if (!marcas || marcas.length === 0) {
            // No hay resultados, ofrecer crear nueva marca
            marcaResults.innerHTML = `
            <div class="autocomplete-item create-new" onclick="crearNuevaMarca('${termino}')">
                <i class="fas fa-plus-circle"></i> Crear nueva marca: "${termino}"
            </div>
        `;
            marcaResults.classList.add('show');
            return;
        }

        let html = '';
        marcas.forEach(marca => {
            html += `
            <div class="autocomplete-item" onclick="seleccionarMarca(${marca.id}, '${marca.nombre.replace(/'/g, "\\'")}')">
                <span class="marca-nombre">${marca.nombre}</span>
                <span class="marca-id">ID: ${marca.id}</span>
            </div>
        `;
        });

        // Siempre ofrecer crear nueva aunque haya resultados
        html += `
        <div class="autocomplete-item create-new" onclick="crearNuevaMarca('${termino}')">
            <i class="fas fa-plus-circle"></i> Crear nueva marca: "${termino}"
        </div>
    `;

        marcaResults.innerHTML = html;
        marcaResults.classList.add('show');
    }

    // Seleccionar una marca existente
    function seleccionarMarca(id, nombre) {
        marcaInput.value = nombre;
        marcaIdHidden.value = id;
        marcaResults.classList.remove('show');
    }

    // Crear nueva marca
    function crearNuevaMarca(nombre) {
        // Mostrar loading
        marcaResults.innerHTML = '<div class="autocomplete-loading"><i class="fas fa-spinner"></i> Creando marca...</div>';

        $.ajax({
            url: '../../../Controllers/MarcasController.php',
            method: 'POST',
            data: {
                funcion: 'crear_marca',
                nombre: nombre
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    seleccionarMarca(response.id, nombre);
                } else {
                    alert('Error al crear la marca');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error creando marca:', error);
                alert('Error de conexión al crear la marca');
            }
        });
    }

    // Cerrar resultados al hacer clic fuera
    document.addEventListener('click', function(e) {
        if (!marcaInput.contains(e.target) && !marcaResults.contains(e.target)) {
            marcaResults.classList.remove('show');
        }
    });

    // =============================================
// CÁLCULO AUTOMÁTICO DE PRECIO USD
// =============================================
let tasaCambioActual = 1; // Valor por defecto

// Obtener tasa de cambio al cargar la página
$(document).ready(function() {
    obtenerTasaCambio();
});

function obtenerTasaCambio() {
    $.ajax({
        url: '../../../Controllers/MonedaController.php',
        method: 'POST',
        data: {
            funcion: 'obtener_tasa_cambio',
            moneda: 'USD'
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                // La respuesta tiene la estructura que vi en tu controlador
                // console.log('Respuesta moneda:', response);
                
                // Extraer la tasa de cambio
                if (response.moneda && response.moneda.tasa_cambio) {
                    tasaCambioActual = parseFloat(response.moneda.tasa_cambio);
                } else if (response.tasa_cambio) {
                    tasaCambioActual = parseFloat(response.tasa_cambio);
                }
                
                // Actualizar el texto informativo
                $('#tasa_cambio_info').text(`1 USD = ${tasaCambioActual} CUP`);
                
                // Si ya hay un valor en CUP, calcular USD inicial
                calcularUSD();
            } else {
                console.error('Error obteniendo tasa de cambio:', response.error);
                $('#tasa_cambio_info').text('Error al obtener tasa');
            }
        },
        error: function(xhr, status, error) {
            console.error('Error AJAX:', error);
            $('#tasa_cambio_info').text('Error de conexión');
        }
    });
}

// Calcular USD cuando cambia el precio CUP
$('#precio_cup').on('input', function() {
    calcularUSD();
});

function calcularUSD() {
    var precioCUP = parseFloat($('#precio_cup').val()) || 0;
    
    if (precioCUP > 0 && tasaCambioActual > 0) {
        var precioUSD = precioCUP / tasaCambioActual;
        $('#precio_usd').val(precioUSD.toFixed(2));
    } else {
        $('#precio_usd').val('');
    }
}

// Opcional: También permitir editar USD y calcular CUP inversamente
$('#precio_usd').on('input', function() {
    var precioUSD = parseFloat($('#precio_usd').val()) || 0;
    
    if (precioUSD > 0 && tasaCambioActual > 0 && !$(this).is('[readonly]')) {
        var precioCUP = precioUSD * tasaCambioActual;
        $('#precio_cup').val(precioCUP.toFixed(2));
    }
});

// Si quieres permitir edición manual de USD (opcional), quita el atributo readonly
// y descomenta esta función:
/*
// Botón para alternar modo automático/manual
$('#toggle_usd_mode').on('click', function() {
    var usdInput = $('#precio_usd');
    var isReadonly = usdInput.prop('readonly');
    
    if (isReadonly) {
        usdInput.prop('readonly', false).css('background-color', 'white');
        $(this).html('<i class="fas fa-sync-alt"></i> Auto');
    } else {
        usdInput.prop('readonly', true).css('background-color', '#f8f9fa');
        $(this).html('<i class="fas fa-pencil-alt"></i> Manual');
        calcularUSD(); // Recalcular automático
    }
});
*/
</script>

<?php include_once '../layouts/footer.php'; ?>