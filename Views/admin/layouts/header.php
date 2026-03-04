<?php
// admin/includes/header_admin.php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Verificar sesión directamente en PHP
if (empty($_SESSION['id'])) {
    header('Location: /login.php');
    exit;
}

// Obtener datos del usuario si es necesario
$id_usuario = $_SESSION['id'];
$username = $_SESSION['user'];
$tipo = $_SESSION['tipo_usuario'];
$avatar = $_SESSION['avatar'];

// Verificar permisos
if ($tipo != 1) { // 1 = admin
    header('Location: /index.php');
    exit;
}

// $usuario = $_SESSION['usuario'];
$seccion = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $titulo ?? 'NexusBuy Admin'; ?></title>
    <script>
        var BASE_PATH = "<?php echo $base_path; ?>";
    </script>
    <!-- Google Fonts -->
    <link href="../../../Util/Css/Librerias/fonts Poppins/fonts_Poppins.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="../../../Util/css/librerias/font awesome/all.min.css">
    
    <!-- AdminLTE -->
    <link rel="stylesheet" href="../../../Util/css/adminlte.min.css">
    
    <!-- DataTables -->
    <link rel="stylesheet" href="../../../Util/css/datatables.min.css">
    
    <!-- Select2 -->
    <link rel="stylesheet" href="../../../Util/css/select2.min.css">
    
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="../../../Util/css/sweetalert2.min.css">
    
    <!-- Toastr -->
    <link rel="stylesheet" href="../../../Util/css/toastr.min.css">
    
    <!-- Estilos personalizados -->
    <link rel="stylesheet" href="../../../Util/css/nexusbuy.css">
    
    <style>
        .security-badge {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 500;
        }
        .security-badge.success { background: #d1f7ea; color: #06d6a0; }
        .security-badge.warning { background: #fff3d1; color: #ffb703; }
        .security-badge.danger { background: #ffe5e5; color: #e63946; }
        .security-badge.info { background: #e1e8ff; color: #4361ee; }
        
        .status-indicator {
            display: inline-block;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            margin-right: 0.5rem;
        }
        .status-indicator.active { background: #06d6a0; }
        .status-indicator.inactive { background: #e9ecef; }
        .status-indicator.warning { background: #ffb703; }
        .status-indicator.danger { background: #e63946; }
    </style>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">