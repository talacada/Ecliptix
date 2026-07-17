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

    public function getCharacterByName(string $searchedName): ?Character
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.name = :searchedName')
            ->setParameter('searchedName', $searchedName)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function countForLeaderboard(): int
    {
        //todo
    }

}
