<?php

namespace App\Repository\Translation;

use App\Entity\Translation\Translation;
use App\Entity\Translation\TranslationDomain;
use App\Entity\Translation\TranslationKey;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class TranslationKeyRepository extends ServiceEntityRepository
{
    /**
     * TransactionKeyRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TranslationKey::class);
    }

    /**
     * @param $locale
     * @param $domain
     * @return array
     */
    public function loadTranslation($locale, $domain)
    {
        return $this->createQueryBuilder('translation_key')
            ->select('translation_key, translation, locale')
            ->leftJoin('translation_key.translations', 'translation')
            ->innerJoin('translation_key.domain', 'domain')
            ->innerJoin('translation.locale', 'locale')
            ->where('domain.domain = :domain')
            ->andWhere('locale.code = :locale')
            ->setParameter('domain', $domain)
            ->setParameter('locale', $locale)
            ->getQuery()
            ->getArrayResult();
    }

    /**
     * @return \Doctrine\ORM\Query
     */
    public function getTranslationKeysQuery()
    {
        return $this->_em->createQuery("SELECT translation_key FROM App\Entity\Translation\TranslationKey translation_key");
    }

    /**
     * @param TranslationDomain $domain
     * @return array
     */
    public function loadKeys(TranslationDomain $domain)
    {
        $results = $this->createQueryBuilder('translation_key')
            ->select('translation_key, translation')
            ->leftJoin('translation_key.translations', 'translation')
            ->leftJoin('translation.locale', 'locale')
            ->where('translation_key.domain = :domain')
            ->andWhere('locale.code = :locale')
            ->setParameter('domain', $domain)
            ->setParameter('locale', 'en')
            ->getQuery()
            ->getArrayResult();


        $keys = [];

        foreach ($results as $key => $row) {
            $keys[$key] = [
                'id' => $row['id'],
                'key' => $row['key'],
                'translation' => $row['translations'][0]['translation']
            ] ;
        }

        return $keys;
    }

    /**
     * @param TranslationKey $key
     * @return Translation|null
     */
    public function getKeyEnglishValue(TranslationKey $key)
    {
        $result = $this->createQueryBuilder('translation_key')
            ->select('translation_key, translation')
            ->leftJoin('translation_key.translations', 'translation')
            ->leftJoin('translation.locale', 'locale')
            ->where('translation_key = :key')
            ->andWhere('locale.code = :locale')
            ->setParameter('key', $key)
            ->setParameter('locale', 'en')
            ->getQuery()
            ->getResult();

        $translation = null;

        if (count($result) > 0 && count($result[0]->getTranslations()) > 0) {
            /** @var Translation $translation */
            $translation = $result[0]->getTranslations()[0];
        }

        return $translation;
    }
}