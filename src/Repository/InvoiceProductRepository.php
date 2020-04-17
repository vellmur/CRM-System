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
     * @param $client
     * @return mixed
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function countRevenue($client)
    {
        $qb = $this->createQueryBuilder('p');

        $qb->select('
            SUM((CASE WHEN (p.product IS NULL) THEN share.price ELSE product.price END) * p.qty) as counter, 
            YEAR(p.createdAt) as year, 
            MONTH(p.createdAt) as month, 
            DAY(p.createdAt) as day',
            '(CASE WHEN (p.product IS NULL) THEN share.name ELSE product.name END) as productName'
            )
            ->innerJoin('p.invoice', 'invoice')
            ->leftJoin('p.share', 'share')
            ->leftJoin('p.product', 'product')
            ->where('share.client = :client OR product.client = :client')
            ->andWhere('invoice.isPaid = 1')
            ->orderBy('productName, p.createdAt')
            ->groupBy('productName, year, month, day')
            ->setParameter('client', $client);

        $result = $qb->getQuery()->getResult();

       return $result;
    }
}
