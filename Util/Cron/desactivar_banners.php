<?php
// Util/Cron/desactivar_banners.php
// Este script debe ejecutarse una vez al día (preferiblemente a las 00:05)
// CRON: 5 0 * * * php /ruta/completa/Util/Cron/desactivar_banners.php

require_once '../../Models/Banner.php';
require_once '../../Models/Notificacion.php';
require_once '../../Models/Escrow.php';

class DesactivadorBanners {
    
    private $banner;
    private $notificacion;
    private $escrow;
    private $log_file;
    
    public function __construct() {
        $this->banner = new Banner();
        $this->notificacion = new Notificacion();
        $this->escrow = new Escrow();
        $this->log_file = __DIR__ . '/logs/banners.log';
    }
    
    /**
     * Ejecuta la desactivación de banners vencidos
     */
    public function ejecutar() {
        $this->log("=== INICIANDO DESACTIVACIÓN DE BANNERS VENCIDOS ===");
        
        try {
            // Desactivar banners vencidos hoy
            $desactivados = $this->desactivarBannersVencidos();
            
            // Notificar a los usuarios
            if ($desactivados > 0) {
                $this->notificarVencimientos($desactivados);
            }
            
            $this->log("✅ Proceso completado. Banners desactivados: {$desactivados}");
            
        } catch (Exception $e) {
            $this->log("❌ ERROR: " . $e->getMessage());
        }
        
        $this->log("=== FIN DEL PROCESO ===\n");
    }
    
    /**
     * Desactivar banners que ya vencieron
     */
    private function desactivarBannersVencidos() {
        // Actualizar banners que tienen fecha_fin < hoy
        $sql = "UPDATE banners 
                SET estado = 0, 
                    activo = 0, 
                    estado_pago = 'vencido'
                WHERE fecha_fin < CURDATE() 
                  AND estado = 1
                  AND estado_pago = 'pagado'";
        
        $this->banner->acceso->query($sql);
        $afectados = $this->banner->acceso->affected_rows;
        
        $this->log("Banners desactivados por vencimiento: {$afectados}");
        
        return $afectados;
    }
    
    /**
     * Notificar a los usuarios cuyos banners vencieron
     */
    private function notificarVencimientos($cantidad) {
        // Obtener banners que vencieron hoy
        $sql = "SELECT b.*, u.id as usuario_id, u.nombres, u.email 
                FROM banners b
                INNER JOIN usuario u ON b.id_usuario = u.id
                WHERE DATE(b.fecha_fin) = CURDATE()
                  AND b.estado_pago = 'vencido'";
        
        $result = $this->banner->acceso->query($sql);
        
        while ($banner = $result->fetch_object()) {
            // Calcular oferta de renovación (opcional)
            $dias = $this->getDiasDuracion($banner->tipo_duracion);
            $precio = $this->getPrecioDuracion($banner->tipo_duracion);
            
            // Crear notificación
            $this->notificacion->crearNotificacion(
                $banner->usuario_id,
                'banner_vencido',
                '⏰ Tu banner ha vencido',
                "El banner '{$banner->titulo}' ha llegado a su fecha de vencimiento. " .
                "Renueva por {$dias} días por solo $" . number_format($precio, 2) . " para seguir visible.",
                $banner->id
            );
            
            $this->log("Notificación de vencimiento enviada al usuario {$banner->usuario_id}");
        }
    }
    
    /**
     * Obtener días de duración según tipo
     */
    private function getDiasDuracion($tipo) {
        $dias = [
            '3_dias' => 3,
            '1_semana' => 7,
            '1_mes' => 30
        ];
        return $dias[$tipo] ?? 7;
    }
    
    /**
     * Obtener precio según tipo
     */
    private function getPrecioDuracion($tipo) {
        $precios = [
            '3_dias' => 100,
            '1_semana' => 250,
            '1_mes' => 750
        ];
        return $precios[$tipo] ?? 250;
    }
    
    /**
     * Escribir en archivo de log
     */
    private function log($mensaje) {
        $fecha = date('Y-m-d H:i:s');
        $linea = "[{$fecha}] {$mensaje}" . PHP_EOL;
        file_put_contents($this->log_file, $linea, FILE_APPEND);
        echo $linea;
    }
}

// Ejecutar el script
$desactivador = new DesactivadorBanners();
$desactivador->ejecutar();
?>