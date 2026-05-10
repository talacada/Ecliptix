<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\CharacterRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CharacterRepository::class)]
#[ORM\Table(name: 'character')]
#[ApiResource]
class Character
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\Column(length: 255)]
    private string $username;

    #[ORM\Column(length: 255)]
    private string $email;

    #[ORM\Column]
    private int $gold = 0;

    #[ORM\Column]
    private int $diamonds = 0;

    #[ORM\Column]
    private int $level = 1;

    #[ORM\Column]
    private int $experience = 0;

    #[ORM\Column]
    private int $damage = 1;

    #[ORM\Column]
    private int $health = 1;

    /**
     * @var Collection<int, ShopRotation>
     */
    #[ORM\OneToMany(targetEntity: ShopRotation::class, mappedBy: 'character', orphanRemoval: true)]
    private Collection $shopRotations;

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
}
