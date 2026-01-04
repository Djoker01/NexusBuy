// funcion_general.js - Funciones generales para todo el sitio
$(document).ready(function () {
  var funcion;
  verificar_sesion();
  //console.log('Inicializando funciones generales...');

  // Actualizar badges al cargar la página
  actualizarBadgeCarrito();
  actualizarBadgeFavorito();
  obtenerContadores();
  actualizarNotificacionesHeader(); // Nueva función para el header

  // Actualizar periódicamente
  setInterval(actualizarBadgeCarrito, 500);
  setInterval(actualizarNotificacionesHeader, 10000); // Actualizar notificaciones cada 10 segundos

  cargarMenuCategorias();

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
        // Actualizar notificaciones después de verificar sesión
        actualizarNotificacionesHeader();
      } else {
        $("#nav_usuario").hide();
        $("#favoritos").hide();
        $("#notification").hide();
      }
    });
  }

  // Función para obtener contadores (para página de notificaciones)
  function obtenerContadores() {
    funcion = 'get_counts';
    $.post("../Controllers/NotificacionController.php", { funcion }, (response) => {
      try {
        const data = JSON.parse(response);
        if (!data.error) {
          $('#count-all').text(data.total || 0);
          $('#count-unread').text(data.no_leidas || 0);
          $('#count-orders').text(data.pedidos || 0);
          $('#count-promotions').text(data.promociones || 0);
          $('#count-system').text(data.sistema || 0);
        }
      } catch (e) {
        console.error('Error al obtener contadores:', e);
      }
    });
  }

  // NUEVA FUNCIÓN: Actualizar notificaciones en el header
  async function actualizarNotificacionesHeader() {
    try {
      // Solo ejecutar si el usuario está logueado y el elemento existe
      if (!$("#notification").is(":visible")) {
        return;
      }

      const response = await $.post("../Controllers/NotificacionController.php", {
        funcion: "get_counts"
      });

      const data = typeof response === 'string' ? JSON.parse(response) : response;
      
      if (data.error) {
        // console.log("Error obteniendo notificaciones:", data.error);
        return;
      }

      const totalNotificaciones = data.total || 0;
      const noLeidas = data.no_leidas || 0;
      const pedidos = data.pedidos || 0;
      const promociones = data.promociones || 0;
      const sistema = data.sistema || 0;

      // Actualizar badge en el icono de campana
      const $badge = $("#notification .navbar-badge");
      if (noLeidas > 0) {
        const badgeText = noLeidas > 99 ? '99+' : noLeidas;
        $badge.text(badgeText);
        $badge.show();
      } else {
        $badge.hide();
      }

      // Actualizar dropdown menu
      const $dropdownHeader = $("#notification .dropdown-header");
      const $dropdownMenu = $("#notification .dropdown-menu");
      
      if (totalNotificaciones > 0) {
        // Actualizar header
        $dropdownHeader.text(`${noLeidas} nuevas Notificación${noLeidas !== 1 ? 'es' : ''}`);
        
        // Crear contenido dinámico para el dropdown
        let dropdownContent = `
          <span class="dropdown-item dropdown-header">${noLeidas} nuevas Notificación${noLeidas !== 1 ? 'es' : ''}</span>
          <div class="dropdown-divider"></div>
        `;
        
        // Agregar items según los tipos de notificaciones
        if (pedidos > 0) {
          dropdownContent += `
            <a href="notificaciones.php?filter=orders" class="dropdown-item">
              <i class="fas fa-shopping-cart mr-2"></i> ${pedidos} Nuevo${pedidos !== 1 ? 's' : ''} Pedido${pedidos !== 1 ? 's' : ''}
              <span class="float-right text-muted text-sm">${getTiempoRelativo()}</span>
            </a>
            <div class="dropdown-divider"></div>
          `;
        }
        
        if (promociones > 0) {
          dropdownContent += `
            <a href="notificaciones.php?filter=promotions" class="dropdown-item">
              <i class="fas fa-tag mr-2"></i> ${promociones} Nueva${promociones !== 1 ? 's' : ''} Promoción${promociones !== 1 ? 'es' : ''}
              <span class="float-right text-muted text-sm">${getTiempoRelativo(12)}</span>
            </a>
            <div class="dropdown-divider"></div>
          `;
        }
        
        if (sistema > 0) {
          dropdownContent += `
            <a href="notificaciones.php?filter=system" class="dropdown-item">
              <i class="fas fa-cog mr-2"></i> ${sistema} Alerta${sistema !== 1 ? 's' : ''} del Sistema
              <span class="float-right text-muted text-sm">${getTiempoRelativo(48)}</span>
            </a>
            <div class="dropdown-divider"></div>
          `;
        }
        
        // Agregar footer
        dropdownContent += `
          <a href="notificaciones.php" class="dropdown-item dropdown-footer">Ver todas las Notificaciones</a>
        `;
        
        $dropdownMenu.html(dropdownContent);
        
      } else {
        // Si no hay notificaciones
        $dropdownHeader.text("0 nuevas Notificaciones");
        $dropdownMenu.html(`
          <span class="dropdown-item dropdown-header">0 nuevas Notificaciones</span>
          <div class="dropdown-divider"></div>
          <a href="#" class="dropdown-item">
            <i class="fas fa-info-circle mr-2"></i> No tienes notificaciones nuevas
          </a>
          <div class="dropdown-divider"></div>
          <a href="notificaciones.php" class="dropdown-item dropdown-footer">Ver todas las Notificaciones</a>
        `);
      }
      
    } catch (error) {
      console.error("Error actualizando notificaciones del header:", error);
    }
  }

  // Función auxiliar para obtener tiempo relativo
  function getTiempoRelativo(horas = 0) {
    if (horas === 0) return "ahora";
    if (horas < 24) return `${horas} hora${horas !== 1 ? 's' : ''}`;
    
    const dias = Math.floor(horas / 24);
    if (dias === 1) return "ayer";
    return `${dias} días`;
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
        // console.log("Usuario no autenticado, ocultando badge");
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
  
  // Evento para actualizar notificaciones cuando se interactúa con ellas
  $(document).on('notificacion-actualizada', function() {
    setTimeout(actualizarNotificacionesHeader, 1000);
    setTimeout(obtenerContadores, 1000);
  });
  
  // También actualizar cuando se hace clic en el dropdown de notificaciones
  $("#notification .nav-link").on("click", function() {
    setTimeout(actualizarNotificacionesHeader, 500);
  });
  
});
