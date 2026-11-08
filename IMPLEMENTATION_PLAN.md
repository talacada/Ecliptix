# Implementation Plan — Modular Architecture & Co-located Tests

This implementation plan outlines the restructuring of the **Ecliptix** codebase from a traditional layer-based directory layout (*Package by Layer*) to a feature-based modular layout (*Package by Feature / Screaming Architecture*), including **co-locating unit and integration tests** within their respective modules.

---

## 1. Scope & Core Constraints

- **Zero Business Logic Changes**: No methods, algorithms, validations, or flows are refactored in this branch. The code logic stays 100% identical to the existing implementation.
- **Pure Reorganization**: Focus exclusively on file structure, namespaces, imports (`use`), and Symfony/PHPUnit configuration.
- **Entity Model Integrity**: Doctrine entities remain DB-to-object data mappings (*Active Record / Data Model*); no rich aggregate domain behavior is introduced in this phase.
- **Continuous Verifiability**: Tests must be runnable and pass after each module migration.

---

## 2. API Contract Guarantee

All HTTP routes, payload schemas, query parameters, authentication mechanisms, and responses remain **100% backwards-compatible and unchanged**:

| Module | Operation | HTTP Method | Route | Input DTO / Entity | Output / Groups |
|---|---|---|---|---|---|
| **Auth** | Register | `POST` | `/api/auth/register` | `RegisterInput` | HTTP 201 |
| **Auth** | Login | `POST` | `/api/auth/login` | `LoginInput` | `LoginOutput` |
| **Auth** | Change Password | `POST` | `/api/auth/change-password` | `ChangePasswordInput` | HTTP 204 |
| **Auth** | Request Reset | `POST` | `/api/auth/request-password-reset`| `RequestPasswordResetInput` | HTTP 204 |
| **Auth** | Reset Password | `POST` | `/api/auth/password-reset` | `PasswordResetInput` | HTTP 204 |
| **Auth** | Verify Email | `GET` | `/api/auth/verify-email` | Query token | HTTP 200 |
| **Auth** | Register Options | `GET` | `/api/auth/register-options` | None | `RegisterOptionsResponse` |
| **Character**| Get Current | `GET` | `/api/character` | None | `character:read` |
| **Character**| Get Public | `GET` | `/api/character/{id}` | None | `character:public:read` |
| **Character**| Update | `PATCH` | `/api/character` | `update:read` | `character:read` |
| **Character**| Delete | `DELETE` | `/api/character` | None | HTTP 204 |
| **Character**| Inventory | `GET` | `/api/character/inventory` | None | `character:read` |
| **Character**| Active Elixirs| `GET` | `/api/character/elixirs` | None | `character:read` |
| **Character**| Friends Add | `POST` | `/api/character/friends` | DTO / Request | HTTP 200/201 |
| **Shop** | Get Rotation | `GET` | `/api/shop/rotation` | None | `shop:read` |
| **Shop** | Buy Offer | `POST` | `/api/shop/offer/{id}/buy` | None | `ItemViewDTO` |
| **Leaderboard**| List | `GET` | `/api/leaderboard` | Query params | `LeaderboardResponse` |

---

## 3. Architecture & Target Directory Structure

### Target Tree

