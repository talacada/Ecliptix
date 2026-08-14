<?php

namespace App\Entity;

use App\Entity\Character\Character;
use App\Repository\PasswordResetTokenRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: PasswordResetTokenRepository::class)]
class PasswordResetToken
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private Character $character;

    #[ORM\Column(type: 'uuid')]
    private Uuid $token;

    #[ORM\Column]
    private DateTimeImmutable $expires_at;

    #[ORM\Column(nullable: true)]
    private ?DateTimeImmutable $used_at = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getToken(): Uuid
    {
        return $this->token;
    }

    public function setToken(Uuid $token): static
    {
        $this->token = $token;

        return $this;
    }

    public function getExpiresAt(): DateTimeImmutable
    {
        return $this->expires_at;
    }

    public function setExpiresAt(DateTimeImmutable $expires_at): static
    {
        $this->expires_at = $expires_at;

        return $this;
    }

    public function getUsedAt(): ?DateTimeImmutable
    {
        return $this->used_at;
    }

    public function setUsedAt(?DateTimeImmutable $used_at): static
    {
        $this->used_at = $used_at;

        return $this;
    }
}
