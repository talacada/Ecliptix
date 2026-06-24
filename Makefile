# FROM HOST
.PHONY: up
up:
	docker compose up -d

.PHONY: build
build:
	docker compose up -d --build

.PHONY: down
down:
	docker compose down

.PHONY: bash
bash:
	docker compose exec \
		-e HOME=/tmp \
		-e XDG_CONFIG_HOME=/tmp/.config \
		--user $(shell id -u):$(shell id -g) \
		app bash

# FROM CONTAINER
.PHONY: cc
cc:
	php bin/console cache:clear

.PHONY: test
test:
	php bin/phpunit

.PHONY: db-create
db-create:
	php bin/console doctrine:database:create --if-not-exists

.PHONY: db-drop
db-drop:
	php bin/console doctrine:database:drop --force

.PHONY: migration
migration:
	php bin/console make:migration

.PHONY: migrate
migrate:
	php bin/console doctrine:migrations:migrate --no-interaction

.PHONY: fixtures
fixtures:
	php bin/console foundry:load-fixtures

.PHONY: db-reset
db-reset:
	php bin/console doctrine:database:drop --force && \
	php bin/console doctrine:database:create --if-not-exists && \
	php bin/console foundry:load-fixtures --no-interaction && \
	php bin/console doctrine:migrations:sync-metadata-storage --no-interaction && \
	php bin/console doctrine:migrations:version --add --all --no-interaction
