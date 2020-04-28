<?php

namespace App\Entity\Customer\Email;

use App\Entity\Customer\Customer;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Email\BaseRecipient;

/**
 * @ORM\Table(name="email__recipient",
 *      uniqueConstraints={@ORM\UniqueConstraint(name="customer_email_recipient", columns={"log_id", "customer_id"})}))
 * @ORM\Entity(repositoryClass="App\Repository\EmailRecipientRepository")
 */
class EmailRecipient extends BaseRecipient implements CustomerRecipientInterface
{
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Customer\Email\CustomerEmail", inversedBy="recipients")
     * @ORM\JoinColumn(name="log_id", referencedColumnName="id", nullable=false)
     */
    protected $emailLog;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Customer\Customer", inversedBy="emails")
     * @ORM\JoinColumn(name="customer_id", referencedColumnName="id", nullable=true)
     */
    private $customer;

    /**
     * @return Customer
     */
    public function getCustomer() : Customer
    {
        return $this->customer;
    }

    /**
     * @param mixed $customer
     */
    public function setCustomer(Customer $customer)
    {
        if (is_array($customer)) $customer = $customer[0];

        $this->customer = $customer;
    }
}