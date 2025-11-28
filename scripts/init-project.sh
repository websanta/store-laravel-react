#!/bin/bash

# Initialize Laravel Project Script
# This script initializes a fresh Laravel installation inside Docker

set -e

echo "========================================"
echo "Laravel Project Initialization Script"
echo "========================================"
echo ""

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Function to print colored messages
print_success() {
    echo -e "${GREEN}✓ $1${NC}"
}

print_error() {
    echo -e "${RED}✗ $1${NC}"
}

print_info() {
    echo -e "${BLUE}→ $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠ $1${NC}"
}

# Check if Docker is running
if ! docker info > /dev/null 2>&1; then
    print_error "Docker is not running. Please start Docker and try again."
    exit 1
fi

print_success "Docker is running"

# Clean up any existing temp directory
if [ -d "temp" ]; then
    print_info "Cleaning up existing temp directory..."
    sudo rm -rf temp
fi

# Check if Laravel is already installed
if [ -f "composer.json" ] && grep -q "laravel/framework" composer.json; then
    print_warning "Laravel already installed, skipping installation..."
else
    print_info "Installing Laravel 12..."

    # Install Laravel using Composer in Docker with proper user permissions
    docker run --rm -v $(pwd):/app -w /app -u $(id -u):$(id -g) composer:latest \
        create-project --prefer-dist laravel/laravel temp "12.*"

    # Move files from temp to root
    if [ -d "temp" ]; then
        print_info "Moving Laravel files to project root..."
        shopt -s dotglob
        cp -r temp/* . 2>/dev/null || true
        shopt -u dotglob

        # Clean up temp directory
        print_info "Cleaning up temp directory..."
        rm -rf temp

        print_success "Laravel 12 installed successfully"
    else
        print_error "Failed to install Laravel"
        exit 1
    fi
fi

# Setup .env file
if [ ! -f ".env" ]; then
    if [ -f ".env.example" ]; then
        print_info "Creating .env from .env.example..."
        cp .env.example .env
        print_success ".env file created"
    else
        print_error ".env.example not found!"
        exit 1
    fi
else
    print_warning ".env file already exists, skipping..."
fi

# Update .env with Docker-specific settings
print_info "Configuring .env for Docker environment..."
sed -i 's/DB_CONNECTION=.*/DB_CONNECTION=pgsql/' .env
sed -i 's/DB_HOST=.*/DB_HOST=postgres/' .env
sed -i 's/DB_PORT=.*/DB_PORT=5432/' .env
sed -i 's/DB_DATABASE=.*/DB_DATABASE=store_db/' .env
sed -i 's/DB_USERNAME=.*/DB_USERNAME=store_user/' .env
sed -i 's/DB_PASSWORD=.*/DB_PASSWORD=secret/' .env
sed -i 's|APP_URL=.*|APP_URL=https://vmmint22.local|' .env
sed -i 's/REDIS_HOST=.*/REDIS_HOST=redis/' .env
sed -i 's/REDIS_PASSWORD=.*/REDIS_PASSWORD=redis_secret/' .env
sed -i 's/CACHE_STORE=.*/CACHE_STORE=redis/' .env
sed -i 's/SESSION_DRIVER=.*/SESSION_DRIVER=redis/' .env
sed -i 's/MAIL_MAILER=.*/MAIL_MAILER=smtp/' .env
sed -i 's/MAIL_HOST=.*/MAIL_HOST=mailpit/' .env
sed -i 's/MAIL_PORT=.*/MAIL_PORT=1025/' .env

# Add Xdebug settings if not present
if ! grep -q "ENABLE_XDEBUG" .env; then
    echo "" >> .env
    echo "# Xdebug Configuration" >> .env
    echo "ENABLE_XDEBUG=true" >> .env
    echo "XDEBUG_MODE=debug,develop,coverage" >> .env
    echo "XDEBUG_CONFIG=client_host=host.docker.internal" >> .env
fi

print_success ".env configured for Docker"

# Create necessary directories
print_info "Creating required directories..."
mkdir -p storage/framework/{sessions,views,cache}
mkdir -p storage/logs
mkdir -p storage/app/public
mkdir -p bootstrap/cache
mkdir -p database/{factories,seeders,migrations}
mkdir -p tests/{Feature,Unit}
print_success "Directories created"

# Set proper permissions
print_info "Setting permissions..."
chmod -R 775 storage bootstrap/cache
chown -R $USER:$USER storage bootstrap/cache
print_success "Permissions set"

# Create .gitkeep files
print_info "Creating .gitkeep files..."
touch storage/logs/.gitkeep
touch storage/app/.gitkeep
touch storage/app/public/.gitkeep
touch infrastructure/docker/nginx/certs/.gitkeep
print_success ".gitkeep files created"

# Install Pest
print_info "Checking for Pest testing framework..."
if [ -f "composer.json" ]; then
    if ! grep -q "pestphp/pest" composer.json; then
        print_info "Pest will be installed via composer after containers start"
    else
        print_success "Pest already configured"
    fi
fi

print_success "Project initialization complete!"
echo ""
echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}Next steps:${NC}"
echo -e "${BLUE}1. Run: make build${NC}"
echo -e "${BLUE}2. Run: make up${NC}"
echo -e "${BLUE}3. Run: make composer-install${NC}"
echo -e "${BLUE}4. Run: make npm-install${NC}"
echo -e "${BLUE}5. Run: make key-generate${NC}"
echo -e "${BLUE}6. Run: make pest-install${NC}"
echo -e "${BLUE}7. Run: make migrate${NC}"
echo ""
echo -e "${YELLOW}Or simply run: make install (does all of the above)${NC}"
echo -e "${GREEN}========================================${NC}"