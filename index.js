$(document).ready(function () {
    var funcion;
    
    // Inicializar funciones
    verificar_sesion();
    explorar_categorias();
    productos_destacados();
    cargarMenuCategorias();
    
    // Llamar a marcas con paginación (solo una vez)
    marcas(1, 30); // Aqui se determina el Limite

    cargarOfertasFlash();
    
    // Event delegation para la paginación de marcas
    $(document).on('click', '.pagination-marcas .page-link[data-pagina]', function(e) {
        e.preventDefault();
        const pagina = $(this).data('pagina');
        cambiarPaginaMarcas(pagina);
    });

    function verificar_sesion() {
        funcion = "verificar_sesion";
        $.post("Controllers/UsuarioController.php", { funcion }, (response) => {
            if (response != "") {
                let sesion = JSON.parse(response);
                $("#nav_login").hide();
                $("#nav_register").hide();
                $("#usuario_nav").text(sesion.user);
                $("#avatar_nav").attr("src", "Util/Img/Users/" + sesion.avatar);
                $("#avatar_menu").attr("src", "Util/Img/Users/" + sesion.avatar);
                $("#usuario_menu").text(sesion.user);
                $("#favoritos").show();
                $("#notification").show();
                
                // Iniciar monitor de favoritos si el usuario está autenticado
                if ($("#favoritos-badge").length) {
                    iniciarMonitorFavoritos();
                }
            } else {
                $("#nav_usuario").hide();
                $("#favoritos").hide();
                $("#notification").hide();
            }
        });
    }

    async function actualizarBadgeFavorito() {
        try {
            const response = await $.post("Controllers/FavoritoController.php", {
                funcion: "count_favorites",
            });

            // Verificar si la respuesta es un string JSON
            let data;
            if (typeof response === 'string') {
                try {
                    data = JSON.parse(response);
                } catch (e) {
                    console.error("Error parseando JSON:", response);
                    throw new Error("Respuesta inválida del servidor");
                }
            } else {
                data = response; // Si ya es un objeto
            }

            // Verificar si hay error de sesión
            if (data.error === 'no_sesion') {
                console.log("Usuario no autenticado, ocultando badge");
                $("#favoritos-badge").hide();
                return;
            }

            // Verificar si la respuesta fue exitosa
            if (data.success === false) {
                console.warn("Error en la respuesta:", data.error);
                $("#favoritos-badge").hide();
                return;
            }

            const cantidad = data.cantidad_total || 0;
            const $badgeFav = $("#favoritos-badge");

            // console.log("Favoritos encontrados:", cantidad);

            if (cantidad > 0) {
                // Limitar a 99+ si hay muchos
                const badgeText = cantidad > 99 ? '99+' : cantidad;
                $badgeFav.text(badgeText);
                $badgeFav.show();
            } else {
                $badgeFav.hide();
            }
        } catch (error) {
            console.error("Error actualizando badge favoritos:", error);
            $("#favoritos-badge").hide();
        }
    }

    // Función para llamar periódicamente si es necesario
    function iniciarMonitorFavoritos() {
        // Actualizar inmediatamente
        actualizarBadgeFavorito();
        
        // Actualizar cada 30 segundos (opcional)
        setInterval(actualizarBadgeFavorito, 30000);
    }

    //Llenar productos destacados dinámicamente
    async function productos_destacados() {
        funcion = "llenar_productos";
        try {
            let data = await fetch("Controllers/ProductoTiendaController.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: new URLSearchParams({
                    funcion: funcion,
                    solo_destacados: true,
                    limite: 18,
                }).toString(),
            });
            let response = await data.text();
            try {
                let productos = JSON.parse(response);

                // Verificar que solo vengan destacados (validación)
                const noDestacados = productos.filter((p) => p.destacado != 1);
                if (noDestacados.length > 0) {
                    console.warn(
                        "ADVERTENCIA: Hay productos no destacados:",
                        noDestacados.length
                    );
                }

                let template2 = "";
                if (Array.isArray(productos) && productos.length > 0) {
                    productos.forEach((producto) => {
                        // Solo mostrar el badge "Destacado" si realmente lo es
                        const isDest = producto.destacado == 1;

                        template2 += `
                        <div class="col-sm-2">
                            <div class="card product-card h-100">
                                ${isDest ? '<div class="featured-badge">Destacado</div>' : ""}
                                
                                <div class="card-body">
                                    <div class="product-image">
                                        <img src="Util/Img/Producto/${producto.imagen}" alt="${producto.producto}"
                                            onerror="this.src='Util/Img/Producto/producto_default.png'">
                                    </div>
                                    <span class="product-brand">${producto.marca}</span>
                                    <a class="product-title" href="Views/descripcion.php?name=${encodeURIComponent(producto.producto)}&id=${producto.id}">
                                        ${producto.producto}
                                    </a>
                                    <span class="badge-free-shipping">Envío gratis</span>
                                    
                                    <div class="product-rating">
                                        <div class="rating-stars">
                                            ${generateStarRating(parseFloat(producto.calificacion) || 0)}
                                        </div>
                                    </div>
                                    
                                    <div class="product-price">
                                        <span class="original-price">$ ${producto.precio}</span>
                                        <span class="discount-percent">-${producto.descuento}%</span>
                                        <div class="current-price">$ ${producto.precio_descuento}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                    });
                } else {
                    template2 = `
                    <div class="col-12 text-center">
                        <p class="text-muted">No hay Productos Destacados en este momento.</p>
                    </div>
                `;
                }

                $("#featured-products").html(template2);
            } catch (error) {
                console.error("Error parseando JSON:", error);
                console.log("Respuesta del servidor:", response);
                Swal.fire("Error", "Error al procesar los datos de los Productos Destacados", "error");
            }
        } catch (error) {
            console.error("Error en productos_destacados:", error);
            Swal.fire("Error", "No se pudieron cargar los productos destacados", "error");
        }
    }

    function generateStarRating(rating) {
        let stars = "";
        const fullStars = Math.floor(rating);
        const hasHalfStar = rating % 1 !== 0;

        for (let i = 0; i < fullStars; i++) {
            stars += '<i class="fas fa-star text-warning"></i>';
        }

        if (hasHalfStar) {
            stars += '<i class="fas fa-star-half-alt"></i>';
        }

        const emptyStars = 5 - Math.ceil(rating);
        for (let i = 0; i < emptyStars; i++) {
            stars += '<i class="far fa-star text-warning"></i>';
        }

        return stars;
    }

    // ===== FUNCIÓN PRINCIPAL PARA OBTENER MARCAS =====
    async function marcas(pagina = 1, limite = 50) {
    const funcion = "obtener_marcas";
    try {
        // Mostrar indicador de carga
        $("#marca_destacada").html(`
            <div class="col-12 text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="sr-only">Cargando...</span>
                </div>
                <p class="text-muted mt-2">Cargando página ${pagina}...</p>
            </div>
        `);
        
        let data = await fetch("Controllers/MarcasController.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: `funcion=${funcion}&page=${pagina}&limit=${limite}`,
        });
        
        let response = await data.text();
        try {
            let resultado = JSON.parse(response);
            
            // console.log("Respuesta del servidor:", resultado);
            
            let templateMarcas = "";
            
            // Verificar si hay marcas
            if (resultado.marcas && Array.isArray(resultado.marcas) && resultado.marcas.length > 0) {
                resultado.marcas.forEach((marca) => {
                    templateMarcas += `
                        <div class="brand-item">
                            <a href="Views/producto.php?marca=${encodeURIComponent(marca.nombre)}" class="brand-link">
                                <img src="Util/Img/Marca/${marca.logo}" alt="${marca.nombre}" class="img-fluid">
                                <p class="brand-name mt-2">${marca.nombre}</p>
                            </a>
                        </div>
                    `;
                });
            } else {
                templateMarcas = `
                    <div class="col-12 text-center py-5">
                        <p class="text-muted">No hay marcas disponibles en esta página.</p>
                    </div>
                `;
            }
            
            // Actualizar el contenedor de marcas
            $("#marca_destacada").html(templateMarcas);
            
            // Generar controles de paginación si hay datos de paginación
            if (resultado.paginacion) {
                generarPaginacionMarcas(
                    resultado.paginacion.pagina_actual,
                    resultado.paginacion.total_paginas,
                    resultado.paginacion.total_marcas
                );
            }
            
        } catch (error) {
            console.error("Error parseando JSON:", error);
            console.log("Respuesta del servidor (texto):", response);
            Swal.fire("Error", "Error al procesar los datos de las marcas", "error");
        }
    } catch (error) {
        console.error("Error en marcas():", error);
        Swal.fire("Error", "No se pudieron cargar las marcas", "error");
    }
}

    // ===== FUNCIÓN PARA GENERAR PAGINACIÓN =====
    function generarPaginacionMarcas(paginaActual, totalPaginas, totalMarcas) {
        // Si solo hay una página, no mostrar paginación
        if (totalPaginas <= 1) {
            if ($("#paginacion-marcas").length) {
                $("#paginacion-marcas").remove();
            }
            return;
        }
        
        let paginacionHTML = `
            <div class="pagination-marcas mt-4">
                <div class="row align-items-center">
                    <div class="col-md-6 mb-2 mb-md-0">
                        <small class="text-muted">
                            Mostrando página ${paginaActual} de ${totalPaginas} 
                            (${totalMarcas} marcas en total)
                        </small>
                    </div>
                    <div class="col-md-6">
                        <nav aria-label="Navegación de marcas">
                            <ul class="pagination pagination-sm justify-content-end mb-0">
        `;
        
        // Botón anterior
        if (paginaActual > 1) {
            paginacionHTML += `
                <li class="page-item">
                    <a class="page-link" href="#" data-pagina="${paginaActual - 1}" aria-label="Anterior">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                </li>
            `;
        } else {
            paginacionHTML += `
                <li class="page-item disabled">
                    <span class="page-link"><i class="fas fa-chevron-left"></i></span>
                </li>
            `;
        }
        
        // Mostrar números de página
        const paginasAMostrar = 5;
        let inicio = Math.max(1, paginaActual - Math.floor(paginasAMostrar / 2));
        let fin = Math.min(totalPaginas, inicio + paginasAMostrar - 1);
        
        // Ajustar inicio si estamos cerca del final
        if (fin - inicio + 1 < paginasAMostrar) {
            inicio = Math.max(1, fin - paginasAMostrar + 1);
        }
        
        // Primera página
        if (inicio > 1) {
            paginacionHTML += `
                <li class="page-item">
                    <a class="page-link" href="#" data-pagina="1">1</a>
                </li>
            `;
            if (inicio > 2) {
                paginacionHTML += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
            }
        }
        
        // Páginas intermedias
        for (let i = inicio; i <= fin; i++) {
            if (i === paginaActual) {
                paginacionHTML += `
                    <li class="page-item active">
                        <span class="page-link">${i}</span>
                    </li>
                `;
            } else {
                paginacionHTML += `
                    <li class="page-item">
                        <a class="page-link" href="#" data-pagina="${i}">${i}</a>
                    </li>
                `;
            }
        }
        
        // Última página
        if (fin < totalPaginas) {
            if (fin < totalPaginas - 1) {
                paginacionHTML += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
            }
            paginacionHTML += `
                <li class="page-item">
                    <a class="page-link" href="#" data-pagina="${totalPaginas}">${totalPaginas}</a>
                </li>
            `;
        }
        
        // Botón siguiente
        if (paginaActual < totalPaginas) {
            paginacionHTML += `
                <li class="page-item">
                    <a class="page-link" href="#" data-pagina="${paginaActual + 1}" aria-label="Siguiente">
                        <i class="fas fa-chevron-right"></i>
                    </a>
                </li>
            `;
        } else {
            paginacionHTML += `
                <li class="page-item disabled">
                    <span class="page-link"><i class="fas fa-chevron-right"></i></span>
                </li>
            `;
        }
        
        paginacionHTML += `
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        `;
        
        // Buscar o crear contenedor de paginación
        if ($("#paginacion-marcas").length) {
            $("#paginacion-marcas").html(paginacionHTML);
        } else {
            // Crear contenedor después del card
            $("#marca_destacada").closest('.card').after(`<div id="paginacion-marcas">${paginacionHTML}</div>`);
        }
    }

    // ===== FUNCIÓN PARA CAMBIAR DE PÁGINA =====
    function cambiarPaginaMarcas(pagina) {
        console.log("Cambiando a página:", pagina);
        
        // Desplazar hacia arriba para mejor UX
        $('html, body').animate({
            scrollTop: $(".featured-brands").offset().top - 20
        }, 300);
        
        // Cargar la página
        marcas(pagina, 50);
        
        return false; // Prevenir comportamiento por defecto del enlace
    }

    //Llenar Categorias Dinamicamente
    async function explorar_categorias() {
        funcion = "explorar_categorias";
        try {
            let data = await fetch("Controllers/CategoriaController.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: "funcion=" + funcion,
            });
            let response = await data.text();

            try {
                let categorias = JSON.parse(response);

                let template = "";

                // Verificar si categorias es un array
                if (Array.isArray(categorias) && categorias.length > 0) {
                    categorias.forEach((categoria) => {
                        // Convertir a número y manejar valores null/undefined
                        const totalProductos = parseInt(categoria.total_productos) || 0;

                        // Usar imagen por defecto si no hay imagen específica
                        const imagen =
                            categoria.imagen && categoria.imagen !== "default_category.png"
                                ? `Util/Img/categorias/${categoria.imagen}`
                                : "Util/Img/categorias/default-category.jpg";
                        // Formatear el número de productos
                        let productosText;
                        if (totalProductos === 0) {
                            productosText = "Sin productos";
                        } else if (totalProductos === 1) {
                            productosText = "1 producto";
                        } else {
                            productosText = `${totalProductos.toLocaleString()} productos`;
                        }

                        template += `
                            <div class="category-item-horizontal">
                                <div class="category-card">
                                    <a href="Views/producto.php?categoria=${categoria.nombre}" class="category-link">
                                        <div class="category-image">
                                            <img src="${imagen}" 
                                                alt="${categoria.nombre}"
                                                onerror="this.src='Util/Img/categorias/default-category.jpg'">
                                            <div class="category-overlay">
                                                <h4>${categoria.nombre}</h4>
                                                <span class="product-count">${productosText}</span>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        `;
                    });
                } else {
                    template = `
                        <div class="col-12 text-center">
                            <p class="text-muted">No hay categorías disponibles en este momento.</p>
                        </div>
                    `;
                }

                $("#feature_categories").html(template);
            } catch (error) {
                console.error("Error parseando JSON:", error);
                console.log("Respuesta del servidor:", response);
                Swal.fire("Error", "Error al procesar los datos de categorías", "error");
            }
        } catch (error) {
            console.error("Error en explorar_categorias:", error);
            Swal.fire("Error", "No se pudieron cargar las categorías", "error");
        }
    }

    async function cargarMenuCategorias() {
        try {
            const funcion = "obtener_categorias_menu";
            const response = await fetch("Controllers/CategoriaController.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: "funcion=" + funcion,
            });

            if (response.ok) {
                const data = await response.text();
                const categorias = JSON.parse(data);
                generarMenuCategorias(categorias);
            } else {
                console.error("Error al cargar categorías:", response.status);
                mostrarErrorMenu();
            }
        } catch (error) {
            console.error("Error cargando categorías:", error);
            mostrarErrorMenu();
        }
    }

    function generarMenuCategorias(categorias) {
        let menuHTML = `
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                <li class="nav-header">CATEGORÍAS</li>
                <li class="nav-item">
                    <a href="Views/producto.php" class="nav-link">
                        <p>Todos los Productos</p>
                    </a>
                </li>
        `;

        categorias.forEach((categoria) => {
            const hasSubcategories =
                categoria.subcategorias && categoria.subcategorias.length > 0;
            const menuItemClass = hasSubcategories
                ? "nav-item has-treeview"
                : "nav-item";
            const menuLinkClass = hasSubcategories ? "nav-link" : "nav-link";

            menuHTML += `
                <li class="${menuItemClass}">
                    <a href="#" class="${menuLinkClass}" ${!hasSubcategories
                    ? `onclick="filtrarPorCategoria(${categoria.id}, '${categoria.nombre.replace(/'/g, "\\'")}')"`
                    : ""}>
                        <i class="nav-icon ${categoria.icono}"></i>
                        <p>
                            ${categoria.nombre}
                            ${hasSubcategories
                            ? '<i class="right fas fa-angle-left"></i>'
                            : ""}
                        </p>
                    </a>
            `;

            if (hasSubcategories) {
                menuHTML += `
                    <ul class="nav nav-treeview">
                `;

                categoria.subcategorias.forEach((subcategoria) => {
                    menuHTML += `
                        <li class="nav-item">
                            <a href="Views/producto.php?subcategoria=${subcategoria.nombre}" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>${subcategoria.nombre}</p>
                            </a>
                        </li>
                    `;
                });

                menuHTML += `</ul>`;
            }

            menuHTML += `</li>`;
        });

        menuHTML += `</ul>`;

        $("#menu-categorias-dinamico").html(menuHTML);

        // Inicializar el plugin treeview de AdminLTE
        try {
            $('[data-widget="treeview"]').Treeview("init");
        } catch (e) {
            console.warn("Error inicializando treeview:", e);
        }
    }

    function mostrarErrorMenu() {
        $("#menu-categorias-dinamico").html(`
            <div class="alert alert-warning m-3">
                <i class="icon fas fa-exclamation-triangle"></i>
                No se pudieron cargar las categorías. Intente recargar la página.
            </div>
        `);
    }

    function filtrarPorSubcategoria(id_subcategoria, nombre_subcategoria) {
        // Usar el nombre de la subcategoría en la URL
        window.location.href = `Views/producto.php?subcategoria=${encodeURIComponent(nombre_subcategoria)}`;
    }

    // ==============================
// CARGAR OFERTAS FLASH - CORREGIDO
// ==============================

function cargarOfertasFlash() {
    console.log("Solicitando ofertas flash...");
    
    $.ajax({
        url: "Controllers/OfertasController.php",
        method: "POST",
        data: {
            funcion: "obtener_ofertas_flash",
            tipo: "todas",
            limite: 6  // Prueba con menos primero
        },
        success: function(response) {
            console.log("Respuesta recibida:", response);
            
            // INTENTA 3 FORMAS DIFERENTES DE PROCESAR LA RESPUESTA
            let ofertas;
            
            // 1. Si ya es un objeto (el caso de tu error)
            if (typeof response === 'object') {
                console.log("La respuesta YA ES un objeto");
                ofertas = response;
            }
            // 2. Si es un string JSON
            else if (typeof response === 'string') {
                try {
                    ofertas = JSON.parse(response);
                    console.log("Parseado de JSON exitoso");
                } catch (e) {
                    console.error("No es JSON válido. Contenido:", response);
                    mostrarErrorOfertas("Formato de respuesta inválido");
                    return;
                }
            }
            // 3. Otro caso
            else {
                console.error("Tipo de respuesta desconocido:", typeof response);
                mostrarErrorOfertas("Error en la respuesta");
                return;
            }
            
            // Verificar estructura
            if (!ofertas || (Array.isArray(ofertas) && ofertas.length === 0)) {
                console.warn("No hay ofertas o array vacío");
                mostrarSinOfertas();
                return;
            }
            
            // Si llegó aquí, mostrar ofertas
            mostrarOfertas(ofertas);
        },
        error: function(xhr, status, error) {
            console.error("Error AJAX:", status, error);
            console.log("Detalles XHR:", xhr);
            mostrarErrorOfertas("Error de conexión: " + status);
        }
    });
}

function mostrarErrorOfertas(mensaje) {
    $("#ofertas-flash-container").html(`
        <div class="col-12 text-center py-4">
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i>
                ${mensaje}
                <button class="btn btn-sm btn-outline-warning ms-2" onclick="cargarOfertasFlash()">
                    Reintentar
                </button>
            </div>
        </div>
    `);
}

function mostrarSinOfertas() {
    $("#ofertas-flash-container").html(`
        <div class="col-12 text-center py-4">
            <p class="text-muted">
                <i class="fas fa-info-circle"></i>
                No hay ofertas flash en este momento
            </p>
        </div>
    `);
}

function mostrarOfertas(ofertas) {
    console.log("Mostrando ofertas:", ofertas);
    
    // Asegurar que sea array
    if (!Array.isArray(ofertas)) {
        console.error("ofertas no es un array:", ofertas);
        mostrarErrorOfertas("Formato de datos incorrecto");
        return;
    }
    
    let html = '';
    ofertas.forEach((producto, index) => {
        console.log(`Producto ${index}:`, producto);
        
        // Validar datos mínimos
        if (!producto || !producto.id) {
            console.warn(`Producto ${index} inválido:`, producto);
            return;
        }
        
        // Asegurar valores
        const descuento = producto.descuento || 0;
        const precio = parseFloat(producto.precio) || 0;
        const precioDescuento = parseFloat(producto.precio_descuento) || 0;
        const tiempo = producto.tiempo_restante || '24:00:00';
        const imagen = producto.imagen || 'producto_default.png';
        const nombre = producto.producto || 'Producto';
        const marca = producto.marca || '';
        
        html += `
            <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 mb-3">
                <div class="card h-100 border border-danger" style="min-height: 320px;">
                    <div class="position-relative">
                        <img src="Util/Img/Producto/${imagen}" 
                             class="card-img-top p-3" 
                             alt="${nombre}"
                             style="height: 150px; object-fit: contain;"
                             onerror="this.src='Util/Img/Producto/producto_default.png'">
                        <span class="position-absolute top-0 end-0 bg-danger text-white px-2 py-1 m-2 rounded">
                            -${descuento}%
                        </span>
                    </div>
                    
                    <div class="card-body p-3">
                        <small class="text-muted d-block mb-1">${marca}</small>
                        <h6 class="card-title" style="font-size: 0.9rem; height: 40px; overflow: hidden;">
                            <a href="Views/descripcion.php?id=${producto.id}" 
                               class="text-decoration-none text-dark">
                                ${nombre.substring(0, 35)}${nombre.length > 35 ? '...' : ''}
                            </a>
                        </h6>
                        
                        <div class="mb-2">
                            <small class="text-danger">
                                <i class="fas fa-clock"></i> ${tiempo}
                            </small>
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-decoration-line-through text-muted d-block">
                                    $${precio.toFixed(2)}
                                </small>
                                <strong class="text-danger fs-5">
                                    $${precioDescuento.toFixed(2)}
                                </strong>
                            </div>
                            <button class="btn btn-danger btn-sm rounded-circle" 
                                    onclick="agregarOferta(${producto.id})">
                                <i class="fas fa-cart-plus"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
    });
    
    $("#ofertas-flash-container").html(html || mostrarSinOfertas());
}

// Función para probar el endpoint directamente
function probarEndpointOfertas() {
    console.log("=== PRUEBA DIRECTA DEL ENDPOINT ===");
    
    // 1. Primero probar con fetch simple
    fetch("Controllers/OfertasController.php", {
        method: "POST",
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: "funcion=obtener_ofertas_flash&tipo=todas&limite=3"
    })
    .then(response => response.text())
    .then(text => {
        console.log("Respuesta CRUDA (texto):", text);
        console.log("Longitud:", text.length);
        
        // Intentar parsear como JSON
        try {
            const json = JSON.parse(text);
            console.log("Parseado como JSON:", json);
        } catch (e) {
            console.error("NO es JSON válido. Contenido completo:");
            console.log(text);
        }
    })
    .catch(error => {
        console.error("Error en fetch:", error);
    });
}

// Función para agregar al carrito
function agregarOferta(productoId) {
    // Usa la misma función que usas en el resto de tu sitio
    // O implementa algo simple:
    $.post("Controllers/CarritoController.php", {
        funcion: "agregar_carrito",
        producto_id: productoId,
        cantidad: 1
    }, function(respuesta) {
        // Mostrar notificación si quieres
        console.log("Producto agregado:", respuesta);
    });
}
});
