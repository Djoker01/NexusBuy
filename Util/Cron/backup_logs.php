<?php
// Util/Cron/backup_logs.php
// Backup de logs optimizado para Cuba

date_default_timezone_set('America/Havana');

$logDir = __DIR__ . '/logs';
$backupDir = __DIR__ . '/backups';

// Crear directorio de backups si no existe
if (!is_dir($backupDir)) {
    mkdir($backupDir, 0755, true);
}

echo "💾 INICIANDO BACKUP DESDE CUBA - " . date('d/m/Y H:i:s') . "\n";

// Comprimir logs del mes anterior
$lastMonth = date('Y-m', strtotime('first day of last month'));
$backupFile = $backupDir . '/logs_cuba_' . $lastMonth . '.zip';

$zip = new ZipArchive();
if ($zip->open($backupFile, ZipArchive::CREATE) === TRUE) {
    // Agregar archivos de log del mes anterior
    $pattern = $logDir . '/*' . $lastMonth . '*.log';
    $files = glob($pattern);
    
    $added = 0;
    foreach ($files as $file) {
        if (is_file($file)) {
            $zip->addFile($file, basename($file));
            $added++;
        }
    }
    
    $zip->close();
    
    echo "✅ Backup creado: " . basename($backupFile) . " ({$added} archivos)\n";
    
    // Eliminar logs antiguos (más de 3 meses)
    $threeMonthsAgo = date('Y-m', strtotime('-3 months'));
    $oldPattern = $logDir . '/*' . $threeMonthsAgo . '*.log';
    $oldFiles = glob($oldPattern);
    
    $deleted = 0;
    foreach ($oldFiles as $file) {
        if (unlink($file)) {
            $deleted++;
        }
    }
    
    echo "🗑️ Logs antiguos eliminados: {$deleted}\n";
    
} else {
    echo "❌ Error creando backup\n";
}

echo "🏁 BACKUP COMPLETADO\n";
?>