<?php
session_start();

$base_path_url = ""; // Ya está en la raíz
$base_path = "../";
$pageTitle = "Pasarela de Pagos";
$pageName = "Pasarela de Pagos";
$breadcrumb = "desactive";
$checkout = "active";
$notificaciones = "desactive";
$soporte = "desactive";
// $pageDescription = "Análisis detallado de toda la plataforma";

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
include_once 'Layouts/header.php';

// Incluir el modelo
include_once '../Models/ConfiguracionSitio.php';
include_once '../Util/Config/config.php'; // Para CODE y KEY

$configuracion = new ConfiguracionSitio();

// Obtener el filtro de la URL
$filtro = isset($_GET['filtro']) ? $_GET['filtro'] : 'centro_ayuda';

// Obtener datos de la base de datos
$datosContacto = $configuracion->obtenerPorCategoria('contacto');
$datosGeneral = $configuracion->obtenerPorCategoria('general');
$datosFinanzas = $configuracion->obtenerPorCategoria('finanzas');
$datosLegal = $configuracion->obtenerPorCategoria('legal');

// ======= NUEVO: OBTENER CONFIGURACIÓN DE TRANSFERMÓVIL =======
$datosPagos = $configuracion->obtenerPorCategoria('pagos');

// Crear array específico para Transfermóvil
$transfermovilConfig = [];

// Buscar específicamente las claves de Transfermóvil
if (!empty($datosPagos)) {
    foreach ($datosPagos as $key => $configItem) {
        if (strpos($key, 'transfermovil_') === 0) {
            $transfermovilConfig[$key] = $configItem['valor'] ?? '';
        }
    }
}

// También puedes obtener valores específicos individualmente
$numeroTarjetaTM = $configuracion->obtener('transfermovil_numero_tarjeta');
$nombreTitularTM = $configuracion->obtener('transfermovil_nombre_titular');
$bancoTM = $configuracion->obtener('transfermovil_banco');
$activoTM = $configuracion->obtener('transfermovil_activo');

// Verificar si Transfermóvil está activo
$transfermovilActivo = ($activoTM === '1' || $activoTM === true);
?>



<title>Pasarela de Pago | NexusBuy</title>
<link rel="stylesheet" href="../Util/Css/cliente/checkout.css">



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
                            <div class="card-header-modern">
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
                            <div class="card-header-modern">
                                <h3 class="card-title">
                                    <i class="fas fa-credit-card mr-2"></i>Método de Pago
                                </h3>
                            </div>
                            <div class="card-body">
                                <!-- Métodos de pago guardados -->
                                <!-- <div id="metodos-guardados" class="mb-4">
                                    <h5 class="mb-3" style="color: black">Métodos guardados</h5>
                                    <div id="lista-metodos-pago">
                                         Se llenará dinámicamente 
                                    </div>
                                </div> -->

                                <!-- Nuevo método de pago -->
                                <div class="mb-4">
                                    <h5 class="mb-3" style="color: black">Agregar nuevo método de pago</h5>

                                    <!-- <div class="payment-method" onclick="seleccionarMetodoPago('tarjeta')">
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
                                    </div> -->

                                    <!-- <div class="payment-method" onclick="seleccionarMetodoPago('transferencia')">
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
                                    </div> -->
                                    <!-- Método de Pago Transfermóvil -->
                                    <div class="payment-method" onclick="seleccionarMetodoPago('transfermovil')">
                                        <input type="radio" name="metodo_pago" id="metodo_transfermovil" value="transfermovil">
                                        <label for="metodo_transfermovil" class="font-weight-bold">
                                            <i class="fas fa-mobile-alt mr-2"></i>Transfermóvil
                                        </label>
                                        <div id="form-transfermovil" class="mt-3" style="display: none;">
                                            <div class="alert alert-info">
                                                <p class="mb-2"><strong>Instrucciones:</strong></p>
                                                <ol class="mb-0 pl-3">
                                                    <li>Al confirmar la compra, se generará un <strong>número de orden</strong> para tu pedido.</li>
                                                    <li>Se realizara la conversión automática a <strong>CUP</strong>.</li>
                                                    <li>Debes realizar la transferencia a nuestro <strong>número de tarjeta</strong>.</li>
                                                    <li>Una vez verifiquemos el pago manualmente, procesaremos tu pedido.</li>
                                                </ol>
                                            </div>
                                            <!-- Aquí se inyectarán dinámicamente el número de tarjeta y la referencia -->
                                            <div id="instrucciones-dinamicas-transfermovil"></div>
                                        </div>
                                    </div>
                                    <!-- Método de Pago Efectivo -->
                                    <div class="payment-method" onclick="seleccionarMetodoPago('efectivo')">
    <input type="radio" name="metodo_pago" id="metodo_efectivo" value="efectivo">
    <label for="metodo_efectivo" class="font-weight-bold">
        <i class="fas fa-money-bill-wave mr-2"></i>Efectivo / Contraentrega
    </label>
    <div id="form-efectivo" class="mt-3" style="display: none;">
        <div class="alert alert-success">
            <p class="mb-2"><strong>💵 Pago en efectivo:</strong></p>
            <ul class="mb-0 pl-3">
                <li>Podrás pagar en efectivo al recibir tu pedido.</li>
                <li>El repartidor te entregará los productos y recibirá el pago.</li>
                <li><strong>Monto a pagar:</strong> <span id="monto-efectivo">$ 0.00</span></li>
                <li class="text-warning"><i class="fas fa-exclamation-triangle mr-1"></i> Asegúrate de tener el cambio exacto si es necesario.</li>
            </ul>
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
                    <input type="hidden" name="metodo_pago" id="input-metodo-pago" value="efectivo">

                    <!-- Paso 3: Revisar y Pagar -->
                    <div class="checkout-step" id="step-content-3" style="display: none;">
                        <div class="card">
                            <div class="card-header-modern">
                                <h3 class="card-title">
                                    <i class="fas fa-clipboard-check mr-2"></i>Revisar tu Pedido
                                </h3>
                            </div>
                            <div class="card-body">
                                <!-- Resumen de productos -->
                                <div class="mb-4">
                                    <h5 class="mb-3" style="color: black">Productos en tu pedido</h5>
                                    <div id="resumen-productos">
                                        <!-- Se llenará dinámicamente -->
                                    </div>
                                </div>

                                <!-- Resumen de envío -->
                                <div class="mb-4">
                                    <h5 class="mb-3" style="color: black">Información de Envío</h5>
                                    <div id="resumen-envio" class="bg-light p-3 rounded">
                                        <!-- Se llenará dinámicamente -->
                                    </div>
                                </div>

                                <!-- Resumen de pago -->
                                <div class="mb-4">
                                    <h5 class="mb-3" style="color: black">Método de Pago</h5>
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
                                        Acepto las <a href="soporte.php?filtro=envio_entregas"> Políticas de Envio y Entrega</a> y la <a href="soporte.php?filtro=privacidad">Política de Privacidad</a>.
                                    </label>
                                </div>

                                <div class="d-flex justify-content-between">
                                    <button type="button" class="btn btn-secondary" onclick="siguientePaso(2)">
                                        <i class="fas fa-arrow-left mr-2"></i>Regresar
                                    </button>
                                    <button type="button" class="btn btn-success btn-lg" id="btn-procesar-pago">
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
                    <div class="card-header-modern">
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
                                <span id="resumen-lateral-descuento">$ 0.00</span>
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

