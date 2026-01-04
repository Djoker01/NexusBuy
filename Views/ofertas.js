// ofertas.js - Funcionalidad completa para la página de ofertas

document.addEventListener('DOMContentLoaded', function() {
    // Inicializar todas las funciones
    initOfertasPage();
});

function initOfertasPage() {
    // 1. Cargar todas las ofertas
    loadAllOfertas();
    
    // 2. Configurar eventos de filtros rápidos
    setupQuickFilters();
    
    // 3. Configurar suscripción
    setupSubscription();
    
    // 4. Configurar scroll suave para anclas
    setupSmoothScrolling();
    
    // 5. Mostrar estadísticas de ofertas
    loadOfertasStats();
    
    // 6. Configurar botones de refresh
    setupRefreshButtons();
    
    // 7. Verificar sesión del usuario
    checkUserSession();

    // 8. Agregar estilos para bundles
    addBundleStyles();
    
    // 9. Agregar estilos para categorías
    addCategoriaStyles();
}

// Función principal para cargar todas las ofertas en una sola petición
function loadAllOfertas() {
    showLoadingState();
    
    const formData = new FormData();
    formData.append('funcion', 'obtener_todas_ofertas');
    
    fetch('../Controllers/OfertasController.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) throw new Error('Error en la respuesta del servidor');
        return response.json();
    })
    .then(data => {
        hideLoadingState();
        
        if (data && Object.keys(data).length > 0) {
            // console.log("Datos recibidos del servidor:", Object.keys(data));
            
            // 1. Primero, inicializar el contador con las ofertas flash
            if (data.flash && data.flash.length > 0) {
                initCountdown(data.flash);
            } else {
                // Si no hay ofertas flash, mostrar "Terminada"
                showNoFlashOffers();
            }
            
            // 2. Luego renderizar todas las secciones
            renderOfertasFlash(data.flash || []);
            renderSuperDescuentos(data.super || []);
            renderMasVendidos(data.vendidos || []);
            renderEnvioGratis(data.envio_gratis || []);
            renderDestacados(data.destacados || []);
            renderBundles(data.bundles || []);
            
            // 3. Renderizar categorías con oferta si están disponibles
            if (data.categorias_oferta && data.categorias_oferta.length > 0) {
                // console.log(`Renderizando ${data.categorias_oferta.length} categorías`);
                renderCategoriasOferta(data.categorias_oferta);
            } else {
                console.log("No hay categorías en los datos recibidos");
                // Mostrar mensaje de no hay categorías
                const categoriaContainer = document.getElementById('categorias-container');
                if (categoriaContainer) {
                    const loadingState = categoriaContainer.querySelector('.loading-state');
                    if (loadingState) loadingState.style.display = 'none';
                    
                    categoriaContainer.innerHTML = `
                        <div class="col-12 text-center py-5">
                            <i class="fas fa-tags fa-3x text-muted mb-3"></i>
                            <h4 class="text-muted">No hay categorías en oferta</h4>
                            <p class="text-muted">Pronto tendremos ofertas especiales por categoría</p>
                        </div>
                    `;
                }
            }
        } else {
            showEmptyState();
            // Si no hay datos, mostrar contador como terminado
            showNoFlashOffers();
        }
    })
    .catch(error => {
        hideLoadingState();
        showErrorState(error);
        console.error('Error cargando ofertas:', error);
        // En caso de error, mostrar contador como terminado
        showNoFlashOffers();
    });
}

// Mostrar estado cuando no hay ofertas flash
function showNoFlashOffers() {
    const countdownElement = document.getElementById('countdown');
    const flashCountdownElement = document.getElementById('flash-countdown');
    const countdownBadge = document.querySelector('.countdown-badge');
    const flashSectionTitle = document.querySelector('#ofertas-flash h2');
    const flashBadge = document.querySelector('#ofertas-flash .badge-danger');
    
    if (countdownElement) {
        countdownElement.textContent = '00:00:00';
        countdownElement.style.color = '#ffffff';
        countdownElement.parentElement.classList.add('text-white');
        countdownElement.parentElement.classList.remove('text-muted');
    }
    
    if (flashCountdownElement) {
        flashCountdownElement.textContent = '00:00:00';
        flashCountdownElement.style.color = '#ffffff';
        flashCountdownElement.parentElement.classList.add('text-white');
        flashCountdownElement.parentElement.classList.remove('text-muted');
    }
    
    if (countdownBadge) {
        countdownBadge.innerHTML = '<i class="fas fa-ban"></i> OFERTA FLASH TERMINADA';
        countdownBadge.classList.remove('badge-danger');
        countdownBadge.classList.add('badge-light');
        countdownBadge.style.animation = 'none';
        countdownBadge.style.color = '#000000';
        countdownBadge.style.border = '1px solid #dee2e6';
    }
    
    if (flashSectionTitle) {
        const icon = flashSectionTitle.querySelector('i');
        if (icon) {
            icon.classList.remove('text-warning');
            icon.classList.add('text-white');
        }
    }
    
    if (flashBadge) {
        flashBadge.textContent = 'Terminada: 00:00:00';
        flashBadge.classList.remove('badge-danger');
        flashBadge.classList.add('badge-light');
        flashBadge.style.color = '#000000';
    }
}

