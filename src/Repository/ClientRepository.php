<?php

namespace App\Repository;

use App\Entity\Client\Client;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * ClientRepository
 *
 */
class ClientRepository extends ServiceEntityRepository
{
    /**
     * ClientRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Client::class);
    }

    /**
     * @param null $search
     * @return mixed
     */
    public function getSoftwareClients($search = null)
    {
        $qb =
            $this->createQueryBuilder('c')
                ->select('c, users, accesses, affiliate')
                ->innerJoin('c.accesses', 'accesses')
                ->leftJoin('c.users', 'users')
                ->leftJoin('c.affiliate', 'affiliate')
                ->orderBy('c.createdAt', 'DESC');

        if ($search) {
            $qb->andWhere("c.name LIKE '%$search%'");
        }

        return $qb->getQuery()->getResult();
    }


    /**
     * @return mixed
     */
    public function getActiveClients()
    {
        $qb =
            $this->createQueryBuilder('c')
                ->select('c, users, devices, pageViews, affiliate')
                ->leftJoin('c.affiliate', 'affiliate')
                ->leftJoin('c.users', 'users')
                ->innerJoin('users.devices', 'devices')
                ->innerJoin('devices.pageViews', 'pageViews')
                ->orderBy('c.createdAt', 'DESC');

        return $qb->getQuery()->getResult();
    }

    /**
     * @return array
     */
    public function countLevelClients()
    {
        $qb = $this->createQueryBuilder('c')
            ->select('COUNT(DISTINCT(c.id)) as num');

        return $qb->getQuery()->getResult();
    }

    /**
     * @return array
     */
    public function countLevelClientsByStatus()
    {
        $qb = $this->createQueryBuilder('c')
            ->leftJoin('c.accesses', 'access')
            ->select('COUNT(DISTINCT(c.id)) as statusNum, access.status as statusId')
            ->groupBy('statusId');

        return $qb->getQuery()->getResult();
    }

    /**
     * @param $days
     * @return mixed
     * @throws \Exception
     */
    public function countNewClients($days)
    {
        $now = new \DateTime();

        $qb = $this->createQueryBuilder('c')
            ->select('COUNT(DISTINCT(c.id)) as num')
            ->andWhere('DATE_DIFF(:now, c.createdAt) <= :daysNum')
            ->setParameter('now', $now)
            ->setParameter('daysNum', $days);

        return $qb->getQuery()->getResult();
    }

    /**
     * @param $search
     * @return array
     */
    public function searchClientsByAllFields($search)
    {
        $search = mb_strtolower($search);
        $qb = $this->createQueryBuilder('c');

        $expr = $qb->expr();

        $qb->select('c, users, accesses, affiliate')
            ->where($expr->like($expr->lower('c.name'),':search'))
            ->orWhere($expr->like($expr->lower('c.email'), ':search'))
            ->orWhere($expr->like($expr->lower('users.username'),':search'))
            ->orWhere($expr->like($expr->lower('users.email'), ':search'))
            ->innerJoin('c.accesses', 'accesses')
            ->leftJoin('c.users', 'users')
            ->leftJoin('c.affiliate', 'affiliate')
            ->setParameter('search', "%$search%")
            ->orderBy('c.createdAt', 'DESC');

        return $qb->getQuery()->getResult();
    }


    /**
     * @return array
     */
    public function getClientsByModulesStatuses()
    {
        $qb = $this->createQueryBuilder('c');

        $qb->select('
                COUNT(0) as total,
                SUM(case when accesses.status = 1 then 1 else 0 end) as pending,
                SUM(case when accesses.status = 2 then 1 else 0 end) as active
                '
            )
            ->innerJoin('c.accesses', 'accesses')
            ->groupBy('c.id');

        return $qb->getQuery()->getResult();
    }

    /**
     * @param $days
     * @return array
     */
    public function countNewClientsByDays($days)
    {
        $now = new \DateTime();

        $qb = $this->createQueryBuilder('c')
            ->select('COUNT(DISTINCT(c.id))')
            ->andWhere('DATE_DIFF(:now, c.createdAt) <= :daysNum')
            ->setParameter('now', $now)
            ->setParameter('daysNum', $days);

        return $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * @param $statusId
     * @return array
     */
    public function getClientsByStatus($statusId)
    {
        $qb = $this->createQueryBuilder('c');

        $qb->select('c, users, accesses, affiliate')
            ->innerJoin('c.accesses', 'accesses')
            ->leftJoin('c.users', 'users')
            ->leftJoin('c.affiliate', 'affiliate')
            ->where('accesses.status = :status')
            ->setParameter('status', $statusId);

        return $qb->getQuery()->getResult();
    }

    /**
     * @param $isConfirmed
     * @return mixed
     */
    public function countClientsByActivation($isConfirmed)
    {
        $qb = $this->createQueryBuilder('c');

        $qb->select($qb->expr()->countDistinct('c.id'))
            ->leftJoin('c.users', 'users')
            ->where('users.isActive = :isActive')
            ->setParameter('isActive', $isConfirmed);

        return $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * @param $isConfirmed
     * @param $text
     * @return mixed
     */
    public function getClientsByActivation($isConfirmed, $text)
    {
        $qb = $this->createQueryBuilder('c');

        $qb
            ->select('c, users, accesses, affiliate')
            ->innerJoin('c.users', 'users')
            ->innerJoin('c.accesses', 'accesses')
            ->leftJoin('c.affiliate', 'affiliate')
            ->where('users.enabled = :isEnabled')
            ->orderBy('c.createdAt', 'DESC')
            ->setParameter('isEnabled', $isConfirmed)
            ->distinct();

        if ($text) {
            $qb->andWhere("c.name LIKE '%$text%'");
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * @param $days
     * @return mixed
     * @throws \Exception
     */
    public function getNewClientsByDays($days)
    {
        $now = new \DateTime();

        $qb = $this->createQueryBuilder('c')
            ->select('c, users, accesses, affiliate')
            ->leftJoin('c.users', 'users')
            ->innerJoin('c.accesses', 'accesses')
            ->leftJoin('c.affiliate', 'affiliate')
            ->where('DATE_DIFF(:now, c.createdAt) <= :daysNum')
            ->orderBy('c.createdAt', 'DESC')
            ->setParameter('now', $now)
            ->setParameter('daysNum', $days);

        return $qb->getQuery()->getResult();
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function getPOSPatrons()
    {
        $today = new \DateTime('midnight');

        $qb = $this->createQueryBuilder('c')
            ->select('c, customers')
            ->leftJoin('c.customers', 'customers')
            ->leftJoin('customers.orders', 'orders')
            ->where('customers.isLead = 0')
            ->andWhere('orders.id IS NOT NULL AND orders.createdAt < :now')
            ->setParameter('now', $today->format('Y-m-d'));

        return $qb->getQuery()->getResult();
    }

    public function getImages(Client $client)
    {
        $qb = $this->createQueryBuilder('c')
            ->select('c, g')
            ->innerJoin('c.gallery', 'g')
            ->where('c = :client')
            ->setParameter('client', $client);

        return $qb->getQuery();
    }
}

