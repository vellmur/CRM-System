<?php

namespace App\Entity\User;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * 
 * @ORM\Table(name="user__device")
 * @ORM\Entity(repositoryClass="App\Repository\DeviceRepository")
 */
class Device
{
    public function __construct()
    {
        $this->createdAt = new \DateTime();
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
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\User\User", inversedBy="devices")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     */
    private $user;

    /**
     * @var string
     *
     * @ORM\Column(name="ip", type="string", length=15, nullable=false)
     * @Assert\Ip
     */
    private $ip;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_computer", type="boolean", nullable=false)
     */
    private $isComputer;

    /**
     * @var string
     *
     * @ORM\Column(name="os", type="string", length=30, nullable=false)
     */
    private $os;

    /**
     * @var string
     *
     * @ORM\Column(name="browser", type="string", length=30, nullable=false)
     */
    private $browser;

    /**
     * @var string
     *
     * @ORM\Column(name="browser_version", type="string", length=20, nullable=false)
     */
    private $browser_version;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\User\PageView", mappedBy="device", cascade={"all"}, orphanRemoval=true)
     */
    private $pageViews;

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
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param mixed $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * @return string
     */
    public function getIp(): string
    {
        return $this->ip;
    }

    /**
     * @param string $ip
     */
    public function setIp(string $ip)
    {
        $this->ip = $ip;
    }

    /**
     * @return bool
     */
    public function isComputer(): bool
    {
        return $this->isComputer;
    }

    /**
     * @param bool $isComputer
     */
    public function setIsComputer(bool $isComputer)
    {
        $this->isComputer = $isComputer;
    }

    /**
     * @return string
     */
    public function getOs(): string
    {
        return $this->os;
    }

    /**
     * @param string $os
     */
    public function setOs(string $os)
    {
        $this->os = $os;
    }

    /**
     * @return string
     */
    public function getBrowser(): string
    {
        return $this->browser;
    }

    /**
     * @param string $browser
     */
    public function setBrowser(string $browser)
    {
        $this->browser = $browser;
    }

    /**
     * @return string
     */
    public function getBrowserVersion(): string
    {
        return $this->browser_version;
    }

    /**
     * @param string $browser_version
     */
    public function setBrowserVersion(string $browser_version)
    {
        $this->browser_version = $browser_version;
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
