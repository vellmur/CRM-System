<?php

namespace App\Repository;

use App\Entity\User\Device;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class DeviceRepository extends ServiceEntityRepository
{
    /**
     * DeviceRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Device::class);
    }

    /**
     * @param $ip
     * @param $os
     * @param $browser
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findDevice($ip, $os, $browser)
    {
        $qb = $this->createQueryBuilder('d')
            ->select('d')
            ->where('d.ip = :ip')
            ->andWhere('d.os = :os')
            ->andWhere('d.browser = :browser')
            ->orderBy('d.createdAt', 'DESC')
            ->setParameter('ip', $ip)
            ->setParameter('os', $os)
            ->setParameter('browser', $browser)
            ->setMaxResults(1);

        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * @param $deviceId
     * @return bool
     */
    public function isDeviceExistsInDb($deviceId)
    {
        $qb = $this->createQueryBuilder('d')
            ->select('d.id')
            ->where('d.id = :deviceId')
            ->setParameter('deviceId', $deviceId);

        return $qb->getQuery()->getResult() ? true : false;
    }
}
