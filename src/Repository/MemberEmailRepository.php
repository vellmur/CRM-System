<?php

namespace App\Repository;

use App\Entity\Building\Building;
use App\Entity\Owner\Email\OwnerEmail;
use App\Entity\Owner\Owner;
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
        parent::__construct($registry, OwnerEmail::class);
    }

    /**
     * @param Building $building
     * @return \Doctrine\ORM\Query
     */
    public function getEmailsLogQuery(Building $building)
    {
        $qb = $this->createQueryBuilder('e');

        $qb->select('e as email, 
                COUNT(0) as total,
                SUM(case when recipients.isSent = 1 then 1 else 0 end) as sent,
                SUM(case when recipients.isDelivered = 1 then 1 else 0 end) as delivered,
                SUM(case when recipients.isOpened = 1 then 1 else 0 end) as opened,
                SUM(case when recipients.isClicked = 1 then 1 else 0 end) as clicked,
                SUM(case when recipients.isBounced = 1 then 1 else 0 end) as bounced'
            )
            ->innerJoin('e.recipients', 'recipients')
            ->where('e.building = :building')
            ->andWhere('e.isDraft = 0')
            ->setParameter('building', $building)
            ->groupBy('e.id')
            ->orderBy('e.createdAt', 'desc');

        return $qb->getQuery();
    }

    /**
     * @param Owner $owner
     * @param $typeId
     * @return bool
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function receivedWeekly(Owner $owner, $typeId)
    {
        $start_week = date("Y-m-d", strtotime('monday this week'));
        $end_week = date("Y-m-d", strtotime('sunday this week'));

        $qb = $this->createQueryBuilder('e');

        $qb->innerJoin('e.recipients', 'recipients')
            ->innerJoin('e.automatedEmail', 'automatedEmail')
            ->where('recipients.owner = :owner')
            ->andWhere('e.createdAt >= :start')
            ->andWhere('e.createdAt <= :end')
            ->andWhere('automatedEmail.type = :type')
            ->setParameter('owner', $owner)
            ->setParameter('start', $start_week)
            ->setParameter('end', $end_week)
            ->setParameter('type', $typeId)
            ->setMaxResults(1);

        $result = $qb->getQuery()->getOneOrNullResult();

        return $result ? true : false;
    }
}
