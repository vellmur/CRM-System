<?php

namespace App\Repository;

use App\Entity\Customer\Vendor;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class VendorRepository extends ServiceEntityRepository
{
    /**
     * VendorRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Vendor::class);
    }

    /**
     * @param $token
     * @return mixed
     */
    public function findByToken($token)
    {
        $qb = $this->createQueryBuilder('v')
            ->where('v.token = :token')
            ->setParameter('token', $token);

        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * @param $building
     * @param $search
     * @return array
     */
    public function searchByAllFields($building, $search)
    {
        $qb = $this->createQueryBuilder('v')
            ->where('v.building = :building')
            ->andWhere("v.name LIKE '%$search%'")
            ->addOrderBy('v.name')
            ->setParameter('building', $building);

        return $qb->getQuery()->getResult();
    }

    /**
     * @param $building
     * @return array
     */
    public function getActiveVendors($building)
    {
        $qb = $this->createQueryBuilder('v')
            ->where('v.building = :building')
            ->andWhere('v.isActive = 1')
            ->addOrderBy('v.name')
            ->setParameter('building', $building);

        return $qb->getQuery()->getResult();
    }
}
