<?php
// Util/Cron/test_banners.php
// Script para probar manualmente los CRONs

require_once '../../Models/Banner.php';

class TestBanners {
    
    private $banner;
    
    public function __construct() {
        $this->banner = new Banner();
    }
    
    public function ejecutar() {
        echo "🔍 TEST DEL MÓDULO DE BANNERS\n";
        echo "==============================\n\n";
        
        // 1. Verificar banners programados para hoy
        $this->verificarProgramados();
        
        // 2. Verificar banners que vencen hoy
        $this->verificarVencimientosHoy();
        
        // 3. Verificar banners próximos a vencer
        $this->verificarProximosVencer();
        
        // 4. Verificar estadísticas
        $this->verificarEstadisticas();
    }
    
    private function verificarProgramados() {
        $sql = "SELECT COUNT(*) as total 
                FROM banners 
                WHERE fecha_inicio <= CURDATE() 
                  AND fecha_fin > CURDATE()
                  AND estado = 0
                  AND estado_pago = 'pagado'";
        
        $result = $this->banner->acceso->query($sql);
        $total = $result->fetch()->total;
        
        echo "📅 Banners programados para HOY: {$total}\n";
        
        if ($total > 0) {
            echo "   ⏰ Se activarán con el CRON correspondiente.\n";
        }
        echo "\n";
    }
    
    private function verificarVencimientosHoy() {
        $sql = "SELECT b.*, u.nombres 
                FROM banners b
                LEFT JOIN usuario u ON b.id_usuario = u.id
                WHERE DATE(b.fecha_fin) = CURDATE()
                  AND b.estado = 1";
        
        $result = $this->banner->acceso->query($sql);
        
        echo "⏳ Banners que VENCEN HOY:\n";
        if ($result->num_rows > 0) {
            while ($b = $result->fetch_object()) {
                echo "   • ID {$b->id}: '{$b->titulo}' (Usuario: {$b->nombres})\n";
            }
        } else {
            echo "   • No hay banners que venzan hoy.\n";
        }
        echo "\n";
    }
    
    private function verificarProximosVencer() {
        $dias = [7, 3, 1];
        
        echo "🔔 Banners PRÓXIMOS A VENCER:\n";
        foreach ($dias as $dia) {
            $fecha = date('Y-m-d', strtotime("+{$dia} days"));
            
            $sql = "SELECT COUNT(*) as total 
                    FROM banners 
                    WHERE DATE(fecha_fin) = :fecha
                      AND estado = 1
                      AND estado_pago = 'pagado'";
            
            $query = $this->banner->acceso->prepare($sql);
            $query->bindParam(':fecha', $fecha);
            $query->execute();
            $total = $query->fetch()->total;
            
            echo "   • En {$dia} días (fecha: {$fecha}): {$total} banners\n";
        }
        echo "\n";
    }
    
    private function verificarEstadisticas() {
        echo "📊 ESTADÍSTICAS GENERALES:\n";
        
        // Total banners
        $sql = "SELECT COUNT(*) as total FROM banners";
        $result = $this->banner->acceso->query($sql);
        $total = $result->fetch()->total;
        echo "   • Total banners: {$total}\n";
        
        // Banners activos
        $sql = "SELECT COUNT(*) as total 
                FROM banners 
                WHERE estado = 1 
                  AND fecha_fin > NOW()";
        $result = $this->banner->acceso->query($sql);
        $activos = $result->fetch()->total;
        echo "   • Banners activos: {$activos}\n";
        
        // Banners pagados vs gratuitos
        $sql = "SELECT estado_pago, COUNT(*) as total 
                FROM banners 
                GROUP BY estado_pago";
        $result = $this->banner->acceso->query($sql);
        while ($row = $result->fetch_object()) {
            echo "   • {$row->estado_pago}: {$row->total}\n";
        }
        echo "\n";
        
        // Recomendación de CRON
        echo "⚙️  CONFIGURACIÓN RECOMENDADA DE CRON:\n";
        echo "   • activar_banners.php      → Cada minuto\n";
        echo "   • desactivar_banners.php   → A las 00:05 diario\n";
        echo "   • notificar_proximos_vencer.php → A las 9:00 diario\n";
    }
}

// Ejecutar test
$test = new TestBanners();
$test->ejecutar();
?>