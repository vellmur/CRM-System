<?php

namespace App\Controller\Customer;

use App\Manager\ImportManager;
use App\Manager\MemberManager;
use App\Manager\ShareManager;
use App\Service\Mail\Sender;
use App\Service\MemberService;
use App\Service\SpreadsheetService;
use JMS\Serializer\SerializerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\Customer\Customer;
use App\Form\Customer\MemberType;

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

        $customer = new Customer();
        $customer->setClient($client);

        $form = $this->createForm(MemberType::class, $customer, [
            'date_format' => $this->getUser()->getDateFormatName()
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->manager->addMember($customer);
            } catch (\Exception $exception) {
                die(var_dump($exception->getMessage()));
            }

            // If customer have active shares after added, send activation email and update status
            $activeShare = $this->manager->checkActivation($customer);

            if ($activeShare && $customer->getEmail()) {
                $email = $this->manager->getEmailManager()->createAutoLog($customer->getClient(), 'activation');
                $this->sender->sendAutomatedEmail($email, $customer, $activeShare);
            }

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
     * @throws \Exception
     */
    public function edit(Request $request, Customer $customer)
    {
        $form = $this->createForm(MemberType::class, $customer, [
            'date_format' => $this->getUser()->getDateFormatName()
        ]);

        $form->handleRequest($request);

        // If action not ajax, update customer and send all notifies
        if ($form->isSubmitted() && $form->isValid() && !$request->isXMLHttpRequest()) {
            $this->manager->update($customer);

            // If customer didn't activate (didn't receive activation email) -> send it and update status if active
            if (!$customer->isActivated()) {
                $activeShare = $this->manager->checkActivation($customer);

                if ($activeShare && $customer->getEmail()) {
                    $email = $this->manager->getEmailManager()->createAutoLog($customer->getClient(), 'activation');
                    $this->sender->sendAutomatedEmail($email, $customer, $activeShare);
                }
            }

            return $this->redirectToRoute('member_edit', ['id' => $customer->getId()]);
        }

        $pickups = $this->manager->getFollowingPickups($customer);

        return $this->render('customer/edit.html.twig', [
            'form' => $form->createView(),
            'member' => $customer,
            'pickups' => $pickups,
            'status' => $this->manager->getMemberStatus($customer)
        ]);
    }

    /**
     * @param Customer $member
     * @return JsonResponse
     */
    public function delete(Customer $member)
    {
        $this->manager->removeMember($member);

        return new JsonResponse(['redirect' => $this->generateUrl('member_list'), 'status' => 'success'], 202);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function checkEmail(Request $request)
    {
        $client = $this->getUser()->getClient();
        $member = $this->manager->findOneByEmailOrPhone($client, $request->request->get('email'));

        $link = null;

        if ($member && $member->getId() != $request->request->get('id')) {
            $link = 'A customer with the same email exists.  <br/> <a class="white-link" target="_blank" href="'
                . $this->generateUrl('member_edit', ['id' => $member->getId()]) . '">Click here to view.</a>';
        }

        return new JsonResponse($this->serializer->serialize([
            'link' => $link
        ], 'json'), 202);
    }

    /**
     * @param Request $request
     * @param PaginatorInterface $paginator
     * @param ShareManager $shareManager
     * @param MemberService $service
     * @return JsonResponse|Response
     */
    public function list(
        Request $request,
        PaginatorInterface $paginator,
        ShareManager $shareManager,
        MemberService $service
    ) {
        $client = $this->getUser()->getClient();

        $searchBy = $request->query->get('searchBy') && $request->query->get('searchBy') != 'undefined'
            ? $request->query->get('searchBy')
            : 'all';

        $query = $this->manager->searchCustomers($client, $searchBy, $request->query->get('search'));
        $members = $paginator->paginate($query, $request->query->getInt('page', 1), 20);

        if (!$request->isXMLHttpRequest()) {
            $shares = $shareManager->getCustomerSharesArray($client);
            $sharesNum = $shareManager->countShareMembers($client);
            $statusAmount = $shareManager->countShareMembersByStatus($client);
            $renewalNum = $shareManager->countRenewalMembers($client);
            $newWeekNum = $shareManager->countNewByDays($client, 7);
            $newMonthNum = $shareManager->countNewByDays($client, 30);
            $sharesInfo = $service->countSharesData($shares, $sharesNum, $statusAmount, $renewalNum, $newWeekNum, $newMonthNum);
            $ordersInfo = $shareManager->countOrders($client);

            return $this->render('customer/list.html.twig', [
                'members' => $members,
                'sharesStats' => $sharesInfo,
                'ordersStats' => $ordersInfo
            ]);
        } else {
            $template = $this->render('customer/forms/table.html.twig', ['members' => $members])->getContent();

            return new JsonResponse([
                'template' => $template,
                'counter' => $members->getTotalItemCount()
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
     * @param ShareManager $shareManager
     * @return JsonResponse
     */
    public function searchSummary(Request $request, ShareManager $shareManager)
    {
        $status = $request->request->get('searchStatus');
        $share = $request->request->get('searchItem');
        $shares = $shareManager->searchSummaryShares($this->getUser()->getClient(), $status, $share);

        $template = $this->render('customer/forms/summary_list.html.twig', ['shares' => $shares])->getContent();

        return new JsonResponse(
            $this->serializer->serialize([
                'template' => $template,
                'counter' => count($shares)
            ], 'json'), 200);
    }

    /**
     * @param Request $request
     * @param SpreadsheetService $spreadsheetService
     * @return JsonResponse|Response
     */
    public function countFileRows(Request $request, SpreadsheetService $spreadsheetService)
    {
        if ($request->isXMLHttpRequest()) {
            // If file added we parse it
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

        // If file added we parse it
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
     * @param ShareManager $shareManager
     * @return JsonResponse
     */
    public function searchOrders(Request $request, ShareManager $shareManager)
    {
        $client = $this->getUser()->getClient();
        $status = $request->request->get('searchStatus');
        $invoices = $shareManager->searchOpenOrders($client, $status);

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
