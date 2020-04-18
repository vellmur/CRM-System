<?php

namespace App\EventListener;

use App\Manager\NotificationManager;
use App\Service\ModuleChecker;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Twig\Environment;

final class NotificationListener extends AppListener
{
    private $token;

    private $notificationManager;

    private $twig;

    private $moduleChecker;

    /**
     * NotificationListener constructor.
     * @param TokenStorageInterface $tokenStorage
     * @param NotificationManager $notificationManager
     * @param Environment $twig
     * @param ModuleChecker $moduleChecker
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        NotificationManager $notificationManager,
        Environment $twig,
        ModuleChecker $moduleChecker
    ) {
        $this->token = $tokenStorage;
        $this->notificationManager = $notificationManager;
        $this->twig = $twig;
        $this->moduleChecker = $moduleChecker;
    }

    /**
     * @param RequestEvent $event
     * @throws \Exception
     */
    public function onKernelRequest(RequestEvent $event)
    {
        if ($this->isSystemEvent($event) === true) {
            return;
        }

        $request = $event->getRequest();

        try {
            $token = $this->token->getToken();
            $user = $token !== null && $token->getUser() !== 'anon.' ? $token->getUser() : null;

            // Set notifications as marked and load new notifications
            if ($user && $user->getClient() && $moduleName = $this->moduleChecker->getModuleNameByUrl($request->getRequestUri())) {
                if ($notifyID = $request->query->get('clicked_notification')) {
                    $this->notificationManager->setNotificationAsSeen($notifyID);
                }

                if ($notifications = $this->notificationManager->getNotifications($user, $moduleName)) {
                    $this->twig->addGlobal('notifications', $notifications);
                }
            }
        } catch (\Exception $exception) {
            if ($this->isDevelopmentEnvironment()) throw $exception;
        }
    }
}