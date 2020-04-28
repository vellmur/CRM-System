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

    /**
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
            ->where('m.client = :client AND m.isLead = 0')
            ->orderBy('m.firstname, m.lastname')
            ->setParameter('client', $client);

        if (strlen($search) > 0) {
            $qb->andWhere("(CONCAT(m.firstname, ' ',m.lastname) LIKE '%$search%') OR m.email LIKE '%$search%'");
        }

        return $qb->getQuery();
    }


    /**
     * @param Client $client
     * @param $deliveryTypeId
     * @return mixed
     * @throws \Exception
     */
    public function getLeadsAndContacts(Client $client, $deliveryTypeId)
    {
        $date = new \DateTime("midnight");
        $date->modify('+2 days');

        $qb = $this->createQueryBuilder('m')
            ->innerJoin('m.notifications', 'notifications')
            ->where('m.client = :client')
            ->andWhere('m.email IS NOT NULL')
            ->andWhere('notifications.notifyType = :deliveryType AND notifications.isActive = 1')
            ->setParameter('deliveryType', $deliveryTypeId)
            ->setParameter('client', $client);

        return $qb->getQuery()->getResult();
    }

    /**
     * @param $client
     * @param $search
     * @return \Doctrine\ORM\Query
     */
    public function searchByMembers($client, $search)
    {
        $qb = $this->createQueryBuilder('m')
            ->select('m')
            ->where('m.client = :client AND m.isLead = 0')
            ->orderBy('m.firstname, m.lastname')
            ->setParameter('client', $client);

        if (strlen($search) > 0) {
            $query = "(CONCAT(m.firstname, ' ',m.lastname) LIKE '%$search%') OR m.email LIKE '%$search%'";

            $qb->andWhere($query);
        }

        return $qb->getQuery();
    }

    /**
     * @param $client
     * @param $search
     * @return \Doctrine\ORM\Query
     */
    public function searchByPatrons($client, $search)
    {
        $qb = $this->createQueryBuilder('m')
            ->select('m, orders')
            ->where('m.client = :client AND m.isLead = 0')
            ->orWhere('m.client = :client AND m.isLead = 0 AND orders.customer IS NOT NULL')
            ->leftJoin('m.orders', 'orders')
            ->orderBy('m.firstname, m.lastname')
            ->setParameter('client', $client);

        if (strlen($search) > 0) {
            $query = "(CONCAT(m.firstname, ' ',m.lastname) LIKE '%$search%') OR m.email LIKE '%$search%'";
            $qb->andWhere($query);
        }

        return $qb->getQuery();
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



