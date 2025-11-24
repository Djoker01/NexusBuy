document.addEventListener("DOMContentLoaded", function () {
  // Variables globales
  let currentView = "grid";
  let activeFilters = 0;
  let currentPage = 1;
  let productsPerPage = 12;
  let allProducts = [];

  // Elementos DOM
  const productsGrid = document.getElementById("productsGrid");
  const resultsCount = document.querySelector(".results-count");
  const filterToggle = document.getElementById("filterToggle");
  const filtersSidebar = document.getElementById("filtersSidebar");
  const filtersOverlay = document.getElementById("filtersOverlay");
  const closeFilters = document.getElementById("closeFilters");
  const applyFiltersBtn = document.getElementById("applyFilters");
  const clearFiltersBtn = document.getElementById("clearFilters");
  const viewButtons = document.querySelectorAll(".view-btn");
  const sortSelect = document.querySelector(".sort-select");

  // Inicializar
  init();

  function init() {
    loadCategories();
    loadProducts();
    setupEventListeners();
    updateActiveFiltersCount();
  }

  function setupEventListeners() {
    // Toggle de filtros móviles
    if (filterToggle && filtersSidebar) {
      filterToggle.addEventListener("click", openFiltersSidebar);
      closeFilters.addEventListener("click", closeFiltersSidebar);
      filtersOverlay.addEventListener("click", closeFiltersSidebar);
    }

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

    // Filtros
    const filterCheckboxes = document.querySelectorAll(
      'input[type="checkbox"]'
    );
    filterCheckboxes.forEach((checkbox) => {
      checkbox.addEventListener("change", updateActiveFiltersCount);
    });

    const priceInputs = document.querySelectorAll("#minPrice, #maxPrice");
    priceInputs.forEach((input) => {
      input.addEventListener("input", updateActiveFiltersCount);
    });

    // Aplicar y limpiar filtros
    if (applyFiltersBtn) {
      applyFiltersBtn.addEventListener("click", applyFilters);
    }

    if (clearFiltersBtn) {
      clearFiltersBtn.addEventListener("click", clearFilters);
    }

    // Rango de precios sincronizado
    const priceRange = document.getElementById("priceRange");
    const minPriceInput = document.getElementById("minPrice");
    const maxPriceInput = document.getElementById("maxPrice");

    if (priceRange && minPriceInput && maxPriceInput) {
      priceRange.addEventListener("input", function () {
        minPriceInput.value = this.value;
        updateActiveFiltersCount();
      });

      minPriceInput.addEventListener("input", function () {
        priceRange.value = this.value;
        updateActiveFiltersCount();
      });

      maxPriceInput.addEventListener("input", updateActiveFiltersCount);
    }
  }

  function openFiltersSidebar() {
    filtersSidebar.classList.add("active");
    filtersOverlay.classList.add("active");
    document.body.style.overflow = "hidden";
  }

  function closeFiltersSidebar() {
    filtersSidebar.classList.remove("active");
    filtersOverlay.classList.remove("active");
    document.body.style.overflow = "";
  }

  function updateActiveFiltersCount() {
    const checkboxes = document.querySelectorAll(
      'input[type="checkbox"]:checked'
    );
    const minPrice = document.getElementById("minPrice").value;
    const maxPrice = document.getElementById("maxPrice").value;

    let count = checkboxes.length;

    if (parseInt(minPrice) > 0 || parseInt(maxPrice) < 1000) {
      count++;
    }

    activeFilters = count;
    document.getElementById("activeFiltersCount").textContent = count;
  }

  // Cargar categorías
  function loadCategories() {
    const categoriesFilter = document.getElementById("categoriesFilter");

    fetch("../Controllers/CategoriaController.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: "funcion=obtener_categorias_activas",
    })
      .then((response) => response.json())
      .then((data) => {
        if (data && data.length > 0) {
          let categoriesHTML = "";
          data.forEach((category) => {
            categoriesHTML += `
                        <div class="filter-option">
                            <input type="checkbox" id="category-${
                              category.id
                            }" name="category" value="${category.id}">
                            <label for="category-${category.id}">${
              category.nombre
            } <span class="filter-count">(${
              category.product_count || 0
            })</span></label>
                        </div>
                    `;
          });
          categoriesFilter.innerHTML = categoriesHTML;

          // Agregar event listeners a los nuevos checkboxes
          const newCheckboxes = categoriesFilter.querySelectorAll(
            'input[type="checkbox"]'
          );
          newCheckboxes.forEach((checkbox) => {
            checkbox.addEventListener("change", updateActiveFiltersCount);
          });
        }
      })
      .catch((error) => {
        console.error("Error loading categories:", error);
        categoriesFilter.innerHTML =
          '<div class="text-danger">Error cargando categorías</div>';
      });
  }

  // Cargar productos
  function loadProducts(filters = {}) {
    showLoading();

    const formData = new FormData();
    formData.append("funcion", "llenar_productos");

    // Agregar filtros si existen
    if (filters.id_subcategoria) {
      formData.append("id_subcategoria", filters.id_subcategoria);
    }
    if (filters.id_categoria) {
      formData.append("id_categoria", filters.id_categoria);
    }

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
        console.log("Productos cargados:", data); // Para debug
        allProducts = data;
        displayProducts(data);
        updateResultsCount(data.length);
      })
      .catch((error) => {
        console.error("Error loading products:", error);
        showError("Error cargando productos: " + error.message);
      });
  }

  function applyFilters() {
    const filters = getCurrentFilters();
    loadProducts(filters);
    closeFiltersSidebar();

    // Mostrar mensaje
    Swal.fire({
      icon: "success",
      title: "Filtros aplicados",
      text: `Mostrando productos con ${activeFilters} filtros activos`,
      timer: 2000,
      showConfirmButton: false,
    });
  }

  function clearFilters() {
    // Limpiar checkboxes
    const filterCheckboxes = document.querySelectorAll(
      'input[type="checkbox"]'
    );
    filterCheckboxes.forEach((checkbox) => {
      checkbox.checked = false;
    });

    // Restablecer precios
    const minPriceInput = document.getElementById("minPrice");
    const maxPriceInput = document.getElementById("maxPrice");
    const priceRange = document.getElementById("priceRange");

    if (minPriceInput && maxPriceInput && priceRange) {
      minPriceInput.value = 0;
      maxPriceInput.value = 1000;
      priceRange.value = 1000;
    }

    updateActiveFiltersCount();
    loadProducts();
    closeFiltersSidebar();
  }

  function getCurrentFilters() {
    const filters = {};

    // Categorías
    const categoryCheckbox = document.querySelector(
      'input[name="category"]:checked'
    );
    if (categoryCheckbox) {
      filters.id_categoria = categoryCheckbox.value;
    }

    // Marcas
    const brandCheckboxes = document.querySelectorAll(
      'input[name="brand"]:checked'
    );
    if (brandCheckboxes.length > 0) {
      filters.marcas = Array.from(brandCheckboxes).map((cb) => cb.value);
    }

    // Precio
    const minPrice = document.getElementById("minPrice").value;
    const maxPrice = document.getElementById("maxPrice").value;
    if (minPrice > 0 || maxPrice < 1000) {
      filters.precio_min = minPrice;
      filters.precio_max = maxPrice;
    }

    return filters;
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
                          product.envio == 1
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
                        <button class="action-btn" title="Agregar a favoritos" onclick="addToFavorites('${
                          product.id
                        }')">
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
  formData.append("funcion", "agregar_carrito");
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

function addToFavorites(productId) {
  // Implementar lógica de favoritos
  Swal.fire({
    icon: "success",
    title: "Agregado a favoritos",
    showConfirmButton: false,
    timer: 1500,
  });
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
  }
}
