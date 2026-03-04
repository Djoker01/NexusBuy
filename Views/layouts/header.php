<!DOCTYPE html>
<html lang="es">
<?php
// Headers de seguridad
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: DENY");
header("X-XSS-Protection: 1; mode=block");
header("Strict-Transport-Security: max-age=31536000; includeSubDomains");
header("Referrer-Policy: strict-origin-when-cross-origin");
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' https://cdnjs.cloudflare.com; style-src 'self' 'unsafe-inline' https://cdnjs.cloudflare.com; img-src 'self' data: https:");

// Iniciar sesión segura
if (session_status() === PHP_SESSION_NONE) {
  session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '',
    'secure' => true, // Solo HTTPS en producción
    'httponly' => true,
    'samesite' => 'Strict'
  ]);
  session_start();
}

// Obtener parámetros de la URL
$nombre_categoria = isset($_GET['categoria']) ? urldecode($_GET['categoria']) : null;
$nombre_subcategoria = isset($_GET['subcategoria']) ? urldecode($_GET['subcategoria']) : null;
$nombre_marca = isset($_GET['marca']) ? urldecode($_GET['marca']) : null;
$busqueda = isset($_GET['busqueda']) ? urldecode($_GET['busqueda']) : null;
$filtro_nuevos = isset($_GET['filtro']) && $_GET['filtro'] === 'nuevos';

// Establecer título según parámetros
if ($busqueda) {
  $titulo_pagina = "Resultados para: " . htmlspecialchars($busqueda);
  $_SESSION['busqueda_filtro'] = $busqueda;
} elseif ($filtro_nuevos) {
  $titulo_pagina = "Nuevos Productos";
  $_SESSION['filtro_nuevos'] = true;
} elseif ($nombre_marca) {
  $titulo_pagina = "Productos de " . $nombre_marca;
  $_SESSION['marca_filtro'] = $nombre_marca;
} elseif ($nombre_subcategoria) {
  $titulo_pagina = $nombre_subcategoria;
  $_SESSION['subcategoria_filtro'] = $nombre_subcategoria;
} elseif ($nombre_categoria) {
  $titulo_pagina = $nombre_categoria;
  $_SESSION['categoria_filtro'] = $nombre_categoria;
}
?>

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="Descubre los mejores productos al mejor precio en NexusBuy. Envío gratis, ofertas exclusivas y la mejor experiencia de compra online.">
  <script>
    // Variable global con la ruta base desde PHP
    var BASE_PATH = "<?php echo $base_path; ?>";
    var BASE_PATH_URL = "<?php echo $base_path_url; ?>";
    // USUARIO_ACTUAL y TIENDA_ACTUAL ya vienen de auth_check.php
    // console.log('Header cargado - Usuario:', window.USUARIO_ACTUAL?.nombre_completo);
    // console.log('Header cargado - Tienda:', window.TIENDA_ACTUAL?.nombre);
  </script>
  <!-- Librerias -->
  <link rel="stylesheet" href="<?php echo $base_path; ?>Util/css/Librerias/font awesome/all.min.css">
  <link rel="stylesheet" href="<?php echo $base_path; ?>Util/css/Librerias/fonts Poppins/fonts_Poppins.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="<?php echo $base_path; ?>Util/css/adminlte.min.css">
  <link rel="stylesheet" href="<?php echo $base_path; ?>Util/css/nexusbuy.css">
  <link rel="stylesheet" href="<?php echo $base_path; ?>Util/css/dropdown.css">
  <link rel="stylesheet" href="<?php echo $base_path; ?>Util/css/sweetalert2.min.css">
  <link rel="shortcut icon" href="<?php echo $base_path; ?>Util/Img/png/favicon.ico" type="image/x-icon">
  <script src="<?php echo $base_path; ?>Util/Js/jquery.min.js"></script>
  <title><?php echo $pageTitle; ?> | NexusBuy</title>
</head>

