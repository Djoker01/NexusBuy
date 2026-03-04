$(document).ready(function () {
  var paginaActual = 1;
  var totalPaginas = 1;
  var filtrosActuales = {};

  // Cargar productos iniciales
  cargarProductos();

  // ==============================================
  // FUNCIÓN PRINCIPAL: Cargar productos
  // ==============================================
  function cargarProductos(pagina = 1, filtros = {}) {
    paginaActual = pagina;
    filtrosActuales = filtros;

    // Mostrar loader
    $("#productos-container").html(`
            <tr>
                <td colspan="6" class="text-center" style="padding: 3rem;">
                    <i class="fas fa-spinner fa-spin fa-3x text-primary"></i>
                    <p class="mt-2">Cargando productos...</p>
                </td>
            </tr>
        `);

    $.ajax({
      url: BASE_PATH + "../../Controllers/ProductoTiendaController.php",
      type: "POST",
      data: {
        funcion: "obtener_productos_paginados",
        id_tienda: TIENDA_ACTUAL.id,
        pagina: pagina,
        por_pagina: 10,
      },
      dataType: "json",
      success: function (response) {
        console.log("Respuesta del servidor:", response);

        if (response.success) {
          renderizarProductos(response.data);
          renderizarPaginacion(response.paginacion);
        } else {
          mostrarTablaVacia();
        }
      },
      error: function (xhr, status, error) {
        console.error("Error:", error);
        mostrarError("Error al cargar los productos");
      },
    });
  }

  // ==============================================
  // RENDERIZAR PRODUCTOS - VERSIÓN MEJORADA
  // ==============================================
  function renderizarProductos(data) {
    if (!data || data.length === 0) {
      mostrarTablaVacia();
      return;
    }

    let template = "";

    data.forEach((item) => {
      let es_destacado = item.destacado == 1;
      // ===== LÓGICA DE STOCK MEJORADA - UNA SOLA LÍNEA =====
      let stockHTML = "";
      let stock = Number(item.stock);
      let minimo = Number(item.stock_minimo);

      if (stock <= 0) {
        stockHTML = `
        <div class="stock-info">
            <span class="stock-badge critical">AGOTADO</span>
        </div>
    `;
      }
      // Caso 2: Stock bajo (igual o menor al mínimo)
      else if (stock < minimo) {
        stockHTML = `
        <div class="stock-info">
                  <span class="stock-badge low">${stock} unidades</span>
                  <span class="stock-warning">
                      <i class="fas fa-exclamation-triangle"></i>
                      ¡Stock bajo! Mínimo: ${minimo}
                  </span>
              </div>
    `;
      }
      // Caso 3: Stock normal (mayor al mínimo)
      else {
        stockHTML = `
        <div class="stock-info">
            <span class="stock-badge normal">${stock} unidades</span>
        </div>
    `;
      }

      // Calcular precio con descuento
      let precioHTML = `
                <div class="price-info">
                    <span class="current-price">$${formatearNumero(item.precio)}</span>
                    <span class="currency">CUP</span>
            `;

      if (item.descuento > 0) {
        let precioOriginal = item.precio / (1 - item.descuento / 100);
        precioHTML += `
                    <div class="discount-info">
                        <span class="original-price">$${formatearNumero(Math.round(precioOriginal))}</span>
                        <span class="discount-badge">-${item.descuento}%</span>
                    </div>
                `;
      }

      precioHTML += `</div>`;

      template += `
            <tr>
                <td>
                    <div class="product-cell">
                        <img src="../../../Util/Img/Producto/${item.imagen || "producto_default.png"}" 
                             class="product-image" 
                             alt="${item.producto}"
                             onerror="this.src='../../../Util/Img/Producto/producto_default.png'">
                        <div class="product-info">
                            <div class="product-name-wrapper">
                                <h4>${item.producto}</h4>
                                ${es_destacado ? '<span class="featured-badge"><i class="fas fa-star"></i></span>' : ""}
                            </div>
                            <span class="product-sku">SKU: ${item.sku}</span>
                        </div>
                    </div>
                </td>
                <td>
                    ${precioHTML}
                </td>
                <td>
                    ${stockHTML}
                </td>
                <td>
                    <div class="sales-info">
                        <span class="sales-count">${item.ventas || 0}</span>
                        <span class="sales-label">ventas</span>
                    </div>
                </td>
                <td>
                    <span class="status-badge ${getEstadoClass(item.estado)}">
                        <i class="fas fa-circle"></i>
                        ${getEstadoTexto(item.estado)}
                    </span>
                </td>
                <td>
                    <div class="action-icons">
                        <button class="action-btn edit" onclick="editarProducto(${item.id})" title="Editar">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="action-btn copy" onclick="duplicarProducto(${item.id})" title="Duplicar">
                            <i class="fas fa-copy"></i>
                        </button>
                        <button class="action-btn delete" onclick="eliminarProducto(${item.id})" title="Eliminar">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
            `;
    });

    $("#productos-container").html(template);
  }

  // ==============================================
  // RENDERIZAR PAGINACIÓN
  // ==============================================
  function renderizarPaginacion(paginacion) {
    if (!paginacion || paginacion.total_paginas <= 1) {
      $(".pagination").hide();
      return;
    }

    $(".pagination").show();
    totalPaginas = paginacion.total_paginas;

    // Actualizar info
    const inicio = (paginaActual - 1) * 10 + 1;
    const fin = Math.min(paginaActual * 10, paginacion.total_registros);
    $(".pagination-info").html(
      `Mostrando <strong>${inicio}-${fin}</strong> de <strong>${paginacion.total_registros}</strong> productos`,
    );

    let controles = "";

    // Botón anterior
    controles += `
            <button class="page-btn" onclick="cambiarPagina(${paginaActual - 1})" 
                    ${paginaActual === 1 ? "disabled" : ""}>
                <i class="fas fa-chevron-left"></i>
            </button>
        `;

    // Números de página
    let inicioPaginas = Math.max(1, paginaActual - 2);
    let finPaginas = Math.min(paginacion.total_paginas, inicioPaginas + 4);

    if (inicioPaginas > 1) {
      controles += `<button class="page-btn" onclick="cambiarPagina(1)">1</button>`;
      if (inicioPaginas > 2) {
        controles += `<span class="page-dots">...</span>`;
      }
    }

    for (let i = inicioPaginas; i <= finPaginas; i++) {
      controles += `
                <button class="page-btn ${i === paginaActual ? "active" : ""}" 
                        onclick="cambiarPagina(${i})">${i}</button>
            `;
    }

    if (finPaginas < paginacion.total_paginas) {
      if (finPaginas < paginacion.total_paginas - 1) {
        controles += `<span class="page-dots">...</span>`;
      }
      controles += `
                <button class="page-btn" onclick="cambiarPagina(${paginacion.total_paginas})">
                    ${paginacion.total_paginas}
                </button>
            `;
    }

    // Botón siguiente
    controles += `
            <button class="page-btn" onclick="cambiarPagina(${paginaActual + 1})"
                    ${paginaActual === paginacion.total_paginas ? "disabled" : ""}>
                <i class="fas fa-chevron-right"></i>
            </button>
        `;

    $(".pagination-controls").html(controles);
  }

  // ==============================================
  // FUNCIONES AUXILIARES
  // ==============================================
  window.cambiarPagina = function (pagina) {
    if (pagina < 1 || pagina > totalPaginas) return;
    cargarProductos(pagina, filtrosActuales);
  };

  function formatearNumero(numero) {
    return Math.round(numero)
      .toString()
      .replace(/\B(?=(\d{3})+(?!\d))/g, ",");
  }

  function getEstadoClass(estado) {
    const clases = {
      activo: "active",
      inactivo: "inactive",
      agotado: "exhausted",
      descontinuado: "discontinued",
      pausado: "paused",
    };
    return clases[estado] || "active";
  }

  function getEstadoTexto(estado) {
    const textos = {
      activo: "Activo",
      inactivo: "Inactivo",
      agotado: "Agotado",
      descontinuado: "Descontinuado",
      pausado: "Pausado",
    };
    return textos[estado] || "Activo";
  }

  function mostrarTablaVacia() {
    $("#productos-container").html(`
            <tr>
                <td colspan="6">
                    <div class="empty-table">
                        <i class="fas fa-box-open fa-4x"></i>
                        <h3>No hay productos para mostrar</h3>
                        <p>Comienza agregando tu primer producto</p>
                        <button class="btn-primary" onclick="openModal()">
                            <i class="fas fa-plus"></i> Agregar Producto
                        </button>
                    </div>
                </td>
            </tr>
        `);
    $(".pagination").hide();
  }

  function mostrarError(mensaje) {
    Swal.fire({
      icon: "error",
      title: "Error",
      text: mensaje,
      timer: 4000,
      showConfirmButton: true,
    });
  }

  // ==============================================
  // ACCIONES DE PRODUCTOS
  // ==============================================
  window.editarProducto = function (id) {
    console.log("Editar producto:", id);
    openModal(true);
  };

  window.duplicarProducto = function (id) {
    Swal.fire({
      title: "¿Duplicar producto?",
      text: "Se creará una copia de este producto",
      icon: "question",
      showCancelButton: true,
      confirmButtonColor: "#4361ee",
      cancelButtonColor: "#6c757d",
      confirmButtonText: "Sí, duplicar",
      cancelButtonText: "Cancelar",
    }).then((result) => {
      if (result.isConfirmed) {
        Swal.fire("Producto duplicado", "", "success");
      }
    });
  };

  window.eliminarProducto = function (id) {
    Swal.fire({
      title: "¿Eliminar producto?",
      text: "Esta acción no se puede deshacer",
      icon: "warning",
      showCancelButton: true,
      confirmButtonColor: "#e63946",
      cancelButtonColor: "#6c757d",
      confirmButtonText: "Sí, eliminar",
      cancelButtonText: "Cancelar",
    }).then((result) => {
      if (result.isConfirmed) {
        Swal.fire("Producto eliminado", "", "success");
        cargarProductos(paginaActual, filtrosActuales);
      }
    });
  };
});
