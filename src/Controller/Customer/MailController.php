<?php

namespace App\Controller\Customer;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use App\Entity\Customer\Email\CustomerEmail;
use App\Form\Customer\AutoEmails;
use App\Form\Customer\EmailType;
use App\Manager\MemberEmailManager;
use App\Manager\MemberManager;
use App\Service\Mail\Sender;
use JMS\Serializer\SerializerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Process\Process;
use Twig\Environment;

class MailController extends AbstractController
{
    private $manager;

    private $memberManager;

    private $serializer;

    /**
     * MemberEmailsController constructor.
     * @param MemberEmailManager $manager
     * @param MemberManager $memberManager
     * @param SerializerInterface $serializer
     */
    public function __construct(MemberEmailManager $manager, MemberManager $memberManager, SerializerInterface $serializer)
    {
        $this->manager = $manager;
        $this->memberManager = $memberManager;
        $this->serializer = $serializer;
    }

    /**
     * @param Request $request
     * @param CustomerEmail|null $email
     * @return Response
     */
    public function compose(Request $request, CustomerEmail $email = null)
    {
        $client = $this->getUser()->getClient();

        if (!$email) $email = new CustomerEmail();
        $email->setClient($client);
        $form = $this->createForm(EmailType::class, $email);
        $form->handleRequest($request);

        $recipients = [];

        if ($form->isSubmitted()) {
            // If action is to send email and email haven`t recipients, show error
            isset($request->request->get('email')['recipients'])
                ? $recipients = $request->request->get('email')['recipients']
                : $form->addError(new FormError('Recipients list is empty!'));

            if ($form->isSubmitted() && $form->isValid()) {
                $email = $this->manager->saveEmail($email, $recipients, false);

                return $this->redirectToRoute('member.email.sending', ['id' => $email->getId()]);
            }
        }

        $customers = $this->memberManager->searchCustomers($client)->getResult();

        foreach ($email->getRecipients() as $recipient) {
            $recipients[] = $recipient->getCustomer()->getId();
        }

        return $this->render('customer/emails/compose.html.twig', [
            'form' => $form->createView(),
            'customers' => $customers,
            'recipients' => $recipients,
            'macros' => $email->getMacros()
        ]);
    }

    /**
     * @param PaginatorInterface $paginator
     * @param $page
     * @param CustomerEmail|null $email
     * @return JsonResponse
     */
    public function loadCustomers(PaginatorInterface $paginator, $page, CustomerEmail $email = null)
    {
        $client = $this->getUser()->getClient();

        $customers = $paginator->paginate($this->memberManager->searchCustomers($client), $page, 20);

        $recipients = [];

        if ($email) {
            foreach ($email->getRecipients() as $recipient) {
                $recipients[] = $recipient->getCustomer()->getId();
            }
        }

        $list = $this->renderView('customer/forms/recipients_list.html.twig', [
            'customers' => $customers,
            'recipients' => $recipients
        ]);

        return new JsonResponse(['list' => $list], 202);
    }

    /**
     * @ParamConverter("id", class="App\Entity\Customer\Email\CustomerEmail",
     *     options={"mapping": {"id" = "id"}}
     * )
     */
    public function sendingEmail(CustomerEmail $email, Sender $sender)
    {
        $process = new Process([
            'php',
            'bin/console',
            'app:send-composed-email',
            $email->getId(), 'customer'
        ]);

        $process->setWorkingDirectory(getcwd() . "/../");
        $process->run();

        if (!$process->isSuccessful()) {
            $sender->sendExceptionToDeveloper($process->getErrorOutput());
        }

        return $this->render('customer/emails/sending.html.twig', [
            'emailId' => $email->getId(),
            'error' => $process->getErrorOutput()
        ]);
    }

