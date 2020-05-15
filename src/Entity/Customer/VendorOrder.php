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
     * @ORM\ManyToOne(targetEntity="App\Entity\Building\Building", inversedBy="vendorOrders")
     * @ORM\JoinColumn(name="building_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $building;

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
    public function getBuilding()
    {
        return $this->building;
    }

    /**
     * @param mixed $building
     */
    public function setBuilding($building)
    {
        $this->building = $building;
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
    public function setVendor($vendor): void
    {
        $this->vendor = $vendor;
    }

    /**
     * @return \DateTime
     */
    public function getOrderDate(): ?\DateTime
    {
        return $this->orderDate;
    }

    /**
     * @param \DateTime $orderDate
     */
    public function setOrderDate(\DateTime $orderDate): void
    {
        $this->orderDate = $orderDate;
    }
}