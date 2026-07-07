<?php

declare(strict_types=1);

namespace App\Repository\Shop;

use App\Entity\Character\Character;
use App\Entity\Shop\ShopRotation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ShopRotation>
 */
class ShopRotationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ShopRotation::class);
    }

    //    /**
    //     * @return ShopRotation[] Returns an array of ShopRotation objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('s.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?ShopRotation
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
    /**
     * @return ShopRotation[]
     */
    public function findAllExpired(Character $character)
    {
        return $this->createQueryBuilder('shopRotation')
            ->andWhere('shopRotation.validUntil <= :now')
            ->andWhere('shopRotation.character = :character')
            ->setParameter('now', new \DateTimeImmutable('today'))
            ->setParameter('character', $character)
            ->getQuery()
            ->execute()
        ;
    }

    /**
     * @return ShopRotation[]
     */
    public function findAllByCharacter(Character $character)
    {
        return $this->createQueryBuilder('shopRotation')
            ->andWhere('shopRotation.character = :character')
            ->andWhere('shopRotation.validUntil > :now')
            ->andWhere('shopRotation.validFrom <= :now') // TODO this is inconsistent
            ->setParameter('character', $character)
            ->setParameter('now', new \DateTimeImmutable('today'))
            ->getQuery()
            ->execute()
        ;
    }
}
