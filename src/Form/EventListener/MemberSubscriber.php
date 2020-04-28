<?php

namespace App\Form\EventListener;

use App\Form\Customer\NotificationType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class MemberSubscriber implements EventSubscriberInterface
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
            FormEvents::POST_SET_DATA => 'postSet',
            FormEvents::PRE_SUBMIT => 'onPreSubmit',
            FormEvents::POST_SUBMIT => 'onPostSubmit',
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
     * @param FormEvent $event
     */
    public function onPreSubmit(FormEvent $event)
    {
        $data = $event->getData();

        // Fix collection indexes validation issue.
        // Description of bug at the bottom of the page: https://knpuniversity.com/screencast/collections/embedded-validation
        if (isset($data['addresses'])) {
            $data['addresses'] = array_values($data['addresses']);

            $event->setData($data);
        }
    }

    /**
     * @param FormEvent $event
     */
    public function onPostSubmit(FormEvent $event)
    {
        $form = $event->getForm();
        $customer = $event->getData();

        $this->checkUniqueAddress($form, $customer->getAddresses());
    }

    /**
     *
     * UniqueEntity validation doesn't works on new added collection items.
     * So here we check unique of address and add error manually.
     *
     * @param FormInterface $form
     * @param $addresses
     */
    public function checkUniqueAddress(FormInterface $form, $addresses)
    {
        $types = [];

        foreach ($addresses as $address) {
            $types[] = $address->getType();
        }

        $uniqueError = in_array(2, array_count_values($types));

        if ($uniqueError) {
            foreach ($form->get('addresses') as $addressType) {
                $addressType->get('type')->addError(new FormError(
                    'Customer can`t have two ' . $addresses[0]->getTypeName() . ' addresses!'
                ));
            }
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
