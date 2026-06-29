# Plán implementace — Aktivní elixíry (Use / Remove)

## Kontext

Elixíry už fungují: definice (`ElixirDefinition`), shop, nákup, stackování v baťůžku, prodej. Teď přidáme jejich **aktivaci** — hráč je zkonzumuje a získají časově omezený efekt.

| Pravidlo | Hodnota |
|----------|---------|
| Max aktivních elixírů současně | 3 (konstanta, nelze zvýšit) |
| Použití stejného elixíru znovu | Prodlouží trvání: `expiresAt += duration` |
| Zrušení aktivního elixíru | Kdykoliv, bez refundu |
| Expirované elixíry | Líný cleanup — při jakékoliv interakci s characterem |
| Efekt na staty | Zatím jen data — výpočet bonusů později (combat / GET Character) |

**Endpointy:**

| Metoda | Cesta | Co dělá |
|--------|-------|---------|
| `POST` | `/character/elixir/use` | Aktivuje elixír z baťůžku (input: `inventoryId`) |
| `POST` | `/character/elixir/{id}/remove` | Zruší aktivní elixír |

**GET Character** vrací pole aktivních elixírů i s definicí a `expiresAt`.

---

## Architektonické rozhodnutí

**Proč samostatná entita `ActiveElixir` a ne jen další stav na `CharacterInventory`?**

- `CharacterInventory` = item v baťůžku/equipu, vázaný na instanci `Item`
- `ActiveElixir` = aktivovaný efekt, vázaný přímo na `ItemDefinition` (instance `Item` byla zkonzumována)
- Oddělené životní cykly — inventory lze prodat, active elixir lze jen zrušit
- GET Character přirozeně serializuje kolekci `activeElixirs`

**Proč `ItemDefinition` a ne `ElixirDefinition`?**

- Všechny systémy (`ItemViewDTO`, repository) už s `ItemDefinition` pracují
- STI zařídí, že `$itemDefinition instanceof ElixirDefinition === true`
- Když někdo omylem zkusí aktivovat equipment, ochrání nás to na úrovni procesoru

**Proč líný cleanup a ne cron?**

- Expirované elixíry nemají herní efekt — vadí jen při zobrazení
- Cleanup při interakci je zdarma (už tak načítáme charactera)
- Cron s přesností na minuty je overkill pro tuhle feature

**Pattern: `ActiveElixir` jako samostatné ApiResource**

Stejný vzorec jako `CharacterInventory` — vlastní entita, vlastní prefix, vlastní provider. Není to sub-resource Characteru.

---

## Fáze 1: Entita a migrace

### 1.1 `src/Entity/Character/ActiveElixir.php`

```php
// Pseudokód — klíčové properties a jejich anotace:

#[ORM\Entity]
class ActiveElixir
{
    // ── PK ──
    #[ORM\Id, ORM\GeneratedValue]
    private ?int $id = null;

    // ── Vazba na Character (inverzní strana: Character::$activeElixirs) ──
    #[ORM\ManyToOne(inversedBy: 'activeElixirs')]
    #[ORM\JoinColumn(nullable: false)]
    private Character $character;

    // ── Reference na definici elixíru (NE instanci Item!) ──
    // STI zařídí, že vždy dostaneme ElixirDefinition
    #[ORM\ManyToOne(targetEntity: ItemDefinition::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ItemDefinition $itemDefinition;

    // ── Kdy vyprší ──
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private DateTimeImmutable $expiresAt;

    // ── Kdy byl aktivován (pro pořádek, ne nutné pro logiku) ──
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private DateTimeImmutable $createdAt;

    // ── Transient — naplní provider, serializuje se jako "definition" ──
    // Stejný pattern jako CharacterInventory::$itemViewDTO
    #[Groups(['activeElixir:read'])]
    #[SerializedName('definition')]
    private ?ItemViewDTO $definitionViewDTO = null;

    // + gettery/settery ke všemu
}
```

### 1.2 Upravit `Character` — přidat kolekci

V `src/Entity/Character/Character.php`:

