<?php

namespace App\Manager;

use App\Entity\Translation\Translation;
use App\Entity\Translation\TranslationDomain;
use App\Entity\Translation\TranslationKey;
use App\Entity\Translation\TranslationLocale;
use App\Entity\Translation\TranslationShared;
use App\Repository\Translation\TranslationRepository;
use Doctrine\ORM\EntityManagerInterface;

class TranslationManager
{
    private $em;

    private $repository;

    /**
     * TranslationManager constructor.
     * @param EntityManagerInterface $em
     * @param TranslationRepository $repository
     */
    public function __construct(EntityManagerInterface $em, TranslationRepository $repository)
    {
        $this->em = $em;
        $this->repository = $repository;
    }

    /**
     * @return array|null
     */
    public function getExistingLocales()
    {
        $locales = $this->em->getRepository(TranslationLocale::class)->getTranslatedLocales();
        $existingLocales = [];

        foreach ($locales as $locale) {
            $existingLocales[] = $locale['code'];
        }

        return $existingLocales;
    }

    /**
     * @return array
     */
    public function getTranslationDomains()
    {
        $domains = $this->em->getRepository(TranslationDomain::class)->getTranslatedDomains();
        $existingDomains = [];

        foreach ($domains as $domain) {
            $existingDomains[] = $domain['domain'];
        }

        return $existingDomains;
    }

    /**
     * @param TranslationLocale $locale
     * @param TranslationKey $key
     * @return bool
     */
    public function isTranslationExists(TranslationLocale $locale, TranslationKey $key)
    {
        return $this->repository->isTranslationExists($locale, $key);
    }

    /**
     * @param string $locale
     * @return bool
     * @throws \Exception
     */
    public function createTranslations(string $locale)
    {
        $this->em->beginTransaction();

        try {
            $translationLocale = $this->getLocale($locale);

            if (!$translationLocale) {
                $translationLocale = $this->createLocale($locale);
            }

            $i = 0;
            $batchSize = 100;

            $query = $this->em->getRepository(TranslationKey::class)->getTranslationKeysQuery();

            $iterableResult = $query->iterate();

            foreach ($iterableResult as $key) {
                if ($this->isTranslationExists($translationLocale, $key[0])) {
                    throw new \Exception('Translation already exists in database.');
                }

                $translation = new Translation();
                $translation->setKey($key[0]);
                $translation->setLocale($translationLocale);

                $this->em->persist($translation);

                $i++;

                if (($i % $batchSize) === 0) {
                    $this->em->flush();
                }
            }

            $this->em->flush();
            $this->em->commit();
            $this->em->clear();

            return true;
        } catch (\Exception $exception) {
            $this->em->rollback();
            $this->em->clear();

            throw $exception;
        }
    }

    /**
     * @param TranslationLocale $locale
     * @param TranslationDomain $domain
     * @param array $newTranslations
     */
    public function updateTranslations(TranslationLocale $locale, TranslationDomain $domain, array $newTranslations)
    {
        $translationsById = [];

        // Sort translations by ids for update
        foreach ($newTranslations['translations'] as $key => $translation) {
            $translationsById[$translation['id']] = $translation;
        }

        $batchSize = 20;
        $i = 1;

        $query = $this->repository->getTranslationsQuery($locale, $domain);
        $iterableResult = $query->iterate();

        foreach ($iterableResult as $row) {
            /** @var Translation $translation */
            $translation = $row[0];

            $newValue = $translationsById[$translation->getId()]['translation'];
            if ($translation->getTranslation() != $newValue) {
                $translation->setTranslation($newValue);

                if (($i % $batchSize) === 0) {
                    $this->em->flush();
                    $this->em->clear();
                }
                ++$i;
            }
        }

        $this->em->flush();
        $this->em->clear();
    }

    /**
     * @param TranslationLocale $locale
     * @param TranslationDomain $domain
     * @return array
     */
    public function loadTranslations(TranslationLocale $locale, TranslationDomain $domain)
    {
        return $this->repository->getTranslationList($locale, $domain);
    }

    /**
     * @param TranslationDomain $domain
     * @return array
     */
    public function loadKeys(TranslationDomain $domain)
    {
        return $this->em->getRepository(TranslationKey::class)->loadKeys($domain);
    }

    /**
     * @param TranslationKey $key
     * @param string $englishValue
     * @return bool|\Exception|\Throwable
     */
    public function addTranslationKey(TranslationKey $key, string $englishValue)
    {
        $this->em->beginTransaction();

        try {
            $this->em->persist($key);

            $locales = $this->getExistingLocales();

            foreach ($locales as $locale) {
                $translationLocale = $this->getLocale($locale);
                $translation = new Translation();
                $translation->setLocale($translationLocale);
                $translation->setKey($key);

                if ($locale == 'en') {
                    $translation->setTranslation($englishValue);
                }

                $this->em->persist($translation);
            }

            $this->em->flush();
            $this->em->commit();
            $this->em->clear();

            return true;
        } catch (\Throwable $exception) {
            $this->em->rollback();
            $this->em->clear();

            return $exception;
        }
    }

