# Testing Architecture Plan — Ecliptix

This document serves as the architectural plan and testing guide for establishing the testing infrastructure in Ecliptix (Symfony 8 + API Platform 4 + PHP 8.4).

---

## 1. Architectural Concept of Tests

The project utilizes two strictly separated **Test Suites**:

1. **Unit Test Suite (`tests/Unit/`)**:
   - **Base**: `PHPUnit\Framework\TestCase`
   - **Purpose**: Lightning-fast verification of domain logic, entities, mathematical calculations, and decision-making processes in services.
   - **Dependencies**: No external dependencies. No Symfony Kernel boot, no database queries. External dependencies are replaced by test doubles (`createMock()`).
   - **Execution speed**: ~1–5 ms per test.

2. **Integration Test Suite (`tests/Integration/`)**:
   - **Base**: `ApiPlatform\Symfony\Bundle\Test\ApiTestCase` / `Symfony\Bundle\FrameworkBundle\Test\KernelTestCase`
   - **Purpose**: End-to-end verification of API endpoints, security (JWT), database persistence, and repository integrity.
   - **Dependencies**: Runs in the test environment (`APP_ENV=test`), boots Symfony Kernel, uses a real test database (`app_test`), and utilizes **Zenstruck Foundry** + `ResetDatabase`.
   - **Execution speed**: ~50–200 ms per test.

---

## 2. Data Flow

### A. Unit Test Flow
```
[Unit Test Method]
       │
       ▼
[Instantiate class under test (new) + Dependency Mocks]
       │
       ▼
[Execute target method with test arguments]
       │
       ▼
[Assertion: Return value / State mutation / Expected exception]
```

### B. Integration API Test Flow
```
[Integration Test Method]
       │
       ▼
[ResetDatabase: DB transaction / clean schema state]
       │
       ▼
[Foundry: Seed required test state (e.g. CharacterFactory, ItemDefinitionStory)]
       │
       ▼
[ApiTestCase Client: HTTP Request (e.g. POST /api/auth/login)]
       │
       ▼
[Symfony Kernel → API Platform Router → Processor → Service → PostgreSQL (app_test)]
       │
       ▼
[HTTP Response: Status code assertion + JSON schema validation + DB verification]
```

---

## 3. CLI Contract

Commands for running tests:

```bash
# Run all tests (Unit + Integration)
make test
# or: php bin/phpunit

# Run Unit tests only
make test-unit
# or: php bin/phpunit --testsuite=unit

# Run Integration / API tests only
make test-integration
# or: php bin/phpunit --testsuite=integration

# Run a specific test file
php bin/phpunit tests/Unit/Entity/Character/CharacterTest.php

# Filter by test method name
php bin/phpunit --filter=testSubtractGoldDecreasesAmount
```

---

## 4. Infrastructure & Configuration Changes

### 1. `phpunit.dist.xml`
- Split into `unit` and `integration` test suites.
- Register `Zenstruck\Foundry\PHPUnit\FoundryExtension`.

### 2. `.env.test`
- Configure isolated test database (e.g. `DATABASE_URL="postgresql://app:DBpassword@db:5432/app_test?serverVersion=16&charset=utf8"`).
- Set test `APP_SECRET` and test environment flags.

### 3. `Makefile`
- Added convenience targets:
  - `make test-unit`
  - `make test-integration`

---

## 5. Directory Structure & Class Scaffolding

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

## 6. Class Scaffolding (Templates with Pseudocode)

### A. Unit Test — Entity without dependencies
```php
namespace App\Tests\Unit\Entity\Character;

use App\Entity\Character\Character;
use PHPUnit\Framework\TestCase;

class CharacterTest extends TestCase
{
    public function testSubtractGoldDecreasesAmount(): void
    {
        // 1. Arrange: Create a new Character instance and set gold to 100
        // 2. Act: Call subtractGold(30)
        // 3. Assert: Verify getGold() returns 70
    }

    public function testSubtractGoldThrowsExceptionWhenInsufficientFunds(): void
    {
        // 1. Arrange: Create Character with 50 gold
        // 2. Expect Exception: InvalidArgumentException ('Not enough gold')
        // 3. Act: Call subtractGold(100)
    }

    public function testSubtractDiamondsDecreasesAmount(): void
    {
        // 1. Arrange & Act: Similar verification for diamond currency
    }
}
```