```php
// Nová OneToMany kolekce (stejný vzorec jako $characterInventories):
#[ORM\OneToMany(targetEntity: ActiveElixir::class, mappedBy: 'character', orphanRemoval: true)]
#[Groups([self::READ_GROUP])]  // ať se serializuje při GET /character
private Collection $activeElixirs;

// Konstruktor: $this->activeElixirs = new ArrayCollection();
// Getter: getActiveElixirs(): Collection
// Setter/add/remove dle potřeby
```

### 1.3 Migrace

Přibyde tabulka `active_elixir` se sloupci:
- `id` (PK, auto)
- `character_id` (FK → character, NOT NULL)
- `item_definition_id` (FK → item_definition, NOT NULL)
- `expires_at` (datetime, NOT NULL)
- `created_at` (datetime, NOT NULL)

---

## Fáze 2: Cleanup služba

### 2.1 `src/Service/Elixir/ActiveElixirCleanupService.php`

```php
// Pseudokód:

class ActiveElixirCleanupService
{
    // DI: EntityManagerInterface

    function removeExpired(Character $character): void
    {
        // Projdi $character->getActiveElixirs()
        // Pokud getExpiresAt() < new DateTimeImmutable():
        //   → $character->getActiveElixirs()->removeElement($elixir)
        //   → $entityManager->remove($elixir)
        // Po průchodu: $entityManager->flush()
    }
}
```

**Kde se bude volat:**
- `MineCharacterProvider` — před vrácením characteru
- `ActiveElixirProvider` — před vrácením kolekce / jednoho elixíru
- `ElixirUseProcessor` — před kontrolou limitu (expirované mohly uvolnit místo)

---

## Fáze 3: Provider

### 3.1 `src/State/Provider/Character/Elixir/ActiveElixirProvider.php`

```php
// Pseudokód:

class ActiveElixirProvider implements ProviderInterface
{
    // DI: LoggedInCharacter, ActiveElixirCleanupService

    function provide(Operation $operation, array $uriVariables, array $context): object|array|null
    {
        $character = $this->loggedInCharacter->getCharacter();
        $this->cleanupService->removeExpired($character);

        // Pokud je 'id' v $uriVariables → vracíme JEDEN ActiveElixir:
        if (isset($uriVariables['id'])) {
            $elixir = najdi podle ID z $character->getActiveElixirs()
            if ($elixir === null) throw 404
            // Naplň DTO
            $dto = new ItemViewDTO()
            $dto->buildDtoFromItemDefinition($elixir->getItemDefinition())
            $elixir->setDefinitionViewDTO($dto)
            return $elixir
        }

        // Jinak vracíme KOLEKCI:
        foreach ($character->getActiveElixirs() as $elixir) {
            $dto = new ItemViewDTO()
            $dto->buildDtoFromItemDefinition($elixir->getItemDefinition())
            $elixir->setDefinitionViewDTO($dto)
        }
        return $character->getActiveElixirs()->toArray()
    }
}
```

Tenhle provider slouží pro:
- `POST /character/elixir/{id}/remove` — načte jeden `ActiveElixir` podle ID
- Případné budoucí `GET /character/elixir` — vrátí kolekci

---

## Fáze 4: Use endpoint

### 4.1 Input DTO `src/ApiResource/Character/Elixir/UseElixirInput.php`

```php
// Pseudokód:

readonly class UseElixirInput
{
    // __construct s #[Assert\NotBlank] private int $inventoryId
    // getter
}
```

Je to `readonly` třída s property `inventoryId` — odkazuje na `CharacterInventory::$id`, ze kterého hráč chce elixír aktivovat.

### 4.2 Processor `src/State/Processor/Character/Elixir/ElixirUseProcessor.php`

