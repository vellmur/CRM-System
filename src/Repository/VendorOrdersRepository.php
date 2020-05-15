<?php

namespace App\Repository;

use App\Entity\Building\Building;
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
     * @param $building
     * @return array
     */
    public function getOrders($building)
    {
        $qb = $this->createQueryBuilder('v')
            ->where('v.building = :building')
            ->orderBy('v.orderDate')
            ->setParameter('building', $building);

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