    /**
     * @param TranslationKey $key
     * @param string $englishValue
     * @throws \Exception
     */
    public function updateTranslationKey(TranslationKey $key, string $englishValue)
    {
        $this->em->beginTransaction();

        try {
            // Update english translation with key updating
            $englishTranslation = $this->em->getRepository(TranslationKey::class)->getKeyEnglishValue($key);
            $englishTranslation->setTranslation($englishValue);

            $this->em->flush();
            $this->em->commit();
            $this->em->clear();
        } catch (\Exception $exception) {
            $this->em->rollback();
            $this->em->clear();

            throw $exception;
        }
    }

    /**
     * @param TranslationKey $key
     * @throws \Exception
     */
    public function removeTranslationKey(TranslationKey $key)
    {
        $this->em->beginTransaction();

        try {
            $this->em->remove($key);

            $this->em->flush();
            $this->em->commit();
        } catch (\Exception $exception) {
            $this->em->rollback();
            $this->em->clear();

            throw $exception;
        }
    }

    /**
     * @param string $locale
     * @return TranslationLocale|object|null
     */
    public function getLocale(string $locale)
    {
        return $this->em->getRepository(TranslationLocale::class)->findOneBy(['code' => $locale]);
    }

    /**
     * @param string $locale
     * @return TranslationLocale
     */
    public function createLocale(string $locale) : TranslationLocale
    {
        $translationLocale = new TranslationLocale();
        $translationLocale->setCode($locale);

        $this->em->persist($translationLocale);
        $this->em->flush();

        return $translationLocale;
    }

    /**
     * @param string $domain
     * @return TranslationDomain|object|null
     */
    public function getDomain(string $domain)
    {
        return $this->em->getRepository(TranslationDomain::class)->findOneBy(['domain' => $domain]);
    }

    /**
     * @param string $domain
     * @return TranslationDomain
     */
    public function createDomain(string $domain) : TranslationDomain
    {
        $translationDomain = new TranslationDomain();
        $translationDomain->setDomain($domain);
        $this->em->persist($translationDomain);
        $this->em->flush();

        return $translationDomain;
    }

    /**
     * @param TranslationDomain $domain
     * @param string $key
     * @return TranslationKey|object|null
     */
    public function getTranslationKey(TranslationDomain $domain, string $key)
    {
        return $this->em->getRepository(TranslationKey::class)->findOneBy(['domain' => $domain, 'key' => $key]);
    }


    /**
     * @param TranslationDomain $domain
     * @param string $key
     * @return TranslationKey
     */
    public function createTranslationKey(TranslationDomain $domain, string $key) : TranslationKey
    {
        $translationKey = new TranslationKey();
        $translationKey->setDomain($domain);
        $translationKey->setKey($key);
        $this->em->persist($translationKey);
        $this->em->flush();

        return$translationKey;
    }

    /**
     * @param TranslationKey $key
     * @param TranslationLocale $locale
     * @return Translation|object|null
     */
    public function getTranslation(TranslationKey $key, TranslationLocale $locale)
    {
        return $this->em->getRepository(Translation::class)->findOneBy(['key' => $key, 'locale' => $locale]);
    }

    /**
     * @param TranslationLocale $locale
     * @param TranslationDomain $domain
     * @param bool $isShared
     */
    public function shareTranslation(TranslationLocale $locale, TranslationDomain $domain, bool $isShared)
    {
        $sharedTranslation = $this->em->getRepository(TranslationShared::class)->findOneBy([
            'locale' => $locale,
            'domain' => $domain
        ]);

        if (!$sharedTranslation) {
            $sharedTranslation = new TranslationShared();
            $sharedTranslation->setLocale($locale);
            $sharedTranslation->setDomain($domain);
        }

        $sharedTranslation->setIsShared($isShared);

        $this->em->persist($sharedTranslation);
        $this->em->flush();
        $this->em->clear();
    }

    /**
     * @return array
     */
    public function getSharedTranslations()
    {
        $sharedTranslations = $this->em->getRepository(TranslationShared::class)->findAll();

        $translations = [];

        foreach ($sharedTranslations as $sharedTranslation) {
            $translations[$sharedTranslation->getLocale()->getCode()] = $sharedTranslation->isShared();
        }

        return $translations;
    }

    /**
     * @return mixed
     */
    public function getSharedTranslation()
    {
        return $this->em->getRepository(TranslationShared::class)->getTranslations();
    }

    /**
     * @return EntityManagerInterface
     */
    public function getEntityManager()
    {
        return $this->em;
    }
}