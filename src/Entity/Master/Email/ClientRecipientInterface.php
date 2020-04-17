<?php

namespace App\Entity\Master\Email;

use App\Entity\Client\Client;

interface ClientRecipientInterface
{
    /**
     * @return Client
     */
    public function getClient() : Client;

    /**
     * @param mixed $client
     */
    public function setClient(Client $client): void;
}
