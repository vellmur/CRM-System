<?php

namespace App\EventListener;

use App\Manager\EmailManager;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;

final class VisitFromEmailListener extends AppListener
{
    private $mailManager;

    /**
     * VisitFromEmailListener constructor.
     * @param EmailManager $mailManager
     */
    public function __construct(EmailManager $mailManager) {
        $this->mailManager = $mailManager;
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
            // If page was opened by click on tracked link from email, save this info to the db
            if ($this->isVisitFromEmail($request)) {
                $id = $request->query->get('email_recipient_id');
                $type = $request->query->get('email_recipient_type');

                $this->mailManager->saveClickedEmail($id, $type);

                if ($redirectTo = $request->query->get('page_redirect_to')) {
                    $event->setResponse(new RedirectResponse($redirectTo));
                    return;
                }
            }
        } catch (\Exception $exception) {
            if ($this->isDevelopmentEnvironment()) throw $exception;
        }
    }

    /**
     * @param Request $request
     * @return bool
     */
    private function isVisitFromEmail(Request $request)
    {
        return $request->query->has('email_recipient_id') && $request->query->has('email_recipient_type');
    }
}