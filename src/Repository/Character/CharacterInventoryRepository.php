<?php

namespace App\Repository\Character;

use App\Entity\Character\Character;
use App\Entity\Character\CharacterInventory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CharacterInventory>
 */
class CharacterInventoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CharacterInventory::class);
    }

    //    /**
    //     * @return CharacterInventory[] Returns an array of CharacterInventory objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('c.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?CharacterInventory
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

    public function findAllUnequipped(Character $character)
    {
        return $this->createQueryBuilder('ci')
            ->andwhere('ci.character = :character')
            ->andWhere('ci.equipped = true')
            ->setParameter('character', $character)
            ->getQuery()
            ->getResult()
        ;
    }
}
