<?php

namespace App\EventListener;

use App\Service\ModuleChecker;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Contracts\Translation\TranslatorInterface;

final class SecurityListener extends AppListener
{
    private $session;

    private $token;

    private $moduleChecker;

    private $translator;

    /**
     * SecurityListener constructor.
     * @param SessionInterface $session
     * @param TokenStorageInterface $tokenStorage
     * @param ModuleChecker $moduleChecker
     * @param TranslatorInterface $translator
     */
    public function __construct(
        SessionInterface $session,
        TokenStorageInterface $tokenStorage,
        ModuleChecker $moduleChecker,
        TranslatorInterface $translator
    ) {
        $this->session = $session;
        $this->token = $tokenStorage;
        $this->moduleChecker = $moduleChecker;
        $this->translator = $translator;
    }

    /**
     * @param RequestEvent $event
     * @throws \Exception
     */
    public function onKernelRequest(RequestEvent $event)
    {
        $request = $event->getRequest();

        if ($this->isSystemEvent($event) === true) {
            return;
        }

        try {
            $user = $this->token->getToken() ? $this->token->getToken()->getUser() : null;
            $building = $user && $user != 'anon.' && $user->getBuilding() ? $user->getBuilding() : null;

            if ($building) {
                $this->session->set('modules_statuses', $this->moduleChecker->getModulesStatuses($building));

                if ($moduleName = $this->moduleChecker->getModuleNameByUrl($request->getRequestUri())) {
                    if (!$this->moduleChecker->buildingHasModuleAccess($building, $user->getRoles(), $moduleName)) {
                        $deniedMessage = $this->translator->trans('access.access_expired', [],'validators');
                        throw new AccessDeniedException($deniedMessage);
                    }
                }
            }
        } catch (\Exception $exception) {
            if ($exception instanceof AccessDeniedException || $this->isDevelopmentEnvironment()) throw $exception;
        }
    }
}