<?php
// /Views/mi-tienda/layouts/auth_check.php

// Iniciar sesión si no está iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Definir constantes de tipos de usuario (ajusta los valores según tu BD)
define('TIPO_ADMIN', 1);
define('TIPO_CLIENTE', 2);
define('TIPO_VENDEDOR', 3);

// Definir qué tipos pueden acceder a la sección "mi-tienda"
// Por ahora solo vendedores, pero podrías añadir admin si aplica
$tipos_permitidos = [TIPO_VENDEDOR]; // Puedes añadir TIPO_ADMIN si los admins también acceden

// Verificación 1: ¿Existe sesión activa?
if (!isset($_SESSION['id'])) {
    // Registrar intento de acceso no autorizado
    error_log("Intento de acceso sin sesión a: " . $_SERVER['REQUEST_URI']);
    
    // Redirigir al login
    header('Location: /nexusbuy/views/login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit;
}

// Verificación 2: ¿El tipo de usuario está permitido?
if (!in_array($_SESSION['tipo_usuario'], $tipos_permitidos)) {
    // Registrar intento de acceso con tipo no autorizado
    error_log("Acceso denegado - Usuario ID: {$_SESSION['id']}, Tipo: {$_SESSION['tipo_usuario']} intentó acceder a: " . $_SERVER['REQUEST_URI']);
    
    // Redirigir a página de acceso denegado
    header('Location: /nexusbuy/views/acceso_denegado.php');
    exit;
}

// Si llegamos aquí, el usuario está autorizado
// Opcional: Obtener datos completos del usuario para usar en las vistas
require_once $_SERVER['DOCUMENT_ROOT'] . '/nexusbuy/Models/Usuario.php';
$usuario_model = new Usuario();
$datos_usuario = $usuario_model->obtener_datos($_SESSION['id']);

if (empty($datos_usuario)) {
    // Caso raro: usuario en sesión pero no en BD
    session_destroy();
    header('Location: /nexusbuy/views/login.php?error=sesion_invalida');
    exit;
}

$usuario_actual = $datos_usuario[0];

// Pasar datos a JavaScript para uso en el frontend
?>
<script>
    window.USUARIO_ACTUAL = {
        id: <?php echo $usuario_actual->id; ?>,
        nombre: '<?php echo $usuario_actual->nombres . ' ' . $usuario_actual->apellidos; ?>',
        username: '<?php echo $usuario_actual->username; ?>',
        tipo: <?php echo $usuario_actual->id_tipo_usuario; ?>,
        tipo_nombre: '<?php echo $usuario_actual->tipo_usuario; ?>',
        avatar: '<?php echo $usuario_actual->avatar; ?>'
    };
</script>
<?php
// Fin del archivo auth_check.php
?>