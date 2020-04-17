<?php

namespace App\Service;

use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class AuthorizedRedirect
{
    private $auth;

    private $router;

    private $moduleChecker;

    public function __construct(AuthorizationCheckerInterface $auth, RouterInterface $router, ModuleChecker $moduleChecker)
    {
        $this->auth = $auth;
        $this->router = $router;
        $this->moduleChecker = $moduleChecker;
    }

    /**
     * @param UserInterface|null $user
     * @return string|void
     */
    public function getAuthorizedRedirectPath(UserInterface $user = null)
    {
        if (!$user || !$this->auth->isGranted('IS_AUTHENTICATED_FULLY')) {
            return;
        }

        if ($this->auth->isGranted('ROLE_ADMIN')) {
            return $this->router->generate('master_dashboard');
        } elseif ($user->getTeam()) {
            return $this->router->generate('customer_list');
        }

        return;
    }
}
