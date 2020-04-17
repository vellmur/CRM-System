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
                ->select('c, team, user, accesses, affiliate')
                ->innerJoin('c.team', 'team')
                ->innerJoin('team.user', 'user')
                ->innerJoin('c.accesses', 'accesses')
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
                ->select('c, team, user, devices, pageViews, affiliate')
                ->innerJoin('c.team', 'team')
                ->innerJoin('team.user', 'user')
                ->innerJoin('user.devices', 'devices')
                ->innerJoin('devices.pageViews', 'pageViews')
                ->leftJoin('c.affiliate', 'affiliate')
                ->orderBy('c.createdAt', 'DESC');

        return $qb->getQuery()->getResult();
    }

    /**
     * @param $user
     * @return mixed
     */
    public function getClientByOwner($user)
    {
        $qb =
            $this->createQueryBuilder('c')
                ->where('c.user = :client')
                ->setParameter('client', $user);

        return $qb->getQuery()->getSingleResult();
    }

    /**
     * @return array
     */
    public function countLevelClients()
    {
        $qb = $this->createQueryBuilder('c')
            ->select('COUNT(DISTINCT(c.id)) as num, c.level')
            ->groupBy('c.level');

        return $qb->getQuery()->getResult();
    }

    /**
     * @return array
     */
    public function countLevelClientsByStatus()
    {
        $qb = $this->createQueryBuilder('c')
            ->leftJoin('c.accesses', 'access')
            ->select('COUNT(DISTINCT(c.id)) as statusNum, c.level, access.status as statusId')
            ->groupBy('c.level, statusId');

        return $qb->getQuery()->getResult();
    }

    /**
     * @param $days
     * @return array
     */
    public function countNewClients($days)
    {
        $now = new \DateTime();

        $qb = $this->createQueryBuilder('c')
            ->select('COUNT(DISTINCT(c.id)) as num, c.level')
            ->andWhere('DATE_DIFF(:now, c.createdAt) <= :daysNum')
            ->groupBy('c.level')
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

        $qb->select('c, team, user, accesses, affiliate, site, textEditor, crops')
            ->where($expr->like($expr->lower('c.name'),':search'))
            ->orWhere($expr->like($expr->lower('c.email'), ':search'))
            ->orWhere($expr->like($expr->lower('user.username'),':search'))
            ->orWhere($expr->like($expr->lower('user.email'), ':search'))
            ->innerJoin('c.team', 'team')
            ->innerJoin('team.user', 'user')
            ->innerJoin('c.accesses', 'accesses')
            ->leftJoin('c.affiliate', 'affiliate')
            ->leftJoin('c.textEditor', 'textEditor')
            ->leftJoin('c.site', 'site')
            ->leftJoin('c.crops', 'crops')
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

        $qb->select('c, team, user, accesses, affiliate, site, textEditor, crops')
            ->innerJoin('c.team', 'team')
            ->innerJoin('team.user', 'user')
            ->innerJoin('c.accesses', 'accesses')
            ->leftJoin('c.affiliate', 'affiliate')
            ->leftJoin('c.textEditor', 'textEditor')
            ->leftJoin('c.site', 'site')
            ->leftJoin('c.crops', 'crops')
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

        $qb
            ->select($qb->expr()->countDistinct('c.id'))
            ->innerJoin('c.team', 'team')
            ->innerJoin('team.user', 'user')
            ->where('user.isActive = :isActive')
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
            ->select('c, team, user, accesses, affiliate, site, textEditor, crops')
            ->innerJoin('c.team', 'team')
            ->innerJoin('team.user', 'user')
            ->innerJoin('c.accesses', 'accesses')
            ->leftJoin('c.affiliate', 'affiliate')
            ->leftJoin('c.textEditor', 'textEditor')
            ->leftJoin('c.site', 'site')
            ->leftJoin('c.crops', 'crops')
            ->where('user.enabled = :isEnabled')
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
     */
    public function getNewClientsByDays($days)
    {
        $now = new \DateTime();

        $qb = $this->createQueryBuilder('c')
            ->select('c, team, user, accesses, affiliate, site, textEditor, crops')
            ->innerJoin('c.team', 'team')
            ->innerJoin('team.user', 'user')
            ->innerJoin('c.accesses', 'accesses')
            ->leftJoin('c.affiliate', 'affiliate')
            ->leftJoin('c.textEditor', 'textEditor')
            ->leftJoin('c.site', 'site')
            ->leftJoin('c.crops', 'crops')
            ->where('DATE_DIFF(:now, c.createdAt) <= :daysNum')
            ->orderBy('c.createdAt', 'DESC')
            ->setParameter('now', $now)
            ->setParameter('daysNum', $days);

        return $qb->getQuery()->getResult();
    }

    /**
     * @return mixed
     */
    public function getNewConfirmedClients()
    {
        $now = new \DateTime();

        $qb = $this->createQueryBuilder('c')
            ->select('c, team, user')
            ->innerJoin('c.team', 'team')
            ->innerJoin('team.user', 'user')
            ->where('c.createdAt = :now and user.enabled = 1')
            ->setParameter('now', $now->format('Y-m-d'));

        return $qb->getQuery()->getResult();
    }

    /**
     * @return mixed
     */
    public function getPendingClients()
    {
        $now = new \DateTime();
        $now->modify('-1 day');

        $qb = $this->createQueryBuilder('c')
            ->select('c, team, user')
            ->innerJoin('c.team', 'team')
            ->innerJoin('team.user', 'user')
            ->where('c.createdAt = :now and user.enabled = 0')
            ->setParameter('now', $now->format('Y-m-d'));

        return $qb->getQuery()->getResult();
    }

    /**
     * @return mixed
     */
    public function getSetupAbortedClients()
    {
        $now = new \DateTime();
        $now->modify('-5 days');

        $qb = $this->createQueryBuilder('c')
            ->select('c, team, user')
            ->innerJoin('c.team', 'team')
            ->innerJoin('team.user', 'user')
            ->where('c.createdAt = :now AND user.enabled = 1 AND (c.plants is empty or c.gardens is empty or c.crops is empty)')
            ->setParameter('now', $now->format('Y-m-d'));

        return $qb->getQuery()->getResult();
    }

    /**
     * Patron - it is a customer who had at least one share with a "PATRONS" type or one pos order with past dates
     *
     * @return mixed
     */
    public function getPOSPatrons()
    {
        $today = new \DateTime('midnight');

        $qb = $this->createQueryBuilder('c')
            ->select('c, customers')
            ->leftJoin('c.customers', 'customers')
            ->leftJoin('customers.shares', 'shares')
            ->leftJoin('customers.orders', 'orders')
            ->where('customers.isLead = 0')
            ->andWhere('(shares.id IS NOT NULL AND shares.renewalDate < :now) OR (orders.id IS NOT NULL AND orders.createdAt < :now)')
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

