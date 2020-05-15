<?php

namespace App\Manager;

use App\Entity\User\PageView;
use App\Entity\Building\ModuleAccess;
use App\Entity\Building\Building;
use App\Entity\User\User;
use Doctrine\ORM\EntityManagerInterface;

class MasterManager
{
    private $em;

    /**
     * MasterManager constructor.
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @param $building
     * @return array
     */
    public function getBuildingUsers($building)
    {
        return $this->em->getRepository(User::class)->findBy(['building' => $building]);
    }

    public function updateBuildingAccess(ModuleAccess $access)
    {
        $access->setUpdatedAt(new \DateTime());
        $this->em->flush();
    }

    /**
     * @param $days
     * @return array
     */
    public function countNewByDays($days)
    {
        return $this->em->getRepository(Building::class)->countNewBuildings($days);
    }

    /**
     * @param null $search
     * @return mixed
     */
    public function getSoftwareBuildings($search = null)
    {
        return $this->em->getRepository(Building::class)->getSoftwareBuildings($search);
    }

    /**
     * @return mixed
     */
    public function getActiveBuildings()
    {
        return $this->em->getRepository(Building::class)->getActiveBuildings();
    }

    /**
     * @param $buildings
     * @return array
     */
    public function getLapsedBuildings($buildings)
    {
        $accesses = [];

        if (count($buildings)) {
            foreach ($buildings as $building) {
                $lapsedCounter = 0;

                foreach ($building->getAccesses() as $access) {
                    if ($access->getStatusName() == 'LAPSED') $lapsedCounter++;
                }

                if ($lapsedCounter > 2) $accesses[] = $building->getId();
            }
        }

        return $accesses;
    }

    /**
     * @param $text
     * @return array
     */
    public function searchBuildings($text)
    {
        return $this->em->getRepository(Building::class)->searchBuildingsByAllFields($text);
    }

    /**
     * @param Building $building
     */
    public function deleteBuilding(Building $building)
    {
        $this->em->remove($building);
        $this->em->flush();
    }

    /**
     * @return int
     */
    public function countTotalBuildings()
    {
        $buildingsModules = $this->em->getRepository(Building::class)->getBuildingsByModulesStatuses();

        return count($buildingsModules);
    }

    /**
     * @param $days
     * @return array
     */
    public function countNewBuildingsByDays($days)
    {
        return $this->em->getRepository(Building::class)->countNewBuildingsByDays($days);
    }

    /**
     * @param $isConfirmed
     * @return mixed
     */
    public function countBuildingsByActivation($isConfirmed)
    {
        return $this->em->getRepository(Building::class)->countBuildingsByActivation($isConfirmed);
    }

    /**
     * @return mixed
     */
    public function countLandingViews()
    {
        return $this->em->getRepository(PageView::class)->countLandingViews();
    }

    /**
     * @param $status
     * @param $text
     * @return array|mixed|null
     */
    public function searchBuildingsBy($status, $text)
    {
        $buildings = null;
        $rep = $this->em->getRepository(Building::class);

        switch ($status)
        {
            case 'all':
                $buildings = $this->getSoftwareBuildings($text);
                break;
            case 'confirmed':
                $buildings = $rep->getBuildingsByActivation(true, $text);
                break;
            case 'unconfirmed':
                $buildings = $rep->getBuildingsByActivation(false, $text);
                break;
            case 'today':
                $buildings = $rep->getNewBuildingsByDays(0);
                break;
            case 'week':
                $buildings = $rep->getNewBuildingsByDays(7);
                break;
            case 'month':
                $buildings = $rep->getNewBuildingsByDays(30);
                break;
            case 'pending':
            case 'active':
            case 'lapsed':
                $status = $this->em->getRepository(ModuleAccess::class)->findOneBy(['name' => $status]);
                $buildings = $rep->getBuildingsByStatus($status);
                break;
            default:
                $buildings = $rep->getSoftwareBuildings();
        }

        return $buildings;
    }

    /**
     * @param string|null $module
     * @param Building|null $building
     * @return array
     */
    public function getViewStatistics(?string $module, ?Building $building)
    {
        // Get views data and create associative array for the chart
        $data = $this->em->getRepository(PageView::class)->countPageViews($module, $building);
        $chartData = $this->createChartArray($data);

        return $chartData;
    }

    /**
     * Create array with 'Item => Year => Month => Day/Total => Num' keys and count same for the 'All' items
     *
     * @param $items
     * @return array
     */
    public function createChartArray($items)
    {
        $monthsNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

        $data = [];

        for ($i = 0; $i < count($items); $i++) {
            $item = $items[$i];
            $month = $monthsNames[$item['month'] - 1];
            $page = ucfirst($item['page']);

            // If selected item is null, count 'All' items data (additional 'All' button for the chart)
            $this->countChartItem($data, $page, $item['year'], $month, $item['day'], $item['counter']);
            $this->countChartItem($data, 'All', $item['year'], $month, $item['day'], $item['counter']);
        }

        return $data;
    }

    /**
     * Count values for the chart item (Page/Step/Product -> View/Price) for each day and total in a month
     *
     * Fill array with zeros (if key not exists) in format Item => Year => Month => Day/Total = 0
     * If key exists, count data for each day in a month and for the month total
     *
     * @param $array
     * @param $itemName
     * @param $year
     * @param $month
     * @param $day
     * @param $num
     */
    public function countChartItem(&$array, $itemName, $year, $month, $day, $num)
    {
        // Fill array with zeros for each day and total in a month
        if (!isset($array[$itemName][$year][$month])) {
            $array[$itemName][$year][$month] = array_fill(1, 31, 0);
            $array[$itemName][$year][$month]['total'] = 0;
        }

        // Count value for each day and for the month total
        $array[$itemName][$year][$month][$day] += $num;
        $array[$itemName][$year][$month]['total'] += $num;

        unset($array);
    }
}