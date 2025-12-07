$(document).ready(function () {
  var funcion;
  verificar_sesion();
  explorar_categorias();
  marcas();
  productos_destacados()

  function verificar_sesion() {
    funcion = "verificar_sesion";
    $.post("Controllers/UsuarioController.php", { funcion }, (response) => {
      if (response != "") {
        let sesion = JSON.parse(response);
        $("#nav_login").hide();
        $("#nav_register").hide();
        $("#usuario_nav").text(sesion.user);
        $("#avatar_nav").attr("src", "Util/Img/Users/" + sesion.avatar);
        $("#avatar_menu").attr("src", "Util/Img/Users/" + sesion.avatar);
        $("#usuario_menu").text(sesion.user);
        $("#favoritos").show();
         $("#notification").show();
      } else {
        $("#nav_usuario").hide();
        $("#favoritos").hide();
         $("#notification").hide();
      }
    });
  }

  //Llenar marcas destacadas dinamicamente
  async function productos_destacados() {
    funcion = "llenar_productos";
    try {
      let data = await fetch("Controllers/ProductoTiendaController.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: "funcion=" + funcion,
      });
      let response = await data.text();
      try {
        let destacados = JSON.parse(response);
        console.log("Obteniendo todas los Productos Destacados: ", destacados);
        let template2 = "";
        if (Array.isArray(destacados) && destacados.length > 0) {
          destacados.forEach((destacado) => {
            const isDest = destacado.destacado == 1;
            template2 += `
                    <div class="col-sm-2">
                        <div class="card product-card h-100">
                        ${isDest
                            ?'<div class="featured-badge">Destacado</div>'
                            : ""
                            }
                            <div class="card-body">
                                <div class="product-image">
                                    <img src="Util/Img/Producto/${destacado.imagen}" alt="${destacado.producto}">
                                </div>
                                <span class="product-brand">${destacado.marca}</span>
                                <a class="product-title" href="Views/descripcion.php?name=${encodeURIComponent(
                      destacado.producto
                    )}&id=${destacado.id}">${destacado.producto}</a>
                                <span class="badge-free-shipping">Envío gratis</span>
                                <div class="product-rating">
                                    <div class="rating-stars">
                            ${generateStarRating(
                              parseFloat(destacado.calificacion) || 0
                            )}
                        </div>
                                </div>
                                <div class="product-price">
                                    <span class="original-price">$ ${destacado.precio}</span>
                                    <span class="discount-percent">-${destacado.descuento}%</span>
                                    <div class="current-price">$ ${destacado.precio_descuento}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
          });
        } else {
          template2 = `
                    <div class="col-12 text-center">
                        <p class="text-muted">No hay Productos Destacados en este momento.</p>
                    </div>
                `;
        }
        $("#featured-products").html(template2);
      } catch (error) {
        console.error("Error parseando JSON:", error);
        console.log("Respuesta del servidor:", response);
        Swal.fire(
          "Error",
          "Error al procesar los datos de los Productos Destacados",
          "error"
        );
      }
    } catch (error) {
      console.error("Error en marcas:", error);
      Swal.fire("Error", "No se pudieron cargar las marcas", "error");
    }
  }

  function generateStarRating(rating) {
    let stars = "";
    const fullStars = Math.floor(rating);
    const hasHalfStar = rating % 1 !== 0;

    for (let i = 0; i < fullStars; i++) {
      stars += '<i class="fas fa-star text-warning"></i>';
    }

    if (hasHalfStar) {
      stars += '<i class="fas fa-star-half-alt"></i>';
    }

    const emptyStars = 5 - Math.ceil(rating);
    for (let i = 0; i < emptyStars; i++) {
      stars += '<i class="far fa-star text-warning"></i>';
    }

    return stars;
  }

  //Llenar marcas destacadas dinamicamente
  async function marcas() {
    funcion = "obtener_marcas";
    try {
      let data = await fetch("Controllers/MarcasController.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: "funcion=" + funcion,
      });
      let response = await data.text();
      try {
        let marcas = JSON.parse(response);
        //console.log("Obteniendo todas las marcas: ", marcas);
        let template1 = "";
        if (Array.isArray(marcas) && marcas.length > 0) {
          marcas.forEach((marca) => {
            template1 += `
                    <div class="brand-item">
                        <img src="Util/Img/Marca/${marca.logo}" alt="${marca.nombre}">
                    </div>
                `;
          });
        } else {
          template1 = `
                    <div class="col-12 text-center">
                        <p class="text-muted">No hay marcas disponibles en este momento.</p>
                    </div>
                `;
        }
        $("#marca_destacada").html(template1);
      } catch (error) {
        console.error("Error parseando JSON:", error);
        console.log("Respuesta del servidor:", response);
        Swal.fire(
          "Error",
          "Error al procesar los datos de las marcas",
          "error"
        );
      }
    } catch (error) {
      console.error("Error en marcas:", error);
      Swal.fire("Error", "No se pudieron cargar las marcas", "error");
    }
  }

  //Lenar Categorias Dinamicamente
  async function explorar_categorias() {
    funcion = "explorar_categorias";
    try {
      let data = await fetch("Controllers/CategoriaController.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: "funcion=" + funcion,
      });
      let response = await data.text();

      try {
        let categorias = JSON.parse(response);
        //console.log("Categorias recibidas:", categorias);

        let template = "";

        // Verificar si categorias es un array
        if (Array.isArray(categorias) && categorias.length > 0) {
          categorias.forEach((categoria) => {
            // Convertir a número y manejar valores null/undefined
            const totalProductos = parseInt(categoria.total_productos) || 0;

            // Usar imagen por defecto si no hay imagen específica
            const imagen =
              categoria.imagen && categoria.imagen !== "default_category.png"
                ? `Util/Img/categorias/${categoria.imagen}`
                : "Util/Img/categorias/default-category.jpg";

            // Formatear el número de productos
            let productosText;
            if (totalProductos === 0) {
              productosText = "Sin productos";
            } else if (totalProductos === 1) {
              productosText = "1 producto";
            } else {
              productosText = `${totalProductos.toLocaleString()} productos`;
            }

            template += `
                        <div class="category-item-horizontal">
                            <div class="category-card">
                                <a href="productos.php?categoria=${categoria.id}" class="category-link">
                                    <div class="category-image">
                                        <img src="${imagen}" 
                                             alt="${categoria.nombre}"
                                             onerror="this.src='Util/Img/categorias/default-category.jpg'">
                                        <div class="category-overlay">
                                            <h4>${categoria.nombre}</h4>
                                            <span class="product-count">${productosText}</span>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        </div>
                    `;
          });
        } else {
          template = `
                    <div class="col-12 text-center">
                        <p class="text-muted">No hay categorías disponibles en este momento.</p>
                    </div>
                `;
        }

        $("#feature_categories").html(template);
      } catch (error) {
        console.error("Error parseando JSON:", error);
        console.log("Respuesta del servidor:", response);
        Swal.fire(
          "Error",
          "Error al procesar los datos de categorías",
          "error"
        );
      }
    } catch (error) {
      console.error("Error en explorar_categorias:", error);
      Swal.fire("Error", "No se pudieron cargar las categorías", "error");
    }
  }
});
