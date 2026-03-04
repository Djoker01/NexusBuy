<?php
$base_path_url = ""; // Ya está en Views
$base_path = "../";
$pageTitle =  "Ofertas y Descuentos";
$pageName = "Ofertas";
$breadcrumb = "active";
$notificaciones = "desactive";
$checkout = "desactive";
$soporte = "desactive";
// $pageDescription = "Análisis detallado de toda la plataforma";
include_once 'Layouts/header.php';

// Verificar si el usuario está logueado para mostrar el contador del carrito
$sesion_activa = isset($_SESSION['id']);
?>
<title>Ofertas y Descuentos | NexusBuy</title>
<link rel="stylesheet" href="<?php echo $base_path; ?>util/css/cliente/ofertas.css">


<section class="ofertas-page">
    <!-- HERO / HEADER DE OFERTAS -->
    <div class="ofertas-hero">
        <div class="container">
            <h1 class="display-4">🔥 Ofertas Imperdibles</h1>
            <p class="lead" id="ofertas-stats">Cargando ofertas disponibles...</p>
            
            <!-- Contador para ofertas flash -->
            <div class="countdown-timer mb-4">
                <span class="countdown-badge">
                    <i class="fas fa-bolt"></i> OFERTA FLASH 
                    <span id="countdown">24:00:00</span>
                </span>
            </div>
            
            <!-- Filtros rápidos -->
            <div class="filtros-rapidos d-flex flex-wrap justify-content-center gap-2">
                <a href="#ofertas-flash" class="btn btn-light btn-sm mr-2">
                    <i class="fas fa-bolt"></i> Flash
                </a>
                <a href="#super-descuentos" class="btn btn-light btn-sm mr-2">
                    <i class="fas fa-fire"></i> 50%+ Off
                </a>
                <a href="#envio-gratis" class="btn btn-light btn-sm mr-2">
                    <i class="fas fa-shipping-fast"></i> Envío Gratis
                </a>
                <a href="#bundles" class="btn btn-light btn-sm mr-2">
                    <i class="fas fa-box"></i> Combos
                </a>
                <button class="btn btn-light btn-sm refresh-ofertas-btn" title="Actualizar ofertas">
                    <i class="fas fa-sync-alt"></i> Actualizar
                </button>
            </div>
        </div>
    </div>

    <div class="container">
        <!-- SECCIÓN 1: OFERTAS FLASH (24-48 horas) -->
        <div class="seccion-oferta" id="ofertas-flash">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-bolt text-warning mr-2"></i> Ofertas Flash</h2>
                <span class="badge badge-danger">Termina en: <span id="flash-countdown">23:45:12</span></span>
            </div>
            <p class="text-muted mb-4">Productos con descuentos exclusivos por tiempo limitado</p>
            <div class="row" id="flash-products">
                <!-- Productos flash cargados por JS -->
                <div class="col-12">
                    <div class="loading-state">
                        <div class="loading-spinner"></div>
                        <p class="loading-text">Cargando ofertas flash...</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- SECCIÓN 2: SUPER DESCUENTOS (+50% off) -->
        <div class="seccion-oferta" id="super-descuentos">
            <h2 class="mb-4"><i class="fas fa-fire text-danger mr-2"></i> Super Descuentos (50%+ OFF)</h2>
            <p class="text-muted mb-4">Los productos con mayores descuentos</p>
            <div class="row" id="super-discount-products">
                <!-- Productos con más del 50% descuento -->
                <div class="col-12">
                    <div class="loading-state">
                        <div class="loading-spinner"></div>
                        <p class="loading-text">Cargando super descuentos...</p>
                    </div>
                </div>
            </div>
        </div>

         <!-- SECCIÓN 3: OFERTAS POR CATEGORÍA -->
<div class="seccion-oferta" id="categorias-oferta">
    <h2 class="mb-4"><i class="fas fa-th-large text-primary mr-2"></i> Ofertas por Categoría</h2>
    <p class="text-muted mb-4">Descubre ofertas en tus categorías favoritas</p>
    
    <!-- Contenedor para categorías - ID CORREGIDO -->
    <div class="row" id="categorias-container">
        <!-- Estado de carga inicial -->
        <div class="col-12">
            <div class="loading-state text-center py-5">
                <div class="spinner-border text-primary mb-3" role="status">
                    <span class="sr-only">Cargando...</span>
                </div>
                <p class="loading-text text-muted">Cargando categorías con ofertas...</p>
            </div>
        </div>
        <!-- Las tarjetas de categorías se insertarán aquí dinámicamente -->
    </div>
    
    <!-- Mensaje cuando no hay categorías (oculto por defecto) -->
    <div id="no-categorias-message" class="d-none text-center py-5">
        <i class="fas fa-tags fa-3x text-muted mb-3"></i>
        <h4 class="text-muted">No hay categorías en oferta</h4>
        <p class="text-muted">Pronto tendremos ofertas especiales por categoría</p>
    </div>
