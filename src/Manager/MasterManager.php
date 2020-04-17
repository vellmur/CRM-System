<?php

namespace App\Manager;

use App\Entity\User\PageView;
use App\Entity\ModuleAccess;
use App\Entity\Client\Client;
use App\Entity\Client\Team;
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
     * @param $client
     * @return array
     */
    public function getClientUsers($client)
    {
        $users = $this->em->getRepository(Team::class)->findBy(['client' => $client]);

        return $users;
    }

    public function updateClientAccess(ModuleAccess $access)
    {
        $access->setUpdatedAt(new \DateTime());
        $this->em->flush();
    }

    /**
     * @return array
     */
    public function getClientsLevelsArray()
    {
        $modules = [
            1 => 'Farmer'
        ];

        return $modules;
    }

    /**
     * @return array
     */
    public function countLevelClients()
    {
        $levelClients = $this->em->getRepository(Client::class)->countLevelClients();

        return $levelClients;
    }

    /**
     * @return array
     */
    public function countLevelClientsByStatus()
    {
        $statusMembers = $this->em->getRepository(Client::class)->countLevelClientsByStatus();

        return $statusMembers;
    }

    /**
     * @param $days
     * @return array
     */
    public function countNewByDays($days)
    {
        $newMembersNum = $this->em->getRepository(Client::class)->countNewClients($days);

        return $newMembersNum;
    }

    /**
     * @param null $search
     * @return mixed
     */
    public function getSoftwareClients($search = null)
    {
        $clients = $this->em->getRepository(Client::class)->getSoftwareClients($search);

        return $clients;
    }

    /**
     * @return mixed
     */
    public function getActiveClients()
    {
        return $this->em->getRepository(Client::class)->getActiveClients();
    }

    /**
     * @param $clients
     * @return array
     */
    public function getLapsedClients($clients)
    {
        $accesses = [];

        if (count($clients)) {
            foreach ($clients as $client) {
                $lapsedCounter = 0;

                foreach ($client->getAccesses() as $access) {
                    if ($access->getStatusName() == 'LAPSED') $lapsedCounter++;
                }

                if ($lapsedCounter > 2) $accesses[] = $client->getId();
            }
        }

        return $accesses;
    }

    /**
     * @param $text
     * @return array
     */
    public function searchClients($text)
    {
        $clients = $this->em->getRepository(Client::class)->searchClientsByAllFields($text);

        return $clients;
    }

    /**
     * @param Client $client
     */
    public function deleteClient(Client $client)
    {
        $this->em->remove($client);
        $this->em->flush();
    }

    /**
     * @return int
     */
    public function countTotalClients()
    {
        $clientsModules = $this->em->getRepository(Client::class)->getClientsByModulesStatuses();

        return  count($clientsModules);
    }

    /**
     * @param $days
     * @return array
     */
    public function countNewClientsByDays($days)
    {
        $count = $this->em->getRepository(Client::class)->countNewClientsByDays($days);

        return $count;
    }

    /**
     * @param $isConfirmed
     * @return mixed
     */
    public function countClientsByActivation($isConfirmed)
    {
        $clientsNum = $this->em->getRepository(Client::class)->countClientsByActivation($isConfirmed);

        return $clientsNum;
    }

    /**
     * @return mixed
     */
    public function countLandingViews()
    {
        $views = $this->em->getRepository(PageView::class)->countLandingViews();

        return $views;
    }

    /**
     * @param $status
     * @param $text
     * @return array|mixed|null
     */
    public function searchClientsBy($status, $text)
    {
        $clients = null;
        $rep = $this->em->getRepository(Client::class);

        switch ($status)
        {
            case 'all':
                $clients = $this->getSoftwareClients($text);
                break;
            case 'confirmed':
                $clients = $rep->getClientsByActivation(true, $text);
                break;
            case 'unconfirmed':
                $clients = $rep->getClientsByActivation(false, $text);
                break;
            case 'today':
                $clients = $rep->getNewClientsByDays(0);
                break;
            case 'week':
                $clients = $rep->getNewClientsByDays(7);
                break;
            case 'month':
                $clients = $rep->getNewClientsByDays(30);
                break;
            case 'pending':
            case 'active':
            case 'lapsed':
                $status = $this->em->getRepository(ModuleAccess::class)->findOneBy(['name' => $status]);
                $clients = $rep->getClientsByStatus($status);
                break;
            default:
                $clients = $rep->getSoftwareClients();
        }

        return $clients;
    }

    /**
     * @param string|null $module
     * @param Client|null $client
     * @return array
     */
    public function getViewStatistics(?string $module, ?Client $client)
    {
        // Get views data and create associative array for the chart
        $data = $this->em->getRepository(PageView::class)->countPageViews($module, $client);
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
            $this->countChartItem($data, 'All', $item['year'], $month, $item['day'], $item['counter']);
            $this->countChartItem($data, $page, $item['year'], $month, $item['day'], $item['counter']);
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