### B. Unit Test — Service with Dependencies (Mocks)
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
        // 1. Arrange: Create mock for CharacterInventoryRepository
        // 2. Configure mock: getUnequippedItems returns array with count >= backpackCapacity
        // 3. Create InventoryManager instance injecting the mock repository
        // 4. Expect Exception: Exception ('Not enough backpack space')
        // 5. Act: Call addToBackpack()
    }

    public function testAddToBackpackIncreasesQuantityForExistingElixir(): void
    {
        // 1. Arrange: Create mock for CharacterInventoryRepository
        // 2. Configure mock: getByDefinition returns existing CharacterInventory elixir stack
        // 3. Act: Call addToBackpack() with an elixir item
        // 4. Assert: Verify existing stack quantity increased by 1
    }
}
```

### C. Integration API Test — API Platform with JWT and Foundry
```php
namespace App\Tests\Integration\Api\Auth;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use Zenstruck\Foundry\Test\ResetDatabase;

class LoginApiTest extends ApiTestCase
{
    use ResetDatabase;

    public function testLoginReturnsJwtToken(): void
    {
        // 1. Arrange: Create user with known email and password using Foundry (CharacterFactory)
        // 2. Act: Send POST request to '/api/auth/login' with JSON payload via static::createClient()
        // 3. Assert: Verify HTTP status 200 OK
        // 4. Assert: Verify JSON response contains 'token'
    }

    public function testLoginFailsWithInvalidCredentials(): void
    {
        // 1. Act: Send POST request to '/api/auth/login' with invalid password
        // 2. Assert: Verify HTTP status 401 Unauthorized
    }
}
```

---

## 7. Architectural Decisions & Trade-offs

1. **Zenstruck Foundry vs. Static Fixtures**:
   - *Choice*: Zenstruck Foundry.
   - *Rationale*: Allows declaring explicit, test-specific state directly within each test method (`CharacterFactory::createOne(['gold' => 500])`) instead of relying on large static fixtures that are fragile and hard to maintain.

2. **Two Test Suites (Unit + Integration) vs. Single Flat Directory**:
   - *Choice*: Strict separation into `tests/Unit` and `tests/Integration`.
   - *Rationale*: Developers can continuously run fast unit tests without overhead. Integration tests run separately, providing comprehensive coverage of API contracts and persistence.

3. **Isolated Test Database (`app_test`)**:
   - *Choice*: Dedicated test database rather than sharing the development database.
   - *Rationale*: Running tests never purges or interferes with local development data in `APP_ENV=dev`.

4. **Foundry Factories for Entity Relations**:
   - *Choice*: Dedicated `CharacterFactory` handling non-nullable relations (`Race`, `AppearanceOption`).
   - *Rationale*: Guarantees valid entity creation in integration tests without boilerplate duplication.

---

## 8. Step-by-Step Implementation Roadmap

1. **Phase 1 — Environment & Infrastructure Configuration**:
   - [x] Configure `phpunit.dist.xml` (split into `unit` and `integration` test suites).
   - [x] Configure `DATABASE_URL` for test environment in `.env.test`.
   - [x] Add `test-unit` and `test-integration` targets to `Makefile`.
   - [ ] Create test DB in PostgreSQL (`php bin/console --env=test doctrine:database:create`).

2. **Phase 2 — Unit Tests**:
   - [ ] Implement `tests/Unit/Entity/Character/CharacterTest.php` (currency operations, stats, default invariants).
   - [ ] Implement `tests/Unit/Factory/ItemDefinitionFactoryTest.php` (stat formulas and pricing calculations).
   - [ ] Implement `tests/Unit/Service/Auth/AppearanceValidationServiceTest.php` (race and appearance option validation).
   - [ ] Implement `tests/Unit/Service/Inventory/InventoryManagerTest.php` (backpack capacity, elixir stacking).
   - [ ] Implement `tests/Unit/Service/Item/ItemFactoryTest.php` (bonus stat rolling).
   - [ ] Verify test suite passes with `make test-unit`.

3. **Phase 3 — Integration & API Tests**:
   - [ ] Create `CharacterFactory` (with default `Race` and `AppearanceOption` relations).
   - [ ] Implement `tests/Integration/Api/Auth/LoginApiTest.php`.
   - [ ] Implement `tests/Integration/Api/Auth/RegisterApiTest.php`.
   - [ ] Implement `tests/Integration/Api/Character/CharacterApiTest.php`.
   - [ ] Verify integration test suite passes with `make test-integration`.
