<?php

namespace App\Repository;

use App\Entity\EmailVerificationToken;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<EmailVerificationToken>
 */
class EmailVerificationTokenRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EmailVerificationToken::class);
    }

    public function getToken(mixed $data): ?EmailVerificationToken
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.token = :token')
            ->andWhere('t.exipres_at > :now')
            ->andWhere('t.used_at IS NULL')
            ->setParameter('token', $data->getToken())
            ->setParameter('now', new DateTimeImmutable('now'))
            ->getQuery()
            ->getOneOrNullResult();
    }
}
