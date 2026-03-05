<?php
namespace Util\Mail;

// Cargar PHPMailer - VERSIÓN CORREGIDA PARA PHP 7.4
class Mailer
{
    private $mailer;
    private $config;
    private $templatePath;
    private $baseTemplate;
    
    public function __construct()
    {
        // Inicializar configuración primero
        $this->loadConfig();
        $this->templatePath = realpath(__DIR__ . '/templates/');
        
        // Si no existe el directorio de templates, crearlo
        if (!$this->templatePath || !is_dir($this->templatePath)) {
            $this->templatePath = __DIR__ . '/templates/';
            if (!is_dir($this->templatePath)) {
                @mkdir($this->templatePath, 0755, true);
            }
        }
        
        // Cargar plantilla base una sola vez
        $this->baseTemplate = $this->loadBaseTemplate();
        
        $this->initialize();
    }
    
    /**
     * Cargar configuración desde email_config.php
     */
    private function loadConfig()
    {
        // Ruta absoluta a la configuración
        $configPath = realpath(__DIR__ . '/../Config/email_config.php');
        
        if (!$configPath || !file_exists($configPath)) {
            // Intentar ruta relativa
            $configPath = __DIR__ . '/../Config/email_config.php';
            if (!file_exists($configPath)) {
                // Configuración por defecto
                $this->config = $this->getDefaultConfig();
                error_log("⚠️ Usando configuración por defecto - Archivo no encontrado: " . $configPath);
                return;
            }
        }
        
        // Incluir el archivo de configuración
        require_once $configPath;
        
        // Cargar configuración con valores por defecto
        $this->config = [
            'enabled' => defined('EMAIL_ENABLED') ? EMAIL_ENABLED : false,
            'host' => defined('EMAIL_HOST') ? EMAIL_HOST : '',
            'port' => defined('EMAIL_PORT') ? EMAIL_PORT : 587,
            'username' => defined('EMAIL_USERNAME') ? EMAIL_USERNAME : '',
            'password' => defined('EMAIL_PASSWORD') ? EMAIL_PASSWORD : '',
            'encryption' => defined('EMAIL_ENCRYPTION') ? EMAIL_ENCRYPTION : 'tls',
            'from_address' => defined('EMAIL_FROM_ADDRESS') ? EMAIL_FROM_ADDRESS : '',
            'from_name' => defined('EMAIL_FROM_NAME') ? EMAIL_FROM_NAME : '',
            'reply_to' => defined('EMAIL_REPLY_TO') ? EMAIL_REPLY_TO : '',
            'charset' => defined('EMAIL_CHARSET') ? EMAIL_CHARSET : 'UTF-8',
            'debug' => defined('EMAIL_DEBUG') ? EMAIL_DEBUG : 0,
            'timeout' => defined('EMAIL_TIMEOUT') ? EMAIL_TIMEOUT : 30,
            'site_url' => defined('SITE_URL') ? SITE_URL : 'http://localhost/nexusbuy',
            'site_name' => defined('SITE_NAME') ? SITE_NAME : 'NexusBuy'
        ];
        
        // Validar configuración mínima
        if (empty($this->config['username']) || empty($this->config['password'])) {
            $this->config['enabled'] = false;
            error_log("⚠️ Mailer deshabilitado - Credenciales SMTP no configuradas");
        }
    }
    
    /**
     * Configuración por defecto
     */
    private function getDefaultConfig()
    {
        return [
            'enabled' => false,
            'host' => 'smtp.gmail.com',
            'port' => 587,
            'username' => '',
            'password' => '',
            'encryption' => 'tls',
            'from_address' => 'no-reply@nexusbuy.com',
            'from_name' => 'NexusBuy',
            'reply_to' => 'soporte@nexusbuy.com',
            'charset' => 'UTF-8',
            'debug' => 0,
            'timeout' => 30,
            'site_url' => 'http://localhost/nexusbuy',
            'site_name' => 'NexusBuy'
        ];
    }
    
