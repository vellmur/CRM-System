<?php

namespace App\Repository;

use App\Entity\Client\Client;
use App\Entity\Customer\Location;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class LocationRepository extends ServiceEntityRepository
{
    /**
     * MemberLocationRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Location::class);
    }

    /**
     * @param Client $client
     * @param $sharesEnabled
     * @return mixed
     */
    public function getLocations(Client $client, $sharesEnabled)
    {
        $qb = $this->createQueryBuilder('l');

        $qb->where('l.client = :client')
            ->setParameter('client', $client)
            ->orderBy('l.type', 'ASC')
            ->addOrderBy('l.name');

        // If shares functionality not enabled, show just first location - delivery
        if (!$sharesEnabled) {
            $qb->setMaxResults(1);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * @param Client $client
     * @return array
     */
    public function getLocationsWorkdays(Client $client)
    {
        $qb = $this->createQueryBuilder('l');

        $qb->innerJoin('l.workdays', 'workdays')
            ->where('l.client = :client')
            ->andWhere('l.isActive = true')
            ->andWhere('workdays.isActive = true')
            ->setParameter('client', $client)
            ->orderBy('l.name');

        return $qb->getQuery()->getResult();
    }

    /**
     * @param $client
     * @param $day
     * @return array
     */
    public function getLocationsByDay($client, $day)
    {
        $qb = $this->createQueryBuilder('l');

        $qb
            ->innerJoin('l.workdays', 'workdays')
            ->where('l.client = :client')
            ->andWhere('workdays.weekday = :day')
            ->andWhere('workdays.isActive = 1')
            ->setParameter('client', $client)
            ->setParameter('day', $day)
            ->orderBy('l.name');

        return $qb->getQuery()->getResult();
    }
}
