FROM php:8.2-apache

RUN apt-get update && apt-get install -y libpq-dev

RUN apt-get update && apt-get install -y \
    libpq-dev git zip unzip \
    && docker-php-ext-install pdo pdo_mysql

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Enable Apache mod_rewrite
RUN a2enmod rewrite



