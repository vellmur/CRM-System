<?php

namespace App\Form\EventListener;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class ProfileSubscriber implements EventSubscriberInterface
{
    private $session;

    /**
     * ProfileSubscriber constructor.
     * @param SessionInterface $session
     */
    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            FormEvents::POST_SUBMIT => 'preSubmit'
        ];
    }

    /**
     * @param FormEvent $event
     */
    public function preSubmit(FormEvent $event)
    {
        $user = $event->getData();

        $this->session->set('_locale', $user->getLocaleCode());
        $this->session->set('date_format', $user->getDateFormatName());
        $this->session->set('timezone', $user->getTimezone());
    }
}