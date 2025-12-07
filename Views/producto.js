document.addEventListener("DOMContentLoaded", function () {
  // Variables globales
  let currentView = "grid";
  let allProducts = [];

  // Elementos DOM
  const productsGrid = document.getElementById("productsGrid");
  const resultsCount = document.querySelector(".results-count");
  const viewButtons = document.querySelectorAll(".view-btn");
  const sortSelect = document.querySelector(".sort-select");

  // Inicializar
  init();

  function init() {
    loadProducts();
    setupEventListeners();
    }

  function setupEventListeners() {
    // Cambio de vista
    viewButtons.forEach((button) => {
      button.addEventListener("click", function () {
        const viewType = this.getAttribute("data-view");
        viewButtons.forEach((btn) => btn.classList.remove("active"));
        this.classList.add("active");
        currentView = viewType;

        if (viewType === "list") {
          productsGrid.classList.add("list-view");
        } else {
          productsGrid.classList.remove("list-view");
        }

        displayProducts(allProducts);
      });
    });

    // Ordenamiento
    if (sortSelect) {
      sortSelect.addEventListener("change", function () {
        sortProducts(this.value);
      });
    }
}
    // Cargar productos
  function loadProducts() {
    showLoading();

    const formData = new FormData();
    formData.append("funcion", "llenar_productos");

    fetch("../Controllers/ProductoTiendaController.php", {
      method: "POST",
      body: formData,
    })
      .then((response) => {
        if (!response.ok) {
          throw new Error("Error en la respuesta del servidor");
        }
        return response.json();
      })
      .then((data) => {
        //console.log("Productos cargados:", data); // Para debug
        allProducts = data;
        displayProducts(data);
        updateResultsCount(data.length);
      })
      .catch((error) => {
        console.error("Error loading products:", error);
        showError("Error cargando productos");
      });
  }

  function sortProducts(sortBy) {
    let sortedProducts = [...allProducts];

    switch (sortBy) {
      case "price-low":
        sortedProducts.sort((a, b) => a.precio_descuento - b.precio_descuento);
        break;
      case "price-high":
        sortedProducts.sort((a, b) => b.precio_descuento - a.precio_descuento);
        break;
      case "rating":
        sortedProducts.sort((a, b) => b.calificacion - a.calificacion);
        break;
      case "newest":
        // Asumiendo que tenemos fecha de creación
        sortedProducts.sort(
          (a, b) => new Date(b.fecha_creacion) - new Date(a.fecha_creacion)
        );
        break;
      case "popular":
      default:
        // Orden por defecto (popularidad)
        break;
    }

    displayProducts(sortedProducts);
  }

  function displayProducts(products) {
    if (!products || products.length === 0) {
      productsGrid.innerHTML = `
            <div class="col-12 text-center py-5">
                <i class="fas fa-search fa-3x text-muted mb-3"></i>
                <h4 class="text-muted">No se encontraron productos</h4>
                <p class="text-muted">Intenta ajustar los filtros de búsqueda</p>
            </div>
        `;
      return;
    }

    const isListView = currentView === "list";
    let html = "";

    products.forEach((product) => {
      const listClass = isListView ? "list-layout" : "";
      const discount = parseFloat(product.descuento) || 0;
      const originalPrice = parseFloat(product.precio) || 0;
      const finalPrice = parseFloat(product.precio_descuento) || originalPrice;
      const isNew = product.es_nuevo == 1;

      html += `
            <div class="product-card ${listClass}">
                <div class="product-image">
                    <img src="../Util/Img/Producto/${product.imagen}" alt="${
        product.producto
      }" onerror="this.src='../Util/Img/Producto/producto_default.png'">
                    <div class="product-badges">
                        ${
                          discount > 0
                            ? `<span class="product-badge badge-discount">-${discount}%</span>`
                            : ""
                        }
                        ${
                          product.envio == 0
                            ? '<span class="product-badge badge-free-shipping">Envío Gratis</span>'
                            : ""
                        }
                        ${
                          isNew
                            ? '<span class="product-badge badge-new">Nuevo</span>'
                            : ""
                        }
                    </div>
                    <div class="product-actions">
                        <button class="action-btn favorite-btn" title="Agregar a favoritos" onclick="toggleFavorite('${
                          product.id
                        }', this)" data-id_producto="${product.id}">
                            <i class="far fa-heart"></i>
                        </button>
                        <button class="action-btn" title="Vista rápida" onclick="quickView('${
                          product.id
                        }')">
                            <i class="far fa-eye"></i>
                        </button>
                    </div>
                </div>
                <div class="product-content">
                    <span class="product-brand">${
                      product.marca || "Marca"
                    }</span>
                    <a href="descripcion.php?name=${encodeURIComponent(
                      product.producto
                    )}&id=${product.id}" class="product-title">
                        ${product.producto}
                    </a>
                    <div class="product-rating">
                        <div class="rating-stars">
                            ${generateStarRating(
                              parseFloat(product.calificacion) || 0
                            )}
                        </div>
                        <span class="rating-count">(${
                          product.total_resenas || 0
                        })</span>
                    </div>
                    ${
                      isListView
                        ? `<p class="product-description">${
                            product.detalles || "Descripción no disponible"
                          }</p>`
                        : ""
                    }
                    <div class="product-price">
                        ${
                          discount > 0
                            ? `
                            <span class="original-price">$${originalPrice.toFixed(
                              2
                            )}</span>
                            <span class="discount-percent">-${discount}%</span>
                        `
                            : ""
                        }
                        <div class="current-price">$${finalPrice.toFixed(
                          2
                        )}</div>
                    </div>
                    <div class="product-meta">
                        <span class="stock-status ${
                          product.stock > 0 ? "in-stock" : "out-of-stock"
                        }">
                            ${product.stock > 0 ? "En Stock" : "Agotado"}
                        </span>
                        <button class="add-to-cart-btn" onclick="addToCart('${
                          product.id
                        }', 1)" ${product.stock <= 0 ? "disabled" : ""}>
                            <i class="fas fa-shopping-cart"></i>
                            ${product.stock > 0 ? "Agregar" : "Agotado"}
                        </button>
                    </div>
                </div>
            </div>
        `;
    });

    productsGrid.innerHTML = html;
    // Verifica estado de favoritos después de cargar productos
    setTimeout(checkFavoriteStatus, 1000);
  }

  function generateStarRating(rating) {
    let stars = "";
    const fullStars = Math.floor(rating);
    const hasHalfStar = rating % 1 !== 0;

    for (let i = 0; i < fullStars; i++) {
      stars += '<i class="fas fa-star"></i>';
    }

    if (hasHalfStar) {
      stars += '<i class="fas fa-star-half-alt"></i>';
    }

    const emptyStars = 5 - Math.ceil(rating);
    for (let i = 0; i < emptyStars; i++) {
      stars += '<i class="far fa-star"></i>';
    }

    return stars;
  }

  function showLoading() {
    productsGrid.innerHTML = `
            <div class="col-12 text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="sr-only">Cargando productos...</span>
                </div>
                <p class="text-muted mt-2">Cargando productos...</p>
            </div>
        `;
  }

  function showError(message) {
    productsGrid.innerHTML = `
            <div class="col-12 text-center py-5">
                <i class="fas fa-exclamation-triangle fa-3x text-danger mb-3"></i>
                <h4 class="text-danger">Error</h4>
                <p class="text-muted">${message}</p>
                <button class="btn btn-primary mt-2" onclick="loadProducts()">Reintentar</button>
            </div>
        `;
  }

  function updateResultsCount(count) {
    if (resultsCount) {
      resultsCount.innerHTML = `Mostrando <strong>1-${count}</strong> de <strong>${count}</strong> productos`;
    }
  }
});

