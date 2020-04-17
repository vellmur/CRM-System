<?php

namespace App\Repository;

use App\Entity\Customer\Payment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class PaymentRepository extends ServiceEntityRepository
{
    /**
     * PaymentRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Payment::class);
    }

    /**
     * @param $client
     * @return mixed
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getLastPaid($client)
    {
        $qb = $this->createQueryBuilder('p');

        $qb->select('method.id as methodId, method.name as methodName, method.price as methodPrice')
            ->leftJoin('p.transaction', 'transaction')
            ->leftJoin('transaction.method', 'method')
            ->where('p.client = :client')
            ->orderBy('p.id', 'DESC')
            ->setParameter('client', $client)
            ->setMaxResults(1);

        $result = $qb->getQuery()->getResult();

        if ($result) {
            return $qb->getQuery()->getSingleResult();
        } else {
            return false;
        }

    }
}
