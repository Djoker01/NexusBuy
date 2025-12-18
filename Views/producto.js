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
    
    // Verificar si hay agregados pendientes después del login
    handlePendingCartAdd();
    
    // Actualizar contador al cargar la página
    updateCartCounter();
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

    const urlParams = new URLSearchParams(window.location.search);
    const categoriaNombre = urlParams.get('categoria');
    const subcategoriaNombre = urlParams.get('subcategoria');

    const formData = new FormData();
    formData.append("funcion", "llenar_productos");

    if (subcategoriaNombre) {
      // Primero intentar cargar la categoría padre
      loadCategoriaFromSubcategoria(subcategoriaNombre)
        .then(categoriaNombre => {
          // Luego buscar subcategoría por nombre para obtener ID
          buscarSubcategoriaPorNombre(subcategoriaNombre)
            .then(id_subcategoria => {
              if (id_subcategoria) {
                const formData = new FormData();
                formData.append("funcion", "llenar_productos");
                formData.append("id_subcategoria", id_subcategoria);
                fetchProducts(formData, categoriaNombre, subcategoriaNombre);
              } else {
                showError("Subcategoría no encontrada");
              }
            })
            .catch(error => {
              console.error("Error buscando subcategoría:", error);
              showError("Error al cargar la subcategoría");
            });
        })
        .catch(error => {
          console.error("Error cargando categoría:", error);
          // Si falla, continuar sin categoría
          buscarSubcategoriaPorNombre(subcategoriaNombre)
            .then(id_subcategoria => {
              if (id_subcategoria) {
                const formData = new FormData();
                formData.append("funcion", "llenar_productos");
                formData.append("id_subcategoria", id_subcategoria);
                fetchProducts(formData, null, subcategoriaNombre);
              } else {
                showError("Subcategoría no encontrada");
              }
            })
            .catch(error => {
              console.error("Error buscando subcategoría:", error);
              showError("Error al cargar la subcategoría");
            });
        });
    } else if (categoriaNombre) {
      formData.append("nombre_categoria", categoriaNombre);
      fetchProducts(formData, categoriaNombre, null);
    } else {
      fetchProducts(formData, null, null);
    }
  }

  // Nueva función para buscar subcategoría por nombre
  function buscarSubcategoriaPorNombre(nombre) {
    return new Promise((resolve, reject) => {
      const formData = new FormData();
      formData.append("funcion", "buscar_subcategoria_por_nombre");
      formData.append("nombre_subcategoria", nombre);

      fetch("../Controllers/SubcategoriaController.php", {
        method: "POST",
        body: formData,
      })
        .then(response => response.json())
        .then(data => {
          if (data.success && data.id) {
            resolve(data.id);
          } else {
            resolve(null);
          }
        })
        .catch(error => {
          console.error("Error buscando subcategoría:", error);
          reject(error);
        });
    });
  }

  // Función separada para hacer el fetch de productos
  function fetchProducts(formData, categoriaNombre, subcategoriaNombre) {
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
        if (data && data.length > 0) {
          allProducts = data;
          displayProducts(data);
          // console.log(data);
          updateResultsCount(data.length, categoriaNombre, subcategoriaNombre);
        } else {
          showNoProductsMessage(categoriaNombre, subcategoriaNombre);
        }
      })
      .catch((error) => {
        console.error("Error loading products:", error);
        showError("Error cargando productos");
      });
  }

  // Función para mostrar mensaje cuando no hay productos
  function showNoProductsMessage(categoriaNombre, subcategoriaNombre) {
    let message = "";

    if (subcategoriaNombre) {
      const categoriaLink = categoriaNombre ?
        `producto.php?categoria=${encodeURIComponent(categoriaNombre)}` :
        'producto.php';

      message = `
            <div class="col-12 text-center py-5">
                <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                <h4 class="text-muted">No hay productos en "${subcategoriaNombre}"</h4>
                <p class="text-muted mb-3">Pronto tendremos nuevos productos disponibles.</p>
                <a href="${categoriaLink}" class="btn btn-outline-primary">
                    <i class="fas fa-arrow-left"></i> Ver productos de la categoría
                </a>
                <a href="producto.php" class="btn btn-outline-secondary ml-2">
                    Ver todos los productos
                </a>
            </div>
        `;
    } else if (categoriaNombre) {
      message = `
            <div class="col-12 text-center py-5">
                <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                <h4 class="text-muted">No hay productos en "${categoriaNombre}"</h4>
                <p class="text-muted mb-3">Pronto tendremos nuevos productos disponibles.</p>
                <a href="producto.php" class="btn btn-outline-primary">
                    Ver todos los productos
                </a>
            </div>
        `;
    } else {
      message = `
            <div class="col-12 text-center py-5">
                <i class="fas fa-search fa-3x text-muted mb-3"></i>
                <h4 class="text-muted">No se encontraron productos</h4>
                <p class="text-muted">Intenta ajustar los filtros de búsqueda</p>
            </div>
        `;
    }

    productsGrid.innerHTML = message;
    updateResultsCount(0, categoriaNombre, subcategoriaNombre);
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
      const urlParams = new URLSearchParams(window.location.search);
      const categoriaNombre = urlParams.get('categoria');
      if (categoriaNombre) {
        showNoProductsMessage(categoriaNombre, null);
      } else {
        productsGrid.innerHTML = `
              <div class="col-12 text-center py-5">
                  <i class="fas fa-search fa-3x text-muted mb-3"></i>
                  <h4 class="text-muted">No se encontraron productos</h4>
                  <p class="text-muted">Intenta ajustar los filtros de búsqueda</p>
              </div>
          `;
      }
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
                    <img src="../Util/Img/Producto/${product.imagen}" alt="${product.producto
            }" onerror="this.src='../Util/Img/Producto/producto_default.png'">
                    <div class="product-badges">
                        ${discount > 0
          ? `<span class="product-badge badge-discount">-${discount}%</span>`
          : ""
        }
                        ${product.envio == 1
          ? '<span class="product-badge badge-free-shipping">Envío Gratis</span>'
          : ""
        }
                        ${isNew
          ? '<span class="product-badge badge-new">Nuevo</span>'
          : ""
        }
                    </div>
                    <div class="product-actions">
                        <button class="action-btn favorite-btn" title="Agregar a favoritos" data-id_producto="${product.id}">
                            <i class="far fa-heart"></i>
                        </button>
                        <button class="action-btn" title="Vista rápida" data-id_producto="${product.id}">
                            <i class="far fa-eye"></i>
                        </button>
                    </div>
                </div>
                <div class="product-content">
                    <span class="product-brand">${product.marca || "Marca"
        }</span>
                    <a href="descripcion.php?name=${encodeURIComponent(product.producto)}&id=${product.id}" class="product-title">
                        ${product.producto}
                    </a>
                    <div class="product-rating">
                        <div class="rating-stars">
                            ${generateStarRating(parseFloat(product.calificacion) || 0)}
                        </div>
                        <span class="rating-count">(${product.total_resenas || 0
        }) Reseñas</span>
                    </div>
                    ${isListView
          ? `<p class="product-description">${product.detalles || "Descripción no disponible"
          }</p>`
          : ""
        }
                    <div class="product-price">
                        ${discount > 0
          ? `
                            <span class="original-price">$${originalPrice.toFixed(2)}</span>
                            <span class="discount-percent">-${discount}%</span>
                        `
          : ""
        }
                        <div class="current-price">$${finalPrice.toFixed(2)}</div>
                    </div>
                    <div class="product-meta">
                        <span class="stock-status ${product.stock > 0 ? "in-stock" : "out-of-stock"
        }">
                            ${product.stock > 0 ? "En Stock" : "Agotado"}
                        </span>
                        <button class="add-to-cart-btn" ${product.stock <= 0 ? "disabled" : ""}>
                            <i class="fas fa-shopping-cart"></i>
                            ${product.stock > 0 ? "Agregar" : "Agotado"}
                        </button>
                    </div>
                </div>
            </div>
        `;
    });

    productsGrid.innerHTML = html;
    
    // Agregar event listeners a los botones después de renderizar
    setupProductButtons();
    
    // Verifica estado de favoritos después de cargar productos
    setTimeout(checkFavoriteStatus, 1000);
  }

  // Configurar event listeners para los botones de productos
  function setupProductButtons() {
    // Botones de favoritos
    document.querySelectorAll('.favorite-btn').forEach(btn => {
      const productId = btn.getAttribute('data-id_producto');
      btn.addEventListener('click', function(e) {
        e.preventDefault();
        toggleFavorite(productId, this);
      });
    });
    
    // Botones de vista rápida
    document.querySelectorAll('.action-btn[title="Vista rápida"]').forEach(btn => {
      btn.addEventListener('click', function(e) {
        e.preventDefault();
        const productCard = this.closest('.product-card');
        const productId = productCard.querySelector('.favorite-btn').getAttribute('data-id_producto');
        quickView(productId);
      });
    });
    
    // Botones de agregar al carrito
    document.querySelectorAll('.add-to-cart-btn:not([disabled])').forEach(btn => {
      btn.addEventListener('click', function(e) {
        e.preventDefault();
        const productCard = this.closest('.product-card');
        const productId = productCard.querySelector('.favorite-btn').getAttribute('data-id_producto');
        
        // Animar el botón
        animateCartButton(this);
        
        // Agregar al carrito
        addToCart(productId, 1);
      });
    });
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
                <button class="btn btn-primary mt-2" onclick="location.reload()">Reintentar</button>
            </div>
        `;
  }

  function updateResultsCount(count, categoriaNombre, subcategoriaNombre) {
    if (resultsCount) {
      let texto = "";

      if (subcategoriaNombre) {
        texto = `Mostrando <strong>${count}</strong> productos en "${subcategoriaNombre}"`;
      } else if (categoriaNombre) {
        texto = `Mostrando <strong>${count}</strong> productos en "${categoriaNombre}"`;
      } else {
        texto = `Mostrando <strong>1-${count}</strong> de <strong>${count}</strong> productos`;
      }

      resultsCount.innerHTML = texto;
    }
  }

  function loadCategoriaFromSubcategoria(subcategoriaNombre) {
    return new Promise((resolve, reject) => {
      const formData = new FormData();
      formData.append("funcion", "obtener_categoria_por_subcategoria");
      formData.append("nombre_subcategoria", subcategoriaNombre);

      fetch("../Controllers/SubcategoriaController.php", {
        method: "POST",
        body: formData,
      })
        .then(response => response.json())
        .then(data => {
          if (data.success && data.categoria_nombre) {
            resolve(data.categoria_nombre);
          } else {
            resolve(null);
          }
        })
        .catch(error => {
          console.error("Error cargando categoría:", error);
          reject(error);
        });
    });
  }
});

