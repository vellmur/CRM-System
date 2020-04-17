<?php

namespace App\Entity\Customer\Email;

use Doctrine\ORM\Mapping as ORM;

/**
 * Client
 *
 * @ORM\Table(name="email__feedback")
 * @ORM\Entity(repositoryClass="App\Repository\FeedbackRepository")
 */
class Feedback
{
    public function __construct()
    {
        $this->createdAt = new \DateTime();
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
     * @ORM\ManyToOne(targetEntity="App\Entity\Customer\Customer", inversedBy="feedback")
     * @ORM\JoinColumn(name="customer_id", referencedColumnName="id")
     */
    private $customer;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Customer\Share", inversedBy="feedback")
     * @ORM\JoinColumn(name="share_id", referencedColumnName="id")
     */
    private $share;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Customer\Email\EmailRecipient", inversedBy="feedback")
     * @ORM\JoinColumn(name="recipient_id", referencedColumnName="id", nullable=true)
     */
    private $recipient;

    /**
     * @var boolean
     * @ORM\Column(name="is_satisfied", type="boolean")
     */
    private $isSatisfied;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="share_date", type="date")
     */
    private $shareDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $createdAt;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getShare()
    {
        return $this->share;
    }

    /**
     * @param mixed $share
     */
    public function setShare($share)
    {
        $this->share = $share;
    }

    /**
     * @return mixed
     */
    public function getRecipient()
    {
        return $this->recipient;
    }

    /**
     * @param mixed $recipient
     */
    public function setRecipient($recipient)
    {
        $this->recipient = $recipient;
    }

    /**
     * @return mixed
     */
    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     * @param mixed $customer
     */
    public function setCustomer($customer)
    {
        $this->customer = $customer;
    }

    /**
     * @return bool
     */
    public function isSatisfied(): bool
    {
        return $this->isSatisfied;
    }

    /**
     * @param bool $isSatisfied
     */
    public function setIsSatisfied(bool $isSatisfied)
    {
        $this->isSatisfied = $isSatisfied;
    }

    /**
     * @return \DateTime
     */
    public function getShareDate(): \DateTime
    {
        return $this->shareDate;
    }

    /**
     * @param \DateTime $shareDate
     */
    public function setShareDate(\DateTime $shareDate)
    {
        $this->shareDate = $shareDate;
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
    public function setCreatedAt(\DateTime $createdAt)
    {
        $this->createdAt = $createdAt;
    }
}