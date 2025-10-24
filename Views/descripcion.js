$(document).ready(function () {
  
  verificar_producto();
  inicializarFormularioReseña();
  verificar_reseña_usuario();
  
  isJsonString();
  verificarEstadoFavorito();
  inicializarFuncionalidadCompartir();
  

  $('#btn-agregar-carrito').on('click', agregarAlCarrito);
  $('#btn-favorito').on('click', toggleFavorito);

    
  
  async function verificar_producto() {
    funcion = "verificar_producto";
    let data = await fetch("../Controllers/ProductoTiendaController.php", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: "funcion=" + funcion,
    });
    if (data.ok) {
      let response = await data.text();
      //console.log(response);
      try {
        let producto = JSON.parse(response);
        //console.log(producto);
        
        let template = "";
        if (producto.imagenes.length > 0) {
          template += `
                 <div class="col-12">
                   <img id="imagen_principal" src="../Util/Img/Producto/${producto.imagenes[0].nombre}" class="img-fluid">
                 </div>
                 <div class="col-12 product-image-thumbs">
                `;
          producto.imagenes.forEach((imagen) => {
            template += `
                            <button prod_img="${imagen.nombre}" class="imagen_pasarelas product-image-thumb">
                              <img src="../Util/Img/Producto/${imagen.nombre}" class="img-fluid">
                            </button>
                  `;
          });
          template += `
                          </div>`;
        } else {
          template += `
              <div class="col-12">
                <img id="imagen_principal" src="../Util/Img/Producto/${producto.imagen}" class="product-image img-fluid">
              </div>
             `;
        }
        $("#imagenes").html(template);
        $("#producto").text(producto.producto);
        $("#marca").text("Marca: " + producto.marca);
        $("#sku").text("SKU: " + producto.sku);
        let template1 = "";
        if (producto.calificacion != 0) {
          template1 += `</br>`;
          for (let index = 0; index < producto.calificacion; index++) {
            template1 += `<i class="fas fa-star text-warning"></i>`;
          }
          let estrellas_faltantes = 5 - producto.calificacion;
          for (let index = 0; index < estrellas_faltantes; index++) {
            template1 += `<i class="far fa-star text-warning"></i>`;
          }
          template1 += `</br>`;
        }
        if (producto.descuento != 0) {
          template1 += `
                          <span class="text-muted" style="text-decoration: line-through">$ ${producto.precio}</span>
                          <span class="text-muted">-${producto.descuento}%</span></br>
                `;
        }
        template1 += `           
                      <h4 class="text-danger">$ ${producto.precio_descuento}</h4>`;
        $("#informacion_precios").html(template1);
        let template2 = "";
        if (producto.envio == "gratis") {
          template2 += ` <i class="fas fa-truck-moving text-danger"></i>
                            <span class="ml-1"> Envio: </span>
                            <span class="badge bg-success">Envio gratis</span>`;
        } else {
          template2 += ` <i class="fas fa-truck-moving text-danger"></i>
                              <span class="ml-1"> Envio: </span>
                              <span class="mr-1"> $ 100</span>`;
        }
        template2 += `</br>`;
        template2 += `<i class="fas fa-store text-danger"></i>
                          <span class="ml-1">Recogelo en tienda: ${producto.direccion_tienda}</span>`;
        $("#informacion_envio").html(template2);
        $("#nombre_tienda").text(producto.tienda);
        $('#numero_reseñas').text(producto.numero_reseñas  + ' Reseñas');
        $("#promedio_calificacion_tienda").text(
          producto.promedio_calificacion_tienda
        );
        $("#product-desc").text(producto.detalles);
        let template3 = "";
        let cont = 0;
        producto.caracteristicas.forEach((caracteristica) => {
          cont++;
          template3 += `
                        <tr>
                        <td>${cont}</td>
                        <td>${caracteristica.titulo}</td>
                        <td>${caracteristica.descripcion}</td>
                        </tr>
                `;
        });
        $("#caracteristicas").html(template3);
        let template4 = "";
        producto.reseñas.forEach((reseña) => {
          template4 += `
                      <div class="card-comment">
                            <img class="img-circle img-sm" src="../Util/Img/Users/${reseña.avatar}" alt="User Image">

                            <div class="comment-text">
                              <span class="username">
                              ${reseña.usuario}
                              `;
          for (let index = 0; index < reseña.calificacion; index++) {
            template4 += `<i class="fas fa-star text-warning"></i> `;
          }
          let estrellas_faltantes = 5 - reseña.calificacion;
          for (let index = 0; index < estrellas_faltantes; index++) {
            template4 += `<i class="far fa-star text-warning"></i> `;
          }
          template4 += `
                              <span class="text-muted float-right"> ${reseña.fecha_creacion}</span>
                              </span>
                              ${reseña.descripcion}
                          </div>
                        </div>
                        
                      `;
        });

        $("#reseñas").html(template4);
        renderizarRedesSociales(producto);
      } catch (error) {
        console.error(error);
        console.log(response);
        if (response == "error") {
          location.href = "../index.php";
        }
      }
    } else {
      Swal.fire({
        icon: "error",
        title: data.statusText,
        text: "Hubo conflicto de codigo: " + data.status,
      });
    }
    inicializarFormularioReseña();
  }
  $(document).on("click", ".imagen_pasarelas", (e) => {
    let elemento = $(this)[0].activeElement;
    let img = $(elemento).attr("prod_img");
    $("#imagen_principal").attr("src", "../Util/Img/Producto/" + img);
  });
  function inicializarFormularioReseña() {
    if ($("#formulario_reseña form").length > 0) {
      $('calificacion-estrellas input[type="radio"]').on(
        "change",
        function () {
          const valor = $(this).val();
          $("calificacion-estrellas .estrella").removeClass("active");
          $(this).siblings(".estrella").addClass("active");
          $(this)
            .parent()
            .find(".estrella")
            .slice(5 - valor)
            .addClass("active");
        }
      );

      $('textarea[name="comentario"]').on("input", function () {
        const longitud = $(this).val().length;
        $("#contador-caracteres").text(longitud);
        if (longitud > 500) {
          $(this).val($(this).val().substring(0, 500));
          $("#contador-caracteres").text(500).addClass("text-danger");
        } else if (longitud > 450) {
          $("#contador-caracteres").addClass("text-warning");
        } else {
          $("#contador-caracteres").removeClass("text-warning text-danger");
        }
      });

      $("#form-reseña").on("submit", function (e) {
        e.preventDefault();
        enviarReseña();
      });
      verificar_reseña_usuario();
    }
  }
  async function verificar_reseña_usuario() {
    try {
      const id_producto = $("#id_producto_tienda").val();
      const funcion = "verificar_reseña_usuario";

      //console.log("Verificando reseña para producto:", id_producto); // Debug

      const response = await fetch("../Controllers/ReseñaController.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `funcion=${funcion}&id_producto_tienda=${id_producto}`,
      });

      if (response.ok) {
        const data = await response.text();
        //console.log("Respuesta del servidor:", data); // Debug

        // VERIFICAR SI LA RESPUESTA ES JSON VÁLIDO
        if (data.trim() === "") {
          console.log("Respuesta vacía del servidor");
          return;
        }

        try {
          const resultado = JSON.parse(data);

          if (resultado.ya_reseñado) {
            // Usuario ya hizo una reseña, deshabilitar formulario
            $("#form-reseña").html(`
                        <div class="alert alert-info">
                            <i class="fas fa-check-circle mr-2"></i>
                            Ya has publicado una reseña para este producto.
                            <br><strong>Tu calificación:</strong> ${resultado.calificacion} estrellas
                            <br><strong>Tu comentario:</strong> "${resultado.comentario}"
                        </div>
                    `);
          }
        } catch (parseError) {
          console.error("Error parseando JSON:", parseError);
          console.error("Datos recibidos:", data);

          // Si no es JSON, verificar si es un mensaje de error simple
          if (data === "no_sesion") {
            console.log("Usuario no tiene sesión activa");
          } else if (data === "error") {
            console.log("Error general del servidor");
          }
        }
      } else {
        console.error("Error HTTP:", response.status, response.statusText);
      }
    } catch (error) {
      console.error("Error de red:", error);
    }
  }
  async function enviarReseña() {
    const formulario = $("#form-reseña");
    const calificacion = $('input[name="calificacion"]:checked').val();
    const comentario = $('textarea[name="comentario"]').val().trim();
    const id_producto = $("#id_producto_tienda").val();

    // Validaciones
    if (!calificacion) {
      Swal.fire(
        "Error",
        "Por favor selecciona una calificación con estrellas",
        "warning"
      );
      return;
    }

    if (!comentario || comentario.length < 10) {
      Swal.fire(
        "Error",
        "El comentario debe tener al menos 10 caracteres",
        "warning"
      );
      return;
    }

    // Mostrar loading
    const boton = formulario.find('button[type="submit"]');
    const textoOriginal = boton.html();
    boton
      .prop("disabled", true)
      .html('<i class="fas fa-spinner fa-spin mr-2"></i>Enviando...');

    try {
      const funcion = "crear_reseña";
      const response = await fetch("../Controllers/ReseñaController.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `funcion=${funcion}&id_producto_tienda=${id_producto}&calificacion=${calificacion}&comentario=${encodeURIComponent(
          comentario
        )}`,
      });

      const resultado = await response.text();
      console.log("Respuesta del servidor (crear_reseña):", resultado); // Debug

      // Manejar respuesta sin intentar parsear JSON
      if (resultado === "success") {
        Swal.fire({
          icon: "success",
          title: "¡Reseña publicada!",
          text: "Tu reseña ha sido publicada exitosamente",
          timer: 2000,
          showConfirmButton: false,
        }).then(() => {
          // Recargar la página para mostrar la nueva reseña
          location.reload();
        });
      } else {
        manejarErrorReseña(resultado);
      }
    } catch (error) {
      console.error("Error:", error);
      Swal.fire("Error", "Ha ocurrido un error al publicar tu reseña", "error");
    } finally {
      boton.prop("disabled", false).html(textoOriginal);
    }
  }
  function manejarErrorReseña(codigoError) {
    switch (codigoError) {
      case "no_sesion":
        Swal.fire(
          "Sesión requerida",
          "Debes iniciar sesión para publicar una reseña",
          "warning"
        );
        break;
      case "ya_reseñado":
        Swal.fire(
          "Ya has reseñado",
          "Solo puedes publicar una reseña por producto",
          "info"
        );
        break;
      case "error_calificacion":
        Swal.fire(
          "Error",
          "La calificación debe ser entre 1 y 5 estrellas",
          "warning"
        );
        break;
      case "error_comentario":
        Swal.fire(
          "Error",
          "El comentario es requerido y debe tener al menos 10 caracteres",
          "warning"
        );
        break;
      default:
        Swal.fire(
          "Error",
          "Ha ocurrido un error al publicar tu reseña",
          "error"
        );
    }
  }
  // Función de debug para respuestas del servidor
  function debugResponse(response, data) {
    console.group("🔍 DEBUG RESPONSE");
    console.log("Status:", response.status);
    console.log("Status Text:", response.statusText);
    console.log("Headers:", response.headers);
    console.log("Data received:", data);
    console.log("Data type:", typeof data);
    console.log("Is JSON?", isJsonString(data));
    console.groupEnd();
  }
   // Función para verificar si un string es JSON válido
   function isJsonString(str) {
    try {
      JSON.parse(str);
    } catch (e) {
      return false;
    }
    return true;
  }

  // Función para agregar al carrito
  async function agregarAlCarrito() {
    try {
        const id_producto_tienda = $("#id_producto_tienda").val();
        const funcion = "agregar_al_carrito";
        
        console.log("Intentando agregar al carrito:", id_producto_tienda);

        const response = await fetch("../Controllers/CarritoController.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: `funcion=${funcion}&id_producto_tienda=${id_producto_tienda}&cantidad=1`
        });

        const resultado = await response.json();
        console.log("Respuesta del servidor:", resultado);

        if (resultado.success) {
            Swal.fire({
                icon: 'success',
                title: '¡Producto agregado!',
                text: resultado.mensaje,
                timer: 2000,
                showConfirmButton: false
            });
            
            // Actualizar contador del carrito
            actualizarContadorCarrito();
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: resultado.error || 'No se pudo agregar el producto al carrito'
            });
        }
    } catch (error) {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Error de conexión al agregar al carrito'
        });
    }
    actualizarContadorCarrito()
  }

  // Función para manejar favoritos
  async function toggleFavorito() {
    const idProducto = $('#btn-favorito').data('producto-id');
    const esFavorito = $('#btn-favorito').hasClass('btn-danger');
    
    if (!idProducto) {
        Swal.fire('Error', 'ID de producto no válido', 'error');
        return;
    }

    try {
        const funcion = esFavorito ? 'eliminar_favorito' : 'agregar_favorito';
        
        const response = await fetch('../Controllers/FavoritoController.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `funcion=${funcion}&id_producto_tienda=${encodeURIComponent(idProducto)}`
        });

        const data = await response.json();
        
        if (data.success) {
            // Cambiar apariencia del botón
            if (esFavorito) {
                $('#btn-favorito').removeClass('btn-danger').addClass('btn-outline-danger');
                $('#btn-favorito i').removeClass('fas').addClass('far');
                $('#texto-favorito').text('En Favoritos');
            } else {
                $('#btn-favorito').removeClass('btn-outline-danger').addClass('btn-danger');
                $('#btn-favorito i').removeClass('far').addClass('fas');
                $('#texto-favorito').text('Añadir a Favoritos');
            }
            
            Swal.fire({
                icon: 'success',
                title: esFavorito ? 'Eliminado' : '¡Agregado!',
                text: data.mensaje,
                timer: 1500,
                showConfirmButton: false
            });
        } else {
            if (data.error === 'no_sesion') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Inicia sesión',
                    text: 'Debes iniciar sesión para gestionar favoritos',
                    showCancelButton: true,
                    confirmButtonText: 'Iniciar sesión',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = 'login.php';
                    }
                });
            } else {
                Swal.fire('Error', data.error || 'Operación fallida', 'error');
            }
        }
    } catch (error) {
        console.error('Error:', error);
        Swal.fire('Error', 'Error de conexión', 'error');
    }
  }

  function inicializarFuncionalidadCompartir() {
    // Evento para el botón principal de compartir (abre modal)
    $('#btn-compartir').on('click', function() {
        $('#modalCompartir').modal('show');
    });

    // Generar enlace actual cuando se abre el modal
    $('#modalCompartir').on('show.bs.modal', function() {
        const enlaceProducto = generarEnlaceProducto();
        $('#enlace-producto').val(enlaceProducto);
    });

    // Botón Facebook
    $('#btn-facebook').on('click', function() {
        compartirFacebook();
    });

    // Botón Twitter
    $('#btn-twitter').on('click', function() {
        compartirTwitter();
    });

    // Botón WhatsApp
    $('#btn-whatsapp').on('click', function() {
        compartirWhatsApp();
    });

    // Botón Copiar (grande)
    $('#btn-copiar').on('click', function() {
        copiarEnlace();
    });

    // Botón Copiar (pequeño del input)
    $('#btn-copiar-input').on('click', function() {
        copiarEnlace();
    });
}

// Función para generar el enlace del producto
function generarEnlaceProducto() {
    const nombreProducto = $('#producto').text().trim() || $('#btn-compartir').data('producto-nombre');
    const idProducto = $('#id_producto_tienda').val();
    
    // Crear enlace canónico
    const urlBase = window.location.origin + window.location.pathname;
    const enlace = `${urlBase}?id=${idProducto}&name=${encodeURIComponent(nombreProducto)}`;
    
    return enlace;
}

// Función para compartir en Facebook
function compartirFacebook() {
    const enlace = generarEnlaceProducto();
    const nombreProducto = $('#producto').text().trim() || $('#btn-compartir').data('producto-nombre');
    
    const url = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(enlace)}&quote=${encodeURIComponent(`Mira este producto: ${nombreProducto}`)}`;
    
    abrirVentanaCompartir(url, 'Compartir en Facebook');
    mostrarMensajeExito('Compartiendo en Facebook...');
}

