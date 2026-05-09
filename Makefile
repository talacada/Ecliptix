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
	@test "$(CONFIRM)" = "1" || (echo 'Refusing to drop DB. Run: make db-drop CONFIRM=1' && exit 1)
	php bin/console doctrine:database:drop --force

.PHONY: migration
migration:
	php bin/console make:migration

.PHONY: migrate
migrate:
	php bin/console doctrine:migrations:migrate --no-interaction
