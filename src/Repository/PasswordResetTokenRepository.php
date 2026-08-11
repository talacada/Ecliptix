<?php

namespace App\Repository;

use App\Entity\Character\Character;
use App\Entity\PasswordResetToken;
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
}
