<?php

namespace App\Manager;

use App\Entity\Client\Client;
use App\Entity\Customer\Email\EmailRecipient;
use App\Entity\Master\Email\AutomatedEmail;
use App\Entity\Master\Email\Email;
use App\Entity\Master\Email\Recipient;
use App\Entity\User\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class EmailManager
{
    private $em;

    private $urlGenerator;

    private $host;

    public $automatedTypes = [
        1 => 'confirmation',
        2 => 'welcome',
        3 => 'failed',
        4 => 'aborted'
    ];

    /**
     * @param EntityManagerInterface $em
     * @param UrlGeneratorInterface $urlGenerator
     * @param $httpProtocol
     * @param $domain
     */
    public function __construct(EntityManagerInterface $em, UrlGeneratorInterface $urlGenerator, $httpProtocol, $domain)
    {
        $this->em = $em;
        $this->urlGenerator = $urlGenerator;
        $this->host = $httpProtocol . '://' . $domain;
    }

    /**
     * @param $id
     * @return Email|null|object
     */
    public function getEmail($id)
    {
        return $this->em->getRepository(Email::class)->find($id);
    }

    /**
     * @param Recipient|EmailRecipient|$recipient
     * @param $isDelivered
     * @return mixed
     */
    public function updateDelivery($recipient, $isDelivered)
    {
        $recipient->setIsDelivered($isDelivered);
        $this->em->flush();

        return $recipient;
    }

    /**
     * @param Email $email
     * @return Email
     */
    public function saveSentEmail(Email $email)
    {
        $email->setInProcess(false);
        $email->setIsDraft(false);

        $this->em->persist($email);
        $this->em->flush();

        return $email;
    }

    /**
     * Simple EM flush function
     */
    public function flush()
    {
        $this->em->flush();
    }

    /**
     * @param Email $email
     * @param $clients
     * @param bool $isDraft
     * @return Email
     */
    public function saveEmail(Email $email, $clients, $isDraft = true)
    {
        // Remove recipients that now not in a list and remove already added recipients from the list
        foreach ($email->getRecipients() as $key => $recipient) {
            if (in_array($recipient->getClient()->getId(), $clients)) {
                $clients = array_diff($clients, [$recipient->getClient()->getId()]);
            } else {
                $email->removeRecipient($recipient);
            }
        }

        // Add new recipient
        foreach ($clients as $clientId) {
            $client = $this->em->find(Client::class, $clientId);

            $recipient = new Recipient();
            $recipient->setClient($client);
            $recipient->setEmailLog($email);
            $recipient->setEmailAddress($client->getContactEmail());
            $email->addRecipient($recipient);
            $this->em->persist($recipient);
        }

        $email->setIsDraft($isDraft);

        $this->em->persist($email);
        $this->em->flush();

        return $email;
    }

    /**
     * @param Recipient $recipient
     * @param $message
     * @return string|string[]
     */
    public function setMacrosFields(Recipient $recipient, $message)
    {
        foreach ($this->getMacrosList() as $macros) {
            foreach ($macros as $key => $macro) {
                if (stristr($message, '{' . $key . '}')) {
                    $value = $this->setMacrosField($recipient, $key);
                    $message = str_replace('{' . $key . '}', $value, $message);
                }
            }
        }

        return $message;
    }

    /**
     * @param Recipient $recipient
     * @param $macros
     * @return string
     */
    private function setMacrosField(Recipient $recipient, $macros)
    {
        $client = $recipient->getClient();
        $user = $client->getOwner();

        $value = '';

        switch ($macros) {
            case 'ClientName':
                $value = $client->getName();
                break;
            case 'ConfirmationLink':
                if (!$user->isEnabled()) {
                    $token = $user->getConfirmationToken();
                    $path = $this->urlGenerator->generate('app_registration_confirm', ['token' => $token]);
                    $value = '<a href="' . $this->host . $path . '" target="_blank">Confirmation link</a>';
                } else {
                    $path = $this->urlGenerator->generate('app_login');
                    $value = '<a href="' . $path . '" target="_blank">Confirmation link</a>';
                }

                break;
            case 'SetupPlantsLink':
                $path = $this->urlGenerator->generate('plant_index');
                $value = '<a href="' . $this->host . $path . '" target="_blank">SETUP>Plants</a>';
                break;
            case 'SetupGardensLink':
                $path = $this->urlGenerator->generate('garden_index');
                $value = '<a href="' . $this->host . $path . '" target="_blank">SETUP>Gardens</a>';
                break;
            case 'SetupCropsLink':
                $path = $this->urlGenerator->generate('crops_garden');
                $value = '<a href="' . $this->host . $path . '" target="_blank">MANAGE CROPS>Plants In Garden</a>';
                break;
        }

        return $value;
    }

    /**
     * @return mixed
     */
    public function getSoftwareClients()
    {
        $clients = $this->em->getRepository(Client::class)->getSoftwareClients();

        return $clients;
    }

    /**
     * @param $text
     * @return array
     */
    public function searchClients($text)
    {
        $clients = $this->em->getRepository(Client::class)->searchClientsByAllFields($text);

        return $clients;
    }

    /**
     * @return mixed
     */
    public function getLogsQuery()
    {
        $logs = $this->em->getRepository(Email::class)->getEmailsLogQuery();

        return $logs;
    }

    /**
     * @param Email $email
     * @return array
     */
    public function getEmailStats(Email $email)
    {
        $allRecipients = $this->em->getRepository(Recipient::class)->getEmailRecipients($email);

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
        }

        $recipients['list'] = $allRecipients;
        $recipients['total'] = count($allRecipients);

        return $recipients;
    }

    /**
     * @return array
     */
    public function getDrafts()
    {
        $drafts = $this->em->getRepository(Email::class)->findBy(['isDraft' => 1]);

        return $drafts;
    }

    /**
     * @param Email $email
     */
    public function destroyEmail(Email $email)
    {
        $this->em->remove($email);
        $this->em->flush();
    }

    /**
     * @param int $recipientId
     * @param string $recipientType
     */
    public function setAsOpened($recipientId, $recipientType)
    {
        $repository = $recipientType == 'client' ? $this->em->getRepository(Recipient::class)
            : $this->em->getRepository(EmailRecipient::class);

        $recipient = $repository->findOneBy(['id' => $recipientId, 'isOpened' => false]);

        if ($recipient) {
            $recipient->setIsOpened(true);
            $recipient->setIsBounced(false);

            $this->em->flush();
        }
    }

    /**
     * @return AutomatedEmail[]|\object[]
     */
    public function getAutomatedEmails()
    {
        $emails = $this->em->getRepository(AutomatedEmail::class)->findAll();

        return $emails;
    }

    /**
     * @return array
     */
    public function getAutomatedTypes()
    {
        return $this->automatedTypes;
    }

    /**
     * @return array
     */
    public static function getMacrosList()
    {
        $macros = [
            'ClientData' => [
                'ClientName' => 'Name',
                'ConfirmationLink' => 'Confirmation link'
            ],
            'Pages' => [
                'SetupPlantsLink' => 'Setup plants',
                'SetupGardensLink' => 'Setup gardens',
                'SetupCropsLink' => 'Setup crops'
            ]
        ];

        return $macros;
    }

    /**
     * @param $type
     * @return Email|null|object
     */
    public function getAutomatedEmail($type)
    {
        $id = array_flip($this->automatedTypes)[$type];

        $email = $this->em->getRepository(Email::class)->find($id);

        return $email;
    }

    public function getTestUser()
    {
        return $this->em->getRepository(User::class)->findOneBy(['email' => 'valentinemurnik@gmail.com']);
    }

    /**
     * @param User $user
     * @return Email
     */
    public function createUserConfirmationEmail(User $user)
    {
        $email = $this->createAutomatedEmail('confirmation');

        $recipient = new Recipient();
        $recipient->setClient($user->getClient());
        $recipient->setEmailAddress($user->getClient()->getContactEmail());
        $this->em->persist($recipient);

        $body = $this->setMacrosFields($recipient, $email->getText());
        $email->setText($body);

        $email->addRecipient($recipient);

        $this->em->flush();

        return $email;
    }

    /**
     * @param $type
     * @return Email
     */
    public function createAutomatedEmail($type)
    {
        $typeId = array_flip($this->automatedTypes)[$type];
        $automatedEmail = $this->em->getRepository(AutomatedEmail::class)->find($typeId);

        $email = new Email();
        $email->setAutomatedEmail($automatedEmail);
        $email->setSubject($automatedEmail->getSubject());
        $email->setText($automatedEmail->getText());
        $email->setIsDraft(false);

        $this->em->persist($email);
        $this->em->flush();

        return $email;
    }

    /**
     * @param Email $email
     * @param Client $client
     * @return Recipient
     */
    public function addEmailRecipient(Email $email, Client $client)
    {
        $recipient = new Recipient();
        $recipient->setClient($client);
        $recipient->setEmailAddress($client->getContactEmail());

        $this->em->persist($recipient);

        $email->addRecipient($recipient);
        $this->em->flush();

        return $recipient;
    }

    /**
     * @return Client[]|array|\object[]
     */
    public function getNewConfirmedClients()
    {
        $clients = $this->em->getRepository(Client::class)->getNewConfirmedClients();

        return $clients;
    }

    /**
     * @return Client[]|array|\object[]
     */
    public function getPendingClients()
    {
        $clients = $this->em->getRepository(Client::class)->getPendingClients();

        return $clients;
    }

    /**
     * @return Client[]|array|\object[]
     */
    public function getSetupAbortedClients()
    {
        $clients = $this->em->getRepository(Client::class)->getSetupAbortedClients();

        return $clients;
    }
}