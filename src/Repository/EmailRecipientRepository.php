<?php

namespace App\Repository;

use App\Entity\Owner\Email\OwnerEmail;
use App\Entity\Owner\Email\EmailRecipient;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class EmailRecipientRepository extends ServiceEntityRepository
{
    /**
     * EmailRecipientRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EmailRecipient::class);
    }

    /**
     * @param OwnerEmail $emailLog
     * @return \Doctrine\Common\Collections\Collection|EmailRecipient[] $recipients
     */
    public function getEmailRecipients(OwnerEmail $emailLog)
    {
        $qb = $this->createQueryBuilder('r');

        $qb->select('r, owners, feedback')
            ->leftJoin('r.owner' ,'owners')
            ->leftJoin('r.feedback', 'feedback')
            ->where('r.emailLog = :emailLog')
            ->orderBy('owners.firstname')
            ->addOrderBy('owners.lastname')
            ->setParameter('emailLog', $emailLog);

        return $qb->getQuery()->getResult();
    }

    /**
     * @param $email
     * @param $field
     * @param $value
     * @return array
     */
    public function getEmailRecipientsByField($email, $field, $value)
    {
        $qb = $this->createQueryBuilder('r');

        $qb->select('r, owners')
            ->leftJoin('r.owner' ,'owners')
            ->where('r.emailLog = :emailLog AND r.' . $field . '= :value')
            ->orderBy('owners.firstname')
            ->addOrderBy('owners.lastname')
            ->setParameter('email', $email)
            ->setParameter('value', $value);


        return $qb->getQuery()->getResult();
    }

    /**
     * @param $ids
     * @return \Doctrine\Common\Collections\Collection|EmailRecipient[] $recipients
     */
    public function getRecipientsByIds($ids)
    {
        $qb = $this->createQueryBuilder('r')
            ->select('r')
            ->where('r.id IN (:ids)')
            ->andWhere('r.isBounced = 0')
            ->setParameter('ids', $ids);

        return $qb->getQuery()->getResult();
    }
}