// Función para compartir en Twitter
function compartirTwitter() {
    const enlace = generarEnlaceProducto();
    const nombreProducto = $('#producto').text().trim() || $('#btn-compartir').data('producto-nombre');
    const texto = `¡Mira este producto! ${nombreProducto}`;
    
    const url = `https://twitter.com/intent/tweet?text=${encodeURIComponent(texto)}&url=${encodeURIComponent(enlace)}`;
    
    abrirVentanaCompartir(url, 'Compartir en Twitter');
    mostrarMensajeExito('Compartiendo en Twitter...');
}

// Función para compartir en WhatsApp
function compartirWhatsApp() {
    const enlace = generarEnlaceProducto();
    const nombreProducto = $('#producto').text().trim() || $('#btn-compartir').data('producto-nombre');
    const texto = `¡Mira este producto! ${nombreProducto} - ${enlace}`;
    
    const url = `https://wa.me/?text=${encodeURIComponent(texto)}`;
    
    abrirVentanaCompartir(url, 'Compartir en WhatsApp');
    mostrarMensajeExito('Compartiendo en WhatsApp...');
}

// Función auxiliar para abrir ventana de compartir
function abrirVentanaCompartir(url, titulo) {
    const ancho = 600;
    const alto = 400;
    const left = (screen.width - ancho) / 2;
    const top = (screen.height - alto) / 2;
    
    window.open(url, titulo, `width=${ancho},height=${alto},left=${left},top=${top},resizable=yes,scrollbars=yes`);
    
    // Cerrar modal después de compartir
    $('#modalCompartir').modal('hide');
}

