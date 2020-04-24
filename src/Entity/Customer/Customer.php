<?php

namespace App\Entity\Customer;

use App\Form\Customer\NotificationType;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

use Doctrine\ORM\Mapping as ORM;

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
    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->addresses = new ArrayCollection();
        $this->shares = new ArrayCollection();
        $this->notifications = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->firstname . ' ' . $this->lastname;
    }

    /**
     * @Assert\Callback
     *
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
     * @var integer|null
     * @ORM\Column(name="delivery_day", type="integer", nullable=true, length=1)
     */
    private $deliveryDay;

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
     * Lead is a new customer, that haven`t shares yet
     *
     * @var boolean
     * @ORM\Column(name="is_lead", type="boolean")
     */
    private $isLead = 1;

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
     * @ORM\Column(name="testimonial", type="text", length=2500, nullable=true)
     */
    private $testimonial;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Customer\Email\EmailRecipient", mappedBy="customer", cascade={"all"}, orphanRemoval=true)
     */
    private $emails;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Customer\CustomerEmailNotify", mappedBy="customer", cascade={"all"}, orphanRemoval=true)
     */
    private $notifications;

    /**
     * @Assert\Valid()
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Customer\Address", mappedBy="customer", cascade={"all"}, orphanRemoval=true)
     */
    private $addresses;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Customer\CustomerShare", mappedBy="customer", cascade={"all"}, orphanRemoval=true)
     */
    private $shares;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Customer\Email\Feedback", mappedBy="customer", cascade={"all"}, orphanRemoval=true)
     */
    private $feedback;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Customer\Payment", mappedBy="customer", cascade={"all"}, orphanRemoval=true)
     */
    private $payments;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Customer\RenewalView", mappedBy="customer", cascade={"all"}, orphanRemoval=true)
     */
    private $renewalViews;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Customer\POS", mappedBy="customer", cascade={"all"}, orphanRemoval=true)
     */
    private $orders;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Customer\TestimonialRecipient", mappedBy="affiliate", cascade={"all"}, orphanRemoval=true)
     */
    private $testimonialRecipients;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Customer\CustomerReferral", mappedBy="customer", cascade={"all"}, orphanRemoval=true)
     */
    private $referrals;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Customer\Invoice", mappedBy="customer", cascade={"all"}, orphanRemoval=true)
     */
    private $invoices;

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
     * @return $this
     */
    public function setId($id)
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
     * @return mixed
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @return mixed
     */
    public function getClientName()
    {
        return $this->client->getName();
    }

    /**
     * @return mixed
     */
    public function getCountry()
    {
        return $this->client->getCountry();
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
     * @return int|null
     */
    public function getDeliveryDay()
    {
        return $this->deliveryDay;
    }

    /**
     * @param int|null $deliveryDay
     */
    public function setDeliveryDay($deliveryDay)
    {
        $this->deliveryDay = $deliveryDay;
    }

    /**
     * Return delivery day of customer in day of week format
     *
     * @return mixed
     */
    public function getWeekDay()
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

        $day = $this->deliveryDay ? array_flip($week)[$this->deliveryDay] : null;

        return $day;
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
    public function getIsLead()
    {
        return $this->isLead;
    }

    /**
     * @param mixed $isLead
     */
    public function setIsLead($isLead)
    {
        $this->isLead = $isLead;
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

    /**
     * @return \Doctrine\Common\Collections\Collection|Address $addresses
     */
    public function getAddresses()
    {
        return $this->addresses;
    }

    /**
     * @param mixed $addresses
     */
    public function setAddresses($addresses)
    {
        $this->addresses = $addresses;
    }

    /**
     * @param $type
     * @return Address|null
     */
    public function getAddressByType($type)
    {
        $address = null;

        $addresses = [];

        foreach ($this->addresses as $key => $customerAddress) {
            $addresses[] = $customerAddress;
        }

        // Count existed customer addresses
        $addressesNum = count($addresses);

        // If customer added some addresses
        if ($addressesNum) {
            switch (mb_strtoupper($type)) {
                case 'BILLING':
                    $typeId = 1;
                    break;
                case 'DELIVERY':
                    $typeId = 3;
                    break;
                case 'BILLING AND DELIVERY':
                    $typeId = 2;
                    break;
                default:
                    $typeId = null;
                    break;
            }

            if ($typeId) {
                // If added only one address and type id is 2 (Billing and Delivery), return first address to both types
                if ($addressesNum == 1 && $addresses[0]->getType() == 2) {
                    $address = $addresses[0];
                } else {
                    // If only one address exists, return first address if type id same as needed, or return null
                    if ($addressesNum == 1) {
                        $address = $addresses[0]->getType() == $typeId ? $addresses[0] : null;
                    } else {
                        // Return address if exists or null
                        $address = $addresses[0]->getType() == $typeId ? $addresses[0] : $addresses[1];
                    }
                }
            }
        }

        return $address;
    }

    /**
     * @param Address $address
     * @return $this
     */
    public function addAddress(Address $address)
    {
        $this->addresses->add($address);
        $address->setCustomer($this);

        return $this;
    }

    /**
     * @param Address $address
     */
    public function removeAddress(Address $address)
    {
        $this->addresses->removeElement($address);
        $address->setCustomer(null);
    }

    /**
     * @return \Doctrine\Common\Collections\Collection|CustomerShare[] $shares
     */
    public function getShares()
    {
        return $this->shares;
    }

    /**
     * @param mixed $shares
     */
    public function setShares($shares)
    {
        $this->shares = $shares;
    }

    /**
     * @param CustomerShare $share
     * @return $this
     */
    public function addShare(CustomerShare $share)
    {
        $this->shares->add($share);
        $share->setCustomer($this);

        return $this;
    }

    /**
     * @param CustomerShare $share
     */
    public function removeShare(CustomerShare $share)
    {
        $this->shares->removeElement($share);
        $share->setCustomer(null);
    }

    /**
     * @return mixed
     */
    public function getOrders()
    {
        return $this->orders;
    }

    /**
     * @param mixed $orders
     */
    public function setOrders($orders)
    {
        $this->orders = $orders;
    }

    /**
     * @return mixed
     */
    public function getTestimonial()
    {
        return $this->testimonial;
    }

    /**
     * @param mixed $testimonial
     */
    public function setTestimonial($testimonial)
    {
        $this->testimonial = $testimonial;
    }
}
