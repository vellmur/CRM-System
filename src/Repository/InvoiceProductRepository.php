<?php

namespace App\Repository;

use App\Entity\Customer\InvoiceProduct;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class InvoiceProductRepository extends ServiceEntityRepository
{
    /**
     * InvoiceProductRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, InvoiceProduct::class);
    }

    /**
     * @param $building
     * @return mixed
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function countRevenue($building)
    {
        $qb = $this->createQueryBuilder('p');

        $qb->select('
            SUM(product.price) * p.qty) as counter, 
            YEAR(p.createdAt) as year, 
            MONTH(p.createdAt) as month, 
            DAY(p.createdAt) as day',
            'product.name END as productName'
            )
            ->innerJoin('p.invoice', 'invoice')
            ->leftJoin('p.product', 'product')
            ->where('product.building = :building')
            ->andWhere('invoice.isPaid = 1')
            ->orderBy('productName, p.createdAt')
            ->groupBy('productName, year, month, day')
            ->setParameter('building', $building);

        $result = $qb->getQuery()->getResult();

       return $result;
    }
}
