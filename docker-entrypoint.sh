#!/bin/sh
set -e

# Render provides the $PORT environment variable (e.g. 10000 or custom)
# Default to port 80 if not defined
APP_PORT="${PORT:-80}"

echo "=========================================================="
echo " Starting CaffeBook Backend Server"
echo " Listening on PORT: $APP_PORT"
echo " Environment: ${APP_ENV:-production}"
echo " Database Driver: ${DB_DRIVER:-mysql}"
echo "=========================================================="

# Configure Apache to listen on $APP_PORT
sed -i "s/Listen 80/Listen $APP_PORT/g" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:80>/<VirtualHost \*:$APP_PORT>/g" /etc/apache2/sites-available/000-default.conf
if [ -f /etc/apache2/sites-available/default-ssl.conf ]; then
    sed -i "s/<VirtualHost _default_:443>/<VirtualHost _default_:$APP_PORT>/g" /etc/apache2/sites-available/default-ssl.conf
fi

# Set ServerName to avoid Apache fully-qualified domain warning
if ! grep -q "ServerName" /etc/apache2/apache2.conf; then
    echo "ServerName localhost" >> /etc/apache2/apache2.conf
fi

# Ensure correct permissions for runtime data (e.g. SQLite database or uploaded images)
chown -R www-data:www-data /var/www/html
chmod -R 775 /var/www/html

# Execute Apache foreground
exec apache2-foreground
