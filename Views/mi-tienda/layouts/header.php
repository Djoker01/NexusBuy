<?php
// /Views/mi-tienda/layouts/header.php

// --- PROTECCIÓN DE ACCESO ---
require_once 'auth_check.php';
// --------------------------

// Extraer variables globales para usarlas directamente en PHP
$usuario_actual = $GLOBALS['usuario_actual'] ?? null;
$tienda_actual = $GLOBALS['tienda_actual'] ?? null;

// Si no define $base_path, usar valor por defecto
if (!isset($base_path)) {
    $base_path = "";
}

// Función para determinar si un menú está activo
function isActive($patterns)
{
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
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>NexusBuy - <?php echo $pageTitle ?? 'Mi Tienda'; ?></title>

    <script>
        // Variable global con la ruta base desde PHP
        var BASE_PATH = "<?php echo $base_path; ?>";
        // USUARIO_ACTUAL y TIENDA_ACTUAL ya vienen de auth_check.php
        // console.log('Header cargado - Usuario:', window.USUARIO_ACTUAL?.nombre_completo);
        // console.log('Header cargado - Tienda:', window.TIENDA_ACTUAL?.nombre);
    </script>

    <!-- Font Awesome 6 (iconos) -->
    <link rel="stylesheet" href="<?php echo $base_path; ?>../../Util/css/Librerias/font awesome/all.min.css">
    <!-- Google Fonts -->
    <link href="<?php echo $base_path; ?>../../Util/css/Librerias/fonts Poppins/fonts_Poppins.css" rel="stylesheet">
    <!-- Tema -->
    <link rel="stylesheet" href="<?php echo $base_path; ?>../../Util/css/mi-tienda.css">
    <link rel="stylesheet" href="<?php echo $base_path; ?>../../Util/css/sweetalert2.min.css">
    <link rel="shortcut icon" href="<?php echo $base_path; ?>../../Util/Img/png/favicon.ico" type="image/x-icon">
    <script src="<?php echo $base_path; ?>../../Util/Js/jquery.min.js"></script>
</head>

<body>
    <div class="app">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="logo">
                <i class="fas fa-store"></i>
                <div class="logo-text">
                    <span>NexusBuy</span>
                    <?php if ($tienda_actual): ?>
                        <small class="store-name-short" title="<?php echo htmlspecialchars($tienda_actual->nombre); ?>">
                            <?php
                            $nombre_corto = strlen($tienda_actual->nombre) > 20
                                ? substr($tienda_actual->nombre, 0, 17) . '...'
                                : $tienda_actual->nombre;
                            echo htmlspecialchars($nombre_corto);
                            ?>
                        </small>
                    <?php endif; ?>
                </div>
            </div>

            <nav>
                <!-- Dashboard -->
                <a href="<?php echo $base_path; ?>dashboard.php"
                    class="nav-item <?php echo isActive('dashboard.php') ? 'active' : ''; ?>">
                    <i class="fas fa-chart-pie"></i>
                    <span>Dashboard</span>
                </a>

                <!-- Productos -->
                <a href="<?php echo $base_path; ?>productos/index.php"
                    class="nav-item <?php echo isActive('/productos/') ? 'active' : ''; ?>">
                    <i class="fas fa-box"></i>
                    <span>Productos</span>
                </a>

                <!-- Pedidos -->
                <a href="<?php echo $base_path; ?>pedidos/index.php"
                    class="nav-item <?php echo isActive('/pedidos/') ? 'active' : ''; ?>">
                    <i class="fas fa-shopping-cart"></i>
                    <span>Pedidos</span>
                </a>

                <!-- Finanzas -->
                <a href="<?php echo $base_path; ?>finanzas/index.php"
                    class="nav-item <?php echo isActive('/finanzas/') ? 'active' : ''; ?>">
                    <i class="fas fa-dollar-sign"></i>
                    <span>Finanzas</span>
                </a>

                <!-- Mensajes -->
                <a href="<?php echo $base_path; ?>mensajes/index.php"
                    class="nav-item <?php echo isActive('/mensajes/') ? 'active' : ''; ?>">
                    <i class="fas fa-comments"></i>
                    <span>Mensajes</span>
                    <span class="badge" style="position: static; margin-left: auto;">3</span>
                </a>

                <!-- Configuración -->
                <a href="<?php echo $base_path; ?>configuracion/index.php"
                    class="nav-item <?php echo isActive('/configuracion/') ? 'active' : ''; ?>">
                    <i class="fas fa-cog"></i>
                    <span>Configuración</span>
                </a>

                <!-- Cerrar sesión -->
                <a href="<?php echo $base_path; ?>../../Controllers/logout.php"
                    class="nav-item logout-item">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Cerrar sesión</span>
                </a>
            </nav>

            <!-- Info del vendedor en sidebar (opcional) -->
            <?php if ($tienda_actual): ?>
                <div class="sidebar-footer">
                    <div class="store-status">
                        <i class="fas fa-circle <?php echo $tienda_actual->estado; ?>"></i>
                        <span>Tienda: <?php echo ucfirst($tienda_actual->estado); ?></span>
                    </div>
                </div>
            <?php endif; ?>
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
                    <!-- Notificaciones -->
                    <div class="notifications" id="notification" title="Notificaciones">
                        <i class="far fa-bell"></i>
                        <span class="badge" id="notification-badge" style="display: none;">0</span>
                    </div>

                    <!-- Información del usuario con menú desplegable -->
                    <div class="dropdown-container" style="position: relative;">
                        <!-- Botón que abre el dropdown -->
                        <div class="user-info" id="userDropdown" role="button" tabindex="0">
                            <div class="user-avatar-wrapper">
                                <img src="<?php echo $base_path; ?>../../Util/Img/Users/<?php echo $_SESSION['user_avatar'] ?? 'default_avatar.png'; ?>"
                                    class="user-avatar"
                                    alt="Avatar"
                                    onerror="this.src='<?php echo $base_path; ?>../../Util/Img/Users/default_avatar.png'">
                            </div>
                            <div class="user-details">
                                <div class="user-name"><?php echo htmlspecialchars($_SESSION['user_nombre_completo'] ?? 'Usuario'); ?></div>
                                <div class="user-role">
                                    <i class="fas fa-store-alt"></i>
                                    <?php echo htmlspecialchars($_SESSION['tienda_nombre'] ?? $usuario_actual->tipo_usuario); ?>
                                </div>
                            </div>
                            <i class="fas fa-chevron-down dropdown-icon"></i>
                        </div>

                        <!-- Dropdown menu - AHORA BIEN POSICIONADO -->
                        <div class="dropdown-menu" id="userDropdownMenu">
                            <div class="dropdown-header">
                                <strong><?php echo htmlspecialchars($_SESSION['user_nombre_completo'] ?? 'Usuario'); ?></strong>
                                <small><?php echo htmlspecialchars($_SESSION['user_email'] ?? ''); ?></small>
                            </div>
                            <div class="dropdown-divider"></div>
                            <a href="<?php echo $base_path; ?>perfil.php" class="dropdown-item">
                                <i class="fas fa-user"></i> Mi Perfil
                            </a>
                            <a href="<?php echo $base_path; ?>configuracion/index.php" class="dropdown-item">
                                <i class="fas fa-cog"></i> Configuración
                            </a>
                            <?php if ($tienda_actual): ?>
                                <a href="<?php echo $base_path; ?>configuracion/tienda.php" class="dropdown-item">
                                    <i class="fas fa-store"></i> Mi Tienda
                                </a>
                            <?php endif; ?>
                            <div class="dropdown-divider"></div>
                            <a href="<?php echo $base_path; ?>../../Controllers/logout.php" class="dropdown-item text-danger">
                                <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Alertas condicionales -->
            <?php if ($tienda_actual && $tienda_actual->estado == 'pendiente'): ?>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    <div class="alert-content">
                        <strong>Tienda pendiente de aprobación:</strong> Mientras tu tienda no sea aprobada, no será visible para los clientes. Debes
                        <a href="<?php echo $base_path; ?>configuracion/index.php" class="alert-link">
                        Completar la configuración <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($tienda_actual && $tienda_actual->estado == 'inactiva'): ?>
                <div class="alert alert-info">
                    <i class="fas fa-store"></i>
                    <div class="alert-content">
                        <strong>¡Bienvenido <?php echo htmlspecialchars($_SESSION['user_nombres'] ?? 'Vendedor'); ?>!</strong> 
                        Para comenzar a vender, primero debes:
                        <a href="<?php echo $base_path; ?>productos/añadir.php" class="alert-link">
                            <i class="fas fa-plus"></i> Añadir un producto 
                        </a> 
                        <a href="<?php echo $base_path; ?>productos/ofertas.php" class="alert-link">
                            <i class="fas fa-plus"></i> Crea una Oferta
                        </a>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($tienda_actual && $tienda_actual->estado == 'suspendida'): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-store"></i>
                    <div class="alert-content">
                        <strong>¡Bienvenido <?php echo htmlspecialchars($_SESSION['user_nombres'] ?? 'Vendedor'); ?>!</strong> 
                        Tu tienda está suspendida, no será publicado ningún servicio u ofertas. Debes contactar con los 
                        <a href="<?php echo $base_path; ?>mensajes/chat.php" class="alert-link">
                            <i class="fas fa-shield"></i> administradores 
                        </a> .
                    </div>
                </div>
            <?php endif; ?>

            <!-- El contenido específico de cada vista se inserta aquí -->