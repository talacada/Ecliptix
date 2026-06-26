# Plán implementace elixírů

## Kontext

Elixíry jsou nový typ konzumovatelných itemů. Na rozdíl od equipmentu:

| Vlastnost | Equipment | Elixír |
|-----------|-----------|--------|
| Staty | Absolutní (`baseDamage: 50`) | Procentuální (`+15% DMG`) |
| Škálování levelem | Ano | Ne (stejný pro všechny levely) |
| Trvání | Permanentní | Časově omezené (1h / 24h / 7d) |
| Stackování | Ne (každý item vlastní slot) | Ano (stejný elixír = `quantity++`) |
| Shop kvóta | 8 slotů v daily rotaci | 2 sloty v daily rotaci |

Aktuální stav:
- `ItemSlotEnum::Elixir` existuje, ale `AppStory` ho přeskakuje (prázdná `else` větev)
- `InventoryManager` má komentář `//WITHOUT elixir logic NOW`
- `CharacterInventory::$quantity` existuje — stackování je připravené na úrovni DB
- `ItemDefinition` nemá žádné elixír-specifické sloupce

## Architektonické rozhodnutí: Doctrine Single Table Inheritance

**Rodič:** `ItemDefinition` — přidá se discriminator sloupec `item_type` (`equipment` | `elixir`)
**Potomek:** `ElixirDefinition extends ItemDefinition` — přidává elixír-specifická pole

Proč STI a ne separátní entitu:
- `ShopOffer`, `Item`, `CharacterInventory` — všechno dál funguje přes `ItemDefinition` bez změny
- `instanceof ElixirDefinition` je bezpečnější kontrola než `$slot === Elixir`
- Doctrine automaticky vrací správnou třídu z repository
- Rozšiřitelné — další typy (svitky, lektvary) lze přidat jako nové potomky

---

## Fáze 1: Entity

### 1.1 Nový enum `src/Entity/Item/ElixirTypeEnum.php`

```php
enum ElixirTypeEnum: string
{
    case Damage = 'damage';
    case Health = 'health';
    // budoucí: Armor, Speed, Crit, ...
}
```

### 1.2 Upravit `src/Entity/Item/ItemDefinition.php`

Přidat STI mapping:

```php
#[ORM\Entity(repositoryClass: ItemDefinitionRepository::class)]
#[ORM\InheritanceType('SINGLE_TABLE')]
#[ORM\DiscriminatorColumn(name: 'item_type', type: 'string')]
#[ORM\DiscriminatorMap([
    'equipment' => ItemDefinition::class,
    'elixir' => ElixirDefinition::class,
])]
class ItemDefinition
```

Nové nullable sloupce (jen pro elixíry):

| Pole | Typ | Nullable | Serializace |
|------|-----|----------|-------------|
| `$elixirType` | `?ElixirTypeEnum` | ano | `ShopRotation::READ_GROUP` |
| `$percentageBonus` | `?int` | ano | `ShopRotation::READ_GROUP` |
| `$durationSeconds` | `?int` | ano | `ShopRotation::READ_GROUP` |

Plus gettery/settery. Equipment itemy tyhle sloupce nechávají `null`.

### 1.3 Nová entita `src/Entity/Item/ElixirDefinition.php`

```php
#[ORM\Entity]
class ElixirDefinition extends ItemDefinition
{
    public function __construct()
    {
        parent::__construct();
        $this->setBaseDamage(0);
        $this->setBaseCrit(0);
        $this->setBaseHealth(0);
        $this->setRequiredLevel(1);
        $this->setDesiredSlot(ItemSlotEnum::Elixir);
    }
}
```

Všechna elixír-specifická pole jsou na rodiči (STI sdílí jednu tabulku). Konstruktor nastavuje výchozí hodnoty — equipment sloupce na 0, slot na `Elixir`, level na 1.

### 1.4 Migrace

Do tabulky `item_definition` přibydou sloupce:
- `item_type VARCHAR(255) NOT NULL DEFAULT 'equipment'`
- `elixir_type VARCHAR(255) DEFAULT NULL`
- `percentage_bonus INT DEFAULT NULL`
- `duration_seconds INT DEFAULT NULL`

---

## -----------------------Fáze 2: Factory a seedování

### 2.1 Nová factory `src/Factory/ElixirDefinitionFactory.php`

Elixíry používají úplně jiný kalkulační model — samostatná factory je čistší než větev v `ItemDefinitionFactory`.

Konfigurace tierů (rarity → procenta + trvání):

