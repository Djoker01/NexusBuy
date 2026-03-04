<?php
// confirmar_suscripcion.php
session_start();
require_once '../Models/Ofertas.php';

$ofertas = new Ofertas();
$title = 'Confirmar Suscripción';
$message = '';
$success = false;
$icon = 'fa-exclamation-circle';
$bg_color = 'bg-danger';

// Obtener token
$token = isset($_GET['token']) ? $_GET['token'] : '';

if (empty($token)) {
    $message = 'Token no proporcionado en la URL.';
} else {
    // Usar el modelo para confirmar
    $result = $ofertas->confirmar_suscripcion($token);
    
    if ($result['success']) {
        $success = true;
        $icon = 'fa-check-circle';
        $bg_color = 'bg-success';
        $message = $result['message'];
        
        // Registrar en log
        error_log("Suscripción confirmada: " . $result['email']);
    } else {
        if (isset($result['already_confirmed']) && $result['already_confirmed']) {
            $success = true;
            $icon = 'fa-info-circle';
            $bg_color = 'bg-info';
            $message = $result['message'];
        } else {
            $message = $result['message'];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title; ?> - NexusBuy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #4361ee 0%, #3a0ca3 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .confirmation-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            max-width: 500px;
            width: 100%;
            overflow: hidden;
        }
        .confirmation-header {
            background: <?php echo $bg_color; ?>;
            color: white;
            padding: 40px 20px;
            text-align: center;
        }
        .confirmation-body {
            padding: 40px;
        }
        .icon-large {
            font-size: 5rem;
            margin-bottom: 20px;
        }
        .btn-home {
            background: #4361ee;
            color: white;
            padding: 12px 35px;
            border-radius: 25px;
            font-weight: 600;
            border: none;
            transition: all 0.3s ease;
        }
        .btn-home:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(67, 97, 238, 0.3);
            color: white;
        }
    </style>
</head>
<body>
    <div class="confirmation-card">
        <div class="confirmation-header">
            <i class="fas <?php echo $icon; ?> icon-large"></i>
            <h2 class="mt-3 mb-0"><?php echo $success ? '¡Éxito!' : 'Lo sentimos'; ?></h2>
        </div>
        <div class="confirmation-body text-center">
            <p class="lead mb-4"><?php echo htmlspecialchars($message); ?></p>
            
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-gift mr-2"></i>
                    <strong>¡Bienvenido a la comunidad NexusBuy!</strong><br>
                    Pronto recibirás nuestras mejores ofertas en tu correo.
                </div>
            <?php else: ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    <strong>No se pudo confirmar la suscripción</strong><br>
                    <?php if (strpos($message, 'inválido') !== false): ?>
                        El enlace de confirmación puede haber expirado.
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
            <div class="mt-4">
                <a href="../index.php" class="btn btn-home">
                    <i class="fas fa-home mr-2"></i> Ir al Inicio
                </a>
                <?php if (!$success): ?>
                    <a href="ofertas.php" class="btn btn-outline-primary ml-2">
                        <i class="fas fa-tag mr-2"></i> Ver Ofertas
                    </a>
                <?php endif; ?>
            </div>
            
            <div class="mt-5 pt-4 border-top">
                <small class="text-muted">
                    <i class="fas fa-question-circle mr-1"></i>
                    ¿Problemas con la confirmación? 
                    <a href="contacto.php">Contáctanos</a>
                </small>
            </div>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Redirigir automáticamente después de 10 segundos si fue exitoso
        <?php if ($success): ?>
        setTimeout(function() {
            window.location.href = '../index.php?suscrito=true';
        }, 10000);
        <?php endif; ?>
        
        // Mostrar botón de reenviar si hay token
        <?php if (!$success && !empty($token)): ?>
        $(document).ready(function() {
            $('.confirmation-body').append(`
                <div class="mt-3">
                    <button class="btn btn-warning btn-sm" onclick="reenviarConfirmacion('<?php echo $token; ?>')">
                        <i class="fas fa-redo mr-1"></i> Reenviar email de confirmación
                    </button>
                </div>
            `);
        });
        
        function reenviarConfirmacion(token) {
            $.post('../Controllers/OfertasController.php', {
                funcion: 'reenviar_confirmacion',
                token: token
            }, function(response) {
                if (response.success) {
                    alert('Email de confirmación reenviado. Revisa tu bandeja de entrada.');
                } else {
                    alert('Error: ' + response.message);
                }
            });
        }
        <?php endif; ?>
    </script>
</body>
</html>