<?php

namespace App\Entity\Customer\Email;

use App\Entity\Client\Client;

interface ClientEmailInterface
{
    /**
     * @return Client
     */
    public function getClient() : Client;

    /**
     * @param Client $client
     * @return mixed
     */
    public function setClient(Client $client);

    /**
     * @return string
     */
    public function getReplyEmail();

    /**
     * @param string $replyEmail
     * @return mixed
     */
    public function setReplyEmail(string $replyEmail);

    /**
     * @return string
     */
    public function getReplyName();

    /**
     * @param string $replyName
     * @return mixed
     */
    public function setReplyName(string $replyName);
}