// CONTADOR REGRESIVO DINÁMICO - Basado en ofertas flash de la BD
function initCountdown(ofertasFlash) {
    if (!ofertasFlash || ofertasFlash.length === 0) {
        showNoFlashOffers();
        return;
    }
    
    // Encontrar la oferta flash que termina más pronto
    let ofertaMasProxima = null;
    let menorTiempoRestante = Infinity;
    
    // Buscar entre todas las ofertas flash cuál termina primero
    ofertasFlash.forEach(oferta => {
        if (oferta.fecha_fin) {
            const fechaFin = new Date(oferta.fecha_fin);
            const ahora = new Date();
            const tiempoRestante = fechaFin - ahora;
            
            if (tiempoRestante > 0 && tiempoRestante < menorTiempoRestante) {
                menorTiempoRestante = tiempoRestante;
                ofertaMasProxima = oferta;
            }
        }
    });
    
    // Si no hay ofertas activas, mostrar terminado
    if (!ofertaMasProxima || menorTiempoRestante <= 0) {
        showNoFlashOffers();
        return;
    }
    
    const fechaFin = new Date(ofertaMasProxima.fecha_fin);
    const countdownElement = document.getElementById('countdown');
    const flashCountdownElement = document.getElementById('flash-countdown');
    const countdownBadge = document.querySelector('.countdown-badge');
    
    // Actualizar badge para mostrar que hay ofertas activas
    if (countdownBadge) {
        countdownBadge.innerHTML = '<i class="fas fa-bolt"></i> OFERTA FLASH ACTIVA';
        countdownBadge.classList.remove('badge-light');
        countdownBadge.classList.add('badge-danger');
        countdownBadge.style.animation = 'pulse 2s infinite';
        countdownBadge.style.color = '#ffffff';
        countdownBadge.style.border = 'none';
    }
    
    function updateCountdown() {
        const ahora = new Date();
        const tiempoRestante = fechaFin - ahora;
        
        if (tiempoRestante <= 0) {
            // La oferta ha terminado
            showNoFlashOffers();
            
            // Recargar ofertas después de que termine
            setTimeout(() => {
                loadAllOfertas();
            }, 5000);
            
            return;
        }
        
        // Calcular días, horas, minutos y segundos
        const dias = Math.floor(tiempoRestante / (1000 * 60 * 60 * 24));
        const horas = Math.floor((tiempoRestante % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutos = Math.floor((tiempoRestante % (1000 * 60 * 60)) / (1000 * 60));
        const segundos = Math.floor((tiempoRestante % (1000 * 60)) / 1000);
        
        // Formatear el tiempo
        let tiempoFormateado;
        if (dias > 0) {
            tiempoFormateado = `${dias}d ${horas.toString().padStart(2, '0')}:${minutos.toString().padStart(2, '0')}:${segundos.toString().padStart(2, '0')}`;
        } else {
            tiempoFormateado = `${horas.toString().padStart(2, '0')}:${minutos.toString().padStart(2, '0')}:${segundos.toString().padStart(2, '0')}`;
        }
        
        // Actualizar elementos del DOM
        if (countdownElement) {
            countdownElement.textContent = tiempoFormateado;
            countdownElement.style.color = '#ffffff';
            
            // Quitar clases de color anteriores
            countdownElement.classList.remove('text-danger', 'text-warning', 'text-success');
            countdownElement.classList.add('text-white');
            
            // Cambiar estilo del badge según el tiempo restante
            if (horas < 1) {
                countdownElement.style.fontWeight = 'bold';
            } else {
                countdownElement.style.fontWeight = 'normal';
            }
        }
        
        if (flashCountdownElement) {
            flashCountdownElement.textContent = tiempoFormateado;
            flashCountdownElement.style.color = '#ffffff';
            
            // Quitar clases de color anteriores
            flashCountdownElement.classList.remove('text-danger', 'text-warning');
            flashCountdownElement.classList.add('text-white');
            
            if (horas < 1) {
                flashCountdownElement.style.fontWeight = 'bold';
            } else {
                flashCountdownElement.style.fontWeight = 'normal';
            }
        }
        
        // Actualizar badge de la sección de ofertas flash
        const flashBadge = document.querySelector('#ofertas-flash .badge');
        if (flashBadge) {
            flashBadge.innerHTML = `Termina en: <span style="color: #ffffff;">${tiempoFormateado}</span>`;
            
            // Cambiar color del badge según tiempo restante
            if (horas < 1) {
                flashBadge.style.backgroundColor = '#dc3545';
            } else if (horas < 6) {
                flashBadge.style.backgroundColor = '#fd7e14';
            } else {
                flashBadge.style.backgroundColor = '#28a745';
            }
            
            // Asegurar que el texto sea blanco
            flashBadge.style.color = '#ffffff';
        }
        
        // Mostrar advertencia si quedan menos de 5 minutos
        if (minutos < 5 && segundos > 0) {
            mostrarAdvertenciaTiempo(minutos, segundos);
        }
    }
    
    // Actualizar inmediatamente
    updateCountdown();
    
    // Actualizar cada segundo
    const countdownInterval = setInterval(updateCountdown, 1000);
    
    // Guardar el intervalo para poder limpiarlo si es necesario
    window.flashCountdownInterval = countdownInterval;
}

// Función para mostrar advertencia de tiempo bajo
function mostrarAdvertenciaTiempo(minutos, segundos) {
    // Solo mostrar cada 30 segundos para no ser intrusivo
    const ahora = Date.now();
    if (!window.ultimaAdvertenciaTiempo || (ahora - window.ultimaAdvertenciaTiempo > 30000)) {
        window.ultimaAdvertenciaTiempo = ahora;
        
        // Crear notificación de advertencia
        const notificacion = document.createElement('div');
        notificacion.className = 'alert alert-warning alert-dismissible fade show position-fixed';
        notificacion.style.cssText = 'position: fixed; bottom: 20px; right: 20px; z-index: 9999; max-width: 350px;';
        notificacion.innerHTML = `
            <i class="fas fa-exclamation-triangle mr-2"></i>
            <strong>¡Oferta Flash por terminar!</strong><br>
            Quedan ${minutos} min ${segundos} seg para que terminen las ofertas flash.
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        `;
        
        document.body.appendChild(notificacion);
        
        // Auto-eliminar después de 5 segundos
        setTimeout(() => {
            if (notificacion.parentNode) {
                notificacion.remove();
            }
        }, 5000);
        
        // Cerrar al hacer clic
        notificacion.querySelector('.close').addEventListener('click', () => {
            notificacion.remove();
        });
        
        // Sonido de advertencia (opcional)
        playWarningSound();
    }
}

// Función para reproducir sonido de advertencia (opcional)
function playWarningSound() {
    try {
        // Crear un contexto de audio simple
        const audioContext = new (window.AudioContext || window.webkitAudioContext)();
        const oscillator = audioContext.createOscillator();
        const gainNode = audioContext.createGain();
        
        oscillator.connect(gainNode);
        gainNode.connect(audioContext.destination);
        
        oscillator.frequency.value = 800;
        oscillator.type = 'sine';
        
        gainNode.gain.setValueAtTime(0.1, audioContext.currentTime);
        gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.5);
        
        oscillator.start(audioContext.currentTime);
        oscillator.stop(audioContext.currentTime + 0.5);
    } catch (error) {
        console.log('Audio no disponible o bloqueado por el navegador');
    }
}

// Renderizar ofertas flash (actualizado para mostrar tiempo individual)
function renderOfertasFlash(productos) {
    const container = document.getElementById('flash-products');
    if (!container) return;
    
    if (productos.length > 0) {
        container.innerHTML = productos.map(producto => 
            createProductCard(producto, 'flash')
        ).join('');
        
        // Actualizar contadores individuales para cada producto
        productos.forEach((producto, index) => {
            if (producto.fecha_fin) {
                setupProductCountdown(producto, index);
            }
        });
    } else {
        container.innerHTML = createEmptyStateCard('flash', 'No hay ofertas flash activas', 'Vuelve más tarde para ver nuestras ofertas flash');
    }
    
    // Añadir eventos a los productos recién creados
    attachProductEvents(container);
}

// Configurar contador para producto individual
function setupProductCountdown(producto, index) {
    const fechaFin = new Date(producto.fecha_fin);
    const ahora = new Date();
    const tiempoRestante = fechaFin - ahora;
    
    if (tiempoRestante <= 0) return;
    
    // Buscar el elemento del producto
    const productCards = document.querySelectorAll('.product-card-flash');
    if (!productCards[index]) return;
    
    const priceContainer = productCards[index].querySelector('.product-price');
    if (!priceContainer) return;
    
    // Crear elemento para mostrar tiempo restante
    let timeElement = priceContainer.querySelector('.flash-time-remaining');
    if (!timeElement) {
        timeElement = document.createElement('small');
        timeElement.className = 'flash-time-remaining text-white d-block mt-1';
        priceContainer.appendChild(timeElement);
    }
    
    function updateProductCountdown() {
        const ahora = new Date();
        const tiempoRestante = fechaFin - ahora;
        
        if (tiempoRestante <= 0) {
            timeElement.textContent = 'Oferta terminada';
            timeElement.classList.remove('text-white');
            timeElement.classList.add('text-white');
            timeElement.style.opacity = '0.8';
            
            // Deshabilitar botón de agregar al carrito
            const addButton = productCards[index].querySelector('.add-to-cart-btn');
            if (addButton) {
                addButton.disabled = true;
                addButton.innerHTML = '<i class="fas fa-ban mr-1"></i> Terminada';
                addButton.classList.remove('btn-primary');
                addButton.classList.add('btn-secondary');
                addButton.style.opacity = '0.6';
            }
            
            return;
        }
        
        // Calcular tiempo restante
        const horas = Math.floor(tiempoRestante / (1000 * 60 * 60));
        const minutos = Math.floor((tiempoRestante % (1000 * 60 * 60)) / (1000 * 60));
        const segundos = Math.floor((tiempoRestante % (1000 * 60)) / 1000);
        
        // Formatear
        let tiempoFormateado;
        if (horas > 0) {
            tiempoFormateado = `${horas}h ${minutos}m`;
        } else if (minutos > 0) {
            tiempoFormateado = `${minutos}m ${segundos}s`;
        } else {
            tiempoFormateado = `${segundos}s`;
        }
        
        timeElement.textContent = `Termina en: ${tiempoFormateado}`;
        timeElement.style.color = '#ffffff';
        
        // Cambiar estilo según tiempo (solo peso de fuente, no color)
        if (horas < 1) {
            timeElement.style.fontWeight = 'bold';
        } else if (horas < 6) {
            timeElement.style.fontWeight = '600';
        } else {
            timeElement.style.fontWeight = 'normal';
        }
    }
    
    // Actualizar inmediatamente
    updateProductCountdown();
    
    // Actualizar cada segundo
    const intervalId = setInterval(updateProductCountdown, 1000);
    
    // Guardar referencia para limpiar después
    if (!window.productCountdownIntervals) {
        window.productCountdownIntervals = [];
    }
    window.productCountdownIntervals.push(intervalId);
}

// Renderizar super descuentos
function renderSuperDescuentos(productos) {
    const container = document.getElementById('super-discount-products');
    if (!container) return;
    
    if (productos.length > 0) {
        container.innerHTML = productos.map(producto => 
            createProductCard(producto, 'super')
        ).join('');
    } else {
        container.innerHTML = createEmptyStateCard('super', 'No hay super descuentos', 'Actualmente no hay productos con más del 50% de descuento');
    }
    
    attachProductEvents(container);
}

// Renderizar más vendidos
function renderMasVendidos(productos) {
    const container = document.getElementById('best-sellers-discount');
    if (!container) return;
    
    if (productos.length > 0) {
        container.innerHTML = productos.map(producto => 
            createProductCard(producto, 'best')
        ).join('');
    } else {
        container.innerHTML = createEmptyStateCard('best', 'No hay productos más vendidos en oferta', 'Vuelve más tarde para ver nuestras ofertas más populares');
    }
    
    attachProductEvents(container);
}

// Renderizar envío gratis
function renderEnvioGratis(productos) {
    const container = document.getElementById('free-shipping-products');
    if (!container) return;
    
    if (productos.length > 0) {
        container.innerHTML = productos.map(producto => 
            createProductCard(producto, 'free')
        ).join('');
    } else {
        container.innerHTML = createEmptyStateCard('free', 'No hay productos con envío gratis', 'Actualmente no hay productos con descuento y envío gratis');
    }
    
    attachProductEvents(container);
}

// Renderizar destacados
function renderDestacados(productos) {
    // Si necesitas un contenedor adicional para destacados, puedes crearlo
    // Por ahora, usaremos una sección existente o la crearemos dinámicamente
    let container = document.getElementById('destacados-container');
    if (!container) {
        // Crear sección dinámica para destacados
        const seccionDestacados = document.createElement('div');
        seccionDestacados.className = 'seccion-oferta';
        seccionDestacados.id = 'destacados';
        seccionDestacados.innerHTML = `
            <h2 class="mb-4"><i class="fas fa-star text-warning mr-2"></i> Destacados en Oferta</h2>
            <div class="row" id="destacados-container"></div>
        `;
        
        // Insertar después de la sección de bundles
        const bundlesSection = document.getElementById('bundles');
        if (bundlesSection && bundlesSection.parentNode) {
            bundlesSection.parentNode.insertBefore(seccionDestacados, bundlesSection.nextSibling);
        }
        container = document.getElementById('destacados-container');
    }
    
    if (productos.length > 0) {
        container.innerHTML = productos.map(producto => 
            createProductCard(producto, 'destacado')
        ).join('');
    } else {
        container.innerHTML = createEmptyStateCard('destacado', 'No hay productos destacados en oferta', 'Pronto tendremos productos destacados con descuento');
    }
    
    attachProductEvents(container);
}

// Renderizar bundles dinámicamente
function renderBundles(bundles) {
    const container = document.getElementById('bundles-container');
    if (!container) return;
    
    if (bundles.length > 0) {
        container.innerHTML = bundles.map(bundle => 
            createBundleCard(bundle)
        ).join('');
    } else {
        container.innerHTML = createEmptyStateCard('bundles', 'No hay combos disponibles', 'Pronto tendremos combos especiales con grandes descuentos');
    }
    
    // Añadir eventos a los bundles
    attachBundleEvents(container);
}

// Renderizar categorías con oferta - VERSIÓN COMPATIBLE CON TU HTML
function renderCategoriasOferta(categorias) {
    // console.log("Renderizando categorías:", categorias);
    
    // Buscar el contenedor específico de tu HTML
    const categoriaContainer = document.getElementById('categorias-container');
    const loadingState = categoriaContainer ? categoriaContainer.querySelector('.loading-state') : null;
    
    if (!categoriaContainer) {
        console.error("No se encontró el contenedor de categorías (id: categorias-container)");
        return;
    }
    
    // Ocultar estado de carga
    if (loadingState) {
        loadingState.style.display = 'none';
    }
    
    // Verificar si hay categorías
    if (categorias && categorias.length > 0) {
        // console.log(`Mostrando ${categorias.length} categorías`);
        
        // Renderizar tarjetas de categorías
        categoriaContainer.innerHTML = categorias.map(categoria => 
            createCategoriaCard(categoria)
        ).join('');
        
        // Añadir eventos a los botones de categorías
        attachCategoriaEvents();
        
    } else {
        console.log("No hay categorías para mostrar");
        // Mostrar mensaje de no hay categorías
        categoriaContainer.innerHTML = `
            <div class="col-12 text-center py-5">
                <i class="fas fa-tags fa-3x text-muted mb-3"></i>
                <h4 class="text-muted">No hay categorías en oferta</h4>
                <p class="text-muted">Pronto tendremos ofertas especiales por categoría</p>
            </div>
        `;
    }
}

// Crear tarjeta de producto para ofertas
function createProductCard(producto, tipo) {
    const descuento = parseInt(producto.descuento) || 0;
    const precioOriginal = parseFloat(producto.precio) || 0;
    const precioFinal = parseFloat(producto.precio_descuento) || precioOriginal;
    const productoId = producto.id || '';
    
    let badgeExtra = '';
    let tipoClass = '';
    let badgeClass = '';
    
    switch(tipo) {
        case 'flash':
            badgeExtra = '<span class="badge badge-danger badge-flash">FLASH</span>';
            tipoClass = 'product-card-flash';
            badgeClass = 'badge-flash';
            break;
        case 'super':
            badgeExtra = `<span class="badge badge-danger badge-super">-${descuento}%</span>`;
            tipoClass = 'product-card-super';
            badgeClass = 'badge-super';
            break;
        case 'destacado':
            badgeExtra = '<span class="badge badge-warning badge-destacado">Destacado</span>';
            tipoClass = 'product-card-destacado';
            badgeClass = 'badge-destacado';
            break;
        case 'best':
            badgeExtra = '<span class="badge badge-info">Más Vendido</span>';
            break;
        case 'free':
            badgeExtra = '<span class="badge badge-free-shipping">Envío Gratis</span>';
            break;
        default:
            if (descuento > 0) {
                badgeExtra = `<span class="badge badge-success">-${descuento}%</span>`;
            }
    }
    
    // Verificar si es nuevo
    const esNuevo = producto.es_nuevo == 1 || producto.es_flash_nuevo == 1;
    const badgeNuevo = esNuevo ? '<span class="badge badge-new">Nuevo</span>' : '';
    
    // Para ofertas flash, mostrar tiempo restante si está disponible
    const tiempoRestanteInfo = tipo === 'flash' && producto.fecha_fin ? 
        `<small class="text-danger d-block mt-1 flash-time-remaining">
            <i class="fas fa-clock mr-1"></i>
            Calculando tiempo restante...
        </small>` : '';
    
    return `
        <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
            <div class="card product-card ${tipoClass} h-100" data-product-id="${productoId}" data-fecha-fin="${producto.fecha_fin || ''}">
                <div class="product-image">
                    <img src="../Util/Img/Producto/${producto.imagen || 'producto_default.png'}" 
                         class="card-img-top" 
                         alt="${producto.producto}"
                         onerror="this.src='../Util/Img/Producto/producto_default.png'">
                    <div class="product-badges">
                        ${badgeExtra}
                        ${badgeNuevo}
                        ${producto.envio == 1 ? '<span class="badge badge-free-shipping">Envío Gratis</span>' : ''}
                    </div>
                    <div class="product-actions">
                        <button class="action-btn favorite-btn" title="Agregar a favoritos" data-id="${productoId}">
                            <i class="far fa-heart"></i>
                        </button>
                        <button class="action-btn quick-view-btn" title="Vista rápida" data-id="${productoId}">
                            <i class="far fa-eye"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body d-flex flex-column">
                    <div class="mb-2">
                        <small class="text-muted product-brand">${producto.marca || 'Marca'}</small>
                    </div>
                    <a href="descripcion.php?name=${encodeURIComponent(producto.producto)}&id=${productoId}" 
                       class="product-title text-dark mb-2">
                        ${truncateText(producto.producto, 50)}
                    </a>
                    
                    <div class="product-rating mb-3">
                        <div class="rating-stars d-inline-block">
                            ${generateStarRating(parseFloat(producto.calificacion) || 0)}
                        </div>
                        <small class="text-muted ml-1">(${producto.total_resenas || 0})</small>
                    </div>
                    
                    <div class="product-price mt-auto">
                        ${descuento > 0 ? `
                            <div class="d-flex align-items-center">
                                <span class="original-price text-muted text-decoration-line-through mr-2">
                                    $${precioOriginal.toFixed(2)}
                                </span>
                                <span class="discount-percent badge ${badgeClass}" style="color:white">
                                    -${descuento}%
                                </span>
                            </div>
                        ` : ''}
                        <div class="current-price h4 text-primary mt-1">
                            $${precioFinal.toFixed(2)}
                        </div>
                        ${tiempoRestanteInfo}
                    </div>
                    
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <span class="stock-status ${producto.stock > 0 ? 'text-success' : 'text-danger'}">
                            <i class="fas ${producto.stock > 0 ? 'fa-check-circle' : 'fa-times-circle'} mr-1"></i>
                            ${producto.stock > 0 ? 'En Stock' : 'Agotado'}
                        </span>
                        
                        <button class="btn ${producto.stock > 0 ? 'btn-primary' : 'btn-secondary'} add-to-cart-btn" 
                                data-id="${productoId}"
                                ${producto.stock <= 0 ? 'disabled' : ''}>
                            <i class="fas ${producto.stock > 0 ? 'fa-shopping-cart' : 'fa-ban'} mr-1"></i>
                            ${producto.stock > 0 ? 'Agregar' : 'Agotado'}
                        </button>
                    </div>
                    
                    ${producto.tienda ? `
                        <div class="product-store mt-2">
                            <small class="text-muted">
                                <i class="fas fa-store-alt mr-1"></i>
                                ${producto.tienda}
                            </small>
                        </div>
                    ` : ''}
                </div>
            </div>
        </div>
    `;
}

// Crear tarjeta de bundle
function createBundleCard(bundle) {
    // console.log("=== CREANDO TARJETA DE BUNDLE ===");
    // console.log("Bundle completo recibido:", bundle);
    const descuento = parseFloat(bundle.descuento_porcentaje) || 0;
    const precioOriginal = parseFloat(bundle.precio_original) || 0;
    const precioOferta = parseFloat(bundle.precio_oferta) || precioOriginal;
    const bundleIdEncrypted = bundle.id_encrypted || bundle.id;
    // console.log("ID para tarjeta:", bundleIdEncrypted);
    
    // Parsear nombres de productos
    const productosNombres = bundle.productos_nombres ? 
        bundle.productos_nombres.split('|').slice(0, 3).join(', ') + '...' : 
        'Varios productos';
    
    // Verificar fecha de expiración
    const fechaFin = bundle.fecha_fin ? new Date(bundle.fecha_fin) : null;
    const hoy = new Date();
    const diasRestantes = fechaFin ? Math.ceil((fechaFin - hoy) / (1000 * 60 * 60 * 24)) : null;
    const badgeExpiracion = diasRestantes !== null && diasRestantes <= 7 ? 
        `<span class="badge badge-warning">${diasRestantes}d restantes</span>` : '';
    
    // Obtener imagen del bundle - USAR EL CAMPO 'imagen' DEL BUNDLE
    let imagenBundle = bundle.imagen || 'bundle_default.png';
    
    // Si la imagen del bundle es null o está vacía, intentar usar la primera imagen de productos
    if (!imagenBundle || imagenBundle === 'bundle_default.png' || imagenBundle === 'producto_default.png') {
        if (bundle.productos_imagenes && bundle.productos_imagenes !== '') {
            const imagenes = bundle.productos_imagenes.split('|');
            if (imagenes.length > 0 && imagenes[0] && imagenes[0] !== '') {
                imagenBundle = imagenes[0];
            }
        }
    }
    
    // Ruta de la imagen (asumiendo que las imágenes están en ../Util/Img/Producto/)
    const rutaImagen = `../Util/Img/Producto/${imagenBundle}`;
    
    return `
        <div class="col-md-6 mb-4">
            <div class="bundle-card" data-bundle-id="${bundleIdEncrypted}">
                <!-- Imagen del bundle -->
                <div class="bundle-image mb-3" style="cursor: pointer;" onclick="viewBundleDetails('${bundleIdEncrypted}')">
                    <img src="${rutaImagen}" 
                         class="img-fluid rounded" 
                         alt="${bundle.nombre}"
                         onerror="this.src='../Util/Img/Producto/bundle_default.png'">
                    <div class="bundle-badges">
                        <span class="badge badge-success">Ahorra ${descuento}%</span>
                        ${badgeExpiracion}
                    </div>
                </div>
                
                <!-- Contenido del bundle -->
                <div class="bundle-content">
                    <h4 style="cursor: pointer;" onclick="viewBundleDetails('${bundleIdEncrypted}')">${bundle.nombre}</h4>
                    <p class="text-muted">${productosNombres}</p>
                    <p class="text-muted small">${bundle.descripcion || 'Combo especial con gran descuento'}</p>
                    
                    <!-- Precios -->
                    <div class="price-comparison mb-3">
                        <span class="old-price">$${precioOriginal.toFixed(2)}</span>
                        <span class="new-price">$${precioOferta.toFixed(2)}</span>
                    </div>
                    
                    <!-- Detalles adicionales -->
                    <div class="bundle-details mt-2">
                        <small class="text-muted">
                            <i class="fas fa-box mr-1"></i> ${bundle.total_productos || 0} productos
                            <span class="mx-2">•</span>
                            <i class="fas fa-store-alt mr-1"></i> ${bundle.tienda || 'Tienda'}
                        </small>
                    </div>
                    
                    <!-- Botones -->
                    <div class="d-flex justify-content-between mt-3">
                        <button class="btn btn-outline-primary btn-sm" onclick="viewBundleDetails('${bundleIdEncrypted}')">
                            <i class="fas fa-info-circle mr-1"></i> Ver detalles
                        </button>
                        
                        <button class="btn btn-danger btn-modern add-bundle-btn" 
                                data-id="${bundleIdEncrypted}"
                                ${bundle.stock <= 0 ? 'disabled' : ''}>
                            <i class="fas ${bundle.stock > 0 ? 'fa-shopping-cart' : 'fa-ban'} mr-2"></i>
                            ${bundle.stock > 0 ? 'Comprar Bundle' : 'Agotado'}
                        </button>
                    </div>
                    
                    <!-- Advertencia de stock bajo -->
                    ${bundle.stock <= 10 && bundle.stock > 0 ? `
                        <small class="text-warning d-block mt-2">
                            <i class="fas fa-exclamation-triangle mr-1"></i>
                            Solo quedan ${bundle.stock} disponibles
                        </small>
                    ` : ''}
                </div>
            </div>
        </div>
    `;
}

// Crear tarjeta de categoría - VERSIÓN COMPATIBLE
function createCategoriaCard(categoria) {
    // console.log("Creando card para categoría:", categoria);
    
    const categoriaId = categoria.id || '';
    const categoriaNombre = categoria.categoria || 'Categoría';
    const descuentoMaximo = categoria.descuento_maximo || '30';
    const totalProductos = categoria.total_productos_oferta || '0';
    const colorFondo = categoria.color_fondo || '#4361ee';
    
    // Manejar la imagen
    let imagenNombre = categoria.imagen_banner || categoriaNombre.toLowerCase();
    
    // Limpiar nombre de imagen
    imagenNombre = imagenNombre.replace(/[^a-zA-Z0-9-_\.]/g, '_');
    
    // Si no tiene extensión, asumir .jpg
    if (!imagenNombre.match(/\.(jpg|jpeg|png|gif|webp)$/i)) {
        imagenNombre += '.jpg';
    }
    
    const imagenPath = `../Util/Img/categorias/${imagenNombre}`;
    
    return `
        <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
            <div class="card categoria-oferta h-100 shadow-sm border-0" 
                 style="background: ${colorFondo}; overflow: hidden;">
                
                <!-- Imagen de la categoría -->
                <div class="categoria-imagen position-relative" style="height: 150px;">
                    <img src="${imagenPath}" 
                         class="card-img-top h-100 w-100" 
                         alt="${categoriaNombre}"
                         style="object-fit: cover;"
                         onerror="this.onerror=null; this.src='../Util/Img/categorias/default.jpg';">
                    
                    <!-- Badge de descuento -->
                    <div class="position-absolute top-0 end-0 m-2">
                        <span class="badge badge-warning p-2">
                            <i class="fas fa-percentage"></i> ${descuentoMaximo}% OFF
                        </span>
                    </div>
                </div>
                
                <!-- Contenido de la categoría -->
                <div class="card-body d-flex flex-column p-3" style="color: white;">
                    <h5 class="card-title mb-2 font-weight-bold">${categoriaNombre}</h5>
                    
                    <div class="categoria-info mb-3">
                        <small class="d-block">
                            <i class="fas fa-box mr-1"></i> ${totalProductos} productos
                        </small>
                        ${categoria.fecha_fin ? `
                            <small class="d-block mt-1">
                                <i class="fas fa-clock mr-1"></i> Válido hasta: ${formatFecha(categoria.fecha_fin)}
                            </small>
                        ` : ''}
                    </div>
                    
                    <!-- Botón para ver ofertas -->
                    <button class="btn btn-light btn-sm view-category-offers-btn mt-auto"
                            data-category-id="${categoriaId}"
                            data-category-name="${categoriaNombre}"
                            style="border-radius: 20px; font-weight: 600;">
                        <i class="fas fa-search mr-1"></i> Ver ofertas
                    </button>
                </div>
            </div>
        </div>
    `;
}

// Función auxiliar para formatear fechas
function formatFecha(fechaStr) {
    try {
        const fecha = new Date(fechaStr);
        return fecha.toLocaleDateString('es-ES', {
            day: 'numeric',
            month: 'short',
            year: 'numeric'
        });
    } catch (e) {
        return fechaStr;
    }
}

// Crear estado vacío para una sección
function createEmptyStateCard(tipo, titulo, mensaje) {
    const iconos = {
        'flash': 'fa-bolt',
        'super': 'fa-fire',
        'best': 'fa-chart-line',
        'free': 'fa-shipping-fast',
        'destacado': 'fa-star',
        'bundles': 'fa-box',
        'categorias': 'fa-tags'
    };
    
    const icono = iconos[tipo] || 'fa-tag';
    
    return `
        <div class="col-12">
            <div class="empty-state text-center py-5">
                <div class="empty-state-icon mb-3">
                    <i class="fas ${icono} fa-3x text-muted"></i>
                </div>
                <h4 class="empty-state-title text-muted">${titulo}</h4>
                <p class="empty-state-text text-muted">${mensaje}</p>
            </div>
        </div>
    `;
}

// Función para truncar texto
function truncateText(text, maxLength) {
    if (text.length <= maxLength) return text;
    return text.substr(0, maxLength) + '...';
}

// Función para generar estrellas de rating
function generateStarRating(rating) {
    let stars = "";
    const fullStars = Math.floor(rating);
    const hasHalfStar = rating % 1 !== 0;
    const emptyStars = 5 - Math.ceil(rating);

    for (let i = 0; i < fullStars; i++) {
        stars += '<i class="fas fa-star text-warning"></i>';
    }

    if (hasHalfStar) {
        stars += '<i class="fas fa-star-half-alt text-warning"></i>';
    }

    for (let i = 0; i < emptyStars; i++) {
        stars += '<i class="far fa-star text-warning"></i>';
    }

    return stars;
}

// Configurar filtros rápidos
function setupQuickFilters() {
    const filtros = document.querySelectorAll('.filtros-rapidos .btn');
    
    filtros.forEach(filtro => {
        filtro.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Remover clase activa de todos los filtros
            filtros.forEach(f => f.classList.remove('active'));
            
            // Agregar clase activa al filtro clickeado
            this.classList.add('active');
            
            // Obtener el target del filtro
            const targetId = this.getAttribute('href').substring(1);
            
            // Desplazarse a la sección correspondiente
            const targetSection = document.getElementById(targetId);
            if (targetSection) {
                targetSection.scrollIntoView({ behavior: 'smooth' });
                
                // Resaltar la sección
                targetSection.classList.add('highlighted');
                setTimeout(() => {
                    targetSection.classList.remove('highlighted');
                }, 2000);
            }
        });
    });
}

