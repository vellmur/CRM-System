<?php

namespace App\Manager;

use App\Entity\Customer\Email\AutoEmail;
use App\Entity\Customer\AvailablePlant;
use App\Entity\Customer\Email\EmailRecipient;
use App\Entity\Customer\Feedback;
use App\Entity\Customer\MemberEmailNotify;
use App\Entity\Customer\CustomerShare;
use App\Entity\Customer\Customer;
use App\Entity\Customer\Share;
use App\Entity\Customer\SuspendedWeek;
use App\Repository\MemberEmailRepository;
use App\Entity\Customer\Email\CustomerEmail;
use App\Entity\Client\Client;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

class MemberEmailManager
{
    private $em;

    private $repo;

    private $router;

    private $translator;

    private $host;

    /**
     * MemberEmailManager constructor.
     * @param EntityManagerInterface $em
     * @param MemberEmailRepository $memberEmailRepository
     * @param UrlGeneratorInterface $router
     * @param TranslatorInterface $translator
     * @param $host
     */
    public function __construct(EntityManagerInterface $em, MemberEmailRepository $memberEmailRepository, UrlGeneratorInterface $router,  TranslatorInterface $translator, $host)
    {
        $this->em = $em;
        $this->repo = $memberEmailRepository;
        $this->router = $router;
        $this->host = $host;
        $this->translator = $translator;
    }

    /**
     * @return \App\Entity\Client[]|array
     */
    public function getSoftwareClients()
    {
        return $this->em->getRepository(Client::class)->getSoftwareClients();
    }

    /**
     * @param Client $client
     * @return array
     */
    public function getLeadsAndContacts(Client $client)
    {
        return $this->em->getRepository(Customer::class)
            ->getLeadsAndContacts($client, $this->getNotifyId('delivery_day'));
    }

    /**
     * @param CustomerEmail $email
     * @param $customers
     * @param bool $isDraft
     * @return CustomerEmail
     */
    public function saveEmail(CustomerEmail $email, $customers, $isDraft = true)
    {
        // Remove recipients that now not in a list and remove already added recipients from the list
        foreach ($email->getRecipients() as $key => $recipient) {
            if (in_array($recipient->getCustomer()->getId(), $customers)) {
                $customers = array_diff($customers, [$recipient->getCustomer()->getId()]);
            } else {
                $email->removeRecipient($recipient);
            }
        }

        // Add new recipient
        foreach ($customers as $customerId) {
            $member = $this->em->find(Customer::class, $customerId);

            if ($member->getEmail()) {
                $recipient = new EmailRecipient();
                $recipient->setCustomer($member);
                $recipient->setEmailAddress($member->getEmail());
                $email->addRecipient($recipient);
                $this->em->persist($recipient);
            }
        }

        $email->setIsDraft($isDraft);

        $this->em->persist($email);
        $this->em->flush();

        return $email;
    }

    /**
     * @param CustomerEmail $email
     * @return CustomerEmail
     */
    public function saveSentEmail(CustomerEmail $email)
    {
        $email->setInProcess(false);
        $email->setIsDraft(false);

        $this->em->persist($email);
        $this->em->flush();

        return $email;
    }

    /**
     * @param Client $client
     * @return \Doctrine\ORM\Query
     */
    public function getLogsQuery(Client $client)
    {
        $logs = $this->repo->getEmailsLogQuery($client);

        return $logs;
    }

    /**
     * @param Client $client
     * @return array
     */
    public function getDrafts(Client $client)
    {
        return $this->repo->findBy(['client' => $client, 'isDraft' => 1]);
    }

