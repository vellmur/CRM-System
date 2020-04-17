<?php

namespace App\EventListener;

use App\Event\RegistrationSuccessEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\RouterInterface;

class RegistrationListener
{
    private $em;

    private $session;

    private $router;

    /**
     * RegistrationListener constructor.
     * @param EntityManagerInterface $em
     * @param SessionInterface $session
     * @param RouterInterface $router
     */
    public function __construct(
        EntityManagerInterface $em,
        SessionInterface $session,
        RouterInterface $router
    ){
        $this->em = $em;
        $this->session = $session;
        $this->router = $router;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            RegistrationSuccessEvent::class => 'onRegistrationSuccess'
        ];
    }

    /**
     * @param RegistrationSuccessEvent $event
     */
    public function onRegistrationSuccess(RegistrationSuccessEvent $event)
    {
        $user = $event->getUser();
        $this->session->set('app_registration/email', $user->getEmail());

        $event->setResponse(new RedirectResponse($this->router->generate('app_registration_check_email')));
    }
}