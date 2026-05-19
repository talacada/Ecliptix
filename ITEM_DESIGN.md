# Item System — Domain Model

## Přehled

```
Character 1──N CharacterInventory N──1 Item N──1 ItemDefinition
                                                   1
                                                   │
                                                   N
                                               ShopOffer
```

- Item **neví** o svém vlastníkovi (žádný owner FK) — vlastnictví řeší CharacterInventory
- CharacterInventory má `unique=true` na `item` — jeden item nemůže být ve více inventářích současně
- ItemDefinition existuje nezávisle — lze ji vytvořit, i když žádný Item ještě neexistuje
- ShopOffer → ItemDefinition: shop slot prodává typ itemu (např. "Iron Sword"), při koupi se vytvoří Item instance
- Upgrade probíhá **po jednotlivých statech** — Item má `bonusDamage`, `bonusCrit`, `bonusHealth`, které se přičítají k `baseDamage`/`baseCrit`/`baseHealth` z definice. Výsledný stat itemu = base + bonus

---

## ItemDefinition (katalogový typ — "co to je")

| Property | Type | Default | Popis |
|---|---|---|---|
| id | int (PK, auto) | — | |
| name | string(255) | — | "Iron Sword" |
| desiredSlot | string(255) | — | helmet / weapon / armor / accessory / consumable |
| baseDamage | int | 0 | |
| baseCrit | int | 0 | % krit šance |
| baseHealth | int | 0 | bonus HP |
| requiredLevel | int | 1 | min. level pro equip |
| description | ?string (nullable) | null | flavor text |

**Vazby:**
- OneToMany → Item (`mappedBy: 'definition'`)

---

## Item (konkrétní instance — "tenhle kus")

| Property | Type | Default | Popis |
|---|---|---|---|
| id | int (PK, auto) | — | |
| definition | M:1 → ItemDefinition (NOT NULL) | — | |
| bonusDamage | int | 0 | vylepšení nad rámec base hodnoty |
| bonusCrit | int | 0 | |
| bonusHealth | int | 0 | |

**Výsledný stat:** `definition->getBaseDamage() + bonusDamage` (obdobně pro crit, health)

**Vazby:**
- ManyToOne → ItemDefinition (`inversedBy: 'items'`)
- OneToMany → CharacterInventory (`mappedBy: 'item'`)

---

## CharacterInventory (vazební — "kdo co vlastní")

| Property | Type | Default | Popis |
|---|---|---|---|
| id | int (PK, auto) | — | |
| character | M:1 → Character (NOT NULL) | — | |
| item | M:1 → Item (NOT NULL, unique=true) | — | 1 item nemůže být ve 2 inventářích |
| equipped | bool | false | |
| quantity | int | 1 | pro stackovatelné itemy (lektvary) |

**Vazby:**
- ManyToOne → Character (`inversedBy: 'characterInventories'`)
- ManyToOne → Item (`inversedBy: 'inventories'`)

---

## ShopOffer (upravená existující)

| Změna | Popis |
|---|---|
| + itemDefinition | M:1 → ItemDefinition (NOT NULL) |
| − slot | ruší se — equipment slot je teď na ItemDefinition |

---

## Úpravy v Character

| Změna | Popis |
|---|---|
| + characterInventories | OneToMany → CharacterInventory (mappedBy: 'character', orphanRemoval: true) |
| + add/remove metody | standardní kolekce management |

---

## Co se NEIMPLEMENTUJE (na později)

- Enchantment entita
- Stone entita
- Loot tabulky
- Trade mezi hráči

---

## Postup implementace

1. `ItemDefinition` entita
2. `Item` entita
3. `CharacterInventory` entita
4. Upravit `ShopOffer` — přidat `itemDefinition`, odebrat `slot`
5. Upravit `Character` — přidat `characterInventories`
6. `make:migration` + `make:migrate`