// ============ FUNCIONES GLOBALES ============

// Función global para agregar al carrito
function addToCart(productId, quantity = 1) {
  // Verificar si el usuario tiene sesión primero
  checkSessionBeforeAdd(productId, quantity);
}

// Función para verificar sesión antes de agregar
function checkSessionBeforeAdd(productId, quantity) {
  fetch("../Controllers/UsuarioController.php", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: "funcion=verificar_sesion"
  })
    .then(response => response.text())
    .then(response => {
      if (response && response !== "") {
        // Usuario tiene sesión, proceder a agregar
        proceedAddToCart(productId, quantity);
      } else {
        // Usuario no tiene sesión, mostrar alerta
        showLoginAlert(productId, quantity);
      }
    })
    .catch(error => {
      console.error("Error verificando sesión:", error);
      proceedAddToCart(productId, quantity);
    });
}

// Función para mostrar alerta de login
function showLoginAlert(productId, quantity) {
  Swal.fire({
    title: "Inicia sesión",
    text: "Debes iniciar sesión para agregar productos al carrito",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#3085d6",
    cancelButtonColor: "#d33",
    confirmButtonText: "Iniciar sesión",
    cancelButtonText: "Cancelar"
  }).then((result) => {
    if (result.isConfirmed) {
      // Guardar el producto para agregar después del login
      sessionStorage.setItem('pendingCartAdd', JSON.stringify({
        productId: productId,
        quantity: quantity
      }));
      window.location.href = "../Views/login.php?redirect=" + encodeURIComponent(window.location.href);
    }
  });
}