    /**
     * @param CustomerEmail $email
     * @return array
     */
    public function getEmailStats(CustomerEmail $email)
    {
        $rep = $this->em->getRepository(EmailRecipient::class);
        $allRecipients = $rep->getEmailRecipients($email);

        $recipients = [
            'delivered' => [],
            'opened' => [],
            'clicked' => [],
            'failed' => [],
            'qty' => [
                'delivered' => 0,
                'opened' => 0,
                'clicked' => 0,
                'failed' => 0
            ]
        ];

        // Extra stats for email feedback
        if ($email->getAutomatedEmail() && $this->getNotifyName($email->getAutomatedEmail()->getType()) == 'feedback') {
            $recipients['feedback'] = [
                'satisfied' => [],
                'notSatisfied' => [],
                'notSure' => []
            ];
        }

        // Sort list of recipients by email status
        foreach ($allRecipients as $recipient) {
            if ($recipient->isDelivered()) {
                $recipients['delivered'][] = $recipient;
                $recipients['qty']['delivered']++;
            } elseif ($recipient->getEmailLog() && !$recipient->getEmailLog()->isInProcess()) {
                $recipients['failed'][] = $recipient;
                $recipients['qty']['failed']++;
            }

            if ($recipient->isOpened()) {
                $recipients['opened'][] = $recipient;
                $recipients['qty']['opened']++;
            }

            if ($recipient->isClicked()) {
                $recipients['clicked'][] = $recipient;
                $recipients['qty']['clicked']++;
            }

            // If email is feedback, count feedbacks
            if (array_key_exists('feedback', $recipients)) {
                if ($recipient->getFeedback()) {
                    if ($recipient->getFeedback()->isSatisfied()) {
                        $recipients['feedback']['satisfied'][] = $recipient;
                    } else {
                        $recipients['feedback']['notSatisfied'][] = $recipient;
                    }
                } else {
                    $recipients['feedback']['notSure'][] = $recipient;
                }
            }
        }

        // Add feedback chart
        if (array_key_exists('feedback', $recipients)) {
            $recipients['feedback']['chart'] = [
                0 => [
                    0 => 'Satisfied',
                    1 => count($recipients['feedback']['satisfied'])
                ],
                1 => [
                    0 => 'Not satisfied',
                    1 => count($recipients['feedback']['notSatisfied'])
                ],
                2 => [
                    0 => 'Not sure',
                    1 => count($recipients['feedback']['notSure'])
                ],
            ];

            array_unshift($recipients['feedback']['chart'], ['CustomerEmail', 'Feedback']);
        }

        $recipients['list'] = $allRecipients;
        $recipients['total'] = count($allRecipients);

        return $recipients;
    }

    /**
     * @param $id
     * @return CustomerEmail|object|null
     */
    public function getEmail($id)
    {
        return $this->repo->find($id);
    }

    /**
     * Get default auto templates for clients (defined by master in twig)
     *
     * @param Environment $templating
     * @return array
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function getDefaultTemplates(Environment $templating)
    {
        $defaultEmails = [];

        foreach (AutoEmail::EMAIL_TYPES as $id => $typeName) {
            $defaultEmails[$typeName]['subject'] = $this->translator->trans('emails.' . $typeName . '.subject', [], 'labels');
            $defaultEmails[$typeName]['body'] = $templating->render('customer/emails/default/' . $typeName . '.html.twig');
        }

        return $defaultEmails;
    }

    /**
     * @param Client $client
     * @return mixed
     */
    public function getAvailablePlants(Client $client)
    {
        $shares = $this->em->getRepository(AvailablePlant::class)->getAvailablePlants($client);

        $list = '';

        if ($shares && count($shares) > 0) {
            $list = '<p>';

            foreach ($shares as $key => $share) {
                $plantName = $share['subName'] == '' ? $share['name'] : $share['name'] . ', ' . $share['subName'];
                $list .= $plantName . '<br/>';
            }

            $list .= '</p>';
        }

        return $list;
    }

