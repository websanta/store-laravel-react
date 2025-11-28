#!/bin/sh
set -e

# Set correct permissions for the storage and bootstrap cache directories
# The container runs as root, but we want the app to use a non-root user (www-data by default in php-fpm)
# The project directory is mounted from the host, so we use the host's UID/GID (usually 1000)
# Here we ensure the storage and bootstrap/cache directories are writable
# We assume the host user has UID/GID 1000 (common on Linux Mint/Ubuntu)

# Check if the host directory /var/www/html/storage exists before changing permissions
if [ -d "/var/www/html/storage" ]; then
    chown -R www-data:www-data /var/www/html/storage
fi

if [ -d "/var/www/html/bootstrap/cache" ]; then
    chown -R www-data:www-data /var/www/html/bootstrap/cache
fi

# Run the main command (php-fpm)
exec "$@"