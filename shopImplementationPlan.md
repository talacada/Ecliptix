# Shop — implementační plán

> **Ecliptix** — Symfony 8 + API Platform, browser RPG

## Přehled

Plán přidává do hry **shop systém**:

1. Každou půlnoc se cronem vygeneruje **denní rotace** 8 náhodných nabídek pro každou postavu.
2. Hráč si zobrazí svou rotaci (GET) a **koupí item** (POST) — ten mu přijde do **baťůžku**.
3. Z baťůžku si item **equipne** do příslušného slotu (Weapon, Helmet, …), nebo ho unequipne zpět.

---

## Datový model — jak to funguje

### Inventory = equip sloty + baťůžek

Postava má **8 fixních equip slotů** (jeden na každý typ itemu):

```
Weapon, Helmet, Armour, Boots, Elixir, RingLeft, RingRight, Necklace
```

Plus **baťůžek** — `Character.backpackCapacity` (výchozí 5), který omezuje počet **neequipnutých** itemů.

`CharacterInventory` je pivot mezi `Character` a `Item`. **Vzniká až když postava získá item** (nákup, loot…). Žádné prázdné řádky předem.

| Pole        | Typ                 | Význam                                                    | 
|-------------|---------------------|-----------------------------------------------------------|
| `character` | ManyToOne→Character | Čí je to item                                             |
| `item`      | OneToOne→Item       | Konkrétní item (nullable v DB, ale v praxi vždy nastaven) |
| `equipped`  | bool                | `true` = nasazený, `false` = v baťůžku                    |
| `slot`      | ItemSlot, nullable  | `null` = v baťůžku; nastaveno = equipnutý v tomto slotu   |
| `quantity`  | int                 | 1 pro vybavení, 1+ pro elixíry (stackují se)              |

**Validační pravidla:**

- **Při equipu:** V cílovém slotu nesmí být jiný equipnutý item → kontrola `COUNT(*) WHERE character=X AND slot=Y AND equipped=true`.
- **Při unequipu / nákupu:** Počet `CharacterInventory WHERE equipped=false` < `backpackCapacity`.
- **Elixíry:** Jako jediné podporují `quantity > 1`. Při nákupu elixíru se nezakládá nový řádek, ale inkrementuje se quantity existujícího stacku.

### Shop rotace a offery

```
ShopRotation (jedna denní rotace na charactera)
 ├── validFrom / validUntil (časové okno)
 ├── type: Daily (enum; Weekly a Event přijdou později)
 └── [1:N] ShopOffer (8 nabídek v rotaci)
      ├── goldPrice / diamondPrice (cena s variací ±20 %)
      └── ItemDefinition (co se prodává)
```

- **Čištění:** Při vygenerování nové rotace se staré daily rotace charactera **smažou** (kaskáda na offery). V DB je vždy jen aktuální rotace.
- **Cena:** `ItemDefinition` má `baseGoldPrice` a `baseDiamondPrice` (ručně nastavené při seedu). Shop při generování přidá **±20 % varianci**.
- **Platba:** Vždy oběma měnami současně (gold AND diamonds). Běžné itemy mají `diamondPrice = 0`.

### ItemDefinition

Šablona itemu — definuje, co item **může být**:

| Pole               | Typ           | Příklad      |
|--------------------|---------------|--------------|
| `name`             | string        | "Iron Sword" |
| `desiredSlot`      | ItemSlot enum | `Weapon`     |
| `rarity`           | Rarity enum   | `Common`     |
| `baseDamage`       | int           | 5            |
| `baseCrit`         | int           | 1            |
| `baseHealth`       | int           | 0            |
| `requiredLevel`    | int           | 1            |
| `baseGoldPrice`    | int           | 100          |
| `baseDiamondPrice` | int           | 0            |

### Item (instance)

Když hráč koupí item, vytvoří se `Item` — konkrétní instance s randomizovanými bonus staty:

| Pole          | Význam                          | 
|---------------|---------------------------------|
| `definition`  | ManyToOne→ItemDefinition        |
| `bonusDamage` | Random variance od `baseDamage` |
| `bonusCrit`   | Random variance od `baseCrit`   |
| `bonusHealth` | Random variance od `baseHealth` |

---

## Enumy

### ItemSlot
```php
enum ItemSlot: string {
    case Weapon    = 'weapon';
    case Helmet    = 'helmet';
    case Armour    = 'armour';
    case Boots     = 'boots';
    case Elixir    = 'elixir';
    case RingLeft  = 'ring_left';
    case RingRight = 'ring_right';
    case Necklace  = 'necklace';
}
```

