<?php

namespace App\EventListener;

use App\Manager\NotificationManager;
use App\Service\ModuleChecker;
use App\Service\PageViewSaver;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\SwitchUserToken;
use Twig\Environment;

final class ViewListener extends AppListener
{
    private $session;

    private $token;

    private $pageViewSaver;

    private $moduleChecker;

    private $notifications;

    private $twig;

    /**
     * ViewListener constructor.
     * @param SessionInterface $session
     * @param TokenStorageInterface $tokenStorage
     * @param PageViewSaver $pageViewSaver
     * @param ModuleChecker $moduleChecker
     * @param NotificationManager $notifications
     * @param Environment $twig
     */
    public function __construct(
        SessionInterface $session,
        TokenStorageInterface $tokenStorage,
        PageViewSaver $pageViewSaver,
        ModuleChecker $moduleChecker,
        NotificationManager $notifications,
        Environment $twig
    ){
        $this->session = $session;
        $this->token = $tokenStorage;
        $this->pageViewSaver = $pageViewSaver;
        $this->moduleChecker = $moduleChecker;
        $this->notifications = $notifications;
        $this->twig = $twig;
    }

    /**
     * @param RequestEvent $event
     * @throws \Exception
     */
    public function onKernelRequest(RequestEvent $event)
    {
        $request = $event->getRequest();

        if ($this->isNotSoftwareUserEvent($event)) {
            return;
        }

        try {
            // If page was opened by click on tracked link from email, save this info to the db
            if ($this->pageViewSaver->isVisitFromEmail($request)) {
                $this->pageViewSaver->saveClickedEmail($request);

                if ($redirectTo = $request->query->get('page_redirect_to')) {
                    $event->setResponse(new RedirectResponse($redirectTo));
                    return;
                }
            }

            $token = $this->token->getToken();
            $user = $token !== null && $token->getUser() !== 'anon.' ? $token->getUser() : null;
            $moduleName = $this->moduleChecker->getModuleNameByUrl($request->getRequestUri());

            // Do not save page views for master visits
            if (!$token instanceof SwitchUserToken) {
                $sessDeviceId = $this->session->get('deviceId');
                $deviceId = $this->pageViewSaver->savePageView($user, $request->getRequestUri(), $moduleName, $sessDeviceId);

                if ($deviceId !== null && $sessDeviceId == null) {
                    $this->session->set('deviceId', $deviceId);
                }
            }

            // Set notifications as marked and load new notifications
            if ($user && $user->getClient() && $moduleName) {
                if ($notifyID = $request->query->get('clicked_notification')) {
                    $this->notifications->setNotificationAsSeen($notifyID);
                }

                if ($notifications = $this->notifications->getNotifications($user, $moduleName)) {
                    $this->twig->addGlobal('notifications', $notifications);
                }
            }
        } catch (\Exception $exception) {
            if ($this->isDevelopment($request->getHost())) throw $exception;
        }
    }
}