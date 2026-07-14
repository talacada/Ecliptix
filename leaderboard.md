# Leaderboard — implementační plán

## Cíl

Nový endpoint `GET /leaderboard` vracející seřazený seznam hráčů podle PrestigePoints.
Endpoint slouží k vyhledávání hráčů pro budoucí zobrazení profilu a fighty.

## API kontrakt

### Request

```
GET /leaderboard
GET /leaderboard?name=Kael
GET /leaderboard?rank=50
GET /leaderboard?page=2           (scroll z aktuální pozice)
```

| Parametr | Typ | Povinný | Popis |
|---|---|---|---|
| `name` | string | ne | Přesný username hráče — najde ho, vycentruje výsledek kolem něj |
| `rank` | int | ne | Konkrétní rank — vrátí stránku obsahující tento rank, hráč uprostřed |
| `page` | int | ne (default → dopočítáno) | Číslo stránky — slouží k scrollování poté, co `name`/`rank` určil "kotvu" |

**Pravidla určení stránky (priorita):**

1. **`name=Kael`** → najdi hráče přesně podle username, zjisti jeho rank, vrať stránku kde je uprostřed. Pokud neexistuje → prázdno / 404.
2. **`rank=50`** → vrať stránku kde je rank 50 (hráč na pozici 50 je uprostřed).
3. **Bez parametrů** → najdi rank přihlášeného hráče, vrať stránku kde je uprostřed.
4. **`page=2`** (samostatně nebo jako posun od kotvy) → vrať konkrétní stránku od ranku 1.

`★ Insight ─────────────────────────────────────────────────`
Proč "vycentrovat"? Frontend chce ukázat kontext — ne jen hledaného hráče, ale i pár nad ním a pod ním. Tím pádem jeden `name`/`rank` request rovnou vrátí použitelný výsek leaderboardu a není potřeba druhý request na okolí. `page` pak slouží až k infinite scroll ("chci vidět další").
`─────────────────────────────────────────────────────────────`

### Response (beze změny)

```json
{
  "page": 1,
  "totalPages": 15,
  "totalItems": 750,
  "items": [
    {
      "rank": 1,
      "character": {
        "id": 42,
        "username": "Kael",
        "level": 10,
        "prestigePoints": 999,
        "gold": 5000,
        ...
      }
    }
  ]
}
```

- `rank` — dopočítaná hodnota (`offset + pozice + 1`), **není persistovaná**
- `character` — plný Character objekt se všemi `character:read` poli
- `totalPages`, `totalItems` — metadata pro frontend (infinite scroll)

## Co se vytvoří / změní

| Soubor | Typ | Popis |
|---|---|---|
| migrace | nová | `prestige_points INT DEFAULT 0 NOT NULL` na `character` |
| `src/Entity/Character/Character.php` | změna | + pole `$prestigePoints` s `#[ORM\Column]` a `#[Groups([READ_GROUP])]`, getter/setter |
| `src/Repository/Character/CharacterRepository.php` | změna | + 3 metody: findForLeaderboard, countForLeaderboard, findRankOfCharacter |
| `src/ApiResource/Leaderboard/LeaderboardEntry.php` | nový | DTO jedné položky: `$rank` (int) + `$character` (Character) |
| `src/ApiResource/Leaderboard/LeaderboardResponse.php` | nový | DTO odpovědi: `$items[]` + paginace metadata. Vlastní `#[ApiResource]` s `GetCollection` na `/leaderboard` |
| `src/State/Provider/Leaderboard/LeaderboardProvider.php` | nový | Provider — logika "najdi kotvu → spočítej stránku → vrať výsek" |

## Datový tok

```
GET /leaderboard?name=Kael
  → LeaderboardProvider::provide()
    → zjistí přihlášeného hráče (Security)
    → určí "kotvu" (target rank):
        name=Kael  → repo.findByUsername('Kael') → repo.findRankOfCharacter(character)
        rank=50    → 50
        bez param  → repo.findRankOfCharacter(loggedInUser)
        page=2     → použije se přímo (offset = (2-1) * limit)
    → spočítá page = ceil(targetRank / limit)
    → spočítá offset = (page - 1) * limit
    → repo.findForLeaderboard(limit, offset)  → Character[]
    → repo.countForLeaderboard()              → totalItems
    → namapuje na LeaderboardEntry[] (rank = offset + index + 1)
    → vrátí LeaderboardResponse(page, totalPages, totalItems, entries)
  → Symfony Serializer → JSON
```

