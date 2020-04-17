<?php

namespace App\Form\EventListener;

use App\Entity\Client\Client;
use App\Entity\Customer\Location;
use App\Entity\Customer\Customer;
use App\Form\Customer\SharesType;
use App\Form\Customer\NotificationType;
use App\Form\Customer\TestimonialRecipientType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Validator\Constraints\Valid;
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
        $customer = $form->getData();

        // Remove share day field in add page (for new customer)
        if (!$customer->getId()) {
            $form->remove('deliveryDay');
        }

        // If page is not membership page - add required fields
        if (!$options['isMembership']) {
            $this->addShares($form, $customer->getClient(), $options['date_format']);
            $this->addNotes($form);
        } else {
            $form->remove('deliveryDay');
            $this->addNotifications($form);
            $this->addTestimonial($form);
            $this->addTestimonialRecipient($form);
        }
    }

    /**
     * @param FormEvent $event
     */
    public function onPreSubmit(FormEvent $event)
    {
        $form = $event->getForm();
        $data = $event->getData();

        $form->remove('testimonialRecipient');
        if (isset($data['testimonialRecipient'])) unset($data['testimonialRecipient']);
        $event->setData($data);

        // Fix collection indexes validation issue.
        // Description of bug at the bottom of the page: https://knpuniversity.com/screencast/collections/embedded-validation
        if (isset($data['addresses'])) {
            $data['addresses'] = array_values($data['addresses']);

            $event->setData($data);
        }

        $client = $form->getData()->getClient();
        $options = $form->getConfig()->getOptions();

        // Validation for a customer add/edit page
        if ($client && !$form->getParent() && !$options['isMembership']) {
             // If address fields not required -> Remove validation
             if (!$this->isAddressRequired($data)) {
                 // Removing validation
                 $options = $form->get('addresses')->getConfig()->getOptions();
                 $options['validation_groups'] = false;
                 $form->add('addresses', CollectionType::class, $options);

                 // If it is new customer, empty address must be removed from form data
                 if (isset($data['email']) && $this->isNewMember($client, $data['email'])) {
                     unset($data['addresses']);
                 }
             }

             $event->setData($data);
         }
    }

    /**
     * @param FormEvent $event
     */
    public function onPostSubmit(FormEvent $event)
    {
        $form = $event->getForm();
        $member = $event->getData();

        $this->checkUniqueAddress($form, $member->getAddresses());
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
     * If addresses fields exists and one of the shares have delivery to the home location -> Require address
     * Or if one of the addresses fields not empty -> Require address
     *
     * @param $data
     * @return bool
     */
    private function isAddressRequired($data)
    {
        $required = false;

        if (array_key_exists('addresses', $data) && count($data['addresses']) == 1) {
            if ((array_key_exists('shares', $data) && $this->haveHomeDelivery($data['shares']))
                || $this->startedTypingAddress($data['addresses'][0])) {
                $required = true;
            }
        }

        return $required;
    }

    /**
     *
     * If one of address fields not empty -> address is started filling
     *
     * @param $address
     * @return bool
     */
    private function startedTypingAddress($address)
    {
        $startedTyping = false;

        if (strlen($address['street']) != 0 || strlen($address['apartment']) != 0 || strlen($address['postalCode']) != 0
            || strlen($address['region']) != 0 || strlen($address['city']) != 0)
        {
            $startedTyping = true;
        }

        return $startedTyping;
    }

    /**
     *
     * Check if one of Customer Shares have delivery to the home location
     *
     * @param $shares
     * @return bool
     */
    private function haveHomeDelivery($shares)
    {
        $haveHomeDelivery = false;

        foreach ($shares as $share) {
            if (isset($share['location'])) {
                $location = $this->em->getRepository(Location::class)->find($share['location']);

                if ($location->isDelivery()) {
                    $haveHomeDelivery = true;
                    break;
                };
            }
        }

        return $haveHomeDelivery;
    }

    /**
     *
     * Check if saved Customer is new
     *
     * @param Client $client
     * @param $email
     * @return bool
     */
    private function isNewMember(Client $client, $email)
    {
       $isNewMember = $this->em->getRepository(Customer::class)->findOneBy([
           'client' => $client,
           'email' => $email
       ]) ? false : true;

       return $isNewMember;
    }

    /**
     * @param FormInterface $form
     * @param Client $client
     * @param $dateFormat
     */
    private function addShares(FormInterface $form, Client $client, $dateFormat)
    {
        $form->add('shares', CollectionType::class, [
            'entry_type' => SharesType::class,
            'entry_options' => [
                'label' => 'customer.share.shares',
                'date_format' => $dateFormat,
                'client' => $client
            ],
            'by_reference' => false,
            'allow_add' => true,
            'allow_delete' => true,
            'prototype' => true,
            'attr' => [
                'class' => 'shares',
            ],
            'constraints' => [
                new Valid()
            ]
        ]);
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

    /**
     * @param FormInterface $form
     */
    public function addTestimonial(FormInterface $form)
    {
        $label = $this->translator->trans('membership.testimonial.share_testimonial', [], 'labels');

        $form->add('testimonial', TextareaType::class, [
            'required' => false,
            'label' => $label,
            'label_attr' => [
                'class' => 'col-md-2 col-sm-3 col-xs-5 control-label'
            ],
            'attr' => [
                'rows' => 7,
                'data-type' => 'string',
                'class' => 'form-control text-uppercase',
                'placeholder' => $label
            ]
        ]);
    }

    /**
     * @param FormInterface $form
     */
    public function addTestimonialRecipient(FormInterface $form)
    {
        $label = $this->translator->trans('membership.testimonial.testimonial', [], 'labels');

        $form ->add('testimonialRecipient', TestimonialRecipientType::class, [
            'mapped' => false,
            'required' => false,
            'label' => $label,
            'label_attr' => [
                'class' => 'col-md-2 col-sm-3 col-xs-5 control-label'
            ],
            'attr' => [
                'rows' => 7,
                'data-type' => 'string',
                'class' => 'form-control text-uppercase',
                'placeholder' => $label
            ]
        ]);
    }
}
