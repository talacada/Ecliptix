<?php

namespace App\Repository;

use App\Entity\Appearance\AppearanceTypeEnum;
use App\Entity\AppearanceOption;
use App\Entity\Race;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AppearanceOption>
 */
class AppearanceOptionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AppearanceOption::class);
    }

    public function getByIdRaceType(int $id, Race $race, AppearanceTypeEnum $type): ?AppearanceOption
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.id = :id')
            ->andWhere('a.race = :race')
            ->andWhere('a.type = :type')
            ->setParameter('id', $id)
            ->setParameter('race', $race)
            ->setParameter('type', $type->value)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function getAllOptionsByRace(Race $race): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.race = :race')
            ->setParameter('race', $race)
            ->getQuery()
            ->getResult();
    }
}
