<?php

namespace App\Form\Customer;

use App\Entity\Customer\Customer;
use App\Form\Type\PhoneType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;

class CustomerType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('id', TextType::class, [
                'required' => false,
                'label' => false,
                'attr' => [
                    'class' => 'hidden custom-read-only'
                ]
            ])
            ->add('client', TextType::class, [
                'required' => false,
                'label' => false,
                'data' => $options['client']->getId(),
                'attr' => [
                    'class' => 'hidden custom-read-only'
                ]
            ])
            ->add('firstname', TextType::class, [
                'attr' => [
                    'class' => 'form-control text-uppercase',
                    'placeholder' => 'customer.add.firstname'
                ],
                'required' => false
            ])
            ->add('lastname', TextType::class, [
                'attr' => [
                    'class' => 'form-control text-uppercase',
                    'placeholder' => 'customer.add.lastname'
                ],
                'required' => false
            ])
            ->add('phone', PhoneType::class, [
                'country_code' => $options['client']->getCountry(),
                'attr' => [
                    'class' => 'form-control text-uppercase',
                    'placeholder' => 'customer.add.phone'
                ],
                'required' => false
            ])
            ->add('email', EmailType::class, [
                'attr' => [
                    'class' => 'form-control text-lowercase',
                    'placeholder' => 'customer.add.email'
                ],
                'required' => false
            ]);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'labels',
            'data_class' => Customer::class,
            'client' => null
        ]);
    }
}