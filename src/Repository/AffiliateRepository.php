<?php

namespace App\Repository;

use App\Entity\Building\Affiliate;
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
    public function findAllAffiliates()
    {
        $qb = $this->createQueryBuilder('a')
            ->select('a, c, users, accesses, referrals')
            ->innerJoin('a.building', 'c')
            ->innerJoin('c.accesses', 'accesses')
            ->leftJoin('a.referrals', 'referrals')
            ->leftJoin('c.users', 'users');

        return $qb->getQuery()->getResult();
    }
}
