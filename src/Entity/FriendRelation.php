<?php

namespace App\Entity;

use App\Entity\Character\Character;
use App\Repository\FriendRelationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FriendRelationRepository::class)]
class FriendRelation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Character $follower = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
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
