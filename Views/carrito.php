<?php
include_once 'Layauts/header_general.php';
?>
<title>Carrito | NexusBuy</title>
<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1>Carrito de compras</h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="../index.php">Home</a></li>
          <li class="breadcrumb-item active">Carrito de Compras</li>
        </ol>
      </div>
    </div>
  </div><!-- /.container-fluid -->
</section>

<section class="content">

  <!-- Default box -->
  <div class="container-fluid">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">Artículos en tu carrito</h3>
      </div>

      <div class="card-body">
        <div class="form-check mb-3">
          <input type="checkbox" name="seleccionar_items" id="seleccionar_items" class="form-check-input">
          <label for="seleccionar-items" class="form-check-label">Seleccionar todos los productos</label>
        </div>
        <div class="row">
          <div class="col-md-7">
            <div id="articulos" class="articulos-container">
              
            </div>
          </div>

          <div class="col-md-5">
            <div class="card"   style="border-radius: 8px">
              <div class="card-header" style="background: #f8f9fa">
                <h5>Resumen de compra</h5>
              </div>
              <div class="card-body">
                <div class="row">
                  <div class="col-sm-8">
                    <strong>Subtotal:</strong><br>
                    <strong>Envio:</strong><br>
                    <strong>Descuento:</strong><br>
                    <strong style="font-size: 18px">Total:</strong>
                  </div>
                  <div class="col-sm-4 text-right">
                    <strong id="subtotal">$ 0</strong><br>
                    <strong id="envio">$ 0</strong><br>
                    <strong id="descuento">$ 0</strong><br>
                    <strong id="total" style="font-size: 18px">$ 0</strong>
                  </div>
                </div>
                <button type="button" id="btn-pagar" class="btn btn-block btn-danger mt-3" disable>Proceder al pago</button>
              </div>
            </div>
            <hr>
            <div class="card"   style="border-radius: 8px">
              <div class="card-header" style="background: #f8f9fa">
                <h5>Métodos de pago aceptados</h5>
                <div class="mt-2">
                  <img src="../Util/Img/Credit/visa.png" alt="Visa" width="40" class="mr-2">
                  <img src="../Util/Img/Credit/mastercard.png" alt="Mastercard" width="40" class="mr-2">
                  <img src="../Util/Img/Credit/american-express.png" alt="American Express" width="40" class="mr-2">
                  <img src="../Util/Img/Credit/paypal.png" alt="PayPal" width="40" class="mr-2">
                  <img src="../Util/Img/Credit/cirrus.png" alt="Cirrus" width="40" class="mr-2">
                </div>
              </div>
              <div class="card-body">
                <h5><strong><i class="fas fa-shield-alt text-success mr-2"></i>Protección al comprar</strong></h5>
                <p class="mb-0" small>Recibe un reembolso completo si el artículo no es como se describe o no se entrega.</p>
              </div>
            </div>
            <div class="card mt-3 border-success">
              <div class="card-body text-center">
                <i class="fas fa-lock text-success fa-2x mb-2"></i>
                <p class="small mb-0">Transacciones 100% seguras y encriptadas</p>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="card-footer">
        <div class="row">
          <div class="col-md-6">
            <a href="../index.php" class="btn btn-outline-secondary">
              <i class="fas fa arrow-left mr-2"></i>Seguir comprando
            </a>
          </div>
          <div class="col-md-6" text-right>
            <button type="button" id="btn-vaciar-carrito" class="btn btn-outline-danger">
              <i class="fas fa-trash mr-2"></i>Vaciar carrito
            </button>
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
?>
<script src="carrito.js"></script>