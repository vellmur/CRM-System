<?php

namespace App\Entity\Translation;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class TranslationLocale
 *
 * @ORM\Table(name="translation__locale", uniqueConstraints={
 *     @ORM\UniqueConstraint(name="translation_locale_unique", columns={"code"})
 * })
 * @ORM\Entity(repositoryClass="App\Repository\Translation\TranslationLocaleRepository")
 */
class TranslationLocale
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
     * @ORM\Column(name="code", type="string", length=2, nullable=false, unique=true)
     */
    private $code;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Translation\Translation", mappedBy="locale", cascade={"remove"})
     */
    private $translations;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Translation\TranslationShared", mappedBy="locale", cascade={"remove"})
     */
    private $sharedLocales;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\User\User", mappedBy="locale")
     */
    private $users;

    public function __toString()
    {
        return $this->code ? $this->code : '';
    }

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
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param mixed $code
     */
    public function setCode($code): void
    {
        $this->code = $code;
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