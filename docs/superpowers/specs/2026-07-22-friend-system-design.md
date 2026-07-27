# Friend System — Design Spec

**Date:** 2026-07-22
**Status:** approved

## Goal

One-way follow system: player A follows player B to track their stats. No confirmation required, no pending states.

## API Contract

| Method | URI | Auth | Description |
|--------|-----|------|-------------|
| `GET` | `/character/{id}` | ROLE_USER | Public profile (limited fields) + `isFollowed` |
| `POST` | `/character/{id}/follow` | ROLE_USER | Follow a character |
| `DELETE` | `/character/{id}/follow` | ROLE_USER | Unfollow a character |
| `GET` | `/character/friends` | ROLE_USER | List of followed characters |

### GET /character/{id} — Response

```json
{
  "id": 5,
  "username": "hrac",
  "level": 12,
  "damage": 340,
  "health": 1200,
  "prestigePoints": 3,
  "isFollowed": true
}
```

No gold, diamonds, email, inventories, shopRotations — public data only.

### POST/DELETE /character/{id}/follow — Response

`204 No Content`. Idempotent: following an already-followed character or unfollowing a non-followed one is a no-op (still 204).

### GET /character/friends — Response

Array of public profiles (same schema as above, `isFollowed` always true).

## Database

New table `friend_relation`:

| Column | Type | Constraints |
|--------|------|------------|
| id | int | PK, autoincrement |
| follower_id | int | FK → character(id), NOT NULL |
| followed_id | int | FK → character(id), NOT NULL |
| created_at | datetime_immutable | NOT NULL, default NOW |

- Unique constraint: `UNIQUE(follower_id, followed_id)`
- Index on `follower_id` (for "my friends" queries)
- Check constraint: `follower_id != followed_id` (can't follow yourself)

## Files

### New files

```
src/Entity/Character/FriendRelation.php          — ORM entity
src/Repository/Character/FriendRelationRepository.php — queries
src/State/Provider/Character/PublicCharacterProvider.php — GET /character/{id}
src/State/Provider/Character/FriendsProvider.php — GET /character/friends
src/State/Processor/Character/Friend/FollowProcessor.php — POST /character/{id}/follow
src/State/Processor/Character/Friend/UnfollowProcessor.php — DELETE /character/{id}/follow
```

### Modified files

```
src/Entity/Character/Character.php               — add PUBLIC_READ_GROUP, isFollowed virtual prop, $following/$followers collections
```

## Entity Design

### FriendRelation

```php
namespace App\Entity\Character;

#[ORM\Entity(repositoryClass: FriendRelationRepository::class)]
#[ORM\Table(name: 'friend_relation')]
#[ORM\UniqueConstraint(columns: ['follower_id', 'followed_id'])]
class FriendRelation
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Character::class, inversedBy: 'following')]
    #[ORM\JoinColumn(nullable: false)]
    private Character $follower;

    #[ORM\ManyToOne(targetEntity: Character::class, inversedBy: 'followers')]
    #[ORM\JoinColumn(nullable: false)]
    private Character $followed;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;
}
```

### Character additions

```php
// New serialization group for public profiles
public const string PUBLIC_READ_GROUP = 'character:public:read';

// Non-persisted, set by provider
#[Groups([self::PUBLIC_READ_GROUP])]
private bool $isFollowed = false;

// Collections
#[ORM\OneToMany(targetEntity: FriendRelation::class, mappedBy: 'follower', orphanRemoval: true)]
private Collection $following;

#[ORM\OneToMany(targetEntity: FriendRelation::class, mappedBy: 'followed', orphanRemoval: true)]
private Collection $followers;
```

Existing properties tagged with both groups where appropriate:
- `#[Groups([self::READ_GROUP, self::PUBLIC_READ_GROUP])]` — id, username, level, damage, health, prestigePoints
- `#[Groups([self::READ_GROUP])]` only — gold, diamonds, email, shopRotations, characterInventories, activeElixirs

## Data Flow

### GET /character/{id}

```
Request → PublicCharacterProvider
  → CharacterRepository::find($id) → Character entity
  → FriendRelationRepository::isFollowing($currentUser, $character) → bool
  → $character->setIsFollowed(true/false)
  → Return Character (normalized with PUBLIC_READ_GROUP)
```

### POST /character/{id}/follow

```
Request → FollowProcessor
  → CharacterRepository::find($id) → followed Character (404 if missing)
  → Guard: follower === followed → 422
  → FriendRelationRepository::findOneBy([follower, followed])
    → exists → early return 204
  → new FriendRelation, persist, flush → 204
```

### DELETE /character/{id}/follow

```
Request → UnfollowProcessor
  → CharacterRepository::find($id) → followed Character (404 if missing)
  → FriendRelationRepository::findOneBy([follower, followed])
    → not found → early return 204
  → $em->remove($relation), flush → 204
```

### GET /character/friends

```
Request → FriendsProvider
  → FriendRelationRepository::findFollowing($currentUser) → FriendRelation[]
  → Map to Character[] (the followed side)
  → Return array (normalized with PUBLIC_READ_GROUP, isFollowed always true)
```

## Edge Cases

| Case | Behavior |
|------|----------|
| Follow yourself | 422 Unprocessable Entity |
| Follow already-followed | 204 (idempotent) |
| Unfollow not-followed | 204 (idempotent) |
| Follow/unfollow non-existent user | 404 |
| Unauthenticated request | 401 |

## Trade-offs

- **One-way vs two-way:** One-way chosen. Simpler, no pending states, no notifications. If needed later, two-way can be built on top.
- **Virtual property vs DTO for isFollowed:** Virtual property on entity chosen. Simpler, avoids extra DTO class, follows project conventions. Trade-off: entity knows about a "view" concern, but for MVP this is fine.
- **POST+DELETE vs PATCH with flag:** POST+DELETE chosen. REST-semantically correct, each endpoint does one thing, easier to extend independently.