#!/bin/bash

DIRECTORY="/app/data"
if [ "`ls -A $DIRECTORY`" = "" ]; then
 mv /app/data2/* /app/data
fi

CONFIG="/app/config"
if [ "`ls -A $CONFIG`" = "" ]; then
 mv /app/config2/* /app/config
fi
echo "complete init data and config"
sleep $PAUSE
exec vendor/bin/heroku-php-apache2