### Rarity
```php
enum Rarity: string {
    case Common    = 'common';
    case Uncommon  = 'uncommon';
    case Rare      = 'rare';
    case Epic      = 'epic';
    case Legendary = 'legendary';
}
```

Rarity multipliér pro cenu a bonus staty: Common=1.0, Uncommon=1.5, Rare=2.5, Epic=5.0, Legendary=10.0. Aplikuje se do `baseGoldPrice`/`baseDiamondPrice` už v seed datech.

### RotationType
```php
enum RotationType: string {
    case Daily  = 'daily';
    case Weekly = 'weekly';
    case Event  = 'event';
}
```

MVP implementuje jen `Daily`.

---

## `API endpointy-------------------------------`

| Metoda | URI                                       | Fáze | Popis                                                    | 
|--------|-------------------------------------------|------|----------------------------------------------------------|
| `GET`  | `/api/shop_rotations`                     | F3   | Aktivní rotace hráče (jen čtení). Bez rotace vrátí `[]`. |
| `POST` | `/api/shop/offers/{id}/buy`               | F4   | Koupit item → jde do baťůžku.                            |
| `POST` | `/api/character_inventories/{id}/equip`   | F5   | Equipnout item z baťůžku do slotu.                       |
| `POST` | `/api/character_inventories/{id}/unequip` | F5   | Dát item ze slotu zpět do baťůžku.                       |

Všechny (kromě registrace) vyžadují `ROLE_USER`.

---

## Fáze implementace (v pořadí)

```
F0 (enumy + schema)
 ├─→ F1 (RotationGenerator)
 │    └─→ F2 (cron command)
 │         └─→ F7 (cron setup)
 ├─→ F3 (GET rotace API)
 │    └─→ F4 (POST buy)
 │         └─→ F5 (equip/unequip)
 └─→ F6 (seed ItemDefinitions)
```

---

### Fáze 0 — Foundation

**První krok, blokuje všechno ostatní.** Enumy, opravy chyb, nová pole na entitách.

#### Nové soubory
```
src/Entity/Item/ItemSlot.php
src/Entity/Item/Rarity.php
src/Entity/Shop/RotationType.php
```

#### Úpravy entit

**`src/Entity/Item/ItemDefinition.php`**
- `desiredSlot`: ze `string` na `ItemSlot` — použít Doctrine `enumType`
- Nová pole: `Rarity $rarity` (výchozí `Common`), `int $baseGoldPrice`, `int $baseDiamondPrice`
- Přidat gettery/settery
- Přidat serializační groups pro pozdější API výpis

**`src/Entity/Shop/ShopRotation.php`**
- Nové pole: `RotationType $type` (výchozí `Daily`)
- Přidat getter/setter

**`src/Entity/Shop/ShopOffer.php`**
- **Opravit konstruktor** — `$this->rotation = $rotation; $this->ItemDefinition = $ItemDefinition;`
- Přidat getter/setter pro `rotation` a `ItemDefinition`
- Přidat serializační groups

**`src/Entity/Character/CharacterInventory.php`**
- Nové pole: `ItemSlot|null $slot` (nullable — `null` = v baťůžku)
- Odstranit holé `#[ApiResource]` (operace přibudou v F5)
- Přidat serializační groups
- Pole `equipped` a `quantity` už existují, beze změny

**`src/Entity/Character/Character.php`**
- Nové pole: `int $backpackCapacity` (výchozí 5, nastavit v konstruktoru)
- Přidat getter/setter

#### Migrace
```bash
make migration
make migrate
```

---

### Fáze 1 — RotationGenerator

**Servisa pro generování denní rotace.** Používá se z cron commandu (F2).

#### Nový soubor: `src/Service/Shop/RotationGenerator.php`

Interface + class. Hlavní metoda:

```
generate(Character): ShopRotation
```

**Logika krok za krokem:**

1. **Smaže** předchozí daily rotace charactera (`DELETE FROM shop_rotation WHERE character_id = X AND type = 'daily'` — Doctrine to s kaskádou na offery vyřeší).
2. Vytvoří novou `ShopRotation`:
   - `character = $character`
   - `type = Daily`
   - `validFrom = new DateTimeImmutable()` (teď)
   - `validUntil = tomorrow 06:00:00`
3. Náhodně vybere **8 unikátních** `ItemDefinition` z DB:
   - Podmínka: `requiredLevel <= character.level + 2` (nedávat itemy na které hráč ještě dlouho nedosáhne)
   - Pokud je v DB méně definic, vezme všechny dostupné (warning do logu).
4. Pro každou definici vytvoří `ShopOffer`:
   - `goldPrice = baseGoldPrice * (0.8 + mt_rand(0, 40) / 100)` → ±20 %
   - `diamondPrice` stejně z `baseDiamondPrice`
   - Přiřadí k rotaci (`$rotation->addShopOffer($offer)`)
