<?php

namespace App\Entity\Customer;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class Apartment
 * @package App\Entity\Customer
 *
 * @ORM\Table(name="customer__apartment", uniqueConstraints={
 *     @ORM\UniqueConstraint(name="aparment_unique", columns={"building_id", "number"})
 * })
 * @ORM\Entity()
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
     * @ORM\ManyToOne(targetEntity="App\Entity\Client\Client", inversedBy="apartments")
     * @ORM\JoinColumn(name="building_id", referencedColumnName="id", nullable=false)
     */
    private $building;

    /**
     * @ORM\Column(name="number", type="integer", length=5, unique=true, nullable=false)
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
        $this->number = $number;
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