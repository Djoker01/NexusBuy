<?php
// --- PROTECCIÓN DE ACCESO ---
require_once 'auth_check.php';
// --------------------------
// Si no se define $base_path, usar valor por defecto (para vistas en la raíz de mi-tienda)
if (!isset($base_path)) {
    $base_path = "";
}

// Función para determinar si un menú está activo
function isActive($patterns) {
    if (!is_array($patterns)) {
        $patterns = [$patterns];
    }
    
    foreach ($patterns as $pattern) {
        if (strpos($_SERVER['PHP_SELF'], $pattern) !== false) {
            return true;
        }
    }
    return false;
}

$current_page = basename($_SERVER['PHP_SELF']);
$current_dir = basename(dirname($_SERVER['PHP_SELF']));
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>NexusBuy - <?php echo $pageTitle ?? ''; ?></title>
    <script>
        // Variable global con la ruta base desde PHP
        var BASE_PATH = "<?php echo $base_path; ?>";
        // console.log('BASE_PATH definida en header:', BASE_PATH); // Para debug
        // Debugging
        // console.log('1. Header iniciado');
        var BASE_PATH = "<?php echo $base_path; ?>";
        // console.log('2. BASE_PATH definida como:', BASE_PATH);
        // console.log('3. Documento actual:', window.location.pathname);
    </script>
    <!-- Font Awesome 6 (iconos) -->
    <link rel="stylesheet" href="<?php echo $base_path; ?>../../Util/css/Librerias/font awesome/all.min.css">
    <!-- Google Fonts -->
    <link href="<?php echo $base_path; ?>../../Util/css/Librerias/fonts Poppins/fonts_Poppins.css" rel="stylesheet">
    <!-- Tema -->
    <link rel="stylesheet" href="<?php echo $base_path; ?>../../Util/css/mi-tienda.css">
    <link rel="stylesheet" href="<?php echo $base_path; ?>../../Util/css/sweetalert2.min.css">
  <link rel="shortcut icon" href="<?php echo $base_path; ?>../../Util/Img/png/favicon.ico" type="image/x-icon">
  
</head>
<body>
    <div class="app">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="logo">
                <i class="fas fa-store"></i>
                <span>NexusBuy</span>
            </div>
            
            <nav>
                <a href="<?php echo $base_path; ?>dashboard.php" class="nav-item <?php echo isActive('dashboard.php') ? 'active' : ''; ?>">
                    <i class="fas fa-chart-pie"></i>
                    <span>Dashboard</span>
                </a>
                <a href="<?php echo $base_path; ?>productos/index.php" class="nav-item <?php echo isActive('/productos/') ? 'active' : ''; ?>">
                    <i class="fas fa-box"></i>
                    <span>Productos</span>
                </a>
                <a href="<?php echo $base_path; ?>pedidos/index.php" class="nav-item <?php echo isActive('/pedidos/') ? 'active' : ''; ?>">
                    <i class="fas fa-shopping-cart"></i>
                    <span>Pedidos</span>
                </a>
                <a href="<?php echo $base_path; ?>finanzas/index.php" class="nav-item <?php echo isActive('/finanzas/') ? 'active' : ''; ?>">
                    <i class="fas fa-dollar-sign"></i>
                    <span>Finanzas</span>
                </a>
                <a href="<?php echo $base_path; ?>mensajes/index.php" class="nav-item <?php echo isActive('/mensajes/') ? 'active' : ''; ?>">
                    <i class="fas fa-comments"></i>
                    <span>Mensajes</span>
                    <span class="badge" style="position: static; margin-left: auto;">3</span>
                </a>
                <a href="<?php echo $base_path; ?>configuracion/index.php" class="nav-item <?php echo isActive('/configuracion/') ? 'active' : ''; ?>">
                    <i class="fas fa-cog"></i>
                    <span>Configuración</span>
                </a>
                <a href="<?php echo $base_path; ?>../../Controllers/logout.php" class="nav-item <?php echo isActive('/configuracion/') ? 'active' : ''; ?>">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Cerrar sesión</span>
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main">
            <!-- Top header -->
            <div class="top-header">
                <div class="page-title">
                    <h1><?php echo $pageName ?? 'Panel de Control'; ?></h1>
                    <p><?php echo $pageDescription ?? ''; ?></p>
                </div>
                <div class="user-menu">
                    <div class="notifications">
                        <i class="far fa-bell"></i>
                        <span class="badge">5</span>
                    </div>
                    <div class="user-info">
                        <img src="" class="user-avatar">
                        <div class="user-details">
                            <div class="user-name">Cargando...</div>
                            <div class="user-role">Vendedor</div>
                        </div>
                    </div>
                </div>
            </div>