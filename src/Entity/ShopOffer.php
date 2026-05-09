<?php

namespace App\Entity;

use App\Repository\ShopOfferRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ShopOfferRepository::class)]
class ShopOffer
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'shopOffers')]
    #[ORM\JoinColumn(nullable: false)]
    private ?ShopRotation $rotation = null;

    #[ORM\Column]
    private ?int $goldPrice = null;

    #[ORM\Column]
    private ?int $diamondPrice = null;

    #[ORM\Column(length: 255)]
    private ?string $slot = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRotation(): ?ShopRotation
    {
        return $this->rotation;
    }

    public function setRotation(?ShopRotation $rotation): static
    {
        $this->rotation = $rotation;

        return $this;
    }

    public function getGoldPrice(): ?int
    {
        return $this->goldPrice;
    }

    public function setGoldPrice(int $goldPrice): static
    {
        $this->goldPrice = $goldPrice;

        return $this;
    }

    public function getDiamondPrice(): ?int
    {
        return $this->diamondPrice;
    }

    public function setDiamondPrice(int $diamondPrice): static
    {
        $this->diamondPrice = $diamondPrice;

        return $this;
    }

    public function getSlot(): ?string
    {
        return $this->slot;
    }

    public function setSlot(string $slot): static
    {
        $this->slot = $slot;

        return $this;
    }
}
