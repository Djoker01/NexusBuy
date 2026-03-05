<?php
// Util/Cron/clean_logs.php
// Limpia logs antiguos automáticamente

if (php_sapi_name() !== 'cli') {
    die('Solo accesible desde CLI');
}

date_default_timezone_set('America/Mexico_City');

$logDir = __DIR__ . '/logs';
$daysToKeep = 30; // Mantener logs de 30 días

echo "🧹 Limpiando logs antiguos...\n";

if (!is_dir($logDir)) {
    echo "✅ No hay directorio de logs\n";
    exit;
}

$files = scandir($logDir);
$deleted = 0;
$kept = 0;

foreach ($files as $file) {
    if ($file == '.' || $file == '..') continue;
    
    $filePath = $logDir . '/' . $file;
    
    // Verificar si es archivo de log
    if (is_file($filePath) && preg_match('/\.log$/i', $file)) {
        $fileTime = filemtime($filePath);
        $daysOld = (time() - $fileTime) / (60 * 60 * 24);
        
        if ($daysOld > $daysToKeep) {
            if (unlink($filePath)) {
                echo "🗑️ Eliminado: $file ($daysOld días)\n";
                $deleted++;
            } else {
                echo "❌ Error eliminando: $file\n";
            }
        } else {
            $kept++;
        }
    }
}

echo "📊 Resultado: Eliminados: $deleted, Mantenidos: $kept\n";
?>