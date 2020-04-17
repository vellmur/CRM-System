<?php

namespace App\Entity\Customer;

use Doctrine\ORM\Mapping as ORM;

/**
 *
 * @ORM\Table(name="share__suspended_weeks")
 * @ORM\Entity(repositoryClass="App\Repository\SuspendWeekRepository")
 */
class SuspendedWeek
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
     * @ORM\ManyToOne(targetEntity="App\Entity\Client\Client", inversedBy="suspendedWeeks")
     * @ORM\JoinColumn(name="client_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $client;

    /**
     * @var integer
     *
     * @ORM\Column(name="week", type="integer", length=2)
     */
    private $week;

    /**
     * @var integer
     *
     * @ORM\Column(name="year", type="integer", length=4)
     */
    private $year;

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
     * @return int
     */
    public function getWeek(): int
    {
        return $this->week;
    }

    /**
     * @param int $week
     */
    public function setWeek(int $week)
    {
        $this->week = $week;
    }

    /**
     * @return int
     */
    public function getYear(): int
    {
        return $this->year;
    }

    /**
     * @param int $year
     */
    public function setYear(int $year)
    {
        $this->year = $year;
    }
}
