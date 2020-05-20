<?php

namespace App\Manager;

use App\Entity\Building\Affiliate;
use App\Entity\Building\Building;
use App\Entity\Owner\Email\AutoEmail;
use App\Entity\Building\ModuleAccess;
use App\Entity\Building\Referral;
use App\Entity\User\User;
use App\Security\AccessUpdater;
use App\Service\Localization\LanguageDetector;
use App\Service\Mail\Sender;
use Doctrine\ORM\EntityManagerInterface;
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

    private $sender;

    private $languageDetector;

    public function __construct(
        EntityManagerInterface $em,
        UserPasswordEncoderInterface $passwordEncoder,
        TokenGeneratorInterface $token,
        Environment $twig,
        TranslatorInterface $translator,
        Sender $sender,
        LanguageDetector $languageDetector
    ) {
        $this->em = $em;
        $this->passwordEncoder = $passwordEncoder;
        $this->token = $token;
        $this->twig = $twig;
        $this->translator = $translator;
        $this->sender = $sender;
        $this->languageDetector = $languageDetector;
    }

    /**
     * @param User $user
     * @param string $buildingName
     * @param string|null $refCode
     * @return User
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function register(User $user, string $buildingName, ?string $refCode = null)
    {
        $this->em->getConnection()->beginTransaction();

        try {
            $this->registerUser($user, $buildingName);

            if ($refCode && $referral = $this->createReferral($user->getBuilding(), $refCode)) {
                $this->em->persist($referral);
                $this->em->flush();
            }

            $this->sender->sendEmailConfirmation($user);

            $this->em->getConnection()->commit();

            return $user;
        } catch (\Throwable $e) {
            $this->em->getConnection()->rollBack();
            $this->em->clear();

            throw $e;
        }
    }

    /**
     * @param User $user
     * @param string $buildingName
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function registerUser(User $user, string $buildingName)
    {
        $user->setRoles([User::ROLE_OWNER]);
        $user->setPassword($this->passwordEncoder->encodePassword($user, $user->getPlainPassword()));
        $user->setConfirmationToken($this->token->generateToken());

        $building = $this->createBuilding($buildingName, $user->getEmail());
        $user->setBuilding($building);

        // Save building as new affiliate
        $affiliate = new Affiliate();
        $affiliate->setReferralCode(substr($this->token->generateToken(),0,20));
        $building->setAffiliate($affiliate);

        $accesses = $this->createAccess($building);
        $building->setAccesses($accesses);
        $this->createAutomatedEmails($building, $this->languageDetector->getLocaleCodeById($user->getLocale()));

        $this->em->persist($building);
        $this->em->persist($user);

        $this->em->flush();
    }

    /**
     * @param Building $building
     * @param string $refCode
     * @return Referral|null
     */
    public function createReferral(Building $building, string $refCode)
    {
        $affiliate = $this->em->getRepository(Affiliate::class)->findOneBy([
            'referralCode' => $refCode
        ]);

        if (!$affiliate) {
            return null;
        }

        $referral = new Referral();
        $referral->setBuilding($building);
        $referral->setAffiliate($affiliate);

        return $referral;
    }

    /**
     * @param Building $building
     * @param User $user
     * @param string $roles
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function addUserToBuilding(Building $building, User $user, string $roles)
    {
        $this->em->getConnection()->beginTransaction();

        try {
            $user->setRoles(is_array($roles) ? $roles : [$roles]);
            $user->setPassword($this->passwordEncoder->encodePassword($user, $user->getPlainPassword()));
            $user->setBuilding($building);

            $this->em->persist($user);
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
     * @param string $name
     * @param string $email
     * @return Building
     */
    private function createBuilding(string $name, string $email) : Building
    {
        $building = new Building();
        $building->setName($name);
        $building->setEmail($email);

        return $building;
    }

    /**
     * @param Building $building
     * @return array
     * @throws \Exception
     */
    private function createAccess(Building $building)
    {
        $today = new \DateTime();
        $trialExtendsAt = new \DateTime();
        $trialExtendsAt->modify('+' . AccessUpdater::TRIAL_DAYS . ' days');

        $accesses = [];

        foreach (ModuleAccess::MODULES as $id => $name) {
            $access = new ModuleAccess();
            $access->setBuilding($building);
            $access->setModule($id);
            $access->setUpdatedAt($today);
            $access->setExpiredAt($trialExtendsAt);
            $access->setStatusByName('ACTIVE');

            $accesses[] = $access;
        }

        return $accesses;
    }

    /**
     * @param Building $building
     * @param string $locale
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function createAutomatedEmails(Building $building, string $locale)
    {
        // Run through all automated email types defined in manager
        foreach (AutoEmail::EMAIL_TYPES as $id => $typeName) {
            $subject = $this->translator->trans(('emails.' . $typeName . '.subject'), [], 'labels', $locale);
            $template = $this->twig->render('owner/emails/default/' . $typeName . '.html.twig');

            $email = new AutoEmail();
            $email->setType($id);
            $email->setSubject($subject);
            $email->setText($template);
            $building->addAutoMail($email);

            $this->em->flush();
        }
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