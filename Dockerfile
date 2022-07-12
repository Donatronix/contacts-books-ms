FROM webdevops/php-nginx:8.1-alpine
LABEL Maintainer="Ihor Porokhnenko <ihor.porokhnenko@gmail.com>"
LABEL Description="Lightweight container with Nginx & PHP-FPM 8 based on Alpine Linux."

# Set environment variables
ARG MODE
ENV MODE=${MODE:-staging}

# Do a single run command to make the intermediary containers smaller.
RUN set -ex

## Update package list
RUN apk update

## Install packages necessary during the build phase
RUN apk --no-cache add mc

## Clean apk cache after all installed packages
RUN rm -rf /var/cache/apk/*

## COPY custom configs
COPY ./config /opt/docker

## Remove incorrect endlines
RUN sed -i 's/\r$//g' /opt/docker/bin/service.d/artisan.sh
RUN sed -i 's/\r$//g' /opt/docker/bin/service.d/pubsub.sh
RUN sed -i 's/\r$//g' /opt/docker/etc/supervisor.d/*

## Set correct permisions
RUN chmod -R 755 /opt/docker/bin/service.d/*.sh

## Copy existing application contents to workdir
COPY --chown=nginx:nginx ./web /var/www/html
COPY --chown=nginx:nginx ./sumra-sdk /var/www/sumra-sdk

## Set work directory
WORKDIR /var/www/html

## Update .ENV and remove unneeded
RUN cp -f .env.${MODE} .env
RUN rm -rf /var/www/html/.env.*

## Set writable dirs
RUN chown -R nginx:nginx /var/www/html
RUN chmod -R 777 /var/www/html/storage/

## Composer packages install
RUN composer install
