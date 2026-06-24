<?php

namespace App\Repository\Character;

use App\Entity\Character\Character;
use App\Entity\Character\CharacterInventory;
use App\Entity\Item\InventoryContainerEnum;
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

    public function getUnequippedItems(Character $character)
    {
        return $this->createQueryBuilder('ci')
            ->andwhere('ci.character = :character')
            ->andWhere('ci.container = :backpackContainer')
            ->setParameter('backpackContainer', InventoryContainerEnum::Backpack)
            ->setParameter('character', $character)
            ->getQuery()
            ->getResult()
        ;
    }

    public function getEquippedItems(Character $character): array
    {
        return $this->createQueryBuilder('ci')
            ->andwhere('ci.character = :character')
            ->andWhere('ci.container = :equippedContainer')
            ->setParameter('equippedContainer', InventoryContainerEnum::Equipped)
            ->setParameter('character', $character)
            ->getQuery()
            ->getResult()
            ;
    }

    public function getInventoryById(int $inventoryId): ?CharacterInventory
    {
        return $this->createQueryBuilder('ci')
            ->andWhere('ci.id = :inventoryId')
            ->setParameter('inventoryId', $inventoryId)
            ->getQuery()
            ->getOneOrNullResult()
            ;
    }

    public function getAllTakenPositions(Character $character): array
    {
        return $this->createQueryBuilder('ci')
            ->select('ci.position')
            ->andWhere('ci.character = :character')
            ->andWhere('ci.container = :backpackContainer')
            ->setParameter('backpackContainer', InventoryContainerEnum::Backpack)
            ->setParameter('character', $character)
            ->getQuery()
            ->getSingleColumnResult()
            ;
    }

    public function getOneByPosition(Character $character, int $position): ?CharacterInventory
    {
        return $this->createQueryBuilder('ci')
            ->andWhere('ci.character = :character')
            ->andWhere('ci.container = :backpackContainer')
            ->setParameter('backpackContainer', InventoryContainerEnum::Backpack)
            ->andWhere('ci.position = :position')
            ->setParameter('character', $character)
            ->setParameter('position', $position)
            ->getQuery()
            ->getOneOrNullResult()
            ;
    }
}
