<?php

namespace App\Entity\User;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="user__page_views")
 * @ORM\Entity(repositoryClass="App\Repository\PageViewRepository")
 */
class PageView
{
    public function __construct()
    {
        $now = new \DateTime();
        $this->createdAt = $now->modify('-6 hours');
    }

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User\Device", inversedBy="pageViews")
     * @ORM\JoinColumn(name="device_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    private $device;

    /**
     * @var integer
     *
     * @ORM\Column(name="module_id", type="integer", length=1, nullable=true)
     */
    private $module;

    /**
     * @var string
     *
     * @ORM\Column(name="link", type="string", length=255, nullable=false)
     */
    private $link;

    /**
     * @var string
     *
     * @ORM\Column(name="page", type="string", length=255, nullable=false)
     */
    private $page;

    /**
     * @var boolean
     * @ORM\Column(name="is_promo", type="boolean", nullable=false)
     */
    private $isPromo = 0;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $createdAt;

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
    public function setId(int $id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getDevice()
    {
        return $this->device;
    }

    /**
     * @param mixed $device
     */
    public function setDevice($device)
    {
        $this->device = $device;
    }

    /**
     * @return int
     */
    public function getModule(): int
    {
        return $this->module;
    }

    /**
     * @param int $module
     */
    public function setModule(int $module)
    {
        $this->module = $module;
    }

    /**
     * @return string
     */
    public function getLink(): string
    {
        return $this->link;
    }

    /**
     * @param string $link
     */
    public function setLink(string $link)
    {
        $this->link = $link;
    }

    /**
     * @return string
     */
    public function getPage(): string
    {
        return $this->page;
    }

    /**
     * @param string $page
     */
    public function setPage(string $page)
    {
        $this->page = $page;
    }

    /**
     * @return bool
     */
    public function isPromo(): bool
    {
        return $this->isPromo;
    }

    /**
     * @param bool $isPromo
     */
    public function setIsPromo(bool $isPromo)
    {
        $this->isPromo = $isPromo;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime $createdAt
     */
    public function setCreatedAt(\DateTime $createdAt)
    {
        $this->createdAt = $createdAt;
    }
}
