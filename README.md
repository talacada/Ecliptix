# Ecliptix

## Development

Start the Docker environment:

```bash
docker compose up -d --build
```

Open the application at `https://localhost:8443`.

Run Symfony and Composer commands through the app container unless your local PHP is 8.4:

```bash
docker compose exec app php bin/console about
docker compose exec app composer install
docker compose exec app php bin/console doctrine:migrations:migrate
```

The database is exposed only on `127.0.0.1:5432` for local tools.