5. Persistne vše (stačí persistnout rotaci, zbytek cascade).
6. Vrátí `ShopRotation`.

**Dependency injection:** `EntityManagerInterface`, `ItemDefinitionRepository`.

---

### Fáze 2 — Cron command

#### Nový soubor: `src/Command/GenerateShopRotationsCommand.php`

```
php bin/console app:shop:generate-rotations
```

- Projde všechny charactery (`CharacterRepository::findAll()`)
- Pro každého zavolá `RotationGenerator::generate()`
- Loguje: počet úspěšně vytvořených, počet chyb
- Error na jednom characterovi **nezastaví** celý běh (try/catch + log)

---

### Fáze 3 — Shop Rotation API (GET)

**Jen čtení.** Žádné generování on-demand.

#### Úpravy entit (serializační groups)

**`src/Entity/Shop/ShopRotation.php`**
- Upravit `GetCollection` operaci:
  - `security: 'is_granted("ROLE_USER")'`
  - `provider: ShopRotationProvider::class`
- Groups (`shop:read`) na: `id`, `type`, `validFrom`, `validUntil`, `shopOffers`

**`src/Entity/Shop/ShopOffer.php`**
- Groups (`shop:read`) na: `id`, `goldPrice`, `diamondPrice`, `itemDefinition`

**`src/Entity/Item/ItemDefinition.php`**
- Groups (`shop:read`) na: `id`, `name`, `desiredSlot`, `rarity`, `baseDamage`, `baseCrit`, `baseHealth`, `requiredLevel`, `description`, `baseGoldPrice`, `baseDiamondPrice`

#### Nový soubor: `src/State/Provider/Shop/ShopRotationProvider.php`

```
implements ProviderInterface
```

- Získá autentizovaného charactera (`Security::getUser()`)
- Zavolá `$character->getShopRotations()` (getter filtruje podle data — jen aktivní)
- Vrátí jako pole `[$rotation]` (nebo `[]` když žádná aktivní rotace není)

---

### Fáze 4 — Buy endpoint (POST)

#### Nový soubor: `src/State/Processor/Shop/BuyProcessor.php`

`POST /api/shop/offers/{id}/buy`

**Logika krok za krokem:**

1. Načíst `ShopOffer` z DB podle `$uriVariables['id']`
2. Načíst autentizovaného charactera (`Security::getUser()`)
3. **Validace:**
   - `offer.rotation.character === currentCharacter` → jinak **403**
   - `offer.rotation.validFrom <= now && offer.rotation.validUntil >= now` → jinak **410 Gone**
   - `character.gold >= offer.goldPrice && character.diamonds >= offer.diamondPrice` → jinak **422**
   - `character.level >= itemDefinition.requiredLevel` → jinak **422**
   - Kapacita baťůžku: počet `CharacterInventory WHERE character=X AND equipped=false` < `backpackCapacity` → jinak **422**
     - *Výjimka pro Elixir:* pokud v baťůžku existuje stack stejné ItemDefinition → inkrementuje se quantity, nezabírá nové místo
4. Vytvořit `Item` instanci:
   - `new Item($itemDefinition)`
   - Nastavit `bonusDamage/Crit/Health` = random variance od base statů (±20 %, škálováno rarity)
5. Vytvořit `CharacterInventory`:
   - `equipped = false`, `slot = null`, `quantity = 1`
   - Pro elixír: najít existující stack se stejnou definicí → `quantity += 1`, nepřidávat nový řádek
6. Odečíst cenu z charactera: `setGold()`, `setDiamonds()`
7. Smazat `ShopOffer` (`$em->remove($offer)`) — tím zmizí z rotace
8. Flush, vrátit `CharacterInventory` (serializovaný s groups)

#### Úprava: `src/Entity/Shop/ShopOffer.php`

Přidat `#[ApiResource]` s custom operací:
```php
new Post(
    uriTemplate: '/api/shop/offers/{id}/buy',
    security: 'is_granted("ROLE_USER")',
    processor: BuyProcessor::class,
    read: false,
    deserialize: false,
)
```

---

### Fáze 5 — Equip / Unequip

#### Nový soubor: `src/State/Processor/Inventory/EquipProcessor.php`

`POST /api/character_inventories/{id}/equip`

1. Načíst `CharacterInventory` podle ID
2. **Validace:** Patří autentizovanému characterovi → jinak **403**
3. **Validace:** `equipped === false` → jinak **422** (už je equipnutý)
4. Určit cílový slot: `ItemDefinition.desiredSlot`
5. **Validace:** V cílovém slotu není jiný equipnutý item → kontrola v DB → jinak **422**
6. Nastavit `equipped = true`, `slot = item.definition.desiredSlot`
7. Flush, vrátit `CharacterInventory`

