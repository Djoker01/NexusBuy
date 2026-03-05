<?php
// Util/Cron/test_scheduler.php
// Para probar manualmente el sistema

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🧪 Test del Scheduler de Newsletters</h1>";

// Simular diferentes días/horas
$tests = [
    'diaria' => 'Todos los días',
    'semanal' => 'Solo lunes',
    'mensual' => 'Solo día 1'
];

foreach ($tests as $freq => $desc) {
    echo "<h3>Probando: $desc ($freq)</h3>";
    
    // Simular fecha
    switch($freq) {
        case 'semanal':
            $dayName = 'Monday';
            break;
        case 'mensual':
            $dayName = 'First day of month';
            break;
        default:
            $dayName = 'Today';
    }
    
    echo "<p>Se ejecutaría: $dayName</p>";
    
    // Probar obtener suscriptores
    try {
        require_once __DIR__ . '/../Config/config.php';
        require_once __DIR__ . '/../../Models/Ofertas.php';
        
        $ofertas = new Ofertas();
        
        // Método para obtener suscriptores (debes crearlo en Ofertas.php)
        $sql = "SELECT COUNT(*) as total FROM suscripcion_ofertas 
                WHERE frecuencia = :freq AND confirmada = 1";
        
        $query = $ofertas->acceso->prepare($sql);
        $query->execute([':freq' => $freq]);
        $count = $query->fetch(PDO::FETCH_ASSOC)['total'];
        
        echo "<p>Suscriptores $freq: $count</p>";
        
        // Obtener ofertas
        switch($freq) {
    case 'diaria':
        $limit = 5;
        break;
    case 'semanal':
        $limit = 15;
        break;
    case 'mensual':
        $limit = 30;
        break;
    default:
        $limit = 10;
        break;
}
        
        $ofertasData = $ofertas->obtener_ofertas_recientes($limit);
        echo "<p>Ofertas disponibles: " . count($ofertasData) . "</p>";
        
        if (!empty($ofertasData)) {
            echo "<ul>";
            foreach (array_slice($ofertasData, 0, 3) as $oferta) {
                echo "<li>{$oferta['producto']} - \${$oferta['precio_descuento']}</li>";
            }
            echo "</ul>";
        }
        
    } catch (Exception $e) {
        echo "<p style='color:red;'>Error: " . $e->getMessage() . "</p>";
    }
    
    echo "<hr>";
}

// Probar envío real (opcional)
echo "<h3>📨 Probar envío real (1 email)</h3>";
echo "<form method='post'>
    Email: <input type='email' name='test_email' value='test@example.com'><br>
    Frecuencia: 
    <select name='test_freq'>
        <option value='diaria'>Diaria</option>
        <option value='semanal'>Semanal</option>
        <option value='mensual'>Mensual</option>
    </select><br>
    <button type='submit' name='test_send'>Probar Envío</button>
</form>";

if (isset($_POST['test_send'])) {
    try {
        require_once __DIR__ . '/../Mail/Mailer.php';
        require_once __DIR__ . '/../../Models/Ofertas.php';
        
        $mailer = new \Util\Mail\Mailer();
        $ofertas = new Ofertas();
        
        if (!$mailer->isConfigured()) {
            echo "<p style='color:red;'>❌ Mailer no configurado</p>";
        } else {
            // Obtener ofertas de prueba
            $testOffers = $ofertas->obtener_ofertas_recientes(3);
            $offersData = [
                'featured' => $testOffers,
                'categories' => [],
                'flash' => [],
                'stats' => ['total_offers' => count($testOffers), 'average_discount' => 30, 'free_shipping' => 1]
            ];
            
            $sent = $mailer->sendNewsletter(
                $_POST['test_email'],
                'Usuario Test',
                $offersData,
                $_POST['test_freq']
            );
            
            if ($sent) {
                echo "<p style='color:green;'>✅ Email enviado exitosamente</p>";
            } else {
                echo "<p style='color:red;'>❌ Error: " . $mailer->getError() . "</p>";
            }
        }
        
    } catch (Exception $e) {
        echo "<p style='color:red;'>❌ Error: " . $e->getMessage() . "</p>";
    }
}
?>