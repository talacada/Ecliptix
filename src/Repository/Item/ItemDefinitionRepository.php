<?php

declare(strict_types=1);

namespace App\Repository\Item;

use App\Entity\Item\ItemDefinition;
use App\Entity\Item\ItemSlotEnum;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ItemDefinition>
 */
class ItemDefinitionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ItemDefinition::class);
    }

    //    /**
    //     * @return ItemDefinition[] Returns an array of ItemDefinition objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('i')
    //            ->andWhere('i.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('i.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?ItemDefinition
    //    {
    //        return $this->createQueryBuilder('i')
    //            ->andWhere('i.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
    public function findRandomByLevel(int $level): ItemDefinition
    {
        /** @var ItemDefinition */
        return $this->createQueryBuilder('i')
            ->andWhere('i.requiredLevel >= :level - 2')
            ->andWhere('i.requiredLevel <= :level + 2')
            ->setParameter('level', $level)
            ->orderBy('RAND()')
            ->setMaxResults(1)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findRandomBySlot(ItemSlotEnum $slot): ?ItemDefinition
    {
        /** @var ItemDefinition|null */
        return $this->createQueryBuilder('d')
            ->where('d.desiredSlot = :slot')
            ->andWhere('d INSTANCE OF App\Entity\Item\ItemDefinition')
            ->setParameter('slot', $slot)
            ->orderBy('RAND()')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findRandomElixir(): ?ItemDefinition
    {
        /** @var ItemDefinition|null */
        return $this->createQueryBuilder('i')
            ->andWhere('i INSTANCE OF App\Entity\Item\ElixirDefinition')
            ->orderBy('RAND()')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
