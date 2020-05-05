<?php

namespace App\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\SwitchUserEvent;
use Symfony\Component\Security\Http\SecurityEvents;
use App\Entity\User\User;

/**
 * Class SwitchUserSubscriber
 * @package App\EventListener
 */
class SwitchUserSubscriber implements EventSubscriberInterface
{
    /**
     * @param SwitchUserEvent $event
     */
    public function onSwitchUser(SwitchUserEvent $event)
    {
        $request = $event->getRequest();

        if ($request->hasSession() && ($session = $request->getSession())) {
            /** @var User $user */
            $user = $event->getTargetUser();

            $session->set('date_format', $user->getDateFormat());
            $session->set('timezone', $user->getClient()->getTimezone());
        }
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [ SecurityEvents::SWITCH_USER => 'onSwitchUser' ];
    }
}