<?php

namespace App\Controller\Customer;

use App\Entity\Customer\VendorOrder;
use App\Form\Customer\VendorOrderType;
use App\Manager\MemberManager;
use App\Manager\OrderManager;
use App\Manager\ShareManager;
use JMS\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class OrderController extends AbstractController
{
    private $manager;

    private $memberManager;

    private $serializer;

    /**
     * OrderController constructor.
     * @param OrderManager $manager
     * @param MemberManager $memberManager
     * @param SerializerInterface $serializer
     */
    public function __construct(OrderManager $manager, MemberManager $memberManager, SerializerInterface $serializer)
    {
        $this->manager = $manager;
        $this->memberManager = $memberManager;
        $this->serializer = $serializer;
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function vendorOrder(Request $request)
    {
        $building = $this->getUser()->getBuilding();

        $order = new VendorOrder();
        $order->setBuilding($building);

        // Create add form for adding of Vendor orders (top form)
        $form = $this->createForm(VendorOrderType::class, $order, [
            'date_format' => $this->getUser()->getDateFormatName(),
            'vendors' => $this->manager->getVendors($building)
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $order->setBuilding($building);
            $this->manager->createOrder($order);

            return $this->redirectToRoute('vendor_orders');
        }

        $productsForms = [];

        $orders = $this->manager->getVendorOrders($building);

        return $this->render('customer/order/for_vendor' . '.html.twig', [
            'form' => $form->createView(),
            'orders' => $orders,
            'productsForms' => $productsForms,
        ]);
    }
}