// Función para copiar enlace al portapapeles
function copiarEnlace() {
    const enlaceInput = $('#enlace-producto');
    const texto = enlaceInput.val();
    
    // Seleccionar el texto
    enlaceInput.select();
    enlaceInput[0].setSelectionRange(0, 99999); // Para móviles
    
    try {
        // Intentar usar la API moderna del portapapeles
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(texto).then(function() {
                mostrarMensajeExito('¡Enlace copiado al portapapeles!');
                actualizarIconoCopiar();
            }).catch(function(err) {
                // Fallback para navegadores más antiguos
                fallbackCopiarTexto(texto);
            });
        } else {
            // Fallback para navegadores que no soportan la API moderna
            fallbackCopiarTexto(texto);
        }
    } catch (err) {
        // Fallback para navegadores que no soportan la API moderna
        fallbackCopiarTexto(texto);
    }
}

// Fallback para copiar texto
function fallbackCopiarTexto(texto) {
    const areaTexto = document.createElement('textarea');
    areaTexto.value = texto;
    areaTexto.style.position = 'fixed';
    areaTexto.style.left = '-999999px';
    areaTexto.style.top = '-999999px';
    document.body.appendChild(areaTexto);
    areaTexto.focus();
    areaTexto.select();
    
    try {
        const exitoso = document.execCommand('copy');
        document.body.removeChild(areaTexto);
        
        if (exitoso) {
            mostrarMensajeExito('¡Enlace copiado al portapapeles!');
            actualizarIconoCopiar();
        } else {
            mostrarMensajeError('Error al copiar el enlace');
        }
    } catch (err) {
        document.body.removeChild(areaTexto);
        mostrarMensajeError('Error al copiar el enlace');
    }
}

