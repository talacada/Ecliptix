<?php

declare(strict_types=1);

namespace App\Repository\Leaderboard;

use App\Entity\Character\Character;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Character>
 */
class LeaderboardRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Character::class);
    }

    public function findRankOfCharacter(Character $searchedCharacter): int
    {
        $countAhead = $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->where('c.prestigePoints > :pp')
            ->orWhere('c.prestigePoints = :pp AND c.id < :id')
            ->setParameter('pp', $searchedCharacter->getPrestigePoints())
            ->setParameter('id', $searchedCharacter->getId())
            ->getQuery()
            ->getSingleScalarResult();

        return (int) $countAhead + 1;
    }

    /**
     * @return Character[]
     */
    public function getLeaderboardAroundRank(int $rank, int $limit): array
    {
        $half = (int) floor($limit / 2);
        $offset = max(0, $rank - $half - 1);

        /** @var Character[] $result */
        $result = $this->createQueryBuilder('c')
            ->orderBy('c.prestigePoints', 'DESC')
            ->addOrderBy('c.id', 'ASC')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        return $result;
    }

    public function getLastRank(): int
    {
        return (int) $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getPrestigePointsAtRank(int $rank): int
    {
        return (int) $this->createQueryBuilder('c')
            ->select('c.prestigePoints')
            ->orderBy('c.prestigePoints', 'DESC')
            ->addOrderBy('c.id', 'ASC')
            ->setFirstResult($rank - 1)
            ->setMaxResults(1)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
