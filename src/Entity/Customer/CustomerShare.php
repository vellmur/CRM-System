<?php

namespace App\Entity\Customer;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;

use Doctrine\ORM\Mapping as ORM;

/**
 * CustomerShare
 *
 * @ORM\Table(name="share__customer")
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Entity(repositoryClass="App\Repository\CustomerShareRepository")
 */
class CustomerShare
{
    public function __construct()
    {
        $this->pickups = new ArrayCollection();
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
     * @ORM\ManyToOne(targetEntity="App\Entity\Customer\Customer", inversedBy="shares")
     * @ORM\JoinColumn(name="customer_id", referencedColumnName="id", nullable=false)
     */
    private $customer;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Customer\Share", inversedBy="shares")
     * @ORM\JoinColumn(name="share_id", referencedColumnName="id", nullable=false)
     */
    private $share;

    /**
     * @ORM\Column(name="type", type="integer", length=1, nullable=false)
     */
    private $type;

    /**
     * @ORM\Column(name="status", type="integer", length=1, nullable=false)
     */
    private $status = 3;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="start_date", type="date", nullable=false)
     * @Assert\NotBlank(message="validation.form.required")
     */
    private $startDate;

    /**
     * This field holds number of only ACTIVE pickups (not skipped/suspended) form start date to end date
     *
     * @ORM\Column(name="pickups_num", type="integer", length=2, nullable=false)
     */
    private $pickupsNum;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="renewal_date", type="date", nullable=false)
     */
    private $renewalDate;

    /**
     * @ORM\Column(name="pickup_day", type="integer", length=1, nullable=true)
     * @Assert\NotBlank(message="validation.form.required")
     */
    private $pickupDay;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Customer\Location", inversedBy="shares")
     * @ORM\JoinColumn(name="location", referencedColumnName="id", nullable=true)
     * @Assert\NotBlank(message="validation.form.required")
     */
    private $location;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Customer\Pickup", mappedBy="share", cascade={"all"}, orphanRemoval=true)
     */
    private $pickups;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Customer\CustomShare", mappedBy="share", cascade={"all"}, orphanRemoval=true)
     */
    private $customShares;

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
     * @return Customer
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
     * @return mixed
     */
    public function getShareName()
    {
        return $this->getShare()->getName();
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param mixed $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return mixed
     */
    public function getTypeName()
    {
        $types = [
            1 => 'MEMBER',
            2 => 'PATRON'
        ];

        return $this->type ? $types[$this->type] : null;
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
    public function getPickupsNum()
    {
        return $this->pickupsNum;
    }

    /**
     * @param mixed $pickupsNum
     */
    public function setPickupsNum($pickupsNum)
    {
        $this->pickupsNum = $pickupsNum;
    }

    /**
     * @return mixed
     */
    public function getRenewalDate()
    {
        return $this->renewalDate;
    }


    /**
     * @param \DateTime $date
     */
    public function setRenewalDate($date)
    {
        $this->renewalDate = $date;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     *
     * Share statuses names and ids sorted by logical period in lifetime of share
     *
     * PENDING - share is pending, when start date will be after 8 days or more
     * ACTIVE - share is active, when start date will be after 7 days or less
     * LAPSED - share is lapsed, when renewal date if past (status sets to renewal after one day of last share pickup),
     * shares remaining - 0
     *
     * @return array
     */
    public function getStatuses()
    {
        $statuses = [
            1 => 'PENDING',
            2 => 'ACTIVE',
            3 => 'LAPSED'
        ];

        return $statuses;
    }

    /**
     * @return mixed
     */
    public function getStatusName()
    {
        return $this->status ? $this->getStatuses()[$this->status] : 'PENDING';
    }

    /**
     * @param $name
     * @return mixed
     */
    public function getStatusId($name)
    {
        return array_flip($this->getStatuses())[$name];
    }

    /**
     * @param $name
     */
    public function setStatusByName($name)
    {
        $this->status = $this->getStatusId(mb_strtoupper($name));
    }

    /**
     * @param $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return mixed
     */
    public function getPickUpDay()
    {
        return $this->pickupDay;
    }

    /**
     * @param mixed $pickUpDay
     */
    public function setPickUpDay($pickUpDay)
    {
        if ($pickUpDay < 1 || $pickUpDay > 7) $pickUpDay = null;
        $this->pickupDay = $pickUpDay;
    }

    /**
     * @return mixed
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * @param mixed $location
     */
    public function setLocation($location)
    {
        $this->location = $location;
    }

    /**
     * @return array
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
    public function getShareDay()
    {
        $week = array_flip($this->getWeekDays());

        $day = $this->pickupDay ? $week[$this->pickupDay] : null;

        return $day;
    }

    /**
     * @return \Doctrine\Common\Collections\Collection|Pickup $pickups
     */
    public function getPickups()
    {
        return $this->pickups;
    }

    /**
     * @param mixed $pickups
     */
    public function setPickups($pickups)
    {
        $this->pickups = $pickups;
    }

    /**
     * @param Pickup $pickup
     * @return $this
     */
    public function addPickup(Pickup $pickup)
    {
        $this->pickups->add($pickup);
        $pickup->setShare($this);

        return $this;
    }

    /**
     * @param Pickup $pickup
     */
    public function removePickup(Pickup $pickup)
    {
        $this->pickups->removeElement($pickup);
        $pickup->setShare(null);
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
    public function setCustomShares($customShares)
    {
        $this->customShares = $customShares;
    }
}
