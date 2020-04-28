<?php

namespace App\Service\Mail;

use App\Entity\Customer\Email\AutoEmail;
use App\Entity\Email\RecipientInterface;
use App\Entity\Customer\Invoice;
use App\Entity\Customer\Email\EmailRecipient;
use App\Entity\Customer\Customer;
use App\Entity\Customer\Email\CustomerEmail;
use App\Entity\Customer\CustomerShare;
use App\Entity\User\User;
use App\Manager\EmailManager;
use App\Manager\MemberEmailManager;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;
use Wa72\HtmlPageDom\HtmlPage;
use Wa72\HtmlPageDom\HtmlPageCrawler;

class Sender
{
    private $mailer;

    private $manager;

    private $memberEmailManager;

    private $router;

    private $templating;

    private $translator;

    public $mailerUser;

    private $domain;

    private $softwareName;

    public function __construct(
        \Swift_Mailer $mailer,
        EmailManager $manager,
        MemberEmailManager $memberEmailManager,
        UrlGeneratorInterface $router,
        Environment $twig,
        TranslatorInterface $translator,
        string $mailerUser,
        string $domain,
        string $softwareName
    ) {
        $this->mailer = $mailer;
        $this->manager = $manager;
        $this->memberEmailManager = $memberEmailManager;
        $this->router = $router;
        $this->templating = $twig;
        $this->translator = $translator;
        $this->mailerUser = $mailerUser;
        $this->domain = $domain;
        $this->softwareName = $softwareName;
    }

    /**
     * @param User $user
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     * @throws \Exception
     */
    public function sendEmailConfirmation(User $user)
    {
        $email = $this->manager->createUserConfirmationEmail($user);

        $this->sendSoftwareMail($email->getRecipients()[0],'emails/composed_email.html.twig', [
            'message' => $email->getText()
        ]);;

        $this->manager->saveSentEmail($email);

        // Notification to a master about new client
        $subject = 'New client join our software';
        $notifyEmails = ['valentinemurnik@gmail.com'];

        if (!$this->isDevelopment()) {
            array_push($notifyEmails, 'cf@blackdirt.org');
        }

        $this->sendMail($this->softwareName, $notifyEmails, 'emails/new_client.html.twig', $subject, [
            'user' => $user
        ]);
    }

    /**
     * @param $senderName
     * @param $to
     * @param $template
     * @param $subject
     * @param array $data
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function sendMail($senderName, $to, $template, $subject, $data = [])
    {
        $message = $this->mailer->createMessage()
            ->setSubject($subject)
            ->setFrom([$this->mailerUser => $senderName])
            ->setTo($to)
            ->setBody($this->templating->render($template, $data), 'text/html');

        $this->mailer->send($message);
    }

    /**
     * @param $recipient
     * @param $template
     * @param $data
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function sendSoftwareMail(RecipientInterface $recipient, $template, $data)
    {
        $message = $this->mailer->createMessage()
            ->setSubject($recipient->getEmailLog()->getSubject())
            ->setFrom([$this->mailerUser => $this->softwareName])
            ->setTo($recipient->getEmailAddress())
            ->setBody($this->templating->render($template, $data));

        $this->sendTrackedMail($message, $recipient);
    }

    /**
     * @param CustomerEmail $email
     * @param Customer $customer
     * @param null $share
     * @return bool
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function sendAutomatedEmail(CustomerEmail $email, Customer $customer, $share = null)
    {
        try {
            $recipient = $this->memberEmailManager->createRecipient($email, $customer);

            $body = $this->templating->render('emails/composed_email.html.twig', [
                'message' => $this->memberEmailManager->setMacrosFields($recipient, $email->getText(), $share)
            ]);

            /** @var \Swift_Message $message */
            $message = $this->mailer->createMessage()
                ->setSubject($email->getSubject())
                ->setFrom([$this->mailerUser => $email->getReplyName()])
                ->setReplyTo([$email->getReplyEmail() => $email->getReplyName()])
                ->setTo($recipient->getCustomer()->getEmail())
                ->setBody($body);

            $delivered = $this->sendTrackedMail($message, $recipient);

            $typeName = AutoEmail::EMAIL_TYPES[$email->getAutomatedEmail()->getType()];

