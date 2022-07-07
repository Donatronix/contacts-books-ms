#!/bin/bash

## Optimize the framework for better performance
echo "Run php artisan optimize"
/usr/local/bin/php /var/www/html/artisan optimize

# Update DB
echo "Run php artisan migrate"
/usr/local/bin/php /var/www/html/artisan migrate

# Swagger docs generate
echo "Run php artisan swagger-lume:generate"
/usr/local/bin/php /var/www/html/artisan swagger-lume:generate