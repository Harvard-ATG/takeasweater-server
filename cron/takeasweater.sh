#!/bin/bash -ex
curl -H "Authorization: Bearer ${TAKEASWEATER_CRON_SECRET}" ${TAKEASWEATER_URL}/noaa_cron.php