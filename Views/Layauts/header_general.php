<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Google Font: Source Sans Pro -->
  
  <!-- Font Awesome -->
  <link rel="stylesheet" href="../Util/css/select2.min.css">
  <link rel="stylesheet" href="../Util/css/css/all.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="../Util/css/adminlte.min.css">
  <!-- Sistema de Temas -->
  <!-- <link rel="stylesheet" href="../Util/css/temas.css"> -->
  <link rel="stylesheet" href="../Util/css/sweetalert2.min.css">
  <style>
    :root{
      --tema-actual: <?php echo $_SESSION['tema_usuario'] ?? 'claro'; ?>;
    }
  </style>
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
        <a href="../index.php" class="nav-link">Home</a>
      </li>
      <li class="nav-item d-none d-sm-inline-block">
        <a href="#" class="nav-link">Contact</a>
      </li>
    </ul>

    <!-- Right navbar links -->
    <ul class="navbar-nav ml-auto">
    
      <!-- Messages Dropdown Menu -->
      <li class="nav-item dropdown">
        <a class="nav-link" href="carrito.php">
          <i class="fas fa-shopping-cart"></i>
          <span class="badge badge-pill badge-danger navbar-badge" id="cart-badge">0</span>
        </a>
      </li>
      <!-- Notifications Dropdown Menu -->
      <li id="favoritos" class="nav-item dropdown">
        <a class="nav-link" href="favoritos.php">
          <i class="fas fa-heart"></i>
          <!-- <span class="badge badge-warning navbar-badge">15</span> -->
        </a>
        
      </li>
      <li class="nav-item" id="nav_register">
        <a class="nav-link" href="register.php" role="button">
          <i class="fas fa-user-plus"></i> Registrarse
        </a>
      </li>
      <li class="nav-item" id="nav_login">
        <a class="nav-link" href="login.php" role="button">
          <i class="far fa-user"></i> Iniciar sesión
        </a>
      </li>
      <li class="nav-item dropdown" id="nav_usuario">
          <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <img id="avatar_nav" src="" width="30" height="30" class="img-fluid img-circle">
            <span id="usuario_nav">Usuario logeado</span>
          </a>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="mi_perfil.php"><i class="fas fa-user-cog"></i> Mi perfil</a></li>
            <li><a class="dropdown-item" href="mis_pedidos.php"><i class="fas fa-shopping-basket"></i> Mis pedidos</a></li>
            <li><a class="dropdown-item" href="../Controllers/logout.php"><i class="fas fa-user-times"></i> Cerrar sesión</a></li>
          </ul>
        </li>
    </ul>
  </nav>
  <!-- /.navbar -->

  <!-- Main Sidebar Container -->
  <aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="../index.php" class="brand-link">
      <img src="../Util/img/logo.png" alt="NexusBuy Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
      <span class="brand-text font-weight-light">NexusBuy</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
      <!-- Sidebar user (optional) -->
      <div class="user-panel mt-3 pb-3 mb-3 d-flex">
        <div class="image">
          <img id="avatar_menu" src="../Util/Img/Users/user_default.png" class="img-circle elevation-2" alt="User Image">
        </div>
        <div class="info">
          <a id="usuario_menu" href="#" class="d-block"></a>
        </div>
      </div>

      <!-- SidebarSearch Form -->
      <div class="form-inline">
        <div class="input-group" data-widget="sidebar-search">
          <input class="form-control form-control-sidebar" type="search" placeholder="Search" aria-label="Search">
          <div class="input-group-append">
            <button class="btn btn-sidebar">
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
                  <div class="spinner-border text-primary" role="status">
                      <span class="sr-only">Cargando categorías...</span>
                  </div>
                  <p class="text-muted mt-2">Cargando categorías...</p>
              </div>
          </div>
      </nav>
      <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
  </aside>

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">