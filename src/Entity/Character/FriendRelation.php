<?php

declare(strict_types=1);

namespace App\Entity\Character;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use App\Attribute\CurrentUserScope;
use App\Repository\FriendRelationRepository;
use App\State\Processor\Character\Friends\FriendsAddProcessor;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ApiResource(
    operations: [
        new GetCollection(
            uriTemplate: '/friends',
            security: 'is_granted("ROLE_USER")',
        ),
        new Get(
            uriTemplate: '/friends/{id}',
            security: 'is_granted("ROLE_USER")',
        ),
        new Delete(
            uriTemplate: '/friends/{id}',
            security: 'is_granted("ROLE_USER")',
        ),
        new Post(
            uriTemplate: '/friends/{id}',
            security: 'is_granted("ROLE_USER")',
            processor: FriendsAddProcessor::class,
        ),
    ],
)]
#[CurrentUserScope('character')]
#[ORM\Entity(repositoryClass: FriendRelationRepository::class)]
#[ORM\Table(name: 'friend_relation')]
#[ORM\UniqueConstraint(columns: ['character_id', 'friend_id'])]
class FriendRelation
{
    public const string READ_GROUP = 'friend_relation:read';
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups([self::READ_GROUP])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'friendsCollection')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Character $character = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups([self::READ_GROUP])]
    private ?Character $friend = null;

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

    public function getFriend(): ?Character
    {
        return $this->friend;
    }

    public function setFriend(?Character $friend): static
    {
        $this->friend = $friend;

        return $this;
    }
}