</div>

        <!-- SECCIÓN 4: MÁS VENDIDOS EN OFERTA -->
        <div class="seccion-oferta" id="mas-vendidos">
            <h2 class="mb-4"><i class="fas fa-chart-line text-success mr-2"></i> Los Más Vendidos en Oferta</h2>
            <p class="text-muted mb-4">Los productos más populares ahora con descuento</p>
            <div class="row" id="best-sellers-discount">
                <!-- Productos más vendidos que tienen descuento -->
                <div class="col-12">
                    <div class="loading-state">
                        <div class="loading-spinner"></div>
                        <p class="loading-text">Cargando más vendidos...</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- SECCIÓN 5: BUNDLES / PAQUETES EN OFERTA -->
        <div class="seccion-oferta" id="bundles">
            <h2 class="mb-4"><i class="fas fa-box text-info mr-2"></i> Combos y Bundles</h2>
            <p class="text-muted mb-4">Paquetes especiales con descuentos exclusivos</p>
            <div class="row" id="bundles-container">
                <!-- Bundles cargados dinámicamente -->
                <div class="col-12">
                    <div class="loading-state">
                        <div class="loading-spinner"></div>
                        <p class="loading-text">Cargando combos especiales...</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- SECCIÓN 6: OFERTAS CON ENVÍO GRATIS -->
        <div class="seccion-oferta" id="envio-gratis">
            <h2 class="mb-4"><i class="fas fa-shipping-fast text-primary mr-2"></i> Ofertas + Envío Gratis</h2>
            <p class="text-muted mb-4">Productos con descuento y envío gratis incluido</p>
            <div class="row" id="free-shipping-products">
                <!-- Productos con descuento + envío gratis -->
                <div class="col-12">
                    <div class="loading-state">
                        <div class="loading-spinner"></div>
                        <p class="loading-text">Cargando productos con envío gratis...</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- SECCIÓN 7: DESTACADOS EN OFERTA (se crea dinámicamente) -->
        <!-- Esta sección se creará automáticamente en JavaScript -->

        <!-- SECCIÓN 8: BANNER PROMOCIONAL -->
        <div class="promo-banner bg-primary text-white p-4 rounded-lg mb-5 shadow">
    <div class="row align-items-center">
        <div class="col-lg-7 mb-3 mb-lg-0">
            <h3 class="font-weight-bold mb-2">
                <i class="fas fa-bell mr-2"></i> ¡No te pierdas las mejores ofertas!
            </h3>
            <p class="mb-0">Suscríbete y recibe ofertas exclusivas directamente en tu correo.</p>
        </div>
        <div class="col-lg-5">
            <div class="input-group mb-2">
                <div class="input-group-prepend">
                    <span class="input-group-text bg-white border-0">
                        <i class="fas fa-user text-primary"></i>
                    </span>
                </div>
                <input type="text" class="form-control border-0" placeholder="Tu nombre (opcional)">
            </div>
            <div class="input-group mb-2">
                <div class="input-group-prepend">
                    <span class="input-group-text bg-white border-0">
                        <i class="fas fa-envelope text-primary"></i>
                    </span>
                </div>
                <input type="email" class="form-control border-0" placeholder="Tu correo electrónico" required>
            </div>
            <div class="input-group mb-3">
                <div class="input-group-prepend">
                    <span class="input-group-text bg-white border-0">
                        <i class="fas fa-clock text-primary"></i>
                    </span>
                </div>
                <select class="form-control border-0" name="frecuencia">
                    <option value="diaria">Ofertas diarias</option>
                    <option value="semanal">Ofertas semanales</option>
                    <option value="mensual">Ofertas mensuales</option>
                </select>
            </div>
            <button class="btn btn-warning btn-block btn-lg font-weight-bold">
                <i class="fas fa-paper-plane mr-2"></i> Suscribirse
            </button>
            <small class="form-text text-white-50 mt-2">
                <i class="fas fa-lock mr-1"></i> Respetamos tu privacidad. No compartiremos tu email.</br>
                Puedes cancelar tu suscripción en cualquier momento.
            </small>
        </div>
    </div>
</div>

        <!-- SECCIÓN 9: BOTÓN FLOTANTE DE REFRESH -->
        <div class="floating-refresh">
            <button class="btn btn-primary btn-refresh-circle" id="btn-refresh-floating" title="Actualizar ofertas">
                <i class="fas fa-sync-alt"></i>
            </button>
        </div>
    </div>
</section>

<!-- MODAL DE SUSCRIPCIÓN EXITOSA (oculto por defecto) -->
<div class="modal fade" id="modalSuscripcionExitosa" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="fas fa-check-circle mr-2"></i> ¡Suscripción Exitosa!
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center">
                <i class="fas fa-envelope-open-text fa-4x text-success mb-3"></i>
                <h4>¡Gracias por suscribirte!</h4>
                <p class="mb-0">Te enviaremos las mejores ofertas directamente a tu correo electrónico.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success" data-dismiss="modal">Entendido</button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL DE INICIO DE SESIÓN REQUERIDO -->
