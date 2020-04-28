<?php

namespace App\Form\Customer;

use App\Entity\Customer\Customer;
use App\Form\EventListener\MemberSubscriber;
use App\Form\Type\PhoneType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Contracts\Translation\TranslatorInterface;

class MemberType extends AbstractType
{
    private $listener;

    private $translator;

    public function __construct(MemberSubscriber $subscriber, TranslatorInterface $translator)
    {
        $this->listener = $subscriber;
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $client = $builder->getData()->getClient();

        $daysOfWeek = [
            $this->translator->trans('sunday', [], 'choices') => 7,
            $this->translator->trans('monday', [], 'choices') => 1,
            $this->translator->trans('tuesday', [], 'choices') => 2,
            $this->translator->trans('wednesday', [], 'choices') => 3,
            $this->translator->trans('thursday', [], 'choices') => 4,
            $this->translator->trans('friday', [], 'choices') => 5,
            $this->translator->trans('saturday', [], 'choices') => 6
        ];

        $builder
            ->add('firstname', TextType::class, [
                'label' => 'customer.add.firstname',
                'label_attr' => [
                    'class' => 'col-md-2 col-sm-3 col-xs-5 control-label'
                ],
                'attr' => [
                    'class' => 'form-control text-uppercase',
                    'placeholder' => 'customer.add.firstname'
                ],

            ])
            ->add('lastname', TextType::class, [
                'label' => 'customer.add.lastname',
                'label_attr' => [
                    'class' => 'col-md-2 col-sm-3 col-xs-5 control-label'
                ],
                'attr' => [
                    'class' => 'form-control text-uppercase',
                    'placeholder' => 'customer.add.lastname'
                ]
            ])
            ->add('phone', PhoneType::class, [
                'required' => false,
                'country_code' => $client->getCountry(),
                'label' => 'customer.add.phone',
                'label_attr' => [
                    'class' => 'col-md-2 col-sm-3 col-xs-5 control-label'
                ],
                'attr' => [
                    'class' => 'form-control text-uppercase',
                    'placeholder' => 'customer.add.phone',
                    'data-rule-phoneOrEmailRequired' => 'true'
                ]
            ])
            ->add('email', EmailType::class, [
                'required' => false,
                'label' => 'customer.add.email',
                'label_attr' => [
                    'class' => 'col-md-2 col-sm-3 col-xs-5 control-label'
                ],
                'attr' => [
                    'class' => 'form-control text-lowercase',
                    'placeholder' => 'customer.add.email',
                    'data-rule-phoneOrEmailRequired' => 'true'
                ]
            ])
            ->add('addresses', CollectionType::class, [
                'required' => false,
                'entry_type' => AddressType::class,
                'entry_options' => [
                    'country' => $client->getCountry()
                ],
                'by_reference' => false,
                'allow_add' => true,
                'allow_delete' => true,
                'prototype' => true,
                'label' => false,
            ]);

        $builder->addEventSubscriber($this->listener);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Customer::class,
            'translation_domain' => 'labels',
            'date_format' => 'yyyy-MM-dd',
            'isMembership' => null
        ]);
    }
}