// Configurar suscripción - Versión mejorada
function setupSubscription() {
    const subscribeBtn = document.querySelector('.promo-banner .btn-warning');
    const emailInput = document.querySelector('.promo-banner input[type="email"]');
    const nameInput = document.querySelector('.promo-banner input[type="text"]');
    const frequencySelect = document.querySelector('.promo-banner select[name="frecuencia"]');
    
    if (!subscribeBtn || !emailInput) {
        console.log('Elementos de suscripción no encontrados');
        return;
    }
    
    // Verificar si ya está suscrito
    checkSubscriptionStatus();
    
    subscribeBtn.addEventListener('click', function(e) {
        e.preventDefault();
        
        const email = emailInput.value.trim();
        const nombre = nameInput ? nameInput.value.trim() : null;
        const frecuencia = frequencySelect ? frequencySelect.value : 'semanal';
        
        if (!isValidEmail(email)) {
            showNotification('error', 'Por favor ingresa un correo electrónico válido');
            emailInput.focus();
            emailInput.classList.add('is-invalid');
            return;
        }
        
        emailInput.classList.remove('is-invalid');
        
        // Mostrar animación de carga
        const originalText = this.innerHTML;
        const originalClass = this.className;
        
        this.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Procesando...';
        this.className = originalClass.replace('btn-warning', 'btn-secondary');
        this.disabled = true;
        
        // Enviar petición
        const formData = new FormData();
        formData.append('funcion', 'suscribir_email_ofertas');
        formData.append('email', email);
        
        if (nombre && nombre !== '') {
            formData.append('nombre', nombre);
        }
        
        if (frecuencia && frecuencia !== 'semanal') {
            formData.append('frecuencia', frecuencia);
        }
        
        fetch('../Controllers/OfertasController.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (data.requires_confirmation) {
                    // Mostrar mensaje de confirmación requerida
                    showConfirmationMessage(email, data.message);
                    
                    // Limpiar formulario
                    emailInput.value = '';
                    if (nameInput) nameInput.value = '';
                    if (frequencySelect) frequencySelect.value = 'semanal';
                } else {
                    // Suscripción inmediata (raro, pero por si acaso)
                    showNotification('success', data.message);
                    localStorage.setItem('ofertas_subscribed', email);
                    updateSubscriptionUI(true, email);
                }
            } else {
                if (data.pending) {
                    // Ya existe suscripción pendiente
                    showResendConfirmation(email, data.message);
                } else {
                    showNotification('error', data.message);
                }
            }
            
            // Restaurar botón
            this.innerHTML = originalText;
            this.className = originalClass;
            this.disabled = false;
        })
        .catch(error => {
            console.error('Error en suscripción:', error);
            showNotification('error', 'Error en la conexión');
            
            // Restaurar botón
            this.innerHTML = originalText;
            this.className = originalClass;
            this.disabled = false;
        });
    });
    
    // Validación en tiempo real
    emailInput.addEventListener('blur', validateEmail);
    emailInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') subscribeBtn.click();
    });
}