<?php include_once 'Layouts/footer.php'; ?>

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

    // Configuración para Transfermóvil (se llena desde PHP)
    window.configTransfermovil = {
        activo: <?php echo $transfermovilActivo ? 'true' : 'false'; ?>,
        numero_tarjeta: '<?php echo addslashes($numeroTarjetaTM); ?>',
        nombre_titular: '<?php echo addslashes($nombreTitularTM); ?>',
        banco: '<?php echo addslashes($bancoTM); ?>',
        esta_configurado: <?php echo (!empty($numeroTarjetaTM) && !empty($nombreTitularTM)) ? 'true' : 'false'; ?>
    };

    // Validar en tiempo real si Transfermóvil está disponible
    if (window.configTransfermovil.activo && window.configTransfermovil.esta_configurado) {
        // console.log('✅ Transfermóvil configurado correctamente');
    } else {
        console.warn('⚠️ Transfermóvil no está configurado o desactivado');

        // Opcional: Ocultar la opción de Transfermóvil si no está configurada
        $(document).ready(function() {
            $('#metodo_transfermovil').closest('.payment-method').hide();
        });
    }
    $(document).on('change', 'input[name="metodo_pago"]', function() {
        $('#input-metodo-pago').val($(this).val());
    });

    // Variable para controlar si es una nueva orden
    window.esNuevaOrden = true; // Esta variable se establecerá dinámicamente
    
    // Función para desbloquear el textarea
    window.desbloquearTextareaParaNuevaOrden = function() {
        // console.log('🔓 Desbloqueando textarea para nueva orden...');
        
        // Limpiar localStorage
        localStorage.removeItem('textarea_transfermovil_bloqueado');
        localStorage.removeItem('textarea_transfermovil_texto');
        
        // Restablecer variable global
        window.textareaUsado = false;
        
        // Desbloquear textarea si existe en el DOM
        const $textarea = $('#textoTransferencia');
        if ($textarea.length) {
            $textarea.prop('disabled', false);
            $textarea.prop('readonly', false);
            $textarea.val('');
            $textarea.css({
                'background-color': '',
                'border-color': '',
                'cursor': '',
                'opacity': '',
                'color': ''
            });
            
            // Remover indicadores de bloqueo
            $('[id^="bloqueado-"]').remove();
            $('[id^="bloqueado-permanentemente-"]').remove();
            
            // Ocultar resultado de parseo
            $('#resultadoParseo').hide();
            
            // console.log('✅ Textarea desbloqueado para nueva orden');
        }
    };
</script>
<script src="checkout.js"></script>