```php
// Pseudokód logiky (detaily nechávám na tobě):

class ElixirUseProcessor implements ProcessorInterface
{
    // DI: LoggedInCharacter, CharacterInventoryRepository
    //      ActiveElixirCleanupService, EntityManagerInterface

    function process(mixed $data, ...)
    {
        assert($data instanceof UseElixirInput)
        $character = loggedInCharacter()

        // ── Krok 1: Cleanup ──
        cleanupService->removeExpired($character)
        // Teď máme přesný počet aktivních, expirované jsou pryč

        // ── Krok 2: Najdi inventory slot ──
        $inventory = inventoryRepo->getInventoryById($data->getInventoryId())
        if (!$inventory || $inventory->getCharacter() !== $character) throw 404

        // ── Krok 3: Ověř, že item v inventáři je elixír ──
        $definition = $inventory->getItem()->getDefinition()
        if (!$definition instanceof ElixirDefinition) throw "není elixír"

        // ── Krok 4: Najdi existující aktivní elixír stejného typu ──
        $existing = najdi v $character->getActiveElixirs()
                    kde getItemDefinition()->getId() === $definition->getId()

        // ── Krok 5: Pokud neexistuje, ověř kapacitu ──
        if ($existing === null && count($character->getActiveElixirs()) >= 3) {
            throw "max 3 aktivní elixíry"
        }

        // ── Krok 6: Aktivace ──
        if ($existing !== null) {
            // Prodloužení: expiresAt += duration
            $existing->setExpiresAt(
                $existing->getExpiresAt()->modify('+' . $definition->getDurationSeconds() . ' seconds')
            )
        } else {
            // Nový aktivní elixír
            $activeElixir = new ActiveElixir()
            $activeElixir->setCharacter($character)
            $activeElixir->setItemDefinition($definition)
            $activeElixir->setCreatedAt(new DateTimeImmutable())
            $activeElixir->setExpiresAt(
                (new DateTimeImmutable())->modify('+' . $definition->getDurationSeconds() . ' seconds')
            )
            $entityManager->persist($activeElixir)
        }

        // ── Krok 7: Odeber z baťůžku ──
        // Stejná logika jako v CharacterInventorySellProcessor:
        if ($inventory->getQuantity() > 1) {
            $inventory->setQuantity($inventory->getQuantity() - 1)
        } else {
            $entityManager->remove($inventory->getItem())
            $entityManager->remove($inventory)
        }

        $entityManager->flush()
    }
}
```

### 4.3 ApiResource — operace na `ActiveElixir`

```php
#[ApiResource(
    operations: [
        // POST /character/elixir/use — nemá {id}, vstup je DTO
        new Post(
            uriTemplate: 'use',
            input: UseElixirInput::class,
            processor: ElixirUseProcessor::class,
        ),
        // POST /character/elixir/{id}/remove — má {id}, načte entitu
        new Post(
            uriTemplate: '{id}/remove',
            uriVariables: [
                'id' => new Link(fromClass: ActiveElixir::class, identifiers: ['id']),
            ],
            deserialize: false,  // žádné request body
            provider: ActiveElixirProvider::class,
            processor: ElixirRemoveProcessor::class,
        ),
    ],
    routePrefix: 'character/elixir/',
    security: 'is_granted("ROLE_USER")',
    normalizationContext: ['groups' => ['activeElixir:read']],
)]
```

---

## Fáze 5: Remove endpoint

### 5.1 Processor `src/State/Processor/Character/Elixir/ElixirRemoveProcessor.php`

```php
// Pseudokód:

class ElixirRemoveProcessor implements ProcessorInterface
{
    // DI: LoggedInCharacter, EntityManagerInterface

    function process(mixed $data, ...)
    {
        assert($data instanceof ActiveElixir)
        $character = loggedInCharacter()

        // Ownership check
        if ($data->getCharacter() !== $character) throw "není tvůj"

        // Prostě smaž — žádný refund
        $entityManager->remove($data)
        $entityManager->flush()
    }
}
```

Žádné složitosti. Provider předtím načetl entitu a ověřil, že patří characterovi (v rámci provideru). Processor jen maže.

---

## Fáze 6: GET Character — zobrazit aktivní elixíry

### 6.1 Upravit `MineCharacterProvider`

