<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\ActiveElixir;
use App\Entity\Character\Character;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ActiveElixir>
 */
class ActiveElixirRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ActiveElixir::class);
    }

    //    /**
    //     * @return ActiveElixir[] Returns an array of ActiveElixir objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('a.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?ActiveElixir
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
    public function findByName(string $name, Character $character): ?ActiveElixir
    {
        /** @var ActiveElixir|null */
        return $this->createQueryBuilder('a')
            ->innerJoin('a.itemDefinition', 'd')
            ->andWhere('a.character = :character')
            ->andWhere('d.name = :name')
            ->setParameter('character', $character)
            ->setParameter('name', $name)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findOneById(int $id): ?ActiveElixir
    {
        /** @var ActiveElixir|null */
        return $this->createQueryBuilder('a')
            ->andWhere('a.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
