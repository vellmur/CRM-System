<?php

namespace App\Controller\Customer;

use App\Entity\Customer\Share;
use App\Form\Customer\ShareType;
use App\Manager\MemberManager;
use App\Manager\ShareManager;
use JMS\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ShareController extends AbstractController
{
    private $manager;

    private $memberManager;

    private $serializer;

    /**
     * ShareController constructor.
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
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function addShare(Request $request)
    {
        $client = $this->getUser()->getClient();

        $share = new Share();
        $share->setClient($client);

        $form = $this->createForm(ShareType::class, $share);
        $form->remove('isActive');

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->memberManager->createShare($share);

            return $this->redirectToRoute('member_share');
        }

        $shares = $this->memberManager->getShares($client);

        $formsArray = [];

        foreach ($shares as $share) {
            array_push($formsArray, $this->createForm(ShareType::class, $share)->createView());
        }

        return $this->render('customer/share.html.twig', [
            'form' => $form->createView(),
            'forms' => $formsArray
        ]);
    }

    /**
     * @param Request $request
     * @param Share $share
     * @return JsonResponse|Response
     */
    public function ajaxUpdateShare(Request $request, Share $share)
    {
        if ($request->isXMLHttpRequest()) {
            $form = $this->createForm(ShareType::class, $share);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $this->getDoctrine()->getManager()->flush();

                return new JsonResponse(['code' => 202, 'status' => 'success'], 202);
            } else {
                return new JsonResponse($this->serializer->serialize(['error' => $form], 'json'), 500);
            }
        }

        return new Response('Request not valid', 400);
    }

    /**
     * @param Request $request
     * @param Share $share
     * @return JsonResponse|Response
     */
    public function ajaxDeleteShare(Request $request, Share $share)
    {
        if ($request->isXMLHttpRequest()) {
            try {
                $this->manager->removeShare($share);

                return new JsonResponse(['code' => 202, 'status' => 'success'], 202);
            } catch (\Exception $e) {
                return new JsonResponse($this->serializer->serialize(['error' => $e], 'json'), 500);
            }
        }

        return new Response('Request not valid', 400);
    }

    /**
     * @return Response
     */
    public function sharesWeeks()
    {
        $client = $this->getUser()->getClient();

        $now = new \DateTime("midnight");
        $now->modify('+7 days');

        $sharesWeeks = $this->memberManager->getSuspendedWeeks($client);

        $nextWeeks = [];

        // Get next 15 weeks with week number and year
        for ($i = 0; $i < 15; $i++) {
            $year = $now->format('Y');
            $week = $now->format('W');

            $nextWeeks[] = [
                'year' => $year,
                'number' => $week,
                'isSuspended' => isset($sharesWeeks[$year]) && in_array($week, $sharesWeeks[$year]) ? true : false
            ];

            $now->modify('+7 days');
        }

        return $this->render('customer/share_weeks.html.twig', [
            'sharesWeeks' => $nextWeeks
        ]);
    }

    /**
     * @param Request $request
     * @return JsonResponse|Response
     */
    public function suspendWeek(Request $request)
    {
        if ($request->isXMLHttpRequest()) {
            $client = $this->getUser()->getClient();

            $year = $request->request->get('year');
            $week = $request->request->get('week');
            $suspendedWeek = $this->memberManager->suspendWeek($client, $year, $week);

            if (!($suspendedWeek instanceof \Throwable)) {
                return new JsonResponse(['status' => 'success'], 200);
            } else {
                return new JsonResponse($this->serializer->serialize([
                    'error' => $suspendedWeek->getMessage()
                ], 'json'), 500);
            }
        }

        return new Response('Request not valid', 400);
    }
}