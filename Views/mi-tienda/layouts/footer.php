<?php
// /Views/mi-tienda/layouts/footer.php
// Este footer se incluye al final de cada vista
?>

        </div> <!-- Cierre de .main -->
    </div> <!-- Cierre de .app -->

    <!-- Footer -->
    <footer class="nexus-footer">
        <div class="footer-container">
            <!-- Primera fila: Enlaces rápidos y contacto -->
            <div class="footer-grid">
                <!-- Columna 1: Logo y descripción -->
                <div class="footer-col">
                    <div class="footer-logo">
                        <i class="fas fa-store"></i>
                        <span>NexusBuy</span>
                    </div>
                    <p class="footer-description">
                        La plataforma de e-commerce más confiable para vendedores cubanos. 
                        Conectamos tu negocio con miles de clientes.
                    </p>
                    <div class="footer-social">
                        <a href="#" class="social-link" title="Facebook"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="social-link" title="Instagram"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="social-link" title="Twitter"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="social-link" title="Telegram"><i class="fab fa-telegram-plane"></i></a>
                    </div>
                </div>

                <!-- Columna 2: Enlaces rápidos -->
                <div class="footer-col">
                    <h4 class="footer-title">Enlaces Rápidos</h4>
                    <ul class="footer-links">
                        <li><a href="<?php echo $base_path; ?>Views/mi-tienda/dashboard.php"><i class="fas fa-chevron-right"></i> Dashboard</a></li>
                        <li><a href="<?php echo $base_path; ?>Views/mi-tienda/productos/index.php"><i class="fas fa-chevron-right"></i> Mis Productos</a></li>
                        <li><a href="<?php echo $base_path; ?>Views/mi-tienda/pedidos/index.php"><i class="fas fa-chevron-right"></i> Pedidos</a></li>
                        <li><a href="<?php echo $base_path; ?>Views/mi-tienda/finanzas/index.php"><i class="fas fa-chevron-right"></i> Finanzas</a></li>
                        <li><a href="<?php echo $base_path; ?>Views/mi-tienda/configuracion/index.php"><i class="fas fa-chevron-right"></i> Configuración</a></li>
                    </ul>
                </div>

                <!-- Columna 3: Soporte -->
                <div class="footer-col">
                    <h4 class="footer-title">Soporte</h4>
                    <ul class="footer-links">
                        <li><a href="#"><i class="fas fa-chevron-right"></i> Centro de Ayuda</a></li>
                        <li><a href="#"><i class="fas fa-chevron-right"></i> Guías para Vendedores</a></li>
                        <li><a href="#"><i class="fas fa-chevron-right"></i> Términos y Condiciones</a></li>
                        <li><a href="#"><i class="fas fa-chevron-right"></i> Política de Privacidad</a></li>
                        <li><a href="#"><i class="fas fa-chevron-right"></i> Reportar un Problema</a></li>
                    </ul>
                </div>

                <!-- Columna 4: Contacto -->
                <div class="footer-col">
                    <h4 class="footer-title">Contacto</h4>
                    <ul class="footer-contact">
                        <li>
                            <i class="fas fa-envelope"></i>
                            <span>soporte@nexusbuy.com</span>
                        </li>
                        <li>
                            <i class="fas fa-phone-alt"></i>
                            <span>+53 5 123 4567</span>
                        </li>
                        <li>
                            <i class="fas fa-clock"></i>
                            <span>Lun - Vie: 9:00 AM - 6:00 PM</span>
                        </li>
                        <li>
                            <i class="fas fa-map-marker-alt"></i>
                            <span>La Habana, Cuba</span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Segunda fila: App y newsletter -->
            <div class="footer-middle">
                <div class="footer-newsletter">
                    <h4>¿Novedades?</h4>
                    <p>Suscríbete para recibir actualizaciones y consejos para tu tienda</p>
                    <form class="newsletter-form" id="newsletterForm">
                        <input type="email" placeholder="Tu correo electrónico" required>
                        <button type="submit" class="newsletter-btn">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </form>
                </div>
                <div class="footer-app">
                    <h4>Descarga nuestra app</h4>
                    <div class="app-buttons">
                        <a href="#" class="app-btn">
                            <i class="fab fa-google-play"></i>
                            <span>Google Play</span>
                        </a>
                        <a href="#" class="app-btn">
                            <i class="fab fa-apple"></i>
                            <span>App Store</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Tercera fila: Copyright y métodos de pago -->
            <div class="footer-bottom">
                <div class="footer-copyright">
                    &copy; <?php echo date('Y'); ?> NexusBuy. Todos los derechos reservados. 
                    <span class="footer-version">v1.0.0</span>
                </div>
                <div class="footer-payments">
                    <span class="payment-text">Aceptamos:</span>
                    <i class="fas fa-credit-card" title="Tarjetas de crédito"></i>
                    <i class="fas fa-mobile-alt" title="Transferencia móvil"></i>
                    <i class="fas fa-university" title="Transferencia bancaria"></i>
                    <i class="fas fa-hand-holding-usd" title="Efectivo"></i>
                </div>
            </div>
        </div>

        <!-- Botón flotante para volver arriba -->
        <button id="backToTop" class="back-to-top" title="Volver arriba">
            <i class="fas fa-arrow-up"></i>
        </button>
    </footer>

    <!-- Scripts comunes -->
    
    <script src="<?php echo $base_path; ?>../../Util/Js/sweetalert2.min.js"></script>
    <script src="<?php echo $base_path; ?>../../Util/Js/jquery.min.js"></script>
    <script src="<?php echo $base_path; ?>../../Util/Js/chart.js"></script>
    <script src="<?php echo $base_path; ?>funcion_general.js"></script>
    <script>
        // Pasar la variable base_path a JavaScript
        var base_path = "<?php echo $base_path; ?>";
    </script>
    

    <!-- Script para el botón de volver arriba -->
    <script>
        $(document).ready(function() {
            // Botón volver arriba
            var $backToTop = $('#backToTop');
            
            $(window).scroll(function() {
                if ($(this).scrollTop() > 300) {
                    $backToTop.fadeIn();
                } else {
                    $backToTop.fadeOut();
                }
            });
            
            $backToTop.click(function(e) {
                e.preventDefault();
                $('html, body').animate({scrollTop: 0}, 300);
            });

            // Newsletter form (ejemplo)
            $('#newsletterForm').submit(function(e) {
                e.preventDefault();
                var email = $(this).find('input[type="email"]').val();
                Swal.fire({
                    icon: 'success',
                    title: '¡Gracias por suscribirte!',
                    text: 'Recibirás nuestras novedades en: ' + email,
                    timer: 3000,
                    showConfirmButton: false
                });
                $(this).find('input[type="email"]').val('');
            });
        });
    </script>
</body>
</html>