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

| Metoda | Cesta | Resource |
|--------|-------|----------|
| `POST` | `/character/inventory/{id}/use` | `CharacterInventory` (vedle `sell`) |
| `POST` | `/character/elixir/{id}/remove` | `ActiveElixir` |

Proč `use` na `CharacterInventory` a ne na samostatném resource?
- Konzistentní s `POST /character/inventory/{inventoryId}/sell` — obojí je akce nad inventářovou položkou
- Žádné body, jen URL parametr — `{id}` je identifikátor inventáře, ze kterého se elixír aktivuje
- Provider `CharacterInventoryProvider` už umí načíst `CharacterInventory` podle ID — reuse
- Odpadá `UseElixirInput` DTO — processor dostane rovnou entitu

**GET Character** vrací pole aktivních elixírů i s definicí a `expiresAt`.

---

## Architektonická rozhodnutí

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
- `ActiveElixirProvider` — před vrácením elixíru pro remove
- `ElixirUseProcessor` — před kontrolou limitu (expirované mohly uvolnit místo)

---

## Fáze 3: Use endpoint (na CharacterInventory)

### 3.1 Přidat operaci na `CharacterInventory`

V `src/Entity/Character/CharacterInventory.php` přidat do `operations` pole:

```php
new Post(
    uriTemplate: 'character/inventory/{inventoryId}/use',
    uriVariables: [
        'inventoryId' => new Link(
            fromClass: CharacterInventory::class,
            identifiers: ['id']
        ),
    ],
    deserialize: false,  // žádné request body, ID je v URL
    provider: CharacterInventoryProvider::class,
    processor: ElixirUseProcessor::class,
),
```

Stejný pattern jako `sell` — `CharacterInventoryProvider` načte entitu podle `{inventoryId}`, processor ji dostane a zpracuje.

### 3.2 Processor `src/State/Processor/Character/Inventory/ElixirUseProcessor.php`

```php
// Pseudokód logiky:

class ElixirUseProcessor implements ProcessorInterface
{
    // DI: LoggedInCharacter, ActiveElixirCleanupService, EntityManagerInterface

    function process(mixed $data, ...)
    {
        assert($data instanceof CharacterInventory)
        $character = loggedInCharacter()

        // Ownership: provider načetl CharacterInventory, ale musí patřit přihlášenému
        if ($data->getCharacter() !== $character) throw 404

        // ── Krok 1: Cleanup ──
        cleanupService->removeExpired($character)

        // ── Krok 2: Ověř, že item v inventáři je elixír ──
        $definition = $data->getItem()->getDefinition()
        if (!$definition instanceof ElixirDefinition) throw "není elixír"

        // ── Krok 3: Najdi existující aktivní elixír stejného typu ──
        $existing = najdi v $character->getActiveElixirs()
                    kde getItemDefinition()->getId() === $definition->getId()

        // ── Krok 4: Pokud neexistuje, ověř kapacitu ──
        if ($existing === null && count($character->getActiveElixirs()) >= 3) {
            throw "max 3 aktivní elixíry"
        }

        // ── Krok 5: Aktivace ──
        if ($existing !== null) {
            // Prodloužení: expiresAt += duration
            $existing->setExpiresAt(
                $existing->getExpiresAt()->modify(
                    '+' . $definition->getDurationSeconds() . ' seconds'
                )
            )
        } else {
            // Nový ActiveElixir
            $activeElixir = new ActiveElixir()
            $activeElixir->setCharacter($character)
            $activeElixir->setItemDefinition($definition)
            $activeElixir->setCreatedAt(new DateTimeImmutable())
            $activeElixir->setExpiresAt(
                (new DateTimeImmutable())->modify(
                    '+' . $definition->getDurationSeconds() . ' seconds'
                )
            )
            $entityManager->persist($activeElixir)
        }

        // ── Krok 6: Odeber z baťůžku ──
        // Stejná logika jako v CharacterInventorySellProcessor:
        if ($data->getQuantity() > 1) {
            $data->setQuantity($data->getQuantity() - 1)
        } else {
            $entityManager->remove($data->getItem())
            $entityManager->remove($data)
        }

        $entityManager->flush()
    }
}
```

