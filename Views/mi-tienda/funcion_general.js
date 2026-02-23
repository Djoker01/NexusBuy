// console.log('funcion_general.js cargado');
// console.log('BASE_PATH en script:', typeof BASE_PATH !== 'undefined' ? BASE_PATH : 'NO DEFINIDA');
// console.log('jQuery disponible:', typeof $ !== 'undefined');
// funcion_general.js - Funciones generales para todo el sitio
$(document).ready(function () {
  // console.log('Inicializando funciones generales...');

  if (window.USUARIO_ACTUAL && window.USUARIO_ACTUAL.id) {
    console.log("Usuario autenticado:", window.USUARIO_ACTUAL);

    // Asegurar que los elementos del header muestren los datos correctos
    // (por si acaso, aunque ya se renderizaron con PHP)
    $(".user-name").text(window.USUARIO_ACTUAL.nombre);
    if (window.USUARIO_ACTUAL.avatar) {
      $(".user-avatar").attr(
        "src",
        BASE_PATH + "../../Util/Img/Users/" + window.USUARIO_ACTUAL.avatar,
      );
    }
    $(".user-role").text(window.USUARIO_ACTUAL.tipo_nombre);

    $("#nav_usuario, #favoritos, #notification").show();
  } else {
    console.warn("No hay usuario autenticado");
    $("#nav_usuario, #favoritos, #notification").hide();
  }
});
