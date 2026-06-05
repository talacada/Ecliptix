<?php

namespace App\Entity\Shop;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Entity\Character\Character;
use App\Repository\Shop\ShopRotationRepository;
use App\State\Provider\Shop\Rotation\ShopRotationProvider;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: ShopRotationRepository::class)]
#[ApiResource(
    operations: [
        new GetCollection(
            security: 'is_granted("ROLE_USER")',
            provider: ShopRotationProvider::class,
        ),
    ],
    normalizationContext: ['groups' => [self::READ_GROUP]],
)]
class ShopRotation
{
    public const string READ_GROUP = 'shopRotation:read';
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(self::READ_GROUP)]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    #[Groups(self::READ_GROUP)]
    private DateTimeImmutable $validFrom;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    #[Groups(self::READ_GROUP)]
    private DateTimeImmutable $validUntil;

    #[ORM\ManyToOne(inversedBy: 'shopRotations')]
    #[ORM\JoinColumn(nullable: false)]
    private Character $character;

    /**
     * @var Collection<int, ShopOffer>
     */
    #[ORM\OneToMany(targetEntity: ShopOffer::class, mappedBy: 'rotation', cascade: ['persist', 'remove'])]
    #[Groups(self::READ_GROUP)]
    private Collection $shopOffers;

    #[ORM\Column(enumType: ShopRotationEnum::class)]
    private ShopRotationEnum $RotationType;

    public function __construct()
    {
        $this->shopOffers = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getValidFrom(): DateTimeImmutable
    {
        return $this->validFrom;
    }

    public function setValidFrom(DateTimeImmutable $validFrom): static
    {
        $this->validFrom = $validFrom;

        return $this;
    }

    public function getValidUntil(): DateTimeImmutable
    {
        return $this->validUntil;
    }

    public function setValidUntil(DateTimeImmutable $validUntil): static
    {
        $this->validUntil = $validUntil;

        return $this;
    }

    public function getCharacter(): Character
    {
        return $this->character;
    }

    public function setCharacter(Character $character): static
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

    public function getRotationType(): ShopRotationEnum
    {
        return $this->RotationType;
    }

    public function setRotationType(ShopRotationEnum $RotationType): static
    {
        $this->RotationType = $RotationType;

        return $this;
    }
}
