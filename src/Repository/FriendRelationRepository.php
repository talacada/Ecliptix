<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Character\Character;
use App\Entity\Character\FriendRelation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<FriendRelation>
 */
class FriendRelationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FriendRelation::class);
    }

    //    /**
    //     * @return FriendRelation[] Returns an array of FriendRelation objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('f')
    //            ->andWhere('f.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('f.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?FriendRelation
    //    {
    //        return $this->createQueryBuilder('f')
    //            ->andWhere('f.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
    public function findRelation(Character $character, Character $friend): ?FriendRelation
    {
        return $this->createQueryBuilder('fr')
            ->andWhere('fr.character = :character')
            ->andWhere('fr.friend = :friend')
            ->setParameter('character', $character)
            ->setParameter('friend', $friend)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
