<?php

namespace App\Manager;

use App\Entity\Client\Affiliate;
use App\Entity\Client\Client;
use App\Entity\Customer\Email\AutoEmail;
use App\Entity\Customer\Location;
use App\Entity\Client\ModuleAccess;
use App\Entity\Client\Referral;
use App\Entity\Client\Team;
use App\Entity\User\User;
use App\Security\AccessUpdater;
use App\Service\MailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

class RegistrationManager
{
    private $em;

    private $passwordEncoder;

    private $token;

    private $twig;

    private $translator;

    private $mailService;

    public function __construct(
        EntityManagerInterface $em,
        UserPasswordEncoderInterface $passwordEncoder,
        TokenGeneratorInterface $token,
        Environment $twig,
        TranslatorInterface $translator,
        MailService $mailService
    ) {
        $this->em = $em;
        $this->passwordEncoder = $passwordEncoder;
        $this->token = $token;
        $this->twig = $twig;
        $this->translator = $translator;
        $this->mailService = $mailService;
    }

    /**
     * @param User $user
     * @param $clientName
     * @param $refCode
     * @return User|FormError
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function signUpUser(User $user, $clientName, $refCode)
    {
        $this->em->getConnection()->beginTransaction();

        try {
            $client = $this->createClient($user, $clientName);
            $this->createAccess($client);
            $this->createAffiliate($client);

            if ($refCode) {
                $affiliate = $this->em->getRepository(Affiliate::class)->findOneBy(['referralCode' => $refCode]);
                if ($affiliate) $this->createReferral($client, $affiliate);
            }

            $this->createUser($user,'ROLE_OWNER');
            $this->createTeam($client, $user);
            $this->createAutomatedEmails($client, $user->getLocale()->getCode());

            $this->em->flush();

            $this->mailService->sendEmailConfirmation($user);

            $this->em->getConnection()->commit();

            return $user;
        } catch (\Throwable $e) {
            $this->em->getConnection()->rollBack();
            $this->em->clear();

            return new FormError(
                'Error while trying to save user: ' . $e->getMessage() . ' on line - ' . $e->getLine() .  '.In file ' . $e->getFile()
            );
        }
    }

    /**
     * @param Client $client
     * @param User $user
     * @param $role
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function newUser(Client $client, User $user, $role)
    {
        $this->em->getConnection()->beginTransaction();

        try {
            $this->createUser($user, $role);
            $this->createTeam($client, $user);

            $this->em->flush();
            $this->em->getConnection()->commit();
            $this->em->clear();
        } catch (\Exception $e) {
            $this->em->getConnection()->rollback();
            $this->em->clear();

            throw $e;
        }
    }

    /**
     * @param User $owner
     * @param $name
     * @return Client
     */
    public function createClient(User $owner, $name)
    {
        $client = new Client();
        $client->setName($name);
        $client->setEmail($owner->getEmail());

        // Add basic delivery location
        $homeDeliveryName = $this->translator->trans('membership.renew.location.home_delivery', [], 'labels');
        $location = new Location();
        $location->setName(mb_strtoupper($homeDeliveryName));
        $location->setTypeByName('Delivery');
        $location->addWorkDays();

        $this->em->persist($location);
        $client->addLocation($location);

        $this->em->persist($client);

        return $client;
    }

    /**
     * @param User $user
     * @param $roles
     */
    public function createUser(User $user, $roles)
    {
        $roles = is_array($roles) ? $roles : [$roles];
        $user->setRoles($roles);
        $user->setPassword($this->passwordEncoder->encodePassword($user, $user->getPlainPassword()));

        if (in_array('ROLE_OWNER', $roles)) {
            $user->setConfirmationToken($this->token->generateToken());
        }

        $this->em->persist($user);
    }

    /**
     * @param Client $client
     * @throws \Exception
     */
    private function createAccess(Client $client)
    {
        // Set access to expired date after sign-up
        $today = new \DateTime();
        $trialExtendsAt = new \DateTime();
        $trialExtendsAt->modify('+' . AccessUpdater::TRIAL_DAYS . ' days');

        foreach (ModuleAccess::MODULES as $id => $name) {
            $access = new ModuleAccess();
            $access->setClient($client);
            $access->setModule($id);
            $access->setUpdatedAt($today);
            $access->setExpiredAt($today);
            $access->setStatusByName('LAPSED');

            $this->em->persist($access);
        }
    }

    /**
     * @param Client $client
     * @param User $user
     */
    public function createTeam(Client $client, User $user)
    {
        $team = new Team();
        $team->setUser($user);

        $user->setTeam($team);
        $client->addTeam($team);

        $this->em->persist($team);
    }

    /**
     * @param Client $client
     * @param string $locale
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function createAutomatedEmails(Client $client, string $locale)
    {
        // Run through all automated email types defined in manager
        foreach (AutoEmail::EMAIL_TYPES as $id => $typeName) {
            $subject = $this->translator->trans(('emails.' . $typeName . '.subject'), [], 'labels', $locale);
            $template = $this->twig->render('customer/emails/default/' . $typeName . '.html.twig');

            $email = new AutoEmail();
            $email->setType($id);
            $email->setSubject($subject);
            $email->setText($template);
            $client->addAutoMail($email);

            $this->em->flush();
        }
    }

    /**
     * @param Client $client
     */
    public function createAffiliate(Client $client)
    {
        $affiliate = new Affiliate();
        $affiliate->setClient($client);

        $this->em->persist($affiliate);
    }

    /**
     * @param Client $client
     * @param $affiliate
     */
    public function createReferral(Client $client, $affiliate)
    {
        $referral = new Referral();
        $referral->setClient($client);
        $referral->setAffiliate($affiliate);

        $this->em->persist($referral);
    }

    /**
     * @param $email
     * @return User|object|null
     */
    public function findUserByEmail($email)
    {
        return $this->em->getRepository(User::class)->findOneBy(['email' => $email]);
    }

    /**
     * @param $username
     * @return User|object|null
     */
    public function findUserByUsername($username)
    {
        return $this->em->getRepository(User::class)->findOneBy(['username' => $username]);
    }

    /**
     * @param $token
     * @return User|object|null
     */
    public function findUserByConfirmationToken($token)
    {
        return $this->em->getRepository(User::class)->findOneBy(['confirmationToken' => $token]);
    }

    public function updateUser(User $user)
    {
        $this->em->persist($user);
        $this->em->flush();
    }

    public function save()
    {
        $this->em->flush();
    }
}