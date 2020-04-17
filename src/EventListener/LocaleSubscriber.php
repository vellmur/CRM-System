<?php

namespace App\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\RequestEvent;

class LocaleSubscriber implements EventSubscriberInterface
{
    private $defaultLocale = 'en';

    private $defaultTimezone = 'UTC';

    /**
     * @param RequestEvent $event
     */
    public function onKernelRequest(RequestEvent $event)
    {
        $request = $event->getRequest();
        if (!$request->hasPreviousSession()) {
            return;
        }

        if ($locale = $request->attributes->get('_locale')) {
            $request->getSession()->set('_locale', $locale);
            $request->setLocale($locale);
        } else {
            if ($request->getSession()->get('_locale') === null) {
                $request->getSession()->set('_locale', $this->defaultLocale);
            }
            $request->setLocale($request->getSession()->get('_locale', $this->defaultLocale));
        }

        $timezone = $request->getSession()->get('timezone') ?? $this->defaultTimezone;
        date_default_timezone_set($timezone);

        // Saving referral code to a session
        if ($referralId = $request->query->get('ref')) {
            $request->getSession()->set('ref', $referralId);
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => [['onKernelRequest', 20]]
        ];
    }
}