// Función para verificar estado de suscripción
function checkSubscriptionStatus() {
    const emailInput = document.querySelector('.promo-banner input[type="email"]');
    if (!emailInput) return;
    
    const email = emailInput.value.trim();
    if (!email || !isValidEmail(email)) return;
    
    const formData = new FormData();
    formData.append('funcion', 'verificar_suscripcion');
    formData.append('email', email);
    
    fetch('../Controllers/OfertasController.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.exists) {
            if (data.confirmed && data.active) {
                updateSubscriptionUI(true, email);
            } else if (!data.confirmed) {
                showResendConfirmation(email, 'Tienes una suscripción pendiente de confirmación.');
            }
        }
    })
    .catch(error => console.error('Error verificando suscripción:', error));
}

// Mostrar mensaje de confirmación requerida
function showConfirmationMessage(email, message) {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: '¡Revisa tu email!',
            html: `
                <div class="text-center">
                    <i class="fas fa-envelope fa-3x text-primary mb-3"></i>
                    <p>${message}</p>
                    <div class="alert alert-info mt-3">
                        <i class="fas fa-envelope-open-text mr-2"></i>
                        <small>Enviamos un email a <strong>${email}</strong></small>
                    </div>
                    <div class="alert alert-warning mt-2">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        <small>¿No ves el email? Revisa tu carpeta de spam.</small>
                    </div>
                </div>
            `,
            icon: 'info',
            confirmButtonText: 'Entendido',
            confirmButtonColor: '#4361ee',
            showCancelButton: true,
            cancelButtonText: 'Reenviar email'
        }).then((result) => {
            if (result.dismiss === Swal.DismissReason.cancel) {
                resendConfirmationEmail(email);
            }
        });
    } else {
        showNotification('info', `${message} Revisa tu email (y spam) para confirmar.`, 8000);
    }
}

// Ofrecer reenviar confirmación
function showResendConfirmation(email, message) {
    if (confirm(`${message}\n\n¿Deseas que reenviemos el email de confirmación a ${email}?`)) {
        resendConfirmationEmail(email);
    }
}

// Reenviar email de confirmación
function resendConfirmationEmail(email) {
    const formData = new FormData();
    formData.append('funcion', 'reenviar_confirmacion');
    formData.append('email', email);
    
    fetch('../Controllers/OfertasController.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('success', data.message);
        } else {
            showNotification('error', data.message);
        }
    })
    .catch(error => {
        console.error('Error reenviando confirmación:', error);
        showNotification('error', 'Error en la conexión');
    });
}

// Actualizar UI cuando ya está suscrito
function updateSubscriptionUI(isSubscribed, email) {
    const emailInput = document.querySelector('.promo-banner input[type="email"]');
    const subscribeBtn = document.querySelector('.promo-banner .btn-warning');
    const nameInput = document.querySelector('.promo-banner input[type="text"]');
    const frequencySelect = document.querySelector('.promo-banner select[name="frecuencia"]');
    
    if (isSubscribed && emailInput && subscribeBtn) {
        emailInput.value = email;
        emailInput.disabled = true;
        
        if (nameInput) nameInput.disabled = true;
        if (frequencySelect) frequencySelect.disabled = true;
        
        subscribeBtn.disabled = true;
        subscribeBtn.innerHTML = '<i class="fas fa-check mr-2"></i> ¡Ya estás suscrito!';
        subscribeBtn.classList.remove('btn-warning');
        subscribeBtn.classList.add('btn-success');
    }
}

// Validar email
function validateEmail() {
    const email = this.value.trim();
    if (email !== '' && !isValidEmail(email)) {
        this.classList.add('is-invalid');
        return false;
    } else {
        this.classList.remove('is-invalid');
        return true;
    }
}

// Función auxiliar para obtener texto descriptivo de frecuencia
function getFrecuenciaText(frecuencia) {
    switch(frecuencia) {
        case 'diaria':
            return 'todos los días';
        case 'mensual':
            return 'una vez al mes';
        case 'semanal':
        default:
            return 'una vez a la semana';
    }
}

// Validar email - Versión mejorada
function isValidEmail(email) {
    // Regex más robusta para validación de email
    const re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return re.test(String(email).toLowerCase());
}

// Función para mostrar notificaciones (ya existe, pero la mantengo como referencia)
function showNotification(type, message, duration = 3000) {
    // ... código existente de showNotification ...
    const notification = document.createElement('div');
    notification.className = `custom-notification alert alert-${type} alert-dismissible fade show position-fixed`;
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 9999;
        min-width: 300px;
        max-width: 400px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        border-radius: 8px;
    `;
    notification.innerHTML = `
        <div class="d-flex align-items-center">
            <i class="fas ${type === 'success' ? 'fa-check-circle' : 
                          type === 'error' ? 'fa-exclamation-circle' : 
                          type === 'warning' ? 'fa-exclamation-triangle' : 
                          'fa-info-circle'} mr-2"></i>
            <div style="flex: 1;">${message}</div>
            <button type="button" class="close ml-2" style="position: static;">
                <span>&times;</span>
            </button>
        </div>
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
        }
    }, duration);
    
    notification.querySelector('.close').addEventListener('click', () => {
        notification.remove();
    });
}

// Inicializar función de suscripción al cargar la página
document.addEventListener('DOMContentLoaded', function() {
    // Verificar si hay parámetro de suscripción exitosa en la URL
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('suscrito') && urlParams.get('suscrito') === 'true') {
        showNotification('success', '¡Suscripción confirmada exitosamente!', 5000);
        
        // Limpiar parámetro de la URL
        const newUrl = window.location.pathname;
        window.history.replaceState({}, document.title, newUrl);
    }
    
    // Configurar suscripción si existe el formulario
    const promoBanner = document.querySelector('.promo-banner');
    if (promoBanner) {
        setupSubscription();
    }
});

// Añadir eventos a productos recién cargados
function attachProductEvents(container) {
    if (!container) return;
    
    // Botones de favoritos
    container.querySelectorAll('.favorite-btn').forEach(btn => {
        const productId = btn.getAttribute('data-id');
        
        // Verificar estado inicial
        checkAndDisplayFavoriteStatus(productId, btn);
        
        // Añadir evento click
        btn.addEventListener('click', function() {
            const productId = this.getAttribute('data-id');
            toggleFavorite(productId, this);
        });
    });
    
    // Botones de vista rápida
    container.querySelectorAll('.quick-view-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const productId = this.getAttribute('data-id');
            showQuickView(productId);
        });
    });
    
    // Botones de agregar al carrito
    container.querySelectorAll('.add-to-cart-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const productId = this.getAttribute('data-id');
            const isDisabled = this.disabled;
            
            if (!isDisabled) {
                addToCart(productId, this);
            }
        });
    });
}

function updateFavoriteCount() {
    const formData = new FormData();
    formData.append('funcion', 'count_favorites');
    
    fetch('../Controllers/FavoritoController.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Buscar elementos que muestran el contador de favoritos
            const favoriteCountElements = document.querySelectorAll('.favorite-count, .badge-favorite');
            
            favoriteCountElements.forEach(element => {
                if (element.classList.contains('badge-favorite')) {
                    // Si es un badge, actualizar el texto
                    element.textContent = data.cantidad_total;
                    // Animar el cambio
                    element.classList.add('updated');
                    setTimeout(() => {
                        element.classList.remove('updated');
                    }, 300);
                } else if (element.classList.contains('favorite-count')) {
                    // Si es un elemento de contador
                    element.textContent = data.cantidad_total;
                }
            });
            
            // También puedes actualizar elementos específicos del header si existen
            const headerFavoriteCount = document.querySelector('#favorite-count-header');
            if (headerFavoriteCount) {
                headerFavoriteCount.textContent = data.cantidad_total;
            }
        }
    })
    .catch(error => {
        console.error('Error actualizando contador de favoritos:', error);
    });
}

// Añadir eventos a bundles
function attachBundleEvents(container) {
    container.querySelectorAll('.add-bundle-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const bundleId = this.getAttribute('data-id');
            const isDisabled = this.disabled;
            
            if (!isDisabled) {
                addBundleToCart(bundleId, this);
            }
        });
    });
    
    // Añadir evento para ver detalles al hacer clic en la imagen
    container.querySelectorAll('.bundle-image').forEach(img => {
        img.addEventListener('click', function() {
            const bundleCard = this.closest('.bundle-card');
            const bundleId = bundleCard.getAttribute('data-bundle-id');
            if (bundleId) {
                viewBundleDetails(bundleId);
            }
        });
    });
    
    // Añadir evento para ver detalles al hacer clic en el título
    container.querySelectorAll('.bundle-content h4').forEach(title => {
        title.addEventListener('click', function() {
            const bundleCard = this.closest('.bundle-card');
            const bundleId = bundleCard.getAttribute('data-bundle-id');
            if (bundleId) {
                viewBundleDetails(bundleId);
            }
        });
    });
}

// Añadir eventos a las categorías
function attachCategoriaEvents() {
    // Evento para botones de ver ofertas
    document.querySelectorAll('.view-category-offers-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            
            const categoryId = this.getAttribute('data-category-id');
            const categoryName = this.getAttribute('data-category-name');
            
            console.log(`Navegando a ofertas de: ${categoryName}`);
            
            // Redirigir a la página de productos filtrada por categoría
            if (categoryId && categoryId !== 'null' && categoryId !== 'undefined') {
                window.location.href = `producto.php?categoria=${encodeURIComponent(categoryName)}&id_categoria=${encodeURIComponent(categoryId)}&filtro=ofertas`;
            } else {
                window.location.href = `producto.php?categoria=${encodeURIComponent(categoryName)}&filtro=ofertas`;
            }
        });
    });
    
    // Efecto hover en las tarjetas
    document.querySelectorAll('.categoria-oferta').forEach(card => {
        card.style.transition = 'transform 0.3s ease, box-shadow 0.3s ease';
        
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-8px)';
            this.style.boxShadow = '0 10px 25px rgba(0,0,0,0.2)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = '0 2px 10px rgba(0,0,0,0.1)';
        });
        
        // Click en toda la tarjeta (opcional)
        card.addEventListener('click', function(e) {
            if (!e.target.closest('.view-category-offers-btn')) {
                const btn = this.querySelector('.view-category-offers-btn');
                if (btn) btn.click();
            }
        });
    });
}

