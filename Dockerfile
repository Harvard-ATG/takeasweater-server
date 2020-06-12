FROM jmcarbo/nginx-php-fpm

COPY config/nginx.conf /etc/nginx/

COPY config/takeasweater.conf /etc/nginx/conf.d/

RUN mkdir /var/www/takeasweater

COPY . /var/www/takeasweater/

CMD ["/usr/bin/supervisord", "-n"]
