#!/bin/bash

php-fpm &

# Wait for the database to be ready (optional but recommended)
# This is a simple check; you might want to improve it for production
echo "Waiting for database to be ready..."
until nc -z -v -w30 db 3306
do
  echo "Waiting for database connection..."
  sleep 5
done

php artisan migrate --force

# Keep the container running
wait