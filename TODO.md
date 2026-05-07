Shop MVP

- GET /shop = vrátí shop pro aktuálně přihlášeného hráče
- POST /shop = koupí offer z body např. { "offerId": 123 }

Důležitý princip

- shop nenabízí vlastněné item instance
- shop nabízí nabídky na koupi
- instance itemu vznikne až při POST /shop

Doporučené entity

- Character
- ItemDefinition
    - definice itemu: název, typ, staty, base cena
- ShopRotation
    - patří jednomu Character
    - má validFrom, validUntil
- ShopOffer
    - patří do ShopRotation
    - odkazuje na ItemDefinition
    - má priceAmount, priceCurrency, slot
- CharacterItem
    - vlastněný item hráče, vytvoří se při nákupu

Proč ne itemOneId ... itemFiveId

- je to zadrátované na 5 slotů
- hůř se to rozšiřuje
- ShopOffer je čistší a přirozenější model

API kontrakt

- GET /shop
    - vrátí rotaci a 5 offerů
    - každý offer: id, item data, cena, měna, expirace
- POST /shop
    - input: offerId
    - najde offer aktuálního hráče
    - ověří platnost
    - odečte měnu
    - vytvoří CharacterItem

Security teď vs. později

- plné přihlášení můžeš udělat později
- ale už teď navrhni shop jako „shop aktuálního hráče“
- takže ne characterId v requestu
- dočasně si udělej CurrentCharacterProvider, který vrací třeba Character #1

Shrnutí jednou větou

- persisted shop je entita, ne jen DTO
- DTO můžeš použít až pro výstup/input v API
- nákup má pracovat nad offerId, ne nad itemDefinitionId

Pokud chceš, příště ti to sepíšu ještě jako krátký blueprint:

- vztahy mezi entitami
- co bude v GET /shop response
- co přesně udělá POST /shop processor.
