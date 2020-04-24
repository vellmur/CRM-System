<?php

namespace App\Controller\Master;

use App\Entity\Master\Email\Email;
use App\Form\Master\AutomatedEmails;
use App\Form\Master\EmailType;
use App\Manager\EmailManager;
use App\Manager\MasterManager;
use App\Service\Mail\Sender;
use JMS\Serializer\SerializerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Process\Process;

class MailController extends AbstractController
{
    private $manager;

    public function __construct(EmailManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @param Request $request
     * @param Email|null $email
     * @return Response
     */
    public function compose(Request $request, Email $email = null)
    {
        if (!$email) $email = new Email();

        $form = $this->createForm(EmailType::class, $email);
        $form->handleRequest($request);

        $recipients = [];

        if ($form->isSubmitted()) {
            // If action is to send email and email haven`t recipients, show error
            isset($request->request->get('email')['recipients'])
                ? $recipients = $request->request->get('email')['recipients']
                : $form->addError(new FormError('Recipients list is empty!'));

            if ($form->isValid()) {
                $email = $this->manager->saveEmail($email, $recipients, false);

                return $this->redirectToRoute('master_email_sending', ['id' => $email->getId()]);
            }
        }

        $clients = $this->manager->getSoftwareClients();

        foreach ($email->getRecipients() as $recipient) {
            $recipients[] = $recipient->getClient()->getId();
        }

        return $this->render('master/email/compose.html.twig', [
            'form' => $form->createView(),
            'clients' => $clients,
            'recipients' => $recipients,
            'macros' => $this->manager->getMacrosList()
        ]);
    }

    /**
     * @param Request $request
     * @param MasterManager $manager
     * @param SerializerInterface $serializer
     * @return JsonResponse
     */
    public function searchRecipients(Request $request, MasterManager $manager, SerializerInterface $serializer)
    {
        $status = $request->query->get('status');
        $text = $request->query->get('search');

        $clients = $manager->searchClientsBy($status, $text);

        $template = $this->render('master/email/recipients_list.html.twig', [
            'clients' => $clients
        ])->getContent();

        return new JsonResponse(
            $serializer->serialize([
                'template' => $template,
                'counter' => count($clients)
            ], 'json'), 200);
    }

    /**
     * @param PaginatorInterface $paginator
     * @param Email|null $email
     * @param $page
     * @return JsonResponse
     */
    public function loadClients(PaginatorInterface $paginator, Email $email = null, $page)
    {
        $client = $this->getUser()->getClient();
        $clients = $paginator->paginate($this->manager->searchClients($client), $page, 20);

        $recipients = [];

        if ($email) {
            foreach ($email->getRecipients() as $recipient) {
                $recipients[] = $recipient->getCustomer()->getId();
            }
        }

        $list = $this->renderView('master/email/recipients_list.html.twig', [
            'clients' => $clients,
            'recipients' => $recipients
        ]);

        return new JsonResponse(['list' => $list], 202);
    }

    /**
     * @param Request $request
     * @param SerializerInterface $serializer
     * @param Email|null $email
     * @return JsonResponse|Response
     */
    public function saveDraft(Request $request, SerializerInterface $serializer, Email $email = null)
    {
        if ($request->isXmlHttpRequest()) {
            if (!$email) { $email = new Email(); }

            $form = $this->createForm(EmailType::class, $email, [
                'csrf_protection' => false
            ]);

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $recipients = isset($request->request->get('email')['recipients'])
                    ? $request->request->get('email')['recipients']
                    : [];

                $email = $this->manager->saveEmail($email, $recipients, true);

                return new JsonResponse(['code' => 202, 'status' => 'success', 'data' => ['draftPath' => $this->generateUrl('master.email.save_draft', ['id' => $email->getId()])]], 202);
            } else {
                return new JsonResponse($serializer->serialize(['error' => $form], 'json'), 500);
            }
        }

        return new Response('Request not valid', 400);
    }

    /**
     * @return Response
     */
    public function drafts()
    {
        return $this->render('master/email/drafts.html.twig', [
            'drafts' => $this->manager->getDrafts()
        ]);
    }

    /**
     * @param Email $email
     * @param Sender $sender
     * @return Response
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function sending(Email $email, Sender $sender)
    {
        $process = new Process([
            'php',
            'bin/console',
            'app:send-composed-email',
            $email->getId(), 'client'
        ]);

        $process->setWorkingDirectory(getcwd() . "/../");
        $process->run();

        if (!$process->isSuccessful()) {
            $sender->sendExceptionToDeveloper($process->getErrorOutput());
        }

        return $this->render('master/email/sending.html.twig', [
            'emailId' => $email->getId(),
            'error' => $process->getErrorOutput()
        ]);
    }

    /**
     * @param Email $email
     * @param Sender $sender
     * @return Response
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function sendingError(Email $email, Sender $sender)
    {
        $errorMsg = 'BDS cant send client email with id: ' . $email->getId()
            . '. Reason: Wait too long on response from sending progress. Maybe supervisord doesnt work.';

        $sender->sendExceptionToDeveloper($errorMsg);

        return $this->render('master/email/send_failed.html.twig', [
            'emailId' => $email->getId()
        ]);
    }

    /**
     * @param Email $email
     * @return Response
     */
    public function checkSending(Email $email)
    {
        $sent = 0;

        foreach ($email->getRecipients() as $recipient) {
            if ($recipient->isSent()) $sent++;
        }

        return new Response(floor($sent / (count($email->getRecipients())/ 100)));
    }

    /**
     * @param PaginatorInterface $paginator
     * @param Request $request
     * @return Response
     */
    public function log(PaginatorInterface $paginator, Request $request)
    {
        $logs = $paginator->paginate($this->manager->getLogsQuery(), $request->query->getInt('page', 1), 20);

        return $this->render('master/email/logs.html.twig', [
            'logs' => $logs
        ]);
    }

    /**
     * @param Email $email
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function details(Email $email)
    {
        $recipients = $this->manager->getEmailStats($email);

        return $this->render('master/email/details.html.twig', [
            'email' => $email,
            'recipients' => $recipients
        ]);
    }

    /**
     * @param Request $request
     * @param SerializerInterface $serializer
     * @param Email $email
     * @return JsonResponse|Response
     */
    public function delete(Request $request, SerializerInterface $serializer, Email $email)
    {
        if ($request->isXMLHttpRequest()) {
            try {
                $this->manager->destroyEmail($email);

                return new JsonResponse(['code' => 202, 'status' => 'success'], 202);
            } catch (\Exception $e) {
                return new JsonResponse($serializer->serialize(['error' => $e], 'json'), 500);
            }
        }

        return new Response('Request not valid', 400);
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function automatedEmails(Request $request)
    {
        $automatedEmails = $this->manager->getAutomatedEmails();

        $form = $this->createForm(AutomatedEmails::class, ['automatedEmails' => $automatedEmails]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->manager->flush();

            return $this->redirectToRoute('master.email.auto');
        }

        return $this->render('master/email/automated.html.twig', [
            'form' => $form->createView(),
            'automatedTypes' => $this->manager->getAutomatedTypes(),
            'macros' => $this->manager->getMacrosList()
        ]);
    }
}