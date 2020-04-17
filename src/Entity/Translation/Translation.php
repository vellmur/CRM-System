<?php

namespace App\Entity\Translation;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Class Translation
 *
 * @ORM\Table(name="translation", uniqueConstraints={
 *     @ORM\UniqueConstraint(name="translation_unique", columns={"key_id", "locale_id"})
 * })
 * @ORM\Entity(repositoryClass="App\Repository\Translation\TranslationRepository")
 * @UniqueEntity(fields={"key", "locale"}, errorPath="key", message="validation.form.unique")
 */
class Translation implements TranslationEntityInterface
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
     * @ORM\ManyToOne(targetEntity="App\Entity\Translation\TranslationKey", inversedBy="translations", cascade={"persist"})
     * @ORM\JoinColumn(name="key_id", referencedColumnName="id", nullable=false)
     */
    private $key;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Translation\TranslationLocale", inversedBy="translations")
     * @ORM\JoinColumn(name="locale_id", referencedColumnName="id", nullable=false)
     */
    private $locale;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $translation;

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
    public function setId(?int $id): void
    {
        $this->id = $id;
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
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @param mixed $locale
     */
    public function setLocale(TranslationLocale $locale): void
    {
        $this->locale = $locale;
    }

    /**
     * @return string|null
     */
    public function getTranslation() : ?string
    {
        return $this->translation;
    }

    /**
     * @param string|null $translation
     */
    public function setTranslation(?string $translation): void
    {
        $this->translation = $translation;
    }

    /**
     * @param array $params
     * @return TranslationEntityInterface
     */
    public function load(array $params): TranslationEntityInterface
    {
        foreach ($params as $key => $value) {
            $this->{$key} = $value;
        }

        return $this;
    }
}