<?php

namespace App\Form\EventListener;

use App\Entity\Customer\CustomerShare;
use App\Manager\MemberManager;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use App\Form\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormInterface;

class ShareSubscriber implements EventSubscriberInterface
{
    private $factory;

    public $manager;

    public function __construct(FormFactoryInterface $factory, MemberManager $manager)
    {
        $this->factory = $factory;
        $this->manager = $manager;
    }

    public static function getSubscribedEvents()
    {
        return array(
            FormEvents::POST_SET_DATA => 'postSet',
            FormEvents::PRE_SUBMIT => 'preSubmit',
        );
    }

    /**
     * @param FormEvent $event
     */
    public function postSet(FormEvent $event)
    {
        $data = $event->getData();
        $form = $event->getForm();

        $pickupDay = null;

        if ($data) {
            $pickupDay = $data->getPickupDay();
            $this->modifyShareFields($form, $data);
        } else {
            $this->addPickupsNumField($form);
        }

        $this->addShareDays($form);
        $this->addLocations($form, $pickupDay);
    }

    /**
     * @param FormEvent $event
     */
    public function preSubmit(FormEvent $event)
    {
        $form = $event->getForm();
        $data = $event->getData();

        $customerShare = $form->getData();

        // If share id exists this means that we are in update action
        if ($customerShare && isset($data['sharesRemaining'])) {
            $pickupsLeft = $this->manager->countPickups($customerShare);

            // Update pickupsNum value in for the share if remainingShares field is changed (increase/decrease total amount)
            if ($data['sharesRemaining'] != $pickupsLeft) {
                $diff = $data['sharesRemaining'] - $pickupsLeft;
                $data['pickupsNum'] += $diff;

                $event->setData($data);
            }
        }

        $this->addShareDays($form);
        $this->addLocations($form, $data['pickUpDay']);
    }

    /**
     * @param FormInterface $form
     * @param $shareType
     */
    public function addShareDays(FormInterface $form, $shareType = null)
    {
        $client = $form->getConfig()->getOptions()['client'];

        $pickupDays = $this->manager->getClientWorkdays($client);

        $form->add('pickUpDay', ChoiceType::class, [
            'required' => false,
            'choices' => $pickupDays,
            'label' => !$shareType || $shareType == 'MEMBER' ? 'customer.share.share_day' : 'customer.share.order_day',
            'label_attr' => [
                'class' => 'col-md-2 col-sm-3 col-xs-5 control-label'
            ],
            'attr' => [
                'class' => 'select',
                'data-empty' => 'false',
            ]
        ]);
    }

    /**
     * @param FormInterface $form
     * @param $shareDay
     * @param $shareType
     */
    public function addLocations(FormInterface $form, $shareDay, $shareType = null)
    {
        $locations = $shareDay
            ? $this->manager->getLocationsByDay($form->getConfig()->getOptions()['client'], $shareDay) : [];

        $form->add('location', ChoiceType::class, [
            'choices' => $locations,
            'attr' => [
                'class' => 'radio',
            ],
            'label_attr' => [
                'class' => 'col-md-2 col-sm-3 col-xs-5 control-label'
            ],
            'choice_attr' => function() {
                return [
                    'data-empty' => 'false'
                ];
            },
            'label' => !$shareType || $shareType == 'MEMBER' ? 'customer.location.location' : 'customer.share.order_location',
            'choice_label' => function($location) {
                return mb_strtoupper($location->getName());
            },
            'choice_value' => function($location) {
                if ($location) return $location->getId();
            },
            'allow_extra_fields' => true,
            'required' => false,
            'multiple' => false,
            'expanded' => true,
            'placeholder' => false
        ]);
    }


