<?php

namespace App\Controller\Security;

use App\Service\MailService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

use App\Form\SupportType;
use Symfony\Contracts\Translation\TranslatorInterface;

class HelpController extends AbstractController
{
    private $mailer;

    /**
     * HelpController constructor.
     * @param MailService $mailer
     */
    public function __construct(MailService $mailer)
    {
        $this->mailer = $mailer;
    }

    public function support(Request $request, TranslatorInterface $translator)
    {
        $choices = [
            $translator->trans('help.support.subjects.technical', [], 'messages') => 'Technical support',
            $translator->trans('help.support.subjects.payment', [], 'messages') => 'Payment support',
            $translator->trans('help.support.subjects.recommendations', [], 'messages') => 'Suggestions and recommendations'
        ];

        $support = $this->createForm(SupportType::class, null, [
            'choices' => $choices
        ]);

        $support->handleRequest($request);

        if ($support->isSubmitted() && $support->isValid()) {
            $data = [
                'name' => $this->getUser()->getTeam()->getClient()->getName(),
                'email' => $this->getUser()->getEmail(),
                'subject' => $request->request->get('support')['subject'],
                'message' => $request->request->get('support')['message']
            ];
            
            $this->mailer->sendMail(
                'Black Dirt Software',
                $_ENV['SUPPORT_EMAIL'],
                'emails/support.html.twig',
                $request->request->get('support')['subject'],
                $data
            );

            return $this->render('help/message_sent.html.twig');
        }

        return $this->render('help/support.html.twig', [
            'supportForm' => $support->createView()
        ]);
    }
}