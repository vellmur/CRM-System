<?php

namespace App\EventListener;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;

abstract class AppListener
{
    /**
     * @param Request $request
     * @return mixed
     */
    protected function isAssetsRequest(Request $request)
    {
        return $request->attributes->get('_format')
            || strstr($request->getRequestUri(), '/js/')
            || strstr($request->getRequestUri(), '/css/');
    }

    /**
     * @param RequestEvent $event
     * @return bool
     */
    protected function isSystemEvent(RequestEvent $event)
    {
        $request = $event->getRequest();

        return !$event->isMasterRequest() || $this->isAssetsRequest($request);
    }

    /**
     * @return bool
     */
    protected function isDevelopmentEnvironment() {
        return $_ENV['APP_ENV'] === 'dev';
    }
}