    /**
     * Inicializar PHPMailer
     */
    private function initialize()
    {
        if (!$this->config['enabled']) {
            error_log("⚠️ Sistema de email deshabilitado en configuración");
            $this->mailer = null;
            return;
        }
        
        try {
            // Cargar PHPMailer de manera segura
            $this->loadPHPMailer();
            
            if (!$this->mailer) {
                error_log("❌ No se pudo cargar PHPMailer");
                return;
            }
            
            // Configuración del servidor SMTP
            $this->mailer->isSMTP();
            $this->mailer->Host = $this->config['host'];
            $this->mailer->Port = $this->config['port'];
            $this->mailer->SMTPAuth = true;
            $this->mailer->Username = $this->config['username'];
            $this->mailer->Password = $this->config['password'];
            
            // Encriptación
            if (!empty($this->config['encryption'])) {
                $this->mailer->SMTPSecure = $this->config['encryption'];
            }
            
            // Configuración adicional
            $this->mailer->CharSet = $this->config['charset'];
            $this->mailer->Timeout = $this->config['timeout'];
            $this->mailer->SMTPKeepAlive = false;
            $this->mailer->SMTPAutoTLS = true;
            
            // Configuración de debug
            if ($this->config['debug'] > 0) {
                $this->mailer->SMTPDebug = $this->config['debug'];
                $this->mailer->Debugoutput = function($str, $level) {
                    error_log("PHPMailer [$level]: $str");
                };
            }
            
            // Configuración del remitente
            $this->mailer->setFrom(
                $this->config['from_address'],
                $this->config['from_name']
            );
            
            if (!empty($this->config['reply_to'])) {
                $this->mailer->addReplyTo($this->config['reply_to'], $this->config['site_name']);
            }
            
            error_log("✅ PHPMailer inicializado correctamente para: " . $this->config['username']);
            
        } catch (\Exception $e) {
            error_log("❌ ERROR inicializando PHPMailer: " . $e->getMessage());
            $this->mailer = null;
        }
    }
    
    /**
     * Cargar PHPMailer de manera segura
     */
    private function loadPHPMailer()
    {
        // Intentar cargar desde Composer primero
        $autoloadPaths = [
            realpath(__DIR__ . '/../../../vendor/autoload.php'),
            realpath(__DIR__ . '/../../vendor/autoload.php'),
            realpath(__DIR__ . '/vendor/autoload.php'),
            'vendor/autoload.php'
        ];
        
        foreach ($autoloadPaths as $path) {
            if ($path && file_exists($path)) {
                require_once $path;
                error_log("✅ Autoload de Composer cargado: " . basename(dirname($path)));
                break;
            }
        }
        
        // Verificar si PHPMailer está disponible
        if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
            // Usar namespace nuevo (PHPMailer 6.0+)
            $this->mailer = new \PHPMailer\PHPMailer\PHPMailer(true);
            error_log("✅ PHPMailer cargado (nuevo namespace)");
        } elseif (class_exists('PHPMailer')) {
            // Usar namespace antiguo
            $this->mailer = new \PHPMailer(true);
            error_log("✅ PHPMailer cargado (namespace antiguo)");
        } else {
            error_log("❌ PHPMailer no encontrado. Ejecuta: composer require phpmailer/phpmailer");
            return false;
        }
        