// Funciones globales
function addToCart(productId, quantity = 1) {
  const formData = new FormData();
  formData.append("funcion", "agregar_al_carrito");
  formData.append("id_producto_tienda", productId);
  formData.append("cantidad", quantity);

  fetch("../Controllers/CarritoController.php", {
    method: "POST",
    body: formData,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        Swal.fire({
          icon: "success",
          title: "Producto agregado",
          text: "El producto se ha añadido al carrito",
          timer: 2000,
          showConfirmButton: false,
        });
        // Actualizar contador del carrito si existe
        updateCartCounter();
      } else {
        Swal.fire({
          icon: "error",
          title: "Error",
          text: data.message || "Error al agregar al carrito",
        });
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      Swal.fire({
        icon: "error",
        title: "Error",
        text: "Error al conectar con el servidor",
      });
    });
}

// Función principal para alternar favoritos
function toggleFavorite(productId, button) {
    const heartIcon = button.querySelector('i');
    const isCurrentlyFavorite = heartIcon.classList.contains('fas');
    
    if (isCurrentlyFavorite) {
        removeFromFavorites(productId, button);
    } else {
        addToFavorites(productId, button);
    }
}

// Función para agregar a favoritos
function addToFavorites(productId, button) {
    const formData = new FormData();
    formData.append("funcion", "agregar_favorito");
    formData.append("id_producto_tienda", productId);

    fetch("../Controllers/FavoritoController.php", {
        method: "POST",
        body: formData,
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Cambiar icono a corazón lleno
            const heartIcon = button.querySelector('i');
            heartIcon.classList.remove('far');
            heartIcon.classList.add('fas');
            heartIcon.style.color = '#e74c3c'; // Color rojo para el corazón lleno
            
            Swal.fire({
                icon: "success",
                title: "Agregado a favoritos",
                showConfirmButton: false,
                timer: 1500,
            });
        } else {
            Swal.fire({
                icon: "error",
                title: "Error",
                text: data.error || "Error al agregar a favoritos",
            });
        }
    })
    .catch(error => {
        console.error("Error:", error);
        Swal.fire({
            icon: "error",
            title: "Error",
            text: "Error al conectar con el servidor",
        });
    });
}

