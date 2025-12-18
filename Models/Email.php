<?php
include_once 'Conexion.php';
class Email {
    private $conexion;
    
    public function __construct($conexion) {
        $this->conexion = $conexion;
    }
    
    // ✅ Enviar email de verificación
    public function enviarVerificacion($email, $nombre, $token) {
        $asunto = "Verifica tu cuenta - NexusBuy";
        $enlace = "https://tudominio.com/verificar.php?token=" . $token;
        
        $mensaje = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #007bff; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; background: #f8f9fa; }
                .button { background: #007bff; color: white; padding: 12px 24px; text-decoration: none; border-radius: 4px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>NexusBuy</h1>
                </div>
                <div class='content'>
                    <h2>¡Hola $nombre!</h2>
                    <p>Gracias por registrarte en NexusBuy. Para activar tu cuenta, haz clic en el siguiente botón:</p>
                    <p style='text-align: center;'>
                        <a href='$enlace' class='button'>Verificar Cuenta</a>
                    </p>
                    <p>Si el botón no funciona, copia y pega este enlace en tu navegador:</p>
                    <p><a href='$enlace'>$enlace</a></p>
                    <p>Este enlace expirará en 24 horas.</p>
                </div>
            </div>
        </body>
        </html>
        ";
        
        return $this->enviarEmail($email, $asunto, $mensaje);
    }
    
    // ✅ Enviar email de recuperación de contraseña
    public function enviarRecuperacion($email, $nombre, $token) {
        $asunto = "Recupera tu contraseña - NexusBuy";
        $enlace = "https://tudominio.com/recuperar.php?token=" . $token;
        
        $mensaje = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #dc3545; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; background: #f8f9fa; }
                .button { background: #dc3545; color: white; padding: 12px 24px; text-decoration: none; border-radius: 4px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>Recuperación de Contraseña</h1>
                </div>
                <div class='content'>
                    <h2>¡Hola $nombre!</h2>
                    <p>Hemos recibido una solicitud para restablecer tu contraseña. Haz clic en el siguiente botón para crear una nueva contraseña:</p>
                    <p style='text-align: center;'>
                        <a href='$enlace' class='button'>Restablecer Contraseña</a>
                    </p>
                    <p>Si el botón no funciona, copia y pega este enlace en tu navegador:</p>
                    <p><a href='$enlace'>$enlace</a></p>
                    <p>Este enlace expirará en 1 hora.</p>
                    <p>Si no solicitaste este cambio, puedes ignorar este mensaje.</p>
                </div>
            </div>
        </body>
        </html>
        ";
        
        return $this->enviarEmail($email, $asunto, $mensaje);
    }
    
    // ✅ Enviar email de confirmación de pedido
    public function enviarConfirmacionPedido($email, $nombre, $orden) {
        $asunto = "Confirmación de Pedido #{$orden['numero_orden']} - NexusBuy";
        
        $mensaje = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #28a745; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; background: #f8f9fa; }
                .table { width: 100%; border-collapse: collapse; }
                .table th, .table td { padding: 8px; text-align: left; border-bottom: 1px solid #ddd; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>¡Pedido Confirmado!</h1>
                </div>
                <div class='content'>
                    <h2>¡Hola $nombre!</h2>
                    <p>Tu pedido <strong>#{$orden['numero_orden']}</strong> ha sido confirmado exitosamente.</p>
                    
                    <h3>Resumen del Pedido:</h3>
                    <table class='table'>
                        <tr>
                            <th>Producto</th>
                            <th>Cantidad</th>
                            <th>Precio</th>
                        </tr>
        ";
        
        // Agregar productos (simulado)
        $mensaje .= "
                        <tr>
                            <td>Producto Ejemplo</td>
                            <td>1</td>
                            <td>$50.00</td>
                        </tr>
        ";
        
        $mensaje .= "
                    </table>
                    
                    <p><strong>Total: {$orden['total']}</strong></p>
                    <p>Puedes ver el estado de tu pedido en cualquier momento desde tu cuenta.</p>
                    <p>¡Gracias por tu compra!</p>
                </div>
            </div>
        </body>
        </html>
        ";
        
        return $this->enviarEmail($email, $asunto, $mensaje);
    }
    
    // ✅ Función base para enviar emails
    private function enviarEmail($para, $asunto, $mensaje) {
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= "From: NexusBuy <no-reply@nexusbuy.com>" . "\r\n";
        $headers .= "Reply-To: soporte@nexusbuy.com" . "\r\n";
        
        // En producción, usarías una librería como PHPMailer
        return mail($para, $asunto, $mensaje, $headers);
    }
}