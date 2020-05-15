<?php

namespace App\Controller\Customer;

use App\Entity\Customer\Vendor;
use App\Form\Customer\VendorType;
use App\Manager\VendorManager;
use JMS\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class VendorController extends AbstractController
{
    private $manager;

    /**
     * VendorController constructor.
     * @param VendorManager $manager
     */
    public function __construct(VendorManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index()
    {
        $building = $this->getUser()->getBuilding();
        $vendors = $this->manager->getBuildingVendors($building);

        return $this->render('customer/vendor/list.html.twig', [
            'vendors' => $vendors
        ]);
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function add(Request $request)
    {
        $building = $this->getUser()->getBuilding();
        
        $vendor = new Vendor();
        $vendor->setBuilding($building);
        
        $form = $this->createForm(VendorType::class, $vendor);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $id = $this->manager->addVendor($vendor);

            return $this->redirectToRoute('vendor_edit', ['id' => $id]);
        }

        return $this->render('customer/vendor/add.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @param Request $request
     * @param Vendor $vendor
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function edit(Request $request, Vendor $vendor)
    {
        $form = $this->createForm(VendorType::class, $vendor);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->manager->updateVendor($vendor);

            return $this->redirectToRoute('vendor_edit', [
                'id' => $vendor->getId()
            ]);
        }

        return $this->render('customer/vendor/edit.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @param Vendor $vendor
     * @return JsonResponse
     */
    public function delete(Vendor $vendor)
    {
        $this->manager->removeVendor($vendor);

        return new JsonResponse([
            'redirect' => $this->generateUrl('vendor_list'),
            'status' => 'success'
        ], 202);
    }

    /**
     * @param Request $request
     * @param SerializerInterface $serializer
     * @return JsonResponse
     */
    public function search(Request $request, SerializerInterface $serializer)
    {
        $building = $this->getUser()->getBuilding();

        $searchText = $request->request->get('searchText');
        $vendors = $this->manager->searchVendors($building, $searchText);

        $template = $this->render('customer/vendor/search.html.twig', ['vendors' => $vendors])->getContent();

        return new JsonResponse(
            $serializer->serialize([
                'template' => $template,
                'counter' => count($vendors)
            ], 'json'), 200);
    }
}