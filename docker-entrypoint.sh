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

# Replace {SITE_ROOT} placeholder in .htaccess if present
if [ -f /var/www/html/.htaccess ]; then
  sed -i "s#{SITE_ROOT}#/var/www/html#g" /var/www/html/.htaccess || true
fi

# If ADMIN_USER and ADMIN_PASS are provided, create .htpasswd (overwrites)
if [ -n "${ADMIN_USER}" ] && [ -n "${ADMIN_PASS}" ]; then
  echo "Creating .htpasswd for user ${ADMIN_USER}"
  printf "%s" "${ADMIN_PASS}" | htpasswd -i -c /var/www/html/.htpasswd "${ADMIN_USER}"
  chown www-data:www-data /var/www/html/.htpasswd || true
fi

exec apache2-foreground
