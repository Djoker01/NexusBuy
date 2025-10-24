$(document).ready(function () {
  cargarMenuCategorias();
});

// Hacer las funciones globales para que estén disponibles en los onclick
window.filtrarPorSubcategoria = async function(id_subcategoria, nombre_subcategoria) {
  try {
      console.log('Filtrando por subcategoría:', id_subcategoria, nombre_subcategoria);
      
      // Mostrar loading
      $('#productos').html(`
          <div class="col-12 text-center py-5">
              <div class="spinner-border text-primary" role="status">
                  <span class="sr-only">Cargando productos...</span>
              </div>
              <p class="text-muted mt-2">Cargando productos de ${nombre_subcategoria}...</p>
          </div>
      `);

      // Actualizar interfaz
      $('.card-title').html(`Productos - ${nombre_subcategoria} <span class="badge bg-secondary">Cargando...</span>`);
      document.title = `${nombre_subcategoria} | NexusBuy`;

      // Actualizar breadcrumb
      $('.breadcrumb').html(`
          <li class="breadcrumb-item"><a href="index.php">Home</a></li>
          <li class="breadcrumb-item active">${nombre_subcategoria}</li>
      `);

      // Llamar al controller
      const funcion = "llenar_productos";
      const response = await fetch('Controllers/ProductoTiendaController.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: `funcion=${funcion}&id_subcategoria=${id_subcategoria}`
      });

      if (response.ok) {
          const data = await response.text();
          console.log('Respuesta del servidor:', data);
          
          try {
              const productos = JSON.parse(data);
              mostrarProductosFiltrados(productos, nombre_subcategoria);
          } catch (parseError) {
              console.error('Error parseando JSON:', parseError);
              mostrarErrorCarga('Error en el formato de datos');
          }
      } else {
          throw new Error(`Error HTTP: ${response.status}`);
      }
  } catch (error) {
      console.error('Error:', error);
      mostrarErrorCarga(error.message);
  }
};

window.filtrarPorSubcategoria = async function(idSubcategoria, nombreSubcategoria) {
    try {
        //console.log('Filtrando por subcategoría:', idSubcategoria, nombreSubcategoria);
        
        // Mostrar loading
        $('#productos').html(`
            <div class="col-12 text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="sr-only">Cargando productos...</span>
                </div>
                <p class="text-muted mt-2">Cargando productos de "${nombreSubcategoria}"...</p>
            </div>
        `);
        
        // Actualizar estado
        window.filtroActivo = true;
        window.filtroSubcategoria = {
            id: idSubcategoria,
            nombre: nombreSubcategoria
        };
        
        const response = await fetch('Controllers/BusquedaController.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `funcion=filtrar_por_subcategoria&id_subcategoria=${idSubcategoria}`
        });
        
        if (response.ok) {
            const data = await response.json();
            
            if (data.success) {
                window.mostrarResultadosFiltro(data, nombreSubcategoria, 'subcategoria');
            } else {
                throw new Error(data.error || 'Error en el filtro');
            }
        } else {
            throw new Error('Error de conexión');
        }
        
    } catch (error) {
        console.error('Error filtrando por subcategoría:', error);
        window.mostrarErrorFiltro('Error al cargar los productos de la categoría');
    }
};

window.mostrarTodosLosProductos = function() {
  console.log('Mostrando todos los productos');
  window.location.href = 'index.php';
};

// Funciones auxiliares
async function cargarMenuCategorias() {
  try {
      //console.log('Cargando menú de categorías...');
      const funcion = "obtener_categorias_menu";
      const response = await fetch('Controllers/CategoriaController.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: 'funcion=' + funcion
      });

      if (response.ok) {
          const data = await response.text();
          //console.log('Datos de categorías recibidos:', data);
          const categorias = JSON.parse(data);
          generarMenuCategorias(categorias);
      } else {
          console.error('Error al cargar categorías:', response.status);
          mostrarErrorMenu();
      }
  } catch (error) {
      console.error('Error cargando categorías:', error);
      mostrarErrorMenu();
  }
}

