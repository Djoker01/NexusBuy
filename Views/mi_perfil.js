$(document).ready(function () {
    var funcion;
    bsCustomFileInput.init();
    cargarActividad();
    obtener_datos();
    llenar_provincia();
    llenar_direcciones();
    llenar_historial();
    cargarConfiguracion();
    obtener_monedas();
    $('#provincia').select2({
        placeholder: 'Seleccione una provincia',
        language: {
            noResults: function () {
                return "No hay resultado";
            },
            searching: function () {
                return "Buscando...";
            }
        }
    });
    $('#municipio').select2({
        placeholder: 'Seleccione un municipio',
        language: {
            noResults: function () {
                return "No hay resultado";
            },
            searching: function () {
                return "Buscando...";
            }
        }
    });
    function llenar_historial() {
      funcion="llenar_historial";
      $.post('../Controllers/HistorialController.php',{funcion}, (response)=>{
        let historiales = JSON.parse(response);
       // console.log(historiales);
       let template='';
       historiales.forEach( historial=> {
        template+=`
                <div class="time-label">
                        <span class="bg-danger">
                          ${historial[0].fecha}
                        </span>
                  </div>        
          `;
          historial.forEach(cambio => {
            template+=`
                       <div>
                        ${cambio.m_icono}

                        <div class="timeline-item">
                          <span class="time"><i class="far fa-clock"></i> ${cambio.hora}</span>

                          <h3 class="timeline-header">${cambio.th_icono} Se realizo la acción ${cambio.tipo_historial} en
                          ${cambio.modulo}</h3>

                          <div class="timeline-body">
                          ${cambio.descripcion}
                          </div>
                          </div>
                      </div>                     
                      
          `;
          });
       });
       template+=`
                <div>
                  <i class="far fa-clock bg-gray"></i>
                </div>       
          `;
        $('#historiales').html(template);
      });
    }
    function llenar_direcciones() {
      funcion="llenar_direcciones";
      $.post('../Controllers/UsuarioMunicipioController.php',{funcion}, (response)=>{
        let direcciones = JSON.parse(response);
        let contador = 0;
        let template='';
        direcciones.forEach(direccion => {
          contador++;
          template+=`
          <div class="callout callout-info">
            <div class="card-header">
              <strong>Dirección ${contador}</strong>
              <div class="card-tools">
                <button dir_id="${direccion.id}" type="button" class="eliminar_direccion btn btn-tool">
                  <i class="fas fa-trash-alt"></i>
                </button>
              </div>
            </div>
            <div class="card-body">
              <h2 class="lead"><b>${direccion.direccion}</b></h2>
                      <p class="text-muted text-sm"><b>Municipio: </b> ${direccion.municipio}</p>
                      <p class="text-muted text-sm"><b>Provincia: </b> ${direccion.provincia}</p>
              </div>
          </div>
                   
          `;
        })
        $('#direcciones').html(template);
      })
    }
    $(document).on('click','.eliminar_direccion', (e) => {
      let elemento = $(this)[0].activeElement;
      let id = $(elemento).attr('dir_id');
      const swalWithBootstrapButtons = Swal.mixin({
        customClass: {
          confirmButton: 'btn btn-success m-3',
          cancelButton: 'btn btn-danger'
        },
        buttonsStyling: false
      })

      swalWithBootstrapButtons.fire({
        title: 'Desea borrar esta dirección?',
        text: "Esta acción puede traer consecuencias!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Si, deseo borrar!',
        cancelButtonText: 'No, deseo cancelar!',
        reverseButtons: true
      }).then((result) => {
        if (result.isConfirmed){
          funcion="eliminar_direccion";
          $.post('../Controllers/UsuarioMunicipioController.php',{funcion, id}, (response)=>{
            //console.log(response);
            if (response=="success") {
              swalWithBootstrapButtons.fire(
                'Borrado!',
                'La dirección fue borrada',
                'succes'
              )
              llenar_direcciones();
              llenar_historial();
            }
            else if (response=="error") {
              swalWithBootstrapButtons.fire(
                'No se borro!',
                'Hubo alteraciones en la integridad de datos',
                'error'
              )
            }
            else {
              swalWithBootstrapButtons.fire(
                'No se ha borrado!',
                'Tenemos problemas en el sistema',
                'error'
              )
            }
          })

        } else if (result.dismiss === Swal.DismissReason.cancel) {
          
        }
      })
    })

    function llenar_provincia(){
      funcion="llenar_provincia";
      $.post('../Controllers/ProvinciaController.php',{funcion}, (response)=>{
        let provincias = JSON.parse(response);
        let template='';
        provincias.forEach(provincia => {
          template+=`
          <option value="${provincia.id}">${provincia.nombre}</option>
          `;
        });
        $('#provincia').html(template);
        $('#provincia').val('').trigger('change');
      })
    }
      $('#provincia').change(function(){
        let id_provincia = $('#provincia').val();
        funcion="llenar_municipio";
        $.post('../Controllers/MunicipioController.php',{funcion, id_provincia}, (response)=>{
          let municipios = JSON.parse(response);
          let template='';
          municipios.forEach(municipio => {
            template+=`
            <option value="${municipio.id}">${municipio.nombre}</option>
            `;
          });
          $('#municipio').html(template);
          $('#municipio').val('').trigger('change');
      })
    })

    
    function obtener_datos() {
        funcion = 'obtener_datos';
          $.post('../Controllers/UsuarioController.php', { funcion }, (response) => {
          let usuario = JSON.parse(response);
          $('#username').text(usuario.username);
          $('#tipo_usuario').text(usuario.tipo_usuario);
          $('#nombres_completos').text(usuario.nombres+' '+usuario.apellidos);
          $('#avatar_perfil').attr('src', '../Util/Img/Users/' + usuario.avatar);
          $('#dni').text(usuario.dni);
          $('#email').text(usuario.email);
          $('#telefono').text(usuario.telefono); 
        });
      }
      $('#form-direccion').submit(e=> {
        funcion = 'crear_direccion';
        let id_municipio = $('#municipio').val();
        let direccion = $('#direccion').val();
        $.post('../Controllers/UsuarioMunicipioController.php', {id_municipio, direccion, funcion}, (response) => {
            if (response =='success') {
              Swal.fire({
                position: 'center',
                icon: 'success',
                title: 'Se ha registrado su dirección',
                showConfirmButton: false,
                timer: 500
              }).then(function(){
                $('#form-direccion').trigger('reset');
                $('#provincia').val('').trigger('change');
                llenar_historial();
                llenar_direcciones();
              })
            } else {
              Swal.fire({
                icon:  'error',
                title: 'Error',
                text: 'Hubo conflicto al crear su dirección, comuniquese con el área de sistemas',
              })
            }
          })
        e.preventDefault();
      })
      $(document).on('click','.editar_datos',(e) => {
        funcion = "obtener_datos";
        $.post('../Controllers/UsuarioController.php',{funcion}, (response) => {
          let usuario = JSON.parse(response);
          $('#nombres_mod').val(usuario.nombres);
          $('#apellidos_mod').val(usuario.apellidos);
          $('#dni_mod').val(usuario.dni);
          $('#email_mod').val(usuario.email);
          $('#telefono_mod').val(usuario.telefono);
        })
      })
      $.validator.setDefaults({
        submitHandler: function () {
          funcion = "editar_datos";
          let datos = new FormData($('#form-datos')[0]);
          datos.append("funcion", funcion);
          $.ajax({
            type: "POST",
            url: '../Controllers/UsuarioController.php',
            data: datos,
            cache: false,
            processData: false,
            contentType: false,
            success: function(response){
              //console.log(response);
              if (response=='success') {
                Swal.fire({
                  position: 'center',
                  icon: 'success',
                  title: 'Se ha editado sus datos',
                  showConfirmButton: false,
                  timer: 500
                }).then(function(){
                  verificar_sesion();
                  obtener_datos();
                  llenar_historial();
                })
              } 
              else if (response=='danger') {
                Swal.fire({
                  icon:  'warning',
                  title: 'No realizo ningun cambio',
                  text: 'Modifique algun elemento para realizar la edición!',
                })
              }
              else {
                Swal.fire({
                  icon:  'error',
                  title: 'Error',
                  text: 'Hubo conflicto al editar sus datos, comuniquese con el área de sistemas',
                })
              }
              
            }
          })
        }
      });
      jQuery.validator.addMethod("letras",
        function (value, element) {
          let variable = value.replace(/ /g, "");
            return /^[A-Za-z]+$/.test(variable);
        },"*Este campo solo permite letras");

  $('#form-datos').validate({
    rules: {
      nombres_mod: {
        required: true,
        letras: true
      },
      apellidos_mod: {
        required: true,
        letras: true
      },
      dni_mod: {
        required: true,
        digits: true,
        minlength: 11,
        maxlength: 11,
      },
      email_mod: {
        required: true,
        email: true,
      },
      telefono_mod: {
        required: true,
        digits: true,
        minlength: 8,
        maxlength: 8,
      }
    },
    messages: {
      nombres_mod: {
        required: "*Este campo es obligatorio",
      },
      apellidos_mod: {
        required: "*Este campo es obligatorio",
      },
      dni_mod: {
        required: "*Este campo es obligatorio",
        minlength: "*El DNI debe ser de solo 11 carácteres",
        maxlength: "*El DNI debe ser de solo 11 carácteres",
        digits: "*El DNI solo esta compuesto por números"
      },
      email_mod: {
        required: "*Este campo es obligatorio",
        email: "*No es formato email"
      },
      telefono_mod: {
        required: "*Este campo es obligatorio",
        minlength: "*El teléfono debe ser de solo 8 carácteres",
        maxlength: "*El teléfono debe ser de solo 8 carácteres",
        digits: "*El teléfono solo esta compuesto por números"
      }
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


  //Cambiar Contraseña

  $.validator.setDefaults({
    submitHandler: function () {
      funcion = "cambiar_contra";
      let pass_old =$('#pass_old').val();
      let pass_new =$('#pass_new').val();
      $.post('../Controllers/UsuarioController.php',{funcion,pass_old,pass_new}, (response)=>{
        if (response=="success") {
          Swal.fire({
            position: 'center',
            icon: 'success',
            title: 'Se ha cambiado su password',
            showConfirmButton: false,
            timer: 1000
          }).then(function(){
            $('#form-contra').trigger('reset');
            llenar_historial();
            
          })
        }
        else if (response="error") {
          Swal.fire({
            icon:  'warningr',
            title: 'Password incorrecto',
            text: 'Ingrese su contraseña actual para poder cambiarla!',
          })
        }
        else {
          Swal.fire({
            icon:  'error',
            title: 'Error',
            text: 'Hubo conflicto al cambiar su password, comuniquese con el área de sistemas',
          })
        }
      })
    }
  });
  
    

  jQuery.validator.addMethod("letras",
        function (value, element) {
          let variable = value.replace(/ /g, "");
          return /^[A-Za-z]+$/.test(variable);
        },"*Este campo solo permite letras");

  $('#form-contra').validate({
    rules: {
      
      pass_old: {
        required: true,
        minlength: 8,
        maxlength: 20,
      },
      pass_new: {
        required: true,
        minlength: 8,
        maxlength: 20,
      },
      pass_repeat: {
        required: true,
        equalTo: "#pass_new"
      }
      
    },
    messages: {
      
      pass_old: {
        required: "*Este campo es obligatorio",
        minlength: "*El password debe ser mínimo 8 carácteres",
        maxlength: "*El password debe ser máximo 20 carácteres"
      },
      pass_new: {
        required: "*Este campo es obligatorio",
        minlength: "*El password debe ser mínimo 8 carácteres",
        maxlength: "*El password debe ser máximo 20 carácteres"
      },
      pass_repeat: {
        required: "*Este campo es obligatorio",
        equalTo: "*No concide con el password ingresado",
      }
      
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

  // TAB PANEL ACTIVIDAD ***************************************************
  

  async function cargarActividad() {
      //console.log('Cargando actividad del usuario...');
      
      try {
          // Mostrar loading
          $('#lista-actividad').html(`
              <div class="text-center py-5">
                  <div class="spinner-border text-primary" role="status">
                      <span class="sr-only">Cargando actividad...</span>
                  </div>
                  <p class="text-muted mt-2">Cargando tu actividad...</p>
              </div>
          `);
          $('#actividad-vacia').hide();
          
          const filtroTipo = $('#filtro-actividad').val();
          const filtroPeriodo = $('#filtro-periodo').val();
          
          const response = await $.post('../Controllers/TabpanelController.php', {
              funcion: 'obtener_actividad_usuario',
              filtro_tipo: filtroTipo,
              filtro_periodo: filtroPeriodo
          });
          
          const data = typeof response === 'string' ? JSON.parse(response) : response;
          
          if (data.error === 'no_sesion') {
              window.location.href = 'login.php';
              return;
          }
          
          if (data.success && data.actividades.length > 0) {
              renderizarActividad(data.actividades);
              actualizarEstadisticas(data.estadisticas);
              $('#actividad-vacia').hide();
              $('#lista-actividad').show();
          } else {
              mostrarActividadVacia();
          }
          
      } catch (error) {
          console.error('Error cargando actividad:', error);
          mostrarError('Error al cargar la actividad');
          mostrarActividadVacia();
      }
  }

  function renderizarActividad(actividades) {
      let html = '';
      
      actividades.forEach((actividad, index) => {
          const fecha = new Date(actividad.fecha).toLocaleDateString('es-ES', {
              year: 'numeric',
              month: 'long',
              day: 'numeric',
              hour: '2-digit',
              minute: '2-digit'
          });
          
          const tiempoRelativo = calcularTiempoRelativo(actividad.fecha);
          const icono = actividad.icono || obtenerIconoPorTipo(actividad.tipo);
          const color = obtenerColorPorTipo(actividad.tipo);
          
          html += `
              <div class="activity-item mb-4 ${index < actividades.length - 1 ? 'border-bottom pb-4' : ''}">
                  <div class="d-flex">
                      <div class="flex-shrink-0">
                          <div class="activity-icon ${color} rounded-circle d-flex align-items-center justify-content-center" 
                              style="width: 40px; height: 40px;">
                              <i class="${icono} text-white"></i>
                          </div>
                      </div>
                      <div class="flex-grow-1 ms-3">
                          <div class="d-flex justify-content-between align-items-start">
                              <div>
                                  <p class="mb-1">${actividad.descripcion}</p>
                                  ${actividad.detalles ? generarDetalles(actividad) : ''}
                              </div>
                              <div class="text-end">
                                  <small class="text-muted d-block">${tiempoRelativo}</small>
                                  <small class="text-muted">${fecha}</small>
                              </div>
                          </div>
                      </div>
                  </div>
              </div>
          `;
      });
      
      $('#lista-actividad').html(html);
  }

  function generarDetalles(actividad) {
      let detallesHtml = '<div class="mt-2 p-2 bg-light rounded small">';
      
      switch(actividad.tipo) {
          case 'compra':
              detallesHtml += `
                  <div class="row">
                      <div class="col-6"><strong>Orden:</strong> ${actividad.detalles.numero_orden}</div>
                      <div class="col-3"><strong>Total:</strong> $${parseFloat(actividad.detalles.total).toFixed(2)}</div>
                      <div class="col-3">
                          <span class="badge ${obtenerClaseEstado(actividad.detalles.estado)}">
                              ${actividad.detalles.estado}
                          </span>
                      </div>
                  </div>
              `;
              break;
              
          case 'reseña':
              detallesHtml += `
                  <div class="row">
                      <div class="col-8"><strong>Producto:</strong> ${actividad.detalles.producto}</div>
                      <div class="col-4 text-right">
                          ${generarEstrellas(actividad.detalles.calificacion)}
                      </div>
                  </div>
                  ${actividad.detalles.comentario ? `
                      <div class="mt-1">
                          <strong>Comentario:</strong> "${actividad.detalles.comentario}"
                      </div>
                  ` : ''}
              `;
              break;
              
          case 'direccion':
              detallesHtml += `
                  <div class="row">
                      <div class="col-12">
                          <strong>Dirección completa:</strong><br>
                          ${actividad.detalles.direccion}, ${actividad.detalles.municipio}, ${actividad.detalles.provincia}
                      </div>
                  </div>
              `;
              break;
              
          default:
              if (actividad.modulo) {
                  detallesHtml += `<strong>Módulo:</strong> ${actividad.modulo}`;
              }
      }
      
      detallesHtml += '</div>';
      return detallesHtml;
  }

  function generarEstrellas(calificacion) {
      let estrellas = '';
      for (let i = 1; i <= 5; i++) {
          if (i <= calificacion) {
              estrellas += '<i class="fas fa-star text-warning small"></i>';
          } else {
              estrellas += '<i class="far fa-star text-warning small"></i>';
          }
      }
      return estrellas;
  }

  function obtenerClaseEstado(estado) {
      const clases = {
          'pendiente': 'bg-warning',
          'confirmado': 'bg-info',
          'enviado': 'bg-primary',
          'entregado': 'bg-success',
          'cancelado': 'bg-danger'
      };
      return clases[estado] || 'bg-secondary';
  }

  function calcularTiempoRelativo(fecha) {
      const ahora = new Date();
      const fechaActividad = new Date(fecha);
      const diferencia = Math.floor((ahora - fechaActividad) / 1000); // en segundos
      
      if (diferencia < 60) return 'Hace un momento';
      if (diferencia < 3600) return `Hace ${Math.floor(diferencia / 60)} minutos`;
      if (diferencia < 86400) return `Hace ${Math.floor(diferencia / 3600)} horas`;
      if (diferencia < 2592000) return `Hace ${Math.floor(diferencia / 86400)} días`;
      if (diferencia < 31536000) return `Hace ${Math.floor(diferencia / 2592000)} meses`;
      return `Hace ${Math.floor(diferencia / 31536000)} años`;
  }

  function obtenerIconoPorTipo(tipo) {
      const iconos = {
          'compra': 'fas fa-shopping-cart',
          'reseña': 'fas fa-star',
          'direccion': 'fas fa-map-marker-alt',
          'sistema': 'fas fa-cog',
          'perfil': 'fas fa-user-edit',
          'pago': 'fas fa-credit-card',
          'envio': 'fas fa-truck',
          'login': 'fas fa-sing-in-alt',
          'registro': 'fas fa-user-plus'
      };
      return iconos[tipo] || 'fas fa-circle';
  }

  function obtenerColorPorTipo(tipo) {
      const colores = {
          'compra': 'bg-primary',
          'reseña': 'bg-warning',
          'direccion': 'bg-danger',
          'sistema': 'bg-info',
          'perfil': 'bg-success'
      };
      return colores[tipo] || 'bg-secondary';
  }

  function actualizarEstadisticas(estadisticas) {
      $('#total-pedidos').text(estadisticas.total_pedidos);
      $('#total-resenas').text(estadisticas.total_resenas);
      $('#total-actualizaciones').text(estadisticas.total_actualizaciones);
      $('#total-direcciones').text(estadisticas.total_direcciones);
  }

  function mostrarActividadVacia() {
      $('#lista-actividad').hide();
      $('#actividad-vacia').show();
  }

  function mostrarError(mensaje) {
      Swal.fire({
          icon: 'error',
          title: 'Error',
          text: mensaje,
          confirmButtonText: 'Entendido'
      });
  }

  // Cargar actividad automáticamente si el tab está activo
  if ($('#activity').hasClass('active')) {
      cargarActividad();
  }


  //TAB PANEL CONFIGURACION *********************************************************

  // Eventos para formularios
  $('#form-notificaciones').on('submit', function(e) {
    e.preventDefault();
    guardarConfiguracion('notificaciones', obtenerDatosNotificaciones());
  });

  $('#form-privacidad').on('submit', function(e) {
      e.preventDefault();
      guardarConfiguracion('privacidad', obtenerDatosPrivacidad());
  });

  $('#form-visualizacion').on('submit', function(e) {
      e.preventDefault();
      guardarConfiguracion('visualizacion', obtenerDatosVisualizacion());
  });

  async function cargarConfiguracion() {
    console.log('Cargando configuración del usuario...');
    
    try {
        const response = await $.post('../Controllers/TabpanelConfigController.php', {
            funcion: 'cargar_configuracion'
        });
        
        const data = typeof response === 'string' ? JSON.parse(response) : response;
        
        if (data.error === 'no_sesion') {
            window.location.href = 'login.php';
            return;
        }
        
        if (data.success && data.configuraciones) {
            aplicarConfiguracion(data.configuraciones);
        } else {
            // Cargar configuración por defecto
            cargarConfiguracionPorDefecto();
        }
        
    } catch (error) {
        console.error('Error cargando configuración:', error);
        cargarConfiguracionPorDefecto();
    }
  }

  function aplicarConfiguracion(configuraciones) {
    console.log('Aplicando configuración:', configuraciones);
    
    // Aplicar configuración de notificaciones
    if (configuraciones.notificaciones) {
        $('#notificacion-email').prop('checked', configuraciones.notificaciones.email || true);
        $('#notificacion-pedidos').prop('checked', configuraciones.notificaciones.pedidos || true);
        $('#notificacion-promociones').prop('checked', configuraciones.notificaciones.promociones || false);
        $('#notificacion-productos').prop('checked', configuraciones.notificaciones.productos || false);
        $('#notificacion-resenas').prop('checked', configuraciones.notificaciones.resenas || true);
    }
    
    // Aplicar configuración de privacidad
    if (configuraciones.privacidad) {
        $('#privacidad-perfil').prop('checked', configuraciones.privacidad.perfil_publico || true);
        $('#privacidad-actividad').prop('checked', configuraciones.privacidad.actividad_publica || false);
        $('#privacidad-busqueda').prop('checked', configuraciones.privacidad.aparecer_busquedas || true);
        $('#privacidad-datos').prop('checked', configuraciones.privacidad.compartir_datos || true);
    }
    
    // Aplicar configuración de visualización
    if (configuraciones.visualizacion) {
        $('#tema-interface').val(configuraciones.visualizacion.tema || 'claro');
        $('#densidad-interface').val(configuraciones.visualizacion.densidad || 'normal');
        $('#idioma-interface').val(configuraciones.visualizacion.idioma || 'es');
        $('#moneda-interface').val(configuraciones.visualizacion.moneda || 'CUP');
    }
    
    // Aplicar cambios visuales inmediatos
    aplicarTema(configuraciones.visualizacion?.tema || 'claro');
  }

  function cargarConfiguracionPorDefecto() {
    console.log('Cargando configuración por defecto');
    
    // Configuración por defecto para notificaciones
    $('#notificacion-email').prop('checked', true);
    $('#notificacion-pedidos').prop('checked', true);
    $('#notificacion-promociones').prop('checked', false);
    $('#notificacion-productos').prop('checked', false);
    $('#notificacion-resenas').prop('checked', true);
    
    // Configuración por defecto para privacidad
    $('#privacidad-perfil').prop('checked', true);
    $('#privacidad-actividad').prop('checked', false);
    $('#privacidad-busqueda').prop('checked', true);
    $('#privacidad-datos').prop('checked', true);
    
    // Configuración por defecto para visualización
    $('#tema-interface').val('claro');
    $('#densidad-interface').val('normal');
    $('#idioma-interface').val('es');
    $('#moneda-interface').val('CUP');
  }

  function obtenerDatosNotificaciones() {
    return {
        email: $('#notificacion-email').is(':checked'),
        pedidos: $('#notificacion-pedidos').is(':checked'),
        promociones: $('#notificacion-promociones').is(':checked'),
        productos: $('#notificacion-productos').is(':checked'),
        resenas: $('#notificacion-resenas').is(':checked')
    };
  }

  function obtenerDatosPrivacidad() {
    return {
        perfil_publico: $('#privacidad-perfil').is(':checked'),
        actividad_publica: $('#privacidad-actividad').is(':checked'),
        aparecer_busquedas: $('#privacidad-busqueda').is(':checked'),
        compartir_datos: $('#privacidad-datos').is(':checked')
    };
  }

  function obtenerDatosVisualizacion() {
    return {
        tema: $('#tema-interface').val(),
        densidad: $('#densidad-interface').val(),
        idioma: $('#idioma-interface').val(),
        moneda: $('#moneda-interface').val()
    };
  }

  async function guardarConfiguracion(tipo, datos) {
    console.log(`Guardando configuración ${tipo}:`, datos);
    
    try {
        const response = await $.post('../Controllers/TabpanelConfigController.php', {
            funcion: 'guardar_configuracion',
            tipo_configuracion: tipo,
            datos_configuracion: JSON.stringify(datos)
        });
        
        const data = typeof response === 'string' ? JSON.parse(response) : response;
        
        if (data.success) {
            mostrarExito('Configuración guardada correctamente');
            
            // Aplicar cambios inmediatos si es configuración visual
            if (tipo === 'visualizacion') {
                aplicarTema(datos.tema);
                aplicarDensidad(datos.densidad);
            }
        } else {
            throw new Error(data.error || 'Error al guardar');
        }
        
    } catch (error) {
        console.error('Error guardando configuración:', error);
        mostrarError('Error al guardar la configuración');
    }
  }

  function aplicarTema(tema) {
    // Remover clases de tema existentes
    $('body').removeClass('tema-claro tema-oscuro');
    
    // Aplicar nuevo tema
    if (tema === 'oscuro') {
        $('body').addClass('tema-oscuro');
        // Aquí podrías cargar un CSS de tema oscuro
    } else {
        $('body').addClass('tema-claro');
    }
    
    // Guardar en localStorage para persistencia
    localStorage.setItem('tema-interface', tema);
  }

  function aplicarDensidad(densidad) {
    $('body').removeClass('densidad-comoda densidad-normal densidad-compacta');
    $('body').addClass(`densidad-${densidad}`);
    localStorage.setItem('densidad-interface', densidad);
  }

  // Cargar configuración automáticamente si el tab está activo
  if ($('#configuracion').hasClass('active')) {
    cargarConfiguracion();
  }

  // Función para cargar monedas desde la base de datos
async function obtener_monedas() {
  console.log('Iniciando obtener_monedas');
  try {
      const response = await $.post('../Controllers/MonedaController.php', {
          funcion: 'obtener_monedas'
      });
      
      const data = typeof response === 'string' ? JSON.parse(response) : response;
      
      if (data.success) {
          llenarSelectMonedas(data.monedas);
      } else {
          console.error('Error cargando monedas:', data.error);
      }
  } catch (error) {
      console.error('Error:', error);
  }
}

// Función para llenar el select de monedas
function llenarSelectMonedas(monedas) {
  const select = $('#moneda-interface');
  select.empty();
  
  monedas.forEach(moneda => {
      select.append(new Option(`${moneda.nombre} (${moneda.codigo})`, moneda.codigo));
  });
  
  // Establecer la moneda guardada en configuración
  const monedaGuardada = localStorage.getItem('moneda-seleccionada') || 'CUP';
  select.val(monedaGuardada);
}

// Evento cuando cambia la moneda
$('#moneda-interface').change(function() {
  const monedaSeleccionada = $(this).val();
  localStorage.setItem('moneda-seleccionada', monedaSeleccionada);
  
  // Llamar a la función global de actualización de precios
  actualizarPreciosMoneda(monedaSeleccionada);
});

// Función para obtener tasa de cambio (mantener por compatibilidad)
async function obtenerTasaCambio(codigoMoneda) {
  try {
      const response = await $.post('../Controllers/MonedaController.php', {
          funcion: 'obtener_tasa_cambio',
          moneda: codigoMoneda
      });
      
      const data = typeof response === 'string' ? JSON.parse(response) : response;
      return data.success ? data.tasa_cambio : 1;
  } catch (error) {
      console.error('Error obteniendo tasa:', error);
      return 1;
  }
}

// Función para actualizar precios
async function actualizarPreciosMoneda(codigoMoneda) {
    try {
        // Mostrar loading en los precios
        $('.precio-producto').html('<i class="fas fa-spinner fa-spin"></i>');
        
        const response = await $.post('../Controllers/MonedaController.php', {
            funcion: 'obtener_tasa_cambio',
            moneda: codigoMoneda
        });
        
        const data = typeof response === 'string' ? JSON.parse(response) : response;
        
        if (data.success) {
            // Aquí llamarás a la función que actualiza todos los precios en la página
            actualizarTodosLosPrecios(data.tasa_cambio, data.moneda);
        } else {
            console.error('Error obteniendo tasa de cambio:', data.error);
        }
    } catch (error) {
        console.error('Error actualizando precios:', error);
    }
}

// Función placeholder para actualizar precios (debes implementarla según tu lógica)
function actualizarTodosLosPrecios(tasaCambio, monedaInfo) {
    console.log('Actualizando precios con tasa:', tasaCambio, 'moneda:', monedaInfo);
    // Tu implementación aquí para actualizar los precios en la página
}
  
});


// Funciones globales para botones avanzados
async function exportarDatos() {
  console.log('Iniciando exportación de datos...');
  $('#modalExportarDatos').modal('show');
}

async function confirmarExportacion() {
  const formatos = [];
  if ($('#exportar-perfil').is(':checked')) formatos.push('perfil');
  if ($('#exportar-pedidos').is(':checked')) formatos.push('pedidos');
  if ($('#exportar-resenas').is(':checked')) formatos.push('resenas');
  if ($('#exportar-direcciones').is(':checked')) formatos.push('direcciones');
  if ($('#exportar-preferencias').is(':checked')) formatos.push('preferencias');
  
  const formato = $('#formato-exportacion').val();
  
  if (formatos.length === 0) {
      mostrarError('Selecciona al menos un tipo de dato para exportar');
      return;
  }
  
  try {
      const response = await $.post('../Controllers/TabpanelConfigController.php', {
          funcion: 'exportar_datos',
          formatos: formatos,
          formato: formato
      });
      
      const data = typeof response === 'string' ? JSON.parse(response) : response;
      
      if (data.success) {
          descargarArchivoExportacion(data.datos, data.formato);
          $('#modalExportarDatos').modal('hide');
          mostrarExito('Datos exportados correctamente');
      } else {
          throw new Error(data.error || 'Error en la exportación');
      }
      
  } catch (error) {
      console.error('Error exportando datos:', error);
      mostrarError('Error al exportar los datos');
  }
}

function descargarArchivoExportacion(datos, formato) {
  let contenido, tipoMime, extension;
  
  switch(formato) {
      case 'json':
          contenido = JSON.stringify(datos, null, 2);
          tipoMime = 'application/json';
          extension = 'json';
          break;
      case 'csv':
          contenido = convertirJSONaCSV(datos);
          tipoMime = 'text/csv';
          extension = 'csv';
          break;
      case 'pdf':
          // En un sistema real, generarías PDF en el servidor
          contenido = JSON.stringify(datos);
          tipoMime = 'application/pdf';
          extension = 'pdf';
          break;
      default:
          contenido = JSON.stringify(datos);
          tipoMime = 'application/octet-stream';
          extension = 'txt';
  }
  
  const blob = new Blob([contenido], { type: tipoMime });
  const url = URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href = url;
  a.download = `mis_datos_nexusbuy_${new Date().toISOString().split('T')[0]}.${extension}`;
  document.body.appendChild(a);
  a.click();
  document.body.removeChild(a);
  URL.revokeObjectURL(url);
}

function convertirJSONaCSV(datos) {
  // Implementación básica de conversión JSON a CSV
  let csv = '';
  
  if (datos.perfil) {
      csv += "PERFIL\n";
      csv += "Campo,Valor\n";
      Object.entries(datos.perfil).forEach(([key, value]) => {
          csv += `"${key}","${value}"\n`;
      });
      csv += "\n";
  }
  
  if (datos.pedidos) {
      csv += "PEDIDOS\n";
      csv += "Número,Fecha,Total,Estado\n";
      datos.pedidos.forEach(pedido => {
          csv += `"${pedido.numero_orden}","${pedido.fecha_creacion}","${pedido.total}","${pedido.estado}"\n`;
      });
      csv += "\n";
  }
  
  return csv;
}

function limpiarHistorial() {
  Swal.fire({
      title: '¿Limpiar historial local?',
      text: 'Esto eliminará datos temporales del navegador, pero no afectará tu cuenta.',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#ffc107',
      cancelButtonColor: '#6c757d',
      confirmButtonText: 'Sí, limpiar',
      cancelButtonText: 'Cancelar'
  }).then((result) => {
      if (result.isConfirmed) {
          localStorage.clear();
          sessionStorage.clear();
          mostrarExito('Historial local limpiado correctamente');
      }
  });
}

function restablecerPreferencias() {
  Swal.fire({
      title: '¿Restablecer preferencias?',
      text: 'Todas tus configuraciones volverán a los valores por defecto.',
      icon: 'question',
      showCancelButton: true,
      confirmButtonColor: '#17a2b8',
      cancelButtonColor: '#6c757d',
      confirmButtonText: 'Sí, restablecer',
      cancelButtonText: 'Cancelar'
  }).then((result) => {
      if (result.isConfirmed) {
          // Eliminar configuraciones guardadas
          localStorage.removeItem('tema-interface');
          localStorage.removeItem('densidad-interface');
          
          // Recargar la página para aplicar cambios
          location.reload();
      }
  });
}

function eliminarCuenta() {
  Swal.fire({
      title: '¿Eliminar cuenta permanentemente?',
      html: `
          <div class="text-left">
              <p class="text-danger"><strong>Esta acción no se puede deshacer</strong></p>
              <p>Se eliminarán todos tus datos:</p>
              <ul class="text-left">
                  <li>Información de perfil</li>
                  <li>Historial de pedidos</li>
                  <li>Reseñas y calificaciones</li>
                  <li>Direcciones guardadas</li>
                  <li>Preferencias y configuración</li>
              </ul>
              <p>Para confirmar, escribe <strong>ELIMINAR</strong> en el campo below:</p>
              <input type="text" id="confirmacion-eliminar" class="form-control" placeholder="ELIMINAR">
          </div>
      `,
      icon: 'error',
      showCancelButton: true,
      confirmButtonColor: '#dc3545',
      cancelButtonColor: '#6c757d',
      confirmButtonText: 'Eliminar cuenta permanentemente',
      cancelButtonText: 'Cancelar',
      preConfirm: () => {
          const confirmacion = $('#confirmacion-eliminar').val();
          if (confirmacion !== 'ELIMINAR') {
              Swal.showValidationMessage('Debes escribir ELIMINAR para confirmar');
          }
          return { confirmacion: confirmacion };
      }
  }).then(async (result) => {
      if (result.isConfirmed) {
          try {
              const response = await $.post('../Controllers/TabpanelConfigController.php', {
                  funcion: 'eliminar_cuenta',
                  confirmacion: result.value.confirmacion
              });
              
              const data = typeof response === 'string' ? JSON.parse(response) : response;
              
              if (data.success) {
                  Swal.fire({
                      icon: 'success',
                      title: 'Cuenta eliminada',
                      text: 'Tu cuenta ha sido eliminada correctamente',
                      confirmButtonText: 'Entendido'
                  }).then(() => {
                      window.location.href = '../index.php';
                  });
              } else {
                  throw new Error(data.error || 'Error al eliminar la cuenta');
              }
              
          } catch (error) {
              console.error('Error eliminando cuenta:', error);
              mostrarError('Error al eliminar la cuenta');
          }
      }
  });
}

function mostrarExito(mensaje) {
  Swal.fire({
      icon: 'success',
      title: 'Éxito',
      text: mensaje,
      timer: 3000,
      showConfirmButton: false
  });
}

function mostrarError(mensaje) {
  Swal.fire({
      icon: 'error',
      title: 'Error',
      text: mensaje,
      confirmButtonText: 'Entendido'
  });
}


  