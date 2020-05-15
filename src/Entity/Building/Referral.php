<?php

namespace App\Entity\Building;

use Symfony\Component\Validator\Constraints as Assert;

use Doctrine\ORM\Mapping as ORM;

/**
 * Referral
 *
 * @ORM\Table(name="building__referral", uniqueConstraints={@ORM\UniqueConstraint(name="referral_unique", columns={"building_id", "affiliate_id"})})
 * @ORM\Entity(repositoryClass="App\Repository\ReferralRepository")
 */

class Referral
{
    public function __construct()
    {
        $this->isPaid = false;
        $this->createdAt = new \DateTime();
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
     * @ORM\OneToOne(targetEntity="App\Entity\Building\Building")
     */
    private $building;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Building\Affiliate", inversedBy="referrals")
     * @ORM\JoinColumn(name="affiliate_id", referencedColumnName="id", nullable=false)
     */
    private $affiliate;

    /**
     * @var boolean
     * @ORM\Column(name="is_paid", type="boolean", nullable=false)
     */
    private $isPaid;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="date")
     */
    private $createdAt;


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
     * @param $building
     * @return $this
     */
    public function setBuilding($building)
    {
        $this->building = $building;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getBuilding()
    {
        return $this->building;
    }

    /**
     * @param $affiliate
     * @return $this
     */
    public function setAffiliate($affiliate)
    {
        $this->affiliate = $affiliate;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getAffiliate()
    {
        return $this->affiliate;
    }


    /**
     * @param $isPaid
     * @return $this
     */
    public function setIsPaid($isPaid)
    {
        $this->isPaid = $isPaid;

        return $this;
    }

    /**
     * Get isPaid
     *
     * @return string
     */
    public function getIsPaid()
    {
        return $this->isPaid;
    }

    /**
     * @param $createdAt
     * @return $this
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }
}