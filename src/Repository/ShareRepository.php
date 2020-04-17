<?php

namespace App\Repository;

use App\Entity\Client\Client;
use App\Entity\Customer\Share;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class ShareRepository extends ServiceEntityRepository
{
    /**
     * ShareRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Share::class);
    }

    /**
     * @param Client $client
     * @return \Doctrine\Common\Collections\Collection|Share[] $feedback
     */
    public function getWeeklyFeedback(Client $client)
    {
        $today = new \DateTime('midnight');
        $weekNumber = $today->format('W') - 1;

        $qb = $this->createQueryBuilder('s');

        $qb
            ->select('
                SUM(case when feedback.isSatisfied = 1 then 1 else 0 end) as satisfied,
                SUM(case when feedback.isSatisfied = 0 then 1 else 0 end) as notSatisfied,
                COUNT(customers.id) as membersNum'
            )
            ->leftJoin('s.shares', 'shares')
            ->innerJoin('shares.customer', 'customers')
            ->innerJoin('s.feedback', 'feedback')
            ->where('s.client = :client')
            ->andWhere('WEEK(feedback.createdAt) = :weekNumber')
            ->setParameter('client', $client)
            ->setParameter('weekNumber', $weekNumber);

        return $qb->getQuery()->getResult();
    }

    /**
     * @param Client $client
     * @param $isActive
     * @return array
     */
    public function getShares(Client $client, $isActive)
    {
        $qb = $this->createQueryBuilder('s');

        $qb->select('s')
            ->where('s.client = :client')
            ->orderBy('s.name')
            ->setParameter('client', $client);

        if ($isActive) $qb->andWhere('s.isActive = ' . $isActive);

        return $qb->getQuery()->getResult();
    }
}