<div class="modal fade" id="modalLoginRequerido" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title">
                    <i class="fas fa-exclamation-triangle mr-2"></i> Inicio de Sesión Requerido
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center">
                <i class="fas fa-shopping-cart fa-4x text-warning mb-3"></i>
                <h4>Debes iniciar sesión</h4>
                <p class="mb-4">Para agregar productos al carrito, necesitas tener una sesión activa.</p>
                <div class="d-flex justify-content-center gap-3">
                    <a href="login.php?redirect=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>" 
                       class="btn btn-primary">
                        <i class="fas fa-sign-in-alt mr-2"></i> Iniciar Sesión
                    </a>
                    <a href="registro.php?redirect=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>" 
                       class="btn btn-outline-primary">
                        <i class="fas fa-user-plus mr-2"></i> Registrarse
                    </a>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
            </div>
        </div>
    </div>
</div>



<?php
include_once 'Layouts/footer.php';
?>
<script src="ofertas.js"></script>
<script>
// Script adicional para la página de ofertas
document.addEventListener('DOMContentLoaded', function() {
    // Botón de actualizar flotante
    const btnRefreshFloating = document.getElementById('btn-refresh-floating');
    if (btnRefreshFloating) {
        btnRefreshFloating.addEventListener('click', function() {
            refreshOfertas();
            // Animación de rotación
            this.querySelector('i').style.transform = 'rotate(360deg)';
            setTimeout(() => {
                this.querySelector('i').style.transform = 'rotate(0deg)';
            }, 500);
        });
    }
    
    // Botón de actualizar en el header
    const btnRefreshHeader = document.querySelector('.refresh-ofertas-btn');
    if (btnRefreshHeader) {
        btnRefreshHeader.addEventListener('click', refreshOfertas);
    }
    
    // Configurar frecuencia de suscripción
    // const emailInput = document.getElementById('email-suscripcion');
    // const btnSuscribir = document.getElementById('btn-suscribir');
    // const radiosFrecuencia = document.querySelectorAll('input[name="frecuencia"]');
    
    // if (btnSuscribir) {
    //     btnSuscribir.addEventListener('click', function() {
    //         const email = emailInput.value.trim();
    //         let frecuencia = 'semanal';
            
    //         // Obtener frecuencia seleccionada
    //         radiosFrecuencia.forEach(radio => {
    //             if (radio.checked) {
    //                 frecuencia = radio.value;
    //             }
    //         });
            
    //         if (!isValidEmail(email)) {
    //             showNotification('error', 'Por favor ingresa un correo electrónico válido');
    //             emailInput.focus();
    //             return;
    //         }
            
    //         // Mostrar loader en el botón
    //         const originalText = this.innerHTML;
    //         this.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Suscribiendo...';
    //         this.disabled = true;
            
    //         // Hacer petición al servidor
    //         const formData = new FormData();
    //         formData.append('funcion', 'suscribir_email_ofertas');
    //         formData.append('email', email);
    //         formData.append('frecuencia', frecuencia);
            
    //         fetch('../Controllers/OfertasController.php', {
    //             method: 'POST',
    //             body: formData
    //         })
    //         .then(response => response.json())
    //         .then(data => {
    //             if (data.success) {
    //                 // Mostrar modal de éxito
    //                 $('#modalSuscripcionExitosa').modal('show');
    //                 emailInput.value = '';
    //             } else {
    //                 showNotification('error', data.message || 'Error al suscribirse');
    //             }
    //         })
    //         .catch(error => {
    //             showNotification('error', 'Error en la conexión');
    //         })
    //         .finally(() => {
    //             // Restaurar botón
    //             this.innerHTML = originalText;
    //             this.disabled = false;
    //         });
    //     });
    // }
    
    // Permitir suscribirse con Enter
    // if (emailInput) {
    //     emailInput.addEventListener('keypress', function(e) {
    //         if (e.key === 'Enter' && btnSuscribir) {
    //             btnSuscribir.click();
    //         }
    //     });
    // }
    
    // Verificar sesión periódicamente (cada 5 minutos)
    setInterval(checkUserSession, 5 * 60 * 1000);
    
    // Mostrar mensaje de bienvenida si es la primera visita hoy
    const hoy = new Date().toDateString();
    const ultimaVisita = localStorage.getItem('ultimaVisitaOfertas');
    
    if (!ultimaVisita || ultimaVisita !== hoy) {
        // Es la primera visita hoy
        showNotification('info', '¡Bienvenido a nuestras ofertas del día! 🎉');
        localStorage.setItem('ultimaVisitaOfertas', hoy);
    }
    
    // Función para validar email
    // function isValidEmail(email) {
    //     const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    //     return re.test(email);
    // }
    
    // Función para mostrar notificación
    function showNotification(type, message) {
        const toast = document.createElement('div');
        toast.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
        toast.style.cssText = 'position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        toast.innerHTML = `
            ${message}
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        `;
        
        document.body.appendChild(toast);
        
        // Auto-eliminar después de 3 segundos
        setTimeout(() => {
            toast.remove();
        }, 3000);
        
        // Cerrar al hacer clic
        toast.querySelector('.close').addEventListener('click', () => {
            toast.remove();
        });
    }
});
</script>