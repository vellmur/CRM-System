<?php

namespace App\Controller\Customer;

use App\Manager\MemberManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class ReportController extends AbstractController
{
    private $manager;

    /**
     * ReportController constructor.
     * @param MemberManager $manager
     */
    public function __construct(MemberManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * This report helps to see all share list with pickups for each day of week for the current week
     * So farmer will know, which package to prepare for the customer
     * We show report for the next 7 days from today
     *
     * @param Request $request
     * @return Response
     * @throws \Exception
     */
    public function shareDay(Request $request)
    {
        if (!$request->query->get('day')) $request->query->set('day', date('l'));

        $client = $this->getUser()->getTeam()->getClient();

        // Get share week date
        $today = new \DateTime("midnight");
        $shareDate = new \DateTime(date('Y-m-d', strtotime('this ' . $request->query->get('day'), strtotime($today->format('Y-m-d')))));

        $shares = $this->manager->getSharesByShareDay($client, $request->query->get('day'), $shareDate);

        $weekNav = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

        return $this->render('customer/report/share_day.html.twig', [
            'weeksNav' => $weekNav,
            'shares' => $shares
        ]);
    }
}
