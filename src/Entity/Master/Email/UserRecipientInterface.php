<?php

namespace App\Entity\Master\Email;

use App\Entity\User\User;

interface UserRecipientInterface
{
    /**
     * @return User
     */
    public function getUser() : User;

    /**
     * @param mixed $user
     */
    public function setUser(User $user): void;
}
