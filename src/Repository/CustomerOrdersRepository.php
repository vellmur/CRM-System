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
     * @param $client
     * @return array
     */
    public function getOrders($client)
    {
        $qb = $this->createQueryBuilder('s')
                    ->where('s.client = :client')
                    ->orderBy('s.startDate')
                    ->setParameter('client', $client);

        return $qb->getQuery()->getResult();
    }
}
