# CLAUDE.md

Ecliptix — výukový projekt Symfony 8 + API Platform pro browser RPG hru inspirovanou Shakes & Fidget.

## Build & Test

```bash
# Build a spuštění (host)
make build

# Testy (host — všechny)
make test

# Testy (kontejner — konkrétní)
docker compose exec --user $(id -u):$(id -g) app php bin/phpunit --filter=testName
docker compose exec --user $(id -u):$(id -g) app php bin/phpunit tests/SomeTest.php

# Migrace
make migration                 # vygenerovat
make migrate                   # spustit

# Ostatní
make bash                      # shell v kontejneru
make cc                        # cache:clear
```

## Architektura

```
HTTP Request → ApiResource/Input DTO → Processor (validace, business logika) → Entity → Persistence → Normalizace (Groups) → HTTP Response
```

- **`src/Entity/`** — Doctrine entity s `#[ApiResource]` atributy (např. `Character.php`)
- **`src/State/Processor/`** — procesory pro vstupní DTO a business logiku
- **`src/State/Provider/`** — custom providery (pokud je potřeba)
- **`src/ApiResource/`** — vstupní DTO pro custom operace (např. `RegisterInput.php`)
- **`src/Repository/`** — Doctrine repository
- **`config/packages/`** — konfigurace frameworku (Doctrine, Security, API Platform)

### Klíčové entity

- **Character** — hráčská postava, implementuje `PasswordAuthenticatedUserInterface`. Staty: level, health, damage, gold, diamonds, experience. Vazba 1:N na `ShopRotation`.
- **ShopRotation** — časově omezené shop nabídky navázané na postavu.

### Autentizace (JWT)

- `POST /api/auth/register` — registrace (`RegisterProcessor`), vytvoří Character
- `POST /api/auth/login` — přihlášení (`LoginProcessor`), vrací JWT token
- `Authorization: Bearer <token>` hlavička pro chráněné endpointy
- Email = primární identifikátor, username = herní jméno (lze měnit)

## Konvence

- **Entity**: `#[Groups(['entity:read', 'entity:write'])]` pro serializaci, statické konstanty `READ_GROUP` / `WRITE_GROUP`
- **Procesory**: `src/State/Processor/[Operace]Processor.php`, vrací entitu nebo DTO
- **DTO**: `src/ApiResource/[Operace]Input.php`
- **Repository**: `[Entity]Repository.php`
- **DI**: constructor injection, vše v `src/` je autowired
- **Entity vlastnosti**: výchozí hodnoty v konstruktoru (`$this->gold = 0`), `ArrayCollection` pro One-to-Many

## Docker

- **App**: PHP 8.4 + FrankenPHP/Caddy na `https://localhost:8443`
- **DB**: PostgreSQL na `127.0.0.1:5432` (user: `app`, pass: `DBpassword`, db: `app`)
- Příkazy zapisující do filesystému (migrace, entity) pouštět s `--user $(id -u):$(id -g)`

## Pravidla chování agenta

### Přímá implementace (Symfony infrastruktura)
Konfigurace, routing, DI, security, migrace, cache — **agent implementuje přímo**.

### Mentoring mód (entity, doménový model, API business logika)
Při práci s entitami, `ApiResource` návrhem a doménovou logikou agent **nepíše hotové řešení**:
- vysvětlí postup krok za krokem
- navrhne varianty a trade-offy
- dá skeleton/částečný příklad
- nechá prostor pro dokončení uživatelem

### Doménový kontext

RPG hra, MVP scope:
- hráčská postava se staty
- měny (`gold`, `diamonds`)
- definice itemů a vlastněné instance itemů
- fight flow, shop flow

Základní use-cases: zobrazit postavu/staty, inventář, shop nabídku, koupit item, provést fight, připsat odměnu.

Preferovat **malé vertikální slice** — nejdřív jednoduchý funkční průřez, pak rozšiřování. Vyhýbat se předčasným abstrakcím (např. obecná `Currency` entita bez jasného use-case).

### Code review

- Aktivně doporučovat aktuální best practices pro Symfony, PHP, API Platform
- Upozorňovat na: architektonická rizika, slabou testovatelnost, bezpečnostní problémy, výkonové dopady
- U entit/ApiResource dávat návrhy zlepšení, ale respektovat mentoring mód

### Styl odpovědí

- Nejprve **co a proč**, potom jak
- U složitějších témat malé kroky
- Uvádět doporučený postup + alternativu
- U změn popsat dopad na runtime, konfiguraci a testy

### Bezpečné hranice

- Neměnit soubory mimo scope úkolu
- Nedělat velké refaktory bez zadání
- Před destruktivními zásahy vždy upozornit
- Nové soubory rovnou `git add`