// Configurar scroll suave
function setupSmoothScrolling() {
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            const targetId = this.getAttribute('href');
            if (targetId === '#') return;
            
            const targetElement = document.querySelector(targetId);
            if (targetElement) {
                e.preventDefault();
                targetElement.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
}

// Alternar favorito
function toggleFavorite(productId, button) {
    if (!productId) {
        showNotification('error', 'ID de producto no válido');
        return;
    }
    
    const heartIcon = button.querySelector('i');
    const isCurrentlyFavorite = heartIcon.classList.contains('fas');
    
    // Cambiar visualmente de inmediato para mejor experiencia de usuario
    if (isCurrentlyFavorite) {
        heartIcon.classList.remove('fas', 'text-danger');
        heartIcon.classList.add('far');
    } else {
        heartIcon.classList.remove('far');
        heartIcon.classList.add('fas', 'text-danger');
    }
    
    // Hacer petición al servidor
    const formData = new FormData();
    formData.append('funcion', isCurrentlyFavorite ? 'eliminar_favorito' : 'agregar_favorito');
    formData.append('id_producto_tienda', productId);
    
    fetch('../Controllers/FavoritoController.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (!isCurrentlyFavorite) {
                showNotification('success', 'Producto agregado a favoritos');
                button.title = 'Quitar de favoritos';
            } else {
                showNotification('info', 'Producto eliminado de favoritos');
                button.title = 'Agregar a favoritos';
            }
            
            // Actualizar contador de favoritos si existe
            updateFavoriteCount();
            
        } else if (data.error === 'no_sesion') {
            // Si no hay sesión, revertir cambio visual
            if (isCurrentlyFavorite) {
                heartIcon.classList.remove('far');
                heartIcon.classList.add('fas', 'text-danger');
            } else {
                heartIcon.classList.remove('fas', 'text-danger');
                heartIcon.classList.add('far');
            }
            
            showNotification('warning', 'Debes iniciar sesión para usar favoritos');
            
            // Redirigir a login después de un tiempo
            setTimeout(() => {
                window.location.href = 'login.php?redirect=' + encodeURIComponent(window.location.href);
            }, 2000);
            
        } else {
            // Error del servidor, revertir cambio visual
            if (isCurrentlyFavorite) {
                heartIcon.classList.remove('far');
                heartIcon.classList.add('fas', 'text-danger');
            } else {
                heartIcon.classList.remove('fas', 'text-danger');
                heartIcon.classList.add('far');
            }
            
            showNotification('error', data.error || 'Error al procesar favorito');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        
        // Revertir cambio visual en caso de error de red
        if (isCurrentlyFavorite) {
            heartIcon.classList.remove('far');
            heartIcon.classList.add('fas', 'text-danger');
        } else {
            heartIcon.classList.remove('fas', 'text-danger');
            heartIcon.classList.add('far');
        }
        
        showNotification('error', 'Error en la conexión');
    });
}

function checkAndDisplayFavoriteStatus(productId, favoriteBtn) {
    if (!productId || !favoriteBtn) return;
    
    const formData = new FormData();
    formData.append('funcion', 'verificar_favorito');
    formData.append('id_producto_tienda', productId);
    
    fetch('../Controllers/FavoritoController.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        const heartIcon = favoriteBtn.querySelector('i');
        
        if (data.es_favorito) {
            heartIcon.classList.remove('far');
            heartIcon.classList.add('fas', 'text-danger');
            favoriteBtn.title = 'Quitar de favoritos';
        } else {
            heartIcon.classList.remove('fas', 'text-danger');
            heartIcon.classList.add('far');
            favoriteBtn.title = 'Agregar a favoritos';
        }
    })
    .catch(error => {
        console.error('Error verificando favorito:', error);
    });
}