```text
src/
├── Auth/
│   ├── ApiResource/                  # RegisterInput, LoginOutput, RegisterOptions...
│   ├── Entity/                       # EmailVerificationToken, PasswordResetToken...
│   ├── Factory/                      # EmailVerificationTokenFactory, PasswordResetTokenFactory
│   ├── Repository/                   # Token repositories
│   ├── Service/                      # EmailVerificationService, AppearanceValidationService
│   ├── State/                        # RegisterProcessor, LoginProcessor, RegisterOptionsProvider...
│   └── Tests/
│       ├── Unit/
│       │   ├── EmailVerificationServiceTest.php
│       │   └── AppearanceValidationServiceTest.php
│       └── Integration/
│           ├── RegisterApiTest.php
│           ├── LoginApiTest.php
│           ├── ChangePasswordApiTest.php
│           ├── PasswordResetApiTest.php
│           ├── RequestPasswordResetApiTest.php
│           ├── VerifyEmailApiTest.php
│           └── RegisterOptionsApiTest.php
│
├── Character/
│   ├── Entity/                       # Character, CharacterInventory, ActiveElixir, FriendRelation, Race, AppearanceOption
│   ├── Factory/                      # CharacterFactory, CharacterInventoryFactory, ActiveElixirFactory, RaceFactory...
│   ├── Repository/                   # CharacterRepository, CharacterInventoryRepository...
│   ├── Service/                      # ElixirCleanUp
│   ├── State/                        # MineCharacterProvider, PublicCharacterProvider, UpdateCharacterProcessor...
│   └── Tests/
│       ├── Unit/
│       │   ├── CharacterTest.php
│       │   └── ElixirCleanUpTest.php
│       └── Integration/
│           ├── CharacterApiTest.php
│           ├── CharacterInventoryApiTest.php
│           ├── ActiveElixirApiTest.php
│           ├── AppearanceOptionApiTest.php
│           └── FriendsApiTest.php
│
├── Shop/
│   ├── Command/                      # GenerateShopRotationsCommand
│   ├── Entity/                       # ShopOffer, ShopRotation, ShopRotationEnum
│   ├── Factory/                      # ShopOfferFactory, ShopRotationFactory
│   ├── Repository/                   # ShopOfferRepository, ShopRotationRepository
│   ├── Service/                      # RotationGenerator
│   ├── State/                        # ShopOfferBuyProcessor, ShopRotationProvider, ShopOfferProvider
│   └── Tests/
│       ├── Unit/
│       │   └── RotationGeneratorTest.php
│       └── Integration/
│           ├── ShopOfferBuyApiTest.php
│           └── ShopRotationApiTest.php
│
├── Item/
│   ├── ApiResource/                  # ItemViewDTO
│   ├── Entity/                       # Item, ItemDefinition, ElixirDefinition, ItemSlotEnum, ItemRarityEnum...
│   ├── Factory/                      # ItemFactory, ItemDefinitionFactory, ElixirDefinitionFactory
│   ├── Repository/                   # ItemRepository, ItemDefinitionRepository
│   ├── Service/                      # ItemStatCalculator, InventoryManager
│   └── Tests/
│       └── Unit/
│           ├── ItemStatCalculatorTest.php
│           ├── ItemFactoryTest.php
│           └── InventoryManagerTest.php
│
├── Leaderboard/
│   ├── ApiResource/                  # LeaderboardEntry, LeaderboardResponse
│   ├── Repository/                   # LeaderboardRepository
│   ├── State/                        # LeaderboardProvider
│   └── Tests/
│       └── Integration/
│           └── LeaderboardApiTest.php
│
└── Shared/                           # Cross-cutting code & shared testing utilities
    ├── Attribute/                    # CurrentUserScope
    ├── Config/                       # CharacterConfig, ItemConfig
    ├── Enum/                         # CurrencyEnum, GameCostEnum
    ├── EventSubscriber/              # RateLimitSubscriber
    ├── OpenApi/                      # JwtOpenApiFactory
    ├── Security/                     # LoggedInCharacter
    ├── State/                        # CurrentUserScopeExtension
    ├── Story/                        # AppStory, CharacterStory, ItemDefinitionStory
    └── Tests/                        # Shared testing foundation
        ├── AbstractApiTestCase.php   # Base class with JWT authentication helper
        └── Helper/
            └── EntityHelper.php
```

---

## 4. Data Flow in Modular Architecture

The execution flow remains consistent across all modules while maintaining localized responsibility:

```
[HTTP Request]
       │
       ▼
[API Platform Route / Firewall]
       │
       ▼
[Module DTO / ApiResource] (e.g. App\Auth\ApiResource\RegisterInput)
       │
       ▼
[Module Provider / Processor] (e.g. App\Shop\State\ShopOfferBuyProcessor)
       │
       ├── Calls ──► [Module / Cross-Module Service] (e.g. App\Item\Service\InventoryManager)
       ├── Loads ──► [Module Repository] (e.g. App\Shop\Repository\ShopOfferRepository)
       │
       ▼
[Module Entity State Mutation] (e.g. App\Character\Entity\Character)
       │
       ▼
[Doctrine EntityManager Flush]
       │
       ▼
[Output DTO / Serializer Normalization (Groups)]
       │
       ▼
[HTTP Response]
```

---

## 5. Architectural Decisions & Trade-offs

