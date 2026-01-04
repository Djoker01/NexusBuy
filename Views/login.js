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
          // console.log('No hay sesión activa');
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
      // console.log('Respuesta del servidor:', response);
      
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

  // Cargar modal de recuperación
  function cargarModalRecuperacion() {
    $.get('recuperar_modal.html', function(html) {
      if ($('#modalRecuperarContra').length === 0) {
        $('body').append(html);
        inicializarModalRecuperacion();
      }
    }).fail(function() {
      // Si falla la carga, crear un modal básico
      crearModalBasico();
    });
  }

  function crearModalBasico() {
    const modalHTML = `
    <div class="modal fade" id="modalRecuperarContra" tabindex="-1">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Recuperar Contraseña</h5>
            <button type="button" class="close" data-dismiss="modal">&times;</button>
          </div>
          <div class="modal-body">
            <div class="form-group">
              <label>Correo Electrónico</label>
              <input type="email" id="email-recuperacion" class="form-control" placeholder="tu@email.com">
            </div>
            <div class="alert alert-info">
              Te enviaremos un enlace para restablecer tu contraseña.
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
            <button type="button" class="btn btn-primary" id="btn-enviar-rec">Enviar</button>
          </div>
        </div>
      </div>
    </div>`;
    
    $('body').append(modalHTML);
    $('#modalRecuperarContra').modal('show');
    
    $('#btn-enviar-rec').click(function() {
      const email = $('#email-recuperacion').val();
      if (email && isValidEmail(email)) {
        enviarSolicitudRecuperacion(email);
      } else {
        toastr.error('Ingresa un email válido');
      }
    });
  }

  function inicializarModalRecuperacion() {
    const modal = $('#modalRecuperarContra');
    
    // Variables de estado
    let currentStep = 1;
    let userEmail = '';
    let recoveryToken = '';
    
    // Configurar botones de mostrar/ocultar contraseña
    modal.on('click', '.toggle-password', function() {
      const targetId = $(this).data('target');
      const input = $(targetId);
      const type = input.attr('type') === 'password' ? 'text' : 'password';
      input.attr('type', type);
      $(this).find('i').toggleClass('fa-eye fa-eye-slash');
    });
    
    // Validación de fortaleza de contraseña en tiempo real
    modal.on('input', '#nueva-contrasena', function() {
      const password = $(this).val();
      const strength = calcularFortalezaPassword(password);
      
      $('#password-strength-bar').css('width', strength.porcentaje + '%')
        .removeClass('bg-danger bg-warning bg-info bg-success')
        .addClass(strength.clase);
      
      $('#password-strength-text').text(strength.texto).removeClass('text-danger text-warning text-info text-success')
        .addClass(strength.claseTexto);
    });
    
    // Validar coincidencia de contraseñas
    modal.on('input', '#nueva-contrasena, #confirmar-contrasena', function() {
      const pass1 = $('#nueva-contrasena').val();
      const pass2 = $('#confirmar-contrasena').val();
      
      if (pass2 === '') {
        $('#password-match').hide();
        return;
      }
      
      const matchElement = $('#password-match');
      if (pass1 === pass2) {
        matchElement.text('✓ Las contraseñas coinciden').removeClass('text-danger').addClass('text-success').show();
      } else {
        matchElement.text('✗ Las contraseñas no coinciden').removeClass('text-success').addClass('text-danger').show();
      }
    });
    
    // Botón Siguiente
    modal.find('#btn-siguiente').click(function() {
      const btn = $(this);
      const btnText = btn.find('.btn-text');
      const spinner = btn.find('.spinner-border');
      
      btn.prop('disabled', true);
      spinner.removeClass('d-none');
      
      switch(currentStep) {
        case 1: // Validar email
          userEmail = $('#email-recuperacion').val().trim();
          
          if (!userEmail || !isValidEmail(userEmail)) {
            toastr.error('Por favor, ingresa un correo electrónico válido');
            btn.prop('disabled', false);
            spinner.addClass('d-none');
            return;
          }
          
          enviarSolicitudRecuperacion(userEmail);
          break;
          
        case 3: // Restablecer contraseña
          const nuevaPass = $('#nueva-contrasena').val();
          const confirmarPass = $('#confirmar-contrasena').val();
          
          if (!nuevaPass || !confirmarPass) {
            toastr.error('Ambos campos de contraseña son obligatorios');
            btn.prop('disabled', false);
            spinner.addClass('d-none');
            return;
          }
          
          if (nuevaPass !== confirmarPass) {
            toastr.error('Las contraseñas no coinciden');
            btn.prop('disabled', false);
            spinner.addClass('d-none');
            return;
          }
          
          if (nuevaPass.length < 8) {
            toastr.error('La contraseña debe tener al menos 8 caracteres');
            btn.prop('disabled', false);
            spinner.addClass('d-none');
            return;
          }
          
          restablecerContraseña(recoveryToken, nuevaPass);
          break;
      }
    });
    
    // Botón Cancelar
    modal.find('#btn-cancelar').click(function() {
      resetearModal();
    });
    
    // Reenviar enlace
    modal.on('click', '#btn-reeviar', function() {
      const btn = $(this);
      btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i>Enviando...');
      
      enviarSolicitudRecuperacion(userEmail, function() {
        setTimeout(() => {
          btn.prop('disabled', false).html('<i class="fas fa-redo mr-1"></i>Reenviar enlace');
          toastr.success('Enlace reenviado correctamente');
        }, 1000);
      });
    });
    
    // Ir a login después de éxito
    modal.on('click', '#btn-ir-login', function() {
      modal.modal('hide');
      setTimeout(() => {
        location.reload();
      }, 300);
    });
    
    // Mostrar modal
    modal.modal('show');
    
    // Evento cuando se cierra el modal
    modal.on('hidden.bs.modal', function() {
      resetearModal();
      modal.remove();
    });
  }

  function calcularFortalezaPassword(password) {
    let score = 0;
    let feedback = {
      porcentaje: 0,
      clase: 'bg-danger',
      claseTexto: 'text-danger',
      texto: 'Muy débil'
    };
    
    if (!password) return feedback;
    
    // Longitud
    if (password.length >= 8) score += 25;
    if (password.length >= 12) score += 15;
    
    // Complejidad
    if (/[a-z]/.test(password)) score += 10;
    if (/[A-Z]/.test(password)) score += 10;
    if (/[0-9]/.test(password)) score += 10;
    if (/[^A-Za-z0-9]/.test(password)) score += 10;
    
    // Diversidad
    const uniqueChars = new Set(password).size;
    score += Math.min(20, uniqueChars * 2);
    
    // Determinar nivel
    if (score >= 80) {
      feedback = { porcentaje: 100, clase: 'bg-success', claseTexto: 'text-success', texto: 'Muy fuerte' };
    } else if (score >= 60) {
      feedback = { porcentaje: 75, clase: 'bg-info', claseTexto: 'text-info', texto: 'Fuerte' };
    } else if (score >= 40) {
      feedback = { porcentaje: 50, clase: 'bg-warning', claseTexto: 'text-warning', texto: 'Moderada' };
    } else if (score >= 20) {
      feedback = { porcentaje: 25, clase: 'bg-danger', claseTexto: 'text-danger', texto: 'Débil' };
    }
    
    return feedback;
  }

  function enviarSolicitudRecuperacion(email, callback = null) {
    const modal = $('#modalRecuperarContra');
    const btn = modal.find('#btn-siguiente');
    const btnText = btn.find('.btn-text');
    const spinner = btn.find('.spinner-border');
    
    $.ajax({
      url: '../Controllers/UsuarioController.php',
      method: 'POST',
      data: {
        funcion: 'recuperar_contra',
        email: email
      },
      success: function(response) {
        spinner.addClass('d-none');
        
        switch(response) {
          case 'success':
            // Mostrar paso 2 (verificar email)
            avanzarPaso(2);
            modal.find('#email-mostrado').text(email);
            toastr.success('Correo enviado. Revisa tu bandeja de entrada.');
            if (callback) callback();
            break;
            
          case 'success_sin_correo':
            avanzarPaso(2);
            modal.find('#email-mostrado').text(email);
            toastr.info('Enlace generado (modo desarrollo). En producción se enviaría por email.');
            if (callback) callback();
            break;
            
          case 'error_email_invalido':
            toastr.error('El formato del correo electrónico es inválido');
            btn.prop('disabled', false);
            break;
            
          case 'error_limite_intentos':
            toastr.error('Has excedido el límite de intentos. Intenta nuevamente mañana.');
            btn.prop('disabled', false);
            break;
            
          case 'error_token_activo':
            toastr.warning('Ya tienes un enlace activo. Revisa tu correo o espera a que expire.');
            btn.prop('disabled', false);
            break;
            
          case 'error_envio_correo':
            toastr.error('Error al enviar el correo. Por favor, intenta nuevamente.');
            btn.prop('disabled', false);
            break;
            
          case 'error_bd':
            toastr.error('Error en el servidor. Intenta más tarde.');
            btn.prop('disabled', false);
            break;
            
          default:
            // Por seguridad, mostrar éxito aunque el email no exista
            avanzarPaso(2);
            modal.find('#email-mostrado').text(email);
            toastr.success('Si el correo existe en nuestro sistema, recibirás un enlace.');
            if (callback) callback();
        }
      },
      error: function() {
        spinner.addClass('d-none');
        btn.prop('disabled', false);
        toastr.error('Error de conexión. Verifica tu internet.');
      }
    });
  }

  function verificarTokenRecuperacion(token) {
    return new Promise((resolve, reject) => {
      $.ajax({
        url: '../Controllers/UsuarioController.php',
        method: 'POST',
        data: {
          funcion: 'verificar_token_recuperacion',
          token: token
        },
        success: function(response) {
          try {
            const data = JSON.parse(response);
            if (data.valido) {
              resolve(data);
            } else {
              reject('Token inválido');
            }
          } catch (e) {
            reject('Error verificando token');
          }
        },
        error: function() {
          reject('Error de conexión');
        }
      });
    });
  }

  function restablecerContraseña(token, nuevaPass) {
    const modal = $('#modalRecuperarContra');
    const btn = modal.find('#btn-siguiente');
    const btnText = btn.find('.btn-text');
    const spinner = btn.find('.spinner-border');
    
    $.ajax({
      url: '../Controllers/UsuarioController.php',
      method: 'POST',
      data: {
        funcion: 'resetear_contra',
        token: token,
        nueva_pass: nuevaPass,
        confirmar_pass: nuevaPass
      },
      success: function(response) {
        spinner.addClass('d-none');
        
        switch(response) {
          case 'success':
            avanzarPaso(4);
            toastr.success('¡Contraseña actualizada exitosamente!');
            break;
            
          case 'error_token_invalido':
            toastr.error('El enlace de recuperación es inválido.');
            btn.prop('disabled', false);
            break;
            
          case 'error_token_expirado':
            toastr.error('El enlace ha expirado. Solicita uno nuevo.');
            btn.prop('disabled', false);
            break;
            
          case 'error_contrasenas_no_coinciden':
            toastr.error('Las contraseñas no coinciden.');
            btn.prop('disabled', false);
            break;
            
          case 'error_contrasena_corta':
            toastr.error('La contraseña debe tener al menos 8 caracteres.');
            btn.prop('disabled', false);
            break;
            
          case 'error_contrasena_debil':
            toastr.error('La contraseña debe incluir mayúsculas, minúsculas y números.');
            btn.prop('disabled', false);
            break;
            
          case 'error_contrasena_igual':
            toastr.error('La nueva contraseña no puede ser igual a la anterior.');
            btn.prop('disabled', false);
            break;
            
          case 'error_actualizacion':
            toastr.error('Error al actualizar la contraseña.');
            btn.prop('disabled', false);
            break;
            
          default:
            toastr.error('Error inesperado. Intenta nuevamente.');
            btn.prop('disabled', false);
        }
      },
      error: function() {
        spinner.addClass('d-none');
        btn.prop('disabled', false);
        toastr.error('Error de conexión. Verifica tu internet.');
      }
    });
  }

  function avanzarPaso(nuevoPaso) {
    const modal = $('#modalRecuperarContra');
    const btn = modal.find('#btn-siguiente');
    const btnText = btn.find('.btn-text');
    
    // Ocultar todos los pasos
    modal.find('.recovery-step').hide().removeClass('active');
    
    // Mostrar paso actual
    modal.find('#step-' + ['email', 'verificar', 'reset', 'exito'][nuevoPaso - 1]).show().addClass('active');
    
    // Actualizar botones
    currentStep = nuevoPaso;
    
    switch(nuevoPaso) {
      case 1:
        btnText.text('Siguiente');
        btn.prop('disabled', false);
        modal.find('#btn-cancelar').show();
        break;
        
      case 2:
        btnText.text('He recibido el email');
        btn.prop('disabled', false);
        modal.find('#btn-cancelar').show();
        
        // Esperar 3 segundos antes de permitir continuar
        setTimeout(() => {
          btnText.text('Continuar');
        }, 3000);
        break;
        
      case 3:
        btnText.text('Restablecer Contraseña');
        btn.prop('disabled', false);
        modal.find('#btn-cancelar').show();
        break;
        
      case 4:
        btn.hide();
        modal.find('#btn-cancelar').hide();
        break;
    }
    
    // Desactivar spinner
    btn.find('.spinner-border').addClass('d-none');
  }

  function resetearModal() {
    const modal = $('#modalRecuperarContra');
    if (modal.length) {
      modal.find('.recovery-step').hide();
      modal.find('#step-email').show();
      modal.find('#email-recuperacion').val('');
      modal.find('#nueva-contrasena').val('');
      modal.find('#confirmar-contrasena').val('');
      modal.find('#recovery-token').val('');
      modal.find('#password-strength-bar').css('width', '0%');
      modal.find('#password-strength-text').text('');
      modal.find('#password-match').hide();
      
      // Restaurar botones
      modal.find('#btn-siguiente').show().prop('disabled', false)
        .find('.btn-text').text('Siguiente');
      modal.find('#btn-cancelar').show();
      modal.find('.spinner-border').addClass('d-none');
      
      currentStep = 1;
      userEmail = '';
      recoveryToken = '';
    }
  }

  // Función para recuperar contraseña
  $('#btn-recuperar-contra').click(function() {
    cargarModalRecuperacion();
  });

  // Mostrar/ocultar contraseña en login
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

  // Verificar si hay token en URL (para enlace directo)
  function verificarTokenURL() {
    const urlParams = new URLSearchParams(window.location.search);
    const token = urlParams.get('token');
    
    if (token && token.length === 64) {
      cargarModalRecuperacion();
      
      // Esperar a que se cargue el modal
      setTimeout(() => {
        const modal = $('#modalRecuperarContra');
        if (modal.length) {
          modal.on('shown.bs.modal', function() {
            verificarTokenRecuperacion(token).then((data) => {
              recoveryToken = token;
              userEmail = data.email;
              avanzarPaso(3);
              modal.find('#email-mostrado').text(data.email);
            }).catch((error) => {
              toastr.error('El enlace de recuperación es inválido o ha expirado.');
              modal.modal('hide');
            });
          });
        }
      }, 500);
    }
  }
  
  // Ejecutar verificación de token en URL
  verificarTokenURL();

  // Función para validar email
  function isValidEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
  }
});
