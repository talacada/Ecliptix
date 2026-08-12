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

    public function getToken(mixed $token): ?EmailVerificationToken
    {
        $tokenValue = $token instanceof EmailVerificationToken ? $token->getToken() : $token;

        return $this->createQueryBuilder('t')
            ->andWhere('t.token = :token')
            ->andWhere('t.expires_at > :now')
            ->andWhere('t.used_at IS NULL')
            ->setParameter('token', $tokenValue)
            ->setParameter('now', new DateTimeImmutable('now'))
            ->getQuery()
            ->getOneOrNullResult();
    }
}
