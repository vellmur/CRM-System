<?php

namespace App\Manager;

use App\Entity\Client\Client;
use App\Entity\Customer\Email\EmailRecipient;
use App\Entity\Email\AutomatedEmailInterface;
use App\Entity\Email\RecipientInterface;
use App\Entity\Master\Email\AutomatedEmail;
use App\Entity\Master\Email\Email;
use App\Entity\Master\Email\Recipient;
use App\Entity\User\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;

class EmailManager
{
    private $em;

    private $urlGenerator;

    private $twig;

    private $host;

    /**
     * EmailManager constructor.
     * @param EntityManagerInterface $em
     * @param UrlGeneratorInterface $urlGenerator
     * @param Environment $twig
     * @param $httpProtocol
     * @param $domain
     */
    public function __construct(EntityManagerInterface $em, UrlGeneratorInterface $urlGenerator, Environment $twig, $httpProtocol, $domain)
    {
        $this->em = $em;
        $this->urlGenerator = $urlGenerator;
        $this->twig = $twig;
        $this->host = $httpProtocol . '://' . $domain;
    }

    /**
     * @param int $id
     * @return object|null
     */
    public function getEmail(int $id)
    {
        return $this->em->getRepository(Email::class)->find($id);
    }

    /**
     * @param RecipientInterface $recipient
     * @param bool $isDelivered
     * @return RecipientInterface
     */
    public function updateDelivery(RecipientInterface $recipient, bool $isDelivered)
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
     * @param array $clients
     * @param bool $isDraft
     * @return Email
     */
    public function saveEmail(Email $email, array $clients = [], bool $isDraft = true)
    {
        // Remove recipients that now not in a list and remove already added recipients from the list
        foreach ($email->getRecipients() as $key => $recipient) {
            if (in_array($recipient->getClient()->getId(), $clients)) {
                $clients = array_diff($clients, [$recipient->getClient()->getId()]);
            } else {
                $email->removeRecipient($recipient);
            }
        }

        foreach ($clients as $clientId) {
            /** @var Client $client */
            $client = $this->em->find(Client::class, $clientId);
            $recipient = $this->addEmailRecipient($email, $client);

            $this->em->persist($recipient);
        }

        $email->setIsDraft($isDraft);

        $this->em->persist($email);
        $this->em->flush();

        return $email;
    }

