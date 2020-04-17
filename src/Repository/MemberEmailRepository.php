<?php

namespace App\Repository;

use App\Entity\Client\Client;
use App\Entity\Customer\Email\CustomerEmail;
use App\Entity\Customer\Customer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class MemberEmailRepository extends ServiceEntityRepository
{
    /**
     * MemberEmailRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CustomerEmail::class);
    }

    /**
     * @param Client $client
     * @return \Doctrine\ORM\Query
     */
    public function getEmailsLogQuery(Client $client)
    {
        $qb = $this->createQueryBuilder('e');

        $qb->select('e as email, 
                COUNT(0) as total,
                SUM(case when recipients.isDelivered = 1 then 1 else 0 end) as delivered,
                SUM(case when recipients.isOpened = 1 then 1 else 0 end) as opened,
                SUM(case when recipients.isClicked = 1 then 1 else 0 end) as clicked,
                SUM(case when recipients.isBounced = 1 then 1 else 0 end) as bounced'
            )
            ->innerJoin('e.recipients', 'recipients')
            ->where('e.client = :client')
            ->andWhere('e.isDraft = 0')
            ->setParameter('client', $client)
            ->groupBy('e.id')
            ->orderBy('e.createdAt', 'desc');

        return $qb->getQuery();
    }

    /**
     * @param Client $client
     * @param $feedbackType
     * @return array
     */
    public function countFeedbackWeeklyStats(Client $client, $feedbackType)
    {
        $today = new \DateTime("midnight");
        $weekNumber = $today->format('W') - 1;

        $qb = $this->createQueryBuilder('e');

        $qb->select('
                COUNT(0) as total,
                SUM(case when recipients.isDelivered = 1 then 1 else 0 end) as delivered,
                SUM(case when recipients.isOpened = 1 then 1 else 0 end) as opened,
                SUM(case when recipients.isClicked = 1 then 1 else 0 end) as clicked
            ')
            ->innerJoin('e.recipients', 'recipients')
            ->innerJoin('e.automatedEmail', 'automatedEmail')
            ->where('e.client = :client')
            ->andWhere('e.isDraft = 0')
            ->andWhere('automatedEmail.type = :feedbackType')
            ->andWhere('WEEK(e.createdAt) = :currentWeek')
            ->setParameter('currentWeek', $weekNumber)
            ->setParameter('feedbackType', $feedbackType)
            ->setParameter('client', $client)
            ->orderBy('e.createdAt', 'desc');

        return $qb->getQuery()->getSingleResult();
    }

    /**
     * @param Customer $customer
     * @param $typeId
     * @return mixed
     */
    public function receivedWeekly(Customer $customer, $typeId)
    {
        $start_week = date("Y-m-d", strtotime('monday this week'));
        $end_week = date("Y-m-d", strtotime('sunday this week'));

        $qb = $this->createQueryBuilder('e');

        $qb->innerJoin('e.recipients', 'recipients')
            ->innerJoin('e.automatedEmail', 'automatedEmail')
            ->where('recipients.customer = :customer')
            ->andWhere('e.createdAt >= :start')
            ->andWhere('e.createdAt <= :end')
            ->andWhere('automatedEmail.type = :type')
            ->setParameter('customer', $customer)
            ->setParameter('start', $start_week)
            ->setParameter('end', $end_week)
            ->setParameter('type', $typeId)
            ->setMaxResults(1);

        $result = $qb->getQuery()->getOneOrNullResult();

        return $result ? true : false;
    }
}
