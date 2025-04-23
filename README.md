# takeasweater-server

Server side of the Take-A-Sweater application.

## Development

The application is built with PHP, Apache and MySQL. To get a local development running, make sure [docker is installed](https://docs.docker.com/get-started/) and then follow these steps:

1. Setup environment variables:
```
cp .env.example .env
```
2. Start docker services:
```
docker compose up
```
3. Visit http://localhost:8000

### Docker setup

This project uses [docker](https://docs.docker.com/) and [docker compose](https://docs.docker.com/compose/) for development and testing.

The `docker-compose.yml` builds the following docker images:

- `Dockerfile.web` - runs PHP + Apache
- `Dockerfile.cron` - runs a cron daemon to update the weather data periodically
- `Dockerfile.mysql` - runs a MySQL database to persist weather data

Environment variables can be provided through a `.env` file and will override any defaults in the `docker-compose.yml`. The most important variables include:

- `OPENWEATHERMAP_API_KEY` (optional): This API key is needed to connect to https://openweathermap.org/ and retrieve weather updates, which are stored in the database.

### Connecting to the database

Port 3306 is exposed by default by the docker compose stack, so you can connect to it directly using your preferred MySQL client.

Alternatively, you can connect using the `mysql` client built into the docker container:

```
docker compose exec db mysql -uroot -proot takeasweater
```

### Database schema and snapshot data

The MySQL database schema is defined in `db/01_schema.sql` and a snapshot of the weather data is stored in `db/init_data/snapshot.zip`. The ZIP file contains one CSV file per database table and is compressed because `noaa_weather.csv` is quite large (>150MB).

After uncompressing, it should contain:

```
db/init_data
├── location.csv
├── noaa_weather.csv
├── precip_codes.csv
├── sky_codes.csv
├── weather.csv
├── weather_modified.csv
└── wind_codes.csv
```

### Cron for Scheduled Weather Updates

The application uses a dedicated cron container to periodically update weather data. Here's how it works:

1. The `Dockerfile.cron` builds a container dedicated for running scheduled tasks with [cron](https://www.redhat.com/en/blog/automate-linux-tasks-cron).
2. Through `docker-compose.yml` it's configured with `TAKASWEATER_URL` to communicate with the web service.
3. Cron will trigger HTTP requests to `noaa_cron.php` periodically. The `noaa_cron.php` is responsible for fetching new data from OpenWeatherMap API using the appropriate API key and then updating the data stored in the database.