<?php

namespace App\Repository\Translation;

use App\Entity\Translation\TranslationShared;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class TranslationSharedRepository extends ServiceEntityRepository
{
    /**
     * TransactionRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TranslationShared::class);
    }

    /**
     * @return mixed
     */
    public function getTranslations()
    {
        $result = $this->createQueryBuilder('t')
            ->select('t, locale, domain')
            ->addSelect('
                COUNT(translations.id) as total,
                SUM(case when translations.translation is not null then 1 else 0 end) as completed
            ')
            ->innerJoin('t.locale' , 'locale')
            ->innerJoin('t.domain' , 'domain')
            ->innerJoin('locale.translations', 'translations')
            ->where('t.isShared = 1')
            ->andWhere('locale.code != :locale')
            ->setParameter('locale', 'en')
            ->groupBy('t')
            ->getQuery()
            ->getResult();


        return $result;
    }

}