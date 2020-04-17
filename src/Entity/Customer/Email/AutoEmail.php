<?php

namespace App\Entity\Customer\Email;

use Doctrine\ORM\Mapping as ORM;
use App\Entity\Client\Client;
use App\Entity\Email\AutomatedEmailBase;

/**
 * Class AutoEmail
 *
 * @ORM\Table(name="email__auto")
 * @ORM\Entity()
 */
class AutoEmail extends AutomatedEmailBase implements ClientAutoEmailInterface
{
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Client\Client", inversedBy="autoEmails")
     * @ORM\JoinColumn(name="client_id", referencedColumnName="id", nullable=false)
     */
    protected $client;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Customer\Email\CustomerEmail", mappedBy="automatedEmail")
     */
    private $emailLog;

    public const EMAIL_TYPES = [
        1 => 'activation',
        2 => 'weekly',
        3 => 'feedback',
        4 => 'renewal',
        5 => 'lapsed',
        6 => 'delivery_day'
    ];

    /**
     * @return Client
     */
    public function getClient() : Client
    {
        return $this->client;
    }

    /**
     * @param Client $client
     */
    public function setClient(Client $client)
    {
        $this->client = $client;
    }
}