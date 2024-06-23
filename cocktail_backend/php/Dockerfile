FROM php:8.3.2-apache

RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

RUN apt-get update \
    && apt-get install -y --no-install-recommends locales apt-utils git libicu-dev g++ libpng-dev libxml2-dev libzip-dev libonig-dev libxslt-dev libssl-dev;

RUN echo "en_US.UTF-8 UTF-8" > /etc/locale.gen && \
    echo "fr_FR.UTF-8 UTF-8" >> /etc/locale.gen && \
    locale-gen

RUN curl -sSk https://getcomposer.org/installer | php -- --disable-tls && \
   mv composer.phar /usr/local/bin/composer

RUN docker-php-ext-configure intl
RUN docker-php-ext-install gd opcache intl zip calendar dom mbstring zip gd xsl

RUN apt-get install -y --no-install-recommends libssl-dev && \
    pecl install mongodb --with-openssl && \
    docker-php-ext-enable mongodb

RUN pecl install ast \
    && docker-php-ext-enable ast

RUN pecl install apcu && docker-php-ext-enable apcu

WORKDIR /var/www/