    /**
     * @param Request $request
     * @param CustomerEmail|null $email
     * @return JsonResponse|Response
     */
    public function saveDraft(Request $request, CustomerEmail $email = null)
    {
        if ($request->isXmlHttpRequest()) {
            if (!$email) {
                $email = new CustomerEmail();
                $email->setClient($this->getUser()->getClient());
            }

            $form = $this->createForm(EmailType::class, $email, [
                'csrf_protection' => false
            ]);

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $recipients = isset($request->request->get('email')['recipients'])
                    ? $request->request->get('email')['recipients']
                    : [];

                $email = $this->manager->saveEmail($email, $recipients, true);

                return new JsonResponse(['code' => 202, 'status' => 'success', 'data' => [
                    'draftPath' => $this->generateUrl('member.email.save_draft', ['id' => $email->getId()])
                ]], 202);
            } else {
                return new JsonResponse($this->serializer->serialize(['error' => $form], 'json'), 500);
            }
        }

        return new Response('Request not valid', 400);
    }

    /**
     * @param CustomerEmail $email
     * @return Response
     */
    public function checkSending(CustomerEmail $email)
    {
        $sent = 0;

        foreach ($email->getRecipients() as $recipient) {
            if ($recipient->isDelivered()) $sent++;
        }

        return new Response(floor($sent / (count($email->getRecipients())/ 100)));
    }

    /**
     * @param CustomerEmail $customerEmail
     * @param Sender $sender
     * @return Response
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function sendingError(CustomerEmail $customerEmail, Sender $sender)
    {
        $errorMsg = 'BDS cant send customer email with id: ' . $customerEmail->getId()
            . '. Reason: Wait too long on response from sending progress. Maybe supervisord doesnt work.';

        $sender->sendExceptionToDeveloper($errorMsg);

        return $this->render('customer/emails/send_failed.html.twig', [
            'emailId' => $customerEmail->getId()
        ]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function searchRecipients(Request $request)
    {
        $client = $this->getUser()->getClient();

        $searchBy = $request->query->get('searchBy');
        $searchText = $request->query->get('search');

        $customers = $this->memberManager->searchCustomers($client, $searchBy, $searchText)->getResult();

        $template = $this->render('customer/forms/recipients_list.html.twig', ['customers' => $customers])->getContent();

        return new JsonResponse(
            $this->serializer->serialize([
                'template' => $template,
                'counter' => count($customers)
            ], 'json'), 200);
    }

    /**
     * @param PaginatorInterface $paginator
     * @param Request $request
     * @return Response
     */
    public function log(PaginatorInterface $paginator, Request $request)
    {
        $query = $this->manager->getLogsQuery($this->getUser()->getClient());
        $logs = $paginator->paginate($query, $request->query->getInt('page', 1), 20);

        return $this->render('customer/emails/logs.html.twig', [
            'logs' => $logs
        ]);
    }

    /**
     * @return Response
     */
    public function drafts()
    {
        $client = $this->getUser()->getClient();
        $drafts = $this->manager->getDrafts($client);

        return $this->render('customer/emails/drafts.html.twig', [
            'drafts' => $drafts
        ]);
    }

    /**
     * @param CustomerEmail $email
     * @return Response
     */
    public function details(CustomerEmail $email)
    {
        $recipients = $this->manager->getEmailStats($email);

        return $this->render('customer/emails/details.html.twig', [
            'email' => $email,
            'recipients' => $recipients
        ]);
    }

    /**
     * @param Request $request
     * @param Environment $engine
     * @return Response
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function autoEmails(Request $request, Environment $engine)
    {
        $client = $this->getUser()->getClient();
        $form = $this->createForm(AutoEmails::class, $client);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->manager->flush();
            return $this->redirectToRoute('member.email.auto');
        }

        return $this->render('customer/emails/auto.html.twig', [
            'form' => $form->createView(),
            'macros' => CustomerEmail::getMacros(),
            'defaultEmails' => $this->manager->getDefaultTemplates($engine)
        ]);
    }

    /**
     * @param Request $request
     * @param CustomerEmail $email
     * @return JsonResponse|Response
     */
    public function ajaxDelete(Request $request, CustomerEmail $email)
    {
        if ($request->isXMLHttpRequest()) {
            try {
                $this->manager->destroyEmail($email);

                return new JsonResponse(['code' => 202, 'status' => 'success'], 202);
            } catch (\Exception $e) {
                return new JsonResponse($this->serializer->serialize(['error' => $e], 'json'), 500);
            }
        }

        return new Response('Request not valid', 400);
    }
}