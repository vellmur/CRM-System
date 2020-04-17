<?php

namespace App\Entity\Client\Notification;

use App\Entity\User\User;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class NotifiableNotification
 *
 * @ORM\Table(name="notification__notify")
 * @ORM\Entity(repositoryClass="App\Repository\NotifyRepository")
 *
 */
class NotifiableNotification implements \JsonSerializable
{
    /**
     * @var integer $id
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue
     */
    protected $id;

    /**
     * @var boolean
     * @ORM\Column(name="seen", type="boolean")
     */
    protected $seen;

    /**
     * @var Notification
     * @ORM\ManyToOne(targetEntity="App\Entity\Client\Notification\Notification", inversedBy="notifiableNotifications", cascade={"persist"})
     */
    protected $notification;

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="App\Entity\User\User", inversedBy="notifications", cascade={"persist"})
     *
     */
    protected $user;

    /**
     * AbstractNotification constructor.
     */
    public function __construct()
    {
        $this->seen = false;
    }

    /**
     * @return int Notification Id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @param User $user
     */
    public function setUser(User $user)
    {
        $this->user = $user;
    }

    /**
     * @return boolean Seen status of the notification
     */
    public function isSeen()
    {
        return $this->seen;
    }

    /**
     * @param boolean $isSeen Seen status of the notification
     * @return $this
     */
    public function setSeen($isSeen)
    {
        $this->seen = $isSeen;

        return $this;
    }

    /**
     * @return Notification
     */
    public function getNotification()
    {
        return $this->notification;
    }

    /**
     * @param Notification $notification
     *
     * @return NotifiableNotification
     */
    public function setNotification($notification)
    {
        $this->notification = $notification;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        return [
            'id'           => $this->getId(),
            'seen'         => $this->isSeen(),
            'notification' => $this->getNotification(),
            // for the notifiable, we serialize only the id:
            // - we don't need not want the FQCN exposed
            // - most of the time we will have a proxy and don't want to trigger lazy loading
            'notifiable'   => [ 'id' => $this->getUser()->getId() ]
        ];
    }
}
