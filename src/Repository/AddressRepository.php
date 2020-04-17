<?php

namespace App\Repository;

use App\Entity\Customer\Address;
use App\Entity\Customer\Customer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class AddressRepository extends ServiceEntityRepository
{
    /**
     * AddressRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Address::class);
    }

    /**
     * @param Customer $member
     * @return array
     */
    public function getDatabaseAddresses(Customer $member)
    {
        $qb = $this->createQueryBuilder('a')
            ->select('a')
            ->innerJoin('a.customer', 'm')
            ->where('m = :m')
            ->setParameter('m', $member);

        return $qb->getQuery()->getArrayResult();
    }

}