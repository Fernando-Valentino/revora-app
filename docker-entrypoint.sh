#!/bin/sh
set -e

echo "Running composer install..."
composer install --no-interaction --optimize-autoloader

echo "Setting permissions..."
chown -R www-data:www-data /var/www/html/vendor

# Execute the main container command
exec "$@"
