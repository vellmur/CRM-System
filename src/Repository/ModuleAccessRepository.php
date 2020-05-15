<?php

namespace App\Repository;

use App\Entity\Building\Building;
use App\Entity\ModuleAccess;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ModuleAccessRepository extends ServiceEntityRepository
{
    /**
     * ModuleAccessRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ModuleAccess::class);
    }

    /**
     * @param Building $building
     * @param $moduleId
     * @return mixed|null
     */
    public function getModuleAccess(Building $building, $moduleId)
    {
        $qb = $this->createQueryBuilder('a')
            ->select()
            ->where('a.building = :building')
            ->andWhere('a.module = :module')
            ->setParameter('building', $building)
            ->setParameter('module', $moduleId)
            ->setMaxResults(1);

        $results = $qb->getQuery()->getResult();

        return count($results) == 1 ? $qb->getQuery()->getResult()[0] : null;
    }
}
