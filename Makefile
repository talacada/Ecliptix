.PHONY: up
up: ## Start containers in the background.
	docker compose up -d

.PHONY: build
build: ## Build and start containers in the background.
	docker compose up -d --build

.PHONY: down
down: ## Stop and remove containers.
	docker compose down

.PHONY: cc
cc: ## Clear Symfony cache.
	docker compose exec app php bin/console cache:clear

.PHONY: test
test: ## Run PHPUnit.
	docker compose exec app php bin/phpunit

.PHONY: db-create
db-create: ## Create the configured database if it does not exist.
	docker compose exec app php bin/console doctrine:database:create --if-not-exists

.PHONY: db-drop
db-drop: ## Drop the configured database. Requires CONFIRM=1.
	@test "$(CONFIRM)" = "1" || (echo 'Refusing to drop DB. Run: make db-drop CONFIRM=1' && exit 1)
	docker compose exec app php bin/console doctrine:database:drop --force

.PHONY: migration
migration: ## Generate a Doctrine migration from mapping changes.
	docker compose exec app php bin/console make:migration

.PHONY: migrate
migrate: ## Run pending Doctrine migrations.
	docker compose exec app php bin/console doctrine:migrations:migrate --no-interaction
