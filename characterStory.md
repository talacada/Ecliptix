# Fixtures — Plán implementace

## Architektura

Tři soubory, jedna jasná cesta:

```
AppStory  (jediný #[AsFixture], čistý orchestrátor)
  ├── injectne ItemDefinitionStory
  ├── injectne CharacterStory
  │
  └── build()
        ├── $this->itemDefinitionStory->generate()   // 1. definice itemů
        └── $this->characterStory->generate()        // 2. postavy s itemy a rotacemi
```

- **`AppStory`** — upravit existující. Zůstává jediným `#[AsFixture]`. Místo přímé generace definic jen orchestruje volání služeb.
- **`ItemDefinitionStory`** — nová plain služba (bez `#[AsFixture]`). Přesune se sem stávající `generateItemDefinitions()` z AppStory.
- **`CharacterStory`** — nová plain služba (bez `#[AsFixture]`). Tvorba 3 postav s itemy, elixíry a shop rotacemi.

## Soubory

| Soubor | Akce | `#[AsFixture]` |
|--------|------|----------------|
| `src/Story/AppStory.php` | **Upravit** — odebrat `generateItemDefinitions()`, přidat konstruktor s injectionem, delegovat | Ano (`main`) |
| `src/Story/ItemDefinitionStory.php` | **Vytvořit** — přesunout sem `generateItemDefinitions()` z AppStory | Ne |
| `src/Story/CharacterStory.php` | **Vytvořit** — nová logika pro postavy, itemy, elixíry, rotace | Ne |
| `src/Repository/Item/ItemDefinitionRepository.php` | **Upravit** — přidat `findRandomBySlot(ItemSlotEnum)` | — |

---

## 1. AppStory (upravit)

```php
#[AsFixture(name: 'main')]
final class AppStory extends Story
{
    public function __construct(
        private ItemDefinitionStory $itemDefinitionStory,
        private CharacterStory $characterStory,
    ) {}

    public function build(): void
    {
        $this->itemDefinitionStory->generate();
        $this->characterStory->generate();
    }
}
```

Původní `generateItemDefinitions()` se odstraní (přesune do ItemDefinitionStory).

---

## 2. ItemDefinitionStory (nová)

Plain služba, žádné `extends Story`, žádné `#[AsFixture]`. Obsahuje přesunutou `generateItemDefinitions()` z AppStory beze změny — stejné konstanty, stejné volání `ItemDefinitionFactory` a `ElixirDefinitionFactory`.

```php
final class ItemDefinitionStory
{
    public function generate(): void
    {
        // přesně to co teď dělá AppStory.generateItemDefinitions()
    }
}
```

---

## 3. CharacterStory (nová)

Plain služba, žádné `extends Story`, žádné `#[AsFixture]`.

### Závislosti (konstruktor)

| Služba | Použití |
|--------|---------|
| `UserPasswordHasherInterface` | Hashování hesel |
| `EntityManagerInterface` | Persist Item, CharacterInventory, ActiveElixir |
| `RotationGenerator` | Shop rotace pro každou postavu |
| `ItemDefinitionRepository` | `findRandomElixir()`, `findRandomBySlot()` |
| `ItemFactory` | `rollBonusStats()` pro bonusové staty itemů |

### `generate(): void`

```
defaultChar = createCharacter(
  email: 'default@gmail.com',
  password: 'Hesloheslo1',
  username: 'default'
)
equipCharacter(defaultChar)

foreach [1, 2]:
  opponent = createCharacter(
    email: faker->email(),
    password: faker->password(),
    username: faker->userName()
  )
  equipCharacter(opponent)

// rotace pro všechny 3 postavy
foreach [všechny 3]:
  rotationGenerator->generate(postava)
```

### `createCharacter(email, password, username): Character`

```php
$character = new Character();
$character->setEmail($email);
$character->setUsername($username);
$character->setPasswordHash(
    $passwordHasher->hashPassword($character, $password)
);
$entityManager->persist($character);
return $character;
```

### `equipCharacter(Character $character): void`

Provede vše níže, nakonec `$entityManager->flush()`:

**Equipped** (container=`Equipped`, position=0):
- 1× Weapon — `findRandomBySlot(ItemSlotEnum::Weapon)`
- 1× Armour — `findRandomBySlot(ItemSlotEnum::Armour)`
- 1× RingLeft — `findRandomBySlot(ItemSlotEnum::RingLeft)`

Pro každý:
```php
$def = $itemDefinitionRepository->findRandomBySlot($slot);
$item = new Item();
$item->setDefinition($def);
[$bd, $bc, $bh] = $itemFactory->rollBonusStats($def);
$item->setBonusDamage($bd);
$item->setBonusCrit($bc);
$item->setBonusHealth($bh);
$entityManager->persist($item);

$inv = new CharacterInventory();
$inv->setCharacter($character);
$inv->setItem($item);
$inv->setContainer(InventoryContainerEnum::Equipped);
$inv->setPosition(0);
$entityManager->persist($inv);
```

**Backpack** (container=`Backpack`):
- 1× Helmet, position=1
- 1× Necklace, position=2
- 1× random Elixir, position=3 — `findRandomElixir()`, bonus staty = 0 (elixíry nemají bonus staty)

**Aktivní elixir**:
```php
$elixirDef = $itemDefinitionRepository->findRandomElixir();
$active = new ActiveElixir();
$active->setCharacter($character);
$active->setItemDefinition($elixirDef);
$active->setExpiresAt(
    (new DateTimeImmutable())->modify('+' . $elixirDef->getDurationSeconds() . ' seconds')
);
$entityManager->persist($active);
```

Nakonec `$entityManager->flush()`.

---

## 4. ItemDefinitionRepository — `findRandomBySlot()`

Nová metoda:

```php
public function findRandomBySlot(ItemSlotEnum $slot): ?ItemDefinition
{
    return $this->createQueryBuilder('d')
        ->where('d.desiredSlot = :slot')
        ->andWhere('d INSTANCE OF App\Entity\Item\ItemDefinition')  // jen equipment, ne elixiry
        ->setParameter('slot', $slot)
        ->orderBy('RAND()')
        ->setMaxResults(1)
        ->getQuery()
        ->getOneOrNullResult();
}
```

---

## Verifikace

1. **Načíst fixtures**:
   ```bash
   make fixtures
   # nebo:
   docker compose exec --user $(id -u):$(id -g) app php bin/console foundry:load-fixtures
   ```

2. **Ověřit v DB**:
   ```sql
   SELECT id, email, username FROM character;
   -- 3 řádky, první ID=1, email=default@gmail.com

   SELECT c.username, ci.container, ci.position, d.name, d.desired_slot
   FROM character_inventory ci
   JOIN character c ON c.id = ci.character_id
   JOIN item i ON i.id = ci.item_id
   JOIN item_definition d ON d.id = i.definition_id
   ORDER BY c.id, ci.container, ci.position;
   -- 6 řádků/postavu (3 equipped + 3 backpack vč. elixíru)

   SELECT * FROM active_elixir;
   -- 1 řádek/postavu

   SELECT * FROM shop_rotation;
   -- 1 Daily rotace/postavu
   ```

3. **Ověřit login**:
   ```bash
   curl -X POST https://localhost:8443/api/auth/login \
     -H 'Content-Type: application/json' \
     -d '{"email":"default@gmail.com","password":"Hesloheslo1"}'
   ```
