$(document).ready(function () {
  //console.log('Carrito.js inicializado');
  let funcion;
  let carritoItems = []; // Usar carritoItems consistentemente
  let seleccionados = new Set();

  // Inicializar carrito
  inicializarCarrito();
  

  async function inicializarCarrito() {
      //console.log('Inicializando carrito...');
      try {
          
          await obtener_carrito();
          inicializarEventos();
          //console.log('Carrito inicializado correctamente');
      } catch (error) {
          console.error('Error inicializando carrito:', error);
          mostrarCarritoVacio();
      }
  }

  

  async function obtener_carrito() {
      //console.log('Obteniendo carrito...');
      funcion = 'obtener_carrito';
      try {
          // Mostrar loading
          $('#articulos').html(`
              <div class="text-center py-5">
                  <div class="spinner-border text-primary" role="status">
                      <span class="sr-only">Cargando carrito...</span>
                  </div>
                  <p class="text-muted mt-2">Cargando tu carrito...</p>
              </div>
          `);

          const response = await $.post('../Controllers/CarritoController.php', { funcion });
          //console.log('Respuesta carrito RAW:', response);
          
          // Verificar si es string y parsear
          let data;
          if (typeof response === 'string') {
              try {
                  data = JSON.parse(response);
              } catch (e) {
                  console.error('Error parseando respuesta:', e);
                  if (response === 'no_sesion' || response.includes('no_sesion')) {
                      mostrarCarritoVacio();
                      return;
                  }
                  throw new Error('Respuesta inválida del servidor');
              }
          } else {
              data = response;
          }

          // Verificar si hay error en la respuesta
          if (data && data.error) {
              if (data.error === 'no_sesion') {
                  console.log('Usuario no tiene sesión');
                  mostrarCarritoVacio();
                  return;
              } else {
                  throw new Error(data.error);
              }
          }

          // Verificar que data es un array
          if (Array.isArray(data)) {
              carritoItems = data; // CORRECCIÓN: Usar carritoItems, no items
              //console.log('Items en carrito:', carritoItems.length);
              
              if (carritoItems.length === 0) {
                  mostrarCarritoVacio();
              } else {
                  renderizarCarrito();
                  await actualizarResumen();
              }
              actualizarBadgePorCambios();
          } else {
              console.error('Respuesta no es array:', data);
              mostrarCarritoVacio();
          }
      } catch (error) {
          console.error('Error obteniendo carrito:', error);
          mostrarError('No se pudo cargar el carrito. Intenta nuevamente.');
          mostrarCarritoVacio();
      }
  }

  function renderizarCarrito() {
    //   console.log('=== INICIANDO RENDERIZADO ===');
    //   console.log('CarritoItems para renderizar:', carritoItems);
    //   console.log('Número de items:', carritoItems.length);
      
      if (carritoItems.length === 0) {
          console.log('No hay items, mostrando carrito vacío');
          mostrarCarritoVacio();
          return;
      }

      let template = '';
      
      carritoItems.forEach((item, index) => {
          //console.log(`Procesando item ${index}:`, item);
          
          // Validaciones y valores por defecto
          const precioFinal = parseFloat(item.precio) || 0;
          const precioOriginal = parseFloat(item.precio_unitario) || precioFinal;
          const cantidad = parseInt(item.cantidad_producto) || 1;
          const stock = parseInt(item.cantidad) || 10;
          const tieneDescuento = parseFloat(item.descuento_porcentaje) > 0;
          const nombre = item.nombre || 'Producto sin nombre';
          const imagen = item.imagen || 'producto_default.png';
          const detalles = item.detalles || 'Sin descripción adicional';
          const tiendaNombre = item.tienda_nombre || 'Tienda no especificada';

          template += `
              <div class="card mb-3 articulo-item" data-id="${item.id}" data-index="${index}">
                  <div class="card-body">
                      <div class="row align-items-center">
                          <div class="col-md-1">
                              <input type="checkbox" class="seleccionar-item form-check-input" 
                                     data-id="${item.id}" data-precio="${precioFinal}">
                          </div>
                          <div class="col-md-2">
                              <img src="../Util/Img/Producto/${imagen}" 
                                   alt="${nombre}" 
                                   class="img-fluid rounded" 
                                   style="max-height: 120px; object-fit: cover;"
                                   onerror="this.src='../Util/Img/Producto/producto_default.png'">
                          </div>
                          <div class="col-md-7">
                              <h5 class="producto-nombre mb-1">${nombre}</h5>
                              <p class="text-muted small mb-2">${detalles}</p>
                              
                              <p class="text-muted small mb-2">
                                  <i class="fas fa-store"></i> Vendido por: ${tiendaNombre}
                              </p>
                              
                              ${tieneDescuento ? `
                                  <div class="mb-2">
                                      <span class="text-muted text-decoration-line-through me-2">$ ${precioOriginal.toFixed(2)}</span>
                                      <span class="badge bg-danger">-${item.descuento_porcentaje}%</span>
                                  </div>
                              ` : ''}
                              
                              <div class="d-flex align-items-center mb-2">
                                  <span class="me-2">Cantidad:</span>
                                  <div class="input-group input-group-sm" style="width: 140px;">
                                      <button class="btn btn-outline-secondary disminuir-cantidad" type="button" 
                                              data-id="${item.id}" ${cantidad <= 1 ? 'disabled' : ''}>
                                          <i class="fas fa-minus"></i>
                                      </button>
                                      <input type="number" class="form-control text-center cantidad-input" 
                                             value="${cantidad}" min="1" max="${stock}" 
                                             data-id="${item.id}" data-precio="${precioFinal}">
                                      <button class="btn btn-outline-secondary aumentar-cantidad" type="button" 
                                              data-id="${item.id}" ${cantidad >= stock ? 'disabled' : ''}>
                                          <i class="fas fa-plus"></i>
                                      </button>
                                  </div>
                                  <small class="text-muted ms-2">Disponible: ${stock}</small>
                              </div>
                              
                              <div class="precio-producto">
                                  <strong class="text-danger h5">$ ${(precioFinal * cantidad).toFixed(2)}</strong>
                                  <small class="text-muted d-block">$ ${precioFinal.toFixed(2)} c/u</small>
                              </div>
                          </div>
                          <div class="col-md-2 text-center">
                              <button type="button" class="btn btn-outline-danger btn-sm eliminar-item" 
                                      data-id="${item.id}" data-nombre="${nombre}">
                                  <i class="fas fa-trash"></i> Eliminar
                              </button>
                          </div>
                      </div>
                  </div>
              </div>
          `;
      });

      //console.log('Template generado, insertando en DOM...');
      
      // Verificar que el contenedor existe
      const $articulos = $('#articulos');
      if ($articulos.length === 0) {
          console.error('ERROR: No se encontró el elemento #articulos');
          return;
      }
      
      $articulos.html(template);
      
      // Verificar que se insertó correctamente
      const itemsInsertados = $('.articulo-item').length;
      //console.log(`Items insertados en DOM: ${itemsInsertados}`);
      
      actualizarEstadoBotones();
      //console.log('=== RENDERIZADO COMPLETADO ===');
  }

  function mostrarCarritoVacio() {
      //console.log('Mostrando carrito vacío');
      const template = `
          <div class="text-center py-5">
              <div class="empty-cart-icon mb-3">
                  <i class="fas fa-shopping-cart fa-4x text-muted"></i>
              </div>
              <h4 class="text-muted">Tu carrito está vacío</h4>
              <p class="text-muted mb-4">Agrega algunos productos para comenzar a comprar</p>
              <a href="../index.php" class="btn btn-primary">
                  <i class="fas fa-shopping-bag mr-2"></i>Descubrir productos
              </a>
          </div>
      `;
      $('#articulos').html(template);
      $('#btn-pagar').prop('disabled', true);
      actualizarResumenCero();
  }

  function actualizarResumenCero() {
      $('#subtotal').text('$ 0.00');
      $('#envio').text('$ 0.00');
      $('#descuento').text('$ 0.00');
      $('#total').text('$ 0.00');
      $('#btn-pagar').text('Proceder al pago');
  }

  function inicializarEventos() {
      //console.log('Inicializando eventos...');
      
      // Seleccionar/deseleccionar items
      $(document).on('change', '.seleccionar-item', function() {
          const itemId = $(this).data('id').toString();
          const precio = parseFloat($(this).data('precio'));
          
          console.log('Checkbox cambiado:', itemId, 'checked:', $(this).is(':checked'));
          
          if ($(this).is(':checked')) {
              seleccionados.add(itemId);
          } else {
              seleccionados.delete(itemId);
          }
          
          actualizarSeleccionTodos();
          actualizarResumen();
      });

      // Seleccionar todos
      $(document).on('change', '#seleccionar_items', function() {
          const isChecked = $(this).is(':checked');
          console.log('Seleccionar todos:', isChecked);
          $('.seleccionar-item').prop('checked', isChecked).trigger('change');
      });

      $(document).on('click', '#btn-vaciar-carrito', function() {
        console.log('Vaciar carrito clickeado');
        vaciarCarrito();
        });
        async function vaciarCarrito() {
            try {
                const result = await Swal.fire({
                    title: '¿Vaciar carrito completo?',
                    text: '¿Estás seguro de que quieres eliminar todos los productos de tu carrito?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Sí, vaciar todo',
                    cancelButtonText: 'Cancelar',
                    reverseButtons: true
                });
        
                if (result.isConfirmed) {
                    // Mostrar loading
                    const loadingSwal = Swal.fire({
                        title: 'Vaciando carrito...',
                        text: 'Por favor espera',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
        
                    funcion = 'vaciar_carrito';
                    const response = await $.post('../Controllers/CarritoController.php', { funcion });
                    
                    await loadingSwal.close();
        
                    console.log('Respuesta vaciar carrito:', response);
        
                    let data;
                    if (typeof response === 'string') {
                        try {
                            data = JSON.parse(response);
                        } catch (e) {
                            data = { error: 'Error parseando respuesta' };
                        }
                    } else {
                        data = response;
                    }
        
                    if (data.success) {
                        // Limpiar todo
                        carritoItems = [];
                        seleccionados.clear();
                        actualizarBadgePorCambios();
                        mostrarCarritoVacio();
                        
                        await Swal.fire({
                            icon: 'success',
                            title: 'Carrito vaciado',
                            text: 'Todos los productos han sido removidos de tu carrito',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    } else {
                        throw new Error(data.error || 'Error al vaciar carrito');
                    }
                }
            } catch (error) {
                console.error('Error vaciando carrito:', error);
                mostrarError('Error al vaciar el carrito: ' + error.message);
            }
        }

            // Aumentar cantidad
            $(document).on('click', '.aumentar-cantidad', async function() {
                const itemId = $(this).data('id');
                console.log('Aumentar cantidad:', itemId);
                
                const input = $(`.cantidad-input[data-id="${itemId}"]`);
                const max = parseInt(input.attr('max'));
                let valor = parseInt(input.val());
                
                if (valor < max) {
                    valor++;
                    input.val(valor);
                    await actualizarCantidad(itemId, valor);
                } else {
                    mostrarError('No hay más stock disponible');
                }
                
                actualizarEstadoBotones();
            });

            // Disminuir cantidad
            $(document).on('click', '.disminuir-cantidad', async function() {
                const itemId = $(this).data('id');
                console.log('Disminuir cantidad:', itemId);
                
                const input = $(`.cantidad-input[data-id="${itemId}"]`);
                let valor = parseInt(input.val());
                
                if (valor > 1) {
                    valor--;
                    input.val(valor);
                    await actualizarCantidad(itemId, valor);
                }
                
                actualizarEstadoBotones();
            });

            // Eliminar item
            $(document).on('click', '.eliminar-item', function() {
                const itemId = $(this).data('id');
                const nombre = $(this).data('nombre');
                console.log('Eliminar item:', itemId, nombre);
                eliminarItem(itemId, nombre);
            });

            // Porceder al pago
            $(document).on('click', '#btn-pagar', function (e) {
                console.log('Botón pagar clickeado');
                e.preventDefault();
                e.stopPropagation();
                procederAlPago();

            })

            //console.log('Eventos inicializados');
    }

  async function actualizarCantidad(itemId, nuevaCantidad) {
      console.log('Actualizando cantidad:', itemId, 'a', nuevaCantidad);
      
      try {
          funcion = 'actualizar_cantidad';
          const response = await $.post('../Controllers/CarritoController.php', {
              funcion,
              id_carrito_detalle: itemId,
              cantidad: nuevaCantidad
          });

          console.log('Respuesta actualizar cantidad:', response);

          let data;
          if (typeof response === 'string') {
              try {
                  data = JSON.parse(response);
              } catch (e) {
                  data = { error: 'Error parseando respuesta' };
              }
          } else {
              data = response;
          }

          if (data.success) {
              // Actualizar en el array local
              const item = carritoItems.find(item => item.id == itemId);
              if (item) {
                  item.cantidad_producto = nuevaCantidad;
                  
                  // Recalcular subtotal del item
                  const itemElement = $(`.articulo-item[data-id="${itemId}"]`);
                  const precioUnitario = parseFloat(itemElement.find('.seleccionar-item').data('precio'));
                  const subtotal = precioUnitario * nuevaCantidad;
                  
                  itemElement.find('.precio-producto strong').text(`$ ${subtotal.toFixed(2)}`);
                  
                  // Actualizar selección si está seleccionado
                  if (itemElement.find('.seleccionar-item').is(':checked')) {
                      await actualizarResumen();
                  }
              }
              console.log('Cantidad actualizada correctamente');
          } else {
              throw new Error(data.error || 'Error al actualizar cantidad');
          }
      } catch (error) {
          console.error('Error actualizando cantidad:', error);
          mostrarError(error.message || 'Error al actualizar la cantidad');
          await obtener_carrito(); // Recargar carrito
      }
  }

    async function eliminarItem(itemId, nombre) {
        try {
            const result = await Swal.fire({
                title: '¿Eliminar producto?',
                text: `¿Estás seguro de que quieres eliminar "${nombre}" del carrito?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            });

            if (result.isConfirmed) {
                funcion = 'eliminar_del_carrito';
                const response = await $.post('../Controllers/CarritoController.php', {
                    funcion,
                    id_carrito_detalle: itemId
                });

                if (response.success) {
                    // Eliminar del array local
                    carritoItems = carritoItems.filter(item => item.id != itemId);
                    
                    // Eliminar de seleccionados
                    seleccionados.delete(itemId.toString());

                    // Actualizar badge despues de eliminar
                    actualizarBadgePorCambios();
                    
                    // Re-renderizar
                    if (carritoItems.length === 0) {
                        mostrarCarritoVacio();
                    } else {
                        renderizarCarrito();
                        await actualizarResumen();
                    }
                    
                    await Swal.fire({
                        icon: 'success',
                        title: 'Producto eliminado',
                        text: 'El producto ha sido removido de tu carrito',
                        timer: 1500,
                        showConfirmButton: false
                    });
                } else {
                    throw new Error(response.error || 'Error al eliminar producto');
                }
            }
        } catch (error) {
            console.error('Error eliminando item:', error);
            mostrarError('Error al eliminar el producto del carrito');
        }
    }

    function actualizarEstadoBotones() {
        carritoItems.forEach(item => {
            const btnAumentar = $(`.aumentar-cantidad[data-id="${item.id}"]`);
            const btnDisminuir = $(`.disminuir-cantidad[data-id="${item.id}"]`);
            
            btnAumentar.prop('disabled', item.cantidad_producto >= item.cantidad);
            btnDisminuir.prop('disabled', item.cantidad_producto <= 1);
        });
    }

    function actualizarSeleccionTodos() {
        const totalItems = $('.seleccionar-item').length;
        const selectedItems = $('.seleccionar-item:checked').length;
        
        $('#seleccionar_items').prop('checked', totalItems > 0 && totalItems === selectedItems);
    }

    async function actualizarResumen() {
        let subtotal = 0;
        
        seleccionados.forEach(itemId => {
            const item = carritoItems.find(item => item.id == itemId);
            if (item) {
                subtotal += (item.precio || 0) * (item.cantidad_producto || 1);
            }
        });
        
        const envio = subtotal > 1000 ? 0 : 250;
        const descuento = 0;
        const total = subtotal + envio - descuento;
        
        $('#subtotal').text(`$ ${subtotal.toFixed(2)}`);
        $('#envio').text(envio === 0 ? 'GRATIS' : `$ ${envio.toFixed(2)}`);
        $('#descuento').text(`$ ${descuento.toFixed(2)}`);
        $('#total').text(`$ ${total.toFixed(2)}`);
        
        const tieneSeleccionados = seleccionados.size > 0;
        $('#btn-pagar').prop('disabled', !tieneSeleccionados);
        
        if (tieneSeleccionados) {
            $('#btn-pagar').text(`Proceder al pago (${seleccionados.size} productos)`);
        } else {
            $('#btn-pagar').text('Proceder al pago');
        }
    }

    function mostrarError(mensaje) {
        console.error('Mostrando error:', mensaje);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: mensaje,
            timer: 4000,
            showConfirmButton: true
        });
    }

    // Función de diagnóstico
    function diagnosticarProblema() {
        console.log('=== DIAGNÓSTICO DEL CARRITO ===');
        console.log('1. CarritoItems en memoria:', carritoItems);
        console.log('2. Número de items:', carritoItems.length);
        console.log('3. Contenedor #articulos existe:', $('#articulos').length > 0);
        console.log('4. Items en DOM:', $('.articulo-item').length);
        console.log('=== FIN DIAGNÓSTICO ===');
    }
    function procederAlPago() {
        if (seleccionados.size === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Selecciona productos',
                text: 'Debes seleccionar al menos un producto para proceder al pago',
                confirmButtonText: 'Entendido'
            });
            return;
        }
        
        const productosSeleccionados = Array.from(seleccionados).map(id => {
            const item = carritoItems.find(item => item.id.toString() === id.toString());
            return item ? item.nombre : 'Producto';
        });
        
        // Calcular totales para mostrar en el resumen
        let subtotalCheckout = 0;
        let envioCheckout = 0;
        
        seleccionados.forEach(itemId => {
            const item = carritoItems.find(item => item.id.toString() === itemId.toString());
            if (item) {
                const precio = parseFloat(item.precio) || 0;
                const cantidad = parseInt(item.cantidad_producto) || 1;
                subtotalCheckout += precio * cantidad;
                
                // Calcular envío (usar la misma lógica que en actualizarResumen)
                if (item.envio === 'pago') {
                    envioCheckout = 250;
                }
            }
        });
        
        const totalCheckout = subtotalCheckout + envioCheckout;
        
        Swal.fire({
            title: 'Confirmar compra',
            html: `
                <div class="text-left">
                    <p><strong>Productos seleccionados (${seleccionados.size}):</strong></p>
                    <ul class="small">
                        ${productosSeleccionados.map(nombre => `<li>${nombre}</li>`).join('')}
                    </ul>
                    <div class="mt-3 p-2 bg-light rounded">
                        <div class="row">
                            <div class="col-6"><strong>Subtotal:</strong></div>
                            <div class="col-6 text-right">$ ${subtotalCheckout.toFixed(2)}</div>
                        </div>
                        <div class="row">
                            <div class="col-6"><strong>Envío:</strong></div>
                            <div class="col-6 text-right">$ ${envioCheckout.toFixed(2)}</div>
                        </div>
                        <div class="row">
                            <div class="col-6"><strong>Total:</strong></div>
                            <div class="col-6 text-right"><strong>$ ${totalCheckout.toFixed(2)}</strong></div>
                        </div>
                    </div>
                </div>
            `,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Continuar al checkout',
            cancelButtonText: 'Seguir comprando',
            reverseButtons: true,
            width: '600px'
        }).then((result) => {
            if (result.isConfirmed) {
                // Guardar los items seleccionados en sessionStorage para pasarlos al checkout
                console.log('Guardando datos para checkout:', {
                    items: Array.from(seleccionados),
                    subtotal: subtotalCheckout,
                    envio: envioCheckout,
                    total: totalCheckout
                });
                const itemsParaCheckout = Array.from(seleccionados);
                sessionStorage.setItem('checkoutItems', JSON.stringify(Array.from(seleccionados)));
                sessionStorage.setItem('checkoutSubtotal', subtotalCheckout.toString());
                sessionStorage.setItem('checkoutEnvio', envioCheckout.toString());
                sessionStorage.setItem('checkoutTotal', totalCheckout.toString());
                
                // Redirigir a la página de checkout
                window.location.href = 'checkout.php';
            }
        });
    }
    // Función para actualizar el badge del carrito
async function actualizarBadgeCarrito() {
    try {
        console.log('Actualizando badge del carrito...');
        
        // Obtener la cantidad total de items en el carrito
        const cantidadTotal = await obtenerCantidadTotalCarrito();
        const $badge = $('#cart-badge');
        
        console.log('Cantidad total en carrito:', cantidadTotal);
        
        if (cantidadTotal > 0) {
            $badge.text(cantidadTotal);
            $badge.show();
            
            // Agregar animación
            $badge.addClass('animate__animated animate__bounceIn');
            setTimeout(() => {
                $badge.removeClass('animate__animated animate__bounceIn');
            }, 1000);
        } else {
            $badge.hide();
        }
        
    } catch (error) {
        console.error('Error actualizando badge:', error);
        // En caso de error, ocultar el badge
        $('#cart-badge').hide();
    }
}

// Función para obtener la cantidad total del carrito desde el servidor
async function obtenerCantidadTotalCarrito() {
    return new Promise((resolve, reject) => {
        // Si ya tenemos los items del carrito, calcular localmente
        if (carritoItems && carritoItems.length > 0) {
            const total = carritoItems.reduce((sum, item) => {
                return sum + (parseInt(item.cantidad_producto) || 1);
            }, 0);
            resolve(total);
            return;
        }
        
        // Si no hay items cargados, consultar al servidor
        $.post('../Controllers/CarritoController.php', { 
            funcion: 'obtener_cantidad_total' 
        })
        .done(function(response) {
            try {
                const data = typeof response === 'string' ? JSON.parse(response) : response;
                resolve(data.cantidad_total || 0);
            } catch (error) {
                console.error('Error parseando respuesta cantidad total:', error);
                resolve(0);
            }
        })
        .fail(function(error) {
            console.error('Error obteniendo cantidad total:', error);
            resolve(0);
        });
    });
}

// Función para actualizar el badge cuando se modifica el carrito
function actualizarBadgePorCambios() {
    //console.log('Actualizando badge por cambios en el carrito...');
    
    // Calcular cantidad total localmente
    const cantidadTotal = carritoItems.reduce((sum, item) => {
        return sum + (parseInt(item.cantidad_producto) || 1);
    }, 0);
    
    const $badge = $('#cart-badge');
    
    if (cantidadTotal > 0) {
        $badge.text(cantidadTotal);
        $badge.show();
        
        // Animación sutil para cambios
        $badge.addClass('animate__animated animate__pulse');
        setTimeout(() => {
            $badge.removeClass('animate__animated animate__pulse');
        }, 500);
    } else {
        $badge.hide();
    }
}
});