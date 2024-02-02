FROM php:8.2-apache

# Set default logging to stdout so that logs can be shown via docker logs
ENV LOG_FILE php://stdout

# Use the default production configuration
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# Install php extension apcu
RUN pecl install apcu \
    && docker-php-ext-enable apcu

# Copy our application code
COPY --chown=www-data:www-data ./public /var/www/html
COPY --chown=www-data:www-data ./src /var/www/src

# Activate the env file (configuration will be done via env-variables)
RUN mv /var/www/src/FriendlyCaptcha/Lite/Env.template.php /var/www/src/FriendlyCaptcha/Lite/Env.php