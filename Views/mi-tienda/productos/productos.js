$(document).ready(function () {
    var funcion;
  // verificar_sesion();
  obtenerProductos();

// function verificar_sesion() {
//     funcion = "verificar_sesion";
//     $.post("../../../Controllers/UsuarioController.php", { funcion }, (response) => {
      
//       if (response != "") {
//         let sesion = JSON.parse(response);
//         // console.log(sesion);
//         // $("#nav_login").hide();
//         // $("#nav_register").hide();
//         $(".user-name").text(sesion.user);
//         $(".user-avatar").attr("src", "../../../Util/Img/Users/" + sesion.avatar);
//         // $("#avatar_menu").attr("src", "../../../Util/Img/Users/" + sesion.avatar);
//         // $("#usuario_menu").text(sesion.user);
//         // $("#favoritos").show();
//         // $("#notification").show();
//         // Actualizar notificaciones después de verificar sesión
//         // actualizarNotificacionesHeader();
//       } else {
//         $("#nav_usuario").hide();
//         $("#favoritos").hide();
//         $("#notification").hide();
//       }
//     });
//   }

  async function obtenerProductos($id_tienda) {
    try {
        // console.log("Obteniendo productos...");
        funcion = "llenar_productos";

        const response = await $.post("../../../Controllers/ProductoTiendaController.php", {
        funcion,
      });
      
    //   console.log("Respuesta del servidor:", response);

      let data;
      if (typeof response === "string") {
        try {
          data = JSON.parse(response);
        } catch (e) {
          console.error("Error parseando JSON:", e);
          if (response.trim() === "no_sesion") {
            // console.log("Usuario no tiene sesión");
            mostrarTablaVacia();
            return;
          }
          throw new Error("Respuesta inválida del servidor");
        }
      } else {
        data = response;
      }
      
      // Verificar si hay error en la respuesta
      if (data && data.error) {
        if (data.error === "no_sesion") {
          // console.log("Usuario no tiene sesión");
          mostrarTablaVacia();
          return;
        } else {
          throw new Error(data.error);
        }
      }

      // Verificar que data es un array
      if (Array.isArray(data)) {
        productosItems = data;
        // console.log('Items en productos encontrados:', productosItems.length);
        // console.log('Datos de items:', productosItems);

        if (productosItems.length === 0) {
          mostrarTablaVacia();
         } else {
          renderizarTabla();
        }
        
      } else {
        console.error("Respuesta no es array:", data);
        mostrarTablaVacia();
      }
        
    } catch (error) {
        console.error("Error obteniendo productos:", error);
        mostrarError("No se pudieron cargar los productos. Intenta nuevamente.");
    }
        
  }

  function renderizarTabla() {
    console.log('=== INICIANDO RENDERIZADO ===');
    console.log('CarritoItems para renderizar:', productosItems);
    console.log('Número de items:', productosItems.length);

    if (!productosItems || productosItems.length === 0) {
      // console.log("No hay items, mostrando carrito vacío");
      mostrarCarritoVacio();
      return;
    }

    let template = "";

    productosItems.forEach((item, index) => {
      console.log(`Procesando item ${index}:`, item);
      template += `
        <tr>
            <td>
                <div class="product-cell">
                    <img class="product-image" src="../../../Util/Img/Producto/${item.imagen}">
                        
                    <div class="product-info">
                         <h4>${item.producto}a</h4>
                        <span>SKU: ${item.sku}</span>
                    </div>
                </div>
            </td>
            <td>
                <div class="price-info">
                    $${item.precio} CUP
                    <br>
                    <small>Descuento ${item.descuento}%</small>
                </div>
            </td>
            <td>
                <span class="stock-badge normal">${item.stock} Unidades</span>
            </td>
            <td>${item.ventas}</td>
            <td>
                <span class="status-badge active">
                    <i class="fas fa-circle" style="font-size: 0.5rem;"></i>
                    ${item.estado}
                </span>
            </td>
            <td>
                <div class="action-icons">
                    <button onclick="openModal(true)"><i class="fas fa-edit"></i></button>
                    <button><i class="fas fa-copy"></i></button>
                    <button class="delete"><i class="fas fa-trash"></i></button>
                </div>
            </td>
        </tr>
      `;
    });

    console.log('Template generado, insertando en DOM...');

    const $articulos = $("#articulos");
    if ($articulos.length === 0) {
      console.error("ERROR: No se encontró el elemento #articulos");
      return;
    }

    $articulos.html(template);
    // actualizarEstadoBotones();
    console.log('=== RENDERIZADO COMPLETADO ===');
    }

  function mostrarTablaVacia() {
    // console.log('Mostrando carrito vacío');
    const template = `
            <div class="empty-table">
                <h3>No tienes productos publicados</h3>
                <p>Publica tu primer producto</p>
                <a href="agregar-producto.php" class="btn btn-primary">
                    <i class="fas fa-plus mr-2"></i>Agregar producto
                </a>
            </div>
        `;
    $("#articulos").html(template);
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
}); //FIN DEL DOCUMEN READY