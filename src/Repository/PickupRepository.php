<?php

namespace App\Repository;

use App\Entity\Client\Client;
use App\Entity\Customer\Customer;
use App\Entity\Customer\CustomerShare;
use App\Entity\Customer\Pickup;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Query\Expr\Join;

class PickupRepository extends ServiceEntityRepository
{
    /**
     * PickupRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Pickup::class);
    }

    /**
     * Get list of all pickups with created orders with a date ranges
     * Query returns list of not skipped pickups for harvest list
     *
     * @param Client $client
     * @return \Doctrine\Common\Collections\Collection|Pickup[] $pickups
     */
    public function getHarvestPickups(Client $client)
    {
        $qb = $this->createQueryBuilder('p');

        $qb
            ->select('p, customerShare, share, orders, shareProducts, product')
            ->innerJoin('p.share', 'customerShare')
            ->innerJoin('customerShare.share', 'share')
            ->innerJoin('share.customerOrders', 'orders')
            ->innerJoin('orders.shareProducts', 'shareProducts')
            ->innerJoin('shareProducts.product', 'product')
            ->where('share.client = :client')
            ->andWhere('orders.endDate >= :now')
            ->andWhere('p.skipped = :skipped')
            ->setParameters([
                'client' => $client,
                'skipped' => false,
                'now' => new \DateTime("midnight")
            ]);

        return $qb->getQuery()->getResult();
    }

    /**
     *
     * Get all following pickups: Pickups that must be received on a future and only for shares with MEMBER(1) type
     *
     * @param Customer $customer
     * @param CustomerShare|null $share
     * @return array
     */
    public function getFollowingPickups(Customer $customer, CustomerShare $share = null)
    {
        $qb = $this->createQueryBuilder('p')
            ->select('p, s')
            ->leftJoin('p.share', 's')
            ->leftJoin('s.share', 'share')
            ->where("s.customer = :customer")
            ->andWhere('s.type = 1')
            ->andWhere('p.date >= :now')
            ->andWhere('s.status <> 3')
            ->orderBy('p.date')
            ->setParameter('customer', $customer)
            ->setParameter('now', new \DateTime("midnight"));

        if ($share) {
            $qb->andWhere('p.share = :share')->setParameter('share', $share);
        }

        $pickups = $qb->getQuery()->getResult();

        $sharePickups = [];

        foreach ($pickups as $pickup) {
            if ($pickup->getShare()) {
                $sharePickups[$pickup->getShare()->getId()][] = $pickup;
            }
        }

        return $sharePickups;
    }

    /**
     * @param Customer $customer
     * @return array
     */
    public function getSharesFeedback(Customer $customer)
    {
        $date = new \DateTime("midnight");
        $from = $date->modify('-6 day');

        $today = new \DateTime("midnight");
        $to = $date->modify('-1 day');

        $qb = $this->createQueryBuilder('p')
            ->select('p, share, customerShare, feedback')
            ->innerJoin('p.share', 'customerShare')
            ->innerJoin('customerShare.share', 'share')
            ->leftJoin('share.feedback', 'feedback', Join::WITH, 'feedback.share = share and feedback.shareDate > :from and feedback.shareDate < :to and feedback.customer = :customer')
            ->where('customerShare.customer = :customer')
            ->andWhere('p.date > :from and p.date < :to')
            ->setParameter('customer', $customer)
            ->setParameter('from', $from)
            ->setParameter('to', $today);

        return $qb->getQuery()->getResult();
    }

    /**
     * @param Client $client
     * @param $year
     * @param $week
     * @return \Doctrine\Common\Collections\Collection|Pickup[] $pickups
     */
    public function getWeekPickups(Client $client, $year, $week)
    {
        $qb = $this->createQueryBuilder('p')
            ->select('p')
            ->innerJoin('p.share', 'customerShare')
            ->innerJoin('customerShare.share', 'share')
            ->where('share.client = :client')
            ->andWhere('YEAR(p.date) = :year')
            ->andWhere('WEEK(p.date) = :week')
            ->andWhere('p.isSuspended = :isSuspended')
            ->setParameter('client', $client)
            ->setParameter('year', $year)
            ->setParameter('week', $week - 1)
            ->setParameter('isSuspended' , false);

        return $qb->getQuery()->getResult();
    }

    /**
     * @param Client $client
     * @param $year
     * @param $week
     * @return \Doctrine\Common\Collections\Collection|Pickup[] $pickups
     */
    public function getSuspendedPickups(Client $client, $year, $week)
    {
        $qb = $this->createQueryBuilder('p')
            ->select('p')
            ->innerJoin('p.share', 'customerShare')
            ->innerJoin('customerShare.share', 'share')
            ->where('share.client = :client')
            ->andWhere('p.isSuspended = ' . true)
            ->andWhere('YEAR(p.date) = :year')
            ->andWhere('WEEK(p.date) = :week')
            ->setParameter('client', $client)
            ->setParameter('year', $year)
            ->setParameter('week', $week - 1);

        return $qb->getQuery()->getResult();
    }
}
