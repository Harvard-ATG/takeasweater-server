# takeasweater-server

Server side of the Take-A-Sweater application.

## Local Development

### Quickstart

This project uses docker to setup a PHP + Apache + MySQL environment. To get up and running, make sure [docker is installed](https://docs.docker.com/get-started/) and then do the following:

1. Run `docker compose up` in the project root directory.
2. Visit http://localhost:8000/

Note that a snapshot of the data is automatically loaded when the MySQL database is initialized.

###  Connecting to the database

If you are using the docker compose setup, port 3306 is exposed by default so you can connect to it using your preferred MySQL client.

To connect using the built-in `mysql` client in the docker image:

```
docker compose exec db mysql -uroot -proot takeasweater
```
