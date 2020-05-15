<?php

namespace App\Repository;

use App\Entity\Building\Building;
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
     * @param Building $building
     * @param $status - All/Paid/Unpaid
     * @return \Doctrine\ORM\Query
     */
    public function getInvoices(Building $building, $status)
    {
        $qb = $this->createQueryBuilder('i')
            ->select('i, members')
            ->leftJoin('i.customer', 'members')
            ->leftJoin('members.building', 'building')
            ->where('building = :building')
            ->orderBy('i.createdAt', 'DESC')
            ->setParameter('building', $building);

        if ($status !== 'all')
            $qb->andWhere('i.isPaid = ' . ($status == 'paid' ? 1 : 0));

        return $qb->getQuery();
    }

    /**
     * @param Building $building
     * @return mixed
     */
    public function countOpenOrders(Building $building)
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
            ->innerJoin('customer.building', 'building')
            ->where('building = :building')
            ->setParameter('building', $building)
            ->setParameter('today', $today);

        return $qb->getQuery()->getSingleResult();
    }

    /**
     * @param Building $building
     * @param $period
     * @return \Doctrine\ORM\Query
     */
    public function searchOpenOrders(Building $building, $period)
    {
        $qb = $this->createQueryBuilder('i')
            ->select('i')
            ->innerJoin('i.customer', 'customer')
            ->innerJoin('customer.building', 'building')
            ->where('building = :building')
            ->setParameter('building', $building);

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
