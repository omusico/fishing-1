#!/bin/bash
chmod -R 757 /var/www/application/Runtime
source /etc/apache2/envvars
tail -F /var/log/apache2/* &
exec apache2 -D FOREGROUND
