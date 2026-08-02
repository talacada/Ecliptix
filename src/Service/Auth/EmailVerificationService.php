<?php

namespace App\Service\Auth;

use App\Entity\Character\Character;
use App\Entity\EmailVerificationToken;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Uid\Uuid;

class EmailVerificationService
{

    public function __construct(
        private EntityManagerInterface $entityManager
    ){ }

    public function createToken(Character $character): EmailVerificationToken
    {
        $token = new EmailVerificationToken();
        $token->setCharacter($character);
        $token->setToken(Uuid::v4());
        $token->setExipresAt(new DateTimeImmutable('now + 24 hours'));
        $token->setUsedAt(null);

        $this->entityManager->persist($token);
        $this->entityManager->flush();

        return $token;
    }
}
