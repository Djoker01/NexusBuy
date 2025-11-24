$(document).ready(function () {
  //console.log('Carrito.js inicializado');
  let funcion;
  let carritoItems = []; // Usar carritoItems consistentemente
  let seleccionados = new Set();
  let monedaActual = localStorage.getItem("moneda-seleccionada") || "CUP";
  let simboloMonedaActual = "$";
  let tasaCambioActual = 1;

  // Inicializar carrito
  inicializarCarrito();

  async function inicializarCarrito() {
    //console.log("Inicializando carrito...");
    try {
      await obtener_carrito();
      inicializarEventos();

      // AGREGAR ESTA LÍNEA
      await actualizarBadgeCarrito();

      //console.log("Carrito inicializado correctamente");
    } catch (error) {
      console.error("Error inicializando carrito:", error);
      mostrarCarritoVacio();
    }
  }

  async function obtener_carrito() {
    //console.log("Obteniendo carrito...");
    funcion = "obtener_carrito";
    try {
      // Mostrar loading
      $("#articulos").html(`
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="sr-only">Cargando carrito...</span>
                </div>
                <p class="text-muted mt-2">Cargando tu carrito...</p>
            </div>
        `);

      const response = await $.post("../Controllers/CarritoController.php", {
        funcion,
      });
      //console.log("Respuesta completa del servidor:", response);

      let data;
      if (typeof response === "string") {
        try {
          data = JSON.parse(response);
        } catch (e) {
          console.error("Error parseando JSON:", e);
          // Verificar si es un mensaje de error simple
          if (response.trim() === "no_sesion") {
            // console.log("Usuario no tiene sesión");
            mostrarCarritoVacio();
            return;
          }
          throw new Error("Respuesta inválida del servidor: " + response);
        }
      } else {
        data = response;
      }

      // Verificar si hay error en la respuesta
      if (data && data.error) {
        if (data.error === "no_sesion") {
          // console.log("Usuario no tiene sesión");
          mostrarCarritoVacio();
          return;
        } else {
          throw new Error(data.error);
        }
      }

      // Verificar que data es un array
      if (Array.isArray(data)) {
        carritoItems = data;
        //console.log('Items en carrito encontrados:', carritoItems.length);
        
        if (carritoItems.length === 0) {
            mostrarCarritoVacio();
        } else {
            renderizarCarrito();
            await actualizarResumen();
            
            // === AGREGAR ESTA LÍNEA ===
            await actualizarPreciosCarrito(); // Actualizar precios al cargar
        }
        actualizarBadgePorCambios();
    } else {
        console.error('Respuesta no es array:', data);
        mostrarCarritoVacio();
    }
} catch (error) {
    console.error('Error obteniendo carrito:', error);
    mostrarError('No se pudo cargar el carrito. Intenta nuevamente.');
    mostrarCarritoVacio();
}
  }

  function renderizarCarrito() {
    //   console.log('=== INICIANDO RENDERIZADO ===');
    //   console.log('CarritoItems para renderizar:', carritoItems);
    //   console.log('Número de items:', carritoItems.length);

    if (!carritoItems || carritoItems.length === 0) {
      // console.log("No hay items, mostrando carrito vacío");
      mostrarCarritoVacio();
      return;
    }

    let template = "";

    carritoItems.forEach((item, index) => {
      //console.log(`Procesando item ${index}:`, item);

      // Usar nombres de campos consistentes con el controller
      const precioFinal = parseFloat(item.precio) || 0;
      const precioOriginal = parseFloat(item.precio_unitario) || precioFinal;
      const cantidad = parseInt(item.cantidad_producto) || 1;
      const stock = parseInt(item.cantidad) || 10; // stock_disponible
      const tieneDescuento = parseFloat(item.descuento_porcentaje) > 0;
      const nombre = item.nombre || "Producto sin nombre";
      const imagen = item.imagen || "producto_default.png";
      const detalles = item.detalles || "Sin descripción adicional";
      const tiendaNombre = item.tienda_nombre || "Tienda no especificada";

      template += `
              <div class="card mb-3 articulo-item" data-id="${
                item.id
              }" data-index="${index}">
                  <div class="card-body">
                      <div class="row align-items-center">
                          <div class="col-md-1">
                              <input type="checkbox" class="seleccionar-item form-check-input" 
                                     data-id="${
                                       item.id
                                     }" data-precio="${precioFinal}">
                          </div>
                          <div class="col-md-2">
                              <img src="../Util/Img/Producto/${imagen}" 
                                   alt="${nombre}" 
                                   class="img-fluid rounded" 
                                   style="max-height: 120px; object-fit: cover;"
                                   onerror="this.src='../Util/Img/Producto/producto_default.png'">
                          </div>
                          <div class="col-md-7">
                              <h5 class="producto-nombre mb-1">${nombre}</h5>
                              <p class="text-muted small mb-2">${detalles}</p>
                              
                              <p class="text-muted small mb-2">
                                  <i class="fas fa-store"></i> Vendido por: ${tiendaNombre}
                              </p>
                              
                              ${
                                tieneDescuento
                                  ? `
                                  <div class="mb-2">
                                      <span class="text-muted text-decoration-line-through me-2">$ ${precioOriginal.toFixed(
                                        2
                                      )}</span>
                                      <span class="badge bg-danger">-${
                                        item.descuento_porcentaje
                                      }%</span>
                                  </div>
                              `
                                  : ""
                              }
                              
                              <div class="d-flex align-items-center mb-2">
                                  <span class="me-2">Cantidad:</span>
                                  <div class="input-group input-group-sm" style="width: 140px;">
                                      <button class="btn btn-outline-secondary disminuir-cantidad" type="button" 
                                              data-id="${item.id}" ${
        cantidad <= 1 ? "disabled" : ""
      }>
                                          <i class="fas fa-minus"></i>
                                      </button>
                                      <input type="number" class="form-control text-center cantidad-input" 
                                             value="${cantidad}" min="1" max="${stock}" 
                                             data-id="${
                                               item.id
                                             }" data-precio="${precioFinal}">
                                      <button class="btn btn-outline-secondary aumentar-cantidad" type="button" 
                                              data-id="${item.id}" ${
        cantidad >= stock ? "disabled" : ""
      }>
                                          <i class="fas fa-plus"></i>
                                      </button>
                                  </div>
                                  <small class="text-muted ms-2">Disponible: ${stock}</small>
                              </div>
                              
                              <div class="precio-producto">
                                  <strong class="text-danger h5">$ ${(
                                    precioFinal * cantidad
                                  ).toFixed(2)}</strong>
                                  <small class="text-muted d-block">$ ${precioFinal.toFixed(
                                    2
                                  )} c/u</small>
                              </div>
                          </div>
                          <div class="col-md-2 text-center">
                              <button type="button" class="btn btn-outline-danger btn-sm eliminar-item" 
                                      data-id="${
                                        item.id
                                      }" data-nombre="${nombre}">
                                  <i class="fas fa-trash"></i> Eliminar
                              </button>
                          </div>
                      </div>
                  </div>
              </div>
          `;
    });

    //console.log('Template generado, insertando en DOM...');

    // Verificar que el contenedor existe
    const $articulos = $("#articulos");
    if ($articulos.length === 0) {
      console.error("ERROR: No se encontró el elemento #articulos");
      return;
    }

    $articulos.html(template);

    // Verificar que se insertó correctamente
    const itemsInsertados = $(".articulo-item").length;
    //console.log(`Items insertados en DOM: ${itemsInsertados}`);

    actualizarEstadoBotones();
    //console.log('=== RENDERIZADO COMPLETADO ===');
  }

  function mostrarCarritoVacio() {
    //console.log('Mostrando carrito vacío');
    const template = `
          <div class="text-center py-5">
              <div class="empty-cart-icon mb-3">
                  <i class="fas fa-shopping-cart fa-4x text-muted"></i>
              </div>
              <h4 class="text-muted">Tu carrito está vacío</h4>
              <p class="text-muted mb-4">Agrega algunos productos para comenzar a comprar</p>
              <a href="../index.php" class="btn btn-primary">
                  <i class="fas fa-shopping-bag mr-2"></i>Descubrir productos
              </a>
          </div>
      `;
    $("#articulos").html(template);
    $("#btn-pagar").prop("disabled", true);
    actualizarResumenCero();
  }

  function actualizarResumenCero() {
    $("#subtotal").text("$ 0.00");
    $("#envio").text("$ 0.00");
    $("#descuento").text("$ 0.00");
    $("#total").text("$ 0.00");
    $("#btn-pagar").text("Proceder al pago");
  }

  function inicializarEventos() {
    //console.log('Inicializando eventos...');

    // Seleccionar/deseleccionar items
    $(document).on("change", ".seleccionar-item", function () {
      const itemId = $(this).data("id").toString();
      const precio = parseFloat($(this).data("precio"));

      // console.log(
      //   "Checkbox cambiado:",
      //   itemId,
      //   "checked:",
      //   $(this).is(":checked")
      // );

      if ($(this).is(":checked")) {
        seleccionados.add(itemId);
      } else {
        seleccionados.delete(itemId);
      }

      actualizarSeleccionTodos();
      actualizarResumen();
    });

    // Seleccionar todos
    $(document).on("change", "#seleccionar_items", function () {
      const isChecked = $(this).is(":checked");
      //console.log("Seleccionar todos:", isChecked);
      $(".seleccionar-item").prop("checked", isChecked).trigger("change");
    });

    $(document).on("click", "#btn-vaciar-carrito", function () {
      //console.log("Vaciar carrito clickeado");
      vaciarCarrito();
    });
    async function vaciarCarrito() {
      try {
        const result = await Swal.fire({
          title: "¿Vaciar carrito completo?",
          text: "¿Estás seguro de que quieres eliminar todos los productos de tu carrito?",
          icon: "warning",
          showCancelButton: true,
          confirmButtonColor: "#d33",
          cancelButtonColor: "#3085d6",
          confirmButtonText: "Sí, vaciar todo",
          cancelButtonText: "Cancelar",
          reverseButtons: true,
        });

        if (result.isConfirmed) {
          // Mostrar loading
          const loadingSwal = Swal.fire({
            title: "Vaciando carrito...",
            text: "Por favor espera",
            allowOutsideClick: false,
            didOpen: () => {
              Swal.showLoading();
            },
          });

          funcion = "vaciar_carrito";
          const response = await $.post(
            "../Controllers/CarritoController.php",
            { funcion }
          );

          await loadingSwal.close();

          // console.log("Respuesta vaciar carrito:", response);

          let data;
          if (typeof response === "string") {
            try {
              data = JSON.parse(response);
            } catch (e) {
              data = { error: "Error parseando respuesta" };
            }
          } else {
            data = response;
          }

          if (data.success) {
            // Limpiar todo
            carritoItems = [];
            seleccionados.clear();
            actualizarBadgePorCambios();
            mostrarCarritoVacio();

            await Swal.fire({
              icon: "success",
              title: "Carrito vaciado",
              text: "Todos los productos han sido removidos de tu carrito",
              timer: 2000,
              showConfirmButton: false,
            });
          } else {
            throw new Error(data.error || "Error al vaciar carrito");
          }
        }
      } catch (error) {
        console.error("Error vaciando carrito:", error);
        mostrarError("Error al vaciar el carrito: " + error.message);
      }
    }

    // Aumentar cantidad
    $(document).on("click", ".aumentar-cantidad", async function () {
      const itemId = $(this).data("id");
      // console.log("Aumentar cantidad:", itemId);

      const input = $(`.cantidad-input[data-id="${itemId}"]`);
      const max = parseInt(input.attr("max")) || 999;
      let valor = parseInt(input.val()) || 1;

      if (valor < max) {
        valor++;
        input.val(valor);
        await actualizarCantidad(itemId, valor);
        actualizarEstadoBotones(); // Actualizar estado de botones después del cambio
      } else {
        mostrarError("No hay más stock disponible");
      }
    });

    // Disminuir cantidad - MEJORADO
    $(document).on("click", ".disminuir-cantidad", async function () {
      const itemId = $(this).data("id");
      // console.log("Disminuir cantidad:", itemId);

      const input = $(`.cantidad-input[data-id="${itemId}"]`);
      let valor = parseInt(input.val()) || 1;

      if (valor > 1) {
        valor--;
        input.val(valor);
        await actualizarCantidad(itemId, valor);
        actualizarEstadoBotones(); // Actualizar estado de botones después del cambio
      }
    });

    $(document).on("change", ".cantidad-input", async function () {
      const itemId = $(this).data("id");
      let valor = parseInt($(this).val()) || 1;
      const max = parseInt($(this).attr("max")) || 999;
      const min = parseInt($(this).attr("min")) || 1;

      // Validar límites
      if (valor < min) valor = min;
      if (valor > max) valor = max;

      $(this).val(valor);
      await actualizarCantidad(itemId, valor);
      actualizarEstadoBotones();
    });

    // Eliminar item
    $(document).on("click", ".eliminar-item", function () {
      const itemId = $(this).data("id");
      const nombre = $(this).data("nombre");
      // console.log("Eliminar item:", itemId, nombre);
      eliminarItem(itemId, nombre);
    });

    // Porceder al pago
    $(document).on("click", "#btn-pagar", function (e) {
      // console.log("Botón pagar clickeado");
      e.preventDefault();
      e.stopPropagation();
      procederAlPago();
    });

    // ESCUCHAR CAMBIOS DE MONEDA
    $(document).on('monedaCambiada', function() {
        // console.log('🎯 Evento monedaCambiada recibido en carrito');
        actualizarPreciosCarrito();
    });
    
    // También escuchar cambios directos en el selector
    $('#moneda-interface').on('change', function() {
        setTimeout(() => {
            actualizarPreciosCarrito();
        }, 500);
    });

    //console.log('Eventos inicializados');
  }

  async function actualizarCantidad(itemId, nuevaCantidad) {
    // console.log("Actualizando cantidad:", itemId, "a", nuevaCantidad);

    try {
      funcion = "actualizar_cantidad";
      const response = await $.post("../Controllers/CarritoController.php", {
        funcion,
        id_carrito_detalle: itemId, // Este es el nombre correcto
        cantidad: nuevaCantidad,
      });

      // console.log("Respuesta actualizar cantidad:", response);

      let data;
      if (typeof response === "string") {
        try {
          data = JSON.parse(response);
        } catch (e) {
          data = { success: false, error: "Error parseando respuesta" };
        }
      } else {
        data = response;
      }

      if (data.success) {
        // Actualizar en el array local
        const item = carritoItems.find((item) => item.id == itemId);
        if (item) {
          item.cantidad_producto = nuevaCantidad;

          // Recalcular subtotal del item
          const itemElement = $(`.articulo-item[data-id="${itemId}"]`);
          const precioUnitario = parseFloat(
            itemElement.find(".seleccionar-item").data("precio")
          );
          const subtotal = precioUnitario * nuevaCantidad;

          itemElement
            .find(".precio-producto strong")
            .text(`$ ${subtotal.toFixed(2)}`);

          // Actualizar selección si está seleccionado
          if (itemElement.find(".seleccionar-item").is(":checked")) {
            await actualizarResumen();
          }
        }
        // console.log("Cantidad actualizada correctamente");

        // Actualizar badge después de cambiar cantidad
        actualizarBadgePorCambios();
      } else {
        throw new Error(data.error || "Error al actualizar cantidad");
      }
    } catch (error) {
      console.error("Error actualizando cantidad:", error);
      mostrarError(error.message || "Error al actualizar la cantidad");
      await obtener_carrito(); // Recargar carrito para sincronizar
    }
  }

  async function eliminarItem(itemId, nombre) {
    try {
      const result = await Swal.fire({
        title: "¿Eliminar producto?",
        text: `¿Estás seguro de que quieres eliminar "${nombre}" del carrito?`,
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#d33",
        cancelButtonColor: "#3085d6",
        confirmButtonText: "Sí, eliminar",
        cancelButtonText: "Cancelar",
      });

      if (result.isConfirmed) {
        funcion = "eliminar_del_carrito";
        const response = await $.post("../Controllers/CarritoController.php", {
          funcion,
          id_carrito_detalle: itemId,
        });

        // console.log("Respuesta eliminar:", response);

        // Parsear la respuesta
        let data;
        if (typeof response === "string") {
          try {
            data = JSON.parse(response);
          } catch (e) {
            data = { success: false, error: "Error parseando respuesta" };
          }
        } else {
          data = response;
        }

        if (data.success) {
          // Eliminar del array local
          carritoItems = carritoItems.filter((item) => item.id != itemId);

          // Eliminar de seleccionados
          seleccionados.delete(itemId.toString());

          // Actualizar badge después de eliminar
          actualizarBadgePorCambios();

          // Re-renderizar
          if (carritoItems.length === 0) {
            mostrarCarritoVacio();
          } else {
            renderizarCarrito();
            await actualizarResumen();
          }

          await Swal.fire({
            icon: "success",
            title: "Producto eliminado",
            text: "El producto ha sido removido de tu carrito",
            timer: 1500,
            showConfirmButton: false,
          });
        } else {
          throw new Error(data.error || "Error al eliminar producto");
        }
      }
    } catch (error) {
      console.error("Error eliminando item:", error);
      mostrarError(
        "Error al eliminar el producto del carrito: " + error.message
      );
    }
  }

  function actualizarEstadoBotones() {
    //console.log("Actualizando estado de botones...");

    carritoItems.forEach((item) => {
      const cantidad = parseInt(item.cantidad_producto) || 1;
      const stock = parseInt(item.cantidad) || 10; // stock disponible

      const btnAumentar = $(`.aumentar-cantidad[data-id="${item.id}"]`);
      const btnDisminuir = $(`.disminuir-cantidad[data-id="${item.id}"]`);
      const input = $(`.cantidad-input[data-id="${item.id}"]`);

      // Actualizar botones
      btnAumentar.prop("disabled", cantidad >= stock);
      btnDisminuir.prop("disabled", cantidad <= 1);

      // Actualizar atributos del input
      input.attr("max", stock);
      input.attr("min", 1);

      // console.log(
      //   `Item ${
      //     item.id
      //   }: cantidad=${cantidad}, stock=${stock}, aumentarDisabled=${
      //     cantidad >= stock
      //   }, disminuirDisabled=${cantidad <= 1}`
      // );
    });
  }

  function actualizarSeleccionTodos() {
    const totalItems = $(".seleccionar-item").length;
    const selectedItems = $(".seleccionar-item:checked").length;

    $("#seleccionar_items").prop(
      "checked",
      totalItems > 0 && totalItems === selectedItems
    );
  }

  async function actualizarResumen() {
    let subtotal = 0;
    
    seleccionados.forEach(itemId => {
        const item = carritoItems.find(item => item.id == itemId);
        if (item) {
            // Usar el precio del data attribute (ya convertido) o el original
            const itemElement = $(`.articulo-item[data-id="${itemId}"]`);
            const precio = parseFloat(itemElement.find('.seleccionar-item').data('precio')) || 
                          parseFloat(item.precio_final) || 0;
            const cantidad = parseInt(item.cantidad_producto) || 1;
            subtotal += precio * cantidad;
        }
    });
    
    const envio = subtotal > 1000 ? 0 : 250;
    const descuento = 0;
    const total = subtotal + envio - descuento;
    
    // === USAR SÍMBOLO ACTUAL ===
    $('#subtotal').text(`${simboloMonedaActual} ${subtotal.toFixed(2)}`);
    $('#envio').text(envio === 0 ? 'GRATIS' : `${simboloMonedaActual} ${envio.toFixed(2)}`);
    $('#descuento').text(`${simboloMonedaActual} ${descuento.toFixed(2)}`);
    $('#total').text(`${simboloMonedaActual} ${total.toFixed(2)}`);
    
    const tieneSeleccionados = seleccionados.size > 0;
    $('#btn-pagar').prop('disabled', !tieneSeleccionados);
    
    if (tieneSeleccionados) {
        $('#btn-pagar').text(`Proceder al pago (${seleccionados.size} productos)`);
    } else {
        $('#btn-pagar').text('Proceder al pago');
    }
}

  function mostrarError(mensaje) {
    console.error("Mostrando error:", mensaje);
    Swal.fire({
      icon: "error",
      title: "Error",
      text: mensaje,
      timer: 4000,
      showConfirmButton: true,
    });
  }

  // Función de diagnóstico
  function diagnosticarProblema() {
    // console.log("=== DIAGNÓSTICO DEL CARRITO ===");
    // console.log("1. CarritoItems en memoria:", carritoItems);
    // console.log("2. Número de items:", carritoItems.length);
    // console.log("3. Contenedor #articulos existe:", $("#articulos").length > 0);
    // console.log("4. Items en DOM:", $(".articulo-item").length);
    // console.log("=== FIN DIAGNÓSTICO ===");
  }
  function procederAlPago() {
    if (seleccionados.size === 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Selecciona productos',
            text: 'Debes seleccionar al menos un producto para proceder al pago',
            confirmButtonText: 'Entendido'
        });
        return;
    }
    
    const productosSeleccionados = Array.from(seleccionados).map(id => {
        const item = carritoItems.find(item => item.id.toString() === id.toString());
        return item ? item.nombre : 'Producto';
    });
    
    // Calcular totales CON PRECIOS CONVERTIDOS
    let subtotalCheckout = 0;
    let envioCheckout = 0;
    
    seleccionados.forEach(itemId => {
        const item = carritoItems.find(item => item.id.toString() === itemId.toString());
        if (item) {
            // Usar precio convertido si existe
            const precio = item.precio_final_convertido || parseFloat(item.precio_final) || 0;
            const cantidad = parseInt(item.cantidad_producto) || 1;
            subtotalCheckout += precio * cantidad;
            
            // Calcular envío convertido
            const envioBase = 250;
            envioCheckout = subtotalCheckout > (1000 / tasaCambioActual) ? 0 : envioBase / tasaCambioActual;
        }
    });
    
    const totalCheckout = subtotalCheckout + envioCheckout;
    
    Swal.fire({
        title: 'Confirmar compra',
        html: `
            <div class="text-left">
                <p><strong>Productos seleccionados (${seleccionados.size}):</strong></p>
                <ul class="small">
                    ${productosSeleccionados.map(nombre => `<li>${nombre}</li>`).join('')}
                </ul>
                <div class="mt-3 p-2 bg-light rounded">
                    <div class="row">
                        <div class="col-6"><strong>Subtotal:</strong></div>
                        <div class="col-6 text-right">${simboloMonedaActual} ${subtotalCheckout.toFixed(2)}</div>
                    </div>
                    <div class="row">
                        <div class="col-6"><strong>Envío:</strong></div>
                        <div class="col-6 text-right">${envioCheckout === 0 ? 'GRATIS' : simboloMonedaActual + ' ' + envioCheckout.toFixed(2)}</div>
                    </div>
                    <div class="row">
                        <div class="col-6"><strong>Total:</strong></div>
                        <div class="col-6 text-right"><strong>${simboloMonedaActual} ${totalCheckout.toFixed(2)}</strong></div>
                    </div>
                </div>
            </div>
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Continuar al checkout',
        cancelButtonText: 'Seguir comprando',
        reverseButtons: true,
        width: '600px'
    }).then((result) => {
        if (result.isConfirmed) {
            // Guardar los items seleccionados en sessionStorage
            // console.log('Guardando datos para checkout:', {
            //     items: Array.from(seleccionados),
            //     subtotal: subtotalCheckout,
            //     envio: envioCheckout,
            //     total: totalCheckout,
            //     moneda: monedaActual,
            //     simbolo: simboloMonedaActual
            // });
            
            sessionStorage.setItem('checkoutItems', JSON.stringify(Array.from(seleccionados)));
            sessionStorage.setItem('checkoutSubtotal', subtotalCheckout.toString());
            sessionStorage.setItem('checkoutEnvio', envioCheckout.toString());
            sessionStorage.setItem('checkoutTotal', totalCheckout.toString());
            sessionStorage.setItem('checkoutMoneda', monedaActual);
            sessionStorage.setItem('checkoutSimbolo', simboloMonedaActual);
            
            // Redirigir a la página de checkout
            window.location.href = 'checkout.php';
        }
    });
}
  // Función para actualizar el badge del carrito
  async function actualizarBadgeCarrito() {
    try {
      //console.log("Actualizando badge del carrito...");

      // Obtener la cantidad total de items en el carrito
      const cantidadTotal = await obtenerCantidadTotalCarrito();
      const $badge = $("#cart-badge");

      //console.log("Cantidad total en carrito:", cantidadTotal);

      if (cantidadTotal > 0) {
        $badge.text(cantidadTotal);
        $badge.show();

        // Agregar animación
        $badge.addClass("animate__animated animate__bounceIn");
        setTimeout(() => {
          $badge.removeClass("animate__animated animate__bounceIn");
        }, 1000);
      } else {
        $badge.hide();
      }
    } catch (error) {
      console.error("Error actualizando badge:", error);
      // En caso de error, ocultar el badge
      $("#cart-badge").hide();
    }
  }

  // Función para obtener la cantidad total del carrito desde el servidor
  async function obtenerCantidadTotalCarrito() {
    return new Promise((resolve, reject) => {
      // Si ya tenemos los items del carrito, calcular localmente
      if (carritoItems && carritoItems.length > 0) {
        const total = carritoItems.reduce((sum, item) => {
          return sum + (parseInt(item.cantidad_producto) || 1);
        }, 0);
        resolve(total);
        return;
      }

      // Si no hay items cargados, consultar al servidor
      $.post("../Controllers/CarritoController.php", {
        funcion: "obtener_cantidad_total",
      })
        .done(function (response) {
          try {
            const data =
              typeof response === "string" ? JSON.parse(response) : response;
            resolve(data.cantidad_total || 0);
          } catch (error) {
            console.error("Error parseando respuesta cantidad total:", error);
            resolve(0);
          }
        })
        .fail(function (error) {
          console.error("Error obteniendo cantidad total:", error);
          resolve(0);
        });
    });
  }

  // Función para actualizar el badge cuando se modifica el carrito
  function actualizarBadgePorCambios() {
    //console.log('Actualizando badge por cambios en el carrito...');

    // Calcular cantidad total localmente
    const cantidadTotal = carritoItems.reduce((sum, item) => {
      return sum + (parseInt(item.cantidad_producto) || 1);
    }, 0);

    const $badge = $("#cart-badge");

    if (cantidadTotal > 0) {
      $badge.text(cantidadTotal);
      $badge.show();

      // Animación sutil para cambios
      $badge.addClass("animate__animated animate__pulse");
      setTimeout(() => {
        $badge.removeClass("animate__animated animate__pulse");
      }, 500);
    } else {
      $badge.hide();
    }
  }

  // Función para debuguear la respuesta del servidor
  function debugResponse(response) {
    // console.group("🔍 DEBUG RESPONSE CARRITO");
    // console.log("Tipo de respuesta:", typeof response);
    // console.log("Respuesta completa:", response);

    if (typeof response === "string") {
      // console.log("Es string, intentando parsear...");
      try {
        const parsed = JSON.parse(response);
        // console.log("Parseado exitoso:", parsed);
      } catch (e) {
        // console.log("Error parseando:", e.message);
        // console.log("Contenido string:", response);
      }
    } else {
      // console.log("Ya es objeto:", response);
    }
    console.groupEnd();
  }

  // FUNCIÓN NUEVA - Actualizar precios cuando cambia la moneda
  async function actualizarPreciosCarrito() {
    try {
        console.log('🔄 Actualizando precios del carrito...');
        
        // Obtener moneda actual del localStorage
        const monedaSeleccionada = localStorage.getItem('moneda-seleccionada') || 'CUP';
        
        // Obtener tasa de cambio
        const response = await $.post('../Controllers/MonedaController.php', {
            funcion: 'obtener_tasa_cambio',
            moneda: monedaSeleccionada
        });
        
        console.log('📨 Respuesta MonedaController:', response);
        
        const data = typeof response === 'string' ? JSON.parse(response) : response;
        
        if (data.success) {
            // DEBUG: Ver la estructura real de la respuesta
            console.log('🔍 Estructura completa de data:', data);
            
            // CORREGIDO: La estructura real según MonedaController.php
            const tasa = parseFloat(data.tasa_cambio) || 1;
            
            // CORREGIDO: El símbolo está en data.moneda (que es un array/objeto)
            let simbolo = '$'; // valor por defecto
            
            // Manejar la estructura real del controlador
            if (data.moneda && typeof data.moneda === 'object') {
                // Si moneda es un objeto con propiedades
                if (data.moneda.simbolo) {
                    simbolo = data.moneda.simbolo;
                }
            } else {
                // Si no hay estructura moneda, usar valores por defecto
                console.warn('⚠️ Estructura de moneda no encontrada, usando valores por defecto');
            }
            
            const codigoMoneda = data.moneda?.codigo || monedaSeleccionada;
            
            console.log(`💱 Conversión carrito: tasa=${tasa}, símbolo=${simbolo}, moneda=${codigoMoneda}`);
            
            // Actualizar cada item en el array carritoItems
            carritoItems.forEach(item => {
                // Obtener precios originales
                const precioFinalOriginal = parseFloat(item.precio_final) || parseFloat(item.precio) || 0;
                const precioUnitarioOriginal = parseFloat(item.precio_unitario) || precioFinalOriginal;
                
                // Calcular nuevos precios y GUARDARLOS en el objeto
                item.precio_final_convertido = precioFinalOriginal / tasa;
                item.precio_unitario_convertido = precioUnitarioOriginal / tasa;
                item.subtotal_convertido = item.precio_final_convertido * (parseInt(item.cantidad_producto) || 1);
                item.moneda_actual = codigoMoneda;
                item.simbolo_moneda = simbolo;
                item.tasa_cambio = tasa;
                
                console.log(`📦 Item ${item.id}:`, {
                    original: precioFinalOriginal,
                    convertido: item.precio_final_convertido,
                    cantidad: item.cantidad_producto,
                    subtotal: item.subtotal_convertido,
                    moneda: item.moneda_actual
                });
            });
            
            // Actualizar la interfaz visual
            $('.articulo-item').each(function() {
                const $item = $(this);
                const itemId = $item.data('id');
                
                const item = carritoItems.find(item => item.id == itemId);
                if (item && item.precio_final_convertido) {
                    const cantidad = parseInt(item.cantidad_producto) || 1;
                    const subtotalConvertido = item.precio_final_convertido * cantidad;
                    
                    const $precioProducto = $item.find('.precio-producto');
                    
                    // Actualizar subtotal (precio total del item)
                    $precioProducto.find('strong.text-danger.h5').text(
                        `${simbolo} ${subtotalConvertido.toFixed(2)}`
                    );
                    
                    // Actualizar precio unitario
                    $precioProducto.find('small.text-muted').text(
                        `${simbolo} ${item.precio_final_convertido.toFixed(2)} c/u`
                    );
                    
                    // Actualizar precio original tachado si hay descuento
                    if (item.descuento_porcentaje > 0 && item.precio_unitario_convertido) {
                        $item.find('.text-decoration-line-through').text(
                            `${simbolo} ${item.precio_unitario_convertido.toFixed(2)}`
                        );
                    }
                    
                    // Actualizar data attribute para cálculos
                    $item.find('.seleccionar-item').data('precio', item.precio_final_convertido);
                }
            });
            
            // Actualizar variables globales
            monedaActual = codigoMoneda;
            simboloMonedaActual = simbolo;
            tasaCambioActual = tasa;
            
            // Recalcular resumen
            await actualizarResumen();
            
            console.log('✅ Precios del carrito actualizados correctamente');
        } else {
            console.error('❌ Error obteniendo tasa de cambio:', data.error);
            // En caso de error, usar valores por defecto
            usarMonedaPorDefecto();
        }
    } catch (error) {
        console.error('❌ Error actualizando precios del carrito:', error);
        // En caso de error, usar valores por defecto
        usarMonedaPorDefecto();
    }
}

function usarMonedaPorDefecto() {
  monedaActual = 'CUP';
  simboloMonedaActual = '$';
  tasaCambioActual = 1;
  restaurarPreciosCarrito();
}

function restaurarPreciosCarrito() {
  console.log('🔄 Restaurando precios originales del carrito...');
  
  // Restaurar precios originales en la interfaz
  $('.articulo-item').each(function() {
      const $item = $(this);
      const itemId = $item.data('id');
      
      const item = carritoItems.find(item => item.id == itemId);
      if (item) {
          const precioFinal = parseFloat(item.precio_final) || 0;
          const cantidad = parseInt(item.cantidad_producto) || 1;
          const subtotal = precioFinal * cantidad;
          
          const $precioProducto = $item.find('.precio-producto');
          
          // Restaurar subtotal
          $precioProducto.find('strong.text-danger.h5').text(`$ ${subtotal.toFixed(2)}`);
          
          // Restaurar precio unitario
          $precioProducto.find('small.text-muted').text(`$ ${precioFinal.toFixed(2)} c/u`);
          
          // Restaurar precio original tachado si hay descuento
          if (item.descuento_porcentaje > 0) {
              const precioUnitario = parseFloat(item.precio_unitario) || precioFinal;
              $item.find('.text-decoration-line-through').text(`$ ${precioUnitario.toFixed(2)}`);
          }
          
          // Restaurar data attribute
          $item.find('.seleccionar-item').data('precio', precioFinal);
      }
  });
  
  // Restaurar resumen
  actualizarResumen();
}
});