`★ Insight ─────────────────────────────────────────────────`
Datový tok se změnil: dřív `name` fungoval jako WHERE filtr na celé tabulce, teď je to dvoufázové — nejdřív *najdi rank* hledaného hráče, pak *vrať stránku* kolem něj. Výhoda: i když hledáš "Kael", vidíš zároveň hráče nad ním a pod ním. Nevýhoda: přibyla `findRankOfCharacter` query navíc.
`─────────────────────────────────────────────────────────────`

## Implementační kroky

### Krok 1: Databáze a entita

Přidat `prestigePoints` do Character entity — `int $prestigePoints = 0`, getter, setter.
Vygenerovat migraci: `make migration` → `ALTER TABLE character ADD prestige_points INT DEFAULT 0 NOT NULL`.


### Krok 3: LeaderboardEntry DTO

```php
// src/ApiResource/Leaderboard/LeaderboardEntry.php
namespace App\ApiResource\Leaderboard;

// DTO s public property — pattern používaný už v LoginOutput
class LeaderboardEntry
{
    // const LEADERBOARD_READ = 'leaderboard:read'

    // #[Groups([LEADERBOARD_READ])] public int $rank
    // #[Groups([LEADERBOARD_READ])] public Character $character

    // constructor(int $rank, Character $character)
}
```

### Krok 4: LeaderboardResponse — API Resource

```php
// src/ApiResource/Leaderboard/LeaderboardResponse.php
namespace App\ApiResource\Leaderboard;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\QueryParameter;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\Parameter;

#[ApiResource(
    operations: [
        new GetCollection(
            uriTemplate: '/leaderboard',
            security: 'is_granted("ROLE_USER")',
            provider: LeaderboardProvider::class,
            normalizationContext: ['groups' => [self::LEADERBOARD_READ, Character::READ_GROUP]],
            paginationEnabled: false,
            openapi: new Operation(
                parameters: [
                    new Parameter(name: 'name', in: 'query', description: 'Přesný username — vycentruje výsledek kolem tohoto hráče', schema: ['type' => 'string']),
                    new Parameter(name: 'rank', in: 'query', description: 'Rank — vycentruje výsledek kolem této pozice', schema: ['type' => 'integer']),
                    new Parameter(name: 'page', in: 'query', description: 'Číslo stránky pro scrollování od kotvy', schema: ['type' => 'integer']),
                ],
            ),
        ),
    ],
)]
class LeaderboardResponse
{
    // const LEADERBOARD_READ = 'leaderboard:read'

    // #[Groups([LEADERBOARD_READ])] public int $page
    // #[Groups([LEADERBOARD_READ])] public int $totalPages
    // #[Groups([LEADERBOARD_READ])] public int $totalItems
    // #[Groups([LEADERBOARD_READ])] public array $items  // LeaderboardEntry[]

    // constructor(int $page, int $totalPages, int $totalItems, array $items)
}
```

###  -------- Krok 4: Provider

```php
// src/State/Provider/Leaderboard/LeaderboardProvider.php
namespace App\State\Provider\Leaderboard;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * @implements ProviderInterface<LeaderboardResponse>
 */
class LeaderboardProvider implements ProviderInterface
{
    // Konstanta: LIMIT = 50 (pevná velikost stránky, klient ji nemění)

    // Constructor: CharacterRepository, RequestStack, Security

    // provide(Operation, array, array): LeaderboardResponse
    //
    //   1. Vytáhnout query params: name (?string), rank (?int), page (?int)
    //   2. Určit cílový rank:
    //        IF name:
    //          najít character přes repo (findOneBy username = exact match)
    //          pokud neexistuje → vyhodit výjimku / vrátit prázdno
    //          targetRank = repo.findRankOfCharacter(character)
    //        ELSE IF rank:
    //          targetRank = max(1, (int) $rank)
    //        ELSE IF page:
    //          přeskočit výpočet kotvy, použít $page přímo
    //        ELSE (bez parametrů):
    //          targetRank = repo.findRankOfCharacter(Security::getUser())
    //
    //   3. Pokud máme targetRank (ne přímé page):
    //        page = ceil(targetRank / LIMIT)
    //
    //   4. offset = (page - 1) * LIMIT
    //   5. characters = repo.findForLeaderboard(LIMIT, offset)
    //   6. totalItems = repo.countForLeaderboard()
    //   7. Namapovat na LeaderboardEntry[]:
    //        rank = offset + index + 1
    //   8. Vrátit new LeaderboardResponse(page, ceil(totalItems / LIMIT), totalItems, entries)
}
```

