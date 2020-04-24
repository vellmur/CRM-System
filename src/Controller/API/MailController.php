<?php

namespace App\Controller\API;

use App\Manager\Mail\MailManager;
use App\Service\Mail\MailService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class MailController extends AbstractController
{
    private $mailManager;

    private $mailService;

    public function __construct(MailManager $mailManager, MailService $mailService)
    {
        $this->mailManager = $mailManager;
        $this->mailService = $mailService;
    }

    /**
     * Here we set emails as opened.
     * When user opens message, he downloads hidden image inside email body with src to our software.
     * So we set email as opened and return transparent (hidden) image.
     *
     * @param int $recipientId
     * @param string $recipientType
     * @return Response
     * @throws \Exception
     */
    public function opensTracking(int $recipientId, string $recipientType)
    {
        if (strstr($recipientType, '.png')) $recipientType = str_replace('.png', '', $recipientType);

        $this->mailManager->setAsOpened($recipientId, $recipientType);

        $imageString = $this->mailService->renderTransparentImage();

        return new Response($imageString, 200, [
            'Content-type' => 'image/jpeg',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'no-cache'
        ]);
    }
}