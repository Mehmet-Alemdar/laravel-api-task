.PHONY: help build up down restart logs shell test pint

help: ## Show this help message
	@echo 'Usage: make [target]'
	@echo ''
	@echo 'Targets:'
	@awk 'BEGIN {FS = ":.*?## "} /^[a-zA-Z_-]+:.*?## / {printf "  %-15s %s\n", $$1, $$2}' $(MAKEFILE_LIST)

build: ## Build the Docker containers
	docker-compose build

up: ## Start the application
	docker-compose up -d

down: ## Stop the application
	docker-compose down

restart: ## Restart the application
	docker-compose restart

logs: ## Show application logs
	docker-compose logs -f

shell: ## Access the application container shell
	docker-compose exec app bash

queue-shell: ## Access the queue worker container shell
	docker-compose exec queue-worker bash

db-shell: ## Access the database shell
	docker-compose exec db psql -U laravel_user -d laravel_api

test: ## Run tests
	docker-compose exec app php artisan test

pint: ## Run Laravel Pint for code formatting
	docker-compose exec app ./vendor/bin/pint

pint-test: ## Test Laravel Pint without making changes
	docker-compose exec app ./vendor/bin/pint --test

install: ## Install dependencies
	docker-compose exec app composer install

migrate: ## Run database migrations
	docker-compose exec app php artisan migrate

seed: ## Seed the database
	docker-compose exec app php artisan db:seed

fresh: ## Fresh migrate and seed
	docker-compose exec app php artisan migrate:fresh --seed

key: ## Generate application key
	docker-compose exec app php artisan key:generate

cache: ## Clear application cache
	docker-compose exec app php artisan cache:clear
	docker-compose exec app php artisan config:clear
	docker-compose exec app php artisan route:clear
	docker-compose exec app php artisan view:clear

optimize: ## Optimize application
	docker-compose exec app php artisan config:cache
	docker-compose exec app php artisan route:cache
	docker-compose exec app php artisan view:cache

queue-work: ## Start queue worker manually
	docker-compose exec queue-worker php artisan queue:work

queue-failed: ## Show failed jobs
	docker-compose exec app php artisan queue:failed

queue-retry: ## Retry failed jobs
	docker-compose exec app php artisan queue:retry all
