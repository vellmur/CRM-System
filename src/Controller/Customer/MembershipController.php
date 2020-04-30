<?php

namespace App\Controller\Customer;

use App\Entity\Customer\Email\EmailRecipient;
use App\Entity\Customer\Customer;
use App\Entity\Customer\VendorOrder;
use App\Form\Customer\ContactType;
use App\Form\Customer\MembershipLoginType;
use App\Form\Customer\CustomerType;
use App\Form\Customer\RenewType;
use App\Form\Customer\VendorOrderType;
use App\Manager\MembershipManager;
use App\Service\Mail\Sender;
use App\Service\Payment\PaymentService;
use JMS\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class MembershipController extends AbstractController
{
    private $manager;

    private $mailer;

    public function __construct(MembershipManager $manager, Sender $sender)
    {
        $this->manager = $manager;
        $this->mailer = $sender;
    }

    /**
     * @param Request $request
     * @param TranslatorInterface $translator
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function login(Request $request, TranslatorInterface $translator)
    {
        $form = $this->createForm(MembershipLoginType::class);
        $form->handleRequest($request);

        if (!$request->isXmlHttpRequest()) {
            if ($form->isSubmitted() && $form->isValid()) {
                $email = $form->getData()['email'];
                $client = isset($form->getData()['client']) ? $this->manager->getClientById($form->getData()['client']) : null;
                $member = $this->manager->findOneByEmail($email, $client);

                if ($member) {
                    $this->mailer->sendMemberControl($member);

                    return $this->redirectToRoute('membership_access_sent');
                } else {
                    $error = new FormError($translator->trans('sign_up.form.email.not_exists', [], 'validators'));
                    $form->get('email')->addError($error);
                }
            }
        }

        return $this->render('customer/membership/login.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @return Response
     */
    public function accessSent()
    {
        return $this->render('customer/membership/access_sent.html.twig');
    }

    /**
     * @param Request $request
     * @param PaymentService $paymentService
     * @param $token
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function signUp(Request $request, PaymentService $paymentService, $token)
    {
        $client = $this->manager->findClientByToken($token);

        if ($client) {
            $member = null;

            // Get info about total shares left, and add existed customer to a form(for prevent unique validation)
            $data = $request->request->get('renew');
            $member = $this->manager->getMemberManager()->findCustomerByData($client, $data['member']);

            $sharesLeft = [];

            if (!$member) {
                $member = new Customer();
                $member->setClient($client);
            } else {
                $sharesLeft = $this->manager->countPickups($member->getShares());
            }

            $form = $this->createForm(RenewType::class, null, [
                'client' => $client,
                'customer' => $member
            ]);

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                try {
                    $this->manager->saveCustomerData($member, $data);

                    $invoice = $paymentService->customerPayment($member, $data);

                    // Save view of renewal competed page
                    $this->manager->saveRenewalView($client, $member->getId(), 'Completed');

                    // If invoice is already paid, renew customer
                    if ($invoice->isPaid()) $this->manager->renewMembership($invoice);
                    if (!$invoice->isSent()) $this->mailer->sendCustomerInvoice($invoice);

                    // Show the invoice
                    return $this->redirect($this->generateUrl('membership_renewal_summary', [
                        'id' => $invoice->getId(),
                        'token' => $member->getToken()
                    ]));
                } catch (\Exception $exception) {
                    $formError = new FormError($exception->getMessage(), null, [], null, 'PaymentError');
                    $form->addError($formError);
                }
            }

            return $this->render('customer/membership/sign_up.html.twig', [
                'form' => $form->createView(),
                'country' => $client->getCountry(),
                'sharesLeft' => $sharesLeft
            ]);
        }

        return $this->render('customer/membership/message.html.twig', [
            'message' => 'Page can not be accessed without the client token!'
        ]);
    }

    /**
     * @param Request $request
     * @param $token string
     * @return JsonResponse
     */
    public function checkEmail(Request $request, $token)
    {
        $client = $this->manager->findClientByToken($token);
        $member = $this->manager->findOneByEmail($request->query->get('email'), $client);
        $memberData = null;

        if ($member) {
            $memberData = [
                'firstname' => $member->getFirstname(),
                'lastname' => $member->getLastname(),
                'phone' => $member->getPhone()
            ];
        }

        $response = new JsonResponse(['member' => $memberData], 200, array());
        $response->setCallback($request->get('callback'));

        return $response;
    }

    /**
     * @param Request $request
     * @param PaymentService $paymentService
     * @param $token
     * @param EmailRecipient|null $recipient
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     * @throws \Exception
     */
    public function profile(Request $request, PaymentService $paymentService, $token, EmailRecipient $recipient = null)
    {
        $member = $this->manager->findOneByToken($token);

        if ($member) {
            $form = $this->createForm(CustomerType::class, $member, ['isMembership' => true]);

            $renewForm = $this->createForm(RenewType::class, null, [
                'client' => $member->getClient(),
                'customer' => $member
            ]);

            $renewForm->handleRequest($request);

            // s after submission of renewal form in renewal tab
            if ($renewForm->isSubmitted() && $renewForm->isValid()) {
                try {
                    $data = $request->request->get('renew');
                    $this->manager->saveCustomerData($member, $data);
                    $invoice = $paymentService->customerPayment($member, $data);

                    // Save view of renewal competed page
                    $this->manager->saveRenewalView($member->getClient(), $member->getId(), 'Completed');

                    // If invoice is already paid, renew customer
                    if ($invoice->isPaid()) $this->manager->renewMembership($invoice);
                    if (!$invoice->isSent()) $this->mailer->sendCustomerInvoice($invoice);

                    // Redirect to a profile, but with the invoice in renewal tab
                    return $this->redirect($this->generateUrl('membership_profile', [
                        'token' => $token,
                        'invoiceId' => $invoice->getId()
                    ]));
                } catch (\Exception $exception) {
                    $formError = new FormError($exception->getMessage(), null, [], null, 'PaymentError');
                    $renewForm->addError($formError);
                }
            }

            // If customer submitted feedback form -> send feedback message to client
            if (isset($request->request->all()['feedback']) and strlen($request->request->get('feedback')['message']) > 2) {
                $this->mailer->sendFeedback($member, $request->request->get('feedback'));
            }

            // If recipient query parameter given, set recipient link as clicked (link from an email)
            if ($recipient) {
                if (!$recipient->isClicked()) $this->manager->setAsClicked($recipient);

                $query = $request->query->all();

                // If exists feedback parameters
                if (isset($query['shareId']) && isset($query['isSatisfied'])) {
                    // Get share date from feedback email date (feedback dates always 2 days after share date)
                    $feedbackDate = new \DateTime($recipient->getEmailLog()->getCreatedAt()->format('Y-m-d'));
                    $shareDate = $feedbackDate->modify('-2 days')->format('Y-m-d');
                    $this->manager->saveFeedback($member, $query['shareId'], $shareDate, $query['isSatisfied'], $recipient);
                }
            }

            // Get invoice id, if exists in request query parameter (after renewal redirection back)
            $invoiceId = isset($request->query->all()['invoiceId']) ? isset($request->query->all()['invoiceId']) : null;

            return $this->render('customer/membership/member/profile.html.twig', [
                'form' => $form->createView(),
                'renewForm' => $renewForm->createView(),
                'invoice' => $invoiceId ? $this->manager->getInvoice($request->query->all()['invoiceId']) : null,
                'date_format' => $member->getClient()->getOwnerDateFormat(),
                'status' =>  $this->manager->getMemberManager()->getMemberStatus($member)
            ]);
        }

        return $this->redirectToRoute('membership');
    }

    /**
     * @param Request $request
     * @return JsonResponse|Response
     */
    public function saveRenewalView(Request $request)
    {
        try {
            $clientId = $request->query->get('clientId');
            $customerId = $request->query->get('customerId');
            $step = $request->query->get('step');
            $this->manager->saveRenewalView($clientId, $customerId, $step);

            $response = new JsonResponse(['result' => 'Purchase (' . $step . ') view was saved!'], 200);
        } catch (\Exception $exception) {
            $response = new JsonResponse(['error' => $exception->getMessage()], 500);
        }

        $response->setCallback($request->get('callback'));

        return $response;
    }

    /**
     * @param Request $request
     * @return JsonResponse|Response
     */
    public function sendRenewalError(Request $request)
    {
        try {
            $error = $request->query->get('error');
            $browser = $request->query->get('browser');
            $content = $request->query->get('content');

            $subject = 'Market widget Javascript Error Notify - ' . $browser . '! ';

            $this->mailer->sendExceptionNotify($subject, $error, $content);
            $response = new JsonResponse(['result' => 'Javascript exception was sent to the admin!'], 200);
        } catch (\Exception $exception) {
            $response = new JsonResponse(['error' => $exception->getMessage()], 500);
        }

        $response->setCallback($request->get('callback'));

        return $response;
    }

    /**
     * @param Request $request
     * @param SerializerInterface $serializer
     * @param $token
     * @return JsonResponse
     */
    public function saveProfile(Request $request, SerializerInterface $serializer, $token)
    {
        $member = $this->manager->findOneByToken($token);

        $status = 'invalid';

        if ($member) {
            $form = $this->createForm(CustomerType::class, $member, ['isMembership' => true]);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                try {
                    $this->manager->getMemberManager()->update($member);
                    $status = "saved";
                } catch (\Exception $e) {
                    $status = $e->getMessage();
                }
            } else {
                return new JsonResponse($serializer->serialize(['error' => $form], 'json'), 500);
            }
        }

        return new JsonResponse(['status' => $status]);
    }

    /**
     * @param Customer $member
     * @return JsonResponse
     */
    public function delete(Customer $member)
    {
        $em = $this->getDoctrine()->getManager();

        $em->remove($member);
        $em->flush();

        return new JsonResponse(['redirect' => $this->generateUrl('membership'), 'status' => 'success'], 202);
    }

    /**
     * @param Request $request
     * @param $token
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function vendorProfile(Request $request, $token)
    {
        $contact = $this->manager->findVendorContactByToken($token);

        if ($contact) {
            $client = $contact->getVendor()->getClient();

            // Create Profile tab form
            $form = $this->createForm(ContactType::class, $contact);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $this->manager->flush();

                return $this->redirect($this->generateUrl('vendor_profile', ['token' => $token]) . '#profile');
            }

            $return = [
                'form' => $form->createView(),
                'date_format' => $client->getOwnerDateFormat()
            ];

            // If vendor is activated add to response orders forms for Orders tab
            if ($contact->getVendor()->isActive()) {
                $order = new VendorOrder();

                $orderForm = $this->createForm(VendorOrderType::class, $order, [
                    'date_format' => $client->getOwnerDateFormat(),
                    'vendors' => [$contact->getVendor()]
                ]);

                $orderForm->handleRequest($request);

                if ($form->isSubmitted() && $orderForm->isValid()) {
                    $order->setClient($client);
                    $this->manager->createVendorOrder($order);

                    return $this->redirect($this->generateUrl('vendor_profile', ['token' => $token]) . '#order');
                }

                $productsForms = [];

                $orders = $this->manager->getVendorOrders($contact->getVendor());

                $return += [
                    'orderForm' => $orderForm->createView(),
                    'orders' => $orders,
                    'productsForms' => $productsForms
                ];
            }

            return $this->render('customer/membership/vendor/profile.html.twig', $return);
        }

        return $this->redirectToRoute('membership');
    }


    /**
     * @param Request $request
     * @param UrlGeneratorInterface $urlGenerator
     * @param Customer $member
     * @return JsonResponse
     */
    public function sendTestimonialMail(Request $request, UrlGeneratorInterface $urlGenerator, Customer $member)
    {
        try {
            $recipient = $this->manager->createTestimonialRecipient(
                $member,
                $request->request->get('email'),
                $request->request->get('firstname'),
                $request->request->get('lastname'),
                $request->request->get('message')
            );

            $signUpLink = $urlGenerator->generate('referred_customer_create', [
                'recipientId' => urlencode(base64_encode($recipient->getId()))
            ], UrlGeneratorInterface::ABSOLUTE_URL);

            if ($signUpLink) {
                $this->mailer->sendMail(
                    $this->getParameter('software_name'),
                    $recipient->getEmail(),
                    'customer/emails/testimonial_email.html.twig',
                    'Invitation to join ' . $member->getClient()->getName(),
                    [
                        'clientName' => $member->getClient()->getName(),
                        'firstname' => $recipient->getFirstname(),
                        'lastname' => $recipient->getLastname(),
                        'message' => $recipient->getMessage(),
                        'signUpLink' => $signUpLink
                    ]
                );
            } else {
                throw new \Exception('Sign up link cant be created.');
            }
        } catch (\Exception $exception) {
            return new JsonResponse(['error' => $exception->getMessage()], 500);
        }

        return new JsonResponse(['status' => 'Testimonial email successfully saved and sent.'], 200);
    }

    /**
     * @param $recipientId
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function referredCustomerCreate($recipientId)
    {
        $recipient = $this->manager->getTestimonialRecipientById(base64_decode(urldecode($recipientId)));

        if (!$recipient) {
            return new Response('Affiliate email wasn`t found.');
        }

        $existsCustomer = $this->manager->findOneByEmail($recipient->getEmail(), $recipient->getAffiliate()->getClient());

        if (!$existsCustomer) {
            $customer = $this->manager->createCustomerFromTestimonial($recipient);

            return $this->redirectToRoute('membership_profile', ['token' => $customer->getToken()]);
        } else {
            return $this->redirectToRoute('membership_profile', ['token' => $existsCustomer->getToken()]);
        }
    }
}