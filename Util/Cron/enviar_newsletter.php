<?php
// Especialmente optimizado para Cuba

// Forzar zona horaria Cuba al inicio
date_default_timezone_set('America/Havana');

// Verificar acceso
if (php_sapi_name() !== 'cli' && !isset($_SERVER['HTTP_CRON_TOKEN'])) {
    header('HTTP/1.1 403 Forbidden');
    die('Acceso prohibido');
}

// Incluir configuración y clases
require_once __DIR__ . '/../Config/config.php';
require_once __DIR__ . '/../../Models/Ofertas.php';
require_once __DIR__ . '/../Mail/Mailer.php';

class CubaNewsletterScheduler
{
    private $ofertas;
    private $mailer;
    private $logFile;
    private $cubaHolidays; // Feriados cubanos
    
    public function __construct()
    {
        // Asegurar zona horaria
        date_default_timezone_set('America/Havana');
        
        $this->ofertas = new Ofertas();
        $this->mailer = new \Util\Mail\Mailer();
        $this->logFile = __DIR__ . '/logs/cuba_newsletter_' . date('Y-m') . '.log';
        
        // Crear directorio de logs
        if (!is_dir(__DIR__ . '/logs')) {
            mkdir(__DIR__ . '/logs', 0755, true);
        }
        
        // Feriados cubanos 2024-2025 (ajusta según año)
        $this->cubaHolidays = [
            '01-01', // Año Nuevo
            '01-02', // Triunfo de la Revolución
            '05-01', // Día del Trabajo
            '07-25', // Conmemoración del asalto al Moncada
            '07-26', // Día de la Rebeldía Nacional
            '07-27', // Día de la Rebeldía Nacional
            '10-10', // Inicio de las Guerras de Independencia
            '12-25'  // Navidad
        ];
    }
    
    /**
     * Verificar si hoy es feriado en Cuba
     */
    private function isCubaHoliday()
    {
        $today = date('m-d');
        return in_array($today, $this->cubaHolidays);
    }
    
    /**
     * Horarios optimizados para Cuba
     */
    private function getCubaSchedule()
    {
        return [
            'diaria' => [
                'enabled' => true,
                'time' => '09:00', // 9:00 AM - Mejor conectividad matutina
                'skip_holidays' => false
            ],
            'semanal' => [
                'enabled' => true,
                'day' => 1, // Lunes
                'time' => '10:00', // 10:00 AM
                'skip_holidays' => true
            ],
            'mensual' => [
                'enabled' => true,
                'day' => 1, // Día 1 de cada mes
                'time' => '11:00', // 11:00 AM
                'skip_holidays' => true
            ]
        ];
    }
    
    /**
     * Ejecutar según horarios cubanos
     */
    public function run()
    {
        $this->log("🇨🇺 INICIANDO ENVÍOS PROGRAMADOS PARA CUBA");
        $this->log("⏰ Hora Cuba: " . date('d/m/Y H:i:s'));
        $this->log("📍 Zona Horaria: " . date_default_timezone_get());
        
        // Verificar si es feriado
        if ($this->isCubaHoliday()) {
            $this->log("🎌 HOY ES FERIADO EN CUBA - Saltando envíos no esenciales");
            return $this->sendHolidayGreetings();
        }
        
        $schedule = $this->getCubaSchedule();
        $currentTime = date('H:i');
        $currentDay = date('N'); // 1-7 (lunes=1)
        $currentMonthDay = date('j'); // 1-31
        
        $results = [];
        
        // Procesar cada frecuencia
        foreach ($schedule as $freq => $config) {
            if (!$config['enabled']) {
                $this->log("⏭️ $freq: Deshabilitada");
                continue;
            }
            
            $shouldRun = false;
            
            switch($freq) {
                case 'diaria':
                    // Todos los días a la hora configurada
                    $shouldRun = ($currentTime === $config['time']);
                    break;
                    
                case 'semanal':
                    // Lunes a la hora configurada
                    $shouldRun = ($currentDay == $config['day'] && $currentTime === $config['time']);
                    break;
                    
                case 'mensual':
                    // Día 1 a la hora configurada
                    $shouldRun = ($currentMonthDay == $config['day'] && $currentTime === $config['time']);
                    break;
            }
            
            if ($shouldRun) {
                $this->log("🚀 Ejecutando: $freq a las {$config['time']}");
                $results[$freq] = $this->sendByFrequency($freq);
                
                // Pausa entre frecuencias para no saturar
                sleep(5);
            }
        }
        
        if (empty($results)) {
            $this->log("ℹ️ No hay envíos programados para este horario");
            $results['info'] = 'No hay envíos programados para ' . $currentTime;
        }
        
        return $results;
    }
    
