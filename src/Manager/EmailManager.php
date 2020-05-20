<?php

namespace App\Manager;

use App\Entity\Building\Building;
use App\Entity\Owner\Email\EmailRecipient;
use App\Entity\Email\AutomatedEmailInterface;
use App\Entity\Email\RecipientInterface;
use App\Entity\Master\Email\AutomatedEmail;
use App\Entity\Master\Email\Email;
use App\Entity\Master\Email\Recipient;
use App\Entity\User\User;
use App\Service\Mail\MailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Twig\Environment;

class EmailManager
{
    private $em;

    private $urlGenerator;

    private $twig;

    private $mailService;

    private $host;

    private $softwareName;

    /**
     * EmailManager constructor.
     * @param EntityManagerInterface $em
     * @param UrlGeneratorInterface $urlGenerator
     * @param Environment $twig
     * @param MailService $mailService
     * @param string $httpProtocol
     * @param string $domain
     * @param string $softwareName
     */
    public function __construct(
        EntityManagerInterface $em,
        UrlGeneratorInterface $urlGenerator,
        Environment $twig,
        MailService $mailService,
        string $httpProtocol,
        string $domain,
        string $softwareName
    ) {
        $this->em = $em;
        $this->urlGenerator = $urlGenerator;
        $this->twig = $twig;
        $this->mailService = $mailService;
        $this->host = $httpProtocol . '://' . $domain;
        $this->softwareName = $softwareName;
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
     * @param bool $isSent
     * @return RecipientInterface
     */
    public function updateDelivery(RecipientInterface $recipient, bool $isSent)
    {
        $recipient->setIsSent($isSent);
        $recipient->setIsDelivered(true);
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
     * @param array $buildings
     * @param bool $isDraft
     * @return Email
     */
    public function saveEmail(Email $email, array $buildings = [], bool $isDraft = true)
    {
        // Remove recipients that now not in a list and remove already added recipients from the list
        foreach ($email->getRecipients() as $key => $recipient) {
            if (in_array($recipient->getBuilding()->getId(), $buildings)) {
                $buildings = array_diff($buildings, [$recipient->getBuilding()->getId()]);
            } else {
                $email->removeRecipient($recipient);
            }
        }

        foreach ($buildings as $buildingId) {
            /** @var Building $building */
            $building = $this->em->find(Building::class, $buildingId);
            $user = $building->getUsers()[0];
            $recipient = $this->addEmailRecipient($email, $user);

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
        $user = $recipient->getUser();

        $value = '';

        switch ($macros) {
            case 'BuildingName':
                $value = $user->getBuilding()->getName();
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
    public function getSoftwareBuildings()
    {
        return $this->em->getRepository(Building::class)->getSoftwareBuildings();
    }

    /**
     * @param $text
     * @return array
     */
    public function searchBuildings($text)
    {
        return $this->em->getRepository(Building::class)->searchBuildingsByAllFields($text);
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
        $recipients = $this->em->getRepository(Recipient::class)->getEmailRecipients($email);
        $recipientsStats = $this->mailService->getMailRecipientsStats($recipients);

        $recipientsStats['list'] = $recipients;

        return $recipientsStats;
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
    public static function getMacrosList()
    {
        return [
            'BuildingData' => [
                'BuildingName' => 'Name',
                'ConfirmationLink' => 'Confirmation link'
            ]
        ];
    }

    /**
     * @param UserInterface $user
     * @return Email
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function createUserConfirmationEmail(UserInterface $user)
    {
        $email = $this->getAutomatedEmailOfType('confirmation');
        $recipient = $this->addEmailRecipient($email, $user);

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
    public function emailsExistsOrCreated()
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
            'welcome' => 'Welcome to ' . $this->softwareName,
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
     * @param User $user
     * @return Recipient
     */
    public function addEmailRecipient(Email &$email, User $user)
    {
        $recipient = new Recipient();
        $recipient->setUser($user);
        $recipient->setEmailAddress($user->getBuilding()->getEmail());

        $email->addRecipient($recipient);

        return $recipient;
    }

    /**
     * @param string $id
     * @param string $type
     */
    public function saveClickedEmail(string $id, string $type)
    {
        $recipient = $type == 'building' ? $this->em->find(Recipient::class, $id)
            : $this->em->find(EmailRecipient::class, $id);

        if ($recipient && !$recipient->isClicked()) {
            $recipient->setIsOpened(true);
            $recipient->setIsClicked(true);
            $recipient->setIsBounced(false);

            $this->em->flush();
        }
    }
}