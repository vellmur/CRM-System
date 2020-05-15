<?php

namespace App\Form\Building;

use App\Form\Subscriber\SubscriptionSubscriber;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Contracts\Translation\TranslatorInterface;

class SubscriptionType extends AbstractType
{
    private $subscriber;

    private $translator;

    public function __construct(SubscriptionSubscriber $subscriber, TranslatorInterface $translator)
    {
        $this->subscriber = $subscriber;
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('module', TextType::class, [
                'attr' => [
                    'class' => 'hidden'
                ],
                'constraints' => [
                    new NotBlank(['message' => $this->translator->trans('payment.validation.empty_module', [], 'messages')])
                ]
            ])
            ->add('donations', ChoiceType::class, [
                'choices' => [
                    $this->translator->trans('payment.description.first_level', ['%amount%' => 50], 'messages') => 50,
                    $this->translator->trans('payment.description.second_level', ['%amount%' => 100], 'messages') => 100,
                    $this->translator->trans('payment.description.third_level', ['%amount%' => 150], 'messages') => 150,
                    $this->translator->trans('payment.description.fourth_level', ['%amount%' => 250], 'messages') => 250
                ],
                'label_attr' => [
                    'class' => 'control-label'
                ],
                'choice_attr' => function() {
                    return [
                        'class' => 'styled'
                    ];
                },
                'expanded' => true
            ])
            ->add('amount', TextType::class, [
                'attr' => [
                    'class' => 'hidden'
                ],
                'constraints' => [
                    new NotBlank(['message' => $this->translator->trans('payment.validation.empty_amount', [], 'messages')])
                ]
            ])
            ->add('method', ChoiceType::class, [
                'choices' => [
                    $this->translator->trans('payment.credit_card', [], 'messages') => 1,
                    'Venmo' => 2
                ],
                'label_attr' => [
                    'class' => 'control-label'
                ],
                'choice_attr' => function($choice) {
                    return [
                        'class' => 'styled',
                        'data-name' => $choice == 1 ? $this->translator->trans('payment.credit_card', [], 'messages') : 'Venmo'
                    ];
                },
                'constraints' => [
                    new NotBlank(['message' => $this->translator->trans('payment.validation.empty_method', [], 'messages')])
                ],
                'label' => 'payment.method',
                'expanded' => true,
                'placeholder' => false
            ])
            ->add('card', CardPaymentType::class);

        $builder->addEventSubscriber($this->subscriber);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'messages',
        ]);
    }
}