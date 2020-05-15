<?php

namespace App\Entity\Building\Notification;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Building\Notification\NotifiableNotification;

/**
 * Class Notification
 *
 * @ORM\Table(name="notification")
 * @ORM\Entity()
 *
 */
class Notification implements \JsonSerializable
{
    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->notifiableNotifications = new ArrayCollection();
    }

    /**
     * @var integer $id
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue
     */
    protected $id;

    /**
     * @var string
     * @ORM\Column(type="string", length=4000)
     */
    protected $subject;
    /**
     * @var string
     * @ORM\Column(type="string", length=4000, nullable=true)
     */
    protected $message;

    /**
     * @var string
     * @ORM\Column(type="string", length=4000, nullable=true)
     */
    protected $link;

    /**
     * @ORM\Column(name="module_id", type="integer", length=1, nullable=false)
     */
    protected $module;

    /**
     * @var \DateTime
     * @ORM\Column(name="created_at", type="datetime", nullable=false)
     */
    protected $createdAt;

    /**
     * @var NotifiableNotification[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Entity\Building\Notification\NotifiableNotification", mappedBy="notification", cascade={"persist"})
     */
    protected $notifiableNotifications;

    /**
     * @return int Notification Id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @return string Notification subject
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * @param string $subject Notification subject
     * @return $this
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * @return string Notification message
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param string $message Notification message
     * @return $this
     */
    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * @return string Link to redirect the user
     */
    public function getLink()
    {
        return $this->link;
    }

    /**
     * @param string $link Link to redirect the user
     * @return $this
     */
    public function setLink($link)
    {
        $this->link = $link;

        return $this;
    }

    /**
     * @return ArrayCollection|NotifiableNotification[]
     */
    public function getNotifiableNotifications()
    {
        return $this->notifiableNotifications;
    }

    /**
     * @param NotifiableNotification $notifiableNotification
     *
     * @return $this
     */
    public function addNotifiableNotification(NotifiableNotification $notifiableNotification)
    {
        if (!$this->notifiableNotifications->contains($notifiableNotification)) {
            $this->notifiableNotifications[] = $notifiableNotification;
            $notifiableNotification->setNotification($this);
        }

        return $this;
    }

    /**
     * @param NotifiableNotification $notifiableNotification
     *
     * @return $this
     */
    public function removeNotifiableNotification(NotifiableNotification $notifiableNotification)
    {
        if ($this->notifiableNotifications->contains($notifiableNotification)) {
            $this->notifiableNotifications->removeElement($notifiableNotification);
            $notifiableNotification->setNotification(null);
        }

        return $this;
    }

    /**
     * @return mixed
     */
    public function getModule()
    {
        return $this->module;
    }

    /**
     * @param mixed $module
     */
    public function setModule($module)
    {
        $this->module = $module;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getSubject() . ' - ' . $this->getMessage();
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        return [
            'id'      => $this->getId(),
            'date'    => $this->getCreatedAt()->format(\DateTime::ISO8601),
            'subject' => $this->getSubject(),
            'message' => $this->getMessage(),
            'link'    => $this->getLink()
        ];
    }
}
