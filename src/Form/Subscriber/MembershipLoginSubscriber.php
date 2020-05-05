<?php

namespace App\Form\Subscriber;

use App\Entity\Customer\Customer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Contracts\Translation\TranslatorInterface;

class MembershipLoginSubscriber implements EventSubscriberInterface
{
    private $factory;

    private $em;

    private $translator;

    public function __construct(FormFactoryInterface $factory, EntityManagerInterface $em, TranslatorInterface $translator)
    {
        $this->factory = $factory;
        $this->em = $em;
        $this->translator = $translator;
    }

    public static function getSubscribedEvents()
    {
        return [
            FormEvents::PRE_SUBMIT => 'preSubmit'
        ];
    }

    /**
     * @param FormEvent $event
     */
    public function preSubmit(FormEvent $event)
    {
        $form = $event->getForm();
        $data = $event->getData();

        if ($data['email']) {
            $members = $this->em->getRepository(Customer::class)->findBy(['email' => $data['email']]);

            if (count($members) > 1) {
                $this->addClients($form, $members);
            }
        }
    }

    /**
     * @param FormInterface $form
     * @param $members
     */
    public function addClients(FormInterface $form, $members)
    {
        $clients = [];

        foreach ($members as $member) {
            $clients[$member->getClient()->getName()] = $member->getClient()->getId();
        }

        $form->add('client', ChoiceType::class, [
            'label' => $this->translator->trans('membership.client', [], 'labels'),
            'label_attr' => [
                'class' => 'control-label'
            ],
            'attr' => [
                'data-empty' => 'false',
                'class' => 'select',
            ],
            'choices' => $clients,
            'placeholder' => '',
            'constraints' => [
                new NotBlank()
            ]
        ]);
    }
}