    /**
     * Here we changing fields for customer shares in customer add/edit pages
     *
     * We disable "startDate" and hide "pickupsNum" field and show new "sharesRemaining" field.
     * Changing of remaining shares field trigger changing of pickups num in db on updatePickups() function of MemberManager,
     * so we use "pickupsNum" in hidden mode to get actual database value (in javascript too);
     *
     * After share is added, client can`t change start date or pickupsNum fields.
     * Client can change only remaining shares field for controlling pickups left number.
     *
     * If share is related to PATRONS type of customers -> hide redundant fields and change label names:
     * Start date -> Order date, Share day -> Order day, Share location -> Order location
     *
     * @param FormInterface $form
     * @param CustomerShare|null $share
     */
    public function modifyShareFields(FormInterface $form, CustomerShare $share = null)
    {
        // Get start date (or order date field for patrons) options
        $date = $form->get('startDate')->getConfig()->getOptions();

        // If share already added to database (client on the edit page)
        if ($share) {
            // If share added and share for MEMBERS, add shares remaining and renewal date fields
            if ($share->getTypeName() == 'MEMBER') {
                $this->addRenewalDateField($form);
                $this->addSharesRemaining($form, $share);

                // Start date (or order date) field must be disabled
                $date['disabled'] = true;
                $date['attr']['class'] = 'form-control custom-read-only';
            }

            // Hide pickups num field in edit page because clients will use shares remaining field for existed share
            $form->add('pickupsNum', HiddenType::class, [
                'attr' => [
                    'class' => 'hidden'
                ]
            ]);
        } else {
            $this->addPickupsNumField($form, $share->getTypeName());
        }

        // Set label based on share type and update startDate field
        if ($share->getTypeName() == 'PATRON') {
            $date['label'] = 'customer.share.order_date';
            $date['attr']['data-min'] = 1;
        } else {
            $date['label'] = 'customer.share.start_date';
        }

        $form->add('startDate', DateType::class, $date);
    }

    /**
     *
     * Pickups num shows only for new shares, not added yet and if share type is not for Patrons
     * This field hold value of active pickups number from start date to renewal date
     *
     * @param FormInterface $form
     * @param $shareType
     */
    public function addPickupsNumField(FormInterface $form, $shareType = null)
    {
        if ($shareType == 'PATRON') {
            // Hide pickups num field
            $form->add('pickupsNum', HiddenType::class, [
                'attr' => [
                    'class' => 'hidden'
                ]
            ]);
        } else {
            $form->add('pickupsNum', ChoiceType::class, [
                'translation_domain' => 'labels',
                'choices' => range(1,36,1),
                'choice_label' => function ($choice) {
                    return $choice;
                },
                'attr' => [
                    'data-empty' => 'false',
                    'class' => 'select',
                ],
                'label' => 'customer.share.shares_num',
                'label_attr' => [
                    'class' => 'col-md-2 col-sm-3 col-xs-5 control-label'
                ],
                'required' => false,
                'placeholder' => false
            ]);
        }
    }


    /**
     *
     * Shares remaining field for control pickups number after adding a share
     * Hidden pickupsNum field will hold value of active pickups from start date to end date, so it readonly
     *
     * Shares remaining field helps to control only pickups that will be in a future
     *
     * @param FormInterface $form
     * @param CustomerShare $share
     */
    public function addSharesRemaining(FormInterface $form, CustomerShare $share)
    {
        $sharesLeft = $this->manager->countPickups($share);

        // Add not mapped sharesRemaining field for control of pickups left number (shares remaining increase/decrease)
        $options['label'] = 'customer.share.remaining_shares';
        $options['label_attr']['class'] = 'col-md-2 col-sm-3 col-xs-5 control-label';
        $options['choices'] = range(0, 36, 1);
        $options['data'] = $sharesLeft;
        $options['mapped'] = false;
        $options['attr']['class'] = 'select';
        $form->add('sharesRemaining', ChoiceType::class, $options);
    }

    /**
     *
     * Read only field for showing calculating of renewal date
     *
     * @param FormInterface $form
     */
    public function addRenewalDateField(FormInterface $form)
    {
        // Renewal date for show calculated renewal date data
        $form->add('renewalDate', DateType::class, [
            'disabled' => true,
            'html5'   => false,
            'widget'  => 'single_text',
            'label' => 'customer.share.renewal_date',
            'label_attr' => [
                'class' => 'col-md-2 col-sm-3 col-xs-5 control-label'
            ],
            'attr' => [
                'class' => 'form-control custom-read-only datepicker',
            ],
            'format' => $form->getConfig()->getOptions()['date_format']
        ]);
    }
}
