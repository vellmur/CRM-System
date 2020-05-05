<?php

namespace App\Entity\User;

use App\Entity\Client\Client;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use DateTime;

/**
 * @ORM\Table(name="user")
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @UniqueEntity(fields="email", message="validation.form.unique")
 * @UniqueEntity(fields="username", message="validation.form.unique")
 */
class User implements UserInterface
{
    public function __construct()
    {
        $this->createdAt = new DateTime();
    }

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=180, unique=true)
     *
     * @Assert\Length(
     *     min="5",
     *     max="32",
     *     minMessage="sign_up.form.username.min_message",
     *     maxMessage="sign_up.form.username.max_message")
     * @Assert\NotBlank(message="validation.form.required")
     */
    private $username;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=180, unique=true)
     * @Assert\Email(message="sign_up.form.email.not_valid")
     * @Assert\NotBlank(message="validation.form.required")
     */
    private $email;

    /**
     * @var int
     *
     * @ORM\Column(type="integer", length=1)
     * @Assert\NotBlank(message="validation.form.required")
     */
    private $locale;

    /**
     * @var int
     *
     * @ORM\Column(name="date_format", type="integer", length=1, nullable=true)
     */
    private $dateFormat = 3;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @var string
     *
     * @Assert\Length(
     *     min="6",
     *     max="32",
     *     minMessage="sign_up.form.password.min_message",
     *     maxMessage="sign_up.form.password.max_message",
     *     groups={"register_validation"})
     * @Assert\NotBlank(message="validation.form.required", groups={"register_validation"})
     */
    private $plainPassword;

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     * @var string|null Random string sent to the user email address in order to verify it.
     * @ORM\Column(name="confirmation_token", type="string", nullable=true)
     */
    private $confirmationToken;

    /**
     *  @ORM\Column(name="password_requested_at", type="date", nullable=true)
     */
    protected $passwordRequestedAt;

    /**
     * @var boolean
     * @ORM\Column(name="enabled", type="boolean", nullable=false)
     */
    private $enabled = false;

    /**
     * @var boolean
     * @ORM\Column(name="is_active", type="boolean", nullable=false)
     */
    private $isActive = true;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Client\Client", inversedBy="users", cascade={"persist"})
     * @ORM\JoinColumn(name="client_id", referencedColumnName="id", nullable=false)
     */
    private $client;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="created_at", type="date")
     */
    private $createdAt;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\User\Device", mappedBy="user", cascade={"all"}, orphanRemoval=true)
     */
    private $devices;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Client\Notification\NotifiableNotification", mappedBy="user", cascade={"all"}, orphanRemoval=true)
     */
    private $notifications;

    const ROLE_ADMIN = 'ROLE_ADMIN';
    const ROLE_OWNER = 'ROLE_OWNER';
    const ROLE_EMPLOYEE = 'ROLE_EMPLOYEE';
    const ROLE_USER = 'ROLE_USER';

    const LOCALES = [
        1 => 'English',
        2 => 'Русский',
        3 => 'Українська'
    ];

    /**
     * @var array
     */
    const DATE_FORMATS = [
        1 => 'dd-MM-yyyy',
        2 => 'MM-dd-yyyy',
        3 => 'yyyy-MM-dd',
        4 => 'dd-MMM-yyyy'
    ];

    const TWIG_DATE_FORMATS = [
        1 => 'd-m-Y',
        2 => 'm-d-Y',
        3 => 'Y-m-d',
        4 => 'd-M-Y'
    ];

    /**
     * @param int $id
     * @return $this
     */
    public function setId(int $id)
    {
        $this->id = $id;

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @param string $username
     * @return $this
     */
    public function setUsername(?string $username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * @return string
     */
    public function getUsername(): string
    {
        return (string) $this->username;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @return string
     */
    public function getPlainPassword(): string
    {
        return (string) $this->plainPassword;
    }

    /**
     * {@inheritdoc}
     */
    public function setPlainPassword(?string $plainPassword)
    {
        $this->plainPassword = $plainPassword;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return (string) $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setPasswordRequestedAt(\DateTime $date = null)
    {
        $this->passwordRequestedAt = $date;

        return $this;
    }

    /**
     * Gets the timestamp that the user requested a password reset.
     *
     * @return \DateTime|null
     */
    public function getPasswordRequestedAt()
    {
        return $this->passwordRequestedAt;
    }

    /**
     * @param $ttl
     * @return bool
     */
    public function isPasswordRequestNonExpired($ttl)
    {
        return $this->getPasswordRequestedAt() instanceof \DateTime &&
            $this->getPasswordRequestedAt()->getTimestamp() + $ttl > time();
    }

    public function eraseCredentials()
    {
        $this->plainPassword = null;
    }

    public function getSalt()
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
    }

    /**
     * {@inheritdoc}
     */
    public function getConfirmationToken()
    {
        return $this->confirmationToken;
    }

    /**
     * @param string|null $confirmationToken
     * @return $this
     */
    public function setConfirmationToken(?string $confirmationToken)
    {
        $this->confirmationToken = $confirmationToken;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDateFormat()
    {
        return $this->dateFormat;
    }

    /**
     * @param $dateFormat
     * @return $this
     */
    public function setDateFormat(string $dateFormat)
    {
        $this->dateFormat = in_array($dateFormat, self::DATE_FORMATS)
            ? array_flip(self::DATE_FORMATS)[$dateFormat] : null;

        return $this;
    }

    /**
     * @return array|null
     */
    public function getDateFormatName(): ?string
    {
        return $this->dateFormat ? self::DATE_FORMATS[$this->dateFormat] : null;
    }

    /**
     * @return mixed|null
     */
    public function getTwigFormatDate(): ?string
    {
        return $this->dateFormat ? self::TWIG_DATE_FORMATS[$this->dateFormat] : null;
    }

    /**
     * @return int
     */
    public function getLocale(): ?int
    {
        return $this->locale;
    }

    /**
     * @return string|null
     */
    public function getLocaleCode(): ?string
    {
        $locales = [
            1 => 'en',
            2 => 'ru',
            3 => 'uk'
        ];

        return $this->locale ? $locales[$this->locale] : null;
    }

    /**
     * @param int $locale
     */
    public function setLocale(int $locale): void
    {
        $this->locale = $locale;
    }

    /**
     * Set createdAt
     *
     * @param $createdAt
     *
     * @return User
     */
    public function setCreatedAt(DateTime $createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param bool $enabled
     * @return $this
     */
    public function setEnabled(bool $enabled)
    {
        $this->enabled = $enabled;

        return $this;
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * @param bool $isActive
     * @return $this
     */
    public function setIsActive(bool $isActive)
    {
        $this->isActive = $isActive;

        return $this;
    }

    /**
     * @return bool
     */
    public function getIsActive()
    {
        return $this->isActive;
    }

    /**
     * @return Client|null
     */
    public function getClient() : ?Client
    {
        return $this->client;
    }

    /**
     * @param Client $client
     * @return $this
     */
    public function setClient(Client $client)
    {
        $this->client = $client;

        return $this;
    }
}
