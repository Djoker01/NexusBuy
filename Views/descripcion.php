<?php
if (!empty($_GET['id']) && $_GET['name']) {
  session_start();
  $_SESSION['product-verification'] = $_GET['id'];
  //echo $_SESSION['product-verification'];
  include_once 'Layauts/header_general.php';
?>
  <title><?php echo $_GET['name'] ?> | NexusBuy</title>
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

    /* Product Gallery */
    .product-gallery {
        position: relative;
        border-radius: var(--border-radius);
        overflow: hidden;
        box-shadow: var(--shadow);
    }

    .main-image {
        background: #f8f9fa;
        border-radius: var(--border-radius);
        overflow: hidden;
        margin-bottom: 15px;
        height: 500px;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }

    .main-image img {
        max-height: 100%;
        max-width: 100%;
        object-fit: contain;
        transition: var(--transition);
    }

    .thumbnail-container {
        display: flex;
        gap: 10px;
        overflow-x: auto;
        padding: 10px 0;
    }

    .thumbnail {
        width: 80px;
        height: 80px;
        border: 2px solid #e9ecef;
        border-radius: 8px;
        overflow: hidden;
        cursor: pointer;
        transition: var(--transition);
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f8f9fa;
    }

    .thumbnail:hover,
    .thumbnail.active {
        border-color: var(--primary);
    }

    .thumbnail img {
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
    }

    /* Product Info */
    .product-info {
        padding-left: 30px;
    }

    .product-title {
        font-size: 2rem;
        font-weight: 700;
        color: var(--dark);
        margin-bottom: 10px;
        line-height: 1.2;
    }

    .product-brand {
        color: var(--primary);
        font-weight: 600;
        font-size: 1.1rem;
        margin-bottom: 5px;
    }

    .product-sku {
        color: #6c757d;
        font-size: 0.9rem;
        margin-bottom: 20px;
    }

    /* Rating */
    .rating-display {
        display: flex;
        align-items: center;
        margin-bottom: 20px;
    }

    .rating-stars {
        color: #ffc107;
        margin-right: 10px;
    }

    .rating-text {
        color: #6c757d;
        font-size: 0.9rem;
    }

    /* Pricing */
    .pricing-section {
        margin-bottom: 25px;
    }

    .current-price {
        font-size: 2.5rem;
        font-weight: 700;
        color: var(--primary);
        line-height: 1;
    }

    .original-price {
        font-size: 1.5rem;
        color: #6c757d;
        text-decoration: line-through;
        margin-left: 10px;
    }

    .discount-badge {
        background: var(--danger);
        color: white;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 0.8rem;
        font-weight: 600;
        margin-left: 10px;
    }

    /* Shipping Info */
    .shipping-card {
        background: var(--gradient-accent);
        color: white;
        border-radius: var(--border-radius);
        padding: 20px;
        margin-bottom: 25px;
        box-shadow: var(--shadow);
    }

    .shipping-card i {
        font-size: 1.5rem;
        margin-bottom: 10px;
    }

    /* Store Info */
    .store-card {
        background: white;
        border-radius: var(--border-radius);
        padding: 20px;
        box-shadow: var(--shadow);
        margin-bottom: 25px;
    }

    .store-rating {
        background: var(--gradient-primary);
        color: white;
        padding: 8px 15px;
        border-radius: 25px;
        font-weight: 600;
    }

    /* Action Buttons */
    .action-buttons {
        display: flex;
        gap: 15px;
        margin-bottom: 25px;
        flex-wrap: wrap;
    }

    .btn-action {
        flex: 1;
        min-width: 200px;
        padding: 15px 25px;
        border: none;
        border-radius: var(--border-radius);
        font-weight: 600;
        font-size: 1rem;
        transition: var(--transition);
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
    }

    .btn-cart {
        background: var(--gradient-primary);
        color: white;
    }

    .btn-cart:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(67, 97, 238, 0.4);
    }

    .btn-favorite {
        background: white;
        color: var(--danger);
        border: 2px solid var(--danger);
    }

    .btn-favorite:hover,
    .btn-favorite.active {
        background: var(--danger);
        color: white;
        transform: translateY(-2px);
    }

    .btn-share {
        background: white;
        color: var(--dark);
        border: 2px solid #e9ecef;
    }

    .btn-share:hover {
        background: #f8f9fa;
        transform: translateY(-2px);
    }

    /* Tabs */
    .product-tabs {
        margin-top: 40px;
    }

    .nav-tabs {
        border-bottom: 2px solid #e9ecef;
    }

    .nav-tabs .nav-link {
        border: none;
        color: #6c757d;
        font-weight: 600;
        padding: 15px 25px;
        transition: var(--transition);
    }

    .nav-tabs .nav-link:hover {
        color: var(--primary);
        background: transparent;
    }

    .nav-tabs .nav-link.active {
        color: var(--primary);
        background: transparent;
        border-bottom: 3px solid var(--primary);
    }

    .tab-content {
        padding: 30px 0;
    }

    /* Description */
    .description-content {
        line-height: 1.8;
        color: #555;
    }

    /* Characteristics Table */
    .characteristics-table {
        width: 100%;
        border-collapse: collapse;
    }

    .characteristics-table tr {
        border-bottom: 1px solid #e9ecef;
    }

    .characteristics-table tr:last-child {
        border-bottom: none;
    }

    .characteristics-table td {
        padding: 15px 10px;
    }

    .characteristics-table td:first-child {
        font-weight: 600;
        color: var(--dark);
        width: 30%;
    }

    /* Reviews */
    .review-card {
        background: white;
        border-radius: var(--border-radius);
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: var(--shadow);
        border-left: 4px solid var(--primary);
    }

    .review-header {
        display: flex;
        justify-content: space-between;
        align-items: start;
        margin-bottom: 15px;
    }

    .reviewer-info h6 {
        margin: 0;
        font-weight: 600;
    }

    .review-date {
        color: #6c757d;
        font-size: 0.8rem;
    }

    .review-form-container {
        background: white;
        border-radius: var(--border-radius);
        padding: 25px;
        box-shadow: var(--shadow);
        border-left: 4px solid var(--primary);
    }

    /* Star Rating */
    .calificacion-estrellas {
        direction: ltr;
        display: inline-flex;
        gap: 5px;
    }

    .calificacion-estrellas input[type="radio"] {
        display: none;
    }

    .calificacion-estrellas .estrella {
        color: #ddd;
        font-size: 1.5em;
        cursor: pointer;
        transition: var(--transition);
    }

    .calificacion-estrellas .estrella:hover,
    .calificacion-estrellas input[type="radio"]:checked ~ .estrella {
        color: #ffc107;
    }

    .calificacion-estrellas:hover .estrella {
        color: #ffc107;
    }

    .calificacion-estrellas input[type="radio"]:checked ~ .estrella {
        color: #ffc107;
    }

    /* Share Modal */
    .share-modal .modal-content {
        border-radius: var(--border-radius);
        border: none;
        box-shadow: var(--shadow-hover);
    }

    .share-modal .modal-header {
        background: var(--gradient-primary);
        color: white;
        border-radius: var(--border-radius) var(--border-radius) 0 0;
        border: none;
    }

    .btn-social {
        border: none;
        padding: 15px;
        border-radius: 8px;
        color: white;
        transition: var(--transition);
        font-size: 0.8rem;
        width: 100%;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 5px;
    }

    .btn-social:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    }

    .btn-facebook { background: #3b5998; }
    .btn-twitter { background: #1da1f2; }
    .btn-whatsapp { background: #25d366; }
    .btn-copy { background: #6c757d; }

    /* Responsive */
    @media (max-width: 768px) {
        .product-info {
            padding-left: 0;
            margin-top: 30px;
        }

        .product-title {
            font-size: 1.5rem;
        }

        .current-price {
            font-size: 2rem;
        }

        .action-buttons {
            flex-direction: column;
        }

        .btn-action {
            min-width: 100%;
        }

        .main-image {
            height: 300px;
        }
    }

    /* Animation */
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .product-gallery,
    .product-info,
    .tab-content {
        animation: fadeIn 0.6s ease-out;
    }

    /* Stock Status */
    .stock-status {
        display: inline-flex;
        align-items: center;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 0.9rem;
        font-weight: 600;
        margin-bottom: 15px;
    }

    .stock-in {
        background: #d4edda;
        color: #155724;
    }

    .stock-low {
        background: #fff3cd;
        color: #856404;
    }

    .stock-out {
        background: #f8d7da;
        color: #721c24;
    }
  </style>

  <!-- Modal para compartir -->
  <div class="modal fade share-modal" id="modalCompartir" tabindex="-1" role="dialog" aria-labelledby="modalCompartirLabel" aria-hidden="true">
      <div class="modal-dialog modal-sm" role="document">
          <div class="modal-content">
              <div class="modal-header">
                  <h5 class="modal-title" id="modalCompartirLabel">
                      <i class="fas fa-share-alt mr-2"></i>Compartir producto
                  </h5>
                  <button type="button" class="close text-white" data-bs-dismiss="modal" aria-label="Close">
                      <span aria-hidden="true">&times;</span>
                  </button>
              </div>
              <div class="modal-body text-center">
                  <div class="row">
                      <!-- Facebook -->
                      <div class="col-6 mb-3">
                          <button type="button" class="btn btn-social btn-facebook" id="btn-facebook">
                              <i class="fab fa-facebook-f fa-lg"></i>
                              <small>Facebook</small>
                          </button>
                      </div>
                      
                      <!-- Twitter -->
                      <div class="col-6 mb-3">
                          <button type="button" class="btn btn-social btn-twitter" id="btn-twitter">
                              <i class="fab fa-twitter fa-lg"></i>
                              <small>Twitter</small>
                          </button>
                      </div>
                      
                      <!-- WhatsApp -->
                      <div class="col-6 mb-3">
                          <button type="button" class="btn btn-social btn-whatsapp" id="btn-whatsapp">
                              <i class="fab fa-whatsapp fa-lg"></i>
                              <small>WhatsApp</small>
                          </button>
                      </div>
                      
                      <!-- Copiar enlace -->
                      <div class="col-6 mb-3">
                          <button type="button" class="btn btn-social btn-copy" id="btn-copiar">
                              <i class="fas fa-link fa-lg"></i>
                              <small>Copiar</small>
                          </button>
                      </div>
                  </div>
                  
                  <!-- Enlace para copiar -->
                  <div class="form-group mt-3">
                      <label class="small text-muted">Enlace del producto:</label>
                      <div class="input-group">
                          <input type="text" class="form-control form-control-sm" id="enlace-producto" readonly>
                          <div class="input-group-append">
                              <button class="btn btn-outline-primary btn-sm" type="button" id="btn-copiar-input">
                                  <i class="fas fa-copy"></i>
                              </button>
                          </div>
                      </div>
                  </div>
              </div>
          </div>
      </div>
  </div>

  <section class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1><?php echo $_GET['name'] ?></h1>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="../index.php">Home</a></li>
            <li class="breadcrumb-item active"><?php echo $_GET['name'] ?></li>
          </ol>
        </div>
      </div>
    </div>
  </section>

  <section class="content">
    <div class="container-fluid">
      <div class="card card-solid">
        <div class="card-body">
          <div class="row">
            <!-- Product Gallery -->
            <div id="imagenes" class="col-12 col-lg-6">
              <div class="product-gallery">
                <div class="main-image">
                  <img id="main-product-image" src="" alt="<?php echo $_GET['name'] ?>" class="img-fluid">
                </div>
                <div class="thumbnail-container" id="thumbnail-container">
                  <!-- Las miniaturas se cargarán aquí dinámicamente -->
                </div>
              </div>
            </div>

            <!-- Product Info -->
            <div class="col-12 col-lg-6">
              <div class="product-info">
                <h1 class="product-title" id="producto"></h1>
                <div class="product-brand" id="marca"></div>
                <div class="product-sku" id="sku"></div>

                <!-- Rating -->
                <div class="rating-display">
                  <div class="rating-stars" id="rating-stars">
                    <!-- Las estrellas se generarán dinámicamente -->
                  </div>
                  <div class="rating-text" id="rating-text"></div>
                </div>

                <!-- Stock Status -->
                <div class="stock-status stock-in" id="stock-status">
                  <i class="fas fa-check-circle mr-2"></i>
                  <span id="stock-text"></span>
                </div>

                <!-- Pricing -->
                <div class="pricing-section">
                  <div class="d-flex align-items-center flex-wrap">
                    <span class="current-price" id="current-price"></span>
                    <span class="original-price" id="original-price"></span>
                    <span class="discount-badge" id="discount-badge"></span>
                  </div>
                </div>

                <!-- Shipping Info -->
                <div class="shipping-card">
                  <div class="d-flex align-items-center">
                    <i class="fas fa-shipping-fast mr-3"></i>
                    <div>
                      <h5 class="mb-1" id="shipping-title">Envío Gratis</h5>
                      <p class="mb-0" id="shipping-desc">Recíbelo en 2-4 días hábiles</p>
                    </div>
                  </div>
                </div>

                <!-- Action Buttons -->
                <div class="action-buttons">
                  <button class="btn-action btn-cart" id="btn-agregar-carrito" data-producto-id="<?php echo $_SESSION['product-verification']; ?>">
                    <i class="fas fa-cart-plus"></i>
                    Agregar al Carrito
                  </button>
                  <button class="btn-action btn-favorite" id="btn-favorito" data-producto-id="<?php echo $_SESSION['product-verification']; ?>">
                    <i class="far fa-heart"></i>
                    <span id="texto-favorito">Favorito</span>
                  </button>
                  <button class="btn-action btn-share" id="btn-compartir" data-producto-nombre="<?php echo htmlspecialchars($_GET['name']); ?>">
                    <i class="fas fa-share-alt"></i>
                    Compartir
                  </button>
                </div>

                <!-- Store Info -->
                <div class="store-card">
                  <div class="d-flex justify-content-between align-items-center">
                    <div>
                      <h5 class="mb-1">Vendido por</h5>
                      <span class="text-muted" id="nombre_tienda">NexusBuy Official</span>
                    </div>
                    <div class="store-rating">
                      <i class="fas fa-star mr-1"></i>
                      <span id="promedio_calificacion_tienda">4.8</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Product Tabs -->
          <div class="product-tabs">
            <nav>
              <div class="nav nav-tabs" id="product-tab" role="tablist">
                <a class="nav-item nav-link active" id="product-desc-tab" data-toggle="tab" href="#product-desc" role="tab" aria-controls="product-desc" aria-selected="true">
                  <i class="fas fa-file-alt mr-2"></i>Descripción
                </a>
                <a class="nav-item nav-link" id="product-caract-tab" data-toggle="tab" href="#product-caract" role="tab" aria-controls="product-caract" aria-selected="false">
                  <i class="fas fa-list mr-2"></i>Características
                </a>
                <a class="nav-item nav-link" id="product-rese-tab" data-toggle="tab" href="#product-rese" role="tab" aria-controls="product-rese" aria-selected="false">
                  <i class="fas fa-star mr-2"></i>Reseñas
                </a>
              </div>
            </nav>
            
            <div class="tab-content" id="nav-tabContent">
              <!-- Description Tab -->
              <div class="tab-pane fade show active" id="product-desc" role="tabpanel" aria-labelledby="product-desc-tab">
                <div class="description-content" id="product-description">
                  <!-- La descripción se cargará aquí dinámicamente -->
                </div>
              </div>

              <!-- Characteristics Tab -->
              <div class="tab-pane fade" id="product-caract" role="tabpanel" aria-labelledby="product-caract-tab">
                <table class="characteristics-table">
                  <thead>
                  <tr>
                    <th scope="col">#</th>
                    <th scope="col">Característica</th>
                    <th scope="col">Descripción</th>
                  </tr>
                </thead>
                  <tbody id="caracteristicas">
                    <!-- Las características se cargarán aquí dinámicamente -->
                  </tbody>
                </table>
              </div>

              <!-- Reviews Tab -->
              <div class="tab-pane fade" id="product-rese" role="tabpanel" aria-labelledby="product-rese-tab">
                <div id="reseñas">
                  <!-- Las reseñas se cargarán aquí dinámicamente -->
                </div>
                
                <!-- Review Form -->
                <div id="formulario_reseña" class="mt-4">
                  <?php if(isset($_SESSION['id'])){ ?>
                  <div class="review-form-container">
                    <h5 class="mb-4">Deja tu reseña</h5>
                    <form id="form-reseña" method="post">
                      <div class="form-group">
                        <label class="font-weight-bold mb-3">Calificación:</label>
                        <div class="calificacion-estrellas mb-4">
                          <input type="radio" name="calificacion" id="estrella5" value="5">
                          <label for="estrella5" class="estrella"><i class="fas fa-star"></i></label>
                          <input type="radio" name="calificacion" id="estrella4" value="4">
                          <label for="estrella4" class="estrella"><i class="fas fa-star"></i></label>
                          <input type="radio" name="calificacion" id="estrella3" value="3">
                          <label for="estrella3" class="estrella"><i class="fas fa-star"></i></label>
                          <input type="radio" name="calificacion" id="estrella2" value="2">
                          <label for="estrella2" class="estrella"><i class="fas fa-star"></i></label>
                          <input type="radio" name="calificacion" id="estrella1" value="1">
                          <label for="estrella1" class="estrella"><i class="fas fa-star"></i></label>
                        </div>
                      </div>
                      <div class="form-group">
                        <label class="font-weight-bold">Tu reseña:</label>
                        <textarea name="comentario" class="form-control" rows="4" placeholder="Comparte tu experiencia con este producto..." maxlength="500" required></textarea>
                        <small class="form-text text-muted">
                          <span id="contador-caracteres">0</span>/500 caracteres
                        </small>
                      </div>
                      <div class="text-right">
                        <button type="submit" class="btn btn-primary btn-lg">
                          <i class="fas fa-paper-plane mr-2"></i>
                          Publicar Reseña
                        </button>
                      </div>
                      <input type="hidden" id="id_producto_tienda" name="id_producto_tienda" value="<?php echo $_SESSION['product-verification'];?>">
                    </form>
                  </div>
                  <?php } else { ?>
                  <div class="alert alert-info text-center">
                    <i class="fas fa-info-circle mr-2"></i>
                    <a href="login.php" class="alert-link font-weight-bold">Inicia sesión</a> para dejar una reseña
                  </div>
                  <?php } ?>
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
} else {
  header('Location: ../index.php');
}
?>
<script src="descripcion.js"></script>