**Co odpadlo oproti původnímu návrhu:**
- `UseElixirInput` DTO — není potřeba, `inventoryId` je v URL, provider načte entitu
- Hledání inventáře v procesoru — provider už ho načetl a ověřil existenci
- `CharacterInventoryRepository` v DI procesoru — netřeba

---

## ----------------------------- Fáze 4: Remove endpoint (na ActiveElixir)

### 4.1 ApiResource na `ActiveElixir`

```php
#[ApiResource(
    operations: [
        new Post(
            uriTemplate: '{id}/remove',
            uriVariables: [
                'id' => new Link(fromClass: ActiveElixir::class, identifiers: ['id']),
            ],
            deserialize: false,
            provider: ActiveElixirProvider::class,
            processor: ElixirRemoveProcessor::class,
        ),
    ],
    routePrefix: 'character/elixir/',
    security: 'is_granted("ROLE_USER")',
    normalizationContext: ['groups' => ['activeElixir:read']],
)]
```

### 4.2 Provider `src/State/Provider/Character/Elixir/ActiveElixirProvider.php`

```php
// Pseudokód:

class ActiveElixirProvider implements ProviderInterface
{
    // DI: LoggedInCharacter, ActiveElixirCleanupService

    function provide(Operation $operation, array $uriVariables, array $context): object|array|null
    {
        $character = loggedInCharacter()
        cleanupService->removeExpired($character)

        // Najdi ActiveElixir podle ID z $uriVariables['id']
        // Musí patřit characterovi, jinak 404
        $elixir = najdi v $character->getActiveElixirs() podle ID
        if (!$elixir) throw 404

        // Naplň DTO pro serializaci
        $dto = new ItemViewDTO()
        $dto->buildDtoFromItemDefinition($elixir->getItemDefinition())
        $elixir->setDefinitionViewDTO($dto)

        return $elixir
    }
}
```

### 4.3 Processor `src/State/Processor/Character/Elixir/ElixirRemoveProcessor.php`

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

---

## Fáze 5: GET Character — zobrazit aktivní elixíry

### 5.1 Upravit `MineCharacterProvider`

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

## Fáze 6: ItemViewDTO — nová metoda

### 6.1 Přidat `buildDtoFromItemDefinition()` do `src/ApiResource/Item/ItemViewDTO.php`

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
| **NOVÝ** | `src/State/Processor/Character/Inventory/ElixirUseProcessor.php` |
| **NOVÝ** | `src/State/Processor/Character/Elixir/ElixirRemoveProcessor.php` |
| **NOVÁ** | Migrace (doctrine migration diff) |
| UPRAVIT | `src/Entity/Character/Character.php` — přidat `$activeElixirs` kolekci |
| UPRAVIT | `src/Entity/Character/CharacterInventory.php` — přidat `use` operaci |
| UPRAVIT | `src/State/Provider/Character/MineCharacterProvider.php` — cleanup + DTO |
| UPRAVIT | `src/ApiResource/Item/ItemViewDTO.php` — `buildDtoFromItemDefinition()` |

---

## Ověřovací scénáře

1. **Build** — `make build` projde, aplikace nastartuje
2. **Migrace** — `make migration && make migrate`, tabulka `active_elixir` existuje
3. **Use** — `POST /character/inventory/{id}/use`:
   - Aktivuje elixír, v DB přibyde `ActiveElixir`, quantity v inventory klesne
4. **Prodloužení** — stejný elixír znovu:
   - `expiresAt` se prodlouží, nový záznam NEVZNIKNE, quantity znovu klesne
5. **Limit** — pokus aktivovat 4. různý typ:
   - Vrátí chybu (max 3)
6. **Ochrana** — pokus aktivovat equipment:
   - Vrátí chybu (není elixír)
7. **Remove** — `POST /character/elixir/{id}/remove`:
   - Záznam zmizí z DB
8. **GET Character** — `GET /character`:
   - Obsahuje pole `activeElixirs` s `definition` (ItemViewDTO), `expiresAt`, `createdAt`
9. **Expirovaný cleanup** — nastavit `expiresAt` do minulosti, zavolat GET /character:
   - Expirovaný elixír tam není
10. **Testy** — `make test` bez regresí
