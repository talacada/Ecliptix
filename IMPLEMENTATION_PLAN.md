# Package by Feature with Co-located Tests Implementation Plan

> When implementing this plan, use `superpowers:executing-plans` and work through one verified slice at a time.

**Goal** Organize Ecliptix around game features so that each feature's entity, repository, services, API classes, factories and tests are easy to find together.

**Architecture** Keep a single Symfony application. Use feature namespaces such as `App\Character\Inventory` and flat directories within each feature. Create subdirectories for game concepts or workflows such as `Inventory`, `Friends` and `PasswordReset`.

**Stack** PHP 8.4+, Symfony 8, API Platform 4.3, Doctrine ORM, PostgreSQL, PHPUnit 13 and Zenstruck Foundry.

## Scope

- Relocate files and update namespaces, imports, class references and discovery configuration. Preserve production class short names, method bodies, DTO fields, validation, serializer groups, ORM metadata and application behavior.
- Keep each concrete test in the same directory as the code for its feature. Preserve test methods, data providers, assertions and fixtures. Give unit test classes names ending in `UnitTest` and matching filenames ending in `UnitTest.php`. Keep the existing `ApiTest` names.
- Preserve the `unit` and `integration` suites used by CI. Keep `tests/bootstrap.php` and the existing Composer `App\Tests\` dev namespace for shared test support.
- Use the current development and CI environments with dev dependencies installed. A production packaging strategy is outside this migration.
- Keep HTTP compatibility and the existing database schema. Compare both against the baseline after each slice.

## Target layout

The `Character` module groups the character itself, appearance, elixirs, friends and inventory into sibling features. Each concrete test shares its directory and namespace with the feature classes.

```text
src/Character/
├── Character/
│   ├── Character.php
│   ├── CharacterRepository.php
│   ├── CharacterFactory.php
│   ├── CharacterConfig.php
│   ├── LoggedInCharacter.php
│   ├── MineCharacterProvider.php
│   ├── PublicCharacterProvider.php
│   ├── UpdateCharacterProcessor.php
│   ├── CharacterUnitTest.php
│   └── CharacterApiTest.php
├── Appearance/
│   ├── AppearanceOption.php
│   ├── AppearanceOptionRepository.php
│   ├── AppearanceOptionFactory.php
│   ├── AppearanceTypeEnum.php
│   ├── Race.php
│   ├── RaceRepository.php
│   ├── RaceFactory.php
│   ├── AppearanceValidationService.php
│   ├── AppearanceValidationServiceUnitTest.php
│   └── AppearanceOptionApiTest.php
├── Elixir/
│   ├── ActiveElixir.php
│   ├── ActiveElixirRepository.php
│   ├── ActiveElixirFactory.php
│   ├── ActiveElixirProvider.php
│   ├── ActiveElixirRemoveProcessor.php
│   ├── ElixirCleanUp.php
│   ├── ElixirCleanUpUnitTest.php
│   └── ActiveElixirApiTest.php
├── Friends/
│   ├── FriendRelation.php
│   ├── FriendRelationRepository.php
│   ├── FriendsAddProcessor.php
│   └── FriendsApiTest.php
└── Inventory/
    ├── CharacterInventory.php
    ├── CharacterInventoryRepository.php
    ├── CharacterInventoryFactory.php
    ├── CharacterInventoryProvider.php
    ├── CharacterInventoryEditProcessor.php
    ├── CharacterInventorySellProcessor.php
    ├── CharacterInventoryUseProcessor.php
    ├── InventoryContainerEnum.php
    ├── InventoryManager.php
    ├── InventoryManagerUnitTest.php
    └── CharacterInventoryApiTest.php
