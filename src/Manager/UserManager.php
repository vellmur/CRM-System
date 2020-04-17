<?php

namespace App\Manager;

use App\Entity\Client\ModuleSetting;
use App\Repository\UserRepository;
use App\Entity\Client\Client;
use App\Entity\User\User;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Client\PaymentSettings;
use Symfony\Component\Security\Core\User\UserInterface;

class UserManager
{
    private $em;

    private $rep;

    /**
     * UserManager constructor.
     * @param EntityManagerInterface $em
     * @param UserRepository $repository
     */
    public function __construct(EntityManagerInterface $em, UserRepository $repository)
    {
        $this->em = $em;
        $this->rep = $repository;
    }

    /**
     * @param User $user
     * @param $roles
     * @throws \Exception
     */
    public function updateUser(User $user, $roles)
    {
        $user->setRoles($roles);

        $this->em->flush();
    }

    /**
     * @param Client $client
     * @param UserInterface $user
     * @return mixed
     */
    public function getClientUsers(Client $client, UserInterface $user)
    {
        return $this->rep->getClientUsers($client, $user);
    }

    /**
     * @param $id
     * @return null|UserInterface
     */
    public function find($id)
    {
        return $this->rep->find($id);
    }

    /**
     * @param Client $client
     */
    public function createSettings(Client $client)
    {
        $setting = new ModuleSetting();
        $setting->setModule(1);
        $setting->setName('crops_enabled');
        $setting->setEnabled(true);
        $client->addModuleSettings($setting);

        $setting = new ModuleSetting();
        $setting->setModule(2);
        $setting->setName('shares_enabled');
        $setting->setEnabled(true);
        $client->addModuleSettings($setting);

        $this->em->flush();
    }

    /**
     * @param Client $client
     */
    public function createPaymentSettings(Client $client)
    {
        $clientSettings = [];

        foreach ($client->getPaymentSettings() as $existingSetting) {
            $clientSettings[] = $existingSetting->getMethod();
        }

        $availableSettings = PaymentSettings::getMethodsNames();

        foreach ($availableSettings as $methodId => $methodName) {
            if (!in_array($methodId, $clientSettings)) {
                $setting = new PaymentSettings();
                $setting->setMethod($methodId);
                $setting->setIsActive(true);
                $this->em->persist($setting);

                $client->addPaymentSettings($setting);
            }
        }

        $this->em->flush();
    }

    /**
     * @param $usernameOrEmail
     * @return User|null
     */
    public function findUserByUsernameOrEmail($usernameOrEmail)
    {
        if (preg_match('/^.+\@\S+\.\S+$/', $usernameOrEmail)) {
            $user = $this->findUserByEmail($usernameOrEmail);
            if (null !== $user) {
                return $user;
            }
        }

        return $this->findUserByUsername($usernameOrEmail);
    }

    /**
     * @param $email
     * @return User|null
     */
    public function findUserByEmail($email)
    {
        return $this->rep->findOneBy(['email' => $email]);
    }

    /**
     * @param $username
     * @return User|null
     */
    public function findUserByUsername($username)
    {
        return $this->rep->findOneBy(['username' => $username]);
    }

    /**
     * @param $token
     * @return User|object|null
     */
    public function findUserByConfirmationToken($token)
    {
        return $this->em->getRepository(User::class)->findOneBy(['confirmationToken' => $token]);
    }

    public function flush()
    {
        $this->em->flush();
    }

    /**
     * @param UserInterface $user
     */
    public function saveUser(UserInterface $user)
    {
        $this->em->persist($user);
        $this->em->flush();
    }
}