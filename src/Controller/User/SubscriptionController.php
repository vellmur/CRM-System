<?php

namespace App\Controller\User;

use App\Entity\Client\Merchant;
use App\Form\Client\SubscriptionType;
use App\Form\User\Payments\MerchantType;
use App\Manager\User\SubscriptionManager;
use JMS\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SubscriptionController extends AbstractController
{
    private $manager;

    /**
     * SubscriptionController constructor.
     * @param SubscriptionManager $manager
     */
    public function __construct(SubscriptionManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function index(Request $request)
    {
        $form = $this->createForm(SubscriptionType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isSubmitted() && $form->isValid()) {

            } else {
                return new JsonResponse(['errors' => $this->getErrorsFromForm($form)], 500);
            }
        }

        return $this->render('account/subscription/subscription.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @param FormInterface $form
     * @return array
     */
    private function getErrorsFromForm(FormInterface $form)
    {
        $errors = [];

        foreach ($form->getErrors() as $error) {
            $errors[] = $error->getMessage();
        }

        foreach ($form->all() as $childForm) {
            if ($childForm instanceof FormInterface) {
                if ($childErrors = $this->getErrorsFromForm($childForm)) {
                    $errors[$childForm->getName()] = $childErrors;
                }
            }
        }

        return $errors;
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function merchant(Request $request)
    {
        $client = $this->getUser()->getClient();

        $merchant = new Merchant();
        $merchant->setClient($client);

        $env = $this->getServerEnv($request->getHost());

        $form = $this->createForm( MerchantType::class, $merchant, [
            'env' => $env
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->manager->createMerchant($merchant);

            return $this->redirect($this->generateUrl('profile_merchant'));
        }

        $merchants = $this->manager->getMerchants($client);

        $forms = [];

        foreach ($merchants as $merchant) {
            array_push($forms, $this->createForm('App\Form\User\Payments\MerchantType', $merchant,[
                'env' => $env
            ])->createView());
        }

        return $this->render('company/merchant.html.twig', [
            'form' => $form->createView(),
            'forms' => $forms
        ]);
    }

    /**
     * @param $host
     * @return string
     */
    function getServerEnv($host)
    {
        $env = stristr($host, 'testserver') || stristr($host, '127.0.0.1') ? 'dev' : 'prod';

        return $env;
    }

    /**
     * @param Request $request
     * @param SerializerInterface $serializer
     * @param Merchant $merchant
     * @return JsonResponse|Response
     */
    public function update(Request $request, SerializerInterface $serializer, Merchant $merchant)
    {
        if ($request->isXMLHttpRequest()) {
            $form = $this->createForm(MerchantType::class, $merchant, [
                'env' => $this->getServerEnv($request->getHost())
            ]);

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $this->manager->updateMerchant();

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
     * @param Merchant $merchant
     * @return JsonResponse|Response
     */
    public function delete(Request $request, SerializerInterface $serializer, Merchant $merchant)
    {
        if ($request->isXMLHttpRequest()) {
            try {
                $this->manager->removeMerchant($merchant);
                return new JsonResponse(['code' => 202, 'status' => 'success'], 202);
            } catch (\Exception $e) {
                return new JsonResponse($serializer->serialize(['error' => $e], 'json'), 500);
            }
        }

        return new Response('Request not valid', 400);
    }
}