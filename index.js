$(document).ready(function () {
  var funcion;
  verificar_sesion();
  explorar_categorias();
  marcas();
  productos_destacados();
  cargarMenuCategorias();
  // filtrarPorSubcategoria();

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

        console.log("Favoritos encontrados:", cantidad);

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

// Llamar cuando el documento esté listo
$(document).ready(function() {
    // Solo si el elemento badge existe
    if ($("#favoritos-badge").length) {
        iniciarMonitorFavoritos();
        
        // También actualizar después de ciertas acciones
        $(document).on('favorito-cambiado', function() {
            setTimeout(actualizarBadgeFavorito, 500);
        });
    }
});

  //Llenar marcas destacadas dinamicamente
  async function productos_destacados() {
    funcion = "llenar_productos";
    try {
      let data = await fetch("Controllers/ProductoTiendaController.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: new URLSearchParams({
          funcion: funcion,
          solo_destacados: true, // ¡ESTE ES EL PARÁMETRO NUEVO!
          limite: 15, // Solo traer 15 productos destacados
        }).toString(),
      });
      let response = await data.text();
      try {
        let productos = JSON.parse(response);
        // console.log("Productos Destacados obtenidos: ", productos);

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
            // Solo mostrar el badge "Destacado" si realmente lo es (aunque ya deberían ser todos)
            const isDest = producto.destacado == 1;

            template2 += `
            <div class="col-sm-2">
              <div class="card product-card h-100">
                ${isDest ? '<div class="featured-badge">Destacado</div>' : ""}
                
                <div class="card-body">
                  <div class="product-image">
                    <img src="Util/Img/Producto/${producto.imagen}" alt="${
              producto.producto
            }"
                         onerror="this.src='Util/Img/Producto/producto_default.png'">
                  </div>
                  <span class="product-brand">${producto.marca}</span>
                  <a class="product-title" href="Views/descripcion.php?name=${encodeURIComponent(
                    producto.producto
                  )}&id=${producto.id}">
                    ${producto.producto}
                  </a>
                  <span class="badge-free-shipping">Envío gratis</span>
                  
                  <div class="product-rating">
                    <div class="rating-stars">
                      ${generateStarRating(
                        parseFloat(producto.calificacion) || 0
                      )}
                    </div>
                  </div>
                  
                  <div class="product-price">
                    <span class="original-price">$ ${producto.precio}</span>
                    <span class="discount-percent">-${
                      producto.descuento
                    }%</span>
                    <div class="current-price">$ ${
                      producto.precio_descuento
                    }</div>
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
        Swal.fire(
          "Error",
          "Error al procesar los datos de los Productos Destacados",
          "error"
        );
      }
    } catch (error) {
      console.error("Error en marcas:", error);
      Swal.fire("Error", "No se pudieron cargar las marcas", "error");
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

  //Llenar marcas destacadas dinamicamente
  async function marcas() {
    funcion = "obtener_marcas";
    try {
      let data = await fetch("Controllers/MarcasController.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: "funcion=" + funcion,
      });
      let response = await data.text();
      try {
        let marcas = JSON.parse(response);
        //console.log("Obteniendo todas las marcas: ", marcas);
        let template1 = "";
        if (Array.isArray(marcas) && marcas.length > 0) {
          marcas.forEach((marca) => {
            template1 += `
                    <div class="brand-item">
                        <img src="Util/Img/Marca/${marca.logo}" alt="${marca.nombre}">
                    </div>
                `;
          });
        } else {
          template1 = `
                    <div class="col-12 text-center">
                        <p class="text-muted">No hay marcas disponibles en este momento.</p>
                    </div>
                `;
        }
        $("#marca_destacada").html(template1);
      } catch (error) {
        console.error("Error parseando JSON:", error);
        console.log("Respuesta del servidor:", response);
        Swal.fire(
          "Error",
          "Error al procesar los datos de las marcas",
          "error"
        );
      }
    } catch (error) {
      console.error("Error en marcas:", error);
      Swal.fire("Error", "No se pudieron cargar las marcas", "error");
    }
  }

  //Lenar Categorias Dinamicamente
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
        //console.log("Categorias recibidas:", categorias);

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
        Swal.fire(
          "Error",
          "Error al procesar los datos de categorías",
          "error"
        );
      }
    } catch (error) {
      console.error("Error en explorar_categorias:", error);
      Swal.fire("Error", "No se pudieron cargar las categorías", "error");
    }
  }

  async function cargarMenuCategorias() {
    try {
      //console.log('Cargando menú de categorías...');
      const funcion = "obtener_categorias_menu";
      const response = await fetch("Controllers/CategoriaController.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: "funcion=" + funcion,
      });

      if (response.ok) {
        const data = await response.text();
        //   console.log('Datos de categorías recibidos:', data);
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
    //console.log('Generando menú con', categorias.length, 'categorías');

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
              <a href="#" class="${menuLinkClass}" ${
        !hasSubcategories
          ? `onclick="filtrarPorCategoria(${
              categoria.id
            }, '${categoria.nombre.replace(/'/g, "\\'")}')"`
          : ""
      }>
                  <i class="nav-icon ${categoria.icono}"></i>
                  <p>
                      ${categoria.nombre}
                      ${
                        hasSubcategories
                          ? '<i class="right fas fa-angle-left"></i>'
                          : ""
                      }
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
      //console.log('Treeview inicializado correctamente');
    } catch (e) {
      console.warn("Error inicializando treeview:", e);
    }
  }

  function filtrarPorSubcategoria(id_subcategoria, nombre_subcategoria) { // ← Cambia parámetros
    // Usar el nombre de la subcategoría en la URL
    window.location.href = `Views/producto.php?subcategoria=${encodeURIComponent(nombre_subcategoria)}`;
}

  
});
