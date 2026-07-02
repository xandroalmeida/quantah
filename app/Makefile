# Atalhos de desenvolvimento do Quantah (Laravel Sail).
# O comando principal é `make up`: de um clone limpo, sobe app + Postgres + seed.
SHELL := /bin/bash
SAIL  := ./vendor/bin/sail

.DEFAULT_GOAL := help
.PHONY: help up down restart test e2e build fresh seed shell

help: ## Lista os comandos
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | \
	  awk 'BEGIN{FS=":.*?## "}{printf "  \033[36m%-10s\033[0m %s\n", $$1, $$2}'

up: ## Clone limpo -> app + Postgres + seed, em um comando (http://localhost:8000)
	@test -f .env || cp .env.example .env
	@# bootstrap: instala o vendor (e o próprio Sail) sem depender de PHP local
	@test -x $(SAIL) || docker run --rm -v "$$(pwd)":/app -w /app composer:2 \
	  install --ignore-platform-reqs --no-scripts --no-interaction
	$(SAIL) up -d
	$(SAIL) composer install --no-interaction
	@grep -q '^APP_KEY=base64' .env || $(SAIL) artisan key:generate
	$(SAIL) npm install
	$(SAIL) npm run build
	$(SAIL) artisan migrate --seed --force
	@echo ""
	@echo "  Quantah no ar -> http://localhost:8000  (usuário de seed: test@example.com)"

down: ## Derruba os containers
	$(SAIL) down

restart: ## Reinicia os containers
	$(SAIL) restart

test: ## Suíte unit + feature
	$(SAIL) artisan test

e2e: ## E2E em browser real (Laravel Dusk, via selenium)
	@test -f .env.dusk.local || { \
	  grep -vE '^(APP_URL|DUSK_DRIVER_URL|APP_ENV)=' .env > .env.dusk.local; \
	  printf 'APP_ENV=local\nAPP_URL=http://laravel.test\nDUSK_DRIVER_URL=http://selenium:4444/wd/hub\n' >> .env.dusk.local; \
	}
	$(SAIL) npm run build
	$(SAIL) artisan dusk

build: ## Build de assets de produção
	$(SAIL) npm run build

fresh: ## Recria o banco do zero com seed
	$(SAIL) artisan migrate:fresh --seed

seed: ## Roda os seeders
	$(SAIL) artisan db:seed

shell: ## Shell no container da app
	$(SAIL) shell
