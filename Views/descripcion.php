<?php
if (!empty($_GET['id']) && $_GET['name']) {
  session_start();
  $_SESSION['product-verification'] = $_GET['id'];
  //echo $_SESSION['product-verification'];
  include_once 'Layauts/header_general.php';
?>
  <title><?php echo $_GET['name'] ?> | NexusBuy</title>
  
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
                    <span id="texto-favorito">Añadir a Favoritos</span>
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