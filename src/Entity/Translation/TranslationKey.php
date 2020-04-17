<?php

namespace App\Entity\Translation;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Class TranslationKey
 *
 * @ORM\Table(name="translation__key", uniqueConstraints={
 *     @ORM\UniqueConstraint(name="translation_key_unique", columns={"domain_id", "trans_key"})
 * })
 * @ORM\Entity(repositoryClass="App\Repository\Translation\TranslationKeyRepository")
 * @UniqueEntity(fields={"domain", "key"}, errorPath="key", message="validation.form.unique")
 */
class TranslationKey
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
     * @ORM\ManyToOne(targetEntity="App\Entity\Translation\TranslationDomain", inversedBy="translationKeys",cascade={"persist"})
     * @ORM\JoinColumn(name="domain_id", referencedColumnName="id", nullable=false)
     */
    private $domain;

    /**
     * @ORM\Column(name="trans_key", type="string", length=255)
     * @Assert\NotBlank(message="validation.form.required")
     */
    private $key;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Translation\Translation", mappedBy="key", cascade={"remove"})
     */
    private $translations;

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
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @param mixed $key
     */
    public function setKey($key): void
    {
        $this->key = $key;
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
}