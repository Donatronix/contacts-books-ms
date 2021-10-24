FROM webdevops/php-nginx:8.0-alpine
LABEL Maintainer="Ihor Porokhnenko <ihor.porokhnenko@gmail.com>"
LABEL Description="Lightweight container with Nginx & PHP-FPM 8 based on Alpine Linux."

# Do a single run command to make the intermediary containers smaller.
RUN set -ex

## Update package list
RUN apk update

## Install packages necessary during the build phase
RUN apk --no-cache add \
    mc \
    nano

## Clean apk cache after all installed packages
RUN rm -rf /var/cache/apk/*

# Configure nginx
#RUN rm -f /etc/nginx/http.d/default.conf
#COPY config/nginx/nginx.conf /etc/nginx/nginx.conf
#COPY config/nginx/http.d/app.conf /etc/nginx/http.d/app.conf

COPY config/vhost.conf /opt/docker/etc/nginx/vhost.conf
COPY config/vhost.ssl.conf /opt/docker/etc/nginx/vhost.ssl.conf
COPY config/vhost.common.d/ /opt/docker/etc/nginx/vhost.common.d/

# Configure PHP-FPM
#COPY config/php/fpm-pool.conf /etc/php8/php-fpm.d/www.conf
#COPY config/php/php.ini /etc/php8/conf.d/custom.ini

## Copy existing application contents to workdir
COPY --chown=nginx:nginx ./web /var/www/html
COPY --chown=nginx:nginx ./pubsub /var/www/pubsub
COPY --chown=nginx:nginx ./json-api /var/www/json-api

## Set work directory
WORKDIR /var/www/html

## Set writable dirs
RUN chmod -R 777 /var/www/html/storage/*

## Remove unneeded files
RUN rm -rf /var/www/html/.idea
RUN find /var/www/html/storage/framework/ -type f -name "*.php" -delete
RUN rm -rf -R /var/www/html/storage/logs/*.log
RUN rm -rf /var/www/html/.editorconfig
RUN rm -rf /var/www/html/.env.example
RUN rm -rf /var/www/html/.gitignore
RUN rm -rf /var/www/html/.styleci.yml

## Composer packages install & update
RUN composer -v install
RUN composer -v update
