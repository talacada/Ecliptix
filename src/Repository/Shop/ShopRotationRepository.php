<?php

declare(strict_types=1);

namespace App\Repository\Shop;

use App\Entity\Character\Character;
use App\Entity\Shop\ShopRotation;
use App\Entity\Shop\ShopRotationEnum;
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
    public function findAllExpired(Character $character): array
    {
        /** @var ShopRotation[] */
        return $this->createQueryBuilder('shopRotation')
            ->andWhere('shopRotation.validUntil <= :now')
            ->andWhere('shopRotation.character = :character')
            ->setParameter('now', new \DateTimeImmutable('today'))
            ->setParameter('character', $character)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return ShopRotation[]
     */
    public function findAllByCharacter(Character $character): array
    {
        /** @var ShopRotation[] */
        return $this->createQueryBuilder('shopRotation')
            ->andWhere('shopRotation.character = :character')
            ->andWhere('shopRotation.validUntil > :now')
            ->andWhere('shopRotation.validFrom <= :now')
            ->setParameter('character', $character)
            ->setParameter('now', new \DateTimeImmutable('today'))
            ->getQuery()
            ->getResult()
        ;
    }

    public function hasActiveDailyRotation(Character $character): bool
    {
        return (int) $this->createQueryBuilder('shopRotation')
            ->select('COUNT(shopRotation.id)')
            ->andWhere('shopRotation.character = :character')
            ->andWhere('shopRotation.rotationType = :type')
            ->andWhere('shopRotation.validUntil > :now')
            ->andWhere('shopRotation.validFrom <= :now')
            ->setParameter('character', $character)
            ->setParameter('now', new \DateTimeImmutable('today'))
            ->setParameter('type', ShopRotationEnum::Daily)
            ->getQuery()
            ->getSingleScalarResult() > 0;
    }
}
