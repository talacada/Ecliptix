<?php

declare(strict_types=1);

namespace App\Repository\Character;

use App\Entity\Character\Character;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Character>
 */
class CharacterRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Character::class);
    }

    public function findForLeaderboard(?string $nameFilter, int $limit, int $offset): array
    {
        $qb = $this->createQueryBuilder('c')
            ->orderBy('c.prestigePoints', 'DESC')
            ->addOrderBy('c.id', 'ASC');

        if ($nameFilter !== null && $nameFilter !== '') {
            $qb->andWhere('c.username LIKE :name')
                ->setParameter('name', '%' . $nameFilter . '%');
        }

        return $qb->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getQuery()
            ->getResult();
    }

    public function countForLeaderboard(?string $nameFilter): int
    {
        $qb = $this->createQueryBuilder('c')
            ->select('COUNT(c.id)');

        if ($nameFilter !== null && $nameFilter !== '') {
            $qb->andWhere('c.username LIKE :name')
                ->setParameter('name', '%' . $nameFilter . '%');
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

}
