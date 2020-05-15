<?php

namespace App\Repository;

use App\Entity\Building\Building;
use App\Entity\Customer\Tag;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class TagRepository extends ServiceEntityRepository
{
    /**
     * TagRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tag::class);
    }

    /**
     * @param Building $building
     * @param $tags
     * @return array
     */
    public function findTags(Building $building, $tags)
    {
        $qb = $this->createQueryBuilder('t');

        $query = $qb
            ->select('t.name, t.id')
            ->where('t.building = :building')
            ->andWhere('t.name IN (:tags)')
            ->setParameter('building', $building)
            ->setParameter('tags', $tags);

        $result = $query->getQuery()->getScalarResult();

        $tags = [];

        foreach ($result as $tag) {
            $tags[$tag['id']] = $tag['name'];
        }

        return $tags;
    }
}
