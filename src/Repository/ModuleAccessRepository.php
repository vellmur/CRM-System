<?php

namespace App\Repository;

use App\Entity\Client\Client;
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
     * @param Client $client
     * @param $moduleId
     * @return mixed|null
     */
    public function getModuleAccess(Client $client, $moduleId)
    {
        $qb = $this->createQueryBuilder('a')
            ->select()
            ->where('a.client = :client')
            ->andWhere('a.module = :module')
            ->setParameter('client', $client)
            ->setParameter('module', $moduleId)
            ->setMaxResults(1);

        $results = $qb->getQuery()->getResult();

        return count($results) == 1 ? $qb->getQuery()->getResult()[0] : null;
    }
}
