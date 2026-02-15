// Sistema de banners para el frontend
const NexusBuyBanners = (function() {
    // Configuración
    const CONFIG = {
        sliderInterval: 5000,
        sliderSelector: '#promoSlider',
        lateralSelector: '#bannerLateral',
        superiorSelector: '#bannerSuperior',
        inferiorSelector: '#bannerInferior',
        popupSelector: '#bannerPopup',
        popupCookieDays: 1,
        popupDelay: 3000
    };

    // Estado
    let banners = {};
    let sliderActual = 0;
    let sliderIntervalo = null;
    let sliderBanners = [];

    // Inicialización
    function init() {
        cargarTodosBanners();
        initEventos();
    }

    // Cargar todos los banners activos
    async function cargarTodosBanners() {
        try {
            const response = await $.post('Controllers/BannerController.php', {
                funcion: 'obtener_todos_banners'
            });

            if (response.success && response.banners) {
                banners = response.banners;
                renderizarTodosBanners();
            }
        } catch (error) {
            console.error('Error cargando banners:', error);
        }
    }

    // Renderizar todas las secciones
    function renderizarTodosBanners() {
        // Slider principal
        if (banners.slider_principal) {
            sliderBanners = banners.slider_principal;
            renderizarSlider();
        }

        // Banner lateral
        if (banners.lateral_derecho) {
            renderizarBannerLateral(banners.lateral_derecho[0]);
        }

        // Banner superior
        if (banners.superior) {
            renderizarBannerSuperior(banners.superior[0]);
        }

        // Banner inferior
        if (banners.inferior) {
            renderizarBannerInferior(banners.inferior[0]);
        }

        // Popup
        if (banners.popup && banners.popup.length > 0) {
            setTimeout(() => {
                mostrarPopup(banners.popup[0]);
            }, CONFIG.popupDelay);
        }
    }

    // ========== SLIDER PRINCIPAL ==========
    function renderizarSlider() {
        const $slider = $(CONFIG.sliderSelector);
        if (!$slider.length || sliderBanners.length === 0) return;

        let slidesHTML = '';
        let dotsHTML = '';

        sliderBanners.forEach((banner, index) => {
            const activeClass = index === 0 ? 'active' : '';

            slidesHTML += `
                <div class="slide ${activeClass}" style="background-image: url('${banner.imagen}');">
                    <div class="slide-content">
                        ${banner.titulo ? `<h2>${banner.titulo}</h2>` : ''}
                        ${banner.descripcion ? `<p>${banner.descripcion}</p>` : ''}
                        <a href="${banner.url}" class="btn btn-primary">
                            <i class="fas ${banner.icono_boton} mr-2"></i>${banner.texto_boton}
                        </a>
                    </div>
                </div>
            `;

            dotsHTML += `<div class="slider-dot ${activeClass}" data-index="${index}"></div>`;
        });

        $slider.html(`
            ${slidesHTML}
            <div class="slider-controls">${dotsHTML}</div>
            <div class="slider-nav prev" id="prevSlide"><i class="fas fa-chevron-left"></i></div>
            <div class="slider-nav next" id="nextSlide"><i class="fas fa-chevron-right"></i></div>
        `);

        initSliderEventos();
        iniciarSliderAuto();
    }

    function initSliderEventos() {
        // Dots
        $('.slider-dot').off('click').on('click', function() {
            const index = $(this).data('index');
            irASlide(index);
        });

        // Flechas
        $('#prevSlide').off('click').on('click', (e) => {
            e.preventDefault();
            slideAnterior();
        });

        $('#nextSlide').off('click').on('click', (e) => {
            e.preventDefault();
            slideSiguiente();
        });

        // Hover pausa
        $('.promo-slider').off('mouseenter mouseleave').on({
            mouseenter: () => pausarSlider(),
            mouseleave: () => reanudarSlider()
        });
    }

    function irASlide(index) {
        if (index < 0) index = sliderBanners.length - 1;
        if (index >= sliderBanners.length) index = 0;

        $('.slide').removeClass('active');
        $(`.slide`).eq(index).addClass('active');

        $('.slider-dot').removeClass('active');
        $(`.slider-dot[data-index="${index}"]`).addClass('active');

        sliderActual = index;
    }

    function slideSiguiente() {
        if (sliderBanners.length > 1) {
            irASlide(sliderActual + 1);
        }
    }

    function slideAnterior() {
        if (sliderBanners.length > 1) {
            irASlide(sliderActual - 1);
        }
    }

    function iniciarSliderAuto() {
        if (sliderBanners.length > 1) {
            sliderIntervalo = setInterval(slideSiguiente, CONFIG.sliderInterval);
        }
    }

    function pausarSlider() {
        if (sliderIntervalo) {
            clearInterval(sliderIntervalo);
            sliderIntervalo = null;
        }
    }

    function reanudarSlider() {
        if (!sliderIntervalo && sliderBanners.length > 1) {
            sliderIntervalo = setInterval(slideSiguiente, CONFIG.sliderInterval);
        }
    }

    // ========== BANNER LATERAL ==========
    function renderizarBannerLateral(banner) {
        const $container = $(CONFIG.lateralSelector);
        if (!$container.length) return;

        const html = `
            <div class="banner-lateral">
                <a href="${banner.url}" class="banner-link">
                    <img src="${banner.imagen}" alt="${banner.titulo}" class="img-fluid">
                    ${banner.titulo ? `<h5 class="banner-titulo">${banner.titulo}</h5>` : ''}
                    ${banner.descripcion ? `<p class="banner-descripcion">${banner.descripcion}</p>` : ''}
                </a>
            </div>
        `;

        $container.html(html);
    }

    // ========== BANNER SUPERIOR ==========
    function renderizarBannerSuperior(banner) {
        const $container = $(CONFIG.superiorSelector);
        if (!$container.length) return;

        const html = `
            <div class="banner-superior" style="background-image: url('${banner.imagen}');">
                <div class="container">
                    <div class="banner-contenido">
                        <h4>${banner.titulo}</h4>
                        <p>${banner.descripcion}</p>
                        <a href="${banner.url}" class="btn btn-sm btn-light">
                            ${banner.texto_boton} <i class="fas ${banner.icono_boton} ml-2"></i>
                        </a>
                    </div>
                </div>
            </div>
        `;

        $container.html(html);
    }

    // ========== BANNER INFERIOR ==========
    function renderizarBannerInferior(banner) {
        const $container = $(CONFIG.inferiorSelector);
        if (!$container.length) return;

        const html = `
            <div class="banner-inferior">
                <a href="${banner.url}" class="banner-link">
                    <img src="${banner.imagen}" alt="${banner.titulo}" class="img-fluid">
                    <div class="banner-texto">
                        <h5>${banner.titulo}</h5>
                        <p>${banner.descripcion}</p>
                    </div>
                </a>
            </div>
        `;

        $container.html(html);
    }

    // ========== POPUP ==========
    function mostrarPopup(banner) {
        if (!banner) return;

        // Verificar cookie
        const cookieName = `banner_popup_${banner.id}`;
        if (document.cookie.includes(cookieName)) return;

        const modalHTML = `
            <div class="modal fade" id="bannerPopupModal" tabindex="-1" role="dialog">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">${banner.titulo || 'Oferta especial'}</h5>
                            <button type="button" class="close" data-dismiss="modal">
                                <span>&times;</span>
                            </button>
                        </div>
                        <div class="modal-body p-0">
                            <a href="${banner.url}" class="popup-link">
                                <img src="${banner.imagen}" alt="${banner.titulo}" class="img-fluid w-100">
                                ${banner.descripcion ? `
                                    <div class="popup-text p-3">
                                        <p>${banner.descripcion}</p>
                                        <span class="btn btn-primary btn-block">${banner.texto_boton}</span>
                                    </div>
                                ` : ''}
                            </a>
                        </div>
                        <div class="modal-footer">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="noMostrarPopup">
                                <label class="form-check-label" for="noMostrarPopup">
                                    No mostrar este mensaje por ${CONFIG.popupCookieDays} día(s)
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;

        $('body').append(modalHTML);

        const $modal = $('#bannerPopupModal');
        $modal.modal('show');

        $('#noMostrarPopup').on('change', function() {
            if (this.checked) {
                const fecha = new Date();
                fecha.setDate(fecha.getDate() + CONFIG.popupCookieDays);
                document.cookie = `${cookieName}=true; expires=${fecha.toUTCString()}; path=/`;
            }
        });

        $modal.on('hidden.bs.modal', function() {
            $(this).remove();
        });
    }

    // ========== EVENTOS GLOBALES ==========
    function initEventos() {
        // Redimensionar ventana (responsive)
        $(window).on('resize', function() {
            // No recargamos, solo ajustamos slider si es necesario
        });
    }

    // API pública
    return {
        init: init,
        cargarBanners: cargarTodosBanners
    };
})();

// Inicializar cuando el DOM esté listo
$(document).ready(function() {
    NexusBuyBanners.init();
});