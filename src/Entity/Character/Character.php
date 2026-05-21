<?php

namespace App\Entity\Character;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\ApiResource\Auth\LoginInput;
use App\ApiResource\Auth\LoginOutput;
use App\ApiResource\Auth\RegisterInput;
use App\Entity\Shop\ShopRotation;
use App\Repository\Character\CharacterRepository;
use App\State\Processor\Auth\LoginProcessor;
use App\State\Processor\Auth\RegisterProcessor;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Attribute\Groups;

#[ApiResource(
    operations: [
        new Get(security: 'is_granted("ROLE_USER")'),
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
        new Patch(security: 'is_granted("ROLE_USER")'),
        new Delete(security: 'is_granted("ROLE_USER")'),
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
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\Column(length: 255, unique: true)]
    #[Groups([self::READ_GROUP])]
    private string $username;

    #[ORM\Column(length: 255, unique: true)]
    private string $email;

    #[ORM\Column]
    #[Groups([self::READ_GROUP])]
    private int $gold;

    #[ORM\Column]
    #[Groups([self::READ_GROUP])]
    private int $diamonds ;

    #[ORM\Column]
    #[Groups([self::READ_GROUP])]
    private int $level;

    #[ORM\Column]
    #[Groups([self::READ_GROUP])]
    private int $experience ;

    #[ORM\Column]
    #[Groups([self::READ_GROUP])]
    private int $damage;

    #[ORM\Column]
    #[Groups([self::READ_GROUP])]
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
    #[ORM\OneToMany(targetEntity: CharacterInventory::class, mappedBy: 'character')]
    private Collection $characterInventories;

    public function __construct()
    {
        $this->shopRotations = new ArrayCollection();
        $this->gold = 0;
        $this->diamonds = 0;
        $this->level = 1;
        $this->experience = 0;
        $this->damage = 1;
        $this->health = 100;
        $this->characterInventories = new ArrayCollection();
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
        $now = new DateTimeImmutable();

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
        if ($this->shopRotations->removeElement($shopRotation)) {
            // set the owning side to null (unless already changed)
            if ($shopRotation->getCharacter() === $this) {
                $shopRotation->setCharacter(null);
            }
        }

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

    public function getUserIdentifier(): string
    {
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
        if ($this->characterInventories->removeElement($characterInventory)) {
            // set the owning side to null (unless already changed)
            if ($characterInventory->getCharacter() === $this) {
                $characterInventory->setCharacter(null);
            }
        }

        return $this;
    }
}
