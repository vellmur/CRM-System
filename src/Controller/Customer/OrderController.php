<?php

namespace App\Controller\Customer;

use App\Entity\Customer\CustomerOrders;
use App\Entity\Customer\ShareProduct;
use App\Entity\Customer\VendorOrder;
use App\Form\Customer\CustomerOrdersType;
use App\Form\Customer\ShareProductType;
use App\Form\Customer\VendorOrderType;
use App\Manager\MemberManager;
use App\Manager\ShareManager;
use JMS\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class OrderController extends AbstractController
{
    private $manager;

    private $memberManager;

    private $serializer;

    /**
     * OrderController constructor.
     * @param ShareManager $manager
     * @param MemberManager $memberManager
     * @param SerializerInterface $serializer
     */
    public function __construct(ShareManager $manager, MemberManager $memberManager, SerializerInterface $serializer)
    {
        $this->manager = $manager;
        $this->memberManager = $memberManager;
        $this->serializer = $serializer;
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function memberOrder(Request $request)
    {
        $client = $this->getUser()->getClient();
        $this->manager->deleteOldShares($client);

        $order = new CustomerOrders();
        $order->setClient($client);

        $form = $this->createForm(CustomerOrdersType::class, $order, [
            'date_format' => $this->getUser()->getDateFormatName()
         ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $order->setClient($client);
            $this->manager->createOrder($order);

            return $this->redirectToRoute('member_orders');
        }

        $ordersForms = [];
        $productsForms = [];

        $products = $this->memberManager->getAvailableProducts($client, 1);

        // Create update order forms and related add product form and list of update products forms for all existed orders
        foreach ($this->manager->getCustomerOrders($client) as $order) {
            // Push update order forms to $ordersForms array
            array_push($ordersForms, $this->createForm(CustomerOrdersType::class, $order, [
                'date_format' => $this->getUser()->getDateFormatName()
            ])->createView());

            // First form in array is Add product form
            $productsForms[$order->getId()][0] = $this->createForm(ShareProductType::class, new ShareProduct(), [
                'client' => $client,
                'products' => $products
            ])->createView();

            // Push to array forms for each product related to order (for updating/removing products)
            foreach ($this->manager->getOrderProducts($order, 'member') as $key => $product) {
                $productsForms[$order->getId()][$key + 1] = $this->createForm(ShareProductType::class, $product, [
                    'client' => $client,
                    'products' => $products
                ])->createView();
            }
        }

        return $this->render('customer/order/for_member.html.twig', [
            'form' => $form->createView(),
            'ordersForms' => $ordersForms,
            'productsForms' => $productsForms
        ]);
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function vendorOrder(Request $request)
    {
        $client = $this->getUser()->getTeam()->getClient();

        $this->manager->deleteOldShares($client);

        $order = new VendorOrder();
        $order->setClient($client);

        // Create add form for adding of Vendor orders (top form)
        $form = $this->createForm(VendorOrderType::class, $order, [
            'date_format' => $this->getUser()->getDateFormatName(),
            'vendors' => $this->manager->getVendors($client)
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $order->setClient($client);
            $this->manager->createOrder($order);

            return $this->redirectToRoute('vendor_orders');
        }

        $productsForms = [];

        $orders = $this->manager->getVendorOrders($client);

        // Create add product form and list of update products forms for all existed orders
        foreach ($orders as $order) {
            $products = [];

            // Get products only if vendor category exists
            if ($order->getVendor()->getCategory())  {
                $products = $this->memberManager->getAvailableProducts($client, $order->getVendor()->getCategory());
            }

            // First form in array is Add product form
            $productsForms[$order->getId()][0] = $this->createForm(ShareProductType::class, new ShareProduct(), [
                'client' => $client,
                'products' => $products
            ])->createView();

            // Push to array forms for each product related to order (for updating/removing products)
            foreach ($this->manager->getOrderProducts($order, 'vendor') as $key => $product) {
                $productsForms[$order->getId()][$key + 1] = $this->createForm(ShareProductType::class, $product, [
                    'client' => $client,
                    'products' => $products
                ])->createView();
            }
        }

        return $this->render('customer/order/for_vendor' . '.html.twig', [
            'form' => $form->createView(),
            'orders' => $orders,
            'productsForms' => $productsForms,
        ]);
    }

    /**
     * @param Request $request
     * @param $orderId
     * @param $role
     * @return JsonResponse|Response
     */
    public function updateShare(Request $request, $orderId, $role)
    {
        if ($request->isXMLHttpRequest()) {
            $shareOrder = $this->manager->getShareOrder($orderId, $role);
            $client = $shareOrder->getClient();

            $options = ['date_format' => $client->getOwnerDateFormat()];

            if ($role == 'member') {
                $type = 'CustomerOrdersType';
            } else {
                $type = 'VendorOrderType';
                $options['vendors'] = $this->manager->getVendors($client);
            }

            $form = $this->createForm('App\Form\Customer\\' . $type, $shareOrder, $options);

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $this->manager->updateShare();

                return new JsonResponse(['code' => 202, 'status' => 'success'], 202);
            } else {
                return new JsonResponse($this->serializer->serialize(['error' => $form], 'json'), 500);
            }
        }

        return new Response('Request not valid', 400);
    }

    /**
     * @param Request $request
     * @param $role
     * @param $orderId
     * @return JsonResponse|Response
     */
    public function deleteShare(Request $request, $role, $orderId)
    {
        if ($request->isXMLHttpRequest()) {
            try {
                $this->manager->deleteShare($orderId, $role);

                return new JsonResponse(['code' => 202, 'status' => 'success'], 202);
            } catch (\Exception $e) {
                return new JsonResponse($this->serializer->serialize(['error' => $e->getMessage()], 'json'), 500);
            }
        }

        return new Response('Request not valid', 400);
    }

    /**
     * @param Request $request
     * @param $role
     * @param $orderId
     * @return JsonResponse|Response
     */
    public function addProduct(Request $request, $role, $orderId)
    {
        if ($request->isXMLHttpRequest()) {
            $order = $this->manager->getShareOrder($orderId, $role);
            $client = $order->getClient();

            $productsCategory = $role == 'member' ? 1 : $order->getVendor()->getCategory();
            $products = $this->memberManager->getAvailableProducts($client, $productsCategory);

            $product = new ShareProduct();

            $form = $this->createForm(ShareProductType::class, $product, [
                'client' => $client,
                'products' => $products
            ]);

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $this->manager->createProduct($product, $order, $role);

                $data = $this->renderView('customer/order/templates/order_product_row.html.twig', [
                    'form' => $form->createView()
                ]);

                return new JsonResponse(['code' => 202, 'status' => 'success', 'template' => $data], 202);
            } else {
                return new JsonResponse($this->serializer->serialize(['error' => $form], 'json'), 500);
            }
        }

        return new Response('Request not valid', 400);
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function harvestList(Request $request)
    {
        $client = $this->getUser()->getClient();

        $this->manager->deleteOldShares($client);

        $reports = $this->manager->getHarvestList($client);

        // Get dates navigation from reports keys (they are equal to pickup dates)
        $datesNav = array_keys($reports);

        // Set reports to the entered date, or to the first date in reports. If reports is empty, return empty array;
        if (!$request->query->get('date') && count($datesNav) > 0) $request->query->set('date', $datesNav[0]);
        $reports = count($reports) ? $reports[$request->query->get('date')] : [];

        return $this->render('customer/order/harvest_list.html.twig', [
            'datesNav' => $datesNav,
            'reports' => $reports
        ]);
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function packaging(Request $request)
    {
        $client = $this->getUser()->getClient();

        $reports = $this->manager->getPackagingList($client);

        // Get dates navigation from reports keys (they are equal to pickup dates)
        $datesNav = array_keys($reports);

        // Set reports to the entered date, or to the first date in reports. If reports is empty, return empty array;
        if (!$request->query->get('date') && count($datesNav) > 0) $request->query->set('date', $datesNav[0]);
        $reports = count($reports) ? $reports[$request->query->get('date')] : [];

        return $this->render('customer/order/packaging_list.html.twig', [
            'datesNav' => $datesNav,
            'reports' => $reports
        ]);
    }
}