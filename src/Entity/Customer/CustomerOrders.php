<?php

namespace App\Entity\Customer;

use App\Entity\Client\Client;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * CustomerOrders
 *
 * @ORM\Table(name="customer__orders")
 * @ORM\Entity(repositoryClass="App\Repository\CustomerOrdersRepository")
 */
class CustomerOrders
{

    public function __construct()
    {
        $this->shareProduct = new ArrayCollection();
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
     * @ORM\ManyToOne(targetEntity="App\Entity\Client\Client", inversedBy="customerOrders")
     * @ORM\JoinColumn(name="client_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $client;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Customer\Share", inversedBy="customerOrders")
     * @ORM\JoinColumn(name="share_id", referencedColumnName="id", onDelete="CASCADE")
     * @Assert\NotBlank(message="validation.form.required")
     */
    private $share;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="start_date", type="date")
     * @Assert\NotBlank(message="validation.form.required")
     */
    private $startDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="end_date", type="date")
     * @Assert\NotBlank(message="validation.form.required")
     */
    private $endDate;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Customer\ShareProduct", mappedBy="customerOrder", cascade={"all"})
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
     * @return Client
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

    /**
     * @return \DateTime
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * @param \DateTime $startDate
     */
    public function setStartDate($startDate)
    {
        $this->startDate = $startDate;
    }

    /**
     * @return mixed
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * @param mixed $endDate
     */
    public function setEndDate($endDate)
    {
        $this->endDate = $endDate;
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