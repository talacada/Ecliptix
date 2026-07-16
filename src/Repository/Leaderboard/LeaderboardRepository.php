<?php

namespace App\Repository\Leaderboard;

use App\Entity\Character\Character;
use App\Entity\Item\Item;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class LeaderboardRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Item::class);
    }

    public function findRankOfCharacter(Character $searchedCharacter)
    {
        //TODO
    }


}
