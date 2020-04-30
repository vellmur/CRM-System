<?php

namespace App\Form\Customer;

use App\Form\CardPaymentType;
use App\Form\EventListener\RenewSubscriber;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Valid;

class RenewType extends AbstractType
{
    private $subscriber;

    public function __construct(RenewSubscriber $subscriber)
    {
        $this->subscriber = $subscriber;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('products', ChoiceType::class, [
                'required' => false,
                'label' => false,
                'expanded' => true,
                'multiple' => true
            ])
            ->add('location', ChoiceType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'Please choose one of the pick-up locations.'])
                ],
                'label' => 'customer.add.location',
                'expanded' => true,
                'placeholder' => false
            ])
            ->add('member', CustomerType::class, [
                'data' => $options['customer'],
                'date_format' => 'y-M-d',
                'label' => false,
                'isMembership' => true
            ])
            ->add('locationAddress', AddressType::class, [
                'attr' => [
                    'class' => 'hidden'
                ],
                'label' => 'membership.renew.addresses.delivery_address',
                'label_attr' => [
                    'class' => 'control-label bold-text'
                ],
                'constraints' => [
                    new Valid()
                ]
            ])
            ->add('isNeedBilling', ChoiceType::class, [
                'choices' => [
                    'membership.renew.location.different_billing_address' => 1
                ],
                'choice_attr' => function($choice) {
                    return [
                        'class' => 'styled'
                    ];
                },
                'expanded' => true,
                'multiple' => true
            ])
            ->add('billingAddress', AddressType::class, [
                'attr' => [
                    'class' => 'hidden'
                ],
                'label' => 'membership.renew.addresses.billing_address',
                'label_attr' => [
                    'class' => 'control-label bold-text'
                ],
                'constraints' => [
                    new Valid()
                ]
            ])
            ->add('card', CardPaymentType::class)
            ->add('renewSubmit', SubmitType::class, [
                'label' => 'button.purchase',
                'attr' => [
                    'class' => 'btn btn-action',
                    'formnovalidate' => 'formnovalidate'
                ],
            ])
          ;

        $builder->addEventSubscriber($this->subscriber);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'labels',
            'client' => null,
            'customer' => null
        ]);
    }
}