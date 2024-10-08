FROM php:8.2-fpm

RUN apt-get update && apt-get install -y \
    sqlite3 libsqlite3-dev \
    zlib1g-dev libzip-dev unzip

RUN docker-php-ext-install \ 
    pdo_sqlite \
    zip

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN composer self-update

WORKDIR /var/www/html

COPY ./app/composer.json .
RUN composer install

COPY ./app/ .

RUN chown -R www-data:www-data /var/www/html

# ENTRYPOINT ["php", "/var/www/html/init.php"]