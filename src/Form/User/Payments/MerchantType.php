<?php

namespace App\Form\User\Payments;

use App\Entity\Customer\Merchant;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class MerchantType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('merchant', ChoiceType::class , [
                'choices' => [
                    'USA ePay' => 1
                ],
                'label' => 'merchant.merchant_select',
                'label_attr' => [
                    'class' => 'col-md-2 col-sm-3 col-xs-5 control-label'
                ],
                'attr' => [
                    'class' => 'select'
                ]
            ])
            ->add('key', TextType::class , [
                'label' => 'merchant.key',
                'label_attr' => [
                    'class' => 'col-md-2 col-sm-3 col-xs-5 control-label'
                ],
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('pin', TextType::class , [
                'required' => false,
                'label' => 'merchant.pin',
                'label_attr' => [
                    'class' => 'col-md-2 col-sm-3 col-xs-5 control-label'
                ],
                'attr' => [
                    'class' => 'form-control',
                    'maxlength' => 4
                ],
            ])
            ->add('currency', ChoiceType::class , [
                'choices' => [
                    '$ U.S. Dollar' => 1
                ],
                'label' => 'merchant.currency',
                'label_attr' => [
                    'class' => 'col-md-2 col-sm-3 col-xs-5 control-label'
                ],
                'attr' => [
                    'class' => 'select'
                ]
            ]);

        if ($options['env'] == 'dev') {
            $builder->add('isSandbox', CheckboxType::class, [
                'required' => false,
                'label' => 'merchant.is_sandbox',
                'label_attr' => [
                    'class' => 'col-md-2 col-sm-3 col-xs-5 control-label'
                ],
                'attr' => [
                    'class' => 'switchery'
                ]
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Merchant::class,
            'translation_domain' => 'labels',
            'env' => null
        ]);
    }
}