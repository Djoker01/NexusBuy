<?php
$base_path_url = "Views/"; // Ya está en la raíz
$base_path = "";
$pageTitle = "NexusBuy - Tu Tienda Online de Confianza";
$pageName = "Home";
$breadcrumb = "active";
$checkout = "desactive";
$notificaciones = "desactive";
$soporte = "desactive";
// $pageDescription = "Análisis detallado de toda la plataforma";

include_once 'views/layouts/header.php';
?>
<link rel="stylesheet" href="Util/css/cliente/index.css">

<section class="content">
    <!-- Promotional Slider -->
    <div class="promo-slider card" id="promoSlider">
        <div class="slide active" style="background-image: linear-gradient(rgba(0,0,0,0.3), rgba(0,0,0,0.3)), url('Util/Img/Banners/Banner 1.jpg')">
            <div class="slide-content fade-in">
                <h2>Ofertas</h2>
                <p>Descuento en productos seleccionados. Aprovecha estas ofertas por tiempo limitado.</p>
                <!-- <button class="btn btn-primary">Ver Ofertas</button> -->
            </div>
        </div>
        <div class="slide" style="background-image: linear-gradient(rgba(0,0,0,0.3), rgba(0,0,0,0.3)), url('Util/Img/Banners/Banner 2.jpg')">
            <div class="slide-content">
                <h2>Envío Gratis</h2>
                <p>En todas tus compras mayores a $50. Disfruta de envío rápido y seguro a todo el país.</p>
                <!-- <button class="btn btn-primary">Comprar Ahora</button> -->
            </div>
        </div>
        <div class="slide" style="background-image: linear-gradient(rgba(0,0,0,0.3), rgba(0,0,0,0.3)), url('Util/Img/Banners/Banner 3.jpg')">
            <div class="slide-content">
                <h2>Nuevos Productos</h2>
                <p>Descubre nuestra última colección de productos exclusivos. Tecnología innovadora al mejor precio.</p>
                <!-- <button class="btn btn-primary">Explorar</button> -->
            </div>
        </div>

        <div class="slider-nav prev" id="prevSlide">
            <i class="fas fa-chevron-left"></i>
        </div>
        <div class="slider-nav next" id="nextSlide">
            <i class="fas fa-chevron-right"></i>
        </div>

        <div class="slider-controls" id="sliderControls">
            <div class="slider-dot active"></div>
            <div class="slider-dot"></div>
            <div class="slider-dot"></div>
        </div>
    </div>

    <!-- Flash Sales Section -->
    <section class="mt-5">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="text-danger">
                <i class="fas fa-bolt"></i> Ofertas Flash
            </h3>
            <a href="Views/ofertas.php" class="btn btn-sm btn-outline-danger">
                Ver todas
            </a>
        </div>
        
        <div class="row" id="ofertas-flash-container">
            <!-- Las ofertas se cargan aquí -->
            <div class="col-12 text-center py-3">
                <div class="spinner-border spinner-border-sm text-danger" role="status">
                    <span class="visually-hidden">Cargando...</span>
                </div>
                <small class="text-muted ms-2">Cargando ofertas...</small>
            </div>
        </div>
    </div>
