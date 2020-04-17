<?php

namespace App\Form\Customer;

use App\Entity\Customer\TestimonialRecipient;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TestimonialRecipientType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstname', TextType::class, [
                'label' => 'customer.add.firstname',
                'label_attr' => [
                    'class' => 'col-md-2 col-sm-3 col-xs-5 control-label'
                ],
                'attr' => [
                    'class' => 'form-control text-uppercase',
                    'placeholder' => 'customer.add.firstname'
                ]
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
            ->add('email', EmailType::class, [
                'required' => false,
                'label' => 'customer.add.email',
                'label_attr' => [
                    'class' => 'col-md-2 col-sm-3 col-xs-5 control-label'
                ],
                'attr' => [
                    'class' => 'form-control text-lowercase',
                    'placeholder' => 'customer.add.email'
                ]
            ]);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'labels',
            'data_class' => TestimonialRecipient::class
        ]);
    }
}