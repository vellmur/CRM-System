<?php

namespace App\EventListener;

use App\Service\Localization\LanguageDetector;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\SecurityEvents;

class LoginSubscriber implements EventSubscriberInterface
{
    private $session;

    private $languageDetector;

    /**
     * LoginSubscriber constructor.
     * @param SessionInterface $session
     * @param LanguageDetector $languageDetector
     */
    public function __construct(SessionInterface $session, LanguageDetector $languageDetector)
    {
        $this->session = $session;
        $this->languageDetector = $languageDetector;
    }

    /**
     * @param InteractiveLoginEvent $event
     */
    public function onInteractiveLogin(InteractiveLoginEvent $event)
    {
        $user = $event->getAuthenticationToken()->getUser();

        if ($user->getLocale() !== null) {
            $this->session->set('_locale', $this->languageDetector->getLocaleCodeById($user->getLocale()));
        }

        $this->session->set('date_format', $user->getDateFormat());
        $this->session->set('timezone', $user->getBuilding()->getTimezone());
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            SecurityEvents::INTERACTIVE_LOGIN => 'onInteractiveLogin',
        ];
    }
}