// Función para actualizar el icono de copiar temporalmente
function actualizarIconoCopiar() {
    const btn = $('#btn-copiar-input');
    const iconoOriginal = btn.html();
    
    btn.html('<i class="fas fa-check"></i>')
       .removeClass('btn-outline-secondary')
       .addClass('btn-success');
    
    setTimeout(() => {
        btn.html(iconoOriginal)
           .removeClass('btn-success')
           .addClass('btn-outline-secondary');
    }, 2000);
}

// Función para mostrar mensaje de éxito
function mostrarMensajeExito(mensaje) {
    Swal.fire({
        icon: 'success',
        title: '¡Éxito!',
        text: mensaje,
        timer: 2000,
        showConfirmButton: false,
        position: 'top-end',
        toast: true
    });
}

// Función para mostrar mensaje de error
function mostrarMensajeError(mensaje) {
    Swal.fire({
        icon: 'error',
        title: 'Error',
        text: mensaje,
        timer: 3000,
        showConfirmButton: false,
        position: 'top-end',
        toast: true
    });
}

// API de compartir nativa (opcional - para móviles)
function compartirNativo() {
    const nombreProducto = $('#producto').text().trim() || $('#btn-compartir').data('producto-nombre');
    const enlace = generarEnlaceProducto();
    
    if (navigator.share) {
        navigator.share({
            title: nombreProducto,
            text: `¡Mira este producto: ${nombreProducto}!`,
            url: enlace,
        })
        .then(() => {
            mostrarMensajeExito('¡Producto compartido exitosamente!');
        })
        .catch((error) => {
            console.log('Error al compartir:', error);
            // Si falla el sharing nativo, abrir modal normal
            $('#modalCompartir').modal('show');
        });
    } else {
        // Navegadores que no soportan la API de compartir
        $('#modalCompartir').modal('show');
    }
}

