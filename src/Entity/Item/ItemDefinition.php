<?php

namespace App\Entity\Item;

use App\Entity\Shop\ShopRotation;
use App\Repository\Item\ItemDefinitionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: ItemDefinitionRepository::class)]
#[ORM\InheritanceType('SINGLE_TABLE')]
#[ORM\DiscriminatorColumn(name: 'item_type', type: 'string')]
#[ORM\DiscriminatorMap([
    'equipment' => ItemDefinition::class,
    'elixir' => ElixirDefinition::class,
])]
class ItemDefinition
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(ShopRotation::READ_GROUP)]
    private string $name = "";

    #[ORM\Column]
    #[Groups(ShopRotation::READ_GROUP)]
    private int $baseDamage = 0;

    #[ORM\Column]
    #[Groups(ShopRotation::READ_GROUP)]
    private int $baseCrit = 0;

    #[ORM\Column]
    #[Groups(ShopRotation::READ_GROUP)]
    private int $baseHealth = 0;

    #[ORM\Column]
    #[Groups(ShopRotation::READ_GROUP)]
    private int $requiredLevel = 1;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(ShopRotation::READ_GROUP)]
    private ?string $description = null;

    /**
     * @var Collection<int, Item>
     */
    #[ORM\OneToMany(targetEntity: Item::class, mappedBy: 'definition', orphanRemoval: true)]
    private Collection $items;

    #[ORM\Column(enumType: ItemRarityEnum::class)]
    //TODO je opravdu potřeba?
    #[Groups(ShopRotation::READ_GROUP)]
    private ItemRarityEnum $rarity;

    #[ORM\Column(enumType: ItemSlotEnum::class)]
    #[Groups(ShopRotation::READ_GROUP)]
    private ItemSlotEnum $desiredSlot;

    #[ORM\Column]
    #[Groups(ShopRotation::READ_GROUP)]
    private int $baseGoldPrice = 0;

    #[ORM\Column]
    #[Groups(ShopRotation::READ_GROUP)]
    private int $baseDiamondPrice = 0;

    #[ORM\Column(nullable: true, enumType: ElixirTypeEnum::class)]
    #[Groups(ShopRotation::READ_GROUP)]
    private ?ElixirTypeEnum $elixirType = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    #[Groups(ShopRotation::READ_GROUP)]
    private ?int $percentageBonus = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    #[Groups(ShopRotation::READ_GROUP)]
    private ?int $durationSeconds = null;

    public function __construct()
    {
        $this->items = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getBaseDamage(): int
    {
        return $this->baseDamage;
    }

    public function setBaseDamage(int $baseDamage): static
    {
        $this->baseDamage = $baseDamage;

        return $this;
    }

    public function getBaseCrit(): int
    {
        return $this->baseCrit;
    }

    public function setBaseCrit(int $baseCrit): static
    {
        $this->baseCrit = $baseCrit;

        return $this;
    }

    public function getBaseHealth(): int
    {
        return $this->baseHealth;
    }

    public function setBaseHealth(int $baseHealth): static
    {
        $this->baseHealth = $baseHealth;

        return $this;
    }

    public function getRequiredLevel(): int
    {
        return $this->requiredLevel;
    }

    public function setRequiredLevel(int $requiredLevel): static
    {
        $this->requiredLevel = $requiredLevel;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return Collection<int, Item>
     */
    public function getItems(): Collection
    {
        return $this->items;
    }

    public function addItems(Item $item): static
    {
        if (!$this->items->contains($item)) {
            $this->items->add($item);
            $item->setDefinition($this);
        }

        return $this;
    }

    public function removeItems(Item $item): static
    {
        $this->items->removeElement($item);

        return $this;
    }

    public function getRarity(): ?ItemRarityEnum
    {
        return $this->rarity;
    }

    public function setRarity(ItemRarityEnum $rarity): static
    {
        $this->rarity = $rarity;

        return $this;
    }

    public function getDesiredSlot(): ?ItemSlotEnum
    {
        return $this->desiredSlot;
    }

    public function setDesiredSlot(ItemSlotEnum $desiredSlot): static
    {
        $this->desiredSlot = $desiredSlot;

        return $this;
    }

    public function getBaseGoldPrice(): ?int
    {
        return $this->baseGoldPrice;
    }

    public function setBaseGoldPrice(int $baseGoldPrice): static
    {
        $this->baseGoldPrice = $baseGoldPrice;

        return $this;
    }

    public function getBaseDiamondPrice(): ?int
    {
        return $this->baseDiamondPrice;
    }

    public function setBaseDiamondPrice(int $baseDiamondPrice): static
    {
        $this->baseDiamondPrice = $baseDiamondPrice;

        return $this;
    }
}
