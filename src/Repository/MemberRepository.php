<?php

namespace App\Repository;

use App\Entity\Client\Client;
use App\Entity\Customer\Customer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class MemberRepository extends ServiceEntityRepository
{
    /**
     * MemberRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Customer::class);
    }

    /**
     * @param Client $client
     * @param $emails
     * @return array
     */
    public function findEmailsMatch(Client $client, $emails)
    {
        $qb = $this->createQueryBuilder('m');

        $qb
            ->select('m.email')
            ->where('m.email IN (:emails) AND m.client = :client')
            ->setParameter('client', $client)
            ->setParameter('emails', $emails, \Doctrine\DBAL\Connection::PARAM_STR_ARRAY);

        $result = $qb->getQuery()->getScalarResult();

        return array_map('current', $result);
    }

    /**
     * @param $token
     * @return mixed
     */
    public function findByToken($token)
    {
        $qb = $this->createQueryBuilder('m')
            ->where('m.token = :token')
            ->leftJoin('m.addresses', 'addresses')
            ->orderBy('addresses.type', 'asc')
            ->setParameter('token', $token);

        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * @param $client
     * @param $search
     * @return \Doctrine\ORM\Query
     */
    public function searchByAll($client, $search)
    {
        $qb = $this->createQueryBuilder('m')
                ->select('m, shares, share, location')
                ->where('m.client = :client')
                ->leftJoin('m.shares', 'shares')
                ->leftJoin('shares.share', 'share')
                ->leftJoin('shares.location' ,'location')
                ->orderBy('m.firstname, m.lastname')
                ->setParameter('client', $client);

        if (strlen($search) > 0) {
            $query = "(CONCAT(m.firstname, ' ',m.lastname) LIKE '%$search%') OR m.email LIKE '%$search%' 
                OR share.name LIKE '%$search%' OR location.name LIKE '%$search%'";

            $shareDay = $this->getShareDay($search);
            if ($shareDay) $query .= ' OR shares.pickupDay=' . $shareDay;

            $status = $this->getStatus($search);
            if ($status) $query .= ' OR shares.status = ' . $status;

            $qb->andWhere($query);
        }

        return $qb->getQuery();
    }

    /**
     *
     * Lead - it is a customer that never had any shares in a system.
     *
     * If lead had any share once, he becomes a customer or contact
     *
     * @param $client
     * @param $search
     * @return \Doctrine\ORM\Query
     */
    public function searchByLeads($client, $search)
    {
        $qb = $this->createQueryBuilder('m')
                ->andWhere('m.client = :client AND m.isLead = 1')
                ->orderBy('m.firstname, m.lastname')
                ->setParameter('client', $client);

        if (strlen($search) > 0) {
            $qb->andWhere("(CONCAT(m.firstname, ' ',m.lastname) LIKE '%$search%') OR m.email LIKE '%$search%'");
        }

        return $qb->getQuery();
    }

    /**
     * @param $client
     * @param $search
     * @return \Doctrine\ORM\Query
     */
    public function searchByContacts($client, $search)
    {
        $qb = $this->createQueryBuilder('m')
            ->where('m.client = :client AND m.isLead = 0 AND shares.id IS NULL')
            ->leftJoin('m.shares', 'shares')
            ->orderBy('m.firstname, m.lastname')
            ->setParameter('client', $client);

        if (strlen($search) > 0) {
            $qb->andWhere("(CONCAT(m.firstname, ' ',m.lastname) LIKE '%$search%') OR m.email LIKE '%$search%'");
        }

        return $qb->getQuery();
    }


    /**
     * Get Leads and Contacts for send delivery day notification
     * Customers that have a delivery day equal to day after tomorrow (today + 2 days)
     *
     * @param Client $client
     * @param $deliveryTypeId
     * @return array
     */
    public function getLeadsAndContacts(Client $client, $deliveryTypeId)
    {
        $date = new \DateTime("midnight");
        $date->modify('+2 days');

        $deliveryDay = $date->format('N');

        $qb = $this->createQueryBuilder('m')
            ->leftJoin('m.shares', 'shares')
            ->innerJoin('m.notifications', 'notifications')
            ->where('m.client = :client')
            ->andWhere('m.email IS NOT NULL')
            ->andWhere('shares.id IS NULL')
            ->andWhere('m.deliveryDay = :deliveryDay')
            ->andWhere('notifications.notifyType = :deliveryType AND notifications.isActive = 1')
            ->setParameter('deliveryType', $deliveryTypeId)
            ->setParameter('client', $client)
            ->setParameter('deliveryDay', $deliveryDay);

        return $qb->getQuery()->getResult();
    }

    /**
     * Member - it is a customer who have at least one share with any status but with for MEMBER type
     *
     * @param $client
     * @param $search
     * @return \Doctrine\ORM\Query
     */
    public function searchByMembers($client, $search)
    {
        $qb = $this->createQueryBuilder('m')
            ->select('m, shares, share, location')
            ->where('m.client = :client AND m.isLead = 0')
            ->andWhere('shares.type = 1')
            ->innerJoin('m.shares', 'shares')
            ->innerJoin('shares.share', 'share')
            ->leftJoin('shares.location' ,'location')
            ->orderBy('m.firstname, m.lastname')
            ->setParameter('client', $client);

        if (strlen($search) > 0) {
            $query = "(CONCAT(m.firstname, ' ',m.lastname) LIKE '%$search%') OR m.email LIKE '%$search%' 
                OR share.name LIKE '%$search%' OR location.name LIKE '%$search%'";

            $shareDay = $this->getShareDay($search);
            if ($shareDay) $query .= ' OR shares.pickupDay = ' . $shareDay;

            $status = $this->getStatus($search);
            if ($status) $query .= ' OR shares.status = ' . $status;

            $qb->andWhere($query);
        }

        return $qb->getQuery();
    }

    /**
     * Patron - it is a customer who have at least one share with a "PATRONS" type
     *
     * @param $client
     * @param $search
     * @return \Doctrine\ORM\Query
     */
    public function searchByPatrons($client, $search)
    {
        $qb = $this->createQueryBuilder('m')
            ->select('m, shares, share, location, orders')
            ->where('m.client = :client AND m.isLead = 0 AND shares.customer IS NOT NULL AND shares.type = 2')
            ->orWhere('m.client = :client AND m.isLead = 0 AND orders.customer IS NOT NULL')
            ->leftJoin('m.shares', 'shares')
            ->leftJoin('shares.share', 'share')
            ->leftJoin('m.orders', 'orders')
            ->leftJoin('shares.location' ,'location')
            ->orderBy('m.firstname, m.lastname')
            ->setParameter('client', $client);

        if (strlen($search) > 0) {
            $query = "(CONCAT(m.firstname, ' ',m.lastname) LIKE '%$search%') OR m.email LIKE '%$search%' 
                OR share.name LIKE '%$search%' OR location.name LIKE '%$search%'";

            $shareDay = $this->getShareDay($search);
            if ($shareDay) $query .= ' OR shares.pickupDay = ' . $shareDay;

            $status = $this->getStatus($search);
            if ($status) $query .= ' OR shares.status = ' . $status;

            $qb->andWhere($query);
        }

        return $qb->getQuery();
    }

    /**
     * @param $status
     * @return bool
     */
    private function getStatus($status)
    {
        $statuses = [
            1 => 'PENDING',
            2 => 'ACTIVE',
            3 => 'LAPSED'
        ];

        $found = $this->searchForMatch($status, $statuses);

        return $found;
    }

    /**
     * @param $day
     * @return bool
     */
    private function getShareDay($day)
    {
        $week = [
            1 => 'MONDAY',
            2 => 'TUESDAY',
            3 => 'WEDNESDAY',
            4 => 'THURSDAY',
            5 => 'FRIDAY',
            6 => 'SATURDAY',
            7 => 'SUNDAY'
        ];

        $found = $this->searchForMatch($day, $week);

        return $found;
    }

    /**
     *
     * Search for matching value in array
     * Array must have this view -> [$key => $string value]
     *
     * @param $search
     * @param $array
     * @return bool
     */
    public function searchForMatch($search, $array)
    {
        $found = false;

        foreach ($array as $item) {
            if (strpos($item,mb_strtoupper($search)) !== false) {
                $found = array_flip($array)[$item];
            }
        }

        return $found;
    }

    /**
     * @param $client
     * @param $search
     * @return \Doctrine\ORM\Query
     */
    public function searchByCustomers($client, $search)
    {
        $qb = $this->createQueryBuilder('m')
            ->select('m')
            ->where('m.client = :client')
            ->orderBy('m.firstname, m.lastname')
            ->setParameter('client', $client);

        if (strlen($search) > 0) {
            $query = "(CONCAT(m.firstname, ' ',m.lastname) LIKE '%$search%') OR m.email LIKE '%$search%'";
            $qb->andWhere($query);
        }

        return $qb->getQuery();
    }
}



