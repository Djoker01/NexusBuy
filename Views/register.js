$(document).ready(function () {
    var funcion;
  
    $.validator.setDefaults({
        submitHandler: function () {
          let username = $('#username').val();
          let pass = $('#pass').val();
          let nombres = $('#nombres').val();
          let apellidos = $('#apellidos').val();
          let dni = $('#dni').val();
          let email = $('#email').val();
          let telefono= $('#telefono').val();
          funcion = "registrar_usuario";
          $.post('../Controllers/UsuarioController.php', {username,pass,nombres,apellidos,dni,email,telefono,funcion},(response)=>{
            if (response == "success") {
              Swal.fire({
                position: 'center',
                icon: 'success',
                title: 'Se ha registrado correctamente',
                showConfirmButton: false,
                timer: 2500
              }).then(function(){
                $('#form-register').trigger('reset');
                location.href = '../Views/login.php';
              })
            } else {
              Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Hubo conflicto al registrarse, comuniquese con el area de sistemas',
              })
            }
          })
        }
      });
      jQuery.validator.addMethod("usuario_existente",
        function (value, element) {
            let funcion="verificar_usuario";
            let bandera;
            $.ajax({
                type: "POST",
                url: "../Controllers/UsuarioController.php",
                data: 'funcion='+funcion+'&&value='+value,
                async: false,
                success: function(response){
                    if (response=="success") {
                        bandera=false;
                    }
                    else{
                       bandera=true; 
                    }
                }
            })

            return bandera;

        },"*El usuario ya existe, por favor ingrese uno diferente");
        

      jQuery.validator.addMethod("letras",
            function (value, element) {
              let variable = value.replace(/ /g, "");
              return /^[A-Za-z]+$/.test(variable);
            },"*Este campo solo permite letras");

      $('#form-register').validate({
        rules: {
          nombres: {
            required: true,
            letras: true
          },
          apellidos: {
            required: true,
            letras: true
          },
          username: {
            required: true,
            minlength: 5,
            maxlength: 20,
            usuario_existente: true
          },
          pass: {
            required: true,
            minlength: 8,
            maxlength: 20,
          },
          pass_repeat: {
            required: true,
            equalTo: "#pass"
          },
          dni: {
            required: true,
            digits: true,
            minlength: 11,
            maxlength: 11,
          },
          email: {
            required: true,
            email: true,
          },
          telefono: {
            required: true,
            digits: true,
            minlength: 8,
            maxlength: 8,
          },
          terms: {
            required: true
          },
        },
        messages: {
          username: {
            required: "*Este campo es obligatorio",
            minlength: "*El username debe ser mínimo 5 carácteres",
            maxlength: "*El username debe ser máximo 20 carácteres"
          },
          pass: {
            required: "*Este campo es obligatorio",
            minlength: "*El password debe ser mínimo 8 carácteres",
            maxlength: "*El password debe ser máximo 20 carácteres"
          },
          pass_repeat: {
            required: "*Este campo es obligatorio",
            equalTo: "*No concide con el password ingresado",
          },
          nombres: {
            required: "*Este campo es obligatorio",
          },
          apellidos: {
            required: "*Este campo es obligatorio",
          },
          dni: {
            required: "*Este campo es obligatorio",
            minlength: "*El DNI debe ser de solo 11 carácteres",
            maxlength: "*El DNI debe ser de solo 11 carácteres",
            digits: "*El DNI solo esta compuesto por números"
          },
          email: {
            required: "*Este campo es obligatorio",
            email: "*No es formato email"
          },
          telefono: {
            required: "*Este campo es obligatorio",
            minlength: "*El teléfono debe ser de solo 8 carácteres",
            maxlength: "*El teléfono debe ser de solo 8 carácteres",
            digits: "*El teléfono solo esta compuesto por números"
          },
          terms: "Por favor acepte los terminos de servicio"
        },
        errorElement: 'span',
        errorPlacement: function (error, element) {
          error.addClass('invalid-feedback');
          element.closest('.form-group').append(error);
        },
        highlight: function (element, errorClass, validClass) {
          $(element).addClass('is-invalid');
          $(element).removeClass('is-valid');
        },
        unhighlight: function (element, errorClass, validClass) {
          $(element).removeClass('is-invalid');
          $(element).addClass('is-valid');
        }
      });
  });
  