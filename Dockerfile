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

RUN apk update && apk add php8-intl icu-dev gmp-dev
#RUN /usr/local/bin/docker-php-ext-configure intl
RUN /usr/local/bin/docker-php-ext-install intl sockets bcmath gmp

RUN composer -v install
RUN composer -v update

#	2. The second part creates a final Docker image with an Apache web server to serve the application

FROM php:8.0.3-apache-buster

COPY --from=build /app /app
COPY conf/vhost.conf /etc/apache2/sites-available/000-default.conf
#COPY conf/laravel-echo-server.json /app

RUN apt update && apt install -y mc sudo openssh-client zlib1g-dev libicu-dev g++ libgmp-dev

#RUN curl -sL https://deb.nodesource.com/setup_10.x | bash - && apt install -y npm && npm install -g laravel-echo-server

RUN chown -R www-data:www-data /app \
    && a2enmod rewrite ssl headers

RUN /usr/local/bin/docker-php-ext-install pdo pdo_mysql intl sockets bcmath gmp

RUN pecl install xdebug-3.0.3

#RUN /usr/local/bin/docker-php-ext-configure xdebug
RUN /usr/local/bin/docker-php-ext-enable xdebug
#RUN cd /app && php artisan l5-swagger:generate

RUN echo "Listen 8080" > /etc/apache2/ports.conf
RUN echo "Listen 8443" >> /etc/apache2/ports.conf
USER www-data
EXPOSE 8443 8080
