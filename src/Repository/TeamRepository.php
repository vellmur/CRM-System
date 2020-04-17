<?php

namespace App\Repository;

use App\Entity\Client\Team;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class TeamRepository extends ServiceEntityRepository
{
    /**
     * TeamRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Team::class);
    }

    /**
     * @return array
     */
    public function getSoftwareUsers()
    {
        $qb =
            $this->createQueryBuilder('t')
                ->leftJoin('t.client', 'f')
                ->leftJoin('t.user', 'u')
                ->orderBy('f.name');

        return $qb->getQuery()->getResult();
    }
}
