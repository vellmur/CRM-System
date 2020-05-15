<?php

namespace App\Manager;

use App\Entity\Building\Building;
use App\Entity\Building\ModuleAccess;
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
     * @return Building[]|array|\object[]
     */
    public function getSoftwareBuildings()
    {
        $buildings = $this->em->getRepository(Building::class)->getSoftwareBuildings();

        return $buildings;
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