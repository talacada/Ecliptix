# Testing Architecture Plan — Ecliptix

Tento dokument slouží jako architektonický plán a metodická příručka pro zavedení testovací infrastruktury v projektu Ecliptix (Symfony 8 + API Platform 4 + PHP 8.4).

---

## 1. Architektonický koncept testů

Projekt bude využívat dvě striktně oddělené testovací sady (**Test Suites**):

1. **Unit Test Suite (`tests/Unit/`)**:
   - **Báze**: `PHPUnit\Framework\TestCase`
   - **Účel**: Bleskové ověření doménové logiky, entit, kalkulací a rozhodovacích procesů ve službách.
   - **Závislosti**: Žádné externí závislosti. Žádný start Symfony Kernelu, žádné volání DB. Externí závislosti jsou nahrazeny mocky (`createMock()`).
   - **Rychlost**: ~1–5 ms na test.

2. **Integration Test Suite (`tests/Integration/`)**:
   - **Báze**: `ApiPlatform\Symfony\Bundle\Test\ApiTestCase` / `Symfony\Bundle\FrameworkBundle\Test\KernelTestCase`
   - **Účel**: Ověření celých API endpointů, bezpečnosti (JWT), persistence v DB a integrity repozitářů.
   - **Závislosti**: Běží v testovacím prostředí (`APP_ENV=test`), startuje Symfony Kernel, pracuje s reálnou testovací DB (`app_test`) a využívá **Zenstruck Foundry** + `ResetDatabase`.
   - **Rychlost**: ~50–200 ms na test.

---

## 2. Tok dat (Data Flow)

### A. Tok v Unit testu
```
[Unit Test Method]
       │
       ▼
[Instanciace testované třídy (new) + Mocky závislostí]
       │
       ▼
[Volání metody s testovacími parametry]
       │
       ▼
[Assertion: Návratová hodnota / Změna stavu / Očekávaná výjimka]
```

### B. Tok v Integračním API testu
```
[Integration Test Method]
       │
       ▼
[ResetDatabase: DB transakce / čistý stav]
       │
       ▼
[Foundry: Vytvoření výchozích dat (např. CharacterFactory, ItemDefinitionStory)]
       │
       ▼
[ApiTestCase Client: HTTP Request (např. POST /api/auth/login)]
       │
       ▼
[Symfony Kernel → API Platform Router → Processor → Service → PostgreSQL (app_test)]
       │
       ▼
[HTTP Response: Status code assertion + JSON schema validation + DB verification]
```

---

## 3. Rozhraní a příkazy (CLI Contract)

Příkazy pro spouštění testů v Docker kontejneru:

```bash
# Spuštění všech testů (Unit + Integration)
make test
# nebo: docker compose exec app php bin/phpunit

# Spuštění pouze rychlých Unit testů
docker compose exec app php bin/phpunit --testsuite=unit

# Spuštění pouze Integračních / API testů
docker compose exec app php bin/phpunit --testsuite=integration

# Spuštění konkrétního testovacího souboru
docker compose exec app php bin/phpunit tests/Unit/Entity/Character/CharacterTest.php

# Filtrování podle názvu testu
docker compose exec app php bin/phpunit --filter=testSubtractGold
```

---

## 4. Konfigurační změny v infrastruktuře

### 1. `phpunit.dist.xml`
- Rozdělení na testovací sady `unit` a `integration`.
- Zachování `Zenstruck\Foundry\PHPUnit\FoundryExtension`.

### 2. `.env.test`
- Nastavení izolované testovací databáze (např. `DATABASE_URL="postgresql://app:DBpassword@db:5432/app_test?serverVersion=16&charset=utf8"`).

### 3. `Makefile`
- Doplnění zkrácených cílů:
  - `make test-unit`
  - `make test-integration`

---

## 5. Struktura adresářů a Scaffolding tříd

```
tests/
├── bootstrap.php
├── Unit/
│   ├── Entity/
│   │   └── Character/
│   │       └── CharacterTest.php
│   ├── Factory/
│   │   └── ItemDefinitionFactoryTest.php
│   └── Service/
│       ├── Auth/
│       │   └── AppearanceValidationServiceTest.php
│       ├── Inventory/
│       │   └── InventoryManagerTest.php
│       └── Item/
│           └── ItemFactoryTest.php
└── Integration/
    ├── Api/
    │   ├── Auth/
    │   │   ├── LoginApiTest.php
    │   │   └── RegisterApiTest.php
    │   ├── Character/
    │   │   └── CharacterApiTest.php
    │   └── Shop/
    │       └── ShopOfferBuyApiTest.php
    └── Repository/
        └── CharacterInventoryRepositoryTest.php
```

---

## 6. Class Scaffolding (Šablony tříd s pseudokódem)

### A. Unit Test — Entita bez závislostí
```php
namespace App\Tests\Unit\Entity\Character;

use App\Entity\Character\Character;
use PHPUnit\Framework\TestCase;

class CharacterTest extends TestCase
{
    public function testSubtractGoldDecreasesAmount(): void
    {
        // 1. Založ novou instanci Character
        // 2. Nastav počáteční stav zlata (např. 100)
        // 3. Zavolej subtractGold(30)
        // 4. Assert: zkontroluj, že hodnota je 70
    }

    public function testSubtractGoldThrowsExceptionWhenInsufficientFunds(): void
    {
        // 1. Založ Character s 50 zlatými
        // 2. Nastav očekávanou výjimku InvalidArgumentException
        // 3. Zavolej subtractGold(100)
    }

    public function testSubtractDiamondsDecreasesAmount(): void
    {
        // 1. Obdobně ověř odečítání diamantů
    }
}
```

