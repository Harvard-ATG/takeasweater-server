# takeasweater-server

Server side of the Take-A-Sweater application, a service that provides weather data and clothing recommendations based on current and forecasted conditions.

## Overview

Take-A-Sweater helps users decide what to wear based on weather conditions. This repository contains the backend services that:
- Collect and store weather data from external APIs
- Process weather information for different locations
- Provide an API for the client application to retrieve recommendations

## Setup

To get a local development running, make sure [docker is installed and running](https://docs.docker.com/get-started/) and then do the following:

1. Setup environment variables
```
cp .env.example .env
```
2. Start docker services
```
docker compose up
```
3. Visit http://localhost:8000 in your web browser

### Docker

This project uses [docker compose](https://docs.docker.com/compose/) for development and testing.

The `docker-compose.yml` builds the following docker images:

- `Dockerfile.web` - runs PHP + Apache
- `Dockerfile.cron` - runs a cron daemon to update the weather data periodically
- `Dockerfile.mysql` - runs a MySQL database to persist weather data

### Environment variables

Use `.env` to configure environment variables for the web application such as:

- `OPENWEATHERMAP_API_KEY` **(optional)**:  API key used to retrieve weather data from https://openweathermap.org/.

Note that the database environment variables are automatically configured in `docker-compose.yml`, but they can be overridden if needed.

### Connecting to the database

The MySQL database port (3306) is exposed by default by the docker compose `db` service, so it's possible to connect using your preferred MySQL client or GUI.

#### Connecting with MySQL Shell

To connect using [MySQL Shell](https://dev.mysql.com/doc/mysql-shell/8.0/en/) (a modern replacement for the legacy [MySQL Command-Line Client](https://dev.mysql.com/doc/refman/8.4/en/mysql.html)), run the following:

```
mysqlsh --uri "takeasweater:takeasweater@localhost:3306/takeasweater"
```


#### Connecting with Docker Compose

```
docker compose exec db mysql -uroot -proot takeasweater
```

### Database Schema and Snapshot Data

1. Database schema: `db/01_schema.sql`.
2. Data snapshot: `db/init_data/snapshot.zip`

The `snapshot.zip`  contains one CSV file per database table and is compressed because `noaa_weather.csv` is quite large (>150MB). CSV files were used to provide flexibility to swap out the database (i.e. postgres or even sqlite).

```
$ unzip -d db/init_data db/init_data/snapshot.zip
$ tree db/init_data
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

### Logs

- Web server logs: `docker compose logs web`
- Cron job logs: `docker compose logs cron`
- Database logs: `docker compose logs db`
