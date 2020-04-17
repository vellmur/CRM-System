<?php

namespace App\Repository;

use App\Entity\Client\Client;
use App\Entity\Customer\Invoice;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class InvoiceRepository extends ServiceEntityRepository
{
    /**
     * InvoiceRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Invoice::class);
    }

    /**
     * @param Client $client
     * @param $status - All/Paid/Unpaid
     * @return \Doctrine\ORM\Query
     */
    public function getInvoices(Client $client, $status)
    {
        $qb = $this->createQueryBuilder('i')
            ->select('i, members')
            ->leftJoin('i.customer', 'members')
            ->leftJoin('members.client', 'client')
            ->where('client = :client')
            ->orderBy('i.createdAt', 'DESC')
            ->setParameter('client', $client);

        if ($status !== 'all')
            $qb->andWhere('i.isPaid = ' . ($status == 'paid' ? 1 : 0));

        return $qb->getQuery();
    }

    /**
     * @param Client $client
     * @return mixed
     */
    public function countOpenOrders(Client $client)
    {
        $now = new \DateTime();
        $today = $now->format('Y-m-d');

        $qb = $this->createQueryBuilder('i')
            ->select('
                SUM(case when i.orderDate = :today then 1 else 0 end) as today,
                SUM(case when i.orderDate > :today then 1 else 0 end) as future,
                SUM(case when i.orderDate >= :today then 1 else 0 end) as open
            ')
            ->innerJoin('i.customer', 'customer')
            ->innerJoin('customer.client', 'client')
            ->where('client = :client')
            ->setParameter('client', $client)
            ->setParameter('today', $today);

        return $qb->getQuery()->getSingleResult();
    }

    /**
     * @param Client $client
     * @param $period
     * @return \Doctrine\ORM\Query
     */
    public function searchOpenOrders(Client $client, $period)
    {
        $qb = $this->createQueryBuilder('i')
            ->select('i')
            ->innerJoin('i.customer', 'customer')
            ->innerJoin('customer.client', 'client')
            ->where('client = :client')
            ->setParameter('client', $client);

        switch ($period) {
            case 'today':
                $qb->andWhere('i.orderDate = :today');
                break;
            case 'future':
                $qb->andWhere('i.orderDate > :today');
                break;
            case 'open':
                $qb->andWhere('i.orderDate >= :today');
                break;
        }

        $today = new \DateTime();
        $qb->setParameter('today', $today->format('Y-m-d'));

        return $qb->getQuery()->getResult();
    }
}
