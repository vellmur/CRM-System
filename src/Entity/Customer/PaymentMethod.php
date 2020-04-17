<?php

namespace App\Entity\Customer;

use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PaymentMethod
 *
 * @ORM\Table(name="customer__payment_method")
 * @ORM\Entity()
 * @UniqueEntity(fields={"name"}, errorPath="name", message="validation.form.unique")
 */
class PaymentMethod
{
    public function __toString() {
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
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="label", type="string", length=255)
     */
    private $label;

    /**
     * @var int
     * @ORM\Column(name="gardener_price", type="integer", nullable=false)
     */
    private $gardenerPrice;

    /**
     * @var int
     * @ORM\Column(name="farmer_price", type="integer", nullable=false)
     */
    private $farmerPrice;

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
     * Set name
     *
     * @param string $name
     *
     * @return PaymentMethod
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set label
     *
     * @param string $label
     *
     * @return PaymentMethod
     */
    public function setLabel($label)
    {
        $this->label = $label;

        return $this;
    }

    /**
     * Get label
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Get gardenerPrice
     *
     * @return int
     */
    public function getGardenerPrice()
    {
        return $this->gardenerPrice;
    }

    /**
     * @param $gardenerPrice
     * @return $this
     */
    public function setGardenerPrice($gardenerPrice)
    {
        $this->gardenerPrice = $gardenerPrice;

        return $this;
    }

    /**
     * Get farmerPrice
     *
     * @return int
     */
    public function getFarmerPrice()
    {
        return $this->farmerPrice;
    }

    /**
     * @param $farmerPrice
     * @return $this
     */
    public function setFarmerPrice($farmerPrice)
    {
        $this->farmerPrice = $farmerPrice;

        return $this;
    }
}
