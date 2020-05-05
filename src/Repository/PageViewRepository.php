<?php

namespace App\Repository;

use App\Entity\Client\Client;
use App\Entity\User\PageView;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class PageViewRepository extends ServiceEntityRepository
{
    /**
     * PageViewRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PageView::class);
    }

    /**
     * @param $deviceId
     * @param $url
     * @param $moduleId
     * @param $pageName
     * @return string
     * @throws \Exception
     */
    public function saveView($deviceId, $url, $moduleId, $pageName)
    {
        $con = $this->getEntityManager()->getConnection();
        $qb = $con->createQueryBuilder();

        $datetime = new \DateTime();

        $qb->insert('device__page_views')
            ->values([
                'device_id' => '?',
                'url' => '?',
                'module_id' => '?',
                'page' => '?',
                'created_at' => '?'
            ])
            ->setParameter(0, $deviceId)
            ->setParameter(1, strtok($url, '?'))
            ->setParameter(2, $moduleId)
            ->setParameter(3, $pageName)
            ->setParameter(4, $datetime->format('Y-m-d'));

        $qb->execute();

        return $con->lastInsertId();
    }

    /**
     * @param int $viewId
     * @param string $promotionName
     * @return string
     * @throws \Exception
     */
    public function savePromotionView(int $viewId, string $promotionName)
    {
        $con = $this->getEntityManager()->getConnection();
        $qb = $con->createQueryBuilder();

        $datetime = new \DateTime();

        $qb->insert('device__promotion_visit')
            ->values([
                'page_id' => '?',
                'name' => '?',
                'created_at' => '?'
            ])
            ->setParameter(0, $viewId)
            ->setParameter(1, $promotionName)
            ->setParameter(2, $datetime->format('Y-m-d'));

        $qb->execute();

        return $con->lastInsertId();
    }

    /**
     * @param string|null $chart
     * @param Client|null $client
     * @return mixed
     */
    public function countPageViews(?string $chart, ?Client $client)
    {
        $qb = $this->createQueryBuilder('v')
            ->select('
            COUNT(v.id) as counter, 
            YEAR(v.createdAt) as year, 
            MONTH(v.createdAt) as month, 
            DAY(v.createdAt) as day, 
            v.url as url,
            v.page as page'
            )
            ->orderBy('v.url')
            ->groupBy('page, url, year, month, day');

        if ($chart && $chart !== 'all') {
            if ($chart == 'website' || $chart == 'promotion') {
                $qb->andWhere('v.module is NULL');
            } else {
                $modules = ['customers' => 1];
                $qb->andWhere('v.module = :module')->setParameter('module', $modules[$chart]);
            }
        }

        if ($client) {
            // Here must be user views
            /*
            $qb->innerJoin('v.device', 'device')
                ->innerJoin('device.user', 'user')
                ->andWhere('user.id = :userId')
                ->setParameter('userId', $client->getOwner()->getId());*/
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * @return array|mixed
     * @throws \Exception
     */
    public function countLandingViews()
    {
        $today = new \DateTime();
        $week = new \DateTime();
        $week->modify('-7 days');
        $month = new \DateTime();
        $month->modify('-30 days');

        $qb = $this->createQueryBuilder('v')
            ->select('
                SUM(case when v.createdAt >= :today then 1 else 0 end) as today,
                SUM(case when v.createdAt >= :week then 1 else 0 end) as week,
                SUM(case when v.createdAt >= :month then 1 else 0 end) as month'
            )
            ->where('v.module is NULL and v.url = :url')
            ->setParameter('url', '/')
            ->setParameter('today', $today->format('Y-m-d'))
            ->setParameter('week', $week->format('Y-m-d'))
            ->setParameter('month', $month->format('Y-m-d'));

        $result = $qb->getQuery()->getScalarResult();

        return $result ? $result[0] : $result;
    }

    /**
     * @return mixed
     */
    public function getPages()
    {
        $qb = $this->createQueryBuilder('p')
            ->where("p.page = ''")
            ->setMaxResults(5000);

        return $qb->getQuery()->getResult();
    }
}
