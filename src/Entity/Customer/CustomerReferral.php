<?php

namespace App\Entity\Customer;

use Doctrine\ORM\Mapping as ORM;

/**
 * Customer
 *
 * @ORM\Table(name="customer__referrals", uniqueConstraints={
 *     @ORM\UniqueConstraint(name="customer_referral_unique", columns={"customer_id", "referral_id"})
 * })
 * @ORM\Entity()
 */
class CustomerReferral
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
     * @ORM\ManyToOne(targetEntity="App\Entity\Customer\Customer", inversedBy="referrals")
     * @ORM\JoinColumn(name="customer_id", referencedColumnName="id")
     */
    private $customer;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Customer\Customer")
     * @ORM\JoinColumn(name="referral_id", referencedColumnName="id")
     */
    private $referral;

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
     * @return mixed
     */
    public function getReferral()
    {
        return $this->referral;
    }

    /**
     * @param mixed $referral
     */
    public function setReferral($referral)
    {
        $this->referral = $referral;
    }
}