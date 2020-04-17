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
     * @param $client
     * @param $date
     * @return array
     */
    public function getOldShares($client, $date)
    {
        $qb = $this->createQueryBuilder('s')
            ->where('s.client = :client')
            ->andWhere('s.orderDate < :date')
            ->setParameter('client', $client)
            ->setParameter('date', $date);

        return $qb->getQuery()->getResult();
    }

    /**
     * @param Client $client
     * @return \Doctrine\Common\Collections\Collection|VendorOrder[] $orders
     */
    public function getVendorOrders(Client $client)
    {
        $qb = $this->createQueryBuilder('v')
            ->select('v, shareProducts, product')
            ->innerJoin('v.vendor', 'vendor')
            ->innerJoin('v.shareProducts', 'shareProducts')
            ->innerJoin('shareProducts.product', 'product')
            ->where('v.client = :client')
            ->andWhere('vendor.category <> :empty')
            ->andWhere('v.orderDate >= :now')
            ->setParameter('client', $client)
            ->setParameter('empty', 'a:0:{}')
            ->setParameter('now', new \DateTime("midnight"));

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
