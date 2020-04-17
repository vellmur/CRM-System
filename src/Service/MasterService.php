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
     * @param $levels
     * @param $levelsNum
     * @param $statusNum
     * @param $newInWeekNum
     * @param $newInMonthNum
     * @return mixed
     */
    public function countLevelsData($levels, $levelsNum, $statusNum, $newInWeekNum, $newInMonthNum)
    {
        $total['total']['name'] = 'Total';
        $total['total']['total'] = 0;
        $total['total']['statusNum'] = [];
        $total['total']['weekNum'] = 0;
        $total['total']['monthNum'] = 0;

        $levelStatusNum = [];

        foreach ($statusNum as $key => $status) {
            // Change database result to array level => status => num of members with this status
            if (!isset($status['level'][$levelStatusNum])) {
                $levelStatusNum[$status['level']]['num'] = [$status['statusId'] => $status['statusNum']];
            } else {
                $levelStatusNum[$status['level']]['num'] += [$status['statusId'] => $status['statusNum']];
            }

            // Count total members by status, create array total => statusNum => status => total num of members
            if (!isset($status['statusId']['total']['statusNum'])) {
                $total['total']['statusNum'][$status['statusId']] = $status['statusNum'];
            } else {
                $total['total']['statusNum'][$status['statusId']] += $status['statusNum'];
            }
        }

        $levelsNum = $this->createAssociativeArray($levelsNum);
        $newInWeekNum = $this->createAssociativeArray($newInWeekNum);
        $newInMonthNum = $this->createAssociativeArray($newInMonthNum);

        // Create array for summary table level => data (count members)
        foreach ($levels as $key => $level) {
            $total[$level]['name'] = $level;
            $total[$level]['total'] = isset($levelsNum[$key]) ? $levelsNum[$key]['num'] : 0;
            $total[$level]['weekNum'] = isset($newInWeekNum[$key]) ? $newInWeekNum[$key]['num'] : 0;
            $total[$level]['monthNum'] = isset($newInMonthNum[$key]) ? $newInMonthNum[$key]['num'] : 0;
            $total[$level]['statusNum'] = isset($levelStatusNum[$key]) ? $levelStatusNum[$key]['num'] : 0;

            $total['total']['total'] += $total[$level]['total'];
            $total['total']['weekNum'] += $total[$level]['weekNum'];
            $total['total']['monthNum'] += $total[$level]['monthNum'];
        }

        // Move total array to the end
        $v = $total['total'];
        unset($total['total']);
        $total['total'] = $v;

        return $total;
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


    /**
     * @param $oldArray
     * @return array
     */
    private function createAssociativeArray($oldArray)
    {
        $newArray = [];

        foreach ($oldArray as $value) {
            if (!array_key_exists($value['level'], $newArray)) {
                $newArray[$value['level']] = $value;
            } else {
                $newArray[$value['level']] += $value;
            }

            unset($newArray[$value['level']]['level']);
        }

        return $newArray;
    }
}