<?php

namespace App\Repository\Leaderboard;

use App\Entity\Character\Character;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class LeaderboardRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Character::class);
    }

    public function findRankOfCharacter(Character $searchedCharacter)
    {
        $countAhead = $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->where('c.prestigePoints > :pp')
            ->orWhere('c.prestigePoints = :pp AND c.id < :id')
            ->setParameter('pp',
                $searchedCharacter->getPrestigePoints())
            ->setParameter('id', $searchedCharacter->getId())
            ->getQuery()
            ->getSingleScalarResult();

        return (int) $countAhead + 1;

    }


}
