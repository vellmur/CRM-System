<?php

namespace App\Repository;

use App\Entity\Client\Client;
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
     * @param Client $client
     * @param $tags
     * @return array
     */
    public function findTags(Client $client, $tags)
    {
        $qb = $this->createQueryBuilder('t');

        $query = $qb
            ->select('t.name, t.id')
            ->where('t.client = :client')
            ->andWhere('t.name IN (:tags)')
            ->setParameter('client', $client)
            ->setParameter('tags', $tags);

        $result = $query->getQuery()->getScalarResult();

        $tags = [];

        foreach ($result as $tag) {
            $tags[$tag['id']] = $tag['name'];
        }

        return $tags;
    }
}
