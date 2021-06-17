FROM composer:latest as build
LABEL maintainer "Eduard <ed@dev-ops.engineer>"

###################	This Dockerfile is made of two parts:	###################
#
#	1. The first part extends a PHP composer image so that you can install the application's dependencies.

WORKDIR /app
COPY ./web /app
COPY ./pubsub /pubsub
COPY ./json-api /json-api
#COPY ./baum /baum

RUN apk update && apk add --no-cache php8-intl icu-dev gmp-dev libpng libpng-dev
#RUN docker-php-ext-configure intl
RUN docker-php-ext-install intl sockets bcmath gmp gd && apk del libpng-dev

#libjpeg-turbo-dev libwebp-dev zlib-dev libxpm-dev

RUN composer -v install
RUN composer -v update

#	2. The second part creates a final Docker image with an Apache web server to serve the application


FROM php:8.0.6-apache-buster

COPY --from=build /app /var/www/html
COPY --from=build /pubsub /var/www/pubsub
COPY --from=build /json-api /var/www/json-api

COPY conf/vhost.conf /etc/apache2/sites-available/000-default.conf
#COPY conf/laravel-echo-server.json /var/www/html
#RUN curl -sL https://deb.nodesource.com/setup_10.x | bash - && apt install -y npm && npm i -g laravel-echo-server

# make sure apt is up to date
RUN apt update --fix-missing && apt upgrade -y
#RUN apt install -y curl
RUN apt install -y \
        sudo mc \
        openssh-client \
        build-essential \
        libssl-dev \
        zlib1g-dev \
        libpng-dev \
        libjpeg-dev \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
#        libxpm-dev
#        libwebp-dev
        libicu-dev \
        libgmp-dev \
        libzip-dev
#       libonig-dev
#       composer


RUN chown -R www-data:www-data /var/www/html \
    && a2enmod rewrite ssl headers

RUN pecl install xdebug-3.0.3

#RUN docker-php-ext-configure xdebug
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd
    
#RUN docker-php-ext-configure gd --with-gd --with-webp-dir --with-jpeg-dir \
#    --with-png-dir --with-zlib-dir --with-xpm-dir --with-freetype-dir \
#    --enable-gd-native-ttf

RUN docker-php-ext-install pdo_mysql intl sockets bcmath gmp zip # mbstring
RUN docker-php-ext-enable xdebug


#RUN cd /var/www/html && php artisan l5-swagger:generate

RUN echo "Listen 8080" > /etc/apache2/ports.conf
RUN echo "Listen 8443" >> /etc/apache2/ports.conf
USER www-data
EXPOSE 8443 8080

