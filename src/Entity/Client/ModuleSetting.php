<?php

namespace App\Entity\Client;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="client__settings")
 * @ORM\Entity()
 */
class ModuleSetting
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
     * @ORM\ManyToOne(targetEntity="App\Entity\Client\Client", inversedBy="modulesSettings")
     * @ORM\JoinColumn(name="client_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    private $client;

    /**
     * @var int
     *
     * @ORM\Column(name="module", type="integer", length=1)
     */
    private $module;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var bool
     *
     * @ORM\Column(name="enabled", type="boolean")
     */
    private $enabled;

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
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @param mixed $client
     */
    public function setClient($client)
    {
        $this->client = $client;
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
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name)
    {
        $this->name = $name;
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * @param bool $enabled
     */
    public function setEnabled(bool $enabled)
    {
        $this->enabled = $enabled;
    }
}