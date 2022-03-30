##
# netstat -uplnt
# nerdctl build -t i-educar .  
# nerdctl tag i-educar:latest i-educar:alpha 
##
FROM php:7.4-apache
LABEL maintainer="Softagon Sistemas <fale@softagon.com.br>"

ENV APACHE_DOCUMENT_ROOT /var/www/html/public
ENV WORKDIR=/var/www/html


WORKDIR ${WORKDIR}



# Install linux dependencies
RUN apt-get update && apt-get install -y \
    build-essential \
    software-properties-common \
    bash-completion \
    libicu-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libonig-dev \
    locales \
    unzip \
    jpegoptim optipng pngquant gifsicle \
    git \
    curl \
    wget \
    gnupg2 \
    nano \
    vim \
    npm \
    sudo \
    openssl \
    net-tools \ 
    nginx \
    net-tools \
    rsync \
    netcat \
    libzip-dev \
    cron \
    libpq-dev \
    libpng-dev 

# PHP installation dependencies
ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/

RUN chmod +x /usr/local/bin/install-php-extensions && \
    install-php-extensions @composer pgsql pdo_pgsql gd pcntl zip redis xdebug imap

# Configure Postgre PDO
RUN docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql

# PHP upload Configuration ini
RUN touch /usr/local/etc/php/conf.d/uploads.ini \
    && echo "upload_max_filesize = 10M;" >> /usr/local/etc/php/conf.d/uploads.ini \
    && echo "file_uploads = On;" >> /usr/local/etc/php/conf.d/uploads.ini \
    && echo "post_max_size = 10M;" >> /usr/local/etc/php/conf.d/uploads.ini \
    && echo "max_execution_time = 600;" >> /usr/local/etc/php/conf.d/uploads.ini \
    && echo "max_input_time = 600;" >> /usr/local/etc/php/conf.d/uploads.ini \
    && echo "max_input_vars = 1200;" >> /usr/local/etc/php/conf.d/uploads.ini \
    && echo "memory_limit = 256M;" >> /usr/local/etc/php/conf.d/uploads.ini 

# Copy laravel-cron file to the cron.d directory
COPY configuration/cron/laravel-cron /etc/cron.d/laravel-cron

# Give execution rights on the cron job
RUN chmod 0644 /etc/cron.d/laravel-cron

COPY . ${WORKDIR}

RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Permission to new install
RUN chown root.root ${WORKDIR}/entrypoint.sh
RUN chmod +x ${WORKDIR}/entrypoint.sh

# Authorize these folders to be edited
RUN chown www-data:www-data ${WORKDIR} -R
RUN chmod -R 777 ${WORKDIR}/storage
RUN chmod -R 777 ${WORKDIR}/bootstrap/cache

# Composer installation.
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN cd ${APACHE_DOCUMENT_ROOT} \
    composer update \
    php artisan cache:clear

RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# Allow rewrite
RUN a2enmod rewrite


EXPOSE 80

# ENTRYPOINT [ "/var/www/html/entrypoint.sh" ]