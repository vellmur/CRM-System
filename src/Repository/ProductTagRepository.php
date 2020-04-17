<?php

namespace App\Repository;

use App\Entity\Customer\Product;
use App\Entity\Customer\ProductTag;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class ProductTagRepository extends ServiceEntityRepository
{
    /**
     * TagRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProductTag::class);
    }

    /**
     * @param Product $product
     * @return array
     */
    public function getTags(Product $product)
    {
        $qb = $this->createQueryBuilder('t');

        $query = $qb
            ->select('t.id, tag.name')
            ->innerJoin('t.tag','tag')
            ->where('t.product = :product')
            ->setParameter('product', $product);

        $result = $query->getQuery()->getScalarResult();

        $tags = [];

        foreach ($result as $tag) {
            $tags[$tag['id']] = $tag['name'];
        }

        return $tags;
    }
}