        return true;
    }
    
    /**
     * Cargar plantilla base
     */
    private function loadBaseTemplate()
    {
        $baseFile = $this->templatePath . '/base.html';
        
        if (file_exists($baseFile)) {
            return file_get_contents($baseFile);
        }
        
        // Crear plantilla base mínima si no existe
        $minimalBase = '<!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>{{subject}}</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; }
                .container { max-width: 600px; margin: 0 auto; }
                .header { background: #4361ee; color: white; padding: 20px; }
                .content { padding: 20px; }
                .footer { background: #f8f9fa; padding: 20px; text-align: center; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>{{site_name}}</h1>
                </div>
                <div class="content">
                    {{content}}
                </div>
                <div class="footer">
                    © {{current_year}} {{site_name}}
                </div>
            </div>
        </body>
        </html>';
        
        // Guardar plantilla base mínima
        file_put_contents($baseFile, $minimalBase);
        
        return $minimalBase;
    }
    
    /**
     * Renderizar plantilla con soporte para herencia simple
     */
    private function renderTemplate($templateName, $data = [])
    {
        $templateFile = $this->templatePath . '/' . $templateName;
        
        if (!file_exists($templateFile)) {
            error_log("⚠️ Plantilla no encontrada: $templateFile");
            return $this->getFallbackTemplate($templateName, $data);
        }
        
        $templateContent = file_get_contents($templateFile);
        
        // Verificar si extiende base.html
        $isExtended = false;
        $subject = isset($data['subject']) ? $data['subject'] : 'Email de ' . $this->config['site_name'];
        
        if (preg_match('/{%\s*extends\s*"([^"]+)"\s*%}/', $templateContent, $matches)) {
            $isExtended = true;
            
            // Extraer subject del bloque subject si existe
            if (preg_match('/{%\s*block\s*subject\s*%}(.*?){%\s*endblock\s*%}/s', $templateContent, $subjectMatches)) {
                $subject = trim($subjectMatches[1]);
            }
            
            // Extraer contenido del bloque content
            if (preg_match('/{%\s*block\s*content\s*%}(.*?){%\s*endblock\s*%}/s', $templateContent, $contentMatches)) {
                $blockContent = $contentMatches[1];
                
                // Usar plantilla base
                $finalContent = $this->baseTemplate;
                
                // Reemplazar {{content}} en base.html
                $finalContent = str_replace('{{content}}', $blockContent, $finalContent);
                
                // Procesar variables
                return $this->processTemplateVariables($finalContent, array_merge($data, ['subject' => $subject]));
            }
        }
        
        // Si no extiende base.html o no tiene bloques, usar contenido directo
        if ($isExtended && strpos($templateContent, '{% block content %}') === false) {
            // La plantilla extiende base pero no tiene bloque content
            // Usar todo el contenido después de extends
            $content = preg_replace('/{%\s*extends\s*"[^"]+"\s*%}\s*/', '', $templateContent);
            $finalContent = str_replace('{{content}}', $content, $this->baseTemplate);
            return $this->processTemplateVariables($finalContent, array_merge($data, ['subject' => $subject]));
        }
        
        // Plantilla sin herencia
        return $this->processTemplateVariables($templateContent, $data);
    }
    
    /**
     * Procesar variables en plantilla
     */
    private function processTemplateVariables($content, $data)
    {
        // Datos por defecto para todas las plantillas
        $defaultData = [
            'site_name' => $this->config['site_name'],
            'site_url' => $this->config['site_url'],
            'current_year' => date('Y'),
            'facebook_url' => 'https://facebook.com/nexusbuy',
            'instagram_url' => 'https://instagram.com/nexusbuy',
            'twitter_url' => 'https://twitter.com/nexusbuy',
            'youtube_url' => 'https://youtube.com/nexusbuy',
            'privacy_url' => $this->config['site_url'] . '/politica-privacidad',
            'terms_url' => $this->config['site_url'] . '/terminos',
            'unsubscribe_url' => $this->config['site_url'] . '/cancelar-suscripcion',
            'contact_url' => $this->config['site_url'] . '/contacto',
            'offers_url' => $this->config['site_url'] . '/ofertas',
            'profile_url' => $this->config['site_url'] . '/mi-cuenta',
            'all_offers_url' => $this->config['site_url'] . '/ofertas',
            'preferences_url' => $this->config['site_url'] . '/preferencias',
            'subject' => 'Email de ' . $this->config['site_name']
        ];
        
        // Combinar datos
        $allData = array_merge($defaultData, $data);
        
        // Reemplazar variables en la plantilla
        foreach ($allData as $key => $value) {
            if (is_string($value)) {
                // Reemplazar {{variable}}
                $content = str_replace('{{' . $key . '}}', $value, $content);
                // Reemplazar {{ variable }} (con espacios)
                $content = str_replace('{{ ' . $key . ' }}', $value, $content);
            }
        }
        
        // Limpiar variables no reemplazadas
        $content = preg_replace('/{{.*?}}/', '', $content);
        
        return $content;
    }
    
    /**
     * Plantilla de respaldo
     */
    private function getFallbackTemplate($templateName, $data)
    {
        $userName = $data['user_name'] ?? 'Usuario';
        $siteName = $this->config['site_name'];
        $siteUrl = $this->config['site_url'];
        
        $template = '<!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Email de ' . htmlspecialchars($siteName) . '</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #4361ee; color: white; padding: 20px; text-align: center; }
                .content { background: #f8f9fa; padding: 30px; }
                .footer { margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd; }
                .button { display: inline-block; background: #4361ee; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>' . htmlspecialchars($siteName) . '</h1>
                </div>
                <div class="content">
                    <h2>Hola ' . htmlspecialchars($userName) . ',</h2>';
        
        switch($templateName) {
            case 'reset_password.html':
                $resetUrl = $data['reset_url'] ?? '';
                $template .= '<p>Recibimos una solicitud para restablecer tu contraseña.</p>
                    <p><a href="' . htmlspecialchars($resetUrl) . '" class="button">Restablecer Contraseña</a></p>
                    <p>O copia este enlace:<br>' . htmlspecialchars($resetUrl) . '</p>';
                break;
                
            case 'confirmation.html':
                $confirmUrl = $data['confirmation_url'] ?? '';
                $template .= '<p>¡Gracias por suscribirte a ' . htmlspecialchars($siteName) . '!</p>
                    <p><a href="' . htmlspecialchars($confirmUrl) . '" class="button">Confirmar Email</a></p>';
                break;
                
            case 'welcome.html':
                $template .= '<p>¡Bienvenido a ' . htmlspecialchars($siteName) . '!</p>
                    <p>Tu suscripción ha sido confirmada exitosamente.</p>';
                break;
                
            case 'newsletter.html':
                $frequency = $data['frequency'] ?? 'semanal';
                $template .= '<p>Aquí tienes tus ofertas ' . htmlspecialchars($frequency) . '.</p>
                    <p><a href="' . htmlspecialchars($siteUrl) . '" class="button">Ver Ofertas</a></p>';
                break;
                
            default:
                $template .= '<p>Has recibido este mensaje de ' . htmlspecialchars($siteName) . '.</p>';
        }
        
        $template .= '</div>
                <div class="footer">
                    <p>&copy; ' . date('Y') . ' ' . htmlspecialchars($siteName) . '</p>
                </div>
            </div>
        </body>
        </html>';
        
        return $template;
    }
    
    /**
     * ENVIAR EMAIL DE RECUPERACIÓN DE CONTRASEÑA
     */
    public function sendPasswordResetEmail($toEmail, $toName, $token)
    {
        if (!$this->mailer) {
            error_log("❌ No se puede enviar email: PHPMailer no inicializado");
            return false;
        }
        
        try {
            // Limpiar destinatarios anteriores
            $this->mailer->clearAllRecipients();
            $this->mailer->clearAttachments();
            
            // Agregar destinatario
            $this->mailer->addAddress($toEmail, $toName ?: '');
            
            // URL de recuperación
            $resetUrl = $this->config['site_url'] . '/Views/login.php?token=' . urlencode($token);
            
            // Datos para la plantilla
            $templateData = [
                'user_name' => $toName ?: 'Usuario',
                'reset_url' => $resetUrl,
                'subject' => 'Restablecer tu contraseña - ' . $this->config['site_name']
            ];
            
            // Renderizar plantilla HTML
            $htmlBody = $this->renderTemplate('reset_password.html', $templateData);
            
            // Versión texto plano
            $textBody = "RESTABLECER CONTRASEÑA\n";
            $textBody .= "======================\n\n";
            $textBody .= "Hola " . ($toName ?: 'Usuario') . ",\n\n";
            $textBody .= "Recibimos una solicitud para restablecer tu contraseña en " . $this->config['site_name'] . ".\n\n";
            $textBody .= "Para crear una nueva contraseña, visita:\n";
            $textBody .= $resetUrl . "\n\n";
            $textBody .= "Este enlace es válido por 1 hora.\n\n";
            $textBody .= "¿No solicitaste este cambio? Ignora este email.\n\n";
            $textBody .= "Saludos,\n";
            $textBody .= "El equipo de " . $this->config['site_name'] . "\n";
            $textBody .= $this->config['site_url'];
            
            // Configurar email
            $this->mailer->Subject = $templateData['subject'];
            $this->mailer->isHTML(true);
            $this->mailer->Body = $htmlBody;
            $this->mailer->AltBody = $textBody;
            
            // Configurar prioridad
            $this->mailer->Priority = 1;
            
            // Enviar email
            $sent = $this->mailer->send();
            
            if ($sent) {
                error_log("✅ Email de recuperación enviado a: $toEmail");
                return true;
            } else {
                error_log("❌ Error enviando email a $toEmail: " . $this->mailer->ErrorInfo);
                return false;
            }
            
        } catch (\Exception $e) {
            error_log("❌ Excepción enviando email de recuperación a $toEmail: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * ENVIAR EMAIL DE CONFIRMACIÓN DE SUSCRIPCIÓN
     */
    public function sendConfirmationEmail($toEmail, $toName, $token)
    {
        if (!$this->mailer) {
            return false;
        }
        
        try {
            $this->mailer->clearAllRecipients();
            $this->mailer->clearAttachments();
            
            $this->mailer->addAddress($toEmail, $toName ?: '');
            
            // URL de confirmación
            $confirmationUrl = $this->config['site_url'] . '/Views/confirmar_suscripcion.php?token=' . urlencode($token);
            
            // Datos para la plantilla
            $templateData = [
                'user_name' => $toName ?: 'Usuario',
                'confirmation_url' => $confirmationUrl,
                'subject' => 'Confirma tu suscripción a ' . $this->config['site_name']
            ];
            
            // Renderizar plantilla
            $htmlBody = $this->renderTemplate('confirmation.html', $templateData);
            
            // Versión texto plano
            $textBody = "CONFIRMA TU SUSCRIPCIÓN\n";
            $textBody .= "========================\n\n";
            $textBody .= "Hola " . ($toName ?: 'Usuario') . ",\n\n";
            $textBody .= "¡Gracias por suscribirte a las ofertas de " . $this->config['site_name'] . "!\n\n";
            $textBody .= "Para confirmar tu suscripción, visita:\n";
            $textBody .= $confirmationUrl . "\n\n";
            $textBody .= "Este enlace es válido por 24 horas.\n\n";
            $textBody .= "Saludos,\n";
            $textBody .= "El equipo de " . $this->config['site_name'];
            
            $this->mailer->Subject = $templateData['subject'];
            $this->mailer->isHTML(true);
            $this->mailer->Body = $htmlBody;
            $this->mailer->AltBody = $textBody;
            
            $sent = $this->mailer->send();
            
            if ($sent) {
                error_log("✅ Email de confirmación enviado a: $toEmail");
            } else {
                error_log("❌ Error enviando confirmación a $toEmail: " . $this->mailer->ErrorInfo);
            }
            
            return $sent;
            
        } catch (\Exception $e) {
            error_log("❌ Error enviando email de confirmación: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * ENVIAR EMAIL DE BIENVENIDA
     */
    public function sendWelcomeEmail($toEmail, $toName, $frecuencia = 'semanal')
    {
        if (!$this->mailer) {
            return false;
        }
        
        try {
            $this->mailer->clearAllRecipients();
            $this->mailer->clearAttachments();
            
            $this->mailer->addAddress($toEmail, $toName ?: '');
            
            // Datos para la plantilla - CORREGIDO PARA PHP 7.4
            switch($frecuencia) {
                case 'diaria':
                    $frequencyText = 'diarias';
                    break;
                case 'semanal':
                    $frequencyText = 'semanales';
                    break;
                case 'mensual':
                    $frequencyText = 'mensuales';
                    break;
                default:
                    $frequencyText = 'semanales';
                    break;
            }
            
            $templateData = [
                'user_name' => $toName ?: 'Usuario',
                'frequency_text' => $frequencyText,
                'subject' => '¡Bienvenido a ' . $this->config['site_name'] . '!'
            ];
            
            $htmlBody = $this->renderTemplate('welcome.html', $templateData);
            
            $textBody = "¡BIENVENIDO/A!\n";
            $textBody .= "==============\n\n";
            $textBody .= "Hola " . ($toName ?: 'Usuario') . ",\n\n";
            $textBody .= "¡Tu suscripción ha sido confirmada exitosamente!\n\n";
            $textBody .= "Recibirás ofertas " . $templateData['frequency_text'] . " en tu email.\n\n";
            $textBody .= "Saludos,\n";
            $textBody .= "El equipo de " . $this->config['site_name'];
            
            $this->mailer->Subject = $templateData['subject'];
            $this->mailer->isHTML(true);
            $this->mailer->Body = $htmlBody;
            $this->mailer->AltBody = $textBody;
            
            $sent = $this->mailer->send();
            
            if ($sent) {
                error_log("✅ Email de bienvenida enviado a: $toEmail");
            } else {
                error_log("❌ Error enviando bienvenida a $toEmail: " . $this->mailer->ErrorInfo);
            }
            
            return $sent;
            
        } catch (\Exception $e) {
            error_log("❌ Error enviando email de bienvenida: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * ENVIAR NEWSLETTER DE OFERTAS (NUEVO MÉTODO)
     */
    public function sendNewsletter($toEmail, $toName, $offersData, $frecuencia)
    {
        if (!$this->mailer) {
            error_log("❌ No se puede enviar newsletter: PHPMailer no inicializado");
            return false;
        }
        
        try {
            $this->mailer->clearAllRecipients();
            $this->mailer->clearAttachments();
            
            $this->mailer->addAddress($toEmail, $toName ?: '');
            
            // Asunto según frecuencia - CORREGIDO PARA PHP 7.4
            switch($frecuencia) {
                case 'diaria':
                    $freqText = 'Diarias';
                    break;
                case 'semanal':
                    $freqText = 'Semanales';
                    break;
                case 'mensual':
                    $freqText = 'Mensuales';
                    break;
                default:
                    $freqText = 'Semanales';
                    break;
            }
            
            $subject = "🎯 Ofertas " . $freqText . " - " . $this->config['site_name'];
            
            // Preparar datos para la plantilla
            $templateData = [
                'user_name' => $toName ?: 'Cliente',
                'frequency' => $frecuencia,
                'featured_offers' => $offersData['featured'] ?? [],
                'categories' => $offersData['categories'] ?? [],
                'flash_deals' => $offersData['flash'] ?? [],
                'stats' => $offersData['stats'] ?? [
                    'total_offers' => count($offersData['featured'] ?? []),
                    'average_discount' => 35,
                    'free_shipping' => 0
                ],
                'subject' => $subject
            ];
            
            // Renderizar plantilla de newsletter
            $htmlBody = $this->renderTemplate('newsletter.html', $templateData);
            
            // Si la plantilla no existe o está vacía, usar versión de respaldo
            if (empty($htmlBody) || strpos($htmlBody, '<html') === false) {
                $htmlBody = $this->getNewsletterFallbackTemplate($templateData);
            }
            
            // Versión texto plano
            $textBody = $this->getNewsletterTextVersion($templateData);
            
            $this->mailer->Subject = $subject;
            $this->mailer->isHTML(true);
            $this->mailer->Body = $htmlBody;
            $this->mailer->AltBody = $textBody;
            
            // Configuraciones para envío masivo
            $this->mailer->Priority = 3; // Prioridad normal
            
            $sent = $this->mailer->send();
            
            if ($sent) {
                error_log("✅ Newsletter enviado a: $toEmail");
                return true;
            } else {
                error_log("❌ Error enviando newsletter a $toEmail: " . $this->mailer->ErrorInfo);
                return false;
            }
            
        } catch (\Exception $e) {
            error_log("❌ Excepción enviando newsletter a $toEmail: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Plantilla de respaldo para newsletter
     */
    private function getNewsletterFallbackTemplate($data)
    {
        $userName = htmlspecialchars($data['user_name']);
        $siteName = htmlspecialchars($this->config['site_name']);
        $siteUrl = htmlspecialchars($this->config['site_url']);
        $frequency = htmlspecialchars($data['frequency']);
        
        $html = '<!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Ofertas ' . $frequency . ' - ' . $siteName . '</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #4361ee; color: white; padding: 20px; text-align: center; }
                .offer { background: white; border: 1px solid #ddd; padding: 15px; margin: 10px 0; }
                .price { color: #e63946; font-weight: bold; font-size: 18px; }
                .button { display: inline-block; background: #4361ee; color: white; padding: 10px 20px; text-decoration: none; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>' . $siteName . '</h1>
                    <p>Ofertas ' . $frequency . '</p>
                </div>
                <h2>Hola ' . $userName . ',</h2>
                <p>Tenemos ' . $data['stats']['total_offers'] . ' ofertas especiales para ti:</p>';
        
        // Mostrar ofertas destacadas
        foreach ($data['featured_offers'] as $index => $offer) {
            $producto = htmlspecialchars($offer['producto'] ?? 'Producto ' . ($index + 1));
            $precio = htmlspecialchars($offer['precio_descuento'] ?? '0');
            $descuento = htmlspecialchars($offer['descuento'] ?? '0');
            
            $html .= '<div class="offer">
                <h3>' . $producto . '</h3>
                <p class="price">$' . $precio . '</p>
                <p>Descuento: ' . $descuento . '%</p>
                <a href="' . $siteUrl . '" class="button">Ver oferta</a>
            </div>';
        }
        
        $html .= '<p>Descuento promedio: ' . $data['stats']['average_discount'] . '%</p>
                <p>Envíos gratis: ' . $data['stats']['free_shipping'] . '</p>
                <p><a href="' . $siteUrl . '" style="display: inline-block; background: #4361ee; color: white; padding: 12px 24px; text-decoration: none; margin-top: 20px;">Ver todas las ofertas</a></p>
                <p>Saludos,<br>El equipo de ' . $siteName . '</p>
            </div>
        </body>
        </html>';
        
        return $html;
    }
    
    /**
     * Versión texto plano del newsletter
     */
    private function getNewsletterTextVersion($data)
    {
        // Mapeo de frecuencia a texto - CORREGIDO PARA PHP 7.4
        $frequencyTextMap = [
            'diaria' => 'diarias',
            'semanal' => 'semanales',
            'mensual' => 'mensuales'
        ];
        
        $frequencyText = isset($frequencyTextMap[$data['frequency']]) 
            ? $frequencyTextMap[$data['frequency']] 
            : 'semanales';
        
        $text = "OFERTAS " . strtoupper($data['frequency']) . "\n";
        $text .= str_repeat("=", 50) . "\n\n";
        $text .= "Hola " . ($data['user_name'] ?: 'Cliente') . ",\n\n";
        $text .= "Aquí tienes tus ofertas " . $frequencyText . " de " . $this->config['site_name'] . ":\n\n";
        
        // Listar ofertas destacadas
        if (!empty($data['featured_offers'])) {
            $text .= "⭐ OFERTAS DESTACADAS:\n";
            foreach ($data['featured_offers'] as $index => $offer) {
                $text .= ($index + 1) . ". " . ($offer['producto'] ?? 'Producto') . " - $" . 
                        ($offer['precio_descuento'] ?? '0') . 
                        " (Descuento: " . ($offer['descuento'] ?? '0') . "%)\n";
            }
            $text .= "\n";
        }
        
        // Estadísticas
        $text .= "📊 ESTADÍSTICAS:\n";
        $text .= "• Total ofertas: " . $data['stats']['total_offers'] . "\n";
        $text .= "• Descuento promedio: " . $data['stats']['average_discount'] . "%\n";
        $text .= "• Envíos gratis: " . $data['stats']['free_shipping'] . "\n\n";
        
        $text .= "Visita nuestro sitio para ver todas las ofertas:\n";
        $text .= $this->config['site_url'] . "\n\n";
        
        $text .= "Saludos,\n";
        $text .= "El equipo de " . $this->config['site_name'] . "\n";
        $text .= $this->config['site_url'];
        
        return $text;
    }
    
    /**
     * Verificar si PHPMailer está configurado
     */
    public function isConfigured()
    {
        return $this->config['enabled'] && $this->mailer !== null;
    }
    
    /**
     * Obtener último error
     */
    public function getError()
    {
        return $this->mailer ? $this->mailer->ErrorInfo : 'Mailer no inicializado';
    }
    
    /**
     * Probar conexión SMTP
     */
    public function testConnection()
    {
        if (!$this->mailer) {
            return "❌ Mailer no inicializado";
        }
        
        try {
            if ($this->mailer->smtpConnect()) {
                $this->mailer->smtpClose();
                return "✅ Conexión SMTP exitosa a " . $this->config['host'] . ":" . $this->config['port'];
            } else {
                return "❌ No se pudo conectar al servidor SMTP";
            }
        } catch (\Exception $e) {
            return "❌ Error de conexión: " . $e->getMessage();
        }
    }
    
    /**
     * Enviar email de prueba simple
     */
    public function sendTestEmail($toEmail, $toName = '')
    {
        if (!$this->mailer) {
            return "❌ Mailer no inicializado";
        }
        
        try {
            $this->mailer->clearAllRecipients();
            $this->mailer->addAddress($toEmail, $toName);
            
            $this->mailer->Subject = '✅ Prueba de correo - ' . $this->config['site_name'];
            $this->mailer->Body = '<h1>Prueba exitosa!</h1><p>El sistema de correo de ' . $this->config['site_name'] . ' está funcionando correctamente.</p>';
            $this->mailer->AltBody = 'Prueba exitosa! El sistema de correo está funcionando.';
            
            if ($this->mailer->send()) {
                return "✅ Email de prueba enviado a $toEmail";
            } else {
                return "❌ Error: " . $this->mailer->ErrorInfo;
            }
        } catch (\Exception $e) {
            return "❌ Excepción: " . $e->getMessage();
        }
    }
    
    /**
     * Método para debug
     */
    public function debugConfig()
    {
        return [
            'enabled' => $this->config['enabled'] ? '✅ Sí' : '❌ No',
            'host' => $this->config['host'],
            'port' => $this->config['port'],
            'username' => substr($this->config['username'], 0, 3) . '...' . substr($this->config['username'], -3),
            'encryption' => $this->config['encryption'],
            'from_address' => $this->config['from_address'],
            'from_name' => $this->config['from_name'],
            'site_url' => $this->config['site_url'],
            'site_name' => $this->config['site_name'],
            'mailer_initialized' => $this->mailer ? '✅ Sí' : '❌ No',
            'template_path' => $this->templatePath,
            'templates_exists' => is_dir($this->templatePath) ? '✅ Sí' : '❌ No'
        ];
    }
    
    /**
     * Limpiar todos los destinatarios (para reutilizar instancia)
     */
    public function clearAllRecipients()
    {
        if ($this->mailer) {
            $this->mailer->clearAllRecipients();
            $this->mailer->clearAttachments();
        }
    }
    
    /**
     * Método para usar en el scheduler
     */
    public function sendHolidayEmail($email, $name, $holidayName, $templateHtml)
    {
        if (!$this->mailer) return false;
        
        try {
            $this->mailer->clearAllRecipients();
            $this->mailer->addAddress($email, $name);
            
            $subject = "🎉 ¡Feliz " . $holidayName . "! - " . $this->config['site_name'];
            
            $textBody = "¡Feliz " . $holidayName . "!\n\n";
            $textBody .= "Hoy es feriado en Cuba. Nuestras ofertas regresan mañana.\n";
            $textBody .= "¡Disfruta del día!\n\n" . $this->config['site_name'];
            
            $this->mailer->Subject = $subject;
            $this->mailer->isHTML(true);
            $this->mailer->Body = $templateHtml;
            $this->mailer->AltBody = $textBody;
            
            return $this->mailer->send();
            
        } catch (\Exception $e) {
            error_log("❌ Error email feriado: " . $e->getMessage());
            return false;
        }
    }
}
?>