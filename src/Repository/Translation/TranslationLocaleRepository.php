<?php
namespace App\Repository\Translation;

use App\Entity\Translation\TranslationLocale;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class TranslationLocaleRepository extends ServiceEntityRepository
{
    /**
     * TransactionLocaleRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TranslationLocale::class);
    }

    /**
     * @return array
     */
    public function getTranslatedLocales()
    {
        return $this->createQueryBuilder('locale')
            ->select('locale.code')
            ->innerJoin('locale.translations', 'translations')
            ->distinct()
            ->getQuery()
            ->getArrayResult();
    }

    /**
     * @return mixed
     */
    public function getAllLocales()
    {
        return $this->createQueryBuilder('locale')->getQuery()->getResult();
    }
}