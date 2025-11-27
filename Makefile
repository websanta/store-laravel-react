# ==============================
# Makefile — Laravel + React + Docker
# ==============================

# ---------------------------------------------------------
# Переменные
# ---------------------------------------------------------
COMPOSE=docker-compose -f infrastructure/docker/docker-compose.yml
APP_CONTAINER=store_backend
FRONTEND_CONTAINER=store_frontend
NGINX_CONTAINER=store_nginx

# ---------------------------------------------------------
# Инфраструктура
# ---------------------------------------------------------
up: ## Запустить все контейнеры (dev)
	@echo "🚀 Поднимаем все контейнеры..."
	$(COMPOSE) up -d --build

down: ## Остановить все контейнеры
	@echo "🛑 Останавливаем контейнеры..."
	$(COMPOSE) down

restart: down up ## Перезапустить все контейнеры

logs: ## Смотреть логи всех контейнеров
	$(COMPOSE) logs -f

ps: ## Список работающих контейнеров
	$(COMPOSE) ps

# ---------------------------------------------------------
# Backend
# ---------------------------------------------------------
bash-backend: ## Открыть bash в контейнере backend
	$(COMPOSE) exec $(APP_CONTAINER) bash

composer-install: ## Установить PHP-зависимости
	$(COMPOSE) exec $(APP_CONTAINER) composer install --no-interaction --prefer-dist

artisan-%: ## Выполнить artisan команду, например: make artisan-migrate
	$(COMPOSE) exec $(APP_CONTAINER) php artisan $*

migrate: ## Запустить миграции
	$(COMPOSE) exec $(APP_CONTAINER) php artisan migrate

seed: ## Заполнить базу данными
	$(COMPOSE) exec $(APP_CONTAINER) php artisan db:seed

test: ## Запустить PHPUnit тесты
	$(COMPOSE) exec $(APP_CONTAINER) ./vendor/bin/phpunit

# ---------------------------------------------------------
# Frontend
# ---------------------------------------------------------
bash-frontend: ## Открыть bash в контейнере frontend
	$(COMPOSE) exec $(FRONTEND_CONTAINER) sh

npm-install: ## Установить frontend зависимости
	$(COMPOSE) exec $(FRONTEND_CONTAINER) npm install

frontend-dev: ## Запустить Vite dev server
	$(COMPOSE) exec $(FRONTEND_CONTAINER) npm run dev

frontend-build: ## Собрать frontend для продакшена
	$(COMPOSE) exec $(FRONTEND_CONTAINER) npm run build

# ---------------------------------------------------------
# Nginx
# ---------------------------------------------------------
bash-nginx: ## Открыть bash в контейнере nginx
	$(COMPOSE) exec $(NGINX_CONTAINER) sh

# ---------------------------------------------------------
# Общие утилиты
# ---------------------------------------------------------
fix-permissions: ## Исправить права на Laravel storage/cache
	$(COMPOSE) exec $(APP_CONTAINER) chown -R www-data:www-data /var/www/backend/storage /var/www/backend/bootstrap/cache

fresh: ## Сбросить базу, мигрировать и запустить seed
	$(COMPOSE) exec $(APP_CONTAINER) php artisan migrate:fresh --seed

build: ## Собрать все образы (backend + frontend + nginx)
	$(COMPOSE) build --no-cache

# ---------------------------------------------------------
# Help
# ---------------------------------------------------------
help: ## Показать все команды
	@echo "Makefile — доступные команды:"
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {p*]()
