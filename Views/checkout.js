$(document).ready(function () {
  // ================= DEBUGGING INICIAL =================
  // console.log('🛠️ Iniciando checkout.js con validación dinámica de tarjetas');

  // Verificar TODOS los elementos importantes
  const elementosCriticos = [
    "resumen-lateral-subtotal",
    "resumen-lateral-envio",
    "resumen-lateral-descuento",
    "resumen-lateral-total",
    "resumen-productos",
    "step-content-1",
    "step-content-2",
    "step-content-3",
    "form-checkout",
  ];

  elementosCriticos.forEach((id) => {
    const elemento = $(`#${id}`);
    // console.log(`🔍 #${id}:`, elemento.length > 0 ? '✅ ENCONTRADO' : '❌ NO ENCONTRADO');
  });

  $(document).on("click", "#btn-procesar-pago", function (e) {
    e.preventDefault();

    // console.log('🖱️ Botón de procesar pago clickeado');

    // Validar que todos los pasos estén completos
    if (!validarPasosCheckout()) {
      mostrarError(
        "Por favor, completa todos los pasos del checkout antes de continuar",
      );
      return;
    }

    // Validar método de pago
    const metodoPago = obtenerMetodoPagoSeleccionado();
    if (!metodoPago) {
      return; // Ya se mostró el error en obtenerMetodoPagoSeleccionado()
    }

    // Llamar a la función principal de procesamiento
    procesarPago();
  });

  // ================= VARIABLES GLOBALES =================
  window.checkoutItems = [];
  window.checkoutSubtotal = 0;
  window.checkoutEnvio = 0;
  window.checkoutTotal = 0;
  window.checkoutSubtotalOriginal = 0;
  window.checkoutEnvioOriginal = 0;
  window.checkoutTotalOriginal = 0;
  window.direccionEnvioCompleta = "";
  window.metodoPagoSeleccionado = null;
  window.datosPago = {};
  window.monedaCheckout = "CUP";
  window.simboloMonedaCheckout = "$";
  window.tasaCambioCheckout = 1;
  window.textareaUsado = false; // Nueva variable para control de uso único
  window.esNuevaOrden = true; // Variable para controlar nueva orden
  window.numeroTarjetaActual = null; // Número de tarjeta actual de la base de datos

  // Verificar que usuarioData existe
  if (typeof usuarioData === "undefined") {
    console.error(
      "ERROR: usuarioData no está definido. Revisa el orden de los scripts en checkout.php",
    );
    window.usuarioData = {};
  } else {
    // console.log('✅ usuarioData cargado correctamente:', usuarioData);
  }

  // Función para validar los pasos del checkout
  function validarPasosCheckout() {
    // Verificar dirección
    if (!$("#direccion").val() || $("#direccion").val().trim() === "") {
      mostrarError("Por favor, ingresa una dirección de envío");
      return false;
    }

    // Verificar que haya productos
    if (window.checkoutItems.length === 0) {
      mostrarError("No hay productos en el carrito");
      return false;
    }

    // Verificar método de pago (se hará en obtenerMetodoPagoSeleccionado)

    return true;
  }

  // ================= INICIALIZACIÓN =================
  inicializarCheckout();

  async function inicializarCheckout() {
    // console.log('Inicializando checkout...');

    try {
      // 1. Cargar datos del carrito
      await cargarDatosCarrito();
      // 2. DEBUG: Verificar datos de envío
      debugDatosEnvio();

      // console.log('🧐 ¿Cómo se carga checkoutItems?');
      // console.log('sessionStorage items:', sessionStorage.getItem('checkoutItems'));

      // 2. Cargar datos del usuario
      await cargarDatosUsuario();

      // 3. Actualizar moneda (esto también actualiza precios)
      await actualizarMonedaCheckout();

      // 5. Cargar métodos de pago
      await cargarMetodosPago();

      // 6. Cargar direcciones
      await cargarDireccionesUsuario();

      // 7. Actualizar resumen de productos
      await actualizarResumenProductos();

      // 8. Inicializar en paso 1
      siguientePaso(1);

      // console.log('✅ Checkout inicializado correctamente');

      // Forzar actualización después de 1 segundo (por si hay carga asíncrona)
      setTimeout(() => {
        // console.log('🔄 Actualización forzada después de 1s');
        actualizarResumenLateral();
      }, 1000);
    } catch (error) {
      console.error("Error inicializando checkout:", error);
      mostrarError("Error al cargar la página de checkout: " + error.message);
    }
  }

  // ================= FUNCIONES PRINCIPALES =================

  function cargarDatosCarrito() {
    return new Promise((resolve, reject) => {
      try {
        // console.log('🔍 Verificando datos del carrito en sessionStorage...');

        // ========== DATOS BÁSICOS ==========
        const itemsStorage = sessionStorage.getItem("checkoutItems");
        const subtotalStorage = sessionStorage.getItem("checkoutSubtotal");
        const envioStorage = sessionStorage.getItem("checkoutEnvio");
        const totalStorage = sessionStorage.getItem("checkoutTotal");
        const descuentoStorage = sessionStorage.getItem("checkoutDescuento");

        // ========== DATOS DE MONEDA Y CONVERSIÓN ==========
        const monedaStorage = sessionStorage.getItem("checkoutMoneda");
        const simboloStorage = sessionStorage.getItem("checkoutSimbolo");
        const tasaStorage = sessionStorage.getItem("checkoutTasa");

        // ========== DATOS CONVERTIDOS Y ORIGINALES ==========
        const subtotalOriginalStorage = sessionStorage.getItem(
          "checkoutSubtotalOriginal",
        );
        const envioOriginalStorage = sessionStorage.getItem(
          "checkoutEnvioOriginal",
        );
        const totalOriginalStorage = sessionStorage.getItem(
          "checkoutTotalOriginal",
        );
        const descuentoOriginalStorage = sessionStorage.getItem(
          "checkoutDescuentoOriginal",
        );

        // ========== DATOS ESPECÍFICOS DEL CARRITO ==========
        const itemsIdsStorage = sessionStorage.getItem("checkoutItemsIds");
        const itemsConEnvioStorage = sessionStorage.getItem(
          "checkoutItemsConEnvio",
        );

        // console.log("📦 TODOS los datos encontrados en sessionStorage:", {
        //   // Básicos
        //   itemsStorage: itemsStorage ? "SÍ" : "NO",
        //   itemsLength: itemsStorage ? JSON.parse(itemsStorage).length : 0,
        //   subtotalStorage: subtotalStorage,
        //   envioStorage: envioStorage,
        //   totalStorage: totalStorage,

        //   // Moneda y conversión
        //   monedaStorage: monedaStorage || "NO",
        //   simboloStorage: simboloStorage || "NO",
        //   tasaStorage: tasaStorage || "NO",

        //   // Valores originales (en CUP)
        //   subtotalOriginalStorage: subtotalOriginalStorage || "NO",
        //   envioOriginalStorage: envioOriginalStorage || "NO",
        //   totalOriginalStorage: totalOriginalStorage || "NO",
        //   descuentoStorage: descuentoStorage || "NO",

        //   // IDs y datos específicos
        //   itemsIdsStorage: itemsIdsStorage ? "SÍ" : "NO",
        //   itemsConEnvioStorage: itemsConEnvioStorage ? "SÍ" : "NO",
        // });

        // Validación crítica: ¿Hay productos?
        if (!itemsStorage || itemsStorage === "[]" || itemsStorage === "null") {
          const error = new Error(
            "No hay productos seleccionados para checkout",
          );
          console.error("❌ Error:", error.message);

          Swal.fire({
            icon: "error",
            title: "Carrito vacío",
            text: "No se encontraron productos en el carrito. Por favor, regresa al carrito y selecciona productos.",
            confirmButtonText: "Volver al carrito",
          }).then(() => {
            window.location.href = "carrito.php";
          });

          reject(error);
          return;
        }

        try {
          // ========== CARGAR DATOS PRINCIPALES ==========

          // 1. Items del carrito (ya convertidos a la moneda seleccionada)
          window.checkoutItems = JSON.parse(itemsStorage);

          // 2. Totales en la MONEDA ACTUAL (los que se muestran al usuario)
          window.checkoutSubtotal = parseFloat(subtotalStorage) || 0;
          window.checkoutEnvio = parseFloat(envioStorage) || 0;
          window.checkoutTotal = parseFloat(totalStorage) || 0;
          window.checkoutDescuento = parseFloat(descuentoStorage) || 0;

          // console.log("🔢 VALORES NUMÉRICOS PARSEADOS:", {
          //   subtotal: window.checkoutSubtotal,
          //   envio: window.checkoutEnvio, // ¡DEBE TENER VALOR!
          //   total: window.checkoutTotal,
          //   envioStorage: envioStorage, // Valor crudo
          //   parseResultado: parseFloat(envioStorage), // Resultado del parse
          // });

          // 3. Datos de MONEDA (CRÍTICO para conversiones)
          window.checkoutMoneda = monedaStorage || "CUP";
          window.checkoutSimbolo = simboloStorage || "$";
          window.checkoutTasa = parseFloat(tasaStorage) || 1;

          // 4. Valores ORIGINALES en CUP (para conversiones y Transfermóvil)
          window.checkoutSubtotalOriginal =
            parseFloat(subtotalOriginalStorage) || 0;
          window.checkoutEnvioOriginal = parseFloat(envioOriginalStorage) || 0;
          window.checkoutTotalOriginal = parseFloat(totalOriginalStorage) || 0;
          window.checkoutDescuentoOriginal =
            parseFloat(descuentoOriginalStorage) || 0;

          // 5. Descuento aplicado (en la moneda actual)
          window.checkoutDescuentoTotal = window.checkoutDescuento;

          // ========== CARGAR DATOS ADICIONALES ==========

          // 6. IDs de los items seleccionados (para backend)
          if (itemsIdsStorage) {
            window.checkoutItemsIds = JSON.parse(itemsIdsStorage) || [];
          } else {
            window.checkoutItemsIds = [];
          }

          // 7. Items con ENVÍO CONVERTIDO (si existe)
          if (itemsConEnvioStorage && itemsConEnvioStorage !== "undefined") {
            try {
              window.checkoutItemsConEnvio = JSON.parse(itemsConEnvioStorage);
              // console.log(
              //   "✅ Datos de envío convertido cargados:",
              //   window.checkoutItemsConEnvio.length,
              //   "items",
              // );
            } catch (e) {
              console.warn("⚠️ Error cargando items con envío convertido:", e);
              // Usar los items normales como fallback
              window.checkoutItemsConEnvio = window.checkoutItems;
            }
          } else {
            window.checkoutItemsConEnvio = window.checkoutItems;
          }

          // ========== VERIFICAR INTEGRIDAD DE DATOS ==========

          // console.log(
          //   "✅ TODOS los datos del carrito cargados correctamente:",
          //   {
          //     // Cantidad y tipo
          //     cantidadItems: window.checkoutItems.length,
          //     tieneEnvioConvertido:
          //       window.checkoutItemsConEnvio !== window.checkoutItems,

          //     // Valores en MONEDA ACTUAL (lo que ve el usuario)
          //     subtotalActual: window.checkoutSubtotal,
          //     envioActual: window.checkoutEnvio,
          //     totalActual: window.checkoutTotal,
          //     monedaActual: window.checkoutMoneda,
          //     simboloActual: window.checkoutSimbolo,
          //     tasaActual: window.checkoutTasa,

          //     // Valores ORIGINALES en CUP (fuente de verdad)
          //     subtotalOriginalCUP: window.checkoutSubtotalOriginal,
          //     envioOriginalCUP: window.checkoutEnvioOriginal,
          //     totalOriginalCUP: window.checkoutTotalOriginal,

          //     // Descuento
          //     descuento: window.checkoutDescuentoTotal,

          //     // Verificación de conversión
          //     conversionValida:
          //       window.monedaCheckout !== "CUP"
          //         ? `Sí (tasa: ${window.checkoutTasa})`
          //         : "No se requiere (CUP)",

          //     // Verificación matemática
          //     verificacionConversion:
          //       window.monedaCheckout !== "CUP"
          //         ? `${window.checkoutTotal} ${window.checkoutMoneda} * ${window.checkoutTasa} = ${(window.checkoutTotal * window.checkoutTasa).toFixed(2)} CUP`
          //         : "N/A",

          //     // Coincidencia con valores originales
          //     totalCoincide:
          //       window.checkoutMoneda === "CUP"
          //         ? window.checkoutTotal === window.checkoutTotalOriginal
          //           ? "✅"
          //           : "❌"
          //         : Math.abs(
          //               window.checkoutTotal * window.checkoutTasa -
          //                 window.checkoutTotalOriginal,
          //             ) < 0.01
          //           ? "✅"
          //           : "❌",
          //   },
          // );

          // ========== VALIDACIÓN ADICIONAL: VERIFICAR CONSISTENCIA ==========

          // Verificar que los valores actuales sean consistentes con los originales
          if (
            window.monedaCheckout !== "CUP" &&
            window.tasaCambioCheckout > 0
          ) {
            const totalCalculadoEnCUP =
              window.checkoutTotal * window.tasaCambioCheckout;
            const diferencia = Math.abs(
              totalCalculadoEnCUP - window.checkoutTotalOriginal,
            );

            if (diferencia > 0.01) {
              console.warn(
                `⚠️ Discrepancia en conversión: ${diferencia.toFixed(4)} CUP`,
              );

              // Recalcular CORRECTAMENTE usando: CUP ÷ tasa = moneda extranjera
              window.checkoutSubtotal = parseFloat(
                (window.checkoutSubtotalOriginal / window.checkoutTasa).toFixed(
                  2,
                ),
              );
              window.checkoutEnvio = parseFloat(
                (window.checkoutEnvioOriginal / window.checkoutTasa).toFixed(2),
              );
              window.checkoutTotal = parseFloat(
                (window.checkoutTotalOriginal / window.checkoutTasa).toFixed(2),
              );

              // console.log("🔄 Valores recalculados correctamente:", {
              //   formula: "CUP ÷ tasa = moneda_extranjera",
              //   subtotal: `${window.checkoutSubtotalOriginal} ÷ ${window.checkoutTasa} = ${window.checkoutSubtotal}`,
              //   total: `${window.checkoutTotalOriginal} ÷ ${window.checkoutTasa} = ${window.checkoutTotal}`,
              // });
            }
          }

          // ========== GUARDAR COPIA EN CUP PARA USO POSTERIOR ==========

          // Crear una copia de los valores originales en CUP para fácil acceso
          window.checkoutSubtotalOriginalCUP = window.checkoutSubtotalOriginal;
          window.checkoutEnvioOriginalCUP = window.checkoutEnvioOriginal;
          window.checkoutTotalOriginalCUP = window.checkoutTotalOriginal;
          window.checkoutDescuentoOriginalCUP =
            window.checkoutDescuentoOriginal;

          // console.log("💾 Copias en CUP guardadas:", {
          //   subtotalCUP: window.checkoutSubtotalOriginalCUP,
          //   envioCUP: window.checkoutEnvioOriginalCUP,
          //   totalCUP: window.checkoutTotalOriginalCUP,
          // });

          resolve();
        } catch (parseError) {
          console.error("❌ Error parseando datos del carrito:", parseError);

          Swal.fire({
            icon: "error",
            title: "Error en los datos",
            text: "Los datos del carrito están corruptos. Por favor, vacía el carrito y vuelve a intentarlo.",
            confirmButtonText: "Volver al carrito",
          }).then(() => {
            window.location.href = "carrito.php";
          });

          reject(parseError);
        }
      } catch (error) {
        console.error("❌ Error general cargando datos del carrito:", error);

        Swal.fire({
          icon: "error",
          title: "Error",
          text: "Error al cargar los datos del carrito: " + error.message,
          confirmButtonText: "Volver al carrito",
        }).then(() => {
          window.location.href = "carrito.php";
        });

        reject(error);
      }
    });
  }

  function debugDatosEnvio() {
    // console.log("🚚 DEBUG DE ENVÍO COMPLETO:");

    // Verificar sessionStorage
    // console.log("📦 sessionStorage:", {
    //   checkoutEnvio: sessionStorage.getItem("checkoutEnvio"),
    //   checkoutEnvioOriginal: sessionStorage.getItem("checkoutEnvioOriginal"),
    //   checkoutTotal: sessionStorage.getItem("checkoutTotal"),
    //   checkoutSubtotal: sessionStorage.getItem("checkoutSubtotal"),
    // });

    // Verificar variables globales
    // console.log("💰 Variables globales:", {
    //   checkoutEnvio: window.checkoutEnvio,
    //   checkoutEnvioOriginal: window.checkoutEnvioOriginal,
    //   checkoutEnvioOriginalCUP: window.checkoutEnvioOriginalCUP,
    //   checkoutTotal: window.checkoutTotal,
    //   checkoutSubtotal: window.checkoutSubtotal,
    //   monedaCheckout: window.monedaCheckout,
    //   simboloMonedaCheckout: window.simboloMonedaCheckout,
    //   tasaCambioCheckout: window.tasaCambioCheckout,
    // });

    // Verificar cálculos
    const totalCalculado =
      window.checkoutSubtotal +
      window.checkoutEnvio -
      (window.checkoutDescuento || 0);
    // console.log("🧮 Verificación de cálculos:", {
    //   formula: "subtotal + envio - descuento = total",
    //   subtotal: window.checkoutSubtotal,
    //   envio: window.checkoutEnvio,
    //   descuento: window.checkoutDescuento || 0,
    //   totalCalculado: totalCalculado,
    //   totalActual: window.checkoutTotal,
    //   coincide: Math.abs(totalCalculado - window.checkoutTotal) < 0.01,
    // });
  }

  function cargarDatosUsuario() {
    return new Promise((resolve, reject) => {
      try {
        // Prellenar formulario con datos del usuario
        if (window.usuarioData && window.usuarioData.nombres) {
          $("#nombres").val(window.usuarioData.nombres);
        }
        if (window.usuarioData && window.usuarioData.apellidos) {
          $("#apellidos").val(window.usuarioData.apellidos);
        }
        if (window.usuarioData && window.usuarioData.email) {
          $("#email").val(window.usuarioData.email);
        }
        if (window.usuarioData && window.usuarioData.telefono) {
          $("#telefono").val(window.usuarioData.telefono);
        }

        // console.log('✅ Datos del usuario cargados');
        resolve();
      } catch (error) {
        console.error("Error cargando datos del usuario:", error);
        resolve(); // Continuamos aunque falle
      }
    });
  }

  async function actualizarMonedaCheckout() {
    try {
      const monedaSeleccionada =
        localStorage.getItem("moneda-seleccionada") || "CUP";
      // console.log('💰 Moneda seleccionada:', monedaSeleccionada);

      // Obtener tasa de cambio
      const response = await $.post("../Controllers/MonedaController.php", {
        funcion: "obtener_tasa_cambio",
        moneda: monedaSeleccionada,
      });

      const data =
        typeof response === "string" ? JSON.parse(response) : response;

      if (data.success) {
        window.tasaCambioCheckout = parseFloat(data.tasa_cambio) || 1;

        // Determinar símbolo
        if (data.moneda && typeof data.moneda === "object") {
          window.simboloMonedaCheckout = data.moneda.simbolo || "$";
          window.monedaCheckout = data.moneda.codigo || monedaSeleccionada;
        } else {
          window.simboloMonedaCheckout = "$";
          window.monedaCheckout = monedaSeleccionada;
        }

        // console.log('💱 Configuración moneda:', {
        //     moneda: window.monedaCheckout,
        //     simbolo: window.simboloMonedaCheckout,
        //     tasa: window.tasaCambioCheckout
        // });

        // IMPORTANTE: Si cambiamos de moneda, necesitamos recalcular
        await actualizarPreciosCheckout();
      } else {
        throw new Error(data.error || "Error obteniendo tasa");
      }
    } catch (error) {
      console.error("❌ Error actualizando moneda:", error);
      // Valores por defecto
      window.monedaCheckout = "CUP";
      window.simboloMonedaCheckout = "$";
      window.tasaCambioCheckout = 1;
      await actualizarPreciosCheckout();
    }
  }

  // ================= FUNCIÓN PARA MANEJAR CONVERSIÓN DE MONEDA SEGÚN MÉTODO DE PAGO =================

  function manejarConversionMonedaParaPago(metodoPago) {
    // console.log("💰 Manejando conversión para método:", metodoPago);

    if (!metodoPago) {
      // console.log("⚠️ No se especificó método de pago");
      return { convertido: false };
    }

    // Métodos que SIEMPRE requieren CUP (moneda nacional)
    const metodosQueRequierenCUP = [
      "transfermovil",
      "transfermovíl",
      "transfermóvil",
      "transferencia bancaria",
      "efectivo",
      "cash",
      "contraentrega",
    ];

    const metodoLower = metodoPago.toLowerCase().trim();
    let requiereConversion = false;

    // Verificar si el método requiere CUP
    for (const metodoCUP of metodosQueRequierenCUP) {
      if (metodoLower.includes(metodoCUP) || metodoCUP.includes(metodoLower)) {
        requiereConversion = true;
        // console.log(`✅ Método "${metodoPago}" requiere CUP`);
        break;
      }
    }

    // Si NO requiere conversión o YA está en CUP, salir
    if (!requiereConversion || window.monedaCheckout === "CUP") {
      // console.log("ℹ️ No se requiere conversión a CUP");
      return {
        convertido: false,
        razon: requiereConversion ? "Ya está en CUP" : "No requiere CUP",
      };
    }

    // ========== SOLO AQUÍ SE CONVIERTE PARA TRANSFERMÓVIL ==========
    // console.log("🔄 CONVIRTIENDO a CUP para", metodoPago);

    try {
      // Guardar estado ORIGINAL (moneda actual con valores convertidos)
      window.monedaOriginal = window.monedaCheckout;
      window.simboloOriginal = window.simboloMonedaCheckout;
      window.tasaOriginal = window.tasaCambioCheckout;

      // Guardar los valores actuales (en moneda extranjera)
      window.subtotalOriginal = window.checkoutSubtotal;
      window.envioOriginal = window.checkoutEnvio;
      window.descuentoOriginal = window.checkoutDescuento; // ¡AGREGAR DESCUENTO!
      window.totalOriginal = window.checkoutTotal;

      // console.log("💾 Estado original guardado:", {
      //   moneda: window.monedaOriginal,
      //   simbolo: window.simboloOriginal,
      //   subtotal: window.subtotalOriginal,
      //   envio: window.envioOriginal,
      //   descuento: window.descuentoOriginal, // ¡AGREGAR!
      //   total: window.totalOriginal,
      // });

      // ========== CONVERSIÓN REAL A CUP ==========
      // Usar valores ORIGINALES en CUP (los que guardó carrito.js)
      window.checkoutSubtotal = window.checkoutSubtotalOriginalCUP;
      window.checkoutEnvio = window.checkoutEnvioOriginalCUP;
      window.checkoutTotal = window.checkoutTotalOriginalCUP;

      // ¡IMPORTANTE! También convertir el descuento
      if (window.checkoutDescuentoOriginalCUP !== undefined) {
        window.checkoutDescuento = window.checkoutDescuentoOriginalCUP;
        // console.log("✅ Descuento convertido a CUP:", window.checkoutDescuento);
      } else {
        // Si no existe, calcular: descuento en CUP = descuento actual × tasa
        window.checkoutDescuento =
          window.descuentoOriginal * window.tasaOriginal;
        // console.log(
        //   "🔄 Descuento calculado para CUP:",
        //   window.checkoutDescuento,
        // );
      }

      // Cambiar configuración a CUP
      window.monedaCheckout = "CUP";
      window.simboloMonedaCheckout = "$";
      window.tasaCambioCheckout = 1;

      // console.log("✅ Convertido a CUP para Transfermóvil:", {
      //   antes: {
      //     moneda: window.monedaOriginal,
      //     subtotal: window.subtotalOriginal,
      //     envio: window.envioOriginal,
      //     descuento: window.descuentoOriginal, // ¡AGREGAR!
      //     total: window.totalOriginal,
      //   },
      //   despues: {
      //     moneda: "CUP",
      //     subtotal: window.checkoutSubtotal,
      //     envio: window.checkoutEnvio,
      //     descuento: window.checkoutDescuento, // ¡AGREGAR!
      //     total: window.checkoutTotal,
      //   },
      //   tasa: window.tasaOriginal,
      //   descuentoConvertido: `${window.descuentoOriginal} ${window.monedaOriginal} × ${window.tasaOriginal} = ${window.checkoutDescuento} CUP`,
      // });

      // Actualizar UI
      actualizarResumenLateral();

      return {
        convertido: true,
        monedaAnterior: window.monedaOriginal,
        monedaNueva: "CUP",
        subtotalAnterior: window.subtotalOriginal,
        subtotalNuevo: window.checkoutSubtotal,
        envioAnterior: window.envioOriginal,
        envioNuevo: window.checkoutEnvio,
        descuentoAnterior: window.descuentoOriginal, // ¡AGREGAR!
        descuentoNuevo: window.checkoutDescuento, // ¡AGREGAR!
        totalAnterior: window.totalOriginal,
        totalNuevo: window.checkoutTotal,
        metodo: metodoPago,
      };
    } catch (error) {
      console.error("❌ Error en conversión a CUP:", error);
      return {
        convertido: false,
        error: error.message,
      };
    }
  }

  // ================= FINAL =================

  // function debugConversionCompleta() {
  // console.log("=== DEBUG CONVERSIÓN COMPLETA ===");
  // console.log("1. Valores del sessionStorage (deberían estar en CUP):", {
  //   checkoutSubtotalOriginal: window.checkoutSubtotalOriginal,
  //   checkoutEnvioOriginal: window.checkoutEnvioOriginal,
  //   checkoutTotalOriginal: window.checkoutTotalOriginal,
  // });

  // console.log("2. Configuración actual:", {
  //   monedaCheckout: window.monedaCheckout,
  //   simboloMonedaCheckout: window.simboloMonedaCheckout,
  //   tasaCambioCheckout: window.tasaCambioCheckout,
  // });

  // console.log("3. Valores mostrados actualmente:", {
  //   checkoutSubtotal: window.checkoutSubtotal,
  //   checkoutEnvio: window.checkoutEnvio,
  //   checkoutTotal: window.checkoutTotal,
  // });

  // console.log("4. Conversión USD → CUP (si aplica):", {
  //   subtotalUSD: window.checkoutSubtotal,
  //   subtotalCUP_calculado:
  //     window.checkoutSubtotal * window.tasaCambioCheckout,
  //   totalUSD: window.checkoutTotal,
  //   totalCUP_calculado: window.checkoutTotal * window.tasaCambioCheckout,
  // });

  // console.log("5. ¿Qué debería pasar con Transfermóvil?");
  // console.log("   - Si moneda actual es USD y tasa = 450");
  // console.log(
  //   "   - checkoutTotal = 2495 (esto está MAL, debería ser ~5.54 USD)",
  // );
  // console.log(
  //   "   - Para CUP necesitamos: 2495 CUP (que es checkoutTotalOriginal)",
  // );
  // console.log("==============================");
  // }

  // ================= FUNCIÓN PARA RESTAURAR MONEDA ORIGINAL =================

  function restaurarMonedaOriginal() {
    if (window.monedaOriginal && window.monedaOriginal !== "CUP") {
      // console.log("🔄 Restaurando moneda original:", window.monedaOriginal);

      try {
        // Restaurar configuración de moneda
        window.monedaCheckout = window.monedaOriginal;
        window.simboloMonedaCheckout = window.simboloOriginal;
        window.tasaCambioCheckout = window.tasaOriginal;

        // Restaurar valores originales (en la moneda original)
        if (window.subtotalOriginal !== undefined) {
          window.checkoutSubtotal = window.subtotalOriginal;
          window.checkoutEnvio = window.envioOriginal;
          window.checkoutDescuento = window.descuentoOriginal || 0; // ¡AGREGAR DESCUENTO!
          window.checkoutTotal = window.totalOriginal;
        } else {
          // Si no hay valores guardados, recargar precios
          // console.log("🔄 No hay valores guardados, recalculando...");
          actualizarPreciosCheckout();
        }

        // Actualizar UI
        actualizarResumenLateral();

        // console.log("✅ Moneda restaurada:", {
        //   moneda: window.monedaCheckout,
        //   simbolo: window.simboloMonedaCheckout,
        //   tasa: window.tasaCambioCheckout,
        //   subtotal: window.checkoutSubtotal,
        //   envio: window.checkoutEnvio,
        //   descuento: window.checkoutDescuento, // ¡AGREGAR!
        //   total: window.checkoutTotal,
        // });
      } catch (error) {
        console.error("❌ Error restaurando moneda:", error);

        // Fallback: usar valores por defecto
        window.monedaCheckout = "CUP";
        window.simboloMonedaCheckout = "$";
        window.tasaCambioCheckout = 1;
        actualizarPreciosCheckout();

        // console.log("🔄 Restaurado a valores por defecto (CUP)");
      }
    } else {
      // console.log("ℹ️ No hay moneda original para restaurar o ya está en CUP");
    }
  }

  // ================= FINAL =================

  function verificarConsistenciaMontos() {
    // console.log("🔍 Verificando consistencia de montos...");

    // Si es CUP, verificación simple
    if (window.monedaCheckout === "CUP") {
      const esConsistente =
        Math.abs(window.checkoutSubtotal - window.checkoutSubtotalOriginal) <
          0.01 &&
        Math.abs(window.checkoutTotal - window.checkoutTotalOriginal) < 0.01;

      // console.log("✅ Verificación CUP:", {
      //   esConsistente: esConsistente,
      //   subtotal: `${window.checkoutSubtotal} = ${window.checkoutSubtotalOriginal}`,
      //   total: `${window.checkoutTotal} = ${window.checkoutTotalOriginal}`,
      // });

      return esConsistente;
    }

    // Para moneda extranjera: verificar conversión CUP → moneda extranjera
    const subtotalCalculado =
      window.checkoutSubtotalOriginal / window.tasaCambioCheckout;
    const totalCalculado =
      window.checkoutTotalOriginal / window.tasaCambioCheckout;

    // Redondear para comparación
    const subtotalCalculadoRedondeado =
      Math.round(subtotalCalculado * 100) / 100;
    const totalCalculadoRedondeado = Math.round(totalCalculado * 100) / 100;

    const subtotalActualRedondeado =
      Math.round(window.checkoutSubtotal * 100) / 100;
    const totalActualRedondeado = Math.round(window.checkoutTotal * 100) / 100;

    // console.log("📊 Verificación de conversión:", {
    //   formula: `CUP ÷ ${window.tasaCambioCheckout} = ${window.monedaCheckout}`,
    //   calculos: {
    //     subtotal: `${window.checkoutSubtotalOriginal} CUP ÷ ${window.tasaCambioCheckout} = ${subtotalCalculado.toFixed(4)} ${window.monedaCheckout}`,
    //     total: `${window.checkoutTotalOriginal} CUP ÷ ${window.tasaCambioCheckout} = ${totalCalculado.toFixed(4)} ${window.monedaCheckout}`,
    //   },
    //   redondeos: {
    //     subtotal: `${subtotalCalculado.toFixed(4)} → ${subtotalCalculadoRedondeado}`,
    //     total: `${totalCalculado.toFixed(4)} → ${totalCalculadoRedondeado}`,
    //   },
    //   valores_actuales: {
    //     subtotal: subtotalActualRedondeado,
    //     total: totalActualRedondeado,
    //   },
    // });

    // Verificar con tolerancia de 0.01 (1 céntimo)
    const subtotalEsConsistente =
      Math.abs(subtotalCalculadoRedondeado - subtotalActualRedondeado) < 0.01;
    const totalEsConsistente =
      Math.abs(totalCalculadoRedondeado - totalActualRedondeado) < 0.01;

    const esConsistente = subtotalEsConsistente && totalEsConsistente;

    if (!esConsistente) {
      console.error("❌ Inconsistencia detectada!");

      if (!subtotalEsConsistente) {
        console.error(
          `   Subtotal: ${subtotalCalculadoRedondeado} ≠ ${subtotalActualRedondeado}`,
        );
        console.error(
          `   Diferencia: ${Math.abs(subtotalCalculadoRedondeado - subtotalActualRedondeado).toFixed(4)}`,
        );
      }

      if (!totalEsConsistente) {
        console.error(
          `   Total: ${totalCalculadoRedondeado} ≠ ${totalActualRedondeado}`,
        );
        console.error(
          `   Diferencia: ${Math.abs(totalCalculadoRedondeado - totalActualRedondeado).toFixed(4)}`,
        );
      }

      console.error("   🔍 Valores de referencia:", {
        subtotalOriginalCUP: window.checkoutSubtotalOriginal,
        totalOriginalCUP: window.checkoutTotalOriginal,
        tasa: window.tasaCambioCheckout,
        moneda: window.monedaCheckout,
      });
    } else {
      // console.log("✅ Conversión consistente");
    }

    return esConsistente;
  }

  async function actualizarPreciosCheckout() {
    // console.log(
    //   "🔄 actualizarPreciosCheckout() ejecutándose - MOSTRAR valores de sessionStorage",
    // );

    // ========== IMPORTANTE ==========
    // NO CONVERTIR los precios aquí. Solo usar los valores YA CONVERTIDOS
    // que vienen de sessionStorage desde carrito.js
    // ================================

    // Los valores YA están convertidos en sessionStorage
    // window.checkoutSubtotal, window.checkoutEnvio, window.checkoutTotal
    // ya tienen los valores en la moneda seleccionada

    // console.log("💰 Valores YA CONVERTIDOS desde carrito.js:", {
    //   subtotal: window.checkoutSubtotal,
    //   envio: window.checkoutEnvio,
    //   total: window.checkoutTotal,
    //   moneda: window.monedaCheckout,
    //   simbolo: window.simboloMonedaCheckout,
    // });

    // Solo asegurar redondeo
    window.checkoutSubtotal = parseFloat(window.checkoutSubtotal.toFixed(2));
    window.checkoutEnvio = parseFloat(window.checkoutEnvio.toFixed(2));
    window.checkoutTotal = parseFloat(window.checkoutTotal.toFixed(2));

    // Los valores originales en CUP ya están cargados
    // window.checkoutSubtotalOriginalCUP, etc.

    // console.log("✅ Precios mostrados SIN conversión:", {
    //   moneda: window.monedaCheckout,
    //   total: window.checkoutTotal,
    //   totalCUP: window.checkoutTotalOriginalCUP,
    // });

    actualizarResumenLateral();
  }

  window.actualizarResumenLateral = function () {
    // console.log("🎯 actualizarResumenLateral() ejecutándose");
    // console.log("💰 Valores a mostrar:", {
    //   subtotal: window.checkoutSubtotal,
    //   envio: window.checkoutEnvio,
    //   descuento: window.checkoutDescuento || 0,
    //   total: window.checkoutTotal,
    //   simbolo: window.simboloMonedaCheckout,
    // });

    // Asegurar que los valores sean números válidos
    const subtotal = isNaN(window.checkoutSubtotal)
      ? 0
      : window.checkoutSubtotal;
    const envio = isNaN(window.checkoutEnvio) ? 0 : window.checkoutEnvio;
    const descuento = isNaN(window.checkoutDescuento)
      ? 0
      : window.checkoutDescuento;
    const total = isNaN(window.checkoutTotal) ? 0 : window.checkoutTotal;

    const subtotalTexto = `${window.simboloMonedaCheckout} ${subtotal.toFixed(2)}`;
    const envioTexto = `${window.simboloMonedaCheckout} ${envio.toFixed(2)}`;
    const descuentoTexto = `${window.simboloMonedaCheckout} ${descuento.toFixed(2)}`;
    const totalTexto = `${window.simboloMonedaCheckout} ${total.toFixed(2)}`;

    // console.log("📝 Textos a insertar:", {
    //   subtotalTexto,
    //   envioTexto,
    //   descuentoTexto,
    //   totalTexto,
    // });

    // Actualizar elementos específicos
    $("#resumen-lateral-subtotal").text(subtotalTexto);
    $("#resumen-lateral-envio").text(envioTexto);
    $("#resumen-lateral-descuento").text(descuentoTexto);
    $("#resumen-lateral-total").text(totalTexto);

    // console.log("✅ Resumen lateral actualizado con descuentos");
  };

  function crearResumenLateralSiNoExiste() {
    // console.log('🛠️ Creando resumen lateral dinámicamente...');

    // Buscar el contenedor del resumen lateral
    let resumenContainer = $(
      ".resumen-lateral, .checkout-summary, .summary, .cart-summary",
    );

    if (resumenContainer.length === 0) {
      // console.log('⚠️ No se encontró contenedor de resumen, creando uno');
      // Crear un contenedor básico
      $("body").append(`
                <div id="resumen-lateral-dinamico" style="position: fixed; top: 100px; right: 20px; width: 300px; background: white; border: 1px solid #ddd; padding: 20px; z-index: 1000; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                    <h5>Resumen del Pedido</h5>
                    <div class="mb-2">
                        <span>Subtotal:</span>
                        <span id="resumen-lateral-subtotal-dinamico" style="float: right;">${
                          window.simboloMonedaCheckout
                        } ${window.checkoutSubtotal.toFixed(2)}</span>
                    </div>
                    <div class="mb-2">
                        <span>Envío:</span>
                        <span id="resumen-lateral-envio-dinamico" style="float: right;">${
                          window.simboloMonedaCheckout
                        } ${window.checkoutEnvio.toFixed(2)}</span>
                    </div>
                    <div class="mb-2">
                        <span>Descuento:</span>
                        <span id="resumen-lateral-descuento-dinamico" style="float: right;">${
                          window.simboloMonedaCheckout
                        } 0.00</span>
                    </div>
                    <hr>
                    <div class="mb-2">
                        <strong>Total:</strong>
                        <strong id="resumen-lateral-total-dinamico" style="float: right;">${
                          window.simboloMonedaCheckout
                        } ${window.checkoutTotal.toFixed(2)}</strong>
                    </div>
                </div>
            `);
      resumenContainer = $("#resumen-lateral-dinamico");
    }

    // Guardar referencias a los nuevos elementos
    window.resumenLateralElements = {
      subtotal: $("#resumen-lateral-subtotal-dinamico"),
      envio: $("#resumen-lateral-envio-dinamico"),
      descuento: $("#resumen-lateral-descuento-dinamico"),
      total: $("#resumen-lateral-total-dinamico"),
    };
  }

  async function actualizarResumenProductos() {
    const $resumenProductos = $("#resumen-productos");
    // console.log('🔄 Actualizando resumen de productos...');
    // console.log('📦 checkoutItems:', window.checkoutItems);

    if (!window.checkoutItems || window.checkoutItems.length === 0) {
      console.error("❌ No hay productos en checkoutItems");
      $resumenProductos.html(`
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    No hay productos seleccionados. Por favor, regresa al carrito.
                    <br>
                    <button class="btn btn-sm btn-primary mt-2" onclick="window.location.href='carrito.php'">
                        <i class="fas fa-shopping-cart mr-2"></i>Volver al carrito
                    </button>
                </div>
            `);
      return;
    }

    try {
      // console.log('📡 Solicitando datos del carrito al servidor...');

      const response = await $.post("../Controllers/CarritoController.php", {
        funcion: "obtener_carrito",
      });

      // console.log('📥 Respuesta del servidor:', response);

      let carritoCompleto;
      if (typeof response === "string") {
        try {
          carritoCompleto = JSON.parse(response);
          // console.log('✅ Datos parseados correctamente');
        } catch (e) {
          console.error("❌ Error parseando respuesta JSON:", e);
          console.error("Respuesta original:", response);

          if (response.includes("no_sesion")) {
            console.error("Sesión expirada, recargando página...");
            window.location.reload();
            return;
          }

          throw new Error("Respuesta del servidor no válida");
        }
      } else {
        carritoCompleto = response;
      }

      // Si el servidor retorna un error
      if (carritoCompleto.error === "no_sesion") {
        console.error("❌ Sesión no válida");
        window.location.reload();
        return;
      }

      if (!Array.isArray(carritoCompleto)) {
        console.error("❌ La respuesta no es un array:", carritoCompleto);
        throw new Error("Formato de respuesta inválido");
      }

      // console.log('✅ Carrito completo recibido:', carritoCompleto);
      // console.log('🔍 Cantidad de productos en carrito:', carritoCompleto.length);

      // Filtrar productos seleccionados
      const productosSeleccionados = carritoCompleto.filter((item) => {
        const encontrado = window.checkoutItems.includes(item.id.toString());
        // console.log(`   - Producto ${item.id}: ${encontrado ? 'SELECCIONADO' : 'NO SELECCIONADO'}`);
        return encontrado;
      });

      // console.log('🎯 Productos seleccionados para checkout:', productosSeleccionados);
      // console.log('📊 Total de productos seleccionados:', productosSeleccionados.length);

      if (productosSeleccionados.length === 0) {
        console.warn(
          "⚠️ No se encontraron los productos seleccionados en el carrito completo",
        );
        // console.log('🔄 Los IDs buscados eran:', window.checkoutItems);
        // console.log('🔄 Los IDs disponibles son:', carritoCompleto.map(item => item.id.toString()));

        $resumenProductos.html(`
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        No se encontraron los productos seleccionados en tu carrito.
                        <br>
                        <small class="text-muted">Esto puede ocurrir si los productos fueron eliminados o si la sesión cambió.</small>
                        <br>
                        <button class="btn btn-sm btn-primary mt-2" onclick="window.location.href='carrito.php'">
                            <i class="fas fa-shopping-cart mr-2"></i>Regresar al carrito
                        </button>
                    </div>
                `);
        return;
      }

      let html = "";
      productosSeleccionados.forEach((producto, index) => {
        const precioFinalOriginal =
          parseFloat(producto.precio_final) || parseFloat(producto.precio) || 0;
        const precioUnitarioOriginal =
          parseFloat(producto.precio_unitario) || precioFinalOriginal;
        const precioFinalConvertido =
          precioFinalOriginal * window.tasaCambioCheckout;
        const precioUnitarioConvertido =
          precioUnitarioOriginal * window.tasaCambioCheckout;
        const cantidad = parseInt(producto.cantidad_producto) || 1;
        const subtotalConvertido = precioFinalConvertido * cantidad;
        const tieneDescuento = parseFloat(producto.descuento_porcentaje) > 0;

        // console.log(`📦 Producto ${index + 1}:`, {
        //     id: producto.id,
        //     nombre: producto.nombre,
        //     precioFinalOriginal,
        //     precioFinalConvertido,
        //     cantidad,
        //     subtotalConvertido
        // });

        html += `
                    <div class="resumen-producto mb-3 p-3 border rounded">
                        <div class="row align-items-center">
                            <div class="col-2">
                                <img src="../Util/Img/Producto/${
                                  producto.imagen || "producto_default.png"
                                }" 
                                     alt="${producto.nombre}" 
                                     class="img-fluid rounded"
                                     style="max-height: 80px; object-fit: cover;"
                                     onerror="this.src='../Util/Img/Producto/producto_default.png'">
                            </div>
                            <div class="col-6">
                                <strong class="d-block">${
                                  producto.nombre || "Producto"
                                }</strong>
                                <small class="text-muted">${
                                  producto.marca_nombre || ""
                                }</small>
                                <br>
                                <small class="text-muted">Vendido por: ${
                                  producto.tienda_nombre || "Tienda"
                                }</small>
                                ${
                                  producto.detalles
                                    ? `<br><small class="text-muted">${producto.detalles}</small>`
                                    : ""
                                }
                            </div>
                            <div class="col-2 text-center">
                                <small class="text-muted">Cantidad: ${cantidad}</small>
                            </div>
                            <div class="col-2 text-right">
                                <strong>${
                                  window.simboloMonedaCheckout
                                } ${subtotalConvertido.toFixed(2)}</strong>
                                ${
                                  tieneDescuento
                                    ? `
                                    <br>
                                    <small class="text-muted text-decoration-line-through">${
                                      window.simboloMonedaCheckout
                                    } ${precioUnitarioConvertido.toFixed(
                                      2,
                                    )}</small>
                                    <small class="text-success">-${
                                      producto.descuento_porcentaje
                                    }%</small>
                                `
                                    : ""
                                }
                                <br>
                                <small class="text-muted">${
                                  window.simboloMonedaCheckout
                                } ${precioFinalConvertido.toFixed(
                                  2,
                                )} c/u</small>
                            </div>
                        </div>
                    </div>
                `;
      });

      $resumenProductos.html(html);
      // console.log('✅ Resumen de productos actualizado correctamente');
    } catch (error) {
      console.error("❌ Error cargando detalles de productos:", error);
      $resumenProductos.html(`
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    Error al cargar los detalles de los productos: ${error.message}
                    <br>
                    <small class="text-muted">Por favor, recarga la página o regresa al carrito.</small>
                    <br>
                    <button class="btn btn-sm btn-primary mt-2" onclick="window.location.reload()">
                        <i class="fas fa-sync-alt mr-2"></i>Recargar página
                    </button>
                    <button class="btn btn-sm btn-secondary mt-2" onclick="window.location.href='carrito.php'">
                        <i class="fas fa-shopping-cart mr-2"></i>Volver al carrito
                    </button>
                </div>
            `);
    }
  }

  // ================= FUNCIONES AUXILIARES =================

  async function cargarDireccionesUsuario() {
    try {
      const response = await $.post(
        "../Controllers/UsuarioMunicipioController.php",
        {
          funcion: "llenar_direcciones",
        },
      );

      const direcciones =
        typeof response === "string" ? JSON.parse(response) : response;
      // console.log(direcciones);
      if (direcciones && direcciones.length > 0) {
        let html = `
                    <div class="mb-3">
                        <h6 class="mb-3" style="color: black">Direcciones guardadas:</h6>
                `;

        direcciones.forEach((direccion, index) => {
          // Formatear dirección completa (dirección, municipio, provincia)
          const direccionCompleta =
            `${direccion.direccion}, ${direccion.municipio}, ${direccion.provincia}`.trim();

          if (direccionCompleta && direccionCompleta !== ", ,") {
            html += `
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" 
                                       name="direccion_guardada" 
                                       id="dir_${index}" 
                                       value="${direccionCompleta.replace(
                                         /"/g,
                                         "&quot;",
                                       )}"
                                       onchange="seleccionarDireccionGuardada('${direccionCompleta.replace(
                                         /'/g,
                                         "\\'",
                                       )}')">
                                <label class="form-check-label" for="dir_${index}">
                                    <small>${direccionCompleta}</small>
                                </label>
                            </div>
                        `;
          }
        });

        // Insertar después del campo de dirección
        $("#direccion").closest(".mb-3").after(html);
      }
    } catch (error) {
      console.error("Error cargando direcciones:", error);
      // No mostrar error al usuario, simplemente no mostrar direcciones guardadas
    }
  }

  async function cargarMetodosPago() {
    try {
      const response = await fetch("../Controllers/PagoController.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded",
        },
        body: "funcion=obtener_metodos_pago",
      });

      const data = await response.json();

      // DEBUG: Ver qué devuelve el servidor
      // console.log('Respuesta métodos de pago:', data);

      if (data.success && Array.isArray(data.metodos)) {
        // Limpiar dropdown
        $("#metodo-pago-select").empty();

        // Agregar opción por defecto
        $("#metodo-pago-select").append(
          '<option value="" selected disabled>Selecciona un método de pago</option>',
        );

        // Agregar métodos
        data.metodos.forEach((metodo, index) => {
          let texto = "";

          switch (metodo.tipo) {
            case "tarjeta_credito":
              texto = `💳 Tarjeta ${
                metodo.numero_enmascarado || "**** **** **** ****"
              }`;
              if (metodo.fecha_vencimiento) {
                texto += ` (Vence: ${metodo.fecha_vencimiento})`;
              }
              break;
            case "paypal":
              texto = `📧 PayPal ${metodo.email_enmascarado || "***@***"}`;
              break;
            case "transferencia":
              texto = `🏦 ${metodo.banco || "Transferencia"} ${
                metodo.cuenta_enmascarada || "****"
              }`;
              break;
            case "efectivo":
              texto = `💵 Pago en efectivo`;
              break;
            default:
              texto = `📋 ${metodo.tipo || "Método de pago"}`;
          }

          // Marcar como predeterminado
          if (metodo.predeterminado) {
            texto += " ⭐";
          }

          $("#metodo-pago-select").append(
            `<option value="${metodo.id}" data-tipo="${metodo.tipo}" 
                         ${metodo.predeterminado ? "selected" : ""}>
                            ${texto} - ${metodo.titular || ""}
                        </option>`,
          );
        });

        // Agregar opción para nuevo método
        $("#metodo-pago-select").append(
          '<option value="nuevo">➕ Agregar nuevo método de pago</option>',
        );

        return data.metodos;
      } else {
        console.warn(
          "No se encontraron métodos de pago o formato inválido:",
          data,
        );

        // Limpiar y mostrar mensaje
        $("#metodo-pago-select").empty();
        $("#metodo-pago-select").append(
          '<option value="" selected>No tienes métodos de pago guardados</option>',
        );
        $("#metodo-pago-select").append(
          '<option value="nuevo">➕ Agregar nuevo método de pago</option>',
        );

        return [];
      }
    } catch (error) {
      console.error("Error cargando métodos de pago:", error);

      // Manejo de error en UI
      $("#metodo-pago-select").empty();
      $("#metodo-pago-select").append(
        '<option value="" selected>Error cargando métodos de pago</option>',
      );
      $("#metodo-pago-select").append(
        '<option value="nuevo">➕ Agregar nuevo método de pago</option>',
      );

      $("#metodo-pago-select").append(
        '<option value="nuevo">➕ Agregar nuevo método de pago</option>',
      );

      return [];
    }
  }

  // ================= EVENT LISTENERS =================

  // Evento para cambio de moneda
  $(document).on("monedaCambiada", function () {
    // console.log("🎯 Evento monedaCambiada recibido en checkout");
    actualizarMonedaCheckout().then(() => {
      // Si estamos en el paso 3, actualizar también el resumen final
      if ($("#step-content-3").is(":visible")) {
        actualizarResumenFinal();
      }
    });
  });

  // Validación de tarjeta de crédito en tiempo real
  $(document).on("input", 'input[name="tarjeta_numero"]', function () {
    let valor = $(this).val().replace(/\s/g, "");
    valor = valor.replace(/(\d{4})/g, "$1 ").trim();
    $(this).val(valor);

    // Validar longitud mínima
    const numerosLimpios = valor.replace(/\s/g, "");
    if (numerosLimpios.length > 16) {
      $(this).val(valor.substring(0, 19)); // 16 dígitos + 3 espacios
    }
  });

  $(document).on("input", 'input[name="tarjeta_vencimiento"]', function () {
    let valor = $(this).val().replace(/\D/g, "");
    if (valor.length >= 2) {
      valor = valor.substring(0, 2) + "/" + valor.substring(2, 4);
    }
    if (valor.length > 5) {
      valor = valor.substring(0, 5);
    }
    $(this).val(valor);
  });

  $(document).on("input", 'input[name="tarjeta_cvv"]', function () {
    let valor = $(this).val().replace(/\D/g, "");
    if (valor.length > 4) {
      valor = valor.substring(0, 4);
    }
    $(this).val(valor);
  });

  // ================= FUNCIONES GLOBALES DENTRO DE $(DOCUMENT).READY =================

  // Función para verificar estado y registrar transferencia
  window.verificarYRegistrarTransferencia = async function (ordenId) {
    console.log("🔍 Verificando estado antes de registrar transferencia...");

    // 🔥 IMPORTANTE: Esperar a que la promesa se resuelva
    const estado = await verificarEstadoFormulario();

    if (!estado.valido) {
        Swal.fire({
            icon: "warning",
            title: "Formulario incompleto",
            html: `
                <p>${estado.mensaje}</p>
                <p class="text-muted">Por favor, ${estado.accion} antes de continuar.</p>
            `,
            confirmButtonText: "Entendido",
        });
        return;
    }

    // Continuar con el registro
    await registrarTransferencia(ordenId);
};

  window.siguientePaso = function (paso) {
    // console.log('🔄 Navegando al paso:', paso);

    // Validar paso actual antes de avanzar
    if (paso === 2 && !validarPaso1()) {
      return;
    }
    if (paso === 3 && !validarPaso2()) {
      return;
    }

    // Ocultar todos los pasos
    $(".checkout-step").hide();

    // Mostrar paso seleccionado
    $(`#step-content-${paso}`).show();

    // Actualizar indicadores de pasos
    $(".step").removeClass("active completed");

    for (let i = 1; i <= 3; i++) {
      if (i < paso) {
        $(`#step-${i}`).addClass("completed");
      } else if (i === paso) {
        $(`#step-${i}`).addClass("active");
      }
    }

    // Si vamos al paso 3, actualizar TODOS los resúmenes
    if (paso === 3) {
      // console.log("🎯 Actualizando resúmenes para paso 3...");
      actualizarResumenFinal();
    }
  };

  function validarPaso1() {
    // console.log('🔍 Validando Paso 1 (Información de envío)...');

    // Obtener valores
    const nombres = $("#nombres").val()?.trim() || "";
    const apellidos = $("#apellidos").val()?.trim() || "";
    const direccion = $("#direccion").val()?.trim() || "";
    const telefono = $("#telefono").val()?.trim() || "";
    const email = $("#email").val()?.trim() || "";

    // console.log('📋 Valores obtenidos:', {
    //     nombres, apellidos, direccion, telefono, email
    // });

    // Limpiar todos los errores previos
    $(".is-invalid").removeClass("is-invalid");
    $(".campo-error").remove();

    let valido = true;
    let errores = [];

    // 1. Validar campos obligatorios SIEMPRE
    const camposObligatorios = [
      { id: "#nombres", valor: nombres, mensaje: "El nombre es obligatorio" },
      {
        id: "#apellidos",
        valor: apellidos,
        mensaje: "El apellido es obligatorio",
      },
      {
        id: "#direccion",
        valor: direccion,
        mensaje: "La dirección es obligatoria",
      },
      {
        id: "#telefono",
        valor: telefono,
        mensaje: "El teléfono es obligatorio",
      },
    ];

    camposObligatorios.forEach((campo) => {
      if (!campo.valor) {
        $(campo.id).addClass("is-invalid");
        mostrarErrorCampo(campo.id, campo.mensaje);
        errores.push(campo.mensaje);
        valido = false;
      }
    });

    // 2. Validar email si se proporcionó
    if (email && !isValidEmail(email)) {
      $("#email").addClass("is-invalid");
      mostrarErrorCampo("#email", "Email inválido");
      errores.push("Email inválido");
      valido = false;
    }

    // 3. Validar longitud mínima de dirección
    if (direccion && direccion.length < 10) {
      $("#direccion").addClass("is-invalid");
      mostrarErrorCampo(
        "#direccion",
        "La dirección es muy corta. Por favor, proporciona una dirección completa.",
      );
      errores.push("Dirección incompleta");
      valido = false;
    }

    // Mostrar resultado de validación
    if (!valido) {
      // console.log("❌ Errores de validación:", errores);

      // Mostrar alerta general con los errores
      let mensajeError = "Por favor corrige los siguientes errores:<br><ul>";
      errores.forEach((error) => {
        mensajeError += `<li>${error}</li>`;
      });
      mensajeError += "</ul>";

      Swal.fire({
        icon: "error",
        title: "Error en el formulario",
        html: mensajeError,
        confirmButtonText: "Entendido",
      });

      // Desplazar al primer error
      const primerError = $(".is-invalid").first();
      if (primerError.length) {
        $("html, body").animate(
          {
            scrollTop: primerError.offset().top - 100,
          },
          500,
        );
      }
    } else {
      // Guardar dirección de envío compuesta
      guardarDireccionEnvio();
      // console.log('✅ Paso 1 validado correctamente');
    }

    return valido;
  }

  function mostrarErrorCampo(selector, mensaje) {
    const $campo = $(selector);
    const $error = $(
      `<small class="text-danger campo-error mt-1 d-block" data-campo="${selector.replace(
        "#",
        "",
      )}">${mensaje}</small>`,
    );

    // Remover error previo si existe
    $(`.campo-error[data-campo="${selector.replace("#", "")}"]`).remove();

    // Insertar después del campo
    $campo.after($error);

    // Si es select, agregar al contenedor padre
    if ($campo.is("select")) {
      $campo.closest(".form-group, .mb-3").append($error);
    }
  }

  function validarPaso2() {
    const metodoSeleccionado =
      $('input[name="metodo_pago"]:checked').val() ||
      $('input[name="metodo_pago_guardado"]:checked').val();

    if (!metodoSeleccionado) {
      mostrarError("Por favor selecciona un método de pago");
      return false;
    }

    // Validar formularios específicos según el método de pago
    let valido = true;
    if ($("#metodo_tarjeta").is(":checked") && !validarFormularioTarjeta()) {
      valido = false;
    }
    if ($("#metodo_paypal").is(":checked") && !validarFormularioPayPal()) {
      valido = false;
    }
    if (
      $("#metodo_transferencia").is(":checked") &&
      !validarFormularioTransferencia()
    ) {
      valido = false;
    }

    if (valido) {
      // Guardar datos del método de pago
      guardarDatosPago();
    }

    return valido;
  }

  function validarFormularioTarjeta() {
    const required = [
      "tarjeta_titular",
      "tarjeta_numero",
      "tarjeta_vencimiento",
      "tarjeta_cvv",
    ];
    let valido = true;

    // Limpiar errores
    required.forEach((name) => {
      $(`input[name="${name}"]`).removeClass("is-invalid");
    });

    // Validar campos requeridos
    required.forEach((name) => {
      const $input = $(`input[name="${name}"]`);
      const valor = $input.val();
      if (!valor || !valor.toString().trim()) {
        $input.addClass("is-invalid");
        valido = false;
      }
    });

    // Validar formato de número de tarjeta
    const numeroTarjeta = $('input[name="tarjeta_numero"]')
      .val()
      .replace(/\s/g, "");
    if (numeroTarjeta.length < 13 || numeroTarjeta.length > 19) {
      $('input[name="tarjeta_numero"]').addClass("is-invalid");
      valido = false;
    }

    // Validar fecha de vencimiento (MM/YY)
    const vencimiento = $('input[name="tarjeta_vencimiento"]').val();
    if (vencimiento) {
      const [mes, año] = vencimiento.split("/");
      if (!mes || !año || mes.length !== 2 || año.length !== 2) {
        $('input[name="tarjeta_vencimiento"]').addClass("is-invalid");
        valido = false;
      }
    }

    // Validar CVV (3-4 dígitos)
    const cvv = $('input[name="tarjeta_cvv"]').val();
    if (cvv && (cvv.length < 3 || cvv.length > 4)) {
      $('input[name="tarjeta_cvv"]').addClass("is-invalid");
      valido = false;
    }

    if (!valido) {
      mostrarError(
        "Por favor completa correctamente todos los campos de la tarjeta",
      );
    }

    return valido;
  }

  function validarFormularioPayPal() {
    const $emailInput = $('input[name="paypal_email"]');
    $emailInput.removeClass("is-invalid");

    const email = $emailInput.val();
    if (!email || !email.trim()) {
      $emailInput.addClass("is-invalid");
      mostrarError("Por favor ingresa tu email de PayPal");
      return false;
    }

    if (!isValidEmail(email)) {
      $emailInput.addClass("is-invalid");
      mostrarError("Por favor ingresa un email válido de PayPal");
      return false;
    }

    return true;
  }

  function validarFormularioTransferencia() {
    const required = ["transferencia_banco", "transferencia_cuenta"];
    let valido = true;

    // Limpiar errores
    required.forEach((name) => {
      $(`input[name="${name}"]`).removeClass("is-invalid");
    });

    // Validar campos requeridos
    required.forEach((name) => {
      const $input = $(`input[name="${name}"]`);
      const valor = $input.val();
      if (!valor || !valor.toString().trim()) {
        $input.addClass("is-invalid");
        valido = false;
      }
    });

    if (!valido) {
      mostrarError("Por favor completa todos los campos de transferencia");
    }

    return valido;
  }

  window.seleccionarMetodoPago = function (tipo) {
    // console.log("Seleccionando método de pago:", tipo);

    // Deseleccionar todos los métodos
    $(".payment-method").removeClass("selected");
    $('input[name="metodo_pago"]').prop("checked", false);
    $('input[name="metodo_pago_guardado"]').prop("checked", false);
    $(".payment-form").hide();

    // Seleccionar el método clickeado
    $(`#metodo_${tipo}`)
      .prop("checked", true)
      .closest(".payment-method")
      .addClass("selected");
    $(`#form-${tipo}`).show();
  };

  window.seleccionarMetodoGuardado = function (id, tipo) {
    // console.log("Seleccionando método guardado:", id, tipo);

    // Deseleccionar todos los métodos nuevos
    $(".payment-method").removeClass("selected");
    $('input[name="metodo_pago"]').prop("checked", false);
    $('input[name="metodo_pago_guardado"]').prop("checked", false);
    $(".payment-form").hide();

    // Seleccionar el método guardado
    $(`#metodo_guardado_${id}`)
      .prop("checked", true)
      .closest(".payment-method")
      .addClass("selected");

    // Guardar datos del método seleccionado
    window.metodoPagoSeleccionado = { id, tipo };
  };

  window.seleccionarDireccionGuardada = function (direccionCompleta) {
    // console.log("Dirección seleccionada:", direccionCompleta);
    window.direccionEnvioCompleta = direccionCompleta;

    // Poner la dirección completa en el textarea
    $("#direccion").val(direccionCompleta);

    // También actualizar automáticamente otros campos si están en la dirección
    // (opcional, dependiendo de tu estructura de datos)
    const partes = direccionCompleta.split(", ");
    if (partes.length >= 1) {
      // Opcional: Si quieres extraer componentes específicos
      // Ejemplo: Si la dirección tiene formato "Calle 123, Municipio, Provincia"
      // $('#direccion').val(partes[0]); // Esto ya no es necesario
      // Si tienes campos separados para municipio y provincia (aunque no los uses en checkout.php)
      // y quieres llenarlos automáticamente:
      // if (partes.length >= 2) {
      //     $('#municipio').val(partes[1]);
      // }
      // if (partes.length >= 3) {
      //     $('#provincia').val(partes[2]);
      // }
    }
  };

  function guardarDireccionEnvio() {
    const nombres = $("#nombres").val()?.trim() || "";
    const apellidos = $("#apellidos").val()?.trim() || "";
    const direccion = $("#direccion").val()?.trim() || "";
    const telefono = $("#telefono").val()?.trim() || "";
    const email = $("#email").val()?.trim() || "";
    const instrucciones = $("#instrucciones").val()?.trim() || "";

    // Construir dirección compuesta
    let direccionCompleta = `${nombres} ${apellidos}`;

    if (direccion) {
      direccionCompleta += `, ${direccion}`;
    }

    // Agregar contacto
    direccionCompleta += `. Tel: ${telefono}`;
    if (email) {
      direccionCompleta += `, Email: ${email}`;
    }
    if (instrucciones) {
      direccionCompleta += `. Instrucciones: ${instrucciones}`;
    }

    window.direccionEnvioCompleta = direccionCompleta;
    // console.log(
    //   "📍 Dirección de envío guardada:",
    //   window.direccionEnvioCompleta,
    // );
  }

  function guardarDatosPago() {
    const metodoSeleccionado =
      $('input[name="metodo_pago"]:checked').val() ||
      $('input[name="metodo_pago_guardado"]:checked').val();

    window.metodoPagoSeleccionado = metodoSeleccionado;
    window.datosPago = {};

    if (metodoSeleccionado === "tarjeta") {
      window.datosPago = {
        tipo: "tarjeta",
        titular: $('input[name="tarjeta_titular"]').val(),
        numero: $('input[name="tarjeta_numero"]').val().replace(/\s/g, ""),
        fecha_vencimiento: $('input[name="tarjeta_vencimiento"]').val(),
        cvv: $('input[name="tarjeta_cvv"]').val(),
      };
    } else if (metodoSeleccionado === "paypal") {
      window.datosPago = {
        tipo: "paypal",
        paypal_email: $('input[name="paypal_email"]').val(),
      };
    } else if (metodoSeleccionado === "transferencia") {
      window.datosPago = {
        tipo: "transferencia",
        banco: $('input[name="transferencia_banco"]').val(),
        numero_cuenta: $('input[name="transferencia_cuenta"]').val(),
      };
    } else if (metodoSeleccionado && !isNaN(parseInt(metodoSeleccionado))) {
      // Es un método guardado (tiene ID numérico)
      window.datosPago = {
        tipo: "guardado",
        id: metodoSeleccionado,
      };
    }

    // console.log("💳 Datos de pago guardados:", window.datosPago);
  }

  function actualizarResumenFinal() {
    // console.log("📋 actualizarResumenFinal() - INICIANDO");

    // DEBUG: Verificar todas las variables globales
    // console.log("🔍 VARIABLES GLOBALES ACTUALES:", {
    //   checkoutSubtotal: window.checkoutSubtotal,
    //   checkoutEnvio: window.checkoutEnvio,
    //   checkoutDescuento: window.checkoutDescuento, // ¡USA window.checkoutDescuento!
    //   checkoutTotal: window.checkoutTotal,
    //   simboloMonedaCheckout: window.simboloMonedaCheckout,
    //   monedaCheckout: window.monedaCheckout,
    // });

    // 1. Obtener valores de las variables GLOBALES, no locales
    const subtotal = window.checkoutSubtotal || 0;
    const envio = window.checkoutEnvio || 0;
    const descuento = window.checkoutDescuento || 0; // ¡CORREGIDO! Usar window.
    const total = window.checkoutTotal || 0;
    const simbolo = window.simboloMonedaCheckout || "$";

    // console.log("🧮 VALORES PARA MOSTRAR:", {
    //   subtotal: subtotal,
    //   envio: envio,
    //   descuento: descuento, // ¡AHORA SÍ DEFINIDO!
    //   total: total,
    //   simbolo: simbolo,
    // });

    // 2. Crear los textos para mostrar
    const subtotalTexto = `${simbolo} ${subtotal.toFixed(2)}`;
    const envioTexto = `${simbolo} ${envio.toFixed(2)}`;
    const descuentoTexto = `${simbolo} ${descuento.toFixed(2)}`; // ¡AHORA SÍ DEFINIDO!
    const totalTexto = `${simbolo} ${total.toFixed(2)}`;

    // console.log("📝 TEXTOS A INSERTAR:", {
    //   subtotalTexto: subtotalTexto,
    //   envioTexto: envioTexto,
    //   descuentoTexto: descuentoTexto,
    //   totalTexto: totalTexto,
    // });

    // 3. Actualizar totales en el resumen final
    $("#resumen-subtotal").text(subtotalTexto);
    $("#resumen-envio-costo").text(envioTexto);
    $("#resumen-descuento").text(descuentoTexto); // ¡AHORA SÍ DEFINIDO!
    $("#resumen-total").text(totalTexto);

    // 4. Actualizar resumen de envío (si existe)
    if ($("#resumen-envio").length) {
      $("#resumen-envio").html(`
            <p class="mb-1"><strong>${$("#nombres").val() || ""} ${$("#apellidos").val() || ""}</strong></p>
            <p class="mb-1">${$("#direccion").val() || ""}</p>
            <p class="mb-1">Tel: ${$("#telefono").val() || ""}</p>
            ${$("#email").val() ? `<p class="mb-1">Email: ${$("#email").val()}</p>` : ""}
            ${$("#instrucciones").val() ? `<p class="mb-1"><em>Instrucciones: ${$("#instrucciones").val()}</em></p>` : ""}
        `);
    }

    // 5. Actualizar resumen de pago (si existe)
    if ($("#resumen-pago").length) {
      let metodoPagoHtml = "";
      if (window.datosPago && window.datosPago.tipo === "tarjeta") {
        const ultimos4 = window.datosPago.numero
          ? window.datosPago.numero.slice(-4)
          : "****";
        metodoPagoHtml = `<p class="mb-0"><i class="far fa-credit-card mr-2"></i>Tarjeta terminada en ${ultimos4}</p>`;
      } else if (window.datosPago && window.datosPago.tipo === "paypal") {
        metodoPagoHtml = `<p class="mb-0"><i class="fab fa-paypal mr-2"></i>PayPal: ${window.datosPago.paypal_email || "No especificado"}</p>`;
      } else if (
        window.datosPago &&
        window.datosPago.tipo === "transferencia"
      ) {
        metodoPagoHtml = `<p class="mb-0"><i class="fas fa-university mr-2"></i>Transferencia: ${window.datosPago.banco || "No especificado"}</p>`;
      } else if (window.datosPago && window.datosPago.tipo === "guardado") {
        metodoPagoHtml = `<p class="mb-0"><i class="fas fa-credit-card mr-2"></i>Método de pago guardado</p>`;
      } else {
        metodoPagoHtml = `<p class="mb-0 text-warning"><i class="fas fa-exclamation-triangle mr-2"></i>Método no especificado</p>`;
      }
      $("#resumen-pago").html(metodoPagoHtml);
    }

    // DEBUG: Verificar después de actualizar
    // console.log("✅ DESPUÉS DE ACTUALIZAR:", {
    //   subtotalElement: $("#resumen-subtotal").text(),
    //   envioElement: $("#resumen-envio-costo").text(),
    //   descuentoElement: $("#resumen-descuento").text(),
    //   totalElement: $("#resumen-total").text(),
    // });

    // console.log("✅ Resumen final actualizado COMPLETAMENTE");
  }

  // Función para limpiar transacciones antes de intentar el pago
  async function limpiarTransaccionesAntesDePago() {
    try {
      const response = await $.post("../Controllers/PagoController.php", {
        funcion: "limpiar_transacciones",
      });
      return response.success;
    } catch (error) {
      // console.log("⚠️ No se pudo limpiar transacciones:", error);
      return false;
    }
  }

  function debugCheckoutItems() {
    // console.log("🔍 DEBUG: Estructura de checkoutItems:");

    if (window.checkoutItems && window.checkoutItems.length > 0) {
      // console.log("Primer item completo:", window.checkoutItems[0]);
      // console.log("Todas las propiedades del primer item:");

      // Mostrar todas las propiedades del primer item
      const firstItem = window.checkoutItems[0];
      for (const key in firstItem) {
        // console.log(`  ${key}:`, firstItem[key]);
      }

      // Mostrar todos los IDs posibles
      // console.log("📋 Todos los IDs posibles:");
      window.checkoutItems.forEach((item, index) => {
        // console.log(`Item ${index}:`, {
        //   id: item.id,
        //   id_carrito: item.id_carrito,
        //   carrito_id: item.carrito_id,
        //   item_id: item.item_id,
        //   id_producto: item.id_producto,
        //   id_producto_tienda: item.id_producto_tienda,
        // });
      });
    } else {
      console.warn("⚠️ checkoutItems está vacío o no definido");
    }
  }

  async function procesarPago() {
    try {
      // console.log("🔄 Iniciando procesamiento de pago...");

      // 1. Obtener método de pago seleccionado
      const metodoPagoSeleccionado = obtenerMetodoPagoSeleccionado();
      const direccionEnvio = $("#direccion").val();

      // console.log("📊 ANTES de cualquier conversión:", {
      //   metodoPago: metodoPagoSeleccionado,
      //   monedaActual: window.monedaCheckout,
      //   totalMostrado: `${window.simboloMonedaCheckout} ${window.checkoutTotal.toFixed(2)}`,
      //   totalOriginalCUP: window.checkoutTotalOriginalCUP,
      //   tasa: window.tasaCambioCheckout,
      // });

      // 2. Validar dirección
      if (!direccionEnvio || direccionEnvio.trim() === "") {
        throw new Error("Por favor, ingresa una dirección de envío válida");
      }

      // 3. Determinar si el método requiere CUP
      const metodosQueRequierenCUP = [
        "transfermovil",
        "transfermovíl",
        "transfermóvil",
        "transferencia",
        "efectivo",
        "cash",
        "contraentrega",
      ];

      const metodoLower = metodoPagoSeleccionado.toLowerCase().trim();
      let requiereCUP = false;

      for (const metodoCUP of metodosQueRequierenCUP) {
        if (
          metodoLower.includes(metodoCUP) ||
          metodoCUP.includes(metodoLower)
        ) {
          requiereCUP = true;
          // console.log(`🎯 Método "${metodoPagoSeleccionado}" requiere CUP`);
          break;
        }
      }

      // 4. Manejar conversión a CUP si es necesario
      let conversionRealizada = null;

      if (requiereCUP && window.monedaCheckout !== "CUP") {
        // console.log("💰 Método requiere CUP, realizando conversión...");

        // Guardar estado original
        window.monedaOriginal = window.monedaCheckout;
        window.simboloOriginal = window.simboloMonedaCheckout;
        window.tasaOriginal = window.tasaCambioCheckout;
        window.subtotalOriginal = window.checkoutSubtotal;
        window.envioOriginal = window.checkoutEnvio;
        window.descuentoOriginal = window.checkoutDescuento; // ¡AGREGAR!
        window.totalOriginal = window.checkoutTotal;

        // Usar valores originales en CUP
        window.checkoutSubtotal = window.checkoutSubtotalOriginalCUP;
        window.checkoutEnvio = window.checkoutEnvioOriginalCUP;
        window.checkoutTotal = window.checkoutTotalOriginalCUP;

        // ¡CONVERTIR DESCUENTO TAMBIÉN!
        if (window.checkoutDescuentoOriginalCUP !== undefined) {
          window.checkoutDescuento = window.checkoutDescuentoOriginalCUP;
        } else {
          // Calcular si no existe
          window.checkoutDescuento =
            window.descuentoOriginal * window.tasaOriginal;
        }

        // Actualizar configuración a CUP
        window.monedaCheckout = "CUP";
        window.simboloMonedaCheckout = "CUP";
        window.tasaCambioCheckout = 1;

        conversionRealizada = {
          convertido: true,
          monedaAnterior: window.monedaOriginal,
          descuentoAnterior: window.descuentoOriginal, // ¡AGREGAR!
          descuentoNuevo: window.checkoutDescuento, // ¡AGREGAR!
          montoAnterior: window.totalOriginal,
          montoNuevo: window.checkoutTotal,
        };

        // console.log("✅ Convertido a CUP:", conversionRealizada);

        // Mostrar notificación al usuario
        Swal.fire({
          icon: "info",
          title: "Pago en moneda nacional",
          html: `Tu pago con <strong>${metodoPagoSeleccionado}</strong> requiere pesos cubanos (CUP).<br>
                      <strong>Total a pagar: $${window.checkoutTotal.toFixed(2)} CUP</strong><br>
                      <small class="text-muted">
                          Equivalente a ${conversionRealizada.montoAnterior.toFixed(2)} ${conversionRealizada.monedaAnterior}
                      </small>`,
          timer: 4000,
          showConfirmButton: false,
          position: "top-end",
          toast: true,
        });

        // Actualizar UI
        actualizarResumenLateral();
      }

      // 5. Obtener IDs de los items del carrito
      // console.log("🔍 checkoutItems:", window.checkoutItems);

      const itemsSeleccionados = window.checkoutItems
        .map((item) => {
          if (typeof item === "number") {
            return item;
          }
          if (typeof item === "string" && !isNaN(item)) {
            return parseInt(item);
          }
          if (typeof item === "object" && item !== null) {
            return (
              item.id_carrito || item.id || item.carrito_id || item.item_id
            );
          }
          return null;
        })
        .filter(
          (id) => id !== undefined && id !== null && id !== "" && !isNaN(id),
        );

      // console.log("🛒 IDs de productos a procesar:", itemsSeleccionados);

      if (itemsSeleccionados.length === 0) {
        // Restaurar moneda si hubo error
        if (conversionRealizada?.convertido) {
          restaurarMonedaOriginal();
        }
        throw new Error("No se pudieron obtener los IDs de productos");
      }

      // 6. Mostrar estado de carga
      $("#btn-procesar-pago").prop("disabled", true).addClass("loading");

      // 7. Preparar datos para enviar (ENVIAR EN LA MONEDA CORRECTA)
      const datosEnvio = {
        funcion: "procesar_pago",
        metodo_pago: metodoPagoSeleccionado,
        direccion_envio: direccionEnvio,
        items_seleccionados: JSON.stringify(itemsSeleccionados),

        // Enviar valores en la moneda ACTUAL (CUP si se convirtió, otra moneda si no)
        subtotal: window.checkoutSubtotal,
        envio: window.checkoutEnvio,
        total: window.checkoutTotal,
        moneda: window.monedaCheckout,
        simbolo_moneda: window.simboloMonedaCheckout,
        tasa_cambio: window.tasaCambioCheckout,

        // También enviar valores originales en CUP para referencia
        subtotal_cup: window.checkoutSubtotalOriginalCUP,
        envio_cup: window.checkoutEnvioOriginalCUP,
        total_cup: window.checkoutTotalOriginalCUP,
      };

      // console.log("📤 Enviando datos al servidor:", datosEnvio);

      // 8. Enviar datos al controlador
      const response = await $.post(
        "../Controllers/PagoController.php",
        datosEnvio,
      );

      const data =
        typeof response === "string" ? JSON.parse(response) : response;

      // console.log("📦 Respuesta del servidor:", data);

      // 9. MANEJAR RESPUESTA
      if (data.success) {
        if (data.tipo_respuesta === "instrucciones_pago_manual") {
          // console.log("🎯 Mostrando instrucciones de Transfermóvil");

          // Guardar el número de tarjeta real globalmente
          window.numeroTarjetaActual = data.instrucciones.numero_tarjeta;

          // Inicializar estado para nueva orden
          inicializarEstadoNuevaOrden();

          // Mostrar instrucciones CON LOS VALORES EN CUP
          await mostrarInstruccionesTransfermovil(data);
        } else {
          // console.log("✅ Pago automático exitoso");

          // Restaurar moneda original si se había cambiado
          if (conversionRealizada?.convertido) {
            restaurarMonedaOriginal();
          }

          await mostrarConfirmacionPagoExitoso(data);
        }
      } else {
        // Restaurar moneda si hubo error
        if (conversionRealizada?.convertido) {
          restaurarMonedaOriginal();
        }
        throw new Error(data.message || "Error al procesar el pago");
      }
    } catch (error) {
      console.error("❌ Error procesando pago:", error);

      // Restaurar moneda si hubo error
      if (window.monedaOriginal && window.monedaOriginal !== "CUP") {
        restaurarMonedaOriginal();

        Swal.fire({
          icon: "info",
          title: "Moneda restaurada",
          text: "Los precios han sido restaurados a la moneda original.",
          timer: 2000,
          showConfirmButton: false,
          position: "top-end",
          toast: true,
        });
      }

      Swal.fire({
        icon: "error",
        title: "Error en el pago",
        text: error.message || "Ocurrió un error al procesar tu pago.",
        confirmButtonText: "Entendido",
      });
    } finally {
      $("#btn-procesar-pago").prop("disabled", false).removeClass("loading");
    }
  }

  function obtenerMetodoPagoSeleccionado() {
    const metodoSeleccionado = $('input[name="metodo_pago"]:checked').val();

    if (!metodoSeleccionado) {
      Swal.fire({
        icon: "warning",
        title: "Método de pago requerido",
        text: "Por favor, selecciona un método de pago para continuar.",
        confirmButtonText: "Entendido",
      });
      throw new Error("Método de pago no seleccionado");
    }

    // console.log("💳 Método de pago seleccionado:", metodoSeleccionado);
    return metodoSeleccionado;
  }

  // Función para convertir precios a CUP si es necesario
  function convertirPreciosACupSiEsNecesario(metodoPago) {
    // console.log("💰 Verificando conversión a CUP para método:", metodoPago);

    // Lista de métodos que requieren pago en CUP
    const metodosEnCup = [
      "transfermovil",
      "transferencia",
      "efectivo",
      "tarjeta_credito_local",
    ];

    // Verificar si el método seleccionado requiere CUP
    const requiereCUP = metodosEnCup.some(
      (metodo) =>
        metodoPago.toLowerCase().includes(metodo) ||
        metodo.toLowerCase().includes(metodoPago),
    );

    // console.log("🔍 Método requiere CUP?", {
    //   metodoPago,
    //   requiereCUP,
    //   metodosEnCup,
    // });

    if (requiereCUP && window.monedaCheckout !== "CUP") {
      // console.log("🔄 Convirtiendo precios a CUP para método:", metodoPago);

      // Guardar los valores originales en la moneda actual
      window.checkoutSubtotalEnMonedaOriginal = window.checkoutSubtotal;
      window.checkoutEnvioEnMonedaOriginal = window.checkoutEnvio;
      window.checkoutTotalEnMonedaOriginal = window.checkoutTotal;

      // Convertir a CUP usando la tasa de cambio inversa
      window.checkoutSubtotal = window.checkoutSubtotalOriginal; // Ya está en CUP
      window.checkoutEnvio = window.checkoutEnvioOriginal; // Ya está en CUP
      window.checkoutTotal = window.checkoutTotalOriginal; // Ya está en CUP

      // Actualizar símbolo y moneda
      window.simboloMonedaCheckout = "$";
      window.monedaCheckout = "CUP";

      // console.log("✅ Precios convertidos a CUP:", {
      //   subtotal: window.checkoutSubtotal,
      //   envio: window.checkoutEnvio,
      //   total: window.checkoutTotal,
      //   simbolo: window.simboloMonedaCheckout,
      // });

      return true; // Se realizó conversión
    }

    // console.log("ℹ️ No se requiere conversión a CUP");
    return false; // No se realizó conversión
  }

  // ================= FUNCIONES PARA VALIDACIÓN DE BENEFICIARIO DINÁMICO =================

  // Función para validar que el beneficiario coincida con la tarjeta REAL de la base de datos
  // Función para validar que el beneficiario coincida con la tarjeta REAL de la base de datos
  function validarBeneficiario(beneficiarioTexto, numeroTarjetaEsperado) {
    // console.log("🔐 Validando beneficiario:", {
    //   beneficiarioTexto,
    //   numeroTarjetaEsperado,
    //   tipo: typeof numeroTarjetaEsperado,
    // });

    if (
      !beneficiarioTexto ||
      numeroTarjetaEsperado === undefined ||
      numeroTarjetaEsperado === null
    ) {
      console.error("❌ Datos faltantes para validación");
      return false;
    }

    // Asegurar que ambos sean strings
    const beneficiarioStr = String(beneficiarioTexto || "")
      .replace(/[\s\-\.]/g, "")
      .toUpperCase();
    const tarjetaEsperadaStr = String(numeroTarjetaEsperado || "").replace(
      /[\s\-\.]/g,
      "",
    );

    // console.log("📝 Strings para comparar:", {
    //   beneficiario: beneficiarioStr,
    //   tarjetaEsperada: tarjetaEsperadaStr,
    //   longitudBeneficiario: beneficiarioStr.length,
    //   longitudTarjeta: tarjetaEsperadaStr.length,
    // });

    // CASO ESPECÍFICO: Beneficiario enmascarado vs tarjeta completa
    // Ej: "9238XXXXXXXX5406" vs "9238959871235406"

    // Si el beneficiario tiene formato enmascarado (contiene X)
    if (beneficiarioStr.includes("X") || beneficiarioStr.includes("*")) {
      // console.log("🔍 Formato enmascarado detectado");

      // Verificar longitudes
      if (beneficiarioStr.length !== tarjetaEsperadaStr.length) {
        // console.log(
        //   `❌ Longitudes diferentes: ${beneficiarioStr.length} vs ${tarjetaEsperadaStr.length}`,
        // );
        return false;
      }

      // Comparar dígito por dígito
      let coincidencias = 0;
      let totalComparaciones = 0;

      for (let i = 0; i < beneficiarioStr.length; i++) {
        const charBenef = beneficiarioStr[i];
        const charTarjeta = tarjetaEsperadaStr[i];

        // Solo comparar donde el beneficiario tiene dígitos (no X)
        if (charBenef !== "X" && charBenef !== "*") {
          totalComparaciones++;
          if (charBenef === charTarjeta) {
            coincidencias++;
          } else {
            // console.log(
            //   `❌ Dígito ${i} no coincide: ${charBenef} vs ${charTarjeta}`,
            // );
          }
        }
      }

      const esValido =
        totalComparaciones > 0 && coincidencias === totalComparaciones;

      // console.log(`🔍 Resultado validación enmascarada:`, {
      //   totalComparaciones,
      //   coincidencias,
      //   esValido,
      //   beneficiario: beneficiarioStr,
      //   tarjeta: tarjetaEsperadaStr,
      // });

      return esValido;
    }

    // CASO: Ambos son números sin enmascarar
    if (/^\d+$/.test(beneficiarioStr) && /^\d+$/.test(tarjetaEsperadaStr)) {
      const esValido =
        beneficiarioStr === tarjetaEsperadaStr ||
        tarjetaEsperadaStr.includes(beneficiarioStr) ||
        beneficiarioStr.includes(tarjetaEsperadaStr);

      // console.log("🔍 Comparando números:", esValido);
      return esValido;
    }

    // Última opción: comparación simple
    const esValido =
      tarjetaEsperadaStr.includes(beneficiarioStr) ||
      beneficiarioStr.includes(tarjetaEsperadaStr);

    // console.log("🔍 Comparación simple:", esValido);
    return esValido;
  }

  // Función para mostrar error cuando el monto no coincide
  function mostrarErrorMonto(
    montoEncontrado,
    montoEsperado,
    formatoOriginal = null,
  ) {
    const simbolo = window.simboloMonedaCheckout || "$";
    const moneda = window.monedaCheckout || "CUP";

    // Formatear montos para mostrar
    const montoEncontradoFormateado = normalizarFormatoMonto(
      montoEncontrado,
      false,
    );
    const montoEsperadoFormateado = normalizarFormatoMonto(
      montoEsperado,
      false,
    );
    const diferencia = Math.abs(montoEncontrado - montoEsperado);
    const diferenciaFormateada = normalizarFormatoMonto(diferencia, false);

    Swal.fire({
      icon: "error",
      title: "❌ Monto incorrecto",
      html: `
            <div class="text-left">
                <p><strong>Error en el monto de transferencia:</strong></p>
                
                <div class="alert alert-danger mt-3">
                    <h6><i class="fas fa-money-bill-wave mr-2"></i>Validación de monto falló:</h6>
                    <table class="table table-sm mt-2">
                        <tr>
                            <td><strong>Monto en el texto:</strong></td>
                            <td class="text-danger">${simbolo} ${montoEncontradoFormateado} ${moneda}</td>
                        </tr>
                        ${
                          formatoOriginal
                            ? `
                        <tr>
                            <td><small>Formato original:</small></td>
                            <td><small class="text-muted">${formatoOriginal}</small></td>
                        </tr>
                        `
                            : ""
                        }
                        <tr>
                            <td><strong>Monto esperado:</strong></td>
                            <td class="text-success">${simbolo} ${montoEsperadoFormateado} ${moneda}</td>
                        </tr>
                        <tr>
                            <td><strong>Diferencia:</strong></td>
                            <td class="text-warning">${simbolo} ${diferenciaFormateada}</td>
                        </tr>
                    </table>
                </div>
                
                <div class="alert alert-info">
                    <h6><i class="fas fa-info-circle mr-2"></i>Formatos aceptados:</h6>
                    <div class="row">
                        <div class="col-6">
                            <small class="d-block">• 250.00 (punto decimal)</small>
                            <small class="d-block">• 250,00 (coma decimal)</small>
                        </div>
                        <div class="col-6">
                            <small class="d-block">• 250 (sin decimales)</small>
                            <small class="d-block">• 1.250,50 (miles con coma)</small>
                        </div>
                    </div>
                </div>
                
                <p class="mt-3"><strong>¿Qué puede estar mal?</strong></p>
                <ul class="text-left pl-3">
                    <li>Transferiste un monto diferente al solicitado</li>
                    <li>El texto copiado es de otra transferencia</li>
                    <li>Hubo un error al copiar/pegar el texto</li>
                    <li>Formato del monto no reconocido</li>
                </ul>
            </div>
        `,
      // 🔥 ELIMINAR OPCIÓN DE FORMULARIO MANUAL
      showCancelButton: true,
      cancelButtonText: "Pegar nuevo texto",
      confirmButtonText: "Entendido",
      confirmButtonColor: "#dc3545",
      width: "650px",
    }).then((result) => {
      if (result.isConfirmed || result.dismiss === Swal.DismissReason.cancel) {
        // Permitir pegar nuevo texto
        const $textarea = $("#textoTransferencia");
        $textarea.prop("disabled", false).val("");
        $textarea.focus();
      }
    });
  }

  // Función para mostrar error cuando el beneficiario no coincide
  function mostrarErrorBeneficiario(numeroTarjetaReal) {
    const numeroEnmascarado =
      formatearNumeroTarjetaEnmascarado(numeroTarjetaReal);

    Swal.fire({
      icon: "error",
      title: "❌ Beneficiario incorrecto",
      html: `
            <div class="text-left">
                <p><strong>Error de seguridad:</strong> El beneficiario no coincide con la tarjeta activa actual.</p>
                
                <div class="alert alert-danger mt-3">
                    <h6><i class="fas fa-shield-alt mr-2"></i>Validación requerida:</h6>
                    <p class="mb-0">
                        El campo <strong>"Beneficiario"</strong> en el texto debe ser:
                        <br>
                        <code class="d-block mt-2 p-2 bg-dark text-white">${numeroEnmascarado}</code>
                    </p>
                    <small class="text-muted">
                        <i class="fas fa-info-circle mr-1"></i>
                        Esta es la tarjeta activa actual obtenida de la base de datos.
                        Las tarjetas pueden cambiar periódicamente por seguridad.
                    </small>
                </div>
                
                <p class="mt-3"><strong>¿Qué debes hacer?</strong></p>
                <ol class="text-left pl-3">
                    <li>Verifica que la transferencia fue a la tarjeta mostrada arriba</li>
                    <li>Si fue a otra tarjeta, no es válida para esta orden</li>
                    <li>El texto debe incluir: <code>Beneficiario: ${numeroEnmascarado}</code></li>
                    <li>Si tienes dudas, contacta con soporte</li>
                </ol>
                
                <div class="alert alert-warning mt-3">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    <strong>Nota:</strong> Las tarjetas pueden rotar automáticamente. 
                    Usa siempre la tarjeta que se muestra en estas instrucciones.
                </div>
            </div>
        `,
      // 🔥 ELIMINAR OPCIÓN DE FORMULARIO MANUAL
      showCancelButton: true,
      cancelButtonText: "Pegar nuevo texto",
      confirmButtonText: "Entendido",
      confirmButtonColor: "#dc3545",
      width: "600px",
    }).then((result) => {
      if (result.isConfirmed || result.dismiss === Swal.DismissReason.cancel) {
        const $textarea = $("#textoTransferencia");
        $textarea.prop("disabled", false).val("");
        $textarea.focus();
      }
    });
  }

  // Función para formatear número de tarjeta en formato enmascarado
  function formatearNumeroTarjetaEnmascarado(numeroCompleto) {
    if (!numeroCompleto) return "Número no disponible";

    const numeroLimpio = numeroCompleto.toString().replace(/\s+/g, "");

    // Si el número tiene 16 dígitos (formato estándar cubano)
    if (numeroLimpio.length === 16) {
      const primeros4 = numeroLimpio.substring(0, 4);
      const ultimos4 = numeroLimpio.substring(12, 16);
      return `${primeros4}XXXXXXXX${ultimos4}`;
    }

    // Si el número tiene otra longitud, enmascarar la parte central
    if (numeroLimpio.length >= 8) {
      const primeros4 = numeroLimpio.substring(0, 4);
      const ultimos4 = numeroLimpio.substring(numeroLimpio.length - 4);
      const digitosCentrales = numeroLimpio.substring(
        4,
        numeroLimpio.length - 4,
      );
      const enmascarado = "X".repeat(digitosCentrales.length);
      return `${primeros4}${enmascarado}${ultimos4}`;
    }

    // Para números muy cortos, mostrar completo
    return numeroCompleto;
  }

  // Función para mostrar información sobre el formato requerido
  function mostrarInfoFormatoBeneficiario() {
    Swal.fire({
      icon: "info",
      title: "📋 Formato del beneficiario",
      html: `
            <div class="text-left">
                <p><strong>El texto de transferencia debe incluir el beneficiario correcto:</strong></p>
                
                <div class="alert alert-info mt-2">
                    <code class="d-block p-2 bg-dark text-white">Beneficiario: 9200XXXXXXXX5658</code>
                </div>
                
                <p class="mb-1"><strong>Donde:</strong></p>
                <ul class="text-left pl-3">
                    <li><strong>9200</strong> = Primeros 4 dígitos de la tarjeta activa actual</li>
                    <li><strong>XXXXXXXX</strong> = 8 dígitos enmascarados (pueden ser X o *)</li>
                    <li><strong>5658</strong> = Últimos 4 dígitos de la tarjeta activa actual</li>
                </ul>
                
                <p class="mt-3 text-muted small">
                    <i class="fas fa-shield-alt mr-1"></i>
                    Esta validación dinámica asegura que el pago fue realizado a la tarjeta activa actual.
                </p>
            </div>
        `,
      confirmButtonText: "Entendido",
      width: "500px",
    });
  }

  // Función para obtener el número real de tarjeta del modal
  function obtenerNumeroTarjetaReal() {
    // Primero intentar obtener del input #numeroTarjeta
    const $numeroTarjetaInput = $("#numeroTarjeta");

    if ($numeroTarjetaInput.length) {
      // Intentar obtener del atributo data-numero-real
      const numeroReal = $numeroTarjetaInput.data("numero-real");
      if (numeroReal) {
        // console.log("✅ Número real obtenido de data attribute:", numeroReal);
        return numeroReal;
      }

      // Si no existe, usar el valor del input
      const valorInput = $numeroTarjetaInput.val();
      // console.log("ℹ️ Número obtenido del valor del input:", valorInput);
      return valorInput;
    }

    // Intentar obtener de la variable global
    if (window.numeroTarjetaActual) {
      // console.log(
      //   "ℹ️ Número obtenido de variable global:",
      //   window.numeroTarjetaActual,
      // );
      return window.numeroTarjetaActual;
    }

    // Fallback: obtener del data del modal
    const numeroDelModal = $("#modalTransfermovil").data("numero-tarjeta-real");
    if (numeroDelModal) {
      // console.log("ℹ️ Número obtenido del data del modal:", numeroDelModal);
      return numeroDelModal;
    }

    console.error("❌ No se pudo obtener el número de tarjeta");
    return null;
  }

  // ================= SISTEMA DE BLOQUEO AUTOMÁTICO =================

  // Función para detectar cuando se pega texto y bloquear automáticamente
  function configurarBloqueoAutomatico() {
    const $textarea = $("#textoTransferencia");

    console.log("🔄 Configurando bloqueo automático, estado inicial:", {
      deshabilitado: $textarea.prop("disabled"),
      readonly: $textarea.prop("readonly"),
      valor: $textarea.val(),
    });

    // Verificar si es una nueva orden
    if (window.esNuevaOrden) {
      // console.log("🆕 Configurando bloqueo automático para NUEVA orden");
      $textarea.prop("disabled", false);
      $textarea.prop("readonly", false);
      $textarea.val("");
    }

    $textarea.on("paste", function (e) {
      setTimeout(async () => {
        const textoPegado = $(this).val().trim();
        if (textoPegado && textoPegado.length > 10) {
          const numeroTarjetaReal = obtenerNumeroTarjetaReal();

          if (!numeroTarjetaReal) {
            console.error("❌ No se pudo obtener número de tarjeta");
            return;
          }

          // 🔥 CRÍTICO: Verificar si ya hay un SweetAlert visible
          if (Swal.isVisible()) {
            // console.log("⚠️ Ya hay un error mostrado, no procesar bloqueo");
            return; // ¡SALIR INMEDIATAMENTE!
          }

          bloquearTextareaPermanentemente("textoTransferencia");

          const datos = await parsearTextoTransferencia(
            textoPegado,
            numeroTarjetaReal,
          );

          // 🔥 VALIDAR FECHA PRIMERO (antes de monto)
          if (datos.fecha) {
            try {
              await validarFechaTransferencia(datos.fecha);
            } catch (error) {
              // 🔥 MOSTRAR ERROR DE FECHA Y SALIR
              mostrarErrorFecha(error.message);
              return; // ¡NO CONTINUAR!
            }
          } else {
            // Si no hay fecha en el texto, también es error
            mostrarErrorValidacion(
              "Fecha no encontrada",
              "El texto no contiene una fecha válida. Solo se aceptan transferencias realizadas HOY.",
            );
            return; // ¡NO CONTINUAR!
          }

          // 🔥 VALIDAR MONTO SEGUNDO
          const montoEsperado = obtenerMontoEsperado();
          if (montoEsperado && datos.montoNumerico) {
            const diferencia = Math.abs(datos.montoNumerico - montoEsperado);
            if (diferencia > 0.01) {
              // 🔥 MOSTRAR ERROR DE MONTO Y SALIR
              mostrarErrorMonto(datos.montoNumerico, montoEsperado);
              return; // ¡NO CONTINUAR!
            }
          }

          // 🔥 VALIDAR BENEFICIARIO TERCERO
          if (
            datos.beneficiario &&
            !validarBeneficiario(datos.beneficiario, numeroTarjetaReal)
          ) {
            mostrarErrorBeneficiario(numeroTarjetaReal);
            return; // ¡NO CONTINUAR!
          }

          // 🔥 SOLO si todo está correcto, mostrar resultado y mensaje de bloqueo
          mostrarResultadoParseoConMonto(datos, montoEsperado);
          mostrarMensajeBloqueoAutomatico();
          window.esNuevaOrden = false;
        }
      }, 100);
    });

    $textarea.on("input", function () {
      const texto = $(this).val().trim();
      if (texto.length > 50 && !$(this).prop("disabled")) {
        setTimeout(async () => {
          if ($(this).val().trim().length > 50) {
            // 🔥 CRÍTICO: Verificar si ya hay un SweetAlert visible
            if (Swal.isVisible()) {
              // console.log("⚠️ Ya hay un error mostrado, no procesar input");
              return;
            }

            bloquearTextareaPermanentemente("textoTransferencia");
            const numeroTarjetaReal = obtenerNumeroTarjetaReal();

            const datos = await parsearTextoTransferencia(
              $(this).val(),
              numeroTarjetaReal,
            );

            // 🔥 VALIDAR FECHA PRIMERO
            if (datos.fecha) {
              try {
                await validarFechaTransferencia(datos.fecha);
              } catch (error) {
                mostrarErrorFecha(error.message);
                return; // ¡NO CONTINUAR!
              }
            } else {
              mostrarErrorValidacion(
                "Fecha no encontrada",
                "El texto no contiene una fecha válida. Solo se aceptan transferencias realizadas HOY.",
              );
              return;
            }

            // 🔥 VALIDAR MONTO SEGUNDO
            const montoEsperado = obtenerMontoEsperado();
            if (montoEsperado && datos.montoNumerico) {
              const diferencia = Math.abs(datos.montoNumerico - montoEsperado);
              if (diferencia > 0.01) {
                mostrarErrorMonto(datos.montoNumerico, montoEsperado);
                return; // ¡NO CONTINUAR!
              }
            }

            // 🔥 VALIDAR BENEFICIARIO TERCERO
            if (
              numeroTarjetaReal &&
              (!datos.beneficiario ||
                datos.errores.some((e) => e.includes("beneficiario")))
            ) {
              mostrarErrorBeneficiario(numeroTarjetaReal);
              return; // ¡NO CONTINUAR!
            }

            // 🔥 SOLO si todo está correcto
            mostrarResultadoParseo(datos);
            mostrarMensajeBloqueoAutomatico();
            window.esNuevaOrden = false;
          }
        }, 500);
      }
    });

    // Prevenir edición manual una vez que haya texto
    $textarea.on("keydown", function (e) {
      if ($(this).val().trim().length > 10 && !$(this).prop("disabled")) {
        if (e.key.length === 1 && !e.ctrlKey && !e.metaKey) {
          e.preventDefault();
          mostrarAdvertenciaEdicion();
          return false;
        }
      }
    });

    // 🔥 Añadir un event listener adicional para verificar el estado
    $(document).on("click", "#btnRegistrarPago", function () {
      console.log(
        "🖱️ Botón Registrar mi Pago clickeado, estado del textarea:",
        {
          deshabilitado: $("#textoTransferencia").prop("disabled"),
          readonly: $("#textoTransferencia").prop("readonly"),
          tieneValor: $("#textoTransferencia").val().length > 0,
        },
      );
    });
  }

  function mostrarResultadoParseoConMonto(datos, montoEsperado) {
    // Asegurar que advertencias sea un array
    const advertencias = datos.advertencias || [];

    // Validar monto
    const montoValido =
      montoEsperado && datos.montoNumerico
        ? Math.abs(datos.montoNumerico - montoEsperado) < 0.01
        : false;

    // 🔥 FORZAR BLOQUEO DEL TEXTAREA
    const $textarea = $("#textoTransferencia");
    $textarea.prop("disabled", true);
    $textarea.prop("readonly", true);

    // Determinar tipo de alerta
    let tipoAlerta = "warning";
    let icono = "fa-exclamation-triangle";
    let titulo = "Datos detectados (verificar)";

    if (montoValido && datos.valido) {
      if (advertencias.length === 0) {
        tipoAlerta = "success";
        icono = "fa-check-circle";
        titulo = "Datos detectados correctamente";
      } else {
        tipoAlerta = "info";
        icono = "fa-info-circle";
        titulo = "Datos detectados (con advertencias)";
      }
    }

    const htmlResultado = `
        <div class="alert alert-${tipoAlerta}">
            <h6 class="mb-1><i class="fas ${icono} mr-2"></i>${titulo}</h6>
            <div class="row">
                <div class="col-md-6">
                    <p class="mb-1"><strong>Banco:</strong> <span id="parsedBanco">${datos.banco || "-"}</span></p>
                    <p class="mb-1"><strong>Fecha:</strong> <span id="parsedFecha">${datos.fecha || "-"}</span></p>
                    <p class="mb-1"><strong>Transacción:</strong> <span id="parsedTransaccion">${datos.transaccion || "-"}</span></p>
                </div>
                <div class="col-md-6">
                    <p class="mb-1"><strong>Monto:</strong> 
                        <span id="parsedMonto" class="${montoValido ? "text-success" : "text-danger"}">
                            ${datos.monto || "-"}
                            ${montoValido}
                        </span>
                    </p>
                    <p class="mb-1"><strong>Saldo:</strong> 
                        <span id="parsedSaldo" class="${datos.saldo ? "text-info" : "text-muted"}">
                            ${datos.saldo || "No especificado"}
                            ${datos.saldo ? "" : " <small>(opcional)</small>"}
                        </span>
                    </p>
                    <p class="mb-1"><strong>Beneficiario:</strong> <span id="parsedBeneficiario">${datos.beneficiario || "-"}</span></p>
                </div>
            </div>
            ${
              advertencias.length > 0
                ? `
            <div class="alert alert-warning mt-2">
                <i class="fas fa-exclamation-circle mr-2"></i>
                <strong>Advertencias:</strong>
                <ul class="mb-0 pl-3">
                    ${advertencias.map((adv) => `<li>${adv}</li>`).join("")}
                </ul>
            </div>
            `
                : ""
            }
        </div>
    `;

    $("#resultadoParseo").html(htmlResultado).show();

    // Guardar en localStorage
    localStorage.setItem("textarea_transfermovil_bloqueado", "true");
    localStorage.setItem(
      "textarea_transfermovil_texto",
      $("#textoTransferencia").val(),
    );

    return montoValido && datos.valido;
  }

  // Función para mostrar resultado del parseo automático
  function mostrarResultadoParseo(datos) {
    // Asegurar que advertencias sea un array
    const advertencias = datos.advertencias || [];

    // Actualizar el HTML del resultado de parseo para incluir beneficiario
    const htmlResultado = `
        <div class="alert alert-success">
            <h6 style="color:#0e220d"><i class="fas fa-check-circle mr-2"></i>Datos detectados (campo bloqueado automáticamente):</h6>
            <div class="row">
                <div class="col-md-6">
                    <p class="mb-1"><strong>Banco:</strong> <span id="parsedBanco">${datos.banco || "-"}</span></p>
                    <p class="mb-1"><strong>Fecha:</strong> <span id="parsedFecha">${datos.fecha || "-"}</span></p>
                    <p class="mb-1"><strong>Transacción:</strong> <span id="parsedTransaccion">${datos.transaccion || "-"}</span></p>
                </div>
                <div class="col-md-6">
                    <p class="mb-1"><strong>Monto:</strong> <span id="parsedMonto">${datos.monto || "-"}</span></p>
                    <p class="mb-1"><strong>Saldo:</strong> <span id="parsedSaldo">${datos.saldo || "-"}</span></p>
                    <p class="mb-1"><strong>Beneficiario:</strong> <span id="parsedBeneficiario">${datos.beneficiario || "-"}</span></p>
                </div>
            </div>
            ${
              datos.beneficiario
                ? `
            <div class="alert alert-info mt-2">
                <i class="fas fa-check text-success mr-2"></i>
                <strong>Beneficiario validado:</strong> Coincide con la tarjeta activa actual
            </div>
            `
                : ""
            }
            ${
              advertencias.length > 0
                ? `
            <div class="alert alert-warning mt-2">
                <i class="fas fa-exclamation-circle mr-2"></i>
                <strong>Advertencias:</strong>
                <ul class="mb-0 pl-3">
                    ${advertencias.map((adv) => `<li>${adv}</li>`).join("")}
                </ul>
            </div>
            `
                : ""
            }
        </div>
    `;

    $("#resultadoParseo").html(htmlResultado).show();

    // Guardar en localStorage
    localStorage.setItem("textarea_transfermovil_bloqueado", "true");
    localStorage.setItem(
      "textarea_transfermovil_texto",
      $("#textoTransferencia").val(),
    );
  }

  // Función para mostrar mensaje de bloqueo automático
  function mostrarMensajeBloqueoAutomatico() {
    Swal.fire({
      icon: "info",
      title: "✓ Texto detectado y bloqueado",
      html: `
            <p>Se ha detectado texto de transferencia y el campo ha sido bloqueado automáticamente.</p>
            <p class="text-muted small mt-2">
                <i class="fas fa-lock mr-1"></i> 
                Esta acción previene modificaciones accidentales o malintencionadas.
            </p>
            `,
      timer: 3000,
      showConfirmButton: false,
    });
  }

  // Función para mostrar advertencia cuando intentan editar
  function mostrarAdvertenciaEdicion() {
    Swal.fire({
      icon: "warning",
      title: "Bloqueo automático",
      html: `
            <p>El campo de texto se bloquea automáticamente para mantener la integridad de los datos.</p>
            <p class="text-muted mt-2">
                <i class="fas fa-exclamation-triangle mr-1"></i>
                No puedes editar el texto directamente.
            </p>
            <div class="alert alert-warning mt-3">
                <strong>Opciones disponibles:</strong>
                <ul class="mb-0 pl-3">
                    <li>Haz clic en "Limpiar datos" para empezar de nuevo</li>
                    <li>Pega un texto nuevo (reemplazará el anterior)</li>
                </ul>
            </div>
        `,
      confirmButtonText: "Entendido",
    }).then(() => {
      // Si quieren pegar algo nuevo
      Swal.fire({
        title: "¿Deseas pegar nuevo texto?",
        text: "Esto reemplazará el texto actual",
        icon: "question",
        showCancelButton: true,
        confirmButtonText: "Sí, pegar nuevo",
        cancelButtonText: "Cancelar",
      }).then((result) => {
        if (result.isConfirmed) {
          // Desbloquear temporalmente para pegar
          const $textarea = $("#textoTransferencia");
          $textarea.prop("disabled", false).val("");
          $textarea.focus();
        }
      });
    });
  }

  // Función para bloquear permanentemente un textarea
  function bloquearTextareaPermanentemente(elementId) {
    const $textarea = $(`#${elementId}`);

    // 🔥 Asegurar que se deshabilite completamente
    $textarea.prop("disabled", true);
    $textarea.prop("readonly", true); // Doble protección
    $textarea.attr("disabled", "disabled"); // Para HTML antiguo

    console.log("🔒 Textarea bloqueado:", {
      elementId: elementId,
      disabled: $textarea.prop("disabled"),
      readonly: $textarea.prop("readonly"),
      valor: $textarea.val().substring(0, 100),
    });

    // Cambiar estilo visual para indicar que está bloqueado permanentemente
    $textarea.css({
      "background-color": "#f5f5f5",
      "border-color": "#dc3545",
      cursor: "not-allowed",
      opacity: "0.7",
      color: "#6c757d",
    });

    // Agregar indicador visual de bloqueo permanente
    $textarea.after(`
        <div class="alert alert-warning small mt-2" id="bloqueado-permanentemente-${elementId}">
            <div class="d-flex align-items-center">
                <i class="fas fa-lock fa-lg mr-2 text-danger"></i>
                <div>
                    <strong class="d-block">Campo bloqueado permanentemente</strong>
                    <small class="text-muted">
                        Este campo ha sido validado y no puede ser modificado. 
                        Si necesitas corregir algo, usa la pestaña de formulario manual.
                    </small>
                </div>
            </div>
        </div>
    `);

    // Remover cualquier botón de habilitar edición
    $(`#bloqueado-${elementId}`).remove();
  }

  // Función para verificar estado al cargar el modal
  function verificarEstadoTextareaAlCargar() {
    // NO cargar estado anterior si es una nueva orden
    if (window.esNuevaOrden) {
      // console.log("🆕 Nueva orden - omitiendo estado anterior");
      return;
    }

    const $textarea = $("#textoTransferencia");
    const textoGuardado = localStorage.getItem("textarea_transfermovil_texto");
    const bloqueadoGuardado = localStorage.getItem(
      "textarea_transfermovil_bloqueado",
    );

    // Si hay un texto guardado y está marcado como bloqueado
    if (textoGuardado && bloqueadoGuardado === "true") {
      // Restaurar el texto
      $textarea.val(textoGuardado);

      // Bloquear permanentemente
      bloquearTextareaPermanentemente("textoTransferencia");

      // Parsear para mostrar los datos
      const parsed = parsearTextoTransferencia(textoGuardado);
      mostrarResultadoParseo(parsed);

      // Marcar como usado
      window.textareaUsado = true;
    }
  }

  // Función para limpiar el estado cuando se cierra el modal o se completa el pago
  function limpiarEstadoTextarea() {
    localStorage.removeItem("textarea_transfermovil_bloqueado");
    localStorage.removeItem("textarea_transfermovil_texto");
    window.textareaUsado = false;
  }

  // Función para limpiar estado completo (botón de emergencia)
  function limpiarEstadoCompleto() {
    Swal.fire({
      title: "¿Limpiar datos y permitir nueva transferencia?",
      html: `
            <p>Esta acción desbloqueará el campo para que puedas registrar una nueva transferencia.</p>
            <div class="alert alert-info mt-3">
                <i class="fas fa-info-circle mr-2"></i>
                <strong>Información:</strong> La orden actual se mantendrá, pero podrás registrar un nuevo pago.
            </div>
        `,
      icon: "question",
      showCancelButton: true,
      confirmButtonText: "Sí, permitir nueva transferencia",
      cancelButtonText: "Cancelar",
      confirmButtonColor: "#28a745",
    }).then((result) => {
      if (result.isConfirmed) {
        // Limpiar localStorage
        localStorage.removeItem("textarea_transfermovil_bloqueado");
        localStorage.removeItem("textarea_transfermovil_texto");

        // Reiniciar variables globales
        window.textareaUsado = false;
        window.esNuevaOrden = true;

        // Restablecer textarea
        const $textarea = $("#textoTransferencia");
        $textarea.val("");
        $textarea.prop("disabled", false);
        $textarea.prop("readonly", false);
        $textarea.css({
          "background-color": "#fff",
          "border-color": "#28a745",
          "border-width": "2px",
          cursor: "",
          opacity: "",
          color: "",
        });

        // Remover indicadores de bloqueo
        $('[id^="bloqueado-"]').remove();
        $('[id^="bloqueado-permanentemente-"]').remove();

        // Ocultar resultado de parseo
        $("#resultadoParseo").hide();

        // Actualizar estado visual
        const $estado = $("#estado-textarea");
        if ($estado.length) {
          $estado.html(
            '<i class="fas fa-bolt mr-1 text-success"></i><span class="text-success">Campo desbloqueado. Pega el texto de tu nueva transferencia.</span>',
          );
        }

        // Mostrar confirmación
        Swal.fire({
          icon: "success",
          title: "Listo para nueva transferencia",
          text: "El campo ha sido desbloqueado. Puedes pegar el texto de tu nueva transferencia.",
          timer: 2000,
          showConfirmButton: false,
        });
      }
    });
  }

  // ================= FUNCIONES AUXILIARES DE FECHA =================

  // Función para obtener fecha actual en formato YYYY-MM-DD
  function obtenerFechaActualLocal() {
    const hoy = new Date();
    const año = hoy.getFullYear();
    const mes = (hoy.getMonth() + 1).toString().padStart(2, "0");
    const dia = hoy.getDate().toString().padStart(2, "0");
    return obtenerFechaActual();
  }

  // Función para obtener fecha en formato legible
  function obtenerFechaActualLegible() {
    const hoy = new Date();
    const opciones = {
      weekday: "long",
      year: "numeric",
      month: "long",
      day: "numeric",
      hour: "2-digit",
      minute: "2-digit",
      timeZone: "America/Havana",
    };
    return hoy.toLocaleDateString("es-ES", opciones);
  }

  // Función para formatear fechas para mostrar al usuario
  function formatFechaParaUsuario(fecha) {
    return fecha.toLocaleDateString("es-ES", {
      weekday: "long",
      year: "numeric",
      month: "long",
      day: "numeric",
    });
  }

  // ================= MODAL TRANSFERMÓVIL CON SISTEMA DE BLOQUEO AUTOMÁTICO =================

  async function mostrarInstruccionesTransfermovil(data) {
    const orden = data.orden;
    const instrucciones = data.instrucciones;

    // console.log(
    //   "📱 Mostrando instrucciones de Transfermóvil para orden:",
    //   orden.numero,
    // );
    // console.log(
    //   "💳 Número de tarjeta REAL de la base de datos:",
    //   instrucciones.numero_tarjeta,
    // );

    // ========== IMPORTANTE: TRANSFERMÓVIL SIEMPRE USA CUP ==========

    // Usar los valores originales en CUP (nunca convertir)
    const montoCUP =
      window.checkoutTotalOriginalCUP || window.checkoutTotalOriginal;
    const monedaMostrar = "CUP";
    const simboloMostrar = "$";

    // console.log("💰 Monto para Transfermóvil (SIEMPRE en CUP):", {
    //   montoCUP: montoCUP,
    //   fuente: window.checkoutTotalOriginalCUP
    //     ? "checkoutTotalOriginalCUP"
    //     : "checkoutTotalOriginal",
    //   monedaAnterior: window.monedaOriginal || window.monedaCheckout,
    //   totalAnterior: window.totalOriginal || window.checkoutTotal,
    //   nota: "Transfermóvil siempre opera en CUP",
    // });

    // IMPORTANTE: Desbloquear textarea para nueva orden
    desbloquearTextareaParaNuevaOrden();

    // PRIMERO: Si ya existe un modal, removerlo
    $("#modalTransfermovil").remove();

    // Reiniciar estado de uso para NUEVA orden
    window.textareaUsado = false;
    window.esNuevaOrden = true;

    // Limpiar localStorage para nueva orden
    localStorage.removeItem("textarea_transfermovil_bloqueado");
    localStorage.removeItem("textarea_transfermovil_texto");

    // Formatear número de tarjeta para mostrar (enmascarado)
    const numeroTarjetaCompleto = instrucciones.numero_tarjeta;
    const numeroTarjetaEnmascarado = formatearNumeroTarjetaEnmascarado(
      numeroTarjetaCompleto,
    );

    // Guardar el número REAL en el modal para validaciones
    window.numeroTarjetaActual = numeroTarjetaCompleto;

    // Crear el contenido HTML del modal
    const htmlInstrucciones = `
        <div class="modal fade" id="modalTransfermovil" tabindex="-1" role="dialog" aria-labelledby="modalTransfermovilLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title" id="modalTransfermovilLabel">
                            <i class="fas fa-mobile-alt mr-2"></i>Instrucciones para pagar con Transfermóvil
                            <span class="badge badge-warning ml-2">Orden #${orden.numero}</span>
                        </h5>
                        <button type="button" class="close text-white" data-bs-dismiss="modal" aria-label="Close" onclick="limpiarEstadoCompleto()">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-success">
                            <h5 class="alert-heading">¡Orden creada exitosamente!</h5>
                            <p class="mb-0">
                                Tu orden <strong>#${orden.numero}</strong> ha sido creada. 
                                Estado del pago: <span class="badge badge-warning">Pendiente</span>
                            </p>
                        </div>
                        
                        <!-- Sección 1: Datos del beneficiario -->
                        <div class="card mb-4">
                            <div class="card-header-modern">
                                <h6 class="mb-0"><i class="fas fa-info-circle mr-2"></i>Datos para la transferencia</h6>
                            </div>
                            <div class="card-body">
                                <div class="row mb-4">
                                    <div class="col-md-6">
                                        <p class="mb-1"><strong>Número de tarjeta:</strong></p>
                                        <div class="input-group">
                                            <input type="text" class="form-control font-weight-bold" 
                                                   value="${numeroTarjetaCompleto}" 
                                                   id="numeroTarjeta" 
                                                   data-numero-real="${numeroTarjetaCompleto}"
                                                   data-numero-enmascarado="${numeroTarjetaEnmascarado}"
                                                   readonly>
                                            <div class="input-group-append">
                                                <button class="btn btn-outline-secondary" type="button" 
                                                        onclick="copiarAlPortapapeles('numeroTarjeta')">
                                                    <i class="fas fa-copy"></i>
                                                </button>
                                            </div>
                                        </div>
                                      </div>
                                    <div class="col-md-6">
                                        <p class="mb-1"><strong>Titular:</strong></p>
                                        <p class="font-weight-bold">${instrucciones.nombre_titular}</p>
                                        </div>
                                </div>
                                
                                <div class="row mb-4">
                                    <div class="col-md-6">
                                        <p class="mb-1"><strong>Monto a transferir:</strong></p>
                                        <!-- Destacar el monto -->
        <div class="alert alert-success p-2">
            <h4 class="text-center mb-0" id="montoDestacado"  style="color:var(--primary)">
                ${simboloMostrar} ${parseFloat(montoCUP).toFixed(2)}
            </h4>
        </div>
        ${
          window.monedaOriginal && window.monedaOriginal !== "CUP"
            ? `<small class="text-muted d-block">
                <i class="fas fa-exchange-alt mr-1"></i>
                Equivalente a ${window.totalOriginal?.toFixed(2) || window.checkoutTotal?.toFixed(2)} ${window.monedaOriginal || window.monedaCheckout}
            </small>`
            : ""
        }
    </div>
                                    <div class="col-md-6">
                                        <p class="mb-1"><strong>Referencia única:</strong></p>
                                        <div class="input-group">
                                            <input type="text" class="form-control font-weight-bold text-primary" 
                                                   value="${instrucciones.referencia}" id="referenciaPago" readonly>
                                            <div class="input-group-append">
                                                <button class="btn btn-outline-primary" type="button" 
                                                        onclick="copiarAlPortapapeles('referenciaPago')">
                                                    <i class="fas fa-copy"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Generar resto del modal -->
                        ${generarRestoDelModal(montoCUP, monedaMostrar, numeroTarjetaEnmascarado, instrucciones)}
                        
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" onclick="limpiarEstadoCompleto()">
                            <i class="fas fa-eraser mr-2"></i>Limpiar datos
                        </button>
                        <button type="button" class="btn btn-primary" id="btnRegistrarPago" data-orden-id="${orden.id}">
                            <i class="fas fa-check-circle mr-2"></i>Registrar mi pago
                        </button>
                        <a href="../index.php" class="btn btn-success" onclick="limpiarEstadoTextarea()">
                            <i class="fas fa-home mr-2"></i>Volver al inicio
                        </a>
                    </div>
                </div>
            </div>
        </div>
    `;

    // Función auxiliar para generar el resto del modal
    function generarRestoDelModal(
      montoCUP,
      monedaMostrar,
      numeroTarjetaEnmascarado,
      instrucciones,
    ) {
      // Obtener fecha actual
      const fechaActual = obtenerFechaActual();
      const fechaActualLegible = obtenerFechaActualLegible();

      return `
        <!-- Sección 2: Métodos de registro -->
        <div class="card mb-4">
            <div class="card-header-modern">
                <h6 class="mb-0"><i class="fas fa-clipboard-check mr-2"></i>Registra tu transferencia</h6>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label for="textoTransferencia"><strong>Pega el texto de tu transferencia:</strong></label>
                    <textarea class="form-control" id="textoTransferencia" rows="6" 
                              placeholder="Pega aquí el texto de tu transferencia (se bloqueará automáticamente):
                              
    Ejemplo:
    Banco: Banco Popular de Ahorro
    Fecha: ${fechaActualLegible.split(",")[0]}
    Beneficiario: ${numeroTarjetaEnmascarado}
    Ordenante: CUP
    Monto: ${parseFloat(montoCUP).toFixed(2)} CUP
    Nro. Transaccion: MM603RT559987
    Saldo restante: CR 669.35 CUP"></textarea>
                    <small class="form-text text-muted">
                        <span id="estado-textarea">
                            <i class="fas fa-info-circle mr-1"></i>
                            El campo se bloqueará automáticamente al pegar texto para evitar modificaciones.
                        </span>
                    </small>
                </div>
                
                <div id="resultadoParseo" class="mt-3" style="display: none;">
                    <!-- Se llenará dinámicamente con los datos parseados -->
                </div>
            </div>
        </div>
        
        <!-- Sección 3: Pasos -->
        <div class="card mb-4">
            <div class="card-header-modern">
                <h6 class="mb-0"><i class="fas fa-list-ol mr-2"></i>Pasos a seguir</h6>
            </div>
            <div class="card-body">
                <ol class="mb-0 pl-3">
                    <li class="mb-2">Realiza la transferencia a la tarjeta indicada</li>
                    <li class="mb-2">Usa la referencia <strong>${instrucciones.referencia}</strong></li>
                    <li class="mb-2">Transfiere <strong>${parseFloat(montoCUP).toFixed(2)} ${monedaMostrar}</strong></li>
                    <li class="mb-2"><strong>IMPORTANTE:</strong> El campo se bloqueará automáticamente al pegar texto</li>
                    <li class="mb-2"><strong>VERIFICA:</strong> Que el beneficiario sea <code>${numeroTarjetaEnmascarado}</code></li>
                    <li class="mb-2">Registra los datos usando uno de los métodos anteriores</li>
                    <li class="mb-2">Haz clic en <strong>"Registrar mi pago"</strong></li>
                </ol>
            </div>
        </div>
    `;
    }

    // Agregar el modal al DOM
    $("body").append(htmlInstrucciones);

    // Guardar información de la orden en el modal
    $("#modalTransfermovil").data({
      "orden-numero": orden.numero,
      "orden-id": orden.id,
      titular: instrucciones.nombre_titular,
      monto: montoCUP, // ¡GUARDAR MONTO PARA VALIDACIÓN!
      monto: montoCUP, // Siempre en CUP
      moneda: monedaMostrar, // Siempre CUP
      "numero-tarjeta-real": numeroTarjetaCompleto,
    });

    setTimeout(() => {
      $("#modalTransfermovil")
        .modal({
          backdrop: "static",
          keyboard: false,
        })
        .modal("show");

      // Configurar bloqueo automático
      configurarBloqueoAutomatico();

      // console.log("✅ Modal de Transfermóvil mostrado con montos en CUP");
    }, 100);
  }

  // Función para desbloquear textarea para nueva orden
  function desbloquearTextareaParaNuevaOrden() {
    // console.log("🔓 Desbloqueando textarea para nueva orden...");

    // Limpiar localStorage
    localStorage.removeItem("textarea_transfermovil_bloqueado");
    localStorage.removeItem("textarea_transfermovil_texto");

    // Restablecer variable global
    window.textareaUsado = false;

    // Desbloquear textarea si existe en el DOM
    const $textarea = $("#textoTransferencia");
    if ($textarea.length) {
      $textarea.prop("disabled", false);
      $textarea.prop("readonly", false);
      $textarea.val("");
      $textarea.css({
        "background-color": "",
        "border-color": "",
        cursor: "",
        opacity: "",
        color: "",
      });

      // Remover indicadores de bloqueo
      $('[id^="bloqueado-"]').remove();
      $('[id^="bloqueado-permanentemente-"]').remove();

      // Ocultar resultado de parseo
      $("#resultadoParseo").hide();

      // console.log("✅ Textarea desbloqueado para nueva orden");
    }
  }

  // Función para inicializar estado para nueva orden
  function inicializarEstadoNuevaOrden() {
    // console.log("🆕 Inicializando estado para nueva orden...");

    // Limpiar localStorage
    localStorage.removeItem("textarea_transfermovil_bloqueado");
    localStorage.removeItem("textarea_transfermovil_texto");

    // Reiniciar variables globales
    window.textareaUsado = false;
    window.esNuevaOrden = true;

    // Asegurar que el modal se cierre si existe
    $("#modalTransfermovil").modal("hide");
    $("#modalTransfermovil").remove();
  }

  async function mostrarConfirmacionPagoExitoso(data) {
    // console.log("✅ Pago automático exitoso:", data);

    // Restaurar moneda original si se había cambiado
    restaurarMonedaOriginal();

    // 1. PRIMERO vaciar el carrito
    await vaciarCarritoCompleto();

    // 2. LUES mostrar la confirmación y redirigir
    await Swal.fire({
      icon: "success",
      title: "¡Pago exitoso!",
      html: `
            <p>Tu orden <strong>#${data.orden.numero}</strong> ha sido procesada correctamente.</p>
            <p class="mb-0">Recibirás un email de confirmación con los detalles de tu compra.</p>
        `,
      confirmButtonText: "Ver mi orden",
      showCancelButton: true,
      cancelButtonText: "Seguir comprando",
    }).then((result) => {
      if (result.isConfirmed) {
        window.location.href = `orden_detalle.php?id=${data.orden.id}`;
      } else {
        window.location.href = "../index.php";
      }
    });
  }

  // ================= FUNCIONES DE VALIDACIÓN DE SEGURIDAD =================

  // Función principal de registro con validaciones de seguridad
  async function registrarTransferencia(ordenId) {
    try {
        console.log("🔐 Iniciando registro de transferencia con validaciones de seguridad...");

        let datosTransferencia = {};
        const $textarea = $("#textoTransferencia");
        const texto = $textarea.val().trim();

        // Verificar si el textarea está bloqueado (validado)
        if (!$textarea.prop("disabled")) {
            Swal.fire({
                icon: "warning",
                title: "Texto no procesado",
                html: `
                    <p>El campo de texto no ha sido procesado automáticamente.</p>
                    <p class="text-muted small mt-2">
                        El campo debería haberse bloqueado automáticamente al pegar texto.
                        Intenta pegar el texto nuevamente.
                    </p>
                `,
                confirmButtonText: "Entendido",
            });
            return;
        }

        if (!texto) {
            mostrarErrorValidacion(
                "Texto vacío",
                "Por favor, pega el texto de tu transferencia",
            );
            return;
        }

        // 🔥 INICIALIZAR numeroTarjetaReal ANTES de usarlo
        const numeroTarjetaReal = obtenerNumeroTarjetaReal();

        if (!numeroTarjetaReal) {
            mostrarErrorValidacion(
                "Error de configuración",
                "No se pudo obtener el número de tarjeta. Por favor, recarga la página.",
            );
            return;
        }

        console.log("💳 Número de tarjeta real obtenido:", numeroTarjetaReal);

        // Verificar que se hayan detectado los datos
        const parsed = await parsearTextoTransferencia(texto, numeroTarjetaReal);
        
        console.log("📊 Datos parseados para registro:", parsed);
        
        if (!parsed.valido) {
            // Verificar si el error es de beneficiario
            if (parsed.errores.some((e) => e.includes("beneficiario"))) {
                mostrarErrorBeneficiario(numeroTarjetaReal);
                return;
            }

            Swal.fire({
                icon: "error",
                title: "Datos incompletos",
                html: `
                    <p>El texto no contiene todos los datos necesarios.</p>
                    <div class="alert alert-warning mt-3">
                        <strong>Solución:</strong> 
                        <ul class="mb-0 pl-3 mt-2">
                            <li>Asegúrate de pegar el texto completo de la transferencia</li>
                            <li>Verifica que incluya: banco, fecha, beneficiario, monto y transacción</li>
                            <li>Vuelve a intentar pegar el texto</li>
                        </ul>
                    </div>
                `,
                confirmButtonText: "Entendido",
            }).then(() => {
                // Permitir pegar nuevo texto
                $textarea.prop("disabled", false).val("");
                $textarea.focus();
            });
            return;
        }

        // 🔥 IMPORTANTE: Asegurar que la transacción no sea "TRANSACCION" literal
        if (parsed.transaccion === "TRANSACCION") {
            console.warn("⚠️ Transacción es literal 'TRANSACCION', buscando transacción real...");
            
            // Buscar la transacción real en el texto (MM603RT559987)
            const transaccionMatch = texto.match(/MM\d+[A-Z]+\d+/i) || texto.match(/([A-Z0-9]{10,})/g);
            if (transaccionMatch) {
                const posiblesTransacciones = transaccionMatch.filter(t => 
                    t !== "TRANSACCION" && 
                    !t.includes('X') && 
                    !t.includes('*') &&
                    t !== numeroTarjetaReal.toString().replace(/\s/g, '')
                );
                
                if (posiblesTransacciones.length > 0) {
                    parsed.transaccion = posiblesTransacciones[0].toUpperCase();
                    console.log(`✅ Transacción corregida: ${parsed.transaccion}`);
                }
            }
        }

        datosTransferencia = {
            banco: parsed.banco,
            fecha: parsed.fecha,
            numero_transaccion: parsed.transaccion,
            monto_transferido: parsed.montoNumerico,
            saldo_restante: parsed.saldoNumerico || null,
            beneficiario: parsed.beneficiario,
            monto_texto: parsed.monto,
            tiene_saldo: !!parsed.saldoNumerico
        };

        console.log("📦 Datos transferencia preparados:", datosTransferencia);

        // ========== VALIDACIONES DE SEGURIDAD ==========

        // 1. Validar formato del número de transacción
        if (!validarFormatoTransaccion(datosTransferencia.numero_transaccion)) {
            mostrarErrorValidacion(
                "Formato de transacción inválido",
                "El número de transacción debe tener entre 8 y 20 caracteres alfanuméricos",
            );
            return;
        }

        // 2. Validar fecha (DEBE SER EXACTAMENTE HOY)
        try {
            await validarFechaTransferencia(datosTransferencia.fecha);
        } catch (error) {
            mostrarErrorFecha(error.message);
            return;
        }

        // 3. Obtener datos de la orden para validaciones
        console.log("📋 Solicitando datos de la orden ID:", ordenId);
const datosOrden = await obtenerDatosOrden(ordenId);
console.log("📋 Datos de orden recibidos:", datosOrden);

if (!datosOrden) {
    mostrarErrorValidacion(
        "Error interno",
        "No se pudieron obtener los datos de la orden",
    );
    return;
}

// Mostrar detalles de la orden
console.log("📊 Detalles de la orden:", {
    id: datosOrden.id,
    numero: datosOrden.numero,
    monto_total: datosOrden.monto_total,
    referencia: datosOrden.referencia,
    estado: datosOrden.estado
});

        // 4. Validar que el monto transferido coincida con el monto de la orden
        const montoEsperado = obtenerMontoEsperado();
        if (montoEsperado && datosTransferencia.monto_transferido) {
            const diferencia = Math.abs(datosTransferencia.monto_transferido - montoEsperado);
            
            console.log("💰 VALIDACIÓN DE MONTO DETALLADA:", {
                montoEncontrado: datosTransferencia.monto_transferido,
                montoEsperado: montoEsperado,
                diferencia: diferencia,
                tolerancia: 0.01
            });
            
            if (diferencia > 0.01) {
                mostrarErrorMonto(
                    datosTransferencia.monto_transferido, 
                    montoEsperado,
                    datosTransferencia.monto_texto
                );
                return;
            }
        }

        // 5. Validar beneficiario si se proporcionó desde el texto
        if (datosTransferencia.beneficiario) {
            if (!validarBeneficiario(datosTransferencia.beneficiario, numeroTarjetaReal)) {
                mostrarErrorBeneficiario(numeroTarjetaReal);
                return;
            }
        }

        // 6. Validar que el número de cuenta del beneficiario sea correcto
        if (!validarNumeroCuenta(numeroTarjetaReal)) {
            mostrarErrorValidacion(
                "Número de cuenta inválido",
                "El número de cuenta del beneficiario no tiene el formato correcto",
            );
            return;
        }

        // 7. Verificar si el número de transacción ya existe
        const transaccionDuplicada = await verificarTransaccionDuplicada(
            datosTransferencia.numero_transaccion,
        );
        if (transaccionDuplicada) {
            mostrarErrorValidacion(
                "Transacción duplicada",
                `El número de transacción ${datosTransferencia.numero_transaccion} ya ha sido registrado anteriormente`,
            );
            return;
        }

        // 8. Validar banco (lista blanca de bancos permitidos)
        if (!validarBanco(datosTransferencia.banco)) {
            mostrarErrorValidacion(
                "Banco no permitido",
                "El banco especificado no está en nuestra lista de bancos autorizados",
            );
            return;
        }

        // ========== ENVIAR DATOS AL SERVIDOR ==========

        Swal.fire({
            title: "Validando datos...",
            text: "Realizando verificaciones de seguridad",
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading(),
        });

        // Preparar datos para enviar
        const datosEnviar = {
            ...datosTransferencia,
            orden_id: ordenId,
            numero_tarjeta_beneficiario: numeroTarjetaReal,
            monto_orden: datosOrden.monto_total,
            referencia_orden: datosOrden.referencia,
            moneda: window.monedaCheckout || "CUP",
            simbolo_moneda: window.simboloMonedaCheckout || "$",
        };

        // Eliminar campo tiene_saldo si no es necesario en el servidor
        delete datosEnviar.tiene_saldo;
        
        console.log("📤 Enviando datos al servidor:", datosEnviar);

        // Enviar al controlador
        const response = await fetch(
            "../Controllers/TransfermovilController.php",
            {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                },
                body: JSON.stringify({
                    funcion: "registrar_transferencia_segura",
                    ...datosEnviar,
                    token_seguridad: generarTokenSeguridad(),
                }),
            },
        );

        const data = await response.json();

        if (data.success) {
            // 1. Vaciar el carrito
            await vaciarCarritoCompleto();

            // 2. Mostrar confirmación exitosa
            await mostrarConfirmacionExitosa(
                data,
                datosTransferencia.numero_transaccion,
            );

            // 3. Limpiar estado después de éxito
            limpiarEstadoTextarea();
        } else {
            throw new Error(data.message || "Error al registrar la transferencia");
        }
    } catch (error) {
        console.error("❌ Error en registro de transferencia:", error);
        mostrarErrorValidacion("Error del sistema", error.message);
    }
}

  // ================= FUNCIONES AUXILIARES DE VALIDACIÓN =================

  // 1. Validar formato del número de transacción
  function validarFormatoTransaccion(numeroTransaccion) {
    if (!numeroTransaccion || typeof numeroTransaccion !== "string") {
      return false;
    }

    // Longitud: 8-20 caracteres, solo mayúsculas y números
    const regex = /^[A-Z0-9]{8,20}$/;
    return regex.test(numeroTransaccion);
  }

  // 2. Validar fecha de transferencia - VERSIÓN ESTRICTA (SOLO HOY)
  async function validarFechaTransferencia(fechaInput) {
    try {
      // console.log("📅 Validando fecha de transferencia (SOLO HOY):", fechaInput);

      // Parsear la fecha usando la misma lógica que normalizarFecha
      const fechaParseada = parsearFechaLocal(fechaInput);

      if (!fechaParseada) {
        throw new Error("Fecha inválida proporcionada");
      }

      // Obtener fecha actual
      const hoy = new Date();
      const añoActual = hoy.getFullYear();
      const mesActual = hoy.getMonth() + 1;
      const diaActual = hoy.getDate();

      // console.log("📊 Comparación de fechas (SOLO HOY):", {
      //     fechaTransferencia: fechaParseada,
      //     hoy: { año: añoActual, mes: mesActual, dia: diaActual },
      //     esHoy: fechaParseada.año === añoActual &&
      //            fechaParseada.mes === mesActual &&
      //            fechaParseada.dia === diaActual
      // });

      // CRÍTICO: Solo aceptar fecha de HOY
      const esHoy =
        fechaParseada.año === añoActual &&
        fechaParseada.mes === mesActual &&
        fechaParseada.dia === diaActual;

      if (!esHoy) {
        // Formatear fechas para mostrar al usuario
        const fechaFormateada = `${fechaParseada.dia.toString().padStart(2, "0")}/${fechaParseada.mes.toString().padStart(2, "0")}/${fechaParseada.año}`;
        const hoyFormateado = `${diaActual.toString().padStart(2, "0")}/${mesActual.toString().padStart(2, "0")}/${añoActual}`;

        // Calcular diferencia de días
        const fechaObj = new Date(
          fechaParseada.año,
          fechaParseada.mes - 1,
          fechaParseada.dia,
        );
        const hoyObj = new Date(añoActual, mesActual - 1, diaActual);
        const diferenciaMs = fechaObj.getTime() - hoyObj.getTime();
        const diferenciaDias = Math.round(diferenciaMs / (1000 * 60 * 60 * 24));

        let mensajeError;

        if (diferenciaDias < 0) {
          // Fecha pasada
          mensajeError = `
                    ❌ La fecha de la transferencia (${fechaFormateada}) es anterior a hoy (${hoyFormateado}).
                    <br><br>
                    <strong>POLÍTICA ESTRICTA:</strong> Solo se aceptan transferencias realizadas HOY.
                    <br><br>
                    <strong>Motivo:</strong> Para garantizar la seguridad y evitar fraudes, 
                    solo procesamos transferencias realizadas en la fecha actual.
                `;
        } else {
          // Fecha futura
          mensajeError = `
                    ❌ La fecha de la transferencia (${fechaFormateada}) está en el futuro (hoy es ${hoyFormateado}).
                    <br><br>
                    <strong>POLÍTICA ESTRICTA:</strong> Solo se aceptan transferencias realizadas HOY.
                    <br><br>
                    <strong>Motivo:</strong> Las transferencias no pueden tener fecha futura.
                `;
        }

        console.error("❌ Error de fecha (SOLO HOY):", mensajeError);
        throw new Error(mensajeError);
      }

      // console.log("✅ Fecha validada correctamente - Es HOY");
      return true;
    } catch (error) {
      console.error("❌ Error validando fecha (SOLO HOY):", error);
      throw error;
    }
  }

  // Función para formatear fecha en formato español (dd/mm/yyyy)
  function formatearFechaEspanol(fechaString) {
    // console.log(`🔧 formatearFechaEspanol recibió: "${fechaString}"`);

    try {
      // Si ya está en formato dd/mm/yyyy, devolver tal cual
      if (fechaString.includes("/")) {
        const partes = fechaString.split("/");
        if (partes.length === 3) {
          const [dia, mes, año] = partes;
          return `${dia.padStart(2, "0")}/${mes.padStart(2, "0")}/${año}`;
        }
      }

      // Si está en formato YYYY-MM-DD
      if (fechaString.includes("-")) {
        const partes = fechaString.split("-");
        if (partes.length === 3) {
          const [año, mes, dia] = partes;
          return `${dia.padStart(2, "0")}/${mes.padStart(2, "0")}/${año}`;
        }
      }

      // Intentar con Date
      const fecha = new Date(fechaString);

      // Verificar si es válida
      if (isNaN(fecha.getTime())) {
        console.error(`❌ Fecha inválida: "${fechaString}"`);
        return fechaString; // Devolver original si no se puede parsear
      }

      // Obtener componentes con métodos seguros
      const dia = fecha.getDate().toString().padStart(2, "0");
      const mes = (fecha.getMonth() + 1).toString().padStart(2, "0");
      const año = fecha.getFullYear();

      const resultado = `${dia}/${mes}/${año}`;
      // console.log(`✅ formatearFechaEspanol resultó: ${resultado}`);
      return resultado;
    } catch (error) {
      console.error(`❌ Error en formatearFechaEspanol:`, error);
      return fechaString; // Devolver original en caso de error
    }
  }

  // Función para obtener fecha actual en formato español
  function obtenerFechaActualEspanol() {
    const hoy = new Date();
    const dia = hoy.getDate().toString().padStart(2, "0");
    const mes = (hoy.getMonth() + 1).toString().padStart(2, "0");
    const año = hoy.getFullYear();
    return `${dia}/${mes}/${año}`;
  }

  // Función para obtener fecha actual en formato YYYY-MM-DD (para input type="date")
  function obtenerFechaActual() {
    const hoy = new Date();
    const año = hoy.getFullYear();
    const mes = (hoy.getMonth() + 1).toString().padStart(2, "0");
    const dia = hoy.getDate().toString().padStart(2, "0");
    return `${año}-${mes}-${dia}`;
  }

  // Función para comparar fechas ignorando ceros a la izquierda
  function sonFechasIguales(fecha1, fecha2) {
    // console.log(`🔧 sonFechasIguales comparando: "${fecha1}" vs "${fecha2}"`);

    try {
      // Si son iguales como strings, ya está
      if (fecha1 === fecha2) {
        // console.log(`✅ Fechas iguales como strings`);
        return true;
      }

      // Parsear ambas fechas
      const d1 = parsearFechaLocal(fecha1);
      const d2 = parsearFechaLocal(fecha2);

      if (!d1 || !d2) {
        console.error(`❌ No se pudieron parsear las fechas`);
        return false;
      }

      // Comparar año, mes y día
      const sonIguales =
        d1.año === d2.año && d1.mes === d2.mes && d1.dia === d2.dia;
      // console.log(`🔍 Comparación local: ${sonIguales} (${d1.dia}/${d1.mes}/${d1.año} vs ${d2.dia}/${d2.mes}/${d2.año})`);

      return sonIguales;
    } catch (error) {
      console.error(`❌ Error en sonFechasIguales:`, error);
      return false;
    }
  }

  function parsearFechaLocal(fechaString) {
    // console.log(`🔧 parsearFechaLocal recibió: "${fechaString}"`);

    try {
      // Si ya está en formato YYYY-MM-DD
      if (fechaString.includes("-") && fechaString.length === 10) {
        const [año, mes, dia] = fechaString.split("-").map(Number);
        return { año, mes, dia };
      }

      // Si está en formato dd/mm/yyyy
      if (fechaString.includes("/")) {
        const partes = fechaString.split("/");
        if (partes.length >= 3) {
          // Determinar formato
          if (partes[0].length === 4) {
            // Formato yyyy/mm/dd
            const [año, mes, dia] = partes.map(Number);
            return { año, mes, dia };
          } else {
            // Formato dd/mm/yyyy
            const [dia, mes, año] = partes.map(Number);
            return { año, mes, dia };
          }
        }
      }

      // Intentar con Date
      const fecha = new Date(fechaString);
      if (!isNaN(fecha.getTime())) {
        const año = fecha.getFullYear();
        const mes = fecha.getMonth() + 1;
        const dia = fecha.getDate();
        return { año, mes, dia };
      }

      console.error(`❌ No se pudo parsear: "${fechaString}"`);
      return null;
    } catch (error) {
      console.error(`❌ Error parseando fecha:`, error);
      return null;
    }
  }

  // 3. Validar monto de transferencia
  function validarMontoTransferencia(montoTransferido, montoOrden) {
    // Convertir a números flotantes con precisión de 2 decimales
    const montoTrans = parseFloat(montoTransferido).toFixed(2);
    const montoOrd = parseFloat(montoOrden).toFixed(2);

    // Obtener también el monto en CUP esperado
    const montoEsperadoCUP = window.checkoutTotalOriginalCUP
      ? parseFloat(window.checkoutTotalOriginalCUP).toFixed(2)
      : null;

    // console.log("💰 Validación de monto:", {
    //   montoTransferido: montoTrans,
    //   montoOrden: montoOrd,
    //   montoEsperadoCUP: montoEsperadoCUP,
    //   windowCheckoutTotal: window.checkoutTotal,
    // });

    // Si tenemos monto en CUP esperado, validar contra ese
    if (montoEsperadoCUP) {
      const esValidoCUP = montoTrans === montoEsperadoCUP;

      // console.log("🔍 Comparación con CUP esperado:", {
      //   montoTrans,
      //   montoEsperadoCUP,
      //   esValidoCUP,
      // });

      if (!esValidoCUP) {
        // Mostrar información útil para debugging
        console.warn(
          `⚠️ Monto transferido (${montoTrans}) no coincide con CUP esperado (${montoEsperadoCUP})`,
        );

        // Verificar si hay diferencia por redondeo
        const diferencia = Math.abs(
          parseFloat(montoTrans) - parseFloat(montoEsperadoCUP),
        );
        if (diferencia < 0.01) {
          // console.log("✅ Diferencia menor a 0.01 (redondeo), aceptando...");
          return true;
        }
      }

      return esValidoCUP;
    }

    // Fallback: validar contra montoOrden
    const esValidoOrden = montoTrans === montoOrd;

    // console.log("🔍 Comparación con montoOrden:", {
    //   montoTrans,
    //   montoOrd,
    //   esValidoOrden,
    // });

    return esValidoOrden;
  }

  function formatearMontoParaValidacion(monto) {
    // Formatear monto para mostrar en mensajes de error
    const simbolo = window.simboloMonedaCheckout || "$";
    const moneda = window.monedaCheckout || "CUP";

    return {
      conSimbolo: `${simbolo} ${parseFloat(monto).toFixed(2)}`,
      conMoneda: `${parseFloat(monto).toFixed(2)} ${moneda}`,
      soloNumero: parseFloat(monto).toFixed(2),
    };
  }

  // 4. Validar número de cuenta del beneficiario (formato flexible)
  function validarNumeroCuenta(numeroCuenta) {
    console.log("🔢 Validando número de cuenta:", numeroCuenta);

    if (!numeroCuenta) {
        console.log("❌ Número de cuenta vacío");
        return false;
    }

    // Convertir a string si no lo es
    const numeroStr = String(numeroCuenta);
    
    // Eliminar espacios
    const numeroLimpio = numeroStr.replace(/\s+/g, "");
    
    console.log("🔢 Número limpio:", numeroLimpio, "longitud:", numeroLimpio.length);

    // Longitud aceptable para tarjetas (generalmente 13-19 dígitos)
    if (numeroLimpio.length < 8 || numeroLimpio.length > 19) {
        console.log(`❌ Longitud inválida: ${numeroLimpio.length} dígitos`);
        return false;
    }

    // Solo números
    if (!/^\d+$/.test(numeroLimpio)) {
        console.log(`❌ Contiene caracteres no numéricos`);
        return false;
    }

    // Algoritmo de Luhn (verificación básica de tarjeta)
    function validarLuhn(numero) {
        let sum = 0;
        let alterna = false;
        
        for (let i = numero.length - 1; i >= 0; i--) {
            let n = parseInt(numero.charAt(i), 10);
            
            if (alterna) {
                n *= 2;
                if (n > 9) {
                    n = (n % 10) + 1;
                }
            }
            
            sum += n;
            alterna = !alterna;
        }
        
        return (sum % 10 === 0);
    }

    const esLuhnValido = validarLuhn(numeroLimpio);
    
    if (!esLuhnValido) {
        console.log("⚠️ No pasa validación Luhn (permitido por ahora para tarjetas cubanas)");
        // Para tarjetas cubanas, el algoritmo Luhn no siempre aplica
        // Así que solo es advertencia, no error
    }

    console.log("✅ Número de cuenta aceptado");
    return true;
}

  // 5. Validar banco (lista blanca)
  function validarBanco(nombreBanco) {
    if (!nombreBanco) return false;

    const bancosPermitidos = [
      "BANCO POPULAR DE AHORRO",
      "BANCO METROPOLITANO",
      "BANCO DE CRÉDITO Y COMERCIO",
      "BANCO FINANCIERO INTERNACIONAL",
      "TRANSFERMÓVIL",
      "BANDEC",
      "BPA",
      "METROPOLITANO",
      "BCC",
      "BFI",
    ];

    const bancoUpper = nombreBanco.toUpperCase();
    return bancosPermitidos.some(
      (banco) => bancoUpper.includes(banco) || banco.includes(bancoUpper),
    );
  }

  // 6. Extraer número de texto (para montos con símbolos)
  function extraerNumeroDeTexto(texto) {
    if (!texto) return null;

    console.log(`🔍 Extrayendo número de: "${texto}"`);

    // Limpiar texto
    const textoLimpio = texto.replace(/[^\d\.,]/g, "").trim();

    if (!textoLimpio) return null;

    // Si es solo un número sin separadores
    if (/^\d+$/.test(textoLimpio)) {
      const resultado = parseFloat(textoLimpio);
      console.log(`   → Número simple: ${resultado}`);
      return resultado;
    }

    // Manejar diferentes formatos
    let numero;

    if (textoLimpio.includes(",") && textoLimpio.includes(".")) {
      // Tiene ambos separadores
      const ultimoPunto = textoLimpio.lastIndexOf(".");
      const ultimaComa = textoLimpio.lastIndexOf(",");

      if (ultimoPunto > ultimaComa) {
        // Formato: "1,250.50" (coma=miles, punto=decimal)
        numero = textoLimpio.replace(/,/g, "");
      } else {
        // Formato: "1.250,50" (punto=miles, coma=decimal)
        numero = textoLimpio.replace(/\./g, "").replace(",", ".");
      }
    } else if (textoLimpio.includes(",")) {
      // Solo coma
      const partes = textoLimpio.split(",");
      if (partes.length === 2 && partes[1].length <= 2) {
        // Formato: "250,50" (coma=decimal)
        numero = textoLimpio.replace(",", ".");
      } else if (partes.length > 1) {
        // Formato: "1,250" (coma=miles) o "1,250,50" (error)
        numero = textoLimpio.replace(/,/g, "");
      } else {
        numero = textoLimpio;
      }
    } else if (textoLimpio.includes(".")) {
      // Solo punto
      const partes = textoLimpio.split(".");
      if (partes.length === 2 && partes[1].length <= 2) {
        // Formato: "250.50" (punto=decimal)
        numero = textoLimpio;
      } else if (partes.length > 1) {
        // Formato: "1.250" (punto=miles)
        numero = textoLimpio.replace(/\./g, "");
      } else {
        numero = textoLimpio;
      }
    } else {
      numero = textoLimpio;
    }

    const resultado = parseFloat(numero);

    console.log(`   → Número parseado: ${resultado} (de "${numero}")`);

    return isNaN(resultado) ? null : resultado;
  }

  // 7. Generar token de seguridad
  function generarTokenSeguridad() {
    const timestamp = Date.now().toString();
    const random = Math.random().toString(36).substring(2, 15);
    return btoa(
      timestamp + "|" + random + "|" + (window.usuarioData?.id || "0"),
    );
  }

  // 8. Obtener datos de la orden desde el servidor
  async function obtenerDatosOrden(ordenId) {
    try {
    console.log("📋 [OBTENER ORDEN] Solicitando datos para orden ID:", ordenId);

    // Asegurar que el usuario está logueado
        const usuarioId = window.usuarioData?.id || 0;
        console.log("👤 Usuario ID:", usuarioId);

      const response = await fetch("../Controllers/TransfermovilController.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({
          funcion: "obtener_datos_orden",
          orden_id: ordenId,
          usuario_id: usuarioId,
        }),
      });

      console.log("📋 [OBTENER ORDEN] Respuesta HTTP:", response.status, response.statusText);
        
        if (!response.ok) {
            console.error("❌ [OBTENER ORDEN] Error HTTP:", response.status);
            return null;
        }

        const responseText = await response.text();
        console.log("📋 [OBTENER ORDEN] Respuesta en texto:", responseText);
        
        let data;
        try {
            data = JSON.parse(responseText);
            console.log("📋 [OBTENER ORDEN] Respuesta parseada:", data);
        } catch (parseError) {
            console.error("❌ [OBTENER ORDEN] Error parseando JSON:", parseError);
            return null;
        }

        if (data.success && data.orden) {
            console.log("✅ [OBTENER ORDEN] Orden obtenida correctamente:", data.orden);
            return data.orden;
        } else {
            console.error("❌ [OBTENER ORDEN] Error en respuesta:", data.message || "Sin datos de orden");
            return null;
        }
    } catch (error) {
        console.error("❌ [OBTENER ORDEN] Error general:", error);
        return null;
    }
}

  // 9. Verificar transacción duplicada
  async function verificarTransaccionDuplicada(numeroTransaccion) {
    try {
      const response = await fetch(
        "../Controllers/TransfermovilController.php",
        {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
          },
          body: JSON.stringify({
            funcion: "verificar_transaccion_duplicada",
            numero_transaccion: numeroTransaccion,
          }),
        },
      );

      const data = await response.json();
      return data.duplicada || false;
    } catch (error) {
      console.error("Error verificando transacción duplicada:", error);
      return true; // Por seguridad, asumir duplicado si hay error
    }
  }

  // 10. Mostrar errores de parseo
  function mostrarErroresParseo(parsed) {
    return `
        <p>No pudimos extraer todos los datos necesarios:</p>
        <ul class="text-left">
            <li>Transacción: ${parsed.transaccion ? "✅" : "❌"}</li>
            <li>Monto: ${parsed.monto ? "✅" : "❌"}</li>
            <li>Fecha: ${parsed.fecha ? "✅" : "❌"}</li>
            <li>Banco: ${parsed.banco ? "✅" : "❌"}</li>
            <li>Beneficiario: ${parsed.beneficiario ? "✅" : "❌"}</li>
        </ul>
        <p>Por favor, usa el formulario o verifica el texto.</p>
    `;
  }

  // 11. Mostrar error de validación de fecha
  function mostrarErrorFecha(mensajeError) {
    const hoy = new Date();
    const hoyLegible = hoy.toLocaleDateString("es-ES", {
      weekday: "long",
      year: "numeric",
      month: "long",
      day: "numeric",
      hour: "2-digit",
      minute: "2-digit",
    });

    Swal.fire({
      icon: "error",
      title: "❌ Fecha no válida",
      html: `
            <div class="text-left">
                <p>${mensajeError}</p>
                <div class="alert alert-danger mt-3">
                    <i class="fas fa-calendar-times mr-2"></i>
                    <strong>POLÍTICA ESTRICTA:</strong> Solo se aceptan transferencias realizadas HOY.
                    <br>
                    <small>Fecha y hora actual: ${hoyLegible}</small>
                </div>
                <p class="mt-2"><strong>Solución:</strong></p>
                <ol class="text-left pl-3">
                    <li>Asegúrate de que la transferencia se realizó HOY</li>
                    <li>Verifica la fecha en el mensaje de Transfermóvil</li>
                    <li>Si la transferencia no es de hoy, realiza una nueva transferencia</li>
                    <li>Contacta con soporte si tienes dudas</li>
                </ol>
                <div class="alert alert-warning mt-3">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    <small>
                        <strong>Nota de seguridad:</strong> Esta política estricta protege contra fraudes 
                        y asegura que todas las transferencias sean procesadas en tiempo real.
                    </small>
                </div>
            </div>
        `,
      confirmButtonText: "Entendido",
      confirmButtonColor: "#dc3545",
      width: "600px",
    });
  }

  // 12. Mostrar error de validación general
  function mostrarErrorValidacion(titulo, mensaje) {
    Swal.fire({
      icon: "error",
      title: titulo,
      text: mensaje,
      confirmButtonText: "Entendido",
    });
  }

  // 13. Mostrar confirmación exitosa
  async function mostrarConfirmacionExitosa(data, numeroTransaccion) {
    await Swal.fire({
      icon: "success",
      title: "¡Pago registrado con éxito!",
      html: `
            <div class="text-left">
                <p><strong>Transacción verificada:</strong> ${numeroTransaccion}</p>
                <p><strong>Orden:</strong> #${data.numero_orden}</p>
                <p><strong>Fecha de registro:</strong> ${new Date().toLocaleString()}</p>
                <p><strong>Estado:</strong> <span class="badge badge-warning">En verificación</span></p>
                <hr>
                <p class="small text-muted">
                    <i class="fas fa-shield-alt mr-1"></i>
                    Todas las validaciones de seguridad fueron aprobadas
                </p>
            </div>
        `,
      confirmButtonText: "Continuar",
    });

    $("#modalTransfermovil").modal("hide");
    window.location.href = "../index.php";
  }

  // Función para verificar el estado del formulario
  async function verificarEstadoFormulario() {
    console.log("🔍 INICIANDO verificarEstadoFormulario()");
    
    const $textarea = $("#textoTransferencia");
    const texto = $textarea.val().trim();

    console.log("📝 Estado del textarea:", {
        texto: texto.substring(0, 50) + (texto.length > 50 ? "..." : ""),
        longitud: texto.length,
        deshabilitado: $textarea.prop("disabled"),
        soloLectura: $textarea.prop("readonly")
    });

    // Verificar diferentes estados
    if (!texto) {
        console.log("❌ ERROR: Campo de texto vacío");
        return {
            valido: false,
            mensaje: "El campo de texto está vacío",
            accion: "pegar el texto de la transferencia",
        };
    }

    // Verificar si está bloqueado (procesado automáticamente)
    if (!$textarea.prop("disabled")) {
        console.log("❌ ERROR: Textarea no está bloqueado");
        return {
            valido: false,
            mensaje: "El texto no ha sido procesado",
            accion: "esperar a que se procese automáticamente",
        };
    }

    // Verificar que se detectaron los datos mínimos (sin contar saldo)
    const numeroTarjetaReal = obtenerNumeroTarjetaReal();
    console.log("💳 Número de tarjeta obtenido:", numeroTarjetaReal);
    
    // 🔥 IMPORTANTE: Esperar a que la promesa se resuelva
    const parsed = await parsearTextoTransferencia(texto, numeroTarjetaReal);
    
    console.log("📊 Resultado del parseo en verificarEstadoFormulario:", {
        transaccion: parsed.transaccion,
        monto: parsed.montoNumerico,
        fecha: parsed.fecha,
        banco: parsed.banco,
        beneficiario: parsed.beneficiario,
        valido: parsed.valido,
        errores: parsed.errores
    });

    // Datos mínimos requeridos: transacción, monto, fecha y banco
    const datosMinimos = parsed.transaccion && parsed.montoNumerico && parsed.fecha && parsed.banco;
    
    if (!datosMinimos) {
        const erroresFaltantes = [];
        if (!parsed.transaccion) erroresFaltantes.push("transacción");
        if (!parsed.montoNumerico) erroresFaltantes.push("monto");
        if (!parsed.fecha) erroresFaltantes.push("fecha");
        if (!parsed.banco) erroresFaltantes.push("banco");
        
        console.log("❌ ERROR: Faltan datos obligatorios:", erroresFaltantes);
        
        return {
            valido: false,
            mensaje: `Faltan datos obligatorios: ${erroresFaltantes.join(", ")}`,
            accion: "pegar un texto completo de transferencia",
        };
    }

    // Verificar monto si está disponible
    if (parsed.montoNumerico) {
        const montoEsperado = obtenerMontoEsperado();
        console.log("💰 Comparación de montos:", {
            montoEncontrado: parsed.montoNumerico,
            montoEsperado: montoEsperado,
            diferencia: Math.abs(parsed.montoNumerico - montoEsperado)
        });
        
        if (montoEsperado && Math.abs(parsed.montoNumerico - montoEsperado) > 0.01) {
            console.log("❌ ERROR: Monto no coincide");
            return {
                valido: false,
                mensaje: "El monto no coincide con el solicitado",
                accion: "verificar el monto en el texto",
            };
        }
    }

    console.log("✅ TODO CORRECTO: Formulario válido");
    return {
        valido: true,
        mensaje: "Texto procesado automáticamente",
        accion: null,
    };
}

  // Función para parsear texto no estructurado CON VALIDACIÓN DINÁMICA DE BENEFICIARIO
  async function parsearTextoTransferencia(texto, numeroTarjetaEsperado = null) {
    const resultado = {
        valido: false,
        banco: null,
        fecha: null,
        transaccion: null,
        monto: null,
        montoNumerico: null,
        saldo: null,
        saldoNumerico: null,
        beneficiario: null,
        rawFecha: null,
        errores: [],
        advertencias: [],
    };

    console.log("🔍 Parseando texto de transferencia...");
    console.log("Texto completo recibido:", texto);

    try {
        // Convertir a líneas y limpiar
        const lineas = texto
            .split("\n")
            .map((linea) => linea.trim())
            .filter((linea) => linea);

        console.log(`📄 Total de líneas: ${lineas.length}`);
        console.log("Líneas individuales:", lineas);

        // Buscar patrones en cada línea
        for (let i = 0; i < lineas.length; i++) {
            const linea = lineas[i];
            const lineaLower = linea.toLowerCase();

            // DEBUG: Mostrar línea
            console.log(`📝 Línea ${i}: "${linea}"`);

            // ========== BUSCAR BANCO ==========
            if (!resultado.banco) {
                // Patrones más flexibles para banco
                if (lineaLower.includes("metropolitano")) {
                    resultado.banco = "Banco Metropolitano";
                    console.log(`   ✅ Banco encontrado: ${resultado.banco}`);
                } else if (lineaLower.includes("popular") || lineaLower.includes("ahorro")) {
                    resultado.banco = "Banco Popular de Ahorro";
                    console.log(`   ✅ Banco encontrado: ${resultado.banco}`);
                } else if (lineaLower.includes("transferencia") && lineaLower.includes("completada")) {
                    // Si la línea dice "La Transferencia fue completada", buscar banco en líneas anteriores
                    for (let j = Math.max(0, i - 2); j < i; j++) {
                        if (lineas[j].toLowerCase().includes("banco")) {
                            resultado.banco = lineas[j].replace(/banco\s*/i, "").trim();
                            console.log(`   🔍 Banco extraído de línea anterior: ${resultado.banco}`);
                            break;
                        }
                    }
                } else if (lineaLower.includes("banco")) {
                    // Extraer nombre del banco directamente
                    resultado.banco = linea.replace(/banco\s*/i, "").trim();
                    console.log(`   🔍 Banco extraído: ${resultado.banco}`);
                }
            }

            // ========== BUSCAR FECHA ==========
            if (!resultado.fecha && (lineaLower.includes("fecha") || /\d{1,2}[\/\-\.]\d{1,2}[\/\-\.]\d{2,4}/.test(linea))) {
                console.log(`   📅 Línea contiene fecha o patrón de fecha`);
                
                // Extraer fecha usando regex más flexible
                const patronesFecha = [
                    /fecha[:\s]+(\d{1,2}[\/\-\.]\d{1,2}[\/\-\.]\d{2,4})/i,
                    /(\d{1,2}[\/\-\.]\d{1,2}[\/\-\.]\d{2,4})/,
                    /(\d{4}[\/\-\.]\d{1,2}[\/\-\.]\d{1,2})/,
                ];

                for (const patron of patronesFecha) {
                    const match = linea.match(patron);
                    if (match) {
                        resultado.rawFecha = match[1] || match[0];
                        try {
                            resultado.fecha = normalizarFecha(resultado.rawFecha);
                            console.log(`   ✅ Fecha encontrada: "${resultado.rawFecha}" → ${resultado.fecha}`);
                            break;
                        } catch (error) {
                            console.log(`   ❌ Error normalizando fecha: ${error.message}`);
                        }
                    }
                }

                // Si no encontró con patrones, buscar manualmente
                if (!resultado.fecha && lineaLower.includes("fecha:")) {
                    const partes = linea.split(/fecha[:\s]+/i);
                    if (partes.length > 1) {
                        resultado.rawFecha = partes[1].trim();
                        try {
                            resultado.fecha = normalizarFecha(resultado.rawFecha);
                            console.log(`   🔍 Fecha extraída manualmente: "${resultado.rawFecha}" → ${resultado.fecha}`);
                        } catch (error) {
                            console.log(`   ❌ Error normalizando fecha manual: ${error.message}`);
                        }
                    }
                }
            }

            // ========== BUSCAR BENEFICIARIO ==========
            if (!resultado.beneficiario && (lineaLower.includes("beneficiario") || lineaLower.includes("destinatario"))) {
                console.log(`   🎯 Línea contiene "beneficiario" o "destinatario"`);
                
                // Patrones más flexibles para beneficiario
                const patronesBeneficiario = [
                    /beneficiario[:\s]+([\dX\*]{8,})/i,
                    /destinatario[:\s]+([\dX\*]{8,})/i,
                    /([\dX\*]{12,})/, // Cualquier secuencia de 12+ dígitos/X/*
                ];

                for (const patron of patronesBeneficiario) {
                    const match = linea.match(patron);
                    if (match) {
                        resultado.beneficiario = match[1].trim();
                        console.log(`   ✅ Beneficiario encontrado: "${resultado.beneficiario}"`);
                        break;
                    }
                }

                // Si no funcionó con patrones, intentar extraer manualmente
                if (!resultado.beneficiario) {
                    // Buscar cualquier número con X
                    const matchNumero = linea.match(/([\dX\*]{12,})/);
                    if (matchNumero) {
                        resultado.beneficiario = matchNumero[1].trim();
                        console.log(`   🔍 Beneficiario extraído por patrón numérico: "${resultado.beneficiario}"`);
                    }
                }
            }

            // ========== BUSCAR MONTO ==========
            if (!resultado.monto) {
                // Patrones más flexibles para monto
                const patronesMonto = [
                    /monto[:\s]+([\d\.,]+)/i,
                    /importe[:\s]+([\d\.,]+)/i,
                    /cantidad[:\s]+([\d\.,]+)/i,
                    /total[:\s]+([\d\.,]+)/i,
                    /transferid[oa][:\s]+([\d\.,]+)/i,
                    /pagad[oa][:\s]+([\d\.,]+)/i,
                    /(\$[\d\.,]+)/,
                    /([\d\.,]+\s*(?:cup|usd|mlc|pesos?))/i,
                ];

                for (const patron of patronesMonto) {
                    const match = linea.match(patron);
                    if (match) {
                        resultado.monto = match[1];
                        resultado.montoNumerico = extraerNumeroDeTexto(resultado.monto);
                        
                        if (resultado.montoNumerico !== null) {
                            console.log(`   ✅ Monto encontrado: "${resultado.monto}" → ${resultado.montoNumerico}`);
                            break;
                        }
                    }
                }
            }

            // ========== BUSCAR TRANSACCIÓN ==========
            if (!resultado.transaccion && /transaccion|transaction|nro\.?|número|operación|comp\.?/i.test(linea)) {
                // console.log(`   🔢 Línea contiene "transacción"`);
                
                const patronesTransaccion = [
                    /(?:transaccion|transaction|nro\.?|número|operación|comp\.?)[:\s]+([A-Z0-9]{8,20})/i,
                    /(?:no\.?\s*)?([A-Z0-9]{8,20})/,
                    /(?:código|code)[:\s]+([A-Z0-9]{8,20})/i
                ];

                for (const patron of patronesTransaccion) {
                    const match = linea.match(patron);
                    if (match) {
                        resultado.transaccion = match[1].toUpperCase();
                        // console.log(`   ✅ Transacción encontrada: ${resultado.transaccion}`);
                        break;
                    }
                }

                // Si no encontró con patrón, buscar cualquier código alfanumérico largo
                if (!resultado.transaccion) {
                    const matchCodigo = linea.match(/([A-Z0-9]{10,})/);
                    if (matchCodigo && !lineaLower.includes("beneficiario") && !lineaLower.includes("cuenta")) {
                        resultado.transaccion = matchCodigo[1].toUpperCase();
                        // console.log(`   🔍 Transacción aproximada: ${resultado.transaccion}`);
                    }
                }
            }

            // ========== BUSCAR SALDO (OPCIONAL) ==========
            if (!resultado.saldo && /saldo|balance|restante|disponible/i.test(linea)) {
                // console.log(`   💸 Línea contiene "saldo"`);
                
                const patronesSaldo = [
                    /(?:saldo|balance|restante|disponible)[:\s]+(?:CR\s*)?([\d\.,]+)/i,
                    /(?:saldo|balance)[:\s]+(?:es\s+)?([\d\.,]+)/i,
                    /(?:nuevo\s+saldo|saldo\s+actual)[:\s]+([\d\.,]+)/i,
                    /(?:saldo\s+final|balance\s+final)[:\s]+([\d\.,]+)/i
                ];

                for (const patron of patronesSaldo) {
                    const match = linea.match(patron);
                    if (match) {
                        resultado.saldo = match[1];
                        resultado.saldoNumerico = extraerNumeroDeTexto(resultado.saldo);
                        // console.log(`   ℹ️ Saldo encontrado (opcional): "${resultado.saldo}" → ${resultado.saldoNumerico}`);
                        break;
                    }
                }
            }
        }

        // ========== BÚSQUEDA AVANZADA SI FALTAN DATOS ==========
        
        // Si faltan datos, buscar en todo el texto
        if (!resultado.monto) {
            console.log("🔍 Búsqueda avanzada de monto en texto completo...");
            const montoMatch = texto.match(/(\d[\d\.,]*\d)/g);
            if (montoMatch) {
                // Filtrar números que no sean fechas o números de tarjeta
                const posiblesMontos = montoMatch.filter(num => {
                    const numLimpio = num.replace(/[^\d]/g, '');
                    return numLimpio.length > 2 && numLimpio.length < 10; // Montos típicos
                });
                
                if (posiblesMontos.length > 0) {
                    resultado.monto = posiblesMontos[0];
                    resultado.montoNumerico = extraerNumeroDeTexto(resultado.monto);
                    console.log(`   🔍 Monto encontrado en búsqueda avanzada: "${resultado.monto}" → ${resultado.montoNumerico}`);
                }
            }
        }

        // Buscar transacción en todo el texto
        if (!resultado.transaccion) {
            console.log("🔍 Búsqueda avanzada de transacción en texto completo...");
            const transaccionMatch = texto.match(/([A-Z0-9]{10,})/g);
            if (transaccionMatch) {
                // Filtrar: no debe ser número de tarjeta (16+ dígitos)
                const posiblesTransacciones = transaccionMatch.filter(t => {
                    const soloDigitos = t.replace(/[A-Z]/g, '');
                    return soloDigitos.length < 16; // No es número de tarjeta
                });
                
                if (posiblesTransacciones.length > 0) {
                    resultado.transaccion = posiblesTransacciones[0].toUpperCase();
                    console.log(`   🔍 Transacción encontrada en búsqueda avanzada: ${resultado.transaccion}`);
                }
            }
        }

        // Buscar banco en todo el texto si no se encontró
        if (!resultado.banco) {
            console.log("🔍 Búsqueda avanzada de banco en texto completo...");
            if (texto.toLowerCase().includes("metropolitano")) {
                resultado.banco = "Banco Metropolitano";
                console.log(`   🔍 Banco encontrado en búsqueda avanzada: ${resultado.banco}`);
            } else if (texto.toLowerCase().includes("popular")) {
                resultado.banco = "Banco Popular de Ahorro";
                console.log(`   🔍 Banco encontrado en búsqueda avanzada: ${resultado.banco}`);
            }
        }

        // Buscar fecha en todo el texto si no se encontró
        if (!resultado.fecha) {
            console.log("🔍 Búsqueda avanzada de fecha en texto completo...");
            const fechaMatch = texto.match(/(\d{1,2}[\/\-\.]\d{1,2}[\/\-\.]\d{2,4})/);
            if (fechaMatch) {
                resultado.rawFecha = fechaMatch[0];
                try {
                    resultado.fecha = normalizarFecha(resultado.rawFecha);
                    console.log(`   🔍 Fecha encontrada en búsqueda avanzada: "${resultado.rawFecha}" → ${resultado.fecha}`);
                } catch (error) {
                    console.log(`   ❌ Error normalizando fecha avanzada: ${error.message}`);
                }
            }
        }

        // Buscar beneficiario en todo el texto si no se encontró
        if (!resultado.beneficiario) {
            console.log("🔍 Búsqueda avanzada de beneficiario en texto completo...");
            const beneficiarioMatch = texto.match(/([\dX\*]{12,})/);
            if (beneficiarioMatch) {
                resultado.beneficiario = beneficiarioMatch[0].trim();
                console.log(`   🔍 Beneficiario encontrado en búsqueda avanzada: "${resultado.beneficiario}"`);
            }
        }

        // ========== VALIDACIONES POST-PARSEO ==========

        console.log("📊 RESUMEN FINAL DE DATOS EXTRAÍDOS:", {
            banco: resultado.banco,
            fecha: resultado.fecha,
            transaccion: resultado.transaccion,
            monto: resultado.montoNumerico,
            montoTexto: resultado.monto,
            saldo: resultado.saldoNumerico,
            saldoTexto: resultado.saldo,
            beneficiario: resultado.beneficiario
        });

        // 1. Validar datos obligatorios
        const datosObligatorios = [
            { campo: resultado.transaccion, nombre: "Número de transacción" },
            { campo: resultado.monto, nombre: "Monto" },
            { campo: resultado.fecha, nombre: "Fecha" },
            { campo: resultado.banco, nombre: "Banco" }
        ];

        datosObligatorios.forEach(dato => {
            if (!dato.campo) {
                resultado.errores.push(`${dato.nombre} no encontrado`);
            }
        });

        // 2. El saldo NO es obligatorio, solo advertencia
        if (!resultado.saldo) {
            resultado.advertencias.push("Saldo no encontrado (campo opcional)");
        }

        // 3. Validar beneficiario si se espera
        if (numeroTarjetaEsperado) {
            if (!resultado.beneficiario) {
                resultado.errores.push("Número de beneficiario no encontrado");
            } else {
                const validoBeneficiario = validarBeneficiario(
                    resultado.beneficiario,
                    numeroTarjetaEsperado,
                );
                if (!validoBeneficiario) {
                    const numeroEnmascarado = formatearNumeroTarjetaEnmascarado(numeroTarjetaEsperado);
                    resultado.errores.push(`El beneficiario no coincide con la tarjeta esperada (${numeroEnmascarado})`);
                }
            }
        }

        // 4. Validar monto contra el esperado
        if (resultado.montoNumerico !== null) {
            const montoEsperado = obtenerMontoEsperado();
            if (montoEsperado) {
                const diferencia = Math.abs(resultado.montoNumerico - montoEsperado);
                const esMontoValido = diferencia < 0.01;
                
                if (!esMontoValido) {
                    resultado.errores.push(`Monto incorrecto: ${resultado.montoNumerico} ≠ ${montoEsperado}`);
                }
                
                console.log("💰 VALIDACIÓN DE MONTO:", {
                    montoEncontrado: resultado.montoNumerico,
                    montoEsperado: montoEsperado,
                    diferencia: diferencia,
                    esValido: esMontoValido
                });
            } else {
                resultado.advertencias.push("No se pudo obtener el monto esperado para validación");
            }
        }

        // 5. Validar formato de transacción
        if (resultado.transaccion && !validarFormatoTransaccion(resultado.transaccion)) {
            resultado.errores.push("Formato de transacción inválido (debe tener 8-20 caracteres alfanuméricos)");
        }

        // 6. Validar fecha (debe ser hoy)
        if (resultado.fecha) {
            try {
                await validarFechaTransferencia(resultado.fecha);
            } catch (error) {
                resultado.errores.push(error.message);
            }
        }

        // 7. Validar banco
        if (resultado.banco && !validarBanco(resultado.banco)) {
            resultado.advertencias.push(`Banco "${resultado.banco}" no está en lista blanca (permitido por ahora)`);
        }

        // Determinar si es válido (todos los obligatorios presentes)
        resultado.valido = !!(
            resultado.transaccion &&
            resultado.monto &&
            resultado.fecha &&
            resultado.banco
        );

        // Si hay beneficiario esperado, también debe estar presente
        if (numeroTarjetaEsperado) {
            resultado.valido = resultado.valido && !!resultado.beneficiario;
        }

        console.log("✅ RESULTADO FINAL DEL PARSEO:", {
            valido: resultado.valido,
            errores: resultado.errores,
            advertencias: resultado.advertencias,
            datosObligatoriosCompletos: resultado.transaccion && resultado.monto && resultado.fecha && resultado.banco,
            tieneBeneficiario: !!resultado.beneficiario,
            tieneSaldo: !!resultado.saldo
        });

    } catch (error) {
        console.error("❌ Error en parseo:", error);
        resultado.errores.push(`Error del sistema: ${error.message}`);
        resultado.valido = false;
    }

    // 🔥 Asegurar que siempre se retorna el resultado
    console.log("🏁 Retornando resultado final del parseo:", resultado);
    return resultado;
}

  function obtenerMontoEsperado() {
    try {
      // Para Transfermóvil, siempre usar CUP
      if (window.monedaCheckout === "CUP") {
        if (
          window.checkoutTotalOriginalCUP !== undefined &&
          !isNaN(window.checkoutTotalOriginalCUP)
        ) {
          return window.checkoutTotalOriginalCUP;
        }
        if (
          window.checkoutTotal !== undefined &&
          !isNaN(window.checkoutTotal)
        ) {
          return window.checkoutTotal;
        }
      }

      // Para otros métodos
      if (window.checkoutTotal !== undefined && !isNaN(window.checkoutTotal)) {
        return window.checkoutTotal;
      }

      // Obtener del modal
      const $modal = $("#modalTransfermovil");
      if ($modal.length) {
        const montoModal = $modal.data("monto");
        if (montoModal) return parseFloat(montoModal);
      }

      // Obtener del input
      const $montoInput = $("#montoTransferido");
      if ($montoInput.length) {
        const montoInput = parseFloat($montoInput.val());
        if (!isNaN(montoInput)) return montoInput;
      }

      return null;
    } catch (error) {
      console.error("❌ Error obteniendo monto esperado:", error);
      return null;
    }
  }

  // Función auxiliar para normalizar Monto con validación estricta
  function normalizarFormatoMonto(monto, incluirSimbolo = true) {
    if (monto === null || monto === undefined) return "N/A";

    const numero =
      typeof monto === "string" ? extraerNumeroDeTexto(monto) : monto;
    if (numero === null || isNaN(numero)) return "N/A";

    // Formatear con 2 decimales
    const formateado = numero.toFixed(2);

    // Agregar separador de miles si es grande
    const partes = formateado.split(".");
    let parteEntera = partes[0];
    const parteDecimal = partes[1];

    // Agregar puntos cada 3 dígitos (formato español)
    parteEntera = parteEntera.replace(/\B(?=(\d{3})+(?!\d))/g, ".");

    const montoFormateado = `${parteEntera},${parteDecimal}`;

    if (incluirSimbolo) {
      const simbolo = window.simboloMonedaCheckout || "$";
      return `${simbolo} ${montoFormateado}`;
    }

    return montoFormateado;
  }

  // Función auxiliar para normalizar fechas con validación estricta
  function normalizarFecha(fechaTexto) {
    // console.log(`📅 Normalizando fecha (SOLO HOY): "${fechaTexto}"`);

    try {
      const hoy = new Date();
      const hoyLocal = obtenerFechaActualLocal(); // YYYY-MM-DD en hora local
      const hoyLegible = `${hoy.getDate().toString().padStart(2, "0")}/${(hoy.getMonth() + 1).toString().padStart(2, "0")}/${hoy.getFullYear()}`;

      // console.log(`ℹ️ Hoy (local): ${hoyLegible}, Hoy (local YYYY-MM-DD): ${hoyLocal}`);

      // Si la fecha es "hoy", usar fecha actual
      if (fechaTexto.toLowerCase() === "hoy") {
        // console.log(`✅ Fecha "hoy" detectada: ${hoyLocal}`);
        return hoyLocal;
      }

      // NO aceptar "ayer" ni fechas pasadas
      if (fechaTexto.toLowerCase() === "ayer") {
        throw new Error(
          `No se aceptan transferencias de ayer. Solo transferencias realizadas HOY (${hoyLegible}).`,
        );
      }

      // Limpiar la fecha
      fechaTexto = fechaTexto.trim();

      // Reemplazar puntos o guiones por slash
      fechaTexto = fechaTexto.replace(/[\.\-]/g, "/");

      // Extraer números de la fecha
      const numeros = fechaTexto.match(/\d+/g);

      if (numeros && numeros.length >= 3) {
        let dia, mes, año;

        // Tomar los tres primeros números
        const num1 = parseInt(numeros[0]);
        const num2 = parseInt(numeros[1]);
        const num3 = parseInt(numeros[2]);

        // console.log(`   Números extraídos: ${num1}/${num2}/${num3}`);

        // DETERMINAR FORMATO - En Cuba usamos dd/mm/yyyy
        // Si el primer número es > 31, probablemente es año (formato yyyy/mm/dd)
        if (num1 > 31 && num1 <= 9999) {
          // Formato yyyy/mm/dd
          año = num1.toString();
          mes = num2.toString().padStart(2, "0");
          dia = num3.toString().padStart(2, "0");
          // console.log(`   Interpretado como: yyyy/mm/dd`);
        }
        // Si el primer número es <= 31 y el segundo es <= 12, probablemente dd/mm
        else if (num1 <= 31 && num2 <= 12) {
          // Formato dd/mm/yyyy (más común en Cuba)
          dia = num1.toString().padStart(2, "0");
          mes = num2.toString().padStart(2, "0");
          año = num3.toString();
          // console.log(`   Interpretado como: dd/mm/yyyy`);
        }
        // Si el primer número es <= 12 y el segundo es <= 31, probablemente mm/dd
        else if (num1 <= 12 && num2 <= 31) {
          // Formato mm/dd/yyyy
          mes = num1.toString().padStart(2, "0");
          dia = num2.toString().padStart(2, "0");
          año = num3.toString();
          // console.log(`   Interpretado como: mm/dd/yyyy`);
        }
        // Caso por defecto: asumir dd/mm (formato más común en Cuba)
        else {
          dia = num1.toString().padStart(2, "0");
          mes = num2.toString().padStart(2, "0");
          año = num3.toString();
          // console.log(`   Interpretado como: dd/mm/yyyy (por defecto)`);
        }

        // Ajustar año si tiene 2 dígitos
        if (año.length === 2) {
          // Asumir siglo 21 para años 00-99
          año = "20" + año;
          // console.log(`   Año ajustado a: ${año}`);
        }

        // Construir fecha en formato YYYY-MM-DD
        const fechaNormalizada = `${año}-${mes}-${dia}`;

        // Comparar con fecha actual usando solo componentes locales
        // console.log(`📊 Comparando: ${fechaNormalizada} vs ${hoyLocal}`);

        // Parsear ambas fechas para comparación
        const fechaParseada = parsearFechaLocal(fechaNormalizada);
        const hoyParseado = parsearFechaLocal(hoyLocal);

        if (!fechaParseada || !hoyParseado) {
          throw new Error("Error al procesar las fechas para comparación");
        }

        const esHoy =
          fechaParseada.año === hoyParseado.año &&
          fechaParseada.mes === hoyParseado.mes &&
          fechaParseada.dia === hoyParseado.dia;

        // console.log(`🔍 Comparación detallada:`, {
        //     fecha: fechaParseada,
        //     hoy: hoyParseado,
        //     esHoy
        // });

        // Si la fecha NO es hoy, rechazar
        if (!esHoy) {
          const fechaUserFormatted = `${fechaParseada.dia.toString().padStart(2, "0")}/${fechaParseada.mes.toString().padStart(2, "0")}/${fechaParseada.año}`;

          // Calcular diferencia
          const fechaObj = new Date(
            fechaParseada.año,
            fechaParseada.mes - 1,
            fechaParseada.dia,
          );
          const hoyObj = new Date(
            hoyParseado.año,
            hoyParseado.mes - 1,
            hoyParseado.dia,
          );
          const diferenciaMs = fechaObj.getTime() - hoyObj.getTime();
          const diferenciaDias = Math.round(
            diferenciaMs / (1000 * 60 * 60 * 24),
          );

          let mensajeError;

          if (diferenciaDias === -1) {
            mensajeError = `La transferencia fue realizada AYER (${fechaUserFormatted}). Hoy es ${hoyLegible}. Solo se aceptan transferencias realizadas HOY.`;
          } else if (diferenciaDias < -1) {
            mensajeError = `La transferencia fue hace ${Math.abs(diferenciaDias)} días (${fechaUserFormatted}). Hoy es ${hoyLegible}. Solo se aceptan transferencias realizadas HOY.`;
          } else if (diferenciaDias === 1) {
            mensajeError = `La transferencia tiene fecha de MAÑANA (${fechaUserFormatted}). Hoy es ${hoyLegible}. Solo se aceptan transferencias realizadas HOY.`;
          } else {
            mensajeError = `La transferencia tiene fecha futura (${fechaUserFormatted}). Hoy es ${hoyLegible}. Solo se aceptan transferencias realizadas HOY.`;
          }

          throw new Error(mensajeError);
        }

        // console.log(`✅ Fecha normalizada y validada: ${fechaNormalizada} (ES HOY)`);
        return fechaNormalizada;
      } else if (numeros && numeros.length === 2) {
        // Caso especial: solo tiene día y mes, asumir año actual
        const num1 = parseInt(numeros[0]);
        const num2 = parseInt(numeros[1]);
        const añoActual = hoy.getFullYear();

        // Obtener día y mes actual
        const diaActual = hoy.getDate();
        const mesActual = hoy.getMonth() + 1;

        // console.log(`   Solo día y mes detectados: ${num1}/${num2}`);
        // console.log(`   Fecha actual: ${diaActual}/${mesActual}/${añoActual}`);

        let dia, mes;

        // Asumir formato dd/mm (más común en Cuba)
        if (num1 <= 31 && num2 <= 12) {
          dia = num1;
          mes = num2;
        } else if (num1 <= 12 && num2 <= 31) {
          mes = num1;
          dia = num2;
        } else {
          throw new Error("Formato de fecha incompleto o inválido");
        }

        // Verificar si coincide con la fecha actual
        if (dia === diaActual && mes === mesActual) {
          const fechaNormalizada = `${añoActual}-${mes.toString().padStart(2, "0")}-${dia.toString().padStart(2, "0")}`;
          // console.log(`✅ Fecha detectada como HOY: ${fechaNormalizada}`);
          return fechaNormalizada;
        } else {
          const fechaFormateada = `${dia.toString().padStart(2, "0")}/${mes.toString().padStart(2, "0")}/${añoActual}`;
          const hoyFormateada = `${diaActual.toString().padStart(2, "0")}/${mesActual.toString().padStart(2, "0")}/${añoActual}`;
          throw new Error(
            `La fecha ${fechaFormateada} no es hoy (${hoyFormateada}). Solo se aceptan transferencias de HOY.`,
          );
        }
      } else {
        throw new Error("Formato de fecha no reconocido");
      }
    } catch (error) {
      console.error(
        `❌ Error normalizando fecha "${fechaTexto}":`,
        error.message,
      );
      throw error;
    }
  }

  // ================= FUNCIONES UTILITARIAS =================

  function isValidEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
  }

  function mostrarError(mensaje) {
    Swal.fire({
      icon: "error",
      title: "Error",
      text: mensaje,
      confirmButtonText: "Entendido",
    });
  }

  function mostrarExito(mensaje) {
    Swal.fire({
      icon: "success",
      title: "Éxito",
      text: mensaje,
      confirmButtonText: "Continuar",
    });
  }

  // Función para copiar al portapapeles
  function copiarAlPortapapeles(elementId) {
    const elemento = document.getElementById(elementId);

    // Seleccionar el contenido
    if (elemento.tagName === "INPUT" || elemento.tagName === "TEXTAREA") {
      elemento.select();
      elemento.setSelectionRange(0, 99999); // Para móviles
    } else {
      // Para otros elementos, crear un rango de selección
      const range = document.createRange();
      range.selectNode(elemento);
      window.getSelection().removeAllRanges();
      window.getSelection().addRange(range);
    }

    try {
      const exitoso = document.execCommand("copy");
      if (exitoso) {
        // Resaltar visualmente el elemento copiado
        const originalBorder = elemento.style.border;
        elemento.style.border = "2px solid #28a745";

        Swal.fire({
          icon: "success",
          title: "Copiado",
          text: "Texto copiado al portapapeles",
          timer: 1500,
          showConfirmButton: false,
          position: "top-end",
          toast: true,
        });

        // Restaurar borde después de 1 segundo
        setTimeout(() => {
          elemento.style.border = originalBorder;
        }, 1000);
      }
    } catch (err) {
      console.error("Error al copiar:", err);
      Swal.fire({
        icon: "error",
        title: "Error",
        text: "No se pudo copiar al portapapeles",
        confirmButtonText: "Entendido",
      });
    }
  }

  // Función para notificar que ya se realizó el pago
  function notificarPagoRealizado(ordenId) {
    Swal.fire({
      title: "¿Ya realizaste el pago?",
      text: "Al confirmar, notificaremos a nuestro equipo para verificar tu transferencia más rápido.",
      icon: "question",
      showCancelButton: true,
      confirmButtonText: "Sí, ya pagué",
      cancelButtonText: "Todavía no",
      showLoaderOnConfirm: true,
      preConfirm: () => {
        return $.post("../Controllers/TransfermovilController.php", {
          funcion: "notificar_pago",
          orden_id: ordenId,
        }).then((response) => {
          const data =
            typeof response === "string" ? JSON.parse(response) : response;
          if (!data.success) {
            throw new Error(data.message || "Error al notificar");
          }
          return data;
        });
      },
    })
      .then((result) => {
        if (result.isConfirmed) {
          Swal.fire({
            icon: "success",
            title: "¡Notificado!",
            text: "Hemos notificado a nuestro equipo. Te contactaremos pronto para confirmar tu pago.",
            confirmButtonText: "Entendido",
          }).then(() => {
            $("#modalTransfermovil").modal("hide");
            window.location.href = "../index.php";
          });
        }
      })
      .catch((error) => {
        Swal.fire({
          icon: "error",
          title: "Error",
          text: "No se pudo enviar la notificación: " + error.message,
          confirmButtonText: "Entendido",
        });
      });
  }

  // Función para vaciar completamente el carrito
  async function vaciarCarritoCompleto() {
    console.log("🛒 Vaciando carrito después de pago exitoso...");

    try {
      // Limpiar el sessionStorage/localStorage primero
      sessionStorage.removeItem("checkoutItems");
      sessionStorage.removeItem("checkoutSubtotal");
      sessionStorage.removeItem("checkoutEnvio");
      sessionStorage.removeItem("checkoutTotal");

      // También limpiar el carrito del servidor
      const response = await $.post("../Controllers/CarritoController.php", {
        funcion: "vaciar_carrito",
        id_usuario: window.usuarioData?.id || 0,
      });

      const data =
        typeof response === "string" ? JSON.parse(response) : response;

      if (data.success) {
        console.log("✅ Carrito vaciado exitosamente en el servidor");
      } else {
        console.warn("⚠️ Carrito no vaciado en servidor:", data.message);
      }
    } catch (error) {
      console.error("❌ Error vaciando carrito:", error);
    }
  }

  function registrarTransaccionTransfermovil(ordenId) {
    const numeroTransaccion = $("#numeroTransaccion")
      .val()
      .trim()
      .toUpperCase();
    const montoTransferido = parseFloat($("#montoTransferido").val());

    // Validaciones básicas
    if (!numeroTransaccion || numeroTransaccion.length < 10) {
      Swal.fire({
        icon: "error",
        title: "Número inválido",
        text: "El número de transacción debe tener al menos 10 caracteres",
        confirmButtonText: "Entendido",
      });
      return;
    }

    if (isNaN(montoTransferido) || montoTransferido <= 0) {
      Swal.fire({
        icon: "error",
        title: "Monto inválido",
        text: "Ingresa un monto válido",
        confirmButtonText: "Entendido",
      });
      return;
    }

    Swal.fire({
      title: "Registrando pago...",
      text: "Por favor espera mientras registramos tu transacción",
      allowOutsideClick: false,
      didOpen: () => Swal.showLoading(),
    });

    // Enviar al controlador
    $.post("../Controllers/TransfermovilController.php", {
      funcion: "registrar_transaccion_usuario",
      orden_id: ordenId,
      numero_transaccion: numeroTransaccion,
      monto_transferido: montoTransferido,
    })
      .then((response) => {
        const data =
          typeof response === "string" ? JSON.parse(response) : response;

        if (data.success) {
          Swal.fire({
            icon: "success",
            title: "¡Pago registrado!",
            html: `
                    <p>Tu número de transacción <strong>${numeroTransaccion}</strong> ha sido registrado.</p>
                    <p class="small text-muted">Nuestro equipo verificará el pago en las próximas 24 horas.</p>
                    <p><strong>Orden #${data.numero_orden}</strong> - Estado: <span class="badge badge-warning">Pendiente de verificación</span></p>
                `,
            confirmButtonText: "Entendido",
          }).then(() => {
            $("#modalTransfermovil").modal("hide");
            window.location.href = "../index.php";
          });
        } else {
          throw new Error(data.message || "Error al registrar la transacción");
        }
      })
      .catch((error) => {
        Swal.fire({
          icon: "error",
          title: "Error",
          text: error.message || "No se pudo registrar la transacción",
          confirmButtonText: "Entendido",
        });
      });
  }

  // ================= EVENT LISTENERS ADICIONALES =================

  // Detectar cambio entre pestañas
  $(document).on(
    "shown.bs.tab",
    '#metodoRegistroTab a[data-toggle="tab"]',
    function (e) {
      const target = $(e.target).attr("href");

      if (target === "#texto") {
        // Si cambiamos a la pestaña de texto, verificar si ya está procesado
        const $textarea = $("#textoTransferencia");
        if ($textarea.val().trim() && !$textarea.prop("disabled")) {
          // Si hay texto pero no está procesado, sugerir pegar de nuevo
          setTimeout(() => {
            Swal.fire({
              icon: "info",
              title: "Texto pendiente de procesar",
              text: "Tienes texto pegado pero no se ha procesado automáticamente. Intenta pegar el texto nuevamente.",
              showCancelButton: true,
              confirmButtonText: "Pegar de nuevo",
              cancelButtonText: "Usar formulario",
            }).then((result) => {
              if (result.isConfirmed) {
                $textarea.val("");
                $textarea.focus();
              }
            });
          }, 500);
        }
      }
    },
  );

  // Prevenir edición manual del textarea con focus
  $(document).on("focus", "#textoTransferencia", function () {
    if ($(this).val().trim().length > 10) {
      $(this).blur(); // Quitar el foco
      mostrarAdvertenciaEdicion();
    }
  });

  // Evento para validar fecha en tiempo real en formulario manual
  $(document).on("change", "#fechaTransferencia", function () {
    const fechaSeleccionada = $(this).val();
    const fechaActual = obtenerFechaActual();

    if (fechaSeleccionada !== fechaActual) {
      // Restaurar fecha actual
      $(this).val(fechaActual);

      // Mostrar mensaje
      Swal.fire({
        icon: "warning",
        title: "Fecha no permitida",
        html: `
                <p>Solo puedes seleccionar la fecha actual (HOY).</p>
                <p class="text-muted small mt-2">
                    <i class="fas fa-calendar-alt mr-1"></i>
                    Fecha actual: ${obtenerFechaActualLegible()}
                </p>
                <div class="alert alert-info mt-3">
                    <i class="fas fa-info-circle mr-2"></i>
                    <strong>Política de seguridad:</strong> Solo procesamos transferencias realizadas hoy.
                </div>
            `,
        timer: 3000,
        showConfirmButton: false,
        position: "top-end",
        toast: true,
      });
    }
  });

  // Cuando se cierre el modal, inicializar para próxima orden
  $(document).on("hidden.bs.modal", "#modalTransfermovil", function () {
    // Inicializar estado para próxima orden
    inicializarEstadoNuevaOrden();
  });

  // Función para imprimir instrucciones
  function imprimirInstrucciones() {
    const contenido = `
        <html>
        <head>
            <title>Instrucciones de Pago - Orden #${$("#modalTransfermovil").data("orden-numero") || ""}</title>
            <style>
                body { font-family: Arial, sans-serif; padding: 20px; }
                h1 { color: #007bff; }
                .section { margin-bottom: 30px; border-bottom: 1px solid #ddd; padding-bottom: 20px; }
                .important { background-color: #fff3cd; padding: 10px; border-radius: 5px; }
                .data { background-color: #f8f9fa; padding: 15px; border-radius: 5px; }
                .steps { padding-left: 20px; }
                @media print {
                    .no-print { display: none; }
                }
            </style>
        </head>
        <body>
            <h1>Instrucciones de Pago - Transfermóvil</h1>
            <div class="section">
                <h3>Datos del beneficiario</h3>
                <div class="data">
                    <p><strong>Número de tarjeta:</strong> ${$("#numeroTarjeta").val()}</p>
                    <p><strong>Titular:</strong> ${$("#modalTransfermovil").data("titular")}</p>
                </div>
            </div>
            <div class="section">
                <h3>Datos de la transferencia</h3>
                <div class="data">
                    <p><strong>Monto a transferir:</strong> ${window.simboloMonedaCheckout} ${$("#modalTransfermovil").data("monto")}</p>
                    <p><strong>Referencia única:</strong> ${$("#referenciaPago").val()}</p>
                </div>
            </div>
            <div class="section">
                <h3>Pasos a seguir</h3>
                <ol class="steps">
                    <li>Abre la aplicación Transfermóvil en tu teléfono</li>
                    <li>Selecciona la opción "Transferir"</li>
                    <li>Ingresa el número de tarjeta del beneficiario</li>
                    <li>Ingresa el monto exacto indicado</li>
                    <li>En el campo de referencia/detalle, escribe la referencia única</li>
                    <li>Confirma los datos y completa la transferencia</li>
                    <li>Anota el número de transacción que te proporciona Transfermóvil</li>
                    <li>Registra el pago en la página web</li>
                </ol>
            </div>
            <div class="section no-print">
                <p><em>Documento generado el ${new Date().toLocaleDateString()} a las ${new Date().toLocaleTimeString()}</em></p>
            </div>
        </body>
        </html>
    `;

    const ventana = window.open("", "_blank");
    ventana.document.write(contenido);
    ventana.document.close();
    ventana.print();
  }

  $(document).on("click", "#btnRegistrarPago", function () {
    const ordenId = $(this).data("orden-id");
    console.log(
      "🖱️ Botón Registrar mi Pago clickeado via event delegation, ordenId:",
      ordenId,
    );
    verificarYRegistrarTransferencia(ordenId);
  });
}); // FIN DE $(document).ready
