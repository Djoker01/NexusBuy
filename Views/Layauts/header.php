<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="Util/css/css/all.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="Util/css/adminlte.min.css">
  <!-- Sistema de Temas -->
  <!-- <link rel="stylesheet" href="Util/css/temas.css"> -->
  <link rel="stylesheet" href="Util/css/sweetalert2.min.css">
</head>
<style>
  :root{
      --tema-actual: <?php echo $_SESSION['tema_usuario'] ?? 'claro'; ?>;
    }
/* Sugerencias de búsqueda */
#sugerencias-busqueda {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    z-index: 1000;
    max-height: 300px;
    overflow-y: auto;
}

#sugerencias-busqueda .dropdown-item {
    border-bottom: 1px solid #f8f9fa;
    padding: 10px 15px;
}

#sugerencias-busqueda .dropdown-item:hover {
    background-color: #f8f9fa;
}

/* Loading para búsqueda */
.loading-busqueda {
    display: inline-block;
    width: 20px;
    height: 20px;
    border: 2px solid #f3f3f3;
    border-top: 2px solid #3498db;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Estilos para búsqueda de subcategorías */
#sugerencias-subcategorias {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    z-index: 1000;
    max-height: 300px;
    overflow-y: auto;
    background: white;
    border: 1px solid #dee2e6;
    border-radius: 0.25rem;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}

#sugerencias-subcategorias .sugerencia-item {
    padding: 8px 12px;
    border-bottom: 1px solid #f8f9fa;
    cursor: pointer;
    transition: background-color 0.2s;
}

#sugerencias-subcategorias .sugerencia-item:hover {
    background-color: #f8f9fa;
}

#sugerencias-subcategorias .sugerencia-item:last-child {
    border-bottom: none;
}

#sugerencias-subcategorias .categoria-principal {
    font-weight: bold;
    color: #007bff;
}

#sugerencias-subcategorias .subcategoria-item {
    padding-left: 20px;
    color: #6c757d;
}

/* Loading para búsqueda de subcategorías */
.loading-subcategorias {
    display: inline-block;
    width: 16px;
    height: 16px;
    border: 2px solid #f3f3f3;
    border-top: 2px solid #3498db;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

.sidebar-search-loading {
    text-align: center;
    padding: 10px;
    color: #6c757d;
}
</style>
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
        <a href="index.php" class="nav-link">Home</a>
      </li>
      <li class="nav-item d-none d-sm-inline-block">
        <a href="#" class="nav-link">Contact</a>
      </li>
    </ul>

    <!-- Right navbar links -->
    <ul class="navbar-nav ml-auto">
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
                        placeholder="Buscar productos..." 
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
      <!-- Messages Dropdown Menu -->
      <li class="nav-item dropdown">
        <a class="nav-link" href="Views/carrito.php">
          <i class="fas fa-shopping-cart"></i>
          <span class="badge badge-pill badge-danger navbar-badge" id="cart-badge">0</span>
        </a>
       </li>
      <!-- Notifications Dropdown Menu -->
      <li id="favoritos" class="nav-item dropdown">
        <a class="nav-link"  href="Views/favoritos.php">
          <i class="fas fa-heart"></i>
        </a>
      </li>
      <li class="nav-item" id="nav_register">
        <a class="nav-link" href="Views/register.php" role="button">
          <i class="fas fa-user-plus"></i> Registrarse
        </a>
      </li>
      <li class="nav-item" id="nav_login">
        <a class="nav-link" href="Views/login.php" role="button">
          <i class="far fa-user"></i> Iniciar sesión
        </a>
      </li>
      <li class="nav-item dropdown" id="nav_usuario">
          <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <img id="avatar_nav" src="" width="30" height="30" class="img-fluid img-circle">
            <span id="usuario_nav">Usuario logeado</span>
          </a>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="Views/mi_perfil.php"><i class="fas fa-user-cog"></i> Mi perfil</a></li>
            <li><a class="dropdown-item" href="Views/mis_pedidos.php"><i class="fas fa-shopping-basket"></i> Mis pedidos</a></li>
            <li><a class="dropdown-item" href="Controllers/logout.php"><i class="fas fa-user-times"></i> Cerrar sesión</a></li>
          </ul>
        </li>
    </ul>
  </nav>
  <!-- /.navbar -->

  <!-- Main Sidebar Container -->
  <aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="index.php" class="brand-link">
      <img src="Util/img/logo.png" alt="NexusBuy Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
      <span class="brand-text font-weight-light">NexusBuy</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
      <!-- Sidebar user (optional) -->
      <div class="user-panel mt-3 pb-3 mb-3 d-flex">
        <div class="image">
          <img id="avatar_menu" src="Util/img/Users/user_default.png" class="img-circle elevation-2" alt="User Image">
        </div>
        <div class="info">
          <a id="usuario_menu" href="Views/mi_perfil.php" class="d-block"></a>
        </div>
      </div>

      <!-- SidebarSearch Form -->
      <!-- <div class="form-inline">
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
        </div> -->

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

