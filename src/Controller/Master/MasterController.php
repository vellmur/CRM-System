<?php

namespace App\Controller\Master;

use App\Entity\Client\Referral;
use App\Manager\AffiliateManager;
use App\Manager\ImageManager;
use App\Manager\MasterManager;
use App\Security\AccessUpdater;
use App\Service\MasterService;
use JMS\Serializer\SerializerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use App\Entity\Client\Affiliate;
use App\Form\Client\AffiliateType;
use App\Entity\Client\Client;
use App\Form\Client\AccessType;
use App\Entity\Client\ModuleAccess;

/**
 * Class MasterController
 * @package App\Controller
 */
class MasterController extends AbstractController
{
    private $manager;

    private $service;

    private $affiliateManager;

    private $serializer;

    public function __construct(
        MasterManager $manager,
        MasterService $service,
        AffiliateManager $affiliateManager,
        SerializerInterface $serializer
    ){
        $this->manager = $manager;
        $this->service = $service;
        $this->affiliateManager = $affiliateManager;
        $this->serializer = $serializer;
    }

    /**
     * @return Response
     */
    public function dashboard()
    {
        $clients = $this->manager->getSoftwareClients();
        $lapsedClients = $this->manager->getLapsedClients($clients);
        $stats = $this->service->countClientsStats();

        return $this->render('master/dashboard.html.twig', [
            'clients' => $clients,
            'stats' => $stats,
            'lapsedClients' => $lapsedClients
        ]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function filterClients(Request $request)
    {
        $status = $request->query->get('status');
        $text = $request->query->get('search');

        $clients = $this->manager->searchClientsBy($status, $text);
        $lapsedClients = $this->manager->getLapsedClients($clients);

        $template = $this->render('master/client/search.html.twig', [
            'clients' => $clients,
            'lapsedClients' => $lapsedClients
        ])->getContent();

        return new JsonResponse(['template' => $template]);
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function affiliates(Request $request)
    {
        $affiliate = new Affiliate();
        $form = $this->createForm(AffiliateType::class, $affiliate);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->affiliateManager->createAffiliate($affiliate);

            return $this->redirectToRoute('master_affiliates');
        }

        $allAffiliates = $this->affiliateManager->findAll();

        $affiliates = [];

        foreach ($allAffiliates as $key => $affiliate) {
            $affiliates[$key]['affiliate'] = $affiliate;
            $affiliates[$key]['unpaidReferrals'] = $this->affiliateManager->countUnpaidReferrals($affiliate);
        }

        return $this->render('master/affiliate/list.html.twig', [
            'form' => $form->createView(),
            'affiliates' => $affiliates
        ]);
    }


    /**
     * @param Request $request
     * @param Affiliate $affiliate
     * @return RedirectResponse|Response
     */
    public function editAffiliate(Request $request, Affiliate $affiliate)
    {
        $form = $this->createForm(AffiliateType::class, $affiliate);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->affiliateManager->updateAffiliate($affiliate);

            return new RedirectResponse($request->headers->get('referer'));
        }
        
        return $this->render('master/affiliate/edit.html.twig', [
            'form' => $form->createView()
        ]);

    }

    /**
     * @param Affiliate $affiliate
     * @param $status
     * @return Response
     */
    public function affiliateReferrals(Affiliate $affiliate, $status)
    {
        $referrals = $status == 'all'
            ? $this->affiliateManager->getAllReferrals($affiliate)
            : $this->affiliateManager->getUnpaidReferrals($affiliate);
        
        return $this->render('master/affiliate/referrals.html.twig', [
            'referrals' => $referrals
        ]);
    }

    /**
     * @param Request $request
     * @param Referral $referral
     * @return Response
     */
    public function referralUpdate(Request $request, Referral $referral)
    {
        $this->affiliateManager->updateReferralPaid($referral, $request->request->get('isPaid'));

        return new Response('success');
    }

    /**
     * @return Response
     */
    public function clients()
    {
        $clients = $this->manager->getSoftwareClients();
        $lapsedClients = $this->manager->getLapsedClients($clients);
        $levels = $this->manager->getClientsLevelsArray();
        $levelsNum = $this->manager->countLevelClients();
        $statusNum = $this->manager->countLevelClientsByStatus();
        $newWeekNum = $this->manager->countNewByDays(7);
        $newMonthNum = $this->manager->countNewByDays(30);

        return $this->render('master/client/list.html.twig', [
            'clients' => $clients,
            'lapsedClients' => $lapsedClients,
            'clientsSummary' => []
        ]);
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function searchClients(Request $request)
    {
        $clients = $this->manager->searchClients($request->query->get('search'));
        $lapsedClients = $this->manager->getLapsedClients($clients);

        $template = $this->render('master/client/search.html.twig', [
            'clients' => $clients,
            'lapsedClients' => $lapsedClients
        ])->getContent();

        return new JsonResponse([
            'template' => $template,
            'counter' => count($clients)
        ]);
    }

    /**
     * @param Client $client
     * @return Response
     */
    public function editClient(Client $client)
    {
        $formsArray = [];

        foreach ($client->getAccesses() as $access) {
            array_push($formsArray, $this->createForm(AccessType::class, $access, [
                'date_format' => $this->getUser()->getDateFormatName()
            ])->createView());
        }
        
        return $this->render('master/client/edit_client.html.twig', [
            'modules' => ModuleAccess::MODULES,
            'forms' => $formsArray,
            'teams' => $this->manager->getClientUsers($client)
        ]);
    }

    /**
     * @param Request $request
     * @param Client $client
     * @return JsonResponse|Response
     */
    public function deleteClient(Request $request, Client $client)
    {
        if ($request->isXMLHttpRequest() && $client) {
            $this->manager->deleteClient($client);

            return new JsonResponse(['code' => 202, 'status' => 'success']);

        }

        return new Response('Request not valid', 400);
    }

    /**
     * @param Request $request
     * @param AccessUpdater $accessUpdater
     * @param ModuleAccess $access
     * @return JsonResponse|Response
     * @throws \Exception
     */
    public function ajaxAccessUpdate(Request $request, AccessUpdater $accessUpdater, ModuleAccess $access)
    {
        if ($request->isXMLHttpRequest())
        {
            $form = $this->createForm(AccessType::class, $access, [
                'date_format' => $this->getUser()->getDateFormatName()
            ]);

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $this->manager->updateClientAccess($access);
                $accessUpdater->updateModulesAccess($access->getClient());

                return new JsonResponse(['code' => 202, 'status' => 'success']);
            } else {
                return new JsonResponse($this->serializer->serialize(['error' => $form], 'json'), 500);
            }
        }

        return new Response('Request not valid', 400);
    }

    /**
     * @param string|null $module
     * @param Client|null $client
     * @return Response
     */
    public function statistics(?string $module, ?Client $client)
    {
        $stats = $this->manager->getViewStatistics($module, $client);
        $modules = ['customers', 'website', 'promotion'];

        $pages = array_keys($stats);

        sort($pages);

        if (($key = array_search('All', $pages)) !== false) {
            unset($pages[$key]);
        }

        array_unshift($pages,"All");

        $clients = $this->manager->getActiveClients();

        return $this->render('master/statistics.html.twig', [
            'modules' => $modules,
            'stats' => $stats,
            'items' => $pages,
            'clients' => $clients
        ]);
    }

    function moveElement(&$array, $a, $b) {
        $out = array_splice($array, $a, 1);
        array_splice($array, $b, 0, $out);
    }

    /**
     * @param Request $request
     * @param ImageManager $manager
     * @param PaginatorInterface $paginator
     * @return Response
     */
    public function imageManager(Request $request, ImageManager $manager, PaginatorInterface $paginator)
    {
        $query = $manager->getImagesQuery();
        $images = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('master/media/manager.html.twig', [
            'images' => $images
        ]);
    }
}