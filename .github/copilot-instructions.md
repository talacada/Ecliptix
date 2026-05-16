# Copilot Instructions for Ecliptix

This document provides guidance for AI assistants working on the Ecliptix project—a Symfony 8 + API Platform learning application for building a browser RPG game inspired by Shakes & Fidget.

## Build & Test Commands

### Running Tests
```bash
# All tests
make test

# Single test (run from container)
docker compose exec --user $(id -u):$(id -g) app php bin/phpunit tests/SomeTest.php

# Run tests with a filter
docker compose exec --user $(id -u):$(id -g) app php bin/phpunit --filter=testName
```

### Building & Migrations
```bash
# Initial build with Docker
make build

# Create/regenerate migration
make migration

# Run pending migrations
make migrate

# Generate entity stubs via Maker
make entity

# Clear cache
make cc
```

### Development Shell
```bash
make bash
```

All `make` targets support running from the host. Targets that modify files (migrations, entities) should be prefixed with `docker compose exec --user $(id -u):$(id -g) app` to avoid permission issues on bind mounts.

## Architecture Overview

### Project Structure
- **`src/Entity/`** — Doctrine ORM entities with API Platform `ApiResource` attributes (e.g., `Character.php`)
- **`src/State/Processor/`** — Input/output processors for data transformation and business logic (e.g., `RegisterProcessor.php`)
- **`src/ApiResource/`** — Input DTOs for custom API operations (e.g., `RegisterInput.php`)
- **`src/Repository/`** — Doctrine repositories for queries
- **`src/Controller/`** — Custom HTTP controllers (if needed; prefer API Platform operations)
- **`config/packages/`** — Framework configuration (Doctrine, Security, API Platform, etc.)
- **`migrations/`** — Doctrine migration files
- **`tests/`** — PHPUnit tests

### API Design Pattern
This project uses **API Platform** for declarative REST APIs:

1. **Entities define the data model** via Doctrine attributes (`#[ORM\*]`)
2. **API operations are declared** via `#[ApiResource(...)]` with custom operations
3. **Serialization groups** control what fields are exposed (`#[Groups(['read', 'write'])]`)
4. **Processors handle business logic** when input DTOs don't map directly to entities
5. **State providers** can override default entity loading if needed

### Data Flow Example
```
HTTP Request (POST /auth/register with RegisterInput)
  → RegisterProcessor (validates, creates Character, hashes password)
  → Character entity persisted
  → Normalized response via Groups (READ_GROUP)
  → HTTP Response
```

### Key Entities
- **`Character`** — Player character with stats (level, gold, diamonds, health, damage) and shop inventory
  - Implements `PasswordAuthenticatedUserInterface`
  - Uses `READ_GROUP` for API serialization
  - Relationship: One-to-Many with `ShopRotation` (time-limited shop offers)

### Authentication (JWT)
- **Email** is the project-wide unique identifier (primary key for Security system)
- **Username** is secondary (game-specific, can be changed)
- **JWT tokens** are used for API authorization; sent via `Authorization: Bearer <token>` header
- Login operations:
  - `POST /api/auth/register` — Creates a new character (via `RegisterProcessor`)
  - `POST /api/auth/login` — Authenticates via email+password, returns JWT token (via `LoginProcessor`)
  - Protected endpoints require valid JWT in Authorization header
- Files: `src/ApiResource/Auth/LoginInput.php`, `src/State/Processor/LoginProcessor.php`
- Configuration: `config/packages/lexik_jwt_authentication.yaml`, `config/packages/security.yaml`

## Key Conventions

### API Platform & Serialization
- Use `#[Groups(['group:read', 'group:write'])]` on entity properties to control exposure
- Define static `const string READ_GROUP = 'entity:read'` for consistency
- Operations define `normalizationContext` (output) and `denormalizationContext` (input)
- Input DTOs go in `src/ApiResource/NameInput.php` and are processed by Processor classes

### Processors & Input Transformation
- Create processors in `src/State/Processor/[Name]Processor.php`
- Processors receive `ProcessorInterface` in type hints; use method `process($data, $operation, $uriVariables, $context)`
- Return the entity or DTO to be normalized back to JSON
- Always validate input before state changes; use Symfony `ValidatorInterface` or attribute-based constraints

### Entities & Relationships
- Use constructor defaults for aggregate values (e.g., `$this->gold = 0`)
- Use `ArrayCollection` for One-to-Many relationships
- Keep business logic that depends on multiple properties in entity methods (e.g., `getShopRotations()` filters by date)
- Include JSDoc for collections: `@var Collection<int, TargetEntity>`

### Naming & Patterns
- Entity filenames match class names: `Character.php`, `ShopRotation.php`
- Repository names: `[Entity]Repository.php`
- Processor names: `[Operation]Processor.php` (e.g., `RegisterProcessor.php`)
- DTO/Input names: `[Operation]Input.php` (e.g., `RegisterInput.php`)

### Dependency Injection
- Services are autowired and autoconfigured in `config/services.yaml`
- All classes in `src/` are auto-registered as services
- Constructor injection is the primary pattern; `ValidatorInterface`, `EntityManagerInterface`, repositories, etc. are available

### Testing
- Test files go in `tests/` mirroring `src/` structure
- PHPUnit runs with `APP_ENV=test` and strict mode (deprecations, notices, warnings fail)
- Bootstrap includes `tests/bootstrap.php` for test setup
- Use the container to fetch services; no mocking framework preconfigured

## Mentoring Approach

### When working on Domain Model or Entities
- This is a **learning project**. When asked to build or refactor domain entities or API resources, **explain the approach and trade-offs first** rather than generating the full solution
- Offer a skeleton or partial example, then guide the user to implementation
- Highlight conceptual differences (data model vs. API contract vs. application logic) when relevant
- Prefer small, vertical slices (one use-case at a time) over premature generalization

### When working on Symfony Infrastructure
- Configuration, routing, DI, security, migrations, cache, etc.
- **Make changes directly**. Infrastructure decisions don't require step-by-step mentoring

### Code Review & Best Practices
- Actively recommend modern Symfony and API Platform best practices
- Flag architectural risks, testability issues, security concerns, or performance impacts
- For entities and API operations, suggest improvements but respect the mentoring mode

## Project Context

**Ecliptix** is a learning project building a **browser RPG game**. Current MVP scope includes:
- Character creation and profile
- Character stats (level, health, damage, gold, diamonds, experience)
- Shop system with time-limited rotations
- Basic item definitions and player inventory
- Fight mechanics (not yet implemented)

Keep designs minimal and focused on vertical slices. Avoid over-engineering domain models with generic abstractions unless there's a clear, current use-case.

## Docker & Local Development

### Container Environment
- **Database**: PostgreSQL on `127.0.0.1:5432`
  - User: `app`, Password: `DBpassword`, Database: `app`
- **App**: PHP 8.4 + FrankenPHP with Caddy on `https://localhost:8443`
- **Local HTTPS**: Uses local CA certificate; import `localhost-root.crt` if `ERR_CERT_AUTHORITY_INVALID` appears

### File Permissions
When running Docker commands that write to the filesystem (migrations, entity generation), use:
```bash
docker compose exec --user $(id -u):$(id -g) app <command>
```
This ensures files are owned by your user, not root.

## Resources
- **Symfony 8 Docs**: https://symfony.com/doc/8.0
- **API Platform Docs**: https://api-platform.com/docs
- **Doctrine ORM**: https://www.doctrine-project.org
- **PHPUnit**: https://phpunit.readthedocs.io

See `AGENTS.md` for additional agent collaboration guidelines.
