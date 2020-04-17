<?php

namespace App\Entity\Customer;

use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class ShareProduct
 *
 * @ORM\Table(name="share__products")
 * @ORM\Entity(repositoryClass="App\Repository\ShareProductsRepository")
 */
class ShareProduct
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
     * @ORM\ManyToOne(targetEntity="App\Entity\Customer\CustomerOrders", inversedBy="shareProducts")
     * @ORM\JoinColumn(name="customer_order", referencedColumnName="id", nullable=true)
     */
    private $customerOrder;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Customer\VendorOrder", inversedBy="shareProducts")
     * @ORM\JoinColumn(name="vendor_order", referencedColumnName="id", onDelete="CASCADE", nullable=true)
     */
    private $vendorOrder;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Customer\Product", inversedBy="shareProduct")
     * @ORM\JoinColumn(name="product_id", referencedColumnName="id", onDelete="CASCADE")
     * @Assert\NotBlank(message="validation.form.required")
     */
    private $product;

    /**
     * @var float
     *
     * @ORM\Column(name="price", type="decimal", precision=7, scale=2)
     */
    private $price;

    /**
     * @var float
     *
     * @ORM\Column(name="weight", type="decimal", precision=7, scale=2)
     */
    private $weight;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Customer\CustomShare", mappedBy="shareProduct", cascade={"all"})
     */
    private $customShares;

    /**
     * @var int
     *
     * @ORM\Column(name="qty", type="integer")
     * @Assert\Range(min = "1", max="10000",
     *      minMessage = "validation.form.minlength",
     *      maxMessage = "validation.form.maxlength")
     */
    private $qty;

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
     * @return CustomerOrders
     */
    public function getCustomerOrder()
    {
        return $this->customerOrder;
    }

    /**
     * @param mixed $customerOrder
     */
    public function setCustomerOrder($customerOrder)
    {
        $this->customerOrder = $customerOrder;
    }

    /**
     * @return mixed
     */
    public function getVendorOrder()
    {
        return $this->vendorOrder;
    }

    /**
     * @param mixed $vendorOrder
     */
    public function setVendorOrder($vendorOrder)
    {
        $this->vendorOrder = $vendorOrder;
    }

    /**
     * @return Product
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * @param mixed $product
     */
    public function setProduct($product)
    {
        $this->product = $product;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->getProduct()->getName();
    }


    /**
     * @return int
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param $price
     */
    public function setPrice($price)
    {
        $this->price = $price;
    }

    /**
     * @return float
     */
    public function getWeight()
    {
        return $this->weight;
    }

    /**
     * @param $weight
     */
    public function setWeight($weight)
    {
        $this->weight = $weight;
    }

    /**
     * @return int
     */
    public function getQty()
    {
        return $this->qty;
    }

    /**
     * @param int $qty
     */
    public function setQty($qty)
    {
        if ($qty <= 0) $qty = 1;

        $this->qty = $qty;
    }

    /**
     * @return mixed
     */
    public function getCustomShares()
    {
        return $this->customShares;
    }

    /**
     * @param mixed $customShares
     */
    public function setCustomOrder($customShares)
    {
        $this->customShares = $customShares;
    }
}