</section>
    <!-- Featured Products -->
    <div class="featured-products">
        <div class="section-title">
            <h2>Productos Destacados</h2>
            <p>Descubre nuestros productos más populares y mejor valorados</p>
        </div>

        <div class="card">
            <div class="card-header">
                <i class="fas fa-star mr-2"></i>Productos Promocionados
            </div>
            <div class="card-body">
                <div id="featured-products" class="row">
                    <!-- Los productos destacados se cargarán aquí -->
                    <div class="col-sm-2">
                        <div class="card product-card h-100">
                            <div class="featured-badge">Destacado</div>
                            <div class="card-body">
                                <div class="product-image">
                                    <img src="Util/Img/Producto/producto_default.png" alt="Producto destacado">
                                </div>
                                <span class="product-brand">Marca Premium</span>
                                <a class="product-title" href="#">Auriculares Inalámbricos con Cancelación de Ruido</a>
                                <span class="badge-free-shipping">Envío gratis</span>
                                <div class="product-rating">
                                    <i class="fas fa-star text-warning"></i>
                                    <i class="fas fa-star text-warning"></i>
                                    <i class="fas fa-star text-warning"></i>
                                    <i class="fas fa-star text-warning"></i>
                                    <i class="fas fa-star-half-alt text-warning"></i>
                                </div>
                                <div class="product-price">
                                    <span class="original-price">$ 199</span>
                                    <span class="discount-percent">-15%</span>
                                    <div class="current-price">$ 169</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Featured Categories -->
    <div class="featured-categories mb-5">
        <div class="section-title">
            <h2>Explorar por Categorías</h2>
            <p>Descubre nuestros productos organizados por categorías</p>
        </div>
        <div id="feature_categories" class="categories-horizontal-container">
            <!-- Aquí se cargan las categorías dinamicamente -->
            <div class="col-12 text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="sr-only">Cargando categorías...</span>
                </div>
                <p class="text-muted mt-2">Cargando categorías...</p>
            </div>
        </div>
    </div>

    <!-- Featured Brands -->
    <div class="featured-brands mb-5">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-crown text-warning mr-2"></i>
                    Marcas Destacadas
                </h3>
            </div>
            <div class="card-body">
                <div id="marca_destacada" class="brands-slider">
                    <!-- Aquí se cargan las marcas dinamicamente -->
                    <div class="col-12 text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">Cargando marcas...</span>
                        </div>
                        <p class="text-muted mt-2">Cargando marcas...</p>
                    </div>
                </div>
            </div>
        </div>
        <!-- Contenedor para la paginación (se creará dinámicamente) -->
        <div id="paginacion-marcas"></div>
    </div>

    <!-- Customer Testimonials -->
    <!-- <div class="testimonials-section mb-5">
        <div class="section-title footer-link en-desarrollo">
            <h2>Lo que dicen nuestros clientes</h2>
            <p>Experiencias reales de compradores satisfechos</p>
        </div>
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="testimonial-card">
                    <div class="testimonial-content">
                        <div class="rating mb-3">
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                        </div>
                        <p class="testimonial-text">
                            "Excelente servicio al cliente y productos de alta calidad.
                            El envío fue rápido y el producto llegó en perfectas condiciones."
                        </p>
                        <div class="testimonial-author">
                            <img src="Util/Img/Users/user4-128x128.jpg" alt="María González">
                            <div class="author-info">
                                <strong>María González</strong>
                                <span>Cliente desde 2023</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="testimonial-card">
                    <div class="testimonial-content">
                        <div class="rating mb-3">
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                        </div>
                        <p class="testimonial-text">
                            "Increíble experiencia de compra. La página es muy intuitiva
                            y encontré exactamente lo que buscaba a un precio excelente."
                        </p>
                        <div class="testimonial-author">
                            <img src="Util/Img/Users/user2-160x160.jpg" alt="Carlos Rodríguez">
                            <div class="author-info">
                                <strong>Carlos Rodríguez</strong>
                                <span>Cliente desde 2022</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="testimonial-card">
                    <div class="testimonial-content">
                        <div class="rating mb-3">
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star-half-alt text-warning"></i>
                        </div>
                        <p class="testimonial-text">
                            "Muy contento con mi compra. El proceso fue sencillo y el
                            soporte respondió rápidamente a mis consultas. ¡Recomendado!"
                        </p>
                        <div class="testimonial-author">
                            <img src="Util/Img/Users/user7-128x128.jpg" alt="Ana Martínez">
                            <div class="author-info">
                                <strong>Ana Martínez</strong>
                                <span>Cliente desde 2024</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div> -->

    <!-- Blog Section -->
    <!-- <div class="blog-section mb-5">
        <div class="section-title footer-link en-desarrollo">
            <h2>Últimas Noticias</h2>
            <p>Mantente informado sobre las últimas tendencias y ofertas</p>
        </div>
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="blog-card">
                    <div class="blog-image">
                        <img src="Util/Img/Blog Post/las mejores ofertas de verano 2024.jpg" alt="Blog Post">
                        <div class="blog-date">
                            <span class="day">15</span>
                            <span class="month">Jun</span>
                        </div>
                    </div>
                    <div class="blog-content">
                        <h5 class="blog-title">Las mejores ofertas de verano 2024</h5>
                        <p class="blog-excerpt">
                            Descubre cómo aprovechar al máximo las ofertas de temporada...
                        </p>
                        <a href="#" class="read-more">Leer más <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="blog-card">
                    <div class="blog-image">
                        <img src="Util/Img/Blog Post/nuevas marcas disponibles.jpg" alt="Blog Post">
                        <div class="blog-date">
                            <span class="day">10</span>
                            <span class="month">Jun</span>
                        </div>
                    </div>
                    <div class="blog-content">
                        <h5 class="blog-title">Nuevas marcas disponibles</h5>
                        <p class="blog-excerpt">
                            Hemos agregado más de 20 nuevas marcas a nuestro catálogo...
                        </p>
                        <a href="#" class="read-more">Leer más <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="blog-card">
                    <div class="blog-image">
                        <img src="Util/Img/Blog Post/guía de compras inteligentes.jpg" alt="Blog Post">
                        <div class="blog-date">
                            <span class="day">05</span>
                            <span class="month">Jun</span>
                        </div>
                    </div>
                    <div class="blog-content">
                        <h5 class="blog-title">Guía de compras inteligentes</h5>
                        <p class="blog-excerpt">
                            Aprende a tomar las mejores decisiones de compra online...
                        </p>
                        <a href="#" class="read-more">Leer más <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </div> -->

    <!-- Benefits Section -->
    <div class="benefits-section mb-5">
        <div class="row">
            <div class="col-md-3 col-6 text-center mb-4">
                <div class="benefit-item">
                    <div class="benefit-icon">
                        <i class="fas fa-shipping-fast"></i>
                    </div>
                    <h5 class="text-title">Envío Gratis</h5>
                    <p class="text-muted">En todas tus compras</p>
                </div>
            </div>
            <div class="col-md-3 col-6 text-center mb-4">
                <div class="benefit-item">
                    <div class="benefit-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h5 class="text-title">Pago Seguro</h5>
                    <p class="text-muted">Transacciones 100% protegidas</p>
                </div>
            </div>
            <div class="col-md-3 col-6 text-center mb-4">
                <div class="benefit-item">
                    <div class="benefit-icon">
                        <i class="fas fa-undo-alt"></i>
                    </div>
                    <h5 class="text-title">Devoluciones</h5>
                    <p class="text-muted">30 días para cambiar de opinión</p>
                </div>
            </div>
            <div class="col-md-3 col-6 text-center mb-4">
                <div class="benefit-item">
                    <div class="benefit-icon">
                        <i class="fas fa-headset"></i>
                    </div>
                    <h5 class="text-title">Soporte 24/7</h5>
                    <p class="text-muted">Estamos aquí para ayudarte</p>
                </div>
            </div>
        </div>
    </div>

    <?php
    include_once 'Views/layouts/footer.php';
    ?>
    <script>
        // // ==============================================
