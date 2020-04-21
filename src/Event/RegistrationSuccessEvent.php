<?php

namespace App\Event;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\EventDispatcher\Event;

class RegistrationSuccessEvent extends Event
{
    public const NAME = 'registration.success';

    private $user;

    private $response;

    public function __construct(UserInterface $user) {
        $this->user = $user;
    }

    /**
     * @return UserInterface
     */
    public function getUser() {
        return $this->user;
    }

    /**
     * @param Response $response
     */
    public function setResponse(Response $response)
    {
        $this->response = $response;
    }

    /**
     * @return mixed
     */
    public function getResponse()
    {
        return $this->response;
    }
}
