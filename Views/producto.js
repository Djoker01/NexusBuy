// producto.js - VERSIÓN COMPLETA CON BÚSQUEDA Y TODAS LAS FUNCIONES

document.addEventListener("DOMContentLoaded", function () {
  // Variables globales
  let currentView = "grid";
  let currentPage = 1;
  let totalPages = 1;
  let currentSearchTerm = "";
  let allProducts = [];
  let currentSort = "popular";

  // Elementos DOM
  const productsGrid = document.getElementById("productsGrid");
  const resultsCount = document.getElementById("resultsCount");
  const viewButtons = document.querySelectorAll(".view-btn");
  const sortSelect = document.querySelector(".sort-select");
  const paginationContainer = document.getElementById("paginationContainer");

  // Inicializar
  init();

  function init() {
    setupEventListeners();
    loadProducts();
    
    // Verificar si hay agregados pendientes después del login
    handlePendingCartAdd();
    
    // Actualizar contador al cargar la página
    updateCartCounter();

    // Actualizar título según filtro
    updatePageTitle();
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
        currentSort = this.value;
        sortProducts(currentSort);
      });
    }
  }

  // Cargar productos
  async function loadProducts() {
    const urlParams = new URLSearchParams(window.location.search);
    
    if (urlParams.has('busqueda')) {
        const term = decodeURIComponent(urlParams.get('busqueda'));
        await searchProducts(term, 1);
    } else {
        await loadRegularProducts();
    }
  }

  // Función para cargar productos regulares (sin búsqueda)
  async function loadRegularProducts() {
    try {
        let funcion = 'llenar_productos';
        let bodyParams = new URLSearchParams();
        bodyParams.append('funcion', funcion);
        
        const urlParams = new URLSearchParams(window.location.search);
        const marcaParam = urlParams.get('marca');
        const categoriaParam = urlParams.get('categoria');
        const subcategoriaParam = urlParams.get('subcategoria');
        const filtroParam = urlParams.get('filtro');
        
        // console.log("Filtrando por categoría:", categoriaParam); // Debug
        
        // Agregar parámetros según corresponda
        if (filtroParam === 'nuevos') {
            bodyParams.append('filtro_nuevos', true);
        } else if (marcaParam) {
            bodyParams.append('nombre_marca', marcaParam);
        } else if (subcategoriaParam) {
            const subcategoriaId = await buscarSubcategoriaPorNombre(subcategoriaParam);
            if (subcategoriaId) {
                bodyParams.append('id_subcategoria', subcategoriaId);
            } else {
                showNoProductsMessage(null, subcategoriaParam);
                return;
            }
        } else if (categoriaParam) {
            // IMPORTANTE: Enviar el nombre de la categoría
            bodyParams.append('nombre_categoria', categoriaParam);
        }
        
        // console.log("Body params:", bodyParams.toString()); // Debug
        
        const response = await fetch('../Controllers/ProductoTiendaController.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: bodyParams.toString()
        });
        
        console.log("Response status:", response.status); // Debug
        
        if (!response.ok) {
            const errorText = await response.text();
            console.error("Error response:", errorText);
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        
        if (!Array.isArray(data)) {
            console.error('La respuesta no es un array:', data);
            showError("Error en los datos recibidos");
            return;
        }
        
        allProducts = data;
        sortProducts(currentSort);
        updateResultsCount(allProducts.length);
        displayProducts(allProducts);
        
    } catch (error) {
        console.error('Error loading regular products:', error);
        showError('Error cargando productos: ' + error.message);
    }
}

  // Función para buscar productos
  async function searchProducts(searchTerm, page = 1, limit = 12) {
    try {
        showLoading();
        
        // console.log("=== INICIANDO BÚSQUEDA ===");
        // console.log("🔍 Término:", searchTerm);
        // console.log("📄 Página:", page);
        
        const formData = new FormData();
        formData.append('funcion', 'buscar_productos');
        formData.append('termino', searchTerm);
        formData.append('pagina', page);
        formData.append('limite', limit);
        
        // console.log("📤 Enviando a BusquedaController.php...");
        
        const response = await fetch('../Controllers/BusquedaController.php', {
            method: 'POST',
            body: formData
        });
        
        // console.log("📥 Status:", response.status);
        
        const rawText = await response.text();
        // console.log("📄 Respuesta cruda:", rawText.substring(0, 500) + "...");
        
        const data = JSON.parse(rawText);
        // console.log("📊 Datos parseados:", data);
        
        if (data.success) {
            // console.log(`✅ ${data.productos.length} productos encontrados de ${data.total_resultados} totales`);
            
            if (data.productos.length === 0) {
                console.log("⚠️  Array de productos está VACÍO");
                showNoSearchResults(searchTerm);
                return;
            }
            
            // Mostrar nombres de los primeros 3 productos para verificar
            data.productos.slice(0, 3).forEach((prod, i) => {
                // console.log(`   ${i+1}. ${prod.producto} (${prod.marca})`);
            });
            
            allProducts = data.productos;
            totalPages = data.total_paginas;
            currentPage = data.pagina_actual;
            
            sortProducts(currentSort);
            updateSearchResultsCount(data.total_resultados, searchTerm);
            displayProducts(allProducts);
            setupPagination(data.total_paginas, data.pagina_actual, searchTerm);
        } else {
            console.log("❌ Error del servidor:", data.error);
            showError(data.error || 'Error en la búsqueda');
        }
        
    } catch (error) {
        console.error('❌ Error en searchProducts:', error);
        showError('Error en la búsqueda: ' + error.message);
    }
}

  // Función para buscar subcategoría por nombre
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

  // Función para mostrar mensaje cuando no hay resultados de búsqueda
  function showNoSearchResults(searchTerm) {
    productsGrid.innerHTML = `
        <div class="col-12 text-center py-5">
            <div class="alert alert-warning">
                <i class="fas fa-search fa-2x mb-3"></i>
                <h4>No se encontraron resultados</h4>
                <p>No hay productos que coincidan con: "<strong>${escapeHtml(searchTerm)}</strong>"</p>
                <div class="mt-3">
                    <p class="text-muted">Sugerencias:</p>
                    <ul class="list-unstyled">
                        <li><i class="fas fa-check text-success"></i> Revisa la ortografía</li>
                        <li><i class="fas fa-check text-success"></i> Usa términos más generales</li>
                        <li><i class="fas fa-check text-success"></i> Intenta con sinónimos</li>
                    </ul>
                </div>
                <a href="producto.php" class="btn btn-primary mt-2">
                    <i class="fas fa-store"></i> Ver todos los productos
                </a>
            </div>
        </div>
    `;
    
    if (resultsCount) {
      resultsCount.textContent = `0 resultados para "${escapeHtml(searchTerm)}"`;
    }
    
    hidePagination();
  }

  // Función para mostrar mensaje cuando no hay productos
  function showNoProductsMessage(categoriaNombre = null, subcategoriaNombre = null) {
    const urlParams = new URLSearchParams(window.location.search);
    const marcaParam = urlParams.get('marca');
    
    let message = 'No se encontraron productos disponibles.';
    let title = 'No hay productos';
    
    if (marcaParam) {
      title = 'Marca no encontrada';
      message = `No se encontraron productos de la marca "${escapeHtml(decodeURIComponent(marcaParam))}".`;
    } else if (categoriaNombre) {
      title = 'Categoría vacía';
      message = `No se encontraron productos en la categoría "${escapeHtml(decodeURIComponent(categoriaNombre))}".`;
    } else if (subcategoriaNombre) {
      title = 'Subcategoría vacía';
      message = `No se encontraron productos en la subcategoría "${escapeHtml(decodeURIComponent(subcategoriaNombre))}".`;
    }
    
    productsGrid.innerHTML = `
        <div class="col-12 text-center py-5">
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle fa-2x mb-3"></i>
                <h4>${title}</h4>
                <p>${message}</p>
                <a href="producto.php" class="btn btn-primary mt-2">
                    <i class="fas fa-store"></i> Ver todos los productos
                </a>
            </div>
        </div>
    `;
  }

  function sortProducts(sortBy) {
    if (!allProducts || allProducts.length === 0) return;
    
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
        // Si hay fecha de creación, ordenar por ella
        sortedProducts.sort((a, b) => {
          if (a.fecha_creacion && b.fecha_creacion) {
            return new Date(b.fecha_creacion) - new Date(a.fecha_creacion);
          }
          return 0;
        });
        break;
      case "popular":
      default:
        // Orden por defecto (popularidad) - mantener orden original
        break;
    }

    displayProducts(sortedProducts);
  }

  // Función para mostrar productos
  function displayProducts(products) {
    if (!products || products.length === 0) {
      const urlParams = new URLSearchParams(window.location.search);
      const busquedaParam = urlParams.get('busqueda');
      
      if (busquedaParam) {
        showNoSearchResults(decodeURIComponent(busquedaParam));
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
      
      // Manejar el ID del producto
      const productId = product.id || '';

      html += `
          <div class="product-card ${listClass}">
              <div class="product-image">
                  <img src="../Util/Img/Producto/${product.imagen}" alt="${escapeHtml(product.producto)}" 
                       onerror="this.src='../Util/Img/Producto/producto_default.png'">
                  <div class="product-badges">
                      ${discount > 0 ? `<span class="product-badge badge-discount">-${discount}%</span>` : ""}
                      ${product.envio && (product.envio === 1 || product.envio === 'Envío gratis' || product.envio === true) 
                        ? '<span class="product-badge badge-free-shipping">Envío Gratis</span>' : ""}
                      ${isNew ? '<span class="product-badge badge-new">Nuevo</span>' : ""}
                  </div>
                  <div class="product-actions">
                      <button class="action-btn favorite-btn" title="Agregar a favoritos" data-id_producto="${productId}">
                          <i class="far fa-heart"></i>
                      </button>
                      <button class="action-btn quick-view-btn" title="Vista rápida" data-id_producto="${productId}">
                          <i class="far fa-eye"></i>
                      </button>
                  </div>
              </div>
              <div class="product-content">
                  <span class="product-brand">${escapeHtml(product.marca || "Marca")}</span>
                  <a href="descripcion.php?name=${encodeURIComponent(product.producto)}&id=${productId}" class="product-title">
                      ${escapeHtml(product.producto)}
                  </a>
                  <div class="product-rating">
                      <div class="rating-stars">
                          ${generateStarRating(parseFloat(product.calificacion) || 0)}
                      </div>
                      <span class="rating-count">(${product.total_resenas || 0} Reseñas)</span>
                  </div>
                  ${isListView ? `<p class="product-description">${escapeHtml(product.detalles || "Descripción no disponible")}</p>` : ""}
                  ${product.etiquetas && product.etiquetas.length > 0 ? `
                    <div class="product-tags mt-2">
                        ${product.etiquetas.split(',').slice(0, 3).map(tag => 
                          `<span class="product-tag">${escapeHtml(tag.trim())}</span>`
                        ).join('')}
                    </div>
                  ` : ""}
                  <div class="product-price">
                      ${discount > 0 ? `
                          <span class="original-price">$${originalPrice.toFixed(2)}</span>
                          <span class="discount-percent">-${discount}%</span>
                      ` : ""}
                      <div class="current-price">$${finalPrice.toFixed(2)}</div>
                  </div>
                  <div class="product-meta">
                      <span class="stock-status ${(product.stock > 0) ? "in-stock" : "out-of-stock"}">
                          ${(product.stock > 0) ? "En Stock" : "Agotado"}
                      </span>
                      <button class="add-to-cart-btn" data-id_producto="${productId}" 
                              ${(product.stock <= 0) ? "disabled" : ""}>
                          <i class="fas fa-shopping-cart"></i>
                          ${(product.stock > 0) ? "Agregar" : "Agotado"}
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

  function setupClearSearchButton() {
    const clearBtn = document.querySelector('a[href="producto.php"]');
    if (clearBtn && clearBtn.textContent.includes('Limpiar búsqueda')) {
        clearBtn.addEventListener('click', function(e) {
            e.preventDefault();
            window.location.href = 'producto.php';
        });
    }
}

// Llamar esta función después de cargar los productos
document.addEventListener('DOMContentLoaded', function() {
    // ... código existente ...
    setupClearSearchButton();
});

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
    document.querySelectorAll('.quick-view-btn').forEach(btn => {
      btn.addEventListener('click', function(e) {
        e.preventDefault();
        const productId = btn.getAttribute('data-id_producto');
        if (productId) {
          quickView(productId);
        }
      });
    });
    
    // Botones de agregar al carrito
    document.querySelectorAll('.add-to-cart-btn:not([disabled])').forEach(btn => {
      btn.addEventListener('click', function(e) {
        e.preventDefault();
        const productId = this.getAttribute('data-id_producto');
        
        if (!productId) {
          console.error('No product ID found');
          return;
        }
        
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
            <p class="text-muted">${escapeHtml(message)}</p>
            <button class="btn btn-primary mt-2" onclick="location.reload()">Reintentar</button>
        </div>
    `;
  }

  // Función para actualizar contador de resultados regulares
  function updateResultsCount(count) {
    const resultsElement = document.getElementById('resultsCount');
    if (resultsElement) {
      const urlParams = new URLSearchParams(window.location.search);
      const filtroParam = urlParams.get('filtro');
      const marcaParam = urlParams.get('marca');
      const categoriaParam = urlParams.get('categoria');
      const subcategoriaParam = urlParams.get('subcategoria');
      
      let message = '';
      if (filtroParam === 'nuevos') {
        message = `${count} productos nuevos encontrados`;
      } else if (marcaParam) {
        message = `${count} productos de la marca "${decodeURIComponent(marcaParam)}"`;
      } else if (categoriaParam) {
        message = `${count} productos en "${decodeURIComponent(categoriaParam)}"`;
      } else if (subcategoriaParam) {
        message = `${count} productos en "${decodeURIComponent(subcategoriaParam)}"`;
      } else {
        message = `${count} productos encontrados`;
      }
      
      resultsElement.textContent = message;
    }
  }

  // Función para actualizar contador de resultados de búsqueda
  function updateSearchResultsCount(count, searchTerm) {
    const resultsElement = document.getElementById('resultsCount');
    if (resultsElement) {
      resultsElement.textContent = `${count} resultados para "${escapeHtml(searchTerm)}"`;
    }
  }

  // Función para configurar paginación en búsquedas
  function setupPagination(totalPages, currentPage, searchTerm) {
    if (!paginationContainer || totalPages <= 1) {
      hidePagination();
      return;
    }
    
    paginationContainer.style.display = 'block';
    
    let paginationHTML = `
        <nav aria-label="Page navigation">
            <ul class="pagination justify-content-center">
    `;
    
    // Botón anterior
    if (currentPage > 1) {
      paginationHTML += `
          <li class="page-item">
              <a class="page-link" href="#" onclick="handleSearchPage(${currentPage - 1}, '${escapeHtml(searchTerm)}'); return false;">
                  <i class="fas fa-chevron-left"></i>
              </a>
          </li>
      `;
    } else {
      paginationHTML += `
          <li class="page-item disabled">
              <span class="page-link">
                  <i class="fas fa-chevron-left"></i>
              </span>
          </li>
      `;
    }
    
    // Números de página
    const maxVisiblePages = 5;
    let startPage = Math.max(1, currentPage - Math.floor(maxVisiblePages / 2));
    let endPage = Math.min(totalPages, startPage + maxVisiblePages - 1);
    
    if (endPage - startPage + 1 < maxVisiblePages) {
      startPage = Math.max(1, endPage - maxVisiblePages + 1);
    }
    
    for (let i = startPage; i <= endPage; i++) {
      if (i === currentPage) {
        paginationHTML += `
            <li class="page-item active">
                <span class="page-link">${i}</span>
            </li>
        `;
      } else {
        paginationHTML += `
            <li class="page-item">
                <a class="page-link" href="#" onclick="handleSearchPage(${i}, '${escapeHtml(searchTerm)}'); return false;">${i}</a>
            </li>
        `;
      }
    }
    
    // Botón siguiente
    if (currentPage < totalPages) {
      paginationHTML += `
          <li class="page-item">
              <a class="page-link" href="#" onclick="handleSearchPage(${currentPage + 1}, '${escapeHtml(searchTerm)}'); return false;">
                  <i class="fas fa-chevron-right"></i>
              </a>
          </li>
      `;
    } else {
      paginationHTML += `
          <li class="page-item disabled">
              <span class="page-link">
                  <i class="fas fa-chevron-right"></i>
              </span>
          </li>
      `;
    }
    
    paginationHTML += `
            </ul>
        </nav>
    `;
    
    paginationContainer.innerHTML = paginationHTML;
  }

  function hidePagination() {
    if (paginationContainer) {
      paginationContainer.style.display = 'none';
    }
  }
});

