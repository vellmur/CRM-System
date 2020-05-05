<?php
namespace App\Tests\Helper;

use App\DataFixtures\UserFixtures;
use App\Entity\User\User;
use App\Manager\RegistrationManager;
use Codeception\Exception\ModuleException;
use Codeception\Module;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class Functional extends Module
{
    /**
     * Create user or administrator and set auth cookie to client
     *
     * @param bool $admin
     */
    public function auth(bool $admin = false)
    {
        /** @var \Codeception\Module\Symfony $symfony */
        try {
            $symfony = $this->getModule('Symfony');
        } catch (ModuleException $e) {
            $this->fail('Unable to get module \'Symfony\'');
        }
        /** @var \Codeception\Module\Doctrine2 $doctrine */
        try {
            $doctrine = $this->getModule('Doctrine2');
        } catch (ModuleException $e) {
            $this->fail('Unable to get module \'Doctrine2\'');
        }

        $userFixtures = UserFixtures::ENABLED_USER;

        /** @var User $user */
        $user = $doctrine->grabEntityFromRepository(User::class, [
            'email' => $userFixtures['email']
        ]);

        if (!$user) {
            /** @var RegistrationManager $manager */
            $manager = $symfony->grabService('app.manager.registration');

            $user = new User();

            try {
                $user->setUsername($userFixtures['username']);
                $user->setEmail($userFixtures['email']);
                $user->setPlainPassword($userFixtures['password']);
                $user->setLocale(1);

                $manager->register($user, $userFixtures['client']['name']);
            } catch (\Throwable $exception) {
                $this->fail('Unable to create user for test: "' . $exception->getMessage() . '".');
            }
        }

        $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
        $symfony->grabService('security.token_storage')->setToken($token);
        /** @var \Symfony\Component\HttpFoundation\Session\Session $session */
        $session = $symfony->grabService('session');
        $session->set('_security_main', serialize($token));
        $session->save();
        $cookie = new Cookie($session->getName(), $session->getId());
        $symfony->client->getCookieJar()->set($cookie);
        $symfony->client->reload();
    }
}
