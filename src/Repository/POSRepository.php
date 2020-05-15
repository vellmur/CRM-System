<?php

namespace App\Repository;

use App\Entity\Building\Building;
use App\Entity\Customer\POS;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class POSRepository extends ServiceEntityRepository
{
    /**
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, POS::class);
    }

    /**
     * @param Building $building
     * @param $period
     * @return \Doctrine\ORM\Query
     */
    public function getOrders(Building $building, $period)
    {
        $qb = $this->createQueryBuilder('pos')
            ->select('pos')
            ->where('pos.building = :building')
            ->orderBy('pos.createdAt', 'DESC')
            ->setParameter('building', $building);

        if ($period) {
            $today = new \DateTime('midnight');

            switch ($period)
            {
                case 'today':
                    $qb->andWhere('pos.createdAt >= :date')
                        ->setParameter('date', $today);
                    break;
                case 'yesterday':
                    $qb->andWhere('pos.createdAt >= :date and pos.createdAt < :today')
                        ->setParameter('date', $today->modify('-1 days'))
                        ->setParameter('today', new \DateTime('midnight'));
                    break;
                case 'week':
                    $qb->andWhere('WEEK(pos.createdAt) = :date')
                        ->setParameter('date', $today->format("W") - 1);
                    break;
                case 'month':
                    $qb->andWhere('MONTH(pos.createdAt) = :date')
                        ->setParameter('date', $today->format("m"));
                    break;
            }
        }

        return $qb->getQuery();
    }

    /**
     * @param Building $building
     * @param $period
     * @return mixed
     */
    public function getPOSSummary(Building $building, $period)
    {
        $qb = $this->createQueryBuilder('pos')
            ->select('COUNT(pos.id) as totalCount, SUM(pos.total) as totalSum')
            ->where('pos.building = :building')
            ->setParameter('building', $building);

        if ($period) {
            $today = new \DateTime('midnight');

            switch ($period)
            {
                case 'today':
                    $qb->andWhere('pos.createdAt >= :date')
                        ->setParameter('date', $today);
                    break;
                case 'yesterday':
                    $qb->andWhere('pos.createdAt >= :date and pos.createdAt < :today')
                        ->setParameter('date', $today->modify('-1 days'))
                        ->setParameter('today', new \DateTime('midnight'));
                    break;
                case 'week':
                    $qb->andWhere('WEEK(pos.createdAt) = :date')
                        ->setParameter('date', $today->format("W") - 1);
                    break;
                case 'month':
                    $qb->andWhere('MONTH(pos.createdAt) = :date')
                        ->setParameter('date', $today->format("m"));
                    break;
            }
        }

        return $qb->getQuery()->getSingleResult();
    }

    /**
     * Get sales for last 30 days
     *
     * @param Building $building
     * @return array
     */
    public function getSalesStatistics(Building $building)
    {
        $date = new \DateTime('midnight');
        $date->modify('-30 days');

        $qb = $this->createQueryBuilder('pos')
            ->select('SUM(pos.total) as total, pos.createdAt as date, DAY(pos.createdAt) as day, AVG(pos.total) as averageSale')
            ->where('pos.createdAt >= :date')
            ->andWhere('pos.building = :building')
            ->orderBy('pos.createdAt')
            ->groupBy('date, day')
            ->setParameter('date', $date)
            ->setParameter('building', $building);

        $result = $qb->getQuery()->getScalarResult();

        return $result;
    }

    /**
     * @param Building $building
     * @return array
     */
    public function getHourSales(Building $building)
    {
        $date = new \DateTime('midnight');
        $date->modify('-30 days');

        $query ='SELECT hour, AVG(sumTotal) as total, num FROM (
            SELECT SUM(total) AS sumTotal, DATE(created_at) AS date, HOUR(created_at) AS hour, COUNT(*) as num FROM pos 
            WHERE DATE(created_at) >= :date AND building_id = :buildingID GROUP BY hour, date
        ) AS T GROUP BY hour, num';

        $stmt = $this->getEntityManager()->getConnection()->prepare($query);
        $stmt->bindValue('date', $date->format('Y-m-d'));
        $stmt->bindValue('buildingID', $building->getId());
        $stmt->execute();
        $result = $stmt->fetchAll();

        return $result;
    }

    /**
     * @param Building $building
     * @return array
     */
    public function getMostPurchasedProducts(Building $building)
    {
        $qb = $this->createQueryBuilder('pos')
            ->innerJoin('pos.products', 'products')
            ->innerJoin('products.product', 'product')
            ->select('product.name as name, SUM(products.weight) as totalWeight')
            ->where('pos.building = :building')
            ->andWhere('products.weight IS NOT NULL')
            ->orderBy('totalWeight', 'DESC')
            ->groupBy('name')
            ->setParameter('building', $building)
            ->setMaxResults(20);

        $result = $qb->getQuery()->getScalarResult();

        return $result;
    }

    /**
     * @param Building $building
     * @return array
     */
    public function getMonthSales(Building $building)
    {
        $date = new \DateTime('midnight');
        $date->modify('-30 days');

        $qb = $this->createQueryBuilder('pos')
            ->select('SUM(pos.total) as averageDaySales, AVG(pos.total) as averageSalePrice, DAY(pos.createdAt) as day')
            ->where('pos.createdAt >= :date')
            ->andWhere('pos.building = :building')
            ->groupBy('day')
            ->setParameter('date', $date)
            ->setParameter('building', $building);

        $results = $qb->getQuery()->getResult();

        $sales = [
            'averageDaySales' => 0,
            'averageSalePrice' => 0
        ];

        if (count($results)) {
            foreach ($results as $result) {
                $sales['averageDaySales'] += $result['averageDaySales'];
                $sales['averageSalePrice'] += $result['averageSalePrice'];
            }

            $sales['averageDaySales'] /= count($results);
            $sales['averageSalePrice'] /= count($results);
        }

        return $sales;
    }
}
