# Util/Cron/README.md
# Configuración de CRON Jobs para NexusBuy
# =========================================

# 1. ACTIVAR BANNERS PROGRAMADOS
# Ejecutar cada minuto para activar banners en su fecha de inicio
# * * * * * php /var/www/html/Util/Cron/activar_banners.php >> /var/www/html/Util/Cron/logs/cron.log 2>&1

# 2. DESACTIVAR BANNERS VENCIDOS
# Ejecutar una vez al día a las 12:05 AM
# 5 0 * * * php /var/www/html/Util/Cron/desactivar_banners.php >> /var/www/html/Util/Cron/logs/cron.log 2>&1

# 3. NOTIFICACIONES DE PRÓXIMO VENCIMIENTO
# Ejecutar una vez al día a las 9:00 AM
# 0 9 * * * php /var/www/html/Util/Cron/notificar_proximos_vencer.php >> /var/www/html/Util/Cron/logs/cron.log 2>&1

# 4. LIMPIEZA DE LOGS (OPCIONAL)
# Mantener logs de los últimos 30 días
# 0 0 * * 0 find /var/www/html/Util/Cron/logs/ -name "*.log" -mtime +30 -delete

# =========================================
# PARA PROBAR MANUALMENTE:
# php /var/www/html/Util/Cron/test_banners.php
# =========================================