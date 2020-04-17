<?php

namespace App\Security;

use App\Entity\User\User;
use App\Service\AuthorizedRedirect;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Guard\Authenticator\AbstractFormLoginAuthenticator;
use Symfony\Component\Security\Http\Util\TargetPathTrait;
use Symfony\Contracts\Translation\TranslatorInterface;

class LoginFormAuthenticator extends AbstractFormLoginAuthenticator
{
    use TargetPathTrait;

    private $em;

    private $router;

    private $token;

    private $passwordEncoder;

    private $redirect;

    private $translator;

    public function __construct(
        EntityManagerInterface $em,
        RouterInterface $router,
        CsrfTokenManagerInterface $token,
        UserPasswordEncoderInterface $passwordEncoder,
        AuthorizedRedirect $redirect,
        TranslatorInterface $translator
    ){
        $this->em = $em;
        $this->router = $router;
        $this->token = $token;
        $this->passwordEncoder = $passwordEncoder;
        $this->redirect = $redirect;
        $this->translator = $translator;
    }

    /**
     * @param Request $request
     * @return bool
     */
    public function supports(Request $request)
    {
        return 'app_login' === $request->attributes->get('_route') && $request->isMethod('POST');
    }

    /**
     * @param Request $request
     * @return array|mixed
     */
    public function getCredentials(Request $request)
    {
        $credentials = [
            'username' => $request->request->get('username'),
            'password' => $request->request->get('password'),
            'csrf_token' => $request->request->get('_csrf_token'),
        ];

        $request->getSession()->set(Security::LAST_USERNAME, $credentials['username']);

        return $credentials;
    }

    /**
     * @param mixed $credentials
     * @param UserProviderInterface $userProvider
     * @return User|object|UserInterface|null
     */
    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        $token = new CsrfToken('authenticate', $credentials['csrf_token']);

        if (!$this->token->isTokenValid($token)) {
            throw new InvalidCsrfTokenException();
        }

        $user = $this->em->getRepository(User::class)->findUserByEmailOrUsername($credentials['username']);

        if (!($user instanceof UserInterface)) {
            throw new CustomUserMessageAuthenticationException($this->translator->trans('login.not_found', [], 'validators'));
        } elseif (!$user->isEnabled()) {
            throw new CustomUserMessageAuthenticationException(
                $this->translator->trans('login.confirmation_required', [], 'validators') . '<br/>'
                . $this->translator->trans('login.didnt_receive', [], 'validators') . '<br/>' .
                $this->translator->trans('login.contact_us', [], 'validators') . ': '
                . $_ENV['SUPPORT_EMAIL']
            );
        }

        return $user;
    }

    /**
     * @param mixed $credentials
     * @param UserInterface $user
     * @return bool
     */
    public function checkCredentials($credentials, UserInterface $user)
    {
        return $this->passwordEncoder->isPasswordValid($user, $credentials['password']);
    }

    /**
     * @param Request $request
     * @param TokenInterface $token
     * @param string $providerKey
     * @return RedirectResponse|\Symfony\Component\HttpFoundation\Response|null
     * @throws \Exception
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        if ($redirectPath = $this->redirect->getAuthorizedRedirectPath($token->getUser())) {
            return new RedirectResponse($redirectPath);
        }

        throw new \Exception('TODO: provide a valid redirect inside '.__FILE__);
    }

    /**
     * @return string
     */
    protected function getLoginUrl()
    {
        return $this->router->generate('app_login');
    }
}