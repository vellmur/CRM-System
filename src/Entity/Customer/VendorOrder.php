<?php

namespace App\Entity\Customer;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * VendorOrders
 *
 * @ORM\Table(name="customer__vendor_orders")
 * @ORM\Entity(repositoryClass="App\Repository\VendorOrdersRepository")
 */
class VendorOrder
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
     * @ORM\ManyToOne(targetEntity="App\Entity\Client\Client", inversedBy="vendorOrders")
     * @ORM\JoinColumn(name="client_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $client;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Customer\Vendor", inversedBy="vendorOrders")
     * @ORM\JoinColumn(name="vendor_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $vendor;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="order_date", type="date")
     * @Assert\NotBlank(message="validation.form.required")
     */
    private $orderDate;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Customer\ShareProduct", mappedBy="vendorOrder", cascade={"all"})
     */
    private $shareProducts;

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
    public function getVendor()
    {
        return $this->vendor;
    }

    /**
     * @param mixed $vendor
     */
    public function setVendor($vendor)
    {
        $this->vendor = $vendor;
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
    public function setOrderDate($orderDate)
    {
        $this->orderDate = $orderDate;
    }

    /**
     * @return \Doctrine\Common\Collections\Collection|ShareProduct[] $products
     */
    public function getShareProducts()
    {
        return $this->shareProducts;
    }

    /**
     * @param mixed $shareProducts
     */
    public function setShareProducts($shareProducts)
    {
        $this->shareProducts = $shareProducts;
    }
}