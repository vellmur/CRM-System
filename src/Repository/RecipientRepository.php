<?php

namespace App\Repository;

use App\Entity\Master\Email\Email;
use App\Entity\Master\Email\Recipient;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class RecipientRepository extends ServiceEntityRepository
{
    /**
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Recipient::class);
    }

    /**
     * @param Email $emailLog
     * @return \Doctrine\Common\Collections\Collection|Recipient[] $recipients
     */
    public function getEmailRecipients(Email $emailLog)
    {
        $qb = $this->createQueryBuilder('r');

        $qb->select('r, buildings')
            ->leftJoin('r.building' ,'buildings')
            ->where('r.emailLog = :email')
            ->orderBy('buildings.name')
            ->setParameter('email', $emailLog);

        return $qb->getQuery()->getResult();
    }

    /**
     * @param $ids
     * @return mixed
     */
    public function getRecipientsByIds($ids)
    {
        $qb = $this->createQueryBuilder('r');

        $qb->select('r')
            ->where('r.id IN (:ids)')
            ->andWhere('r.isBounced = 0')
            ->setParameter('ids', $ids);

        return $qb->getQuery()->getResult();
    }
}
