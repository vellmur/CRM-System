<?php

namespace App\Controller\Owner;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use App\Entity\Owner\Email\OwnerEmail;
use App\Form\Owner\AutoEmails;
use App\Form\Owner\EmailType;
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
     * @param OwnerEmail|null $email
     * @return Response
     */
    public function compose(Request $request, OwnerEmail $email = null)
    {
        $building = $this->getUser()->getBuilding();

        if (!$email) $email = new OwnerEmail();
        $email->setBuilding($building);
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

        $owners = $this->memberManager->searchOwners($building)->getResult();

        foreach ($email->getRecipients() as $recipient) {
            $recipients[] = $recipient->getOwner()->getId();
        }

        return $this->render('owner/emails/compose.html.twig', [
            'form' => $form->createView(),
            'owners' => $owners,
            'recipients' => $recipients,
            'macros' => $email->getMacros()
        ]);
    }

    /**
     * @param PaginatorInterface $paginator
     * @param $page
     * @param OwnerEmail|null $email
     * @return JsonResponse
     */
    public function loadOwners(PaginatorInterface $paginator, $page, OwnerEmail $email = null)
    {
        $building = $this->getUser()->getBuilding();

        $owners = $paginator->paginate($this->memberManager->searchOwners($building), $page, 20);

        $recipients = [];

        if ($email) {
            foreach ($email->getRecipients() as $recipient) {
                $recipients[] = $recipient->getOwner()->getId();
            }
        }

        $list = $this->renderView('owner/forms/recipients_list.html.twig', [
            'owners' => $owners,
            'recipients' => $recipients
        ]);

        return new JsonResponse(['list' => $list], 202);
    }

    /**
     * @ParamConverter("id", class="App\Entity\Owner\Email\OwnerEmail",
     *     options={"mapping": {"id" = "id"}}
     * )
     */
    public function sendingEmail(OwnerEmail $email, Sender $sender)
    {
        $process = new Process([
            'php',
            'bin/console',
            'app:send-composed-email',
            $email->getId(), 'owner'
        ]);

        $process->setWorkingDirectory(getcwd() . "/../");
        $process->run();

        if (!$process->isSuccessful()) {
            $sender->sendExceptionToDeveloper($process->getErrorOutput());
        }

        return $this->render('owner/emails/sending.html.twig', [
            'emailId' => $email->getId(),
            'error' => $process->getErrorOutput()
        ]);
    }

    /**
     * @param Request $request
     * @param OwnerEmail|null $email
     * @return JsonResponse|Response
     */
    public function saveDraft(Request $request, OwnerEmail $email = null)
    {
        if ($request->isXmlHttpRequest()) {
            if (!$email) {
                $email = new OwnerEmail();
                $email->setBuilding($this->getUser()->getBuilding());
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
     * @param OwnerEmail $email
     * @return Response
     */
    public function checkSending(OwnerEmail $email)
    {
        $sent = 0;

        foreach ($email->getRecipients() as $recipient) {
            if ($recipient->isDelivered()) $sent++;
        }

        return new Response(floor($sent / (count($email->getRecipients())/ 100)));
    }

    /**
     * @param OwnerEmail $ownerEmail
     * @param Sender $sender
     * @return Response
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function sendingError(OwnerEmail $ownerEmail, Sender $sender)
    {
        $errorMsg = 'BDS cant send owner email with id: ' . $ownerEmail->getId()
            . '. Reason: Wait too long on response from sending progress. Maybe supervisord doesnt work.';

        $sender->sendExceptionToDeveloper($errorMsg);

        return $this->render('owner/emails/send_failed.html.twig', [
            'emailId' => $ownerEmail->getId()
        ]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function searchRecipients(Request $request)
    {
        $building = $this->getUser()->getBuilding();

        $searchBy = $request->query->get('searchBy');
        $searchText = $request->query->get('search');

        $owners = $this->memberManager->searchOwners($building, $searchBy, $searchText)->getResult();

        $template = $this->render('owner/forms/recipients_list.html.twig', ['owners' => $owners])->getContent();

        return new JsonResponse(
            $this->serializer->serialize([
                'template' => $template,
                'counter' => count($owners)
            ], 'json'), 200);
    }

    /**
     * @param PaginatorInterface $paginator
     * @param Request $request
     * @return Response
     */
    public function log(PaginatorInterface $paginator, Request $request)
    {
        $query = $this->manager->getLogsQuery($this->getUser()->getBuilding());
        $logs = $paginator->paginate($query, $request->query->getInt('page', 1), 20);

        return $this->render('owner/emails/logs.html.twig', [
            'logs' => $logs
        ]);
    }

    /**
     * @return Response
     */
    public function drafts()
    {
        $building = $this->getUser()->getBuilding();
        $drafts = $this->manager->getDrafts($building);

        return $this->render('owner/emails/drafts.html.twig', [
            'drafts' => $drafts
        ]);
    }

    /**
     * @param OwnerEmail $email
     * @return Response
     */
    public function details(OwnerEmail $email)
    {
        $recipients = $this->manager->getEmailStats($email);

        return $this->render('owner/emails/details.html.twig', [
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
        $building = $this->getUser()->getBuilding();
        $form = $this->createForm(AutoEmails::class, $building);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->manager->flush();
            return $this->redirectToRoute('member.email.auto');
        }

        return $this->render('owner/emails/auto.html.twig', [
            'form' => $form->createView(),
            'macros' => OwnerEmail::getMacros(),
            'defaultEmails' => $this->manager->getDefaultTemplates($engine)
        ]);
    }

    /**
     * @param Request $request
     * @param OwnerEmail $email
     * @return JsonResponse|Response
     */
    public function ajaxDelete(Request $request, OwnerEmail $email)
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