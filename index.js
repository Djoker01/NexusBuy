$(document).ready(function () {
  var funcion;
  verificar_sesion();
  inicializarBusqueda();
  inicializarBusquedaSubcategorias();

  const urlParams = new URLSearchParams(window.location.search);
  const subcategoriaId = urlParams.get("subcategoria");
  const subcategoriaNombre = urlParams.get("nombre");

  // Esperar un momento para asegurar que menu_subcategoria.js esté cargado
  setTimeout(() => {
    if (subcategoriaId && subcategoriaNombre) {
      // Verificar que la función existe antes de llamarla
      if (typeof window.filtrarPorSubcategoria === "function") {
        //console.log('Ejecutando filtro por subcategoría:', subcategoriaId, subcategoriaNombre);
        window.filtrarPorSubcategoria(subcategoriaId, subcategoriaNombre);
      } else {
        console.error("Error: filtrarPorSubcategoria no está disponible");
        //console.log('Cargando todos los productos como fallback...');
        llenar_productos();
      }
    } else {
      llenar_productos();
    }
  }, 500); // Esperar 500ms para que los scripts se carguen

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
      } else {
        $("#nav_usuario").hide();
        $("#favoritos").hide();
      }
    });
  }

  async function llenar_productos() {
    funcion = "llenar_productos";
    try {
      let data = await fetch("Controllers/ProductoTiendaController.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: "funcion=" + funcion,
      });

      if (data.ok) {
        let response = await data.text();
        try {
          let productos = JSON.parse(response);
          let template = ``;
          if (productos.length === 0) {
            template = `
              <div class="col-12 text-center py-5">
                <div class="alert alert-info">
                  <i class="fas fa-info-circle mr-2"></i>
                  No hay productos disponible en este momento.
                </div>
              </div>
            `;
          } else {
            productos.forEach((producto) => {
              template += `
              <div class="col-sm-2 mb-4">
                  <div class="card product-card h-100">
                    <div class="card-body">
                      <div class="row">
                        <div class="col-sm-12">
                          <img src="Util/Img/Producto/${
                            producto.imagen
                          }" class="img-fluid">
                        </div>
                        <div class="col-sm-12 mt-2">
                          <span class="text-muted float-left">${
                            producto.marca
                          }</span></br>
                          <a class="titulo_producto" href="Views/descripcion.php?name=${encodeURIComponent(
                            producto.producto
                          )}&&id=${producto.id}">${producto.producto}</a>`;
              if (producto.envio == "gratis") {
                template += `</br>`;
                template += `<span class="badge bg-success">Envio gratis</span>`;
              }
              if (producto.calificacion != 0) {
                template += `</br>`;
                for (let index = 0; index < producto.calificacion; index++) {
                  template += `<i class="fas fa-star text-warning"></i>`;
                }
                let estrellas_faltantes = 5 - producto.calificacion;
                for (let index = 0; index < estrellas_faltantes; index++) {
                  template += `<i class="far fa-star text-warning"></i>`;
                }
                template += `</br>`;
              }
              if (producto.descuento != 0) {
                template += `
                          <span class="text-muted" style="text-decoration: line-through">$ ${producto.precio}</span>
                          <span class="text-muted">-${producto.descuento}%</span></br>
                `;
              }
              template += `           
                          <h4 class="text-danger">$ ${producto.precio_descuento}</h4>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              `;
            });
          }

          $("#productos").html(template);
        } catch (error) {
          console.error(error);
          //console.log(response);
        }
      } else {
        Swal.fire({
          icon: "error",
          title: data.statusText,
          text: "Hubo conflicto de codigo: " + data.status,
        });
      }
    } catch (error) {
      console.error("Error en llenar_productos:", error);
      Swal.fire("Error", "No se pudieron cargar los productos", "error");
    }
  }

  // Función global para realizar búsqueda
  window.realizarBusqueda = async function (termino, pagina = 1) {
    try {
      //console.log('Buscando:', termino, 'Página:', pagina);

      // Mostrar loading
      $("#productos").html(`
          <div class="col-12 text-center py-5">
              <div class="spinner-border text-primary" role="status">
                  <span class="sr-only">Buscando productos...</span>
              </div>
              <p class="text-muted mt-2">Buscando "${termino}"...</p>
          </div>
      `);

      // Actualizar estado
      window.busquedaActiva = true;
      window.terminoBusquedaActual = termino;
      window.paginaActual = pagina;

      // Ocultar sugerencias
      window.ocultarSugerencias();

      const response = await fetch("Controllers/BusquedaController.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `funcion=buscar_productos&termino=${encodeURIComponent(
          termino
        )}&pagina=${pagina}&limite=12`,
      });

      if (response.ok) {
        const data = await response.json();

        if (data.success) {
          window.mostrarResultadosBusqueda(data);
        } else {
          throw new Error(data.error || "Error en la búsqueda");
        }
      } else {
        throw new Error("Error de conexión");
      }
    } catch (error) {
      console.error("Error en búsqueda:", error);
      window.mostrarErrorBusqueda("Error al realizar la búsqueda");
    }
  };

  // Función global para mostrar resultados
  window.mostrarResultadosBusqueda = function (data) {
    let template = "";

    if (data.productos.length === 0) {
      template = `
          <div class="col-12 text-center py-5">
              <div class="alert alert-info">
                  <i class="fas fa-search mr-2"></i>
                  No se encontraron productos para "<strong>${data.termino_busqueda}</strong>"
              </div>
              <button class="btn btn-primary mt-3" onclick="window.limpiarBusqueda()">
                  <i class="fas fa-arrow-left mr-2"></i>
                  Ver Todos los Productos
              </button>
          </div>
      `;
    } else {
      // Mostrar información de resultados
      template += `
          <div class="col-12 mb-4">
              <div class="alert alert-success">
                  <i class="fas fa-check-circle mr-2"></i>
                  Se encontraron <strong>${
                    data.total_resultados
                  }</strong> productos para "<strong>${
        data.termino_busqueda
      }</strong>"
                  ${
                    data.total_resultados > 12
                      ? ` - Página ${data.pagina_actual} de ${data.total_paginas}`
                      : ""
                  }
              </div>
          </div>
      `;

      // Generar template de productos
      data.productos.forEach((producto) => {
        template += window.generarTemplateProducto(producto);
      });

      // Agregar paginación si es necesario
      if (data.total_paginas > 1) {
        template += window.generarPaginacion(data);
      }
    }

    $("#productos").html(template);

    // Actualizar título y breadcrumb
    $(".card-title").html(
      `Resultados de "${data.termino_busqueda}" <span class="badge bg-success">${data.total_resultados} productos</span>`
    );

    // Agregar botón para limpiar búsqueda al breadcrumb
    $(".breadcrumb").html(`
      <li class="breadcrumb-item"><a href="index.php">Home</a></li>
      <li class="breadcrumb-item active">
          Búsqueda: "${data.termino_busqueda}"
          <button class="btn btn-sm btn-outline-secondary ml-2" onclick="window.limpiarBusqueda()">
              <i class="fas fa-times"></i> Limpiar
          </button>
      </li>
  `);
  };

  // Función global para generar template de producto
  window.generarTemplateProducto = function (producto) {
    let template = `
      <div class="col-sm-2 mb-4">
          <div class="card product-card h-100">
              <div class="card-body">
                  <div class="row">
                      <div class="col-sm-12">
                          <img src="Util/Img/Producto/${
                            producto.imagen
                          }" class="img-fluid" alt="${producto.producto}">
                      </div>
                      <div class="col-sm-12 mt-2">
                          <span class="text-muted float-left">${
                            producto.marca
                          }</span></br>
                          <a class="titulo_producto" href="Views/descripcion.php?name=${encodeURIComponent(
                            producto.producto
                          )}&&id=${producto.id}">${producto.producto}</a>`;

    if (producto.envio == "gratis") {
      template += `</br><span class="badge bg-success">Envio gratis</span>`;
    }

    if (producto.calificacion != 0) {
      template += `</br>`;
      for (let index = 0; index < producto.calificacion; index++) {
        template += `<i class="fas fa-star text-warning"></i>`;
      }
      let estrellas_faltantes = 5 - producto.calificacion;
      for (let index = 0; index < estrellas_faltantes; index++) {
        template += `<i class="far fa-star text-warning"></i>`;
      }
      template += `</br>`;
    }

    if (producto.descuento != 0) {
      template += `
          <span class="text-muted" style="text-decoration: line-through">$ ${producto.precio}</span>
          <span class="text-muted">-${producto.descuento}%</span></br>
      `;
    }

    template += `           
          <h4 class="text-danger">$ ${producto.precio_descuento}</h4>
      </div>
  </div>
</div>
</div>
</div>`;

    return template;
  };

  // Función global para generar paginación
  window.generarPaginacion = function (data) {
    let paginacion = `
      <div class="col-12">
          <nav aria-label="Paginación de resultados">
              <ul class="pagination justify-content-center">
  `;

    // Botón anterior
    if (data.pagina_actual > 1) {
      paginacion += `
          <li class="page-item">
              <button class="page-link" onclick="window.realizarBusqueda('${
                window.terminoBusquedaActual
              }', ${data.pagina_actual - 1})">
                  <i class="fas fa-chevron-left"></i>
              </button>
          </li>
      `;
    }

    // Números de página
    for (let i = 1; i <= data.total_paginas; i++) {
      if (i === data.pagina_actual) {
        paginacion += `<li class="page-item active"><span class="page-link">${i}</span></li>`;
      } else if (i >= data.pagina_actual - 2 && i <= data.pagina_actual + 2) {
        paginacion += `
              <li class="page-item">
                  <button class="page-link" onclick="window.realizarBusqueda('${window.terminoBusquedaActual}', ${i})">${i}</button>
              </li>
          `;
      }
    }

    // Botón siguiente
    if (data.pagina_actual < data.total_paginas) {
      paginacion += `
          <li class="page-item">
              <button class="page-link" onclick="window.realizarBusqueda('${
                window.terminoBusquedaActual
              }', ${data.pagina_actual + 1})">
                  <i class="fas fa-chevron-right"></i>
              </button>
          </li>
      `;
    }

    paginacion += `
              </ul>
          </nav>
      </div>
  `;

    return paginacion;
  };

  // Función global para limpiar búsqueda
  window.limpiarBusqueda = function () {
    window.busquedaActiva = false;
    window.terminoBusquedaActual = "";
    window.paginaActual = 1;

    $("#input-busqueda").val("");
    window.ocultarSugerencias();

    // Restaurar vista normal
    llenar_productos();

    // Restaurar título y breadcrumb
    $(".card-title").html("Productos");
    $(".breadcrumb").html(`
      <li class="breadcrumb-item"><a href="index.php">Home</a></li>
      <li class="breadcrumb-item active">Home</li>
  `);
  };

  // Función global para mostrar sugerencias
  window.mostrarSugerencias = function (sugerencias, termino) {
    let html = "";

    if (sugerencias.length > 0) {
      html = '<div class="dropdown-menu show w-100" id="sugerencias-busqueda">';

      sugerencias.forEach((sugerencia) => {
        let icono = "";
        let badge = "";

        switch (sugerencia.tipo) {
          case "producto":
            icono = "fas fa-box";
            badge =
              '<span class="badge bg-primary float-right">Producto</span>';
            break;
          case "marca":
            icono = "fas fa-tag";
            badge = '<span class="badge bg-success float-right">Marca</span>';
            break;
          case "categoria":
            icono = "fas fa-folder";
            badge =
              '<span class="badge bg-warning float-right">Categoría</span>';
            break;
        }

        html += `
              <a class="dropdown-item" href="javascript:void(0)" onclick="window.realizarBusqueda('${sugerencia.producto.replace(
                /'/g,
                "\\'"
              )}', 1)">
                  <i class="${icono} mr-2"></i>
                  ${sugerencia.producto}
                  ${
                    sugerencia.marca
                      ? `<small class="text-muted"> - ${sugerencia.marca}</small>`
                      : ""
                  }
                  ${badge}
              </a>
          `;
      });

      html += `</div>`;
    }

    $("#input-busqueda").parent().append(html);
  };

  // Función global para ocultar sugerencias
  window.ocultarSugerencias = function () {
    $("#sugerencias-busqueda").remove();
  };

  // Función global para mostrar error
  window.mostrarErrorBusqueda = function (mensaje) {
    $("#productos").html(`
      <div class="col-12 text-center py-5">
          <div class="alert alert-danger">
              <i class="fas fa-exclamation-triangle mr-2"></i>
              ${mensaje}
          </div>
          <button class="btn btn-primary mt-3" onclick="window.limpiarBusqueda()">
              <i class="fas fa-arrow-left mr-2"></i>
              Volver a Todos los Productos
          </button>
      </div>
  `);
  };

  // ================= FUNCIONES LOCALES (solo dentro del document ready) =================

  // Función local para inicializar búsqueda (no necesita ser global)
  function inicializarBusqueda() {
    //console.log('Inicializando búsqueda...');

    // Inicializar variables globales
    window.busquedaActiva = false;
    window.terminoBusquedaActual = "";
    window.paginaActual = 1;

    // Evento para el formulario de búsqueda
    $("#form-busqueda").on("submit", function (e) {
      e.preventDefault();
      const termino = $("#input-busqueda").val().trim();
      if (termino) {
        window.realizarBusqueda(termino, 1);
      }
    });

    // Evento para el botón de limpiar búsqueda
    $(document).on("click", '[data-widget="navbar-search"]', function () {
      window.limpiarBusqueda();
    });

    // Búsqueda en tiempo real con debounce
    let timeoutBusqueda;
    $("#input-busqueda").on("input", function () {
      clearTimeout(timeoutBusqueda);
      const termino = $(this).val().trim();

      if (termino.length >= 2) {
        timeoutBusqueda = setTimeout(() => {
          buscarSugerencias(termino);
        }, 300);
      } else {
        window.ocultarSugerencias();
      }
    });

    // Cerrar sugerencias al hacer clic fuera
    $(document).on("click", function (e) {
      if (!$(e.target).closest(".navbar-search-block").length) {
        window.ocultarSugerencias();
      }
    });

    //console.log('Búsqueda inicializada');
  }

  // Función local para buscar sugerencias (no necesita ser global)
  async function buscarSugerencias(termino) {
    try {
      const response = await fetch("Controllers/BusquedaController.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `funcion=buscar_sugerencias&termino=${encodeURIComponent(
          termino
        )}`,
      });

      if (response.ok) {
        const data = await response.json();
        window.mostrarSugerencias(data.sugerencias, termino);
      }
    } catch (error) {
      console.error("Error buscando sugerencias:", error);
    }
  }

  // ================= BÚSQUEDA DE SUBCATEGORÍAS =================

  function inicializarBusquedaSubcategorias() {
    //console.log('Inicializando búsqueda de subcategorías...');

    let timeoutBusquedaSubcategorias;

    // Evento para input de búsqueda de subcategorías
    $("#buscar-subcategoria-input").on("input", function () {
      clearTimeout(timeoutBusquedaSubcategorias);
      const termino = $(this).val().trim();

      if (termino.length >= 2) {
        // Mostrar loading
        mostrarLoadingSubcategorias();

        timeoutBusquedaSubcategorias = setTimeout(() => {
          buscarSubcategorias(termino);
        }, 500);
      } else if (termino.length === 0) {
        // Si está vacío, mostrar todas las categorías
        restaurarCategoriasCompletas();
        ocultarSugerenciasSubcategorias();
      } else {
        ocultarSugerenciasSubcategorias();
      }
    });

    // Evento para el botón de búsqueda
    $("#btn-buscar-subcategoria").on("click", function () {
      const termino = $("#buscar-subcategoria-input").val().trim();
      if (termino) {
        buscarSubcategorias(termino);
      }
    });

    // Evento para enter en el input
    $("#buscar-subcategoria-input").on("keypress", function (e) {
      if (e.which === 13) {
        e.preventDefault();
        const termino = $(this).val().trim();
        if (termino) {
          buscarSubcategorias(termino);
        }
      }
    });

    // Cerrar sugerencias al hacer clic fuera
    $(document).on("click", function (e) {
      if (
        !$(e.target).closest(".sidebar-search").length &&
        !$(e.target).closest("#sugerencias-subcategorias").length
      ) {
        ocultarSugerenciasSubcategorias();
      }
    });
  }

  // Función para buscar subcategorías
  async function buscarSubcategorias(termino) {
    try {
      //console.log('Buscando subcategorías:', termino);

      const response = await fetch("Controllers/BusquedaController.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `funcion=buscar_subcategorias&termino=${encodeURIComponent(
          termino
        )}`,
      });

      if (response.ok) {
        const data = await response.json();

        if (data.success) {
          mostrarResultadosSubcategorias(data.subcategorias, termino);
        } else {
          mostrarErrorSubcategorias(data.error || "Error en la búsqueda");
        }
      } else {
        throw new Error("Error de conexión");
      }
    } catch (error) {
      console.error("Error buscando subcategorías:", error);
      mostrarErrorSubcategorias("Error al buscar categorías");
    }
  }

  // Función para mostrar resultados de búsqueda de subcategorías
  function mostrarResultadosSubcategorias(subcategorias, termino) {
    let html = "";

    if (subcategorias.length === 0) {
      html = `
          <div class="sugerencia-item text-center text-muted">
              <i class="fas fa-search mr-2"></i>
              No se encontraron categorías para "<strong>${termino}</strong>"
          </div>
      `;
    } else {
      // Agrupar por categoría principal
      const categoriasAgrupadas = {};

      subcategorias.forEach((item) => {
        if (!categoriasAgrupadas[item.categoria]) {
          categoriasAgrupadas[item.categoria] = [];
        }
        categoriasAgrupadas[item.categoria].push(item);
      });

      // Generar HTML agrupado
      Object.keys(categoriasAgrupadas).forEach((categoria) => {
        html += `<div class="sugerencia-item categoria-principal">${categoria}</div>`;

        categoriasAgrupadas[categoria].forEach((subcategoria) => {
          html += `
                  <div class="sugerencia-item subcategoria-item" 
                       data-id="${subcategoria.id}" 
                       data-nombre="${subcategoria.nombre}">
                      <i class="fas fa-folder-open mr-2"></i>
                      ${subcategoria.nombre}
                  </div>
              `;
        });
      });
    }

    // Mostrar sugerencias
    mostrarSugerenciasSubcategorias(html);

    // Agregar eventos a los items
    $(".sugerencia-item.subcategoria-item").on("click", function () {
      const id = $(this).data("id");
      const nombre = $(this).data("nombre");
      seleccionarSubcategoria(id, nombre);
    });
  }

  // Función para seleccionar una subcategoría
  function seleccionarSubcategoria(id, nombre) {
    //console.log('Subcategoría seleccionada:', id, nombre);

    // Limpiar búsqueda
    $("#buscar-subcategoria-input").val("");
    ocultarSugerenciasSubcategorias();

    // Filtrar productos por subcategoría
    if (typeof window.filtrarPorSubcategoria === "function") {
      window.filtrarPorSubcategoria(id, nombre);
    } else {
      // Si no existe la función, redirigir con parámetros
      window.location.href = `index.php?subcategoria=${id}&nombre=${encodeURIComponent(
        nombre
      )}`;
    }
  }

  // Función para mostrar sugerencias de subcategorías
  function mostrarSugerenciasSubcategorias(html) {
    // Remover sugerencias anteriores
    $("#sugerencias-subcategorias").remove();

    // Crear contenedor de sugerencias
    const sugerenciasHTML = `
      <div id="sugerencias-subcategorias">
          ${html}
      </div>
  `;

    // Insertar después del input group
    $(".sidebar-search").append(sugerenciasHTML);
  }

  // Función para ocultar sugerencias de subcategorías
  function ocultarSugerenciasSubcategorias() {
    $("#sugerencias-subcategorias").remove();
  }

  // Función para mostrar loading
  function mostrarLoadingSubcategorias() {
    const loadingHTML = `
      <div id="sugerencias-subcategorias">
          <div class="sidebar-search-loading">
              <div class="loading-subcategorias mr-2"></div>
              Buscando categorías...
          </div>
      </div>
  `;

    $("#sugerencias-subcategorias").remove();
    $(".sidebar-search").append(loadingHTML);
  }

  // Función para mostrar error
  function mostrarErrorSubcategorias(mensaje) {
    const errorHTML = `
      <div id="sugerencias-subcategorias">
          <div class="sugerencia-item text-center text-danger">
              <i class="fas fa-exclamation-triangle mr-2"></i>
              ${mensaje}
          </div>
      </div>
  `;

    $("#sugerencias-subcategorias").remove();
    $(".sidebar-search").append(errorHTML);
  }

  // Función para restaurar categorías completas
  function restaurarCategoriasCompletas() {
    // Esta función debería recargar el menú completo de categorías
    // Depende de cómo tengas implementado menu_subcategoria.js
    if (typeof window.cargarMenuCategorias === "function") {
      window.cargarMenuCategorias();
    }
  }
});
