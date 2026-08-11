FROM php:8.2-fpm

RUN apt-get update \
    && apt-get install -y default-libmysqlclient-dev \
    && docker-php-ext-configure mysqli \
    && docker-php-ext-install -j$(nproc) mysqli pdo_mysql

WORKDIR /var/www/html

COPY conf.d/php.ini /usr/local/etc/php/conf.d/php.ini