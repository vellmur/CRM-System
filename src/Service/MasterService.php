<?php

namespace App\Service;

use App\Manager\MasterManager;

class MasterService
{
    private $manager;

    public function __construct(MasterManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @return array
     */
    public function countBuildingsStats()
    {
        $stats['total'] = $this->manager->countTotalBuildings();
        $stats['today'] = $this->manager->countNewBuildingsByDays(0);
        $stats['week'] = $this->manager->countNewBuildingsByDays(7);
        $stats['month'] = $this->manager->countNewBuildingsByDays(30);
        $stats['confirmed'] = $this->manager->countBuildingsByActivation(true);
        $stats['unconfirmed'] = $this->manager->countBuildingsByActivation(false);
        $stats['visits'] = $this->manager->countLandingViews();

        return $stats;
    }
}