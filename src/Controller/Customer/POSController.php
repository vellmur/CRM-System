<?php

namespace App\Controller\Customer;

use App\Entity\Customer\POS;
use App\Form\Customer\POSType;
use App\Manager\MemberManager;
use App\Manager\ProductManager;
use JMS\Serializer\SerializerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class POSController extends AbstractController
{
    private $manager;

    private $memberManager;

    private $serializer;

    /**
     * POSController constructor.
     * @param ProductManager $manager
     * @param MemberManager $memberManager
     * @param SerializerInterface $serializer
     */
    public function __construct(ProductManager $manager, MemberManager $memberManager, SerializerInterface $serializer)
    {
        $this->manager = $manager;
        $this->memberManager = $memberManager;
        $this->serializer = $serializer;
    }

    /**
     * @param SerializerInterface $serializer
     * @return Response
     */
    public function dashboard(SerializerInterface $serializer)
    {
        $building = $this->getUser()->getBuilding();
        $statistics = $this->manager->getSalesStatistics($building);
        $monthSales = $this->manager->getMonthSales($building);

        return $this->render('customer/pos/dashboard.html.twig', [
            'stats' => $serializer->serialize($statistics, 'json'),
            'monthSales' => $monthSales
        ]);
    }

    /**
     * @param Request $request
     * @param PaginatorInterface $paginator
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     * @throws \Exception
     */
    public function entry(Request $request, PaginatorInterface $paginator)
    {
        $building = $this->getUser()->getBuilding();

        $order = new POS();
        $order->setBuilding($building);

        // Pre-set customer by email or phone if found
        if ($request->request->get('pos') && $request->request->get('pos')['customer']) {
            $customerData = $request->request->get('pos')['customer'];
            $order->setCustomer($this->memberManager->findCustomerByData($building, $customerData));
        }

        $form = $this->createForm(POSType::class, $order);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $order->getCustomer()->setBuilding($building);

            // Create customer if he is not created yet
            if (!$order->getCustomer()->getId() && $order->getCustomer()->getFullname()) {
                $order->getCustomer()->setIsLead(false);
                $customer = $this->memberManager->addCustomer($building, $order->getCustomer());
                $order->setCustomer($customer);
            } elseif (!$order->getCustomer()->getFullname()) {
                $order->setCustomer(null);
            }

            $pos = $this->manager->createPOSOrder($order);

            if ($pos instanceof \Exception) {
                $form->addError(new FormError($pos->getMessage()));
            } else {
                return $this->redirectToRoute('pos_entry');
            }
        }

        $query = $this->manager->getPOSOrders($building, 'today');
        $orders = $paginator->paginate($query, $request->query->getInt('page', 1), 20);

        $summary = $this->manager->getPOSSummary($building, 'today');

        return $this->render('customer/pos/pos.html.twig', [
            'form' => $form->createView(),
            'orders' => $orders,
            'summary' => $summary
        ]);
    }

    /**
     * @param Request $request
     * @return JsonResponse|Response
     */
    public function searchCustomers(Request $request)
    {
        if ($request->isXMLHttpRequest()) {
            $building = $this->getUser()->getBuilding();
            $search = $request->request->get('search');

            $customers = $this->manager->searchByCustomers($building, $search);

            $result = [];

            foreach ($customers as $customer) {
                $result['names'][] = $customer->getFullname();
                $result['values'][$customer->getFullname()] = [
                    'id' => $customer->getId(),
                    'firstName' => $customer->getFirstname(),
                    'lastName' => $customer->getLastname(),
                    'email' => $customer->getEmail(),
                    'phone' => $customer->getPhone()
                ];
            }

            return new JsonResponse($this->serializer->serialize(['customers' => $result], 'json'), 200);
        }

        return new Response('Request not valid', 400);
    }

    /**
     * @param Request $request
     * @return JsonResponse|Response
     */
    public function searchPOSProducts(Request $request)
    {
        if ($request->isXMLHttpRequest()) {
            $building = $this->getUser()->getBuilding();
            $search = $request->request->get('search');

            $products = $this->manager->searchPosProducts($building, $search);

            $result = [];

            foreach ($products as $product) {
                $productName = $product->getDescription() ? $product->getName() . ', ' . $product->getDescription() : $product->getName();
                $result['results'][] = $productName;
                $result['values'][$productName] = $product->getId();
                $result['names'][$product->getId()] = $product->getName() ;
                $result['prices'][$product->getId()] = $product->getPrice();
                $result['byQty'][$product->getId()] = $product->isPayByItem();
            }

            return new JsonResponse($this->serializer->serialize(['products' => $result], 'json'), 200);
        }

        return new Response('Request not valid', 400);
    }

    /**
     * @param Request $request
     * @param PaginatorInterface $paginator
     * @param $period
     * @return Response
     */
    public function orders(Request $request, PaginatorInterface $paginator, $period)
    {
        $building = $this->getUser()->getBuilding();

        $query = $this->manager->getPOSOrders($building, $period);
        $orders = $paginator->paginate($query, $request->query->getInt('page', 1), 20);
        $summary = $this->manager->getPOSSummary($building, $period);

        return $this->render('customer/pos/orders.html.twig', [
            'orders' => $orders,
            'summary' => $summary
        ]);
    }

    /**
     * @param POS $order
     * @return Response
     */
    public function invoice(POS $order)
    {
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'DejaVu Serif');

        $dompdf = new Dompdf($pdfOptions);
        $html = $this->renderView('customer/pos/invoice.html.twig', ['order' => $order]);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $dompdf->stream("mypdf.pdf", [
            "Attachment" => false
        ]);
    }

    /**
     * @param Request $request
     * @param POS $order
     * @return JsonResponse|Response
     */
    public function deleteOrder(Request $request, POS $order)
    {
        if ($request->isXMLHttpRequest()) {
            try {
                $this->manager->removePOSOrder($order);

                return new JsonResponse(['code' => 202, 'status' => 'success'], 202);
            } catch (\Exception $e) {
                return new JsonResponse($this->serializer->serialize(['error' => $e], 'json'), 500);
            }
        }

        return new Response('Request not valid', 400);
    }
}