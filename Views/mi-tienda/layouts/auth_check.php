<?php
// /Views/mi-tienda/layouts/auth_check.php

// Iniciar sesión si no está iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Definir constantes de tipos de usuario según tu BD
define('TIPO_ADMIN', 1);
define('TIPO_CLIENTE', 2);
define('TIPO_VENDEDOR', 3);
define('TIPO_EMPLEADO', 4);

// Niveles de permisos según tu tabla tipo_usuario
define('NIVEL_CLIENTE', 1);
define('NIVEL_EMPLEADO', 25);
define('NIVEL_VENDEDOR', 50);
define('NIVEL_ADMIN', 100);

// Definir qué tipos pueden acceder a la sección "mi-tienda"
$tipos_permitidos = [TIPO_VENDEDOR, TIPO_ADMIN]; // Vendedores y admins

// Verificación 1: ¿Existe sesión activa?
if (!isset($_SESSION['id'])) {
    error_log("Intento de acceso sin sesión a: " . $_SERVER['REQUEST_URI']);
    header('Location: /nexusbuy/views/login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit;
}

// Verificación 2: ¿El tipo de usuario está permitido?
if (!in_array($_SESSION['tipo_usuario'], $tipos_permitidos)) {
    error_log("Acceso denegado - Usuario ID: {$_SESSION['id']}, Tipo: {$_SESSION['tipo_usuario']} intentó acceder a: " . $_SERVER['REQUEST_URI']);
    header('Location: /nexusbuy/views/acceso_denegado.php');
    exit;
}

// Si llegamos aquí, el usuario está autorizado
require_once $_SERVER['DOCUMENT_ROOT'] . '/nexusbuy/Models/Usuario.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/nexusbuy/Models/Tienda.php';

$usuario_model = new Usuario();
$tienda_model = new Tienda();

// Obtener datos del usuario
$datos_usuario = $usuario_model->obtener_datos($_SESSION['id']);

if (empty($datos_usuario)) {
    // Usuario en sesión pero no en BD
    session_destroy();
    header('Location: /nexusbuy/views/login.php?error=sesion_invalida');
    exit;
}

$usuario_actual = $datos_usuario[0];

// Obtener datos de la tienda si es vendedor
$tienda_actual = null;
if ($usuario_actual->id_tipo_usuario == TIPO_VENDEDOR) {
    $tienda_data = $tienda_model->obtener_tienda_por_usuario($usuario_actual->id);
    
    if (!empty($tienda_data)) {
        $tienda_actual = $tienda_data[0];
        $_SESSION['id_tienda'] = $tienda_actual->id;
        
        // Decodificar campos JSON para pasarlos a JavaScript
        $redes_sociales = !empty($tienda_actual->redes_sociales) ? json_decode($tienda_actual->redes_sociales, true) : new stdClass();
        $politicas = !empty($tienda_actual->politicas) ? json_decode($tienda_actual->politicas, true) : new stdClass();
    } else {
        // El usuario es vendedor pero no tiene tienda creada
        error_log("Vendedor ID {$usuario_actual->id} no tiene tienda asociada");
        $_SESSION['id_tienda'] = null;
    }
}

// ============================================
// HACER VARIABLES DISPONIBLES GLOBALMENTE
// ============================================
$GLOBALS['usuario_actual'] = $usuario_actual;
$GLOBALS['tienda_actual'] = $tienda_actual;

// ============================================
// GUARDAR EN SESIÓN PARA FÁCIL ACCESO
// ============================================
$_SESSION['user_id'] = $usuario_actual->id;
$_SESSION['user_nombre_completo'] = $usuario_actual->nombres . ' ' . $usuario_actual->apellidos;
$_SESSION['user_nombres'] = $usuario_actual->nombres;
$_SESSION['user_apellidos'] = $usuario_actual->apellidos;
$_SESSION['user_username'] = $usuario_actual->username;
$_SESSION['user_email'] = $usuario_actual->email;
$_SESSION['user_avatar'] = $usuario_actual->avatar ?: 'default_avatar.png';
$_SESSION['user_tipo'] = $usuario_actual->tipo_usuario;
$_SESSION['user_tipo_id'] = $usuario_actual->id_tipo_usuario;
$_SESSION['user_telefono'] = $usuario_actual->telefono ?? '';
$_SESSION['user_dni'] = $usuario_actual->dni ?? '';

if ($tienda_actual) {
    $_SESSION['tienda_id'] = $tienda_actual->id;
    $_SESSION['tienda_nombre'] = $tienda_actual->nombre;
    $_SESSION['tienda_logo'] = $tienda_actual->logo ?: 'default_store_logo.png';
    $_SESSION['tienda_banner'] = $tienda_actual->banner ?? '';
    $_SESSION['tienda_email'] = $tienda_actual->email ?? '';
    $_SESSION['tienda_telefono'] = $tienda_actual->telefono ?? '';
    $_SESSION['tienda_estado'] = $tienda_actual->estado;
    $_SESSION['tienda_calificacion'] = $tienda_actual->calificacion_promedio ?? 0;
}

// Función para limpiar strings para JavaScript
function js_string($texto) {
    return htmlspecialchars($texto ?? '', ENT_QUOTES, 'UTF-8');
}
?>

<!-- Pasar datos a JavaScript -->
<script>
// ============================================
// DATOS DEL USUARIO ACTUAL
// ============================================
window.USUARIO_ACTUAL = {
    id: <?php echo $usuario_actual->id; ?>,
    nombres: '<?php echo js_string($usuario_actual->nombres); ?>',
    apellidos: '<?php echo js_string($usuario_actual->apellidos); ?>',
    nombre_completo: '<?php echo js_string($usuario_actual->nombres . ' ' . $usuario_actual->apellidos); ?>',
    username: '<?php echo js_string($usuario_actual->username); ?>',
    email: '<?php echo js_string($usuario_actual->email); ?>',
    tipo_id: <?php echo $usuario_actual->id_tipo_usuario; ?>,
    tipo_nombre: '<?php echo js_string($usuario_actual->tipo_usuario); ?>',
    nivel_permisos: <?php echo $usuario_actual->nivel_permisos ?? 1; ?>,
    avatar: '<?php echo js_string($usuario_actual->avatar ?: 'default_avatar.png'); ?>',
    telefono: '<?php echo js_string($usuario_actual->telefono ?? ''); ?>',
    dni: '<?php echo js_string($usuario_actual->dni ?? ''); ?>'
};

<?php if ($tienda_actual): ?>
// ============================================
// DATOS DE LA TIENDA DEL VENDEDOR
// ============================================
window.TIENDA_ACTUAL = {
    id: <?php echo $tienda_actual->id; ?>,
    nombre: '<?php echo js_string($tienda_actual->nombre); ?>',
    descripcion: '<?php echo js_string($tienda_actual->descripcion); ?>',
    logo: '<?php echo js_string($tienda_actual->logo ?: 'default_store_logo.png'); ?>',
    banner: '<?php echo js_string($tienda_actual->banner ?? ''); ?>',
    email: '<?php echo js_string($tienda_actual->email); ?>',
    telefono: '<?php echo js_string($tienda_actual->telefono); ?>',
    sitio_web: '<?php echo js_string($tienda_actual->sitio_web ?? ''); ?>',
    direccion: '<?php echo js_string($tienda_actual->direccion); ?>',
    id_municipio: <?php echo $tienda_actual->id_municipio ?: 'null'; ?>,
    calificacion: <?php echo $tienda_actual->calificacion_promedio ?: 0; ?>,
    estado: '<?php echo js_string($tienda_actual->estado); ?>',
    verificada: <?php echo ($tienda_actual->estado == 'activa') ? 'true' : 'false'; ?>,
    // Campos JSON decodificados
    redes_sociales: <?php echo json_encode($redes_sociales ?? new stdClass()); ?>,
    politicas: <?php echo json_encode($politicas ?? new stdClass()); ?>
};
<?php else: ?>
// Usuario no tiene tienda o no es vendedor
window.TIENDA_ACTUAL = null;
<?php endif; ?>

// ============================================
// CONFIGURACIÓN GENERAL DEL SITIO
// ============================================
window.CONFIG = {
    version: '1.0.0',
    ambiente: '<?php echo ($_SERVER['HTTP_HOST'] == 'localhost') ? 'desarrollo' : 'produccion'; ?>',
    debug: <?php echo ($_SERVER['HTTP_HOST'] == 'localhost') ? 'true' : 'false'; ?>
};

// Log para debug (solo en desarrollo)
if (window.CONFIG.debug) {
    // console.log('=== DATOS DE SESIÓN (DESDE AUTH_CHECK) ===');
    console.log('Usuario:', window.USUARIO_ACTUAL);
    console.log('Tienda:', window.TIENDA_ACTUAL);
    // console.log('==========================================');
}
</script>
<?php
// Fin del archivo auth_check.php
?>