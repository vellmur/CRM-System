<?php

namespace App\Repository;

use App\Entity\Customer\Email\CustomerEmail;
use App\Entity\Customer\Email\EmailRecipient;
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
     * @param CustomerEmail $emailLog
     * @return mixed
     */
    public function getEmailRecipients(CustomerEmail $emailLog)
    {
        $qb = $this->createQueryBuilder('r');

        $qb->select('r, customers, feedback')
            ->leftJoin('r.customer' ,'customers')
            ->leftJoin('r.feedback', 'feedback')
            ->where('r.emailLog = :emailLog')
            ->orderBy('customers.firstname')
            ->addOrderBy('customers.lastname')
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

        $qb->select('r, customers')
            ->leftJoin('r.customer' ,'customers')
            ->where('r.emailLog = :emailLog AND r.' . $field . '= :value')
            ->orderBy('customers.firstname')
            ->addOrderBy('customers.lastname')
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
