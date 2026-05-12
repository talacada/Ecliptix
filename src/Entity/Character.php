<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\ApiResource\Auth\RegisterInput;
use App\Repository\CharacterRepository;
use App\State\Processor\RegisterProcessor;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    operations: [
        new Get(),
        new Post(
            uriTemplate: '/auth/register',
            input: RegisterInput::class,
            processor: RegisterProcessor::class,
        ),
        new Patch(),
        new Delete(),
    ],
    normalizationContext: ['groups' => [self::READ_GROUP]],
)]
#[ORM\Entity(repositoryClass: CharacterRepository::class)]
#[ORM\Table(name: 'character')]
#[UniqueEntity('username')]
#[UniqueEntity('email')]
class Character
{
    public const string READ_GROUP = 'character:read';
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\Column(length: 255)]
    #[Groups([self::READ_GROUP])]
    private string $username;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Email should not be blank.')]
    #[Assert\Email(message: 'Email should be a valid email address.')]
    private string $email;

    #[ORM\Column]
    #[Groups([self::READ_GROUP])]
    private int $gold = 0;

    #[ORM\Column]
    #[Groups([self::READ_GROUP])]
    private int $diamonds = 0;

    #[ORM\Column]
    #[Groups([self::READ_GROUP])]
    private int $level = 1;

    #[ORM\Column]
    #[Groups([self::READ_GROUP])]
    private int $experience = 0;

    #[ORM\Column]
    #[Groups([self::READ_GROUP])]
    private int $damage = 1;

    #[ORM\Column]
    #[Groups([self::READ_GROUP])]
    private int $health = 1;

    /**
     * @var Collection<int, ShopRotation>
     */
    #[ORM\OneToMany(targetEntity: ShopRotation::class, mappedBy: 'character', orphanRemoval: true)]
    #[Groups([self::READ_GROUP])]
    private Collection $shopRotations;

    #[ORM\Column(length: 255)]
    private ?string $passwordHash = null;

    public function __construct()
    {
        $this->shopRotations = new ArrayCollection();
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

    public function getPasswordHash(): ?string
    {
        return $this->passwordHash;
    }

    public function setPasswordHash(string $passwordHash): static
    {
        $this->passwordHash = $passwordHash;

        return $this;
    }
}
