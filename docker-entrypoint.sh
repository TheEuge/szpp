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

# If ADMIN_USER is provided, try to create .htpasswd.
# Prefer a precomputed ADMIN_PASS_HASH (htpasswd-style hash). If that's not
# provided, fall back to creating the file from ADMIN_PASS (plaintext).
if [ -n "${ADMIN_USER}" ]; then
  if [ -n "${ADMIN_PASS_HASH}" ]; then
    echo "Writing .htpasswd for user ${ADMIN_USER} from ADMIN_PASS_HASH"
    # ADMIN_PASS_HASH should be a full htpasswd-compatible hash (e.g. from htpasswd -nbB)
    printf "%s:%s\n" "${ADMIN_USER}" "${ADMIN_PASS_HASH}" > /var/www/html/.htpasswd
  elif [ -n "${ADMIN_PASS}" ]; then
    echo "Creating .htpasswd for user ${ADMIN_USER} from ADMIN_PASS"
    # Use htpasswd tool to generate the file from plaintext password
    printf "%s" "${ADMIN_PASS}" | htpasswd -i -c /var/www/html/.htpasswd "${ADMIN_USER}"
  else
    echo "ADMIN_USER is set but neither ADMIN_PASS nor ADMIN_PASS_HASH provided; skipping .htpasswd creation"
  fi
  chown www-data:www-data /var/www/html/.htpasswd || true
fi

# If .htpasswd still doesn't exist for any reason, create an empty placeholder
# so Apache's authn_file module doesn't log AH01620 (missing password file).
if [ ! -f /var/www/html/.htpasswd ]; then
  echo "Creating placeholder .htpasswd to avoid Apache AH01620 errors"
  touch /var/www/html/.htpasswd || true
  chown www-data:www-data /var/www/html/.htpasswd || true
  chmod 0640 /var/www/html/.htpasswd || true
fi

# If .htaccess still contains Basic Auth directives (leftover from earlier image
# or manual edits), move it out of the way and replace with a neutral file so
# Apache will not prompt visitors with a Basic Auth modal.
if [ -f /var/www/html/.htaccess ]; then
  if grep -Ei "AuthType|Require valid-user|AuthUserFile" /var/www/html/.htaccess >/dev/null 2>&1; then
    echo "Found Basic Auth directives in /var/www/html/.htaccess; disabling file"
    mv /var/www/html/.htaccess /var/www/html/.htaccess.server.disabled || true
    cat > /var/www/html/.htaccess <<'HT'
# .htaccess disabled by docker-entrypoint
# The original file was moved to .htaccess.server.disabled
HT
    chown www-data:www-data /var/www/html/.htaccess || true
  fi
fi

exec apache2-foreground
