<?php

namespace App\Repository;

use App\Entity\Building\Affiliate;
use App\Entity\Building\Referral;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class ReferralRepository extends ServiceEntityRepository
{
    /**
     * ReferralRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Referral::class);
    }

    /**
     * @param Affiliate $affiliate
     * @return array
     */
    public function getAllReferrals(Affiliate $affiliate)
    {
        $qb = $this->createQueryBuilder('r')
            ->innerJoin('r.building', 'building')
            ->leftJoin('building.subscriptions', 'subscription')
            ->leftJoin('subscription.transaction', 'transaction')
            ->select('r as referral, transaction.amount, transaction.createdAt')
            ->where('r.affiliate = :affiliate')
            ->setParameter('affiliate', $affiliate);

        return $qb->getQuery()->getResult();
    }

    /**
     * @param Affiliate $affiliate
     * @return mixed
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function countUnpaidReferrals(Affiliate $affiliate)
    {
        $qb = $this->createQueryBuilder('r')
            ->innerJoin('r.building', 'building')
            ->innerJoin('building.subscriptions', 'subscription')
            ->select('COUNT(DISTINCT(r.id)) as num')
            ->where('r.affiliate = :affiliate')
            ->andWhere('r.isPaid = 0')
            ->setParameter('affiliate', $affiliate);

        return $qb->getQuery()->getSingleResult();
    }

    /**
     * @param Affiliate $affiliate
     * @return array
     */
    public function getUnpaidReferrals(Affiliate $affiliate)
    {
        $qb = $this->createQueryBuilder('r')
            ->innerJoin('r.building', 'building')
            ->innerJoin('building.subscriptions', 'subscriptions')
            ->innerJoin('subscription.transaction', 'transaction')
            ->select('r as referral, payment.amount, transaction.createdAt')
            ->where('r.affiliate = :affiliate')
            ->andWhere('r.isPaid = 0')
            ->setParameter('affiliate', $affiliate);

        return $qb->getQuery()->getResult();
    }
}