function generarMenuCategorias(categorias) {
  //console.log('Generando menú con', categorias.length, 'categorías');
  
  let menuHTML = `
      <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
          <li class="nav-header">CATEGORÍAS</li>
          <li class="nav-item">
              <a href="index.php" class="nav-link">
                  <i class="nav-icon fas fa-home"></i>
                  <p>Todos los Productos</p>
              </a>
          </li>
  `;

  categorias.forEach(categoria => {
      const hasSubcategories = categoria.subcategorias && categoria.subcategorias.length > 0;
      const menuItemClass = hasSubcategories ? 'nav-item has-treeview' : 'nav-item';
      const menuLinkClass = hasSubcategories ? 'nav-link' : 'nav-link';
      
      menuHTML += `
          <li class="${menuItemClass}">
              <a href="#" class="${menuLinkClass}" ${!hasSubcategories ? `onclick="filtrarPorCategoria(${categoria.id}, '${categoria.nombre.replace(/'/g, "\\'")}')"` : ''}>
                  <i class="nav-icon ${categoria.icono}"></i>
                  <p>
                      ${categoria.nombre}
                      ${hasSubcategories ? '<i class="right fas fa-angle-left"></i>' : ''}
                  </p>
              </a>
      `;

      if (hasSubcategories) {
          menuHTML += `
              <ul class="nav nav-treeview">
          `;
          
          categoria.subcategorias.forEach(subcategoria => {
              menuHTML += `
                  <li class="nav-item">
                      <a href="#" class="nav-link" onclick="filtrarPorSubcategoria(${subcategoria.id}, '${subcategoria.nombre.replace(/'/g, "\\'")}')">
                          <i class="far fa-circle nav-icon"></i>
                          <p>${subcategoria.nombre}</p>
                      </a>
                  </li>
              `;
          });

          menuHTML += `</ul>`;
      }

      menuHTML += `</li>`;
  });

  menuHTML += `</ul>`;
  
  $('#menu-categorias-dinamico').html(menuHTML);
  
  // Inicializar el plugin treeview de AdminLTE
  try {
      $('[data-widget="treeview"]').Treeview('init');
      //console.log('Treeview inicializado correctamente');
  } catch (e) {
      console.warn('Error inicializando treeview:', e);
  }
}

function mostrarProductosFiltrados(productos, nombre_filtro) {
  console.log('Mostrando', productos.length, 'productos para:', nombre_filtro);
  
  if (productos.length === 0) {
      $('#productos').html(`
          <div class="col-12 text-center py-5">
              <div class="alert alert-info">
                  <i class="fas fa-info-circle mr-2"></i>
                  No hay productos disponibles en <strong>${nombre_filtro}</strong>.
              </div>
              <button class="btn btn-primary mt-3" onclick="mostrarTodosLosProductos()">
                  <i class="fas fa-arrow-left mr-2"></i>
                  Ver Todos los Productos
              </button>
          </div>
      `);
      
      $('.card-title').html(`Productos - ${nombre_filtro} <span class="badge bg-warning">0 productos</span>`);
      return;
  }

  let template = '';
  productos.forEach(producto => {
      template += `
      <div class="col-sm-2 mb-4">
          <div class="card product-card h-100">
              <div class="card-body">
                  <div class="row">
                      <div class="col-sm-12">
                          <img src="Util/Img/Producto/${producto.imagen}" class="img-fluid product-image" alt="${producto.producto}">
                      </div>
                      <div class="col-sm-12 mt-2">
                          <span class="text-muted float-left">${producto.marca}</span></br>
                          <a class="titulo_producto" href="Views/descripcion.php?name=${encodeURIComponent(producto.producto)}&&id=${producto.id}">${producto.producto}</a>`;
      
      if (producto.envio == 'gratis') {
          template += `</br><span class="badge bg-success">Envio gratis</span>`;
      }
      
      if (producto.calificacion != 0) {
          template += `</br>`;
          for (let index = 0; index < producto.calificacion; index++) {
              template += `<i class="fas fa-star text-warning"></i>`;
          }
          let estrellas_faltantes = 5 - producto.calificacion;
          for (let index = 0; index < estrellas_faltantes; index++) {
              template += `<i class="far fa-star text-warning"></i>`;
          }
          template += `</br>`;
      }
      
      if (producto.descuento != 0) {
          template += `
              <span class="text-muted" style="text-decoration: line-through">$ ${producto.precio}</span>
              <span class="text-muted">-${producto.descuento}%</span></br>
          `;
      }
      
      template += `           
          <h4 class="text-danger">$ ${producto.precio_descuento}</h4>
      </div>
  </div>
</div>
</div>
</div>`;
  });

  $('#productos').html(template);
  $('.card-title').html(`Productos - ${nombre_filtro} <span class="badge bg-primary">${productos.length} productos</span>`);
  
  console.log('Productos mostrados correctamente');
}

function mostrarErrorCarga(mensaje) {
  $('#productos').html(`
      <div class="col-12 text-center py-5">
          <div class="alert alert-danger">
              <i class="fas fa-exclamation-triangle mr-2"></i>
              ${mensaje || 'Error al cargar los productos. Por favor, intenta nuevamente.'}
          </div>
          <button class="btn btn-primary mt-3" onclick="mostrarTodosLosProductos()">
              <i class="fas fa-arrow-left mr-2"></i>
              Volver a Todos los Productos
          </button>
      </div>
  `);
}

function mostrarErrorMenu() {
  $('#menu-categorias-dinamico').html(`
      <div class="alert alert-warning">
          <i class="fas fa-exclamation-triangle mr-2"></i>
          Error al cargar las categorías. Recarga la página.
      </div>
  `);
}