```php
private const array ELIXIR_TIERS = [
    ElixirTypeEnum::Damage->value => [
        ItemRarityEnum::Common->value => ['bonus' => 10, 'seconds' => 3600],       // 1h
        ItemRarityEnum::Rare->value   => ['bonus' => 15, 'seconds' => 86400],      // 24h
        ItemRarityEnum::Epic->value   => ['bonus' => 30, 'seconds' => 604800],     // 7d
    ],
    ElixirTypeEnum::Health->value => [
        ItemRarityEnum::Common->value => ['bonus' => 5,  'seconds' => 3600],
        ItemRarityEnum::Rare->value   => ['bonus' => 15, 'seconds' => 86400],
        ItemRarityEnum::Epic->value   => ['bonus' => 45, 'seconds' => 604800],
    ],
];
```

Ceny flat podle rarity (neodvozují se od statů jako u equipmentu):

```php
private const array GOLD_PRICES = [
    ItemRarityEnum::Common->value => 100,
    ItemRarityEnum::Rare->value   => 500,
    ItemRarityEnum::Epic->value   => 2000,
];
```

Legendary elixíry v MVP nejsou.

`defaults()` metoda:
- Náhodný `ElixirTypeEnum`
- Náhodná rarita (Common / Rare / Epic)
- Podle konfigurace nastaví `percentageBonus`, `durationSeconds`, `baseGoldPrice`
- `baseDiamondPrice = 0` (pro elixíry se v MVP diamanty nepoužívají)
- Description generované: např. `"+15% damage po dobu 24h"`

### 2.2 Upravit `src/Factory/ItemDefinitionFactory.php`

Odstranit `'elixir'` z polí `STAT_SCALING` a `STAT_BASE` — už je to mrtvý kód, elixíry mají vlastní factory.

### 2.3 Upravit `src/Story/AppStory.php`

Doplnit generování elixírů do prázdné `else` větve:

```php
if ($slot != ItemSlotEnum::Elixir) {
    // stávající generování equipmentu (beze změny)
} else {
    // 2 typy × 3 rarity = 6 elixírových definicí
    foreach (ElixirTypeEnum::cases() as $elixirType) {
        foreach ([ItemRarityEnum::Common, ItemRarityEnum::Rare, ItemRarityEnum::Epic] as $rarity) {
            ElixirDefinitionFactory::new()
                ->with([
                    'elixirType' => $elixirType,
                    'rarity' => $rarity,
                ])
                ->create();
        }
    }
}
```

---

## Fáze 3: Repository

### 3.1 Upravit `src/Repository/Item/ItemDefinitionRepository.php`

Přidat metodu pro výběr náhodného elixíru (bez filtru na level):

```php
public function findRandomElixir(): ?ItemDefinition
{
    return $this->createQueryBuilder('i')
        ->andWhere('i.item_type = :type')
        ->setParameter('type', 'elixir')
        ->orderBy('RAND()')
        ->setMaxResults(1)
        ->getQuery()
        ->getOneOrNullResult();
}
```

`findRandomByLevel()` zůstává beze změny — elixíry se jím nevybírají (nemají `requiredLevel` v rozsahu).

---

## Fáze 4: Shop generování

### 4.1 Upravit `src/Service/Shop/RotationGenerator.php`

Přidat konfiguraci kvóty místo hardcodovaných 8:

```php
private const array OFFER_QUOTA = [
    'equipment' => 8,
    'elixir'    => 2,
];
```

Po stávajícím cyklu pro equipment přidat cyklus pro elixíry:

```php
// Elixir offers — no level filter, no bonus stat rolling
for ($i = 0; $i < self::OFFER_QUOTA['elixir']; $i++) {
    $elixirDef = $this->itemDefinitionRepository->findRandomElixir();
    if ($elixirDef === null) {
        continue;
    }
    $offer = new ShopOffer($shopRotation, $elixirDef);
    $offer->setGoldPrice((int)($elixirDef->getBaseGoldPrice() * (mt_rand(80, 120) / 100)));
    $offer->setDiamondPrice($elixirDef->getBaseDiamondPrice());
    // Bonus staty se pro elixíry nerolují — baseDamage/baseCrit/baseHealth jsou 0
    $shopRotation->addShopOffer($offer);
}
```

Proč elixíry nemají bonus staty: `ItemFactory::rollBonusStats()` vrací `[0, 0, 0]` když jsou base staty 0 — kontrola `if ($definition->getBaseDamage() > 0)` to zařídí. Není potřeba speciální větev.

---

## Fáze 5: Stackování v inventáři

### 5.1 Upravit `src/Repository/Character/CharacterInventoryRepository.php`

Přidat metodu pro nalezení existujícího stacku elixíru v baťůžku:

```php
public function findBackpackByDefinition(Character $character, int $definitionId): ?CharacterInventory
{
    return $this->createQueryBuilder('ci')
        ->join('ci.item', 'i')
        ->andWhere('ci.character = :character')
        ->andWhere('i.definition = :definitionId')
        ->andWhere('ci.container = :container')
        ->setParameter('character', $character)
        ->setParameter('definitionId', $definitionId)
        ->setParameter('container', InventoryContainerEnum::Backpack)
        ->getQuery()
        ->getOneOrNullResult();
}
```

