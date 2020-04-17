<?php

namespace App\Repository;

use App\Entity\Client\Client;
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
     * @param $client
     * @return \Doctrine\Common\Collections\Collection|Product[] $products
     */
    public function getCustomerProducts($client)
    {
        $qb =
            $this->createQueryBuilder('product')
                ->select('product, productTags, tags')
                ->leftJoin('product.tags', 'productTags')
                ->leftJoin('productTags.tag', 'tags')
                ->where('product.client = :client AND product.category = 1')
                ->andWhere('product.isPos = 1 AND product.deliveryPrice IS NOT NULL')
                ->orderBy('product.name')
                ->setParameter('client', $client);

        return $qb->getQuery()->getResult();
    }

    /**
     * @param $client
     * @param $categories
     * @return array
     */
    public function getClientProducts($client, $categories)
    {
        $qb =
            $this->createQueryBuilder('product')
                ->where('product.client = :client');

        if ($categories) {
            $qb->andWhere('product.category IN (:categories)')
                ->setParameter('categories', $categories);
        }

        $qb->orderBy('product.name')
            ->setParameter('client', $client);

        $products = $qb->getQuery()->getResult();

        return $products;
    }


    /**
     * @param Client $client
     * @param $category
     * @return array
     */
    public function getProductsPricing(Client $client, $category)
    {
        $qb =
            $this->createQueryBuilder('product')
                ->select('product')
                ->where('product.client = :client')
                ->orderBy('product.name')
                ->setParameter('client', $client);

        if ($category !== 'all') $qb->andWhere('product.category = ' . $category);

        return $qb->getQuery()->getResult();
    }

    /**
     * @param $client
     * @param $category
     * @param $search
     * @return \Doctrine\ORM\Query
     */
    public function searchByAll($client, $category, $search)
    {
        $qb = $this->createQueryBuilder('product')
            ->select('product')
            ->where('product.client = :client')
            ->orderBy('product.name')
            ->setParameter('client', $client);

        $search = ucfirst($search);

        $qb->andWhere("product.name LIKE '%$search%' OR product.description LIKE '%$search%' OR product.sku LIKE '%$search%'");

        if ($category != 'all') {
            $qb->andWhere('product.category = ' . $category);
        }

        return $qb->getQuery();
    }

    /**
     * @param $client
     * @param $search
     * @return \Doctrine\ORM\Query
     */
    public function searchPOSProducts($client, $search)
    {
        $search = ucfirst($search);

        $qb = $this->createQueryBuilder('product')
            ->select('product')
            ->where('product.client = :client')
            ->andWhere('product.category = 1')
            ->andWhere('product.isPos = 1')
            ->andWhere("product.name LIKE '%$search%' OR product.sku LIKE '%$search%'")
            ->orderBy('product.name')
            ->setParameter('client', $client);

        return $qb->getQuery()->getResult();
    }
}
