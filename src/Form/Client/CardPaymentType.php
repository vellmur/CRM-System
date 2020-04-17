<?php

namespace App\Form\Client;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotNull;

class CardPaymentType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'translation_domain' => 'labels',
                'label' => false,
                'attr' => [
                    'placeholder' => 'account.payment.card.name',
                    'class' => 'form-control text-uppercase',
                    'autocomplete' => 'off'
                ],
                'constraints' => [
                    new NotNull(['message' => 'validation.form.required'])
                ]
            ])
            ->add('number', TextType::class, [
                'translation_domain' => 'labels',
                'label' => false,
                'attr' => [
                    'placeholder' => 'account.payment.card.number',
                    'class' => 'form-control',
                    'data-stripe' => 'number',
                    'size' => 20,
                    'autocomplete' => 'off'
                ],
                'constraints' => [
                    new NotNull(['message' => 'validation.form.required'])
                ]
            ])
            ->add('cvc', NumberType::class, [
                'label' => false,
                'attr' => [
                    'placeholder' => 'CVV',
                    'data-mask' => '9999',
                    'class' => 'form-control',
                    'data-stripe' => "cvc",
                    'size' => 4,
                    'autocomplete' => 'off'
                ],
                'constraints' => [
                    new NotNull(['message' => 'validation.form.required'])
                ]
            ])
            ->add('expiredAt', TextType::class, [
                'attr' => [
                    'placeholder' => 'MM / YY',
                    'class' => 'form-control',
                    'autocomplete' => 'off'
                ],
                'constraints' => [
                    new NotNull(['message' => 'validation.form.required'])
                ]
            ])
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'labels'
        ]);
    }
}