// Función principal para agregar al carrito
function proceedAddToCart(productId, quantity = 1) {
  // El productId ya viene encriptado desde el servidor
  // Necesitamos asegurarnos de que los caracteres especiales se mantengan

  // Si el ID tiene espacios, convertirlos a +
  const processedId = productId.replace(/ /g, '+');

  const formData = new FormData();
  formData.append("funcion", "agregar_al_carrito");
  formData.append("id_producto_tienda", processedId);
  formData.append("cantidad", quantity);

  fetch("../Controllers/CarritoController.php", {
    method: "POST",
    body: formData,
  })
    .then((response) => {
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }
      return response.json();
    })
    .then((data) => {
      if (data.success) {
        Swal.fire({
          icon: "success",
          title: "¡Producto agregado!",
          text: data.mensaje || "El producto se ha añadido al carrito",
          timer: 2000,
          showConfirmButton: false,
          position: 'top-end',
          toast: true
        });

        // Actualizar contadores
        updateCartCounter(data.cantidad_total);

      } else if (data.error === 'no_sesion') {
        // Esto no debería ocurrir si verificamos antes, pero por si acaso
        showLoginAlert(productId, quantity);
      } else {
        Swal.fire({
          icon: "error",
          title: "Error",
          text: data.error || "Error al agregar al carrito",
          confirmButtonText: "Entendido"
        });
      }
    })
    .catch((error) => {
      console.error("Error en addToCart:", error);
      Swal.fire({
        icon: "error",
        title: "Error de conexión",
        text: "No se pudo conectar con el servidor. Intenta nuevamente.",
        confirmButtonText: "Reintentar"
      });
    });
}

