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
    public function countClientsStats()
    {
        $stats['total'] = $this->manager->countTotalClients();
        $stats['today'] = $this->manager->countNewClientsByDays(0);
        $stats['week'] = $this->manager->countNewClientsByDays(7);
        $stats['month'] = $this->manager->countNewClientsByDays(30);
        $stats['confirmed'] = $this->manager->countClientsByActivation(true);
        $stats['unconfirmed'] = $this->manager->countClientsByActivation(false);
        $stats['visits'] = $this->manager->countLandingViews();

        return $stats;
    }
}