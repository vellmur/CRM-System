<?php

namespace App\Entity\Owner;

use App\Entity\Owner\Email\AutoEmail;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Automates emails for
 *
 * @ORM\Table(name="owner__notifies", uniqueConstraints={@ORM\UniqueConstraint(name="owner_emails_unique", columns={"owner_id", "notify_type"})})
 * @ORM\Entity()
 * @UniqueEntity(fields="name", errorPath="name", message="validation.form.unique")
 */
class OwnerEmailNotify
{
    public function __toString()
    {
        return $this->getNotifyName();
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
     * @ORM\ManyToOne(targetEntity="App\Entity\Owner\Owner", inversedBy="notifications")
     * @ORM\JoinColumn(name="owner_id", referencedColumnName="id")
     */
    private $owner;

    /**
     * @var int
     *
     * @ORM\Column(name="notify_type", type="integer", length=1)
     */
    private $notifyType;

    /**
     * @var boolean
     * @ORM\Column(name="is_active", type="boolean")
     */
    private $isActive = 1;

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
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * @param mixed $owner
     */
    public function setOwner($owner)
    {
        $this->owner = $owner;
    }

    /**
     * @return int
     */
    public function getNotifyType()
    {
        return $this->notifyType;
    }

    /**
     * @param int $notifyType
     */
    public function setNotifyType($notifyType)
    {
        $this->notifyType = $notifyType;
    }

    /**
     * @return mixed
     */
    public function isActive()
    {
        return $this->isActive;
    }

    /**
     * @param mixed $isActive
     */
    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;
    }

    /**
     * @return mixed
     */
    public function getNotifyName()
    {
        return AutoEmail::EMAIL_TYPES[$this->notifyType];
    }
}
