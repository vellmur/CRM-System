<?php

namespace App\Manager;

use App\Entity\Client\Client;
use App\Entity\Customer\InvoiceProduct;
use App\Entity\Customer\RenewalView;
use Doctrine\ORM\EntityManagerInterface;

class StatisticsManager
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @param Client $client
     * @param $chart
     * @return array
     */
    public function getRenewalChartData(Client $client, $chart)
    {
        $repository = $chart == 'revenue'
            ? $this->em->getRepository(InvoiceProduct::class)
            : $this->em->getRepository(RenewalView::class);

        $data = [];
        $selectedItem = null;

        // Get data from the needed repository
        switch ($chart) {
            case 'views':
            case 'completed':
                // Completed is the same data as for views, but counts only 'Completed' step
                if ($chart == 'completed') $selectedItem = RenewalView::getStepId('Completed');
                $data = $repository->countRenewalTabsViews($client, $selectedItem);
                break;
            case 'revenue':
                $data = $repository->countRevenue($client);
                break;
        }

        // Create associative array for the chart
        $chartData = $this->createChartArray($data, $chart, $selectedItem);

        return $chartData;
    }

    /**
     * Create array with 'Item => Year => Month => Day/Total => Num' keys and count same for the 'All' items
     *
     * @param $items
     * @param $chart
     * @param $selectedItem
     * @return array
     */
    public function createChartArray($items, $chart, $selectedItem)
    {
        $monthsNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

        $data = [];

        foreach ($items as $key => $item) {
            // Item name is product name for revenue or step name for views
            $itemName = $chart == 'revenue' ? $item['productName'] : RenewalView::getStepName($item['item']);
            $month = $monthsNames[$item['month'] - 1];

            // If selected item is null, count 'All' items data (additional 'All' button for the chart)
            if (!$selectedItem) {
                $this->countChartItem($data, 'All', $item['year'], $month, $item['day'], $item['counter']);
            }

            $this->countChartItem($data, $itemName, $item['year'], $month, $item['day'], $item['counter']);
        }

        //echo '<pre>'; print_r($data); echo '</pre>';die();

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