    /**
     * @param EmailRecipient $recipient
     * @param Share $share
     * @param $isSatisfied
     * @return string
     */
    public function getFeedbackLink(EmailRecipient $recipient, Share $share, $isSatisfied)
    {
        // Helps to generate absolute path from console command
        $context = $this->router->getContext();
        $context->setHost($this->host);
        $this->router->setContext($context);

        $link = $this->router->generate('membership_profile', [
            'token' => $recipient->getCustomer()->getToken(),
            'id' => $recipient->getId(),
            'shareId' => $share->getId(),
            'isSatisfied' => $isSatisfied
        ], UrlGeneratorInterface::ABSOLUTE_URL);

        $link .= '#feedback';

        return $link;
    }

    /**
     * @param EmailRecipient $recipient
     * @return string
     */
    public function getProfileLink(EmailRecipient $recipient)
    {
        // Helps to generate absolute path from console command
        $context = $this->router->getContext();
        $context->setHost($this->host);
        $this->router->setContext($context);

        $link = $this->router->generate('membership_profile', [
            '_locale' => $recipient->getCustomer()->getClient()->getOwner()->getLocale()->getCode(),
            'token' => $recipient->getCustomer()->getToken(),
            'id' => $recipient->getId()
        ], UrlGeneratorInterface::ABSOLUTE_URL);

        return $link;
    }

    /**
     * Get share for weekly, renewal or lapsed notifications.
     *
     * Weekly notification: If 2 days lefts to next share pickup date.
     * Renewal notification: Sends 1 week(7 days) before the renewal date and last share pickup date.
     * Lapsed notification: If renewal date is the past and share status is not equal to lapsed.
     *
     * Weekly email can be send only once in a week.
     * Renewal and lapsed emails, can be sent only once in a day.
     *
     * @param CustomerShare $share
     * @return array
     */
    public function getShareStatusNotify(CustomerShare $share)
    {
        $member = $share->getMember();

        // This array saves share that needs to be notified by each notify type
        $notify = [];

        // Count days to renewal date (last share pickup date)
        $daysToRenew = $this->countDaysLeft($share->getRenewalDate());

        // If renewal date is past, update status and save as lapsed notify
        if ($daysToRenew < 0) {
            if ($share->getStatusName() != 'LAPSED') {
                 $share->setStatusByName('LAPSED');
                 $this->em->flush();

                // If lapsed notification enabled, save share as lapsed notify
                if ($this->notifyEnabled($member, 'lapsed')) {
                    $notify['lapsed'] = $share;
                }
            }
            // Count days to the renewal date and if 7 days lefts (one share pickup) -> send renewal notification
        } elseif ($daysToRenew == 7) {
            if ($this->notifyEnabled($member, 'renewal')) {
                $notify['renewal'] = $share;
            }
            // Count days to the next share pickup date and if 2 days lefts to share day -> send weekly notification
        } elseif ($this->countDaysToPickup($share) == 2 && $this->canReceiveWeeklyNotify($member)) {
            $notify['weekly'] = $share;
        }

        return $notify;
    }

    /**
     * Get shares to activate or send activation emails to customer
     *
     * @param Client $client
     * @return \Doctrine\Common\Collections\Collection|CustomerShare[] $shares
     */
    public function getNotActiveShares(Client $client)
    {
        return $this->em->getRepository(CustomerShare::class)->getNotActiveShares($client);
    }

    /**
     * @param Client $client
     * @return \Doctrine\Common\Collections\Collection|CustomerShare[] $shares
     */
    public function getNotLapsedShares(Client $client)
    {
        return $this->em->getRepository(CustomerShare::class)->getNotLapsedShares($client);
    }

    /**
     * @param Client $client
     * @return mixed
     */
    public function getWeeklyFeedback(Client $client)
    {
        return $this->em->getRepository(Share::class)->getWeeklyFeedback($client);
    }

