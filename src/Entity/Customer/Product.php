<?php

namespace App\Entity\Customer;

use App\Entity\Building\Building;
use App\Entity\Media\Image;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class Product
 * 
 * @ORM\Table(name="pos__products")
 * @ORM\Entity(repositoryClass="App\Repository\ProductRepository")
 */
class Product
{
    public function __construct()
    {
        $this->tags = new ArrayCollection();
        $this->payByItem = 1;
    }

    public function __toString()
    {
        return $this->name;
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
     * @ORM\ManyToOne(targetEntity="App\Entity\Building\Building", inversedBy="products")
     * @ORM\JoinColumn(name="building_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $building;

    /**
     * @var string
     * @ORM\Column(name="name", type="string", length=255, nullable=true)
     * @Assert\NotBlank(message="validation.form.required")
     */
    private $name;

    /**
     * @ORM\Column(name="description", type="text", length=2500, nullable=true)
     */
    private $description;

    /**
     * @var float
     *
     * @Assert\Range(
     *      min = "0.01",
     *      max = "10000",
     *      minMessage = "Price must be at least $0 to enter",
     *      maxMessage = "Price cannot be taller than ${{ limit }} to enter"
     * )
     * @ORM\Column(name="price", type="decimal", precision=7, scale=2)
     * @Assert\NotBlank(message="validation.form.required")
     */
    private $price;

    /**
     * @var float
     *
     * @Assert\Range(
     *      min = "0.01",
     *      max = "10000",
     *      minMessage = "Price must be at least $0 to enter",
     *      maxMessage = "Price cannot be taller than ${{ limit }} to enter"
     * )
     * @ORM\Column(name="delivery_price", type="decimal", precision=7, scale=2, nullable=true)
     */
    private $deliveryPrice;

    /**
     * @var int
     * @ORM\Column(name="category", type="integer", length=1, nullable=true)
     */
    private $category;

    /**
     * @var float
     *
     * @ORM\Column(name="weight", type="decimal", precision=7, scale=2, nullable=true)
     */
    private $weight;

    /**
     * @var string
     * @ORM\Column(name="sku", type="string", length=16, nullable=true)
     */
    private $sku;

    /**
     * Pay by qty or by weight
     *
     * @var boolean
     * @ORM\Column(name="pay_by_item", type="boolean", options={"default": 1})
     */
    private $payByItem = 1;

    /**
     * Is product available from the pos page
     *
     * @var boolean
     * @ORM\Column(name="is_pos", type="boolean")
     */
    private $isPos = 0;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Media\Image")
     * @ORM\JoinColumn(name="image", referencedColumnName="id", onDelete="SET NULL", nullable=true)
     */
    private $image;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Customer\InvoiceProduct", mappedBy="product")
     */
    private $invoices;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Customer\POSProduct", mappedBy="product")
     */
    private $posProducts;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Customer\ProductTag", mappedBy="product")
     */
    private $tags;

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
     * @return float
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
    public function getDeliveryPrice()
    {
        return $this->deliveryPrice;
    }

    /**
     * @param float $deliveryPrice
     */
    public function setDeliveryPrice($deliveryPrice)
    {
        $this->deliveryPrice = $deliveryPrice;
    }

    /**
     * @return float
     */
    public function getWeight()
    {
        return $this->weight;
    }

    /**
     * @param float $weight
     */
    public function setWeight($weight)
    {
        $this->weight = $weight;
    }

    /**
     * @return mixed
     */
    public function getSku()
    {
        return $this->sku;
    }

    /**
     * @param mixed $sku
     */
    public function setSku($sku)
    {
        $this->sku = $sku;
    }

    /**
     * @return Building
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

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param mixed $category
     */
    public function setCategory($category)
    {
        $this->category = $category;
    }

    /**
     * @return bool
     */
    public function isPayByItem()
    {
        return $this->payByItem;
    }

    /**
     * @param bool $payByItem
     */
    public function setPayByItem($payByItem)
    {
        $this->payByItem = $payByItem;
    }

    /**
     * @return bool
     */
    public function isPos(): bool
    {
        return $this->isPos;
    }

    /**
     * @param bool $isPos
     */
    public function setIsPos(bool $isPos)
    {
        $this->isPos = $isPos;
    }

    /**
     * @return \Doctrine\Common\Collections\Collection|ProductTag[] $tags
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * @param mixed $tags
     */
    public function setTags($tags)
    {
        $this->tags = $tags;
    }

    /**
     * @param ProductTag $tag
     */
    public function removeTag(ProductTag $tag)
    {
        $this->tags->removeElement($tag);
    }

    /**
     * @return Image|null
     */
    public function getImage() :?Image
    {
        return $this->image;
    }

    /**
     * @param Image|null $image
     */
    public function setImage(?Image $image): void
    {
        $this->image = $image;
    }
}