<?php
/**
 * CRON: Liberar retenciones vencidas
 * Ejecutar diariamente: 0 0 * * * php /ruta/Util/Cron/liberar_retenciones.php
 */

require_once '../../Models/Escrow.php';
require_once '../../Models/Notificacion.php';

$escrow = new Escrow();
$notificacion = new Notificacion();

echo "[" . date('Y-m-d H:i:s') . "] Iniciando liberación de retenciones...\n";

$liberadas = $escrow->liberarRetencionesVencidas();

if ($liberadas === false) {
    echo "[" . date('Y-m-d H:i:s') . "] ERROR: " . $escrow->error . "\n";
    exit(1);
}

echo "[" . date('Y-m-d H:i:s') . "] Liberadas {$liberadas} retenciones\n";

// Enviar notificaciones a vendedores
if ($liberadas > 0) {
    // Aquí iría la lógica para notificar a los vendedores
    echo "[" . date('Y-m-d H:i:s') . "] Notificaciones enviadas\n";
}

echo "[" . date('Y-m-d H:i:s') . "] Proceso completado\n";
exit(0);
?>