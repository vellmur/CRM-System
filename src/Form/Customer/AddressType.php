<?php

namespace App\Form\Customer;

use App\Entity\Customer\Address;
use App\Form\EventListener\AddressSubscriber;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotNull;

class AddressType extends AbstractType
{
    private $locationListener;

    public function __construct(AddressSubscriber $subscriber)
    {
        $this->locationListener = $subscriber;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options*
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('type', ChoiceType::class, [
                'choices' =>  [
                    'customer.address.types.billing' => 1,
                    'customer.address.types.billing_and_delivery' => 2,
                    'customer.address.types.delivery' => 3
                ],
                'attr' => [
                    'class' => 'select'
                ],
                'label' => 'customer.address.type',
                'label_attr' => [
                    'class' => 'col-md-2 col-sm-3 col-xs-5 control-label'
                ],
                'required' => false,
                'placeholder' => false
            ])
            ->add('street', TextType::class, [
                'label' => 'customer.address.street',
                'label_attr' => [
                    'class' => 'col-md-2 col-sm-3 col-xs-5 control-label'
                ],
                'attr' => [
                    'class' => 'form-control text-uppercase readonly-visible',
                    'placeholder' => 'customer.address.street',
                    'data-type' => 'string',
                    'onfocus'=> "this.removeAttribute('readonly');",
                    'readonly' => 'readonly'
                ],
                'constraints' => [
                    new NotNull()
                ]
            ])
            ->add('apartment', TextType::class, [
                'required' => false,
                'label' => 'customer.address.apartment',
                'label_attr' => [
                    'class' => 'col-md-2 col-sm-3 col-xs-5 control-label'
                ],
                'attr' => [
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
                    'class' => 'form-control text-uppercase',
                    'placeholder' => 'customer.address.postal_code',
                    'data-type' => 'number'
                ],
                'constraints' => [
                    new NotNull()
                ]
            ])
            ->add('region', TextType::class, [
                'label' => 'customer.address.region',
                'label_attr' => [
                    'class' => 'col-md-2 col-sm-3 col-xs-5 control-label'
                ],
                'attr' => [
                    'class' => 'form-control text-uppercase',
                    'placeholder' => 'customer.address.region',
                    'data-type' => 'text'
                ],
                'constraints' => [
                    new NotNull()
                ]
            ])
            ->add('city', TextType::class, [
                'label' => 'customer.address.city',
                'label_attr' => [
                    'class' => 'col-md-2 col-sm-3 col-xs-5 control-label'
                ],
                 'attr' => [
                     'class' => 'form-control text-uppercase',
                     'placeholder' => 'customer.address.city',
                     'data-type' => 'text'
                 ],
                'constraints' => [
                    new NotNull()
                ]
             ]);

        $builder->addEventSubscriber($this->locationListener);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Address::class,
            'country' => null
        ]);
    }
}