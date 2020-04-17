<?php

namespace App\Entity\Customer;

use Doctrine\ORM\Mapping as ORM;

/**
 * Address
 *
 * @ORM\Table(name="customer__location_workdays")
 * @ORM\Entity(repositoryClass="App\Repository\WorkdayRepository")
 *
 */
class Workday
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
     * @var Location
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Customer\Location", inversedBy="workdays")
     * @ORM\JoinColumn(name="location_id", referencedColumnName="id")
     */
    private $location;

    /**
     * @var integer
     * @ORM\Column(name="weekday", type="integer", length=1, nullable=false)
     */
    private $weekday;

    /**
     * @var string
     *
     * @ORM\Column(name="start_time", type="string", length=8, nullable=true)
     */
    private $startTime;

    /**
     * @var integer
     * @ORM\Column(name="duration", type="integer", length=2, nullable=true)
     */
    private $duration;

    /**
     * @var boolean
     * @ORM\Column(name="is_active", type="boolean", nullable=false)
     */
    private $isActive = false;

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
    public function setId(int $id)
    {
        $this->id = $id;
    }

    /**
     * @return Location
     */
    public function getLocation(): Location
    {
        return $this->location;
    }

    /**
     * @param Location $location
     */
    public function setLocation(Location $location)
    {
        $this->location = $location;
    }

    /**
     * @return int
     */
    public function getWeekday()
    {
        return $this->weekday;
    }

    /**
     * @param int $weekday
     */
    public function setWeekday($weekday)
    {
        // Weekday can't be null
        if (!$weekday) $weekday = $this->weekday;
        $this->weekday = $weekday;
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
     * @return array|bool
     */
    public function getWeekdayName()
    {
        return array_flip($this->getWeekDays())[$this->weekday];
    }

    /**
     * @return string
     */
    public function getStartTime()
    {
        return $this->startTime;
    }

    /**
     * @param string $startTime
     */
    public function setStartTime($startTime)
    {
        $this->startTime = $startTime;
    }


    /**
     * @return int
     */
    public function getDuration()
    {
        return $this->duration;
    }

    /**
     * @param int $duration
     */
    public function setDuration($duration)
    {
        $this->duration = $duration;
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->isActive;
    }

    /**
     * @param bool $isActive
     */
    public function setIsActive(bool $isActive)
    {
        $this->isActive = $isActive;
    }
}