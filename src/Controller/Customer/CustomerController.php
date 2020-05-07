<?php

namespace App\Controller\Customer;

use App\Manager\ImportManager;
use App\Manager\MemberManager;
use App\Manager\OrderManager;
use App\Service\Mail\Sender;
use App\Service\SpreadsheetService;
use JMS\Serializer\SerializerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\Customer\Customer;
use App\Form\Customer\CustomerType;

class CustomerController extends AbstractController
{
    private $manager;

    private $sender;

    private $serializer;

    /**
     * MemberController constructor.
     * @param MemberManager $manager
     * @param Sender $sender
     * @param SerializerInterface $serializer
     */
    public function __construct(MemberManager $manager, Sender $sender, SerializerInterface $serializer)
    {
        $this->manager = $manager;
        $this->sender = $sender;
        $this->serializer = $serializer;
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     * @throws \Exception
     */
    public function add(Request $request)
    {
        $client = $this->getUser()->getClient();

        $requestData = $request->request->get('customer');
        $apartmentNum = $requestData != null ? $requestData['apartment']['number'] : null;
        $apartment = $this->manager->findOrCreateApartment($client, $apartmentNum);

        $customer = new Customer();
        $customer->setApartment($apartment);

        $form = $this->createForm(CustomerType::class, $customer)->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->manager->addCustomer($client, $customer);

            return $this->redirectToRoute('member_edit', ['id' => $customer->getId()]);
        }

        return $this->render('customer/add.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @param Request $request
     * @param Customer $customer
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function edit(Request $request, Customer $customer)
    {
        $requestData = $request->request->get('customer');
        $apartmentNum = $requestData != null ? $requestData['apartment']['number'] : null;

        if ($apartmentNum && (!$customer->getApartment() || $customer->getApartment()->getNumber() != $apartmentNum)) {
            $apartment = $this->manager->findOrCreateApartment($customer->getClient(), $apartmentNum);
            $customer->setApartment($apartment);
        }

        $form = $this->createForm(CustomerType::class, $customer)->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->manager->update($customer);

            return $this->redirectToRoute('member_edit', ['id' => $customer->getId()]);
        }

        return $this->render('customer/edit.html.twig', [
            'form' => $form->createView(),
            'customer' => $customer
        ]);
    }

    /**
     * @param Customer $member
     * @return JsonResponse
     */
    public function delete(Customer $member)
    {
        $this->manager->removeCustomer($member);

        return new JsonResponse(['redirect' => $this->generateUrl('member_list'), 'status' => 'success'], 202);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws \Exception
     */
    public function checkEmail(Request $request)
    {
        $customer = $this->manager->findOneByEmailOrPhone($this->getUser()->getClient(), $request->request->get('email'));

        $link = null;

        if ($customer && $customer->getId() != $request->request->get('id')) {
            $link = 'A customer with the same email exists.  <br/> <a class="white-link" target="_blank" href="'
                . $this->generateUrl('member_edit', ['id' => $customer->getId()]) . '">Click here to view.</a>';
        }

        return new JsonResponse($this->serializer->serialize([
            'link' => $link
        ], 'json'), 202);
    }

    /**
     * @param Request $request
     * @param PaginatorInterface $paginator
     * @param OrderManager $orderManager
     * @return JsonResponse|Response
     */
    public function list(Request $request, PaginatorInterface $paginator, OrderManager $orderManager)
    {
        $client = $this->getUser()->getClient();

        $searchBy = $request->query->get('searchBy') && $request->query->get('searchBy') != 'undefined'
            ? $request->query->get('searchBy')
            : 'all';

        $query = $this->manager->searchCustomers($client, $searchBy, $request->query->get('search'));
        $customers = $paginator->paginate($query, $request->query->getInt('page', 1), 20);

        if (!$request->isXMLHttpRequest()) {
            $ordersInfo = $orderManager->countOrders($client);

            return $this->render('customer/list.html.twig', [
                'customers' => $customers,
                'ordersStats' => $ordersInfo
            ]);
        } else {
            $template = $this->render('customer/forms/table.html.twig', [
                'customers' => $customers
            ])->getContent();

            return new JsonResponse([
                'template' => $template,
                'counter' => $customers->getTotalItemCount()
            ],  200);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse|Response
     */
    public function searchCustomers(Request $request)
    {
        if ($request->isXMLHttpRequest()) {
            $client = $this->getUser()->getClient();
            $customers = $this->manager->searchByAllCustomers($client, $request->request->get('search'));

            $result = [];

            foreach ($customers as $customer) {
                $result['names'][] = $customer->getFullname();
                $result['values'][$customer->getFullname()] = $customer->getId();
            }

            return new JsonResponse(
                $this->serializer->serialize([
                    'customers' => $result,
                ], 'json'), 200);
        }

        return new Response('Request not valid', 400);
    }

    /**
     * @param Request $request
     * @param SpreadsheetService $spreadsheetService
     * @return JsonResponse|Response
     */
    public function countFileRows(Request $request, SpreadsheetService $spreadsheetService)
    {
        if ($request->isXMLHttpRequest()) {
            if (isset($request->files->all()['parseMembers'])) {
                $spreadsheet = $spreadsheetService->loadFile($request->files->get('parseMembers')['file']);

                return new JsonResponse($this->serializer->serialize([
                    'rows' => $spreadsheet->getHighestRow() - 1
                ], 'json'), 202);
            } else {
                return new JsonResponse($this->serializer->serialize([
                    'error' => 'File not valid! Please try again!'
                ], 'json'), 500);
            }
        }

        return new Response('Request not valid', 400);
    }

    /**
     * @param Request $request
     * @param SpreadsheetService $spreadsheetService
     * @param ImportManager $importManager
     * @return Response
     */
    public function parse(Request $request, SpreadsheetService $spreadsheetService, ImportManager $importManager)
    {
        $rowsNum = 0;
        $importedNum = 0;

        if (isset($request->files->all()['parseMembers']) && $request->files->get('parseMembers')['file']) {
            $spreadsheet = $spreadsheetService->loadFile($request->files->get('parseMembers')['file']);
            $rowsNum = $spreadsheet->getHighestRow() - 1;

            $members = $spreadsheetService->createAssociativeArray($spreadsheet);

            $status = $request->request->get('parseMembers')['status'];
            $importedNum = $importManager->importCustomers($this->getUser()->getClient(), $members, $status);
        }

        return $this->render('customer/parse.html.twig', [
            'rowsCounter' => $rowsNum,
            'imported' => $importedNum
        ]);
    }

    /**
     * @param Request $request
     * @param OrderManager $orderManager
     * @return JsonResponse
     */
    public function searchOrders(Request $request, OrderManager $orderManager)
    {
        $client = $this->getUser()->getClient();
        $status = $request->request->get('searchStatus');
        $invoices = $orderManager->searchOpenOrders($client, $status);

        $template = $this->render('customer/invoices_table_list.html.twig', [
            'invoices' => $invoices
        ])->getContent();

        return new JsonResponse(
            $this->serializer->serialize([
                'template' => $template,
                'counter' => 1
            ], 'json'), 200);
    }
}
