<?php

namespace App\Manager;

use App\Entity\Customer\CustomerEmailNotify;
use App\Entity\Customer\Email\AutoEmail;
use App\Entity\Customer\Email\EmailRecipient;
use App\Entity\Customer\Customer;
use App\Repository\MemberEmailRepository;
use App\Entity\Customer\Email\CustomerEmail;
use App\Entity\Building\Building;
use App\Service\Mail\MailService;
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

    private $mailService;

    private $host;

    /**
     * MemberEmailManager constructor.
     * @param EntityManagerInterface $em
     * @param MemberEmailRepository $memberEmailRepository
     * @param UrlGeneratorInterface $router
     * @param TranslatorInterface $translator
     * @param MailService $mailService
     * @param $host
     */
    public function __construct(
        EntityManagerInterface $em,
        MemberEmailRepository $memberEmailRepository,
        UrlGeneratorInterface $router,
        TranslatorInterface $translator,
        MailService $mailService,
        $host
    ) {
        $this->em = $em;
        $this->repo = $memberEmailRepository;
        $this->router = $router;
        $this->host = $host;
        $this->translator = $translator;
        $this->mailService = $mailService;
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
     * @param Building $building
     * @return \Doctrine\ORM\Query
     */
    public function getLogsQuery(Building $building)
    {
        return $this->repo->getEmailsLogQuery($building);
    }

    /**
     * @param Building $building
     * @return array
     */
    public function getDrafts(Building $building)
    {
        return $this->repo->findBy(['building' => $building, 'isDraft' => 1]);
    }

    /**
     * @param CustomerEmail $email
     * @return array
     */
    public function getEmailStats(CustomerEmail $email)
    {
        $rep = $this->em->getRepository(EmailRecipient::class);
        $recipients = $rep->getEmailRecipients($email);

        $recipientsStats = $this->mailService->getMailRecipientsStats($recipients);

        // Extra stats for email feedback
        if ($email->getAutomatedEmail() && AutoEmail::EMAIL_TYPES[$email->getAutomatedEmail()->getType()] == 'feedback') {
            $recipientsStats['feedback'] = [
                'satisfied' => [],
                'notSatisfied' => [],
                'notSure' => []
            ];

            foreach ($recipients as $recipient) {
                if ($recipient->getFeedback()) {
                    if ($recipient->getFeedback()->isSatisfied()) {
                        $recipientsStats['feedback']['satisfied'][] = $recipient;
                    } else {
                        $recipientsStats['feedback']['notSatisfied'][] = $recipient;
                    }
                } else {
                    $recipientsStats['feedback']['notSure'][] = $recipient;
                }
            }

            $recipientsStats['feedback']['chart'] = [
                0 => [
                    0 => 'Satisfied',
                    1 => count($recipientsStats['feedback']['satisfied'])
                ],
                1 => [
                    0 => 'Not satisfied',
                    1 => count($recipientsStats['feedback']['notSatisfied'])
                ],
                2 => [
                    0 => 'Not sure',
                    1 => count($recipientsStats['feedback']['notSure'])
                ],
            ];

            array_unshift($recipientsStats['feedback']['chart'], ['CustomerEmail', 'Feedback']);
        }

        $recipientsStats['list'] = $recipients;

        return $recipientsStats;
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
     * Get default auto templates for buildings (defined by master in twig)
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
            '_locale' => 'en',
            'token' => $recipient->getCustomer()->getToken(),
            'id' => $recipient->getId()
        ], UrlGeneratorInterface::ABSOLUTE_URL);

        return $link;
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
     * @return false|float
     * @throws \Exception
     */
    public function countDaysLeft($date)
    {
        $startDate = strtotime(date_format($date, 'Y-m-d H:i:s'));
        $now = strtotime(date_format(new \DateTime("midnight"), 'Y-m-d H:i:s'));

        return floor(($startDate - $now) / (60 * 60 * 24));
    }

    /**
     * @param Building $building
     * @param $typeName
     * @return CustomerEmail
     * @throws \Exception
     */
    public function createAutoLog(Building $building, $typeName)
    {
        // Get id of created CustomerEmail log and get entity of email type
        $autoEmail = $this->em->getRepository(AutoEmail::class)->findOneBy([
            'building' => $building,
            'type' => $this->getNotifyId($typeName)
        ]);

        if ($autoEmail == null) {
            throw new \Exception('Automated email with type ' . $typeName . ' for building with id ' . $building->getId() . 'doesnt exists.');
        }

        $email = new CustomerEmail();
        $email->setBuilding($building);
        $email->setSubject($autoEmail->getSubject());
        $email->setText($autoEmail->getText());
        $email->setReplyEmail($building->getEmail());
        $email->setReplyName($building->getName());
        $email->setIsDraft(false);
        $email->setAutomatedEmail($autoEmail);

        $this->em->persist($email);
        $this->em->flush();

        return $email;
    }

    /**
     * CustomerEmail logs created by building in manual sending
     *
     * @param Building $building
     * @param $subject
     * @param $text
     * @return CustomerEmail
     */
    public function createLog(Building $building, $subject, $text)
    {
        $log = new CustomerEmail();

        $log->setBuilding($building);
        $log->setIsDraft(false);
        $log->setReplyName($building->getEmail());
        $log->setReplyEmail($building->getEmail());
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
        $notify = $this->em->getRepository(CustomerEmailNotify::class)->findOneBy([
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
     * @return string|string[]
     */
    public function setMacrosFields(EmailRecipient $recipient, $message)
    {
        foreach ($recipient->getEmailLog()->getMacros() as $macros) {
            foreach ($macros as $key => $macro) {
                // If macros found -> replace it
                if (stristr($message, '{' . $key . '}')) {
                    $value = $this->setCustomerMacros($recipient, $key);
                    $message = str_replace('{' . $key . '}', $value, $message);
                }
            }
        }

        return $message;
    }

    /**
     * @param EmailRecipient $recipient
     * @param $field
     * @return string
     */
    public function setCustomerMacros(EmailRecipient $recipient, $field)
    {
        $member = $recipient->getCustomer();

        $value = '';

        switch ($field) {
            case 'BuildingName':
                $value = $member->getBuilding()->getName();
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
            case 'ProfileLink':
                $value = '<a href="' . $this->getProfileLink($recipient) . '">View profile</a>';
                break;
            case 'RenewLink':
                $value = '<a href="' . $this->getProfileLink($recipient) . '#market' . '">Renew membership</a>';
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
}