// Si quieres usar la API nativa en móviles, puedes cambiar el evento principal:
function inicializarCompartirModerno() {
    $('#btn-compartir').on('click', function() {
        // En móviles, usar API nativa si está disponible
        if (navigator.share) {
            compartirNativo();
        } else {
            // En desktop, usar modal normal
            $('#modalCompartir').modal('show');
        }
    });
}

  // Función para verificar estado de favorito al cargar la página
  async function verificarEstadoFavorito() {
    const idProducto = $('#btn-favorito').data('producto-id');
    
    if (!idProducto) return;

    try {
        const response = await fetch('../Controllers/FavoritoController.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `funcion=verificar_favorito&id_producto_tienda=${encodeURIComponent(idProducto)}`
        });

        const data = await response.json();
        
        if (data.es_favorito) {
            $('#btn-favorito').removeClass('btn-outline-danger').addClass('btn-danger');
            $('#btn-favorito i').removeClass('far').addClass('fas');
            $('#texto-favorito').text('En Favoritos');
        }
    } catch (error) {
        console.error('Error verificando favorito:', error);
    }
  }

  // Función para actualizar contador del carrito
  async function actualizarContadorCarrito() {
    try {
        const response = await fetch("../Controllers/CarritoController.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: "funcion=obtener_cantidad_total"
        });
        
        const resultado = await response.json();
        if (resultado.cantidad_total > 0) {
            // Actualizar el badge del carrito en tu navbar
            $('.carrito-badge').text(resultado.cantidad_total).show();
        }
    } catch (error) {
        console.error('Error actualizando contador:', error);
    }
  }

  // Función para renderizar las redes sociales de la tienda
