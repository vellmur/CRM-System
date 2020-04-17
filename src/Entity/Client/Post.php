<?php

namespace App\Entity\Client;

use Doctrine\ORM\Mapping as ORM;
use App\Entity\Blog\BasePost;

/**
 * @ORM\Table(name="client__posts")
 * @ORM\Entity()
 */
class Post extends BasePost
{
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Client\Client", inversedBy="posts")
     * @ORM\JoinColumn(name="client_id", referencedColumnName="id", onDelete="CASCADE", nullable=false)
     */
    private $client;

    /**
     * @return mixed
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @param mixed $client
     */
    public function setClient($client)
    {
        $this->client = $client;
    }
}
