# --- Makefile for Laravel/React Docker Development ---

# Define names of services
DOCKER_COMPOSE_FILE := infrastructure/docker-compose.yml
PHP_SERVICE := store
NODE_SERVICE := node

# Define project root where all application files live
APP_ROOT := $(shell pwd)

.PHONY: up down install artisan test frontend-dev certs

# Start all containers in detached mode and build images if necessary
up:
	@echo "Starting Docker containers..."
	docker compose -f $(DOCKER_COMPOSE_FILE) up -d --build

# Stop and remove all containers, networks, and volumes
down:
	@echo "Stopping and removing Docker containers..."
	docker compose -f $(DOCKER_COMPOSE_FILE) down --remove-orphans

# Install Composer and Node dependencies
install: composer-install node-install

composer-install:
	@echo "Installing Composer dependencies..."
	docker compose -f $(DOCKER_COMPOSE_FILE) run --rm $(PHP_SERVICE) composer install

node-install:
	@echo "Installing Node dependencies..."
	docker compose -f $(DOCKER_COMPOSE_FILE) run --rm $(NODE_SERVICE) npm install

# Run Laravel Artisan commands
artisan:
	@echo "Running php artisan $(cmd)..."
	docker compose -f $(DOCKER_COMPOSE_FILE) run --rm $(PHP_SERVICE) php artisan $(cmd)

# Start frontend development watcher (npm run dev)
frontend-dev:
	@echo "Starting frontend development server (npm run dev)..."
	docker compose -f $(DOCKER_COMPOSE_FILE) run --rm -p 5173:5173 $(NODE_SERVICE) npm run dev

# Generate self-signed SSL certificates for vmmint22.local
certs:
	@echo "Generating self-signed SSL certificates for vmmint22.local..."
	openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
		-keyout infrastructure/docker/nginx/certs/temp-key.pem \
		-out infrastructure/docker/nginx/certs/temp.pem \
		-subj "/C=RU/ST=Moscow/L=Moscow/O=WebSanta Dev/OU=IT Department/CN=vmmint22.local"
	@echo "Certificates generated in infrastructure/docker/nginx/certs/"

# Example test target (update once testing is configured)
test:
	@echo "Running application tests..."
	docker compose -f $(DOCKER_COMPOSE_FILE) run --rm $(PHP_SERVICE) php artisan test