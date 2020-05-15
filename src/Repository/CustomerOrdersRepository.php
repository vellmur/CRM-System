<?php

namespace App\Repository;

use App\Entity\Customer\CustomerOrders;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class CustomerOrdersRepository extends ServiceEntityRepository
{
    /**
     * CustomerOrdersRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CustomerOrders::class);
    }

    /**
     * @param $building
     * @return array
     */
    public function getOrders($building)
    {
        $qb = $this->createQueryBuilder('s')
                    ->where('s.building = :building')
                    ->orderBy('s.startDate')
                    ->setParameter('building', $building);

        return $qb->getQuery()->getResult();
    }
}
