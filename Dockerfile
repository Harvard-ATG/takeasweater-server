FROM jmcarbo/nginx-php-fpm

RUN apt-get -y update && \
    apt-get -y install curl

COPY config/nginx.conf /etc/nginx/

COPY config/takeasweater.conf /etc/nginx/conf.d/

RUN mkdir /var/www/takeasweater

COPY . /var/www/takeasweater/

HEALTHCHECK --interval=30s --timeout=30s --start-period=10s --retries=3 CMD [ "curl -f http://127.0.0.1:80/healthcheck"]

ENTRYPOINT ["/var/www/takeasweater/docker-entrypoint.sh"]
