$(document).ready(function () {
  var funcion;
  verificar_sesion();
  explorar_categorias();

  

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
      } else {
        $("#nav_usuario").hide();
        $("#favoritos").hide();
      }
    });
  }

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
            console.log("Categorias recibidas:", categorias);
            
            let template = "";
            
            // Verificar si categorias es un array
            if (Array.isArray(categorias) && categorias.length > 0) {
                categorias.forEach(categoria => {
                    // Convertir a número y manejar valores null/undefined
                    const totalProductos = parseInt(categoria.total_productos) || 0;
                    
                    // Usar imagen por defecto si no hay imagen específica
                    const imagen = categoria.imagen && categoria.imagen !== 'default_category.png' 
                        ? `Util/Img/categorias/${categoria.imagen}`
                        : 'Util/Img/categorias/default-category.jpg';
                    
                    // Formatear el número de productos
                    let productosText;
                    if (totalProductos === 0) {
                        productosText = 'Sin productos';
                    } else if (totalProductos === 1) {
                        productosText = '1 producto';
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
            Swal.fire("Error", "Error al procesar los datos de categorías", "error");
        }
    } catch (error) {
        console.error("Error en explorar_categorias:", error);
        Swal.fire("Error", "No se pudieron cargar las categorías", "error");
    }
}
});
