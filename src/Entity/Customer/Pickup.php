<?php

namespace App\Entity\Customer;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

use Doctrine\ORM\Mapping as ORM;

/**
 *
 * @ORM\Table(name="share__pickups")
 * @ORM\Entity(repositoryClass="App\Repository\PickupRepository")
 * @UniqueEntity(fields={"share", "date"}, errorPath="name", message="validation.form.unique")
 */
class Pickup
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
     * @ORM\ManyToOne(targetEntity="App\Entity\Customer\CustomerShare", inversedBy="pickups")
     * @ORM\JoinColumn(name="share_id", referencedColumnName="id", nullable=true)
     */
    private $share;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="date")
     * @Assert\NotBlank(message="validation.form.required")
     */
    private $date;

    /**
     *
     * @var boolean
     * @ORM\Column(name="skipped", type="boolean")
     */
    private $skipped = 0;

    /**
     *
     * @var boolean
     * @ORM\Column(name="is_suspended", type="boolean")
     */
    private $isSuspended = 0;

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
     * @param $share
     * @return $this
     */
    public function setShare($share)
    {
        $this->share = $share;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getShare()
    {
        return $this->share;
    }

    /**
     * @return CustomerOrders
     */
    public function getPickupOrder()
    {
        return $this->getShare()->getShare()->getCustomerOrders()[0];
    }

    /**
     * @param $date
     * @return $this
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @return mixed
     */
    public function isSkipped()
    {
        return $this->skipped;
    }

    /**
     * @param mixed $skipped
     */
    public function setSkipped($skipped)
    {
        $this->skipped = $skipped;
    }

    /**
     * @return bool
     */
    public function isSuspended(): bool
    {
        return $this->isSuspended;
    }

    /**
     * @param bool $isSuspended
     */
    public function setIsSuspended(bool $isSuspended)
    {
        $this->isSuspended = $isSuspended;
    }
}