// Mostrar vista rápida
function showQuickView(productId) {
    if (!productId) {
        showNotification('error', 'ID de producto no válido');
        return;
    }
    
    // Procesar el ID (reemplazar espacios por + si es necesario)
    const processedId = productId.replace(/ /g, '+');
    
    // Mostrar cargando
    const quickViewLoader = document.createElement('div');
    quickViewLoader.className = 'modal-backdrop fade show d-flex align-items-center justify-content-center';
    quickViewLoader.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 1040;
        background-color: rgba(0,0,0,0.5);
    `;
    quickViewLoader.innerHTML = `
        <div class="spinner-border text-light" role="status">
            <span class="sr-only">Cargando...</span>
        </div>
    `;
    document.body.appendChild(quickViewLoader);
    
    const formData = new FormData();
    formData.append('funcion', 'obtener_producto_rapido');
    formData.append('id_producto_tienda', processedId);
    
    fetch('../Controllers/ProductoTiendaController.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Error en la respuesta del servidor');
        }
        return response.json();
    })
    .then(data => {
        // Remover cargando
        if (quickViewLoader.parentNode) {
            quickViewLoader.remove();
        }
        
        if (data.success && data.producto) {
            // Mostrar modal de vista rápida
            showQuickViewModal(data.producto);
        } else {
            showNotification('error', data.error || 'No se pudo cargar el producto');
        }
    })
    .catch(error => {
        // Remover cargando
        if (quickViewLoader.parentNode) {
            quickViewLoader.remove();
        }
        
        console.error('Error en quickView:', error);
        showNotification('error', 'Error al cargar el producto');
    });
}

// Mostrar modal de vista rápida para ofertas
function showQuickViewModal(producto) {
    // Verificar si ya hay un modal abierto
    const existingModal = document.getElementById('quickViewModalOfertas');
    if (existingModal) {
        existingModal.remove();
    }
    
    const discount = parseInt(producto.descuento) || 0;
    const originalPrice = parseFloat(producto.precio) || 0;
    const finalPrice = parseFloat(producto.precio_descuento) || originalPrice;
    const hasDiscount = discount > 0;
    const isNew = producto.es_nuevo == 1 || producto.es_flash_nuevo == 1;
    const isFlash = producto.es_flash == 1;
    
    // Determinar si hay tiempo restante para ofertas flash
    let tiempoRestanteHTML = '';
    if (isFlash && producto.fecha_fin) {
        const fechaFin = new Date(producto.fecha_fin);
        const ahora = new Date();
        const tiempoRestante = fechaFin - ahora;
        
        if (tiempoRestante > 0) {
            const horas = Math.floor(tiempoRestante / (1000 * 60 * 60));
            const minutos = Math.floor((tiempoRestante % (1000 * 60 * 60)) / (1000 * 60));
            const segundos = Math.floor((tiempoRestante % (1000 * 60)) / 1000);
            
            let tiempoFormateado;
            if (horas > 0) {
                tiempoFormateado = `${horas}h ${minutos}m ${segundos}s`;
            } else if (minutos > 0) {
                tiempoFormateado = `${minutos}m ${segundos}s`;
            } else {
                tiempoFormateado = `${segundos}s`;
            }
            
            tiempoRestanteHTML = `
                <div class="alert alert-danger mt-3">
                    <i class="fas fa-clock mr-2"></i>
                    <strong>¡Oferta Flash!</strong> Termina en: <span class="font-weight-bold">${tiempoFormateado}</span>
                </div>
            `;
        }
    }
    
    const modalHTML = `
        <div class="modal fade show" id="quickViewModalOfertas" tabindex="-1" role="dialog" aria-labelledby="quickViewModalLabel" style="display: block; padding-right: 17px;">
            <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title" id="quickViewModalLabel">
                            <i class="fas fa-eye mr-2"></i> Vista Rápida
                        </h5>
                        <button type="button" class="close text-white" onclick="closeQuickViewOfertas()" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body p-0">
                        <div class="row no-gutters">
                            <!-- Imagen del producto -->
                            <div class="col-md-6">
                                <div class="p-4">
                                    <div class="product-image-quickview">
                                        <img src="../Util/Img/Producto/${producto.imagen || 'producto_default.png'}" 
                                             class="img-fluid rounded" 
                                             alt="${producto.producto}"
                                             onerror="this.src='../Util/Img/Producto/producto_default.png'">
                                        <div class="product-badges-quickview mt-3">
                                            ${hasDiscount ? 
                                                `<span class="badge badge-danger mr-2">-${discount}%</span>` : ''}
                                            ${isFlash ? 
                                                `<span class="badge badge-warning mr-2">FLASH</span>` : ''}
                                            ${isNew ? 
                                                `<span class="badge badge-success mr-2">Nuevo</span>` : ''}
                                            ${producto.envio == 1 ? 
                                                `<span class="badge badge-info">Envío Gratis</span>` : ''}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Detalles del producto -->
                            <div class="col-md-6">
                                <div class="p-4">
                                    <!-- Marca -->
                                    <div class="mb-2">
                                        <small class="text-muted">${producto.marca || 'Marca'}</small>
                                    </div>
                                    
                                    <!-- Nombre del producto -->
                                    <h4 class="product-title-quickview mb-3">${producto.producto}</h4>
                                    
                                    <!-- Calificación -->
                                    <div class="product-rating-quickview mb-3">
                                        <div class="rating-stars d-inline-block">
                                            ${generateStarRating(parseFloat(producto.calificacion) || 0)}
                                        </div>
                                        <small class="text-muted ml-2">(${producto.total_resenas || 0} reseñas)</small>
                                    </div>
                                    
                                    <!-- Precio -->
                                    <div class="product-price-quickview mb-4">
                                        ${hasDiscount ? `
                                            <div class="d-flex align-items-center mb-1">
                                                <span class="original-price text-muted text-decoration-line-through mr-3">
                                                    $${originalPrice.toFixed(2)}
                                                </span>
                                                <span class="discount-percent badge badge-success" style="color:white">
                                                    -${discount}% OFF
                                                </span>
                                            </div>
                                        ` : ''}
                                        <div class="current-price h3 text-primary">
                                            $${finalPrice.toFixed(2)}
                                        </div>
                                        ${tiempoRestanteHTML}
                                    </div>
                                    
                                    <!-- Descripción -->
                                    <div class="product-description-quickview mb-4">
                                        <p class="text-muted">${producto.detalles || 'Descripción del producto no disponible.'}</p>
                                    </div>
                                    
                                    <!-- Stock -->
                                    <div class="stock-status-quickview mb-4">
                                        <span class="badge ${producto.stock > 0 ? 'badge-success' : 'badge-danger'}">
                                            <i class="fas ${producto.stock > 0 ? 'fa-check-circle' : 'fa-times-circle'} mr-1"></i>
                                            ${producto.stock > 0 ? 'En Stock' : 'Agotado'}
                                        </span>
                                        <small class="text-muted ml-2">${producto.stock > 0 ? `${producto.stock} unidades disponibles` : 'Producto agotado'}</small>
                                    </div>
                                    
                                    <!-- Tienda -->
                                    ${producto.tienda ? `
                                        <div class="product-store-quickview mb-4">
                                            <small class="text-muted">
                                                <i class="fas fa-store-alt mr-1"></i>
                                                Tienda: ${producto.tienda}
                                            </small>
                                        </div>
                                    ` : ''}
                                    
                                    <!-- Acciones -->
                                    <div class="product-actions-quickview">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <!-- Cantidad -->
                                            <div class="quantity-selector" style="min-width: 120px;">
                                                <label class="sr-only">Cantidad</label>
                                                <div class="input-group">
                                                    <div class="input-group-prepend">
                                                        <button class="btn btn-outline-secondary" type="button" onclick="adjustQuantityOfertas(-1)">
                                                            <i class="fas fa-minus"></i>
                                                        </button>
                                                    </div>
                                                    <input type="number" 
                                                           id="quickViewQuantityOfertas" 
                                                           class="form-control text-center" 
                                                           value="1" 
                                                           min="1" 
                                                           max="${producto.stock || 1}"
                                                           style="width: 50px;">
                                                    <div class="input-group-append">
                                                        <button class="btn btn-outline-secondary" type="button" onclick="adjustQuantityOfertas(1)">
                                                            <i class="fas fa-plus"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <!-- Botones de acción -->
                                            <div class="d-flex">
                                                <!-- Botón de favoritos -->
                                                <button class="btn btn-outline-danger btn-favorite-quickview mr-2" 
                                                        id="quickViewFavoriteBtn"
                                                        data-id="${producto.id}"
                                                        title="Agregar a favoritos">
                                                    <i class="far fa-heart"></i>
                                                </button>
                                                
                                                <!-- Botón de agregar al carrito -->
                                                <button class="btn btn-primary btn-add-to-cart-quickview"
                                                        onclick="addToCartFromQuickViewOfertas('${producto.id}', ${producto.stock > 0})"
                                                        ${producto.stock <= 0 ? 'disabled' : ''}>
                                                    <i class="fas fa-shopping-cart mr-2"></i>
                                                    Agregar
                                                </button>
                                            </div>
                                        </div>
                                        
                                        <!-- Botón para ver detalles completos -->
                                        <div class="mt-3">
                                            <a href="descripcion.php?name=${encodeURIComponent(producto.producto)}&id=${producto.id}" 
                                               class="btn btn-outline-primary btn-block">
                                                <i class="fas fa-external-link-alt mr-2"></i>
                                                Ver detalles completos
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-backdrop fade show"></div>
    `;
    
    // Insertar el modal
    const modalContainer = document.createElement('div');
    modalContainer.innerHTML = modalHTML;
    document.body.appendChild(modalContainer);
    
    // Prevenir scroll del body
    document.body.style.overflow = 'hidden';
    
    // Configurar eventos del modal
    setupQuickViewEvents(producto.id);
    
    // Verificar estado de favorito
    checkQuickViewFavoriteStatus(producto.id);
    
    // Iniciar contador si es oferta flash
    if (isFlash && producto.fecha_fin) {
        startQuickViewCountdown(producto.fecha_fin);
    }
}

// Configurar eventos del modal de vista rápida
function setupQuickViewEvents(productId) {
    // Configurar botón de favoritos
    const favoriteBtn = document.getElementById('quickViewFavoriteBtn');
    if (favoriteBtn) {
        favoriteBtn.addEventListener('click', function() {
            const productId = this.getAttribute('data-id');
            toggleFavorite(productId, this);
        });
    }
    
    // Cerrar modal al hacer clic fuera
    const backdrop = document.querySelector('.modal-backdrop');
    if (backdrop) {
        backdrop.addEventListener('click', closeQuickViewOfertas);
    }
    
    // Manejar tecla ESC
    document.addEventListener('keydown', handleQuickViewKeydown);
}

// Verificar estado de favorito para el modal
function checkQuickViewFavoriteStatus(productId) {
    if (!productId) return;
    
    const formData = new FormData();
    formData.append('funcion', 'verificar_favorito');
    formData.append('id_producto_tienda', productId);
    
    fetch('../Controllers/FavoritoController.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        const favoriteBtn = document.getElementById('quickViewFavoriteBtn');
        if (favoriteBtn && data.es_favorito !== undefined) {
            const heartIcon = favoriteBtn.querySelector('i');
            if (data.es_favorito) {
                heartIcon.classList.remove('far');
                heartIcon.classList.add('fas');
                favoriteBtn.title = 'Quitar de favoritos';
            } else {
                heartIcon.classList.remove('fas');
                heartIcon.classList.add('far');
                favoriteBtn.title = 'Agregar a favoritos';
            }
        }
    })
    .catch(error => {
        console.error('Error verificando favorito en quick view:', error);
    });
}

// Ajustar cantidad en el modal
function adjustQuantityOfertas(change) {
    const quantityInput = document.getElementById('quickViewQuantityOfertas');
    if (!quantityInput) return;
    
    let currentValue = parseInt(quantityInput.value) || 1;
    const maxStock = parseInt(quantityInput.max) || 999;
    
    currentValue += change;
    if (currentValue < 1) currentValue = 1;
    if (currentValue > maxStock) currentValue = maxStock;
    
    quantityInput.value = currentValue;
}

// Agregar al carrito desde vista rápida
function addToCartFromQuickViewOfertas(productId, hasStock) {
    if (!hasStock) {
        showNotification('error', 'Producto agotado');
        return;
    }
    
    const quantityInput = document.getElementById('quickViewQuantityOfertas');
    const quantity = quantityInput ? parseInt(quantityInput.value) || 1 : 1;
    
    // Cerrar el modal primero
    closeQuickViewOfertas();
    
    // Luego agregar al carrito
    addToCart(productId, quantity);
}

// Cerrar vista rápida
function closeQuickViewOfertas() {
    const modal = document.getElementById('quickViewModalOfertas');
    const backdrop = document.querySelector('.modal-backdrop');
    
    if (modal) {
        modal.remove();
    }
    if (backdrop) {
        backdrop.remove();
    }
    
    // Restaurar scroll del body
    document.body.style.overflow = 'auto';
    
    // Remover listener de tecla ESC
    document.removeEventListener('keydown', handleQuickViewKeydown);
}

// Manejar tecla ESC
function handleQuickViewKeydown(e) {
    if (e.key === 'Escape') {
        closeQuickViewOfertas();
    }
}

// Iniciar contador regresivo para ofertas flash en vista rápida
function startQuickViewCountdown(fechaFinStr) {
    const fechaFin = new Date(fechaFinStr);
    const ahora = new Date();
    const tiempoRestante = fechaFin - ahora;
    
    if (tiempoRestante <= 0) return;
    
    function updateQuickViewCountdown() {
        const ahora = new Date();
        const tiempoRestante = fechaFin - ahora;
        
        if (tiempoRestante <= 0) {
            // Oferta terminada
            const alertElement = document.querySelector('.modal-body .alert-danger');
            if (alertElement) {
                alertElement.innerHTML = `
                    <i class="fas fa-ban mr-2"></i>
                    <strong>¡Oferta Flash Terminada!</strong>
                `;
                alertElement.classList.remove('alert-danger');
                alertElement.classList.add('alert-secondary');
            }
            
            // Deshabilitar botón de agregar al carrito
            const addButton = document.querySelector('.btn-add-to-cart-quickview');
            if (addButton) {
                addButton.disabled = true;
                addButton.innerHTML = '<i class="fas fa-ban mr-2"></i> Oferta Terminada';
                addButton.classList.remove('btn-primary');
                addButton.classList.add('btn-secondary');
            }
            
            return;
        }
        
        // Calcular tiempo restante
        const horas = Math.floor(tiempoRestante / (1000 * 60 * 60));
        const minutos = Math.floor((tiempoRestante % (1000 * 60 * 60)) / (1000 * 60));
        const segundos = Math.floor((tiempoRestante % (1000 * 60)) / 1000);
        
        // Formatear tiempo
        let tiempoFormateado;
        if (horas > 0) {
            tiempoFormateado = `${horas.toString().padStart(2, '0')}h ${minutos.toString().padStart(2, '0')}m ${segundos.toString().padStart(2, '0')}s`;
        } else if (minutos > 0) {
            tiempoFormateado = `${minutos.toString().padStart(2, '0')}m ${segundos.toString().padStart(2, '0')}s`;
        } else {
            tiempoFormateado = `${segundos.toString().padStart(2, '0')}s`;
        }
        
        // Actualizar alerta
        const alertElement = document.querySelector('.modal-body .alert-danger span.font-weight-bold');
        if (alertElement) {
            alertElement.textContent = tiempoFormateado;
        }
    }
    
    // Actualizar inmediatamente
    updateQuickViewCountdown();
    
    // Actualizar cada segundo
    const intervalId = setInterval(updateQuickViewCountdown, 1000);
    
    // Guardar referencia para limpiar al cerrar
    if (!window.quickViewCountdownInterval) {
        window.quickViewCountdownInterval = intervalId;
        
        // Limpiar al cerrar el modal
        const modal = document.getElementById('quickViewModalOfertas');
        if (modal) {
            const observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (mutation.removedNodes.length > 0) {
                        const removedNodes = Array.from(mutation.removedNodes);
                        if (removedNodes.some(node => node.id === 'quickViewModalOfertas')) {
                            clearInterval(window.quickViewCountdownInterval);
                            window.quickViewCountdownInterval = null;
                            observer.disconnect();
                        }
                    }
                });
            });
            
            observer.observe(document.body, { childList: true });
        }
    }
}

// AGREGAR PRODUCTO AL CARRITO - Actualizada para usar el CarritoController
function addToCart(productId, button) {
    if (!productId) {
        showNotification('error', 'ID de producto no válido');
        return;
    }
    
    const originalText = button.innerHTML;
    const originalClass = button.className;
    
    // Cambiar visualmente el botón
    button.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Agregando...';
    button.className = originalClass.replace('btn-primary', 'btn-secondary');
    button.disabled = true;
    
    // Hacer petición al CarritoController
    const formData = new FormData();
    formData.append('funcion', 'agregar_al_carrito');
    formData.append('id_producto_tienda', productId);
    formData.append('cantidad', 1);
    
    fetch('../Controllers/CarritoController.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('success', data.mensaje || 'Producto agregado al carrito');
            
            // Actualizar contador del carrito
            if (data.cantidad_total !== undefined) {
                updateCartCount(data.cantidad_total);
            } else {
                updateCartCount();
            }
            
            // Animación de éxito
            button.innerHTML = '<i class="fas fa-check mr-1"></i> ¡Agregado!';
            button.className = originalClass.replace('btn-primary', 'btn-success');
            
            setTimeout(() => {
                button.innerHTML = originalText;
                button.className = originalClass;
                button.disabled = false;
            }, 1500);
            
        } else if (data.error === 'no_sesion') {
            showNotification('warning', 'Debes iniciar sesión para agregar productos al carrito');
            // Redirigir a login después de un tiempo
            setTimeout(() => {
                window.location.href = 'login.php?redirect=' + encodeURIComponent(window.location.href);
            }, 2000);
            
            // Restaurar botón
            button.innerHTML = originalText;
            button.className = originalClass;
            button.disabled = false;
        } else {
            showNotification('error', data.error || 'Error al agregar al carrito');
            
            // Restaurar botón
            button.innerHTML = originalText;
            button.className = originalClass;
            button.disabled = false;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('error', 'Error en la conexión');
        
        // Restaurar botón
        button.innerHTML = originalText;
        button.className = originalClass;
        button.disabled = false;
    });
}

// AGREGAR BUNDLE AL CARRITO - Función completa y corregida
function addBundleToCart(bundleIdEncrypted, button) {
    if (!bundleIdEncrypted) {
        showNotification('error', 'ID de bundle no válido');
        return;
    }
    
    const originalText = button.innerHTML;
    const originalClass = button.className;
    const bundleCard = button.closest('.bundle-card');
    const bundleTitle = bundleCard.querySelector('h4').textContent;
    
    // console.log("=== AGREGANDO BUNDLE AL CARRITO ===");
    // console.log("Bundle ID encriptado:", bundleIdEncrypted);
    // console.log("Bundle título:", bundleTitle);
    
    // Cambiar visualmente el botón
    button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Procesando...';
    button.className = originalClass.replace('btn-danger', 'btn-secondary');
    button.disabled = true;
    
    // Verificar sesión primero
    const formDataSession = new FormData();
    formDataSession.append('funcion', 'obtener_cantidad_total');
    
    fetch('../Controllers/CarritoController.php', {
        method: 'POST',
        body: formDataSession
    })
    .then(response => response.json())
    .then(data => {
        if (data.error === 'no_sesion') {
            // Usuario no tiene sesión
            showNotification('warning', 'Debes iniciar sesión para agregar bundles al carrito');
            button.innerHTML = originalText;
            button.className = originalClass;
            button.disabled = false;
            
            setTimeout(() => {
                window.location.href = 'login.php?redirect=' + encodeURIComponent(window.location.href);
            }, 2000);
            return;
        }
        
        // Usuario tiene sesión, proceder con el bundle
        processBundleAdd(bundleIdEncrypted, bundleTitle, button, originalText, originalClass);
    })
    .catch(error => {
        console.error('Error verificando sesión:', error);
        showNotification('error', 'Error al verificar sesión');
        button.innerHTML = originalText;
        button.className = originalClass;
        button.disabled = false;
    });
}

// Función para procesar la adición del bundle
function processBundleAdd(bundleIdEncrypted, bundleTitle, button, originalText, originalClass) {
    // console.log("Procesando bundle ID encriptado:", bundleIdEncrypted);
    
    // Mostrar confirmación con SweetAlert2
    Swal.fire({
        title: `Agregar "${bundleTitle}"`,
        html: `
            <div class="text-left">
                <p>¿Deseas agregar este bundle a tu carrito?</p>
                <p><small class="text-muted">Se agregarán todos los productos incluidos en el bundle.</small></p>
            </div>
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#4361ee',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, agregar bundle',
        cancelButtonText: 'Cancelar',
        showLoaderOnConfirm: true,
        preConfirm: () => {
            // Agregar bundle al carrito
            const formDataAdd = new FormData();
            formDataAdd.append('funcion', 'agregar_bundle_carrito');
            formDataAdd.append('id_bundle_encrypted', bundleIdEncrypted); // ← ENVIAR ENCRIPTADO
            
            // console.log("Enviando petición para agregar bundle...");
            
            return fetch('../Controllers/CarritoController.php', {
                method: 'POST',
                body: formDataAdd
            })
            .then(response => {
                // console.log("Respuesta recibida, status:", response.status);
                return response.json();
            })
            .then(data => {
                // console.log("Datos recibidos:", data);
                if (!data.success) {
                    throw new Error(data.error || 'Error al agregar el bundle');
                }
                return data;
            });
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const data = result.value;
            
            // console.log("Bundle agregado exitosamente:", data);
            
            // Mostrar notificación de éxito
            showNotification('success', data.mensaje || '¡Bundle agregado al carrito!');
            
            // Actualizar contador del carrito
            if (data.cantidad_total !== undefined) {
                updateCartCount(data.cantidad_total);
            } else {
                updateCartCount();
            }
            
            // Animación de éxito en el botón
            button.innerHTML = '<i class="fas fa-check mr-2"></i> ¡Agregado!';
            button.className = originalClass.replace('btn-danger', 'btn-success');
            
            // Mostrar detalles si hubo errores parciales
            if (data.errores && data.errores.length > 0) {
                setTimeout(() => {
                    Swal.fire({
                        title: 'Advertencia',
                        html: `El bundle fue agregado parcialmente:<br><br>
                               <strong>Agregados:</strong> ${data.productos_agregados}/${data.total_productos}<br>
                               <strong>Errores:</strong> ${data.errores.join('<br>')}`,
                        icon: 'warning',
                        confirmButtonText: 'Entendido'
                    });
                }, 500);
            }
            
            // Restaurar botón después de 2 segundos
            setTimeout(() => {
                button.innerHTML = originalText;
                button.className = originalClass;
                button.disabled = false;
            }, 2000);
            
        } else {
            // Restaurar botón si cancela
            button.innerHTML = originalText;
            button.className = originalClass;
            button.disabled = false;
        }
    }).catch(error => {
        console.error('Error en processBundleAdd:', error);
        showNotification('error', error.message || 'Error al agregar el bundle');
        
        // Restaurar botón
        button.innerHTML = originalText;
        button.className = originalClass;
        button.disabled = false;
    });
}