```

Use the following destination map for the remaining files. A move keeps the existing filename unless the table gives it a new name ending in `UnitTest`.

| Directory | Files |
| --- | --- |
| `src/Leaderboard/` | `LeaderboardEntry.php`, `LeaderboardResponse.php`, `LeaderboardProvider.php`, `LeaderboardRepository.php`, `LeaderboardApiTest.php` |
| `src/Shop/` | `ShopOffer.php`, `ShopOfferFactory.php`, `ShopOfferRepository.php`, `ShopOfferProvider.php`, `ShopOfferBuyProcessor.php`, `ShopOfferBuyApiTest.php`, `ShopRotation.php`, `ShopRotationEnum.php`, `ShopRotationFactory.php`, `ShopRotationRepository.php`, `ShopRotationProvider.php`, `ShopRotationApiTest.php`, `RotationGenerator.php`, `RotationGeneratorUnitTest.php`, `GenerateShopRotationsCommand.php` |
| `src/Item/` | `Item.php`, `ItemDefinition.php`, `ElixirDefinition.php`, `ElixirTypeEnum.php`, `ItemRarityEnum.php`, `ItemSlotEnum.php`, `ItemConfig.php`, `ItemFactory.php`, `ItemDefinitionFactory.php`, `ElixirDefinitionFactory.php`, `ItemRepository.php`, `ItemDefinitionRepository.php`, `ItemStatCalculator.php`, `ItemViewDTO.php`, `ItemFactoryUnitTest.php`, `ItemStatCalculatorUnitTest.php` |
| `src/Auth/` | `LoginInput.php`, `LoginOutput.php`, `LoginProcessor.php`, `LoginApiTest.php`, `ChangePasswordInput.php`, `ChangePasswordProcessor.php`, `ChangePasswordApiTest.php` |
| `src/Auth/Registration/` | `RegisterInput.php`, `RegisterProcessor.php`, `RegisterApiTest.php`, `RegisterOptionsProvider.php`, `RegisterOptionsResponse.php`, `RegisterOptionsApiTest.php`, `AppearanceGroupDto.php`, `AppearanceOptionDto.php`, `RaceDto.php` |
| `src/Auth/EmailVerification/` | `EmailVerificationToken.php`, `EmailVerificationTokenFactory.php`, `EmailVerificationTokenRepository.php`, `EmailVerificationService.php`, `EmailVerificationServiceUnitTest.php`, `VerifyEmailProcessor.php`, `VerifyEmailApiTest.php` |
| `src/Auth/PasswordReset/` | `PasswordResetToken.php`, `PasswordResetTokenFactory.php`, `PasswordResetTokenRepository.php`, `PasswordResetInput.php`, `PasswordResetProcessor.php`, `PasswordResetApiTest.php`, `RequestPasswordResetInput.php`, `RequestPasswordResetProcessor.php`, `RequestPasswordResetApiTest.php` |
| `src/Shared/` | `CurrencyEnum.php`, `GameCostEnum.php`, `GameCostEnumUnitTest.php`, `CurrentUserScope.php`, `CurrentUserScopeExtension.php`, `RateLimitSubscriber.php`, `JwtOpenApiFactory.php` |
| `tests/Support/` | `AbstractApiTestCase.php`, `EntityHelper.php` |
| Existing locations | `src/Kernel.php`, `src/Story/AppStory.php`, `src/Story/CharacterStory.php`, `src/Story/ItemDefinitionStory.php`, `tests/bootstrap.php` |

`InventoryManager` and `InventoryContainerEnum` belong to character inventory. `ItemViewDTO` belongs to `Item` because both inventory and shop use it. `AppearanceValidationService` belongs to `Character/Appearance` because registration and character updates both use it. Keep `LoggedInCharacter` in `Character/Character/` since it resolves the character and coordinates elixir cleanup and shop rotation. Existing cross-feature calls stay in place.

## HTTP and database baselines

Use the current router output, OpenAPI export and integration tests as the contract. Preserve methods, paths, URI and query parameters, JSON and JSON-LD formats, IRIs, request fields, response status and shape, and JWT access rules. Examples to check during the relevant slice include the following.

| Current operation | Contract to preserve |
| --- | --- |
| `GET /api/leaderboard` | Optional `name` and `rank` query parameters, JWT, `LeaderboardResponse` |
| `GET /api/auth/register/options` | Public endpoint returning registration options |
| `POST /api/auth/verify-email` | Token in the request body, HTTP 200 with `message` on success |
| `POST /api/auth/login` | `LoginInput` to `LoginOutput`, HTTP 201 on success |
| `GET /api/character/inventory` and `GET /api/character/inventory/{inventoryId}` | JWT, `inventory:read` and `item:read` groups |
| `PATCH /api/character/inventory/{inventoryId}` | JWT, existing `inventory:write` fields |
| `POST /api/character/inventory/{inventoryId}/sell` and `POST /api/character/inventory/{inventoryId}/use` | JWT, HTTP 201 on success |
| `POST /api/shop/offer/{id}` | JWT, HTTP 201 with item output on success |
| `GET /api/character/elixir/{id}` and `DELETE /api/character/elixir/{id}` | JWT, HTTP 200 and 204 on success respectively |
| `POST /api/friends/{id}` | JWT, HTTP 201 on success |

Keep resource attributes on their existing classes. For example, `RegisterInput` and `RegisterProcessor` move to `Auth/Registration`, while the registration operation declared on `Character` stays there. The request still flows from the API resource to its provider or processor, then through the same repositories, persistence and serializer configuration.

Keep ORM associations, join and table definitions, naming strategy, enum values and the `ItemDefinition` discriminator map. Compare the SQL reported by `doctrine:schema:update --dump-sql` against the same local database and migration state before and after the move. The namespace change should introduce no new SQL.

## Discovery during the move

### Symfony and API Platform

Keep the existing `App\:` service import and autoconfiguration in `config/services.yaml`. Add this exclusion before the first concrete test moves into `src/`.

```yaml
        exclude:
            - '../src/**/*Test.php'
