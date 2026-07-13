# Leaderboard — implementační plán

## Cíl

Nový endpoint `GET /leaderboard` vracející seřazený seznam hráčů podle PrestigePoints.
Endpoint slouží k vyhledávání hráčů pro budoucí zobrazení profilu a fighty.

## API kontrakt

### Request

```
GET /leaderboard?page=1&name=Kael&rank=5
```

| Parametr | Typ | Povinný | Popis |
|---|---|---|---|
| `page` | int | ne (default 1) | Číslo stránky |
| `name` | string | ne | Fulltext search podle username (LIKE %value%) |
| `rank` | int | ne | Konkrétní rank — vrátí stránku obsahující tento rank |

### Response

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
        "diamonds": 120,
        "damage": 45,
        "health": 200,
        "experience": 1500,
        "shopRotations": [...],
        "characterInventories": [...],
        "activeElixirs": [...]
      }
    }
  ]
}
```

- `rank` — dopočítaná hodnota (`offset + pozice + 1`), **není persistovaná**
- `character` — plný Character objekt se všemi `character:read` poli
- `totalPages`, `totalItems` — metadata pro frontend (infinite scroll)

### Chování `rank` filtru

Když frontend pošle `?rank=50`, vrátí se stránka, na které se nachází rank 50
(tj. `page = ceil(50 / limit)`, stránka obsahuje ranky okolo 50).

## Co se vytvoří

| Soubor | Typ | Popis |
|---|---|---|
| migrace | nová | `prestige_points INT DEFAULT 0 NOT NULL` |
| `src/Entity/Character/Character.php` | změna | + pole `$prestigePoints` (jen field, žádná nová operace) |
| `src/ApiResource/Leaderboard/LeaderboardEntry.php` | nový | DTO položky: `rank` + `character` |
| `src/ApiResource/Leaderboard/LeaderboardResponse.php` | nový | DTO odpovědi: `items[]` + paginace metadata |
| `src/Repository/Character/CharacterRepository.php` | změna | + `findForLeaderboard()`, `countForLeaderboard()` |
| `src/State/Provider/Leaderboard/LeaderboardProvider.php` | nový | Provider pro `/leaderboard` |

## Implementační kroky

### Krok 1: Databáze a entita

Přidat `prestigePoints` do Character entity:

```php
#[ORM\Column]
#[Groups([self::READ_GROUP])]
private int $prestigePoints = 0;

public function getPrestigePoints(): int { return $this->prestigePoints; }
public function setPrestigePoints(int $prestigePoints): void { $this->prestigePoints = $prestigePoints; }
```

Vygenerovat migraci: `make migration` → `ALTER TABLE character ADD prestige_points INT DEFAULT 0 NOT NULL`.

### Krok 2: Repository metody

```php
public function findForLeaderboard(?string $nameFilter, int $limit, int $offset): array
{
    $qb = $this->createQueryBuilder('c')
        ->orderBy('c.prestigePoints', 'DESC')
        ->addOrderBy('c.id', 'ASC'); // deterministické pořadí při shodě PP

    if ($nameFilter !== null && $nameFilter !== '') {
        $qb->andWhere('c.username LIKE :name')
           ->setParameter('name', '%' . $nameFilter . '%');
    }

    return $qb->setMaxResults($limit)
        ->setFirstResult($offset)
        ->getQuery()
        ->getResult();
}

public function countForLeaderboard(?string $nameFilter): int
{
    $qb = $this->createQueryBuilder('c')
        ->select('COUNT(c.id)');

    if ($nameFilter !== null && $nameFilter !== '') {
        $qb->andWhere('c.username LIKE :name')
           ->setParameter('name', '%' . $nameFilter . '%');
    }

    return (int) $qb->getQuery()->getSingleScalarResult();
}
```

`★ Insight ─────────────────────────────────────────────────`
Proč `addOrderBy('c.id', 'ASC')` jako sekundární řazení? Když mají dva hráči stejné PP, bez druhého ORDER BY není zaručené pořadí — databáze může vrátit pokaždé jiné. Přidáním ID zajistíme deterministické pořadí: kdo byl dřív zaregistrovaný, je výš.
`─────────────────────────────────────────────────────────────`

### Krok 3: LeaderboardEntry DTO (položka seznamu)

```php
namespace App\ApiResource\Leaderboard;

use App\Entity\Character\Character;
use Symfony\Component\Serializer\Attribute\Groups;

class LeaderboardEntry
{
    public const string LEADERBOARD_READ = 'leaderboard:read';

    #[Groups([self::LEADERBOARD_READ])]
    public int $rank;

    #[Groups([self::LEADERBOARD_READ])]
    public Character $character;

    public function __construct(int $rank, Character $character)
    {
        $this->rank = $rank;
        $this->character = $character;
    }
}
```

`★ Insight ─────────────────────────────────────────────────`
DTO používá `public` property místo getterů/setterů — Symfony Serializer s nimi pracuje přímo (property access). Pro readonly DTO je to idiomatičtější a míň kódu. V projektu už tenhle pattern je u `LoginOutput`.
`─────────────────────────────────────────────────────────────`

### Krok 4: LeaderboardResponse DTO (API Resource)

Tohle je samostatný API resource, ne operace na Characteru. Má vlastní `#[ApiResource]` a vystavuje `/leaderboard` endpoint.

