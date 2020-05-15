<?php

namespace App\Controller\User;

use App\Form\User\ChangePasswordType;
use App\Form\User\ProfileType;
use App\Manager\RegistrationManager;
use App\Manager\UserManager;
use JMS\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use App\Form\User\UserType;
use App\Entity\User\User;
use Symfony\Contracts\Translation\TranslatorInterface;


class UserController extends AbstractController
{
    private $manager;

    public function __construct(UserManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function update(Request $request)
    {
        $form = $this->createForm(ProfileType::class, $this->getUser());
        $form->handleRequest($request);

       if ($form->isSubmitted() && $form->isValid()) {
            $this->manager->saveUser($this->getUser());

            return $this->redirect($this->generateUrl('profile_edit'));
        }

        return $this->render('account/profile/profile.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @param Request $request
     * @param RegistrationManager $registrationManager
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function list(Request $request, RegistrationManager $registrationManager)
    {
        $building = $this->getUser()->getBuilding();

        $user = new User();
        $form = $this->createForm(UserType::class, $user, [
            'validation_groups' => ['register_validation', 'Default']
        ]);

        $form->remove('isActive');
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $role = $request->request->get('user')['role'] == 'owner' ? 'ROLE_OWNER' : 'ROLE_EMPLOYEE';
            $registrationManager->addUserToBuilding($building, $user, $role);

            return $this->redirectToRoute('user_index');
        }

        $users = $this->manager->getBuildingUsers($building, $this->getUser());
        array_unshift($users, $this->getUser());

        $formsArray = [];

        foreach ($users as $user) {
            $role = in_array('ROLE_OWNER', $user->getRoles()) ? 'owner' : 'employee';

            $userForm = $this->createForm(UserType::class, $user, [
                'user_role' => $role
            ]);

            $userForm->remove('plainPassword');
            $formsArray[] = $userForm->createView();
        }

        $passwordForm = $this->createForm(ChangePasswordType::class, $user);

        return $this->render('account/user.html.twig', [
            'form' => $form->createView(),
            'forms' => $formsArray,
            'passwordForm' => $passwordForm->createView()
        ]);
    }

    /**
     * @param Request $request
     * @param TranslatorInterface $translator
     * @param SerializerInterface $serializer
     * @return JsonResponse|Response
     */
    public function changePassword(Request $request, TranslatorInterface $translator, SerializerInterface $serializer)
    {
        if ($request->isXMLHttpRequest()) {
            $user = $this->manager->find($request->request->get('user_id'));
            $form = $this->createForm(ChangePasswordType::class, $user);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $this->manager->saveUser($user);

                return new JsonResponse($translator->trans('resetting.successful_saving', [], 'messages'),  200);
            } else {
                return new JsonResponse($serializer->serialize(['error' => $form], 'json'), 500);
            }
        }

        return new Response('Request not valid', 400);
    }

    /**
     * @param Request $request
     * @param User $user
     * @param SerializerInterface $serializer
     * @return JsonResponse|Response
     * @throws \Exception
     */
    public function ajaxUpdate(Request $request, User $user, SerializerInterface $serializer)
    {
        if ($request->isXMLHttpRequest())
        {
            $form = $this->createForm(UserType::class, $user);
            $form->remove('plainPassword');
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $role = $request->request->get('user')['role'] == 'owner' ? 'ROLE_OWNER' : 'ROLE_EMPLOYEE';
                $this->manager->updateUser($user, [$role]);

                return new JsonResponse(['code' => 202, 'status' => 'success'], 202);
            } else {
                return new JsonResponse($serializer->serialize(['error' => $form], 'json'), 500);
            }
        }

        return new Response('Request not valid', 400);
    }
}
