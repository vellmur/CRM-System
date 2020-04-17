<?php

namespace App\Entity\Customer;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class CustomShare
 *
 * Entity saves custom changes to share products
 *
 * @ORM\Table(name="share__custom")
 * @ORM\Entity()
 */
class CustomShare
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
     * @ORM\ManyToOne(targetEntity="App\Entity\Customer\ShareProduct", inversedBy="customShares", cascade={"all"})
     * @ORM\JoinColumn(name="share_product_id", referencedColumnName="id")
     */
    private $shareProduct;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Customer\Product", inversedBy="customShares")
     * @ORM\JoinColumn(name="product_id", referencedColumnName="id")
     */
    private $product;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Customer\CustomerShare", inversedBy="customShares")
     * @ORM\JoinColumn(name="share_id", referencedColumnName="id")
     */
    private $share;

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
    public function getShareProduct()
    {
        return $this->shareProduct;
    }

    /**
     * @param mixed $shareProduct
     */
    public function setShareProduct($shareProduct)
    {
        $this->shareProduct = $shareProduct;
    }

    /**
     * @return mixed
     */
    public function getShare()
    {
        return $this->share;
    }

    /**
     * @param mixed $share
     */
    public function setShare($share)
    {
        $this->share = $share;
    }
}