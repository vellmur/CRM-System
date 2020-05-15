<?php

namespace App\Entity\Customer;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Class Apartment
 * @package App\Entity\Customer
 *
 * @ORM\Table(name="customer__apartment", uniqueConstraints={
 *     @ORM\UniqueConstraint(name="aparment_unique", columns={"building_id", "number"})
 * })
 * @ORM\Entity()
 * @UniqueEntity(
 *     fields={"building", "number"},
 *     errorPath="number",
 *     message="validation.form.unique"
 * )
 */
class Apartment
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
     * @ORM\ManyToOne(targetEntity="App\Entity\Building\Building", inversedBy="apartments")
     * @ORM\JoinColumn(name="building_id", referencedColumnName="id", nullable=false)
     */
    private $building;

    /**
     * @ORM\Column(name="number", type="string", length=10, unique=true, nullable=false)
     * @Assert\NotBlank(message="validation.form.required")
     */
    private $number;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Customer\Customer", mappedBy="apartment", cascade={"all"})
     */
    private $residents;

    /**
     * @return int
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getBuilding()
    {
        return $this->building;
    }

    /**
     * @param mixed $building
     */
    public function setBuilding($building): void
    {
        $this->building = $building;
    }

    /**
     * @return int|null
     */
    public function getNumber() : ?int
    {
        return $this->number;
    }

    /**
     * @param int|null $number
     */
    public function setNumber(?int $number): void
    {
        $this->number = trim($number);
    }

    /**
     * @return mixed
     */
    public function getResidents()
    {
        return $this->residents;
    }

    /**
     * @param mixed $residents
     */
    public function setResidents($residents): void
    {
        $this->residents = $residents;
    }
}