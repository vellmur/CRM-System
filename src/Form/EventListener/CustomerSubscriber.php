<?php

namespace App\Form\EventListener;

use App\Form\Customer\NotificationType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormInterface;

class CustomerSubscriber implements EventSubscriberInterface
{
    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            FormEvents::POST_SET_DATA => 'postSet'
        ];
    }

    /**
     * @param FormEvent $event
     */
    public function postSet(FormEvent $event)
    {
        $form = $event->getForm();
        $options = $form->getConfig()->getOptions();

        // If page is not membership page - add required fields
        if (!$options['isMembership']) {
            $this->addNotes($form);
        } else {
            $this->addNotifications($form);
        }
    }

    /**
     * @param FormInterface $form
     */
    private function addNotes(FormInterface $form)
    {
        $form->add('notes', TextareaType::class, [
            'required' => false,
            'label' => 'customer.add.notes',
            'label_attr' => [
                'class' => 'col-md-2 col-sm-3 col-xs-5 control-label'
            ],
            'attr' => [
                'rows' => 7,
                'data-type' => 'string',
                'class' => 'form-control text-uppercase',
                'placeholder' => 'customer.add.notes'
            ]
        ]);
    }

    /**
     * @param FormInterface $form
     */
    public function addNotifications(FormInterface $form)
    {
        $form->add('notifications', CollectionType::class, [
            'entry_type' => NotificationType::class,
            'label' => false,
            'required' => false
        ]);
    }
}