    /**
     * Enviar saludo en feriados cubanos
     */
    private function sendHolidayGreetings()
    {
        $this->log("🎉 Enviando saludos de feriado");
        
        try {
            // Obtener todos los suscriptores
            $suscriptores = $this->getAllSubscribers();
            
            foreach ($suscriptores as $suscriptor) {
                $this->sendHolidayEmail(
                    $suscriptor['email'],
                    $suscriptor['nombre']
                );
                sleep(2); // Pausa más larga en feriados
            }
            
            return ['holiday_greetings_sent' => count($suscriptores)];
            
        } catch (Exception $e) {
            $this->log("❌ Error enviando saludos: " . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }
    
    /**
     * Email especial para feriados cubanos
     */
    private function sendHolidayEmail($email, $name)
    {
        if (!$this->mailer->isConfigured()) return false;
        
        try {
            $this->mailer->clearAllRecipients();
            $this->mailer->addAddress($email, $name);
            
            $subject = "🎉 ¡Feliz " . $this->getCubaHolidayName() . "! - " . SITE_NAME;
            
            $htmlBody = $this->getHolidayTemplate($name);
            $textBody = "¡Feliz " . $this->getCubaHolidayName() . "!\n\n";
            $textBody .= "Hoy es feriado en Cuba. Nuestras ofertas regresan mañana.\n";
            $textBody .= "¡Disfruta del día!\n\n" . SITE_NAME;
            
            $this->mailer->Subject = $subject;
            $this->mailer->isHTML(true);
            $this->mailer->Body = $htmlBody;
            $this->mailer->AltBody = $textBody;
            
            return $this->mailer->send();
            
        } catch (Exception $e) {
            $this->log("❌ Error email feriado: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtener nombre del feriado cubano actual
     */
    private function getCubaHolidayName()
    {
        $monthDay = date('m-d');
        
        $holidays = [
            '01-01' => 'Año Nuevo',
            '01-02' => 'Triunfo de la Revolución',
            '05-01' => 'Día del Trabajo',
            '07-25' => 'Conmemoración del asalto al Moncada',
            '07-26' => 'Día de la Rebeldía Nacional',
            '07-27' => 'Día de la Rebeldía Nacional',
            '10-10' => 'Inicio de las Guerras de Independencia',
            '12-25' => 'Navidad'
        ];
        
        return isset($holidays[$monthDay]) ? $holidays[$monthDay] : 'Feriado Nacional';
    }
    
    /**
     * Template para feriados cubanos
     */
    private function getHolidayTemplate($name)
    {
        $holidayName = $this->getCubaHolidayName();
        
        return '
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; background: #f0f8ff; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #002868; color: white; padding: 30px; text-align: center; }
                .content { background: white; padding: 30px; }
                .cuba-flag { color: #002868; font-weight: bold; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>🎉 ¡' . $holidayName . '!</h1>
                    <p class="cuba-flag">🇨🇺 Cuba</p>
                </div>
                <div class="content">
                    <h2>Hola ' . htmlspecialchars($name) . ',</h2>
                    <p>Hoy celebramos <strong>' . $holidayName . '</strong> en Cuba.</p>
                    <p>Nuestro equipo desea que disfrutes este día especial con tus seres queridos.</p>
                    <p>Las ofertas regresarán mañana con nuevas promociones.</p>
                    <p style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee;">
                        <strong>¡Feliz ' . $holidayName . '!</strong><br>
                        El equipo de ' . SITE_NAME . '
                    </p>
                </div>
            </div>
        </body>
        </html>';
    }
    
    /**
     * Enviar por frecuencia (heredado del scheduler original)
     */
    private function sendByFrequency($frecuencia)
    {
        // Método similar al anterior pero optimizado para Cuba
        $result = ['total' => 0, 'success' => 0, 'failed' => 0];
        
        try {
            $suscriptores = $this->getConfirmedSubscribers($frecuencia);
            
            if (empty($suscriptores)) {
                $this->log("ℹ️ No hay suscriptores $frecuencia");
                return $result;
            }
            
            $ofertas = $this->getOffersByFrequency($frecuencia);
            
            if (empty($ofertas)) {
                $this->log("⚠️ Sin ofertas $frecuencia");
                return $result;
            }
            
            $offersData = $this->prepareOffersData($ofertas, $frecuencia);
            
            foreach ($suscriptores as $suscriptor) {
                $result['total']++;
                
                try {
                    // Envío optimizado para Cuba
                    $sent = $this->sendEmailCubaOptimized(
                        $suscriptor,
                        $offersData,
                        $frecuencia
                    );
                    
                    if ($sent) {
                        $result['success']++;
                        $this->log("✅ {$suscriptor['email']}");
                    } else {
                        $result['failed']++;
                        $this->log("❌ {$suscriptor['email']}");
                    }
                    
                    // Pausa adaptativa para conexiones cubanas
                    sleep($this->getCubaPauseTime());
                    
                } catch (Exception $e) {
                    $result['failed']++;
                    $this->log("❌ Excepción: " . $e->getMessage());
                }
            }
            
            $this->log("📊 $frecuencia: {$result['success']}/{$result['total']} enviados");
            
        } catch (Exception $e) {
            $this->log("❌ Error $frecuencia: " . $e->getMessage());
        }
        
        return $result;
    }
    
    /**
     * Envío optimizado para condiciones de red en Cuba
     */
    private function sendEmailCubaOptimized($suscriptor, $offersData, $frecuencia)
    {
        if (!$this->mailer->isConfigured()) return false;
        
        // Configuraciones específicas para Cuba
        $this->mailer->Timeout = 60; // Timeout más largo
        $this->mailer->SMTPKeepAlive = false; // No mantener conexión
        
        // Intentar hasta 3 veces con delay
        $maxRetries = 3;
        
        for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
            try {
                $sent = $this->mailer->sendNewsletter(
                    $suscriptor['email'],
                    $suscriptor['nombre'],
                    $offersData,
                    $frecuencia
                );
                
                if ($sent) return true;
                
                // Si falla, esperar y reintentar
                if ($attempt < $maxRetries) {
                    $delay = $attempt * 5; // 5, 10, 15 segundos
                    $this->log("⚠️ Reintento $attempt para {$suscriptor['email']} en {$delay}s");
                    sleep($delay);
                }
                
            } catch (Exception $e) {
                $this->log("⚠️ Intento $attempt falló: " . $e->getMessage());
                if ($attempt < $maxRetries) sleep($attempt * 5);
            }
        }
        
        return false;
    }
    
    /**
     * Pausa adaptativa para Cuba
     */
    private function getCubaPauseTime()
    {
        $hour = date('H');
        
        // Menos pausa en horas de baja demanda
        if ($hour >= 22 || $hour <= 6) {
            return 1; // 1 segundo - noche/madrugada
        } elseif ($hour >= 14 && $hour <= 17) {
            return 3; // 3 segundos - tarde
        } else {
            return 2; // 2 segundos - mañana/tarde-noche
        }
    }
    
    /**
     * Obtener todos los suscriptores
     */
    private function getAllSubscribers()
    {
        $sql = "SELECT id, email, nombre FROM suscripcion_ofertas 
                WHERE estado = 'activa' AND confirmada = 1";
        
        try {
            $query = $this->ofertas->acceso->prepare($sql);
            $query->execute();
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $this->log("❌ Error obteniendo suscriptores: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtener suscriptores por frecuencia
     */
    private function getConfirmedSubscribers($frecuencia)
    {
        $sql = "SELECT id, email, nombre FROM suscripcion_ofertas 
                WHERE frecuencia = :frecuencia 
                AND estado = 'activa' 
                AND confirmada = 1
                ORDER BY fecha_confirmacion DESC";
        
        try {
            $query = $this->ofertas->acceso->prepare($sql);
            $query->execute([':frecuencia' => $frecuencia]);
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $this->log("❌ Error suscriptores $frecuencia: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtener ofertas
     */
    private function getOffersByFrequency($frecuencia)
    {
         $ofertasPrueba = [
        [
            'producto' => 'iPhone 15 Pro',
            'precio_descuento' => '1099',
            'descuento' => '15',
            'envio' => 1
        ],
        [
            'producto' => 'Samsung Galaxy S24',
            'precio_descuento' => '849',
            'descuento' => '15',
            'envio' => 0
        ],
        [
            'producto' => 'Laptop Dell XPS 13',
            'precio_descuento' => '1299',
            'descuento' => '13',
            'envio' => 1
        ]
    ];
    
    switch($frecuencia) {
        case 'diaria':
            return array_slice($ofertasPrueba, 0, 8);
        case 'semanal':
            return array_slice($ofertasPrueba, 0, 20);
        case 'mensual':
            return array_slice($ofertasPrueba, 0, 40);
        default:
            return $ofertasPrueba;
    }
}
    
    /**
     * Preparar datos de ofertas
     */
    private function prepareOffersData($ofertas, $frecuencia)
    {
        // Similar al método anterior pero puedes adaptar para Cuba
        $featured = array_slice($ofertas, 0, min(4, count($ofertas)));
        
        return [
            'featured' => $featured,
            'categories' => [],
            'flash' => [],
            'stats' => [
                'total_offers' => count($ofertas),
                'average_discount' => 35,
                'free_shipping' => count(array_filter($ofertas, function($o) {
                    return isset($o['envio']) && $o['envio'] == 1;
                }))
            ]
        ];
    }
    
    /**
     * Registrar en log con timestamp cubano
     */
    private function log($message)
    {
        $timestamp = date('d/m/Y H:i:s'); // Formato cubano
        $logMessage = "[{$timestamp} 🇨🇺] {$message}\n";
        
        file_put_contents($this->logFile, $logMessage, FILE_APPEND);
        
        if (php_sapi_name() === 'cli') {
            echo $logMessage;
        }
    }
}

// ==============================================
// EJECUTAR SCHEDULER PARA CUBA
// ==============================================

// Verificar acceso
$allowed = false;
$token = 'CUBA_NEXUSBUY_2024'; // Cambia esto por un token seguro

if (php_sapi_name() === 'cli') {
    $allowed = true;
} elseif (isset($_GET['token']) && $_GET['token'] === $token) {
    $allowed = true;
} elseif (isset($_SERVER['HTTP_X_CRON_TOKEN']) && $_SERVER['HTTP_X_CRON_TOKEN'] === $token) {
    $allowed = true;
}

if (!$allowed) {
    if (php_sapi_name() !== 'cli') {
        header('HTTP/1.1 403 Forbidden');
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Acceso no autorizado']);
    }
    exit;
}

// Ejecutar
try {
    $scheduler = new CubaNewsletterScheduler();
    $results = $scheduler->run();
    
    if (php_sapi_name() === 'cli') {
        echo "\n📊 RESUMEN EJECUCIÓN:\n";
        print_r($results);
    } else {
        header('Content-Type: application/json');
        echo json_encode($results, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
    
} catch (Exception $e) {
    $error = "❌ ERROR CRÍTICO: " . $e->getMessage();
    
    if (php_sapi_name() === 'cli') {
        echo $error . "\n";
    } else {
        header('Content-Type: application/json');
        echo json_encode(['error' => $error], JSON_PRETTY_PRINT);
    }
}
?>