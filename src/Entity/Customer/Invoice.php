<?php

namespace App\Entity\Customer;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * CustomerInvoice
 *
 * @ORM\Table(name="customer__invoice")
 * @ORM\Entity(repositoryClass="App\Repository\InvoiceRepository")
 */
class Invoice
{
    public function __construct()
    {
        $this->items = new ArrayCollection();
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
     * @ORM\ManyToOne(targetEntity="App\Entity\Customer\Customer", inversedBy="invoices")
     * @ORM\JoinColumn(name="customer_id", referencedColumnName="id", nullable=false)
     */
    private $customer;

    /**
     * @var float
     *
     * @ORM\Column(name="amount", type="decimal", precision=8, scale=2)
     */
    private $amount;

    /**
     * @ORM\Column(name="ref_num", type="string", nullable=true)
     */
    private $refnum;

    /**
     * @var \DateTime
     * @ORM\Column(name="order_date", type="date")
     */
    private $orderDate;

    /**
     *
     * @var boolean
     * @ORM\Column(name="is_paid", type="boolean")
     */
    private $isPaid = 0;

    /**
     *
     * @var boolean
     * @ORM\Column(name="is_sent", type="boolean")
     */
    private $isSent = 0;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Customer\InvoiceProduct", mappedBy="invoice", cascade={"all"}, orphanRemoval=true)
     */
    private $items;

    /**
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

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
     * @return Customer
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
     * @return float
     */
    public function getAmount(): float
    {
        return $this->amount;
    }

    /**
     * @param float $amount
     */
    public function setAmount(float $amount)
    {
        $this->amount = $amount;
    }

    /**
     * @param InvoiceProduct $invoiceProduct
     * @return $this
     */
    public function addItem(InvoiceProduct $invoiceProduct)
    {
        $this->items->add($invoiceProduct);
        $invoiceProduct->setInvoice($this);

        return $this;
    }

    /**
     * @return mixed
     */
    public function getRefnum()
    {
        return $this->refnum;
    }

    /**
     * @param mixed $refnum
     */
    public function setRefnum($refnum)
    {
        $this->refnum = $refnum;
    }

    /**
     * @return \DateTime
     */
    public function getOrderDate()
    {
        return $this->orderDate;
    }

    /**
     * @param \DateTime $orderDate
     */
    public function setOrderDate(\DateTime $orderDate)
    {
        $this->orderDate= $orderDate;
    }

    /**
     * @return bool
     */
    public function isPaid(): bool
    {
        return $this->isPaid;
    }

    /**
     * @param bool $isPaid
     */
    public function setIsPaid(bool $isPaid)
    {
        $this->isPaid = $isPaid;
    }

    /**
     * @return bool
     */
    public function isSent(): bool
    {
        return $this->isSent;
    }

    /**
     * @param bool $isSent
     */
    public function setIsSent(bool $isSent)
    {
        $this->isSent = $isSent;
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

    /**
     * @return \Doctrine\Common\Collections\Collection|InvoiceProduct $items
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @param mixed $items
     */
    public function setItems($items)
    {
        $this->items = $items;
    }
}