    /**
     * @param Recipient $recipient
     * @param string $message
     * @return string|string[]
     */
    public function setMacrosFields(Recipient $recipient, string $message)
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
     * @param string $macros
     * @return string
     */
    private function setMacrosField(Recipient $recipient, string $macros)
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
        }

        return $value;
    }

    /**
     * @return mixed
     */
    public function getSoftwareClients()
    {
        return $this->em->getRepository(Client::class)->getSoftwareClients();
    }

    /**
     * @param $text
     * @return array
     */
    public function searchClients($text)
    {
        return $this->em->getRepository(Client::class)->searchClientsByAllFields($text);
    }

    /**
     * @return mixed
     */
    public function getLogsQuery()
    {
        return $this->em->getRepository(Email::class)->getEmailsLogQuery();
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
        return $this->em->getRepository(Email::class)->findBy(['isDraft' => 1]);
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
     * @return AutomatedEmail[]|\object[]
     */
    public function getAutomatedEmails()
    {
        return $this->em->getRepository(AutomatedEmail::class)->findAll();
    }

    /**
     * @return array
     */
    public function getAutomatedTypes()
    {
        return AutomatedEmail::AUTOMATED_TYPES;
    }

    /**
     * @return array
     */
    public static function getMacrosList()
    {
        return [
            'ClientData' => [
                'ClientName' => 'Name',
                'ConfirmationLink' => 'Confirmation link'
            ]
        ];
    }

    /**
     * @param User $user
     * @return Email
     * @throws \Exception
     */
    public function createUserConfirmationEmail(User $user)
    {
        $email = $this->getAutomatedEmailOfType('confirmation');
        $recipient = $this->addEmailRecipient($email, $user->getClient());

        $this->em->persist($recipient);

        $body = $this->setMacrosFields($recipient, $email->getText());
        $email->setText($body);

        $this->em->flush();

        return $email;
    }

    /**
     * @param string $typeName
     * @return Email
     * @throws \Doctrine\DBAL\ConnectionException|\Exception
     */
    public function getAutomatedEmailOfType(string $typeName)
    {
        $this->emailsExistsOrCreated();
        $typeId = $this->getTypeByName($typeName);
        $automatedEmail = $this->getAutomatedEmailByType($typeId, $typeName);

        $email = $this->createEmailFromAutomated($automatedEmail);
        $this->em->persist($email);
        $this->em->flush();

        return $email;
    }

    /**
     * @param AutomatedEmailInterface $automatedEmail
     * @return Email
     */
    private function createEmailFromAutomated(AutomatedEmailInterface $automatedEmail)
    {
        $email = new Email();
        $email->setAutomatedEmail($automatedEmail);
        $email->setSubject($automatedEmail->getSubject());
        $email->setText($automatedEmail->getText());
        $email->setIsDraft(false);

        return $email;
    }

    /**
     * @param int $typeId
     * @param string $typeName
     * @return AutomatedEmailInterface
     * @throws \Exception
     */
    private function getAutomatedEmailByType(int $typeId, string $typeName)
    {
        /** @var AutomatedEmailInterface $automatedEmail */
        $automatedEmail = $this->em->getRepository(AutomatedEmail::class)->findOneBy(['type' => $typeId]);

        if (!$automatedEmail) {
            throw new \Exception('The automated email with type "' . $typeName
                . '" was not found in the db. Email was not sent.');
        }

        return $automatedEmail;
    }

    /**
     * @throws \Doctrine\DBAL\ConnectionException
     */
    private function emailsExistsOrCreated()
    {
        if (count($this->em->getRepository(AutomatedEmail::class)->findAll()) == 0) {
            $this->em->getConnection()->beginTransaction();

            try {
                foreach (AutomatedEmail::AUTOMATED_TYPES as $typeId => $typeName) {
                    $this->em->persist($this->createDefaultAutomatedEmail($typeId, $typeName));
                }

                $this->em->flush();
                $this->em->getConnection()->commit();
            } catch (\Exception $e) {
                $this->em->getConnection()->rollBack();
                $this->em->clear();

                throw new \Exception('The automated emails were not created (' . $e->getMessage() . ').');
            }
        }
    }

    /**
     * @param int $typeId
     * @param string $typeName
     * @return AutomatedEmail
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    private function createDefaultAutomatedEmail(int $typeId, string $typeName)
    {
        $automatedEmail = new AutomatedEmail();
        $automatedEmail->setType($typeId);
        $automatedEmail->setSubject($this->getAutomatedSubjectByType($typeName));
        $automatedEmail->setText($this->getAutomatedBody($typeName));

        return $automatedEmail;
    }

    /**
     * @param string $typeName
     * @return string
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    private function getAutomatedBody(string $typeName)
    {
        return $this->twig->render('master/email/automated/' . $typeName . '.html.twig');
    }

    /**
     * @param string $typeName
     * @return mixed
     * @throws \Exception
     */
    private function getAutomatedSubjectByType(string $typeName)
    {
        $subjects = [
            'confirmation' => 'Please confirm your email',
            'welcome' => 'Welcome to Black Dirt Software',
            'aborted' => 'Oops! Let’s try again…',
            'failed' => 'You’re Almost Set Up!'
        ];

        if (!isset($subjects[$typeName])) {
            throw new \Exception('Subject for email type "' . $typeName . '" wasn`t found.');
        }

        return  $subjects[$typeName];
    }


    /**
     * @param string $name
     * @return mixed
     * @throws \Exception
     */
    private function getTypeByName(string $name)
    {
        $automatedTypes = array_flip(AutomatedEmail::AUTOMATED_TYPES);

        if (!isset($automatedTypes[$name])) {
            throw new \Exception('Email type doesnt exists.');
        }

        return $automatedTypes[$name];
    }

    /**
     * @param Email $email
     * @param Client $client
     * @return Recipient
     */
    public function addEmailRecipient(Email &$email, Client $client)
    {
        $recipient = new Recipient();
        $recipient->setClient($client);
        $recipient->setEmailAddress($client->getContactEmail());

        $email->addRecipient($recipient);

        return $recipient;
    }

    /**
     * @param string $id
     * @param string $type
     */
    public function saveClickedEmail(string $id, string $type)
    {
        $recipient = $type == 'client' ? $this->em->find(Recipient::class, $id)
            : $this->em->find(EmailRecipient::class, $id);

        if ($recipient && !$recipient->isClicked()) {
            $recipient->setIsOpened(true);
            $recipient->setIsClicked(true);
            $recipient->setIsBounced(false);

            $this->em->flush();
        }
    }
}