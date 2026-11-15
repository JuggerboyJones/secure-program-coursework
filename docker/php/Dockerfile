FROM php:8.4-apache

RUN docker-php-ext-install pdo_mysql

COPY docker/php/apache.conf /etc/apache2/sites-available/000-default.conf
COPY docker/php/development.ini /usr/local/etc/php/conf.d/development.ini

WORKDIR /var/www/app
