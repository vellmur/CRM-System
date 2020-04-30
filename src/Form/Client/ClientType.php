<?php

namespace App\Form\Client;

use App\Entity\Client\Client;
use App\Form\EventListener\AddressSubscriber;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints\NotNull;

class ClientType extends AbstractType
{
    public $locationSubscriber;

    /**
     * ClientType constructor.
     * @param AddressSubscriber $subscriber
     */
    public function __construct(AddressSubscriber $subscriber)
    {
        $this->locationSubscriber = $subscriber;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $currencies = Client::getCurrencies();
        ksort($currencies);

        $builder
            ->add('name', TextType::class, [
                'translation_domain' => 'messages',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'register.company_name'
                ]
            ])
            ->add('email', TextType::class, [
                'required' => false,
                'label' => 'customer.add.email',
                'label_attr' => [
                    'class' => 'control-label'
                ],
                'attr' => [
                    'data-empty' => 'false',
                    'class' => 'form-control'
                ]
            ])
            ->add('country', ChoiceType::class, [
                'choices' => [
                    'Россия' => 'ru',
                    'Україна' => 'ua'
                ],
                'attr' => [
                    'class' => 'select'
                ],
                'empty_data' => 'uk',
                'required' => false,
                'placeholder' => ''
            ])
            ->add('street', TextType::class, [
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
            ->add('postalCode', TextType::class, [
                'attr' => [
                    'class' => 'form-control'
                ],
                'required' => false
            ])
            ->add('region', TextType::class, [
                'attr' => [
                    'class' => 'form-control'
                ],
                'required' => false
            ])
            ->add('city', TextType::class, [
                'attr' => [
                    'class' => 'form-control'
                ],
                'required' => false
            ])
            ->add('currency', ChoiceType::class , [
                'choices' =>  $currencies,
                'placeholder' => false,
                'label' => 'account.settings.currency',
                'attr' => [
                    'class' => 'select'
                ]
            ]);

        $builder->addEventSubscriber($this->locationSubscriber);
    }


    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Client::class,
            'validation_groups' => ['register_validation', 'profile_validation'],
            'translation_domain' => 'labels',
            'cascade_validation' => true
        ]);
    }

    public function getName()
    {
        return 'client';
    }
}
