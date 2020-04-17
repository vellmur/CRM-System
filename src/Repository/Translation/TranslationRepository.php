<?php

namespace App\Repository\Translation;

use App\Entity\Translation\Translation;
use App\Entity\Translation\TranslationDomain;
use App\Entity\Translation\TranslationKey;
use App\Entity\Translation\TranslationLocale;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class TranslationRepository extends ServiceEntityRepository
{
    /**
     * TransactionRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Translation::class);
    }

    /**
     * @param TranslationLocale $locale
     * @param TranslationDomain $domain
     * @return array
     */
    public function getTranslationList(TranslationLocale $locale, TranslationDomain $domain)
    {
        $qb = $this->createQueryBuilder('t')
            ->select('t, t_key')
            ->innerJoin('t.key' , 't_key')
            ->where('t.locale = :locale')
            ->andWhere('t_key.domain = :domain')
            ->setParameter('domain', $domain)
            ->setParameter('locale', $locale);

        if ($domain->getDomain() == 'choices') {
            $qb->orderBy('t.key');
        }

        return $qb
            ->getQuery()
            ->getArrayResult();
    }

    /**
     * @param TranslationLocale $locale
     * @param TranslationDomain $domain
     * @return \Doctrine\ORM\Query
     */
    public function getTranslationsQuery(TranslationLocale $locale, TranslationDomain $domain)
    {
        $localeId = $locale->getId();
        $domainId = $domain->getId();

        return $this->_em->createQuery("SELECT t FROM App\Entity\Translation\Translation t 
             INNER JOIN t.key t_key 
             WHERE  t.locale = '$localeId'
             AND t_key.domain = '$domainId'
          ");
    }

    /**
     * @param TranslationLocale $locale
     * @param TranslationKey $key
     * @return bool
     */
    public function isTranslationExists(TranslationLocale $locale, TranslationKey $key)
    {
        $result = $this->createQueryBuilder('t')
            ->select('t.id')
            ->where('t.locale = :locale and t.key = :key')
            ->setParameter('locale', $locale)
            ->setParameter('key', $key)
            ->getQuery()
            ->setMaxResults(1)
            ->getResult();

        return count($result) > 0;
    }
}