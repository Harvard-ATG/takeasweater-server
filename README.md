# takeasweater-server

Server side of the Take-A-Sweater application.

## Local Development

### Setup

Start the application:

```
docker compose up
```

Setup the database schema:

```
cat src/database/schema.sql | docker compose exec -T db mysql -uroot -proot takeasweater
```

Then visit http://localhost:8000

###  Connecting to the database

To connect to the database:

```
docker compose exec db mysql -uroot -proot takeasweater
```

Or use your preferred MySQL client to connect .
