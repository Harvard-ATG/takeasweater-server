# takeasweater-server

Server side of the Take-A-Sweater application.

## Setup

1. Configure database settings:

```sh
$ cp config.php.example config.php
$ nano config.php
$
```

2. Setup and configure Apache/Nginx. 

3. Setup cron job to update the NOAA weather. See `classes/noaa_cron.php`.