```php
// Pseudokód úpravy:

function provide(...)
{
    $character = loggedInCharacter()

    // ★ PŘIDAT: Cleanup expirovaných
    $this->cleanupService->removeExpired($character)

    // ★ PŘIDAT: Naplnit DTO pro každý aktivní elixír
    foreach ($character->getActiveElixirs() as $elixir) {
        $dto = new ItemViewDTO()
        $dto->buildDtoFromItemDefinition($elixir->getItemDefinition())
        $elixir->setDefinitionViewDTO($dto)
    }

    return $character
}
```

Díky `#[Groups([self::READ_GROUP])]` na `Character::$activeElixirs` se kolekce automaticky serializuje jako součást GET /character odpovědi.

---

## Fáze 7: ItemViewDTO — nová metoda

### 7.1 Přidat `buildDtoFromItemDefinition()` do `src/ApiResource/Item/ItemViewDTO.php`

```php
// Pseudokód:

function buildDtoFromItemDefinition(ItemDefinition $definition): void
{
    // Nastav společné položky:
    $this->setName($definition->getName())
    $this->setDescription($definition->getDescription())
    $this->setRequiredLevel($definition->getRequiredLevel())
    $this->setSlot($definition->getDesiredSlot())

    // Pro elixíry přidej elixír-specifická pole:
    if ($definition instanceof ElixirDefinition) {
        $this->setElixirType($definition->getElixirType()->value)
        $this->setPercentageBonus($definition->getPercentageBonus())
        $this->setDurationSeconds($definition->getDurationSeconds())
    }
    // damage/crit/health zůstávají null — nemáme Item s bonus staty
}
```

**Proč nová metoda a ne reuse `buildDtoFromItem`?**

`ActiveElixir` nemá instanci `Item` — při aktivaci se `Item` (z baťůžku) zkonzumuje (quantity-- nebo smazání). Aktivní záznam už jen odkazuje na definici. Nemáme odkud vzít bonus staty — což pro elixíry nevadí, protože bonus staty jsou vždy 0.

---

## Přehled souborů

| Akce | Soubor |
|------|--------|
| **NOVÝ** | `src/Entity/Character/ActiveElixir.php` |
| **NOVÝ** | `src/Service/Elixir/ActiveElixirCleanupService.php` |
| **NOVÝ** | `src/State/Provider/Character/Elixir/ActiveElixirProvider.php` |
| **NOVÝ** | `src/ApiResource/Character/Elixir/UseElixirInput.php` |
| **NOVÝ** | `src/State/Processor/Character/Elixir/ElixirUseProcessor.php` |
| **NOVÝ** | `src/State/Processor/Character/Elixir/ElixirRemoveProcessor.php` |
| **NOVÁ** | Migrace (doctrine migration diff) |
| UPRAVIT | `src/Entity/Character/Character.php` — přidat `$activeElixirs` kolekci |
| UPRAVIT | `src/State/Provider/Character/MineCharacterProvider.php` — cleanup + DTO |
| UPRAVIT | `src/ApiResource/Item/ItemViewDTO.php` — `buildDtoFromItemDefinition()` |

---

## Ověřovací scénáře

1. **Build** — `make build` projde, aplikace nastartuje
2. **Migrace** — `make migration && make migrate`, tabulka `active_elixir` existuje
3. **Use** — `POST /character/elixir/use { inventoryId: X }`:
   - Aktivuje elixír → vrátí 200, v DB přibyde `ActiveElixir`, quantity v inventory klesne
4. **Prodloužení** — stejný elixír znovu:
   - `expiresAt` se prodlouží, nový záznam NEVZNIKNE, quantity znovu klesne
5. **Limit** — pokus aktivovat 4. různý typ:
   - Vrátí chybu (max 3)
6. **Remove** — `POST /character/elixir/{id}/remove`:
   - Záznam zmizí z DB
7. **GET Character** — `GET /character`:
   - Obsahuje pole `activeElixirs` s `definition` (ItemViewDTO), `expiresAt`, `createdAt`
8. **Expirovaný cleanup** — nastavit `expiresAt` do minulosti, zavolat GET /character:
   - Expirovaný elixír tam není
9. **Testy** — `make test` bez regresí