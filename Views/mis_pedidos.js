$(document).ready(function () {
  //console.log("Mis Pedidos inicializado");

  // Cargar pedidos al iniciar
  cargarPedidos();

  // Evento para filtro de estado
  $("#filtro-estado").change(function () {
    cargarPedidos($(this).val());
  });

  async function cargarPedidos(filtroEstado = "") {
    //console.log("Cargando pedidos con filtro:", filtroEstado);

    try {
      // Mostrar loading
      $("#lista-pedidos").html(`
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Cargando pedidos...</span>
                    </div>
                    <p class="text-muted mt-2">Cargando tus pedidos...</p>
                </div>
            `);
      $("#estado-vacio").hide();

      const response = await $.post("../Controllers/PedidoController.php", {
        funcion: "obtener_pedidos_usuario",
        filtro_estado: filtroEstado,
      });

      const data =
        typeof response === "string" ? JSON.parse(response) : response;

      if (data.error === "no_sesion") {
        window.location.href = "login.php";
        return;
      }

      if (data.success && data.pedidos.length > 0) {
        renderizarPedidos(data.pedidos);
        $("#estado-vacio").hide();
        $("#lista-pedidos").show();
      } else {
        mostrarEstadoVacio();
      }
    } catch (error) {
      console.error("Error cargando pedidos:", error);
      mostrarError("Error al cargar los pedidos");
      mostrarEstadoVacio();
    }
  }

  function renderizarPedidos(pedidos) {
    let html = "";

    pedidos.forEach((pedido) => {
      const fecha = new Date(pedido.fecha_creacion).toLocaleDateString(
        "es-ES",
        {
          year: "numeric",
          month: "long",
          day: "numeric",
          hour: "2-digit",
          minute: "2-digit",
        }
      );

      html += `
                <div class="card pedido-card mb-4">
                    <div class="card-header">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <h6 class="mb-0">
                                    <i class="fas fa-receipt mr-2"></i>
                                    Pedido #${pedido.numero_orden}
                                </h6>
                                <small class="text-muted">Realizado el ${fecha}</small>
                            </div>
                            <div class="col-md-3 text-center">
                                <span class="${
                                  pedido.estado_clase
                                } estado-pedido">
                                    ${pedido.estado_texto}
                                </span>
                            </div>
                            <div class="col-md-3 text-right">
                                <strong class="h5 text-danger">$${pedido.total.toFixed(
                                  2
                                )}</strong>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8">
                                <h6>Productos (${pedido.total_productos})</h6>
                                <div class="row">
            `;

      // Mostrar primeros 3 productos
      pedido.detalles.slice(0, 3).forEach((detalle) => {
        html += `
                    <div class="col-4 mb-2">
                        <div class="d-flex align-items-center">
                            <img src="../Util/Img/Producto/${
                              detalle.imagen || "producto_default.png"
                            }" 
                                 alt="${detalle.producto_nombre}" 
                                 class="producto-img mr-2"
                                 onerror="this.src='../Util/Img/Producto/producto_default.png'">
                            <div>
                                <small class="d-block font-weight-bold">${
                                  detalle.producto_nombre
                                }</small>
                                <small class="text-muted">Cantidad: ${
                                  detalle.cantidad
                                }</small>
                            </div>
                        </div>
                    </div>
                `;
      });

      // Si hay más de 3 productos, mostrar indicador
      if (pedido.detalles.length > 3) {
        html += `
                    <div class="col-4 mb-2">
                        <div class="d-flex align-items-center justify-content-center h-100">
                            <span class="badge badge-light">+${
                              pedido.detalles.length - 3
                            } más</span>
                        </div>
                    </div>
                `;
      }

      html += `
                                </div>
                            </div>
                            <div class="col-md-4 border-left">
                                <h6>Resumen</h6>
                                <div class="small">
                                    <div class="d-flex justify-content-between">
                                        <span>Subtotal:</span>
                                        <span>$${pedido.subtotal_productos.toFixed(
                                          2
                                        )}</span>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span>Envío:</span>
                                        <span>$${(
                                          pedido.total -
                                          pedido.subtotal_productos
                                        ).toFixed(2)}</span>
                                    </div>
                                    <hr>
                                    <div class="d-flex justify-content-between font-weight-bold">
                                        <span>Total:</span>
                                        <span>$${pedido.total.toFixed(2)}</span>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <button class="btn btn-outline-primary btn-sm btn-block" 
                                            onclick="verDetallesPedido(${
                                              pedido.id
                                            })">
                                        <i class="fas fa-eye mr-1"></i> Ver Detalles
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
    });

    $("#lista-pedidos").html(html);
  }

  function mostrarEstadoVacio() {
    $("#lista-pedidos").hide();
    $("#estado-vacio").show();
  }

  function mostrarError(mensaje) {
    Swal.fire({
      icon: "error",
      title: "Error",
      text: mensaje,
      confirmButtonText: "Entendido",
    });
  }
});

// Función global para ver detalles del pedido
async function verDetallesPedido(idPedido) {
  //console.log("Viendo detalles del pedido:", idPedido);

  try {
    const response = await $.post("../Controllers/PedidoController.php", {
      funcion: "obtener_detalles_pedido",
      id_pedido: idPedido,
    });

    const data = typeof response === "string" ? JSON.parse(response) : response;

    if (data.success) {
      mostrarModalDetalles(data.pedido, data.detalles);
    } else {
      throw new Error(data.error || "Error al cargar detalles");
    }
  } catch (error) {
    console.error("Error cargando detalles:", error);
    Swal.fire(
      "Error",
      "No se pudieron cargar los detalles del pedido",
      "error"
    );
  }
}

function mostrarModalDetalles(pedido, detalles) {
  const fecha = new Date(pedido.fecha_creacion).toLocaleDateString("es-ES", {
    year: "numeric",
    month: "long",
    day: "numeric",
    hour: "2-digit",
    minute: "2-digit",
  });

  let html = `
        <div class="row">
            <div class="col-md-6">
                <h6>Información del Pedido</h6>
                <p><strong>Número:</strong> ${pedido.numero_orden}</p>
                <p><strong>Fecha:</strong> ${fecha}</p>
                <p><strong>Estado:</strong> <span class="estado-pedido ${getEstadoClass(
                  pedido.estado
                )}">${getEstadoTexto(pedido.estado)}</span></p>
            </div>
            <div class="col-md-6">
                <h6>Información de Envío</h6>
                <p><strong>Dirección:</strong><br>${
                  pedido.direccion_envio || "No especificada"
                }</p>
                ${
                  pedido.empresa_envio
                    ? `<p><strong>Transportista:</strong> ${pedido.empresa_envio}</p>`
                    : ""
                }
                ${
                  pedido.tipo_envio
                    ? `<p><strong>Tipo de envío:</strong> ${pedido.tipo_envio}</p>`
                    : ""
                }
            </div>
        </div>
        
        <hr>
        
        <h6>Productos</h6>
        <div class="table-responsive">
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Precio Unit.</th>
                        <th>Cantidad</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
    `;

  let subtotal = 0;
  detalles.forEach((detalle) => {
    const subtotalProducto = parseFloat(detalle.subtotal);
    subtotal += subtotalProducto;

    html += `
            <tr>
                <td>
                    <div class="d-flex align-items-center">
                        <img src="../Util/Img/Producto/${
                          detalle.imagen || "producto_default.png"
                        }" 
                             alt="${detalle.producto_nombre}" 
                             width="40" 
                             class="mr-2 rounded"
                             onerror="this.src='../Util/Img/Producto/producto_default.png'">
                        <div>
                            <div class="font-weight-bold">${
                              detalle.producto_nombre
                            }</div>
                            <small class="text-muted">${
                              detalle.marca_nombre
                            } • ${detalle.tienda_nombre}</small>
                        </div>
                    </div>
                </td>
                <td>$${parseFloat(detalle.precio_unitario).toFixed(2)}</td>
                <td>${detalle.cantidad}</td>
                <td>$${subtotalProducto.toFixed(2)}</td>
            </tr>
        `;
  });

  const envio = parseFloat(pedido.total) - subtotal;

  html += `
                </tbody>
            </table>
        </div>
        
        <div class="row mt-3">
            <div class="col-md-6 offset-md-6">
                <div class="bg-light p-3 rounded">
                    <div class="d-flex justify-content-between">
                        <span>Subtotal:</span>
                        <span>$${subtotal.toFixed(2)}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Envío:</span>
                        <span>$${envio.toFixed(2)}</span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between font-weight-bold h5">
                        <span>Total:</span>
                        <span class="text-danger">$${parseFloat(
                          pedido.total
                        ).toFixed(2)}</span>
                    </div>
                </div>
            </div>
        </div>
    `;

  $("#detalles-pedido-content").html(html);
  $("#modalDetallesPedido").modal("show");
}

// Funciones auxiliares para estados
function getEstadoClass(estado) {
  const clases = {
    pendiente: "estado-pendiente",
    confirmado: "estado-confirmado",
    enviado: "estado-enviado",
    entregado: "estado-entregado",
    cancelado: "estado-cancelado",
  };
  return clases[estado] || "estado-pendiente";
}

function getEstadoTexto(estado) {
  const textos = {
    pendiente: "Pendiente",
    confirmado: "Confirmado",
    enviado: "Enviado",
    entregado: "Entregado",
    cancelado: "Cancelado",
  };
  return textos[estado] || "Pendiente";
}
