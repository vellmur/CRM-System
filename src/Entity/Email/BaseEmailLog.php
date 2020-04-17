<?php

namespace App\Entity\Email;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\MappedSuperclass
 */
abstract class BaseEmailLog implements EmailLogInterface
{
    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->recipients = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->subject;
    }

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="subject", type="string", length=255)
     * @Assert\NotBlank(message="validation.form.required")
     */
    protected $subject;

    /**
     * @ORM\Column(name="text", type="text", length=10000)
     * @Assert\NotBlank(message="validation.form.required")
     */
    protected $text;

    /**
     * @var boolean
     * @ORM\Column(name="is_draft", type="boolean", nullable=false)
     */
    protected $isDraft = 1;

    /**
     * @var boolean
     * @ORM\Column(name="in_process", type="boolean", nullable=false)
     */
    protected $inProcess = 0;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     */
    protected $createdAt;

    protected $automatedEmail;

    protected $recipients;


    /**
     * @return int|null
     */
    public function getId()
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
     * @return string|null
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * @param string $subject
     */
    public function setSubject(string $subject): void
    {
        $this->subject = $subject;
    }

    /**
     * @return mixed
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * @param mixed $text
     */
    public function setText($text): void
    {
        $this->text = $text;
    }

    /**
     * @return bool
     */
    public function isDraft(): bool
    {
        return $this->isDraft;
    }

    /**
     * @param bool $isDraft
     */
    public function setIsDraft(bool $isDraft): void
    {
        $this->isDraft = $isDraft;
    }

    /**
     * @return bool
     */
    public function isInProcess(): bool
    {
        return $this->inProcess;
    }

    /**
     * @param bool $inProcess
     */
    public function setInProcess(bool $inProcess): void
    {
        $this->inProcess = $inProcess;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime $createdAt
     */
    public function setCreatedAt(\DateTime $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @return mixed
     */
    public function getAutomatedEmail()
    {
        return $this->automatedEmail;
    }

    /**
     * @param mixed $automatedEmail
     */
    public function setAutomatedEmail($automatedEmail)
    {
        $this->automatedEmail = $automatedEmail;
    }

    /**
     * @return array|ArrayCollection
     */
    public function getRecipients()
    {
        return $this->recipients;
    }

    /**
     * @param $recipients
     * @return mixed|void
     */
    public function setRecipients($recipients)
    {
        $this->recipients = $recipients;
    }

    /**
     * @param RecipientInterface $recipient
     * @return $this|mixed
     */
    public function addRecipient(RecipientInterface $recipient)
    {
        $this->recipients->add($recipient);
        $recipient->setEmailLog($this);

        return $this;
    }

    /**
     * @param RecipientInterface $recipient
     * @return mixed|void
     */
    public function removeRecipient(RecipientInterface $recipient)
    {
        $this->recipients->removeElement($recipient);
        $recipient->setEmailLog(null);
    }
}
