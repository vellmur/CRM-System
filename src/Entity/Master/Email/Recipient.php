<?php

namespace App\Entity\Master\Email;

use App\Entity\Client\Client;
use App\Entity\Email\BaseRecipient;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="master__email_recipient", uniqueConstraints={
 *     @ORM\UniqueConstraint(name="master_email_recipient", columns={"email_id", "client_id"})
 * }))
 * @ORM\Entity(repositoryClass="App\Repository\RecipientRepository")
 */
class Recipient extends BaseRecipient implements ClientRecipientInterface
{
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Master\Email\Email", inversedBy="recipients")
     * @ORM\JoinColumn(name="email_id", referencedColumnName="id", nullable=false)
     */
    protected $emailLog;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Client\Client", inversedBy="emailRecipients")
     * @ORM\JoinColumn(name="client_id", referencedColumnName="id", nullable=false)
     */
    private $client;

    /**
     * @return Client
     */
    public function getClient() : Client
    {
        return $this->client;
    }

    /**
     * @param mixed $client
     */
    public function setClient(Client $client): void
    {
        $this->client = $client;

    }
}