<?php

namespace App\Form\User\Collection;

use App\Form\Type\PriceType;
use App\Form\User\PaymentSettingsType;
use App\Form\User\ModuleSettingsType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class SettingsCollection extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('settings', CollectionType::class, [
                'entry_type' => ModuleSettingsType::class,
                'label' => false,
                'allow_add' => true,
                'prototype' => true
            ])
            ->add('paymentSettings', CollectionType::class, [
                'entry_type' => PaymentSettingsType::class,
                'label' => false,
                'allow_add' => true,
                'prototype' => true
            ])
            ->add('deliveryPrice', PriceType::class, [
                'required' => false,
                'translation_domain' => 'labels',
                'label' => 'account.settings.delivery_price',
                'label_attr' => [
                    'class' => 'control-label'
                ],
                'attr' => [
                    'class' => 'form-control price-field',
                ],
                'constraints' => new NotBlank(['message' => 'validation.form.required'])
            ])
            ->add('orderTime', TextType::class, [
                'required' => false,
                'translation_domain' => 'labels',
                'label' => 'account.settings.time_to_order',
                'label_attr' => [
                    'class' => 'control-label'
                ],
                'attr' => [
                    'class' => 'form-control time-field',
                    'autocomplete' => 'off'
                ],
                'constraints' => new NotBlank(['message' => 'validation.form.required'])
            ])
            ->add('orderDisableTime', ChoiceType::class, [
                'choices' => [
                    'account.settings.day_before' => 0,
                    'account.settings.day_of' => 1
                ],
                'translation_domain' => 'labels',
                'label' => 'account.settings.when_to_disable',
                'label_attr' => [
                    'class' => 'control-label'
                ],
                'choice_attr' => function() {
                    return [
                        'class' => 'styled'
                    ];
                },
                'expanded' => true,
                'placeholder' => false
            ]);
    }
}