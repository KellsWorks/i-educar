FROM php:7.4-apache

RUN apt-get update

# Install Postgre PDO
RUN apt-get install -y zip libzip-dev cron libpq-dev libpng-dev \
    && docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql \
    && docker-php-ext-install pdo pdo_pgsql gd pcntl pgsql zip

# Composer installation.
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy laravel-cron file to the cron.d directory
COPY configuration/cron/laravel-cron /etc/cron.d/laravel-cron

# Give execution rights on the cron job
RUN chmod 0644 /etc/cron.d/laravel-cron

COPY . /var/www/html/

ENV APACHE_DOCUMENT_ROOT /var/www/html/public

RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Authorize these folders to be edited
RUN chown www-data:www-data /var/www/html -R
RUN chmod -R 777 /var/www/html/storage
RUN chmod -R 777 /var/www/html/bootstrap/cache

# https://getcomposer.org/doc/03-cli.md#composer-allow-superuser
ENV COMPOSER_ALLOW_SUPERUSER=1
RUN composer install
ENV PATH="${PATH}:/root/.composer/vendor/bin"

RUN php artisan cache:clear

# Allow rewrite
RUN a2enmod rewrite