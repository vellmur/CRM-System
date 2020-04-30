<?php

namespace App\Entity\Customer;

use Doctrine\ORM\Mapping as ORM;
use App\Entity\Client\Client;
use App\Form\Customer\NotificationType;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Customer
 *
 * @ORM\Table(name="customer", uniqueConstraints={
 *     @ORM\UniqueConstraint(name="customer_unique", columns={"client_id", "email"}),
 *     @ORM\UniqueConstraint(name="customer_phone_unique", columns={"client_id", "phone"})
 * })
 * @ORM\Entity(repositoryClass="App\Repository\MemberRepository")
 * @UniqueEntity(
 *     fields={"client", "email"},
 *     errorPath="email",
 *     message="validation.form.email_unique"
 * )
 * @UniqueEntity(
 *     fields={"client", "phone"},
 *     errorPath="phone",
 *     message="validation.form.unique"
 * )
 */
class Customer
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
     * @ORM\ManyToOne(targetEntity="App\Entity\Client\Client", inversedBy="customers")
     * @ORM\JoinColumn(name="client_id", referencedColumnName="id")
     */
    private $client;

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
     * @Assert\Email(message = "validation.form.not_valid_email")
     */
    private $email;

    /**
     * @ORM\Column(name="phone", type="string", length=20, nullable=true)
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
     *
     * This field helps to know did customer received an activation email
     * Active customer - this is a customer that have shares and start date of one from shares is past or coming soon
     * We send activation email just once for single customer, so this field changes just one time
     *
     * @var boolean
     * @ORM\Column(name="is_activated", type="boolean")
     */
    private $isActivated = 0;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Customer\Email\EmailRecipient", mappedBy="customer", cascade={"all"}, orphanRemoval=true)
     */
    private $emails;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Customer\CustomerEmailNotify", mappedBy="customer", cascade={"all"}, orphanRemoval=true)
     */
    private $notifications;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Customer\Apartment", inversedBy="residents", cascade={"all"})
     * @ORM\JoinColumn(name="apartment_id", referencedColumnName="id", nullable=false)
     * @Assert\Valid
     */
    protected $apartment;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Customer\Payment", mappedBy="customer", cascade={"all"}, orphanRemoval=true)
     */
    private $payments;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Customer\POS", mappedBy="customer", cascade={"all"}, orphanRemoval=true)
     */
    private $orders;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Customer\Invoice", mappedBy="customer", cascade={"all"}, orphanRemoval=true)
     */
    private $invoices;

    /**
     * Customer constructor.
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
     * @Assert\Callback
     * @param ExecutionContextInterface $context
     */
    public function validate(ExecutionContextInterface $context)
    {
        if (!$this->email && !$this->phone) {
            $context->buildViolation('validation.form.required')
                ->setTranslationDomain('validators')
                ->atPath('email')
                ->addViolation();

            $context->buildViolation('validation.form.required')
                ->setTranslationDomain('validators')
                ->atPath('phone')
                ->addViolation();
        }
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
     * @param $client
     * @return $this
     */
    public function setClient($client)
    {
        $this->client = $client;

        return $this;
    }

    /**
     * @return Client
     */
    public function getClient()
    {
        return $this->client;
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
     * Set firstname
     *
     * @param string $firstname
     * @return Customer
     */
    public function setFirstname($firstname)
    {
        $this->firstname = mb_strtoupper($firstname, "utf-8");

        return $this;
    }

    /**
     * Get lastname
     *
     * @return string
     */
    public function getLastname()
    {
        return $this->lastname;
    }

    /**
     * Set lastname
     *
     * @param string $lastname
     * @return Customer
     */
    public function setLastname($lastname)
    {
        $this->lastname = mb_strtoupper($lastname, "utf-8");

        return $this;
    }

    /**
     * @return string
     */
    public function getFullname()
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
     * @return Customer
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
     * @return Customer
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
     * @return Customer
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
     * @return Customer
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
     * @param CustomerEmailNotify $notification
     * @return $this
     */
    public function addNotification(CustomerEmailNotify $notification)
    {
        $this->notifications->add($notification);
        $notification->setCustomer($this);

        return $this;
    }
}