// Función alternativa para agregar bundle directamente
function addBundleDirectly(bundleId, bundleTitle, button, originalText, originalClass) {
    Swal.fire({
        title: `Agregar "${bundleTitle}"`,
        text: '¿Deseas agregar este bundle a tu carrito?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#4361ee',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, agregar',
        cancelButtonText: 'Cancelar',
        showLoaderOnConfirm: true,
        preConfirm: () => {
            const formData = new FormData();
            formData.append('funcion', 'agregar_bundle_carrito');
            formData.append('bundle_id', bundleId);
            
            return fetch('../Controllers/CarritoController.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    throw new Error(data.error || 'Error al agregar el bundle');
                }
                return data;
            });
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const data = result.value;
            
            showNotification('success', data.mensaje || '¡Bundle agregado al carrito!');
            
            // Actualizar contador del carrito
            if (data.cantidad_total !== undefined) {
                updateCartCount(data.cantidad_total);
            } else {
                updateCartCount();
            }
            
            // Animación de éxito
            button.innerHTML = '<i class="fas fa-check mr-2"></i> ¡Agregado!';
            button.className = originalClass.replace('btn-danger', 'btn-success');
            
            setTimeout(() => {
                button.innerHTML = originalText;
                button.className = originalClass;
                button.disabled = false;
            }, 2000);
            
        } else {
            // Restaurar botón si cancela
            button.innerHTML = originalText;
            button.className = originalClass;
            button.disabled = false;
        }
    }).catch(error => {
        console.error('Error:', error);
        showNotification('error', error.message || 'Error al agregar el bundle');
        
        // Restaurar botón
        button.innerHTML = originalText;
        button.className = originalClass;
        button.disabled = false;
    });
}

// Función para ver detalles del bundle
function viewBundleDetails(bundleIdEncrypted) {
    if (!bundleIdEncrypted) {
        showNotification('error', 'ID de bundle no válido');
        return;
    }
    
    console.log("Enviando bundle ID encriptado:", bundleIdEncrypted);
    
    // Mostrar loader
    Swal.fire({
        title: 'Cargando...',
        html: 'Obteniendo detalles del bundle',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    const formData = new FormData();
    formData.append('funcion', 'obtener_info_bundle');
    formData.append('id_bundle_encrypted', bundleIdEncrypted); // Enviar encriptado
    
    fetch('../Controllers/CarritoController.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        Swal.close();
        
        if (data.success) {
            showBundleModal(data.bundle);
        } else {
            showNotification('error', data.error || 'Error al cargar el bundle');
        }
    })
    .catch(error => {
        Swal.close();
        console.error('Error:', error);
        showNotification('error', 'Error al cargar el bundle');
    });
}

// Función para mostrar modal con detalles del bundle
function showBundleModal(bundle) {
    console.log("Mostrando modal para bundle:", bundle);
    // Cerrar cualquier modal existente
    closeBundleModal();

    const bundleId = bundle.id_encrypted || bundle.id;
    console.log("Bundle ID para modal:", bundleId);
    
    const modalHTML = `
        <div class="modal fade show" id="bundleDetailModal" tabindex="-1" role="dialog" style="display: block; padding-right: 17px;">
            <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title">
                            <i class="fas fa-box mr-2"></i> ${bundle.nombre}
                        </h5>
                        <button type="button" class="close text-white" onclick="closeBundleModal()">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <img src="../Util/Img/Producto/${bundle.imagen || 'bundle_default.png'}" 
                                     class="img-fluid rounded mb-3" 
                                     alt="${bundle.nombre}"
                                     onerror="this.src='../Util/Img/Producto/bundle_default.png'">
                                
                                <div class="bundle-prices mb-3">
                                    ${bundle.precio_original > bundle.precio_final ? `
                                        <div class="d-flex align-items-center">
                                            <span class="text-muted text-decoration-line-through mr-3">
                                                $${parseFloat(bundle.precio_original).toFixed(2)}
                                            </span>
                                            <span class="badge badge-danger">
                                                -${bundle.porcentaje_ahorro || bundle.descuento_porcentaje || 0}%
                                            </span>
                                        </div>
                                    ` : ''}
                                    <h3 class="text-success">$${parseFloat(bundle.precio_final).toFixed(2)}</h3>
                                </div>
                                
                                <div class="bundle-info">
                                    <p><strong>Tienda:</strong> ${bundle.tienda_nombre || 'No especificada'}</p>
                                    <p><strong>Stock:</strong> ${bundle.stock > 0 ? bundle.stock + ' disponibles' : 'Consultar stock'}</p>
                                    ${bundle.fecha_fin ? `<p><strong>Válido hasta:</strong> ${new Date(bundle.fecha_fin).toLocaleDateString()}</p>` : ''}
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <h5>Descripción</h5>
                                <p class="text-muted">${bundle.descripcion || 'No hay descripción disponible.'}</p>
                                
                                <h5 class="mt-4">Productos incluidos (${bundle.total_productos})</h5>
                                <div class="bundle-products-list" style="max-height: 300px; overflow-y: auto;">
                                    ${bundle.productos && bundle.productos.length > 0 ? 
                                        bundle.productos.map(producto => `
                                            <div class="bundle-product-item mb-2 p-2 border rounded">
                                                <div class="d-flex align-items-center">
                                                    <img src="../Util/Img/Producto/${producto.imagen || 'producto_default.png'}" 
                                                         class="rounded mr-2" 
                                                         style="width: 50px; height: 50px; object-fit: cover;" 
                                                         alt="${producto.nombre}">
                                                    <div class="flex-grow-1">
                                                        <strong class="d-block">${producto.nombre}</strong>
                                                        <small class="text-muted">${producto.marca_nombre || ''}</small>
                                                        <div class="d-flex justify-content-between">
                                                            <span>$${parseFloat(producto.precio_final || producto.precio || 0).toFixed(2)}</span>
                                                            <small class="${producto.stock > 0 ? 'text-success' : 'text-danger'}">
                                                                ${producto.stock > 0 ? 'Disponible' : 'Agotado'}
                                                            </small>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        `).join('') : 
                                        '<p class="text-muted">No hay productos listados.</p>'
                                    }
                                </div>
                                
                                <div class="mt-4">
                                    <button class="btn btn-success btn-lg btn-block" onclick="addBundleToCartFromModal('${bundleId}')">
                                        <i class="fas fa-shopping-cart mr-2"></i>
                                        Agregar Bundle al Carrito
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-backdrop fade show"></div>
    `;
    
    const modalContainer = document.createElement('div');
    modalContainer.innerHTML = modalHTML;
    document.body.appendChild(modalContainer);
    document.body.style.overflow = 'hidden';
    
    // Configurar eventos del modal
    const backdrop = document.querySelector('.modal-backdrop');
    if (backdrop) {
        backdrop.addEventListener('click', closeBundleModal);
    }
    
    // Manejar tecla ESC
    document.addEventListener('keydown', handleBundleModalKeydown);
}

// Función para cerrar el modal del bundle
function closeBundleModal() {
    const modal = document.getElementById('bundleDetailModal');
    const backdrop = document.querySelector('.modal-backdrop');
    
    if (modal) modal.remove();
    if (backdrop) backdrop.remove();
    
    document.body.style.overflow = 'auto';
    
    // Remover listener de tecla ESC
    document.removeEventListener('keydown', handleBundleModalKeydown);
}

// Manejar tecla ESC para modal de bundle
function handleBundleModalKeydown(e) {
    if (e.key === 'Escape') {
        closeBundleModal();
    }
}

// Función para agregar bundle desde el modal
function addBundleToCartFromModal(bundleIdEncrypted) {
    console.log("Agregando desde modal, ID:", bundleIdEncrypted);
    
    const button = document.querySelector(`.bundle-card[data-bundle-id="${bundleIdEncrypted}"] .add-bundle-btn`);
    if (button) {
        closeBundleModal();
        setTimeout(() => {
            addBundleToCart(bundleIdEncrypted, button);
        }, 300);
    } else {
        // Si no encuentra el botón, agregar directamente
        closeBundleModal();
        setTimeout(() => {
            addBundleToCartDirect(bundleIdEncrypted);
        }, 300);
    }
}

function addBundleToCartDirect(bundleIdEncrypted) {
    console.log("Agregando bundle directamente, ID:", bundleIdEncrypted);
    
    // Verificar sesión primero
    const formDataSession = new FormData();
    formDataSession.append('funcion', 'obtener_cantidad_total');
    
    fetch('../Controllers/CarritoController.php', {
        method: 'POST',
        body: formDataSession
    })
    .then(response => response.json())
    .then(data => {
        if (data.error === 'no_sesion') {
            showNotification('warning', 'Debes iniciar sesión para agregar bundles al carrito');
            setTimeout(() => {
                window.location.href = 'login.php?redirect=' + encodeURIComponent(window.location.href);
            }, 2000);
            return;
        }
        
        // Agregar bundle al carrito
        const formData = new FormData();
        formData.append('funcion', 'agregar_bundle_carrito');
        formData.append('id_bundle_encrypted', bundleIdEncrypted);
        
        fetch('../Controllers/CarritoController.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('success', data.mensaje || '¡Bundle agregado al carrito!');
                updateCartCount(data.cantidad_total);
            } else {
                showNotification('error', data.error || 'Error al agregar el bundle');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('error', 'Error al agregar el bundle');
        });
    })
    .catch(error => {
        console.error('Error verificando sesión:', error);
        showNotification('error', 'Error al verificar sesión');
    });
}

