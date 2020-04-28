<?php

namespace App\Repository;

use App\Entity\Client\Client;
use App\Entity\Customer\Vendor;
use App\Entity\Customer\VendorOrder;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class VendorOrdersRepository extends ServiceEntityRepository
{
    /**
     * VendorOrdersRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VendorOrder::class);
    }

    /**
     * @param $client
     * @return array
     */
    public function getOrders($client)
    {
        $qb = $this->createQueryBuilder('v')
            ->where('v.client = :client')
            ->orderBy('v.orderDate')
            ->setParameter('client', $client);

        return $qb->getQuery()->getResult();
    }

    /**
     * @param Vendor $vendor
     * @return array
     */
    public function getVendorOrder(Vendor $vendor)
    {
        $qb = $this->createQueryBuilder('v')
            ->where('v.vendor = :vendor')
            ->orderBy('v.orderDate')
            ->setParameter('vendor', $vendor);

        return $qb->getQuery()->getResult();
    }
}