```

API Platform registers attributed resources through Symfony autoconfiguration. Keep those classes covered by the service import even when they are Doctrine entities. Check the router after each move. Update `security.providers.app_user_provider.entity.class` in `config/packages/security.yaml` to `App\Character\Character\Character` in the same step that moves `Character.php`.

### Doctrine

Keep the `App\Entity` mapping for `src/Entity` while any legacy entity remains. Add one attribute mapping per module that contains moved entities, as part of the slice that first introduces them. The first new mapping will cover inventory and later cover the rest of `Character` recursively.

```yaml
        mappings:
            Character:
                type: attribute
                is_bundle: false
                dir: '%kernel.project_dir%/src/Character'
                prefix: 'App\Character'
                alias: Character
```

Use the same pattern for `Shop`, `Item` and `Auth`. The old mapping and new mappings must coexist during migration. Keep ORM metadata on the original classes and change class references as the entities move.

Doctrine's attribute driver loads PHP files from its mapped directories before filtering them by ORM attributes. The co-located tests and Foundry factories therefore require the existing dev dependencies in the local and CI environments. The Symfony exclusion controls service discovery only. If a future deployment uses `composer install --no-dev`, handle packaging or ORM discovery for test and fixture files as part of that deployment.

### PHPUnit

Add the new discovery rules alongside the existing rules before the first move. Keep the bootstrap and Foundry extension.

```xml
<testsuites>
    <testsuite name="unit">
        <directory>tests/Unit</directory>
        <directory suffix="UnitTest.php">src</directory>
    </testsuite>
    <testsuite name="integration">
        <directory>tests/Integration</directory>
        <directory suffix="ApiTest.php">src</directory>
    </testsuite>
