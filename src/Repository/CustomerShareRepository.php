<?php

namespace App\Repository;

use App\Entity\Client\Client;
use App\Entity\Customer\Customer;
use App\Entity\Customer\CustomerShare;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class CustomerShareRepository extends ServiceEntityRepository
{
    /**
     * CustomerShareRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CustomerShare::class);
    }

    /**
     * @param $client
     * @return array
     */
    public function getNotLapsedShares($client)
    {
        $qb = $this->createQueryBuilder('s')
            ->innerJoin('s.share', 'share')
            ->innerJoin('s.customer', 'm')
            ->where('s.type = 1')
            ->andWhere('m.email IS NOT NULL')
            ->andWhere('s.status <> 3')
            ->andWhere('m.client = :client')
            ->orderBy('m.firstname, m.lastname')
            ->setParameter('client', $client);

        return $qb->getQuery()->getResult();
    }

    /**
     * @param $client
     * @param $share
     * @return array
     */
    public function getTotalShares($client, $share = null)
    {
        $qb = $this->createQueryBuilder('s')
            ->select('s, m, share')
            ->leftJoin('s.customer', 'm')
            ->leftJoin('s.share', 'share')
            ->where('m.client = :client')
            ->orderBy('m.firstname, m.lastname')
            ->groupBy('m, s')
            ->setParameter('client', $client);

        if ($share) {
            $qb->andWhere('share = :share')->setParameter('share', $share);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * @param $client
     * @param $status
     * @param $share
     * @return array
     */
    public function getSharesByStatus($client, $status, $share = null)
    {
        $qb = $this->createQueryBuilder('s')
            ->select('s, m, share')
            ->leftJoin('s.customer', 'm')
            ->leftJoin('s.share', 'share')
            ->where('m.client = :client')
            ->andWhere('s.status = :status')
            ->orderBy('m.firstname, m.lastname')
            ->setParameter('client', $client)
            ->setParameter('status', $status);

        if ($share) {
            $qb->andWhere('share = :share')->setParameter('share', $share);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * @param $client
     * @param $share
     * @return array
     */
    public function getRenewalShares($client, $share = null)
    {
        $now = new \DateTime("midnight");

        $qb = $this->createQueryBuilder('s')
            ->select('s, m, share')
            ->leftJoin('s.customer', 'm')
            ->leftJoin('s.share', 'share')
            ->where('m.client = :client')
            ->andWhere('s.renewalDate >= :now')
            ->andWhere('s.status <> 3')
            ->andWhere('DATE_DIFF(s.renewalDate, :now) < 7')
            ->orderBy('m.firstname, m.lastname')
            ->setParameter('client', $client)
            ->setParameter('now', $now);

        if ($share) {
            $qb->andWhere('share = :share')->setParameter('share', $share);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * @param $client
     * @return array
     */
    public function countRenewalMembers($client)
    {
        $now = new \DateTime("midnight");

        $qb = $this->createQueryBuilder('s')
            ->leftJoin('s.customer', 'customer')
            ->leftJoin('s.share', 'share')
            ->select('COUNT(DISTINCT(s.id)) as num, share.name, DATE_DIFF(s.renewalDate, :now) as diff')
            ->where('customer.client = :client')
            ->andWhere('s.renewalDate >= :now')
            ->andWhere('DATE_DIFF(s.renewalDate, :now) < 7')
            ->andWhere('s.status <> 3')
            ->groupBy('share.name, diff')
            ->setParameter('client', $client)
            ->setParameter('now', $now);

        return $qb->getQuery()->getResult();
    }


    /**
     * @param $client
     * @param $share
     * @param $days
     * @return array
     */
    public function getNewMembers($client, $days, $share = null)
    {
        $now = new \DateTime();

        $qb = $this->createQueryBuilder('s')
            ->select('s, m, share')
            ->innerJoin('s.customer', 'm')
            ->innerJoin('s.share', 'share')
            ->where('m.client = :client')
            ->andWhere('DATE_DIFF(:now, m.createdAt) <= :daysNum')
            ->orderBy('m.firstname, m.lastname')
            ->setParameter('now', $now)
            ->setParameter('daysNum', $days)
            ->setParameter('client', $client);

        if ($share) {
            $qb->andWhere('share = :share')->setParameter('share', $share);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * @param $client
     * @return array
     */
    public function countShareMembers($client)
    {
        $qb = $this->createQueryBuilder('s')
            ->leftJoin('s.customer', 'm')
            ->leftJoin('s.share', 'share')
            ->select('COUNT(DISTINCT(s.id)) as num, share.name ')
            ->where('m.client = :client')
            ->groupBy('share.name')
            ->setParameter('client', $client);

        return $qb->getQuery()->getResult();
    }

    /**
     * @param $client
     * @return array
     */
    public function countShareMembersByStatus($client)
    {
        $qb = $this->createQueryBuilder('s')
            ->leftJoin('s.customer', 'm')
            ->leftJoin('s.share', 'share')
            ->select('COUNT(DISTINCT(s.id)) as amount, share.name, s.status as status')
            ->where('m.client = :client')
            ->groupBy('share.name, status')
            ->setParameter('client', $client);

        return $qb->getQuery()->getResult();
    }

    /**
     * @param $client
     * @param $days
     * @return array
     */
    public function countNewMembers($client, $days)
    {
        $now = new \DateTime();

        $qb = $this->createQueryBuilder('s')
            ->leftJoin('s.customer', 'm')
            ->leftJoin('s.share', 'share')
            ->select('COUNT(DISTINCT(s.id)) as num, share.name')
            ->where('m.client = :client')
            ->andWhere('DATE_DIFF(:now, m.createdAt) <= :daysNum')
            ->groupBy('share.name')
            ->setParameter('now', $now)
            ->setParameter('daysNum', $days)
            ->setParameter('client', $client);

        return $qb->getQuery()->getResult();
    }

    /**
     * @param CustomerShare $share
     * @return mixed
     */
    public function getOldShare(CustomerShare $share)
    {
        $qb = $this->createQueryBuilder('s')
            ->leftJoin('s.pickups', 'pickups')
            ->select('s.pickupsNum, s.pickupDay, s.startDate, SUM(pickups.skipped) as skippedNum')
            ->where('s.id = :id')
            ->setParameter('id', $share->getId());

        return $qb->getQuery()->getSingleResult();
    }

    /**
     * @param Client $client
     * @param $shareDay
     * @param $shareDate
     * @return array
     */
    public function searchByShareDay(Client $client, $shareDay, $shareDate)
    {
        $shareDay = $this->getShareDay($shareDay);

        $now = new \DateTime("midnight");

        $qb = $this->createQueryBuilder('s')
            ->select('s, share, members, pickups, location, addresses')
            ->innerJoin('s.share', 'share')
            ->innerJoin('s.pickups', 'pickups')
            ->innerJoin('s.customer', 'members')
            ->innerJoin('s.location', 'location')
            ->leftJoin('members.addresses', 'addresses')
            ->where('members.client = :client AND members.isLead = 0 AND s.pickupDay = :shareDay AND s.renewalDate >= :from')
            ->andWhere('pickups.date = :date and pickups.skipped = false')
            ->orderBy('members.firstname, members.lastname')
            ->setParameter('shareDay', $shareDay)
            ->setParameter('client', $client)
            ->setParameter('from', $now)
            ->setParameter('date', $shareDate);

        return $qb->getQuery()->getResult();
    }

    /**
     * @param $client
     * @return array
     */
    public function getCustomerSharesArray($client)
    {
        $qb = $this->createQueryBuilder('s')
            ->leftJoin('s.share', 'share')
            ->select('share.name')
            ->where('share.client = :client')
            ->orderBy('share.name')
            ->setParameter('client', $client)
            ->distinct();

        return $qb->getQuery()->getResult();
    }

    /**
     * Get shares with active pickups
     *
     * @param Customer $customer
     * @return array
     */
    public function getMemberCurrentShares(Customer $customer)
    {
        $qb = $this->createQueryBuilder('s')
            ->select('s, share, location, pickups')
            ->leftJoin('s.share', 'share')
            ->leftJoin('s.location', 'location')
            ->leftJoin('s.pickups', 'pickups')
            ->where('s.customer = :customer')
            ->andWhere('pickups.date >= :now')
            ->orderBy('share.name, pickups.date')
            ->setParameter('customer', $customer)
            ->setParameter('now', new \DateTime('midnight'));

        return $qb->getQuery()->getResult();
    }

    /**
     * Get share products for given date range
     *
     * @param $member
     * @return array
     */
    public function getSharesProducts($member)
    {
        $qb =
            $this->createQueryBuilder('s')
                ->select('s, share, pickups, memberOrder, shareProducts, product, customShares')
                ->innerJoin('s.share', 'share')
                ->innerJoin('s.pickups', 'pickups')
                ->innerJoin('share.customerOrders', 'memberOrder')
                ->innerJoin('memberOrder.shareProducts', 'shareProducts')
                ->innerJoin('shareProducts.product', 'product')
                ->leftJoin('shareProducts.customShares', 'customShares')
                ->where('s.customer = :customer')
                ->andWhere('pickups.date >= :now')
                ->andWhere(':now >= memberOrder.startDate and :now <= memberOrder.endDate')
                ->orderBy('share.name, pickups.date, product.name')
                ->setParameter('customer', $member)
                ->setParameter('now', new \DateTime());

        return $qb->getQuery()->getResult();
    }

    /**
     * Get shares that must be archived
     *
     * @param $client
     * @return array
     */
    public function getSharesToArchive($client)
    {
        $qb = $this->createQueryBuilder('s')
            ->leftJoin('s.customer', 'm')
            ->andWhere("s.renewalDate < :now")
            ->andWhere('m.client = :client')
            ->setParameter('client', $client)
            ->setParameter('now', new \DateTime('midnight'));

        return $qb->getQuery()->getResult();
    }

    /**
     * Get shares that is not active yet (PENDING status = 1) or with not activated customer
     *
     * @param $client
     * @return array
     */
    public function getNotActiveShares($client)
    {
        $qb = $this->createQueryBuilder('s')
            ->innerJoin('s.customer', 'm')
            ->where("s.status = 1")
            ->orWhere("m.isActivated = 0")
            ->andWhere('m.client = :client')
            ->setParameter('client', $client);

        return $qb->getQuery()->getResult();
    }

    /**
     * @param $day
     * @return bool
     */
    private function getShareDay($day)
    {
        $found = false;

        $week = [
            1 => 'MONDAY',
            2 => 'TUESDAY',
            3 => 'WEDNESDAY',
            4 => 'THURSDAY',
            5 => 'FRIDAY',
            6 => 'SATURDAY',
            7 => 'SUNDAY'
        ];

        foreach ($week as $item) {
            if (strpos($item,mb_strtoupper($day)) !== false) {
                $found = array_flip($week)[$item];
            }
        }

        return $found;
    }
}
