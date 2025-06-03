#!/bin/bash -ex
echo "Starting takeasweater cron job at $(date -Iminutes)"
curl -sS -H "Authorization: Bearer ${TAKEASWEATER_CRON_SECRET}" ${TAKEASWEATER_URL}/noaa_cron.php
echo "Completed takeasweater cron job at $(date -Iminutes)"