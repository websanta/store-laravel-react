#!/bin/bash

# Setup Permissions Script
# This script fixes file and directory permissions for Laravel

set -e

echo "========================================"
echo "Laravel Permissions Setup Script"
echo "========================================"
echo ""

# Colors
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

print_success() {
    echo -e "${GREEN}✓ $1${NC}"
}

print_info() {
    echo -e "${BLUE}→ $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠ $1${NC}"
}

# Docker Compose file path
COMPOSE_FILE="infrastructure/docker-compose.yml"

# Check if running inside Docker container
if [ -f /.dockerenv ]; then
    print_info "Running inside Docker container..."

    # Set ownership to www:www user
    print_info "Setting ownership to www:www..."
    chown -R www:www /var/www/storage
    chown -R www:www /var/www/bootstrap/cache
    print_success "Ownership set"

    # Set directory permissions to 775
    print_info "Setting directory permissions to 775..."
    find /var/www/storage -type d -exec chmod 775 {} \;
    find /var/www/bootstrap/cache -type d -exec chmod 775 {} \;
    print_success "Directory permissions set"

    # Set file permissions to 664
    print_info "Setting file permissions to 664..."
    find /var/www/storage -type f -exec chmod 664 {} \;
    find /var/www/bootstrap/cache -type f -exec chmod 664 {} \;
    print_success "File permissions set"

    # Special handling for logs
    if [ -d "/var/www/storage/logs" ]; then
        print_info "Setting special permissions for logs directory..."
        chmod -R 775 /var/www/storage/logs
        print_success "Logs directory permissions set"
    fi

    # Verify permissions
    print_info "Verifying permissions..."
    storage_perm=$(stat -c %a /var/www/storage)
    cache_perm=$(stat -c %a /var/www/bootstrap/cache)
    print_success "Storage permissions: $storage_perm"
    print_success "Cache permissions: $cache_perm"

else
    print_info "Running from host, will use docker compose exec..."

    # Set ownership to www:www user
    print_info "Setting ownership to www:www..."
    docker compose -f "$COMPOSE_FILE" exec -u root store chown -R www:www /var/www/storage
    docker compose -f "$COMPOSE_FILE" exec -u root store chown -R www:www /var/www/bootstrap/cache
    print_success "Ownership set"

    # Set directory permissions to 775
    print_info "Setting directory permissions to 775..."
    docker compose -f "$COMPOSE_FILE" exec -u root store find /var/www/storage -type d -exec chmod 775 {} \;
    docker compose -f "$COMPOSE_FILE" exec -u root store find /var/www/bootstrap/cache -type d -exec chmod 775 {} \;
    print_success "Directory permissions set"

    # Set file permissions to 664
    print_info "Setting file permissions to 664..."
    docker compose -f "$COMPOSE_FILE" exec -u root store find /var/www/storage -type f -exec chmod 664 {} \;
    docker compose -f "$COMPOSE_FILE" exec -u root store find /var/www/bootstrap/cache -type f -exec chmod 664 {} \;
    print_success "File permissions set"

    # Special handling for logs
    if docker compose -f "$COMPOSE_FILE" exec store test -d /var/www/storage/logs 2>/dev/null; then
        print_info "Setting special permissions for logs directory..."
        docker compose -f "$COMPOSE_FILE" exec -u root store chmod -R 775 /var/www/storage/logs
        print_success "Logs directory permissions set"
    fi

    # Make sure .env is readable
    if [ -f ".env" ]; then
        print_info "Setting .env file permissions..."
        docker compose -f "$COMPOSE_FILE" exec -u root store chmod 644 /var/www/.env 2>/dev/null || true
        print_success ".env permissions set"
    fi

    # Verify permissions
    print_info "Verifying permissions..."
    storage_perm=$(docker compose -f "$COMPOSE_FILE" exec store stat -c %a /var/www/storage | tr -d '\r')
    cache_perm=$(docker compose -f "$COMPOSE_FILE" exec store stat -c %a /var/www/bootstrap/cache | tr -d '\r')
    print_success "Storage permissions: $storage_perm"
    print_success "Cache permissions: $cache_perm"
fi

echo ""
print_success "Permissions setup complete!"
echo ""
print_info "Summary:"
echo "  - All storage files/directories owned by www:www"
echo "  - Directories: 775 (rwxrwxr-x)"
echo "  - Files: 664 (rw-rw-r--)"
echo "  - This allows web server and CLI to read/write"
