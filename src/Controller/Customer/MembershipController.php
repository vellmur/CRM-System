<?php

namespace App\Controller\Customer;

use App\Entity\Customer\Invoice;
use App\Entity\Customer\Email\EmailRecipient;
use App\Entity\Customer\Customer;
use App\Entity\Customer\CustomerShare;
use App\Entity\Customer\ShareProduct;
use App\Entity\Customer\VendorOrder;
use App\Form\Customer\ContactType;
use App\Form\Customer\MembershipLoginType;
use App\Form\Customer\MemberType;
use App\Form\Customer\RenewType;
use App\Form\Customer\ShareProductType;
use App\Form\Customer\VendorOrderType;
use App\Manager\MembershipManager;
use App\Manager\ShareManager;
use App\Manager\StatisticsManager;
use App\Service\MailService;
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

    public function __construct(MembershipManager $manager, MailService $mailService)
    {
        $this->manager = $manager;
        $this->mailer = $mailService;
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
                    $this->manager->saveMemberData($member, $data);

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

            foreach ($member->getAddresses() as $address) {
                $memberData['address'][$address->getTypeName()] = [
                    'street' => $address->getStreet(),
                    'apartment' => $address->getApartment(),
                    'postalCode' => $address->getPostalCode(),
                    'region' => $address->getRegion(),
                    'city' => $address->getCity()
                ];
            }
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
            $form = $this->createForm(MemberType::class, $member, ['isMembership' => true]);

            $renewForm = $this->createForm(RenewType::class, null, [
                'client' => $member->getClient(),
                'customer' => $member
            ]);

            $renewForm->handleRequest($request);

            // s after submission of renewal form in renewal tab
            if ($renewForm->isSubmitted() && $renewForm->isValid()) {
                try {
                    $data = $request->request->get('renew');
                    $this->manager->saveMemberData($member, $data);
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
                'shares' => $this->manager->getCustomerShares($member),
                'sharesLeft' => $this->manager->countPickups($member->getShares()),
                'products' => $this->manager->getMemberManager()->getAvailableProducts($member->getClient(), 1),
                'sharesProducts' => $this->manager->getSharesProducts($member),
                'customShares' => $this->getCustomShares($member),
                'feedback' => $this->manager->getSharesFeedback($member),
                'invoice' => $invoiceId ? $this->manager->getInvoice($request->query->all()['invoiceId']) : null,
                'date_format' => $member->getClient()->getOwnerDateFormat(),
                'status' =>  $this->manager->getMemberManager()->getMemberStatus($member),
                'isWeekSuspended' => $this->manager->getMemberManager()->getEmailManager()->isWeekSuspended($member->getClient())
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
            $form = $this->createForm(MemberType::class, $member, ['isMembership' => true]);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $addressesChanges = $this->manager->computeAddressesChange($member);

                try {
                    $this->manager->getMemberManager()->update($member);
                    $status = "saved";
                } catch (\Exception $e) {
                    $status = $e->getMessage();
                }

               // if ($addressesChanges) {
                   //  $status = $this->manager->getMemberManager()->getMemberStatus($member);
                    // $this->mailer->sendAddressesChanges($member, $status, $addressesChanges);
             //   }
            } else {
                return new JsonResponse($serializer->serialize(['error' => $form], 'json'), 500);
            }
        }

        return new JsonResponse(['status' => $status]);
    }

    /**
     * @param Request $request
     * @param $token
     * @return JsonResponse
     */
    public function saveFeedback(Request $request, $token)
    {
        $member = $this->manager->findOneByToken($token);

        $status = 'invalid';

        $values = $request->request->all();

        if ($member) {
            $this->manager->saveFeedback($member, $values['shareId'], $values['shareDate'], $values['isSatisfied']);
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

                // Create add product form and list of update products forms for all existed orders
                foreach ($orders as $order) {
                    $products = $this->manager->getMemberManager()->getAvailableProducts($client, $contact->getVendor()->getCategory());

                    // First form in array is Add product form
                    $shareProduct = new ShareProduct();
                    $productsForms[$order->getId()][0] = $this->createForm(ShareProductType::class, $shareProduct, [
                        'client' => $client,
                        'products' => $products
                    ])->createView();

                    // Push to array forms for each product related to order (for updating/removing products)
                    foreach ($this->manager->getOrderProducts($order, 'vendor') as $key => $product) {
                        $productsForms[$order->getId()][$key + 1] = $this->createForm(ShareProductType::class, $product, [
                            'client' => $client,
                            'products' => $products
                        ])->createView();
                    }
                }

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
     * @param Customer $member
     * @return array
     */
    public function getCustomShares(Customer $member)
    {
        $customShares = [];

        foreach ($member->getShares() as $share) {
            foreach ($share->getCustomShares() as $customShare) {
                $customShares[$share->getId()][$customShare->getShareProduct()->getId()] = $customShare;
            }
        }

        return $customShares;
    }

    /**
     * @param Request $request
     * @return JsonResponse|Response
     */
    public function skipPickup(Request $request)
    {
        if ($request->isXMLHttpRequest()) {
            $pickup = $this->manager->getPickup($request->request->get('pickup'));

            // Save share data before skipping / receiving of the pickup
            $share = [
                'action' => $pickup->isSkipped() ? 'receiving' : 'skipping',
                'renewalDate' => $pickup->getShare()->getRenewalDate()->format('Y-m-d'),
                'pickupsNum' => $this->manager->getMemberManager()->countPickups($pickup->getShare())
            ];

            $pickups = $this->manager->controlPickup($pickup);

            if (!($pickups instanceof \Throwable)) {
                $customerShare = $pickup->getShare();
                $dateFormat = $customerShare->getShare()->getClient()->getTwigFormatDate();

                $renewalDate = $customerShare->getRenewalDate()->format($dateFormat);

                $response = [];

                // Add all new pickups (active/suspended) to response
                foreach ($pickups as $key => $pickup) {
                    $response[$key] = [
                        'id' => $pickup->getId(),
                        'date' => $pickup->getDate()->format($dateFormat),
                        'shareId' => $pickup->getShare()->getId(),
                        'renewalDate' => $renewalDate,
                        'isSuspended' => $pickup->isSuspended()
                    ];
                }

                // Send email notify to client about skipping the week by customer
                $this->mailer->sendSkipWeekNotify($share, $pickups[0]->getShare());

                return new JsonResponse([
                    'pickups' => $response
                ], 200);
            } else {
                return new JsonResponse(['error' => $pickup->getMessage()], 500);
            }
        }

        return new Response('Request not valid', 400);
    }

    /**
     * @param Request $request
     * @param CustomerShare $share
     * @param ShareManager $shareManager
     * @return JsonResponse|Response
     */
    public function customizeShare(Request $request, CustomerShare $share, ShareManager $shareManager)
    {
        if ($request->isXMLHttpRequest())
        {
            // Try to get customized share and share product if customized share already exists
            $custom = $shareManager->getCustomShareById($request->request->get('customId'));
            $shareProduct = $shareManager->getShareProductById($request->request->get('shareProductId'));

            // Save name of original product from customized product or from original share product for customize notify
            $originalName = $custom ? $custom->getProduct()->getName() : $shareProduct->getProduct()->getName();

            // Customize share and return custom share
            $customized = $shareManager->customizeShare($share, $request->request->get('productId'), $custom, $shareProduct);

            // Send notification about customized event with original and customized Product names
            $this->mailer->sendCustomizeNotify(
                $share->getMember(),
                $share->getShareDay(),
                $originalName,
                $customized->getProduct()->getName()
            );

            return new JsonResponse(['code' => 202, 'status' => 'success', 'customId' => $customized->getId()], 202);
        }

        return new Response('Request not valid', 400);
    }

    /**
     * Event happens by clicking on payments methods on renewal tab or customer sign-up page. (if credit card method selected)
     * Send reminder to a client that merchant must be configured in order to receive payments.
     *
     * @param Request $request
     * @param $token
     * @param $merchant
     * @param $isSent
     * @return JsonResponse|Response
     */
    public function isMethodConfigured(Request $request, $token, $merchant, $isSent)
    {
        if ($request->isXMLHttpRequest()) {
            $client = $this->manager->findClientByToken($token);

            if ($client) {
                $merchant = $this->manager->gePaymentMerchant($client, $merchant);

                // Merchant settings for the client not found or merchant key not entered
                if (!$merchant || !$merchant->getKey()) {
                    // If email reminder was not sent
                    if ($isSent == 'false') {
                        // Send notify to client, that merchant must be configured
                        $this->mailer->sendMail(
                            'Black Dirt Software',
                            $client->getContactEmail(),
                            'emails/member/merchant_configuration_reminder.html.twig',
                            'You need to add merchant key into BDS',
                            ['client' => $client->getName()]
                        );
                    }

                    return new JsonResponse([
                        'error' => 'Payment by credit card is not possible. Please contact ' . $client->getName()
                            . ' and let them know they have not setup their credit card processing.'
                    ], 500);
                }

                return new JsonResponse(['result' => 'OK!'], 202);
            } else {
                return new JsonResponse(['error' => 'Token not valid!'], 500);
            }
        }

        return new Response('Request not valid', 400);
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
                    'Black Dirt Software',
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

        // Add credits
    }
}