<?php

namespace App\Controller\User;

use App\Form\Client\SubscriptionType;
use App\Manager\User\SubscriptionManager;
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
}