<?php

namespace App\Form\Customer;

use App\Entity\Customer\POS;
use App\Form\Type\PriceType;
use App\Manager\MemberManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Valid;

class POSType extends AbstractType
{
    private $manager;

    public function __construct(MemberManager $manager)
    {
        $this->manager = $manager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('customerSearch', TextType::class, [
                'attr' => [
                    'class' => 'form-control text-uppercase',
                    'placeholder' => 'pos.customers_search',
                    'autocomplete' => 'off'
                ],
                'required' => false,
                'mapped' => false
            ])
            ->add('customer', CustomerType::class, [
                'label' => false
            ])
            ->add('productSearch', TextType::class, [
                'attr' => [
                    'class' => 'form-control text-uppercase',
                    'placeholder' => 'pos.products_search',
                    'autocomplete' => 'off',
                    'autofocus' => true
                ],
                'required' => false,
                'mapped' => false
            ])
            ->add('products', CollectionType::class, [
                'entry_type' => POSProductType::class,
                'allow_add' => true,
                'prototype' => true,
                'constraints' => [
                    new Valid(),
                    new NotBlank()
                ]
            ])
            ->add('receivedAmount', PriceType::class, [
                'attr' => [
                    'class' => 'form-control price-field',
                    'placeholder' => 'pos.received_amount',
                    'autocomplete' => 'off'
                ]
            ])
            ->add('returnedAmount', PriceType::class, [
                'attr' => [
                    'class' => 'form-control custom-read-only price-field',
                    'placeholder' => 'pos.change'
                ],
                'disabled' => true,
                'required' => false,
                'mapped' => false
            ])
            ->add('total', PriceType::class, [
                'currency' => false,
                'attr' => [
                    'class' => 'hidden custom-read-only'
                ]
            ]);

        // Add validation to customer only if customer must be added
        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $customerData = $event->getData()['customer'];

            if ($customerData['firstname'] || $customerData['lastname']) {
                $options = $event->getForm()->get('customer')->getConfig()->getOptions();
                $options['constraints'] = new Valid();
                $event->getForm()->add('customer', CustomerType::class, $options);
            }
        });
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => POS::class,
            'client' => null,
            'translation_domain' => 'labels'
        ]);
    }
}