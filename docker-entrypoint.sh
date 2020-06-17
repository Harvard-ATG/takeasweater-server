#!/bin/bash

cp /var/www/takeasweater/config.php.example /var/www/takeasweater/config.php
/usr/bin/supervisord -n
