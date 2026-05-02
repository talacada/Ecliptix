# Ecliptix

## Development

Show available project shortcuts:

```bash
make
```

Start the Docker environment:

```bash
make build
```

Open the application at `https://localhost:8443`.

Run Symfony and Composer commands through the app container unless your local PHP is 8.4:

```bash
docker compose exec app composer install
make migrate
```

The database is exposed only on `127.0.0.1:5432` for local tools.

Database credentials:

```text
Network type: PostgreSQL (TCP/IP)
Hostname / IP: 127.0.0.1
Port: 5432
User: app
Password: DBpassword
Database: app
```
