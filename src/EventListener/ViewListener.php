<?php

namespace App\EventListener;

use App\Service\PageViewSaver;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\SwitchUserToken;

final class ViewListener extends AppListener
{
    private $session;

    private $token;

    private $pageViewSaver;

    /**
     * @param SessionInterface $session
     * @param TokenStorageInterface $tokenStorage
     * @param PageViewSaver $pageViewSaver
     */
    public function __construct(SessionInterface $session, TokenStorageInterface $tokenStorage, PageViewSaver $pageViewSaver) {
        $this->session = $session;
        $this->token = $tokenStorage;
        $this->pageViewSaver = $pageViewSaver;
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

            // Do not save page views for master visits
            if (!$token instanceof SwitchUserToken) {
                $sessDeviceId = $this->session->get('deviceId');
                $deviceId = $this->pageViewSaver->savePageView($user, $request->getRequestUri(), $sessDeviceId);

                if ($deviceId !== null && $sessDeviceId == null) {
                    $this->session->set('deviceId', $deviceId);
                }
            }
        } catch (\Exception $exception) {

            if ($this->isDevelopmentEnvironment()) throw $exception;
        }
    }
}