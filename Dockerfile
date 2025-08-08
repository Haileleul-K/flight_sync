FROM php:8.3-apache

RUN a2enmod rewrite
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

RUN apt-get update && apt-get install -y \
    git curl zip unzip libpng-dev libonig-dev libxml2-dev libzip-dev libpq-dev \
    && docker-php-ext-install pdo_pgsql mbstring zip exif pcntl bcmath gd

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY --chown=www-data:www-data . .

RUN composer install --no-dev --optimize-autoloader

RUN chown -R www-data:www-data /var/www/html /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache \
    && mkdir -p /var/www/html/storage/framework/views \
    && chown -R www-data:www-data /var/www/html/storage/framework/views \
    && chmod -R 775 /var/www/html/storage/framework/views \
    && [ -f /var/www/html/ca.pem ] && chown www-data:www-data /var/www/html/ca.pem && chmod 644 /var/www/html/ca.pem || true

RUN echo "<VirtualHost *:80>\n\
    ServerAdmin webmaster@localhost\n\
    DocumentRoot /var/www/html/public\n\
    <Directory /var/www/html/public>\n\
        Options -Indexes +FollowSymLinks\n\
        AllowOverride All\n\
        Require all granted\n\
    </Directory>\n\
    ErrorLog \${APACHE_LOG_DIR}/error.log\n\
    CustomLog \${APACHE_LOG_DIR}/access.log combined\n\
</VirtualHost>" > /etc/apache2/sites-available/000-default.conf

# Enable PHP error logging
RUN echo "log_errors = On" >> /usr/local/etc/php/php.ini \
    && echo "error_log = /var/log/apache2/php_errors.log" >> /usr/local/etc/php/php.ini \
    && echo "display_errors = On" >> /usr/local/etc/php/php.ini

# Add wait-for-it script to wait for database
RUN curl -o /wait-for-it.sh https://raw.githubusercontent.com/vishnubob/wait-for-it/master/wait-for-it.sh \
    && chmod +x /wait-for-it.sh

USER www-data

# Clear caches during build to avoid stale configuration
RUN php artisan config:clear \
    && php artisan cache:clear \
    && php artisan view:clear

EXPOSE 80

CMD /wait-for-it.sh $DB_HOST:5432 --timeout=60 -- \
    php artisan config:clear && \
    php artisan config:cache && \
    php artisan view:cache && \
    php artisan migrate --force --verbose && \
    apache2-foreground