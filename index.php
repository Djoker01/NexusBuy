<?php
include_once 'Views/Layauts/header.php';
?>
<title>Home | NexusBuy</title>
<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Home</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                    <li class="breadcrumb-item active">Home</li>
                </ol>
            </div>
        </div>
    </div>
</section>
<style>
   .breadcrumb {
        background: transparent;
        margin: 0;
        padding: 0;
    }

    .breadcrumb-item a {
        color: rgba(255, 255, 255, 0.8);
        text-decoration: none;
    }

    .breadcrumb-item.active {
        color: white;
    }

    /* Card Styles */
    .card {
        border: none;
        border-radius: var(--border-radius);
        box-shadow: var(--shadow);
        transition: var(--transition);
        margin-bottom: 25px;
        overflow: hidden;
    }

    .card:hover {
        box-shadow: var(--shadow-hover);
        transform: translateY(-5px);
    }

    .card-header {
        background: var(--gradient-primary);
        color: white;
        border: none;
        padding: 15px 20px;
        font-weight: 600;
        font-size: 1.25rem;
    }

    .card-body {
        padding: 20px;
    }

    /* Promotional Slider */
    .promo-slider {
        position: relative;
        border-radius: var(--border-radius);
        overflow: hidden;
        margin-bottom: 30px;
        height: 400px;
    }

    .slide {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        opacity: 0;
        transition: opacity 1s ease-in-out;
        background-size: cover;
        background-position: center;
        display: flex;
        align-items: center;
        padding: 0 60px;
    }

    .slide.active {
        opacity: 1;
    }

    .slide-content {
        max-width: 600px;
        background: rgba(255, 255, 255, 0.9);
        padding: 30px;
        border-radius: var(--border-radius);
        box-shadow: var(--shadow);
    }

    .slide h2 {
        font-size: 2.5rem;
        font-weight: 700;
        margin-bottom: 15px;
        color: var(--primary);
    }

    .slide p {
        font-size: 1.1rem;
        margin-bottom: 20px;
        color: #555;
    }

    .slide .btn {
        background: var(--gradient-primary);
        border: none;
        padding: 12px 30px;
        font-weight: 600;
        border-radius: 50px;
        transition: var(--transition);
    }

    .slide .btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(67, 97, 238, 0.4);
    }

    .slider-controls {
        position: absolute;
        bottom: 20px;
        left: 0;
        width: 100%;
        display: flex;
        justify-content: center;
        gap: 10px;
    }

    .slider-dot {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.5);
        cursor: pointer;
        transition: var(--transition);
    }

    .slider-dot.active {
        background: white;
        transform: scale(1.2);
    }

    .slider-nav {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        background: rgba(255, 255, 255, 0.8);
        width: 50px;
        height: 50px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: var(--transition);
        z-index: 10;
    }

    .slider-nav:hover {
        background: white;
        box-shadow: var(--shadow);
    }

    .slider-nav.prev {
        left: 20px;
    }

    .slider-nav.next {
        right: 20px;
    }

    /* Featured Products */
    .featured-products {
        margin: 40px 0;
    }

    .section-title {
        text-align: center;
        margin-bottom: 30px;
        position: relative;
    }

    .section-title h2 {
        font-size: 2rem;
        font-weight: 700;
        color: var(--primary);
        display: inline-block;
        margin-bottom: 15px;
    }

    .section-title h2:after {
        content: '';
        display: block;
        width: 60px;
        height: 4px;
        background: var(--gradient-accent);
        margin: 10px auto;
        border-radius: 2px;
    }

    .section-title p {
        color: #666;
        max-width: 600px;
        margin: 0 auto;
    }

    /* Product Cards */
    .product-card {
        border-radius: var(--border-radius);
        overflow: hidden;
        transition: var(--transition);
        height: 100%;
        position: relative;
    }

    .product-card:hover {
        transform: translateY(-8px);
    }

    .product-card .card-body {
        padding: 15px;
        display: flex;
        flex-direction: column;
        height: 100%;
    }

    .product-image {
        height: 180px;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        border-radius: 8px;
        margin-bottom: 15px;
    }

    .product-image img {
        max-height: 100%;
        max-width: 100%;
        object-fit: contain;
        transition: var(--transition);
    }

    .product-card:hover .product-image img {
        transform: scale(1.05);
    }

    .product-brand {
        font-size: 0.85rem;
        color: #777;
        margin-bottom: 5px;
    }

    .product-title {
        font-weight: 600;
        color: var(--dark);
        text-decoration: none;
        margin-bottom: 10px;
        display: block;
        line-height: 1.4;
        transition: var(--transition);
    }

    .product-title:hover {
        color: var(--primary);
    }

    .product-rating {
        margin-bottom: 10px;
    }

    .product-price {
        margin-top: auto;
    }

    .original-price {
        text-decoration: line-through;
        color: #999;
        font-size: 0.9rem;
        margin-right: 5px;
    }

    .discount-percent {
        color: var(--danger);
        font-size: 0.9rem;
        font-weight: 500;
    }

    .current-price {
        font-size: 1.3rem;
        font-weight: 700;
        color: var(--primary);
        margin-top: 5px;
    }

    .badge-free-shipping {
        background: var(--success);
        color: white;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 0.75rem;
        display: inline-block;
        margin-bottom: 8px;
    }

    .featured-badge {
        position: absolute;
        top: 10px;
        left: 10px;
        background: var(--gradient-accent);
        color: white;
        padding: 5px 10px;
        border-radius: 4px;
        font-size: 0.75rem;
        font-weight: 600;
        z-index: 2;
    }

    /* Grid Layout */
    .row {
        display: flex;
        flex-wrap: wrap;
        margin: 0 -10px;
    }

    .col-sm-2 {
        padding: 0 10px;
        margin-bottom: 20px;
    }

    /* Animations */
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(20px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .fade-in {
        animation: fadeIn 0.8s ease-out;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .slide {
            padding: 0 20px;
        }

        .slide h2 {
            font-size: 1.8rem;
        }

        .slide-content {
            padding: 20px;
        }

        .promo-slider {
            height: 350px;
        }

        .slider-nav {
            width: 40px;
            height: 40px;
        }
    }

    @media (max-width: 576px) {
        .promo-slider {
            height: 300px;
        }

        .slide h2 {
            font-size: 1.5rem;
        }

        .section-title h2 {
            font-size: 1.6rem;
        }
    }

    /* Flash Sales Styles */
    .flash-timer {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .countdown-timer {
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .countdown-item {
        background: var(--danger);
        color: white;
        padding: 8px 12px;
        border-radius: 8px;
        text-align: center;
        min-width: 60px;
    }

    .countdown-number {
        font-size: 1.2rem;
        font-weight: 700;
        display: block;
    }

    .countdown-label {
        font-size: 0.7rem;
        opacity: 0.9;
        display: block;
    }

    .countdown-separator {
        font-weight: 700;
        color: var(--danger);
    }

    /* Category Cards */
    .category-card {
        border-radius: var(--border-radius);
        overflow: hidden;
        box-shadow: var(--shadow);
        transition: var(--transition);
    }

    .category-card:hover {
        transform: translateY(-5px);
        box-shadow: var(--shadow-hover);
    }

    .category-image {
        position: relative;
        height: 200px;
        overflow: hidden;
    }

    .category-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: var(--transition);
    }

    .category-card:hover .category-image img {
        transform: scale(1.1);
    }

    .category-overlay {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        background: linear-gradient(transparent, rgba(0, 0, 0, 0.8));
        color: white;
        padding: 20px;
    }

    .category-overlay h4 {
        margin: 0;
        font-weight: 600;
    }

    .product-count {
        font-size: 0.9rem;
        opacity: 0.9;
    }

    /* Category Cards */
    .category-card {
        border-radius: var(--border-radius);
        overflow: hidden;
        box-shadow: var(--shadow);
        transition: var(--transition);
    }

    .category-card:hover {
        transform: translateY(-5px);
        box-shadow: var(--shadow-hover);
    }

    .category-image {
        position: relative;
        height: 200px;
        overflow: hidden;
    }

    .category-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: var(--transition);
    }

    .category-card:hover .category-image img {
        transform: scale(1.1);
    }

    .category-overlay {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        background: linear-gradient(transparent, rgba(0, 0, 0, 0.8));
        color: white;
        padding: 20px;
    }

    .category-overlay h4 {
        margin: 0;
        font-weight: 600;
    }

    .product-count {
        font-size: 0.9rem;
        opacity: 0.9;
    }

    /* Brands Slider */
    .brands-slider {
        display: flex;
        justify-content: space-around;
        align-items: center;
        flex-wrap: wrap;
        gap: 20px;
    }

    .brand-item {
        padding: 15px;
        background: white;
        border-radius: 8px;
        box-shadow: var(--shadow);
        transition: var(--transition);
    }

    .brand-item:hover {
        transform: translateY(-3px);
    }

    .brand-item img {
        height: 40px;
        object-fit: contain;
    }

    /* Testimonial Cards */
    .testimonial-card {
        background: white;
        border-radius: var(--border-radius);
        box-shadow: var(--shadow);
        padding: 25px;
        height: 100%;
        transition: var(--transition);
    }

    .testimonial-card:hover {
        transform: translateY(-5px);
        box-shadow: var(--shadow-hover);
    }

    .testimonial-text {
        font-style: italic;
        color: #666;
        margin-bottom: 20px;
    }

    .testimonial-author {
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .testimonial-author img {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        object-fit: cover;
    }

    .author-info strong {
        display: block;
        margin-bottom: 2px;
    }

    .author-info span {
        font-size: 0.9rem;
        color: #888;
    }

    /* Benefits Section */
    .benefit-item {
        padding: 20px;
    }

    .benefit-icon {
        width: 70px;
        height: 70px;
        background: var(--gradient-primary);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 15px;
        color: white;
        font-size: 1.5rem;
    }

    .benefit-item h5 {
        margin-bottom: 8px;
        font-weight: 600;
    }

    /* Blog Cards */
    .blog-card {
        background: white;
        border-radius: var(--border-radius);
        box-shadow: var(--shadow);
        overflow: hidden;
        transition: var(--transition);
        height: 100%;
    }

    .blog-card:hover {
        transform: translateY(-5px);
        box-shadow: var(--shadow-hover);
    }

    .blog-image {
        position: relative;
        height: 200px;
        overflow: hidden;
    }

    .blog-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: var(--transition);
    }

    .blog-card:hover .blog-image img {
        transform: scale(1.1);
    }

    .blog-date {
        position: absolute;
        top: 15px;
        left: 15px;
        background: var(--primary);
        color: white;
        padding: 10px;
        border-radius: 8px;
        text-align: center;
        line-height: 1;
    }

    .blog-date .day {
        display: block;
        font-size: 1.2rem;
        font-weight: 700;
    }

    .blog-date .month {
        display: block;
        font-size: 0.8rem;
        opacity: 0.9;
    }

    .blog-content {
        padding: 20px;
    }

    .blog-title {
        font-weight: 600;
        margin-bottom: 10px;
        color: var(--dark);
    }

    .blog-excerpt {
        color: #666;
        margin-bottom: 15px;
        font-size: 0.9rem;
    }

    .read-more {
        color: var(--primary);
        text-decoration: none;
        font-weight: 500;
        transition: var(--transition);
    }

    .read-more:hover {
        color: var(--secondary);
    }

    /* Newsletter Section */
    .newsletter-section .card {
        background: var(--gradient-primary) !important;
    }

    .newsletter-form .form-control {
        border: none;
        padding: 12px 15px;
    }

    .newsletter-form .btn {
        padding: 12px 20px;
        font-weight: 600;
    }

    /* Responsive Adjustments */
    @media (max-width: 768px) {
        .flash-timer {
            flex-direction: column;
            text-align: center;
            margin-top: 10px;
        }

        .brands-slider {
            justify-content: center;
            gap: 15px;
        }

        .brand-item {
            flex: 0 0 calc(33.333% - 15px);
        }
    }

    /* Contenedor horizontal para categorías - MANTIENE ESTILOS ORIGINALES */
    .categories-horizontal-container {
        display: flex;
        overflow-x: auto;
        overflow-y: hidden;
        scroll-behavior: smooth;
        gap: 25px;
        padding: 10px 5px 15px 5px;
        margin: 0 -5px;
        scrollbar-width: thin;
        scrollbar-color: #c1c1c1 #f1f1f1;
    }

    .categories-horizontal-container::-webkit-scrollbar {
        height: 8px;
    }

    .categories-horizontal-container::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 4px;
    }

    .categories-horizontal-container::-webkit-scrollbar-thumb {
        background: #c1c1c1;
        border-radius: 4px;
    }

    .categories-horizontal-container::-webkit-scrollbar-thumb:hover {
        background: #a8a8a8;
    }

    /* Items de categoría en horizontal - MANTIENE TODOS LOS ESTILOS ORIGINALES */
    .category-item-horizontal {
        flex: 0 0 auto;
        width: calc(25% - 19px);
        /* Equivalente a col-md-3 con gap de 25px */
        min-width: 250px;
        /* Ancho mínimo para mantener proporción */
    }

    /* Para pantallas más pequeñas */
    @media (max-width: 768px) {
        .category-item-horizontal {
            width: calc(50% - 13px);
            /* Equivalente a col-6 con gap de 25px */
            min-width: 180px;
        }
    }

    .pagination-marcas {
        background-color: #f8f9fa;
        border-radius: 8px;
        padding: 15px;
        border: 1px solid #dee2e6;
    }

    .pagination-marcas .page-item.active .page-link {
        background-color: #007bff;
        border-color: #007bff;
    }

    .pagination-marcas .page-link {
        color: #495057;
        border-radius: 4px;
        margin: 0 2px;
    }

    .pagination-marcas .page-link:hover {
        background-color: #e9ecef;
    }

    .brand-item {
        text-align: center;
        padding: 15px;
        border-radius: 8px;
        transition: transform 0.3s;
    }

    .brand-item:hover {
        transform: translateY(-5px);
    }

    .brand-item img {
        max-height: 80px;
        object-fit: contain;
    }

    .brand-name {
        font-size: 0.85rem;
        font-weight: 500;
        margin-bottom: 0;
    }

    .brand-item {
        text-align: center;
        padding: 15px;
        border-radius: 8px;
        transition: transform 0.3s, box-shadow 0.3s;
        margin-bottom: 20px;
    }

    .brand-item:hover {
        transform: translateY(-5px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }

    .brand-link {
        text-decoration: none;
        color: inherit;
        display: block;
    }

    .brand-link:hover {
        text-decoration: none;
        color: inherit;
    }

    .brand-item img {
        max-height: 80px;
        object-fit: contain;
        margin-bottom: 10px;
        transition: transform 0.3s;
    }

    .brand-item:hover img {
        transform: scale(1.05);
    }

    .brand-name {
        font-size: 0.85rem;
        font-weight: 500;
        margin-bottom: 0;
        color: #333;
    }

    /* Efecto para indicar que es clickeable */
    .brand-link {
        cursor: pointer;
    }

    .brand-link .brand-item {
        background-color: #fff;
        border: 1px solid #e0e0e0;
    }

    .brand-link:hover .brand-item {
        background-color: #f8f9fa;
        border-color: #007bff;
    }

    /* Estilos para ofertas flash simplificadas */
.flash-sales-simple {
    background: linear-gradient(135deg, #fff8f8 0%, #ffeded 100%);
    padding: 2rem 0;
    border-radius: 12px;
    border: 1px solid #ffcccc;
}

.flash-product-card {
    position: relative;
    border: 2px solid #ffcccc;
    border-radius: 12px;
    transition: all 0.3s ease;
    height: 100%;
    overflow: hidden;
}

.flash-product-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(255, 0, 0, 0.15);
    border-color: #ff6666;
}

.flash-discount-badge {
    position: absolute;
    top: 12px;
    right: 12px;
    background: #ff4444;
    color: white;
    padding: 5px 10px;
    border-radius: 20px;
    font-weight: bold;
    font-size: 0.9rem;
    z-index: 2;
}

.flash-time-badge {
    position: absolute;
    top: 12px;
    left: 12px;
    background: rgba(0, 0, 0, 0.7);
    color: white;
    padding: 5px 10px;
    border-radius: 20px;
    font-size: 0.8rem;
    z-index: 2;
}

.flash-price {
    font-size: 1.3rem;
    font-weight: bold;
    color: #ff4444;
}

.flash-old-price {
    text-decoration: line-through;
    color: #999;
    font-size: 0.9rem;
}

/* Estilo mínimo para ofertas flash */
.border-danger.border-opacity-25 {
    transition: transform 0.2s;
}
.border-danger.border-opacity-25:hover {
    transform: translateY(-3px);
    border-color: #dc3545 !important;
}

/* ====================================
   ESTILOS PARA PAGINACIÓN DE MARCAS
   ==================================== */

/* Contenedor principal */
.pagination-marcas {
    margin: 20px 0;
}

/* Personalización de la paginación Bootstrap */
.pagination-marcas .pagination {
    margin-bottom: 0;
}

.pagination-marcas .page-link {
    color: #4361ee; /* Color primario de tu tema */
    border: 1px solid #dee2e6;
    padding: 6px 12px;
    font-size: 0.875rem;
    transition: all 0.3s ease;
}

.pagination-marcas .page-link:hover {
    color: #fff;
    background-color: #3a0ca3; /* Color secundario */
    border-color: #3a0ca3;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(58, 12, 163, 0.2);
}

.pagination-marcas .page-item.active .page-link {
    background: var(--gradient-primary); /* Usa tu variable CSS */
    color: white;
    border-color: #4361ee;
    font-weight: 600;
}

.pagination-marcas .page-item.disabled .page-link {
    color: #adb5bd;
    background-color: #f8f9fa;
    border-color: #dee2e6;
}

/* Flechas con iconos Font Awesome */
.pagination-marcas .page-link i {
    font-size: 0.8rem;
}

/* Separador de puntos suspensivos */
.pagination-marcas .page-item.disabled .page-link:not([href]) {
    background: transparent;
    border: none;
    color: #6c757d;
    padding: 6px 4px;
}

/* Responsive */
@media (max-width: 768px) {
    .pagination-marcas .page-link {
        padding: 4px 8px;
        font-size: 0.8rem;
        margin: 0 2px;
    }
    
    .pagination-marcas .pagination {
        flex-wrap: wrap;
        justify-content: center;
    }
}

/* Estilo alternativo más moderno */
.pagination-marcas.modern .page-link {
    border-radius: 8px;
    margin: 0 4px;
    border: none;
    background: #f8f9fa;
}

.pagination-marcas.modern .page-item.active .page-link {
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(67, 97, 238, 0.3);
}

/* Para la información de página actual */
.pagination-info {
    font-size: 0.875rem;
    color: #6c757d;
    padding: 8px 0;
}
</style>



<section class="content">
    <!-- Promotional Slider -->
    <div class="promo-slider card">
        <div class="slide active" style="background-image: linear-gradient(rgba(0,0,0,0.3), rgba(0,0,0,0.3)), url('Util/Img/Banner/Banner 1.jpg')">
            <div class="slide-content fade-in footer-link en-desarrollo">
                <h2>Ofertas de Verano</h2>
                <p>Hasta 50% de descuento en productos seleccionados. Aprovecha estas ofertas por tiempo limitado.</p>
                <button class="btn btn-primary">Ver Ofertas</button>
            </div>
        </div>
        <div class="slide" style="background-image: linear-gradient(rgba(0,0,0,0.3), rgba(0,0,0,0.3)), url('Util/Img/Banner/Banner 2.jpg')">
            <div class="slide-content footer-link en-desarrollo">
                <h2>Envío Gratis</h2>
                <p>En todas tus compras mayores a $50. Disfruta de envío rápido y seguro a todo el país.</p>
                <button class="btn btn-primary">Comprar Ahora</button>
            </div>
        </div>
        <div class="slide" style="background-image: linear-gradient(rgba(0,0,0,0.3), rgba(0,0,0,0.3)), url('Util/Img/Banner/Banner 3.jpg')">
            <div class="slide-content footer-link en-desarrollo">
                <h2>Nuevos Productos</h2>
                <p>Descubre nuestra última colección de productos exclusivos. Tecnología innovadora al mejor precio.</p>
                <button class="btn btn-primary">Explorar</button>
            </div>
        </div>

        <div class="slider-nav prev">
            <i class="fas fa-chevron-left"></i>
        </div>
        <div class="slider-nav next">
            <i class="fas fa-chevron-right"></i>
        </div>

        <div class="slider-controls">
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
    <div class="testimonials-section mb-5">
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
    </div>

    <!-- Blog Section -->
    <div class="blog-section mb-5">
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
    </div>

    <!-- Benefits Section -->
    <div class="benefits-section mb-5">
        <div class="row">
            <div class="col-md-3 col-6 text-center mb-4">
                <div class="benefit-item">
                    <div class="benefit-icon">
                        <i class="fas fa-shipping-fast"></i>
                    </div>
                    <h5>Envío Gratis</h5>
                    <p class="text-muted">En compras mayores a $50</p>
                </div>
            </div>
            <div class="col-md-3 col-6 text-center mb-4">
                <div class="benefit-item">
                    <div class="benefit-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h5>Pago Seguro</h5>
                    <p class="text-muted">Transacciones 100% protegidas</p>
                </div>
            </div>
            <div class="col-md-3 col-6 text-center mb-4">
                <div class="benefit-item">
                    <div class="benefit-icon">
                        <i class="fas fa-undo-alt"></i>
                    </div>
                    <h5>Devoluciones</h5>
                    <p class="text-muted">30 días para cambiar de opinión</p>
                </div>
            </div>
            <div class="col-md-3 col-6 text-center mb-4">
                <div class="benefit-item">
                    <div class="benefit-icon">
                        <i class="fas fa-headset"></i>
                    </div>
                    <h5>Soporte 24/7</h5>
                    <p class="text-muted">Estamos aquí para ayudarte</p>
                </div>
            </div>
        </div>
    </div>

    <?php
    include_once 'Views/Layauts/footer.php';
    ?>
    <script>
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
            setTimeout(() => {
                const featuredContainer = document.getElementById('featured-products');
                if (featuredContainer) {
                    // En una implementación real, aquí cargarías los productos destacados desde tu backend
                    //console.log('Cargando productos destacados...');
                }
            }, 1000);
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
