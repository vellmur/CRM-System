<?php

namespace App\Repository;

use App\Entity\Client\Client;
use App\Entity\Customer\Feedback;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class FeedbackRepository extends ServiceEntityRepository
{
    /**
     * FeedbackRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Feedback::class);
    }

    /**
     * @param Client $client
     * @return array
     */
    public function getWeeklyFeedbackReport(Client $client)
    {
        $today = new \DateTime("midnight");
        $weekNumber = $today->format('W') - 1;

        $qb = $this->createQueryBuilder('f');

        $qb->select('
                COUNT(0) as feedbackNum,
                SUM(case when f.isSatisfied = 1 then 1 else 0 end) as satisfied,
                SUM(case when f.isSatisfied = 0 then 1 else 0 end) as notSatisfied'
            )
            ->innerJoin('f.customer', 'members')
            ->where('members.client = :client')
            ->andWhere('WEEK(f.shareDate) = :currentWeek')
            ->setParameter('currentWeek', $weekNumber)
            ->setParameter('client', $client);

        return $qb->getQuery()->getSingleResult();
    }
}
