<?php

namespace App\Form\Customer;

use App\Entity\Customer\Location;
use App\Form\EventListener\AddressSubscriber;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Validator\Constraints\NotBlank;

class LocationType extends AbstractType
{
    private $locationListener;

    public function __construct(AddressSubscriber $subscriber)
    {
        $this->locationListener = $subscriber;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'customer.location.name',
                'label_attr' => [
                    'class' => 'col-md-2 col-sm-3 col-xs-5 control-label'
                ],
                'attr' => [
                    'data-behavior' => 'uppercase',
                    'data-type' => 'string',
                    'class' => 'form-control text-uppercase',
                    'placeholder' => 'customer.location.name'
                ]
            ])
            ->add('region', TextType::class, [
                'label' => 'customer.address.region',
                'label_attr' => [
                    'class' => 'col-md-2 col-sm-3 col-xs-5 control-label'
                ],
                'attr' => [
                    'data-type' => 'string',
                    'class' => 'form-control text-uppercase',
                    'placeholder' => 'customer.address.region'
                ],
                'constraints' => [
                    new NotBlank(['message' => 'validation.form.required'])
                ]
            ])
            ->add('city', TextType::class, [
                'label' => 'customer.address.city',
                'label_attr' => [
                    'class' => 'col-md-2 col-sm-3 col-xs-5 control-label'
                ],
                'attr' => [
                    'data-type' => 'string',
                    'class' => 'form-control text-uppercase',
                    'placeholder' => 'customer.address.city'
                ],
                'constraints' => [
                    new NotBlank(['message' => 'validation.form.required'])
                ]
            ])
            ->add('street', TextType::class, [
                'label' => 'customer.address.street',
                'label_attr' => [
                    'class' => 'col-md-2 col-sm-3 col-xs-5 control-label'
                ],
                'attr' => [
                    'class' => 'form-control text-uppercase',
                    'placeholder' => 'customer.address.street'
                ],
                'constraints' => [
                    new NotBlank(['message' => 'validation.form.required'])
                ]
            ])
            ->add('apartment', TextType::class, [
                'required' => false,
                'label' => 'customer.address.apartment',
                'label_attr' => [
                    'class' => 'col-md-2 col-sm-3 col-xs-5 control-label'
                ],
                'attr' => [
                    'data-type' => 'number',
                    'class' => 'form-control text-uppercase',
                    'placeholder' => 'customer.address.apartment'
                ]
            ])
            ->add('postalCode', TextType::class, [
                'label' => 'customer.address.postal_code',
                'label_attr' => [
                    'class' => 'col-md-2 col-sm-3 col-xs-5 control-label'
                ],
                'attr' => [
                    'data-type' => 'number',
                    'class' => 'form-control text-uppercase',
                    'placeholder' => 'customer.address.postal_code'
                ],
                'constraints' => [
                    new NotBlank(['message' => 'validation.form.required'])
                ]
            ])
            ->add('description', TextareaType::class, [
                'required' => false,
                'label' => 'customer.location.description',
                'label_attr' => [
                    'class' => 'col-md-2 col-sm-3 col-xs-5 control-label'
                ],
                'attr' => [
                    'rows' => 3,
                    'class' => 'form-control text-uppercase',
                    'placeholder' => 'customer.location.description'
                ]
            ])
            ->add('workdays', CollectionType::class, [
                'required' => false,
                'entry_type' => WorkdayType::class,
                'by_reference' => false,
                'allow_add' => true,
                'prototype' => true,
                'label' => 'customer.share.share_day',
                'label_attr' => [
                    'class' => 'col-md-2 col-sm-3 col-xs-5 control-label'
                ],
            ])
            ->add('isActive', CheckboxType::class, [
                'required' => false,
                'label' => 'table.active',
                'label_attr' => [
                    'class' => 'col-md-2 col-sm-3 col-xs-5 control-label'
                ],
                'attr' => [
                    'class' => 'switchery'
                ]
            ])
        ;

        $builder->addEventSubscriber($this->locationListener);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Location::class,
            'translation_domain' => 'labels',
            'country' => null
        ]);
    }
}
