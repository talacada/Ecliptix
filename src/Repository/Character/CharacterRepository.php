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

    public function getCharacterByUserName(string $searchedName): ?Character
    {
        /** @var Character|null $result */
        $result = $this->createQueryBuilder('c')
            ->andWhere('c.username = :searchedName')
            ->setParameter('searchedName', $searchedName)
            ->getQuery()
            ->getOneOrNullResult();

        return $result;
    }

    public function getCharacterById(int $id): ?Character
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