</testsuites>
```

Rename the filename and class together when moving a unit test. For example, `InventoryManagerTest` becomes `InventoryManagerUnitTest`. Remove the two legacy `<directory>` entries after their last tests have moved.

## Migration steps

Run the commands from the project's Docker app environment with dev dependencies installed. Use the existing local test database and JWT setup. Compare test methods and data sets after accounting for the intentional class and namespace changes.

Before starting the next slice, clear the dev and test caches with `php bin/console cache:clear` and `php bin/console cache:clear --env=test`. Check both test inventories against the baseline and run `php bin/phpunit`. Compare the router and OpenAPI exports with the baseline. For slices that move entities, also compare mapping coverage and schema SQL.

### 0. Record the baseline

- [ ] Capture `php bin/console debug:router --format=json`, `php bin/console api:openapi:export`, `php bin/console doctrine:mapping:info` and `php bin/console doctrine:schema:update --dump-sql`. Save outputs locally for comparison.
- [ ] Capture the output of `php bin/phpunit --testsuite=unit --list-tests` and `php bin/phpunit --testsuite=integration --list-tests`. Run `make ready` to record the current formatting, PHPStan and test results.

### 1. Move Leaderboard and enable co-located tests

- [ ] Add the Symfony and PHPUnit discovery changes above. Move the five Leaderboard files to `src/Leaderboard/` and update references to `App\Leaderboard`.
- [ ] Move `AbstractApiTestCase` and `EntityHelper` to `tests/Support/` under `App\Tests\Support`. Update their imports in all tests. Keep the test bootstrap at `tests/bootstrap.php`.
- [ ] Run `php bin/phpunit src/Leaderboard/LeaderboardApiTest.php`. Check that the integration suite lists the moved test once.

### 2. Move Character inventory

- [ ] Move the inventory files from `src/Entity/Character/`, `src/Entity/Item/InventoryContainerEnum.php`, `src/Repository/Character/`, `src/Factory/`, `src/Service/Inventory/`, `src/State/{Provider,Processor}/Character/Inventory/` and the existing unit and integration suites into `src/Character/Inventory/`. Use the `Inventory/` branch of the target tree as the exact file list. Update references in Shop, fixtures and tests as part of the same move.
- [ ] Add the `Character` Doctrine mapping alongside `App\Entity`. Confirm that `App\Character\Inventory\CharacterInventory` is mapped and the rest of the legacy entities are still present.
- [ ] Run `php bin/phpunit src/Character/Inventory`. Run `php bin/phpunit --filter=ShopOfferBuyApiTest` to cover the existing call into inventory from the shop.

### 3. Move Shop and Item

- [ ] Move the files in the `Shop` row as one slice and add the `Shop` Doctrine mapping. Update cross-feature imports and keep the `app:shop:generate-rotations` command name. Run `php bin/phpunit src/Shop`.
- [ ] Move the files in the `Item` row as the next slice and add the `Item` Doctrine mapping. Update references to `ItemViewDTO`, the item definitions and their factories across Shop, Character and fixture stories. Run `php bin/phpunit src/Item` and rerun Shop and Inventory tests.
- [ ] Confirm that Doctrine still discovers `ItemDefinition` and its `ElixirDefinition` subtype with the original discriminator mapping.

### 4. Move the remaining Character slices

- [ ] Move `Character/Appearance`, `Character/Friends` and `Character/Elixir` one at a time. Update references from Auth, Shop, fixtures and test factories in the same slice. The existing `Character` Doctrine mapping covers each new directory.
- [ ] Move the `Character/Character/` branch under the `App\Character\Character` namespace. Change the JWT user provider class at the same time. Update ORM associations, PHPDoc generics, provider and processor references, and fixture imports throughout the project.
- [ ] Run `php bin/phpunit src/Character`, plus the existing Auth integration suite files, since login, registration and JWT authentication resolve the moved character.

### 5. Move Auth and Shared

- [ ] Move the four Auth rows and add the `Auth` Doctrine mapping when the first token entity moves. Keep operation metadata on the class that currently declares it. Run `php bin/phpunit src/Auth`.
- [ ] Move the files in the `Shared` row. Verify that the current-user query extension, rate limiter and JWT OpenAPI decorator remain registered through the existing service import. Rerun the friends and auth API tests.

### 6. Remove legacy discovery and verify the result

- [ ] Remove the `App\Entity` mapping once `doctrine:mapping:info` confirms every entity is registered under its new module. Remove obsolete test directory entries and empty legacy directories. Update the directory and namespace examples in `CLAUDE.md` to describe the feature layout.
- [ ] Search application code, config, tests and fixture stories for stale FQCNs from the moved files. Confirm the Symfony dev and test containers compile.
- [ ] Compare both `--list-tests` outputs with the baseline. Each test method and data set should appear once in its original suite.
- [ ] Compare router, OpenAPI and schema output against the baseline. Account for intentional internal class-name changes and check that the external contract and reported schema SQL match.
- [ ] Run `make ready`, which runs the project's formatting checks, configured PHPStan analysis and full test suite.
