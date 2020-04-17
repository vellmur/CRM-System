<?php

namespace App\Entity\Client;

use App\Entity\Customer\Email\AutoEmail;
use App\Entity\Customer\Location;
use App\Entity\Customer\Customer;
use App\Entity\Customer\Share;
use App\Entity\Customer\Tag;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\Common\Collections\ArrayCollection;

use Doctrine\ORM\Mapping as ORM;

/**
 * Client
 *
 * @ORM\Table(name="client")
 * @ORM\Entity(repositoryClass="App\Repository\ClientRepository")
 * @UniqueEntity(fields={"name"}, errorPath="name", message="validation.form.unique", groups={"register_validation", "profile_validation"})
 */
class Client
{
    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->accesses =  new ArrayCollection();
        $this->transactions = new ArrayCollection();
        $this->location =  new ArrayCollection();
        $this->notes = new ArrayCollection();
        $this->modulesSettings = new ArrayCollection();
        $this->paymentSettings = new ArrayCollection();
        $this->tags = new ArrayCollection();
        $this->team = new ArrayCollection();
        $this->posts = new ArrayCollection();

        $this->token = substr(base_convert(sha1(uniqid(mt_rand())), 16, 36), 0, 30);
    }

    public function __toString()
    {
        return $this->name;
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
     * @var string
     * @ORM\Column(name="name", type="string", length=255, unique=true, nullable=false)
     * @Assert\NotBlank(message="validation.form.required", groups={"register_validation", "profile_validation"})
     */
    private $name;

    /**
     * @ORM\Column(name="email", type="string", length=50, nullable=true)
     * @Assert\Email(message = "validation.form.not_valid_email")
     * @Assert\NotBlank(message="validation.form.required", groups={"profile_validation"})
     */
    private $email;

    /**
     * @var integer
     * @ORM\Column(name="level", type="integer", length=1, nullable=false)
     */
    private $level = 1;

    /**
     * @var integer
     * @ORM\Column(name="weight_format", type="integer", length=1, nullable=false)
     */
    private $weightFormat = 2;

    /**
     * @var int
     *
     * @ORM\Column(name="currency", type="integer", length=2)
     */
    private $currency = 4;

    /**
     * @var string
     * @ORM\Column(name="country", type="string", length=2, nullable=true)
     */
    private $country;

    /**
     * @ORM\Column(name="postal_code", type="string", length=10, nullable=true)
     */
    private $postalCode;

    /**
     * @ORM\Column(name="region", type="integer", length=10, nullable=true)
     */
    private $region;

    /**
     * @ORM\Column(name="city", type="integer", length=10, nullable=true)
     */
    private $city;

    /**
     * @var int
     *
     * @ORM\Column(name="timezone", type="string", length=30, nullable=true)
     */
    protected $timezone;

    /**
     * @var float
     *
     * @ORM\Column(name="delivery_price", type="decimal", precision=7, scale=2)
     */
    private $deliveryPrice = 0;

    /**
     * @var string
     * @ORM\Column(name="order_time", length=5, type="string", length=255, nullable=true)
     */
    private $orderTime;

    /**
     * Does orders on delivery allowed on the same day, or only on the day before
     *
     * @var boolean
     * @ORM\Column(name="same_day_orders", type="boolean", nullable=false)
     */
    private $sameDayOrders = true;

    /**
     * @ORM\Column(name="token", type="string", length=30)
     */
    private $token;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="date")
     */
    private $createdAt;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Customer\Location", mappedBy="client", cascade={"remove"})
     */
    private $locations;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Client\ModuleAccess", mappedBy="client", cascade={"remove"})
     */
    private $accesses;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Client\Team", mappedBy="client", cascade={"remove"})
     */
    private $team;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Client\Subscription", mappedBy="client", cascade={"remove"})
     */
    private $subscriptions;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Customer\Customer", mappedBy="client", cascade={"remove"})
     */
    private $customers;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Customer\Vendor", mappedBy="client", cascade={"remove"})
     */
    private $vendors;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Customer\Share", mappedBy="client", cascade={"remove"})
     */
    private $shares;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Client\Affiliate", mappedBy="client", cascade={"remove"})
     */
    private $affiliate;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Customer\CustomerOrders", mappedBy="client", cascade={"remove"})
     */
    private $customerOrders;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Customer\VendorOrder", mappedBy="client", cascade={"remove"})
     */
    private $vendorOrders;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Customer\Email\CustomerEmail", mappedBy="client", cascade={"remove"})
     */
    private $emails;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Customer\Email\AutoEmail", mappedBy="client", cascade={"all"})
     */
    private $autoEmails;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Customer\SuspendedWeek", mappedBy="client", cascade={"all"})
     */
    private $suspendedWeeks;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Client\Merchant", mappedBy="client", cascade={"all"})
     */
    private $merchants;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Customer\RenewalView", mappedBy="client", cascade={"all"}, orphanRemoval=true)
     */
    private $renewalViews;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Master\Email\Recipient", mappedBy="client", cascade={"remove"})
     */
    private $emailRecipients;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Client\Post", mappedBy="client", cascade={"persist", "remove"})
     */
    private $posts;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Media\Image", mappedBy="client", cascade={"persist", "remove"})
     */
    private $images;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Client\ModuleSetting", mappedBy="client", cascade={"persist", "remove"})
     */
    private $modulesSettings;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Client\PaymentSettings", mappedBy="client", cascade={"persist", "remove"})
     */
    private $paymentSettings;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Customer\POS", mappedBy="client", cascade={"persist", "remove"})
     */
    private $pos;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Customer\Product", mappedBy="client", cascade={"remove"})
     */
    private $products;

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
     * Set name
     *
     * @param string $name
     *
     * @return Client
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }


    /**
     * @param mixed $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @return mixed
     */
    public function getContactEmail()
    {
        $email = $this->getEmail() ? $this->getEmail() : $this->getTeam()[0]->getUser()->getEmail();

        return $email;
    }

    public function getDateFormat()
    {
        return $this->getTeam()[0]->getUser()->getTwigFormatDate();
    }

    /**
     * @return mixed
     */
    public function getTwigFormatDate()
    {
        $format = $this->getTeam()[0]->getUser()->getTwigFormatDate();

        return $format;
    }

    /**
     * @return User|null
     */
    public function getOwner()
    {
        return $this->getTeam() ? $this->getTeam()[0]->getUser() : null;;
    }

    /**
     * @return mixed
     */
    public function getOwnerDateFormat()
    {
        $format = $this->getTeam()[0]->getUser()->getDateFormatName();

        return $format;
    }

    /**
     * @param $weightFormat
     * @return $this
     */
    public function setWeightFormat($weightFormat)
    {
        $this->weightFormat = $weightFormat;

        return $this;
    }

    /**
     * @return int
     */
    public function getWeightFormat()
    {
        return $this->weightFormat;
    }

    /**
     * @return mixed
     */
    public function getWeightName(){
        $weightFormats = ['Kg', 'Lbs'];

        return $weightFormats[$this->weightFormat - 1];
    }

    /**
     * @return int
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * @param int $currency
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;
    }

    /**
     * @return array
     */
    public static function getCurrencies()
    {
        $currencies = [
            'EUR' => 1,
            'RUB' => 2,
            'UAH' => 3,
            'USD' => 4,
            'CAD' => 5,
            'GBP' => 6,
            'AUD' => 7,
            'NZF' => 8,
            'NOK' => 9,
            'SEK' => 10,
            'TRY' => 11,
            'CHF' => 12,
            'DKK' => 13
        ];

        return $currencies;
    }

    /**
     * @return array
     */
    public function getCurrencyCodes()
    {
        return [
            'EUR' => '&#8364;',
            'RUB' => '&#8381;',
            'UAH' => '&#8372;',
            'USD' => '&#36;',
            'CAD' => '&#36;',
            'GBP' => '&#65505;',
            'AUD' => '&#36;',
            'NZF' => '&#36;',
            'NOK' => '&#107;&#114; ',
            'SEK' => '&#107;&#114; ',
            'TRY' => '&#8378;',
            'CHF' => '&#8355;',
            'DKK' => '&#107;&#114; ',
        ];
    }

    /**
     * @return string
     */
    public function getCurrencyFormat()
    {
        $currencyNum = $this->getCurrency() ?? 4;
        $currency = array_flip(self::getCurrencies())[$currencyNum];
        $symbol = $this->getCurrencyCodes()[$currency];

        return new \Twig\Markup($symbol, 'UTF-8');
    }

    /**
     * @return mixed
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @param $country
     * @return $this
     */
    public function setCountry($country)
    {
        $this->country = $country;

        return $this;
    }

    /**
     * @return array
     */
    public function getRegions()
    {
        $regions = $this->country ? $this->country->getRegions() : [];

        return $regions;
    }

    /**
     * @return mixed
     */
    public function getPostalCode()
    {
        return $this->postalCode;
    }

    /**
     * @param mixed $postalCode
     */
    public function setPostalCode($postalCode)
    {
        $this->postalCode = $postalCode;
    }

    /**
     * Get region
     *
     * @return string
     */
    public function getRegion()
    {
        return $this->region;
    }

    /**
     * @param $region
     * @return $this
     */
    public function setRegion($region)
    {
        $this->region = $region;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @param $city
     * @return $this
     */
    public function setCity($city)
    {
        $this->city = $city;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTimezone()
    {
        return $this->timezone ? $this->timezone : date_default_timezone_get();
    }

    /**
     * @param mixed $timezone
     */
    public function setTimezone($timezone)
    {
        $this->timezone = $timezone;
    }

    /**
     * @return mixed
     */
    public function getDeliveryPrice()
    {
        return $this->deliveryPrice;
    }

    /**
     * @param mixed $deliveryPrice
     */
    public function setDeliveryPrice($deliveryPrice)
    {
        $this->deliveryPrice = $deliveryPrice;
    }

    /**
     * @return mixed
     */
    public function getOrderTime()
    {
        return $this->orderTime ? $this->orderTime : '20:00';
    }

    /**
     * @param mixed $orderTime
     */
    public function setOrderTime($orderTime)
    {
        $this->orderTime = $orderTime;
    }

    /**
     * @return array
     */
    public function getOrderHourAndMinute()
    {
        $orderTime = explode(':', $this->getOrderTime());

        return $orderTime;
    }

    /**
     * @return mixed
     */
    public function getOrderHour()
    {
        return $this->getOrderHourAndMinute()[0];
    }

    /**
     * @return mixed
     */
    public function getOrderMinute()
    {
        return $this->getOrderHourAndMinute()[1];
    }

    /**
     * @return mixed
     */
    public function isSameDayOrdersAllowed()
    {
        return $this->sameDayOrders;
    }

    /**
     * @param mixed $sameDayOrders
     */
    public function setSameDayOrders($sameDayOrders)
    {
        $this->sameDayOrders = $sameDayOrders;
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
     * @param mixed $token
     */
    public function setToken($token)
    {
        $this->token = substr(sha1(openssl_random_pseudo_bytes(50)) . sha1($token), 0, 30);
    }

    /**
     * @return string
     */
    public function getToken()
    {
        return $this->token;
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
     * @param $level
     * @return $this
     */
    public function setLevel($level)
    {
        $this->level = $level;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getLevel()
    {
        return $this->level;
    }

    /**
     * @return mixed
     */
    public function getLevelName()
    {
        $names = [
            1 => 'Farmer',
            2 => 'Gardener'
        ];

        return $names[$this->getLevel()];
    }
    
    /**
     * @return mixed
     */
    public function getReferralLink()
    {
        return $this->getAffiliate()->getReferralLink();
    }

    /**
     * @return mixed
     */
    public function getMembershipLink()
    {
        return 'http://' . $_SERVER['HTTP_HOST'] . '/membership/member/sign-up/' . $this->getToken();
    }

    /**
     * @param Team $team
     * @return $this
     */
    public function addTeam(Team $team)
    {
        $this->team->add($team);
        $team->setClient($this);

        return $this;
    }

    /**
     * @param Team $team
     */
    public function removeTeam(Team $team)
    {
        $this->team->removeElement($team);
    }

    /**
     * Get team
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTeam()
    {
        return $this->team;
    }

    /**
     * @return mixed
     */
    public function getSubscriptions()
    {
        return $this->subscriptions;
    }

    /**
     * @param mixed $subscriptions
     */
    public function setSubscriptions($subscriptions)
    {
        $this->subscriptions = $subscriptions;
    }

    /**
     * Get locations
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getLocations()
    {
        return $this->locations;
    }

    /**
     * @param Location $location
     * @return $this
     */
    public function addLocation(Location $location)
    {
        $location->setClient($this);
        $this->locations[] = $location;

        return $this;
    }


    /**
     * @param Transaction $transaction
     * @return $this
     */
    public function addTransaction(Transaction $transaction)
    {
        $this->transactions[] = $transaction;

        return $this;
    }

    /**
     * @param Transaction $transaction
     */
    public function removeTransaction(Transaction $transaction)
    {
        $this->transactions->removeElement($transaction);
    }

    /**
     * Get transactions
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTransactions()
    {
        return $this->transactions;
    }

    /**
     * @param Customer $customer
     * @return $this
     */
    public function addCustomer(Customer $customer)
    {
        $this->customers[] = $customer;

        return $this;
    }

    /**
     * @param Customer $customer
     */
    public function removeCustomer(Customer $customer)
    {
        $this->customers->removeElement($customer);
    }

    /**
     * Get customers
     *
     * @return \Doctrine\Common\Collections\Collection|Customer[] $customers
     */
    public function getCustomers()
    {
        return $this->customers;
    }

    /**
     * @param Share $share
     * @return $this
     */
    public function addShare(Share $share)
    {
        $this->shares[] = $share;

        return $this;
    }

    /**
     * @param Share $share
     */
    public function removeShare(Share $share)
    {
        $this->shares->removeElement($share);
    }

    /**
     * Get shares
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getShares()
    {
        return $this->shares;
    }

    /**
     * Get accesses
     *
     * @return \Doctrine\Common\Collections\Collection|ModuleAccess[] $accesses
     */
    public function getAccesses()
    {
        return $this->accesses;
    }

    public function getAffiliate()
    {
        return $this->affiliate;
    }

    /**
     * @return mixed
     */
    public function getAutoEmails()
    {
        return $this->autoEmails;
    }

    /**
     * @param mixed $autoEmails
     */
    public function setAutoEmails($autoEmails)
    {
        $this->autoEmails = $autoEmails;
    }

    /**
     * @param AutoEmail $autoEmail
     * @return $this
     */
    public function addAutoMail(AutoEmail $autoEmail)
    {
        $autoEmail->setClient($this);
        $this->autoEmails[] = $autoEmail;

        return $this;
    }

    /**
     * @return \Doctrine\Common\Collections\Collection|ModuleSetting[] $moduleSettings
     */
    public function getModulesSettings()
    {
        return $this->modulesSettings;
    }

    /**
     * @param mixed $modulesSettings
     */
    public function setModulesSettings($modulesSettings)
    {
        $this->modulesSettings = $modulesSettings;
    }

    /**
     * @param ModuleSetting $moduleSetting
     * @return $this
     */
    public function addModuleSettings(ModuleSetting $moduleSetting)
    {
        $moduleSetting->setClient($this);
        $this->modulesSettings[] = $moduleSetting;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPaymentSettings()
    {
        return $this->paymentSettings;
    }

    /**
     * @param mixed $paymentSettings
     */
    public function setPaymentSettings($paymentSettings)
    {
        $this->paymentSettings = $paymentSettings;
    }

    /**
     * @param PaymentSettings $paymentSettings
     * @return $this
     */
    public function addPaymentSettings(PaymentSettings $paymentSettings)
    {
        $paymentSettings->setClient($this);
        $this->paymentSettings[] = $paymentSettings;

        return $this;
    }

    /**
     * @return \Doctrine\Common\Collections\Collection|Tag[] $tags
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * @param mixed $tags
     */
    public function setTags($tags)
    {
        $this->tags = $tags;
    }

    /**
     * @param Tag $tag
     * @return $this
     */
    public function addTag(Tag $tag)
    {
        $tag->setClient($this);
        $this->tags[] = $tag;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPosts()
    {
        return $this->posts;
    }

    /**
     * @param mixed $posts
     */
    public function setPosts($posts)
    {
        $this->posts = $posts;
    }

    /**
     * @return mixed
     */
    public function getImages()
    {
        return $this->images;
    }
}
