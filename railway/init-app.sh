#!/bin/bash
# Make sure this file has executable permissions, run `chmod +x railway/init-app.sh`
set -e

# Run after the image is built, when the private MySQL hostname is reachable.
php artisan migrate --force

php artisan optimize:clear
php artisan config:cache
php artisan event:cache
php artisan route:cache
php artisan view:cache
