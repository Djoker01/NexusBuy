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
                    <div class="card favorito-card">
                        <div class="position-relative">
                            <img src="../Util/Img/Producto/${producto.imagen}" 
                                 class="card-img-top producto-img" 
                                 alt="${producto.producto}"
                                 onerror="this.src='../Util/Img/Producto/producto_default.png'">
                            
                            ${tieneDescuento ? `
                                <span class="badge badge-danger badge-descuento">
                                    -${producto.descuento}%
                                </span>
                            ` : ''}
                            
                            <button class="btn-favorito activo" 
                                    onclick="toggleFavorito('${producto.id_encrypted}')"
                                    title="Quitar de favoritos">
                                <i class="fas fa-heart"></i>
                            </button>
                        </div>
                        
                        <div class="card-body">
                            <h6 class="card-title">
                                <a href="descripcion.php?id=${producto.id_encrypted}" class="text-dark">
                                    ${producto.producto}
                                </a>
                            </h6>
                            
                            <p class="card-text small text-muted mb-2">
                                ${producto.marca}
                            </p>
                            
                            <div class="mb-2">
                                ${generarEstrellas(producto.calificacion)}
                            </div>
                            
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    ${tieneDescuento ? `
                                        <div>
                                            <span class="precio-original small">$ ${precioOriginal.toFixed(2)}</span>
                                            <span class="text-danger font-weight-bold ml-2">$ ${precioFinal.toFixed(2)}</span>
                                        </div>
                                    ` : `
                                        <span class="text-danger font-weight-bold">$ ${precioFinal.toFixed(2)}</span>
                                    `}
                                </div>
                            </div>
                            
                            <div class="mt-3">
                                <button class="btn btn-primary btn-sm btn-block" 
                                        onclick="agregarAlCarrito('${producto.id_encrypted}')">
                                    <i class="fas fa-cart-plus mr-1"></i> Agregar al Carrito
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });
        
        $('#lista-favoritos').html(html);
    }
    
    function renderizarFavoritosLista() {
        let html = `
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Precio</th>
                            <th>Calificación</th>
                            <th>Fecha</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
        `;
        
        favoritos.forEach(producto => {
            const precioFinal = parseFloat(producto.precio_descuento) || parseFloat(producto.precio);
            const fecha = new Date(producto.fecha_agregado).toLocaleDateString('es-ES');
            
            html += `
                <tr>
                    <td>
                        <div class="d-flex align-items-center">
                            <img src="../Util/Img/Producto/${producto.imagen}" 
                                 alt="${producto.producto}" 
                                 width="60" 
                                 class="rounded mr-3"
                                 onerror="this.src='../Util/Img/Producto/producto_default.png'">
                            <div>
                                <div class="font-weight-bold">${producto.producto}</div>
                                <small class="text-muted">${producto.marca}</small>
                            </div>
                        </div>
                    </td>
                    <td>
                        <strong class="text-danger">$ ${precioFinal.toFixed(2)}</strong>
                        ${producto.descuento > 0 ? `
                            <br><small class="text-muted text-decoration-line-through">$ ${parseFloat(producto.precio).toFixed(2)}</small>
                        ` : ''}
                    </td>
                    <td>${generarEstrellas(producto.calificacion)}</td>
                    <td>
                        <small class="text-muted">${fecha}</small>
                    </td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-outline-primary" 
                                    onclick="agregarAlCarrito('${producto.id_encrypted}')">
                                <i class="fas fa-cart-plus"></i>
                            </button>
                            <button class="btn btn-outline-danger" 
                                    onclick="toggleFavorito('${producto.id_encrypted}')">
                                <i class="fas fa-trash"></i>
                            </button>
                            <a href="descripcion.php?id=${producto.id_encrypted}" 
                               class="btn btn-outline-info">
                                <i class="fas fa-eye"></i>
                            </a>
                        </div>
                    </td>
                </tr>
            `;
        });
        
        html += `
                    </tbody>
                </table>
            </div>
        `;
        
        $('#lista-favoritos-tabla').html(html);
    }
    
    function generarEstrellas(calificacion) {
        if (!calificacion || calificacion == 0) {
            return '<small class="text-muted">Sin calificaciones</small>';
        }
        
        let estrellas = '';
        for (let i = 1; i <= 5; i++) {
            if (i <= calificacion) {
                estrellas += '<i class="fas fa-star text-warning small"></i>';
            } else {
                estrellas += '<i class="far fa-star text-warning small"></i>';
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
        
        cargarFavoritos(1); // Volver a la primera página
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
        
        // Generar enlace único para compartir (en un sistema real, esto se generaría en el servidor)
        const enlace = `${window.location.origin}${window.location.pathname}?compartir=${generateUniqueId()}`;
        $('#enlace-compartir').val(enlace);
        $('#modalCompartirFavoritos').modal('show');
    }
    
    function generateUniqueId() {
        return 'fav_' + Math.random().toString(36).substr(2, 9);
    }
});

// Funciones globales
async function toggleFavorito(idProducto) {
    console.log('Toggle favorito:', idProducto);
    
    try {
        // Primero verificar si ya es favorito
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
                await cargarFavoritos(paginaActual);
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
            // Actualizar badge del carrito
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
    cargarFavoritos(pagina);
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