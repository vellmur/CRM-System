<?php

namespace App\Repository;

use App\Entity\Master\Email\Email;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class EmailRepository extends ServiceEntityRepository
{
    /**
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Email::class);
    }

    /**
     * @return \Doctrine\ORM\Query
     */
    public function getEmailsLogQuery()
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
            ->where('e.isDraft = 0')
            ->groupBy('e.id')
            ->orderBy('e.createdAt', 'desc');

        return $qb->getQuery();
    }
}
