$(document).ready(function () {
  var funcion;
  verificar_sesion();

  function verificar_sesion() {
    funcion = 'verificar_sesion';
    $.post('../Controllers/UsuarioController.php', {funcion}, (response) => {
      if (response != '') {
        redirigirPorTipoUsuario(response);
      }
    });
  }
    $('#form-login').submit(e=> {
    funcion = 'login';
    let user = $('#user').val();
    let pass = $('#pass').val();
    $.post('../Controllers/UsuarioController.php', {user, pass, funcion}, (response) => {
      console.log(response);
        if (response.startsWith('error:')) {
          toastr.error(response.substring(6));
          return;
        } if(!isNaN(response)) {
          toastr.success('!Logueado correctamente!');
          redirigirPorTipoUsuario(response);
        }else{
          toastr.error('Error inesperado');
          redirigirPorTipoUsuario(response);
        }
      });
    e.preventDefault();
  })
  function redirigirPorTipoUsuario(tipoUsuario){
    switch (tipoUsuario.toString()) {
      case '1':
        location.href = '../index-admin.php';
        break;
      case '2':
          location.href = '../index.php';
          break;
      case '3':
        location.href = 'vendedor.php';
        break;
    }
  }
})