    /**
     * Count number of days lefts to the next pickup date
     *
     * @param CustomerShare $share
     * @return int|mixed
     */
    public function countDaysToPickup(CustomerShare $share)
    {
        $daysToPickup = 0;

        $now = new \DateTime("midnight");

        foreach ($share->getPickups() as $pickup) {
            if ($pickup->getDate()->format('Y-m-d') >= $now->format('Y-m-d')) {
                $daysToPickup = $this->countDaysLeft($pickup->getDate());
                break;
            }
        }

        return $daysToPickup;
    }

    /**
     * @param CustomerShare $share
     * @return int|mixed
     */
    public function mustReceiveFeedback(CustomerShare $share)
    {
        $feedbackNotify = false;

        // If feedback notification is enabled in customer data
        if ($this->notifyEnabled($share->getCustomer(), 'feedback')) {
            $now = new \DateTime("midnight");

            foreach ($share->getPickups() as $key => $pickup) {
                // If next pickup date is future -> we need previous pickup date for a feedback
                if ($pickup->getDate() > $now) {
                    // If next pickup date is not first customer pickup
                    if ($key > 0) {
                        // Previous pickup date is pickup needed for the feedback
                        $feedbackDate = $share->getPickups()[$key - 1];

                        // If pickup is not skipped and 2 days gone from the date, customer must receive notify
                        if (!$feedbackDate->isSkipped() && $this->countDaysLeft($feedbackDate->getDate()) == -2) $feedbackNotify = true;
                    }

                    break;
                }
            }
        }

        return $feedbackNotify;
    }

    /**
     * Weekly notification can be send if customer enabled type in profile and weekly type was'nt already sent this week
     *
     * @param Customer $member
     * @return bool
     */
    public function canReceiveWeeklyNotify(Customer $member)
    {
        return $this->notifyEnabled($member, 'weekly')
            && !$this->repo->receivedWeekly($member, $this->getNotifyId('weekly'));
    }

    /**
     * @param $date
     * @return mixed
     */
    public function countDaysLeft($date)
    {
        $startDate = strtotime(date_format($date, 'Y-m-d H:i:s'));
        $now = strtotime(date_format(new \DateTime("midnight"), 'Y-m-d H:i:s'));

        $diffInDays = floor(($startDate - $now) / (60 * 60 * 24));

        return $diffInDays;
    }

    /**
     * @param Client $client
     * @param $typeName
     * @return CustomerEmail
     * @throws \Exception
     */
    public function createAutoLog(Client $client, $typeName)
    {
        // Get id of created CustomerEmail log and get entity of email type
        $autoEmail = $this->em->getRepository(AutoEmail::class)->findOneBy([
            'client' => $client,
            'type' => $this->getNotifyId($typeName)
        ]);

        if ($autoEmail == null) {
            throw new \Exception('Automated email with type ' . $typeName . ' for client with id ' . $client->getId() . 'doesnt exists.');
        }

        $email = new CustomerEmail();
        $email->setClient($client);
        $email->setSubject($autoEmail->getSubject());
        $email->setText($autoEmail->getText());
        $email->setReplyEmail($client->getContactEmail());
        $email->setReplyName($client->getName());
        $email->setIsDraft(false);
        $email->setAutomatedEmail($autoEmail);

        $this->em->persist($email);
        $this->em->flush();

        return $email;
    }

    /**
     * CustomerEmail logs created by client in manual sending
     *
     * @param Client $client
     * @param $subject
     * @param $text
     * @return CustomerEmail
     */
    public function createLog(Client $client, $subject, $text)
    {
        $log = new CustomerEmail();

        $log->setClient($client);
        $log->setIsDraft(false);
        $log->setReplyName($client->getEmail());
        $log->setReplyEmail($client->getContactEmail());
        $log->setSubject($subject);
        $log->setText($text);

        $this->em->persist($log);
        $this->em->flush();

        return $log;
    }

