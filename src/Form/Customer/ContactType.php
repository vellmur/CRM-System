<?php

namespace App\Form\Customer;

use App\Entity\Customer\Contact;
use App\Form\Type\PhoneType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContactType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (!$options['country_code'] && $builder->getData()) {
            $options['country_code'] = $builder->getData()->getVendor()->getClient()->getCountry();
        }

        $builder
            ->add('name', TextType::class, [
                'label' => 'customer.vendor.name',
                'label_attr' => [
                    'class' => 'col-md-2 col-sm-3 col-xs-5 control-label'
                ],
                'attr' => [
                    'data-behavior' => 'uppercase',
                    'class' => 'form-control text-uppercase',
                    'data-type' => 'string',
                    'data-empty' => 'false',
                    'placeholder' => 'customer.vendor.name'
                ]
            ])
            ->add('email', TextType::class, [
                'label' => 'customer.add.email',
                'label_attr' => [
                    'class' => 'col-md-2 col-sm-3 col-xs-5 control-label'
                ],
                'attr' => [
                    'data-behavior' => 'lowercase',
                    'class' => 'form-control text-lowercase',
                    'data-empty' => 'false',
                    'placeholder' => 'customer.add.email'
                ]
            ])
            ->add('phone', PhoneType::class, [
                'country_code' => $options['country_code'],
                'label' => 'customer.add.phone',
                'label_attr' => [
                    'class' => 'col-md-2 col-sm-3 col-xs-5 control-label'
                ],
                'attr' => [
                    'class' => 'form-control text-lowercase',
                    'placeholder' => 'customer.add.phone'
                ]
            ])
            ->add('notifyEnabled', CheckboxType::class, [
                'required' => false,
                'by_reference' => false,
                'label' => 'emails.is_notification_enabled',
                'label_attr' => [
                    'class' => 'col-md-2 col-sm-3 col-xs-5 control-label'
                ],
                'attr' => [
                    'class' => 'switchery'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Contact::class,
            'country_code' => null,
            'translation_domain' => 'labels'
        ]);
    }
}