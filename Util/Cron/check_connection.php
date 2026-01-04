<?php
// Util/Cron/check_connection.php
// Verifica conectividad desde Cuba

date_default_timezone_set('America/Havana');

$logFile = __DIR__ . '/logs/connection_check.log';

function logMessage($message)
{
    global $logFile;
    $timestamp = date('d/m/Y H:i:s');
    $logMessage = "[{$timestamp} 🇨🇺] {$message}\n";
    file_put_contents($logFile, $logMessage, FILE_APPEND);
    echo $logMessage;
}

logMessage("🔍 VERIFICANDO CONECTIVIDAD DESDE CUBA");

// 1. Verificar servicios esenciales
$services = [
    'SMTP Gmail' => ['host' => 'smtp.gmail.com', 'port' => 587],
    'DNS Google' => ['host' => '8.8.8.8', 'port' => 53],
    'Internet' => ['host' => 'google.com', 'port' => 80]
];

foreach ($services as $name => $service) {
    $start = microtime(true);
    
    $fp = @fsockopen($service['host'], $service['port'], $errno, $errstr, 10);
    
    if ($fp) {
        $time = round((microtime(true) - $start) * 1000, 2);
        logMessage("✅ {$name}: CONECTADO ({$time}ms)");
        fclose($fp);
    } else {
        logMessage("❌ {$name}: SIN CONEXIÓN ({$errstr})");
    }
}

// 2. Verificar base de datos
try {
    require_once __DIR__ . '/../Config/config.php';
    require_once __DIR__ . '/../../Models/Conexion.php';
    
    $db = new Conexion();
    $pdo = $db->pdo;
    
    // Consulta simple
    $stmt = $pdo->query("SELECT 1");
    if ($stmt->fetch()) {
        logMessage("✅ Base de datos: CONECTADA");
    }
    
} catch (Exception $e) {
    logMessage("❌ Base de datos: ERROR - " . $e->getMessage());
}

// 3. Verificar correos pendientes
try {
    require_once __DIR__ . '/../../Models/Ofertas.php';
    $ofertas = new Ofertas();
    
    $sql = "SELECT COUNT(*) as pendientes FROM suscripcion_ofertas 
            WHERE ultimo_envio IS NULL OR ultimo_envio < DATE_SUB(NOW(), INTERVAL 1 WEEK)";
    
    $query = $ofertas->acceso->prepare($sql);
    $query->execute();
    $pendientes = $query->fetch(PDO::FETCH_ASSOC)['pendientes'];
    
    logMessage("📧 Correos pendientes: {$pendientes}");
    
} catch (Exception $e) {
    logMessage("⚠️ No se pudo verificar correos pendientes");
}

// 4. Estadísticas del sistema
logMessage("💾 Memoria usada: " . round(memory_get_usage() / 1024 / 1024, 2) . " MB");
logMessage("⏰ Hora servidor: " . date('d/m/Y H:i:s'));
logMessage("📍 Zona horaria: " . date_default_timezone_get());

logMessage("✅ VERIFICACIÓN COMPLETADA");
?>