// badge-carrito.js - Para páginas que no son el carrito
$(document).ready(function () {
  var funcion;
  verificar_sesion();
  //console.log('Inicializando badge del carrito...');

  // Actualizar badge al cargar la página
  actualizarBadgeCarrito();
  actualizarBadgeFavorito();

  // Actualizar periódicamente
  setInterval(actualizarBadgeCarrito, 500);

  cargarMenuCategorias();
  // filtrarPorSubcategoria();
 



  function verificar_sesion() {
    funcion = "verificar_sesion";
    $.post("../Controllers/UsuarioController.php", { funcion }, (response) => {
      //console.log(response);
      if (response != "") {
        let sesion = JSON.parse(response);
        $("#nav_login").hide();
        $("#nav_register").hide();
        $("#usuario_nav").text(sesion.user);
        $("#avatar_nav").attr("src", "../Util/Img/Users/" + sesion.avatar);
        $("#avatar_menu").attr("src", "../Util/Img/Users/" + sesion.avatar);
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
        const response = await $.post("../Controllers/FavoritoController.php", {
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
            $("#stat-favoritos").html(badgeText);
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

  async function actualizarBadgeCarrito() {
    try {
      const response = await $.post("../Controllers/CarritoController.php", {
        funcion: "obtener_cantidad_total",
      });

      const data =
        typeof response === "string" ? JSON.parse(response) : response;
      const cantidad = data.cantidad_total || 0;
      const $badge = $("#cart-badge");

      if (cantidad > 0) {
        $badge.text(cantidad);
        $badge.show();
      } else {
        $badge.hide();
      }
    } catch (error) {
      console.error("Error actualizando badge:", error);
      $("#cart-badge").hide();
    }
  }

async function cargarMenuCategorias() {
    try {
      //console.log('Cargando menú de categorías...');
      const funcion = "obtener_categorias_menu";
      const response = await fetch("../Controllers/CategoriaController.php", {
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
              <a href="producto.php" class="nav-link">
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
                      <a href="producto.php?subcategoria=${subcategoria.nombre}" class="nav-link">
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
 
  // function filtrarPorSubcategoria(id_subcategoria, nombre_subcategoria) {
  //   // Primero obtener la categoría padre
  //   // const categoriaId = obtener_categoria_por_subcategoria(id_subcategoria);

  //   if (categoriaId) {
  //     // Redirigir a producto.php con ambos parámetros
  //     window.location.href = `producto.php?categoria=${categoriaNombre}&subcategoria=${id_subcategoria}`;
  //   } else {
  //     // Si no se puede obtener la categoría, usar solo subcategoría
  //     window.location.href = `producto.php?subcategoria=${encodeURIComponent(nombre_subcategoria)}`;
  //   }
  // }
  
});