    /**
     * Check if customer enabled given notification type
     *
     * @param Customer $customer
     * @param $typeName
     * @return bool
     */
    public function notifyEnabled(Customer $customer, $typeName)
    {
        $notify = $this->em->getRepository(MemberEmailNotify::class)->findOneBy([
            'customer' => $customer,
            'notifyType' => $this->getNotifyId($typeName)
        ]);

        if ($notify && $notify->isActive()) {
            $result = true;
        } else {
            $result = false;
        }

        return $result;
    }

    /**
     * @param $typeName string
     * @return int
     */
    public function getNotifyId($typeName)
    {
        $types = array_flip(AutoEmail::EMAIL_TYPES);

        return $types[$typeName];
    }

    /**
     * @param $id
     * @return mixed
     */
    public function getNotifyName($id)
    {
        return AutoEmail::EMAIL_TYPES[$id];
    }

    /**
     * @param CustomerEmail $email
     * @param Customer|null $member
     * @param null $address
     * @return EmailRecipient
     */
    public function createRecipient(CustomerEmail $email, Customer $member = null, $address = null)
    {
        $recipient = new EmailRecipient();

        // Save recipient as customer
        if ($member) {
            if ($member->getEmail()) {
                $recipient->setCustomer($member);
                $recipient->setEmailAddress($member->getEmail());
            }
        } else {
            $recipient->setEmailAddress($address);
        }

        $email->addRecipient($recipient);

        $this->em->persist($recipient);
        $this->em->flush();

        return $recipient;
    }

    /**
     * @param CustomerEmail $email
     */
    public function destroyEmail(CustomerEmail $email)
    {
        $this->em->remove($email);
        $this->em->flush();
    }

    /**
     * @param EmailRecipient $recipient
     * @param $message
     * @param null $share
     * @return mixed
     */
    public function setMacrosFields(EmailRecipient $recipient, $message, $share = null)
    {
        foreach ($recipient->getEmailLog()->getMacros() as $macros) {
            foreach ($macros as $key => $macro) {
                // If macros found -> replace it
                if (stristr($message, '{' . $key . '}')) {
                    $value = $this->setCustomerMacros($recipient, $key, $share);
                    $message = str_replace('{' . $key . '}', $value, $message);
                }
            }
        }

        return $message;
    }

