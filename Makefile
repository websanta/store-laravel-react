.PHONY: help build up down restart logs shell composer artisan npm test clean install setup

# Color output
YELLOW := \033[0;33m
GREEN := \033[0;32m
RED := \033[0;31m
BLUE := \033[0;34m
NC := \033[0m # No Color

# Docker Compose file path
COMPOSE_FILE := infrastructure/docker-compose.yml

help: ## Show this help message
	@echo '$(YELLOW)Available commands:$(NC)'
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "  $(GREEN)%-25s$(NC) %s\n", $$1, $$2}'

install: ## Initial project installation (complete setup)
	@echo "$(YELLOW)Starting complete project installation...$(NC)"
	@make setup
	@make build
	@make up
	@echo "$(YELLOW)Waiting for containers to be ready...$(NC)"
	@sleep 10
	@make permissions
	@make composer-install
	@make livewire-install
	@make breeze-install
	@make filament-install
	@make npm-install
	@make key-generate
	@make pest-install
	@make migrate
	@make storage-link
	@make start-vite
	@echo "$(GREEN)============================================$(NC)"
	@echo "$(GREEN)Installation complete!$(NC)"
	@echo "$(YELLOW)Access points:$(NC)"
	@echo "  $(BLUE)Application:$(NC) https://vmmint22.local"
	@echo "  $(BLUE)Admin Panel:$(NC) https://vmmint22.local/admin"
	@echo "  $(BLUE)Mailpit:$(NC)     http://localhost:8025"
	@echo "  $(BLUE)pgAdmin:$(NC)     http://localhost:5050"
	@echo "  $(BLUE)Vite Dev:$(NC)    https://vmmint22.local:5174"
	@echo "$(GREEN)============================================$(NC)"

setup: ## Setup environment file
	@if [ ! -f .env ]; then \
		echo "$(YELLOW)Creating .env file...$(NC)"; \
		cp .env.example .env; \
		echo "$(GREEN).env file created$(NC)"; \
	else \
		echo "$(BLUE).env file already exists$(NC)"; \
	fi

dev: ## Start development environment
	@echo "$(YELLOW)Starting development environment...$(NC)"
	@make up
	@docker compose -f $(COMPOSE_FILE) exec -d node npm run dev
	@echo "$(GREEN)============================================$(NC)"
	@echo "$(GREEN)Development environment started!$(NC)"
	@echo "$(YELLOW)Access points:$(NC)"
	@echo "  $(BLUE)Application:$(NC) https://vmmint22.local"
	@echo "  $(BLUE)Admin Panel:$(NC) https://vmmint22.local/admin"
	@echo "  $(BLUE)Mailpit:$(NC)     http://localhost:8025"
	@echo "  $(BLUE)pgAdmin:$(NC)     http://localhost:5050"
	@echo "  $(BLUE)Vite Dev:$(NC)    https://vmmint22.local:5174"
	@echo "$(GREEN)============================================$(NC)"

fbuild: ## Build assets for production
	@echo "$(YELLOW)Building assets for production...$(NC)"
	@docker compose -f $(COMPOSE_FILE) exec node npm run build
	@echo "$(GREEN)Production build complete!$(NC)"
	@echo "$(BLUE)Built files are in public/build/$(NC)"

build-watch: ## Build assets with watch mode
	@echo "$(YELLOW)Building assets in watch mode...$(NC)"
	@docker compose -f $(COMPOSE_FILE) exec node npm run build -- --watch

build: ## Full production build (composer + npm)
	@echo "$(YELLOW)Starting full production build...$(NC)"
	@make composer-install
	@make npm-build
	@make optimize
	@echo "$(GREEN)============================================$(NC)"
	@echo "$(GREEN)Production build complete!$(NC)"
	@echo "$(YELLOW)Built assets:$(NC) public/build/"
	@echo "$(YELLOW)Next steps:$(NC)"
	@echo "  1. Deploy files to production server"
	@echo "  2. Run migrations: make migrate"
	@echo "  3. Clear cache: make cache-clear"
	@echo "$(GREEN)============================================$(NC)"

deploy-prepare: ## Prepare application for deployment
	@echo "$(YELLOW)Preparing application for deployment...$(NC)"
	@make cache-clear
	@make composer-install
	@make npm-build
	@make optimize
	@echo "$(GREEN)Application ready for deployment!$(NC)"

dbuild: ## Build Docker containers
	@echo "$(YELLOW)Building Docker containers...$(NC)"
	docker compose -f $(COMPOSE_FILE) build --no-cache

dbuild-quick: ## Build Docker containers (with cache)
	@echo "$(YELLOW)Building Docker containers (quick)...$(NC)"
	docker compose -f $(COMPOSE_FILE) build

up: ## Start Docker containers
	@echo "$(YELLOW)Starting Docker containers...$(NC)"
	docker compose -f $(COMPOSE_FILE) up -d
	@echo "$(GREEN)Containers started!$(NC)"
	@make ps

down: ## Stop Docker containers
	@echo "$(YELLOW)Stopping Docker containers...$(NC)"
	docker compose -f $(COMPOSE_FILE) down
	@echo "$(GREEN)Containers stopped!$(NC)"

down-v: ## Stop and remove all containers with volumes
	@echo "$(YELLOW)Stopping containers and removing volumes...$(NC)"
	docker compose -f $(COMPOSE_FILE) down -v
	@echo "$(GREEN)Containers and volumes removed!$(NC)"

restart: ## Restart Docker containers
	@echo "$(YELLOW)Restarting containers...$(NC)"
	@make down
	@make up

logs: ## Show container logs (use CONTAINER=name for specific container)
	@docker compose -f $(COMPOSE_FILE) logs -f $(CONTAINER)

logs-store: ## Show store container logs
	@docker compose -f $(COMPOSE_FILE) logs -f store

logs-nginx: ## Show nginx container logs
	@docker compose -f $(COMPOSE_FILE) logs -f nginx

logs-node: ## Show node container logs
	@docker compose -f $(COMPOSE_FILE) logs -f node

shell: ## Access store container shell
	@docker compose -f $(COMPOSE_FILE) exec store sh

shell-root: ## Access store container shell as root
	@docker compose -f $(COMPOSE_FILE) exec -u root store sh

shell-node: ## Access node container shell
	@docker compose -f $(COMPOSE_FILE) exec node sh

composer-install: ## Install Composer dependencies
	@echo "$(YELLOW)Installing Composer dependencies...$(NC)"
	docker compose -f $(COMPOSE_FILE) exec store composer install --optimize-autoloader
	@echo "$(GREEN)Composer dependencies installed!$(NC)"

composer-update: ## Update Composer dependencies
	@echo "$(YELLOW)Updating Composer dependencies...$(NC)"
	docker compose -f $(COMPOSE_FILE) exec store composer update
	@echo "$(GREEN)Composer dependencies updated!$(NC)"

composer: ## Run Composer command (use CMD="command" syntax)
	@docker compose -f $(COMPOSE_FILE) exec store composer $(CMD)

npm-install: ## Install NPM dependencies
	@echo "$(YELLOW)Installing NPM dependencies (this may take 2-3 minutes)...$(NC)"
	@docker compose -f $(COMPOSE_FILE) exec node npm install --prefer-offline --no-audit
	@echo "$(GREEN)NPM dependencies installed!$(NC)"

npm-update: ## Update NPM dependencies
	@echo "$(YELLOW)Updating NPM dependencies...$(NC)"
	@docker compose -f $(COMPOSE_FILE) exec node npm update
	@echo "$(GREEN)NPM dependencies updated!$(NC)"

npm: ## Run NPM command (use CMD="command" syntax)
	@docker compose -f $(COMPOSE_FILE) exec node npm $(CMD)

livewire-install: ## Install Livewire
	@echo "$(YELLOW)Installing Livewire...$(NC)"
	docker compose -f $(COMPOSE_FILE) exec store composer require livewire/livewire
	docker compose -f $(COMPOSE_FILE) exec store php artisan livewire:publish --assets
	@echo "$(GREEN)Livewire installed and assets published!$(NC)"

start-vite: ## Start Vite dev server
	@echo "$(YELLOW)Starting Vite dev server...$(NC)"
	@docker compose -f $(COMPOSE_FILE) exec -d node npm run dev
	@echo "$(GREEN)Vite dev server started in background!$(NC)"
	@echo "$(BLUE)Check logs with: make logs-node$(NC)"

stop-vite: ## Stop Vite dev server
	@echo "$(YELLOW)Stopping Vite dev server...$(NC)"
	@docker compose -f $(COMPOSE_FILE) exec node pkill -f vite || true
	@echo "$(GREEN)Vite dev server stopped!$(NC)"

restart-vite: ## Restart Vite dev server
	@make stop-vite
	@sleep 2
	@make start-vite

artisan: ## Run Artisan command (use CMD="command" syntax)
	@docker compose -f $(COMPOSE_FILE) exec store php artisan $(CMD)

key-generate: ## Generate application key
	@echo "$(YELLOW)Generating application key...$(NC)"
	docker compose -f $(COMPOSE_FILE) exec store php artisan key:generate
	@echo "$(GREEN)Application key generated!$(NC)"

migrate: ## Run database migrations
	@echo "$(YELLOW)Running database migrations...$(NC)"
	docker compose -f $(COMPOSE_FILE) exec store php artisan migrate
	@echo "$(GREEN)Migrations complete!$(NC)"

migrate-fresh: ## Fresh migration with seed
	@echo "$(RED)WARNING: This will drop all tables!$(NC)"
	@echo "$(YELLOW)Running fresh migrations with seed...$(NC)"
	docker compose -f $(COMPOSE_FILE) exec store php artisan migrate:fresh --seed
	@echo "$(GREEN)Fresh migrations complete!$(NC)"

migrate-rollback: ## Rollback last migration
	@echo "$(YELLOW)Rolling back last migration...$(NC)"
	docker compose -f $(COMPOSE_FILE) exec store php artisan migrate:rollback
	@echo "$(GREEN)Rollback complete!$(NC)"

seed: ## Seed the database
	@echo "$(YELLOW)Seeding database...$(NC)"
	docker compose -f $(COMPOSE_FILE) exec store php artisan db:seed
	@echo "$(GREEN)Database seeded!$(NC)"

cache-clear: ## Clear application cache
	@echo "$(YELLOW)Clearing cache...$(NC)"
	docker compose -f $(COMPOSE_FILE) exec store php artisan cache:clear
	docker compose -f $(COMPOSE_FILE) exec store php artisan config:clear
	docker compose -f $(COMPOSE_FILE) exec store php artisan route:clear
	docker compose -f $(COMPOSE_FILE) exec store php artisan view:clear
	@echo "$(GREEN)Cache cleared!$(NC)"

optimize: ## Optimize application
	@echo "$(YELLOW)Optimizing application...$(NC)"
	docker compose -f $(COMPOSE_FILE) exec store php artisan config:cache
	docker compose -f $(COMPOSE_FILE) exec store php artisan route:cache
	docker compose -f $(COMPOSE_FILE) exec store php artisan view:cache
	docker compose -f $(COMPOSE_FILE) exec store php artisan optimize
	@echo "$(GREEN)Application optimized!$(NC)"

pest-install: ## Install Pest testing framework (Pest 3.x)
	@echo "$(YELLOW)Installing Pest and Pest Laravel plugin...$(NC)"
	docker compose -f $(COMPOSE_FILE) exec store composer require pestphp/pest --dev --with-all-dependencies
	docker compose -f $(COMPOSE_FILE) exec store composer require pestphp/pest-plugin-laravel --dev
	@echo "$(YELLOW)Initializing Pest folder structure...$(NC)"
	docker compose -f $(COMPOSE_FILE) exec store ./vendor/bin/pest --init
	@echo "$(GREEN)Pest installed and initialized successfully!$(NC)"

test: ## Run tests with Pest
	@echo "$(YELLOW)Running tests with Pest...$(NC)"
	docker compose -f $(COMPOSE_FILE) exec store php artisan test
	@echo "$(GREEN)Tests complete!$(NC)"

test-coverage: ## Run tests with coverage
	@echo "$(YELLOW)Running tests with coverage...$(NC)"
	docker compose -f $(COMPOSE_FILE) exec store php artisan test --coverage --min=80
	@echo "$(GREEN)Tests with coverage complete!$(NC)"

test-filter: ## Run specific test (use FILTER="TestName")
	@docker compose -f $(COMPOSE_FILE) exec store php artisan test --filter=$(FILTER)

test-parallel: ## Run tests in parallel
	@echo "$(YELLOW)Running tests in parallel...$(NC)"
	docker compose -f $(COMPOSE_FILE) exec store php artisan test --parallel
	@echo "$(GREEN)Parallel tests complete!$(NC)"

storage-link: ## Create storage symbolic link
	@echo "$(YELLOW)Creating storage link...$(NC)"
	docker compose -f $(COMPOSE_FILE) exec store php artisan storage:link
	@echo "$(GREEN)Storage link created!$(NC)"

permissions: ## Fix storage and cache permissions
	@echo "$(YELLOW)Fixing permissions...$(NC)"
	@chmod +x scripts/setup-permissions.sh
	@./scripts/setup-permissions.sh
	@echo "$(GREEN)Permissions fixed!$(NC)"

volumes-list: ## List all project volumes
	@echo "$(YELLOW)Project volumes:$(NC)"
	@docker volume ls | grep infrastructure || echo "No volumes found"

volumes-prune: ## Remove unused volumes (careful!)
	@echo "$(RED)WARNING: This will remove ALL unused Docker volumes!$(NC)"
	@read -p "Are you sure? [y/N] " -n 1 -r; \
	echo; \
	if [[ $$REPLY =~ ^[Yy]$$ ]]; then \
		docker volume prune -f; \
		echo "$(GREEN)Unused volumes removed!$(NC)"; \
	fi

clean: ## Clean up containers, volumes, and cache
	@echo "$(RED)WARNING: This will remove all containers, volumes, and cached data!$(NC)"
	@read -p "Are you sure? [y/N] " -n 1 -r; \
	echo; \
	if [[ $$REPLY =~ ^[Yy]$$ ]]; then \
		echo "$(YELLOW)Cleaning up...$(NC)"; \
		docker compose -f $(COMPOSE_FILE) down -v; \
		rm -rf vendor node_modules; \
		rm -rf storage/logs/*.log; \
		echo "$(GREEN)Cleanup complete!$(NC)"; \
	fi

db-backup: ## Backup database (output: backup_YYYY-MM-DD_HH-MM-SS.sql)
	@echo "$(YELLOW)Creating database backup...$(NC)"
	@docker compose -f $(COMPOSE_FILE) exec -T postgres pg_dump -U store_user store_db > backup_$$(date +%Y-%m-%d_%H-%M-%S).sql
	@echo "$(GREEN)Backup created: backup_$$(date +%Y-%m-%d_%H-%M-%S).sql$(NC)"

db-restore: ## Restore database from backup (use FILE=backup.sql)
	@if [ -z "$(FILE)" ]; then \
		echo "$(RED)Error: Please specify FILE=backup.sql$(NC)"; \
		exit 1; \
	fi
	@echo "$(YELLOW)Restoring database from $(FILE)...$(NC)"
	@docker compose -f $(COMPOSE_FILE) exec -T postgres psql -U store_user store_db < $(FILE)
	@echo "$(GREEN)Database restored!$(NC)"

ps: ## Show running containers
	@docker compose -f $(COMPOSE_FILE) ps

stats: ## Show container resource usage
	@docker stats --no-stream

breeze-install: ## Install Laravel Breeze with React and TypeScript
	@echo "$(YELLOW)Installing Laravel Breeze...$(NC)"
	docker compose -f $(COMPOSE_FILE) exec store composer require laravel/breeze --dev
	docker compose -f $(COMPOSE_FILE) exec store php artisan breeze:install react --typescript
	docker compose -f $(COMPOSE_FILE) exec node npm install --legacy-peer-deps
	docker compose -f $(COMPOSE_FILE) exec node npm run build
	@echo "$(GREEN)Breeze with React and TypeScript installed successfully!$(NC)"

filament-install: ## Install Filament admin panel
	@echo "$(YELLOW)Installing Filament...$(NC)"
	docker compose -f $(COMPOSE_FILE) exec store composer require filament/filament:"^3.0"
	docker compose -f $(COMPOSE_FILE) exec store php artisan filament:install --panels
	@echo "$(GREEN)Filament installed successfully!$(NC)"
	@echo "$(BLUE)Create admin user with: make filament-user$(NC)"

filament-user: ## Create Filament admin user
	@docker compose -f $(COMPOSE_FILE) exec store php artisan make:filament-user

xdebug-enable: ## Enable Xdebug
	@echo "$(YELLOW)Enabling Xdebug...$(NC)"
	@docker compose -f $(COMPOSE_FILE) exec store sed -i 's/;zend_extension=xdebug/zend_extension=xdebug/' /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini || true
	@docker compose -f $(COMPOSE_FILE) restart store
	@echo "$(GREEN)Xdebug enabled!$(NC)"

xdebug-disable: ## Disable Xdebug (better performance)
	@echo "$(YELLOW)Disabling Xdebug...$(NC)"
	@docker compose -f $(COMPOSE_FILE) exec store sed -i 's/zend_extension=xdebug/;zend_extension=xdebug/' /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini || true
	@docker compose -f $(COMPOSE_FILE) restart store
	@echo "$(GREEN)Xdebug disabled!$(NC)"

xdebug-status: ## Check Xdebug status
	@docker compose -f $(COMPOSE_FILE) exec store php -v | grep -i xdebug || echo "Xdebug is not enabled"

mailpit-open: ## Open Mailpit in browser
	@echo "$(BLUE)Opening Mailpit...$(NC)"
	@xdg-open http://localhost:8025 2>/dev/null || open http://localhost:8025 2>/dev/null || echo "Please open http://localhost:8025 manually"

init-scripts: ## Make all scripts executable
	@echo "$(YELLOW)Making scripts executable...$(NC)"
	@chmod +x scripts/*.sh
	@echo "$(GREEN)Scripts are now executable!$(NC)"

typescript-check: ## Check TypeScript types
	@echo "$(YELLOW)Checking TypeScript types...$(NC)"
	@docker compose -f $(COMPOSE_FILE) exec node npm run type-check || true

lint: ## Run ESLint
	@echo "$(YELLOW)Running ESLint...$(NC)"
	@docker compose -f $(COMPOSE_FILE) exec node npm run lint

lint-fix: ## Fix ESLint issues
	@echo "$(YELLOW)Fixing ESLint issues...$(NC)"
	@docker compose -f $(COMPOSE_FILE) exec node npm run lint:fix

format: ## Format code with Prettier
	@echo "$(YELLOW)Formatting code...$(NC)"
	@docker compose -f $(COMPOSE_FILE) exec node npm run format

info: ## Show system information
	@echo "$(GREEN)============================================$(NC)"
	@echo "$(YELLOW)Docker Compose Version:$(NC)"
	@docker compose version
	@echo ""
	@echo "$(YELLOW)Container Status:$(NC)"
	@docker compose -f $(COMPOSE_FILE) ps
	@echo ""
	@echo "$(YELLOW)Access URLs:$(NC)"
	@echo "  $(BLUE)Application:$(NC)  https://vmmint22.local"
	@echo "  $(BLUE)Admin Panel:$(NC)  https://vmmint22.local/admin"
	@echo "  $(BLUE)Mailpit UI:$(NC)   http://localhost:8025"
	@echo "  $(BLUE)pgAdmin:$(NC)      http://localhost:5050"
	@echo "  $(BLUE)Vite Dev:$(NC)     http://localhost:5174"
	@echo "$(GREEN)============================================$(NC)"
