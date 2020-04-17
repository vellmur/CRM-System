<?php

namespace App\EventListener;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;

abstract class AppListener
{
    /**
     * @param Request $request
     * @return bool
     */
    protected function isProfilerRequest(Request $request)
    {
        return $request->attributes->get('_route') == '_wdt' || $request->attributes->get('_route') == '_profiler';
    }

    /**
     * @param Request $request
     * @return bool
     */
    protected function isClientWebsite(Request $request)
    {
        return $request->attributes->get('subDomain') != null;
    }

    /**
     * @param Request $request
     * @return mixed
     */
    protected function isAssetsRequest(Request $request)
    {
        return $request->attributes->get('_format') || strstr($request->getRequestUri(), '/js/') || strstr($request->getRequestUri(), '/css/');
    }

    /**
     * @param RequestEvent $event
     * @return bool
     */
    protected function isNotSoftwareUserEvent(RequestEvent $event)
    {
        $request = $event->getRequest();

        $result = !$event->isMasterRequest() || $this->isProfilerRequest($request) || $this->isClientWebsite($request) || $this->isAssetsRequest($request);

        return $result;
    }

    /**
     * @param $domain
     * @return bool
     */
    protected function isDevelopment($domain) {
        return strstr($domain, 'testserver')
            || strstr($domain, '127.0.0.1')
            || strstr($domain, 'customer.local');
    }
}