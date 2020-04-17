<?php

namespace App\Repository;

use App\Entity\Client\Client;
use App\Entity\Customer\Workday;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 *  WorkdayRepository
 */
class WorkdayRepository extends ServiceEntityRepository
{
    private $translator;

    /**
     * WorkdayRepository constructor.
     * @param ManagerRegistry $registry
     * @param TranslatorInterface $translator
     */
    public function __construct(ManagerRegistry $registry, TranslatorInterface $translator)
    {
        parent::__construct($registry, Workday::class);

        $this->translator = $translator;
    }

    /**
     * @param Client $client
     * @return mixed
     */
    public function getWorkdays(Client $client)
    {
        $qb = $this->createQueryBuilder('workday')
            ->innerJoin('workday.location', 'location')
            ->where('location.client = :client')
            ->andWhere('workday.isActive = 1')
            ->setParameter('client', $client);

        $results = $qb->getQuery()->getResult();

        $workdays = [];

        foreach ($results as $workday) {
            $weekDayName = $this->translator->trans(mb_strtolower($workday->getWeekdayName()), [], 'choices');
            $workdays[$weekDayName] = $workday->getWeekday();
        }

        return $workdays;
    }

}
