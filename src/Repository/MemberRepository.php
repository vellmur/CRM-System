<?php

namespace App\Repository;

use App\Entity\Building\Building;
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
     * @param Building $building
     * @param $emails
     * @return array
     */
    public function findEmailsMatch(Building $building, $emails)
    {
        $qb = $this->createQueryBuilder('m');

        $qb
            ->select('m.email')
            ->where('m.email IN (:emails) AND m.building = :building')
            ->setParameter('building', $building)
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
     * @param $building
     * @param $search
     * @return \Doctrine\ORM\Query
     */
    public function searchByAll($building, $search)
    {
        $qb = $this->createQueryBuilder('c')
                ->select('c')
                ->innerJoin('c.apartment', 'apartment')
                ->where('c.building = :building')
                ->orderBy('apartment.number, c.firstname, c.lastname')
                ->setParameter('building', $building);

        if (strlen($search) > 0) {
            $search = trim(str_replace('-', '', str_replace('+', '', $search)));

            $qb->andWhere("(CONCAT(c.firstname,' ', c.lastname) LIKE :search) OR c.email LIKE :search 
                OR c.phone LIKE :search OR apartment.number LIKE :search")
                ->setParameter(':search', '%'. $search . '%');
        }

        return $qb->getQuery();
    }

    /**
     * @param $building
     * @param $search
     * @return \Doctrine\ORM\Query
     */
    public function searchByLeads($building, $search)
    {
        $qb = $this->createQueryBuilder('m')
                ->andWhere('m.building = :building AND m.isLead = 1')
                ->orderBy('m.firstname, m.lastname')
                ->setParameter('building', $building);

        if (strlen($search) > 0) {
            $qb->andWhere("(CONCAT(m.firstname, ' ',m.lastname) LIKE '%$search%') OR m.email LIKE '%$search%'");
        }

        return $qb->getQuery();
    }

    /**
     * @param $building
     * @param $search
     * @return \Doctrine\ORM\Query
     */
    public function searchByContacts($building, $search)
    {
        $qb = $this->createQueryBuilder('m')
            ->where('m.building = :building AND m.isLead = 0')
            ->orderBy('m.firstname, m.lastname')
            ->setParameter('building', $building);

        if (strlen($search) > 0) {
            $qb->andWhere("(CONCAT(m.firstname, ' ',m.lastname) LIKE '%$search%') OR m.email LIKE '%$search%'");
        }

        return $qb->getQuery();
    }


    /**
     * @param Building $building
     * @param $deliveryTypeId
     * @return mixed
     * @throws \Exception
     */
    public function getLeadsAndContacts(Building $building, $deliveryTypeId)
    {
        $date = new \DateTime("midnight");
        $date->modify('+2 days');

        $qb = $this->createQueryBuilder('m')
            ->innerJoin('m.notifications', 'notifications')
            ->where('m.building = :building')
            ->andWhere('m.email IS NOT NULL')
            ->andWhere('notifications.notifyType = :deliveryType AND notifications.isActive = 1')
            ->setParameter('deliveryType', $deliveryTypeId)
            ->setParameter('building', $building);

        return $qb->getQuery()->getResult();
    }

    /**
     * @param $building
     * @param $search
     * @return \Doctrine\ORM\Query
     */
    public function searchByMembers($building, $search)
    {
        $qb = $this->createQueryBuilder('m')
            ->select('m')
            ->where('m.building = :building AND m.isLead = 0')
            ->orderBy('m.firstname, m.lastname')
            ->setParameter('building', $building);

        if (strlen($search) > 0) {
            $query = "(CONCAT(m.firstname, ' ',m.lastname) LIKE '%$search%') OR m.email LIKE '%$search%'";

            $qb->andWhere($query);
        }

        return $qb->getQuery();
    }

    /**
     * @param $building
     * @param $search
     * @return \Doctrine\ORM\Query
     */
    public function searchByPatrons($building, $search)
    {
        $qb = $this->createQueryBuilder('m')
            ->select('m, orders')
            ->where('m.building = :building AND m.isLead = 0')
            ->orWhere('m.building = :building AND m.isLead = 0 AND orders.customer IS NOT NULL')
            ->leftJoin('m.orders', 'orders')
            ->orderBy('m.firstname, m.lastname')
            ->setParameter('building', $building);

        if (strlen($search) > 0) {
            $query = "(CONCAT(m.firstname, ' ',m.lastname) LIKE '%$search%') OR m.email LIKE '%$search%'";
            $qb->andWhere($query);
        }

        return $qb->getQuery();
    }

    /**
     * @param $building
     * @param $search
     * @return \Doctrine\ORM\Query
     */
    public function searchByCustomers($building, $search)
    {
        $qb = $this->createQueryBuilder('m')
            ->select('m')
            ->where('m.building = :building')
            ->orderBy('m.firstname, m.lastname')
            ->setParameter('building', $building);

        if (strlen($search) > 0) {
            $query = "(CONCAT(m.firstname, ' ',m.lastname) LIKE '%$search%') OR m.email LIKE '%$search%'";
            $qb->andWhere($query);
        }

        return $qb->getQuery();
    }
}



