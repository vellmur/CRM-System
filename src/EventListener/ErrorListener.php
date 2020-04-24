<?php

namespace App\EventListener;

use App\Service\Mail\Sender;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Twig\Environment;

class ErrorListener extends AppListener
{
    private $templating;

    private $env;

    private $sender;

    /**
     * ErrorListener constructor.
     * @param Environment $templating
     * @param Sender $sender
     * @param $env
     */
    public function __construct(Environment $templating, Sender $sender, $env)
    {
        $this->templating = $templating;
        $this->sender = $sender;
        $this->env = $env;
    }

    /**
     * @param ExceptionEvent $event
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function onKernelException(ExceptionEvent $event)
    {
        if ($this->isSystemEvent($event) === true) {
            return;
        }

        $exception = $event->getThrowable();

        // Show widget error
        if (strpos($event->getRequest()->getRequestUri(), 'widget-load')) {
            die('Problem Loading Widget - ' . $exception->getMessage());
        }

        // Production errors
        if ('prod' == $this->env) {
            if ($exception instanceof NotFoundHttpException) {
                $response = new Response($this->templating->render('error/404.html.twig'));
                $response->headers->set('X-Status-Code', 404);
                $response->setStatusCode(404);
            } elseif ($exception instanceof AccessDeniedHttpException) {
                $response = new Response($this->templating->render('error/403.html.twig', [
                    'message' => $exception->getMessage()
                ]));

                $response->headers->set('X-Status-Code', 403);
                $response->setStatusCode(403);
            } else {
                $response = new Response($this->templating->render('error/500.html.twig', [
                    'error' => $exception->getMessage(),
                    'file' => $exception->getFile() . ' on line ' . $exception->getLine(),
                    'trace' => $exception->getTraceAsString()
                ]));

                $response->setStatusCode(500);
                $response->headers->set('X-Status-Code', 500);

                $this->sender->sendExceptionToDeveloper(
                    '<p>Error from ErrorListener in production: </p><p>' . $exception->getMessage()
                    . ' .</p><p>At file ' . $exception->getFile() . ' on line ' . $exception->getLine() . '.</p>'
                    . '<p> . Trace: ' . $exception->getTraceAsString() . '</p>'
                );
            }

            $event->setResponse($response);
        }
    }
}