// Función para eliminar de favoritos
function removeFromFavorites(productId, button) {
    const formData = new FormData();
    formData.append("funcion", "eliminar_favorito");
    formData.append("id_producto_tienda", productId);

    fetch("../Controllers/FavoritoController.php", {
        method: "POST",
        body: formData,
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Cambiar icono a corazón vacío
            const heartIcon = button.querySelector('i');
            heartIcon.classList.remove('fas');
            heartIcon.classList.add('far');
            heartIcon.style.color = ''; // Remover color
            
            Swal.fire({
                icon: "success",
                title: "Eliminado de favoritos",
                showConfirmButton: false,
                timer: 1500,
            });
        } else {
            Swal.fire({
                icon: "error",
                title: "Error",
                text: data.error || "Error al eliminar de favoritos",
            });
        }
    })
    .catch(error => {
        console.error("Error:", error);
        Swal.fire({
            icon: "error",
            title: "Error",
            text: "Error al conectar con el servidor",
        });
    });
}

// Función para verificar estado de favorito al cargar la página
function checkFavoriteStatus() {
    document.querySelectorAll('.favorite-btn').forEach(btn => {
        const productId = btn.getAttribute('data-id_producto');
        if (productId) {
            const formData = new FormData();
            formData.append("funcion", "verificar_favorito");
            formData.append("id_producto_tienda", productId);

            fetch("../Controllers/FavoritoController.php", {
                method: "POST",
                body: formData,
            })
            .then(response => response.json())
            .then(data => {
                const heartIcon = btn.querySelector('i');
                if (data.es_favorito) {
                    heartIcon.classList.remove('far');
                    heartIcon.classList.add('fas');
                    heartIcon.style.color = '#e74c3c';
                } else {
                    heartIcon.classList.remove('fas');
                    heartIcon.classList.add('far');
                    heartIcon.style.color = '';
                }
            })
            .catch(error => {
                console.error("Error verificando favorito:", error);
            });
        }
    });
}

// También actualiza la función addToFavorites existente para que no interfiera:
function addToFavoritesOld(productId) {
    // Esta función queda obsoleta, pero la mantenemos por compatibilidad
    toggleFavorite(productId, event.target.closest('.favorite-btn'));
}

function quickView(productId) {
  // Implementar vista rápida
  Swal.fire({
    title: "Vista rápida",
    text: "Funcionalidad en desarrollo",
    icon: "info",
  });
}

function updateCartCounter() {
  // Implementar actualización del contador del carrito
  const cartCounter = document.querySelector(".cart-counter");
  if (cartCounter) {
    // Lógica para actualizar el contador
    let currentCount = parseInt(cartCounter.textContent) || 0;
        cartCounter.textContent = currentCount + 1;
  }
}
