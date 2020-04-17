<?php

namespace App\Entity\Translation;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="translation__shared", uniqueConstraints={
 *     @ORM\UniqueConstraint(name="translation_shared_unique", columns={"locale_id", "domain_id"})
 * })
 * @ORM\Entity(repositoryClass="App\Repository\Translation\TranslationSharedRepository")
 * @ORM\HasLifecycleCallbacks
 */
class TranslationShared
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
     * @ORM\ManyToOne(targetEntity="App\Entity\Translation\TranslationLocale", inversedBy="sharedLocales", cascade={"persist"})
     * @ORM\JoinColumn(name="locale_id", referencedColumnName="id", nullable=false)
     */
    private $locale;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Translation\TranslationDomain", inversedBy="sharedDomains", cascade={"persist"})
     * @ORM\JoinColumn(name="domain_id", referencedColumnName="id", nullable=false)
     */
    private $domain;

    /**
     * @ORM\Column(name="is_shared", type="boolean")
     */
    private $isShared = 0;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated_at", type="datetime")
     */
    private $updatedAt;

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
    public function getLocale() : TranslationLocale
    {
        return $this->locale;
    }

    /**
     * @return mixed
     */
    public function getDomain() : TranslationDomain
    {
        return $this->domain;
    }

    /**
     * @param mixed $domain
     */
    public function setDomain(TranslationDomain $domain): void
    {
        $this->domain = $domain;
    }

    /**
     * @param mixed $locale
     */
    public function setLocale(TranslationLocale $locale): void
    {
        $this->locale = $locale;
    }

    /**
     * @return bool
     */
    public function isShared(): bool
    {
        return $this->isShared;
    }

    /**
     * @param bool $isShared
     */
    public function setIsShared(bool $isShared): void
    {
        $this->isShared = $isShared;
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedAt(): \DateTime
    {
        return $this->updatedAt;
    }

    /**
     * @param \DateTime $updatedAt
     */
    public function setUpdatedAt(\DateTime $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    /**
     * Gets triggered only on insert

     * @ORM\PrePersist
     */
    public function onPrePersist()
    {
        $this->updatedAt = new \DateTime("now");
    }

    /**
     * Gets triggered every time on update

     * @ORM\PreUpdate
     */
    public function onPreUpdate()
    {
        $this->updatedAt = new \DateTime("now");
    }
}