function renderizarRedesSociales(producto) {
  const redesSociales = producto.redes_sociales || {};
  let template5 = '';

  // Facebook
  if (redesSociales.facebook) {
      template5 += `
          <a href="${redesSociales.facebook}" target="_blank" class="text-gray" title="Síguenos en Facebook">
              <i class="fab fa-facebook-square fa-2x"></i>
          </a>
      `;
  }

  // Instagram
  if (redesSociales.instagram) {
      template5 += `
          <a href="${redesSociales.instagram}" target="_blank" class="text-gray ml-2" title="Síguenos en Instagram">
              <i class="fab fa-instagram fa-2x"></i>
          </a>
      `;
  }

  // TikTok
  if (redesSociales.tiktok) {
      template5 += `
          <a href="${redesSociales.tiktok}" target="_blank" class="text-gray ml-2" title="Síguenos en TikTok">
              <i class="fab fa-tiktok fa-2x"></i>
          </a>
      `;
  }

  // YouTube
  if (redesSociales.youtube) {
      template5 += `
          <a href="${redesSociales.youtube}" target="_blank" class="text-gray ml-2" title="Suscríbete en YouTube">
              <i class="fab fa-youtube fa-2x"></i>
          </a>
      `;
  }

  // WhatsApp
  if (redesSociales.whatsapp) {
      const mensaje = `Hola, me interesa el producto: ${producto.producto}`;
      template5 += `
          <a href="https://wa.me/${redesSociales.whatsapp}?text=${encodeURIComponent(mensaje)}" target="_blank" class="text-gray ml-2" title="Contáctanos por WhatsApp">
              <i class="fab fa-whatsapp fa-2x"></i>
          </a>
      `;
  }

  // Email
  if (redesSociales.email) {
      const asunto = `Consulta sobre: ${producto.producto}`;
      template5 += `
          <a href="mailto:${redesSociales.email}?subject=${encodeURIComponent(asunto)}" class="text-gray ml-2" title="Escríbenos un email">
              <i class="fas fa-envelope fa-2x"></i>
          </a>
      `;
  }

  // Sitio Web
  if (redesSociales.sitio_web) {
      template5 += `
          <a href="${redesSociales.sitio_web}" target="_blank" class="text-gray ml-2" title="Visita nuestro sitio web">
              <i class="fas fa-globe fa-2x"></i>
          </a>
      `;
  }

  // Si no hay redes sociales, mostrar mensaje
  if (template5 === '') {
      template5 = `
          <span class="text-muted small">Redes sociales no disponibles</span>
      `;
  }

  $('.product-share').html(template5);
}
});
