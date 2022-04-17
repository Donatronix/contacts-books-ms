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
COPY config/vhost.conf /opt/docker/etc/nginx/vhost.conf
COPY config/vhost.ssl.conf /opt/docker/etc/nginx/vhost.ssl.conf
COPY config/vhost.common.d/ /opt/docker/etc/nginx/vhost.common.d/

## Copy existing application contents to workdir
COPY --chown=nginx:nginx ./web /var/www/html
COPY --chown=nginx:nginx ./sumra-sdk /var/www/sumra-sdk

## Set work directory
WORKDIR /var/www/html

## Remove unneeded files
RUN rm -rf /var/www/html/.idea
RUN find /var/www/html/storage/framework/ -type f -name "*.php" -delete
RUN rm -rf -R /var/www/html/storage/logs/*.log
RUN rm -rf /var/www/html/.editorconfig
RUN rm -rf /var/www/html/.gitignore
RUN rm -rf /var/www/html/.styleci.yml
RUN rm -rf /var/www/html/.env.example

## Update env
RUN rm -rf /var/www/html/.env
RUN cp -f .env.production .env
RUN rm -rf /var/www/html/.env.production

## Set writable dirs
RUN chown -R nginx:nginx /var/www/html

## Composer packages install & update
RUN composer -v install
RUN composer -v update
