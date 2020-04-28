<?php

namespace App\DataFixtures;

use App\Entity\User\User;
use App\Manager\RegistrationManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserFixtures extends Fixture
{
    private $registrationManager;

    private $encoder;

    private const DEFAULT_LOCALE_REFERENCE = 'default-locale';

    // This user data will be used in codeception tests after load (test/(functional/acceptance)/SignUpCest.php)
    const ENABLED_USER = [
        'username' => 'johnwick',
        'email' => 'johnwick@mail.ru',
        'client' => [
            'name' => 'John Wick Company'
        ],
        'password' => 'admin23101994'
    ];

    const NOT_ENABLED_USER = [
        'username' => 'chucknorris',
        'email' => 'chucknorris@gmail.com',
        'client' => [
            'name' => 'Chuck Norris Company'
        ],
        'password' => 'admin34421101994'
    ];

    /**
     * UserFixtures constructor.
     * @param UserPasswordEncoderInterface $encoder
     */
    public function __construct(RegistrationManager $registrationManager, UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
        $this->registrationManager = $registrationManager;
    }

    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $userAdmin = $this->createAdmin();
        $manager->persist($userAdmin);
        $manager->flush();

        // Create confirmed user
        $firstUser = self::ENABLED_USER;
        $confirmedUser = $this->createUser($firstUser['username'], $firstUser['email'], $firstUser['password'], true);
        $this->registrationManager->registerUser($confirmedUser, $firstUser['client']['name']);

        // Create not enabled user
        $secondUser = self::NOT_ENABLED_USER;
        $notConfirmedUser = $this->createUser($secondUser['username'], $secondUser['email'], $secondUser['password'], false);
        $this->registrationManager->registerUser($notConfirmedUser, $secondUser['client']['name']);
    }

    /**
     * @return User
     */
    private function createAdmin()
    {
        $userAdmin = new User();
        $userAdmin->setUsername('admin');
        $userAdmin->setLocale($this->getReference(UserFixtures::DEFAULT_LOCALE_REFERENCE));
        $userAdmin->setPassword($this->encoder->encodePassword($userAdmin,'admin23101994'));
        $userAdmin->setEmail('admin@gmail.com');
        $userAdmin->setRoles([User::ROLE_ADMIN]);
        $userAdmin->setEnabled(true);
        $userAdmin->setIsActive(true);

        return $userAdmin;
    }

    /**
     * @param string $username
     * @param string $email
     * @param string $password
     * @param bool $isEnabled
     * @return User
     */
    private function createUser(string $username, string $email, string $password, bool $isEnabled)
    {
        $user = new User();
        $user->setUsername($username);
        $user->setLocale($this->getReference(UserFixtures::DEFAULT_LOCALE_REFERENCE));
        $user->setPlainPassword($password);
        $user->setEmail($email);
        $user->setEnabled($isEnabled);

        return $user;
    }
}
