$(document).ready(function () {
  var funcion;
  verificar_sesion();

  function verificar_sesion() {
    funcion = 'verificar_sesion';
    $.post('../Controllers/UsuarioController.php', {funcion}, (response) => {
      if (response != '') {
        try {
          const userData = JSON.parse(response);
          redirigirPorTipoUsuario(userData.tipo_usuario);
        } catch (e) {
          console.log('No hay sesión activa');
        }
      }
    });
  }

  $('#form-login').submit(e => {
    funcion = 'login';
    let user = $('#user').val();
    let pass = $('#pass').val();
    
    // Validaciones básicas
    if (!user || !pass) {
      toastr.error('Por favor, complete todos los campos');
      return false;
    }

    $.post('../Controllers/UsuarioController.php', {user, pass, funcion}, (response) => {
      console.log('Respuesta del servidor:', response);
      
      if (response === 'error') {
        toastr.error('Usuario o contraseña incorrectos');
        return;
      }
      
      if (!isNaN(response)) {
        toastr.success('¡Logueado correctamente!');
        
        // Obtener datos completos del usuario para redirección
        funcion = 'verificar_sesion';
        $.post('../Controllers/UsuarioController.php', {funcion}, (sessionResponse) => {
          if (sessionResponse != '') {
            try {
              const userData = JSON.parse(sessionResponse);
              redirigirPorTipoUsuario(userData.tipo_usuario);
            } catch (e) {
              console.error('Error al obtener datos de sesión:', e);
              toastr.error('Error al iniciar sesión');
            }
          }
        });
      } else {
        toastr.error('Error inesperado al iniciar sesión');
      }
    }).fail(function(xhr, status, error) {
      console.error('Error en la petición:', error);
      toastr.error('Error de conexión. Intente nuevamente.');
    });
    
    e.preventDefault();
  });

  function redirigirPorTipoUsuario(tipoUsuario) {
    const tipo = tipoUsuario.toString();
    
    switch (tipo) {
      case '1': // superadmin
        location.href = '../index-admin.php';
        break;
      case '2': // cliente
        location.href = '../index.php';
        break;
      case '3': // vendedor
        location.href = '../mi_tienda.php';
        break;
      case '4': // empleado
        location.href = '../index-empleado.php';
        break;
      default:
        console.warn('Tipo de usuario no reconocido:', tipoUsuario);
        location.href = '../index.php';
        break;
    }
  }

  // Función para recuperar contraseña
  $('#btn-recuperar-contra').click(function() {
    mostrarModalRecuperacion();
  });

  // Mostrar/ocultar contraseña
  $('#toggle-password').click(function() {
    const passwordInput = $('#pass');
    const type = passwordInput.attr('type') === 'password' ? 'text' : 'password';
    passwordInput.attr('type', type);
    $(this).find('span').toggleClass('fa-eye fa-eye-slash');
  });

  // Limpiar mensajes de error al empezar a escribir
  $('#user, #pass').on('input', function() {
    toastr.clear();
  });

  

  // Función para validar email
  function isValidEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
  }
});