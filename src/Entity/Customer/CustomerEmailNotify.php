<?php

namespace App\Entity\Customer;

use App\Entity\Customer\Email\AutoEmail;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Automates emails for
 *
 * @ORM\Table(name="customer__notifies", uniqueConstraints={@ORM\UniqueConstraint(name="customer_emails_unique", columns={"customer_id", "notify_type"})})
 * @ORM\Entity()
 * @UniqueEntity(fields="name", errorPath="name", message="validation.form.unique")
 */
class CustomerEmailNotify
{
    public function __toString()
    {
        return $this->getNotifyName();
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
     * @ORM\ManyToOne(targetEntity="App\Entity\Customer\Customer", inversedBy="notifications")
     * @ORM\JoinColumn(name="customer_id", referencedColumnName="id")
     */
    private $customer;

    /**
     * @var int
     *
     * @ORM\Column(name="notify_type", type="integer", length=1)
     */
    private $notifyType;

    /**
     * @var boolean
     * @ORM\Column(name="is_active", type="boolean")
     */
    private $isActive = 1;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
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
     * @return int
     */
    public function getNotifyType()
    {
        return $this->notifyType;
    }

    /**
     * @param int $notifyType
     */
    public function setNotifyType($notifyType)
    {
        $this->notifyType = $notifyType;
    }

    /**
     * @return mixed
     */
    public function isActive()
    {
        return $this->isActive;
    }

    /**
     * @param mixed $isActive
     */
    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;
    }

    /**
     * @return mixed
     */
    public function getNotifyName()
    {
        return AutoEmail::EMAIL_TYPES[$this->notifyType];
    }
}
