<?php

namespace App\Entity\Email;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\MappedSuperclass
 */
abstract class BaseRecipient implements RecipientInterface
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var EmailLogInterface
     */
    protected $emailLog;

    /**
     * @ORM\Column(name="email_address", type="string", length=50, nullable=false)
     *
     * @Assert\Email(message = "validation.form.not_valid_email")
     * @Assert\NotBlank(message="validation.form.required")
     */
    protected $emailAddress;

    /**
     * @var boolean
     * @ORM\Column(name="is_delivered", type="boolean", nullable=false)
     */
    protected $isDelivered = 0;

    /**
     * @var boolean
     * @ORM\Column(name="is_opened", type="boolean", nullable=false)
     */
    protected $isOpened = 0;

    /**
     * @var boolean
     * @ORM\Column(name="is_clicked", type="boolean", nullable=false)
     */
    protected $isClicked = 0;

    /**
     * @var boolean
     * @ORM\Column(name="is_bounced", type="boolean", nullable=false)
     */
    protected $isBounced = 0;

    /**
     * @return int
     */
    public function getId(): int
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
    public function getEmailAddress()
    {
        return $this->emailAddress;
    }

    /**
     * @param mixed $emailAddress
     */
    public function setEmailAddress($emailAddress): void
    {
        $this->emailAddress = $emailAddress;
    }

    /**
     * @return bool
     */
    public function isDelivered(): bool
    {
        return $this->isDelivered;
    }

    /**
     * @param bool $isDelivered
     */
    public function setIsDelivered(bool $isDelivered): void
    {
        $this->isDelivered = $isDelivered;
    }

    /**
     * @return bool
     */
    public function isOpened(): bool
    {
        return $this->isOpened;
    }

    /**
     * @param bool $isOpened
     */
    public function setIsOpened(bool $isOpened): void
    {
        $this->isOpened = $isOpened;
    }

    /**
     * @return bool
     */
    public function isClicked(): bool
    {
        return $this->isClicked;
    }

    /**
     * @param bool $isClicked
     */
    public function setIsClicked(bool $isClicked): void
    {
        $this->isClicked = $isClicked;
    }

    /**
     * @return bool
     */
    public function isBounced(): bool
    {
        return $this->isBounced;
    }

    /**
     * @param bool $isBounced
     */
    public function setIsBounced(bool $isBounced): void
    {
        $this->isBounced = $isBounced;
    }

    /**
     * @param EmailLogInterface $emailLog
     */
    public function setEmailLog(EmailLogInterface $emailLog)
    {
        $this->emailLog = $emailLog;
    }

    /**
     * @return mixed
     */
    public function getEmailLog() : EmailLogInterface
    {
        return $this->emailLog;
    }
}