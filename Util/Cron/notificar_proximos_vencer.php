<?php
// Util/Cron/notificar_proximos_vencer.php
// Este script debe ejecutarse una vez al día (preferiblemente a las 9:00 AM)
// CRON: 0 9 * * * php /ruta/completa/Util/Cron/notificar_proximos_vencer.php

require_once '../../Models/Banner.php';
require_once '../../Models/Notificacion.php';

class NotificadorProximosVencer {
    
    private $banner;
    private $notificacion;
    private $log_file;
    
    // Días antes del vencimiento para notificar
    private $dias_notificacion = [7, 3, 1];
    
    public function __construct() {
        $this->banner = new Banner();
        $this->notificacion = new Notificacion();
        $this->log_file = __DIR__ . '/logs/banners.log';
    }
    
    /**
     * Ejecuta las notificaciones de banners próximos a vencer
     */
    public function ejecutar() {
        $this->log("=== INICIANDO NOTIFICACIONES DE PRÓXIMO VENCIMIENTO ===");
        
        try {
            $total_notificaciones = 0;
            
            foreach ($this->dias_notificacion as $dias) {
                $notificados = $this->notificarPorDiasRestantes($dias);
                $total_notificaciones += $notificados;
                $this->log("Notificaciones para {$dias} días: {$notificados}");
            }
            
            $this->log("✅ Proceso completado. Total notificaciones enviadas: {$total_notificaciones}");
            
        } catch (Exception $e) {
            $this->log("❌ ERROR: " . $e->getMessage());
        }
        
        $this->log("=== FIN DEL PROCESO ===\n");
    }
    
    /**
     * Notificar banners que vencen en X días
     */
    private function notificarPorDiasRestantes($dias_restantes) {
        $fecha_limite = date('Y-m-d', strtotime("+{$dias_restantes} days"));
        
        $sql = "SELECT b.*, u.id as usuario_id, u.nombres, u.email 
                FROM banners b
                INNER JOIN usuario u ON b.id_usuario = u.id
                WHERE DATE(b.fecha_fin) = :fecha_limite
                  AND b.estado = 1
                  AND b.estado_pago = 'pagado'
                  AND NOT EXISTS (
                      SELECT 1 FROM notificaciones n 
                      WHERE n.referencia_id = b.id 
                        AND n.tipo = 'banner_proximo_vencer'
                        AND n.estado != 'eliminado'
                        AND DATE(n.fecha_creacion) = CURDATE()
                  )";
        
        $query = $this->banner->acceso->prepare($sql);
        $query->bindParam(':fecha_limite', $fecha_limite);
        $query->execute();
        
        $banners = $query->fetchAll(PDO::FETCH_OBJ);
        $contador = 0;
        
        foreach ($banners as $banner) {
            $this->enviarNotificacion($banner, $dias_restantes);
            $contador++;
        }
        
        return $contador;
    }
    
    /**
     * Enviar notificación específica
     */
    private function enviarNotificacion($banner, $dias_restantes) {
        $mensajes = [
            7 => "Quedan 7 días para que venza tu banner. Renueva ahora y obtén 10% de descuento en la próxima compra.",
            3 => "⚠️ Solo quedan 3 días para que tu banner deje de mostrarse. ¡Renueva hoy para no perder visibilidad!",
            1 => "🚨 ÚLTIMO DÍA: Tu banner vence mañana. Renueva ahora para mantener tu publicidad activa sin interrupción."
        ];
        
        $mensaje = $mensajes[$dias_restantes] ?? "Tu banner '{$banner->titulo}' vencerá en {$dias_restantes} días. Considera renovarlo.";
        
        // Añadir información de renovación
        $dias_duracion = $this->getDiasDuracion($banner->tipo_duracion);
        $precio = $this->getPrecioDuracion($banner->tipo_duracion);
        
        $mensaje .= "\n\n📊 Rendimiento actual:";
        $mensaje .= "\n• Impresiones: " . $this->getImpresionesBanner($banner->id);
        $mensaje .= "\n• Clicks: " . $this->getClicksBanner($banner->id);
        $mensaje .= "\n• CTR: " . $this->getCTRBanner($banner->id) . "%";
        $mensaje .= "\n\n💰 Renueva por {$dias_duracion} días por $" . number_format($precio, 2);
        
        // Crear notificación
        $this->notificacion->crearNotificacion(
            $banner->usuario_id,
            'banner_proximo_vencer',
            "📅 Tu banner vence en {$dias_restantes} días",
            $mensaje,
            $banner->id
        );
        
        $this->log("Notificación enviada al usuario {$banner->usuario_id} por banner {$banner->id} (vence en {$dias_restantes} días)");
    }
    
    /**
     * Obtener impresiones del banner
     */
    private function getImpresionesBanner($banner_id) {
        $sql = "SELECT SUM(impresiones) as total FROM banner_estadisticas WHERE banner_id = ?";
        $query = $this->banner->acceso->prepare($sql);
        $query->execute([$banner_id]);
        $result = $query->fetch();
        return number_format($result['total'] ?? 0);
    }
    
    /**
     * Obtener clicks del banner
     */
    private function getClicksBanner($banner_id) {
        $sql = "SELECT SUM(clicks) as total FROM banner_estadisticas WHERE banner_id = ?";
        $query = $this->banner->acceso->prepare($sql);
        $query->execute([$banner_id]);
        $result = $query->fetch();
        return number_format($result['total'] ?? 0);
    }
    
    /**
     * Obtener CTR del banner
     */
    private function getCTRBanner($banner_id) {
        $sql = "SELECT 
                    SUM(impresiones) as imp,
                    SUM(clicks) as clk
                FROM banner_estadisticas 
                WHERE banner_id = ?";
        $query = $this->banner->acceso->prepare($sql);
        $query->execute([$banner_id]);
        $result = $query->fetch();
        
        if ($result && $result['imp'] > 0) {
            return round(($result['clk'] / $result['imp']) * 100, 2);
        }
        return 0;
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
$notificador = new NotificadorProximosVencer();
$notificador->ejecutar();
?>