// ============ FUNCIONES GLOBALES ============

// Función para manejar cambio de página en búsquedas (global)
function handleSearchPage(page, searchTerm) {
  event.preventDefault();
  
  // Buscar función searchProducts en el contexto actual
  if (typeof searchProducts === 'function') {
    searchProducts(searchTerm, page);
  } else {
    // Recargar la página con el nuevo número de página
    const url = new URL(window.location.href);
    url.searchParams.set('pagina', page);
    window.location.href = url.toString();
  }
  
  // Scroll al inicio de los resultados
  setTimeout(() => {
    const productsGrid = document.getElementById('productsGrid');
    if (productsGrid) {
      window.scrollTo({
        top: productsGrid.offsetTop - 100,
        behavior: 'smooth'
      });
    }
  }, 100);
}

// Función para escapar HTML
function escapeHtml(text) {
  if (!text) return '';
  const div = document.createElement('div');
  div.textContent = text;
  return div.innerHTML;
}

// Función para generar estrellas
function generateStarRating(rating) {
  let stars = "";
  const fullStars = Math.floor(rating);
  const hasHalfStar = rating % 1 >= 0.5;

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
  const counters = document.querySelectorAll('.cart-counter, .cart-badge, .badge-cart, #cart-badge');

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
        
        // Actualizar contador de favoritos
        updateFavoritesCounter();
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
        
        // Actualizar contador de favoritos
        updateFavoritesCounter();
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

// Función para actualizar contador de favoritos
function updateFavoritesCounter() {
  const formData = new FormData();
  formData.append("funcion", "contar_favoritos");

  fetch("../Controllers/FavoritoController.php", {
    method: "POST",
    body: formData,
  })
    .then(response => response.json())
    .then(data => {
      if (data.cantidad !== undefined) {
        const counters = document.querySelectorAll('#favoritos-badge, .favoritos-counter');
        counters.forEach(counter => {
          counter.textContent = data.cantidad;
          counter.style.display = data.cantidad > 0 ? 'inline-block' : 'none';
        });
      }
    })
    .catch(error => {
      console.error("Error obteniendo cantidad de favoritos:", error);
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
                  ${producto.envio == 1 || producto.envio === 'Envío gratis'
          ? '<span class="quick-view-badge badge-free-shipping">Envío Gratis</span>'
          : ""}
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
                    <span>${producto.envio == 1 || producto.envio === 'Envío gratis' ? 'Envío Gratis' : 'Envío con costo'}</span>
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

// Función para actualizar título de página según filtro
function updatePageTitle() {
  const urlParams = new URLSearchParams(window.location.search);
  const busquedaParam = urlParams.get('busqueda');
  const filtroParam = urlParams.get('filtro');
  const marcaParam = urlParams.get('marca');
  const categoriaParam = urlParams.get('categoria');
  const subcategoriaParam = urlParams.get('subcategoria');
  
  let title = 'Productos | NexusBuy';
  
  if (busquedaParam) {
    title = `Buscar: ${decodeURIComponent(busquedaParam)} | NexusBuy`;
  } else if (filtroParam === 'nuevos') {
    title = 'Nuevos Productos | NexusBuy';
  } else if (marcaParam) {
    title = `Productos de ${decodeURIComponent(marcaParam)} | NexusBuy`;
  } else if (subcategoriaParam) {
    title = `${decodeURIComponent(subcategoriaParam)} | NexusBuy`;
  } else if (categoriaParam) {
    title = `${decodeURIComponent(categoriaParam)} | NexusBuy`;
  }
  
  document.title = title;
}

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

// Función para inicializar cuando la página carga
window.onload = function() {
  // Ya está inicializado en DOMContentLoaded
};