            // If type of email is activation, send CC (copy of email to a farm owner)
            if ($typeName == 'activation') {
                $message->setTo($customer->getClient()->getContactEmail());
                $this->mailer->send($message);
            }
        } catch (Exception $exception) {
            $delivered = false;
        }

        return $delivered;
    }

    /**
     * @param RecipientInterface $recipient
     * @param $message
     * @return bool
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function sendComposedMail(RecipientInterface $recipient, $message)
    {
        try {
            $body = $this->templating->render('emails/composed_email.html.twig', ['message' => $message]);

            /** @var \Swift_Message $message */
            $message = $this->mailer->createMessage()
                ->setBody($body);

            if ($recipient instanceof EmailRecipient) {
                $message->setFrom([$this->mailerUser => $recipient->getEmailLog()->getReplyName()])
                    ->setSubject($recipient->getEmailLog()->getSubject())
                    ->setReplyTo([$recipient->getEmailLog()->getReplyEmail() => $recipient->getEmailLog()->getReplyName()])
                    ->setTo($recipient->getCustomer()->getEmail());
            } else {
                $message->setFrom([$this->mailerUser => $this->softwareName])
                    ->setTo($recipient->getEmailAddress())
                    ->setSubject($recipient->getEmailLog()->getSubject() . ' (' . $recipient->getId() . ')');
            }

            $delivered = $this->sendTrackedMail($message, $recipient);
        } catch (Exception $exception) {
            $delivered = false;
        }

        return $delivered;
    }

    /**
     * @param Customer $customer
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function sendMemberControl(Customer $customer)
    {
        $locale = $customer->getClient()->getOwner()->getLocale()->getCode();

        $profileLink = $this->router->generate('membership_profile', [
            'token' => $customer->getToken(),
            '_locale' => $locale
        ], UrlGeneratorInterface::ABSOLUTE_URL);

        $subject = $this->translator->trans('membership.email_access.control', [], 'labels', $locale);
        $template = 'emails/member/send_control.html.twig';

        $this->sendMail($customer->getClient()->getName(), $customer->getEmail(), $template, $subject, [
            'member' => $customer->getFullname(),
            'email' => $customer->getEmail(),
            'link' => $profileLink
        ]);
    }

    /**
     * @param Invoice $invoice
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function sendCustomerInvoice(Invoice $invoice)
    {
        $client = $invoice->getCustomer()->getClient();

        $template = $this->templating->render('customer/emails/renewal_invoice.html.twig', [
            'invoice' => $invoice
        ]);

        $to = [$client->getContactEmail(), 'kinroom@blackdirt.org'];

        if ($invoice->getCustomer()->getEmail()) $to[] = $invoice->getCustomer()->getEmail();

        $message = $this->mailer->createMessage()
            ->setSubject($this->translator->trans('invoice.title', [], 'labels'))
            ->setFrom([$this->mailerUser => $client->getName()])
            ->setReplyTo([$client->getContactEmail() => $client->getName()])
            ->setTo($to)
            ->setBody($template, 'text/html');

        $this->mailer->send($message);
        $invoice->setIsSent(true);
        
        $this->manager->flush();
    }

    /**
     * @param Customer $customer
     * @param null $message
     * @return string
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function sendWidgetContact(Customer $customer, $message = null)
    {
        try {
            $subject = $message ? 'Contact us message' : 'New subscription';
            if (strlen($customer->getFullname()) > 1) $subject .= ' from ' . $customer->getFullname();

            $message = $this->mailer->createMessage()
                ->setSubject($subject)
                ->setFrom([$this->mailerUser => $this->softwareName])
                ->setReplyTo([$customer->getEmail() => $customer->getFullname() ? $customer->getFullname() : 'Contact'])
                ->setTo([$customer->getClient()->getContactEmail(), 'kinroom@blackdirt.org'])
                ->setBody($this->templating->render('emails/member/contact_send.html.twig', [
                    'subject' => $subject,
                    'customer' => $customer,
                    'message' => $message
                ]), 'text/html');

            $this->mailer->send($message);
            return 'Contact was successfully sent!';
        } catch (Exception $exception) {
            die(var_dump($exception->getMessage()));
        }
    }

    /**
     * @param Customer $customer
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function sendAddedMailchimpContact(Customer $customer)
    {
        try {
            $subject = 'New subscription';
            if (strlen($customer->getFullname()) > 1) $subject .= ' from ' . $customer->getFullname();

            $message = $this->mailer->createMessage()
                ->setSubject($subject)
                ->setFrom([$this->mailerUser => $this->softwareName])
                ->setReplyTo(['cf@blackdirt.org' => $this->softwareName])
                ->setTo(['kinroom@blackdirt.org', 'cf@blackdirt.org'])
                ->setBody($this->templating->render('emails/widget/new_mailchimp_subscription.twig', [
                    'subject' => $subject,
                    'customer' => $customer
                ]), 'text/html');

            $this->mailer->send($message);
        } catch (Exception $exception) {
            throw $exception;
        }
    }

    /**
     * @return bool
     */
    function isDevelopment() : bool {
        return strstr($this->domain, 'testserver')
            || strstr($this->domain, '127.0.0.1')
            || strstr($this->domain, 'customer.local');
    }

    /* ------------------------- Testing functions ------------------------------------- */
    /**
     * @param Customer $member
     * @param $status
     * @param $addresses
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function sendAddressesChanges(Customer $customer, $status, $addresses)
    {
        $subject = $status . ' address change';
        $template = 'emails/member/addresses_updated.html.twig';

        $this->sendMail($customer->getClientName(), $customer->getClient()->getContactEmail(), $template, $subject, [
            'member' => $customer,
            'status' => $status,
            'addresses' => $addresses
        ]);
    }

    /**
     * @param $shareBeforeSkipping
     * @param CustomerShare $share
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function sendSkipWeekNotify($shareBeforeSkipping, CustomerShare $share)
    {
        $to = $this->isDevelopment() ? 'kinroom@blackdirt.org' : 'cf@blackdirt.org';

        $data = [
            'member' => $share->getCustomer(),
            'share' => $share->getShareName(),
            'action' => $shareBeforeSkipping['action'],
            'previousRenewalDate' => $shareBeforeSkipping['renewalDate'],
            'renewalDate' => $share->getRenewalDate()->format('Y-m-d'),
            'previousSharesNum' => $shareBeforeSkipping['pickupsNum'],
            'sharesNum' => $shareBeforeSkipping['pickupsNum'],
            'shareDay' => $share->getShareDay()
        ];

        $message = $this->mailer->createMessage()
            ->setSubject('Customer ' . $shareBeforeSkipping['action'] . ' a week')
            ->setFrom([$this->mailerUser => $this->softwareName])
            ->setTo($to)
            ->setBody($this->templating->render('emails/member/skip_week.html.twig', $data), 'text/html');

        $this->mailer->send($message);
    }

    /**
     * @param Customer $customer
     * @param $shareDay
     * @param $original
     * @param $custom
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function sendCustomizeNotify(Customer $customer, $shareDay, $original, $custom)
    {
        $to = $this->isDevelopment() ? 'valentinemurnik@gmail.com' : $customer->getClient()->getContactEmail();

        $data = [
            'member' => $customer,
            'shareDay' => $shareDay,
            'originalProduct' => $original,
            'customProduct' => $custom,
        ];

        $message = $this->mailer->createMessage()
            ->setSubject('Customer customizing share')
            ->setFrom([$this->mailerUser => 'BlackDirtSoftware'])
            ->setTo($to)
            ->setBody($this->templating->render('emails/member/customize_notify.html.twig', $data), 'text/html');

        $this->mailer->send($message);
    }

    /**
     * Send feedback from customer (profile/feedback tab) to client (farm owner)
     *
     * @param Customer $customer
     * @param $feedback
     */
    public function sendFeedback(Customer $customer, $feedback)
    {
        $shares = [];

        foreach ($feedback['share'] as $shareId => $isSatisfied) {
            $share = $this->memberEmailManager->getShareById($shareId);

            $shares[] = [
                'name' => $share->getName(),
                'isSatisfied' => $isSatisfied == 1 ? 'Satisfied' : 'Not satisfied'
            ];
        }

        // Send customer review to client
        $this->sendMail($this->softwareName,
            $customer->getClient()->getContactEmail(),
            'emails/member/member_review.html.twig',
            'Customer feedback',
            [
                'member' => $customer,
                'shares' => $shares,
                'text' => $feedback['message']
            ]
        );
    }

    /**
     * @param \Swift_Message $message
     * @param RecipientInterface $recipient
     * @return bool
     */
    public function sendTrackedMail(\Swift_Message $message, RecipientInterface $recipient)
    {
        $recipientType = $recipient instanceof EmailRecipient ? 'customer' : 'client';
        $body = $this->addTracking($message->getBody(), $recipient->getId(), $recipientType);

        $message->setBody($body, 'text/html');

        // Add bouncing tracking
        $message->getHeaders()->addTextHeader('X-Mail-Recipient-ID', $recipient->getId());
        $message->getHeaders()->addTextHeader('X-Mail-Recipient-Type', $recipientType);

        $sent = $this->mailer->send($message) > 0;
        $this->manager->updateDelivery($recipient, $sent);

        return $sent;
    }

    /**
     * Add tracking of recipient actions to each email: Opening, Clicking
     *
     * @param string $body
     * @param int $recipientId
     * @param string $recipientType
     * @return string|HtmlPageCrawler
     */
    public function addTracking(string $body, int $recipientId, string $recipientType)
    {
        $html = $this->linkify($body);
        $html = $this->addOpeningTracking($html, $recipientId, $recipientType);
        $body = $this->addClickTracking($html, $recipientId, $recipientType);

        return $body;
    }

    /**
     * Add tracking of clicks to each link inside href attribute of each link
     *
     * @param $html
     * @param $recipientId
     * @param $recipientType
     * @return string|HtmlPageCrawler
     */
    public function addClickTracking($html, $recipientId, $recipientType)
    {
        $dom = new HtmlPage($html);

        $dom->filter('a')->reduce(function (HtmlPageCrawler $link) use ($recipientType, $recipientId) {
            // Replace external link with software link and save original source in data attribute
            if (!stristr($link->getAttribute('href'), $this->domain)) {
                $link->setAttribute('data-redirect-to', $link->getAttribute('href'));
                $link->setAttribute('href', 'https://' . $this->domain);
            }

            // Add recipient id/type and redirect link (if original source saved) in order to track recipient clicks
            $trackedLink = $link->getAttribute('href') . '?email_recipient_type=' . $recipientType . '&email_recipient_id=' . $recipientId;
            if ($link->getAttribute('data-redirect-to')) $trackedLink .= '&page_redirect_to=' . $link->getAttribute('data-redirect-to');
            $link->removeAttribute('data-redirect-to');

            $link->setAttribute('href', $trackedLink);
        });

        return $dom->getCrawler()->html();
    }

    /**
     * Convert all text urls to html links with a tag and href attribute
     *
     * @param $value
     * @param array $protocols
     * @param array $attributes
     * @return mixed
     */
    public function linkify($value, $protocols = ['http', 'mail'], $attributes = [])
    {
        $attr = '';

        foreach ($attributes as $key => $val) {
            $attr = ' ' . $key . '="' . htmlentities($val) . '"';
        }

        $links = [];

        // Extract existing links and tags
        $value = preg_replace_callback('~(<a .*?>.*?</a>|<.*?>)~i', function ($match) use (&$links) {
            return '<' . array_push($links, $match[1]) . '>';
        }, $value);

        // Extract text links for each protocol
        foreach ((array)$protocols as $protocol) {
            switch ($protocol) {
                case 'http':
                case 'https':
                    $value = preg_replace_callback('~(?:(https?)://([^\s<]+)|(www\.[^\s<]+?\.[^\s<]+))(?<![\.,:])~i',
                        function ($match) use ($protocol, &$links, $attr) {
                            if ($match[1]) $protocol = $match[1]; $link = $match[2] ?: $match[3];
                            return '<' . array_push($links, "<a $attr href=\"$protocol://$link\">$link</a>") . '>';
                        }, $value);
                    break;
            }
        }

        // Insert all link
        return preg_replace_callback('/<(\d+)>/', function ($match) use (&$links) { return $links[$match[1] - 1]; }, $value);
    }

    /**
     * @param string $html
     * @param int $recipientId
     * @param string $recipientType
     * @return string|string[]
     */
    public function addOpeningTracking(string $html, int $recipientId, string $recipientType)
    {
        // Generate path to the transparent (hidden) image with unique parameters in order to track message opening
        $path = $this->router->generate('emails.open.tracking', [
            'recipientId' => $recipientId,
            'recipientType' => $recipientType,
            'imageName' => bin2hex(random_bytes(10)) . '.png'
        ]);

        $image = '<img style="visibility:hidden;display:none;" src="https://' . $this->domain . $path . '" />';

        // Include image to the email body
        $pos = strripos($html, '</html>');
        $html = substr_replace($html, $image, $pos, 0);

        return $html;
    }

    /**
     * @param User $user
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function sendResettingEmailMessage(User $user)
    {
        $url = $this->router->generate('app_resetting_reset', [
            'token' => $user->getConfirmationToken()
        ], UrlGeneratorInterface::ABSOLUTE_URL);

        $subject = $this->softwareName . ' - Reset password';

        $this->sendMail($this->softwareName, $user->getEmail(), 'emails/password_resetting.email.twig', $subject, [
            'client' => $user->getClient()->getName(),
            'confirmationUrl' => $url
        ]);
    }

    /**
     * @param $subject
     * @param $error
     * @param null $content
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function sendExceptionNotify($subject, $error, $content = null)
    {
        $now = new \ DateTime();
        $subject = $subject . 'Date: ' . $now->format('Y-m-d H:i:s') . '.';
        $template = 'emails/exception_notify.html.twig';

        $this->sendMail($this->mailerUser, 'kinroom@blackdirt.org', $template, $subject, [
            'subject' => $subject,
            'error' => $error,
            'content' => $content
        ]);
    }

    /**
     * @param $message
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function sendExceptionToDeveloper($message)
    {
        $now = new \DateTime();
        $timezone = new \DateTimeZone('Europe/Kiev');
        $now->setTimezone($timezone);

        $message = $this->mailer->createMessage()
            ->setSubject('Notify about error in ' . $this->domain . ' at '  . $now->format('d-m-Y H:i'))
            ->setFrom([$this->mailerUser => 'BDS'])
            ->setTo('valentinemurnik@gmail.com')
            ->setBody($this->templating->render('emails/error_notify.html.twig', [
                'message' => $message
            ]), 'text/html');

        $this->mailer->send($message);
    }
}