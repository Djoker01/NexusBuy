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
?>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="Descubre los mejores productos al mejor precio en NexusBuy. Envío gratis, ofertas exclusivas y la mejor experiencia de compra online.">
  <!-- Librerias -->
  <link rel="stylesheet" href="../Util/css/Librerias/font awesome/all.min.css">
  <link rel="stylesheet" href="../Util/css/Librerias/fonts Poppins/fonts_Poppins.css">
  <!-- Theme style -->
   <!-- <link rel="stylesheet" href="../Util/css/bootstrap.min.css"> -->
    <link rel="stylesheet" href="../Util/css/adminlte.min.css">
  <link rel="stylesheet" href="../Util/css/nexusbuy.css">
  <link rel="stylesheet" href="../Util/css/sweetalert2.min.css">
  
</head>

<body class="hold-transition sidebar-mini <?php echo isset($_SESSION['tema_usuario']) ? 'tema-' . $_SESSION['tema_usuario'] : 'tema-claro'; ?> <?php echo isset($_SESSION['densidad_usuario']) ? 'densidad-' . $_SESSION['densidad_usuario'] : 'densidad-normal'; ?>">
<!-- Site wrapper -->
<div class="wrapper">
  <!-- Navbar -->
  <nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <!-- Left navbar links -->
    <ul class="navbar-nav">
      <li class="nav-item">
        <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
      </li>
      <li class="nav-item d-none d-sm-inline-block">
        <a href="../index.php" class="nav-link"><i class="fas fa-home"></i> Home</a>
      </li>
      <li class="nav-item d-none d-sm-inline-block">
        <a href="soporte.php" class="nav-link"><i class="fas fa-headset"></i> Soporte</a>
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
          <form class="form-inline" id="form-busqueda">
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
            <span class="badge badge-pill badge-danger navbar-badge">15</span>
          </a>
          <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
            <span class="dropdown-item dropdown-header">15 Notificaciones</span>
            <div class="dropdown-divider"></div>
            <a href="#" class="dropdown-item">
              <i class="fas fa-envelope mr-2"></i> 4 nuevos mensajes
              <span class="float-right text-muted text-sm">3 mins</span>
            </a>
            <div class="dropdown-divider"></div>
            <a href="#" class="dropdown-item">
              <i class="fas fa-cog mr-2"></i> 8 sistema
              <span class="float-right text-muted text-sm">12 horas</span>
            </a>
            <div class="dropdown-divider"></div>
            <a href="#" class="dropdown-item">
              <i class="fas fa-file mr-2"></i> 3 nuevos reportes
              <span class="float-right text-muted text-sm">2 días</span>
            </a>
            <div class="dropdown-divider"></div>
            <a href="#" class="dropdown-item dropdown-footer">Ver todas las Notificaciones</a>
          </div>
        </li>

      <!-- Cart -->
      <li class="nav-item dropdown">
        <a class="nav-link" href="carrito.php">
          <i class="fas fa-shopping-cart"></i>
          <span class="badge badge-pill badge-danger navbar-badge" id="cart-badge">0</span>
        </a>
      </li>

      <!-- Favorites -->
      <li id="favoritos" class="nav-item dropdown">
        <a class="nav-link" href="favoritos.php">
          <i class="fas fa-heart"></i>
          <span class="badge badge-pill badge-danger navbar-badge" id="favoritos-badge">0</span>
        </a>
      </li>

      <!-- Auth Links -->
      <li class="nav-item" id="nav_register">
        <a class="nav-link" href="register.php" role="button">
          <i class="fas fa-user-plus"></i> Registrarse
        </a>
      </li>
      <li class="nav-item" id="nav_login">
        <a class="nav-link" href="login.php" role="button">
          <i class="fas fa-sign-in-alt"></i> Iniciar sesión
        </a>
      </li>

      <!-- User Menu -->
      <li class="nav-item dropdown" id="nav_usuario">
        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
          <img id="avatar_nav" src="" width="32" height="32" class="img-fluid img-circle">
          <span id="usuario_nav">Usuario</span>
        </a>
        <ul class="dropdown-menu dropdown-menu-right">
          <li><a class="dropdown-item" href="mi_perfil.php"><i class="fas fa-user-cog"></i> Mi perfil</a></li>
          <li><a class="dropdown-item" href="mis_pedidos.php"><i class="fas fa-shopping-basket"></i> Mis pedidos</a></li>
          <li><hr class="dropdown-divider"></li>
          <li><a class="dropdown-item" href="../Controllers/logout.php"><i class="fas fa-sign-out-alt"></i> Cerrar sesión</a></li>
        </ul>
      </li>
    </ul>
  </nav>
  <!-- /.navbar -->

  <!-- Main Sidebar Container -->
  <aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="index.php" class="brand-link text-center">
      <img src="../Util/img/png/logo.png" alt="NexusBuy Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
      <span class="brand-text font-weight-bold">NexusBuy</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
      <!-- Sidebar user panel -->
      <div class="user-panel mt-3 pb-3 mb-3 d-flex">
        <div class="image">
          <img id="avatar_menu" src="../Util/img/Users/default_avatar.png" class="img-circle elevation-2" alt="User Image">
        </div>
        <div class="info">
          <a id="usuario_menu" href="Views/mi_perfil.php" class="d-block">Invitado</a>
          <small class="text-white-50">Bienvenido a NexusBuy</small>
        </div>
      </div>

      <!-- SidebarSearch Form -->
      <div class="form-inline sidebar-search">
        <div class="input-group" data-widget="sidebar-search">
          <input class="form-control form-control-sidebar" 
                type="search" 
                id="buscar-subcategoria-input"
                placeholder="Buscar categoría..." 
                aria-label="Buscar categoría">
          <div class="input-group-append">
            <button class="btn btn-sidebar" type="button" id="btn-buscar-subcategoria">
              <i class="fas fa-search fa-fw"></i>
            </button>
          </div>
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

