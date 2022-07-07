#!/bin/bash

# turn on bash's job control
set -m

# if any of the commands in your code fails for any reason, the entire script fails
set -o errexit

# fail exit if one of your pipe command fails
set -o pipefail

# exits if any of your variables is not set
set -o nounset

# Set sleep 60 sec
sleep 10

# Run Pubsub Queue
echo "Run php artisan queue:listen rabbitmq --queue=${PUBSUB_RECEIVER} --timeout=0 --tries=3"
/usr/local/bin/php /var/www/html/artisan queue:listen rabbitmq --queue=${PUBSUB_RECEIVER} --timeout=0 --tries=3 > /dev/null

# now we bring the primary process back into the foreground and leave it there
fg %1
