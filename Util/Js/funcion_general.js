// funcion_general.js - Funciones generales para todo el sitio
// console.log('funcion_general.js cargado');
console.log('BASE_PATH en script:', typeof BASE_PATH !== 'undefined' ? BASE_PATH : 'NO DEFINIDA');
// console.log('BASE_PATH_URL en script:', typeof BASE_PATH_URL !== 'undefined' ? BASE_PATH_URL : 'NO DEFINIDA');
$(document).ready(function () {
  // Variable para controlar si el dropdown está abierto
  let dropdownOpen = false;

  // Toggle dropdown al hacer clic en user-info
  $("#userDropdown").click(function (e) {
    e.stopPropagation();
    e.preventDefault();

    const $this = $(this);
    const $menu = $("#userDropdownMenu");

    // Cerrar cualquier otro dropdown abierto
    $(".dropdown-menu.show").not($menu).removeClass("show");
    $(".user-info.open").not($this).removeClass("open");

    // Toggle el actual
    $this.toggleClass("open");
    $menu.toggleClass("show");

    dropdownOpen = $menu.hasClass("show");
  });

  // Cerrar dropdown al hacer clic fuera
  $(document).click(function (e) {
    if (!$(e.target).closest(".dropdown-container").length) {
      $("#userDropdown").removeClass("open");
      $("#userDropdownMenu").removeClass("show");
      dropdownOpen = false;
    }
  });

  // Cerrar dropdown con tecla ESC
  $(document).keydown(function (e) {
    if (e.key === "Escape" && dropdownOpen) {
      $("#userDropdown").removeClass("open");
      $("#userDropdownMenu").removeClass("show");
      dropdownOpen = false;
    }
  });

  // Prevenir que el dropdown se cierre al hacer clic dentro de él
  $("#userDropdownMenu").click(function (e) {
    e.stopPropagation();
  });

  // En móviles, ajustar comportamiento
  if (window.innerWidth <= 768) {
    $("#userDropdown")
      .off("click")
      .on("click", function (e) {
        e.stopPropagation();
        const $menu = $("#userDropdownMenu");

        if ($menu.hasClass("show")) {
          $menu.removeClass("show");
          $(this).removeClass("open");
        } else {
          // Cerrar cualquier otro menú abierto
          $(".dropdown-menu.show").removeClass("show");
          $(".user-info.open").removeClass("open");

          $menu.addClass("show");
          $(this).addClass("open");

          // Scroll suave para mostrar el menú
          $("html, body").animate(
            {
              scrollTop: $(document).height(),
            },
            300,
          );
        }
      });
  }

  
  //console.log('Inicializando funciones generales...');
  var funcion;
  verificar_sesion();
  // Actualizar badges al cargar la página
  actualizarBadgeCarrito();
  actualizarBadgeFavorito();
  obtenerContadores();
  actualizarNotificacionesHeader(); // Nueva función para el header
  obtenerRedesSociales();
  obtenerContacto();

  // Actualizar periódicamente
  setInterval(actualizarBadgeCarrito, 500);
  setInterval(actualizarNotificacionesHeader, 10000); // Actualizar notificaciones cada 10 segundos

  cargarMenuCategorias();

  function verificar_sesion() {
    funcion = "verificar_sesion";
    $.post(BASE_PATH + "Controllers/UsuarioController.php", { funcion }, (response) => {
      //console.log(response);
      if (response != "") {
        let sesion = JSON.parse(response);
        console.log(sesion);
        $("#nav_login").hide();
        $("#nav_register").hide();
        $("#usuario_nav").text(sesion.user);
        $(".admin-mini-avatar").attr("src", BASE_PATH + "Util/Img/Users/" + sesion.avatar);
        $("#avatar_menu").attr("src", BASE_PATH + "Util/Img/Users/" + sesion.avatar);
        $(".user-username").text(sesion.user);
        $(".user-name").text(sesion.nombre + " " + sesion.apellido);
        $("#favoritos").show();
        $("#notification").show();
        // Actualizar notificaciones después de verificar sesión
        actualizarNotificacionesHeader();
      } else {
        $(".dropdown-container").hide();
        $("#favoritos").hide();
        $("#notification").hide();
      }
    });
  }

  // Función para obtener contadores (para página de notificaciones)
  function obtenerContadores() {
    funcion = "get_counts";
    $.post(
      BASE_PATH + "Controllers/NotificacionController.php",
      { funcion },
      (response) => {
        try {
          const data = JSON.parse(response);
          if (!data.error) {
            $("#count-all").text(data.total || 0);
            $("#count-unread").text(data.no_leidas || 0);
            $("#count-orders").text(data.pedidos || 0);
            $("#count-promotions").text(data.promociones || 0);
            $("#count-system").text(data.sistema || 0);
          }
        } catch (e) {
          console.error("Error al obtener contadores:", e);
        }
      },
    );
  }

  // NUEVA FUNCIÓN: Actualizar notificaciones en el header
  async function actualizarNotificacionesHeader() {
    try {
      // Solo ejecutar si el usuario está logueado y el elemento existe
      if (!$("#notification").is(":visible")) {
        return;
      }

      const response = await $.post(
        BASE_PATH + "Controllers/NotificacionController.php",
        {
          funcion: "get_counts",
        },
      );

      const data =
        typeof response === "string" ? JSON.parse(response) : response;

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
        const badgeText = noLeidas > 99 ? "99+" : noLeidas;
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
        $dropdownHeader.text(
          `${noLeidas} nuevas Notificación${noLeidas !== 1 ? "es" : ""}`,
        );

        // Crear contenido dinámico para el dropdown
        let dropdownContent = `
          <span class="dropdown-item dropdown-header">${noLeidas} nuevas Notificación${
            noLeidas !== 1 ? "es" : ""
          }</span>
          <div class="dropdown-divider"></div>
        `;

        // Agregar items según los tipos de notificaciones
        if (pedidos > 0) {
          dropdownContent += `
            <a href="${BASE_PATH_URL}notificaciones.php?filter=orders" class="dropdown-item">
              <i class="fas fa-shopping-cart mr-2"></i> ${pedidos} Nuevo${
                pedidos !== 1 ? "s" : ""
              } Pedido${pedidos !== 1 ? "s" : ""}
              <span class="float-right text-muted text-sm">${getTiempoRelativo()}</span>
            </a>
            <div class="dropdown-divider"></div>
          `;
        }

        if (promociones > 0) {
          dropdownContent += `
            <a href="${BASE_PATH_URL}notificaciones.php?filter=promotions" class="dropdown-item">
              <i class="fas fa-tag mr-2"></i> ${promociones} Nueva${
                promociones !== 1 ? "s" : ""
              } Promoción${promociones !== 1 ? "es" : ""}
              <span class="float-right text-muted text-sm">${getTiempoRelativo(
                12,
              )}</span>
            </a>
            <div class="dropdown-divider"></div>
          `;
        }

        if (sistema > 0) {
          dropdownContent += `
            <a href="${BASE_PATH_URL}notificaciones.php?filter=system" class="dropdown-item">
              <i class="fas fa-cog mr-2"></i> ${sistema} Alerta${
                sistema !== 1 ? "s" : ""
              } del Sistema
              <span class="float-right text-muted text-sm">${getTiempoRelativo(
                48,
              )}</span>
            </a>
            <div class="dropdown-divider"></div>
          `;
        }

        // Agregar footer
        dropdownContent += `
          <a href="${BASE_PATH_URL}notificaciones.php" class="dropdown-item dropdown-footer">Ver todas las Notificaciones</a>
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
          <a href="${BASE_PATH_URL}notificaciones.php" class="dropdown-item dropdown-footer">Ver todas las Notificaciones</a>
        `);
      }
    } catch (error) {
      console.error("Error actualizando notificaciones del header:", error);
    }
  }

  // Función auxiliar para obtener tiempo relativo
  function getTiempoRelativo(horas = 0) {
    if (horas === 0) return "ahora";
    if (horas < 24) return `${horas} hora${horas !== 1 ? "s" : ""}`;

    const dias = Math.floor(horas / 24);
    if (dias === 1) return "ayer";
    return `${dias} días`;
  }

  async function actualizarBadgeFavorito() {
    try {
      const response = await $.post(BASE_PATH + "Controllers/FavoritoController.php", {
        funcion: "count_favorites",
      });

      // Verificar si la respuesta es un string JSON
      let data;
      if (typeof response === "string") {
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
      if (data.error === "no_sesion") {
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
        const badgeText = cantidad > 99 ? "99+" : cantidad;
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
  $(document).ready(function () {
    // Solo si el elemento badge existe
    if ($("#favoritos-badge").length) {
      iniciarMonitorFavoritos();

      // También actualizar después de ciertas acciones
      $(document).on("favorito-cambiado", function () {
        setTimeout(actualizarBadgeFavorito, 500);
      });
    }
  });

  async function actualizarBadgeCarrito() {
    try {
      const response = await $.post(BASE_PATH + "Controllers/CarritoController.php", {
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
      const response = await fetch(BASE_PATH + "Controllers/CategoriaController.php", {
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
              <a href="${BASE_PATH_URL}producto.php" class="nav-link">
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
                      <a href="${BASE_PATH_URL}producto.php?subcategoria=${subcategoria.nombre}" class="nav-link">
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

    menuHTML += `<li class="nav-item">
                      <a href="${BASE_PATH_URL}ofertas.php" class="nav-link">
                          <i class="fas fa-tag nav-icon"></i>
                          <p>Outlet y Ofertas</p>
                      </a>
                  </li>
                  </ul>`;

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
  $(document).on("notificacion-actualizada", function () {
    setTimeout(actualizarNotificacionesHeader, 1000);
    setTimeout(obtenerContadores, 1000);
  });

  // También actualizar cuando se hace clic en el dropdown de notificaciones
  $("#notification .nav-link").on("click", function () {
    setTimeout(actualizarNotificacionesHeader, 500);
  });

  async function obtenerRedesSociales() {
    try {
      const sociales = await $.post(
        BASE_PATH + "Controllers/ConfiguracionSitioController.php",
        {
          funcion: "obtenerRedesSociales",
        },
      );

      //   console.log("Redes encontradas:", sociales);
      const enlacesSociales = document.getElementById("redes");

      let html = "";

      sociales.forEach((social) => {
        html += `
            <a href="${social.url}" class="text-white mr-3" style="font-size: 1.2rem;">
              <i class="${social.icono}"></i>
            </a>
        `;
      });
      enlacesSociales.innerHTML = html;
    } catch (error) {
      console.error("Error cargando Redes Sociales:", error);
    }
  }

  async function obtenerContacto() {
    try {
      const response = await $.post(
        BASE_PATH + "Controllers/ConfiguracionSitioController.php",
        {
          funcion: "obtener_contacto",
        },
      );

      //   console.log("Contacto encontrado:", response);
      const datosContacto = document.getElementById("datos_contacto");
      const data = response.data;

      let html = "";
      const direccion = data.direccion_principal.valor;
      const telefono = data.telefono_contacto.valor;
      const correo = data.email_contacto.valor;

      html += `
            <i class="fas fa-map-marker-alt mr-2"></i> ${direccion}</li>
            <li class="mb-2"><i class="fas fa-phone mr-2"></i> ${telefono}</li>
            <li class="mb-2"><i class="fas fa-envelope mr-2"></i> ${correo}</li>
        `;

      datosContacto.innerHTML = html;
    } catch (error) {
      console.error("Error cargando Datos de Contacto:", error);
    }
  }
});

// ============================
// Bdusqueda
// ============================
// Función para manejar el envío del formulario de búsqueda
function handleSearch(event) {
  event.preventDefault();
  const searchTerm = document.getElementById("input-busqueda").value.trim();

  if (searchTerm.length < 2) {
    showSearchAlert("Por favor, ingresa al menos 2 caracteres para buscar");
    return;
  }

  // Redirigir a la página de productos con el término de búsqueda
  window.location.href = `${BASE_PATH_URL}producto.php?busqueda=${encodeURIComponent(searchTerm)}`;
}

// Función para mostrar alertas de búsqueda
function showSearchAlert(message) {
  Swal.fire({
    icon: "warning",
    title: "Búsqueda",
    text: message,
    timer: 2000,
    showConfirmButton: false,
    position: "top-end",
    toast: true,
  });
}

// Función para inicializar autocompletado en tiempo real
function initSearchAutocomplete() {
  const searchInput = document.getElementById("input-busqueda");

  if (!searchInput) return;

  let debounceTimer;

  searchInput.addEventListener("input", function () {
    clearTimeout(debounceTimer);
    const searchTerm = this.value.trim();

    if (searchTerm.length < 2) {
      hideSearchSuggestions();
      return;
    }

    debounceTimer = setTimeout(() => {
      fetchSearchSuggestions(searchTerm);
    }, 300);
  });

  // Cerrar sugerencias al hacer clic fuera
  document.addEventListener("click", function (e) {
    if (!e.target.closest(".navbar-search-block")) {
      hideSearchSuggestions();
    }
  });
}

// Función para obtener sugerencias de búsqueda
function fetchSearchSuggestions(searchTerm) {
  const formData = new FormData();
  formData.append("funcion", "buscar_sugerencias");
  formData.append("termino", searchTerm);

  fetch(BASE_PATH + "Controllers/BusquedaController.php", {
    method: "POST",
    body: formData,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.sugerencias && data.sugerencias.length > 0) {
        showSearchSuggestions(data.sugerencias, searchTerm);
      } else {
        hideSearchSuggestions();
      }
    })
    .catch((error) => {
      console.error("Error fetching suggestions:", error);
      hideSearchSuggestions();
    });
}

// Función para mostrar sugerencias de búsqueda
function showSearchSuggestions(suggestions, searchTerm) {
  hideSearchSuggestions();

  const searchContainer = document.querySelector(".navbar-search-block");
  const searchInput = document.getElementById("input-busqueda");

  if (!searchContainer || !searchInput) return;

  const suggestionsContainer = document.createElement("div");
  suggestionsContainer.className = "search-suggestions";
  suggestionsContainer.innerHTML = `
        <div class="suggestions-list">
            ${suggestions
              .map(
                (item) => `
                <div class="suggestion-item" data-type="${item.tipo}" data-value="${item.producto}">
                    <i class="fas ${getSuggestionIcon(item.tipo)} mr-2"></i>
                    <span class="suggestion-text">
                        <strong>${highlightText(item.producto, searchTerm)}</strong>
                        ${item.marca ? `<small class="text-muted ml-2">${item.marca}</small>` : ""}
                    </span>
                    <span class="suggestion-category badge badge-light">${item.categoria}</span>
                </div>
            `,
              )
              .join("")}
        </div>
    `;

  searchContainer.appendChild(suggestionsContainer);

  // Agregar event listeners a las sugerencias
  suggestionsContainer.querySelectorAll(".suggestion-item").forEach((item) => {
    item.addEventListener("click", function () {
      const value = this.getAttribute("data-value");
      const type = this.getAttribute("data-type");
      handleSuggestionClick(value, type);
    });
  });
}

// Función para obtener icono según el tipo de sugerencia
function getSuggestionIcon(type) {
  switch (type) {
    case "producto":
      return "fa-box";
    case "marca":
      return "fa-tag";
    case "categoria":
      return "fa-folder";
    default:
      return "fa-search";
  }
}

// Función para resaltar texto en las sugerencias
function highlightText(text, searchTerm) {
  if (!searchTerm) return text;
  const regex = new RegExp(`(${searchTerm})`, "gi");
  return text.replace(regex, "<mark>$1</mark>");
}

// Función para manejar clic en sugerencia
function handleSuggestionClick(value, type) {
  const searchInput = document.getElementById("input-busqueda");
  searchInput.value = value;

  // Redirigir según el tipo
  let redirectUrl = BASE_PATH_URL + "producto.php?";

  switch (type) {
    case "producto":
    case "marca":
      redirectUrl += `busqueda=${encodeURIComponent(value)}`;
      break;
    case "categoria":
      redirectUrl += `categoria=${encodeURIComponent(value)}`;
      break;
  }

  hideSearchSuggestions();
  window.location.href = redirectUrl;
}

// Función para ocultar sugerencias
function hideSearchSuggestions() {
  const existingSuggestions = document.querySelector(".search-suggestions");
  if (existingSuggestions) {
    existingSuggestions.remove();
  }
}

// Estilos CSS para las sugerencias (puedes agregarlos a nexusbuy.css)
const searchSuggestionsCSS = `
.search-suggestions {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: white;
    border: 1px solid #dee2e6;
    border-radius: 0.25rem;
    box-shadow: 0 0.5rem 1rem rgba(0,0,0,.15);
    z-index: 1000;
    max-height: 400px;
    overflow-y: auto;
}

.suggestions-list {
    padding: 0.5rem 0;
}

.suggestion-item {
    display: flex;
    align-items: center;
    padding: 0.5rem 1rem;
    cursor: pointer;
    transition: background-color 0.2s;
}

.suggestion-item:hover {
    background-color: #f8f9fa;
}

.suggestion-item .suggestion-text {
    flex: 1;
}

.suggestion-item mark {
    background-color: #fff3cd;
    padding: 0;
}

.suggestion-category {
    font-size: 0.75rem;
}
`;

// Inicializar búsqueda cuando se carga la página
document.addEventListener("DOMContentLoaded", function () {
  initSearchAutocomplete();

  // También agregar estilos dinámicamente
  if (!document.querySelector("#search-suggestions-styles")) {
    const styleElement = document.createElement("style");
    styleElement.id = "search-suggestions-styles";
    styleElement.textContent = searchSuggestionsCSS;
    document.head.appendChild(styleElement);
  }
});

function initSidebarSearch() {
  const sidebarSearchInput = document.getElementById(
    "buscar-subcategoria-input",
  );
  const sidebarSearchButton = document.getElementById(
    "btn-buscar-subcategoria",
  );

  if (!sidebarSearchInput || !sidebarSearchButton) return;

  sidebarSearchButton.addEventListener("click", function () {
    const searchTerm = sidebarSearchInput.value.trim();

    if (searchTerm.length < 2) {
      Swal.fire({
        icon: "warning",
        title: "Búsqueda de categorías",
        text: "Ingresa al menos 2 caracteres",
        timer: 2000,
        showConfirmButton: false,
        position: "top-end",
        toast: true,
      });
      return;
    }

    searchSubcategories(searchTerm);
  });

  sidebarSearchInput.addEventListener("keypress", function (e) {
    if (e.key === "Enter") {
      sidebarSearchButton.click();
    }
  });
}

function searchSubcategories(searchTerm) {
  const formData = new FormData();
  formData.append("funcion", "buscar_subcategorias");
  formData.append("termino", searchTerm);

  fetch(BASE_PATH + "Controllers/BusquedaController.php", {
    method: "POST",
    body: formData,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success && data.subcategorias.length > 0) {
        showSubcategoryResults(data.subcategorias);
      } else {
        showNoSubcategoryResults(searchTerm);
      }
    })
    .catch((error) => {
      console.error("Error searching subcategories:", error);
      showSubcategorySearchError();
    });
}

function showSubcategoryResults(subcategorias) {
  const categoriesContainer = document.getElementById(
    "menu-categorias-dinamico",
  );

  if (!categoriesContainer) return;

  const resultsHTML = `
        <div class="subcategory-search-results">
            <div class="search-results-header">
                <h6 class="text-light">
                    <i class="fas fa-search mr-2"></i>Resultados de búsqueda
                </h6>
                <button class="btn btn-sm btn-outline-light" onclick="loadDefaultCategories()">
                    <i class="fas fa-arrow-left"></i> Volver
                </button>
            </div>
            <div class="list-group">
                ${subcategorias
                  .map(
                    (item) => `
                    <a href="${BASE_PATH_URL}producto.php?subcategoria=${encodeURIComponent(item.nombre)}" 
                       class="list-group-item list-group-item-action bg-transparent border-light text-white">
                        <i class="fas fa-folder-open mr-2"></i>
                        <span class="font-weight-bold">${item.nombre}</span>
                        <small class="text-light-50 ml-2">(${item.categoria})</small>
                    </a>
                `,
                  )
                  .join("")}
            </div>
        </div>
    `;

  categoriesContainer.innerHTML = resultsHTML;
}

function showNoSubcategoryResults(searchTerm) {
  const categoriesContainer = document.getElementById(
    "menu-categorias-dinamico",
  );

  if (!categoriesContainer) return;

  categoriesContainer.innerHTML = `
        <div class="subcategory-no-results text-center py-4">
            <i class="fas fa-search fa-2x text-light mb-3"></i>
            <h6 class="text-light">No se encontraron subcategorías</h6>
            <p class="text-light-50">No hay resultados para: "${searchTerm}"</p>
            <button class="btn btn-sm btn-outline-light" onclick="loadDefaultCategories()">
                <i class="fas fa-arrow-left"></i> Ver todas las categorías
            </button>
        </div>
    `;
}

function loadDefaultCategories() {
  // Esta función debería recargar las categorías normales
  // Depende de cómo tengas implementado el cargado de categorías
  console.log("Cargar categorías por defecto");
  // location.reload(); // Opción simple
}

// Inicializar búsqueda en sidebar
document.addEventListener("DOMContentLoaded", function () {
  initSidebarSearch();
});

// ============================================
// MONEDA GLOBAL - VERSIÓN CORREGIDA
// ============================================

$(document).ready(function () {
    // console.log("✅ Sistema de moneda inicializado");
    
    // Variables globales para la moneda
    let monedaActual = localStorage.getItem("moneda-seleccionada") || "CUP";
    let simboloMonedaActual = "$";
    let preciosOriginales = {}; // Para almacenar precios base

    // Establecer moneda inicial
    $("#moneda-interface").val(monedaActual);

    // Inicializar después de un breve delay
    setTimeout(() => {
        inicializarSistemaMoneda();
    }, 500);

    // ================= FUNCIONES PRINCIPALES =================

    async function inicializarSistemaMoneda() {
        try {
            // 1. Obtener información de la moneda actual
            const infoMoneda = await obtenerInfoMoneda(monedaActual);
            if (infoMoneda.success) {
                simboloMonedaActual = infoMoneda.moneda.simbolo;
                
                // 2. Guardar precios originales si estamos en página de descripción
                if ($("#informacion_precios").length > 0) {
                    guardarPreciosOriginales();
                }
                
                // 3. Actualizar precios
                await actualizarPreciosMoneda(monedaActual);
            }
        } catch (error) {
            console.error("❌ Error inicializando sistema de moneda:", error);
            restaurarPreciosOriginales();
        }
    }

    // Función para obtener información de una moneda
    async function obtenerInfoMoneda(codigoMoneda) {
        try {
            const response = await $.post(BASE_PATH + "Controllers/MonedaController.php", {
                funcion: "obtener_tasa_cambio",
                moneda: codigoMoneda
            });
            return typeof response === 'string' ? JSON.parse(response) : response;
        } catch (error) {
            console.log(response);
            console.error("Error obteniendo info moneda:", error);
            return { success: false, error: error.message };
        }
    }

    // Función principal para actualizar precios
    async function actualizarPreciosMoneda(codigoMoneda) {
        try {
            // console.log(`🔄 Actualizando precios a: ${codigoMoneda}`);
            
            const response = await $.post(BASE_PATH + "Controllers/MonedaController.php", {
                funcion: "convertir_precios_productos",
                moneda: codigoMoneda
            });

            const data = typeof response === 'string' ? JSON.parse(response) : response;

            if (data.success) {
                // Actualizar variables globales
                monedaActual = codigoMoneda;
                simboloMonedaActual = data.moneda.simbolo;
                localStorage.setItem("moneda-seleccionada", codigoMoneda);

                // Actualizar precios en todas las vistas
                await actualizarPreciosIndex(data.productos);
                await actualizarPreciosCarrito();
                await actualizarPreciosCheckout();
                await actualizarPreciosFavoritos();
                await actualizarPreciosDescripcion();
                await actualizarPreciosPedidos();

                // Actualizar símbolos
                actualizarSimbolosMoneda();

                // console.log(`✅ Precios actualizados a ${codigoMoneda} (${simboloMonedaActual})`);
            } else {
                throw new Error(data.error || "Error desconocido");
            }
        } catch (error) {
            console.error("❌ Error actualizando precios:", error);
            restaurarPreciosOriginales();
        }
    }

    // ================= FUNCIONES ESPECÍFICAS POR VISTA =================

    // Función para actualizar precios en el index
    async function actualizarPreciosIndex(productos) {
        if ($("#productos").length > 0 || $(".product-card").length > 0) {
            // console.log("📦 Actualizando precios en productos destacados...");
            
            productos.forEach((producto) => {
                const precioFinal = producto.precio_descuento_convertido || producto.precio_convertido;
                const precioOriginal = producto.precio_convertido;

                // Buscar por ID primero (más preciso)
                $(`.product-card[data-id="${producto.id}"]`).each(function () {
                    actualizarCardProducto($(this), precioFinal, precioOriginal, producto.descuento);
                });

                // También buscar por nombre (fallback)
                if ($(`[data-id="${producto.id}"]`).length === 0) {
                    $(`.product-card:contains("${producto.producto}")`).each(function () {
                        actualizarCardProducto($(this), precioFinal, precioOriginal, producto.descuento);
                    });
                }
            });
        }
    }

    function actualizarCardProducto($card, precioFinal, precioOriginal, descuento) {
        // Actualizar precio con descuento
        $card.find("h4.text-danger, .current-price, .text-danger.font-weight-bold")
            .text(`${simboloMonedaActual} ${precioFinal.toFixed(2)}`);

        // Actualizar precio tachado si hay descuento
        if (descuento > 0) {
            $card.find('.original-price, span[style*="line-through"], .text-decoration-line-through')
                .text(`${simboloMonedaActual} ${precioOriginal.toFixed(2)}`);
        }
    }

    // Función MEJORADA para actualizar precios en descripción
    async function actualizarPreciosDescripcion() {
        if ($("#informacion_precios").length > 0 && Object.keys(preciosOriginales).length > 0) {
            try {
                console.log("📄 Actualizando precios en descripción...");
                
                const infoMoneda = await obtenerInfoMoneda(monedaActual);
                
                if (infoMoneda.success) {
                    const tasa = parseFloat(infoMoneda.tasa_cambio);
                    const simbolo = infoMoneda.moneda.simbolo;

                    // ✅ CORRECCIÓN: MULTIPLICAR en lugar de dividir
                    const precioOriginalConvertido = preciosOriginales.precio / tasa;
                    const precioDescuentoConvertido = preciosOriginales.precio_descuento / tasa;

                    // Reconstruir el template
                    let nuevoTemplate = "";

                    // Agregar calificación si existe
                    if (preciosOriginales.calificacion && preciosOriginales.calificacion > 0) {
                        nuevoTemplate += `</br>`;
                        for (let i = 0; i < Math.floor(preciosOriginales.calificacion); i++) {
                            nuevoTemplate += `<i class="fas fa-star text-warning"></i>`;
                        }
                        if (preciosOriginales.calificacion % 1 !== 0) {
                            nuevoTemplate += `<i class="fas fa-star-half-alt text-warning"></i>`;
                        }
                        const estrellasVacias = 5 - Math.ceil(preciosOriginales.calificacion);
                        for (let i = 0; i < estrellasVacias; i++) {
                            nuevoTemplate += `<i class="far fa-star text-warning"></i>`;
                        }
                        nuevoTemplate += `</br>`;
                    }

                    // Agregar precios
                    if (preciosOriginales.descuento > 0) {
                        nuevoTemplate += `
                            <span class="text-muted" style="text-decoration: line-through">
                                ${simbolo} ${precioOriginalConvertido.toFixed(2)}
                            </span>
                            <span class="text-muted ms-2">-${preciosOriginales.descuento}%</span></br>
                        `;
                    }

                    nuevoTemplate += `<h4 class="text-danger mt-2">${simbolo} ${precioDescuentoConvertido.toFixed(2)}</h4>`;

                    // Actualizar
                    $("#informacion_precios").html(nuevoTemplate);
                }
            } catch (error) {
                console.error("Error en descripción:", error);
            }
        }
    }

    // Función para guardar precios originales
    function guardarPreciosOriginales() {
        try {
            // Extraer precios del HTML actual
            const precioText = $(".text-danger:contains('$')").text();
            const precioMatch = precioText.match(/\$ (\d+\.?\d*)/);
            
            if (precioMatch) {
                preciosOriginales = {
                    precio: parseFloat(precioMatch[1]),
                    precio_descuento: parseFloat(precioMatch[1]), // ajustar si hay descuento
                    descuento: 0,
                    calificacion: 0
                };
                
                console.log("💰 Precios originales guardados:", preciosOriginales);
            }
        } catch (error) {
            console.error("Error guardando precios:", error);
        }
    }

    // Función para actualizar símbolos
    function actualizarSimbolosMoneda() {
        $("h4.text-danger, .text-danger.font-weight-bold, .current-price").each(function () {
            const $element = $(this);
            const texto = $element.text().trim();
            
            if (texto && !texto.startsWith(simboloMonedaActual)) {
                const soloNumero = texto.replace(/[^\d.,]/g, "").trim();
                if (soloNumero && !isNaN(parseFloat(soloNumero))) {
                    $element.text(`${simboloMonedaActual} ${parseFloat(soloNumero).toFixed(2)}`);
                }
            }
        });
    }

    // Función para restaurar precios originales
    function restaurarPreciosOriginales() {
        // console.log("🔄 Restaurando precios originales...");
        // Lógica para restaurar según sea necesario
    }

    // ================= FUNCIONES PARA OTRAS VISTAS =================

    async function actualizarPreciosCarrito() {
        // Tu lógica actual para el carrito
        // console.log("🛒 Actualizando carrito...");
    }

    async function actualizarPreciosCheckout() {
        // Tu lógica actual para checkout
        // console.log("💳 Actualizando checkout...");
    }

    async function actualizarPreciosFavoritos() {
        // Tu lógica actual para favoritos
        // console.log("❤️ Actualizando favoritos...");
    }

    async function actualizarPreciosPedidos() {
        if ($('.pedido-card').length > 0 || $('#lista-pedidos').length > 0) {
            try {
                console.log('📋 Actualizando precios en pedidos...');
                
                const infoMoneda = await obtenerInfoMoneda(monedaActual);
                
                if (infoMoneda.success) {
                    const tasa = parseFloat(infoMoneda.tasa_cambio);
                    const simbolo = infoMoneda.moneda.simbolo;
                    
                    // 1. Actualizar totales en tarjetas
                    $('.pedido-card .text-danger').each(function() {
                        const $element = $(this);
                        const texto = $element.text();
                        const precioMatch = texto.match(/\$ (\d+\.?\d*)/);
                        
                        if (precioMatch) {
                            const precioOriginal = parseFloat(precioMatch[1]);
                            const precioConvertido = (precioOriginal / tasa).toFixed(2); // ✅ MULTIPLICAR
                            $element.text(`${simbolo} ${precioConvertido}`);
                        }
                    });
                    
                    // 2. Actualizar otros elementos de precio
                    $('.pedido-card .d-flex.justify-content-between').each(function() {
                        const $element = $(this);
                        const texto = $element.text();
                        const precioMatch = texto.match(/\$ (\d+\.?\d*)/);
                        
                        if (precioMatch) {
                            const precioOriginal = parseFloat(precioMatch[1]);
                            const precioConvertido = (precioOriginal / tasa).toFixed(2); // ✅ MULTIPLICAR
                            const nuevoTexto = texto.replace(/\$ \d+\.?\d*/, `${simbolo} ${precioConvertido}`);
                            $element.text(nuevoTexto);
                        }
                    });
                    
                    console.log('✅ Precios de pedidos actualizados');
                }
            } catch (error) {
                console.error('❌ Error actualizando pedidos:', error);
            }
        }
    }

    // ================= EVENT LISTENERS =================

    // Evento para cambiar moneda desde el selector
    $("#moneda-interface").change(function() {
        const nuevaMoneda = $(this).val();
        if (nuevaMoneda !== monedaActual) {
            actualizarPreciosMoneda(nuevaMoneda);
        }
    });

    // Exponer funciones útiles al ámbito global
    window.convertirMoneda = function(codigoMoneda) {
        return actualizarPreciosMoneda(codigoMoneda);
    };

    window.obtenerMonedaActual = function() {
        return {
            codigo: monedaActual,
            simbolo: simboloMonedaActual
        };
    };
});
