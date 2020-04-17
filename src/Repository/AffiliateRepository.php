<?php

namespace App\Repository;

use App\Entity\Client\Affiliate;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class AffiliateRepository extends ServiceEntityRepository
{
    /**
     * AffiliateRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Affiliate::class);
    }

    /**
     * @return mixed
     */
    public function findAffiliatesInfo()
    {
        $qb = $this->createQueryBuilder('affiliate')
            ->innerJoin('affiliate.referrals', 'referrals')
            ->select('affiliate, COUNT(DISTINCT(referrals.id)) as referralsNum');

        return $qb->getQuery()->getSingleResult();
    }

    public function findAllAffiliates()
    {
        $qb = $this->createQueryBuilder('a')
            ->select('a, c, team, user, accesses, referrals')
            ->innerJoin('a.client', 'c')
            ->innerJoin('c.team', 'team')
            ->innerJoin('team.user', 'user')
            ->innerJoin('c.accesses', 'accesses')
            ->leftJoin('a.referrals', 'referrals');

        return $qb->getQuery()->getResult();
    }
}