    /**
     * @param EmailRecipient $recipient
     * @param $field
     * @param CustomerShare|null $share
     * @return string
     */
    public function setCustomerMacros(EmailRecipient $recipient, $field, CustomerShare $share = null)
    {
        $member = $recipient->getCustomer();

        $value = '';

        switch ($field) {
            case 'ClientName':
                $value = $member->getClient()->getName();
                break;
            case 'Firstname':
                $value = $member->getFirstName();
                break;
            case 'Lastname':
                $value = $member->getLastName();
                break;
            case 'Notes':
                $value = $member->getNotes();
                break;
            case 'CustomerEmail':
                $value = $member->getEmail();
                break;
            case 'Phone':
                $value = $member->getPhone();
                break;
            case 'DeliveryDay':
                $value = $member->getWeekDay();
                break;
            case 'AvailablePlants':
                $value = $this->getAvailablePlants($member->getClient());
                break;
            case 'ProfileLink':
                $value = '<a href="' . $this->getProfileLink($recipient) . '">View profile</a>';
                break;
            case 'SkipWeek':
                $value = '<a href="' . $this->getProfileLink($recipient) . '#skip_week' . '">Skip a week</a>';
                break;
            case 'CustomizeShare':
                $value = '<a href="' . $this->getProfileLink($recipient) . '#customize' . '">Customize a share</a>';
                break;
            case 'RenewLink':
                $value = '<a href="' . $this->getProfileLink($recipient) . '#market' . '">Renew membership</a>';
                break;
            case 'FeedbackLinks':
                if ($share !== null) {
                    $satisfiedLink = $this->getFeedbackLink($recipient, $share->getShare(), '1');
                    $notSatisfiedLink = $this->getFeedbackLink($recipient, $share->getShare(), '0');

                    $value = '<a href="' . $satisfiedLink . '">Satisfied</a> / <a href="' . $notSatisfiedLink . '">Not satisfied</a>';
                } else {
                    $value = '';
                }

                break;
            case 'ShareName':
                $value = $share !== null ? $share->getShareName() : '';
                break;
            case 'ShareRenewal':
                $value = $share !== null ? $share->getRenewalDate()->format('Y-m-d') : '';
                break;
            case 'ShareStatus':
                $value = $share !== null ? $share->getStatusName() : '';
                break;
            case 'ShareDay':
                $value = $share !== null ? $share->getShareDay() : '';
                break;
            case 'ShareLocation':
                $value = $share !== null && $share->getLocation() ? $share->getLocation()->getName() : '';
                break;
            case 'DelType':
                $address = $member->getAddressByType('delivery');
                $value = $address !== null ? $address->getTypeName() : '';
                break;
            case 'DelStreet':
                $address = $member->getAddressByType('delivery');
                $value = $address !== null ? $address->getStreet() : '';
                break;
            case 'DelApartment':
                $address = $member->getAddressByType('delivery');
                $value = $address !== null ? $address->getApartment() : '';
                break;
            case 'DelPostalCode':
                $address = $member->getAddressByType('delivery');
                $value = $address !== null ? $address->getPostalCode() : '';
                break;
            case 'DelCity':
                $address = $member->getAddressByType('delivery');
                $value = $address !== null ? $address->getCity() : '';
                break;
            case 'DelState':
                $address = $member->getAddressByType('delivery');
                $value = $address !== null ? $address->getRegion() : '';
                break;
            case 'BilType':
                $address = $member->getAddressByType('billing');
                $value = $address !== null ? $address->getTypeName() : '';
                break;
            case 'BilStreet':
                $address = $member->getAddressByType('billing');
                $value = $address !== null ? $address->getStreet() : '';
                break;
            case 'BilApartment':
                $address = $member->getAddressByType('billing');
                $value = $address !== null ? $address->getApartment() : '';
                break;
            case 'BilPostalCode':
                $address = $member->getAddressByType('billing');
                $value = $address !== null ? $address->getPostalCode() : '';
                break;
            case 'BilCity':
                $address = $member->getAddressByType('billing');
                $value = $address !== null ? $address->getCity() : '';
                break;
            case 'BilState':
                $address = $member->getAddressByType('billing');
                $value = $address !== null ? $address->getRegion() : '';
                break;
        }

        return $value;
    }

    /**
     * Simple EM flush function
     */
    public function flush()
    {
        $this->em->flush();
    }

    /**
     * @param Client $client
     * @return array
     */
    public function getFeedbackReport(Client $client)
    {
        $feedbackReport = $this->em->getRepository(Feedback::class)->getWeeklyFeedbackReport($client);
        $recipients = $this->em->getRepository(CustomerEmail::class)->countFeedbackWeeklyStats($client, $this->getNotifyId('feedback'));

        $report = [
            'recipients' => $recipients,
            'feedback' => $feedbackReport
        ];

        return $report;
    }

    /**
     * @param $id
     * @return Share|null|object
     */
    public function getShareById($id)
    {
        return $this->em->find(Share::class, $id);
    }

    /**
     * @param Client $client
     * @param $weekDate \DateTime|null
     * @return bool
     */
    public function isWeekSuspended(Client $client, $weekDate = null) : bool
    {
        // If week is not defined set week number to current
        if (!$weekDate) {
            // If today is not Saturday, set date to next Saturday (we getting right week number from last day of week)
            $weekDate = new \DateTime("midnight");
            if ($weekDate->format('w') != 6) $weekDate->modify('next Saturday');
        }

        $suspendedWeek = $this->em->getRepository(SuspendedWeek::class)->findOneBy([
            'client' => $client,
            'week' => $weekDate->format('W'),
            'year' => $weekDate->format('Y')
        ]);

        return $suspendedWeek ? true : false;
    }
}