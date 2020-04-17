<?php

namespace App\Repository;

use App\Entity\Client\Client;
use App\Entity\Customer\SuspendedWeek;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class SuspendWeekRepository extends ServiceEntityRepository
{
    /**
     * SuspendWeekRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SuspendedWeek::class);
    }

    /**
     * @param Client $client
     * @return array
     */
    public function getSuspendedWeeks(Client $client)
    {
        $now = new \DateTime("midnight");

        $qb = $this->createQueryBuilder('s');

        $qb->select('s.year, s.week')
            ->where('s.client = :client')
            ->andWhere('s.year >= :year')
            ->setParameter('client', $client)
            ->setParameter('year', $now->format('Y'));

        $weeks = $qb->getQuery()->getArrayResult();

        $suspendedWeeks = [];

        foreach ($weeks as $key => $week) {
            $suspendedWeeks[$week['year']][] = $week['week'];
        }

        return $suspendedWeeks;
    }
}
