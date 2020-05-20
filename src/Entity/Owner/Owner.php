<?php

namespace App\Entity\Owner;

use Doctrine\ORM\Mapping as ORM;
use App\Entity\Building\Building;
use App\Form\Owner\NotificationType;
use Doctrine\Common\Collections\ArrayCollection;
use App\Validator\Constraints as AppAssert;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Owner
 *
 * @ORM\Table(name="owner", uniqueConstraints={
 *     @ORM\UniqueConstraint(name="owner_unique", columns={"building_id", "email"}),
 *     @ORM\UniqueConstraint(name="owner_phone_unique", columns={"building_id", "phone"})
 * })
 * @ORM\Entity(repositoryClass="App\Repository\MemberRepository")
 * @UniqueEntity(
 *     fields={"building", "email"},
 *     errorPath="email",
 *     message="validation.form.email_unique"
 * )
 * @UniqueEntity(
 *     fields={"building", "phone"},
 *     errorPath="phone",
 *     message="validation.form.unique"
 * )
 * @AppAssert\EmailOrPhoneRequired
 * @ORM\HasLifecycleCallbacks()
 */
class Owner
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
     * @ORM\ManyToOne(targetEntity="App\Entity\Building\Building", inversedBy="owners")
     * @ORM\JoinColumn(name="building_id", referencedColumnName="id")
     */
    private $building;

    /**
     * @ORM\Column(name="firstname", type="string", length=25)
     * @Assert\NotBlank(message="validation.form.required")
     */
    private $firstname;

    /**
     * @ORM\Column(name="lastname", type="string", length=25)
     * @Assert\NotBlank(message="validation.form.required")
     */
    private $lastname;

    /**
     * @ORM\Column(name="email", type="string", length=50, nullable=true)
     * @Assert\Email(message="validation.form.not_valid_email")
     */
    private $email;

    /**
     * @ORM\Column(name="phone", type="string", length=20, nullable=true)
     * @Assert\Length(max="20")
     */
    private $phone;

    /**
     * @ORM\Column(name="notes", type="text", length=2500, nullable=true)
     */
    private $notes;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="date")
     */
    private $createdAt;

    /**
     * @ORM\Column(name="token", type="string", length=50)
     */
    private $token;

    /**
     * This field helps to know did owner received an activation email
     * Active owner - this is a owner that have shares and start date of one from shares is past or coming soon
     * We send activation email just once for single owner, so this field changes just one time
     *
     * @var boolean
     * @ORM\Column(name="is_activated", type="boolean")
     */
    private $isActivated = 0;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Owner\Email\EmailRecipient", mappedBy="owner", cascade={"all"}, orphanRemoval=true)
     */
    private $emails;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Owner\OwnerEmailNotify", mappedBy="owner", cascade={"all"}, orphanRemoval=true)
     */
    private $notifications;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Owner\Apartment", inversedBy="residents", cascade={"all"})
     * @ORM\JoinColumn(name="apartment_id", referencedColumnName="id", nullable=false)
     * @Assert\Valid
     */
    protected $apartment;

    /**
     * Owner constructor.
     * @throws \Exception
     */
    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->notifications = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->firstname . ' ' . $this->lastname;
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
     * @param int $id
     * @return $this
     */
    public function setId(int $id)
    {
        $this->id = $id;

        return $this;
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
     * @return Building
     */
    public function getBuilding()
    {
        return $this->building;
    }

    /**
     * @return string|null
     */
    public function getCountry(): ?string
    {
        return $this->getBuilding()->getAddress() ? $this->getBuilding()->getAddress()->getCountry() : null;
    }

    /**
     * Get firstname
     *
     * @return string
     */
    public function getFirstname()
    {
        return $this->firstname;
    }

    /**
     * Set firstName
     *
     * @param string $firstName
     * @return Owner
     */
    public function setFirstName($firstName)
    {
        $this->firstname = mb_strtoupper($firstName, "utf-8");

        return $this;
    }

    /**
     * Get getLastName
     *
     * @return string
     */
    public function getLastName()
    {
        return $this->lastname;
    }

    /**
     * Set $lastName
     *
     * @param string $lastName
     * @return Owner
     */
    public function setLastName($lastName)
    {
        $this->lastname = mb_strtoupper($lastName, "utf-8");

        return $this;
    }

    /**
     * @return string
     */
    public function getFullName()
    {
        return $this->firstname || $this->lastname ? $this->firstname . ' ' . $this->lastname : null;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set email
     *
     * @param string $email
     * @return Owner
     */
    public function setEmail($email)
    {
        $this->email = strlen($email) ? trim(strtolower($email)) : null;

        return $this;
    }

    /**
     * Get phone
     *
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * Set phone
     *
     * @param string $phone
     * @return Owner
     */
    public function setPhone($phone)
    {
        //left only numbers in phone
        $this->phone = strlen($phone) ? preg_replace('/[^0-9.]+/', '', $phone) : null;

        return $this;
    }

    /**
     * Get notes
     *
     * @return string
     */
    public function getNotes()
    {
        return $this->notes;
    }

    /**
     * Set notes
     *
     * @param string $notes
     * @return Owner
     */
    public function setNotes($notes)
    {
        $this->notes = mb_strtoupper($notes);

        return $this;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return Owner
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

    /**
     * @return mixed
     */
    public function getApartment()
    {
        return $this->apartment;
    }

    /**
     * @param mixed $apartment
     */
    public function setApartment($apartment): void
    {
        $this->apartment = $apartment;
    }

    /**
     * @return mixed
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @param mixed $token
     */
    public function setToken($token)
    {
        $this->token = substr(sha1(openssl_random_pseudo_bytes(50)) . sha1($token), 0, 50);
    }

    /**
     * @return mixed
     */
    public function isActivated()
    {
        return $this->isActivated;
    }

    /**
     * @param mixed $isActivated
     */
    public function setIsActivated($isActivated)
    {
        $this->isActivated = $isActivated;
    }

    /**
     * @return \Doctrine\Common\Collections\Collection|NotificationType $notifications
     */
    public function getNotifications()
    {
        return $this->notifications;
    }

    /**
     * @param mixed $notifications
     */
    public function setNotifications($notifications)
    {
        $this->notifications = $notifications;
    }

    /**
     * @param OwnerEmailNotify $notification
     * @return $this
     */
    public function addNotification(OwnerEmailNotify $notification)
    {
        $this->notifications->add($notification);
        $notification->setOwner($this);

        return $this;
    }
}