// Función optimizada para actualizar contador
function updateCartCounter(cantidad = null) {
  if (cantidad !== null) {
    // Usar la cantidad proporcionada
    updateCartDisplay(cantidad);
  } else {
    // Obtener la cantidad del servidor
    fetchCartCount();
  }
}

// Función para obtener la cantidad del servidor
function fetchCartCount() {
  const formData = new FormData();
  formData.append("funcion", "obtener_cantidad_total");

  fetch("../Controllers/CarritoController.php", {
    method: "POST",
    body: formData,
  })
    .then(response => response.json())
    .then(data => {
      if (data.cantidad_total !== undefined) {
        updateCartDisplay(data.cantidad_total);
      }
    })
    .catch(error => {
      console.error("Error obteniendo cantidad del carrito:", error);
    });
}

// Función para actualizar la visualización del contador
function updateCartDisplay(cantidad) {
  // Actualizar todos los contadores posibles
  const counters = document.querySelectorAll('.cart-counter, .cart-badge, .badge-cart');

  counters.forEach(counter => {
    counter.textContent = cantidad;
    counter.style.display = cantidad > 0 ? 'inline-block' : 'none';
  });

  // También actualizar elementos específicos
  const navCartCounter = document.getElementById('nav-cart-counter');
  if (navCartCounter) {
    navCartCounter.textContent = cantidad;
    navCartCounter.style.display = cantidad > 0 ? 'inline-block' : 'none';
  }
}

// Función para manejar agregados pendientes después del login
function handlePendingCartAdd() {
  const pendingAdd = sessionStorage.getItem('pendingCartAdd');
  if (pendingAdd) {
    try {
      const { productId, quantity } = JSON.parse(pendingAdd);
      sessionStorage.removeItem('pendingCartAdd');

      // Esperar un momento para asegurar que la sesión esté establecida
      setTimeout(() => {
        proceedAddToCart(productId, quantity);
      }, 500);
    } catch (error) {
      console.error("Error procesando agregado pendiente:", error);
      sessionStorage.removeItem('pendingCartAdd');
    }
  }
}

