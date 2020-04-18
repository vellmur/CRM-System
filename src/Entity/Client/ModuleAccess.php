<?php

namespace App\Entity\Client;

use Doctrine\ORM\Mapping as ORM;

/**
 * ModuleAccess
 *
 * @ORM\Table(name="client__module_access", uniqueConstraints={
 *     @ORM\UniqueConstraint(name="access_unique", columns={"client_id", "module_id"})
 * })
 * @ORM\Entity(repositoryClass="App\Repository\ModuleAccessRepository")
 */
class ModuleAccess
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
     * @ORM\ManyToOne(targetEntity="App\Entity\Client\Client", inversedBy="accesses", cascade={"persist", "remove" })
     * @ORM\JoinColumn(name="client_id", referencedColumnName="id", nullable=false)
     */
    private $client;

    /**
     * @ORM\Column(name="module_id", type="integer", length=1, nullable=false)
     */
    private $module;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="expired_at", type="datetime")
     */
    private $expiredAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated_at", type="datetime")
     */
    private $updatedAt;

    /**
     * @ORM\Column(name="status", type="integer", length=1, nullable=false)
     */
    private $status;


    public const MODULES = [
        1 => 'customers'
    ];

    /**
     * Modules statuses names and ids sorted by logical period in lifetime of module access period:
     * PENDING - module is pending, when access start date is the future
     * ACTIVE - module is active, when access start date is started
     * RENEWAL - module is renewal, when 7 days lefts to the end of access
     * LAPSED - module is lapsed, when access date is past
     */
    public const STATUSES = [
        1 => 'PENDING',
        2 => 'ACTIVE',
        3 => 'RENEWAL',
        4 => 'LAPSED'
    ];

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param $client
     * @return $this
     */
    public function setClient($client)
    {
        $this->client = $client;

        return $this;
    }

    /**
     * @return Client
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @param $module
     * @return $this
     */
    public function setModule($module)
    {
        $this->module = $module;

        return $this;
    }

    /**
     * @return string
     */
    public function getModule()
    {
        return $this->module;
    }

    /**
     * @param $expiredAt
     * @return $this
     */
    public function setExpiredAt($expiredAt)
    {
        $this->expiredAt = $expiredAt;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getExpiredAt()
    {
        return $this->expiredAt;
    }

    /**
     * @param $updatedAt
     * @return $this
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @param $status
     * @return $this
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return mixed
     */
    public function getStatusName()
    {
        return self::STATUSES[$this->status];
    }

    /**
     * @param $name
     * @return $this
     */
    public function setStatusByName($name)
    {
        $this->status = array_flip(self::STATUSES)[mb_strtoupper($name)];

        return $this;
    }

    /**
     * @param $id
     * @return mixed
     */
    public static function getStatusNameById($id)
    {
        return self::STATUSES[$id];
    }

    /**
     * @param string $name
     * @return mixed
     */
    public static function getModuleId(string $name)
    {
        return array_flip(self::MODULES)[strtolower($name)];
    }

    /**
     * @return mixed
     */
    public function getModuleName()
    {
        return $this->module !== 0 ? self::MODULES[$this->module] : null;
    }
}