| Decision | Rationale | Trade-offs & Mitigations |
|---|---|---|
| **Package by Feature** over **Package by Layer** | High cohesion: All code relating to a feature (Shop, Character, Auth) is co-located. Context switching across the editor is minimized. | Cross-module relations exist (e.g., Shop needs Character & Item). Handled via explicit imports and clean shared abstractions. |
| **Co-located Tests (`src/<Module>/Tests/`)** | Tests live alongside the code they test. Deleting or refactoring a module keeps test cleanup intuitive. | Symfony container must be configured to exclude test directories from autowiring so it doesn't attempt to instantiate test cases as services. |
| **Single Doctrine Mapping for `App\`** | Configuring Doctrine to map `%kernel.project_dir%/src` under prefix `App` allows entities to live cleanly in `App\<Module>\Entity\*` without registering dozens of manual bundle mappings. | Doctrine scans all classes in `src/`, but only processes those with `#[ORM\Entity]` or `#[ORM\Embeddable]`. |
| **Shared Foundation (`src/Shared/`)** | Avoids artificial coupling where `Shop` depends on `Character` just to get a currency enum or test case base class. | Keep `Shared/` lean; do not let it become a junk drawer. Only place truly cross-cutting utilities there. |

---

## 6. Required Configuration Changes (Prerequisites)

Before moving any module, the configuration files must be updated to support the new structure.

### 1. `config/services.yaml`
Exclude test classes and entities from dependency injection autowiring:

```yaml
services:
    _defaults:
        autowire: true
        autoconfigure: true

    App\:
        resource: '../src/'
        exclude:
            - '../src/**/Tests/'
            - '../src/**/Entity/'
            - '../src/Kernel.php'
```

### 2. `config/packages/doctrine.yaml`
Point the entity mapping to the entire `src/` directory with the `App` prefix:

```yaml
        mappings:
            App:
                type: attribute
                is_bundle: false
                dir: '%kernel.project_dir%/src'
                prefix: 'App'
                alias: App
```

### 3. `phpunit.dist.xml`
Point the test suites to the modular directories:

```xml
    <testsuites>
        <testsuite name="unit">
            <directory suffix="Test.php">src/*/Tests/Unit</directory>
            <directory suffix="Test.php">src/Shared/Tests/Unit</directory>
        </testsuite>
        <testsuite name="integration">
            <directory suffix="Test.php">src/*/Tests/Integration</directory>
            <directory suffix="Test.php">src/Shared/Tests/Integration</directory>
        </testsuite>
    </testsuites>
```

---

## 7. Class Scaffolding Blueprints

Below are structural blueprints demonstrating how relocated classes and tests are scaffolded.

### A. Entity Scaffolding (`App\Shop\Entity\ShopOffer`)

```php
namespace App\Shop\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use App\Character\Entity\Character;
use App\Item\Entity\ItemDefinition;
use App\Shop\Repository\ShopOfferRepository;
use App\Shop\State\ShopOfferBuyProcessor;
use Doctrine\ORM\Mapping as ORM;

#[ApiResource(
    operations: [
        /* API Platform operation declarations pointing to modular processors */
    ]
)]
#[ORM\Entity(repositoryClass: ShopOfferRepository::class)]
#[ORM\Table(name: 'shop_offer')]
class ShopOffer
{
    /* ORM mapping properties: id, rotation, itemDefinition, prices */

    /* Standard getters, setters, and DB-to-object mapping helpers */
}
```

### B. Processor Scaffolding (`App\Shop\State\ShopOfferBuyProcessor`)

```php
namespace App\Shop\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Item\ApiResource\ItemViewDTO;
use App\Shop\Entity\ShopOffer;

/**
 * @implements ProcessorInterface<ShopOffer, ItemViewDTO>
 */
class ShopOfferBuyProcessor implements ProcessorInterface
{
    public function __construct(
        /* Injected modular dependencies: LoggedInCharacter, CharacterInventoryRepository, ItemFactory, InventoryManager, EntityManager */
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): ItemViewDTO
    {
        /*
         * 1. Validate logged in character against offer rotation
         * 2. Validate currencies and backpack capacity
         * 3. Create item, assign to backpack, deduct gold/diamonds
         * 4. Remove offer and persist inventory changes
         * 5. Return ItemViewDTO
         */
    }
}
```

### C. Co-located Integration Test Scaffolding (`App\Shop\Tests\Integration\ShopOfferBuyApiTest`)

```php
namespace App\Shop\Tests\Integration;

use App\Shared\Tests\AbstractApiTestCase;

class ShopOfferBuyApiTest extends AbstractApiTestCase
{
    public function testCharacterCanBuyOfferWithSufficientFunds(): void
    {
        /*
         * 1. Create character via CharacterFactory with gold
         * 2. Create rotation & offer via ShopOfferFactory
         * 3. Authenticate client
         * 4. Perform POST /api/shop/offer/{id}/buy
         * 5. Assert HTTP 200/201 and verify response item structure
         */
    }
}
```

---

## 8. Step-by-Step Implementation Roadmap

Execution proceeds in isolated vertical slices to keep the project continuously verifiable via test execution:

