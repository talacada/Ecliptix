<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Entity\Character\Character;
use App\Repository\FriendRelationRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ApiResource(
    operations: [
        new GetCollection(
            uriTemplate: '/friends',
            security: 'is_granted("ROLE_USER")',
        ),
        new Get(
            uriTemplate: '/friends/{friend_relation_id}',
            security: 'is_granted("ROLE_USER") and object.getFollower() == user',

        ),
        new Delete(
            uriTemplate: '/friends/{id}',
            security: 'is_granted("ROLE_USER") and object.getFollower() == user',
        )
    ]
)]
#[ORM\Entity(repositoryClass: FriendRelationRepository::class)]
class FriendRelation
{

    public const string READ_GROUP = 'friend_relation:read';
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups([self::READ_GROUP])]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Character $follower = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups([self::READ_GROUP])]
    private ?Character $followed = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFollower(): ?Character
    {
        return $this->follower;
    }

    public function setFollower(?Character $follower): static
    {
        $this->follower = $follower;

        return $this;
    }

    public function getFollowed(): ?Character
    {
        return $this->followed;
    }

    public function setFollowed(?Character $followed): static
    {
        $this->followed = $followed;

        return $this;
    }
}
