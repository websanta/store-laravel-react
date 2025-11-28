#!/bin/bash

# Seed Database Script
# This script runs database migrations and seeders

set -e

echo "========================================"
echo "Database Seeding Script"
echo "========================================"
echo ""

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

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

# Parse arguments
FRESH=false
SEED=true
ENV="local"

while [[ $# -gt 0 ]]; do
    case $1 in
        --fresh)
            FRESH=true
            shift
            ;;
        --no-seed)
            SEED=false
            shift
            ;;
        --env)
            ENV="$2"
            shift 2
            ;;
        *)
            echo "Unknown option: $1"
            echo "Usage: $0 [--fresh] [--no-seed] [--env <environment>]"
            exit 1
            ;;
    esac
done

# Check if container is running
if ! docker compose ps store | grep -q "Up"; then
    print_error "Store container is not running. Please start containers first."
    echo "Run: make up"
    exit 1
fi

print_success "Store container is running"

# Check database connection
print_info "Checking database connection..."
if ! docker compose exec store php artisan db:show > /dev/null 2>&1; then
    print_error "Cannot connect to database. Please check database configuration."
    exit 1
fi
print_success "Database connection successful"

# Confirm if fresh migration
if [ "$FRESH" = true ]; then
    print_warning "WARNING: This will drop all tables and recreate them!"
    print_warning "All existing data will be lost!"
    echo ""
    read -p "Are you sure you want to continue? (yes/no): " confirm
    if [ "$confirm" != "yes" ]; then
        print_info "Operation cancelled."
        exit 0
    fi
fi

# Run migrations
if [ "$FRESH" = true ]; then
    print_info "Running fresh migrations..."
    if [ "$SEED" = true ]; then
        docker compose exec store php artisan migrate:fresh --seed --force
    else
        docker compose exec store php artisan migrate:fresh --force
    fi
    print_success "Fresh migrations completed"
else
    print_info "Running migrations..."
    docker compose exec store php artisan migrate --force
    print_success "Migrations completed"

    # Run seeders if requested and not fresh
    if [ "$SEED" = true ]; then
        print_info "Running database seeders..."
        docker compose exec store php artisan db:seed --force
        print_success "Seeders completed"
    fi
fi

# Show database info
print_info "Database information:"
docker compose exec store php artisan db:show --counts

echo ""
print_success "Database seeding complete!"
echo ""

# Provide additional info
if [ "$SEED" = true ]; then
    print_info "Default users may have been created. Check your seeders for credentials."
fi

print_info "You can now access the application at: https://vmmint22.local"