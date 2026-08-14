<?php

namespace App\Repository;

use App\Entity\Character\Character;
use App\Entity\PasswordResetToken;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PasswordResetToken>
 */
class PasswordResetTokenRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PasswordResetToken::class);
    }
	public function getByCharacter(Character $character): ?PasswordResetToken
	{
        return $this->createQueryBuilder('p')
            ->andWhere('p.character = :character')
            ->andWhere('p.used_at IS NULL')
            ->setParameter('character', $character)
            ->getQuery()
            ->getOneOrNullResult();
	}

    public function getByToken(string $token): ?PasswordResetToken
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.token = :token')
            ->andWhere('p.used_at IS NULL')
            ->andWhere('p.expires_at >= :expires_at')
            ->setParameter('token', $token)
            ->setParameter('expires_at', new DateTimeImmutable('now'))
            ->getQuery()
            ->getOneOrNullResult();
    }
}
