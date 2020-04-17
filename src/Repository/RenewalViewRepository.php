<?php

namespace App\Repository;

use App\Entity\Client\Client;
use App\Entity\Customer\RenewalView;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class RenewalViewRepository extends ServiceEntityRepository
{
    /**
     * RenewalViewRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RenewalView::class);
    }

    /**
     * @param Client $client
     * @param $step
     * @return array
     */
    public function countRenewalTabsViews(Client $client, $step = null)
    {
        $qb = $this->createQueryBuilder('s')
            ->select('
            COUNT(s.id) as counter, 
            YEAR(s.createdAt) as year, 
            MONTH(s.createdAt) as month, 
            DAY(s.createdAt) as day, 
            s.step as item'
            )
            ->where('s.client = :client')
            ->orderBy('s.step, s.createdAt')
            ->groupBy('item, year, month, day')
            ->setParameter('client', $client);

        if ($step) {
            $qb->andWhere('s.step = :step')->setParameter('step', $step);
        }

        $results = $qb->getQuery()->getResult();

        return $results;
    }
}
