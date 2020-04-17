<?php

namespace App\Entity\Customer\Email;

use App\Entity\Client\Client;

interface ClientAutoEmailInterface
{
    /**
     * @return Client
     */
    public function getClient() : Client;

    /**
     * @param Client $client
     */
    public function setClient(Client $client);
}
