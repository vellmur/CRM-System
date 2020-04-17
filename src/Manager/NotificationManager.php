<?php

namespace App\Manager;

use App\Entity\Client\Notification\NotifiableNotification;
use App\Entity\Client\Notification\Notification;
use App\Entity\Client\ModuleAccess;
use App\Entity\User\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class NotificationManager
{
    private $em;

    private $router;

    /**
     * NotificationManager constructor.
     * @param EntityManagerInterface $em
     * @param UrlGeneratorInterface $router
     */
    public function __construct(EntityManagerInterface $em, UrlGeneratorInterface $router)
    {
        $this->em = $em;
        $this->router = $router;
    }

    /**
     * @param string $moduleName
     * @param string $subject
     * @param string $message
     * @param null $route
     * @param null $users
     * @return Notification
     */
    public function createNotification($moduleName, $subject, $message, $route = null, $users = null)
    {
        $notification = new Notification();

        $notification
            ->setSubject($subject)
            ->setMessage($message)
            ->setModule(ModuleAccess::getModuleId($moduleName));

        if ($route) {
            $notification->setLink($this->router->generate($route));
        }

        if ($users) {
            if (!is_array($users)) $users = [$users];
            $this->addNotification($users, $notification);
        } else {
            $this->em->flush();
        }

        return $notification;
    }

    /**
     * Add a Notification to a list of NotifiableInterface entities
     *
     * @param User[] $users
     * @param Notification $notification
     */
    public function addNotification($users, Notification $notification)
    {
        foreach ($users as $user) {
           $notifiable = new NotifiableNotification();
           $notifiable->setUser($user);
           $this->em->persist($notifiable);

           $notification->addNotifiableNotification($notifiable);
        }

        $this->em->flush();
    }

    /**
     * @param $user
     * @param $moduleName
     * @return mixed|null
     */
    public function getNotifications($user, $moduleName)
    {
        $moduleId = ModuleAccess::getModuleId($moduleName);

        return $this->em->getRepository(NotifiableNotification::class)->getUserNotifications($user, $moduleId);
    }

    /**
     * @param string $notificationId
     */
    public function setNotificationAsSeen($notificationId)
    {
        $notification = $this->em->find(NotifiableNotification::class, $notificationId);

        if ($notification) {
            $notification->setSeen(true);
            $this->em->flush();
        }
    }
}