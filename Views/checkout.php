<?php
session_start();

// Verificar que hay productos en el carrito y que el usuario está logueado
if (empty($_SESSION['id'])) {
    header('Location: login.php');
    exit();
}

// Obtener datos del usuario para prellenar el formulario
include_once '../Models/Usuario.php';
$usuario = new Usuario();
$usuario->obtener_datos($_SESSION['id']);
$datos_usuario = $usuario->objetos[0] ?? null;

// Verificar que se obtuvieron los datos del usuario
if (!$datos_usuario) {
    echo "Error: No se pudieron cargar los datos del usuario.";
    exit();
}
include_once 'Layauts/header_general.php';
?>



<title>Pasarela de Pago | NexusBuy</title>
<style>
    /* ESTILOS ESPECÍFICOS DEL CHECKOUT - No están en nexusbuy.css */
    
    /* Pasos del checkout - diseño específico */
    .checkout-steps {
        display: flex;
        justify-content: space-between;
        margin-bottom: 30px;
        position: relative;
    }

    .checkout-steps::before {
        content: '';
        position: absolute;
        top: 20px;
        left: 0;
        right: 0;
        height: 2px;
        background: #dee2e6;
        z-index: 1;
    }

    .step {
        text-align: center;
        position: relative;
        z-index: 2;
        flex: 1;
    }

    .step-number {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: #dee2e6;
        color: #6c757d;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 10px;
        font-weight: bold;
        border: 3px solid white;
    }

    .step.active .step-number {
        background: var(--primary);
        color: white;
    }

    .step.completed .step-number {
        background: var(--success);
        color: white;
    }

    .step-label {
        font-size: 14px;
        color: #6c757d;
        font-weight: 500;
    }

    .step.active .step-label {
        color: var(--primary);
    }

    .step.completed .step-label {
        color: var(--success);
    }

    /* Métodos de pago - diseño específico */
    .payment-method {
        border: 2px solid #dee2e6;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 15px;
        cursor: pointer;
        transition: var(--transition);
    }

    .payment-method:hover {
        border-color: var(--primary);
    }

    .payment-method.selected {
        border-color: var(--primary);
        background-color: #f8f9fa;
    }

    .payment-method input[type="radio"] {
        margin-right: 10px;
    }

    /* Contenedores de resumen específicos */
    .resumen-producto {
        border-bottom: 1px solid #dee2e6;
        padding: 10px 0;
    }

    .resumen-producto:last-child {
        border-bottom: none;
    }
    
    /* Ajustes específicos para formularios de checkout */
    .checkout-step {
        animation: fadeIn 0.5s ease;
    }
    
    /* Estilos para el botón de procesar pago específico */
    #btn-procesar-pago {
        min-width: 200px;
        padding: 15px 30px;
        font-size: 1.1rem;
    }
    
    #btn-procesar-pago.loading {
        position: relative;
        color: transparent;
    }
    
    #btn-procesar-pago.loading::after {
        content: '';
        position: absolute;
        left: 50%;
        top: 50%;
        transform: translate(-50%, -50%);
        width: 20px;
        height: 20px;
        border: 2px solid white;
        border-top: 2px solid transparent;
        border-radius: 50%;
        animation: spin 0.8s linear infinite;
    }
    
    /* Responsive específico para checkout */
    @media (max-width: 768px) {
        .checkout-steps {
            flex-direction: column;
            gap: 20px;
            align-items: flex-start;
        }
        
        .checkout-steps::before {
            display: none;
        }
        
        .step {
            display: flex;
            align-items: center;
            gap: 15px;
            text-align: left;
            width: 100%;
        }
        
        .step-number {
            margin: 0;
            flex-shrink: 0;
        }
        
        .checkout-step .card-body {
            padding: 20px;
        }
        
        #btn-procesar-pago {
            width: 100%;
        }
    }
</style>

<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Pasarela de Pago</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="../index.php">Home</a></li>
                    <li class="breadcrumb-item"><a href="carrito.php">Carrito</a></li>
                    <li class="breadcrumb-item active">Checkout</li>
                </ol>
            </div>
        </div>
    </div>
</section>

