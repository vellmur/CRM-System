<?php

namespace App\Form\Subscriber;

use App\Service\Localization\CurrencyFormatter;
use App\Service\Localization\LanguageDetector;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class ProfileSubscriber implements EventSubscriberInterface
{
    private $session;

    private $languageDetector;

    /**
     * ProfileSubscriber constructor.
     * @param SessionInterface $session
     * @param LanguageDetector $languageDetector
     */
    public function __construct(SessionInterface $session, LanguageDetector $languageDetector)
    {
        $this->session = $session;
        $this->languageDetector = $languageDetector;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            FormEvents::POST_SET_DATA => 'postSet',
            FormEvents::POST_SUBMIT => 'preSubmit'
        ];
    }

    /**
     * Here we dynamically set owner location data, based on country/region/city/postalCode
     * @param FormEvent $event
     */
    public function postSet(FormEvent $event)
    {
        $form = $event->getForm();
        $user = $event->getData();

        $currency = $user->getBuilding()->getCurrency()
            ? CurrencyFormatter::getCurrencySymbolById($user->getBuilding()->getCurrency())
            : null;

        $form->get('building')->get('currency')->setData($currency);
    }

    /**
     * @param FormEvent $event
     */
    public function preSubmit(FormEvent $event)
    {
        $user = $event->getData();

        if ($user->getBuilding()->getCurrency()) {
            $user->getBuilding()->setCurrency(CurrencyFormatter::getCurrencyIdBySymbol($user->getBuilding()->getCurrency()));
        }

        $this->session->set('_locale', $this->languageDetector->getLocaleCodeById($user->getLocale()));
        $this->session->set('date_format', $user->getDateFormatName());
        $this->session->set('timezone', $user->getBuilding()->getTimezone());
    }
}