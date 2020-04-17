<?php

namespace App\Controller\Customer;

use App\Entity\Customer\Location;
use App\Form\Customer\LocationType;
use App\Manager\MemberManager;
use App\Service\ModuleChecker;
use JMS\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class LocationController extends AbstractController
{
    private $manager;

    /**
     * LocationController constructor.
     * @param MemberManager $manager
     */
    public function __construct(MemberManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @param Request $request
     * @param ModuleChecker $moduleChecker
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function addLocation(Request $request)
    {
        $client = $this->getUser()->getClient();

        $location = new Location();
        $location->setClient($client);
        $location->addWorkDays();

        $form = $this->createForm(LocationType::class, $location, [
            'country' => $client->getCountry()
        ]);

        $form->remove('isActive');
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->manager->createLocation($location);

            return $this->redirectToRoute('member_location');
        }

        $locations = $this->manager->getLocations($client, true);

        $formsArray = [];

        foreach ($locations as $location) {
            array_push($formsArray, $this->createForm(LocationType::class, $location, [
                'country' => $client->getCountry()
            ])->createView());
        }

        return $this->render('customer/location.html.twig', [
            'form' => $form->createView(),
            'forms' => $formsArray
        ]);
    }

    /**
     * @param Request $request
     * @param SerializerInterface $serializer
     * @param Location $location
     * @return JsonResponse|Response
     */
    public function updateLocation(Request $request, SerializerInterface $serializer, Location $location)
    {
        if ($request->isXMLHttpRequest()) {
            $client = $this->getUser()->getTeam()->getClient();

            $form = $this->createForm(LocationType::class, $location, [
                'country' => $client->getCountry()
            ]);

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                // Add workdays to locations if they not exists
                if (count($location->getWorkdays()) == 0) {
                    $location->addWorkdays();
                }

                $this->manager->updateLocation($location);

                return new JsonResponse(['code' => 202, 'status' => 'success'], 202);
            } else {
                return new JsonResponse($serializer->serialize(['error' => $form], 'json'), 500);
            }
        }

        return new Response('Request not valid', 400);
    }

    /**
     * @param Request $request
     * @param SerializerInterface $serializer
     * @param Location $location
     * @return JsonResponse|Response
     */
    public function deleteLocation(Request $request, SerializerInterface $serializer, Location $location)
    {
        if ($request->isXMLHttpRequest()) {
            try {
                $this->manager->removeLocation($location);

                return new JsonResponse(['code' => 202, 'status' => 'success'], 202);
            } catch (\Exception $e) {
                return new JsonResponse($serializer->serialize(['error' => $e], 'json'), 500);
            }
        }

        return new Response('Request not valid', 400);
    }
}