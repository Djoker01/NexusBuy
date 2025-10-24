<?php
if (!empty($_GET['id']) && $_GET['name']) {
  session_start();
  $_SESSION['product-verification'] = $_GET['id'];
  //echo $_SESSION['product-verification'];
  include_once 'Layauts/header_general.php';
?>
  <title><?php echo $_GET['name'] ?> | NexusBuy</title>
  <style>
    .product-share a {
    transition: all 0.3s ease;
    text-decoration: none;
    display: inline-block;
}

.product-share a:hover {
    transform: translateY(-2px);
    opacity: 0.8 !important;
}

/* Colores específicos para cada red social */
.product-share a[href*="facebook"]:hover {
    color: #3b5998 !important;
}

.product-share a[href*="instagram"]:hover {
    color: #E4405F !important;
}

.product-share a[href*="tiktok"]:hover {
    color: #000000 !important;
}

.product-share a[href*="youtube"]:hover {
    color: #FF0000 !important;
}

.product-share a[href*="whatsapp"]:hover {
    color: #25D366 !important;
}

.product-share a[href^="mailto"]:hover {
    color: #EA4335 !important;
}

.product-share a[href*="//"]:hover:not([href*="facebook"]):not([href*="instagram"]):not([href*="tiktok"]):not([href*="youtube"]):not([href*="whatsapp"]) {
    color: #28a745 !important;
}
    .calificacion-estrellas{
      direction: rtl;
      unicode-bidi: bidi-override;
      display: inline-block;
    }
    .calificacion-estrellas input[type="radio"]{
      display: none;
    }
    .calificacion-estrellas .estrella{
      color: #ddd;
      font-size: 1.5em;
      padding: 0 2px;
      cursor: pointer;
      transition: color 0.2s;
    }
    .calificacion-estrellas .estrella:hover,
    .calificacion-estrellas .estrella.active,
    .calificacion-estrellas input[type="radio"]:checked ~ .estrella{
      color: #ffc107;
    }
    .calificacion-estrellas .estrella:hover ~ .estrella,
    .calificacion-estrellas input[type="radio"]:checked ~ .estrella{
      color: #ffc107;
    }
    .reseña-form-container{
      background: #f8f9fa;
      padding: 20px;
      border-radius: 8px;
      border-left: 4px solid #007bff;
    }
    #contador-caracteres.text-warning{
      color: #ffc107 !important;
      font-weight: bold;
    }
    #contador-caracteres.text-danger{
      color: #dc3545 !important;
      font-weight: bold;
    }
    #form-reseña textarea{
      resize: vertical;
      min-height: 100px;
    }
    #form-reseña textarea:focus{
      border-color: #007bff;
      box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
    }
    /* Estilos para los botones de acción */
    #btn-agregar-carrito:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    }

    #btn-favorito.btn-danger {
        background: linear-gradient(45deg, #dc3545, #e35d6a);
        border-color: #dc3545;
    }

    #btn-compartir:hover {
        background-color: #6c757d;
        color: white;
    }

    /* Transiciones suaves */
    .btn {
        transition: all 0.3s ease;
    }
    .btn-social {
    border: none;
    padding: 15px 10px;
    border-radius: 8px;
    color: white;
    transition: all 0.3s ease;
    font-size: 0.8rem;
    }

    .btn-social:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    }

    .btn-social:active {
        transform: translateY(0);
    }

    .btn-facebook {
        background: #3b5998;
    }

    .btn-facebook:hover {
        background: #344e86;
    }

    .btn-twitter {
        background: #1da1f2;
    }

    .btn-twitter:hover {
        background: #0d95e8;
    }

    .btn-whatsapp {
        background: #25d366;
    }

    .btn-whatsapp:hover {
        background: #20bd5c;
    }

    .btn-social i {
        margin-bottom: 5px;
    }
  </style>

  <!-- Modal para compartir -->
  <div class="modal fade" id="modalCompartir" tabindex="-1" role="dialog" aria-labelledby="modalCompartirLabel" aria-hidden="true">
      <div class="modal-dialog modal-sm" role="document">
          <div class="modal-content">
              <div class="modal-header">
                  <h5 class="modal-title" id="modalCompartirLabel">
                      <i class="fas fa-share-alt mr-2"></i>Compartir producto
                  </h5>
                  <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                      <span aria-hidden="true">&times;</span>
                  </button>
              </div>
              <div class="modal-body text-center">
                  <div class="row">
                      <!-- Facebook -->
                      <div class="col-6 mb-3">
                          <button type="button" class="btn btn-social btn-facebook w-100" id="btn-facebook">
                              <i class="fab fa-facebook-f fa-lg"></i><br>
                              <small>Facebook</small>
                          </button>
                      </div>
                      
                      <!-- Twitter -->
                      <div class="col-6 mb-3">
                          <button type="button" class="btn btn-social btn-twitter w-100" id="btn-twitter">
                              <i class="fab fa-twitter fa-lg"></i><br>
                              <small>Twitter</small>
                          </button>
                      </div>
                      
                      <!-- WhatsApp -->
                      <div class="col-6 mb-3">
                          <button type="button" class="btn btn-social btn-whatsapp w-100" id="btn-whatsapp">
                              <i class="fab fa-whatsapp fa-lg"></i><br>
                              <small>WhatsApp</small>
                          </button>
                      </div>
                      
                      <!-- Copiar enlace -->
                      <div class="col-6 mb-3">
                          <button type="button" class="btn btn-social btn-secondary w-100" id="btn-copiar">
                              <i class="fas fa-link fa-lg"></i><br>
                              <small>Copiar enlace</small>
                          </button>
                      </div>
                  </div>
                  
                  <!-- Enlace para copiar -->
                  <div class="form-group mt-3">
                      <label class="small text-muted">Enlace del producto:</label>
                      <div class="input-group">
                          <input type="text" class="form-control form-control-sm" id="enlace-producto" readonly>
                          <div class="input-group-append">
                              <button class="btn btn-outline-secondary btn-sm" type="button" id="btn-copiar-input">
                                  <i class="fas fa-copy"></i>
                              </button>
                          </div>
                      </div>
                  </div>
              </div>
          </div>
      </div>
  </div>

  <!-- Cierre del Modal -->


  <section class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1><?php echo $_GET['name'] ?> </h1>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="../index.php">Home</a></li>
            <li class="breadcrumb-item active"><?php echo $_GET['name'] ?> </li>
          </ol>
        </div>
      </div>
    </div><!-- /.container-fluid -->
  </section>

  <!-- Main content -->
  <section class="content">

    <!-- Default box -->
    <div class="card card-solid">
      <div class="card-body">
        <div class="row">
          <div id="imagenes" class="col-12 col-sm-6">

          </div>
          <div class="col-12 col-sm-6">
            <h3 id="producto" class="my-3">LOWA Men’s Renegade GTX Mid Hiking Boots Review</h3>
            <span id="marca"></span></br>
            <span id="sku"></span>
            <div id="informacion_precios">

            </div>
            <hr>
            <div class="card card-light">
              <div id="informacion_envio" class="card-body">

              </div>
            </div>
            <h4>Enviado y vendido por: </h4>

            <div class="bg-light py-2 px-3 mt-4 border">
              <h2 class="mb-0">
                <button class="btn btn-primary">
                  <i class="fas fa-star text-warning mr-1"></i><span id="promedio_calificacion_tienda">4.5</span>
                </button>
                <span id="nombre_tienda" class="text-muted ml-1">nombre de tienda</span>
              </h2>
              <h4 class="mt-0">
                <!-- <small id="numero_reseñas">250 reseñas</small> -->
              </h4>
              <div class="mt-1 product-share">
                <a href="#" class="text-gray">
                  <i class="fab fa-facebook-square fa-2x"></i>
                </a>
                <a href="#" class="text-gray">
                  <i class="fab fa-twitter-square fa-2x"></i>
                </a>
                <a href="#" class="text-gray">
                  <i class="fas fa-envelope-square fa-2x"></i>
                </a>
                <a href="#" class="text-gray">
                  <i class="fas fa-rss-square fa-2x"></i>
                </a>
              </div>
            </div>

            <div class="mt-4">

              <div class="input-group mb-3">
                
                <button class="btn btn-success btn-lg mr-4" id="btn-agregar-carrito" data-producto-id="<?php echo $_SESSION['product-verification']; ?>">
                  <i class="fas fa-cart-plus mr-2"></i>
                  Agregar al carrito
                </button>
                <button class="btn btn-outline-danger btn-lg mr-4" id="btn-favorito" data-producto-id="<?php echo $_SESSION['product-verification']; ?>">
                  <i class="far fa-heart mr-2 text-danger"></i>
                  <span id="texto-favorito">Añadir a Favoritos</span>
                </button>
                <button class="btn btn-outline-secondary btn-lg" id="btn-compartir" data-producto-nombre="<?php echo htmlspecialchars($_GET['name']); ?>">
                  <i class="fas fa-share-alt mr-1 text-danger"></i>
                  Compartir
                </button>
              </div>
            </div>
          </div>
        </div>
        <div class="row mt-4">
          <nav class="w-100">
            <div class="nav nav-tabs" id="product-tab" role="tablist">
              <a class="nav-item nav-link active" id="product-desc-tab" data-toggle="tab" href="#product-desc" role="tab" aria-controls="product-desc" aria-selected="true">Descripción</a>
              <a class="nav-item nav-link" id="product-caract-tab" data-toggle="tab" href="#product-caract" role="tab" aria-controls="product-caract" aria-selected="false">Caracteristicas</a>
              <a class="nav-item nav-link" id="product-rese-tab" data-toggle="tab" href="#product-rese" role="tab" aria-controls="product-rese" aria-selected="false">Reseñas</a>
            </div>
          </nav>
          <div class="tab-content p-3" id="nav-tabContent">
            <div class="tab-pane fade show active" id="product-desc" role="tabpanel" aria-labelledby="product-desc-tab">
              descripción
            </div>
            <div class="tab-pane fade" id="product-caract" role="tabpanel" aria-labelledby="product-caract-tab">
              <table class="table table-hover table-responsive">
                <thead>
                  <tr>
                    <th scope="col">#</th>
                    <th scope="col">Característica</th>
                    <th scope="col">Descripción</th>
                  </tr>
                </thead>
                <tbody id="caracteristicas">

                </tbody>
              </table>
            </div>
            <div class="tab-pane fade" id="product-rese" role="tabpanel" aria-labelledby="product-rese-tab">
              <div id="reseñas" class="card-footer card-comments">
                
              </div>
              <div class="card-footer" id="formulario_reseña">
                <?php if(isset($_SESSION['id'])){?>
                <div class="reseña-form-container">
                  <h6 class="mb-3">Deja tu reseña</h6>
                  <form id="form-reseña" method="post">
                  <div class="form-group">
                    <label id="agr_rese" class="font-weight-bold">Calificación:</label>
                    <div class="calificacion-estrellas mb-3">
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
                    <textarea name="comentario" class="form-control" rows="3" placeholder="Comparte tu experiencia con este producto..." maxlength="500" required></textarea>
                    <small class="form-text text-muted">
                      <span id="contador-caracteres">0</span>
                    </small>
                  </div>
                  <div class="text-right">
                    <button type="submit" class="btn btn-primary">
                      <i class="fas fa-paper-plane mr-2"></i>
                      Publicar Reseña
                    </button>
                  </div>
                  <input type="hidden" id="id_producto_tienda" name="id_producto_tienda" value="<?php echo $_SESSION['product-verification'];?>">
                </form>
                </div>
                <?php }else{ ?>
                  <div class="alert alert-info text-center">
                    <i class="fas fa-info-circle mr-2"></i>
                    <a href="login.php" class="alert-link">Inicia sesión</a>
                  </div>
                  <?php } ?>
                </div>
            </div>
          </div>
        </div>
      </div>
      <!-- /.card-body -->
    </div>
    <!-- /.card -->

  </section>
<?php
  include_once 'Layauts/footer_general.php';
} else {
  header('Location: ../index.php');
}
?>
<script src="descripcion.js"></script>