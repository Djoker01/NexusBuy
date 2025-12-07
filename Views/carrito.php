<?php
include_once 'Layauts/header_general.php';
?>
<title>Carrito de Compras | NexusBuy</title>

<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Carrito de Compras</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="../index.php">Home</a></li>
                    <li class="breadcrumb-item active">Carrito de Compras</li>
                </ol>
            </div>
        </div>
    </div>
</section>

<section class="content">
    <div class="container-fluid">
        <!-- Cart Header -->
        <div class="cart-header">
            <h1 class="cart-title">
                <i class="fas fa-shopping-cart mr-2"></i>Tu Carrito
            </h1>
            <p class="cart-subtitle">Revisa y gestiona los productos que has seleccionado</p>
        </div>

        <div class="row">
            <!-- Cart Items -->
            <div class="col-lg-8">
                <div class="cart-items-container">
                    <!-- Select All -->
                    <div class="select-all-container">
                        <div class="form-check">
                            <input type="checkbox" name="seleccionar_items" id="seleccionar_items" class="form-check-input">
                            <label for="seleccionar_items" class="form-check-label font-weight-600">
                                Seleccionar todos los productos
                            </label>
                        </div>
                    </div>

                    <!-- Cart Items Container -->
                    <div id="articulos" class="articulos-container">
                        <!-- Los artículos se cargarán dinámicamente aquí -->
                        <div class="empty-cart" id="empty-cart" style="display: none;">
                            <div class="empty-cart-icon">
                                <i class="fas fa-shopping-cart"></i>
                            </div>
                            <h3 class="empty-cart-title">Tu carrito está vacío</h3>
                            <p class="text-muted mb-4">Descubre productos increíbles y añádelos a tu carrito</p>
                            <a href="producto.php" class="btn btn-primary btn-lg">
                                <i class="fas fa-shopping-bag mr-2"></i>
                                Comenzar a Comprar
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Order Summary -->
            <div class="col-lg-4">
                <!-- Summary Card -->
                <div class="summary-card">
                    <div class="summary-header">
                        <h4 class="mb-0">
                            <i class="fas fa-receipt mr-2"></i>
                            Resumen de Compra
                        </h4>
                    </div>
                    <div class="summary-body">
                        <div class="summary-row">
                            <span>Subtotal:</span>
                            <strong id="subtotal">$ 0.00</strong>
                        </div>
                        <div class="summary-row">
                            <span>Envío:</span>
                            <strong id="envio">$ 0.00</strong>
                        </div>
                        <div class="summary-row">
                            <span>Descuento:</span>
                            <strong id="descuento" class="text-success">-$ 0.00</strong>
                        </div>
                        <div class="summary-row">
                            <span class="font-weight-bold">Total:</span>
                            <strong id="total" class="summary-total">$ 0.00</strong>
                        </div>

                        <button type="button" id="btn-pagar" class="btn-checkout" disabled>
                            <i class="fas fa-lock mr-2"></i>
                            Proceder al Pago
                        </button>

                        <div class="text-center mt-3">
                            <small class="text-muted">
                                <i class="fas fa-lock text-success mr-1"></i>
                                Transacción 100% segura
                            </small>
                        </div>
                    </div>
                </div>

                <!-- Payment Methods -->
                <div class="payment-methods">
                    <h5 class="mb-3">
                        <i class="fas fa-credit-card mr-2"></i>
                        Métodos de Pago
                    </h5>
                    <div class="payment-icons">
                        <div class="payment-icon">
                            <img src="../Util/Img/Credit/visa.png" alt="Visa">
                        </div>
                        <div class="payment-icon">
                            <img src="../Util/Img/Credit/mastercard.png" alt="Mastercard">
                        </div>
                        <div class="payment-icon">
                            <img src="../Util/Img/Credit/american-express.png" alt="American Express">
                        </div>
                        <div class="payment-icon">
                            <img src="../Util/Img/Credit/paypal.png" alt="PayPal">
                        </div>
                        <div class="payment-icon">
                            <img src="../Util/Img/Credit/cirrus.png" alt="Cirrus">
                        </div>
                    </div>
                </div>

                <!-- Security Badge -->
                <div class="security-badge">
                    <div class="security-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h6 class="font-weight-bold mb-2">Compra Protegida</h6>
                    <p class="small mb-0 text-muted">
                        Recibe un reembolso completo si el artículo no es como se describe o no se entrega.
                    </p>
                </div>
            </div>
        </div>

        <!-- Footer Actions -->
        <div class="cart-footer">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <a href="producto.php" class="btn btn-continue-shopping">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Seguir Comprando
                    </a>
                </div>
                <div class="col-md-6 text-md-right">
                    <button type="button" id="btn-vaciar-carrito" class="btn btn-clear-cart">
                        <i class="fas fa-trash mr-2"></i>
                        Vaciar Carrito
                    </button>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
include_once 'Layauts/footer_general.php';
?>
<script src="carrito.js"></script>