```php
namespace App\ApiResource\Leaderboard;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use App\Entity\Character\Character;
use App\State\Provider\Leaderboard\LeaderboardProvider;
use Symfony\Component\Serializer\Attribute\Groups;

#[ApiResource(
    operations: [
        new GetCollection(
            uriTemplate: '/leaderboard',
            security: 'is_granted("ROLE_USER")',
            provider: LeaderboardProvider::class,
            normalizationContext: ['groups' => [self::LEADERBOARD_READ, Character::READ_GROUP]],
            paginationEnabled: false,
        ),
    ],
)]
class LeaderboardResponse
{
    public const string LEADERBOARD_READ = 'leaderboard:read';

    #[Groups([self::LEADERBOARD_READ])]
    public int $page;

    #[Groups([self::LEADERBOARD_READ])]
    public int $totalPages;

    #[Groups([self::LEADERBOARD_READ])]
    public int $totalItems;

    /**
     * @var LeaderboardEntry[]
     */
    #[Groups([self::LEADERBOARD_READ])]
    public array $items;

    /**
     * @param LeaderboardEntry[] $items
     */
    public function __construct(int $page, int $totalPages, int $totalItems, array $items)
    {
        $this->page = $page;
        $this->totalPages = $totalPages;
        $this->totalItems = $totalItems;
        $this->items = $items;
    }
}
```

`★ Insight ─────────────────────────────────────────────────`
Proč samostatný `#[ApiResource]` a ne operace na Characteru? Protože LeaderboardResponse je plnohodnotný API endpoint — má vlastní URI, vlastní provider, vlastní serializační skupiny. Character entita o něm nemusí vědět. API Platform resource ≠ entita — resource je "co vystavuju ven" a může to být klidně DTO bez vlastní databázové tabulky.
`─────────────────────────────────────────────────────────────`

### Krok 5: Provider

Provider řeší:
1. Čtení query parametrů (`page`, `name`, `rank`)
2. Přepočet `rank` parametru na offset
3. Volání repository metod
4. Mapování na `LeaderboardEntry` (rank = offset + index + 1)
5. Paginace metadata

```php
namespace App\State\Provider\Leaderboard;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\ApiResource\Leaderboard\LeaderboardEntry;
use App\ApiResource\Leaderboard\LeaderboardResponse;
use App\Repository\Character\CharacterRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use App\Entity\Character\Character;

/**
 * @implements ProviderInterface<LeaderboardResponse>
 */
class LeaderboardProvider implements ProviderInterface
{
    private const int DEFAULT_LIMIT = 50;
    private const int MAX_LIMIT = 100;

    public function __construct(
        private CharacterRepository $repository,
        private RequestStack $requestStack,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): LeaderboardResponse
    {
        $request = $this->requestStack->getMainRequest();
        $page = max(1, (int) ($request?->query->get('page', 1)));
        $nameFilter = $request?->query->get('name');
        $rankFilter = $request?->query->get('rank');

        $limit = self::DEFAULT_LIMIT;

        // Když je rank filtr, přesměrujeme stránku
        if ($rankFilter !== null) {
            $targetRank = max(1, (int) $rankFilter);
            $page = (int) ceil($targetRank / $limit);
        }

        $offset = ($page - 1) * $limit;

        /** @var Character[] $characters */
        $characters = $this->repository->findForLeaderboard(
            $nameFilter !== null ? (string) $nameFilter : null,
            $limit,
            $offset
        );

        $totalItems = $this->repository->countForLeaderboard(
            $nameFilter !== null ? (string) $nameFilter : null
        );

        $entries = [];
        foreach ($characters as $index => $character) {
            $entries[] = new LeaderboardEntry(
                rank: $offset + $index + 1,
                character: $character,
            );
        }

        return new LeaderboardResponse(
            page: $page,
            totalPages: (int) ceil($totalItems / $limit),
            totalItems: $totalItems,
            items: $entries,
        );
    }
}
```

`★ Insight ─────────────────────────────────────────────────`
Provider vrací `LeaderboardResponse` DTO, ne `PaginatorInterface`. API Platform nepřidá svoje hydra pagination metadata (`hydra:totalItems` atd.), ale frontend dostane `totalPages`/`totalItems`/`page` jako standardní JSON pole uvnitř odpovědi. Pro infinite scroll je to perfektně použitelné a vyhneme se implementaci `PaginatorInterface` pro "nepřímou" query (kde data a count běží separátně).
`─────────────────────────────────────────────────────────────`

## Shrnutí

| Co | Jak |
|---|---|
| Rank | `offset + index + 1` — čistý výpočet, žádné window funkce |
| Paginace | Manuální v provideru (page/limit), API Platform pagination disabled |
| Filtry | `name` = LIKE na username, `rank` = přepočet na stránku |
| Serializace | `leaderboard:read` pro rank, `character:read` pro Character uvnitř |
| Přístup | Jen pro přihlášené (`ROLE_USER`) |
| Rozšiřitelnost | DTO jde rozšířit o další computed hodnoty bez změny entity |
