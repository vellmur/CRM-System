<?php

namespace App\Form\User;

use App\Entity\Client\PaymentSettings;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

class PaymentSettingsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('isActive', CheckboxType::class, [
                'required' => false,
                'label' => false,
                'attr' => [
                    'class' => 'switchery table-switch'
                ]
            ])
            ->add('description', TextareaType::class, [
                'required' => false,
                'label' => 'account.payment.description',
                'label_attr' => [
                    'class' => 'col-md-2 col-sm-3 col-xs-5 control-label'
                ],
                'attr' => [
                    'rows' => 3,
                    'data-type' => 'string',
                    'class' => 'form-control',
                    'placeholder' => 'account.payment.description'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => PaymentSettings::class,
            'translation_domain' => 'labels'
        ]);
    }

}
