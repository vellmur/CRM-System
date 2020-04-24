<?php

namespace App\Controller\Security;

use App\Form\Security\ResettingType;
use App\Manager\UserManager;
use App\Service\AuthorizedRedirect;
use App\Service\Mail\Sender;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    const RETRY_TTL = 7200;

    private $manager;

    public function __construct(UserManager $manager) {
        $this->manager = $manager;
    }

    /**
     * @param AuthenticationUtils $auth
     * @param AuthorizedRedirect $redirect
     * @return Response
     */
    public function login(AuthenticationUtils $auth, AuthorizedRedirect $redirect) : Response
    {
        if ($redirectPath = $redirect->getAuthorizedRedirectPath($this->getUser())) {
            return new RedirectResponse($redirectPath);
        }

        return $this->render('auth/login/login.html.twig', [
            'last_username' => $auth->getLastUsername(),
            'error' => $auth->getLastAuthenticationError()
        ]);
    }

    /**
     * @return Response
     */
    public function resetting()
    {
        return $this->render('auth/resetting/request.html.twig');
    }

    /**
     * @param Request $request
     * @param TokenGeneratorInterface $token
     * @param Sender $sender
     * @return RedirectResponse
     * @throws \Exception
     */
    public function sendResettingEmail(Request $request, TokenGeneratorInterface $token, Sender $sender)
    {
        $username = $request->request->get('username');
        $user = $this->manager->findUserByUsernameOrEmail($username);

        if (null !== $user && !$user->isPasswordRequestNonExpired(self::RETRY_TTL)) {
            if (null === $user->getConfirmationToken()) {
                $user->setConfirmationToken($token->generateToken());
            }

            $sender->sendResettingEmailMessage($user);
            $user->setPasswordRequestedAt(new \DateTime());
            $this->manager->flush();
        }

        return new RedirectResponse($this->generateUrl('app_resetting_check_email', [
            'username' => $username
        ]));
    }

    /**
     * Tell the user to check his email provider.
     *
     * @param Request $request
     * @return RedirectResponse|Response
     */
    public function checkEmail(Request $request)
    {
        $username = $request->query->get('username');

        if (empty($username)) {
            return new RedirectResponse($this->generateUrl('app_resetting_request'));
        }

        return $this->render('auth/resetting/check_email.html.twig', [
            'tokenLifetime' => ceil(self::RETRY_TTL / 3600)
        ]);
    }

    /**
     * @param Request $request
     * @param UserPasswordEncoderInterface $encoder
     * @param $token
     * @return RedirectResponse|Response
     */
    public function reset(Request $request, UserPasswordEncoderInterface $encoder, $token)
    {
        $user = $this->manager->findUserByConfirmationToken($token);

        if (null === $user) {
            return new RedirectResponse($this->container->get('router')->generate('app_login'));
        }

        $form = $this->createForm(ResettingType::class);
        $form->setData($user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword($encoder->encodePassword($user, $user->getPlainPassword()));
            $this->manager->saveUser($user);

            return new RedirectResponse($this->generateUrl('app_resetting_resetted', [
                'username' => $user->getUsername()
            ]));
        }

        return $this->render('auth/resetting/reset.html.twig',  [
            'token' => $token,
            'form' => $form->createView()
        ]);
    }

    /**
     * @param $username
     * @return Response
     */
    public function resetted($username)
    {
        return $this->render('auth/resetting/password_updated.html.twig', [
            'user' => $this->manager->findUserByUsername($username)
        ]);
    }
}