```
[Phase 0: Config Setup] ──► [Phase 1: Pilot Module (Shop)] ──► [Phase 2: Shared Foundations]
                                                                        │
┌───────────────────────────────────────────────────────────────────────┘
▼
[Phase 3: Item Module] ──► [Phase 4: Auth Module] ──► [Phase 5: Character Module]
                                                               │
┌──────────────────────────────────────────────────────────────┘
▼
[Phase 6: Leaderboard Module] ──► [Phase 7: Full QA Verification (PHPStan & CI)]
```

### Phase 0: Configuration Setup & Validation
1. Update `config/services.yaml` with the `exclude` directives.
2. Update `config/packages/doctrine.yaml` entity mappings.
3. Update `phpunit.dist.xml` with modular directory paths.
4. Clear cache inside container (`make cc`).
5. Run existing tests to ensure no regressions from configuration alone.

### Phase 1: Pilot Module — `Shop`
1. Create `src/Shop/` directory structure (`Entity`, `Repository`, `Service`, `Command`, `State`, `Factory`, `Tests/Unit`, `Tests/Integration`).
2. Move shop files:
   - `src/Entity/Shop/*` ➔ `src/Shop/Entity/`
   - `src/Repository/Shop/*` ➔ `src/Shop/Repository/`
   - `src/Service/Shop/*` ➔ `src/Shop/Service/`
   - `src/Command/GenerateShopRotationsCommand.php` ➔ `src/Shop/Command/`
   - `src/State/Processor/Shop/*` & `src/State/Provider/Shop/*` ➔ `src/Shop/State/`
   - `src/Factory/Shop*` ➔ `src/Shop/Factory/`
   - `tests/Unit/Service/Shop/*` ➔ `src/Shop/Tests/Unit/`
   - `tests/Integration/Shop/*` ➔ `src/Shop/Tests/Integration/`
3. Update namespaces to `App\Shop\*` and adjust imports in moved files.
4. Run shop tests:
   ```bash
   docker compose exec app php bin/phpunit --filter=Shop
   ```

### Phase 2: Shared Foundations
1. Create `src/Shared/` (`Tests/`, `Tests/Helper/`, `Enum/`, `Config/`, `Security/`, etc.).
2. Move shared test infrastructure:
   - `tests/Integration/AbstractApiTestCase.php` ➔ `src/Shared/Tests/AbstractApiTestCase.php`
   - `tests/Helper/EntityHelper.php` ➔ `src/Shared/Tests/Helper/EntityHelper.php`
   - `tests/Unit/Enum/GameCostEnumTest.php` ➔ `src/Shared/Tests/Unit/GameCostEnumTest.php`
3. Move shared cross-cutting classes:
   - `src/Enum/*` ➔ `src/Shared/Enum/`
   - `src/Config/*` ➔ `src/Shared/Config/`
   - `src/Security/LoggedInCharacter.php` ➔ `src/Shared/Security/LoggedInCharacter.php`
   - `src/Attribute/CurrentUserScope.php` & `src/State/Extension/CurrentUserScopeExtension.php` ➔ `src/Shared/State/`
4. Update imports across the codebase referencing shared files.

### Phase 3: `Item` Module
1. Create `src/Item/` structure (`Entity`, `Repository`, `Service`, `ApiResource`, `Factory`, `Tests/Unit`).
2. Move item-related files:
   - `src/Entity/Item/*` ➔ `src/Item/Entity/`
   - `src/Repository/Item/*` ➔ `src/Item/Repository/`
   - `src/Service/Item/*` & `src/Service/Inventory/*` ➔ `src/Item/Service/`
   - `src/ApiResource/Item/*` ➔ `src/Item/ApiResource/`
   - `src/Factory/Item*`, `ElixirDefinitionFactory.php` ➔ `src/Item/Factory/`
   - `tests/Unit/Service/Item/*` & `tests/Unit/Service/Inventory/*` ➔ `src/Item/Tests/Unit/`
3. Update namespaces to `App\Item\*` and fix imports.
4. Run item tests:
   ```bash
   docker compose exec app php bin/phpunit src/Item/Tests
   ```