### B. Unit Test — Servisa se závislostmi (s Mockem)
```php
namespace App\Tests\Unit\Service\Inventory;

use App\Entity\Character\Character;
use App\Entity\Item\Item;
use App\Repository\Character\CharacterInventoryRepository;
use App\Service\Inventory\InventoryManager;
use PHPUnit\Framework\TestCase;

class InventoryManagerTest extends TestCase
{
    public function testAddToBackpackThrowsExceptionWhenBackpackFull(): void
    {
        // 1. Vytvoř Mock pro CharacterInventoryRepository
        // 2. Nakonfiguruj Mock: metoda getUnequippedItems vrátí pole o délce rovné kapacitě batohu
        // 3. Vytvoř instanci InventoryManager s předaným Mockem
        // 4. Nastav očekávanou výjimku Exception ('Not enough backpack space')
        // 5. Zavolej addToBackpack()
    }

    public function testAddToBackpackIncreasesQuantityForExistingElixir(): void
    {
        // 1. Vytvoř Mock pro CharacterInventoryRepository
        // 2. Nakonfiguruj Mock: getByDefinition vrátí existující instanci CharacterInventory (elixír)
        // 3. Zavolej addToBackpack() s elixírem
        // 4. Assert: zkontroluj, že stávajícímu stacku se zvedla quantity o 1
    }
}
```

### C. Integrační API Test — API Platform s JWT a Foundry
```php
namespace App\Tests\Integration\Api\Auth;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use Zenstruck\Foundry\Test\ResetDatabase;

class LoginApiTest extends ApiTestCase
{
    use ResetDatabase;

    public function testLoginReturnsJwtToken(): void
    {
        // 1. Pomocí Foundry (např. CharacterFactory / Story) vytvoř uživatele se známým emailem a heslem
        // 2. Vytvoř HTTP klienta: static::createClient()
        // 3. Pošli POST požadavek na '/api/auth/login' s přihlašovacími údaji v JSON těle
        // 4. Assert: ověř HTTP status 200 OK
        // 5. Assert: ověř přítomnost JWT tokenu v odpovědi
    }

    public function testLoginFailsWithInvalidCredentials(): void
    {
        // 1. Pošli POST na '/api/auth/login' se špatným heslem
        // 2. Assert: ověř HTTP status 401 Unauthorized
    }
}
```

---

## 7. Rozhodnutí a Trade-offy (Decisions & Trade-offs)

1. **Zenstruck Foundry vs. Doctrine Fixtures**:
   - *Volba*: Zenstruck Foundry.
   - *Důvod*: Umožňuje vytvářet konkrétní testovací stav přímo uvnitř testu (`CharacterFactory::createOne(['gold' => 500])`) namísto obřích statických fixture souborů, které se špatně udržují.

2. **Dvě testovací sady (Unit + Integration) vs. Jedna plochá složka**:
   - *Volba*: Striktní rozdělení na `tests/Unit` a `tests/Integration`.
   - *Důvod*: Vývojář může spouštět unit testy neustále bez čekání na databázi a kernel. Integrační testy běží separátně a spolehlivě ověřují celistvost API.

3. **Izolovaná testovací DB (`app_test`)**:
   - *Volba*: Samostatná databáze pro testy namísto sdílení vývojové DB.
   - *Důvod*: Spuštění testů nikdy nesmaže ani neovlivní data, která vývojář používá při manuálním hraní/vývoji v `APP_ENV=dev`.

---

## 8. Krok za krokem: Doporučený postup implementace

1. **Fáze 1 — Konfigurace prostředí**:
   - [ ] Upravit `phpunit.dist.xml` (přidat testsuites `unit` a `integration`).
   - [ ] Nastavit `DATABASE_URL` pro testovací prostředí v `.env.test`.
   - [ ] Doplnit cíle `test-unit` a `test-integration` do `Makefile`.
   - [ ] Vytvořit testovací DB v Postgresu (`php bin/console --env=test doctrine:database:create`).

2. **Fáze 2 — První Unit testy**:
   - [ ] Vytvořit `tests/Unit/Entity/Character/CharacterTest.php` (ekonomika, staty).
   - [ ] Vytvořit `tests/Unit/Factory/ItemDefinitionFactoryTest.php` (výpočty cen a statů).
   - [ ] Vytvořit `tests/Unit/Service/Inventory/InventoryManagerTest.php` (pravidla batohu a stackování).
   - [ ] Ověřit průchod přes `make test-unit`.

3. **Fáze 3 — První Integrační API testy**:
   - [ ] Vytvořit `tests/Integration/Api/Auth/LoginApiTest.php`.
   - [ ] Vytvořit `tests/Integration/Api/Auth/RegisterApiTest.php`.
   - [ ] Ověřit průchod přes `make test-integration`.
