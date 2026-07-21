# Ecliptix

[![PHP](https://img.shields.io/badge/PHP-8.4-777bb4?logo=php)](https://php.net)
[![Symfony](https://img.shields.io/badge/Symfony-8.0-black?logo=symfony)](https://symfony.com)
[![PHPStan](https://img.shields.io/badge/PHPStan-level%208-blue)](https://phpstan.org)
[![License](https://img.shields.io/badge/license-MIT-green)](LICENSE)

Backend for a browser RPG (inspired by Shakes & Fidget), built as a learning project to explore Symfony 8 and API Platform 4.

## Features

- **JWT authentication** — register, login, change password
- **Character** — stats (level, health, damage), currencies (gold, diamonds), prestige
- **Shop** — daily rotation with random equipment and elixir offers
- **Inventory** — equip, unequip, reorder backpack, sell items
- **Elixirs** — consumable buffs with duration, auto-cleanup of expired ones
- **Leaderboard** — search by username, center around rank, paginated results

## Tech Stack

| Area | Tool |
|------|------|
| Language | PHP 8.4 |
| Framework | Symfony 8.0 + API Platform 4 |
| Database | PostgreSQL 16 |
| Server | FrankenPHP (Caddy) |
| Auth | JWT (LexikJWTAuthenticationBundle) |
| QA | PHPStan level 8, PHP-CS-Fixer, GitHub Actions CI |
| Fixtures | Zenstruck Foundry |

## Architecture

HTTP requests flow through **DTO → Processor/Provider → Entity → Repository**, following a lightweight CQRS pattern:

- `src/ApiResource/` — input/output DTOs
- `src/State/Processor/` — write operations (registration, purchases, inventory changes)
- `src/State/Provider/` — read operations with custom query logic
- `src/Service/` — domain logic (item factory, inventory manager, rotation generator)

API documentation is available at `/docs` (Swagger/OpenAPI).

## Getting Started

```bash
make build
```

Open `https://localhost:8443`.

### HTTPS Certificate

The dev server uses a local Caddy/FrankenPHP certificate authority. On first run, export and trust the root certificate:

```bash
docker compose cp app:/data/caddy/pki/authorities/local/root.crt ./localhost-root.crt
```

Linux:

```bash
sudo cp ./localhost-root.crt /usr/local/share/ca-certificates/ecliptix-localhost.crt
sudo update-ca-certificates
```

Windows: open `localhost-root.crt` → Install Certificate → Local Machine → Trusted Root Certification Authorities.

### Running Commands

Run Symfony and Composer commands through the app container:

```bash
docker compose exec --user $(id -u):$(id -g) app composer install
make entity
make migrate
```

When writing to the project tree, always use `--user $(id -u):$(id -g)` — files created without it end up owned by `root` and become read-only from your editor.

Database credentials (local only):

```text
Host: 127.0.0.1:5432
User: app
Password: DBpassword
Database: app
```

### JWT Keys

JWT key files (`config/jwt/private.pem`, `config/jwt/public.pem`) are gitignored. Generate them on first setup:

```bash
make bash
php bin/console lexik:jwt:generate-keypair
```

Set the passphrase in your `.env.local`:

```env
JWT_PASSPHRASE=your_passphrase_here
```

API requests authenticate via `Authorization: Bearer <token>`.

## Quality Checks

```bash
make cs     # PHP-CS-Fixer (dry-run)
make stan   # PHPStan level 8
make test   # PHPUnit
make ready  # all three
```