### Phase 4: `Auth` Module
1. Create `src/Auth/` structure (`Entity`, `Repository`, `Service`, `ApiResource`, `State`, `Factory`, `Tests/Unit`, `Tests/Integration`).
2. Move auth-related files:
   - `src/Entity/EmailVerificationToken.php`, `PasswordResetToken.php` ➔ `src/Auth/Entity/`
   - `src/Repository/EmailVerificationTokenRepository.php`, `PasswordResetTokenRepository.php` ➔ `src/Auth/Repository/`
   - `src/Service/Auth/*` ➔ `src/Auth/Service/`
   - `src/ApiResource/Auth/*` ➔ `src/Auth/ApiResource/`
   - `src/State/Processor/Auth/*` & `src/State/Provider/Auth/*` ➔ `src/Auth/State/`
   - `src/Factory/EmailVerificationTokenFactory.php`, `PasswordResetTokenFactory.php` ➔ `src/Auth/Factory/`
   - `tests/Unit/Service/Auth/*` ➔ `src/Auth/Tests/Unit/`
   - `tests/Integration/Auth/*` ➔ `src/Auth/Tests/Integration/`
3. Update namespaces to `App\Auth\*` and fix imports.
4. Run auth tests:
   ```bash
   docker compose exec app php bin/phpunit src/Auth/Tests
   ```

### Phase 5: `Character` Module
1. Create `src/Character/` structure (`Entity`, `Repository`, `Service`, `State`, `Factory`, `Tests/Unit`, `Tests/Integration`).
2. Move character-related files:
   - `src/Entity/Character/*`, `Race.php`, `AppearanceOption.php`, `Appearance/` ➔ `src/Character/Entity/`
   - `src/Repository/Character/*`, `RaceRepository.php`, `AppearanceOptionRepository.php`, `FriendRelationRepository.php` ➔ `src/Character/Repository/`
   - `src/Service/Elixir/ElixirCleanUp.php` ➔ `src/Character/Service/`
   - `src/State/Provider/Character/*`, `src/State/Processor/Character/*` ➔ `src/Character/State/`
   - `src/Factory/CharacterFactory.php`, `CharacterInventoryFactory.php`, `ActiveElixirFactory.php`, `RaceFactory.php`, `AppearanceOptionFactory.php` ➔ `src/Character/Factory/`
   - `tests/Unit/Entity/Character/*`, `tests/Unit/Service/Elixir/*` ➔ `src/Character/Tests/Unit/`
   - `tests/Integration/Character/*`, `tests/Integration/Friends/*` ➔ `src/Character/Tests/Integration/`
3. Update namespaces to `App\Character\*` and adjust imports in entities and services.
4. Run character tests:
   ```bash
   docker compose exec app php bin/phpunit src/Character/Tests
   ```

### Phase 6: `Leaderboard` Module
1. Create `src/Leaderboard/` structure (`ApiResource`, `Repository`, `State`, `Tests/Integration`).
2. Move leaderboard files:
   - `src/ApiResource/Leaderboard/*` ➔ `src/Leaderboard/ApiResource/`
   - `src/Repository/Leaderboard/*` ➔ `src/Leaderboard/Repository/`
   - `src/State/Provider/Leaderboard/*` ➔ `src/Leaderboard/State/`
   - `tests/Integration/Leaderboard/*` ➔ `src/Leaderboard/Tests/Integration/`
3. Update namespaces to `App\Leaderboard\*` and fix imports.
4. Run leaderboard tests:
   ```bash
   docker compose exec app php bin/phpunit src/Leaderboard/Tests
   ```

### Phase 7: Verification & Cleanup
1. Remove now-empty legacy directories (`src/Entity`, `src/Repository`, `src/State`, `src/Service`, `tests/Unit`, `tests/Integration`).
2. Keep only `tests/bootstrap.php` in `tests/` (or update `phpunit.dist.xml` to point to `tests/bootstrap.php`).
3. Run entire test suite:
   ```bash
   make test
   ```
4. Run PHPStan static analysis:
   ```bash
   docker compose exec app vendor/bin/phpstan analyse -l 8 src
   ```

---

## 9. Teaching & Pitfalls to Watch For

1. **Doctrine Relationship References**:
   In Doctrine entity attributes, ensure `targetEntity` references use fully-qualified class names or class constants (e.g. `targetEntity: Character::class` rather than string literal `'App\Entity\Character\Character'`). Using class constants prevents silent breakage when classes are moved.

2. **Symfony DI Autowiring Pitfall**:
   If a test file is placed inside `src/` without being excluded in `services.yaml`, Symfony attempts to load it into the service container during cache warmup, failing because PHPUnit `TestCase` classes do not meet dependency injection autowiring requirements. Always verify `../src/**/Tests/` is in `services.yaml` exclusions.

3. **Database Migrations Integrity**:
   Because table names are explicitly defined via `#[ORM\Table(name: '...')]`, changing entity namespaces will **NOT** alter database table names. Database schema remains 100% consistent.
