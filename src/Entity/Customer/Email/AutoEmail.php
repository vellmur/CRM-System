<?php

namespace App\Entity\Customer\Email;

use Doctrine\ORM\Mapping as ORM;
use App\Entity\Building\Building;
use App\Entity\Email\AutomatedEmailBase;

/**
 * Class AutoEmail
 *
 * @ORM\Table(name="email__auto")
 * @ORM\Entity()
 */
class AutoEmail extends AutomatedEmailBase implements BuildingAutoEmailInterface
{
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Building\Building", inversedBy="autoEmails")
     * @ORM\JoinColumn(name="building_id", referencedColumnName="id", nullable=false)
     */
    protected $building;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Customer\Email\CustomerEmail", mappedBy="automatedEmail")
     */
    private $emailLog;

    public const EMAIL_TYPES = [
        1 => 'activation',
        2 => 'weekly',
        3 => 'feedback',
        4 => 'renewal',
        5 => 'lapsed',
        6 => 'delivery_day'
    ];

    /**
     * @return Building
     */
    public function getBuilding() : Building
    {
        return $this->building;
    }

    /**
     * @param Building $building
     */
    public function setBuilding(Building $building)
    {
        $this->building = $building;
    }
}