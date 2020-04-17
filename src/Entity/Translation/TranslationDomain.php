<?php

namespace App\Entity\Translation;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class TranslationDomain
 *
 * @ORM\Table(name="translation__domain", uniqueConstraints={
 *     @ORM\UniqueConstraint(name="translation_domain_unique", columns={"domain"})
 * })
 * @ORM\Entity(repositoryClass="App\Repository\Translation\TranslationDomainRepository")
 */
class TranslationDomain
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string", nullable=false, unique=true)
     */
    private $domain;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Translation\TranslationKey", mappedBy="domain", cascade={"remove"})
     */
    private $translationKeys;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Translation\TranslationShared", mappedBy="domain", cascade={"remove"})
     */
    private $sharedDomains;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * @param mixed $domain
     */
    public function setDomain($domain): void
    {
        $this->domain = $domain;
    }

    /**
     * @return mixed
     */
    public function getTranslations()
    {
        return $this->translations;
    }

    /**
     * @param mixed $translations
     */
    public function setTranslations($translations): void
    {
        $this->translations = $translations;
    }

    /**
     * @return mixed
     */
    public function getTranslationKeys()
    {
        return $this->translationKeys;
    }

    /**
     * @param mixed $translationKeys
     */
    public function setTranslationKeys($translationKeys): void
    {
        $this->translationKeys = $translationKeys;
    }
}