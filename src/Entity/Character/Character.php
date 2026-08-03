<?php

declare(strict_types=1);

namespace App\Entity\Character;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\ApiResource\Auth\ChangePasswordInput;
use App\ApiResource\Auth\LoginInput;
use App\ApiResource\Auth\LoginOutput;
use App\ApiResource\Auth\RegisterInput;
use App\Entity\AppearanceOption;
use App\Entity\Race;
use App\Entity\Shop\ShopRotation;
use App\Repository\Character\CharacterRepository;
use App\State\Processor\Auth\ChangePasswordProcessor;
use App\State\Processor\Auth\LoginProcessor;
use App\State\Processor\Auth\RegisterProcessor;
use App\State\Provider\Character\MineCharacterProvider;
use App\State\Provider\Character\PublicCharacterProvider;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    operations: [
        new Post(
            uriTemplate: '/auth/register',
            input: RegisterInput::class,
            processor: RegisterProcessor::class,
        ),
        new Post(
            uriTemplate: '/auth/login',
            normalizationContext: ['groups' => ['login:read']],
            input: LoginInput::class,
            output: LoginOutput::class,
            processor: LoginProcessor::class,
        ),
        new Post(
            uriTemplate: '/auth/change-password',
            security: 'is_granted("ROLE_USER")',
            input: ChangePasswordInput::class,
            processor: ChangePasswordProcessor::class,
        ),
        new Get(
            uriTemplate: '/character/{id}',
            requirements: ['id' => '\d+'],
            normalizationContext: ['groups' => self::READ_PUBLIC_GROUP],
            security: 'is_granted("ROLE_USER")',
            provider: PublicCharacterProvider::class,
        ),
        new Get(
            uriTemplate: '/character',
            security: 'is_granted("ROLE_USER")',
            provider: MineCharacterProvider::class,
        ),
        new Patch(
            uriTemplate: '/character',
            denormalizationContext: ['groups' => self::UPDATE_GROUP],
            security: 'is_granted("ROLE_USER")',
            validationContext: ['groups' => ['Default', self::UPDATE_GROUP]],
            read: true,
            provider: MineCharacterProvider::class,
        ),
        new Delete(
            uriTemplate: '/character',
            security: 'is_granted("ROLE_USER")',
            read: true,
            provider: MineCharacterProvider::class,
        ),
    ],
    normalizationContext: ['groups' => [self::READ_GROUP]],
)]
#[ORM\Entity(repositoryClass: CharacterRepository::class)]
#[ORM\Table(name: 'character')]
#[UniqueEntity('username')]
#[UniqueEntity('email')]
class Character implements PasswordAuthenticatedUserInterface, UserInterface
{
    public const string READ_GROUP = 'character:read';
    public const string READ_PUBLIC_GROUP = 'character:public:read';
    public const string UPDATE_GROUP = 'update:read';
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups([self::READ_GROUP, self::READ_PUBLIC_GROUP])]
    private int $id;

    #[ORM\Column(length: 255, unique: true)]
    #[Groups([self::READ_GROUP, self::UPDATE_GROUP, self::READ_PUBLIC_GROUP])]
    #[Assert\NotBlank(groups: [self::UPDATE_GROUP])]
    #[Assert\Length(min: 4, max: 20, groups: [self::UPDATE_GROUP])]
    private string $username;

    #[ORM\Column(length: 255, unique: true)]
    private string $email;

    #[ORM\Column]
    #[Groups([self::READ_GROUP])]
    private int $gold;

    #[ORM\Column]
    #[Groups([self::READ_GROUP])]
    private int $diamonds;

    #[ORM\Column]
    #[Groups([self::READ_GROUP, self::READ_PUBLIC_GROUP])]
    private int $level;

    #[ORM\Column]
    #[Groups([self::READ_GROUP, self::READ_PUBLIC_GROUP])]
    private int $experience;

    #[ORM\Column]
    #[Groups([self::READ_GROUP, self::READ_PUBLIC_GROUP])]
    private int $damage;

    #[ORM\Column]
    #[Groups([self::READ_GROUP, self::READ_PUBLIC_GROUP])]
    private int $health;

    /**
     * @var Collection<int, ShopRotation>
     */
    #[ORM\OneToMany(targetEntity: ShopRotation::class, mappedBy: 'character', orphanRemoval: true)]
    #[Groups([self::READ_GROUP])]
    private Collection $shopRotations;

    #[ORM\Column(length: 255)]
    private ?string $passwordHash = null;

    /**
     * @var Collection<int, CharacterInventory>
     */
    #[ORM\OneToMany(targetEntity: CharacterInventory::class, mappedBy: 'character', orphanRemoval: true)]
    #[ORM\OrderBy(['position' => 'ASC'])]
    #[Groups([self::READ_GROUP])]
    private Collection $characterInventories;

    #[ORM\Column]
    #[Groups([self::READ_GROUP])]
    private int $backpackCapacity;

    /**
     * @var Collection<int, ActiveElixir>
     */
    #[Groups([self::READ_GROUP, self::READ_PUBLIC_GROUP])]
    #[ORM\OneToMany(targetEntity: ActiveElixir::class, mappedBy: 'character', orphanRemoval: true)]
    private Collection $activeElixirs;

    #[ORM\Column]
    #[Groups([self::READ_GROUP, self::READ_PUBLIC_GROUP])]
    private int $prestigePoints = 0;

    /**
     * @var Collection<int, FriendRelation>
     */
    #[Groups([self::READ_GROUP])]
    #[ORM\OneToMany(targetEntity: FriendRelation::class, mappedBy: 'character', orphanRemoval: true)]
    private Collection $friendsCollection;

    #[Groups([self::READ_PUBLIC_GROUP])]
    private bool $friends = false;

    #[ORM\Column]
    #[Groups([self::READ_GROUP])]
    private bool $email_verified;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups([self::READ_GROUP, self::READ_PUBLIC_GROUP])]
    private Race $race;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups([self::READ_GROUP, self::READ_PUBLIC_GROUP])]
    private AppearanceOption $hair;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups([self::READ_GROUP, self::READ_PUBLIC_GROUP])]
    private AppearanceOption $eyes;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups([self::READ_GROUP, self::READ_PUBLIC_GROUP])]
    private AppearanceOption $mouth;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups([self::READ_GROUP, self::READ_PUBLIC_GROUP])]
    private AppearanceOption $nose;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups([self::READ_GROUP, self::READ_PUBLIC_GROUP])]
    private AppearanceOption $ears;

    public function __construct()
    {
        $this->shopRotations = new ArrayCollection();
        $this->gold = 10000000;
        $this->diamonds = 10000000;
        $this->level = 1;
        $this->experience = 0;
        $this->damage = 1;
        $this->health = 100;
        $this->characterInventories = new ArrayCollection();
        $this->activeElixirs = new ArrayCollection();
        $this->friendsCollection = new ArrayCollection();
        $this->backpackCapacity = 4;
        $this->prestigePoints = 0;
        $this->email_verified = false;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getGold(): int
    {
        return $this->gold;
    }

    public function setGold(int $gold): static
    {
        $this->gold = $gold;

        return $this;
    }

    public function getDiamonds(): int
    {
        return $this->diamonds;
    }

    public function setDiamonds(int $diamonds): static
    {
        $this->diamonds = $diamonds;

        return $this;
    }

    public function getLevel(): int
    {
        return $this->level;
    }

    public function setLevel(int $level): static
    {
        $this->level = $level;

        return $this;
    }

    public function getExperience(): int
    {
        return $this->experience;
    }

    public function setExperience(int $experience): static
    {
        $this->experience = $experience;

        return $this;
    }

    public function getDamage(): int
    {
        return $this->damage;
    }

    public function setDamage(int $damage): static
    {
        $this->damage = $damage;

        return $this;
    }

    public function getHealth(): int
    {
        return $this->health;
    }

    public function setHealth(int $health): static
    {
        $this->health = $health;

        return $this;
    }

    /**
     * @return Collection<int, ShopRotation>
     */
    public function getShopRotations(): Collection
    {
        $allRotations = $this->shopRotations;
        $now = new \DateTimeImmutable();

        $showRotation = new ArrayCollection();

        foreach ($allRotations as $rotation) {
            if ($rotation->getValidFrom() < $now && $rotation->getValidUntil() > $now) {
                $showRotation->add($rotation);
            }
        }

        return $showRotation;
    }

    public function addShopRotation(ShopRotation $shopRotation): static
    {
        if (!$this->shopRotations->contains($shopRotation)) {
            $this->shopRotations->add($shopRotation);
            $shopRotation->setCharacter($this);
        }

        return $this;
    }

    public function removeShopRotation(ShopRotation $shopRotation): static
    {
        $this->shopRotations->removeElement($shopRotation);

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->passwordHash;
    }

    public function setPasswordHash(string $passwordHash): static
    {
        $this->passwordHash = $passwordHash;

        return $this;
    }

    public function getRoles(): array
    {
        return ['ROLE_USER'];
    }

    public function eraseCredentials(): void
    {
        // Not needed since we use hashed passwords
    }

    /**
     * @return non-empty-string
     */
    public function getUserIdentifier(): string
    {
        if ('' === $this->email) {
            throw new \LogicException('User email cant be empty.');
        }

        return $this->email;
    }

    /**
     * @return Collection<int, CharacterInventory>
     */
    public function getCharacterInventories(): Collection
    {
        return $this->characterInventories;
    }

    public function addCharacterInventory(CharacterInventory $characterInventory): static
    {
        if (!$this->characterInventories->contains($characterInventory)) {
            $this->characterInventories->add($characterInventory);
            $characterInventory->setCharacter($this);
        }

        return $this;
    }

    public function removeCharacterInventory(CharacterInventory $characterInventory): static
    {
        $this->characterInventories->removeElement($characterInventory);

        return $this;
    }

    public function getBackpackCapacity(): int
    {
        return $this->backpackCapacity;
    }

    public function setBackpackCapacity(int $backpackCapacity): static
    {
        $this->backpackCapacity = $backpackCapacity;

        return $this;
    }

    public function subtractGold(int $amount): void
    {
        if ($amount > $this->gold) {
            throw new \InvalidArgumentException('Not enough gold');
        }
        $this->gold -= $amount;
    }

    public function subtractDiamonds(int $amount): void
    {
        if ($amount > $this->diamonds) {
            throw new \InvalidArgumentException('Not enough diamonds');
        }
        $this->diamonds -= $amount;
    }

    public function addGold(int $amount): void
    {
        $this->gold += $amount;
    }

    /**
     * @return Collection<int, ActiveElixir>
     */
    public function getActiveElixirs(): Collection
    {
        return $this->activeElixirs;
    }

    public function addActiveElixir(ActiveElixir $activeElixir): static
    {
        if (!$this->activeElixirs->contains($activeElixir)) {
            $this->activeElixirs->add($activeElixir);
            $activeElixir->setCharacter($this);
        }

        return $this;
    }

    public function removeActiveElixir(ActiveElixir $activeElixir): static
    {
        if ($this->activeElixirs->removeElement($activeElixir)) {
            // set the owning side to null (unless already changed)
            if ($activeElixir->getCharacter() === $this) {
                $activeElixir->setCharacter(null);
            }
        }

        return $this;
    }

    public function getPrestigePoints(): ?int
    {
        return $this->prestigePoints;
    }

    public function setPrestigePoints(int $prestigePoints): static
    {
        $this->prestigePoints = $prestigePoints;

        return $this;
    }

    /**
     * @return Collection<int, FriendRelation>
     */
    public function getFriendsCollection(): Collection
    {
        return $this->friendsCollection;
    }

    /**
     * @param Collection<int, FriendRelation> $friendsCollection
     */
    public function setFriendsCollection(Collection $friendsCollection): void
    {
        $this->friendsCollection = $friendsCollection;
    }

    public function isFriends(): bool
    {
        return $this->friends;
    }

    public function setFriends(bool $friends): void
    {
        $this->friends = $friends;
    }

    public function getRace(): Race
    {
        return $this->race;
    }

    public function setRace(Race $race): static
    {
        $this->race = $race;

        return $this;
    }

    public function getHair(): AppearanceOption
    {
        return $this->hair;
    }

    public function setHair(AppearanceOption $hair): static
    {
        $this->hair = $hair;

        return $this;
    }

    public function getEyes(): AppearanceOption
    {
        return $this->eyes;
    }

    public function setEyes(AppearanceOption $eyes): static
    {
        $this->eyes = $eyes;

        return $this;
    }

    public function getMouth(): AppearanceOption
    {
        return $this->mouth;
    }

    public function setMouth(AppearanceOption $mouth): static
    {
        $this->mouth = $mouth;

        return $this;
    }

    public function getNose(): AppearanceOption
    {
        return $this->nose;
    }

    public function setNose(AppearanceOption $nose): static
    {
        $this->nose = $nose;

        return $this;
    }

    public function getEars(): AppearanceOption
    {
        return $this->ears;
    }

    public function setEars(AppearanceOption $ears): static
    {
        $this->ears = $ears;

        return $this;
    }

    public function isEmailVerified(): bool
    {
        return $this->email_verified;
    }

    public function setEmailVerified(bool $email_verified): static
    {
        $this->email_verified = $email_verified;

        return $this;
    }
}
