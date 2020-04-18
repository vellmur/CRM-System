<?php

namespace App\Manager;

use App\Entity\Client\Client;
use App\Entity\Client\ModuleAccess;
use Doctrine\ORM\EntityManagerInterface;

class CommandManager
{
    private $em;

    /**
     * CommandManager constructor.
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @return Client[]|array|\object[]
     */
    public function getSoftwareClients()
    {
        $clients = $this->em->getRepository(Client::class)->getSoftwareClients();

        return $clients;
    }

    /**
     * @param ModuleAccess $access
     */
    public function extendModuleExpiration(ModuleAccess $access)
    {
        $expirationDate = new \DateTime($access->getExpiredAt()->format('Y-m-d'));
        $expirationDate->modify('+1 month');
        $access->setExpiredAt($expirationDate);
        $access->setStatusByName('ACTIVE');
    }

    public function update()
    {
        $this->em->flush();
    }
}