$(document).ready(function () {
    console.log('Favoritos inicializado');
    
    // Variables globales
    let favoritos = [];
    let paginaActual = 1;
    const limitePorPagina = 12;
    let filtros = {
        categoria: '',
        precio: '',
        orden: 'recientes'
    };
    
    // Inicializar favoritos
    inicializarFavoritos();
    
    // Eventos
    $('#filtro-categoria, #filtro-precio, #filtro-orden').change(function() {
        aplicarFiltros();
    });
    
    $('#btn-vista-lista').click(function() {
        cambiarVista('lista');
    });
    
    $('#btn-vista-grid').click(function() {
        cambiarVista('grid');
    });
    
    $('#btn-limpiar-favoritos').click(limpiarFavoritos);
    $('#btn-compartir-favoritos').click(mostrarModalCompartir);
    
    // Botones de la sidebar
    $('#btn-comprar-todo').click(function() {
        comprarTodosLosFavoritos();
    });
    
    $('#btn-exportar-favoritos').click(function() {
        exportarFavoritos();
    });
    
    async function inicializarFavoritos() {
        console.log('Inicializando favoritos...');
        
        try {
            await cargarCategorias();
            await cargarFavoritos();
        } catch (error) {
            console.error('Error inicializando favoritos:', error);
            mostrarError('Error al cargar los favoritos');
        }
    }
    
    async function cargarFavoritos(pagina = 1) {
        console.log('Cargando favoritos, página:', pagina);
        
        try {
            // Mostrar loading
            $('#lista-favoritos').html(`
                <div class="col-12 text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Cargando favoritos...</span>
                    </div>
                    <p class="text-muted mt-2">Cargando tus productos favoritos...</p>
                </div>
            `);
            
            const response = await $.post('../Controllers/FavoritoController.php', {
                funcion: 'obtener_favoritos',
                pagina: pagina,
                limite: limitePorPagina,
                filtro_categoria: filtros.categoria,
                filtro_precio: filtros.precio,
                orden: filtros.orden
            });
            
            const data = typeof response === 'string' ? JSON.parse(response) : response;
            
            if (data.error === 'no_sesion') {
                window.location.href = 'login.php';
                return;
            }
            
            if (data.success && data.favoritos.length > 0) {
                favoritos = data.favoritos;
                paginaActual = pagina;
                
                renderizarFavoritosGrid();
                renderizarFavoritosLista();
                actualizarPaginacion(data.paginacion);
                actualizarContador(data.paginacion.total_productos);
                
                $('#favoritos-vacios').hide();
                $('#vista-grid, #vista-lista').show();
            } else {
                mostrarFavoritosVacios();
            }
            
        } catch (error) {
            console.error('Error cargando favoritos:', error);
            mostrarError('Error al cargar los favoritos');
            mostrarFavoritosVacios();
        }
    }
    
    function renderizarFavoritosGrid() {
        let html = '';
        
        favoritos.forEach(producto => {
            const precioFinal = parseFloat(producto.precio_descuento) || parseFloat(producto.precio);
            const precioOriginal = parseFloat(producto.precio);
            const tieneDescuento = producto.descuento > 0;
            
            html += `
                <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                    <div class="card favorite-card">
                        <div class="product-image">
                            <img src="../Util/Img/Producto/${producto.imagen}" 
                                 alt="${producto.producto}"
                                 onerror="this.src='../Util/Img/Producto/producto_default.png'">
                            
                            ${tieneDescuento ? `
                                <span class="discount-badge">
                                    -${producto.descuento}%
                                </span>
                            ` : ''}
                            
                            <button class="favorite-badge" 
                                    onclick="toggleFavoritoDesdeCard('${producto.id_encrypted}')"
                                    title="Quitar de favoritos">
                                <i class="fas fa-heart"></i>
                            </button>
                        </div>
                        
                        <div class="product-info">
                            <div class="product-category">${producto.categoria}</div>
                            <h5 class="product-title">
                                <a href="descripcion.php?id=${producto.id_encrypted}" class="text-dark text-decoration-none">
                                    ${producto.producto}
                                </a>
                            </h5>
                            
                            <p class="product-brand">
                                ${producto.marca}
                            </p>
                            
                            <div class="rating-stars">
                                ${generarEstrellas(producto.calificacion)}
                            </div>
                            
                            <div class="pricing">
                                ${tieneDescuento ? `
                                    <span class="original-price">$${precioOriginal.toFixed(2)}</span>
                                    <span class="current-price">$${precioFinal.toFixed(2)}</span>
                                ` : `
                                    <span class="current-price">$${precioFinal.toFixed(2)}</span>
                                `}
                            </div>
                            
                            <div class="product-actions">
                                <button class="btn-add-cart" 
                                        onclick="agregarAlCarrito('${producto.id_encrypted}')">
                                    <i class="fas fa-cart-plus mr-1"></i> Agregar
                                </button>
                                <a href="descripcion.php?id=${producto.id_encrypted}" 
                                   class="btn-quick-view">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });
        
        $('#lista-favoritos').html(html);
    }
    
    function renderizarFavoritosLista() {
        let html = '';
        
        favoritos.forEach(producto => {
            const precioFinal = parseFloat(producto.precio_descuento) || parseFloat(producto.precio);
            const fecha = new Date(producto.fecha_agregado).toLocaleDateString('es-ES');
            const tieneDescuento = producto.descuento > 0;
            
            html += `
                <div class="list-view-item">
                    <div class="list-view-content">
                        <div class="list-view-image">
                            <img src="../Util/Img/Producto/${producto.imagen}" 
                                 alt="${producto.producto}"
                                 onerror="this.src='../Util/Img/Producto/producto_default.png'">
                        </div>
                        <div class="list-view-details">
                            <div class="product-category">${producto.categoria}</div>
                            <h5 class="product-title">
                                <a href="descripcion.php?id=${producto.id_encrypted}" class="text-dark text-decoration-none">
                                    ${producto.producto}
                                </a>
                            </h5>
                            <p class="product-brand">${producto.marca}</p>
                            <div class="rating-stars">
                                ${generarEstrellas(producto.calificacion)}
                            </div>
                            <div class="pricing">
                                ${tieneDescuento ? `
                                    <span class="original-price">$${parseFloat(producto.precio).toFixed(2)}</span>
                                    <span class="current-price">$${precioFinal.toFixed(2)}</span>
                                ` : `
                                    <span class="current-price">$${precioFinal.toFixed(2)}</span>
                                `}
                            </div>
                            <small class="text-muted">Agregado: ${fecha}</small>
                        </div>
                        <div class="list-view-actions">
                            <button class="btn btn-primary btn-sm" 
                                    onclick="agregarAlCarrito('${producto.id_encrypted}')">
                                <i class="fas fa-cart-plus mr-1"></i> Agregar al Carrito
                            </button>
                            <button class="btn btn-outline-danger btn-sm" 
                                    onclick="toggleFavoritoDesdeCard('${producto.id_encrypted}')">
                                <i class="fas fa-trash mr-1"></i> Eliminar
                            </button>
                            <a href="descripcion.php?id=${producto.id_encrypted}" 
                               class="btn btn-outline-info btn-sm">
                                <i class="fas fa-eye mr-1"></i> Ver Detalles
                            </a>
                        </div>
                    </div>
                </div>
            `;
        });
        
        $('#lista-favoritos-tabla').html(html);
    }
    
    function generarEstrellas(calificacion) {
        if (!calificacion || calificacion == 0) {
            return '<small class="text-muted">Sin calificaciones</small>';
        }
        
        let estrellas = '';
        for (let i = 1; i <= 5; i++) {
            if (i <= calificacion) {
                estrellas += '<i class="fas fa-star text-warning"></i>';
            } else {
                estrellas += '<i class="far fa-star text-warning"></i>';
            }
        }
        return estrellas;
    }
    
    function actualizarPaginacion(paginacion) {
        let html = '';
        const totalPaginas = paginacion.total_paginas;
        const paginaActual = paginacion.pagina_actual;
        
        // Botón anterior
        html += `
            <li class="page-item ${paginaActual === 1 ? 'disabled' : ''}">
                <a class="page-link" href="#" onclick="cambiarPagina(${paginaActual - 1})">
                    <i class="fas fa-chevron-left"></i>
                </a>
            </li>
        `;
        
        // Números de página
        for (let i = 1; i <= totalPaginas; i++) {
            if (i === 1 || i === totalPaginas || (i >= paginaActual - 2 && i <= paginaActual + 2)) {
                html += `
                    <li class="page-item ${i === paginaActual ? 'active' : ''}">
                        <a class="page-link" href="#" onclick="cambiarPagina(${i})">${i}</a>
                    </li>
                `;
            } else if (i === paginaActual - 3 || i === paginaActual + 3) {
                html += '<li class="page-item disabled"><span class="page-link">...</span></li>';
            }
        }
        
        // Botón siguiente
        html += `
            <li class="page-item ${paginaActual === totalPaginas ? 'disabled' : ''}">
                <a class="page-link" href="#" onclick="cambiarPagina(${paginaActual + 1})">
                    <i class="fas fa-chevron-right"></i>
                </a>
            </li>
        `;
        
        $('#paginacion-favoritos').html(html);
    }
    
    function actualizarContador(total) {
        const texto = total === 1 ? '1 producto' : `${total} productos`;
        $('#contador-favoritos').text(texto);
    }
    
    function mostrarFavoritosVacios() {
        $('#vista-grid, #vista-lista').hide();
        $('#favoritos-vacios').show();
        $('#contador-favoritos').text('0 productos');
        $('#paginacion-favoritos').html('');
    }
    
    async function cargarCategorias() {
        try {
            const response = await $.post('../Controllers/FavoritoController.php', {
                funcion: 'obtener_categorias_favoritos'
            });
            
            const data = typeof response === 'string' ? JSON.parse(response) : response;
            
            if (data.success) {
                let options = '<option value="">Todas las categorías</option>';
                data.categorias.forEach(categoria => {
                    options += `<option value="${categoria.id}">${categoria.nombre}</option>`;
                });
                $('#filtro-categoria').html(options);
            }
        } catch (error) {
            console.error('Error cargando categorías:', error);
        }
    }
    
    function aplicarFiltros() {
        filtros = {
            categoria: $('#filtro-categoria').val(),
            precio: $('#filtro-precio').val(),
            orden: $('#filtro-orden').val()
        };
        
        cargarFavoritos(1);
    }
    
    function cambiarVista(tipo) {
        if (tipo === 'lista') {
            $('#btn-vista-lista').removeClass('btn-outline-secondary').addClass('btn-primary');
            $('#btn-vista-grid').removeClass('btn-primary').addClass('btn-outline-secondary');
            $('#vista-grid').hide();
            $('#vista-lista').show();
        } else {
            $('#btn-vista-grid').removeClass('btn-outline-secondary').addClass('btn-primary');
            $('#btn-vista-lista').removeClass('btn-primary').addClass('btn-outline-secondary');
            $('#vista-lista').hide();
            $('#vista-grid').show();
        }
    }
    
    async function limpiarFavoritos() {
        const result = await Swal.fire({
            title: '¿Limpiar todos los favoritos?',
            text: 'Esta acción no se puede deshacer. Se eliminarán todos los productos de tu lista de favoritos.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, limpiar todo',
            cancelButtonText: 'Cancelar'
        });
        
        if (result.isConfirmed) {
            try {
                const response = await $.post('../Controllers/FavoritoController.php', {
                    funcion: 'limpiar_favoritos'
                });
                
                const data = typeof response === 'string' ? JSON.parse(response) : response;
                
                if (data.success) {
                    mostrarExito('Todos los favoritos han sido eliminados');
                    await cargarFavoritos(1);
                } else {
                    throw new Error(data.error || 'Error al limpiar favoritos');
                }
            } catch (error) {
                console.error('Error limpiando favoritos:', error);
                mostrarError('Error al limpiar los favoritos');
            }
        }
    }
    
    function mostrarModalCompartir() {
        if (favoritos.length === 0) {
            mostrarError('No hay productos para compartir');
            return;
        }
        
        const enlace = `${window.location.origin}${window.location.pathname}?compartir=${generateUniqueId()}`;
        $('#enlace-compartir').val(enlace);
        $('#modalCompartirFavoritos').modal('show');
    }
    
    function generateUniqueId() {
        return 'fav_' + Math.random().toString(36).substr(2, 9);
    }
    
    async function comprarTodosLosFavoritos() {
        if (favoritos.length === 0) {
            mostrarError('No hay productos para comprar');
            return;
        }
        
        const result = await Swal.fire({
            title: '¿Agregar todos los favoritos al carrito?',
            text: `Se agregarán ${favoritos.length} productos a tu carrito de compras.`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#4361ee',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, agregar todos',
            cancelButtonText: 'Cancelar'
        });
        
        if (result.isConfirmed) {
            try {
                let agregados = 0;
                let errores = 0;
                
                for (const producto of favoritos) {
                    try {
                        const response = await $.post('../Controllers/CarritoController.php', {
                            funcion: 'agregar_al_carrito',
                            id_producto_tienda: producto.id_encrypted,
                            cantidad: 1
                        });
                        
                        const data = typeof response === 'string' ? JSON.parse(response) : response;
                        
                        if (data.success) {
                            agregados++;
                        } else {
                            errores++;
                        }
                    } catch (error) {
                        errores++;
                    }
                }
                
                if (agregados > 0) {
                    mostrarExito(`Se agregaron ${agregados} productos al carrito${errores > 0 ? ` (${errores} errores)` : ''}`);
                    // Actualizar badge del carrito si existe la función
                    if (typeof actualizarBadgeCarrito === 'function') {
                        actualizarBadgeCarrito();
                    }
                } else {
                    mostrarError('No se pudo agregar ningún producto al carrito');
                }
            } catch (error) {
                console.error('Error agregando productos al carrito:', error);
                mostrarError('Error al agregar los productos al carrito');
            }
        }
    }
    
    function exportarFavoritos() {
        if (favoritos.length === 0) {
            mostrarError('No hay productos para exportar');
            return;
        }
        
        // Crear contenido CSV
        let csvContent = "Producto,Marca,Categoría,Precio,Calificación\n";
        
        favoritos.forEach(producto => {
            const precioFinal = parseFloat(producto.precio_descuento) || parseFloat(producto.precio);
            csvContent += `"${producto.producto}","${producto.marca}","${producto.categoria}",${precioFinal.toFixed(2)},${producto.calificacion}\n`;
        });
        
        // Crear y descargar archivo
        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement("a");
        const url = URL.createObjectURL(blob);
        
        link.setAttribute("href", url);
        link.setAttribute("download", "mis_favoritos.csv");
        link.style.visibility = 'hidden';
        
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        
        mostrarExito('Lista de favoritos exportada correctamente');
    }
});

// Funciones globales
async function toggleFavoritoDesdeCard(idProducto) {
    await toggleFavorito(idProducto);
}

async function toggleFavorito(idProducto) {
    console.log('Toggle favorito:', idProducto);
    
    try {
        const responseVerificar = await $.post('../Controllers/FavoritoController.php', {
            funcion: 'verificar_favorito',
            id_producto_tienda: idProducto
        });
        
        const dataVerificar = typeof responseVerificar === 'string' ? JSON.parse(responseVerificar) : responseVerificar;
        const esFavorito = dataVerificar.es_favorito;
        
        const funcion = esFavorito ? 'eliminar_favorito' : 'agregar_favorito';
        
        const response = await $.post('../Controllers/FavoritoController.php', {
            funcion: funcion,
            id_producto_tienda: idProducto
        });
        
        const data = typeof response === 'string' ? JSON.parse(response) : response;
        
        if (data.success) {
            if (esFavorito) {
                mostrarExito('Producto eliminado de favoritos');
                // Recargar la lista
                location.reload();
            } else {
                mostrarExito('Producto agregado a favoritos');
            }
        } else {
            throw new Error(data.error || 'Error al modificar favoritos');
        }
        
    } catch (error) {
        console.error('Error toggle favorito:', error);
        mostrarError('Error al modificar favoritos');
    }
}

async function agregarAlCarrito(idProducto) {
    console.log('Agregar al carrito desde favoritos:', idProducto);
    
    try {
        const response = await $.post('../Controllers/CarritoController.php', {
            funcion: 'agregar_al_carrito',
            id_producto_tienda: idProducto,
            cantidad: 1
        });
        
        const data = typeof response === 'string' ? JSON.parse(response) : response;
        
        if (data.success) {
            mostrarExito('Producto agregado al carrito');
            if (typeof actualizarBadgeCarrito === 'function') {
                actualizarBadgeCarrito();
            }
        } else {
            if (data.error === 'no_sesion') {
                window.location.href = 'login.php';
            } else {
                throw new Error(data.error || 'Error al agregar al carrito');
            }
        }
        
    } catch (error) {
        console.error('Error agregando al carrito:', error);
        mostrarError('Error al agregar el producto al carrito');
    }
}

function cambiarPagina(pagina) {
    // Usar la función dentro del document ready
    $(document).ready(function() {
        cargarFavoritos(pagina);
    });
}

function copiarEnlace() {
    const enlaceInput = document.getElementById('enlace-compartir');
    enlaceInput.select();
    document.execCommand('copy');
    
    mostrarExito('Enlace copiado al portapapeles');
}

function compartirFacebook() {
    const enlace = encodeURIComponent($('#enlace-compartir').val());
    const texto = encodeURIComponent('¡Mira mis productos favoritos en NexusBuy!');
    window.open(`https://www.facebook.com/sharer/sharer.php?u=${enlace}&quote=${texto}`, '_blank');
}

function compartirTwitter() {
    const enlace = encodeURIComponent($('#enlace-compartir').val());
    const texto = encodeURIComponent('Mis productos favoritos en NexusBuy');
    window.open(`https://twitter.com/intent/tweet?text=${texto}&url=${enlace}`, '_blank');
}

function compartirPinterest() {
    const enlace = encodeURIComponent($('#enlace-compartir').val());
    const descripcion = encodeURIComponent('Mis productos favoritos en NexusBuy');
    window.open(`https://pinterest.com/pin/create/button/?url=${enlace}&description=${descripcion}`, '_blank');
}

function mostrarExito(mensaje) {
    Swal.fire({
        icon: 'success',
        title: 'Éxito',
        text: mensaje,
        timer: 3000,
        showConfirmButton: false
    });
}

function mostrarError(mensaje) {
    Swal.fire({
        icon: 'error',
        title: 'Error',
        text: mensaje,
        confirmButtonText: 'Entendido'
    });
}