`★ Insight ─────────────────────────────────────────────────`
Proč `Security` v konstruktoru a ne `TokenStorageInterface`? `Security` je Symfony 6+ helper, který obaluje `TokenStorageInterface` a přidává helper metody (`getUser()`, `isGranted()`). Je čistší, míň verbose a Symfony ho doporučuje pro autowiring. V projektu už ho nejspíš používá `MineCharacterProvider` — držíme se konzistence.
`─────────────────────────────────────────────────────────────`
### Krok 5: Repository metody

```php
// src/Repository/Character/CharacterRepository.php

/**
 * Vrátí výsek leaderboardu — stránku hráčů seřazených podle PP.
 * Žádný filtr — leaderboard je vždy globální, "kotva" se řeší přes offset.
 *
 * @param int $limit  — počet výsledků (velikost stránky)
 * @param int $offset — od jaké pozice (stránka * limit)
 * @return Character[]
 */
public function findForLeaderboard(int $limit, int $offset): array
{
    // QueryBuilder: SELECT c FROM Character c
    //   ORDER BY c.prestigePoints DESC, c.id ASC
    //   setMaxResults($limit), setFirstResult($offset)
}

/**
 * Celkový počet hráčů pro paginaci.
 */
public function countForLeaderboard(): int
{
    // QueryBuilder: SELECT COUNT(c.id) FROM Character c
}

/**
 * Zjistí absolutní rank (1-based) konkrétního characteru
 * podle pořadí prestigePoints DESC, id ASC.
 *
 * @return int — rank hráče (1 = nejvyšší PP)
 */
public function findRankOfCharacter(Character $character): int
{
    // COUNT(*) + 1 FROM character
    //   WHERE prestigePoints > :pp
    //      OR (prestigePoints = :pp AND id < :id)
    // Pokud je PP = 0 a žádný hráč nemá vyšší → rank = 1
}
```

`★ Insight ─────────────────────────────────────────────────`
`findRankOfCharacter` nepoužívá window funkce (`ROW_NUMBER()`), ale jednoduché `COUNT` — sečte všechny hráče s vyšším PP (a menším ID při shodě). Pro MVP naprosto dostačující, na pár tisíc hráčů s indexem na `(prestige_points, id)` to letí. Window funkce by byly potřeba až při desítkách tisíc řádků.
`─────────────────────────────────────────────────────────────`

## Shrnutí

| Co             | Jak                                                                     | 
|----------------|-------------------------------------------------------------------------|
| Řazení         | `prestigePoints DESC, id ASC` — deterministické                         |
| "Kotva"        | Priorita: `name` → `rank` → přihlášený hráč → `page`                    |
| Rank           | `COUNT` hráčů s vyšším PP — jednoduché, ne window funkce                |
| Name filtr     | Přesná shoda username → najdi rank → vycentruj stránku                  |
| Paginace       | Manuální v provideru (LIMIT=50 pevně), API Platform pagination disabled |
| Serializace    | `leaderboard:read` pro rank, `character:read` pro Character uvnitř      |
| OpenAPI        | Parametry dokumentované přes `Operation(parameters: [...])`             |
| Přístup        | Jen pro přihlášené (`ROLE_USER`)                                        |
| Rozšiřitelnost | DTO jde rozšířit o další computed hodnoty bez změny entity              |
