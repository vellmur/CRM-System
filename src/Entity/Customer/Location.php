<?php

namespace App\Entity\Customer;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Location
 *
 * @ORM\Table(name="customer__location")
 * @ORM\Entity(repositoryClass="App\Repository\LocationRepository")
 * @UniqueEntity(fields={"client", "name"}, errorPath="name", message="validation.form.unique")
 */
class Location
{
    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->workdays = new ArrayCollection();
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
     * @ORM\ManyToOne(targetEntity="App\Entity\Client\Client", inversedBy="locations")
     * @ORM\JoinColumn(name="client_id", referencedColumnName="id", nullable=false)
     */
    private $client;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     * @Assert\NotBlank(message="validation.form.required")
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="street", type="string", length=255, nullable=true)
     */
    private $street;

    /**
     * @var string
     *
     * @ORM\Column(name="apartment", type="integer", length=10, nullable=true)
     */
    private $apartment;

    /**
     * @var string
     *
     * @ORM\Column(name="city", type="string", length=255, nullable=true)
     */
    private $city;

    /**
     * @var string
     *
     * @ORM\Column(name="region", type="string", length=255, nullable=true)
     */
    private $region;

    /**
     * @var string
     *
     * @ORM\Column(name="postalCode", type="integer", length=20, nullable=true)
     */
    private $postalCode;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=255, nullable=true)
     */
    private $description;

    /**
     * @var integer
     * @ORM\Column(name="type", type="integer", nullable=false, length=1)
     */
    private $type = 2;

    /**
     * @var boolean
     * @ORM\Column(name="is_active", type="boolean", nullable=false)
     */
    private $isActive = true;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="date")
     */
    private $createdAt;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Customer\Workday", mappedBy="location", cascade={"all"}, orphanRemoval=true)
     */
    private $workdays;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Customer\CustomerShare", mappedBy="location", cascade={"all"}, orphanRemoval=true)
     */
    private $shares;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Customer\Invoice", mappedBy="location", cascade={"all"}, orphanRemoval=true)
     */
    private $invoices;

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
     * @param $client
     * @return $this
     */
    public function setClient($client)
    {
        $this->client = $client;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @param $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = mb_strtoupper($name);

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
     * @param $street
     * @return $this
     */
    public function setStreet($street)
    {
        $this->street = (strlen($street) > 0) ? mb_strtoupper($street) : null;

        return $this;
    }

    /**
     * Get street
     *
     * @return string
     */
    public function getStreet()
    {
        return $this->street;
    }

    /**
     * @return string
     */
    public function getApartment()
    {
        return $this->apartment;
    }

    /**
     * @param string $apartment
     */
    public function setApartment($apartment)
    {
        $this->apartment = $apartment;
    }
    
    /**
     * @param $city
     * @return $this
     */
    public function setCity($city)
    {
        $this->city = strlen($city) > 0 ? mb_strtoupper($city) : null;

        return $this;
    }

    /**
     * Get city
     *
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @return string
     */
    public function getRegion()
    {
        return $this->region;
    }

    /**
     * @param string $region
     */
    public function setRegion($region)
    {
        $this->region = $region;
    }

    /**
     * @return string
     */
    public function getPostalCode()
    {
        return $this->postalCode;
    }

    /**
     * @param string $postalCode
     */
    public function setPostalCode($postalCode)
    {
        $this->postalCode = $postalCode;
    }

    /**
     * @param $description
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = strlen($description) > 0 ? mb_strtoupper($description) : null;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param $isActive
     * @return $this
     */
    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;

        return $this;
    }

    /**
     * @return bool
     */
    public function getIsActive()
    {
        return $this->isActive;
    }

    /**
     * @return mixed
     */
    public function getShares()
    {
        return $this->shares;
    }

    /**
     * @param mixed $shares
     */
    public function setShares($shares)
    {
        $this->shares = $shares;
    }

    /**
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param int $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return array
     */
    public function getTypes()
    {
        $types = [
            1 => 'Delivery',
            2 => 'Pickup'
        ];
        
        return $types;
    }

    /**
     * @return mixed
     */
    public function getTypeName() {
        return $this->getTypes()[$this->type];
    }

    /**
     * @param $name
     * @return mixed
     */
    public function setTypeByName($name)
    {
        $this->type = array_flip($this->getTypes())[ucfirst($name)];

        return $this;
    }

    /**
     * @return bool
     */
    public function isDelivery()
    {
        return $this->getTypeName() == 'Delivery' ? true : false;
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
     * @return \Doctrine\Common\Collections\Collection|Workday $workdays
     */
    public function getWorkdays()
    {
        return $this->workdays;
    }

    /**
     * @return mixed
     */
    public function getWeekDays()
    {
        $week = [
            'Monday' => 1,
            'Tuesday' => 2,
            'Wednesday' => 3,
            'Thursday' => 4,
            'Friday' => 5,
            'Saturday' => 6,
            'Sunday' => 7
        ];

        return $week;
    }

    /**
     * @return mixed
     */
    public function addWorkDays()
    {
       foreach ($this->getWeekDays() as $day) {
           $workday = new Workday();
           $workday->setWeekday($day);
           $this->addWorkday($workday);
       }

        return $this;
    }

    /**
     * @param mixed $workdays
     */
    public function setWorkdays($workdays)
    {
        $this->workdays = $workdays;
    }

    /**
     * @param Workday $workday
     * @return $this
     */
    public function addWorkday(Workday $workday)
    {
        $this->workdays->add($workday);
        $workday->setLocation($this);

        return $this;
    }

    /**
     * @param Workday $workday
     */
    public function removeWorkday(Workday $workday)
    {
        $this->workdays->removeElement($workday);
        $workday->setLocation(null);
    }
}
