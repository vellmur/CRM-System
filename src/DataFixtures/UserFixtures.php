<?php

namespace App\DataFixtures;

use App\Entity\Building\Address;
use App\Entity\Building\Building;
use App\Entity\User\User;
use App\Manager\EmailManager;
use App\Manager\RegistrationManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserFixtures extends Fixture
{
    private $registrationManager;

    private $emailManager;

    private $encoder;

    const ADMIN = [
        'username' => 'master',
        'email' => 'admin@mail.ru',
        'building' => [
            'name' => '[NEW COMPANY]',
            'address' => [
                'country' => 'ua'
            ]
        ],
        'password' => '11111111'
    ];

    // This user data will be used in codeception tests after load (test/(functional/acceptance)/SignUpCest.php)
    const ENABLED_USER = [
        'username' => 'johnwick',
        'email' => 'johnwick@mail.ru',
        'building' => [
            'name' => 'John Wick Company'
        ],
        'password' => 'admin23101994'
    ];

    /** @var User */
    public const ENABLED_USER_REFERENCE = null;

    const NOT_ENABLED_USER = [
        'username' => 'chucknorris',
        'email' => 'chucknorris@gmail.com',
        'building' => [
            'name' => 'Chuck Norris Company'
        ],
        'password' => 'admin34421101994'
    ];

    /**
     * UserFixtures constructor.
     * @param UserPasswordEncoderInterface $encoder
     */
    public function __construct(RegistrationManager $registrationManager, EmailManager $emailManager, UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
        $this->registrationManager = $registrationManager;
        $this->emailManager = $emailManager;
    }

    /**
     * @param ObjectManager $manager
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function load(ObjectManager $manager)
    {
        $userAdmin = $this->createAdmin();
        $manager->persist($userAdmin);
        $manager->flush();

        $this->emailManager->emailsExistsOrCreated();

        // Create confirmed user
        $firstUser = self::ENABLED_USER;
        $confirmedUser = $this->createUser($firstUser['username'], $firstUser['email'], $firstUser['password'], true);
        $this->registrationManager->registerUser($confirmedUser, $firstUser['building']['name']);

        $this->addReference(self::ENABLED_USER_REFERENCE, $confirmedUser);

        // Create not enabled user
        $secondUser = self::NOT_ENABLED_USER;
        $notConfirmedUser = $this->createUser($secondUser['username'], $secondUser['email'], $secondUser['password'], false);
        $this->registrationManager->registerUser($notConfirmedUser, $secondUser['building']['name']);
    }

    /**
     * @return User
     * @throws \Exception
     */
    private function createAdmin()
    {
        $userAdmin = new User();
        $userAdmin->setUsername(self::ADMIN['username']);
        $userAdmin->setLocale(1);
        $userAdmin->setPassword($this->encoder->encodePassword($userAdmin,self::ADMIN['password']));
        $userAdmin->setEmail(self::ADMIN['email']);
        $userAdmin->setRoles([User::ROLE_ADMIN]);
        $userAdmin->setEnabled(true);
        $userAdmin->setIsActive(true);

        $building = new Building();
        $building->setName(self::ADMIN['building']['name']);
        $building->setEmail(self::ADMIN['email']);

        $address = new Address();
        $address->setCountry(self::ADMIN['building']['address']['country']);
        $address->setRegion('Kiev');
        $address->setCity('Kiev');
        $address->setPostalCode('18023');

        $building->setAddress($address);
        $userAdmin->setBuilding($building);

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
        $user->setLocale(1);
        $user->setPlainPassword($password);
        $user->setEmail($email);
        $user->setEnabled($isEnabled);

        return $user;
    }
}
