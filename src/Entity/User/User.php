<?php

namespace App\Entity\User;

use App\Entity\Client\Client;
use App\Entity\Client\Team;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use EWZ\Bundle\RecaptchaBundle\Validator\Constraints as Recaptcha;
use Doctrine\Common\Collections\Collection;
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
     * @ORM\ManyToOne(targetEntity="App\Entity\Translation\TranslationLocale", inversedBy="users",cascade={"persist"})
     * @ORM\JoinColumn(name="locale_id", referencedColumnName="id", nullable=false)
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
     * @ORM\OneToOne(targetEntity="App\Entity\Client\Team", mappedBy="user", cascade={"persist", "remove" })
     */
    private $team;

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

    /**
     * @Recaptcha\IsTrue(groups={"register_validation"})
     */
    public $recaptcha;

    /**
     * @return $this
     */
    public function setId($id)
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
    public function setUsername($username)
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
    public function setPlainPassword($plainPassword)
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

    public function isPasswordRequestNonExpired($ttl)
    {
        return $this->getPasswordRequestedAt() instanceof \DateTime &&
            $this->getPasswordRequestedAt()->getTimestamp() + $ttl > time();
    }

    /**
     * {@inheritdoc}
     */
    public function getConfirmationToken()
    {
        return $this->confirmationToken;
    }

    /**
     * {@inheritdoc}
     */
    public function setConfirmationToken($confirmationToken)
    {
        $this->confirmationToken = $confirmationToken;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getSalt()
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
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
    public function setDateFormat($dateFormat)
    {
        $this->dateFormat = $dateFormat;

        return $this;
    }

    // get name of date format
    public function getDateFormatName()
    {
        $dateFormats = ['dd-MM-yyyy', 'MM-dd-yyyy', 'yyyy-MM-dd', 'dd-MMM-yyyy'];

        return $dateFormats[$this->dateFormat];
    }

    // Date format for twig option - format
    public function getTwigFormatDate()
    {
        $dateFormats = ['d-m-Y', 'm-d-Y', 'Y-m-d', 'd-M-Y'];

        return $dateFormats[$this->dateFormat];
    }

    /**
     * @return mixed
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @param mixed $locale
     */
    public function setLocale($locale): void
    {
        $this->locale = $locale;
    }

    /**
     * @return mixed|null
     */
    public function getTimeZone()
    {
        return $this->getTeam() && $this->getClient()->getTimezone() ? $this->getClient()->getTimezone() : null;
    }

    /**
     * Set createdAt
     *
     * @param DateTime $createdAt
     *
     * @return User
     */
    public function setCreatedAt($createdAt)
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
     * @param $enabled
     * @return $this
     */
    public function setEnabled($enabled)
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
     * @param $isActive
     * @return $this
     */
    public function setIsActive($isActive)
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
     * @param $team
     * @return $this
     */
    public function setTeam($team)
    {
        $this->team = $team;

        return $this;
    }

    /**
     * @return Team
     */
    public function getTeam()
    {
        return $this->team;
    }

    /**
     * Remove team
     *
     * @param Team $team
     */
    public function removeTeam(Team $team)
    {
        $this->team->removeElement($team);
    }

    /**
     * @return Collection
     */
    public function getClientAccess()
    {
        return $this->getTeam()->getClient()->getAccesses();
    }

    /**
     * @return Client|bool
     */
    public function getClient()
    {
        return $this->getTeam() ? $this->getTeam()->getClient() : null;
    }
}
