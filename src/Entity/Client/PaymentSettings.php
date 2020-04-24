<?php

namespace App\Entity\Client;

use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PaymentSettings
 *
 * @ORM\Table(name="client__payment_settings", uniqueConstraints={
 *     @ORM\UniqueConstraint(name="member_unique", columns={"client_id", "method"})
 * })
 * @ORM\Entity()
 */
class PaymentSettings
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
     * @ORM\ManyToOne(targetEntity="App\Entity\Client\Client", inversedBy="paymentSettings")
     * @ORM\JoinColumn(name="client_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $client;

    /**
     * @var int
     *
     * @ORM\Column(name="method", type="integer", length=1)
     */
    private $method;

    /**
     * @var boolean
     * @ORM\Column(name="is_active", type="boolean")
     */
    private $isActive;

    /**
     * @ORM\Column(name="description", type="text", length=2500, nullable=true)
     */
    private $description;

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
    public function getMethod(): int
    {
        return $this->method;
    }

    /**
     * @param int $method
     */
    public function setMethod(int $method)
    {
        $this->method = $method;
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->isActive;
    }

    /**
     * @param bool $isActive
     */
    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;
    }

    /**
     * @return array
     */
    public static function getMethodsNames()
    {
        $methods = [
            1 => 'cash',
            2 => 'card',
            3 => 'venmo'
        ];

        return $methods;
    }

    /**
     * @return mixed
     */
    public function getMethodName()
    {
        return self::getMethodsNames()[$this->method];
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param mixed $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }
}
