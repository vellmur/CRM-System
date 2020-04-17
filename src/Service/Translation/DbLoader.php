<?php

namespace App\Service\Translation;

use App\Repository\Translation\TranslationKeyRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;
use Symfony\Component\Translation\Exception\InvalidResourceException;
use Symfony\Component\Translation\Exception\NotFoundResourceException;
use Symfony\Component\Translation\Loader\LoaderInterface;
use Symfony\Component\Translation\MessageCatalogue;

class DbLoader implements LoaderInterface
{
    private $em;

    private $translationEntity;

    public function __construct(EntityManagerInterface $em, $translationEntity)
    {
        $this->em = $em;
        $this->translationEntity = $translationEntity;
    }

    /**
     * Loads a locale.
     *
     * @param mixed  $resource A resource
     * @param string $locale   A locale
     * @param string $domain   The domain
     *
     * @return MessageCatalogue A MessageCatalogue instance
     *
     * @throws NotFoundResourceException when the resource cannot be found
     * @throws InvalidResourceException  when the resource cannot be loaded
     */
    public function load($resource, $locale, $domain = 'labels')
    {
        $catalogue = new MessageCatalogue($locale);

        $transUnits = $this->getRepository()->loadTranslation($locale, $domain);

        foreach ($transUnits as $transUnit) {
            foreach ($transUnit['translations'] as $translation) {
                if ($translation['locale']['code'] == $locale && $translation['translation']) {
                    $catalogue->set($transUnit['key'], $translation['translation'], $domain);
                }
            }
        }

        return $catalogue;
    }

    /**
     * @return ObjectRepository
     */
    public function getRepository(): TranslationKeyRepository
    {
        return $this->em->getRepository($this->translationEntity);
    }
}