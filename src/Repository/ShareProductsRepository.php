<?php

namespace App\Repository;

use App\Entity\Customer\ShareProduct;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class ShareProductsRepository extends ServiceEntityRepository
{
    /**
     * ShareProductsRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ShareProduct::class);
    }

    /**
     * @param $share
     * @param $role
     * @return array
     */
    public function getOrderProducts($share, $role)
    {
        $q = $this->createQueryBuilder('o');

        if ($role == 'member') {
            $q->innerJoin('o.memberOrder', 'memberOrder')
                ->where('o.memberOrder = :order');
        } else {
            $q->innerJoin('o.vendorOrder', 'vendorOrder')
                ->where('o.vendorOrder = :order');
        }

        $q->innerJoin('o.product', 'product')
            ->innerJoin('product.plant', 'p')
            ->innerJoin('p.plant', 'plant')
            ->orderBy('product.name')
            ->addOrderBy('plant.name')
            ->addOrderBy('plant.subName')
            ->setParameter('order', $share);

        return $q->getQuery()->getResult();
    }

    /**
     * @param $client
     * @return array
     */
    public function countShareTotalPrice($client)
    {
        $q = $this->createQueryBuilder('s');

        $q->select('SUM(s.price)')
            ->where('s.client = :client')
            ->setParameter('client', $client);

        return $q->getQuery()->getSingleScalarResult();
    }
}
