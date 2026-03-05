<?php
// CONFIGURACIÓN PARA CUBA

// ==============================================
// 1. ZONA HORARIA CUBA
// ==============================================
date_default_timezone_set('America/Havana');

// ==============================================
// 2. ACTIVACIÓN DEL SISTEMA
// ==============================================
define('EMAIL_ENABLED', true);

// ==============================================
// 3. CONFIGURACIÓN SMTP (AJUSTA CON TUS DATOS)
// ==============================================
define('EMAIL_HOST', 'smtp.gmail.com');      // Recomendado para Cuba
define('EMAIL_PORT', 587);                   // Puerto para Cuba (generalmente abierto)
define('EMAIL_USERNAME', 'noeldavidchaconsanchez@gmail.com');
define('EMAIL_PASSWORD', 'hzhe mgxq qhyd ygsw'); // App Password de Gmail
define('EMAIL_ENCRYPTION', 'tls');           // tls funciona mejor en Cuba

// ==============================================
// 4. CONFIGURACIÓN DEL REMITENTE
// ==============================================
define('EMAIL_FROM_ADDRESS', 'ofertas@nexusbuy.com');
define('EMAIL_FROM_NAME', 'NexusBuy Ofertas');
define('EMAIL_REPLY_TO', 'soporte@nexusbuy.com');

// ==============================================
// 5. CONFIGURACIÓN GENERAL
// ==============================================
define('EMAIL_CHARSET', 'UTF-8');
define('EMAIL_DEBUG', 0);                    // 0 en producción
define('EMAIL_TIMEOUT', 60);                 // 60 segundos para conexiones lentas

// ==============================================
// 6. CONFIGURACIÓN DEL SITIO - CUBA
// ==============================================
define('SITE_URL', 'http://localhost/nexusbuy'); // Cambia por tu dominio
define('SITE_NAME', 'NexusBuy');
define('SITE_LOGO_URL', '../Util/img/logo.png');

// ==============================================
// 7. HORARIOS DE ENVÍO OPTIMIZADOS PARA CUBA
// ==============================================
// Horarios recomendados para Cuba (cuando hay mejor conectividad)
define('SEND_DAILY_TIME', '09:00');      // 9:00 AM - Mañana
define('SEND_WEEKLY_TIME', '10:00');     // 10:00 AM - Lunes
define('SEND_MONTHLY_TIME', '11:00');    // 11:00 AM - Día 1
define('SEND_TEST_TIME', '15:00');       // 3:00 PM - Para pruebas

// ==============================================
// 8. CONFIGURACIÓN ESPECÍFICA PARA INTERNET EN CUBA
// ==============================================
define('EMAIL_SMTP_KEEPALIVE', false);   // false para conexiones intermitentes
define('EMAIL_SMTP_AUTO_TLS', true);     // true para mejor compatibilidad
define('EMAIL_SMTP_TIMEOUT', 60);        // 60 segundos timeout
define('EMAIL_MAX_RETRIES', 3);          // Reintentos por problemas de conexión
define('EMAIL_RETRY_DELAY', 10);         // 10 segundos entre reintentos

// ==============================================
// 9. CONFIGURACIÓN DE LOGS
// ==============================================
define('LOG_TIMEZONE', 'America/Havana');
define('LOG_FORMAT', 'd/m/Y H:i:s');     // Formato cubano: día/mes/año
?>