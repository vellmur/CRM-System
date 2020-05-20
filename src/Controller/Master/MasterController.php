<?php

namespace App\Controller\Master;

use App\Entity\Building\Referral;
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
use App\Entity\Building\Affiliate;
use App\Form\Building\AffiliateType;
use App\Entity\Building\Building;
use App\Form\Building\AccessType;
use App\Entity\Building\ModuleAccess;

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
        $buildings = $this->manager->getSoftwareBuildings();
        $lapsedBuildings = $this->manager->getLapsedBuildings($buildings);
        $stats = $this->service->countBuildingsStats();

        return $this->render('master/dashboard.html.twig', [
            'buildings' => $buildings,
            'stats' => $stats,
            'lapsedBuildings' => $lapsedBuildings
        ]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function filterBuildings(Request $request)
    {
        $status = $request->query->get('status');
        $text = $request->query->get('search');

        $buildings = $this->manager->searchBuildingsBy($status, $text);
        $lapsedBuildings = $this->manager->getLapsedBuildings($buildings);

        $template = $this->render('master/building/search.html.twig', [
            'buildings' => $buildings,
            'lapsedBuildings' => $lapsedBuildings
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
    public function buildings()
    {
        $buildings = $this->manager->getSoftwareBuildings();
        $lapsedBuildings = $this->manager->getLapsedBuildings($buildings);

        return $this->render('master/building/list.html.twig', [
            'buildings' => $buildings,
            'lapsedBuildings' => $lapsedBuildings,
            'buildingsSummary' => []
        ]);
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function searchBuildings(Request $request)
    {
        $buildings = $this->manager->searchBuildings($request->query->get('search'));
        $lapsedBuildings = $this->manager->getLapsedBuildings($buildings);

        $template = $this->render('master/building/search.html.twig', [
            'buildings' => $buildings,
            'lapsedBuildings' => $lapsedBuildings
        ])->getContent();

        return new JsonResponse([
            'template' => $template,
            'counter' => count($buildings)
        ]);
    }

    /**
     * @param Building $building
     * @return Response
     */
    public function editBuilding(Building $building)
    {
        $formsArray = [];

        foreach ($building->getAccesses() as $access) {
            array_push($formsArray, $this->createForm(AccessType::class, $access, [
                'date_format' => $this->getUser()->getDateFormatName()
            ])->createView());
        }
        
        return $this->render('master/building/edit_building.html.twig', [
            'modules' => ModuleAccess::MODULES,
            'forms' => $formsArray,
            'building' => $building,
            'users' => $this->manager->getBuildingUsers($building)
        ]);
    }

    /**
     * @param Request $request
     * @param Building $building
     * @return JsonResponse|Response
     */
    public function deleteBuilding(Request $request, Building $building)
    {
        if ($request->isXMLHttpRequest() && $building) {
            $this->manager->deleteBuilding($building);

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
                $this->manager->updateBuildingAccess($access);
                $accessUpdater->updateModulesAccess($access->getBuilding());

                return new JsonResponse(['code' => 202, 'status' => 'success']);
            } else {
                return new JsonResponse($this->serializer->serialize(['error' => $form], 'json'), 500);
            }
        }

        return new Response('Request not valid', 400);
    }

    /**
     * @param string|null $module
     * @param Building|null $building
     * @return Response
     */
    public function statistics(?string $module, ?Building $building)
    {
        $stats = $this->manager->getViewStatistics($module, $building);
        $modules = ['owners', 'website', 'promotion'];

        $pages = array_keys($stats);

        sort($pages);

        if (($key = array_search('All', $pages)) !== false) {
            unset($pages[$key]);
        }

        array_unshift($pages,"All");

        $buildings = $this->manager->getActiveBuildings();

        return $this->render('master/statistics.html.twig', [
            'modules' => $modules,
            'stats' => $stats,
            'items' => $pages,
            'buildings' => $buildings
        ]);
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