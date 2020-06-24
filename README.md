# takeasweater-server

Server side of the Take-A-Sweater application.

## Setup

1. Configure database settings:

```sh
$ cp config.php.example config.php    # Copy example config
$ nano config.php                     # Update database settings
```

2. Setup and configure Apache/Nginx. 

3. Setup cron job to update the NOAA weather. See `classes/noaa_cron.php`.


## Docker Setup

1. Build the docker image:

```
$ docker build -t image_tag .
```

2. Run the image:

```
$ docker run -p 8000:8000 \
-e DB_NAME='your_db_name' \
-e DB_USER='your_db_user' \
-e DB_PASSWORD='your_db_password' \
-e DB_HOST='your_db_host' \
-e DB_CHARSET='your_db_charset' \
-e OPENWEATHERMAP_API_KEY='the_api_key' \
-e MAIL_TO='address_for_cron_output@whatever.com' \
--name container_name image_tag
```

Then visit http://127.0.0.1:8000

### Notes on Docker Setup

- If you don't want the cron job to kick off, you can comment out those lines in 
`docker-entrypoint.sh`. Then you will not have to provide an environment variable
for `MAIL_TO`.
- The Docker configuration is dependent on the files in `/config`. So, treat them 
with care.
