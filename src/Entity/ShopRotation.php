<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\ShopRotationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ShopRotationRepository::class)]
#[ApiResource]
class ShopRotation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private ?\DateTimeImmutable $validFrom = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private ?\DateTimeImmutable $validUntill = null;

    #[ORM\ManyToOne(inversedBy: 'shopRotations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Character $character = null;

    /**
     * @var Collection<int, ShopOffer>
     */
    #[ORM\OneToMany(targetEntity: ShopOffer::class, mappedBy: 'rotation', orphanRemoval: true)]
    private Collection $shopOffers;

    public function __construct()
    {
        $this->shopOffers = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getValidFrom(): ?\DateTimeImmutable
    {
        return $this->validFrom;
    }

    public function setValidFrom(\DateTimeImmutable $validFrom): static
    {
        $this->validFrom = $validFrom;

        return $this;
    }

    public function getValidUntill(): ?\DateTimeImmutable
    {
        return $this->validUntill;
    }

    public function setValidUntill(\DateTimeImmutable $validUntill): static
    {
        $this->validUntill = $validUntill;

        return $this;
    }

    public function getCharacter(): ?Character
    {
        return $this->character;
    }

    public function setCharacter(?Character $character): static
    {
        $this->character = $character;

        return $this;
    }

    /**
     * @return Collection<int, ShopOffer>
     */
    public function getShopOffers(): Collection
    {
        return $this->shopOffers;
    }

    public function addShopOffer(ShopOffer $shopOffer): static
    {
        if (!$this->shopOffers->contains($shopOffer)) {
            $this->shopOffers->add($shopOffer);
            $shopOffer->setRotation($this);
        }

        return $this;
    }

    public function removeShopOffer(ShopOffer $shopOffer): static
    {
        if ($this->shopOffers->removeElement($shopOffer)) {
            // set the owning side to null (unless already changed)
            if ($shopOffer->getRotation() === $this) {
                $shopOffer->setRotation(null);
            }
        }

        return $this;
    }
}