// Función para animar botón del carrito
function animateCartButton(button) {
  button.classList.add('cart-animation');
  setTimeout(() => {
    button.classList.remove('cart-animation');
  }, 300);
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

// Función para vista rápida
function quickView(productId) {
  // Mostrar loader mientras se carga
  Swal.fire({
    title: 'Cargando producto...',
    text: 'Por favor espere',
    allowOutsideClick: false,
    didOpen: () => {
      Swal.showLoading();
    }
  });

  // Desencriptar el ID del producto
  const processedId = productId.replace(/ /g, '+');
  
  const formData = new FormData();
  formData.append("funcion", "obtener_producto_rapido");
  formData.append("id_producto_tienda", processedId);

  fetch("../Controllers/ProductoTiendaController.php", {
    method: "POST",
    body: formData,
  })
    .then(response => {
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }
      return response.json();
    })
    .then(data => {
      Swal.close();
      
      if (data.success) {
        // Mostrar modal de vista rápida con los datos
        showQuickViewModal(data.producto);
      } else {
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: data.error || 'No se pudo cargar el producto',
          confirmButtonText: 'Entendido'
        });
      }
    })
    .catch(error => {
      Swal.close();
      console.error("Error en quickView:", error);
      Swal.fire({
        icon: 'error',
        title: 'Error de conexión',
        text: 'No se pudo cargar la información del producto',
        confirmButtonText: 'Reintentar'
      });
    });
}

// Función para mostrar el modal de vista rápida
function showQuickViewModal(producto) {
  // Crear el HTML del modal
  const modalHTML = `
    <div class="quick-view-modal">
      <div class="quick-view-overlay" onclick="closeQuickView()"></div>
      <div class="quick-view-content">
        <div class="quick-view-header">
          <h3>Vista Rápida</h3>
          <button class="quick-view-close" onclick="closeQuickView()">
            <i class="fas fa-times"></i>
          </button>
        </div>
        <div class="quick-view-body">
          <div class="row">
            <div class="col-md-6">
              <div class="quick-view-image">
                <img src="../Util/Img/Producto/${producto.imagen}" 
                     alt="${producto.producto}"
                     onerror="this.src='../Util/Img/Producto/producto_default.png'">
                     <div class="quick-view-badges">
                ${producto.descuento > 0 ? 
                  `<span class="quick-view-badge badge-discount">-${producto.descuento}%</span>` : ''}
                  ${producto.envio == 1
          ? '<span class="quick-view-badge badge-free-shipping">Envío Gratis</span>'
          : ""
        }
                ${producto.es_nuevo == 1 ? 
                  `<span class="quick-view-badge badge-new">Nuevo</span>` : ''}
                  </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="quick-view-details">
                <span class="product-brand">${producto.marca || 'Marca'}</span>
                <h2 class="product-title">${producto.producto}</h2>
                
                <div class="product-rating mb-3">
                  <div class="rating-stars">
                    ${generateStarRating(parseFloat(producto.calificacion) || 0)}
                  </div>
                  <span class="rating-count">(${producto.total_resenas || 0} reseñas)</span>
                </div>
                
                <div class="product-price mb-4">
                  ${producto.descuento > 0 ? 
                    `<span class="original-price">$${parseFloat(producto.precio).toFixed(2)}</span>
                     <span class="discount-percent">-${producto.descuento}%</span>` : ''}
                  <div class="current-price">$${parseFloat(producto.precio_descuento).toFixed(2)}</div>
                </div>
                
                <p class="product-description mb-4">${producto.detalles || 'Descripción no disponible'}</p>
                
                <div class="stock-status mb-4">
                  <span class="badge ${producto.stock > 0 ? 'badge-success' : 'badge-danger'}">
                    ${producto.stock > 0 ? 'En Stock' : 'Agotado'}
                  </span>
                  <small class="text-muted ml-2">${producto.stock} unidades disponibles</small>
                </div>
                
                <div class="quantity-selector mb-4">
                  <label class="mr-2">Cantidad:</label>
                  <div class="quantity-controls">
                    <button class="quantity-btn minus" onclick="adjustQuantity(-1)">-</button>
                    <input type="number" id="quickViewQuantity" value="1" min="1" max="${producto.stock}">
                    <button class="quantity-btn plus" onclick="adjustQuantity(1)">+</button>
                  </div>
                </div>
                
                <div class="action-buttons">
                  <button class="btn btn-primary btn-lg mr-2" 
                          onclick="addToCartFromQuickView('${producto.id}', ${producto.stock > 0})"
                          ${producto.stock <= 0 ? 'disabled' : ''}>
                    <i class="fas fa-shopping-cart mr-2"></i>
                    Agregar al Carrito
                  </button>
                  <button class="btn btn-outline-secondary btn-lg"
                          onclick="window.location.href='descripcion.php?name=${encodeURIComponent(producto.producto)}&id=${producto.id}'">
                    <i class="fas fa-external-link-alt mr-2"></i>
                    Ver Detalles Completos
                  </button>
                </div>
                
                <div class="product-meta mt-4">
                  <div class="meta-item">
                    <i class="fas fa-shipping-fast"></i>
                    <span>${producto.envio == 1 ? 'Envío Gratis' : 'Envío con costo'}</span>
                  </div>
                  <div class="meta-item">
                    <i class="fas fa-store"></i>
                    <span>Tienda: ${producto.tienda || 'NexusBuy'}</span>
                  </div>
                  <div class="meta-item">
                    <i class="fas fa-calendar-alt"></i>
                    <span>Agregado: ${formatDate(producto.fecha_creacion)}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  `;

  // Insertar el modal en el body
  const modalContainer = document.createElement('div');
  modalContainer.id = 'quickViewModalContainer';
  modalContainer.innerHTML = modalHTML;
  document.body.appendChild(modalContainer);
  
  // Prevenir scroll del body
  document.body.style.overflow = 'hidden';
}