<section class="content">
    <div class="container-fluid">
        <!-- Pasos del checkout -->
        <div class="checkout-steps">
            <div class="step active" id="step-1">
                <div class="step-number">1</div>
                <div class="step-label">Información de Envío</div>
            </div>
            <div class="step" id="step-2">
                <div class="step-number">2</div>
                <div class="step-label">Método de Pago</div>
            </div>
            <div class="step" id="step-3">
                <div class="step-number">3</div>
                <div class="step-label">Revisar y Pagar</div>
            </div>
        </div>

        <div class="row">
            <!-- Formulario principal -->
            <div class="col-lg-8">
                <form id="form-checkout">
                    <!-- Paso 1: Información de Envío -->
                    <div class="checkout-step" id="step-content-1">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-truck mr-2"></i>Información de Envío
                                </h3>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form-label required">Nombres</label>
                                            <input type="text" class="form-control" id="nombres" name="nombres" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form-label required">Apellidos</label>
                                            <input type="text" class="form-control" id="apellidos" name="apellidos" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="direccion" class="form-label">
                                        Dirección <span class="text-danger">*</span>
                                    </label>
                                    <textarea class="form-control" id="direccion" rows="2"
                                        placeholder="Ej: Calle 123 #456 entre A y B, Municipio, Provincia"></textarea>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form-label required">Teléfono</label>
                                            <input type="tel" class="form-control" id="telefono" name="telefono" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form-label">Email</label>
                                            <input type="email" class="form-control" id="email" name="email">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Instrucciones especiales (Opcional)</label>
                                    <textarea class="form-control" id="instrucciones" name="instrucciones" rows="3" placeholder="Instrucciones para la entrega..."></textarea>
                                </div>
                                <div class="text-right">
                                    <button type="button" class="btn btn-primary" onclick="siguientePaso(2)">
                                        Continuar a Pago <i class="fas fa-arrow-right ml-2"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Paso 2: Método de Pago -->
                    <div class="checkout-step" id="step-content-2" style="display: none;">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-credit-card mr-2"></i>Método de Pago
                                </h3>
                            </div>
                            <div class="card-body">
                                <!-- Métodos de pago guardados -->
                                <div id="metodos-guardados" class="mb-4">
                                    <h5 class="mb-3">Métodos guardados</h5>
                                    <div id="lista-metodos-pago">
                                        <!-- Se llenará dinámicamente -->
                                    </div>
                                </div>

                                <!-- Nuevo método de pago -->
                                <div class="mb-4">
                                    <h5 class="mb-3">Agregar nuevo método de pago</h5>

                                    <div class="payment-method" onclick="seleccionarMetodoPago('tarjeta')">
                                        <input type="radio" name="metodo_pago" id="metodo_tarjeta" value="tarjeta">
                                        <label for="metodo_tarjeta" class="font-weight-bold">
                                            <i class="far fa-credit-card mr-2"></i>Tarjeta de Crédito/Débito
                                        </label>

                                        <div id="form-tarjeta" class="mt-3" style="display: none;">
                                            <div class="row">
                                                <div class="col-12">
                                                    <div class="form-group">
                                                        <label class="form-label required">Nombre del Titular</label>
                                                        <input type="text" class="form-control" name="tarjeta_titular" placeholder="Como aparece en la tarjeta">
                                                    </div>
                                                </div>
                                                <div class="col-12">
                                                    <div class="form-group">
                                                        <label class="form-label required">Número de Tarjeta</label>
                                                        <input type="text" class="form-control" name="tarjeta_numero" placeholder="1234 5678 9012 3456" maxlength="19">
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label class="form-label required">Fecha de Vencimiento</label>
                                                        <input type="text" class="form-control" name="tarjeta_vencimiento" placeholder="MM/AA" maxlength="5">
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label class="form-label required">CVV</label>
                                                        <input type="text" class="form-control" name="tarjeta_cvv" placeholder="123" maxlength="4">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="payment-method" onclick="seleccionarMetodoPago('paypal')">
                                        <input type="radio" name="metodo_pago" id="metodo_paypal" value="paypal">
                                        <label for="metodo_paypal" class="font-weight-bold">
                                            <i class="fab fa-paypal mr-2"></i>PayPal
                                        </label>

                                        <div id="form-paypal" class="mt-3" style="display: none;">
                                            <div class="form-group">
                                                <label class="form-label required">Email de PayPal</label>
                                                <input type="email" class="form-control" name="paypal_email" placeholder="tu@email.com">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="payment-method" onclick="seleccionarMetodoPago('transferencia')">
                                        <input type="radio" name="metodo_pago" id="metodo_transferencia" value="transferencia">
                                        <label for="metodo_transferencia" class="font-weight-bold">
                                            <i class="fas fa-university mr-2"></i>Transferencia Bancaria
                                        </label>

                                        <div id="form-transferencia" class="mt-3" style="display: none;">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label class="form-label required">Banco</label>
                                                        <input type="text" class="form-control" name="transferencia_banco" placeholder="Nombre del banco">
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label class="form-label required">Número de Cuenta</label>
                                                        <input type="text" class="form-control" name="transferencia_cuenta" placeholder="Número de cuenta">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between">
                                    <button type="button" class="btn btn-secondary" onclick="siguientePaso(1)">
                                        <i class="fas fa-arrow-left mr-2"></i>Regresar
                                    </button>
                                    <button type="button" class="btn btn-primary" onclick="siguientePaso(3)">
                                        Revisar Pedido <i class="fas fa-arrow-right ml-2"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Paso 3: Revisar y Pagar -->
                    <div class="checkout-step" id="step-content-3" style="display: none;">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-clipboard-check mr-2"></i>Revisar tu Pedido
                                </h3>
                            </div>
                            <div class="card-body">
                                <!-- Resumen de productos -->
                                <div class="mb-4">
                                    <h5 class="mb-3">Productos en tu pedido</h5>
                                    <div id="resumen-productos">
                                        <!-- Se llenará dinámicamente -->
                                    </div>
                                </div>

                                <!-- Resumen de envío -->
                                <div class="mb-4">
                                    <h5 class="mb-3">Información de Envío</h5>
                                    <div id="resumen-envio" class="bg-light p-3 rounded">
                                        <!-- Se llenará dinámicamente -->
                                    </div>
                                </div>

                                <!-- Resumen de pago -->
                                <div class="mb-4">
                                    <h5 class="mb-3">Método de Pago</h5>
                                    <div id="resumen-pago" class="bg-light p-3 rounded">
                                        <!-- Se llenará dinámicamente -->
                                    </div>
                                </div>

                                <!-- Totales -->
                                <div class="bg-light p-3 rounded mb-4">
                                    <div class="row">
                                        <div class="col-6">
                                            <strong>Subtotal:</strong>
                                        </div>
                                        <div class="col-6 text-right">
                                            <span id="resumen-subtotal">$ 0.00</span>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-6">
                                            <strong>Envío:</strong>
                                        </div>
                                        <div class="col-6 text-right">
                                            <span id="resumen-envio-costo">$ 0.00</span>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-6">
                                            <strong>Descuento:</strong>
                                        </div>
                                        <div class="col-6 text-right">
                                            <span id="resumen-descuento">$ 0.00</span>
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="row">
                                        <div class="col-6">
                                            <strong class="h5">Total:</strong>
                                        </div>
                                        <div class="col-6 text-right">
                                            <span id="resumen-total" class="h5 text-danger">$ 0.00</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-check mb-3">
                                    <input type="checkbox" class="form-check-input" id="terminos" required>
                                    <label class="form-check-label" for="terminos">
                                        Acepto los <a href="#" target="_blank">términos y condiciones</a> y la
                                        <a href="#" target="_blank">política de privacidad</a>
                                    </label>
                                </div>

                                <div class="d-flex justify-content-between">
                                    <button type="button" class="btn btn-secondary" onclick="siguientePaso(2)">
                                        <i class="fas fa-arrow-left mr-2"></i>Regresar
                                    </button>
                                    <button type="button" class="btn btn-success btn-lg" id="btn-procesar-pago" onclick="procesarPago()">
                                        <i class="fas fa-lock mr-2"></i>Realizar Pago
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Resumen del pedido -->
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Resumen del Pedido</h3>
                    </div>
                    <div class="card-body">
                        <div id="resumen-lateral">
                            <!-- Se llenará dinámicamente -->
                        </div>

                        <hr>

                        <div class="row">
                            <div class="col-6">
                                <strong>Subtotal:</strong>
                            </div>
                            <div class="col-6 text-right">
                                <span id="resumen-lateral-subtotal">$ 0.00</span>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-6">
                                <strong>Envío:</strong>
                            </div>
                            <div class="col-6 text-right">
                                <span id="resumen-lateral-envio">$ 0.00</span>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-6">
                                <strong>Descuento:</strong>
                            </div>
                            <div class="col-6 text-right">
                                <span id="#resumen-lateral-descuento">$ 0.00</span>
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-6">
                                <strong class="h5">Total:</strong>
                            </div>
                            <div class="col-6 text-right">
                                <span id="resumen-lateral-total" class="h5 text-danger">$ 0.00</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Información de seguridad -->
                <div class="card mt-3">
                    <div class="card-body text-center">
                        <i class="fas fa-lock text-success fa-2x mb-2"></i>
                        <p class="small mb-0">
                            <strong>Compra 100% segura</strong><br>
                            Tus datos están protegidos con encriptación SSL
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include_once 'Layauts/footer_general.php'; ?>

<script>
    // Pasar datos del usuario a JavaScript de forma segura
    const usuarioData = {
        id: <?php echo json_encode($_SESSION['id']); ?>,
        nombres: <?php echo json_encode($datos_usuario->nombres ?? ''); ?>,
        apellidos: <?php echo json_encode($datos_usuario->apellidos ?? ''); ?>,
        email: <?php echo json_encode($datos_usuario->email ?? ''); ?>,
        telefono: <?php echo json_encode($datos_usuario->telefono ?? ''); ?>,
        avatar: <?php echo json_encode($datos_usuario->avatar ?? 'user_default.png'); ?>
    };
    //console.log('Datos del usuario cargados:', usuarioData);
</script>
<script src="checkout.js"></script>