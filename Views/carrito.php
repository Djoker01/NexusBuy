<?php
include_once 'Layauts/header_general.php';
?>
<title>Carrito de Compras | NexusBuy</title>

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

    /* Cart Header */
    .cart-header {
        background: white;
        border-radius: var(--border-radius);
        box-shadow: var(--shadow);
        padding: 25px;
        margin-bottom: 25px;
        border-left: 4px solid var(--primary);
    }

    .cart-title {
        font-size: 1.8rem;
        font-weight: 700;
        color: var(--dark);
        margin: 0;
    }

    .cart-subtitle {
        color: #6c757d;
        margin: 5px 0 0 0;
    }

    /* Cart Items */
    .cart-items-container {
        background: white;
        border-radius: var(--border-radius);
        box-shadow: var(--shadow);
        overflow: hidden;
    }

    .cart-item {
        display: flex;
        align-items: center;
        padding: 25px;
        border-bottom: 1px solid #e9ecef;
        transition: var(--transition);
        position: relative;
    }

    .cart-item:last-child {
        border-bottom: none;
    }

    .cart-item:hover {
        background: #f8f9fa;
    }

    .cart-item-checkbox {
        margin-right: 20px;
    }

    .cart-item-image {
        width: 100px;
        height: 100px;
        border-radius: 8px;
        overflow: hidden;
        margin-right: 20px;
        flex-shrink: 0;
        background: #f8f9fa;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .cart-item-image img {
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
    }

    .cart-item-details {
        flex: 1;
        margin-right: 20px;
    }

    .cart-item-title {
        font-weight: 600;
        color: var(--dark);
        margin-bottom: 5px;
        line-height: 1.4;
    }

    .cart-item-brand {
        color: #6c757d;
        font-size: 0.9rem;
        margin-bottom: 8px;
    }

    .cart-item-shipping {
        color: var(--success);
        font-size: 0.85rem;
        font-weight: 500;
    }

    .cart-item-price {
        text-align: right;
        margin-right: 20px;
    }

    .current-price {
        font-size: 1.3rem;
        font-weight: 700;
        color: var(--primary);
    }

    .original-price {
        text-decoration: line-through;
        color: #6c757d;
        font-size: 0.9rem;
    }

    .discount-badge {
        background: var(--danger);
        color: white;
        padding: 2px 6px;
        border-radius: 4px;
        font-size: 0.7rem;
        font-weight: 600;
        margin-left: 5px;
    }

    .quantity-controls {
        display: flex;
        align-items: center;
        gap: 10px;
        margin: 10px 0;
    }

    .quantity-btn {
        width: 32px;
        height: 32px;
        border: 1px solid #dee2e6;
        background: white;
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: var(--transition);
    }

    .quantity-btn:hover {
        background: var(--primary);
        color: white;
        border-color: var(--primary);
    }

    .quantity-input {
        width: 60px;
        text-align: center;
        border: 1px solid #dee2e6;
        border-radius: 6px;
        padding: 5px;
        font-weight: 600;
    }

    .cart-item-actions {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .btn-remove {
        background: none;
        border: none;
        color: #6c757d;
        cursor: pointer;
        transition: var(--transition);
        padding: 5px;
        border-radius: 4px;
    }

    .btn-remove:hover {
        color: var(--danger);
        background: rgba(220, 53, 69, 0.1);
    }

    /* Summary Card */
    .summary-card {
        background: white;
        border-radius: var(--border-radius);
        box-shadow: var(--shadow);
        overflow: hidden;
        position: sticky;
        top: 20px;
    }

    .summary-header {
        background: var(--gradient-primary);
        color: white;
        padding: 20px;
        text-align: center;
    }

    .summary-body {
        padding: 25px;
    }

    .summary-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px 0;
        border-bottom: 1px solid #e9ecef;
    }

    .summary-row:last-child {
        border-bottom: none;
    }

    .summary-total {
        font-size: 1.3rem;
        font-weight: 700;
        color: var(--primary);
    }

    .btn-checkout {
        background: var(--gradient-primary);
        color: white;
        border: none;
        border-radius: 8px;
        padding: 15px 30px;
        font-size: 1.1rem;
        font-weight: 600;
        width: 100%;
        transition: var(--transition);
        margin-top: 20px;
    }

    .btn-checkout:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(67, 97, 238, 0.4);
    }

    .btn-checkout:disabled {
        background: #6c757d;
        cursor: not-allowed;
        transform: none;
        box-shadow: none;
    }

    /* Payment Methods */
    .payment-methods {
        background: white;
        border-radius: var(--border-radius);
        box-shadow: var(--shadow);
        padding: 25px;
        margin-top: 25px;
    }

    .payment-icons {
        display: flex;
        gap: 15px;
        flex-wrap: wrap;
        margin-top: 15px;
    }

    .payment-icon {
        width: 50px;
        height: 30px;
        background: white;
        border: 1px solid #e9ecef;
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 5px;
        transition: var(--transition);
    }

    .payment-icon:hover {
        border-color: var(--primary);
        transform: translateY(-2px);
    }

    .payment-icon img {
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
    }

    /* Security Badge */
    .security-badge {
        background: white;
        border: 2px solid var(--success);
        border-radius: var(--border-radius);
        padding: 20px;
        text-align: center;
        margin-top: 25px;
    }

    .security-icon {
        color: var(--success);
        font-size: 2rem;
        margin-bottom: 10px;
    }

    /* Empty Cart */
    .empty-cart {
        text-align: center;
        padding: 60px 20px;
    }

    .empty-cart-icon {
        font-size: 4rem;
        color: #dee2e6;
        margin-bottom: 20px;
    }

    .empty-cart-title {
        color: #6c757d;
        margin-bottom: 10px;
    }

    /* Footer Actions */
    .cart-footer {
        background: white;
        border-radius: var(--border-radius);
        box-shadow: var(--shadow);
        padding: 25px;
        margin-top: 25px;
    }

    .btn-continue-shopping {
        background: white;
        color: var(--primary);
        border: 2px solid var(--primary);
        border-radius: 8px;
        padding: 12px 30px;
        font-weight: 600;
        transition: var(--transition);
    }

    .btn-continue-shopping:hover {
        background: var(--primary);
        color: white;
        transform: translateY(-2px);
    }

    .btn-clear-cart {
        background: white;
        color: var(--danger);
        border: 2px solid var(--danger);
        border-radius: 8px;
        padding: 12px 30px;
        font-weight: 600;
        transition: var(--transition);
    }

    .btn-clear-cart:hover {
        background: var(--danger);
        color: white;
        transform: translateY(-2px);
    }

    /* Responsive */
    @media (max-width: 768px) {
        .cart-item {
            flex-direction: column;
            align-items: flex-start;
            padding: 20px;
        }

        .cart-item-image {
            width: 80px;
            height: 80px;
            margin-bottom: 15px;
        }

        .cart-item-details {
            margin-right: 0;
            margin-bottom: 15px;
            width: 100%;
        }

        .cart-item-price {
            text-align: left;
            margin-right: 0;
            margin-bottom: 15px;
        }

        .cart-item-actions {
            flex-direction: row;
            width: 100%;
            justify-content: space-between;
        }

        .quantity-controls {
            margin: 0;
        }

        .cart-footer .row {
            flex-direction: column;
            gap: 15px;
        }

        .cart-footer .col-md-6 {
            text-align: center !important;
        }
    }

    /* Animations */
    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .cart-item {
        animation: slideIn 0.4s ease-out;
    }

    /* Selection States */
    .cart-item.selected {
        background: rgba(67, 97, 238, 0.05);
        border-left: 4px solid var(--primary);
    }

    .select-all-container {
        background: #f8f9fa;
        padding: 15px 25px;
        border-bottom: 1px solid #e9ecef;
    }
</style>

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