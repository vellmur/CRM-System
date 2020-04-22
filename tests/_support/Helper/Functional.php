<?php
namespace App\Tests\Helper;

use App\Entity\Translation\TranslationLocale;
use App\Entity\User\User;
use App\Manager\RegistrationManager;
use Codeception\Exception\ModuleException;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class Functional extends \Codeception\Module
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

        /** @var RegistrationManager $manager */
        $manager = $symfony->grabService('app.manager.registration');

        $user = new User();

        try {
            $locale = $doctrine->_getEntityManager()->getRepository(TranslationLocale::class)->findOneBy(['code' => 'en']);
            $user->setUsername('testuser');
            $user->setEmail('testemail@example.com');
            $user->setPlainPassword('user_passWord');
            $user->setLocale($locale);

            $manager->register($user, 'Test Client');
        } catch (\Throwable $exception) {
            $this->fail('Unable to create user for test: "' . $exception->getMessage() . '".');
        }

        /** @var User $user */
        $user = $doctrine->grabEntityFromRepository(User::class, [
            'id' => $user->getId(),
        ]);

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