#### Nový soubor: `src/State/Processor/Inventory/UnequipProcessor.php`

`POST /api/character_inventories/{id}/unequip`

1. Načíst `CharacterInventory` podle ID
2. **Validace:** Patří characterovi → jinak **403**
3. **Validace:** `equipped === true` → jinak **422**
4. **Validace:** Kapacita baťůžku → počet `WHERE equipped=false` < `backpackCapacity` → jinak **422**
5. Nastavit `equipped = false`, `slot = null`
6. Flush, vrátit `CharacterInventory`

#### Úprava: `src/Entity/Character/CharacterInventory.php`

Přidat `#[ApiResource]` s custom operacemi `/equip` a `/unequip`.

---

### Fáze 6 — Seed ItemDefinitions

#### Nový soubor: `src/DataFixtures/ItemDefinitionFixtures.php`

Vyžaduje `doctrine/doctrine-fixtures-bundle` (doinstalovat jako dev závislost).

**~15–20 definic** pokrývajících všechny sloty:

| Item               | Slot      | Rarity   | Lvl | baseDmg | baseGoldPrice | 
|--------------------|-----------|----------|-----|---------|---------------|
| Wooden Sword       | Weapon    | Common   | 1   | 2       | 50            |
| Iron Sword         | Weapon    | Common   | 1   | 5       | 100           |
| Steel Sword        | Weapon    | Uncommon | 3   | 8       | 300           |
| Leather Cap        | Helmet    | Common   | 1   | 0       | 40            |
| Iron Helm          | Helmet    | Common   | 2   | 0       | 120           |
| Cloth Robe         | Armour    | Common   | 1   | 0       | 60            |
| Chain Mail         | Armour    | Uncommon | 3   | 0       | 250           |
| Leather Boots      | Boots     | Common   | 1   | 0       | 40            |
| Boots of Speed     | Boots     | Rare     | 5   | 0       | 500           |
| Health Potion      | Elixir    | Common   | 1   | 0       | 25            |
| Strength Elixir    | Elixir    | Uncommon | 2   | 0       | 150           |
| Ring of Protection | RingLeft  | Common   | 2   | 0       | 180           |
| Ring of Power      | RingRight | Uncommon | 4   | 0       | 400           |
| Amulet of Health   | Necklace  | Common   | 2   | 0       | 200           |
| Necklace of Wisdom | Necklace  | Rare     | 5   | 0       | 600           |

(Každá definice má i `baseDiamondPrice` — u Common/Uncommon typicky 0, u Rare+ nenulové.)

---

### Fáze 7 — Cron setup

Zajistit spouštění `php bin/console app:shop:generate-rotations` každou půlnoc.

**Varianta A — hostitelský cron:**
```
0 0 * * * cd /path/to/project && docker compose exec -T app php bin/console app:shop:generate-rotations
```

**Varianta B — cron container v compose.yaml** (čistší, vše v Dockeru):
- Samostatná služba se stejným image jako `app`, command `crond -f`
- Crontab nastavený na `0 0 * * * php /app/bin/console app:shop:generate-rotations`

---

## Verifikace (checklist)

- [ ] **F0:** `make migration && make migrate` projde čistě
- [ ] **F1:** Unit test — `generate()` vrátí rotaci s 8 offery, staré smazané, ceny ±20 %
- [ ] **F2:** `php bin/console app:shop:generate-rotations` vytvoří rotace, druhé spuštění je idempotentní
- [ ] **F3:** `GET /api/shop_rotations` vrátí rotaci, nebo `[]` když žádná neexistuje
- [ ] **F4:** `POST /api/shop/offers/{id}/buy` → 200, gold odečten, offer smazán, item v baťůžku (`equipped=false, slot=null`)
- [ ] **F5:** Equip → `slot` nastaven. Unequip → `slot=null`. Duplicitní equip → 422. Plný baťůžek → 422.
- [ ] **F6:** Po fixtures aspoň 8 definic v DB
- [ ] **F7:** Cron o půlnoci vygeneruje rotace

---

## Mimo scope (připraveno pro budoucnost)

- Rozšíření kapacity baťůžku — `backpackCapacity` už je na entitě, chybí jen endpoint
- Weekly / Event rotace — `RotationType` enum už existuje, generuje se jen Daily
- Prodej/výkup itemů — `baseGoldPrice`/`baseDiamondPrice` na definici připraveno jako referenční cena
- Item enhancement / crafting
- Frontend
