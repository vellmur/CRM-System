<?php

namespace App\Controller\Owner;

use App\Manager\ImportManager;
use App\Manager\MemberManager;
use App\Service\Mail\Sender;
use App\Service\SpreadsheetService;
use JMS\Serializer\SerializerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\Owner\Owner;
use App\Form\Owner\OwnerType;

class OwnerController extends AbstractController
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
        $building = $this->getUser()->getBuilding();

        $requestData = $request->request->get('owner');
        $apartmentNum = $requestData != null ? $requestData['apartment']['number'] : null;
        $apartment = $this->manager->findOrCreateApartment($building, $apartmentNum);

        $owner = new Owner();
        $owner->setBuilding($building);
        $owner->setApartment($apartment);

        $form = $this->createForm(OwnerType::class, $owner);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->manager->addOwner($building, $owner);

            return $this->redirectToRoute('member_edit', ['id' => $owner->getId()]);
        }

        return $this->render('owner/add.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @param Request $request
     * @param Owner $owner
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function edit(Request $request, Owner $owner)
    {
        $requestData = $request->request->get('owner');
        $apartmentNum = $requestData != null ? $requestData['apartment']['number'] : null;

        if ($apartmentNum && (!$owner->getApartment() || $owner->getApartment()->getNumber() != $apartmentNum)) {
            $apartment = $this->manager->findOrCreateApartment($owner->getBuilding(), $apartmentNum);
            $owner->setApartment($apartment);
        }

        $form = $this->createForm(OwnerType::class, $owner)->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->manager->update($owner);

            return $this->redirectToRoute('member_edit', ['id' => $owner->getId()]);
        }

        return $this->render('owner/edit.html.twig', [
            'form' => $form->createView(),
            'owner' => $owner
        ]);
    }

    /**
     * @param Owner $member
     * @return JsonResponse
     */
    public function delete(Owner $member)
    {
        $this->manager->removeOwner($member);

        return new JsonResponse(['redirect' => $this->generateUrl('member_list'), 'status' => 'success'], 202);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws \Exception
     */
    public function checkEmail(Request $request)
    {
        $owner = $this->manager->findOneByEmailOrPhone($this->getUser()->getBuilding(), $request->request->get('email'));

        $link = null;

        if ($owner && $owner->getId() != $request->request->get('id')) {
            $link = 'A owner with the same email exists.  <br/> <a class="white-link" target="_blank" href="'
                . $this->generateUrl('member_edit', ['id' => $owner->getId()]) . '">Click here to view.</a>';
        }

        return new JsonResponse($this->serializer->serialize([
            'link' => $link
        ], 'json'), 202);
    }

    /**
     * @param Request $request
     * @param PaginatorInterface $paginator
     * @return Response
     */
    public function list(Request $request, PaginatorInterface $paginator)
    {
        $building = $this->getUser()->getBuilding();

        $query = $this->manager->searchOwners($building, 'all', $request->query->get('search'));
        $owners = $paginator->paginate($query, $request->query->getInt('page', 1), 20);

        return $this->render('owner/list.html.twig', [
            'owners' => $owners
        ]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function searchOwners(Request $request)
    {
        if ($request->getMethod() === 'POST') {
            $owners = $this->manager->searchByAllOwners($this->getUser()->getBuilding(), $request->query->get('search'));
            $template = $this->render('owner/forms/table.html.twig', [
                'owners' => $owners,
                'withoutNav' => true
            ])->getContent();

            return new JsonResponse([
                'template' => $template,
                'counter' => count($owners)
            ], Response::HTTP_OK);
        }

        return new JsonResponse('Request not valid', 400);
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
            $importedNum = $importManager->importOwners($this->getUser()->getBuilding(), $members, $status);
        }

        return $this->render('owner/parse.html.twig', [
            'rowsCounter' => $rowsNum,
            'imported' => $importedNum
        ]);
    }
}
