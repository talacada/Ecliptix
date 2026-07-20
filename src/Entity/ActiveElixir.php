<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use App\Entity\Character\Character;
use App\Entity\Item\ItemDefinition;
use App\Repository\Character\ActiveElixirRepository;
use App\State\Processor\Character\Elixir\ActiveElixirRemoveProcessor;
use App\State\Provider\Character\Elixir\ActiveElixirProvider;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\SerializedName;

#[ApiResource(
    operations: [
        new Get(
            uriTemplate: '{id}',
            provider: ActiveElixirProvider::class,
        ),
        new Delete(
            uriTemplate: '{id}',
            provider: ActiveElixirProvider::class,
            processor: ActiveElixirRemoveProcessor::class,
        ),
    ],
    routePrefix: 'character/elixir/',
    normalizationContext: ['groups' => [self::READ_GROUP]],
    security: 'is_granted("ROLE_USER")',
)]
#[ORM\Entity(repositoryClass: ActiveElixirRepository::class)]
class ActiveElixir
{
    public const READ_GROUP = 'elixir:read';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups([Character::READ_GROUP, self::READ_GROUP])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'activeElixirs')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Character $character = null;

    #[ORM\ManyToOne(targetEntity: ItemDefinition::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ItemDefinition $itemDefinition;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $expiresAt;

    #[Groups([Character::READ_GROUP, self::READ_GROUP])]
    #[SerializedName('name')]
    public function getElixirName(): string
    {
        return $this->itemDefinition->getName();
    }

    #[Groups([Character::READ_GROUP, self::READ_GROUP])]
    #[SerializedName('description')]
    public function getElixirDescription(): ?string
    {
        return $this->itemDefinition->getDescription();
    }

    #[Groups([Character::READ_GROUP, self::READ_GROUP])]
    #[SerializedName('percentageBonus')]
    public function getElixirPercentageBonus(): ?int
    {
        return $this->itemDefinition->getPercentageBonus();
    }

    #[Groups([Character::READ_GROUP, self::READ_GROUP])]
    #[SerializedName('remainingSeconds')]
    public function getRemainingSeconds(): int
    {
        return $this->expiresAt->getTimestamp() - new \DateTimeImmutable()->getTimestamp();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getItemDefinition(): ItemDefinition
    {
        return $this->itemDefinition;
    }

    public function setItemDefinition(ItemDefinition $itemDefinition): static
    {
        $this->itemDefinition = $itemDefinition;

        return $this;
    }

    public function getExpiresAt(): \DateTimeImmutable
    {
        return $this->expiresAt;
    }

    public function setExpiresAt(\DateTimeImmutable $expiresAt): static
    {
        $this->expiresAt = $expiresAt;

        return $this;
    }
}
