#!/bin/bash

# Start PHP-FPM in background
service php8.2-fpm start

# Start Nginx in foreground
exec nginx -g "daemon off;"
