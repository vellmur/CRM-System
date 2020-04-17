<?php

namespace App\Entity\Customer;

use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Client
 *
 * @ORM\Table(name="customer__renewal_views")
 * @ORM\Entity(repositoryClass="App\Repository\RenewalViewRepository")
 * @UniqueEntity(fields="name", errorPath="name", message="validation.form.unique")
 */
class RenewalView
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
     * @ORM\ManyToOne(targetEntity="App\Entity\Client\Client", inversedBy="renewalViews")
     * @ORM\JoinColumn(name="client_id", referencedColumnName="id", nullable=false)
     */
    private $client;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Customer\Customer", inversedBy="renewalViews")
     * @ORM\JoinColumn(name="customer_id", referencedColumnName="id", nullable=true)
     */
    private $customer;

    /**
     * @var integer
     *
     * @ORM\Column(name="ip", type="integer", length=11, nullable=true, options={"unsigned"=true})
     */
    private $ip;

    /**
     * @var integer
     *
     * @ORM\Column(name="step", type="integer", length=1, nullable=false)
     */
    private $step;

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
     * @return mixed
     */
    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     * @param mixed $customer
     */
    public function setCustomer($customer)
    {
        $this->customer = $customer;
    }

    /**
     * Return unFormatted IP from INET_ATON function (INET_NTOA analogue)
     *
     * @return null|string
     */
    public function getIp()
    {
        return $this->ip ? long2ip(sprintf("%d", $this->ip)) : null;
    }

    /**
     * @param string $ip
     */
    public function setIp(string $ip)
    {
        $this->ip = $ip ? sprintf("%u", ip2long($ip)) : null;
    }

    /**
     * @return int
     */
    public function getStep(): int
    {
        return $this->step;
    }

    /**
     * @param string $step
     */
    public function setStep(string $step)
    {
        $this->step = self::getRenewalSteps()[ucfirst($step)];
    }

    /**
     * @return array
     */
    public static function getRenewalSteps()
    {
        $steps = [
            'Market' => 1,
            'Customer' => 2,
            'Payment' => 3,
            'Location' => 4,
            'Addresses' => 5,
            'Summary' => 6,
            'Completed' => 7
        ];

        return $steps;
    }


    /**
     * @param null $id
     * @return mixed
     */
    public static function getStepName($id = null)
    {
        $steps = array_flip(self::getRenewalSteps());

        return $id ? $steps[$id] : $steps[self::getStep()];
    }

    /**
     * @param $name
     * @return mixed
     */
    public static function getStepId($name)
    {
        return self::getRenewalSteps()[$name];
    }

    /**
     * @param \DateTime $createdAt
     */
    public function setCreatedAt(\DateTime $createdAt)
    {
        $this->createdAt = $createdAt;
    }
}