<body class="hold-transition sidebar-mini">
  <!-- Site wrapper -->
  <div class="wrapper">
    <!-- Navbar -->
    <nav class="main-header navbar navbar-expand navbar-white navbar-light">
      <!-- Left navbar links -->
      <ul class="navbar-nav">
        <li class="nav-item">
          <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
        </li>
        <li class="nav-item d-sm-inline-block">
          <a href="<?php echo $base_path; ?>index.php" class="nav-link"><i class="fas fa-home"></i> Home</a>
        </li>
        <li class="nav-item d-sm-inline-block">
          <a href="<?php echo $base_path_url; ?>soporte.php" class="nav-link"><i class="fas fa-headset"></i> Soporte</a>
        </li>
      </ul>

      <!-- Right navbar links -->
      <ul class="navbar-nav ml-auto">
        <!-- Search -->
        <li class="nav-item">
          <a class="nav-link" data-widget="navbar-search" href="#" role="button">
            <i class="fas fa-search"></i>
          </a>
          <div class="navbar-search-block">
            <form class="form-inline" id="form-busqueda" onsubmit="handleSearch(event)">
              <div class="input-group input-group-sm">
                <input class="form-control form-control-navbar"
                  type="search"
                  id="input-busqueda"
                  placeholder="Buscar productos, marcas, categorías..."
                  aria-label="Buscar"
                  autocomplete="off">
                <div class="input-group-append">
                  <button class="btn btn-navbar" type="submit">
                    <i class="fas fa-search"></i>
                  </button>
                  <button class="btn btn-navbar" type="button" data-widget="navbar-search">
                    <i class="fas fa-times"></i>
                  </button>
                </div>
              </div>
            </form>
          </div>
        </li>

        <!-- Notification -->
        <li id="notification" class="nav-item dropdown">
          <a class="nav-link" data-toggle="dropdown" href="#">
            <i class="fas fa-bell"></i>
            <span class="badge badge-pill badge-danger navbar-badge">0</span>
          </a>
          <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
            <span class="dropdown-item dropdown-header">0 nuevas Notificaciones</span>
            <div class="dropdown-divider"></div>
            <a href="#" class="dropdown-item">
              <i class="fas fa-shopping-cart mr-2"></i> No tienes notificaciones nuevas
            </a>
            <div class="dropdown-divider"></div>
            <a href="<?php echo $base_path_url; ?>notificaciones.php" class="dropdown-item dropdown-footer">Ver todas las Notificaciones</a>
          </div>
        </li>

        <!-- Cart -->
        <li class="nav-item dropdown">
          <a class="nav-link" href="<?php echo $base_path_url; ?>carrito.php">
            <i class="fas fa-shopping-cart"></i>
            <span class="badge badge-pill badge-danger navbar-badge" id="cart-badge">0</span>
          </a>
        </li>

        <!-- Favorites -->
        <li id="favoritos" class="nav-item dropdown">
          <a class="nav-link" href="<?php echo $base_path_url; ?>favoritos.php">
            <i class="fas fa-heart"></i>
            <span class="badge badge-pill badge-danger navbar-badge" id="favoritos-badge">0</span>
          </a>
        </li>

        <!-- Auth Links -->
        <li class="nav-item" id="nav_register">
          <a class="nav-link" href="Views/register.php" role="button">
            <i class="fas fa-user-plus"></i> Registrarse
          </a>
        </li>
        <li class="nav-item" id="nav_login">
          <a class="nav-link" href="Views/login.php" role="button">
            <i class="fas fa-sign-in-alt"></i> Iniciar sesión
          </a>
        </li>

        <!-- Información del usuario con menú desplegable -->
        <div class="dropdown-container" style="position: relative;">
          <!-- Botón que abre el dropdown -->
          <div class="header-admin-user" id="userDropdown" role="button" tabindex="0">
            <img src=""
              alt="Avatar"
              class="admin-mini-avatar"
              onerror="this.src='<?php echo $base_path; ?>Util/Img/Users/default_avatar.png'">
            <div class="user-username"></div>
            <i class="fas fa-chevron-down dropdown-icon" style="color: #6c757d;"></i>
          </div>
          <!-- Dropdown menu - AHORA BIEN POSICIONADO -->
          <div class="dropdown-menu" id="userDropdownMenu">
            <div class="dropdown-header">
              <strong class="user-name"><!-- Aqui va el nombre completo del usuario --></strong>
              <small>Cliente</small>
            </div>
            <div class="dropdown-divider"></div>
            <a href="<?php echo $base_path_url; ?>mi-perfil.php" class="dropdown-item">
              <i class="fas fa-user-cog"></i> Mi Perfil
            </a>
            <a href="<?php echo $base_path_url; ?>mis-pedidos.php" class="dropdown-item">
              <i class="fas fa-shopping-basket"></i> Mis Pedidos
            </a>
            <div class="dropdown-divider"></div>
            <a href="<?php echo $base_path; ?>Controllers/logout.php" class="dropdown-item text-danger">
              <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
            </a>
          </div>
        </div>
      </ul>
    </nav>
    <!-- /.navbar -->

    <!-- Main Sidebar Container -->
    <aside class="main-sidebar sidebar-dark-primary elevation-4">
      <!-- Brand Logo -->
      <a href="index.php" class="brand-link text-center">
        <img src="<?php echo $base_path; ?>Util/img/png/logo.png" alt="NexusBuy Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
        <span class="brand-text font-weight-bold">NexusBuy</span>
      </a>

      <!-- Sidebar -->
      <div class="sidebar">
        <!-- Sidebar user panel -->
        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
          <div class="image">
            <img id="avatar_menu" src="<?php echo $base_path; ?>Util/img/Users/default_avatar.png" class="img-circle elevation-2" alt="User Image">
          </div>
          <div class="info">
            <a id="usuario_menu" href="<?php echo $base_path_url; ?>mi-perfil.php" class="d-block">Invitado</a>
            <small class="text-white-50">Bienvenido a NexusBuy</small>
          </div>
        </div>

        <!-- Sidebar Menu -->
        <nav class="mt-2">
          <div id="menu-categorias-dinamico">
            <!-- Las categorías se cargarán dinámicamente aquí -->
            <div class="text-center py-4">
              <div class="spinner-border text-light" role="status">
                <span class="sr-only">Cargando categorías...</span>
              </div>
              <p class="text-light mt-2">Cargando categorías...</p>
            </div>
          </div>
        </nav>
        <!-- /.sidebar-menu -->
      </div>
      <!-- /.sidebar -->
    </aside>

    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper" style="background-color: #f5f7ff;">
      <?php if ($soporte == 'active'): ?>
