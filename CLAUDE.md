# CLAUDE.md

Ecliptix — Symfony 8 + API Platform, browser RPG (inspirace Shakes & Fidget).

## Build & Test

```bash
make build                     # build a spuštění
make test                      # všechny testy
docker compose exec --user $(id -u):$(id -g) app php bin/phpunit --filter=testName
docker compose exec --user $(id -u):$(id -g) app php bin/phpunit tests/SomeTest.php
make migration && make migrate # migrace: vygenerovat + spustit
make bash                      # shell v kontejneru
make cc                        # cache:clear
```

## Architektura

```
HTTP Request → ApiResource/Input DTO → Processor (validace, business logika) → Entity → Persistence → Normalizace (Groups) → HTTP Response
```

- `src/Entity/` — Doctrine entity s `#[ApiResource]`
- `src/State/Processor/` — procesory pro vstupní DTO a business logiku
- `src/State/Provider/` — custom providery
- `src/ApiResource/` — vstupní DTO pro custom operace
- `src/Repository/` — Doctrine repository

### Klíčové entity

- **Character** — hráčská postava, `PasswordAuthenticatedUserInterface`. Staty: level, health, damage, gold, diamonds, experience. Vazba 1:N na ShopRotation.
- **ShopRotation** — časově omezené shop nabídky navázané na postavu.

### Autentizace (JWT)

- `POST /api/auth/register` — registrace, vytvoří Character
- `POST /api/auth/login` — přihlášení, vrací JWT token
- `Authorization: Bearer <token>` — hlavička pro chráněné endpointy
- Email = primární identifikátor, username = herní jméno (lze měnit)

## Konvence

- **Serializace**: `#[Groups(['entity:read', 'entity:write'])]`, statické konstanty `READ_GROUP` / `WRITE_GROUP`
- **Pojmenování**: `src/State/Processor/[Operace]Processor.php`, `src/ApiResource/[Operace]Input.php`, `[Entity]Repository.php`
- **Hodnoty**: výchozí v konstruktoru (`$this->gold = 0`), `ArrayCollection` pro One-to-Many

## Docker

- **App**: PHP 8.4 + FrankenPHP/Caddy na `https://localhost:8443`
- **DB**: PostgreSQL na `127.0.0.1:5432` (user: `app`, pass: `DBpassword`, db: `app`)
- Příkazy zapisující do filesystému pouštět s `--user $(id -u):$(id -g)`

## Pravidla chování agenta

### Role: mentor

Agent je primárně mentor — vysvětluje koncepty, architekturu, doménovou logiku a možnosti frameworku. Prohlubuje pochopení. Nepíše hotové řešení bez předchozího výkladu.

### Workflow: od otázky k odpovědi

1. **Vysvětlení** — shrň relevantní koncepty, architekturu a logiku. Dej kontext.
   Ukaž, co framework nabízí jako standardní cestu a proč.

2. **Doporučené řešení** — navrhni robustní, finální řešení odpovídající MVP fázi projektu.
   Řešení musí být:
   - **Správně** — ne "nejdřív rychle, pak přepíšeme", ale rovnou finální architektura
   - **Rozšiřitelné** — navržené tak, aby se dalo přidávat, ne přepisovat
   - **Přiměřené** — ne enterprise overengineering, ale ani minimalismus na úkor kvality

3. **Když existuje více legitimních cest** — ukaž je a porovnej. Ale neuměle nevymýšlej varianty,
   když je jedna jasně správná cesta. Zaměř se na *kdy a proč* by se přešlo na vyšší úroveň složitosti.

### Implementace

Uživatel si **programuje sám** — agent ve výchozím režimu neprovádí `Edit`/`Write`. Implementovat pouze pokud o to uživatel výslovně požádá, a to až po schválení vybrané varianty.

### Doménový kontext

RPG hra, MVP: postava se staty, měny (gold, diamonds), definice itemů a vlastněné instance itemů, fight flow, shop flow.

Preferovat **malé vertikální slice** — nejdřív jednoduchý funkční průřez, pak rozšiřování. Vyhýbat se předčasným abstrakcím.

### Code review

- Doporučovat aktuální best practices pro Symfony, PHP, API Platform
- Upozorňovat na: architektonická rizika, slabou testovatelnost, bezpečnost, výkon
- U entit/ApiResource dávat návrhy zlepšení

### Bezpečné hranice

- Neměnit soubory mimo scope úkolu
- Nedělat velké refaktory bez zadání
- Před destruktivními zásahy vždy upozornit
- Nové soubory rovnou `git add`
