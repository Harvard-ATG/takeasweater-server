#!/bin/bash

# set environment variables for db connection
echo 'env[DB_NAME] = "$DB_NAME"' >> /etc/php5/fpm/pool.d/www.conf
echo 'env[DB_USER] = "$DB_USER"' >> /etc/php5/fpm/pool.d/www.conf
echo 'env[DB_PASSWORD] = "$DB_PASSWORD"' >> /etc/php5/fpm/pool.d/www.conf
echo 'env[DB_HOST] = "$DB_HOST"' >> /etc/php5/fpm/pool.d/www.conf
echo 'env[DB_CHARSET] = "$DB_CHARSET"' >> /etc/php5/fpm/pool.d/www.conf
echo 'env[OPENWEATHERMAP_API_KEY] = "$OPENWEATHERMAP_API_KEY"' >> /etc/php5/fpm/pool.d/www.conf

# configure cron job
sed -i "s/CHANGE_ME/$MAIL_TO/g" /var/www/takeasweater/config/update-takeasweater
cp /var/www/takeasweater/config/update-takeasweater /etc/cron.d/update-takeasweater

cp /var/www/takeasweater/config.php.example /var/www/takeasweater/config.php

/usr/bin/supervisord -n
