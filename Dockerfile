FROM php:8.3-apache

# Install packages needed for common PHP extensions and utilities
RUN apt-get update \
  && apt-get install -y --no-install-recommends \
    libzip-dev \
    libxml2-dev \
    unzip \
    git \
  && docker-php-ext-install xml \
  && rm -rf /var/lib/apt/lists/*

# Copy app
WORKDIR /var/www/html
COPY . /var/www/html

# Ensure content and uploads exist and are writable by www-data
RUN mkdir -p /var/www/html/content /var/www/html/uploads \
  && chown -R www-data:www-data /var/www/html/content /var/www/html/uploads || true

# Add entrypoint to adapt Apache to $PORT (Render requires listening on $PORT)
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

EXPOSE 80
CMD ["/usr/local/bin/docker-entrypoint.sh"]