<section class="content-header" style= "display: none">
      


        <?php elseif ($breadcrumb == 'active'): ?>
          <section class="content-header">
          <div class="container-fluid">
            <div class="row mb-2">
              <div class="col-sm-6">
                <h1><?php echo $pageName; ?></h1>
              </div>
              <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">

                  <li class="breadcrumb-item"><a href="<?php echo $base_path; ?>index.php">Home</a></li>
                  <li class="breadcrumb-item active"><?php echo $pageName ?></li>
                </ol>
              </div>
            </div>
          </div>
        


<?php elseif ($notificaciones == 'active'): ?>
  <section class="content-header">
          <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Notificaciones</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="../index.php">Home</a></li>
                    <li class="breadcrumb-item"><a href="mi-perfil.php">Mi Perfil</a></li>
                    <li class="breadcrumb-item active">Notificaciones</li>
                </ol>
            </div>
        </div>
    </div>

<?php elseif ($checkout == 'active'): ?>
<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Pasarela de Pago</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="../index.php">Home</a></li>
                    <li class="breadcrumb-item"><a href="carrito.php">Carrito</a></li>
                    <li class="breadcrumb-item active">Checkout</li>
                </ol>
            </div>
        </div>
    </div>




        <?php else: ?>
          <section class="content-header">
          <div class="products-header">
            <div class="container-fluid">
              <div class="row align-items-center">
                <div class="col-md-6">
                  <h1>
                    <?php
                    if ($busqueda) {
                      echo "Resultados para: " . htmlspecialchars($busqueda);
                    } elseif ($filtro_nuevos) {
                      echo "Nuevos Productos";
                    } elseif ($nombre_marca) {
                      echo "Productos de " . htmlspecialchars($nombre_marca);
                    } elseif ($nombre_subcategoria) {
                      echo htmlspecialchars($nombre_subcategoria);
                    } elseif ($nombre_categoria) {
                      echo htmlspecialchars($nombre_categoria);
                    } else {
                      echo "Todos los Productos";
                    }
                    ?>
                  </h1>
                  <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                      <li class="breadcrumb-item"><a href="<?php echo $base_path; ?>index.php">Home</a></li>
                      <li class="breadcrumb-item"><a href="<?php echo $base_path_url; ?>producto.php">Productos</a></li>
                      <?php if ($busqueda): ?>
                        <li class="breadcrumb-item active" id="busquedaBreadcrumb">
                          Busqueda: "<?php echo htmlspecialchars($busqueda); ?>"
                        </li>
                      <?php elseif ($filtro_nuevos): ?>
                        <li class="breadcrumb-item active" id="filtroNuevosBreadcrumb">
                          Nuevos Productos
                        </li>
                      <?php elseif ($nombre_marca): ?>
                        <li class="breadcrumb-item active" id="marcaBreadcrumb">
                          <?php echo htmlspecialchars($nombre_marca); ?>
                        </li>
                      <?php elseif ($nombre_categoria && !$nombre_subcategoria && !$nombre_marca): ?>
                        <li class="breadcrumb-item active" id="categoriaBreadcrumb">
                          <?php echo htmlspecialchars($nombre_categoria); ?>
                        </li>
                      <?php elseif ($nombre_subcategoria): ?>
                        <?php if ($nombre_categoria): ?>
                          <li class="breadcrumb-item">
                            <a href="producto.php?categoria=<?php echo urlencode($nombre_categoria); ?>" id="categoriaBreadcrumb">
                              <?php echo htmlspecialchars($nombre_categoria); ?>
                            </a>
                          </li>
                        <?php endif; ?>
                        <li class="breadcrumb-item active" id="subcategoriaBreadcrumb">
                          <?php echo htmlspecialchars($nombre_subcategoria); ?>
                        </li>
                      <?php else: ?>
                        <li class="breadcrumb-item active">Todos los productos</li>
                      <?php endif; ?>
                    </ol>
                  </nav>
                </div>
                <div class="col-md-6 text-md-right">
                  <p class="mb-0">Encuentra exactamente lo que buscas</p>
                </div>
              </div>
            
            </div>
            
          </div>
          <?php endif; ?>
        </section>      