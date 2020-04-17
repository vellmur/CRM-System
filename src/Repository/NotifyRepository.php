<?php

namespace App\Repository;

use App\Entity\Client\Notification\NotifiableNotification;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class NotifyRepository extends ServiceEntityRepository
{
    /**
     * NoteRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, NotifiableNotification::class);
    }

    /**
     * @param object $user
     * @param $module
     * @return mixed
     */
    public function getUserNotifications($user, $module)
    {
        $qb = $this->createQueryBuilder('n');

        $notes = $qb
            ->select('n, notification')
            ->innerJoin('n.notification', 'notification')
            ->where('n.user = :user')
            ->andWhere('notification.module = :module')
            ->andWhere('n.seen = 0')
            ->setParameter('user', $user)
            ->setParameter('module', $module)
            ->orderBy('notification.createdAt')
            ->setMaxResults(10);

        return $notes->getQuery()->getResult();
    }
}
