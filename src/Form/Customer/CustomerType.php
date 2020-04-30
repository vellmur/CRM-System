<?php

namespace App\Form\Customer;

use App\Entity\Customer\Customer;
use App\Form\EventListener\CustomerSubscriber;
use App\Form\Type\PhoneType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;

class CustomerType extends AbstractType
{
    private $listener;

    public function __construct(CustomerSubscriber $subscriber)
    {
        $this->listener = $subscriber;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstname', TextType::class, [
                'required' => false,
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
                'required' => false,
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
                'country_code' => $builder->getData() ? $builder->getData()->getClient()->getCountry() : null,
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
            ->add('apartment', ApartmentType::class, [
                'required' => false
            ])
        ;

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