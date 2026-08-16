<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use App\Entity\Character\Character;
use App\Repository\EmailVerificationTokenRepository;
use App\State\Processor\Auth\VerifyEmailProcessor;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: EmailVerificationTokenRepository::class)]
#[ApiResource(
    operations: [
        new Post(
            uriTemplate: '/auth/verify-email',
            processor: VerifyEmailProcessor::class,
        ),
    ]
)]
class EmailVerificationToken
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]

    private ?int $id = null;

    #[ORM\OneToOne(cascade: ['persist'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Character $character = null;

    #[ORM\Column(type: 'uuid')]
    private ?Uuid $token = null;

    #[ORM\Column]
    private ?DateTimeImmutable $expires_at = null;

    #[ORM\Column(nullable: true)]
    private ?DateTimeImmutable $used_at = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCharacter(): ?Character
    {
        return $this->character;
    }

    public function setCharacter(Character $character): static
    {
        $this->character = $character;

        return $this;
    }

    public function getToken(): ?Uuid
    {
        return $this->token;
    }

    public function setToken(Uuid $token): static
    {
        $this->token = $token;

        return $this;
    }

    public function getExpiresAt(): ?DateTimeImmutable
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
