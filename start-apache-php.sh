#!/bin/bash
/usr/local/php56/sbin/php-fpm &
apache2ctl -D FOREGROUND
