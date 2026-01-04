// Inicializar moneda al cargar la página
$(document).ready(function () {
  // Establecer moneda inicial
  const monedaGuardada = localStorage.getItem("moneda-seleccionada") || "CUP";
  $("#moneda-interface").val(monedaGuardada);

  // Actualizar precios con la moneda guardada después de un delay
  setTimeout(() => {
    actualizarPreciosMoneda(monedaGuardada);
  }, 1000);

  // Variables globales para la moneda
  let monedaActual = localStorage.getItem("moneda-seleccionada") || "CUP";
  let simboloMonedaActual = "$";

  // Función principal para actualizar precios en toda la aplicación
  async function actualizarPreciosMoneda(codigoMoneda) {
    try {
      // Mostrar loading en los precios
    //   mostrarLoadingPrecios();

      const response = await $.post("../Controllers/MonedaController.php", {
        funcion: "convertir_precios_productos",
        moneda: codigoMoneda,
      });

      const data =
        typeof response === "string" ? JSON.parse(response) : response;

      if (data.success) {
        monedaActual = codigoMoneda;
        simboloMonedaActual = data.moneda.simbolo;

        // Actualizar precios en todas las vistas
        await actualizarPreciosIndex(data.productos);
        await actualizarPreciosCarrito();
        await actualizarPreciosCheckout();
        await actualizarPreciosFavoritos();
        await actualizarPreciosDescripcion();
        await actualizarPreciosPedidos();

        // Actualizar símbolos en toda la página
        actualizarSimbolosMoneda();

        //console.log("Precios actualizados a moneda:", codigoMoneda);
      } else {
        console.error("Error actualizando precios:", data.error);
        restaurarPreciosOriginales();
      }
    } catch (error) {
      console.error("Error actualizando precios:", error);
      restaurarPreciosOriginales();
    }
  }

  // Función para mostrar loading en los precios
//   function mostrarLoadingPrecios() {
//     $(
//       ".precio-producto, .text-danger h4, .precio-original, .resumen-lateral-total"
//     ).each(function () {
//       const $element = $(this);
//       if (!$element.hasClass("no-loading")) {
//         $element.data("original-html", $element.html());
//         $element.html('<i class="fas fa-spinner fa-spin"></i>');
//       }
//     });
//   }

  // Función para restaurar precios originales en caso de error
  function restaurarPreciosOriginales() {
    $(".precio-producto, .text-danger h4, .precio-original").each(function () {
      const $element = $(this);
      const originalHtml = $element.data("original-html");
      if (originalHtml) {
        $element.html(originalHtml);
      }
    });
  }

  // Función para actualizar símbolos de moneda en toda la página
  function actualizarSimbolosMoneda() {
    // Actualizar símbolos en elementos de precio
    $(
      "h4.text-danger, .text-danger.font-weight-bold, .precio-producto strong"
    ).each(function () {
      const $element = $(this);
      const texto = $element.text();

      // Solo actualizar si no tiene el símbolo correcto
      if (!texto.includes(simboloMonedaActual)) {
        const soloNumero = texto.replace(/[^\d.,]/g, "").trim();
        if (soloNumero) {
          $element.text(`${simboloMonedaActual} ${soloNumero}`);
        }
      }
    });
  }

  // ================= FUNCIONES ESPECÍFICAS POR VISTA =================

  // Función para actualizar precios en el index/tienda
  async function actualizarPreciosIndex(productos) {
    if ($("#productos").length > 0) {
      productos.forEach((producto) => {
        const precioFinal =
          producto.precio_descuento_convertido || producto.precio_convertido;
        const precioOriginal = producto.precio_convertido;

        // Actualizar en cards de productos
        $(`.product-card:contains("${producto.producto}")`).each(function () {
          const $card = $(this);

          // Actualizar precio con descuento
          $card
            .find("h4.text-danger")
            .text(`${simboloMonedaActual} ${precioFinal.toFixed(2)}`);

          // Actualizar precio tachado si hay descuento
          if (producto.descuento > 0) {
            $card
              .find('span[style*="line-through"]')
              .text(`${simboloMonedaActual} ${precioOriginal.toFixed(2)}`);
          }
        });
      });
    }
  }

  // Función para actualizar precios en el carrito
  async function actualizarPreciosCarrito() {
    if (
      $("#articulos").length > 0 &&
      typeof window.carritoItems !== "undefined"
    ) {
      try {
        const response = await $.post("../Controllers/MonedaController.php", {
          funcion: "convertir_precios_productos",
          moneda: monedaActual,
          productos: JSON.stringify(window.carritoItems),
        });

        const data =
          typeof response === "string" ? JSON.parse(response) : response;

        if (data.success) {
          data.productos.forEach((producto) => {
            const precioUnitario =
              producto.precio_unitario_convertido || producto.precio_convertido;
            const subtotal = precioUnitario * (producto.cantidad_producto || 1);

            // Actualizar en el carrito
            $(`.articulo-item[data-id="${producto.id}"]`).each(function () {
              const $item = $(this);

              // Actualizar precio unitario
              $item
                .find(".precio-producto strong")
                .text(`${simboloMonedaActual} ${subtotal.toFixed(2)}`);
              $item
                .find(".precio-producto small")
                .text(
                  `${simboloMonedaActual} ${precioUnitario.toFixed(2)} c/u`
                );

              // Actualizar checkbox data
              $item.find(".seleccionar-item").data("precio", precioUnitario);
            });
          });

          // Recalcular resumen
          if (typeof actualizarResumen === "function") {
            await actualizarResumen();
          }
        }
      } catch (error) {
        console.error("Error actualizando precios del carrito:", error);
      }
    }
  }

  // Función para actualizar precios en checkout
  async function actualizarPreciosCheckout() {
    if ($("#resumen-lateral-total").length > 0) {
      try {
        const tasaResponse = await $.post(
          "../Controllers/MonedaController.php",
          {
            funcion: "obtener_tasa_cambio",
            moneda: monedaActual,
          }
        );

        const tasaData =
          typeof tasaResponse === "string"
            ? JSON.parse(tasaResponse)
            : tasaResponse;

        if (tasaData.success) {
          const tasa = parseFloat(tasaData.tasa_cambio);

          // Convertir totales
          if (window.checkoutSubtotal) {
            const subtotalConvertido = window.checkoutSubtotal * tasa;
            const envioConvertido = window.checkoutEnvio * tasa;
            const totalConvertido = window.checkoutTotal * tasa;

            $("#resumen-lateral-subtotal").text(
              `${simboloMonedaActual} ${subtotalConvertido.toFixed(2)}`
            );
            $("#resumen-lateral-envio").text(
              `${simboloMonedaActual} ${envioConvertido.toFixed(2)}`
            );
            $("#resumen-lateral-total").text(
              `${simboloMonedaActual} ${totalConvertido.toFixed(2)}`
            );

            // Actualizar en el paso 3 si está visible
            $("#resumen-subtotal").text(
              `${simboloMonedaActual} ${subtotalConvertido.toFixed(2)}`
            );
            $("#resumen-envio-costo").text(
              `${simboloMonedaActual} ${envioConvertido.toFixed(2)}`
            );
            $("#resumen-total").text(
              `${simboloMonedaActual} ${totalConvertido.toFixed(2)}`
            );
          }
        }
      } catch (error) {
        console.error("Error actualizando precios checkout:", error);
      }
    }
  }

  // Función para actualizar precios en favoritos
  function actualizarPreciosFavoritos() {
    if (
      typeof window.favoritos !== "undefined" &&
      $("#lista-favoritos").length > 0
    ) {
      window.favoritos.forEach((producto) => {
        const precioFinal =
          producto.precio_descuento_convertido || producto.precio_convertido;
        const precioOriginal = producto.precio_convertido;

        // Actualizar en vista grid
        $(`.favorito-card:contains("${producto.producto}")`).each(function () {
          const $card = $(this);

          // Actualizar precios
          $card
            .find(".text-danger.font-weight-bold")
            .text(`${simboloMonedaActual} ${precioFinal.toFixed(2)}`);

          if (producto.descuento > 0) {
            $card
              .find(".precio-original")
              .text(`${simboloMonedaActual} ${precioOriginal.toFixed(2)}`);
          }
        });
      });
    }
  }

  // Función MEJORADA para actualizar precios en descripción de producto
  async function actualizarPreciosDescripcion() {
    if ($("#informacion_precios").length > 0) {
      try {
        // Obtener la moneda actual y símbolo desde las variables globales
        const monedaActual =
          localStorage.getItem("moneda-seleccionada") || "CUP";

        // Obtener tasa de cambio
        const response = await $.post("../Controllers/MonedaController.php", {
          funcion: "obtener_tasa_cambio",
          moneda: monedaActual,
        });

        const data =
          typeof response === "string" ? JSON.parse(response) : response;

        if (data.success) {
          const tasa = parseFloat(data.tasa_cambio);
          const simbolo = data.moneda.simbolo;

          // Convertir precios usando los originales guardados
          const precioOriginalConvertido = preciosOriginales.precio / tasa;
          const precioDescuentoConvertido =
            preciosOriginales.precio_descuento / tasa;

          // Reconstruir el template de precios
          let nuevoTemplate = "";

          // Recrear las estrellas si existen
          if (preciosOriginales.calificacion != 0) {
            nuevoTemplate += `</br>`;
            for (
              let index = 0;
              index < preciosOriginales.calificacion;
              index++
            ) {
              nuevoTemplate += `<i class="fas fa-star text-warning"></i>`;
            }
            let estrellas_faltantes = 5 - preciosOriginales.calificacion;
            for (let index = 0; index < estrellas_faltantes; index++) {
              nuevoTemplate += `<i class="far fa-star text-warning"></i>`;
            }
            nuevoTemplate += `</br>`;
          }

          // Agregar precios convertidos
          if (preciosOriginales.descuento != 0) {
            nuevoTemplate += `
                    <span class="text-muted" style="text-decoration: line-through">${simbolo} ${precioOriginalConvertido.toFixed(
              2
            )}</span>
                    <span class="text-muted">-${
                      preciosOriginales.descuento
                    }%</span></br>
                `;
          }

          nuevoTemplate += `           
                <h4 class="text-danger">${simbolo} ${precioDescuentoConvertido.toFixed(
            2
          )}</h4>`;

          // Actualizar el HTML
          $("#informacion_precios").html(nuevoTemplate);

          console.log("Precios actualizados en descripción:", {
            moneda: monedaActual,
            tasa: tasa,
            precio_original: precioOriginalConvertido,
            precio_descuento: precioDescuentoConvertido,
          });
        }
      } catch (error) {
        console.error("Error actualizando precios en descripción:", error);
      }
    }
  }

  // Función para actualizar precios en pedidos
  async function actualizarPreciosPedidos() {
    if ($('.pedido-card').length > 0 || $('#lista-pedidos').length > 0) {
        try {
            //console.log('🔄 Actualizando precios en Mis Pedidos...');
            
            const tasaResponse = await $.post('../Controllers/MonedaController.php', {
                funcion: 'obtener_tasa_cambio',
                moneda: monedaActual
            });
            
            const tasaData = typeof tasaResponse === 'string' ? JSON.parse(tasaResponse) : tasaResponse;
            
            if (tasaData.success) {
                const tasa = parseFloat(tasaData.tasa_cambio);
                const simbolo = tasaData.moneda.simbolo;
                
                //console.log(`💱 Aplicando conversión a pedidos: ${tasa} ${simbolo}`);
                
                // 1. ACTUALIZAR TOTALES EN LAS TARJETAS DE PEDIDO
                $('.pedido-card .text-danger').each(function() {
                    const $element = $(this);
                    const texto = $element.text();
                    
                    const precioMatch = texto.match(/\$ (\d+\.?\d*)/);
                    if (precioMatch) {
                        const precioOriginal = parseFloat(precioMatch[1]);
                        const precioConvertido = (precioOriginal / tasa).toFixed(2);
                        $element.text(`${simbolo} ${precioConvertido}`);
                    }
                });
                
                // 2. ACTUALIZAR RESUMEN DENTRO DE CADA TARJETA
                $('.pedido-card .d-flex.justify-content-between').each(function() {
                    const $element = $(this);
                    const texto = $element.text();
                    
                    const precioMatch = texto.match(/\$ (\d+\.?\d*)/);
                    if (precioMatch) {
                        const precioOriginal = parseFloat(precioMatch[1]);
                        const precioConvertido = (precioOriginal / tasa).toFixed(2);
                        const nuevoTexto = texto.replace(/\$ \d+\.?\d*/, `${simbolo} ${precioConvertido}`);
                        $element.text(nuevoTexto);
                    }
                });
                
                // 3. ACTUALIZAR MODAL DE DETALLES (si está abierto)
                $('#modalDetallesPedido').find('td, .d-flex.justify-content-between').each(function() {
                    const $element = $(this);
                    const texto = $element.text();
                    
                    const precioMatch = texto.match(/\$ (\d+\.?\d*)/);
                    if (precioMatch) {
                        const precioOriginal = parseFloat(precioMatch[1]);
                        const precioConvertido = (precioOriginal / tasa).toFixed(2);
                        $element.text(texto.replace(/\$ \d+\.?\d*/, `${simbolo} ${precioConvertido}`));
                    }
                });
                
                //console.log('✅ Precios actualizados en Mis Pedidos');
            }
        } catch (error) {
            console.error('❌ Error actualizando precios en pedidos:', error);
        }
    }
}

  // LISTENER PARA CAMBIOS DE MONEDA - AGREGAR AL FINAL
  $(document).on("monedaCambiada", function () {
    actualizarPreciosDescripcion();
  });

  // También escuchar cambios en el selector de moneda global
  $("#moneda-interface").on("change", function () {
    setTimeout(() => {
      actualizarPreciosDescripcion();
    }, 500);
  });
});
