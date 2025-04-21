# takeasweater-server

Server side of the Take-A-Sweater application.

## Local Development

### Setup

Start the application:

```
docker compose up
```

Then visit http://localhost:8000

###  Connecting to the database

If you are using the `docker-compose.yml`, port 3306 is exposed by default so you can connect to it using the MySQL client of your choice.

Alternatively, you can connect using `docker compose` like this:

```
docker compose exec db mysql -uroot -proot takeasweater
```
