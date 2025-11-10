#!/bin/bash
set -e

# If PORT is set (Render), configure apache to listen on it
if [ -n "${PORT}" ]; then
  echo "Configuring Apache to listen on port ${PORT}"
  sed -i "s/Listen 80/Listen ${PORT}/g" /etc/apache2/ports.conf || true
  # Update virtual host
  sed -i "s/<VirtualHost \*:80>/<VirtualHost \*:${PORT}>/g" /etc/apache2/sites-enabled/000-default.conf || true
fi

# Ensure permissions
chown -R www-data:www-data /var/www/html/content /var/www/html/uploads || true

exec apache2-foreground
