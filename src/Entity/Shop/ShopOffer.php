<?php

namespace App\Entity\Shop;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use App\ApiResource\Item\ItemViewDTO;
use App\Entity\Item\ItemDefinition;
use App\Repository\Shop\ShopOfferRepository;
use App\State\Processor\Shop\Offer\ShopOfferBuyProcessor;
use App\State\Provider\Shop\Offer\ShopOfferProvider;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\SerializedName;

#[ApiResource(
    operations: [
        new Post(
            uriTemplate: '{id}',
            provider: ShopOfferProvider::class,
            processor: ShopOfferBuyProcessor::class,
        )
    ],
    routePrefix: 'shop/offer/',
    security: 'is_granted("ROLE_USER")',
)]
#[ORM\Entity(repositoryClass: ShopOfferRepository::class)]
class ShopOffer
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(ShopRotation::READ_GROUP)]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'shopOffers')]
    #[ORM\JoinColumn(nullable: false)]
    private ShopRotation $rotation;

    #[ORM\Column]
    #[Groups(ShopRotation::READ_GROUP)]
    private int $goldPrice = 0;

    #[ORM\Column]
    #[Groups(ShopRotation::READ_GROUP)]
    private int $diamondPrice = 0;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ItemDefinition $ItemDefinition;

    #[ORM\Column]
    private ?int $bonusDamage = null;

    #[ORM\Column]
    private ?int $bonusCrit = null;

    #[ORM\Column]
    private ?int $bonusHealth = null;

    public function __construct(ShopRotation $rotation, ItemDefinition $ItemDefinition)
    {
        $this->rotation = $rotation;
        $this->ItemDefinition = $ItemDefinition;
    }

    #[Groups(ShopRotation::READ_GROUP)]
    #[SerializedName('item')]
    public function getViewItem(): ItemViewDTO
    {
        $dto = new ItemViewDTO();
        $dto->buildDtoOnlyWithBonusStats($this->ItemDefinition, $this->bonusDamage, $this->bonusCrit, $this->bonusHealth);

        return $dto;
    }
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRotation(): ShopRotation
    {
        return $this->rotation;
    }

    public function setRotation(ShopRotation $rotation): static
    {
        $this->rotation = $rotation;

        return $this;
    }

    public function getGoldPrice(): int
    {
        return $this->goldPrice;
    }

    public function setGoldPrice(int $goldPrice): static
    {
        $this->goldPrice = $goldPrice;

        return $this;
    }

    public function getDiamondPrice(): int
    {
        return $this->diamondPrice;
    }

    public function setDiamondPrice(int $diamondPrice): static
    {
        $this->diamondPrice = $diamondPrice;

        return $this;
    }

    public function getItemDefinition(): ItemDefinition
    {
        return $this->ItemDefinition;
    }

    public function setItemDefinition(ItemDefinition $ItemDefinition): static
    {
        $this->ItemDefinition = $ItemDefinition;

        return $this;
    }

    public function getBonusDamage(): ?int
    {
        return $this->bonusDamage;
    }

    public function setBonusDamage(int $bonusDamage): static
    {
        $this->bonusDamage = $bonusDamage;

        return $this;
    }

    public function getBonusCrit(): ?int
    {
        return $this->bonusCrit;
    }

    public function setBonusCrit(int $bonusCrit): static
    {
        $this->bonusCrit = $bonusCrit;

        return $this;
    }

    public function getBonusHealth(): ?int
    {
        return $this->bonusHealth;
    }

    public function setBonusHealth(int $bonusHealth): static
    {
        $this->bonusHealth = $bonusHealth;

        return $this;
    }
}
