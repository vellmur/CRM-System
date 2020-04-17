<?php
namespace App\Repository\Translation;

use App\Entity\Translation\TranslationDomain;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class TranslationDomainRepository extends ServiceEntityRepository
{
    /**
     * TransactionLocaleRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TranslationDomain::class);
    }

    /**
     * @return array
     */
    public function getTranslatedDomains()
    {
        return $this->createQueryBuilder('domain')
            ->select('domain.domain')
            ->innerJoin('domain.translationKeys', 'translation_keys')
            ->distinct()
            ->getQuery()
            ->getArrayResult();
    }

}