// SLIDER PROMOCIONAL - INICIALIZACIÓN COMPLETA
// ==============================================

document.addEventListener('DOMContentLoaded', function() {
            // Slider functionality
            const slides = document.querySelectorAll('.slide');
            const dots = document.querySelectorAll('.slider-dot');
            const prevBtn = document.querySelector('.slider-nav.prev');
            const nextBtn = document.querySelector('.slider-nav.next');
            let currentSlide = 0;
            let slideInterval;

            function showSlide(n) {
                slides.forEach(slide => slide.classList.remove('active'));
                dots.forEach(dot => dot.classList.remove('active'));

                currentSlide = (n + slides.length) % slides.length;

                slides[currentSlide].classList.add('active');
                dots[currentSlide].classList.add('active');

                // Reset animation
                const content = slides[currentSlide].querySelector('.slide-content');
                content.classList.remove('fade-in');
                void content.offsetWidth; // Trigger reflow
                content.classList.add('fade-in');
            }

            function nextSlide() {
                showSlide(currentSlide + 1);
            }

            function prevSlide() {
                showSlide(currentSlide - 1);
            }

            function startSlider() {
                slideInterval = setInterval(nextSlide, 5000);
            }

            function stopSlider() {
                clearInterval(slideInterval);
            }

            // Event listeners
            nextBtn.addEventListener('click', () => {
                stopSlider();
                nextSlide();
                startSlider();
            });

            prevBtn.addEventListener('click', () => {
                stopSlider();
                prevSlide();
                startSlider();
            });

            dots.forEach((dot, index) => {
                dot.addEventListener('click', () => {
                    stopSlider();
                    showSlide(index);
                    startSlider();
                });
            });

            // Pause slider on hover
            const slider = document.querySelector('.promo-slider');
            slider.addEventListener('mouseenter', stopSlider);
            slider.addEventListener('mouseleave', startSlider);

            // Initialize slider
            startSlider();

            // Simular carga de productos destacados
            // setTimeout(() => {
            //     const featuredContainer = document.getElementById('featured-products');
            //     if (featuredContainer) {
            //         // En una implementación real, aquí cargarías los productos destacados desde tu backend
            //         //console.log('Cargando productos destacados...');
            //     }
            // }, 1000);
        });

        // Countdown Timer for Flash Sales
        function startFlashCountdown() {
            const countdownElement = document.getElementById('flash-countdown');
            if (!countdownElement) return;

            let hours = 2;
            let minutes = 45;
            let seconds = 30;

            const countdown = setInterval(() => {
                seconds--;

                if (seconds < 0) {
                    seconds = 59;
                    minutes--;
                }

                if (minutes < 0) {
                    minutes = 59;
                    hours--;
                }

                if (hours < 0) {
                    clearInterval(countdown);
                    // Reset or hide flash sales
                    return;
                }

                // Update display
                const hoursElement = countdownElement.querySelector('.countdown-item:nth-child(1) .countdown-number');
                const minutesElement = countdownElement.querySelector('.countdown-item:nth-child(3) .countdown-number');
                const secondsElement = countdownElement.querySelector('.countdown-item:nth-child(5) .countdown-number');

                hoursElement.textContent = hours.toString().padStart(2, '0');
                minutesElement.textContent = minutes.toString().padStart(2, '0');
                secondsElement.textContent = seconds.toString().padStart(2, '0');

            }, 1000);
        }

        // Initialize when page loads
        document.addEventListener('DOMContentLoaded', function() {
            startFlashCountdown();

            // Newsletter subscription
            const newsletterForm = document.querySelector('.newsletter-form');
            if (newsletterForm) {
                newsletterForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const email = this.querySelector('input[type="email"]').value;
                    if (email) {
                        // Simulate subscription
                        Swal.fire({
                            icon: 'success',
                            title: '¡Gracias por suscribirte!',
                            text: 'Te hemos enviado un email de confirmación.',
                            timer: 3000,
                            showConfirmButton: false
                        });
                        this.querySelector('input[type="email"]').value = '';
                    }
                });
            }
        });
    </script>
    <script src="index.js"></script>
    