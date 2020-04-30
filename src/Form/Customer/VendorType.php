<?php

namespace App\Form\Customer;

use App\Entity\Customer\Vendor;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Validator\Constraints\Valid;
use Symfony\Contracts\Translation\TranslatorInterface;

class VendorType extends AbstractType
{
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
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
            ->add('name', TextType::class, [
                'label' => 'customer.vendor.name',
                'label_attr' => [
                    'class' => 'col-md-2 col-sm-3 col-xs-5 control-label'
                ],
                'attr' => [
                    'class' => 'form-control text-uppercase',
                    'placeholder' => 'customer.vendor.name',
                ]
            ])
            ->add('category', ChoiceType::class, [
                'translation_domain'=>'labels',
                'choices' =>  [
                    $this->translator->trans('product.vendor_eatery', [], 'labels') => 2,
                    $this->translator->trans('product.vendor_market', [], 'labels') => 3,
                ],
                'label' => 'customer.vendor.category',
                'label_attr' => [
                    'class' => 'col-md-2 col-sm-3 col-xs-5 control-label'
                ],
                'choice_attr' => [
                    'class' => 'styled'
                ],
                'required' => false,
                'multiple' => true,
                'expanded' => true,
                'placeholder' => false
            ])
            ->add('orderDay', ChoiceType::class, [
                'choices' => $daysOfWeek,
                'label' => 'customer.vendor.order_day',
                'label_attr' => [
                    'class' => 'col-md-2 col-sm-3 col-xs-5 control-label'
                ],
                'choice_attr' => [
                    'class' => 'styled'
                ],
                'required' => false,
                'multiple' => true,
                'expanded' => true,
                'placeholder' => false
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
            ->add('contacts', CollectionType::class, [
                'by_reference' => false,
                'entry_type' => ContactType::class,
                'entry_options' => [
                    'label' => 'customer.vendor.contacts',
                    'country_code' => $client->getCountry(),
                ],
                'allow_add' => true,
                'allow_delete' => true,
                'prototype' => true,
                'attr' => [
                    'class' => 'contacts',
                ],
                'constraints' => [
                    new Valid()
                ]
            ]);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Vendor::class,
            'translation_domain' => 'labels'
        ]);
    }
}