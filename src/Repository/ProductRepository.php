<?php

namespace App\Repository;

use App\Entity\Building\Building;
use App\Entity\Customer\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class ProductRepository extends ServiceEntityRepository
{
    /**
     * ProductRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    /**
     * @param $building
     * @return \Doctrine\Common\Collections\Collection|Product[] $products
     */
    public function getCustomerProducts($building)
    {
        $qb =
            $this->createQueryBuilder('product')
                ->select('product, productTags, tags')
                ->leftJoin('product.tags', 'productTags')
                ->leftJoin('productTags.tag', 'tags')
                ->where('product.building = :building AND product.category = 1')
                ->andWhere('product.isPos = 1 AND product.deliveryPrice IS NOT NULL')
                ->orderBy('product.name')
                ->setParameter('building', $building);

        return $qb->getQuery()->getResult();
    }

    /**
     * @param $building
     * @param $categories
     * @return array
     */
    public function getBuildingProducts($building, $categories)
    {
        $qb =
            $this->createQueryBuilder('product')
                ->where('product.building = :building');

        if ($categories) {
            $qb->andWhere('product.category IN (:categories)')
                ->setParameter('categories', $categories);
        }

        $qb->orderBy('product.name')
            ->setParameter('building', $building);

        $products = $qb->getQuery()->getResult();

        return $products;
    }


    /**
     * @param Building $building
     * @param $category
     * @return array
     */
    public function getProductsPricing(Building $building, $category)
    {
        $qb =
            $this->createQueryBuilder('product')
                ->select('product')
                ->where('product.building = :building')
                ->orderBy('product.name')
                ->setParameter('building', $building);

        if ($category !== 'all') $qb->andWhere('product.category = ' . $category);

        return $qb->getQuery()->getResult();
    }

    /**
     * @param $building
     * @param $category
     * @param $search
     * @return \Doctrine\ORM\Query
     */
    public function searchByAll($building, $category, $search)
    {
        $qb = $this->createQueryBuilder('product')
            ->select('product')
            ->where('product.building = :building')
            ->orderBy('product.name')
            ->setParameter('building', $building);

        $search = ucfirst($search);

        $qb->andWhere("product.name LIKE '%$search%' OR product.description LIKE '%$search%' OR product.sku LIKE '%$search%'");

        if ($category != 'all') {
            $qb->andWhere('product.category = ' . $category);
        }

        return $qb->getQuery();
    }

    /**
     * @param $building
     * @param $search
     * @return \Doctrine\ORM\Query
     */
    public function searchPOSProducts($building, $search)
    {
        $search = ucfirst($search);

        $qb = $this->createQueryBuilder('product')
            ->select('product')
            ->where('product.building = :building')
            ->andWhere('product.category = 1')
            ->andWhere('product.isPos = 1')
            ->andWhere("product.name LIKE '%$search%' OR product.sku LIKE '%$search%'")
            ->orderBy('product.name')
            ->setParameter('building', $building);

        return $qb->getQuery()->getResult();
    }
}
