COMPOSE = docker compose
PHP = $(COMPOSE) run --rm php-cli
NODE = $(COMPOSE) run --rm node

.PHONY: build up down destroy shell php-shell node-shell composer-install composer-update migrate seed demo-seed test lint fix front-build

build:
	$(COMPOSE) build
	$(COMPOSE) run --rm php-cli /usr/bin/composer install

up:
	$(COMPOSE) up -d --wait nginx php-fpm mysql valkey
	$(PHP) artisan migrate --force
	$(PHP) artisan db:seed --force

down:
	$(COMPOSE) down

destroy:
	$(COMPOSE) down -v --remove-orphans

shell: php-shell

php-shell:
	$(COMPOSE) run --rm --entrypoint bash php-cli

node-shell:
	$(COMPOSE) run --rm --entrypoint sh node

composer-install:
	$(COMPOSE) run --rm php-cli /usr/bin/composer install

composer-update:
	$(COMPOSE) run --rm php-cli /usr/bin/composer update

migrate:
	$(PHP) artisan migrate

seed:
	$(PHP) artisan db:seed

demo-seed:
	$(PHP) artisan meterpipe:demo:seed

test:
	$(PHP) artisan test

lint:
	$(COMPOSE) run --rm php-cli /usr/bin/composer validate --strict
	$(COMPOSE) run --rm php-cli /usr/bin/composer audit --ignore-unreachable
	$(PHP) -d memory_limit=1G vendor/bin/phpstan analyse
	$(PHP) vendor/bin/php-cs-fixer fix --dry-run --diff --sequential

fix:
	$(PHP) vendor/bin/php-cs-fixer fix

front-build:
	$(NODE) npm install
	$(NODE) npm run build
