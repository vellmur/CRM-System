<?php

namespace App\Entity\Customer;

use App\Entity\Client\Client;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Vendor
 *
 * @ORM\Table(name="customer__vendor")
 * @ORM\Entity(repositoryClass="App\Repository\VendorRepository")
 * @UniqueEntity(fields="name", errorPath="name", message="validation.form.unique")
 */
class Vendor
{
    public function __construct()
    {
        $this->contacts = new ArrayCollection();
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
     * @ORM\ManyToOne(targetEntity="App\Entity\Client\Client", inversedBy="vendors")
     * @ORM\JoinColumn(name="client_id", referencedColumnName="id", nullable=false)
     */
    private $client;

    /**
     * @var string
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     * @Assert\NotBlank(message="validation.form.required")
     */
    private $name;

    /**
     * @ORM\Column(name="category", type="array")
     */
    private $category = [];

    /**
     * @ORM\Column(name="order_day", type="array")
     */
    private $orderDay = [];

    /**
     * @var boolean
     * @ORM\Column(name="is_active", type="boolean", nullable=false)
     */
    private $isActive = true;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Customer\Address", inversedBy="vendor", cascade={"all"}, orphanRemoval=true)
     */
    private $address;
    
    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Customer\Contact", mappedBy="vendor", cascade={"all"}, orphanRemoval=true)
     */
    private $contacts;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Customer\VendorOrder", mappedBy="vendor", cascade={"all"}, orphanRemoval=true)
     */
    private $vendorOrders;

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
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = mb_strtoupper($name, "utf-8");;
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
     * @return mixed
     */
    public function getOrderDay()
    {
        return $this->orderDay;
    }

    /**
     * @param mixed $orderDay
     */
    public function setOrderDay($orderDay)
    {
        $this->orderDay = $orderDay;
    }

    /**
     * @return mixed
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @param mixed $address
     */
    public function setAddress(Address $address)
    {
        $address->setType(1);
        $address->setVendor($this);
        $this->address = $address;
    }

    /**
     * @return boolean
     */
    public function isActive()
    {
        return $this->isActive;
    }

    /**
     * @param boolean $isActive
     */
    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;
    }

    /**
     * @return \Doctrine\Common\Collections\Collection|Contact $contacts
     */
    public function getContacts()
    {
        return $this->contacts;
    }

    /**
     * @param Contact $contact
     */
    public function addContact(Contact $contact)
    {
        $contact->setVendor($this);
        $this->contacts->add($contact);
    }

    /**
     * @param Contact $contact
     */
    public function removeContact(Contact $contact)
    {
        $this->contacts->removeElement($contact);
    }
}