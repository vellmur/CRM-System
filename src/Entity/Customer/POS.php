<?php

namespace App\Entity\Customer;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class POS
 *
 * @ORM\Table(name="pos")
 * @ORM\Entity(repositoryClass="App\Repository\POSRepository")
 */
class POS
{
    public function __construct()
    {
        $this->products = new ArrayCollection();
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
     * @ORM\ManyToOne(targetEntity="App\Entity\Client\Client", inversedBy="pos")
     * @ORM\JoinColumn(name="client_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $client;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Customer\Customer", inversedBy="orders", cascade={"persist"})
     * @ORM\JoinColumn(name="customer_id", referencedColumnName="id", onDelete="CASCADE", nullable=true)
     */
    private $customer;

    /**
     * @var float
     *
     * @Assert\Range(
     *      min = "0.01",
     *      max = "1000000",
     *      minMessage = "Price must be at least $0 to enter",
     *      maxMessage = "Price cannot be taller than ${{ limit }} to enter"
     * )
     * @ORM\Column(name="total", type="decimal", precision=7, scale=2)
     * @Assert\NotBlank(message="validation.form.required")
     */
    private $total;

    /**
     * @var float
     *
     * @Assert\Range(
     *      min = "0.01",
     *      max = "1000000",
     *      minMessage = "Price must be at least $0 to enter",
     *      maxMessage = "Price cannot be taller than ${{ limit }} to enter"
     * )
     * @ORM\Column(name="received_amount", type="decimal", precision=7, scale=2)
     * @Assert\NotBlank(message="validation.form.required")
     */
    private $receivedAmount;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Customer\POSProduct", mappedBy="pos", cascade={"persist", "remove"})
     * @Assert\NotBlank(message="validation.form.required")
     */
    private $products;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $createdAt;

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
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * @param float $total
     */
    public function setTotal(float $total)
    {
        $this->total = $total;
    }

    /**
     * @return float
     */
    public function getReceivedAmount()
    {
        return $this->receivedAmount;
    }

    /**
     * @param float $receivedAmount
     */
    public function setReceivedAmount(float $receivedAmount)
    {
        $this->receivedAmount = $receivedAmount;
    }

    /**
     * @return \Doctrine\Common\Collections\Collection|POSProduct[] $accesses
     */
    public function getProducts()
    {
        return $this->products;
    }

    /**
     * @param mixed $products
     */
    public function setProducts($products)
    {
        $this->products = $products;
    }

    /**
     * @param POSProduct $product
     * @return $this
     */
    public function addProducts(POSProduct $product)
    {
        $product->setPos($this);
        $this->products->add($product);

        return $this;
    }

    /**
     * @param POSProduct $product
     */
    public function removeShare(POSProduct $product)
    {
        $this->products->removeElement($product);
        $product->setPos(null);
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
}