// Función para cerrar vista rápida
function closeQuickView() {
  const modalContainer = document.getElementById('quickViewModalContainer');
  if (modalContainer) {
    document.body.removeChild(modalContainer);
    document.body.style.overflow = 'auto';
  }
}

// Función para ajustar cantidad
function adjustQuantity(change) {
  const quantityInput = document.getElementById('quickViewQuantity');
  if (!quantityInput) return;
  
  let currentValue = parseInt(quantityInput.value) || 1;
  const maxStock = parseInt(quantityInput.max) || 999;
  
  currentValue += change;
  if (currentValue < 1) currentValue = 1;
  if (currentValue > maxStock) currentValue = maxStock;
  
  quantityInput.value = currentValue;
}

// Función para agregar al carrito desde vista rápida
function addToCartFromQuickView(productId, hasStock) {
  if (!hasStock) {
    Swal.fire({
      icon: 'error',
      title: 'Producto agotado',
      text: 'Este producto no está disponible en este momento',
      confirmButtonText: 'Entendido'
    });
    return;
  }
  
  const quantityInput = document.getElementById('quickViewQuantity');
  const quantity = quantityInput ? parseInt(quantityInput.value) || 1 : 1;
  
  // Cerrar el modal primero
  closeQuickView();
  
  // Luego agregar al carrito
  addToCart(productId, quantity);
}

// Función para formatear fecha
function formatDate(dateString) {
  if (!dateString) return 'Fecha no disponible';
  
  const date = new Date(dateString);
  return date.toLocaleDateString('es-ES', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  });
}

// Teclas de acceso rápido para la vista rápida

// Manejar tecla ESC para cerrar vista rápida
document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') {
    closeQuickView();
  }
});

// Prevenir scroll cuando el modal está abierto
document.addEventListener('wheel', function(e) {
  if (document.getElementById('quickViewModalContainer')) {
    e.preventDefault();
  }
}, { passive: false });

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