### 5.2 Upravit `src/Service/Inventory/InventoryManager.php`

Do `addToBackpack()` přidat stackovací logiku:

```php
public function addToBackpack(Character $character, Item $item): CharacterInventory
{
    $definition = $item->getDefinition();

    // Elixíry stackujeme
    if ($definition instanceof ElixirDefinition) {
        $existing = $this->characterInventoryRepository
            ->findBackpackByDefinition($character, $definition->getId());

        if ($existing !== null) {
            $existing->setQuantity($existing->getQuantity() + 1);
            return $existing;
        }
    }

    // První elixír daného typu nebo equipment — vytvořit nový slot
    if ($character->getBackpackCapacity() <= count($this->characterInventoryRepository->getUnequippedItems($character))) {
        throw new Exception("Not enough backpack space");
    }

    $characterInventory = new CharacterInventory();
    $characterInventory->setCharacter($character);
    $characterInventory->setItem($item);
    $characterInventory->setPosition($this->getFirstAvailablePosition($character));
    $characterInventory->setContainer(InventoryContainerEnum::Backpack);

    return $characterInventory;
}
```

Odstranit komentář `//WITHOUT elixir logic NOW`.

### 5.3 Upravit `src/State/Processor/Shop/Offer/ShopOfferBuyProcessor.php`

Při nákupu elixíru nevalidovat kapacitu baťůžku (stackování nepřidává nový slot):

```php
$isElixir = $data->getItemDefinition() instanceof ElixirDefinition;

if (!$isElixir && $character->getBackpackCapacity() <= count($this->characterInventoryRepository->getUnequippedItems($character))) {
    throw new Exception("Not enough backpack space");
}
```

---

## Fáze 6: API výstup (ItemViewDTO)

### 6.1 Upravit `src/ApiResource/Item/ItemViewDTO.php`

Přidat elixír-specifická pole do DTO:

```php
#[Groups([self::READ_GROUP])]
private ?string $elixirType = null;

#[Groups([self::READ_GROUP])]
private ?int $percentageBonus = null;

#[Groups([self::READ_GROUP])]
private ?int $durationSeconds = null;
```

Upravit `buildDtoFromItem()` a `buildDtoOnlyWithBonusStats()` — pokud je `$definition instanceof ElixirDefinition`, vyplnit elixír pole:

```php
if ($definition instanceof ElixirDefinition) {
    $this->setElixirType($definition->getElixirType()?->value);
    $this->setPercentageBonus($definition->getPercentageBonus());
    $this->setDurationSeconds($definition->getDurationSeconds());
}
```

---

## Fáze 7: Úklid

- `ItemDefinitionFactory`: odstranit `'elixir'` z `STAT_SCALING` a `STAT_BASE`
- `InventoryManager`: odstranit komentář `//WITHOUT elixir logic NOW`
- `RotationGenerator`: odstranit `//TODO make quotes`

---

## Soubory

| Akce | Soubor |
|------|--------|
| **NOVÝ** | `src/Entity/Item/ElixirTypeEnum.php` |
| **NOVÝ** | `src/Entity/Item/ElixirDefinition.php` |
| **NOVÝ** | `src/Factory/ElixirDefinitionFactory.php` |
| UPRAVIT | `src/Entity/Item/ItemDefinition.php` |
| UPRAVIT | `src/Factory/ItemDefinitionFactory.php` |
| UPRAVIT | `src/Story/AppStory.php` |
| UPRAVIT | `src/Repository/Item/ItemDefinitionRepository.php` |
| UPRAVIT | `src/Service/Shop/RotationGenerator.php` |
| UPRAVIT | `src/Service/Inventory/InventoryManager.php` |
| UPRAVIT | `src/State/Processor/Shop/Offer/ShopOfferBuyProcessor.php` |
| UPRAVIT | `src/ApiResource/Item/ItemViewDTO.php` |
| UPRAVIT | `src/Repository/Character/CharacterInventoryRepository.php` |
| **NOVÁ** | Migrace (doctrine migration) |

---

## Ověření

1. `make build` — aplikace nastartuje
2. `make migration && make migrate` — schéma se zaktualizuje
3. Fixtures (`AppStory`) vytvoří ~700 equipment definicí + 6 elixírových (2 typy × 3 rarity)
4. `php bin/console app:shop:generate-rotations` — daily rotace obsahuje 8 equipment + 2 elixír nabídky
5. `POST /shop/offer/{id}` pro elixír — vytvoří se `CharacterInventory` s `quantity = 1`
6. Druhý nákup stejného elixíru — `quantity` se inkrementuje, nový slot nevzniká
7. `make test` — bez regresí