// ACTUALIZAR CONTADOR DEL CARRITO - Función mejorada
function updateCartCount(nuevaCantidad = null) {
    if (nuevaCantidad !== null) {
        // Si se proporciona la cantidad, actualizar directamente
        const cartCount = document.querySelector('.cart-count');
        if (cartCount) {
            cartCount.textContent = nuevaCantidad;
            cartCount.classList.add('updated');
            
            setTimeout(() => {
                cartCount.classList.remove('updated');
            }, 300);
        }
    } else {
        // Obtener cantidad actual del servidor
        const formData = new FormData();
        formData.append('funcion', 'obtener_cantidad_total');
        
        fetch('../Controllers/CarritoController.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            const cartCount = document.querySelector('.cart-count');
            if (cartCount && data.cantidad_total !== undefined) {
                cartCount.textContent = data.cantidad_total;
                cartCount.classList.add('updated');
                
                setTimeout(() => {
                    cartCount.classList.remove('updated');
                }, 300);
            }
        })
        .catch(error => console.error('Error actualizando contador:', error));
    }
}

// Cargar estadísticas de ofertas
function loadOfertasStats() {
    const formData = new FormData();
    formData.append('funcion', 'obtener_estadisticas_ofertas');
    
    fetch('../Controllers/OfertasController.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data && data.total_ofertas) {
            // Actualizar el hero con las estadísticas
            const heroLead = document.querySelector('.ofertas-hero .lead');
            if (heroLead) {
                const statsText = [];
                if (data.total_ofertas) statsText.push(`${data.total_ofertas} productos en oferta`);
                if (data.total_ofertas_flash) statsText.push(`${data.total_ofertas_flash} ofertas flash`);
                if (data.total_bundles) statsText.push(`${data.total_bundles} combos especiales`);
                if (data.max_descuento) statsText.push(`hasta ${data.max_descuento}% de descuento`);
                
                heroLead.textContent = `¡${statsText.join(' • ')}!`;
            }
        }
    })
    .catch(error => {
        console.error('Error cargando estadísticas:', error);
    });
}

// Configurar botones de refresh
function setupRefreshButtons() {
    // Botón de actualizar flotante
    const btnRefreshFloating = document.getElementById('btn-refresh-floating');
    if (btnRefreshFloating) {
        btnRefreshFloating.addEventListener('click', function() {
            refreshOfertas();
            // Animación de rotación
            const icon = this.querySelector('i');
            icon.style.transform = 'rotate(360deg)';
            icon.style.transition = 'transform 0.5s ease';
            
            setTimeout(() => {
                icon.style.transform = 'rotate(0deg)';
            }, 500);
        });
    }
    
    // Botón de actualizar en el header
    const btnRefreshHeader = document.querySelector('.refresh-ofertas-btn');
    if (btnRefreshHeader) {
        btnRefreshHeader.addEventListener('click', function() {
            refreshOfertas();
            // Animación
            this.classList.add('refreshing');
            setTimeout(() => {
                this.classList.remove('refreshing');
            }, 1000);
        });
    }
}

// Función para refrescar ofertas
function refreshOfertas() {
    // Limpiar intervalo anterior si existe
    if (window.flashCountdownInterval) {
        clearInterval(window.flashCountdownInterval);
        window.flashCountdownInterval = null;
    }
    
    // Limpiar contadores de productos
    if (window.productCountdownIntervals) {
        window.productCountdownIntervals.forEach(intervalId => {
            clearInterval(intervalId);
        });
        window.productCountdownIntervals = [];
    }
    
    // Mostrar indicador de recarga
    showNotification('info', 'Actualizando ofertas...');
    
    // Recargar todas las ofertas
    loadAllOfertas();
}

// Función para limpiar todos los intervalos al salir de la página
function cleanupIntervals() {
    if (window.flashCountdownInterval) {
        clearInterval(window.flashCountdownInterval);
        window.flashCountdownInterval = null;
    }
    
    if (window.productCountdownIntervals) {
        window.productCountdownIntervals.forEach(intervalId => {
            clearInterval(intervalId);
        });
        window.productCountdownIntervals = [];
    }
    
    // Limpiar intervalo de vista rápida
    if (window.quickViewCountdownInterval) {
        clearInterval(window.quickViewCountdownInterval);
        window.quickViewCountdownInterval = null;
    }
    
    // Cerrar cualquier modal abierto
    closeQuickViewOfertas();
    closeBundleModal();
}

// Limpiar intervalos cuando el usuario abandona la página
window.addEventListener('beforeunload', cleanupIntervals);
window.addEventListener('pagehide', cleanupIntervals);

// También limpiar al cambiar de sección (SPA-like behavior)
document.addEventListener('visibilitychange', function() {
    if (document.hidden) {
        // Página no visible, pausar temporizadores
        if (window.flashCountdownInterval) {
            clearInterval(window.flashCountdownInterval);
            window.flashCountdownInterval = null;
        }
    } else {
        // Página visible nuevamente, recargar datos
        loadAllOfertas();
    }
});

// Función para mostrar notificaciones
function showNotification(type, message, duration = 3000) {
    // Eliminar notificaciones anteriores
    const oldNotifications = document.querySelectorAll('.custom-notification');
    oldNotifications.forEach(notification => {
        if (notification.parentNode) {
            notification.remove();
        }
    });
    
    const notification = document.createElement('div');
    notification.className = `custom-notification alert alert-${type} alert-dismissible fade show position-fixed`;
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 9999;
        min-width: 300px;
        max-width: 400px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        border-radius: 8px;
    `;
    notification.innerHTML = `
        <div class="d-flex align-items-center">
            <i class="fas ${type === 'success' ? 'fa-check-circle' : 
                          type === 'error' ? 'fa-exclamation-circle' : 
                          type === 'warning' ? 'fa-exclamation-triangle' : 
                          'fa-info-circle'} mr-2"></i>
            <div style="flex: 1;">${message}</div>
            <button type="button" class="close ml-2" style="position: static;">
                <span>&times;</span>
            </button>
        </div>
    `;
    
    document.body.appendChild(notification);
    
    // Auto-eliminar después del tiempo especificado
    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
        }
    }, duration);
    
    // Cerrar al hacer clic
    notification.querySelector('.close').addEventListener('click', () => {
        notification.remove();
    });
}

// Estados de carga
function showLoadingState() {
    const loadingElements = document.querySelectorAll('.loading-state');
    loadingElements.forEach(el => {
        if (el.querySelector('.loading-spinner')) return;
        el.innerHTML = `
            <div class="loading-spinner"></div>
            <p class="loading-text" style="color: #ffffff;">Cargando ofertas...</p>
        `;
    });
}

function hideLoadingState() {
    const loadingElements = document.querySelectorAll('.loading-state');
    loadingElements.forEach(el => {
        el.style.display = 'none';
    });
}

function showErrorState(error) {
    const errorContainer = document.createElement('div');
    errorContainer.className = 'alert alert-danger alert-dismissible fade show mt-3';
    errorContainer.innerHTML = `
        <i class="fas fa-exclamation-triangle mr-2"></i>
        <strong>Error:</strong> ${error.message || 'Error al cargar las ofertas. Por favor, intenta nuevamente.'}
        <button type="button" class="close" data-dismiss="alert">
            <span>&times;</span>
        </button>
    `;
    
    const container = document.querySelector('.container');
    if (container) {
        container.prepend(errorContainer);
    }
}

function showEmptyState() {
    // Mostrar estado vacío global si no hay ofertas
    const mainContent = document.querySelector('.container');
    if (mainContent) {
        mainContent.innerHTML = `
            <div class="empty-state text-center py-5">
                <div class="empty-state-icon mb-3">
                    <i class="fas fa-tag fa-4x text-white"></i>
                </div>
                <h2 class="empty-state-title text-white">No hay ofertas disponibles</h2>
                <p class="empty-state-text text-white mb-4">Actualmente no tenemos ofertas disponibles. Vuelve más tarde para ver nuestras promociones.</p>
                <a href="index.php" class="btn btn-light">
                    <i class="fas fa-home mr-2"></i> Volver al inicio
                </a>
            </div>
        `;
    }
}

// Validar email
function isValidEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

// Verificar sesión del usuario
function checkUserSession() {
    // Verificar carrito
    const formDataCart = new FormData();
    formDataCart.append('funcion', 'obtener_cantidad_total');
    
    fetch('../Controllers/CarritoController.php', {
        method: 'POST',
        body: formDataCart
    })
    .then(response => response.json())
    .then(data => {
        if (data.error === 'no_sesion') {
            // Usuario no ha iniciado sesión
            console.log('Usuario no autenticado para carrito');
        } else if (data.cantidad_total !== undefined) {
            // Usuario autenticado, actualizar contador del carrito
            updateCartCount(data.cantidad_total);
        }
    })
    .catch(error => {
        console.error('Error verificando sesión del carrito:', error);
    });
    
    // Verificar favoritos
    const formDataFav = new FormData();
    formDataFav.append('funcion', 'count_favorites');
    
    fetch('../Controllers/FavoritoController.php', {
        method: 'POST',
        body: formDataFav
    })
    .then(response => response.json())
    .then(data => {
        if (data.error === 'no_sesion') {
            // Usuario no ha iniciado sesión para favoritos
            console.log('Usuario no autenticado para favoritos');
        } else if (data.success) {
            // Actualizar contador de favoritos
            updateFavoriteCount();
        }
    })
    .catch(error => {
        console.error('Error verificando favoritos:', error);
    });
}

// Añade esta función para incluir estilos para los bundles
function addBundleStyles() {
    const style = document.createElement('style');
    style.textContent = `
        /* Estilos para bundles */
        .bundle-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            height: 100%;
        }
        
        .bundle-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
        }
        
        .bundle-image {
            position: relative;
            overflow: hidden;
            max-height: 200px;
        }
        
        .bundle-image img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            transition: transform 0.5s ease;
        }
        
        .bundle-card:hover .bundle-image img {
            transform: scale(1.05);
        }
        
        .bundle-badges {
            position: absolute;
            top: 10px;
            left: 10px;
        }
        
        .bundle-badges .badge {
            margin-right: 5px;
            font-size: 0.8rem;
            padding: 0.25em 0.6em;
        }
        
        .bundle-content {
            padding: 1.5rem;
        }
        
        .bundle-content h4 {
            color: #333;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        
        .price-comparison {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .old-price {
            text-decoration: line-through;
            color: #6c757d;
            font-size: 1.1rem;
        }
        
        .new-price {
            color: #dc3545;
            font-size: 1.5rem;
            font-weight: 700;
        }
        
        .btn-modern {
            border-radius: 25px;
            padding: 0.5rem 1.5rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-modern:hover:not(:disabled) {
            transform: scale(1.05);
        }
        
        .btn-modern:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .bundle-card {
                margin-bottom: 1.5rem;
            }
            
            .bundle-image {
                max-height: 150px;
            }
            
            .bundle-image img {
                height: 150px;
            }
        }
    `;
    document.head.appendChild(style);
}

// Agrega esta función para los estilos de categorías
function addCategoriaStyles() {
    const style = document.createElement('style');
    style.textContent = `
        /* Estilos específicos para categorías en oferta */
        #categorias-oferta {
            margin-top: 3rem;
        }
        
        .categoria-oferta {
            border-radius: 10px;
            transition: all 0.3s ease;
            border: none !important;
            cursor: pointer;
        }
        
        .categoria-imagen {
            overflow: hidden;
            border-top-left-radius: 10px;
            border-top-right-radius: 10px;
            position: relative;
        }
        
        .categoria-imagen img {
            transition: transform 0.5s ease;
        }
        
        .categoria-oferta:hover .categoria-imagen img {
            transform: scale(1.1);
        }
        
        .categoria-imagen::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 30%;
            background: linear-gradient(to top, rgba(0,0,0,0.3), transparent);
        }
        
        .view-category-offers-btn {
            background-color: white !important;
            color: #333 !important;
            border: 2px solid white;
            transition: all 0.3s ease;
        }
        
        .view-category-offers-btn:hover {
            background-color: #f8f9fa !important;
            border-color: #f8f9fa;
            transform: translateY(-2px) translateX(5px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        .loading-state {
            min-height: 200px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }
        
        /* Efecto hover en la tarjeta completa */
        .categoria-oferta:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.2) !important;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .categoria-oferta {
                margin-bottom: 1.5rem;
            }
            
            .categoria-imagen {
                height: 120px !important;
            }
        }
    `;
    document.head.appendChild(style);
}

// Exportar funciones si es necesario
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        initOfertasPage,
        loadAllOfertas,
        initCountdown,
        showNoFlashOffers,
        refreshOfertas,
        cleanupIntervals,
        addToCart,
        addBundleToCart,
        updateCartCount,
        viewBundleDetails,
        closeBundleModal
    };
}