<?php

namespace App\Form\Client;

use App\Entity\Client\Client;
use App\Form\EventListener\OwnerLocationSubscriber;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

use MenaraSolutions\Geographer\Earth;

class ClientType extends AbstractType
{
    public $locationSubscriber;

    public function __construct(OwnerLocationSubscriber $subscriber)
    {
        $this->locationSubscriber = $subscriber;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $earth = new Earth();

        $countries = [];

        foreach ($earth->getCountries()->setLocale('ru') as $country) {
            $countries[$country->getName('ru')] = $country->getCode();
        }

        ksort($countries);

        $currencies = Client::getCurrencies();
        ksort($currencies);

        $builder
            ->add('name', TextType::class, [
                'translation_domain' => 'messages',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'register.farm_name'
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
                'choices' => $countries,
                'attr' => [
                    'class' => 'select'
                ],
                'empty_data' => 'US',
                'required' => false,
                'placeholder' => ''
            ])
            ->add('postalCode', TextType::class, [
                'attr' => [
                    'class' => 'form-control'
                ],
                'required' => false
            ])
            ->add('region', ChoiceType::class, [
                'label_attr' => [
                    'class' => 'control-label'
                ],
                'attr' => [
                    'class' => 'select'
                ],
                'required' => false,
                'placeholder' => ''
            ])
            ->add('city', ChoiceType::class, [
                'choices' => [],
                'label' => 'City',
                'label_attr' => [
                    'class' => 'col-md-2 col-sm-3 col-xs-5 control-label'
                ],
                'attr' => [
                    'class' => 'select'
                ],
                'required' => false,
                'placeholder' => ''
            ])
            ->add('weightFormat', ChoiceType::class, [
                'choices' => [
                    'Kg' => '1',
                    'Lbs' => '2'
                ],
                'attr' => [
                    'class' => 'select',
                ],
                'required' => false,
                'multiple' => false,
                'placeholder' => false
            ])
            ->add('currency', ChoiceType::class , [
                'choices' => $currencies,
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
