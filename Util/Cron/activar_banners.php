<?php
// Util/Cron/activar_banners.php
// Este script debe ejecutarse cada minuto o cada 5 minutos
// CRON: * * * * * php /ruta/completa/Util/Cron/activar_banners.php

require_once '../../Models/Banner.php';
require_once '../../Models/Notificacion.php';
require_once '../../Models/Escrow.php';

class ActivadorBanners {
    
    private $banner;
    private $notificacion;
    private $escrow;
    private $log_file;
    
    public function __construct() {
        $this->banner = new Banner();
        $this->notificacion = new Notificacion();
        $this->escrow = new Escrow();
        $this->log_file = __DIR__ . '/logs/banners.log';
        
        // Crear directorio de logs si no existe
        $log_dir = __DIR__ . '/logs';
        if (!file_exists($log_dir)) {
            mkdir($log_dir, 0777, true);
        }
    }
    
    /**
     * Ejecuta la activación de banners programados
     */
    public function ejecutar() {
        $this->log("=== INICIANDO ACTIVACIÓN DE BANNERS PROGRAMADOS ===");
        
        try {
            // Activar banners que deben empezar hoy
            $activados = $this->activarBannersHoy();
            
            // Notificar a los usuarios
            if ($activados > 0) {
                $this->notificarActivaciones($activados);
            }
            
            $this->log("✅ Proceso completado. Banners activados: {$activados}");
            
        } catch (Exception $e) {
            $this->log("❌ ERROR: " . $e->getMessage());
        }
        
        $this->log("=== FIN DEL PROCESO ===\n");
    }
    
    /**
     * Activar banners cuya fecha_inicio es hoy o anterior
     */
    private function activarBannersHoy() {
        // Actualizar banners que tienen fecha_inicio <= hoy y fecha_fin > hoy
        $sql = "UPDATE banners 
                SET estado = 1, activo = 1
                WHERE estado_pago = 'pagado' 
                  AND estado = 0
                  AND fecha_inicio <= CURDATE()
                  AND fecha_fin > CURDATE()";
        
        $this->banner->acceso->query($sql);
        $afectados = $this->banner->acceso->affected_rows;
        
        // Registrar en log
        $this->log("Banners activados: {$afectados}");
        
        return $afectados;
    }
    
    /**
     * Notificar a los usuarios cuyos banners se activaron
     */
    private function notificarActivaciones($cantidad) {
        // Obtener detalles de los banners activados para notificar
        $sql = "SELECT b.*, u.id as usuario_id, u.nombres, u.email 
                FROM banners b
                INNER JOIN usuario u ON b.id_usuario = u.id
                WHERE b.estado = 1 
                  AND b.activo = 1
                  AND DATE(b.fecha_inicio) = CURDATE()
                  AND b.estado_pago = 'pagado'";
        
        $result = $this->banner->acceso->query($sql);
        
        while ($banner = $result->fetch_object()) {
            // Crear notificación para cada usuario
            $this->notificacion->crearNotificacion(
                $banner->usuario_id,
                'banner_activado',
                '🎯 Tu banner ya está activo',
                "El banner '{$banner->titulo}' ha sido activado y ya se está mostrando en " . ucfirst($banner->posicion),
                $banner->id
            );
            
            $this->log("Notificación enviada al usuario {$banner->usuario_id} por banner {$banner->id}");
        }
    }
    
    /**
     * Escribir en archivo de log
     */
    private function log($mensaje) {
        $fecha = date('Y-m-d H:i:s');
        $linea = "[{$fecha}] {$mensaje}" . PHP_EOL;
        file_put_contents($this->log_file, $linea, FILE_APPEND);
        echo $linea; // También mostrar en consola si se ejecuta manualmente
    }
}

// Ejecutar el script
$activador = new ActivadorBanners();
$activador->ejecutar();
?>