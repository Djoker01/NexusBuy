// badge-carrito.js - Para páginas que no son el carrito
$(document).ready(function () {
  var funcion;
  verificar_sesion();
  //console.log('Inicializando badge del carrito...');

  // Actualizar badge al cargar la página
  actualizarBadgeCarrito();

  // Actualizar periódicamente
  setInterval(actualizarBadgeCarrito, 30000);

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
      } else {
        $("#nav_usuario").hide();
        $("#favoritos").hide();
      }
    });
  }

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


  
});
